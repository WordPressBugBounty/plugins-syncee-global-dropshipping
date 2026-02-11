<?php

/**
 * Plugin Name: Syncee Premium Dropshipping & Wholesale
 * Description: Find dropshipping and wholesale products from trusted US/CA/EU/AU suppliers, import them to [your WooCommerce store](https://syncee.com/woocommerce/).
 * Version: 1.0.23
 * Author: Syncee
 * Author URI: https://syncee.com
 *
 * @package Syncee
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define constants.
define( 'SYNCEE_PLUGIN_VERSION', '1.0.25' );

//DEMO
//define( 'SYNCEE_URL', 'https://demo.v5.syncee.io' );
//define( 'SYNCEE_INSTALLER_URL', 'https://installer.v5.syncee.io' );

//STAGE
define( 'SYNCEE_URL', 'https://syncee.com' );
define( 'SYNCEE_INSTALLER_URL', 'https://installer.syncee.com' );


define( 'SYNCEE_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'SYNCEE_RETAILER_REST_PATH', '/wp-json/syncee/retailer/v1/' );


include 'Syncee.php';
