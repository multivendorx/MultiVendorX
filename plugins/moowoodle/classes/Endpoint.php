<?php
/**
 * Endpoint class file.
 *
 * @package MooWoodle
 */

namespace MooWoodle;

/**
 * MooWoodle Endpoint class
 *
 * @class       Emails class
 * @version     3.3.0
 * @author      DualCube
 */
class Endpoint {
	/**
     * Holds the endpoint name.
     *
     * @var string
     */
	private $endpoint_slug = 'my-courses';

	/**
     * Endpoint constructor.
     */
	public function __construct() {
		add_action( 'init', array( $this, 'register_endpoint' ) );
		add_filter( 'woocommerce_account_menu_items', array( $this, 'add_account_menu_item' ) );
		add_action( 'woocommerce_account_' . $this->endpoint_slug . '_endpoint', array( $this, 'render_my_courses_content' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
     * Register the endpoint.
     */
	public function register_endpoint() {
		add_rewrite_endpoint( $this->endpoint_slug, EP_ROOT | EP_PAGES );
	}

	/**
	 * Add "My Courses" menu item to WooCommerce My Account menu.
	 *
	 * @param array $menu_items The existing account menu items.
	 * @return array Modified menu items with My Courses.
	 */
	public function add_account_menu_item( $menu_items ) {
		$position = (int) MooWoodle()->setting->get_setting( 'my_courses_priority', 0 );

		return array_merge(
			array_slice( $menu_items, 0, $position + 1, true ),
			array( $this->endpoint_slug => __( 'My Courses', 'moowoodle' ) ),
			array_slice( $menu_items, $position + 1, null, true )
		);
	}

	/**
	 * Render the MooWoodle course section on the customer's My Account page.
	 *
	 * This outputs a container div which can be used by JavaScript (e.g., React).
	 * to dynamically load and display the user's enrolled courses.
	 *
	 * @return void
	 */
	public function render_my_courses_content() {
		echo '<div id="moowoodle-my-course"></div>';
	}

	/**
     * Enqueues all frontend assets for this endpoint.
     *
     * @return void
     */
	public function enqueue_assets() {
		if ( is_account_page() ) {
			FrontendScripts::enqueue_script( 'moowoodle-vendor' );
			FrontendScripts::enqueue_script( 'moowoodle-my-courses' );
			FrontendScripts::localize_scripts( 'moowoodle-my-courses' );
			FrontendScripts::enqueue_style( 'moowoodle-my-courses' );
		}
	}
}
