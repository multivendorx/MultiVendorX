<?php
/**
 * Integration tests for the core subscribe/unsubscribe/stock-check business logic.
 *
 * @package Notifima
 */

use Notifima\Subscriber;

/**
 * Class Test_Subscriber
 */
class Test_Subscriber extends WP_UnitTestCase {

	/**
	 * Create a simple, manage-stock, out-of-stock WooCommerce product.
	 *
	 * @param array $overrides Product prop overrides.
	 * @return WC_Product_Simple
	 */
	private function create_out_of_stock_product( $overrides = array() ) {
		$product = new WC_Product_Simple();
		$product->set_name( 'Notifima Test Product' );
		$product->set_regular_price( '10.00' );
		$product->set_manage_stock( true );
		$product->set_stock_quantity( 0 );
		$product->set_stock_status( 'outofstock' );
		foreach ( $overrides as $prop => $value ) {
			$product->{"set_{$prop}"}( $value );
		}
		$product->save();

		return $product;
	}

	/**
	 * insert_subscriber() should create a row, and is_already_subscribed() should then find it.
	 */
	public function test_insert_subscriber_creates_a_subscribed_row() {
		$product = $this->create_out_of_stock_product();

		$result = Subscriber::insert_subscriber( 'shopper@example.com', $product->get_id() );

		$this->assertNotFalse( $result );
		$this->assertNotFalse( Subscriber::is_already_subscribed( 'shopper@example.com', $product->get_id() ) );
	}

	/**
	 * Subscribing the same email to the same product twice must not create a duplicate row -
	 * the table's UNIQUE KEY (product_id, email, status) plus the ON DUPLICATE KEY UPDATE clause
	 * in insert_subscriber() should upsert instead.
	 */
	public function test_insert_subscriber_is_idempotent_for_the_same_email_and_product() {
		global $wpdb;
		$product = $this->create_out_of_stock_product();

		Subscriber::insert_subscriber( 'shopper@example.com', $product->get_id() );
		Subscriber::insert_subscriber( 'shopper@example.com', $product->get_id() );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$row_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}notifima_subscribers WHERE product_id = %d AND email = %s",
				$product->get_id(),
				'shopper@example.com'
			)
		);

		$this->assertSame( '1', $row_count );
	}

	/**
	 * remove_subscriber() should flip the row's status to unsubscribed rather than deleting it,
	 * and is_already_subscribed() (which only matches status = 'subscribed') should stop finding it.
	 */
	public function test_remove_subscriber_unsubscribes_an_existing_subscription() {
		$product = $this->create_out_of_stock_product();
		Subscriber::insert_subscriber( 'shopper@example.com', $product->get_id() );

		$removed = Subscriber::remove_subscriber( $product->get_id(), 'shopper@example.com' );

		$this->assertTrue( $removed );
		// is_already_subscribed() only matches status = 'subscribed' rows via $wpdb->get_var(),
		// which returns null (not false) when nothing matches - see its docblock.
		$this->assertNull( Subscriber::is_already_subscribed( 'shopper@example.com', $product->get_id() ) );
	}

	/**
	 * remove_subscriber() for an email that was never subscribed should report failure, not
	 * silently succeed.
	 */
	public function test_remove_subscriber_returns_false_when_not_subscribed() {
		$product = $this->create_out_of_stock_product();

		$this->assertFalse( Subscriber::remove_subscriber( $product->get_id(), 'never-subscribed@example.com' ) );
	}

	/**
	 * update_product_subscriber_count() should reflect only rows with status = 'subscribed'.
	 */
	public function test_update_product_subscriber_count_counts_only_subscribed_rows() {
		$product = $this->create_out_of_stock_product();

		Subscriber::insert_subscriber( 'one@example.com', $product->get_id() );
		Subscriber::insert_subscriber( 'two@example.com', $product->get_id() );
		Subscriber::remove_subscriber( $product->get_id(), 'two@example.com' );

		Subscriber::update_product_subscriber_count( $product->get_id() );

		$this->assertSame( '1', get_post_meta( $product->get_id(), 'no_of_subscribers', true ) );
	}

	/**
	 * is_product_outofstock(): a manage-stock product with quantity above the low-stock
	 * threshold and status 'instock' should not be considered out of stock.
	 */
	public function test_is_product_outofstock_false_for_a_well_stocked_product() {
		$product = new WC_Product_Simple();
		$product->set_manage_stock( true );
		$product->set_stock_quantity( 25 );
		$product->set_stock_status( 'instock' );
		$product->save();

		$this->assertFalse( Subscriber::is_product_outofstock( $product ) );
	}

	/**
	 * is_product_outofstock(): quantity at/below zero should always be out of stock.
	 */
	public function test_is_product_outofstock_true_when_quantity_is_zero() {
		$product = $this->create_out_of_stock_product();

		$this->assertTrue( Subscriber::is_product_outofstock( $product ) );
	}

	/**
	 * is_product_outofstock() must work directly on a WC_Product_Variation instance without
	 * needing to re-fetch it - this pins down the fix that removed a redundant
	 * `new WC_Product_Variation()` re-instantiation inside is_product_outofstock().
	 */
	public function test_is_product_outofstock_works_on_a_real_variation_instance() {
		$parent = new WC_Product_Variable();
		$parent->set_name( 'Notifima Variable Test Product' );
		$parent->save();

		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $parent->get_id() );
		$variation->set_regular_price( '15.00' );
		$variation->set_manage_stock( true );
		$variation->set_stock_quantity( 0 );
		$variation->set_stock_status( 'outofstock' );
		$variation->save();

		// Re-fetch through wc_get_product(), exactly as production code does, so we're testing
		// the same object type WooCommerce actually hands the plugin at runtime.
		$fetched_variation = wc_get_product( $variation->get_id() );

		$this->assertInstanceOf( WC_Product_Variation::class, $fetched_variation );
		$this->assertTrue( Subscriber::is_product_outofstock( $fetched_variation ) );
	}

	/**
	 * get_related_product(): a simple product should resolve to just itself.
	 */
	public function test_get_related_product_for_a_simple_product_returns_only_itself() {
		$product = $this->create_out_of_stock_product();

		$this->assertSame( array( $product->get_id() ), Subscriber::get_related_product( $product ) );
	}

	/**
	 * get_related_product(): a variable product with children should resolve to all its
	 * variation IDs, not the parent ID.
	 */
	public function test_get_related_product_for_a_variable_product_returns_its_variations() {
		$parent = new WC_Product_Variable();
		$parent->set_name( 'Notifima Variable Test Product' );
		$parent->save();

		$variation_one = new WC_Product_Variation();
		$variation_one->set_parent_id( $parent->get_id() );
		$variation_one->set_regular_price( '15.00' );
		$variation_one->save();

		$variation_two = new WC_Product_Variation();
		$variation_two->set_parent_id( $parent->get_id() );
		$variation_two->set_regular_price( '18.00' );
		$variation_two->save();

		$parent->set_children( array( $variation_one->get_id(), $variation_two->get_id() ) );
		$parent->save();

		$related_ids = Subscriber::get_related_product( wc_get_product( $parent->get_id() ) );

		sort( $related_ids );
		$expected = array( $variation_one->get_id(), $variation_two->get_id() );
		sort( $expected );

		$this->assertSame( $expected, $related_ids );
	}
}
