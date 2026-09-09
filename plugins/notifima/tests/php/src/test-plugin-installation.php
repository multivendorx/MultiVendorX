<?php
/**
 * Sanity checks that the plugin bootstraps and installs correctly in the test environment.
 *
 * @package Notifima
 */

/**
 * Class Test_Plugin_Installation
 */
class Test_Plugin_Installation extends WP_UnitTestCase {

	/**
	 * The plugin's main accessor function should return the singleton instance.
	 */
	public function test_main_instance_is_available() {
		$this->assertInstanceOf( \Notifima\Notifima::class, Notifima() );
	}

	/**
	 * Activation should create the custom subscribers table.
	 */
	public function test_subscribers_table_exists() {
		global $wpdb;

		$table = $wpdb->prefix . 'notifima_subscribers';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

		$this->assertSame( $table, $found );
	}

	/**
	 * WooCommerce should have been loaded alongside the plugin.
	 */
	public function test_woocommerce_is_loaded() {
		$this->assertTrue( class_exists( 'WooCommerce' ) );
		$this->assertTrue( function_exists( 'wc_get_product' ) );
	}
}
