<?php
/**
 * Multivendor X API
 *
 * Handles MVX-API endpoint requests.
 *
 * @author   Multivendor X
 * @category API
 * @package  MVX/API
 * @since    3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * API class.
 */
class MVX_REST_API {
	/**
	 * Setup class.
	 *
	 * @since 3.1
	 */
	public function __construct() {

		// Add query vars.
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );

		// Register API endpoints.
		add_action( 'init', array( $this, 'add_endpoint' ), 0 );

		// Handle wc-api endpoint requests.
		add_action( 'parse_request', array( $this, 'handle_api_requests' ), 0 );
		
		// WP REST API.
		$this->rest_api_init();

		add_action( 'rest_api_init', array( $this, 'mvx_rest_routes_react_module' ) );
	}
	

	/**
	 * Add new query vars.
	 *
	 * @since 3.1
	 * @param array $vars Query vars.
	 * @return string[]
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'mvx-api';
		return $vars;
	}

	/**
	 * MVX API for payment gateway IPNs, etc.
	 *
	 * @since 3.1
	 */
	public static function add_endpoint() {
		add_rewrite_endpoint( 'mvx-api', EP_ALL );
	}

	/**
	 * API request - Trigger any API requests.
	 *
	 * @since   3.1
	 */
	public function handle_api_requests() {
		global $wp;

		if ( ! empty( $_GET['mvx-api'] ) ) { // WPCS: input var okay, CSRF ok.
			$wp->query_vars['mvx-api'] = sanitize_key( wp_unslash( $_GET['mvx-api'] ) ); // WPCS: input var okay, CSRF ok.
		}

		// mvx-api endpoint requests.
		if ( ! empty( $wp->query_vars['mvx-api'] ) ) {

			// Buffer, we won't want any output here.
			ob_start();

			// No cache headers.
			wc_nocache_headers();

			// Clean the API request.
			$api_request = strtolower( wc_clean( $wp->query_vars['mvx-api'] ) );

			// Trigger generic action before request hook.
			do_action( 'mvx_rest_api_request', $api_request );

			// Is there actually something hooked into this API request? If not trigger 400 - Bad request.
			status_header( has_action( 'mvx_rest_api_' . $api_request ) ? 200 : 400 );

			// Trigger an action which plugins can hook into to fulfill the request.
			do_action( 'mvx_rest_api_' . $api_request );

			// Done, clear buffer and exit.
			ob_end_clean();
			die( '-1' );
		}
	}

	/**
	 * Init WP REST API.
	 *
	 * @since 3.1
	 */
	private function rest_api_init() {
		// REST API was included starting WordPress 4.4.
		if ( ! class_exists( 'WP_REST_Server' ) ) {
			return;
		}

		// Init REST API routes.
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ), 10 );
	}

	/**
	 * Include REST API classes.
	 *
	 * @since 3.1
	 */
	private function rest_api_includes() {
		// REST API v1 controllers.
		$this->load_controller_class('vendors');
		$this->load_controller_class('products');
		$this->load_controller_class('coupons');
		$this->load_controller_class('orders');
		$this->load_controller_class('vendor-reviews');
	}

	/**
	 * Register REST API routes.
	 *
	 * @since 3.1
	 */
	public function register_rest_routes() {
		// Register settings to the REST API.
		$this->register_wp_admin_settings();

		$this->rest_api_includes();

		$controllers = array(
			// v1 controllers.
			'MVX_REST_API_Vendors_Controller',
			'MVX_REST_API_Vendor_Reviews_Controller'
		);

		foreach ( $controllers as $controller ) {
			$this->$controller = new $controller();
			$this->$controller->register_routes();
		}
	}

	/**
	 * Register WC settings from WP-API to the REST API.
	 *
	 * @since  3.0.0
	 */
	public function register_wp_admin_settings() {
		$pages = WC_Admin_Settings::get_settings_pages();
		foreach ( $pages as $page ) {
			new WC_Register_WP_Admin_Settings( $page, 'page' );
		}

		$emails = WC_Emails::instance();
		foreach ( $emails->get_emails() as $email ) {
			new WC_Register_WP_Admin_Settings( $email, 'email' );
		}
	}
	
	/**
	 * Load class located under api folder
	 *
	 * @since  3.1
	 */
	function load_controller_class($class_name = '') {
        global $MVX;
        if ('' != $class_name) {
            require_once($MVX->plugin_path . 'api/class-' . esc_attr($MVX->token) . '-rest-' . esc_attr($class_name) . '-controller.php');
        } // End If Statement
    }

    public function mvx_rest_routes_react_module() {
        register_rest_route( 'mvx_module/v1', '/checkbox_update', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'save_checkbox_module' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/module_lists', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'wcp_get_module_lists' ),
            'permission_callback' => array( $this, 'save_settings_permission' ),
            'args'     => [
                'module_id' => [
                    'required' => false,
                    'type'     => 'string',
                ],
            ],
        ] );

        register_rest_route( 'mvx_module/v1', '/module_lists/module_ids', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_get_module_lists_keys' ),
            'permission_callback' => array( $this, 'save_settings_permission' ),
        ] );


        register_rest_route( 'mvx_module/v1', '/save_dashpages', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'mvx_save_dashpages' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/save_registration', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'mvx_save_registration_forms' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/get_registration', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_get_registration_forms_data' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/save_front_registration', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'mvx_front_registration' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/vendor_details', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_vendor_details' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/all_vendors', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_all_vendor_details' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/create_vendor', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'mvx_create_vendor' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/update_vendor', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'mvx_update_vendor' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/all_vendor_followers', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_all_vendor_followers' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/vendor_list_search', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_vendor_list_search' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/specific_search_vendor', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_specific_search_vendor' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/specific_vendor_shipping', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_specific_vendor_shipping' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/specific_vendor_shipping_zone', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_specific_vendor_shipping_zone' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/add_shipping_option', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_add_shipping_option' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/add_vendor_shipping_method', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'mvx_add_vendor_shipping_method' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/update_vendor_shipping_method', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'mvx_update_vendor_shipping_method' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/delete_shipping_method', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'mvx_delete_shipping_method' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/toggle_shipping_method', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'mvx_toggle_shipping_method' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/update_post_code', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'mvx_update_post_code' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/all_commission', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_all_commission_details' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/commission_list_search', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_commission_list_search' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/search_specific_commission', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_search_specific_commission' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/show_commission_status_list', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_show_commission_status_list' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );
        
        register_rest_route( 'mvx_module/v1', '/show_commission_from_status_list', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_show_commission_from_status_list' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/show_vendor_name', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_show_vendor_name' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/show_vendor_name_from_list', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_show_vendor_name_from_list' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/update_commission_bulk', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'mvx_update_commission_bulk' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/update_specific_vendor_shipping_option', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'mvx_update_specific_vendor_shipping_option' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/vendor_delete', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'mvx_vendor_delete' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/details_specific_commission', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_details_specific_commission' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );
    
        register_rest_route( 'mvx_module/v1', '/get_commission_id_status', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_get_commission_id_status' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/update_commission_status', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'mvx_update_commission_status' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

    }

    public function mvx_update_commission_status($request) {
        global $MVX;
        $value = $request->get_param('value') ? ($request->get_param('value')) : 0;
        $commission_id = $request->get_param('commission_id') ? absint($request->get_param('commission_id')) : 0;
        if( $value == 'paid' ) {
            $MVX->postcommission->mvx_mark_commission_paid( array( $commission_id ) ) ;
        } else {
            update_post_meta($commission_id, '_paid_status', wc_clean(wp_unslash($value)));
        }
    }

    public function mvx_get_commission_id_status($request) {
        $commission_id = $request->get_param('commission_id') ? absint($request->get_param('commission_id')) : 0;
        $status = MVX_Commission::get_status($commission_id, 'edit');

        $commission_status_list_action = array();
        $commission_status = mvx_get_commission_statuses();
        foreach ($commission_status as $status_key => $status_value) {
            if ($status_key == $status) {
                $commission_status_list_action[] = array(
                    'value' => $status_key,
                    'label' => $status_value
                );
            }
        }
        return rest_ensure_response($commission_status_list_action);
    }

    public function mvx_details_specific_commission($request) {
        //$payment_details = array();
        global $MVX;
        $commission_id = $request->get_param('commission_id') ? ($request->get_param('commission_id')) : 0;
        $commission_order_id = get_post_meta($commission_id, '_commission_order_id', true);
        $order = wc_get_order($commission_order_id);
        if (!$order) return;
        $vendor_order = mvx_get_order($commission_order_id);
        $commission_order_version = get_post_meta($commission_order_id, '_order_version', true);
        $post = get_post($commission_id);
        $vendor = get_mvx_vendor($post->post_author);
        if (!$vendor) {
            $vendor_id = get_post_meta($commission_order_id, '_vendor_id', true);
            $vendor = get_mvx_vendor($vendor_id);
        }
        $commission_type_object = get_post_type_object( $post->post_type );
        $shipping_amount   =  get_post_meta( $commission_id, '_shipping', true );
        $tax_amount = get_post_meta( $commission_id, '_tax', true );

        $commission_amount =  get_post_meta( $commission_id, '_commission_amount', true );


        $meta_list = array();

        if ( $vendor ) {
            /* translators: %s: associated vendor */
            $vendor_string = sprintf(
                __( 'Associated vendor %s', 'dc-woocommerce-multi-vendor' ),
                '<a href="' . sprintf('?page=%s&ID=%s&name=vendor_personal', 'vendors', $vendor->id) . '" target="_blank">'.$vendor->page_title.'</a>'
            );

            $meta_list[] = $vendor_string;
        }

        /* translators: %s: Commission status */
        $status = MVX_Commission::get_status($commission_id, 'edit');
        $status_html = '';
        if($status == 'paid'){
            $status_html .= '<mark class="order-status status-processing tips"><span>'.MVX_Commission::get_status($commission_id).'</span></mark>';
        }else{
            $status_html .= '<mark class="order-status status-refunded tips"><span>'.MVX_Commission::get_status($commission_id).'</span></mark>';
        }

        $meta_list[] = sprintf(
            __( 'Commission status: %s', 'dc-woocommerce-multi-vendor' ),
            $status_html
        );

        $order_meta_details = wp_kses_post( implode( '. ', $meta_list ) );


        $line_items = $order->get_items(apply_filters('mvx_admin_commission_order_item_types', 'line_item'));
        $line_items_details = $shipping_items_details = '';
        $line_items_meta_details = [];
        $hidden_order_itemmeta = apply_filters(
                'mvx_admin_commission_hidden_order_itemmeta', array(
                '_qty',
                '_tax_class',
                '_product_id',
                '_variation_id',
                '_line_subtotal',
                '_line_subtotal_tax',
                '_line_total',
                '_line_tax',
                'method_id',
                '_vendor_item_commission',
                'cost',
            )
        );
        if ($line_items) {
            foreach ($line_items as $item_id => $item) {
                $product = $item->get_product();
                $product_link = $product ? admin_url('post.php?post=' . $item->get_product_id() . '&action=edit') : '';
                $thumbnail = $product ? apply_filters('mvx_admin_commission_order_item_thumbnail', $product->get_image('thumbnail', array('title' => '')), $item_id, $item) : '';
                $row_class = apply_filters('mvx_admin_commission_html_order_item_class', !empty($class) ? $class : '', $item, $order);
                $meta_data = $item->get_formatted_meta_data('');

                if ($meta_data) {
                    foreach ($meta_data as $meta_id => $meta) {
                        if (in_array($meta->key, $hidden_order_itemmeta, true)) {
                            continue;
                        }
                        $line_items_meta_details[]  =   array(
                            'display_key'   =>  wp_kses_post($meta->display_key),
                            'display_value' =>  wp_kses_post(force_balance_tags($meta->display_value)),
                        );
                    }
                }
                $refunded = $order->get_total_refunded_for_item($item_id);
                $refunded_qty = $order->get_qty_refunded_for_item($item_id);

                $line_items_details   =   array(
                    'item_id'   =>  $item_id,
                    'product'   =>  $item->get_product(),
                    'product_link'  =>  $product ? admin_url('post.php?post=' . $item->get_product_id() . '&action=edit') : '',
                    'thumbnail' =>  $product ? apply_filters('mvx_admin_commission_order_item_thumbnail', $product->get_image('thumbnail', array('title' => ''), false), $item_id, $item) : '',
                    'row_class' =>  apply_filters('mvx_admin_commission_html_order_item_class', !empty($class) ? $class : '', $item, $order),
                    'item_name' =>  $item->get_name(),
                    'product_link_display'  =>  $product_link ? '<a href="' . esc_url($product_link) . '" class="wc-order-item-name">' . esc_html($item->get_name()) . '</a>' : '<div class="wc-order-item-name">' . esc_html($item->get_name()) . '</div>',
                    'product_sku'   => $product && $product->get_sku() ? '<div class="wc-order-item-sku"><strong>' . esc_html__('SKU:', 'dc-woocommerce-multi-vendor') . '</strong> ' . esc_html($product->get_sku()) . '</div>'    :   '',
                    'variation_id_text' =>  '<div class="wc-order-item-variation"><strong>' . esc_html__('Variation ID:', 'dc-woocommerce-multi-vendor') . '</strong> ',
                    'get_variation_post_type'   =>  get_post_type($item->get_variation_id()),
                    'check_variation_id'    =>  $item->get_variation_id(),
                    'item_variation_display'    =>  esc_html($item->get_variation_id()),
                    'no_longer_exist'   =>  sprintf(esc_html__('%s (No longer exists)', 'dc-woocommerce-multi-vendor'), $item->get_variation_id()),
                    'close_div' =>  '</div>',
                    'meta_data' =>  $line_items_meta_details,
                    'meta_format_data'  =>  $meta_data,
                    'item_cost' =>  wc_price($order->get_item_total($item, false, true), array('currency' => $order->get_currency())),
                    'line_cost_html'    => $item->get_subtotal() !== $item->get_total() ? '<span class="wc-order-item-discount">-' . wc_price(wc_format_decimal($order->get_item_subtotal($item, false, false) - $order->get_item_total($item, false, false), ''), array('currency' => $order->get_currency())) . '</span>' :   '',
                    'quantity_1st'  =>  '<small class="times">&times;</small> ' . esc_html($item->get_quantity()),
                    'quantity_2nd'  =>  $order->get_qty_refunded_for_item($item_id) ? '<small class="refunded">-' . ( $refunded_qty * -1 ) . '</small>' :   '',

                    'line_cost' =>  wc_price($item->get_total(), array('currency' => $order->get_currency())),
                    'line_cost_1st' =>  $item->get_subtotal() !== $item->get_total()    ?   '<span class="wc-order-item-discount">-' . wc_price(wc_format_decimal($item->get_subtotal() - $item->get_total(), ''), array('currency' => $order->get_currency())) . '</span>' :   '',
                    'line_cost_2nd' =>  $refunded ?  '<small class="refunded">-' . wc_price($refunded, array('currency' => $order->get_currency())) . '</small>'    :   '',
                    'item_thunbail' =>  '<div class="wc-order-item-thumbnail">' . ($thumbnail) . '</div>',
                );
            }
        }

        $line_items_shipping = $order->get_items('shipping');
        $shipping_methods = WC()->shipping() ? WC()->shipping->load_shipping_methods() : array();
        $shipping_items_meta_details  =   '';
        $refunded = $order->get_total_refunded_for_item($item_id, 'shipping');

        $get_total_shipping_refunded = $order->get_total_shipping_refunded();

        if ($line_items_shipping) {

            foreach ($line_items_shipping as $item_id => $item) {

                $hidden_order_itemmeta = apply_filters(
                    'mvx_admin_commission_hidden_order_itemmeta', array(
                        '_qty',
                        '_tax_class',
                        '_product_id',
                        '_variation_id',
                        '_line_subtotal',
                        '_line_subtotal_tax',
                        '_line_total',
                        '_line_tax',
                        'method_id',
                        'cost',
                    )
                );
                if ($meta_data = $item->get_formatted_meta_data('')) {
                    foreach ($meta_data as $meta_id => $meta) {
                        if (in_array($meta->key, $hidden_order_itemmeta, true)) {
                            continue;
                        }
                        $shipping_items_meta_details  =   array(
                            'display_key'   =>  wp_kses_post($meta->display_key),
                            'display_value' =>  wp_kses_post(force_balance_tags($meta->display_value)),
                            'shipping_price'    =>  wc_price($item->get_total(), array('currency' => $order->get_currency())),

                        );
                    }
                }

                $shipping_items_details   =   array(
                    'shipping_text'   =>  esc_html($item->get_name() ? $item->get_name() : __('Shipping', 'dc-woocommerce-multi-vendor') ),
                    'meta_data' =>  $shipping_items_meta_details,
                    'refunded_amount'   =>  $refunded ? '<small class="refunded">-' . wc_price($refunded, array('currency' => $order->get_currency())) . '</small>' : ''
                );

            }
        }




        $tax_data   =   '';
        $get_total_tax_refunded_by_rate_id = $order->get_total_tax_refunded_by_rate_id($tax->rate_id);
        if (wc_tax_enabled()) {
            foreach ($order->get_tax_totals() as $code => $tax) {
                $tax_data   =   array(
                    'tax_label' =>  esc_html($tax->label),
                    'get_total_tax_refunded_by_rate_id' =>  $get_total_tax_refunded_by_rate_id,
                    'greater_zero'  =>  '<del>' . strip_tags($tax->formatted_amount) . '</del> <ins>' . wc_price(WC_Tax::round($tax->amount, wc_get_price_decimals()) - WC_Tax::round($get_total_tax_refunded_by_rate_id, wc_get_price_decimals()), array('currency' => $order->get_currency())) . '</ins>',
                    'else_output'   =>  wp_kses_post($tax->formatted_amount)
                );
            }
        }

        $commission_total = get_post_meta( $commission_id, '_commission_total', true );
        $is_migration_order = get_post_meta($order_id, '_order_migration', true); // backward compatibility
        $notes = $MVX->postcommission->get_commission_notes($commission_id);
        $notes_data = '';
        if ($notes) {
            foreach ($notes as $note) {
                $notes_data = array(
                    'comment_content'   =>  $note->comment_content,
                    'comment_date'   =>  $note->comment_date,
                );
            }
        }

        $order_edit_link = sprintf('post.php?post=%s&action=edit', $commission_order_id);

        $payment_details = array(
            'commission_id' => $commission_id,
            'commission_order_id'   => $commission_order_id,
            'commission_type_object'    =>  $commission_type_object,
            'vendor_edit_link'  => sprintf('?page=%s&ID=%s&name=vendor_personal', 'vendors', $vendor->id),
            'vendor'    =>  $vendor,
            'status'    =>  MVX_Commission::get_status($commission_id, 'edit'),
            'order_edit_link'   =>  $order_edit_link,
            'order' => $order,
            'avater_image'  =>  get_avatar($vendor->id, 50),
            'payment_title' =>  isset($MVX->payment_gateway->payment_gateways[$vendor->payment_mode]) ? $MVX->payment_gateway->payment_gateways[$vendor->payment_mode]->gateway_title : '',
            'commission_total_calculate'    =>  MVX_Commission::commission_amount_totals($commission_id, 'edit'),
            'commission_totals' =>  '<del>' . wc_price($commission_amount, array('currency' => $order->get_currency())) . '</del> <ins>' . MVX_Commission::commission_amount_totals($commission_id).'</ins>',
            'commission_totals_else'    =>  MVX_Commission::commission_amount_totals($commission_id),
            'shipping_amount'   =>  get_post_meta( $commission_id, '_shipping', true ),
            'commission_shipping_totals'    =>  MVX_Commission::commission_shipping_totals($commission_id, 'edit'),
            'commission_shipping_totals_output' =>  '<del>' . wc_price($shipping_amount, array('currency' => $order->get_currency())) . '</del> <ins>' . MVX_Commission::commission_shipping_totals($commission_order_id, 'edit').'</ins>',
            'tax_amount'    =>  $tax_amount,
            'commission_tax_total'  =>  MVX_Commission::commission_tax_totals($commission_id, 'edit'),
            'commission_tax_total_output'   =>  '<del>' . wc_price($tax_amount, array('currency' => $order->get_currency())) . '</del> <ins>' . MVX_Commission::commission_tax_totals($commission_id).'</ins>',
            'order_meta_details'    =>  $order_meta_details,
            'order_status_display'  =>  ucfirst($order->get_status()),
            'commission_amount' =>  $commission_amount,
            'line_items'    =>  $line_items_details,

            'order_total_discount'  =>  $order->get_total_discount(),
            'commission_include_coupon' =>  get_post_meta($commission_id, '_commission_include_coupon', true),
            'is_shipping'   =>   get_post_meta($commission_id, '_shipping', true),
            'commission_total_include_shipping' =>  get_post_meta($commission_id, '_commission_total_include_shipping', true),
            'is_tax'    =>  get_post_meta($commission_id, '_tax', true),
            'commission_total_include_tax'  =>  get_post_meta($commission_id, '_commission_total_include_tax', true),
            'formated_commission_total' =>  $vendor_order->get_formatted_commission_total(),
            'get_total_shipping_refunded'   =>  $get_total_shipping_refunded,
            'refund_shipping_display'    =>  '<del>' . strip_tags(wc_price($order->get_shipping_total(), array('currency' => $order->get_currency()))) . '</del> <ins>' . wc_price($order->get_shipping_total() - $get_total_shipping_refunded, array('currency' => $order->get_currency())) . '</ins>',
            'else_shipping' =>  wc_price($order->get_shipping_total(), array('currency' => $order->get_currency())),
            'tax_data'  =>  $tax_data,
            'commission_total'  =>  $commission_total,
            'is_migration_order'    =>  $is_migration_order,
            'commission_total_edit' =>  MVX_Commission::commission_totals($commission_id, 'edit'),
            'commission_total_display'  => '<del>' . wc_price($commission_total, array('currency' => $order->get_currency())) . '</del> <ins>' . MVX_Commission::commission_totals($commission_id).'</ins>',
            'is_refuned'    =>  get_post_meta( $commission_id, '_commission_refunded', true ),
            'refunded_output'   =>  wc_price(get_post_meta( $commission_id, '_commission_refunded', true ), array('currency' => $order->get_currency())),
            'get_shipping_method'   =>  $order->get_shipping_methods(),
            'notes_data'    =>  $notes_data
        );

                ///print_r($payment_details);die;


        return $payment_details; 


    }

    public function mvx_vendor_delete($request) {
        require_once(ABSPATH.'wp-admin/includes/user.php');
        $vendor_ids = $request->get_param('vendor_ids') ? ($request->get_param('vendor_ids')) : array();
        $all_details = array();

        if ($vendor_ids) {
            foreach (wp_list_pluck($vendor_ids, "ID") as $key => $value) {
                wp_delete_user($value);
            }
        }
    }

    public function mvx_update_specific_vendor_shipping_option($request) {
        $value = $request->get_param('value') ? ($request->get_param('value')) : 0;
        $vendor_id = $request->get_param('vendor_id') ? ($request->get_param('vendor_id')) : 0;
        mvx_update_user_meta($vendor_id, 'vendor_shipping_options', wc_clean($value));
    }

    public function mvx_update_commission_bulk($request) {
        global $MVX;
        $value = $request->get_param('value') ? ($request->get_param('value')) : 0;
        $commission_list = $request->get_param('commission_list') ? ($request->get_param('commission_list')) : array();
        if ($value == 'mark_paid') {
            $MVX->postcommission->mvx_mark_commission_paid($commission_list);
        } else if ($value == 'export') {
            $commissions_data = array();
            $currency = get_woocommerce_currency();
            foreach ($commission_list as $commission) {
                $commission_data = $MVX->postcommission->get_commission($commission);
                $commission_staus = get_post_meta($commission, '_paid_status', true);
                $recipient = get_user_meta($commission_data->vendor->id, '_vendor_paypal_email', true) ? get_user_meta($commission_data->vendor->id, '_vendor_paypal_email', true) : $commission_data->vendor->page_title;
                $commission_amount = get_post_meta( $commission, '_commission_amount', true ) ? get_post_meta( $commission, '_commission_amount', true ) : 0;
                $shipping_amount = get_post_meta( $commission, '_shipping', true ) ? get_post_meta( $commission, '_shipping', true ) : 0;
                $tax_amount = get_post_meta( $commission, '_tax', true ) ? get_post_meta( $commission, '_tax', true ) : 0;
                $commission_total = get_post_meta( $commission, '_commission_total', true ) ? get_post_meta( $commission, '_commission_total', true ) : 0;
                $commission_order = get_post_meta($commission, '_commission_order_id', true) ? wc_get_order(get_post_meta($commission, '_commission_order_id', true)) : false;
                if ($commission_order) $currency = $commission_order->get_currency();
                $commissions_data[] = apply_filters('mvx_vendor_commission_data', array(
                    'Recipient'     =>  $recipient,
                    'Currency'      =>  $currency,
                    'Commission'    =>  $commission_amount,
                    'Shipping'      =>  $shipping_amount,
                    'Tax'           =>  $tax_amount,
                    'Total'         =>  $commission_total,
                    'Status'        =>  $commission_staus
                ), $commission_data);
            }
            return rest_ensure_response($commissions_data);
        }
    }

    public function mvx_show_vendor_name() {
        $option_lists[] = array('value' => 'all', 'label' => __('Show All Commission', 'dc-woocommerce-multi-vendor'));
        $vendors = get_mvx_vendors();
        if ($vendors) {
            foreach($vendors as $vendor_key => $vendor_value) {
                $option_lists[] = array(
                    'value' => $vendor_value->id,
                    'label' => $vendor_value->user_data->data->display_name
                );
            }
        }
        return rest_ensure_response($option_lists);
    }

    public function mvx_show_vendor_name_from_list($request) {
        $vendor_name = $request->get_param('vendor_name') ? ($request->get_param('vendor_name')) : 0;
        return $this->mvx_find_specific_commission( array(), '',  $vendor_name);
    }

    public function mvx_show_commission_from_status_list($request) {
        $commission_status = $request->get_param('commission_status') ? ($request->get_param('commission_status')) : 0;
        return $this->mvx_find_specific_commission( array(), $commission_status );
    }

    public function mvx_show_commission_status_list() {
        $option_lists[] = array('value' => 'all', 'label' => __('Show All Commission', 'dc-woocommerce-multi-vendor'));
        $commission_statuses = mvx_get_commission_statuses(); 

        if ($commission_statuses) {
            foreach($commission_statuses as $commission_key => $commission_value) {
                $option_lists[] = array(
                    'value' => $commission_key,
                    'label' => $commission_value
                );
            }
        }
        return rest_ensure_response($option_lists);
    }

    public function mvx_search_specific_commission($request) {
        $commission_ids = array();
        $commission_ids[] = $request->get_param('commission_ids') ? ($request->get_param('commission_ids')) : 0;
        return $this->mvx_find_specific_commission($commission_ids);
    }

    public function mvx_all_commission_details($request) {
        return $this->mvx_find_specific_commission();
    }

    public function mvx_find_specific_commission($commission_ids = array(), $status = '', $vendor_name = '') {
        $commission_list = array();
        $args = array(
            'post_type' => 'dc_commission',
            'post_status' => array('publish', 'private'),
            'posts_per_page' => -1,
            'fields' => 'ids',
            'post__in' => $commission_ids
        );

        if ($status) {
            $args['meta_query'] = array(
                array(
                    'key' => '_paid_status',
                    'value' => wc_clean($status),
                    'compare' => '='
                )
            );
        }

        if ($vendor_name) {
            $args['meta_query'] = array(
                array(
                    'key' => '_commission_vendor',
                    'value' => wc_clean($vendor_name),
                    'compare' => '='
                )
            );

        }
        $commissions = new WP_Query( $args );

        if ($commissions->get_posts() && !empty($commissions->get_posts())) {
            foreach ($commissions->get_posts() as $commission_key => $commission_value) {

                $order_id = get_post_meta($commission_value, '_commission_order_id', true);
                $commission_details = get_post($commission_value);

                $edit_url = 'post.php?post=' . $order_id . '&action=edit';

                $edit_commission_url = 'post.php?post=' . $commission_value . '&action=edit';

                $order = wc_get_order($order_id);
                $vendor_order = ( $order ) ? mvx_get_order( $order->get_id() ) : array();
                $product_list = '';
                $vendor_list = '';
                $net_earning = '';

                // find vendor 
                $vendor_user_id = get_post_meta($commission_value, '_commission_vendor', true);
                if ( $vendor_order ) {
                    $vendor = $vendor_order->get_vendor();
                    $vendor_list = '<a href="' . esc_url($vendor->permalink) . '">' . $vendor->page_title . '</a>';
                } else { // BW compatibilities
                    if ($vendor_user_id) {
                        if ($vendor) {
                            $edit_vendor_url = get_edit_user_link($vendor_user_id);
                            $vendor_list = '<a href="' . esc_url($edit_vendor_url) . '">' . $vendor->page_title . '</a>';
                        }
                    }
                }
                // find product 
                if ( $order ) {
                    $line_items = $order->get_items( 'line_item' );
                    foreach ($line_items as $item_id => $item) {
                        $product = $item->get_product();
                        $name = ( $product ) ? $product->get_formatted_name() : $item->get_name();
                        $product_id = ( $product ) ? $product->get_id() : 0;
                        $product_list .= ' &nbsp;[&nbsp;<a href="' . esc_url(get_permalink($product_id)) . '">' . $name . '</a>&nbsp;]&nbsp;';
                    }
                }

                // find amount
                $commission_amount = get_post_meta($commission_value, '_commission_amount', true) ? wc_price(get_post_meta($commission_value, '_commission_amount', true)) : 0;

                if ( $vendor_order ) {
                    $net_earning = $vendor_order->get_commission_total();
                } else { // BW compatibilities
                    $commission_vendor = get_post_meta($commission_value, '_commission_vendor', true);
                    $vendor_user_id = get_term_meta($commission_vendor, '_vendor_user_id', true);
                    $vendor = get_mvx_vendor($vendor_user_id);
                    if ($vendor) {
                        $vendor_total = get_mvx_vendor_order_amount(array('vendor_id' => $vendor->id, 'order_id' => $order_id));
                        $net_earning = wc_price($vendor_total['total']);
                    }
                }

                $commission_list[] = array(
                    'id'            =>  $commission_value,
                    'title'         =>  '<a href="' . sprintf('?page=%s&CommissionID=%s', 'commission', $commission_value) . '">#' . $commission_details->post_title . '</a>',
                    'order_id'      =>  '<a href="' . esc_url($edit_url) . '">#' . $order_id . '</a>',
                    'product'       =>  $product_list,
                    'vendor'        =>  $vendor_list,
                    'amount'        =>  $commission_amount,
                    'net_earning'   =>  $net_earning,
                    'status'        =>  get_post_meta($commission_value, '_paid_status', true) ? ucfirst(get_post_meta($commission_value, '_paid_status', true)) : '',
                    'date'          =>  $commission_details->post_modified,
                );
            }
        }

        return rest_ensure_response($commission_list);
    }

    public function mvx_commission_list_search() {
        $option_lists[] = array('value' => 'all', 'label' => __('All commission', 'dc-woocommerce-multi-vendor'));
         $args = array(
            'post_type' => 'dc_commission',
            'post_status' => array('publish', 'private'),
            'posts_per_page' => -1,
            'fields' => 'ids',
        );
        $commissions = new WP_Query( $args );
        $commissions_list = $commissions->get_posts();

        if ($commissions_list) {
            foreach($commissions_list as $commission_value) {
                $option_lists[] = array(
                    'value' => $commission_value,
                    'label' => $commission_value
                );
            }
        }
        return rest_ensure_response($option_lists);
    }

    public function mvx_update_post_code($request) {
        global $MVX;

        if ( !class_exists( 'MVX_Shipping_Zone' ) ) {
            $MVX->load_vendor_shipping();
        }

        

        $zone_id = $request->get_param('zone_id') ? $request->get_param('zone_id') : 0;
        $vendor_id = $request->get_param('vendor_id') ? wc_clean($request->get_param('vendor_id')) : 0;
        $_select_zone_postcodes = $request->get_param('value') ? $request->get_param('value') : 0;

        $type = $request->get_param('type') ? $request->get_param('type') : 0;

        $zones = MVX_Shipping_Zone::get_vendor_zone($zone_id, $vendor_id);
        $location = $zones && is_array($zones['locations']) ? $zones['locations'] : array();

        if ($type && $type == 'postcode' && !empty($_select_zone_postcodes)) {
            $postcode_array = array();
            $zone_postcodes = array_map('trim', explode(',', $_select_zone_postcodes));
            foreach ($zone_postcodes as $zone_postcode) {
                $postcode_array[] = array(
                    'code' => $zone_postcode,
                    'type' => 'postcode'
                );
            }

            $location = array_merge($location, $postcode_array);
        }

        if ($type && $type == 'select_state' && !empty($_select_zone_postcodes)) {
            $state_array = array();
            if (!empty($_select_zone_postcodes)) {
                foreach ($_select_zone_postcodes as $zone_statecode) {
                    $state_array[] = array(
                        'code' => $zone_statecode['value'],
                        'type' => 'state'
                    );
                }
            }

            $location = array_merge($location, $state_array);
        }

        
        MVX_Shipping_Zone::save_location($location, $zone_id, $vendor_id);

        $MVX->load_class('shipping-gateway');
        MVX_Shipping_Gateway::load_class('shipping-method');
        $vendor_shipping = new MVX_Vendor_Shipping_Method();
        $vendor_shipping->process_admin_options();

        // clear shipping transient
        WC_Cache_Helper::get_transient_version('shipping', true);

    }

    public function mvx_toggle_shipping_method($request) {
        global $MVX;
        $zone_id = $request->get_param('zone_id') ? $request->get_param('zone_id') : 0;
        $instance_id = $request->get_param('instance_id') ? ($request->get_param('instance_id')) : 0;
        $vendor_id = $request->get_param('vendor_id') ? wc_clean($request->get_param('vendor_id')) : 0;
        $value = $request->get_param('value') ? $request->get_param('value') : 0;


        $data = array(
            'instance_id' => wc_clean($instance_id),
            'zone_id' => absint($zone_id),
            'checked' => ( $value ) ? 1 : 0
        );
        if ( !class_exists( 'MVX_Shipping_Zone' ) ) {
            $MVX->load_vendor_shipping();
        }
        $result = MVX_Shipping_Zone::toggle_shipping_method($data, $vendor_id);
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        $message = $data['checked'] ? __('Shipping method enabled successfully', 'dc-woocommerce-multi-vendor') : __('Shipping method disabled successfully', 'dc-woocommerce-multi-vendor');
        wp_send_json_success($message);
    }

    public function mvx_delete_shipping_method($request) {
        global $MVX;
        $zone_id = $request->get_param('zone_id') ? $request->get_param('zone_id') : 0;
        $instance_id = $request->get_param('instance_id') ? ($request->get_param('instance_id')) : 0;
        $vendor_id = $request->get_param('vendor_id') ? wc_clean($request->get_param('vendor_id')) : 0;

        $data = array(
            'zone_id' => wc_clean($zone_id),
            'instance_id' => wc_clean($instance_id)
        );
        
        if ( !class_exists( 'MVX_Shipping_Zone' ) ) {
            $MVX->load_vendor_shipping();
        }
        
        $result = MVX_Shipping_Zone::delete_shipping_methods($data, $vendor_id);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message(), 'mvx');
        }

        wp_send_json_success(__('Shipping method deleted', 'dc-woocommerce-multi-vendor'));
    }

    public function mvx_update_vendor_shipping_method($request) {
        global $MVX;
        $data_details = $request->get_param('data_details') ? $request->get_param('data_details') : 0;
        $change_value = $request->get_param('change_value') ? wc_clean($request->get_param('change_value')) : 0;

        $vendorid = $request->get_param('vendorid') ? wc_clean($request->get_param('vendorid')) : 0;
        $zoneid = $request->get_param('zoneid') ? wc_clean($request->get_param('zoneid')) : 0;

        if ( !class_exists( 'MVX_Shipping_Zone' ) ) {
            $MVX->load_vendor_shipping();
        }

        do_action( 'mvx_before_update_shipping_method', $data_details );
        $result = MVX_Shipping_Zone::update_shipping_method($data_details);
        $MVX->load_class('shipping-gateway');
        MVX_Shipping_Gateway::load_class('shipping-method');
        $vendor_shipping = new MVX_Vendor_Shipping_Method();
        $vendor_shipping->set_post_data($data_details['settings']);
        $vendor_shipping->process_admin_options();

        // clear shipping transient
        WC_Cache_Helper::get_transient_version('shipping', true);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message(), 'MVX');
        }

        wp_send_json_success(__('Shipping method updated', 'dc-woocommerce-multi-vendor'));


    }

    public function mvx_add_shipping_option() {
        $option_lists = array();
        $shipping_methods = mvx_get_shipping_methods();
        if ($shipping_methods) {
            foreach ($shipping_methods as $key => $method) {
                $option_lists[] = array(
                    'value' => esc_attr( $method->id ),
                    'label' => esc_attr( $method->get_method_title() )
                );
            }
        }
        return $option_lists;
    }

    public function mvx_add_vendor_shipping_method($request) {
        global $MVX;
        $vendorid = $request->get_param('vendorid') ? absint($request->get_param('vendorid')) : 0;
        $method_id = $request->get_param('method_id') ? wc_clean($request->get_param('method_id')) : 0;
        $zoneid = $request->get_param('zoneid') ? absint($request->get_param('zoneid')) : 0;
        $this->mvx_backend_add_shipping_method($zoneid, $method_id, $vendorid);
    }

     public function mvx_backend_add_shipping_method($zone_id, $method_id, $vendor_id) {
        global $MVX;
        $data = array(
            'zone_id' => $zone_id,
            'method_id' => $method_id
        );
        if ( !class_exists( 'MVX_Shipping_Zone' ) ) {
            $MVX->load_vendor_shipping();
        }
        $result = MVX_Shipping_Zone::add_shipping_methods($data, $vendor_id);
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message(), 'mvx');
        }

        wp_send_json_success(__('Shipping method added successfully', 'dc-woocommerce-multi-vendor'));
    }

    public function mvx_specific_vendor_shipping_zone($request) {
        global $MVX;
        $vendor_id = $request->get_param('vendor_id') ? absint($request->get_param('vendor_id')) : 0;
        $zone_ids = $request->get_param('zone_id') ? absint($request->get_param('zone_id')) : 0;

        if ( !class_exists( 'MVX_Shipping_Zone' ) ) {
            $MVX->load_vendor_shipping();
        }
        $user_list = array();
        $zones = MVX_Shipping_Zone::get_vendor_zone($zone_ids, $vendor_id);
        if ($zones) {
        //$zone = WC_Shipping_Zones::get_zone(absint($zone_ids));

            $show_post_code_list = $show_state_list = $show_post_code_list = false;
            $zone_id = $zones['data']['id'];
            $zone_locations = $zones['data']['zone_locations'];

            $zone_location_types = array_column(array_map('mvx_convert_to_array', $zone_locations), 'type', 'code');

            $selected_continent_codes = array_keys($zone_location_types, 'continent');

            if (!$selected_continent_codes) {
                $selected_continent_codes = array();
            }

            $selected_country_codes = array_keys($zone_location_types, 'country');
            $all_states = WC()->countries->get_states();

            $state_key_by_country = array();
            $state_key_by_country = array_intersect_key($all_states, array_flip($selected_country_codes));

            array_walk($state_key_by_country, 'mvx_state_key_alter');

            if ($selected_country_codes && is_array($selected_country_codes) && !empty($selected_country_codes) && isset($selected_country_codes[0])) {
                $state_key_by_country = $state_key_by_country[$selected_country_codes[0]];
            }

            $show_limit_location_link = apply_filters('show_limit_location_link', (!in_array('postcode', $zone_location_types)));
            $vendor_shipping_methods = $zones['shipping_methods'];
            if ($show_limit_location_link) {
                if (in_array('state', $zone_location_types)) {
                    $show_city_list = apply_filters('mvx_city_select_dropdown_enabled', false);
                    $show_post_code_list = true;
                } elseif (in_array('country', $zone_location_types)) {
                    $show_state_list = true;
                    $show_city_list = apply_filters('mvx_city_select_dropdown_enabled', false);
                    $show_post_code_list = true;
                }
            }

            $want_to_limit_location = !empty($zones['locations']);
            $countries = $states = $cities = array();
            $postcodes = '';
            if ($want_to_limit_location) {
                $postcodes = array();
                foreach ($zones['locations'] as $each_location) {
                    switch ($each_location['type']) {
                        case 'state':
                            $states[] = $each_location['code'];
                            break;
                        case 'postcode':
                            $postcodes[] = $each_location['code'];
                            break;
                        default:
                            break;
                    }
                }
                
                $postcodes = implode(',', $postcodes);
            }

            $state_selct_final = array();
            if ($state_key_by_country) {
                foreach ($state_key_by_country as $key => $value) {
                    $state_selct_final[] = array(
                        'value' => $key,
                        'label' => $value,
                    );
                }
            }


            // display specific state value
            $new_state_list = array();
            if ($state_selct_final) {
                foreach ($state_selct_final as $state_key => $state_value) {
                    if ($states && in_array($state_value['value'], $states)) {
                        $new_state_list[] = $state_selct_final[$state_key];
                    }
                }
            }

            $user_list = array(
                'zones' => $zones,
                'vendor_shipping_methods' => $vendor_shipping_methods,
                'postcodes' => $postcodes,
                'state_select' => $state_selct_final,
                'get_database_state_name'   => $new_state_list
            );
        }
        return rest_ensure_response($user_list);
    }

    public function mvx_specific_vendor_shipping($request) {
        $user_list = array();
        $vendor_id = $request->get_param('vendor_id') ? absint($request->get_param('vendor_id')) : 0;
        $vendor_all_shipping_zones = mvx_get_shipping_zone();
        if (!empty($vendor_all_shipping_zones)) {
            foreach ($vendor_all_shipping_zones as $key => $vendor_shipping_zones) {

                $vendor_shipping_methods = $vendor_shipping_zones['shipping_methods'];
                $vendor_shipping_methods_titles = array();
                if ($vendor_shipping_methods) {
                    foreach ($vendor_shipping_methods as $key_child => $shipping_method) {
                        $class_name = 'yes' === $shipping_method['enabled'] ? 'method_enabled' : 'method_disabled';
                        $vendor_shipping_methods_titles[] = "<li class='mvx-shipping-zone-method wc-shipping-zone-method $class_name'>" . $shipping_method['title'] . "</li>";
                    }
                }
                $vendor_shipping_methods_titles = implode('', $vendor_shipping_methods_titles);

                $user_list[] = apply_filters('mvx_list_table_vendors_columns_data', array(
                        'zone_name' => "<a href='". sprintf('?page=%s&ID=%s&name=%s&zone_id=%s', 'vendors', $vendor_id, 'vendor_shipping', $vendor_shipping_zones['zone_id']) ."'>". $vendor_shipping_zones['zone_name'] ."</a>",
                        'region' => $vendor_shipping_zones['formatted_zone_location'],
                        'shipping_method' => $vendor_shipping_methods_titles,
                    ));
            }
        }
        return rest_ensure_response($user_list);
    }

    public function mvx_vendor_list_search() {
        $user_list = array();
        $option_lists[] = array('value' => 'all', 'label' => __('All Vendors', 'dc-woocommerce-multi-vendor'));
        $user_query = new WP_User_Query(array('role' => 'dc_vendor', 'orderby' => 'registered', 'order' => 'ASC'));
        $users = $user_query->get_results();
        foreach($users as $user) {
            $option_lists[] = array(
                'value' => sanitize_text_field($user->data->ID),
                'label' => sanitize_text_field($user->data->display_name)
            );
        }
        return rest_ensure_response($option_lists);
    }

    public function mvx_specific_search_vendor($request) {
        $vendor_id = $request->get_param('vendor_id') ? absint($request->get_param('vendor_id')) : 0;
        return $this->mvx_list_all_vendor($vendor_id);
    }

    public function mvx_all_vendor_followers($request) {
        $user_list = array();
        $vendor_id = $request->get_param('vendor_id') ? absint($request->get_param('vendor_id')) : 0;
        $mvx_vendor_followed_by_customer = get_user_meta( $vendor_id, 'mvx_vendor_followed_by_customer', true ) ? get_user_meta( $vendor_id, 'mvx_vendor_followed_by_customer', true ) : array();
        if ($mvx_vendor_followed_by_customer) {
            foreach ($mvx_vendor_followed_by_customer as $key_folloed => $value_followed) {
                $user_details = get_user_by( 'ID', $value_followed['user_id'] );
                if ( !$user_details ) continue;
                $user_list[] = apply_filters('mvx_list_table_vendors_columns_data', array(
                    'name' => $user_details->data->display_name,
                    'time' => human_time_diff(strtotime($value_followed['timestamp'])),
                ), $user_details);
            }
        }
        return $user_list;
        die;
    }

    public function mvx_create_vendor($request) {
        $fetch_data = $request->get_param('model');
        $all_details = array();
        $userdata = array(
            'user_login' => isset( $fetch_data['user_login'] ) ? sanitize_user( $fetch_data['user_login'] ) : '',
            'user_pass' => isset( $fetch_data['password'] ) ? sanitize_text_field( wp_unslash( $fetch_data['password'] ) ) : '',
            'user_email' => isset( $fetch_data['user_email'] ) ? sanitize_email( $fetch_data['user_email'] ) : '',
            'user_nicename' => isset( $fetch_data['user_nicename'] ) ? sanitize_text_field( wp_unslash( $fetch_data['user_nicename'] ) ) : '',
            'first_name' => isset( $fetch_data['first_name'] ) ? sanitize_text_field( wp_unslash( $fetch_data['first_name'] ) ) : '',
            'last_name' => isset( $fetch_data['last_name'] ) ? sanitize_text_field( wp_unslash( $fetch_data['last_name'] ) ) : '',
            'role' => 'dc_vendor',
        );
        $user_id = wp_insert_user( $userdata ) ;
        if ( is_wp_error( $user_id ) ) {
            $error_string = $user_id->get_error_message();
            $message = $error_string;
            $redirect_to = '';
            $user_id = null;
        } else {
            $email = WC()->mailer()->emails['WC_Email_Vendor_New_Account'];
            $email->trigger( $user_id, $userdata['user_pass'], false);
            if (isset($fetch_data['vendor_profile_image']) && $fetch_data['vendor_profile_image'] != '') update_user_meta($user_id, "_vendor_profile_image", absint( $fetch_data['vendor_profile_image']));
            $message = __( 'Vendor successfully created!', 'dc-woocommerce-multi-vendor' );
            $redirect_to = apply_filters('mvx_add_new_vendor_redirect_url', admin_url('admin.php?page=vendors&ID='.$user_id. '&name=vendor_personal'));
        }

        if ($redirect_to) {
            $all_details['redirect_link']   =   $redirect_to;
        }
        $all_details['error'] =   $message;
        return $all_details;
        die();

    }

    public function mvx_update_vendor($request) {
        $user_id = $request->get_param('vendor_id') ? absint( $request->get_param('vendor_id') ) : 0;
        $model = $request->get_param('model') ? ( $request->get_param('model') ) : 0;
        $modelname = $request->get_param('modelname') ? ( $request->get_param('modelname') ) : 0;

        $mvx_shipping_by_distance = $mvx_shipping_by_country = array();

        // distnce wise shipping save
        if (isset($model['mvx_shipping_by_distance_rates']) && !empty($model['mvx_shipping_by_distance_rates'])) {
            update_user_meta($user_id, '_mvx_shipping_by_distance_rates', array_filter( array_map( 'wc_clean', $model['mvx_shipping_by_distance_rates'])));
        }

        if (isset($model['mvx_byd_default_cost']) && !empty($model['mvx_byd_default_cost'])) {
            $mvx_shipping_by_distance['_default_cost'] = isset($model['mvx_byd_default_cost']) ? wc_clean($model['mvx_byd_default_cost'] ) : '';
        }

        if (isset($model['mvx_byd_max_distance']) && !empty($model['mvx_byd_max_distance'])) {
            $mvx_shipping_by_distance['_max_distance'] = isset($model['mvx_byd_max_distance']) ? wc_clean($model['mvx_byd_max_distance'] ) : '';
        }

        if (isset($model['mvx_byd_enable_local_pickup']) && !empty($model['mvx_byd_enable_local_pickup'])) {
            $mvx_shipping_by_distance['_enable_local_pickup'] = isset($model['mvx_byd_enable_local_pickup']) ? wc_clean($model['mvx_byd_enable_local_pickup'] ) : '';
        }

        if (isset($model['mvx_byd_local_pickup_cost']) && !empty($model['mvx_byd_local_pickup_cost'])) {
            $mvx_shipping_by_distance['_local_pickup_cost'] = isset($model['mvx_byd_local_pickup_cost']) ? wc_clean($model['mvx_byd_local_pickup_cost'] ) : '';
        }

        if (!empty($mvx_shipping_by_distance)) {
            update_user_meta($user_id, '_mvx_shipping_by_distance', $mvx_shipping_by_distance);
        }
        
        // shipping by country

        if (isset($model['mvx_country_shipping_rates']) && !empty($model['mvx_country_shipping_rates'])) {
            mvx_update_user_meta($user_id, '_mvx_country_shipping_rates', $model['mvx_country_shipping_rates']);
        }

        if (isset($model['mvx_shipping_type_price']) && !empty($model['mvx_shipping_type_price'])) {
            $mvx_shipping_by_country['_mvx_shipping_type_price'] = isset($model['mvx_shipping_type_price']) ? wc_clean($model['mvx_shipping_type_price'] ) : '';
        }

        if (isset($model['mvx_additional_product']) && !empty($model['mvx_additional_product'])) {
            $mvx_shipping_by_country['_mvx_additional_product'] = isset($model['mvx_additional_product']) ? wc_clean($model['mvx_additional_product'] ) : '';
        }

        if (isset($model['mvx_additional_qty']) && !empty($model['mvx_additional_qty'])) {
            $mvx_shipping_by_country['_mvx_additional_qty'] = isset($model['mvx_additional_qty']) ? wc_clean($model['mvx_additional_qty'] ) : '';
        }

        if (isset($model['mvx_byc_free_shipping_amount']) && !empty($model['mvx_byc_free_shipping_amount'])) {
            $mvx_shipping_by_country['_free_shipping_amount'] = isset($model['mvx_byc_free_shipping_amount']) ? wc_clean($model['mvx_byc_free_shipping_amount'] ) : '';
        }

        if (isset($model['mvx_byc_enable_local_pickup']) && !empty($model['mvx_byc_enable_local_pickup'])) {
            $mvx_shipping_by_country['_enable_local_pickup'] = isset($model['mvx_byc_enable_local_pickup']) ? wc_clean($model['mvx_byc_enable_local_pickup'] ) : '';
        }

        if (isset($model['mvx_byc_local_pickup_cost']) && !empty($model['mvx_byc_local_pickup_cost'])) {
            $mvx_shipping_by_country['_local_pickup_cost'] = isset($model['mvx_byc_local_pickup_cost']) ? wc_clean($model['mvx_byc_local_pickup_cost'] ) : '';
        }

        if (!empty($mvx_shipping_by_country)) {
            update_user_meta($user_id, '_mvx_shipping_by_country', array_filter( array_map( 'wc_clean', $mvx_shipping_by_country) ) );
        }

        // vendor personal details


            $vendor = get_mvx_vendor($user_id);
            if ($vendor) {

                // vendor personal
                if ($modelname == 'vendor_personal') {
                    $userdata = array(
                        'ID' => $user_id,
                        'user_login' => isset( $model['user_login'] ) ? sanitize_user( $model['user_login'] ) : '',
                        'user_pass' => isset( $model['password'] ) ? sanitize_text_field( wp_unslash( $model['password'] ) ) : '',
                        'user_email' => isset( $model['user_email'] ) ? sanitize_email( $model['user_email'] ) : '',
                        'user_nicename' => isset( $model['user_nicename'] ) ? sanitize_text_field( wp_unslash( $model['user_nicename'] ) ) : '',
                        'display_name' => isset( $model['display_name'] ) && isset($model['display_name']['value']) ? $model['display_name']['value'] : '',
                        'first_name' => isset( $model['first_name'] ) ? sanitize_text_field( wp_unslash( $model['first_name'] ) ) : '',
                        'last_name' => isset( $model['last_name'] ) ? sanitize_text_field( wp_unslash( $model['last_name'] ) ) : '',
                    );
                    $user_id = wp_update_user( $userdata ) ;
                }

                if ($modelname == 'vendor_store' || $modelname == 'vendor_social' || $modelname == 'vendor_payments') {
                    foreach($model as $key => $value) {
                        $skip_vendor_update_data = apply_filters('mvx_skipped_vendor_update_keys', array('mvx_commission_type'));
                        if (in_array($key, $skip_vendor_update_data)) continue;

                        if ($value != '') {
                            if ($key == 'vendor_page_title') {
                                if (!$vendor->update_page_title(wc_clean($value))) {
                                    $all_details['error']   =  __('Title Update Error', 'dc-woocommerce-multi-vendor');
                                    return $all_details; 
                                }
                            } else if ($key == 'vendor_page_slug') {
                                if (!$vendor->update_page_slug(wc_clean($value))) {
                                    $all_details['error']   =  __('Slug already exists', 'dc-woocommerce-multi-vendor');
                                    return $all_details; 
                                }
                            } else if ($key === "vendor_country") {
                                if (isset($value['value'])) {
                                    $country_code = wc_clean( wp_unslash( $value['value'] ) );
                                    $country_data = wc_clean( wp_unslash( WC()->countries->get_countries() ) );
                                    $country_name = ( isset( $country_data[ $country_code ] ) ) ? $country_data[ $country_code ] : $country_code; //To get country name by code
                                    update_user_meta($user_id, '_' . $key, $country_name);
                                    update_user_meta($user_id, '_' . $key . '_code', $country_code);
                                }
                            } else if ($key === "vendor_state") {
                                $country_code = isset( $model['vendor_country'] ) && isset( $model['vendor_country']['value'] ) ? wc_clean( wp_unslash( $model['vendor_country']['value'] ) ) : '';
                                $state_code = wc_clean( wp_unslash( $value['key'] ) );
                                $state_data = wc_clean( wp_unslash( WC()->countries->get_states($country_code) ) );
                                $state_name = ( isset( $state_data[$state_code] ) ) ? $state_data[$state_code] : $state_code; //to get State name by state code
                                update_user_meta($user_id, '_' . $key, $state_name);
                                update_user_meta($user_id, '_' . $key . '_code', $state_code);
                            } else if (substr($key, 0, strlen("vendor_")) === "vendor_") {

                                if (is_array($value)) {
                                    update_user_meta($user_id, "_" . $key, wp_unslash( $value['value'] ) );
                                } else {
                                    update_user_meta($user_id, "_" . $key, wp_unslash( $value ) );
                                }
                                
                            }
                        } else {
                            if (substr($key, 0, strlen("vendor_")) === "vendor_") {
                                delete_user_meta($user_id, "_" . $key);
                            }
                        }
                    }
                }

    }

        // policy details
        if ($modelname == 'vendor_policy') {
            if ( isset( $model['vendor_shipping_policy'] ) ) {
                update_user_meta( $user_id, 'vendor_shipping_policy', stripslashes( html_entity_decode( $model['vendor_shipping_policy'], ENT_QUOTES, get_bloginfo( 'charset' ) ) ) );
            }
            if ( isset( $model['vendor_refund_policy'] ) ) {
                update_user_meta( $user_id, 'vendor_refund_policy', stripslashes( html_entity_decode( $model['vendor_refund_policy'], ENT_QUOTES, get_bloginfo( 'charset' ) ) ) );
            }
            if ( isset( $model['vendor_cancellation_policy'] ) ) {
                update_user_meta( $user_id, 'vendor_cancellation_policy', stripslashes( html_entity_decode( $model['vendor_cancellation_policy'], ENT_QUOTES, get_bloginfo( 'charset' ) ) ) );
            }
        }

        if (isset($model['_store_location']) && !empty($model['_store_location'])) {
            mvx_update_user_meta($user_id, '_store_location', wc_clean($model['_store_location']));
        }
        if (isset($model['_store_lat']) && !empty($model['_store_lat'])) {
            mvx_update_user_meta($user_id, '_store_lat', wc_clean($model['_store_lat']));
        }
        if (isset($model['_store_lng']) && !empty($model['_store_lng'])) {
            mvx_update_user_meta($user_id, '_store_lng', wc_clean($model['_store_lng']));
        }

        do_action('mvx_vendor_details_update', $model, $vendor);


    }

    public function mvx_vendor_details($request) {
        $vendor_id = $request->get_param( 'vendor_id' );
        $uniquename = $request->get_param( 'uniquename' );
        return 
        print_r($uniquename);die;
    }
    
    public function mvx_all_vendor_details() {
        return $this->mvx_list_all_vendor('');
    }

    public function mvx_list_all_vendor($specific_id = array()) {
        $user_list = array();
        $user_query = new WP_User_Query(array('role' => 'dc_vendor', 'orderby' => 'registered', 'order' => 'ASC', 'include' => $specific_id));
        $users = $user_query->get_results();
        foreach($users as $user) {
            $vendor = get_mvx_vendor($user->data->ID);
            $product_count = 0;
            $vendor_permalink = ''; 
            $status = "";
            if ($vendor) {
                $vendor_products = $vendor->get_products_ids();
                $vendor_permalink = $vendor->permalink;
                $product_count = count($vendor_products);
            }
            
            if (in_array('dc_vendor', $user->roles)) {
                $is_block = get_user_meta($vendor->id, '_vendor_turn_off', true);
            
                if ($is_block) {
                    $status = "<p class='vendor-status suspended-vendor'>" . __('Suspended', 'dc-woocommerce-multi-vendor') . "</p>";
                } else {
                    $status = "<p class='vendor-status approved-vendor'>" . __('Approved', 'dc-woocommerce-multi-vendor') . "</p>";
                }
            } else if (in_array('dc_rejected_vendor', $user->roles)) {
                $status = "<p class='vendor-status rejected-vendor'>" . __('Rejected', 'dc-woocommerce-multi-vendor') . "</p>";
            } else if (in_array('dc_pending_vendor', $user->roles)) {
                $status = "<p class='vendor-status pending-vendor'>" . __('Pending', 'dc-woocommerce-multi-vendor') . "</p>";
            }

            $name_display = "<b><a href='". sprintf('?page=%s&ID=%s&name=vendor_personal', 'vendors', $user->data->ID) ."'>" . $user->data->display_name . "</a>|   |<a href='".$vendor->permalink."'>Shop</a></b>";
            
            $user_list[] = apply_filters('mvx_list_table_vendors_columns_data', array(
                'ID' => $user->data->ID,
                'name' => $name_display,
                'email' => $user->data->user_email,
                'registered' => get_date_from_gmt( $user->data->user_registered ),
                'products' => $product_count,
                'status' => $status,
                'permalink' => $vendor_permalink,
                'username' => $user->data->user_login
            ), $user);
        }
        return rest_ensure_response($user_list);
    }

    public function mvx_front_registration($request) {
        global $MVX;
        $all_details = array();
        $get_managements_data = $request->get_param( 'model' );

        $email = isset($get_managements_data['mvx_vendor_fields_email']) && !empty($get_managements_data['mvx_vendor_fields_email']) ? $get_managements_data['mvx_vendor_fields_email'] : '';
        $username = isset($get_managements_data['mvx_vendor_fields_username']) && !empty($get_managements_data['mvx_vendor_fields_username']) ? $get_managements_data['mvx_vendor_fields_username'] : '';
        $password = isset($get_managements_data['mvx_vendor_fields_password']) && !empty($get_managements_data['mvx_vendor_fields_password']) ? $get_managements_data['mvx_vendor_fields_password'] : '';

        $user = wc_create_new_customer( sanitize_email( $email ), wc_clean( $username ), $password );
        if ($user) {

            wp_set_current_user( $user );
            wp_set_auth_cookie( $user, true );

            $is_approve_manually = $MVX->vendor_caps->vendor_general_settings('approve_vendor_manually');
            if (!is_user_mvx_vendor($user)) {
                if ($is_approve_manually) {
                    $userdeta = new WP_User(absint($user));
                    $userdeta->set_role('dc_pending_vendor');
                } else {
                    $userdeta = new WP_User(absint($user));
                    $userdeta->set_role('dc_vendor');
                }
            }

            $MVX->user->mvx_customer_new_account($user);
            $redirect_to = apply_filters( 'mvx_user_apply_vendor_redirect_url', get_permalink( mvx_vendor_dashboard_page_id() ) );

            $all_details['redirect_link']   =   $redirect_to;
            return $all_details; 
            die;
        }

        $all_details['error']   =   '';
        return $all_details; 
        die;
    }

    public function mvx_save_registration_forms($request) {
        $form_data = json_decode(stripslashes_deep($request->get_param( 'form_data' )), true);
        if (!empty($form_data) && is_array($form_data)) {
            foreach ($form_data as $key => $value) {
                $form_data[$key]['hidden'] = true;
            }
        }

        mvx_update_option('mvx_vendor_registration_form_data', $form_data);
        die;
    }

    public function mvx_get_registration_forms_data() {
        $mvx_vendor_registration_form_data = mvx_get_option('mvx_vendor_registration_form_data');
        return rest_ensure_response( $mvx_vendor_registration_form_data );
    }

    public function mvx_save_dashpages($req) {
        $all_details = array();
        $modelname = $req->get_param('modelname');
        $get_managements_data = $req->get_param( 'model' );
        $optionname = 'mvx_'.$modelname.'_tab_settings';
        update_option($optionname, $get_managements_data);
        $all_details['error'] = 'Settings Saved';
        return $all_details; 
        die;
    }

    public function save_settings_permission() {
        return true;
    }

    public function wcp_get_module_lists($request) {
        $module_id = $request->get_param( 'module_id' );
        $all_module_lists = $this->mvx_list_all_modules();

        if ($module_id && $module_id == 'all') {
            $response = $all_module_lists ? array_values($all_module_lists) : false;
        } else {
            $response[] = $all_module_lists[$module_id];
        }
        return rest_ensure_response( $response );
    }

    public function mvx_get_module_lists_keys() {
        $option_lists[] = array('value' => 'all', 'label' => __('All Modules', 'dc-woocommerce-multi-vendor'));
        foreach ($this->mvx_list_all_modules() as $key => $value) {
            $option_lists[] = array(
                'value' => sanitize_text_field($value['id']),
                'label' => sanitize_text_field($value['name'])
            );
        }
        return rest_ensure_response( $option_lists );
    }

    public function save_checkbox_module($request) {
        $module_id = $request['module_id'];
        $is_checked = $request['is_checked'];
        $active_module_list = get_option('mvx_all_active_module_list') ? get_option('mvx_all_active_module_list') : array();
        if ($module_id && !in_array($module_id, $active_module_list) && $is_checked) {
            array_push($active_module_list, $module_id);
        } elseif ($module_id && in_array($module_id, $active_module_list) && !$is_checked) {
            unset($active_module_list[array_search($module_id, $active_module_list)]);
        }
        update_option( 'mvx_all_active_module_list', $active_module_list );
        return rest_ensure_response( 'success' );
    }

    public function mvx_list_all_modules() {
        global $MVX;
        $thumbnail_dir = $MVX->plugin_url.'assets/images/modules';
        $thumbnail_path = $MVX->plugin_path.'assets/images/modules';
        require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
        $mvx_pro_is_active = is_plugin_active('mvx-pro/mvx-pro.php') ? true :false;
            $mvx_all_modules   =   [
            [
                'label' =>  __('Payment', 'dc-woocommerce-multi-vendor'),
                'options'       =>  [
                    [
                        'id'           => 'bank-transfer',
                        'name'         => __( 'Bank Transfer', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( "Manually transfer money directly to the vendor's bank account.", 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'paypal-masspay',
                        'name'         => __( 'PayPal Masspay', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Schedule payment to multiple vendors at the same time.', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                       
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'paypal-payout',
                        'name'         => __( 'PayPal Payout', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Send payments automatically to multiple vendors as per scheduled', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'paypal-marketplace',
                        'name'         => __( 'PayPal Marketplace (Real time Split)', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Using  split payment pay vendors instantly after a completed order ', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active' => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'stripe-connect',
                        'name'         => __( 'Stripe Connect', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Connect to vendors stripe account and make hassle-free transfers as scheduled.', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'stripe-marketplace',
                        'name'         => __( 'Stripe Marketplace (Real time Split)', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Real-Time Split payments pays vendor directly after a completed order', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active' => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'mangopay',
                        'name'         => __( 'Mangopay', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Gives the benefit of both realtime split transfer and scheduled distribution', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active' => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'razorpay',
                        'name'         => __( 'Razorpay', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'For clients looking to pay multiple Indian vendors instantly', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('MVX Razorpay Split Payment', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wordpress.org/plugins/mvx-razorpay-split-payment/',
                                'is_active' => is_plugin_active('mvx-razorpay-split-payment/mvx-razorpay-checkout-gateway.php') ? true :false,
                            )
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ]
                ]
            ],
            [
                'label' =>  __('Shipping', 'dc-woocommerce-multi-vendor'),
                'options'       =>  [
                    [
                        'id'           => 'vendor-shipping',
                        'name'         => __( 'Vendor Shipping', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Enable sellers to control their shipping', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'zone-wise-shipping',
                        'name'         => __( 'Zone-Wise Shipping', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Limit vendors to sell in selected zones', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                        'parent_category' => __( 'Shipping.', 'dc-woocommerce-multi-vendor' ),
                    ],
                    [
                        'id'           => 'distance-shipping',
                        'name'         => __( 'Distance Shipping', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Calculate Rates based on distance between the vendor store and drop location', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'country-wise-shipping',
                        'name'         => __( 'Country-Wise Shipping', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Let vendors choose and manage shipping, to countries of their choice', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'weight-wise-shipping',
                        'name'         => __( 'Weight Wise Shipping (using Table Rate Shipping)', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Vendors can create shipping rates based on price, weight and quantity', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'per-product-shipping',
                        'name'         => __( 'Per Product Shipping', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'let vendors add shipping cost to specific products', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('Per Product Shipping', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://woocommerce.com/products/per-product-shipping/',
                                'is_active' => is_plugin_active('woocommerce-shipping-per-product/woocommerce-shipping-per-product.php') ?true : false,
                            ),
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                ]
            ],
            [
                'label' =>  __('Vendor Store Boosters', 'dc-woocommerce-multi-vendor'),
                'options'       =>  [
                    [
                        'id'           => 'vendor-verification',
                        'name'         => __( 'Verifictaion', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Verify vendors on the basis of Id documents, Address  and Social Media Account  ', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'vendor-vacation',
                        'name'         => __( 'Vacation', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'On vacation mode, vendor can allow / disable sale & notify customer accordingly', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'business-hours',
                        'name'         => __( 'Business Hours', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Gives vendors the option to set and manage business timings', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'vendor-staff',
                        'name'         => __( 'Staff Manager', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Lets vendors hire and manage staff to support store', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'store-follow',
                        'name'         => __( 'Follow Store', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Permit customers to follow store, receive updates & lets vendors keep track of customers', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'vendor-review',
                        'name'         => __( 'Store Review', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Allows customers to rate and review stores and their purchased products', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'import-export',
                        'name'         => __( 'Product Import/Export  ', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Helps vendors seamlessly import or export product data using CSV etc', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'refund',
                        'name'         => __( 'Marketplace Refund', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Enable customer refund requests & Let vendors manage customer refund ', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'single-product-multiple-vendor',
                        'name'         => __( 'Single Product Multiple Vendor', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Lets multiple vendors sell the same products ', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'marketplace-analytics',
                        'name'         => __( 'Store Analytics', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Gives vendors detailed store report & connect to google analytics', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'store-seo',
                        'name'         => __( 'Store SEO  ', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Lets vendors manage their store SEOs using Rank Math and Yoast SEO', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'invoice-management',
                        'name'         => __( 'Invoice', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Send invoice and packaging slips to vendor', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'wholesale',
                        'name'         => __( 'Wholesale', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Set wholesale price and quantity for customers ', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'report-abuse',
                        'name'         => __( 'Report Abuse', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Lets customers report false products', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'live-chat',
                        'name'         => __( 'Live Chat', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Allows real-time messaging between vendors and customers', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'seller-subscription',
                        'name'         => __( 'Makertplace  Membership', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Lets Admin create marketplace memberships levels and manage vendor-wise individual capablity  ', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'store-location',
                        'name'         => __( 'Store Location', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( "If enabled customers can view a vendor's store location", 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'store-policy',
                        'name'         => __( 'Store Policy', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Offers vendors the option to set individual store specific policies', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                ]
            ],
            [
                'label' =>  __('Notifictaion', 'dc-woocommerce-multi-vendor'),
                'options'       =>  [
                    [
                        'id'           => 'announcement',
                        'name'         => __( 'Announcement', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'lets admin make important annoucements to vendors', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'knowladgebase',
                        'name'         => __( 'Knowladgebase', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Admin can share tutorials and othe vendor-specific information with vendors', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                ]
            ],
            [
                'label' =>  __('Marketplace Products', 'dc-woocommerce-multi-vendor'),
                'options'       =>  [
                    [
                        'id'           => 'simple',
                        'name'         => __( 'Simple (Downloadable & Virtual)', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Covers the vast majority of any tangible products you may sell or ship i.e books', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                        'parent_category' => __( 'Marketplace Products.', 'dc-woocommerce-multi-vendor' ),
                    ],
                    [
                        'id'           => 'Variable',
                        'name'         => __( 'Variable', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'A product with variations, like different SKU, price, stock option, etc.', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'External',
                        'name'         => __( 'External', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Grants vendor the option to  list and describe on admin website but sold elsewhere', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'grouped',
                        'name'         => __( 'Grouped', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'A cluster of simple related products that can be purchased individually', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'booking',
                        'name'         => __( 'Booking', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Allow customers to book appointments, make reservations or rent equipment etc', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WooCommerce Booking', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://woocommerce.com/products/woocommerce-bookings/',
                                'is_active' => is_plugin_active('woocommerce-bookings/woocommerce-bookings.php') ? true :false,
                            ),
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'subscription',
                        'name'         => __( 'Subscription', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Let customers subscribe to your products or services and pay weekly, monthly or yearly ', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WooCommerce Subscription', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://woocommerce.com/products/woocommerce-subscriptions/',
                                'is_active' => is_plugin_active('woocommerce-subscriptions/woocommerce-subscriptions.php') ? true : false,
                            ),
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'accommodation',
                        'name'         => __( 'Accommodation', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Grant your guests the ability to quickly book overnight stays in a few clicks', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WooCommerce Accommodation & Booking', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://woocommerce.com/products/woocommerce-accommodation-bookings/',
                                'is_active' => is_plugin_active('woocommerce-bookings/woocommerce-bookings.php') && is_plugin_active('woocommerce-accommodation-bookings/woocommerce-accommodation-bookings.php') ? true : false,
                            ),
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'product-bundle',
                        'name'         => __( 'Bundle', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Offer personalized product bundles, bulk discount packages, and assembled products.', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('Product Bundle', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://woocommerce.com/products/product-bundles/',
                                'is_active' => is_plugin_active('woocommerce-product-bundles/woocommerce-product-bundles.php') ? true : false,
                            ),
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'simple-auction',
                        'name'         => __( 'Auction', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Implement an auction system similar to eBay on your store', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('Simple Auction', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => '',
                                'is_active' => is_plugin_active('woocommerce-simple-auctions/woocommerce-simple-auctions.php') ? true : false,
                            ),
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'rental-pro',
                        'name'         => __( 'Rental', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Perfect for those desiring to offer rental, booking, or real state agencies or services.', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('AffiliateWP', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://woocommerce.com/products/rental-products/',
                                'is_active' => is_plugin_active('woocommerce-rental-and-booking/redq-rental-and-bookings.php') ? true : false,
                            ),
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                ]
            ],
            [
                'label' =>  __('Third Party Compartibility', 'dc-woocommerce-multi-vendor'),
                'options'       =>  [
                    [
                        'id'           => 'elementor',
                        'name'         => __( 'Elementor', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Create Sellers Pages using Elementors drag and drop feature ', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('Elementor Website Builder', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wordpress.org/plugins/elementor/',
                                'is_active' => is_plugin_active('elementor/elementor.php') ? true :false,
                            ),
                            array(
                                'plugin_name'   => __('Elementor Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://elementor.com/pricing/',
                                'is_active' => is_plugin_active('elementor-pro/elementor-pro.php') ? true : false,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                        'parent_category' => __( 'Third Party Compartibility', 'dc-woocommerce-multi-vendor' ),
                    ],
                    [
                        'id'           => 'buddypress',
                        'name'         => __( 'Buddypress', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Allows stores to have a social networking feature', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('Buddypress', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wordpress.org/plugins/buddypress/',
                                'is_active' => is_plugin_active('buddypress/bp-loader.php') ? true : false,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'wpml',
                        'name'         => __( 'WPML', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Gives vendors the option of selling their product in different languages', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('The WordPress Multilingual Plugin', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wpml.org/',
                                'is_active' => class_exists( 'SitePress' ) ? true : false,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'acf',
                        'name'         => __( 'Advance Custom field', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Allows for an on demand product field in Add Product section', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('Advanced custom fields', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wordpress.org/plugins/advanced-custom-fields/',
                                'is_active' => class_exists('ACF') ? true : false,
                            ),
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                        'category'  => 'store boosters',
                    ],
                    [
                        'id'           => 'geo-my-wp',
                        'name'         => __( 'GEOmyWP', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Offer vendor the option to attach location info along with their products', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('Geo My wp', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wordpress.org/plugins/geo-my-wp/',
                                'is_active' => is_plugin_active('geo-my-wp/geo-my-wp.php') ? true :false,
                            ),
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'toolset',
                        'name'         => __( 'Toolset Types', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( "Allows admin to create custom fields, and taxonomy for vendor's product field", 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('Toolset', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://toolset.com/',
                                'is_active' => is_plugin_active('types/wpcf.php') ? true :false,
                            ),
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'affiliate',
                        'name'         => __( 'WP Affiliate', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Launch affiliate programme into your marketplace', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('AffiliateWP', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://affiliatewp.com/',
                                'is_active' => is_plugin_active('affiliate-wp/affiliate-wp.php') ? true :false,
                            ),
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'product-addons',
                        'name'         => __( 'Product Addon', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Offer add-ons like gift wrapping, special messages etc along with primary products', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('Product Add-Ons', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://woocommerce.com/products/product-add-ons/',
                                'is_active' => is_plugin_active('woocommerce-product-addons/woocommerce-product-addons.php') ? true :false,
                            ),
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                ]
            ],
        ];
        
        if ($mvx_all_modules) {
            foreach ($mvx_all_modules as $parent_module_key => $parent_module_value) {
                if (isset($parent_module_value['options']) && !empty($parent_module_value['options'])) {
                    foreach ($parent_module_value['options'] as $module_key => $module_value) {
                        $mvx_all_modules[$parent_module_key]['options'][$module_key]['is_active'] = $this->is_current_module_active($module_value['id']);
                        $mvx_all_modules[$parent_module_key]['options'][$module_key]['thumbnail_dir'] = file_exists($thumbnail_path . '/'.$module_value['id'].'.png') ? $thumbnail_dir . '/'. $module_value['id'].'.png' : '';

                        if (isset($mvx_all_modules[$parent_module_key]['options'][$module_key]['required_plugin_list']) && !empty($mvx_all_modules[$parent_module_key]['options'][$module_key]['required_plugin_list'])) {

                            foreach ($mvx_all_modules[$parent_module_key]['options'][$module_key]['required_plugin_list'] as $req_key => $req_value) {
                                $is_required_plugin_active = $req_value['is_active'] ? $req_value['is_active'] : false;
                            }
                            $mvx_all_modules[$parent_module_key]['options'][$module_key]['is_required_plugin_active'] = $is_required_plugin_active;
                        } else {
                            $mvx_all_modules[$parent_module_key]['options'][$module_key]['is_required_plugin_active'] = true;
                        }
                    }
                }
            }
        }
        return apply_filters('mvx_list_modules', $mvx_all_modules);
    }

    public function is_current_module_active($module_name) {
        $is_module_active = get_option('mvx_all_active_module_list', true);
        $is_active = $is_module_active && is_array($is_module_active) && in_array($module_name, $is_module_active) ? true : false;
        return $is_active;
    }

}
