<?php
/**
 * Plugin Name: MultiVendorX
 * Plugin URI: https://multivendorx.com/?utm_source=wpadmin&utm_medium=pluginsettings&utm_campaign=multivendorx
 * Description: An AI-powered WooCommerce multivendor marketplace solution to build, manage, and scale your platform.
 * Author: MultiVendorX
 * Version: 5.0.16
 * Author URI: https://multivendorx.com/?utm_source=wpadmin&utm_medium=pluginsettings&utm_campaign=multivendorx
 * Requires at least: 6.4.0
 * Tested up to: 7.1.0
 * WC requires at least: 8.2.0
 * WC tested up to: 11.1.0
 *
 * Text Domain: multivendorx
 * Requires Plugins: woocommerce
 * Domain Path: /languages/
 *
 * @package MultiVendorX
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Main MultiVendorX Class
 */
function MultiVendorX() {
    return \MultiVendorX\MultiVendorX::init( __FILE__ );
}

MultiVendorX();
