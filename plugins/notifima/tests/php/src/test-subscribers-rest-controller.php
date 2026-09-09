<?php
/**
 * Integration tests for the /notifima/v1/subscribers REST endpoint.
 *
 * @package Notifima
 */

/**
 * Class Test_Subscribers_Rest_Controller
 */
class Test_Subscribers_Rest_Controller extends WP_UnitTestCase {

	/**
	 * The REST server instance used to dispatch requests directly (no HTTP round trip).
	 *
	 * @var WP_REST_Server
	 */
	protected $server;

	/**
	 * Boot a fresh REST server and re-register routes before every test.
	 */
	public function setUp(): void {
		parent::setUp();

		global $wp_rest_server;
		$wp_rest_server = new WP_REST_Server();
		$this->server   = $wp_rest_server;
		do_action( 'rest_api_init', $this->server );
	}

	/**
	 * Create a simple, out-of-stock WooCommerce product.
	 *
	 * @return WC_Product_Simple
	 */
	private function create_out_of_stock_product() {
		$product = new WC_Product_Simple();
		$product->set_name( 'REST Test Product' );
		$product->set_regular_price( '9.99' );
		$product->set_manage_stock( true );
		$product->set_stock_quantity( 0 );
		$product->set_stock_status( 'outofstock' );
		$product->save();

		return $product;
	}

	/**
	 * Build a subscribers "update" request (POST /subscribers/{id}) with a valid REST nonce.
	 *
	 * @param array $params Body params (action, customer_email, product_id, ...).
	 * @return WP_REST_Request
	 */
	private function build_update_request( $params ) {
		$request = new WP_REST_Request( 'POST', '/notifima/v1/subscribers/0' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		foreach ( $params as $key => $value ) {
			$request->set_param( $key, $value );
		}
		return $request;
	}

	/**
	 * Switch the current user, keeping Notifima()->current_user(_id) in sync.
	 *
	 * Notifima's container caches get_current_user_id()/wp_get_current_user() once, at plugin
	 * boot (see Notifima::init_classes()) - correct for a real request, since WordPress boots a
	 * fresh PHP process per request and current-user resolution happens before that point. A
	 * PHPUnit run boots the plugin once for the whole suite though, so switching users with
	 * wp_set_current_user() between tests would otherwise leave that cached value stale. Refresh
	 * it explicitly here to reproduce what a real request's boot would have captured.
	 *
	 * @param int $user_id User ID to switch to (0 for a logged-out/guest request).
	 * @return void
	 */
	private function set_current_test_user( $user_id ) {
		wp_set_current_user( $user_id );
		Notifima()->current_user    = wp_get_current_user();
		Notifima()->current_user_id = $user_id;
	}

	/**
	 * A logged-in user subscribing to an out-of-stock product should get a success response and
	 * a real row in the subscribers table.
	 */
	public function test_subscribe_action_creates_a_subscriber_row() {
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$this->set_current_test_user( $user_id );

		$product = $this->create_out_of_stock_product();

		$request = $this->build_update_request(
			array(
				'action'         => 'subscribe',
				'customer_email' => 'rest-shopper@example.com',
				'product_id'     => $product->get_id(),
				'product_title'  => $product->get_name(),
			)
		);

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertSame( 200, $response->get_status() );
		$this->assertTrue( $data['status'] );
		$this->assertNotFalse(
			\Notifima\Subscriber::is_already_subscribed( 'rest-shopper@example.com', $product->get_id() )
		);
	}

	/**
	 * Subscribing twice with the same email should report "already subscribed" rather than
	 * silently creating a duplicate.
	 */
	public function test_subscribe_action_reports_already_subscribed_on_second_attempt() {
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$this->set_current_test_user( $user_id );

		$product = $this->create_out_of_stock_product();
		$params  = array(
			'action'         => 'subscribe',
			'customer_email' => 'rest-shopper@example.com',
			'product_id'     => $product->get_id(),
			'product_title'  => $product->get_name(),
		);

		$this->server->dispatch( $this->build_update_request( $params ) );
		$response = $this->server->dispatch( $this->build_update_request( $params ) );
		$data     = $response->get_data();

		$this->assertFalse( $data['status'] );
		$this->assertTrue( $data['already_subscribed'] );
	}

	/**
	 * A user unsubscribing their own email should succeed and the row should stop matching
	 * is_already_subscribed().
	 */
	public function test_unsubscribe_action_removes_own_subscription() {
		$user = self::factory()->user->create_and_get( array( 'role' => 'subscriber' ) );
		$this->set_current_test_user( $user->ID );

		$product = $this->create_out_of_stock_product();
		\Notifima\Subscriber::insert_subscriber( $user->user_email, $product->get_id() );

		$request = $this->build_update_request(
			array(
				'action'         => 'unsubscribe',
				'product_id'     => $product->get_id(),
				// Deliberately empty rather than omitted: unsubscribe_user() falls back to the
				// current user's own email in that case (see its docblock) - the REST client
				// this endpoint actually serves always submits the field, even when empty.
				'customer_email' => '',
			)
		);

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertSame( 200, $response->get_status() );
		$this->assertTrue( $data['status'] );
		$this->assertNull( \Notifima\Subscriber::is_already_subscribed( $user->user_email, $product->get_id() ) );
	}

	/**
	 * A logged-in user must not be able to unsubscribe someone else's email address.
	 */
	public function test_unsubscribe_action_rejects_mismatched_email() {
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$this->set_current_test_user( $user_id );

		$product = $this->create_out_of_stock_product();
		\Notifima\Subscriber::insert_subscriber( 'someone-else@example.com', $product->get_id() );

		$request = $this->build_update_request(
			array(
				'action'         => 'unsubscribe',
				'product_id'     => $product->get_id(),
				'customer_email' => 'someone-else@example.com',
			)
		);

		$response = $this->server->dispatch( $request );

		$this->assertSame( 403, $response->get_status() );
		$this->assertNotNull(
			\Notifima\Subscriber::is_already_subscribed( 'someone-else@example.com', $product->get_id() )
		);
	}

	/**
	 * GET /subscribers must require manage_options - a plain subscriber should be refused.
	 */
	public function test_get_items_requires_manage_options_capability() {
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$this->set_current_test_user( $user_id );

		$request = new WP_REST_Request( 'GET', '/notifima/v1/subscribers' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );

		$response = $this->server->dispatch( $request );

		$this->assertSame( 403, $response->get_status() );
	}

	/**
	 * GET /subscribers as an administrator should list previously created subscriber rows and
	 * report accurate per-status counts via the response headers.
	 */
	public function test_get_items_lists_subscribers_for_an_administrator() {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->set_current_test_user( $admin_id );

		$product = $this->create_out_of_stock_product();
		\Notifima\Subscriber::insert_subscriber( 'admin-view@example.com', $product->get_id() );

		$request = new WP_REST_Request( 'GET', '/notifima/v1/subscribers' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertSame( 200, $response->get_status() );
		$this->assertNotEmpty( $data );
		$this->assertSame( 'admin-view@example.com', $data[0]['email'] );
		// Utill::get_subscribers()'s count mode casts to (int), so the header carries a real int here.
		$this->assertSame( 1, $response->get_headers()['X-Subscribed'] );
	}
}
