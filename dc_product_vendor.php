<?php
/**
 * Plugin Name: MultiVendorX
 * Plugin URI: https://multivendorx.com/
 * Description: A Free Extension That Transforms Your WooCommerce Site into a Marketplace.
 * Author: MultiVendorX
 * Version: 4.1.1
 * Author URI: https://multivendorx.com/
 * Requires at least: 5.4
 * Tested up to: 6.4.2
 * WC requires at least: 8.2.2
 * WC tested up to: 8.5.1
 *
 * Text Domain: multivendorx
 * Domain Path: /languages/
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

function MVX() {
    return \MultiVendorX\MultiVendorX::init(__FILE__);
}

MVX();