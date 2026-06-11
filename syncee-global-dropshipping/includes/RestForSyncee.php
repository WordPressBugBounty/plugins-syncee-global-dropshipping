<?php


class RestForSyncee
{
    private $namespace = 'syncee/retailer/v1';

    function __construct()
    {
        add_action('rest_api_init', array($this, 'registerEndpointsForSyncee'));
    }

    function registerEndpointsForSyncee()
    {
        $routes = [
            ['startInstallFlow',          'POST', [$this, 'permission_admin']],
            ['callbackFromWoocommerce',   'POST', $this->require_state('woocommerce_auth')],
            ['saveAccessTokenFromSyncee', 'POST', $this->require_state('syncee_install')],
            ['saveTokenFromSyncee',       'POST', $this->require_state('syncee_install')],
            ['uninstallEcom',             'POST', [$this, 'permission_admin']],
            ['getRequirements',           'GET',  [$this, 'permission_admin']],
            ['getDataForFrontend',        'GET',  [$this, 'permission_admin']],
        ];

        foreach ($routes as $route) {
            list($endpoint, $method, $permission_callback) = $route;
            register_rest_route(
                $this->namespace,
                $endpoint,
                [
                    'methods' => $method,
                    'callback' => [$this, $endpoint],
                    'permission_callback' => $permission_callback,
                ]
            );
        }
    }

    /**
     * Public on purpose: WP's REST router invokes the callable from outside
     * this class scope, so PHP visibility rules require this be public.
     */
    public function permission_admin()
    {
        return current_user_can('manage_options');
    }

    /**
     * Returns a closure usable as a REST permission_callback. The closure
     * checks that a valid, unexpired, stage-matching state token was provided
     * AND atomically consumes (deletes) it. Doing the delete here (rather than
     * in the endpoint body) narrows the race window for replay attacks — two
     * concurrent requests with the same token can no longer both pass the
     * permission check.
     */
    private function require_state($required_stage)
    {
        return function (WP_REST_Request $request) use ($required_stage) {
            $state = $request->get_param('state');
            if (!is_string($state) || strlen($state) < 32) {
                return false;
            }
            $key = 'syncee_state_' . hash('sha256', $state);
            $stored_stage = get_transient($key);
            if ($stored_stage !== $required_stage) {
                return false;
            }
            delete_transient($key);
            return true;
        };
    }

    public function startInstallFlow(WP_REST_Request $request)
    {
        $stage = $request->get_param('stage');
        if (!in_array($stage, ['woocommerce_auth', 'syncee_install'], true)) {
            wp_send_json_error(['code' => 'invalid_stage'], 400);
        }

        $state = wp_generate_password(64, false);
        set_transient('syncee_state_' . hash('sha256', $state), $stage, 15 * MINUTE_IN_SECONDS);

        wp_send_json_success(['state' => $state]);
    }

    public function callbackFromWoocommerce(WP_REST_Request $request)
    {
        $postData = $request->get_json_params();
        if (!is_array($postData)) {
            // Fall back to manual decode for callers that don't set Content-Type: application/json.
            $postData = json_decode($request->get_body(), true);
        }
        if (!is_array($postData)) {
            wp_send_json_error(['code' => 'invalid_body'], 400);
        }

        $consumer_key    = isset($postData['consumer_key'])    ? sanitize_text_field($postData['consumer_key'])    : '';
        $consumer_secret = isset($postData['consumer_secret']) ? sanitize_text_field($postData['consumer_secret']) : '';
        $key_permissions = isset($postData['key_permissions']) ? sanitize_text_field($postData['key_permissions']) : '';
        $user_id         = isset($postData['user_id'])         ? sanitize_text_field($postData['user_id'])         : '';

        if ($consumer_key === '' || $consumer_secret === '' || $key_permissions === '') {
            wp_send_json_error(['code' => 'missing_fields'], 400);
        }

        update_option('data_to_syncee_installer', [
            'domain'         => get_option('siteurl'),
            'currency'       => get_option('woocommerce_currency'),
            'weightUnit'     => get_option('woocommerce_weight_unit'),
            'consumerKey'    => $consumer_key,
            'consumerSecret' => $consumer_secret,
            'keyPermissions' => $key_permissions,
            'userId'         => $user_id,
        ]);

        wp_send_json_success();
    }

    public function getRequirements(WP_REST_Request $request)
    {
        require_once __DIR__ . '/RequirementsForSyncee.php';
        wp_send_json_success(RequirementsForSyncee::getRequirementsStatusForSyncee());
    }

    public function uninstallEcom(WP_REST_Request $request)
    {
        $response = wp_safe_remote_post(
            SYNCEE_INSTALLER_URL . '/api/woocommerce_auth/uninstall',
            [
                'body' => [
                    'domain'            => get_option('siteurl'),
                    'access_token'      => get_option('syncee_access_token'),
                    'syncee_user_token' => get_option('syncee_user_token'),
                ],
            ]
        );

        if (is_wp_error($response)) {
            wp_send_json_error(['code' => 'installer_unreachable', 'message' => $response->get_error_message()], 502);
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            wp_send_json_error(
                ['code' => 'installer_error', 'message' => wp_remote_retrieve_response_message($response)],
                $code ?: 500
            );
        }

        delete_option('syncee_access_token');
        delete_option('syncee_user_token');
        wp_send_json_success(['message' => 'Successfully uninstalled store in Syncee.']);
    }

    public function saveAccessTokenFromSyncee(WP_REST_Request $request)
    {
        $accessToken = sanitize_text_field((string) $request->get_param('accessToken'));
        if ($accessToken === '') {
            wp_send_json_error(['code' => 'missing_token'], 400);
        }
        update_option('syncee_access_token', $accessToken);

        wp_send_json_success();
    }

    public function saveTokenFromSyncee(WP_REST_Request $request)
    {
        $accessToken = sanitize_text_field((string) $request->get_param('token'));
        if ($accessToken === '') {
            wp_send_json_error(['code' => 'missing_token'], 400);
        }
        update_option('syncee_user_token', $accessToken);

        wp_send_json_success();
    }

    public function getDataForFrontend(WP_REST_Request $request)
    {
        wp_send_json_success([
            'rest_url'                 => esc_url_raw(get_option('siteurl') . SYNCEE_RETAILER_REST_PATH),
            'site_url'                 => esc_url_raw(get_option('siteurl')),
            'syncee_access_token'      => get_option('syncee_access_token', false),
            'syncee_user_token'        => get_option('syncee_user_token', false),
            'data_to_syncee_installer' => get_option('data_to_syncee_installer', false),
        ]);
    }
}

new RestForSyncee();
