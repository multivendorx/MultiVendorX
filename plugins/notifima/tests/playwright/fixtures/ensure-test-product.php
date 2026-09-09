<?php
/**
 * Idempotently ensure a fixed-slug, out-of-stock WooCommerce product exists for the Playwright
 * suite to subscribe to. Run inside the wp-env "cli" container via `wp eval-file` (see
 * global-setup.ts) - this file is never loaded by the plugin itself.
 *
 * @package Notifima
 */

$slug = 'notifima-e2e-out-of-stock-product';

$existing = get_page_by_path( $slug, OBJECT, 'product' );

if ( $existing ) {
	$product = wc_get_product( $existing->ID );
} else {
	$product = new WC_Product_Simple();
	$product->set_name( 'Notifima E2E Out Of Stock Product' );
	$product->set_slug( $slug );
	$product->set_regular_price( '25.00' );
	$product->set_status( 'publish' );
}

$product->set_manage_stock( true );
$product->set_stock_quantity( 0 );
$product->set_stock_status( 'outofstock' );
$product->save();

// Every subscriber row from a previous run would make this product look "already subscribed"
// to the logged-in admin user the suite logs in as - clear it so each run starts clean.
global $wpdb;
$wpdb->delete( $wpdb->prefix . 'notifima_subscribers', array( 'product_id' => $product->get_id() ) );
delete_post_meta( $product->get_id(), 'no_of_subscribers' );

WP_CLI::log( 'PRODUCT_ID=' . $product->get_id() );
WP_CLI::log( 'PRODUCT_SLUG=' . $slug );
