<?php
/**
 * Plugin Name: Notifima
 * Plugin URI: https://notifima.com/?utm_source=wpadmin&utm_medium=pluginsettings&utm_campaign=notifima
 * Description: Boost sales with real-time stock alerts! Notify customers instantly when products are back in stock. Simplify data management by exporting and importing stock data with ease.
 * Author: MultiVendorX
 * Version: 3.1.6
 * Requires at least: 6.4.0
 * Tested up to: 7.1.0
 * WC requires at least: 8.2.2
 * WC tested up to: 11.0.1
 * Author URI: https://multivendorx.com/?utm_source=wpadmin&utm_medium=pluginsettings&utm_campaign=notifima
 * Text Domain: notifima
 * Requires Plugins: woocommerce
 * Domain Path: /languages/
 *
 * @package Notifima
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Returns the main instance of the Notifima plugin.
 *
 * @return \Notifima\Notifima
 */
function Notifima() {
    return \Notifima\Notifima::init( __FILE__ );
}

Notifima();
