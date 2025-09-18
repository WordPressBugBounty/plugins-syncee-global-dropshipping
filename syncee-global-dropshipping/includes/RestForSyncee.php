<?php


class RestForSyncee
{
    private $namespace = 'syncee/retailer/v1';
    private $endpoints =
        [
            'POST' => [
                'callbackFromWoocommerce' => false,
                'saveAccessTokenFromSyncee' => false,
                'saveTokenFromSyncee' => false,
                'uninstallEcom' => false,
            ],
            'GET' => [
                'getCallbackData' => false,
                'getDataForFrontend' => true,
                'getRequirements' => false,
                'getShopData' => false,
            ],
        ];

    function __construct()
    {
        add_action('rest_api_init', array($this, 'registerEndpointsForSyncee'));
    }


    function registerEndpointsForSyncee()
    {
        foreach ($this->endpoints as $requestType => $endpoints) {
            foreach ($endpoints as $rest => $protected)
                register_rest_route(
                    $this->namespace,
                    $rest,
                    [
                        'methods' => $requestType,
                        'callback' => array($this, $rest),
                        'permission_callback' => $protected ? function () {
                            return current_user_can('edit_others_posts');
                        } : '__return_true',
                    ]
                );
        }
    }

    function callbackFromWoocommerce($request)
    {
        $postData = $request->get_body();
        if (!is_array($request->get_body()))
            $postData = json_decode($postData);

        $consumer_key = sanitize_text_field($postData->consumer_key);
        $consumer_secret = sanitize_text_field($postData->consumer_secret);
        $key_permissions = sanitize_text_field($postData->key_permissions);
        $user_id = sanitize_text_field($postData->user_id);

        if (is_string($consumer_key) && is_string($consumer_secret) && is_string($key_permissions)) {
            $registerData = [
                'domain' => get_option('siteurl'),
                'currency' => get_option('woocommerce_currency'),
                'weightUnit' => get_option('woocommerce_weight_unit'),
                'consumerKey' => $consumer_key,
                'consumerSecret' => $consumer_secret,
                'keyPermissions' => $key_permissions,
                'userId' => $user_id
            ];

            update_option('data_to_syncee_installer', $registerData);
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }

    }


    function getCallbackData()
    {
        $dataToInstaller = esc_html(get_option('data_to_syncee_installer'));
        wp_send_json_success($dataToInstaller);
    }

    function getShopData()
    {
        $shopData = esc_sql([
            'domain' => get_option('siteurl'),
            'currency' => get_option('woocommerce_currency'),
            'weightUnit' => get_option('woocommerce_weight_unit')
        ]);
        wp_send_json_success($shopData);
    }

    function getRequirements()
    {
        include 'RequirementsForSyncee.php';
        $requirementsStatus = RequirementsForSyncee::getRequirementsStatusForSyncee();
        $rStatus = esc_sql($requirementsStatus);
        wp_send_json_success($rStatus);
    }


    function uninstallEcom()
    {
        $uninstallUrl = SYNCEE_INSTALLER_URL . '/api/woocommerce_auth/uninstall';
        $uninstallData = [
            'domain' => get_option('siteurl'),
            'access_token' => get_option('syncee_access_token'),
            'syncee_user_token' => get_option('syncee_user_token'),
        ];
        $response = wp_safe_remote_post(
            $uninstallUrl,
            array(
                'headers' => array(),
                'body' => $uninstallData
            )
        );

        if ($response['response']['code'] === 200) {
            delete_option('syncee_access_token');
            delete_option('syncee_user_token');
            wp_send_json_success('Successfully uninstalled store in Syncee!', 200);
        } else {
            wp_send_json_error(esc_html($response['response']['message']), esc_html($response['response']['code']));
        }
    }

    function saveAccessTokenFromSyncee()
    {
        $accessToken = sanitize_text_field($_POST['accessToken']);
        update_option('syncee_access_token', $accessToken);
        wp_send_json_success(esc_html(get_option('syncee_access_token')));
    }


    function saveTokenFromSyncee()
    {
        $accessToken = sanitize_text_field($_POST['token']);
        update_option('syncee_user_token', $accessToken);
        wp_send_json_success(esc_html(get_option('syncee_user_token')));
    }


    function getDataForFrontend()
    {
        $response = [
            'rest_url' => get_option('siteurl') . SYNCEE_RETAILER_REST_PATH,
            'site_url' => get_option('siteurl'),
            'syncee_access_token' => get_option('syncee_access_token', false),
            'syncee_user_token' => get_option('syncee_user_token', false),
            'data_to_syncee_installer' => get_option('data_to_syncee_installer', false),
        ];

        wp_send_json_success(esc_sql($response));

    }
}

new RestForSyncee();



