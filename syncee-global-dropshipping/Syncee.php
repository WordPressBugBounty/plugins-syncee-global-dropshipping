<?php

class Syncee
{

    private static $instance = null;
    private $plugin_path;
    private $plugin_url;

    /**
     * Creates or returns an instance of this class.
     */
    public static function get_instance()
    {
        // If an instance hasn't been created and set to $instance create an instance and set it to $instance.
        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Initializes the plugin by setting localization, hooks, filters, and administrative functions.
     */
    private function __construct()
    {
        $this->plugin_path = plugin_dir_path(__FILE__);
        $this->plugin_url = plugin_dir_url(__FILE__);

        $this->init_rests();

        add_action('admin_enqueue_scripts', array($this, 'register_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'register_styles'));

        $this->run_plugin();
    }

    public function get_plugin_url()
    {
        return $this->plugin_url;
    }

    public function get_plugin_path()
    {
        return $this->plugin_path;
    }

    /**
     * Enqueue and register JavaScript files here.
     */
    public function register_scripts($hook_suffix = '')
    {
        if (!is_admin()) {
            return;
        }
        if (!current_user_can('manage_options')) {
            return;
        }
        if ('toplevel_page_syncee' !== $hook_suffix) {
            return;
        }

        wp_enqueue_script(
            'syncee-frontend-js',
            plugins_url('/JS/index.js', __FILE__),
            ['jquery'],
            SYNCEE_PLUGIN_VERSION,
            true
        );

        wp_enqueue_script(
            'syncee-frontend-js-sweetalert',
            plugins_url('/JS/sweetalert.js', __FILE__),
            [],
            SYNCEE_PLUGIN_VERSION
        );

        // SECURITY: sensitive options (syncee_access_token, syncee_user_token,
        // data_to_syncee_installer) MUST NOT be localized here. JS reads them
        // from the capability-gated getDataForFrontend REST endpoint.
        wp_localize_script(
            'syncee-frontend-js',
            'syncee_globals',
            [
                'rest_url' => esc_url_raw(get_option('siteurl') . SYNCEE_RETAILER_REST_PATH),
                'site_url' => esc_url_raw(get_option('siteurl')),
                'syncee_url' => SYNCEE_URL,
                'syncee_installer_url' => SYNCEE_INSTALLER_URL,
                'img_dir_url' => plugins_url('/img/', __FILE__),
                'syncee_retailer_nonce' => wp_create_nonce('wp_rest'),
            ]
        );
    }

    /**
     * Enqueue and register CSS files here.
     */
    public function register_styles($hook_suffix = '')
    {
        if (!is_admin()) {
            return;
        }
        if (!current_user_can('manage_options')) {
            return;
        }
        if ('toplevel_page_syncee' !== $hook_suffix) {
            return;
        }

        wp_enqueue_style('syncee-admin-css', plugins_url('/View/index.css', __FILE__), [], SYNCEE_PLUGIN_VERSION);
    }

    public function displayInterface()
    {
        echo '<div class="wrap js-syncee-admin-interface">';
        esc_html_e('Loading, please wait...', 'syncee');
        echo '</div>';
    }

    function registerMenu()
    {
        $icon = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyBpZD0iTGF5ZXJfMSIgZGF0YS1uYW1lPSJMYXllciAxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMjQyIDIwNzAuNyI+CiAgPGRlZnM+CiAgICA8c3R5bGU+CiAgICAgIC5jbHMtMSB7CiAgICAgICAgZmlsbDogIzI4NmRmODsKICAgICAgfQogICAgPC9zdHlsZT4KICAKICAKICAKICAGPC9zdHlsZT4KICAKICAKICAGPC9kZWZzPgogIDxwYXRoIGNsYXNzPSJjbHMtMSIgZD0ibTIyNDEuNywxNjU3LjNMMjAwNi41LDBIMjM1LjVMLjMsMTY1Ny4zYy0uMiwxLjItLjMsMi41LS4zLDMuNywxLDE4NS44LDE0NC4xLDQwOS43LDMyOS42LDQwOS43aDE1ODIuOGMxODUuNSwwLDMyOC42LTIyMy45LDMyOS42LTQwOS43LDAtMS4zLDAtMi41LS4zLTMuN1o1NDUuNiw4NTIuNGMzNS4zLTE0MC4yLDExMi4zLTI1My41LDIzMy40LTMzMiwxOTkuNy0xMjkuNiw0MDcuMS0xMzQuNSw2MTMuNy0xOC4xLDU3LjcsMzIuNSwxMDYuMSw4MS44LDE2NCwxMjcuNSwzNy4yLTI4LjMsMTMxLjYtMTAxLjksMTMxLjYtMTAxLjksMCwwLDcuMywyODYuOCwxMS41LDQzMC42LTEzOC44LTM5LjUtMjY5LjMtNzYuNi00MTEuMS0xMTYuOSw1MC40LTQwLjMsOTEuMy03My4xLDEzNC44LTEwNy45LTU5LjYtNzQuOC0xMzUuOS0xMjIuNy0yMjgtMTQyLjctMjI1LjgtNDguOS00MjkuNSw3My40LTQ5NC42LDI5NC40LTE5LjYsNjYuNS03NS42LDkxLjUtMTI3LjEsNTcuMS0zMy44LTIyLjYtMzYuNi01Ni4zLTI4LjEtOTAuMVptMTE1MS4yLDM1My45Yy01MS4zLDIxOC4yLTI0OC41LDM5My4xLTQ4MS40LDQyMS44LTE5Mi45LDIzLjgtMzU3LjQtMzcuOC00OTIuOS0xNzYuOS05LTkuMy0xNy40LTE5LjItMjktMzIuMi00NC45LDM0LjEtMTM3LjcsMTA0LjQtMTM3LjcsMTA0LjQsMCwwLTguNi0yODMuNy0xMi45LTQyNy40LDEzOC44LDM5LjUsMjY5LjMsNzYuNiw0MTEuMiwxMTYuOS01MC42LDQwLjQtOTEuNSw3My4xLTEzNS40LDEwOC4yLDc4LjQsOTMuNCwxNzcuMSwxNDcuOCwyOTcuOSwxNTIsMjEyLjksNy40LDM2My44LTEwNC4zLDQyNi0zMDgsMTUuNi01MS4yLDU4LjMtNzguNSwxMDIuNC02NS41LDQ0LjUsMTMsNjQuMyw1My44LDUxLjgsMTA2LjdaIi8+Cjwvc3ZnPg==';

        add_menu_page(
            "Syncee",
            "Syncee",
            "manage_options",
            "syncee",
            array($this, "synceeMenu"),
            $icon,
            '55.5'
        );
    }

    function synceeMenu()
    {
        $img_url = plugins_url('/img/', SYNCEE_PLUGIN_DIR . '/plugin.php');
        include __DIR__ . '/View/index.php';
    }

    function okToActivateSynceePlugin()
    {
        return in_array('woocommerce/woocommerce.php', get_option('active_plugins'));
    }

    function uninstallSynceePlugin()
    {
        if (in_array('Syncee/plugin.php', get_option('active_plugins')))
            deactivate_plugins(['Syncee/plugin.php']);
        if (in_array('syncee-global-dropshipping', get_option('active_plugins')))
            deactivate_plugins(['syncee-global-dropshipping/plugin.php']);

    }

    function sendErrorMessage($title, $desc)
    {
        echo '<div class="error"><p>' . esc_html($title) . '</p><p><b>Error: </b>' . esc_html($desc) . '</p></div>';
    }

    function removePluginActivatedMessage()
    {
        unset($_GET['activate'], $_GET['error']);
    }

    /**
     * Place code for your plugin's functionality here.
     */
    private function run_plugin()
    {
//        if ($this->okToActivateSynceePlugin()) {
            add_action('admin_menu', array($this, 'registerMenu'));
//        } else {
//            $this->uninstallSynceePlugin();
//            $this->removePluginActivatedMessage();
//            $this->sendErrorMessage('Syncee plugin', 'WooCoommerce needs to be installed and activated before you can activate Syncee plugin.');
//        }
    }

    private function init_rests()
    {
        include_once SYNCEE_PLUGIN_DIR . '/includes/RestForSyncee.php';
    }
}

Syncee::get_instance();
