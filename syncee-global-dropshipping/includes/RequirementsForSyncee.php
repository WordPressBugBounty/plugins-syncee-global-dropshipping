<?php


/**
 * Handles requirements checks
 */
class RequirementsForSyncee
{

    public static $x = 10;

    /**
     * Checks if cURL is activated on php installation.
     *
     * @return array cURL requirements check results
     */
    public static function checkCurlForSyncee()
    {
        if (!in_array('curl', get_loaded_extensions())) {
            return array(
                'title' => __('PHP cURL', 'syncee'),
                'pass' => false,
                'reason' => __('PHP cURL seems to be disabled or not installed on your server.', 'syncee'),
                'solution' => __('Please activate cURL to use the syncee plugin. <br>')
            );
        }

        return array(
            'title' => __('PHP cURL', 'syncee'),
            'pass' => true,
        );
    }

    /**
     * Checks if the connection is SSL.
     *
     * @return array SSL requirements check results
     */
    public static function checkSSLConnectionForSyncee()
    {
        if (!getenv('IS_DEV') && (!isset($_SERVER['HTTPS']) || empty($_SERVER['HTTPS']) || 'on' !== strtolower($_SERVER['HTTPS']))) {
            return array(
                'title' => __('SSL Connection', 'syncee'),
                'pass' => false,
                'reason' => __('You are not using a SSL connection', 'syncee'),
                'solution' => __(
                    'Please set up a HTTPS certificate to use syncee.<br>'
                ),
            );
        }

        return array(
            'title' => __('SSL Connection', 'syncee'),
            'pass' => true,
        );
    }

    /**
     * Checks Store name.
     * checks if the store has a name to use on syncee.
     *
     * @return array Store name requirements check results
     */
    public static function getStoreNameRequirementsStatusForSyncee()
    {

        if (get_option('blogname') === '') {
            return array(
                'title' => __('Store name', 'syncee'),
                'pass' => false,
                'reason' => __('Seems like your store does not have a name.', 'syncee'),
                'solution' => __(
                    'Please set a name for your store. <br> You can set one in <b>Settings > General > Site Title<b>',
                    'syncee'
                ),
            );
        }

        return array(
            'title' => __('Store name', 'syncee'),
            'pass' => true,
        );
    }

    /**
     * Checks Current User requirements.
     *
     * @return array Current User requirements check results
     */
    public static function getUserRequirementsStatusForSyncee()
    {
        $currentUser = wp_get_current_user();
        if ($currentUser->get('user_email') === '') {
            return array(
                'title' => __('Current User', 'syncee'),
                'pass' => false,
                'reason' => __('Your user does not have an e-mail account.', 'syncee'),
                'solution' => __(
                    'Please you need to assign an e-mail to your user.',
                    'syncee'
                ),
            );
        }

        return array(
            'title' => __('Current User', 'syncee'),
            'pass' => true,
        );
    }

    /**
     * Checks WooCommerce requirements.
     *
     * @return array WooCommerce requirements check results
     */
    public static function getWooCommerceRequirementsStatusForSyncee()
    {
        if (!function_exists('wc')) {
            return array(
                'title' => __('WooCommerce', 'syncee'),
                'pass' => false,
                'reason' => __('WooCommerce plugin is not activated', 'syncee'),
                'solution' => __(
                    'Please install and activate the WooCommerce plugin',
                    'syncee'
                ),
            );
        }

        if (!version_compare(wc()->version, '2.6', '>=')) {
            return array(
                'title' => __('WooCommerce', 'syncee'),
                'pass' => false,
                'reason' => __(
                    'WooCommerce plugin is out of date, version >= 2.6 is required',
                    'syncee'
                ),
                'solution' => __(
                    'Please update your WooCommerce plugin',
                    'syncee'
                ),
            );
        }

        return array(
            'title' => __('WooCommerce', 'syncee'),
            'pass' => true,
        );
    }

    /**
     * Checks WordPress requirements.
     *
     * @return array WordPress requirements check results
     */
    public static function getWordPressRequirementsStatusForSyncee()
    {
        if (!version_compare(get_bloginfo('version'), '4.4', '>=')) {
            return array(
                'title' => __('WordPress', 'syncee'),
                'pass' => false,
                'reason' => __(
                    'WordPress is out of date, version >= 4.4 is required',
                    'syncee'
                ),
                'solution' => __(
                    'Please update your WordPress installation',
                    'syncee'
                ),
            );
        }

        return array(
            'title' => __('WordPress', 'syncee'),
            'pass' => true,
        );
    }

    /**
     * Checks permalinks requirements.
     *
     * @return array Permalinks requirements check results
     */
    public static function getPermalinksRequirementsStatusForSyncee()
    {
        if (get_option('permalink_structure', '') === '') {
            return array(
                'title' => __('Permalinks', 'syncee'),
                'pass' => false,
                'reason' => __(
                    'Permalinks set to "Plain"',
                    'syncee'
                ),
                'solution' => __(
                    'Set permalinks to anything other than "Plain" in <b>Settings > Permalinks</b>',
                    'syncee'
                ),
            );
        }

        return array(
            'title' => __('Permalinks', 'syncee'),
            'pass' => true,
        );
    }

    /**
     * Retrieves collective requirements status.
     *
     * @return array Requirements check results
     */
    public static function getRequirementsStatusForSyncee()
    {
        $requirementsStatus = array(
            'woocommerce' => self::getWooCommerceRequirementsStatusForSyncee(),
            'wordpress' => self::getWordPressRequirementsStatusForSyncee(),
            'permalinks' => self::getPermalinksRequirementsStatusForSyncee(),
            'user' => self::getUserRequirementsStatusForSyncee(),
            'store_name' => self::getStoreNameRequirementsStatusForSyncee(),
            'check_ssl' => self::checkSSLConnectionForSyncee(),
            'check_curl' => self::checkCurlForSyncee(),
        );
        return $requirementsStatus;
    }

//    private static function getApiURL() {
//        $plugin = new \syncee\Plugin();
//        return $plugin->getApiURL();
//    }
}
