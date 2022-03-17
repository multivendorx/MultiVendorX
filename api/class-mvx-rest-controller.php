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

        register_rest_route( 'mvx_module/v1', '/product_list_option', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_product_list_option' ),
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

        register_rest_route( 'mvx_module/v1', '/export_csv_for_report_product_chart', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'mvx_export_csv_for_report_product_chart' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/export_csv_for_report_vendor_chart', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'mvx_export_csv_for_report_vendor_chart' ),
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


        register_rest_route( 'mvx_module/v1', '/get_report_overview_data', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'mvx_get_report_overview_data' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/fetch_report_overview_data', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_fetch_report_overview_data' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/create_announcement', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'mvx_create_announcement' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/display_announcement', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_display_announcement' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/update_announcement_display', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_update_announcement_display' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );


        register_rest_route( 'mvx_module/v1', '/update_custom_post_status', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'mvx_update_custom_post_status' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/update_announcement', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'mvx_update_announcement' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        // knowledgebase
        register_rest_route( 'mvx_module/v1', '/create_knowladgebase', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'mvx_create_knowladgebase' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/display_list_knowladgebase', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_display_list_knowladgebase' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/update_knowladgebase_display', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_update_knowladgebase_display' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/update_knowladgebase', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'mvx_update_knowladgebase' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        register_rest_route( 'mvx_module/v1', '/list_of_all_tab_based_settings_field', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_list_of_all_tab_based_settings_field' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        // pending product
        register_rest_route( 'mvx_module/v1', '/list_of_pending_vendor_product', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_list_of_pending_vendor_product' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );
        // pending vendor
        register_rest_route( 'mvx_module/v1', '/list_of_pending_vendor', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_list_of_pending_vendor' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );
        // pending coupon
        register_rest_route( 'mvx_module/v1', '/list_of_pending_vendor_coupon', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_list_of_pending_vendor_coupon' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );
        // pending transaction
        register_rest_route( 'mvx_module/v1', '/list_of_pending_transaction', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_list_of_pending_transaction' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );
        // pending Question
        register_rest_route( 'mvx_module/v1', '/list_of_pending_question', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_list_of_pending_question' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        // store review
        register_rest_route( 'mvx_module/v1', '/list_of_store_review', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'mvx_list_of_store_review' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );


        // store review
        register_rest_route( 'mvx_module/v1', '/search_announcement', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'mvx_search_announcement' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

        // delete post
        register_rest_route( 'mvx_module/v1', '/delete_post_details', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'mvx_delete_post_details' ),
            'permission_callback' => array( $this, 'save_settings_permission' )
        ] );

    }

    public function mvx_delete_post_details($request) {
        $ids = $request && $request->get_param('ids') ? $request->get_param('ids') : 0;
        wp_delete_post($ids);
        return $this->mvx_display_announcement();
    }

    public function mvx_update_custom_post_status($request) {
        $ids = $request && $request->get_param('ids') ? wp_list_pluck($request->get_param('ids'), 'id') : 0;
        $value = $request && $request->get_param('value') ? ($request->get_param('value')) : 0;
        foreach ($ids as $key_id => $value_id) {
            $post_update = array(
                'ID'           => $value_id,
                'post_status' => $value,
            );
            wp_update_post( $post_update );
        }
    }

    public function mvx_search_announcement($request) {
        $ids = $request && $request->get_param('ids') ? ($request->get_param('ids')) : 0;
        $value = $request && $request->get_param('value') ? ($request->get_param('value')) : 0;
        $all_announcement   =   $this->mvx_display_announcement();
        $search_announcement_renew = [];
        if ($all_announcement->data && !empty($all_announcement->data) && !empty($value)) {
            foreach ($all_announcement->data as $announce_key => $anounce_value) {
                if (strpos($anounce_value['sample_title'], $value) !== false) {
                    $search_announcement_renew[]    =   $all_announcement->data[$announce_key];
                }
            }            
        } else {
            return rest_ensure_response($all_announcement->data);
        }
        return rest_ensure_response($search_announcement_renew);
        
    }

    public function mvx_list_of_store_review() {
        $review_list = array();
        $mvx_vendor_reviews = array();
        $args_default = array(
                'status' => 'approve',
                'type' => 'mvx_vendor_rating',
                'count' => false,
                'posts_per_page' => -1,
                'offset' => 0,
            );
        $mvx_vendor_reviews = get_comments($args_default);
        if ($mvx_vendor_reviews) {
            foreach ($mvx_vendor_reviews as $mvx_vendor_review) {
                $comment_vendor_id = get_comment_meta($mvx_vendor_review->comment_ID, 'vendor_rating_id', true);
                $vendor = get_mvx_vendor($comment_vendor_id);
                $vendor_term_id = get_user_meta($comment_vendor_id, '_vendor_term_id', true);
                $rating_val_array = mvx_get_vendor_review_info($vendor_term_id);
                $rating = round($rating_val_array['avg_rating'], 1);
                $review = '<span itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating" class="star-rating" title='. sprintf(__('Rated %s out of 5', 'dc-woocommerce-multi-vendor'), $rating). '>
                        <span style="width: ' . ( round($rating_val_array['avg_rating']) / 5 ) * 100 .'%"><strong itemprop="ratingValue">'. ($rating). '</strong> '. ('out of 5').'</span>
                    </span>';

                $review_list[] = apply_filters('mvx_list_table_reviews_columns_data', array(
                    'id'        => $mvx_vendor_review->comment_ID,
                    'author'    => $mvx_vendor_review->comment_author,
                    'user_id'   => $vendor->page_title,
                    'time'      => human_time_diff(strtotime($mvx_vendor_review->comment_date)),
                    'content'   => $mvx_vendor_review->comment_content,
                    'review'    => $review
                ), $mvx_vendor_review);
            }
        }

        return rest_ensure_response($review_list);
    }

    public function mvx_list_of_pending_vendor_product() {
        $pending_list = [];
        $vendor_ids = get_mvx_vendors(array(), 'ids');
        $args = array(
            'posts_per_page' => -1,
            'author__in' => $vendor_ids,
            'post_type' => 'product',
            'post_status' => 'pending',
        );
        $get_pending_products = new WP_Query($args);
        $get_pending_products = $get_pending_products->get_posts();

        if (!empty($get_pending_products)) {
            foreach ($get_pending_products as $get_pending_product) {
                $currentvendor = get_mvx_vendor($get_pending_product->post_author);
                $vendor_term = get_term($currentvendor->term_id);
                $pending_list[] = array(
                    'id'        =>  $get_pending_product->ID,
                    'vendor'    =>  $vendor_term->name,
                    'product'   =>  $get_pending_product->post_title,
                );
            }
        }
        return rest_ensure_response($pending_list);
    }
    public function mvx_list_of_pending_vendor() {
        $pending_list = [];
        $get_pending_vendors = get_users('role=dc_pending_vendor');
        if (!empty($get_pending_vendors)) {
            foreach ($get_pending_vendors as $pending_vendor) {
                $dismiss = get_user_meta($pending_vendor->ID, '_dismiss_to_do_list', true);
                if ($dismiss)   continue;
                $pending_list[] = array(
                    'id'        =>  $pending_vendor->ID,
                    'vendor'    =>  $pending_vendor->user_login,
                );
            }
        }
        return rest_ensure_response($pending_list);
    }
    public function mvx_list_of_pending_vendor_coupon() {
        $pending_list = [];
        $vendor_ids = get_mvx_vendors(array(), 'ids');
        $args = array(
            'posts_per_page' => -1,
            'author__in' => $vendor_ids,
            'post_type' => 'shop_coupon',
            'post_status' => 'pending',
        );
        $get_pending_coupons = new WP_Query($args);
        $get_pending_coupons = $get_pending_coupons->get_posts();
        if (!empty($get_pending_coupons)) {
            foreach ($get_pending_coupons as $get_pending_coupon) {
                $dismiss = get_post_meta($get_pending_coupon->ID, '_dismiss_to_do_list', true);
                if ($dismiss)   continue;
                $currentvendor = get_mvx_vendor($get_pending_coupon->post_author);
                $vendor_term = get_term($currentvendor->term_id);
                $pending_list[] = array(
                    'id'        =>  $currentvendor->ID,
                    'vendor'    =>  $vendor_term->name,
                    'coupon'    =>  $get_pending_coupon->post_title,

                );
            }
        }
        return rest_ensure_response($pending_list);
    }
    public function mvx_list_of_pending_transaction() {
        $pending_list = [];
        $args = array(
            'post_type' => 'mvx_transaction',
            'post_status' => 'mvx_processing',
            'meta_key' => 'transaction_mode',
            'meta_value' => 'direct_bank',
            'posts_per_page' => -1
        );
        $transactions = get_posts($args);

        if (!empty($transactions)) {
            foreach ($transactions as $transaction) {
                $dismiss = get_post_meta($transaction->ID, '_dismiss_to_do_list', true);
                $vendor_term_id = $transaction->post_author;
                $currentvendor = get_mvx_vendor_by_term($vendor_term_id);
                $vendor_term = get_term($vendor_term_id);
                if ($dismiss || !$currentvendor) {
                    continue;
                }
                $account_name = get_user_meta($currentvendor->id, '_vendor_account_holder_name', true);
                $account_no = get_user_meta($currentvendor->id, '_vendor_bank_account_number', true);
                $bank_name = get_user_meta($currentvendor->id, '_vendor_bank_name', true);
                $iban = get_user_meta($currentvendor->id, '_vendor_iban', true);
                $amount = get_post_meta($transaction->ID, 'amount', true) - get_post_meta($transaction->ID, 'transfer_charge', true) - get_post_meta($transaction->ID, 'gateway_charge', true);
                $address_array = apply_filters('mvx_todo_pending_bank_transfer_row_account_details_data', array(
                __('Account Name-', 'dc-woocommerce-multi-vendor') . ' ' . $account_name,
                __('Account No -', 'dc-woocommerce-multi-vendor') . ' ' . $account_no,
                __('Bank Name -', 'dc-woocommerce-multi-vendor') . ' ' . $bank_name,
                __('IBAN -', 'dc-woocommerce-multi-vendor') . ' ' . $iban,
                    ), $currentvendor, $transaction);

                $pending_list[] = array(
                    'id'        =>  $currentvendor->id,
                    'vendor'    =>  $vendor_term->name,
                    'commission'    =>  $transaction->post_title,
                    'amount'    =>  $amount,
                    'account_details'   =>  implode('<br/>', $address_array)
                );
            }
        }
        return rest_ensure_response($pending_list);
    }

    public function mvx_list_of_pending_question() {
        global $MVX;
        $vendor_ids = get_mvx_vendors(array(), 'ids');
        $pending_list = [];
        $args = array(
            'posts_per_page' => -1,
            'author__in' => $vendor_ids,
            'post_type' => 'product',
            'post_status' => 'publish',
        );
        $get_vendor_products = new WP_Query($args);
        $get_vendor_products = $get_vendor_products->get_posts();
        if (!empty($get_vendor_products) && apply_filters('admin_can_approve_qna_answer', true)) {
            foreach ($get_vendor_products as $get_vendor_product) {
                $get_pending_questions = $MVX->product_qna->get_Pending_Questions($get_vendor_product->ID);
                if (!empty($get_pending_questions)) {
                    foreach ($get_pending_questions as $pending_question) {
                        $question_by = get_userdata($pending_question->ques_by);
                        $question_by = "<img src=' " . $MVX->plugin_url . 'assets/images/wp-avatar-frau.jpg' ."' class='avatar avatar-32 photo' height='32' width='32'>" .$question_by->data->display_name . "";
                        $pending_list[] = array(
                            //'id'        =>  $currentvendor->id,
                            'question_by'           =>  $question_by,
                            'product_name'          =>  get_the_title($pending_question->product_ID),
                            'question_details'      =>  $pending_question->ques_details,
                        );
                    }   
                }
            }
        }
        return rest_ensure_response($pending_list);
    }

    public function mvx_create_knowladgebase($request) {
        $fetch_data = $request->get_param('model');
        $knowladgebase_title = $fetch_data && isset($fetch_data['knowladgebase_title']) ? $fetch_data['knowladgebase_title'] : '';
        $knowladgebase_content = $fetch_data && isset($fetch_data['knowladgebase_content']) ? $fetch_data['knowladgebase_content'] : '';
        wp_insert_post( array( 'post_title' => $knowladgebase_title, 'post_type' => 'mvx_university', 'post_status' => 'publish', 'post_content' => $knowladgebase_content ) );
    }
    public function mvx_display_list_knowladgebase($request) {
        $announcement_list = array();
        $args = array(
            'post_type' => 'mvx_university',
            'post_status' => array('publish', 'auto-draft'),
            'posts_per_page' => -1,
        );
        $knowladgebase = get_posts($args);

        foreach ($knowladgebase as $knowladgebasekey => $knowladgebasevalue) {
            $knowladgebase_list[] = array(
                'id'            =>  $knowladgebasevalue->ID,
                'title'         =>  '<a href="' . sprintf('?page=%s&name=%s&knowladgebaseID=%s', 'mvx#&submenu=work-board', 'knowladgebase', $knowladgebasevalue->ID) . '">#' . $knowladgebasevalue->post_title . '</a>',
                'date'          =>  $knowladgebasevalue->post_modified,
            );
        }
        return rest_ensure_response($knowladgebase_list);
    }
    public function mvx_update_knowladgebase_display($request) {
        global $MVX;
        $knowladgebase_id = $request && $request->get_param('knowladgebase_id') ? ($request->get_param('knowladgebase_id')) : 0;
        $knowladgebase_fields_data = mvx_admin_backend_settings_fields_details();
        if ($knowladgebase_id && absint($knowladgebase_id) > 0) {

            $post_details = get_post($knowladgebase_id);
            $knowladgebase_title =   $post_details->post_title;
            $knowladgebase_content   =   $post_details->post_content;
        }

        $knowladgebase_fields_data['update_knowladgebase_display'] = [
            [
                'key'       => 'knowladgebase_title',
                'type'      => 'text',
                'label'     => __( 'Title (required)', 'dc-woocommerce-multi-vendor' ),
                'props'     => array(
                    'required'  => true
                ),
                'database_value' => isset($knowladgebase_title) ? $knowladgebase_title : '',
            ],
            [
                'label' => __('Enter Content', 'dc-woocommerce-multi-vendor'),
                'type' => 'textarea', 
                'key' => 'knowladgebase_content', 
                'database_value' => $knowladgebase_content
            ]
        ];

        return rest_ensure_response($knowladgebase_fields_data);
    }
    public function mvx_update_knowladgebase($request) {
        $knowladgebase_id = $request && $request->get_param('knowladgebase_id') ? ($request->get_param('knowladgebase_id')) : 0;
        $fetch_data = $request->get_param('model');
        
        $knowladgebase_post = array(
            'ID'    =>  $knowladgebase_id,
        );

        if (isset($fetch_data['knowladgebase_title']) && !empty($fetch_data['knowladgebase_title'])) {
            $knowladgebase_post['post_title'] = $fetch_data['knowladgebase_title'];
        }
        if (isset($fetch_data['knowladgebase_content']) && !empty($fetch_data['knowladgebase_content'])) {
            $knowladgebase_post['post_content'] = $fetch_data['knowladgebase_content'];
        }
        // Update the post into the database
        wp_update_post( $knowladgebase_post );
    }


    // announcement

    public function mvx_update_announcement($request) {
        $announcement_id = $request && $request->get_param('announcement_id') ? ($request->get_param('announcement_id')) : 0;
        $fetch_data = $request->get_param('model');
        
        $announcement_post = array(
            'ID'    =>  $announcement_id,
        );

        if (isset($fetch_data['announcement_title']) && !empty($fetch_data['announcement_title'])) {
            $announcement_post['post_title'] = $fetch_data['announcement_title'];
        }
        if (isset($fetch_data['announcement_content']) && !empty($fetch_data['announcement_content'])) {
            $announcement_post['post_content'] = $fetch_data['announcement_content'];
        }
        // Update the post into the database
        wp_update_post( $announcement_post );

        if (isset($fetch_data['announcement_url']) && !empty($fetch_data['announcement_url'])) {
            update_post_meta($announcement_id, '_mvx_vendor_notices_url', $fetch_data['announcement_url']);
        }
        if (isset($fetch_data['announcement_vendors'])) {
            $notify_vendors = isset($fetch_data['announcement_vendors']) ? wp_list_pluck(array_filter($fetch_data['announcement_vendors']), 'value')  : get_mvx_vendors( array(), 'ids' );
            update_post_meta($announcement_id, '_mvx_vendor_notices_vendors', $notify_vendors);
        }
    }

    public function mvx_update_announcement_display($request) {
        global $MVX;
        $announcement_id = $request && $request->get_param('announcement_id') ? ($request->get_param('announcement_id')) : 0;
        $announcement_fields_data = mvx_admin_backend_settings_fields_details();
        $show_anouncemnt_vendor = [];
        if ($announcement_id && absint($announcement_id) > 0) {
            $post_details = get_post($announcement_id);
            $announcement_title =   $post_details->post_title;
            $announcement_url   =   get_post_meta($post_details->ID, '_mvx_vendor_notices_url', true) ? get_post_meta($post_details->ID, '_mvx_vendor_notices_url', true) : '';
            $announcement_content   =   $post_details->post_content;
            $announcement_vendors   =   get_post_meta($post_details->ID, '_mvx_vendor_notices_vendors', true) ? get_post_meta($post_details->ID, '_mvx_vendor_notices_vendors', true) : '';

            if ($this->mvx_show_vendor_name()->data && is_array($announcement_vendors)) {
                foreach ($this->mvx_show_vendor_name()->data as $announcement_key => $announcement_value) {
                    if ($announcement_value['value'] && in_array($announcement_value['value'], $announcement_vendors)) {
                        $show_anouncemnt_vendor[]  = $this->mvx_show_vendor_name()->data[$announcement_key];
                    }
                }
            }
        }

        $announcement_fields_data['update_announcement_display'] = [
            [
                'key'       => 'announcement_title',
                'type'      => 'text',
                'label'     => __( 'Title (required)', 'dc-woocommerce-multi-vendor' ),
                'props'     => array(
                    'required'  => true
                ),
                'database_value' => isset($announcement_title) ? $announcement_title : '',
            ],
            [
                'key'       => 'announcement_url',
                'type'      => 'url',
                'label'     => __( 'Enter Url', 'dc-woocommerce-multi-vendor' ),
                'props'     => array(
                ),
                'database_value' => isset($announcement_url) ? $announcement_url : '',
            ],
            [
                'label' => __('Enter Content', 'dc-woocommerce-multi-vendor'),
                'type' => 'textarea', 
                'key' => 'announcement_content', 
                'database_value' => $announcement_content
            ],
            [
                'key'       => 'announcement_vendors',
                'type'      => 'multi-select',
                'label'     => __( 'Vendors', 'dc-woocommerce-multi-vendor' ),
                'options'   => ($MVX->vendor_rest_api->mvx_show_vendor_name()->data),
                'database_value' => isset($show_anouncemnt_vendor) ? $show_anouncemnt_vendor : '',
            ],
        ];

        return rest_ensure_response($announcement_fields_data);
    }

    public function mvx_list_of_all_tab_based_settings_field($request) {
        global $MVX;
        $vendor_id = $request && $request->get_param('vendor_id') ? ($request->get_param('vendor_id')) : 0;

        $woo_countries = new WC_Countries();
        $countries = $woo_countries->get_allowed_countries();
        $country_list = [];
        foreach ($countries as $countries_key => $countries_value) {
            $country_list[] = array(
                'lebel' => $countries_key,
                'value' => $countries_value
            );
        }

        // Find MVX created pages
        $pages = get_pages();
        $woocommerce_pages = array(wc_get_page_id('shop'), wc_get_page_id('cart'), wc_get_page_id('checkout'), wc_get_page_id('myaccount'));
        $pages_array = array();
        if($pages){
            foreach ($pages as $page) {
                if (!in_array($page->ID, $woocommerce_pages)) {
                    $pages_array[] = array(
                        'value'=> $page->ID,
                        'label'=> $page->post_title,
                        'key'=> $page->ID,
                    );
                }
            }
        }

        $default_nested_data = array(
            array(
                'nested_datas'  => array(
                    (Object)[]
                )
            )
        );

        $woo_countries = new WC_Countries();
        $countries = $woo_countries->get_allowed_countries();
        $country_list = [];
        foreach ($countries as $countries_key => $countries_value) {
            $country_list[] = array(
                'label' => $countries_value,
                'value' => $countries_key
            );
        }



        //vendor_country_code
        //vendor_state_code

        $user = null;
        $mvx_shipping_by_distance = $mvx_shipping_by_country = $vendor_default_shipping_options = '';
        $display_name_option = $shipping_options_list = $showdisplayname = $showpayment_method = array();

        if(isset($vendor_id) && absint($vendor_id) > 0) {
            $user = get_user_by("ID", $vendor_id);
                        
            // display name for vendor start

            if(isset($user->display_name)) {
                if ($user->user_login) {
                    $display_name_option[] = array(
                        'value'=> $user->user_login,
                        'label'=> $user->user_login,
                        'key'=> $user->user_login,
                    );
                }
                if ($user->first_name) {
                    $display_name_option[] = array(
                        'value'=> $user->first_name,
                        'label'=> $user->first_name,
                        'key'=> $user->first_name,
                    );
                }
                if ($user->last_name) {
                    $display_name_option[] = array(
                        'value'=> $user->last_name,
                        'label'=> $user->last_name,
                        'key'=> $user->last_name,
                    );
                }

                if ($user->first_name && $user->last_name) {
                    $display_name_option[] = array(
                        'value'=> $user->first_name . " " . $user->last_name,
                        'label'=> $user->first_name . " " . $user->last_name,
                        'key'=> $user->first_name . " " . $user->last_name,
                    );
                     $display_name_option[] = array(
                        'value'=> $user->last_name . " " . $user->first_name,
                        'label'=> $user->last_name . " " . $user->first_name,
                        'key'=> $user->last_name . " " . $user->first_name,
                    );
                }
            }

            foreach ($display_name_option as $display_key => $display_value) {
                if ($display_value['value'] && $display_value['value'] == $user->display_name) {
                    $showdisplayname[]  = $display_name_option[$display_key];
                }
            }


            // set option vendor payment method
            $payment_admin_settings = get_option('mvx_commission-configuration_tab_settings');
            $payment_mode = array('payment_mode' => __('Payment Mode', 'dc-woocommerce-multi-vendor'));
            if ($payment_admin_settings && isset($payment_admin_settings['payment_method_disbursement']) && !empty($payment_admin_settings['payment_method_disbursement']) && in_array('paypal_masspay', $payment_admin_settings['payment_method_disbursement'])) {
                $payment_mode['paypal_masspay'] = __('PayPal Masspay', 'dc-woocommerce-multi-vendor');
            }
            if ($payment_admin_settings && isset($payment_admin_settings['payment_method_disbursement']) && !empty($payment_admin_settings['payment_method_disbursement']) && in_array('paypal_payout', $payment_admin_settings['payment_method_disbursement'])) {
                $payment_mode['paypal_payout'] = __('PayPal Payout', 'dc-woocommerce-multi-vendor');
            }
            if ($payment_admin_settings && isset($payment_admin_settings['payment_method_disbursement']) && !empty($payment_admin_settings['payment_method_disbursement']) && in_array('stripe_masspay', $payment_admin_settings['payment_method_disbursement'])) {
                $payment_mode['stripe_masspay'] = __('Stripe Connect', 'dc-woocommerce-multi-vendor');
            }
            if ($payment_admin_settings && isset($payment_admin_settings['payment_method_disbursement']) && !empty($payment_admin_settings['payment_method_disbursement']) && in_array('direct_bank', $payment_admin_settings['payment_method_disbursement'])) {
                $payment_mode['direct_bank'] = __('Direct Bank', 'dc-woocommerce-multi-vendor');
            }
            $vendor_payment_mode_select = apply_filters('mvx_vendor_payment_mode', $payment_mode);
            $vendor_payment_method_display_section  =   array();
            foreach ($vendor_payment_mode_select as $selectkey => $selectvalue) {
                $vendor_payment_method_display_section[]    =   array(
                    'label' =>  $selectvalue,
                    'value' =>  $selectkey
                );
            }

            $payment_method = get_user_meta($vendor_id, '_vendor_payment_mode', true);
            foreach ($vendor_payment_method_display_section as $payment_key => $payment_value) {
                if ($payment_value['value'] && $payment_value['value'] == $payment_method) {
                    $showpayment_method  = $vendor_payment_method_display_section[$payment_key];
                }
            }

            $commission_value = get_user_meta($vendor_id, '_vendor_commission', true);
            $vendor_paypal_email = get_user_meta($vendor_id, '_vendor_paypal_email', true);

            $vendor_bank_name = get_user_meta($vendor_id, '_vendor_bank_name', true);
            $vendor_aba_routing_number = get_user_meta($vendor_id, '_vendor_aba_routing_number', true);
            $vendor_destination_currency = get_user_meta($vendor_id, '_vendor_destination_currency', true);
            $vendor_bank_address = get_user_meta($vendor_id, '_vendor_bank_address', true);
            $vendor_iban = get_user_meta($vendor_id, '_vendor_iban', true);
            $vendor_account_holder_name = get_user_meta($vendor_id, '_vendor_account_holder_name', true);
            $vendor_bank_account_number = get_user_meta($vendor_id, '_vendor_bank_account_number', true);


            $_vendor_shipping_policy = get_user_meta( $user->data->ID, 'vendor_shipping_policy', true ) ? get_user_meta( $user->data->ID, 'vendor_shipping_policy', true ) : __( 'No policy found', 'dc-woocommerce-multi-vendor' );
            $_vendor_refund_policy = get_user_meta( $user->data->ID, 'vendor_refund_policy', true ) ? get_user_meta( $user->data->ID, 'vendor_refund_policy', true ) : __( 'No policy found', 'dc-woocommerce-multi-vendor' );
            $_vendor_cancellation_policy = get_user_meta( $user->data->ID, 'vendor_cancellation_policy', true ) ? get_user_meta( $user->data->ID, 'vendor_cancellation_policy', true ) : __( 'No policy found', 'dc-woocommerce-multi-vendor' );


            $vendor_phone = get_user_meta( $user->data->ID, '_vendor_phone', true ) ? get_user_meta( $user->data->ID, '_vendor_phone', true ) : '';
            $vendor_address_1 = get_user_meta( $user->data->ID, '_vendor_address_1', true ) ? get_user_meta( $user->data->ID, '_vendor_address_1', true ) : '';
            $vendor_address_2 = get_user_meta( $user->data->ID, '_vendor_address_2', true ) ? get_user_meta( $user->data->ID, '_vendor_address_2', true ) : '';
            $vendor_city = get_user_meta( $user->data->ID, '_vendor_city', true ) ? get_user_meta( $user->data->ID, '_vendor_city', true ) : '';
            $vendor_postcode = get_user_meta( $user->data->ID, '_vendor_postcode', true ) ? get_user_meta( $user->data->ID, '_vendor_postcode', true ) : '';




            $vendor_country_code = get_user_meta( $user->data->ID, '_vendor_country_code', true ) ? get_user_meta( $user->data->ID, '_vendor_country_code', true ) : '';
            $vendor_state_code = get_user_meta( $user->data->ID, '_vendor_state_code', true ) ? get_user_meta( $user->data->ID, '_vendor_state_code', true ) : '';

            // display country value from database
            $vendor_country_code_data = $vendor_state_code_data = array();
            foreach ($country_list as $display_country_key => $display_country_value) {
                if ($display_country_value['value'] && $display_country_value['value'] == $vendor_country_code) {
                    $vendor_country_code_data[]  = $country_list[$display_country_key];
                }
            }

            // display state value from database
            $state_list = wc_clean( wp_unslash( WC()->countries->get_states($vendor_country_code) ) );
            if ($state_list) {
                foreach ($state_list as $display_state_key => $display_state_value) {
                    if ($display_state_key && $display_state_key == $vendor_state_code) {
                        $vendor_state_code_data[]  = [
                            'label' => $display_state_value,
                            'value' => $display_state_key
                        ];
                    }
                }
            }
            

            $user_vendor = get_mvx_vendor($user->data->ID);

            $current_offset = get_user_meta($user->data->ID, 'gmt_offset', true);
            $tzstring = get_user_meta($user->data->ID, 'timezone_string', true);
            // Remove old Etc mappings. Fallback to gmt_offset.
            if (false !== strpos($tzstring, 'Etc/GMT')) {
                $tzstring = '';
            }


            $vendor_fb_profile = get_user_meta($user->data->ID, '_vendor_fb_profile', true) ? get_user_meta($user->data->ID, '_vendor_fb_profile', true) : '';
            $vendor_twitter_profile = get_user_meta($user->data->ID, '_vendor_twitter_profile', true) ? get_user_meta($user->data->ID, '_vendor_twitter_profile', true) : '';
            $vendor_linkdin_profile = get_user_meta($user->data->ID, '_vendor_linkdin_profile', true) ? get_user_meta($user->data->ID, '_vendor_linkdin_profile', true) : '';
            $vendor_youtube_profile = get_user_meta($user->data->ID, '_vendor_youtube', true) ? get_user_meta($user->data->ID, '_vendor_youtube', true) : '';
            $vendor_instagram_profile = get_user_meta($user->data->ID, '_vendor_instagram', true) ? get_user_meta($user->data->ID, '_vendor_instagram', true) : '';

            if (empty($tzstring)) { // Create a UTC+- zone if no timezone string exists
                $check_zone_info = false;
                if (0 == $current_offset) {
                    $tzstring = 'UTC+0';
                } elseif ($current_offset < 0) {
                    $tzstring = 'UTC' . $current_offset;
                } else {
                    $tzstring = 'UTC+' . $current_offset;
                }
            }

            // Shipping options
            
            $vendor_default_shipping_options_database_value = get_user_meta($vendor_id, 'vendor_shipping_options', true) ? get_user_meta($vendor_id, 'vendor_shipping_options', true) : '';
            $shipping_options = apply_filters('mvx_vendor_shipping_option_to_vendor', array(
                'distance_by_zone' => __('Shipping by Zone', 'dc-woocommerce-multi-vendor'),
            ) );
            if (get_mvx_vendor_settings( 'enabled_distance_by_shipping_for_vendor', 'general' ) && 'Enable' === get_mvx_vendor_settings( 'enabled_distance_by_shipping_for_vendor', 'general' )) {
                $shipping_options['distance_by_shipping'] = __('Shipping by Distance', 'dc-woocommerce-multi-vendor');
            }
            if (get_mvx_vendor_settings( 'enabled_shipping_by_country_for_vendor', 'general' ) && 'Enable' === get_mvx_vendor_settings( 'enabled_shipping_by_country_for_vendor', 'general' )) {
                $shipping_options['shipping_by_country'] = __('Shipping by Country', 'dc-woocommerce-multi-vendor');
            }
            foreach ($shipping_options as $shipping_key => $shipping_value) {
                $shipping_options_list[] = array(
                    'value' => sanitize_text_field($shipping_key),
                    'label' => sanitize_text_field($shipping_value)
                );
            }

            $vendor_default_shipping_options = array();
            foreach ($shipping_options_list as $key => $value) {
                if ($value['value'] == $vendor_default_shipping_options_database_value) {
                    $vendor_default_shipping_options[] = $shipping_options_list[$key];
                }
            }

            $shipping_distance_rate = mvx_get_user_meta( $vendor_id, '_mvx_shipping_by_distance_rates', true ) ? mvx_get_user_meta( $vendor_id, '_mvx_shipping_by_distance_rates', true ) : $default_nested_data;

            $mvx_shipping_by_distance = mvx_get_user_meta( $vendor_id, '_mvx_shipping_by_distance', true ) ? get_user_meta( $vendor_id, '_mvx_shipping_by_distance', true ) : array();

            $mvx_shipping_by_country = mvx_get_user_meta( $vendor_id, '_mvx_shipping_by_country', true ) ? mvx_get_user_meta( $vendor_id, '_mvx_shipping_by_country', true ) : '';

            $shipping_country_rate = mvx_get_user_meta( $vendor_id, '_mvx_country_shipping_rates', true ) ? mvx_get_user_meta( $vendor_id, '_mvx_country_shipping_rates', true ) : $default_nested_data;

        }

        $settings_fields_data = mvx_admin_backend_settings_fields_details();

        $settings_fields_data['user_ID']   =  [
            'userid'    =>  $vendor_id
        ];
        $settings_fields_data['vendor_default_shipping_options']   = $vendor_default_shipping_options;
        $settings_fields_data['shipping_options']  = $shipping_options_list;

        $settings_fields_data['vendor-personal'] =   [
            [
                'key'       => 'user_login',
                'type'      => 'text',
                'label'     => __( 'Username (required)', 'dc-woocommerce-multi-vendor' ),
                'desc' => __('Usernames cannot be changed.', 'dc-woocommerce-multi-vendor'),
                'props'     => array(
                    'required'  => true
                ),
                'database_value' => isset($user->user_login) ? $user->user_login : '',
            ],
            [
                'key'       => 'password',
                'type'      => 'password',
                'label'     => __( 'Password', 'dc-woocommerce-multi-vendor' ),
                'desc'     => __('Keep it blank for not to update.', 'dc-woocommerce-multi-vendor'),
                'props'     => array(
                    'required'  => true
                ),
                'database_value' => '',
            ],
            [
                'key'       => 'first_name',
                'type'      => 'text',
                'label'     => __( 'First Name', 'dc-woocommerce-multi-vendor' ),
                'database_value' => isset($user->first_name) ? $user->first_name : '',
            ],
            [
                'key'       => 'last_name',
                'type'      => 'text',
                'label'     => __( 'Last Name', 'dc-woocommerce-multi-vendor' ),
                'database_value' => isset($user->last_name) ? $user->last_name : '',
            ],
            [
                'key'       => 'user_email',
                'type'      => 'email',
                'label'     => __( 'Email (required)', 'dc-woocommerce-multi-vendor' ),
                'props'     => array(
                    'required'  => true
                ),
                'database_value' => isset($user->user_email) ? $user->user_email : '',
            ],
            [
                'key'       => 'user_nicename',
                'type'      => 'text',
                'label'     => __( 'Nick Name (required)', 'dc-woocommerce-multi-vendor' ),
                'props'     => array(
                    'required'  => true
                ),
                'database_value' => isset($user->user_nicename) ? $user->user_nicename : '',
            ],
            [
                'key'       => 'display_name',
                'type'      => 'select',
                'label'     => __( 'Display name', 'dc-woocommerce-multi-vendor' ),
                'desc'      => __( 'If you are not sure where to add widget, just go to admin <a href=".admin_url("widgets.php")." terget="_blank">widget</a> section and add your preferred widgets to <b>vendor store sidebar</b>.', 'dc-woocommerce-multi-vendor' ),
                'options' => $display_name_option,
                'restricted_page'   => '?page=vendors&name=add_new',
                'database_value' => isset($showdisplayname) ? $showdisplayname : '',
            ],
            [
                'key'    => 'vendor_profile_image',
                'label'   => __( 'Profile Image', 'dc-woocommerce-multi-vendor' ),
                'type'    => 'file',
                'width' =>  75,
                'height'    => 75,
                'database_value' => array(),
            ],
            
        ];

        $settings_fields_data['vendor-store'] =   [
            [
                'label' => __('Store Name *', 'dc-woocommerce-multi-vendor'),
                'type' => 'text',
                'key' => 'vendor_page_title',
                'database_value' => isset($user_vendor->page_title) ? $user_vendor->page_title : '' 
            ],
            [
                'label' => __('Store Slug *', 'dc-woocommerce-multi-vendor'),
                'type' => 'text',
                'key' => 'vendor_page_slug',
                'desc' => sprintf(__('Store URL will be something like - %s', 'dc-woocommerce-multi-vendor'), trailingslashit(get_home_url()) . 'vendor_slug'),
                'database_value' => isset($user_vendor->page_slug) ? $user_vendor->page_slug : '',
            ],
            [
                'label' => __('Store Description', 'dc-woocommerce-multi-vendor'),
                'type' => 'wpeditor', 
                'key' => 'vendor_description', 
                'database_value' => isset($vendor_description) ? $vendor_description : ''
            ],

            [
                'label' => __('Phone', 'dc-woocommerce-multi-vendor'), 
                'type' => 'number', 
                'key' => 'vendor_phone', 
                'database_value' => isset($vendor_phone) ? $vendor_phone : ''
            ],
            [
                'label' => __('Address', 'dc-woocommerce-multi-vendor'), 
                'type' => 'text', 
                'key' => 'vendor_address_1', 
                'database_value' => isset($vendor_address_1) ? $vendor_address_1 : ''
            ],
            [
                'label' => '', 
                'type' => 'text', 
                'key' => 'vendor_address_2', 
                'database_value' => isset($vendor_address_2) ? $vendor_address_2 : ''
            ],
            [
                'label' => __('Country', 'dc-woocommerce-multi-vendor'), 
                'type' => 'country', 
                'key' => 'vendor_country', 
                'class' => 'country_to_state regular-text', 
                'options' => $country_list, 
                'database_value' => isset($vendor_country_code_data) ? $vendor_country_code_data : ''
            ],
            [
                'label' => __('State', 'dc-woocommerce-multi-vendor'), 
                'type' => 'state', 
                'key' => 'vendor_state', 
                'class' => 'regular-text', 
                'options' => array(), 
                'database_value' => isset($vendor_state_code_data) ? $vendor_state_code_data : ''
            ],
            [
                'label' => __('City', 'dc-woocommerce-multi-vendor'), 
                'type' => 'text', 
                'key' => 'vendor_city', 
                'database_value' => isset($vendor_city) ? $vendor_city : ''
            ],
            [
                'label' => __('ZIP code', 'dc-woocommerce-multi-vendor'), 
                'type' => 'text', 
                'key' => 'vendor_postcode', 
                'database_value' => isset($vendor_postcode) ? $vendor_postcode : ''
            ],
            [
                'label' => __('Timezone', 'dc-woocommerce-multi-vendor'),
                'type' => 'text', 
                'key' => 'timezone_string',
                'props'     => array(
                    'disabled'  => true
                ),
                'database_value' => isset($tzstring) ? $tzstring : '', 
            ],
        ];


        $settings_fields_data['vendor-social'] =   [
            [
                'label' => __('Facebook', 'dc-woocommerce-multi-vendor'), 
                'type' => 'url', 
                'key' => 'vendor_fb_profile', 
                'database_value' => isset($vendor_fb_profile) ? $vendor_fb_profile : ''
            ],
            [
                'label' => __('Twitter', 'dc-woocommerce-multi-vendor'), 
                'type' => 'url', 
                'key' => 'vendor_twitter_profile', 
                'database_value' => isset($vendor_twitter_profile) ? $vendor_twitter_profile : ''
            ],
            [
                'label' => __('LinkedIn', 'dc-woocommerce-multi-vendor'), 
                'type' => 'url', 
                'key' => 'vendor_linkdin_profile', 
                'database_value' => isset($vendor_linkdin_profile) ? $vendor_linkdin_profile : ''
            ],
            [
                'label' => __('YouTube', 'dc-woocommerce-multi-vendor'), 
                'type' => 'url', 
                'key' => 'vendor_youtube', 
                'database_value' => isset($vendor_youtube_profile) ? $vendor_youtube_profile : ''
            ],
            [
                'label' => __('Instagram', 'dc-woocommerce-multi-vendor'), 
                'type' => 'url', 
                'key' => 'vendor_instagram', 
                'database_value' => isset($vendor_instagram_profile) ? $vendor_instagram_profile : ''
            ],
        ];

        $settings_fields_data['vendor-payments'] =   [
                [
                    'key'       => 'vendor_payment_mode',
                    'type'      => 'select',
                    'label'     => __( 'Choose Payment Method', 'dc-woocommerce-multi-vendor' ),
                    'options'   => isset($vendor_payment_method_display_section) ? $vendor_payment_method_display_section : array(),
                    'database_value' => isset($showpayment_method) ? $showpayment_method : '',
                ],
                [
                    'label' => __('Commission Amount', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'number', 
                    'key' => 'vendor_commission',
                    'placeholder' => '0.00',
                    'database_value' => isset($commission_value) ? $commission_value : ''
                ],
                [
                    'label' => __('Paypal Email', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'text', 
                    'key' => 'vendor_paypal_email',
                    'placeholder' => '0.00',
                    'depend'    => 'vendor_payment_mode',
                    'dependvalue'       =>  'paypal_masspay',
                    'database_value' => isset($vendor_paypal_email) ? $vendor_paypal_email : ''
                ],
                [
                    'label' => __('Paypal Email', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'text', 
                    'key' => 'vendor_paypal_email',
                    'placeholder' => '0.00',
                    'depend'    => 'vendor_payment_mode',
                    'dependvalue'       =>  'paypal_payout',
                    'database_value' => isset($vendor_paypal_email) ? $vendor_paypal_email : ''
                ],

               /* [
                    'label' => __('Account type', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'select', 
                    'key' => 'vendor_bank_account_type', 
                    'label_for' => 'vendor_bank_account_type', 
                    'name' => 'vendor_bank_account_type', 
                    'options' => $vendor_bank_account_type_select, 
                    'database_value' => $vendor_obj->bank_account_type, 
                ],*/
                [
                    'label' => __('Bank Name', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'text',
                    'depend'    => 'vendor_payment_mode',
                    'dependvalue'       =>  'direct_bank',
                    'key' => 'vendor_bank_name', 
                    'database_value' => isset($vendor_bank_name) ? $vendor_bank_name : '' 
                ],

                [
                    'label' => __('ABA Routing Number', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'text',
                    'depend'    => 'vendor_payment_mode',
                    'dependvalue'       =>  'direct_bank',
                    'key' => 'vendor_aba_routing_number', 
                    'database_value' => isset($vendor_aba_routing_number) ? $vendor_aba_routing_number : ''
                ],

                [
                    'label' => __('Destination Currency', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'text',
                    'depend'    => 'vendor_payment_mode',
                    'dependvalue'       =>  'direct_bank',
                    'key' => 'vendor_destination_currency', 
                    'database_value' => isset($vendor_destination_currency) ? $vendor_destination_currency : ''
                ],
                [
                    'label' => __('Bank Address', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'textarea', 
                    'key' => 'vendor_bank_address', 
                    'depend'    => 'vendor_payment_mode',
                    'dependvalue'       =>  'direct_bank',
                    'rows'=>'6', 
                    'cols'=>'53', 
                    'database_value' => isset($vendor_bank_address) ? $vendor_bank_address : ''
                ],
                [
                    'label' => __('IBAN', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'text',
                    'depend'    => 'vendor_payment_mode',
                    'dependvalue'       =>  'direct_bank',
                    'key' => 'vendor_iban', 
                    'database_value' => isset($vendor_iban) ? $vendor_iban : ''
                ],
                [
                    'label' => __('Account Holder Name', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'text',
                    'depend'    => 'vendor_payment_mode',
                    'dependvalue'       =>  'direct_bank',
                    'key' => 'vendor_account_holder_name', 
                    'database_value' => isset($vendor_account_holder_name) ? $vendor_account_holder_name : ''
                ],
                [
                    'label' => __('Account Number', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'text',
                    'depend'    => 'vendor_payment_mode',
                    'dependvalue'       =>  'direct_bank',
                    'key' => 'vendor_bank_account_number', 
                    'database_value' => isset($vendor_bank_account_number) ? $vendor_bank_account_number : ''
                ],
        ];

        $settings_fields_data['vendor-policy'] =   [
            [
                'label' => __('Shipping Policy', 'dc-woocommerce-multi-vendor'), 
                'type' => 'wpeditor', 
                'key' => 'vendor_shipping_policy', 
                'database_value' => isset($_vendor_shipping_policy) ? $_vendor_shipping_policy : ''
            ],
            [
                'label' => __('Refund Policy', 'dc-woocommerce-multi-vendor'), 
                'type' => 'wpeditor', 
                'key' => 'vendor_refund_policy', 
                'database_value' => isset($_vendor_refund_policy) ? $_vendor_refund_policy : ''
            ],
            [
                'label' => __('Cancellation/Return/Exchange Policy', 'dc-woocommerce-multi-vendor'),
                 'type' => 'wpeditor', 
                 'key' => 'vendor_cancellation_policy', 
                 'database_value' => isset($_vendor_cancellation_policy) ? $_vendor_cancellation_policy : ''
             ],
        ];

        $settings_fields_data['distance-shipping'] =   [
            [
                'label' => __('Default Cost', 'dc-woocommerce-multi-vendor'), 
                'type' => 'text', 
                'key' => 'mvx_byd_default_cost',
                'placeholder' => '0.00',
                'database_value' => isset($mvx_shipping_by_distance['_default_cost']) ? $mvx_shipping_by_distance['_default_cost'] : ''
            ],
            [
                'label' => __('Max Distance (km)', 'dc-woocommerce-multi-vendor'), 
                'type' => 'text',
                'placeholder' => __('No Limit', 'dc-woocommerce-multi-vendor'), 
                'key' => 'mvx_byd_max_distance', 
                'database_value' => isset($mvx_shipping_by_distance['_max_distance']) ? $mvx_shipping_by_distance['_max_distance'] : ''
            ],
            [
                'label' => __('Enable Local Pickup', 'dc-woocommerce-multi-vendor'), 
                'type' => 'checkbox', 
                'key' => 'mvx_byd_enable_local_pickup',
                'options' => array(
                    array(
                        'key'=> "mvx_byd_enable_local_pickup",
                        'label'=> __('', 'dc-woocommerce-multi-vendor'),
                        'value'=> "mvx_byd_enable_local_pickup"
                    ),
                ),
                'database_value' => isset($mvx_shipping_by_distance['_enable_local_pickup']) ? $mvx_shipping_by_distance['_enable_local_pickup'] : ''
            ],
            [
                'label' => __('Local Pickup Cost', 'dc-woocommerce-multi-vendor'), 
                'type' => 'text', 
                'key' => 'mvx_byd_local_pickup_cost', 
                'placeholder' => '0.00',
                'database_value' => isset($mvx_shipping_by_distance['_local_pickup_cost']) ? $mvx_shipping_by_distance['_local_pickup_cost'] : ''
            ],
            [
                'key'       => 'mvx_shipping_by_distance_rates',
                'type'      => 'nested',
                'label'     => __( 'Distance-Cost Rules:', 'dc-woocommerce-multi-vendor' ),
                'parent_options' => array(
                    array(
                        'key'=>'mvx_distance_rule',
                        'type'=> "select",
                        'class' => "nested-parent-class",
                        'name' => "nested-parent-name",
                        'label'=> __('Distance Rule', 'dc-woocommerce-multi-vendor'),
                        'options' => array(
                            array(
                                'key'=> "up_to",
                                'label'=> __('Distance up to', 'dc-woocommerce-multi-vendor'),
                                'value'=> __('up_to', 'dc-woocommerce-multi-vendor'),
                            ),
                            array(
                                'key'=> "more_than",
                                'label'=> __('Distance more than', 'dc-woocommerce-multi-vendor'),
                                'value'=> __('more_than', 'dc-woocommerce-multi-vendor'),
                            ),
                        ),
                    ),
                    array(
                        'key'   => 'mvx_distance_unit',
                        'type'  => "text",
                        'class' => "nested-parent-class",
                        'name'  => "nested-parent-name",
                        'label' => __('Distance', 'dc-woocommerce-multi-vendor') . ' ( '. __('km', 'dc-woocommerce-multi-vendor') .' )', 
                    ),
                    array(
                        'key'   => 'mvx_distance_price',
                        'type'  => "text",
                        'class' => "nested-parent-class",
                        'name'  => "nested-parent-name",
                        'label' => __('Cost', 'dc-woocommerce-multi-vendor') . ' ('.get_woocommerce_currency_symbol().')',
                    ),
                ),
                'child_options' => array(
                    
                ),
                'database_value' => isset($vendor_id) ? $shipping_distance_rate : $default_nested_data,
            ]
        ];

        $settings_fields_data['country-shipping'] =   [
                [
                    'label' => __('Default Shipping Price', 'dc-woocommerce-multi-vendor'), 
                    'placeholder' => '0.00', 
                    'type' => 'text', 
                    'class' => 'col-md-6 col-sm-9', 
                    'key' => 'mvx_shipping_type_price', 
                    'database_value' => isset($mvx_shipping_by_country['_mvx_shipping_type_price']) ? $mvx_shipping_by_country['_mvx_shipping_type_price'] : '', 
                ],

                [
                    'label' => __('Per Product Additional Price', 'dc-woocommerce-multi-vendor'), 
                    'placeholder' => '0.00', 
                    'type' => 'text', 
                    'class' => 'col-md-6 col-sm-9', 
                    'key' => 'mvx_additional_product', 
                    'database_value' => isset($mvx_shipping_by_country['_mvx_additional_product']) ? $mvx_shipping_by_country['_mvx_additional_product'] : '',
                    'desc' => __('If a customer buys more than one type product from your store, first product of the every second type will be charged with this price', 'dc-woocommerce-multi-vendor') 
                ],

                [
                    'label' => __('Per Qty Additional Price', 'dc-woocommerce-multi-vendor'), 
                    'placeholder' => '0.00', 
                    'type' => 'text', 
                    'class' => 'col-md-6 col-sm-9', 
                    'key' => 'mvx_additional_qty', 
                    'database_value' => isset($mvx_shipping_by_country['_mvx_additional_qty']) ? $mvx_shipping_by_country['_mvx_additional_qty'] : '', 
                    'hints' => __('Every second product of same type will be charged with this price', 'dc-woocommerce-multi-vendor'),
                ],

                [
                    'label' => __('Free Shipping Minimum Order Amount', 'dc-woocommerce-multi-vendor'), 
                    'placeholder' => __( 'NO Free Shipping', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'text', 
                    'class' => 'col-md-6 col-sm-9', 
                    'key' => 'mvx_byc_free_shipping_amount', 
                    'database_value' => isset($mvx_shipping_by_country['_free_shipping_amount']) ? $mvx_shipping_by_country['_free_shipping_amount'] : '', 
                    'hints' => __('Free shipping will be available if order amount more than this. Leave empty to disable Free Shipping.', 'dc-woocommerce-multi-vendor') 
                ],

                [
                    'label' => __('Enable Local Pickup', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'checkbox', 
                    'class' => 'mvx-checkbox mvx_ele', 
                    'key' => 'mvx_byc_enable_local_pickup', 
                    'options' => array(
                        array(
                            'key'=> "mvx_byc_enable_local_pickup",
                            'label'=> __('', 'dc-woocommerce-multi-vendor'),
                            'value'=> "mvx_byc_enable_local_pickup"
                        ),
                    ),
                    'database_value' => isset($mvx_shipping_by_country['_enable_local_pickup']) ? $mvx_shipping_by_country['_enable_local_pickup'] : '' 
                ],

                [
                    'label' => __('Local Pickup Cost', 'dc-woocommerce-multi-vendor'), 
                    'placeholder' => '0.00', 
                    'type' => 'text', 
                    'class' => 'col-md-6 col-sm-9', 
                    'key' => 'mvx_byc_local_pickup_cost', 
                    'database_value' => isset($mvx_shipping_by_country['_local_pickup_cost']) ? $mvx_shipping_by_country['_local_pickup_cost'] : '' 
                ],

                [
                    'key'       => 'mvx_country_shipping_rates',
                    'type'      => 'nested',
                    'label'     => __( 'Shipping Rates by Country', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __( 'Add the countries you deliver your products to. You can specify states as well. If the shipping price is same except some countries, there is an option Everywhere Else, you can use that.', 'dc-woocommerce-multi-vendor' ),
                    'parent_options' => array(
                        array(
                            'key'       =>'mvx_country_to',
                            'type'      => "country",
                            'class'     => "nested-parent-class",
                            'name'      => "nested-parent-name",
                            'label'     => __('Country', 'dc-woocommerce-multi-vendor'),
                            'options'   => $country_list
                        ),
                        array(
                            'key'           => 'mvx_country_to_price',
                            'type'          => "text",
                            'class'         => "nested-parent-class",
                            'name'          => "nested-parent-name",
                            'placeholder'   => '0.00 (' . __('Free Shipping', 'dc-woocommerce-multi-vendor') . ')',
                            'label'         => __('Cost', 'dc-woocommerce-multi-vendor') . ' ('.get_woocommerce_currency_symbol().')',
                        ),
                    ),
                    'child_options' => array(
                        array(
                            'key'       =>'mvx_state_to',
                            'type'      => "state",
                            'class'     => "nested-parent-class",
                            'name'      => "nested-parent-name",
                            'label'     => __('State', 'dc-woocommerce-multi-vendor'),
                            'options'   => array()
                        ),
                        array(
                            'key'   => 'mvx_state_to_price',
                            'type'  => "text",
                            'class' => "nested-parent-class",
                            'name'  => "nested-parent-name",
                            'placeholder' => '0.00 (' . __('Free Shipping', 'dc-woocommerce-multi-vendor') . ')',
                            'label' => __('Cost', 'dc-woocommerce-multi-vendor') . ' ('.get_woocommerce_currency_symbol().')',
                        ),
                    ),
                    'database_value' => isset($vendor_id) ? $shipping_country_rate : $default_nested_data,
                ]
        ];

        $settings_fields_data['vendor-application'] =   [];

        $settings_fields_data['vendor-shipping'] =   [];

        $settings_fields_data['vendor-followers'] =   [];

        //$settings_fields_data['vendor_personal'] =   [];

        
        return rest_ensure_response($settings_fields_data);
    }

    public function mvx_create_announcement($request) {
        $all_details = [];
        $fetch_data = $request->get_param('model');

        $announcement_title = $fetch_data && isset($fetch_data['announcement_title']) ? $fetch_data['announcement_title'] : '';
        $announcement_url = $fetch_data && isset($fetch_data['announcement_url']) ? $fetch_data['announcement_url'] : '';
        $announcement_content = $fetch_data && isset($fetch_data['announcement_content']) ? $fetch_data['announcement_content'] : '';
        $announcement_vendors = $fetch_data && isset($fetch_data['announcement_vendors']) ? $fetch_data['announcement_vendors'] : '';
        $post_id = wp_insert_post( array( 'post_title' => $announcement_title, 'post_type' => 'mvx_vendor_notice', 'post_status' => 'publish', 'post_content' => $announcement_content ) );
        update_post_meta( $post_id, '_mvx_vendor_notices_url', wc_clean($announcement_url) );

        $notify_vendors = isset($fetch_data['announcement_vendors']) && !empty($fetch_data['announcement_vendors']) ? wp_list_pluck(array_filter($fetch_data['announcement_vendors']), 'value')  : get_mvx_vendors( array(), 'ids' );
        if (isset($fetch_data['announcement_vendors']) && !empty($fetch_data['announcement_vendors'])) {
            update_post_meta($post_id, '_mvx_vendor_notices_vendors', $notify_vendors);
        } else {
            update_post_meta($post_id, '_mvx_vendor_notices_vendors', get_mvx_vendors( array(), 'ids' ));
        }

        $all_details['redirect_link'] = admin_url('admin.php?page=mvx#&submenu=work-board&name=announcement&AnnouncementID='. $post_id .'');
        return $all_details;
    }

    public function mvx_display_announcement() {
        $announcement_list = $vedors_list_renew = array();
        $args = array(
            'post_type' => 'mvx_vendor_notice',
            'post_status' => array('publish', 'auto-draft'),
            'posts_per_page' => -1,
        );
        $announcement = get_posts($args);

        foreach ($announcement as $announcementkey => $announcementvalue) {
            $vedors_list_renew = [];
            $vedors_list = get_post_meta($announcementvalue->ID, '_mvx_vendor_notices_vendors', true);

            if ($vedors_list && is_array($vedors_list) && !empty($vedors_list)) {
                foreach ($vedors_list as $key => $value) {
                    $vendor = get_mvx_vendor($value);
                    $vedors_list_renew[] = $vendor->page_title;
                }
            }

            $announcement_list[] = array(
                'id'            =>  $announcementvalue->ID,
                'sample_title'  =>  $announcementvalue->post_title,
                'title'         =>  '<a href="' . sprintf('?page=%s&name=%s&AnnouncementID=%s', 'mvx#&submenu=work-board', 'announcement', $announcementvalue->ID) . '">' . $announcementvalue->post_title . '</a>',
                'date'          =>  human_time_diff(strtotime($announcementvalue->post_modified)),
                'vendor'        =>  $vedors_list_renew ? implode(',', $vedors_list_renew) : '',
                'link'          =>  sprintf('?page=%s&name=%s&AnnouncementID=%s', 'mvx#&submenu=work-board', 'announcement', $announcementvalue->ID),
                'type'          =>  'post',
            );
        }
        return rest_ensure_response($announcement_list);
    }

    public function mvx_fetch_report_overview_data() {
        return $this->mvx_report_data('');
    }

    public function mvx_get_report_overview_data($request) {
        return $this->mvx_report_data($request);
    }

    public function mvx_report_data($request) {
        global $MVX;
        // get date value from datepicker
        $value = $request && $request->get_param('value') ? ($request->get_param('value')) : 0;
        $product = $request && $request->get_param('product') ? ($request->get_param('product')) : 0;
        $selectvendor = $request && $request->get_param('vendor') ? ($request->get_param('vendor')) : 0;

        //print_r($product);die;

        // Bydefault last 7 days
        $start_date    = strtotime( '-6 days', strtotime( 'midnight', current_time( 'timestamp' ) ) );
        $end_date      = strtotime( 'midnight', current_time( 'timestamp' ) );

        if ($value) {
            $initial_start = $value ? $value[0] : '';
            $initial_end = $value ? $value[1] : '';
            $start_date = max( strtotime( '-20 years' ), strtotime( sanitize_text_field( $initial_start ) ) );
            $end_date = strtotime( 'midnight', strtotime( sanitize_text_field( $initial_end ) ) );
        }

        $end_date = strtotime('+1 day', $end_date);


        /** *Report overview* **/
        $overview_sales = $gross_sales = $vendor_earning = $overview_admin_earning = $pending_vendors = $overview_vendors = $products = $transactions = $report_product_net_sales = $total_item_sold = $report_vendor_net_sales = 0;
        $report_html = '';
        $product_number_stack = $total_orders_product_chart = $product_sales_data_chart = $product_item_sold_chart = $total_number_order_data_chart = $net_sales_data_chart = $total_sales = $banking_datas = array();


        // transaction history
        if ( $selectvendor ) {
            $requestData = array('from_date'=> date("Y-m-d", $start_date) , 'to_date' => date("Y-m-d", $end_date) );

            $data_store = $MVX->ledger->load_ledger_data_store();
            $vendor_all_ledgers = apply_filters('mvx_admin_report_banking_data', $data_store->get_ledger( array( 'vendor_id' => $selectvendor ), '', $requestData )); 
            if ( !empty( $vendor_all_ledgers ) ) {
                foreach ($vendor_all_ledgers as $ledger ) {
                    // total credited balance
                    $total_credit += floatval( $ledger->credit );
                    // total debited balance
                    $total_debit += floatval( $ledger->debit );
                    $order = wc_get_order( $ledger->order_id );
                    $currency = ( $order ) ? $order->get_currency() : '';
                    $ref_types = get_mvx_ledger_types();
                    $ref_type = isset($ref_types[$ledger->ref_type]) ? $ref_types[$ledger->ref_type] : ucfirst( $ledger->ref_type );
                    $type = '<mark class="type ' . $ledger->ref_type . '"><span>' . $ref_type . '</span></mark>';
                    $status = $ledger->ref_status;
                    if($ref_type == 'Commission') {
                        $link = admin_url('post.php?post=' . $ledger->order_id . '&action=edit');
                        $ref_link = '<a href="'.esc_url($link).'">#'.$ledger->order_id.'</a>';
                    } elseif($ref_type == 'Refund' && $ref_type == 'Withdrawal') {
                        $com_id = get_post_meta( $ledger->order_id, '_commission_id', true );
                        $link = admin_url('post.php?post=' . $com_id . '&action=edit');
                        $ref_link = '<a href="'.esc_url($link).'">#'.$com_id.'</a>';
                    }
                    $credit = ( $ledger->credit ) ? wc_price($ledger->credit, array('currency' => $currency)) : '';
                    $debit = ( $ledger->debit ) ? wc_price($ledger->debit, array('currency' => $currency)) : '';
                    $banking_datas[] = apply_filters( 'mvx_admin_report_banking_details', array( 
                        'status' => ucfirst( $status ), 
                        'date' => mvx_date($ledger->created), 
                        'type' => $ref_type, 
                        'reference_id' => $ref_link, 
                        'Credit' => $credit, 
                        'Debit' => $debit, 
                        'balance' => wc_price($ledger->balance, array('currency' => $currency))
                    ), $ledger );
                }
            }
        }


        $args_overview = apply_filters('mvx_report_admin_overview_query_args', array(
                'post_type' => 'shop_order',
                'posts_per_page' => -1,
                'post_parent' => 0,
                'post_status' => array('wc-processing', 'wc-completed'),
                'date_query' => array(
                    'inclusive' => true,
                    'after' => array(
                        'year' => date('Y', $start_date),
                        'month' => date('n', $start_date),
                        'day' => date('1'),
                    ),
                    'before' => array(
                        'year' => date('Y', $end_date),
                        'month' => date('n', $end_date),
                        'day' => date('j', $end_date),
                    ),
                )
            ));

        $qry = new WP_Query($args_overview);
        $orders_overview = apply_filters('mvx_report_admin_overview_orders_overview', $qry->get_posts());
        $sales_data_chart = array();

         if ( !empty( $orders_overview ) ) {
            foreach ( $orders_overview as $order_obj ) {
                $order = wc_get_order($order_obj->ID);
                $date = date_create($order->order_date);
                $sales_data_chart[] = array(
                    'name'  =>  date_format($date,"d M"),
                    'Net Sales'  =>  $order->get_subtotal(),
                );
                
                $overview_sales += $order->get_subtotal();
                $mvx_suborders = get_mvx_suborders($order_obj->ID);
                if(!empty($mvx_suborders)) {
                    foreach ($mvx_suborders as $suborder) {
                        $vendor_order = mvx_get_order($suborder->get_id());
                        if( $vendor_order ) {
                            $gross_sales += $suborder->get_total( 'edit' );
                            $vendor_earning += $vendor_order->get_commission_total('edit');
                        }
                    }
                }
            }
            $overview_admin_earning = $gross_sales - $vendor_earning;
        }

        $user_args = array(
            'role' => 'dc_vendor',
            'date_query' => array(
                'inclusive' => true,
                'after' => array(
                    'year' => date('Y', $start_date),
                    'month' => date('n', $start_date),
                    'day' => date('1'),
                ),
                'before' => array(
                    'year' => date('Y', $end_date),
                    'month' => date('n', $end_date),
                    'day' => date('j', $end_date),
                ),
            )
        );
        $user_query = new WP_User_Query($user_args);
        if (!empty($user_query->results)) 
            $overview_vendors = count($user_query->results);
        
        $pending_user_args = array(
            'role' => 'dc_pending_vendor',
            'date_query' => array(
                'inclusive' => true,
                'after' => array(
                    'year' => date('Y', $start_date),
                    'month' => date('n', $start_date),
                    'day' => date('1'),
                ),
                'before' => array(
                    'year' => date('Y', $end_date),
                    'month' => date('n', $end_date),
                    'day' => date('j', $end_date),
                ),
            )
        );
        $pending_user_query = new WP_User_Query($pending_user_args);
        if (!empty($pending_user_query->results)) 
            $pending_vendors = count($pending_user_query->results);

        $product_args = array(
            'posts_per_page' => -1,
            //'author__in' => $vendor_ids,
            'post_type' => 'product',
            'post_status' => 'pending',
            'date_query' => array(
                'inclusive' => true,
                'after' => array(
                    'year' => date('Y', $start_date),
                    'month' => date('n', $start_date),
                    'day' => date('1'),
                ),
                'before' => array(
                    'year' => date('Y', $end_date),
                    'month' => date('n', $end_date),
                    'day' => date('j', $end_date),
                ),
            )
        );
        $get_pending_products = new WP_Query($product_args);
        if (!empty($get_pending_products->get_posts())) 
            $products = count($get_pending_products->get_posts());

        $transactions_args = array(
            'post_type' => 'mvx_transaction',
            'post_status' => 'mvx_processing',
            'meta_key' => 'transaction_mode',
            'meta_value' => 'direct_bank',
            'posts_per_page' => -1,
            'date_query' => array(
                'inclusive' => true,
                'after' => array(
                    'year' => date('Y', $start_date),
                    'month' => date('n', $start_date),
                    'day' => date('1'),
                ),
                'before' => array(
                    'year' => date('Y', $end_date),
                    'month' => date('n', $end_date),
                    'day' => date('j', $end_date),
                ),
            )
        );
        $transactions = get_posts($transactions_args);
        if (!empty($transactions)) 
            $transactions = count($transactions);



        /** * ------------------------------------------- report product overview ------------------------------------------------------- * **/

        $args_report_product = apply_filters( 'mvx_report_data_product_query_args', array(
            'post_type' => 'shop_order',
            'posts_per_page' => -1,
            'post_status' => array('wc-processing', 'wc-completed'),
            'meta_query' => array(
                array(
                    'key' => '_commissions_processed',
                    'value' => 'yes',
                    'compare' => '='
                )
            ),
            'date_query' => array(
                'inclusive' => true,
                'after' => array(
                    'year' => date('Y', $start_date),
                    'month' => date('n', $start_date),
                    'day' => date('j', $start_date),
                ),
                'before' => array(
                    'year' => date('Y', $end_date),
                    'month' => date('n', $end_date),
                    'day' => date('j', $end_date),
                ),
            )
        ) );

        $qry_report_product = new WP_Query($args_report_product);
        $overview_orders_product = apply_filters('mvx_filter_orders_report_product', $qry_report_product->get_posts());

        if (!empty($overview_orders_product)) {
            $pro_total = $vendor_total = array();
            foreach ($overview_orders_product as $order_obj) {
                try {
                    $order = wc_get_order($order_obj->ID);
                    if ($order) :

                        $date = date_create($order->order_date);
                        /*$total_orders_product_chart[] = array(
                            'name'  =>  date_format($date,"d M"),
                            'Net Sales'  =>  absint(1),
                        );*/

                        $vendor_order = mvx_get_order($order->get_id());
                        if( $vendor_order ) {
                            $line_items = $order->get_items( 'line_item' );
                            
                            foreach ($line_items as $item_id => $item) {
                                
                                if ( $product && $product != $item->get_product_id() ) {
                                    continue;
                                }

                                $total_orders_product_chart[] = array(
                                    'name'  =>  date_format($date,"d M"),
                                    'Net Sales'  =>  absint(1),
                                );

                                $pro_total[$item->get_product_id()] = isset( $pro_total[$item->get_product_id()] ) ? $pro_total[$item->get_product_id()] + $item->get_subtotal() : $item->get_subtotal();
                                $total_sales[$item->get_product_id()]['product_id'] = $item->get_product_id();
                                $total_sales[$item->get_product_id()]['total_sales'] = $pro_total[$item->get_product_id()];
                                $total_sales[$item->get_product_id()]['quantities'] = $item->get_quantity();
                                $total_sales[$item->get_product_id()]['order_id'] = $order_obj->ID;

                                $total_sales[$item->get_product_id()]['details'][] = array(
                                    'date'  =>  date_format($date,"d M"),
                                    'value' =>  $item->get_subtotal()
                                );

                                $meta_data = $item->get_meta_data();
                                // get item commission
                                foreach ( $meta_data as $meta ) {
                                    if($meta->key == '_vendor_item_commission') {
                                        $vendor_total[$item->get_product_id()] = isset( $vendor_total[$item->get_product_id()] ) ? $vendor_total[$item->get_product_id()] + floatval($meta->value) : floatval($meta->value);
                                        $total_sales[$item->get_product_id()]['vendor_earning'] = $vendor_total[$item->get_product_id()];
                                    }
                                }
                                // admin part
                                $total_sales[$item->get_product_id()]['admin_earning'] = $total_sales[$item->get_product_id()]['total_sales'] - $total_sales[$item->get_product_id()]['vendor_earning'];
                            }
                        }
                    endif;
                } catch (Exception $ex) {

                }
            }

            if (sizeof($total_sales) > 0) {
                foreach ($total_sales as $product_id => $sales_report) {
                    $report_product_net_sales += ( $sales_report['total_sales'] > 0 ) ? $sales_report['total_sales'] : 0;

                        foreach ($sales_report['details'] as $detailskey => $detailsvalue) {
                            $product_sales_data_chart[] = array(
                                'name'  =>  $detailsvalue['date'],
                                'Net Sales'  => $detailsvalue['value'],
                            );
                        }

                    /*$order_id = wc_get_order($sales_report['order_id']);

                    $order = wc_get_order($order_id);
                    $date = date_create($order->order_date);
                    $product_sales_data_chart[] = array(
                        'name'  =>  date_format($date,"d M"),
                        'Net Sales'  => ( $sales_report['total_sales'] > 0 ) ? $sales_report['total_sales'] : 0,
                    );*/
                    
                    //$total_sales_width = ( $sales_report['total_sales'] > 0 ) ? round($sales_report['total_sales']) / round($sales_report['total_sales']) * 100 : 0;
                    //$admin_earning_width = ( $sales_report['admin_earning'] > 0 ) ? ( $sales_report['admin_earning'] / round($sales_report['total_sales']) ) * 100 : 0;
                    //$vendor_earning_width = ( $sales_report['vendor_earning'] > 0 ) ? ( $sales_report['vendor_earning'] / round($sales_report['total_sales']) ) * 100 : 0;
                    $product = wc_get_product($product_id);
                    if( $product ) {

                        $product_item_sold_chart[] = array(
                            'name'  =>  date_format($date,"d M"),
                            'Net Sales'  => 1,
                        );
                        // set product in an array
                        $product_number_stack[]   =   $product_id;
                        $product_url = admin_url('post.php?post=' . $product_id . '&action=edit');
                    }
                }
            } else {
                $report_html = '<tr><td colspan="3">' . __('No product was sold in the given period.', 'dc-woocommerce-multi-vendor') . '</td></tr>';
            }
        } else {
            $report_html = '<tr><td colspan="3">' . __('Your store has no products.', 'dc-woocommerce-multi-vendor') . '</td></tr>';
        }

        // total item sold **
        $total_item_sold = count($product_number_stack);

        // product report end

        $product_report_datatable = array();
        if ($total_sales) {
            foreach ($total_sales as $total_sales_key => $total_sales_value) {
                $product = wc_get_product( $total_sales_key );
                $product_report_datatable[] = array(
                    'id'                    =>  $total_sales_key,
                    'title'                 =>  $product->get_name(),
                    'admin_earning'         =>  $total_sales_value['admin_earning'],
                    'vendor_earning'        =>  $total_sales_value['vendor_earning'],
                    'gross'                 =>  $total_sales_value['total_sales'],
                );
            }
        }


        //print_r($total_sales);die;

        /** * ---------------------------------------------------- vendor report start ------------------------------------------------------------- * **/

        $all_vendors = get_mvx_vendors();

        $total_sales = $admin_earning = $vendor_report = $report_bk = $total_number_orders = array();

        if (!empty($all_vendors) && is_array($all_vendors)) {
            foreach ($all_vendors as $vendor) {
                $gross_sales = $my_earning = $vendor_earning = 0;
                $chosen_product_ids = array();
                $vendor_id = $vendor->id;

                if ( $selectvendor && $selectvendor != $vendor_id ) {
                    continue;
                }
                

                $args = apply_filters('mvx_report_admin_vendor_tab_query_args', array(
                    'post_type' => 'shop_order',
                    'posts_per_page' => -1,
                    'author' => $vendor_id,
                    'post_status' => array('wc-processing', 'wc-completed'),
                    'meta_query' => array(
                        array(
                            'key' => '_commissions_processed',
                            'value' => 'yes',
                            'compare' => '='
                        ),
                        array(
                            'key' => '_vendor_id',
                            'value' => $vendor_id,
                            'compare' => '='
                        )
                    ),
                    'date_query' => array(
                        'inclusive' => true,
                        'after' => array(
                            'year' => date('Y', $start_date),
                            'month' => date('n', $start_date),
                            'day' => date('j', $start_date),
                        ),
                        'before' => array(
                            'year' => date('Y', $end_date),
                            'month' => date('n', $end_date),
                            'day' => date('j', $end_date),
                        ),
                    )
                ) );

                $qry = new WP_Query($args);

                $orders = apply_filters('mvx_filter_orders_report_vendor', $qry->get_posts());

                if ( !empty( $orders ) ) {
                    foreach ( $orders as $order_obj ) {
                        try {
                            $order = wc_get_order($order_obj->ID);
                            if ($order) :

                                $date = date_create($order->order_date);
                                $total_number_order_data_chart[] = array(
                                    'name'  =>  date_format($date,"d M"),
                                    'Net Sales'  => 1,
                                );


                                $net_sales_data_chart[] = array(
                                    'name'  =>  date_format($date,"d M"),
                                    'Net Sales'  => $order->get_total( 'edit' ),
                                );

                                $total_number_orders[] = $order_obj->ID;
                                $vendor_order = mvx_get_order($order->get_id());
                                $gross_sales += $order->get_total( 'edit' );
                                $vendor_earning += $vendor_order->get_commission_total('edit');
                            endif;
                        } catch (Exception $ex) {

                        }
                        
                    }
                }
                
                $total_sales[$vendor_id]['total_sales'] = $gross_sales;
                $total_sales[$vendor_id]['vendor_earning'] = $vendor_earning;
                $total_sales[$vendor_id]['admin_earning'] = $gross_sales - $vendor_earning;
                $total_sales[$vendor_id]['vendor_id'] = $vendor_id; // for report filter
            }

            $html_chart = '';
            
            foreach ($total_sales as $vendor_id => $report) {
                $report_vendor_net_sales += ( $report['total_sales'] > 0 ) ? $report['total_sales'] : 0;
                //$total_sales_width = ( $report['total_sales'] > 0 ) ? round($report['total_sales']) / round($report['total_sales']) * 100 : 0;
                //$admin_earning_width = ( $report['admin_earning'] > 0 ) ? ( $report['admin_earning'] / round($report['total_sales']) ) * 100 : 0;
                //$vendor_earning_width = ( $report['vendor_earning'] > 0 ) ? ( $report['vendor_earning'] / round($report['total_sales']) ) * 100 : 0;
            }

        } else {
            $html_chart = '<tr><td colspan="3">' . __('Your store has no vendors.', 'dc-woocommerce-multi-vendor') . '</td></tr>';
        }

        $order_number_count = !empty($total_number_orders) ? count($total_number_orders) : 0;
        // vendor report end


        
        //print_r($total_sales);die;

        $vendor_report_datatable = array();
        if ($total_sales) {
            foreach ($total_sales as $total_sales_key => $total_sales_value) {
                $vendor = get_mvx_vendor($total_sales_key);
                $name_display = "<b><a href='". sprintf('?page=%s&ID=%s&name=vendor-personal', 'vendors', $vendor->id) ."'>" . $vendor->page_title . "</a>";
                $vendor_report_datatable[] = array(
                    'id'                    =>  $vendor->id,
                    'title'                 =>  $name_display,
                    'defalt_title'          =>  $vendor->page_title,
                    'admin_earning'         =>  $total_sales_value['admin_earning'],
                    'vendor_earning'        =>  $total_sales_value['vendor_earning'],
                    'gross'                 =>  $total_sales_value['total_sales'],
                );
            }
        }

        // merge work product

        // order count ** 
        $total_orders_product = $total_orders_product_chart ? count($total_orders_product_chart) : 0;

        $order_count_pro_chart = array();
        foreach ($total_orders_product_chart as $chart_key => $chart_value) {
            $order_count_pro_chart[$chart_value['name']] += $chart_value['Net Sales'];
        }

        $net_sales_pro_chart = array();
        foreach ($product_sales_data_chart as $chart_key => $chart_value) {
            $net_sales_pro_chart[$chart_value['name']] += $chart_value['Net Sales'];
        }

        $item_count = array();
        foreach ($product_item_sold_chart as $chart_key => $chart_value) {
            $item_count[$chart_value['name']] += $chart_value['Net Sales'];
        }

        $merge_three_data = array_merge_recursive($order_count_pro_chart,$net_sales_pro_chart, $item_count);
        $final_array_product_chart = [];

        foreach ($merge_three_data as $merge_three_data_key => $merge_three_data_value) {
            $final_array_product_chart[] = array(
                'Date'          => $merge_three_data_key,
                'Order Count'   => isset($merge_three_data_value[0]) ? $merge_three_data_value[0] : 0,
                'Net Sales'     => isset($merge_three_data_value[1]) ? $merge_three_data_value[1] : 0,
                'Item Sold'     => isset($merge_three_data_value[2]) ? $merge_three_data_value[2] : 0
            );
        }

        // merge work vendor

        $order_count_ven_chart = array();
        foreach ($total_number_order_data_chart as $chart_key => $chart_value) {
            $order_count_ven_chart[$chart_value['name']] += $chart_value['Net Sales'];
        }

        $net_sales_ven_chart = array();
        foreach ($net_sales_data_chart as $chart_key => $chart_value) {
            $net_sales_ven_chart[$chart_value['name']] += $chart_value['Net Sales'];
        }

        $item_ven_count = array();
        foreach ($product_item_sold_chart as $chart_key => $chart_value) {
            $item_ven_count[$chart_value['name']] += $chart_value['Net Sales'];
        }

        $merge_three_data_ven = array_merge_recursive($order_count_ven_chart,$net_sales_ven_chart, $item_ven_count);
        $final_array_vendor_chart = [];

        foreach ($merge_three_data_ven as $merge_three_data_key => $merge_three_data_value) {
            $final_array_vendor_chart[] = array(
                'Date'          => $merge_three_data_key,
                'Order Count'   => isset($merge_three_data_value[0]) ? $merge_three_data_value[0] : 0,
                'Net Sales'     => isset($merge_three_data_value[1]) ? $merge_three_data_value[1] : 0,
                'Item Sold'     => isset($merge_three_data_value[2]) ? $merge_three_data_value[2] : 0
            );
        }





        $report_by_admin_overview = array(
            'admin_overview'    =>  array(
                'sales' =>  array( 
                    'value'  => wc_price($overview_sales), 
                    'label'    =>  __('Total Sales', 'dc-woocommerce-multi-vendor') 
                ),
                'admin_earning' =>  array( 
                    'value'  => wc_price($overview_admin_earning), 
                    'label'    =>  __('Admin Earnings', 'dc-woocommerce-multi-vendor') 
                ),
                'vendors'   =>  array( 
                    'value'  => ($overview_vendors), 
                    'label'    =>  __('Signup Vendors', 'dc-woocommerce-multi-vendor') 
                ),
                'pending_vendors'   =>  array( 
                    'value'  => ($pending_vendors), 
                    'label'    =>  __('Awaiting Vendors', 'dc-woocommerce-multi-vendor') 
                ),
                'products'  =>  array( 
                    'value'  => ($products), 
                    'label'    =>  __('Awaiting Products', 'dc-woocommerce-multi-vendor') 
                ),
                'transactions'  =>  array( 
                    'value'  => wc_price($transactions), 
                    'label'    =>  __('Awaiting Withdrawals', 'dc-woocommerce-multi-vendor') 
                ),
                'sales_data_chart'  =>  $sales_data_chart
            ),

            'vendor'    =>  array(
                'total_number_orders'   =>  array(
                    'value' =>  ($order_number_count),
                    'label' =>  __('Orders', 'dc-woocommerce-multi-vendor')
                ),
                'net_sales'   =>  array(
                    'value' =>  wc_price($report_vendor_net_sales),
                    'label' =>  __('Net Sales', 'dc-woocommerce-multi-vendor')
                ),
                'total_item_sold'   =>  array(
                    'value' =>  ($total_item_sold),
                    'label' =>  __('Items Sold', 'dc-woocommerce-multi-vendor')
                ),
                'sales_data_chart'  =>  $final_array_vendor_chart, /*array(
                    'total_orders_product_chart'    =>  $total_number_order_data_chart,
                    'product_sales_data_chart'      =>  $net_sales_data_chart,
                    'product_item_sold_chart'       =>  $product_item_sold_chart
                )*/
                'vendor_report_datatable'  =>  $vendor_report_datatable
            ),

            'product'   =>  array(
                'total_orders_product'  =>  array(
                    'value' =>  ($total_orders_product),
                    'label' =>  __('Orders', 'dc-woocommerce-multi-vendor')
                ),
                'net_sales'  =>  array(
                    'value' =>  wc_price($report_product_net_sales),
                    'label' =>  __('Net Sales', 'dc-woocommerce-multi-vendor')
                ),
                'total_item_sold'  =>  array(
                    'value' =>  ($total_item_sold),
                    'label' =>  __('Items Sold', 'dc-woocommerce-multi-vendor')
                ),
                'sales_data_chart'  =>  $final_array_product_chart,/*array(
                    'total_orders_product_chart'    =>  $total_orders_product_chart,
                    'product_sales_data_chart'      =>  $product_sales_data_chart,
                    'product_item_sold_chart'       =>  $product_item_sold_chart
                )*/
                'product_report_datatable'  =>  $product_report_datatable
            ),
            'banking_overview'  =>  $banking_datas
        );

        //print_r($report_by_admin_overview);die;
        return rest_ensure_response($report_by_admin_overview);
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


        $meta_list = $meta_list_associate_vendor = array();

        if ( $vendor ) {
            /* translators: %s: associated vendor */
            $vendor_string = sprintf(
                __( '<div class="mvx-commission-label-class">Associated vendor</div> <div class="mvx-commission-value-class">%s</div>', 'dc-woocommerce-multi-vendor' ),
                '<a href="' . sprintf('?page=%s&ID=%s&name=vendor-personal', 'vendors', $vendor->id) . '" target="_blank">'.$vendor->page_title.'</a>'
            );

            $meta_list_associate_vendor[] = $vendor_string;
        }

        /* translators: %s: Commission status */
        $status = MVX_Commission::get_status($commission_id, 'edit');
        $status_html = '';
        if($status == 'paid') {
            $status_html .= '<p class="commission-status-paid">'.MVX_Commission::get_status($commission_id).'</p>';
        }else{
            $status_html .= '<p class="commission-status-unpaid">'.MVX_Commission::get_status($commission_id).'</p>';
        }

        $meta_list[] = sprintf(
            __( '<p class="commission-status-text-check">Commission status: </p> %s', 'dc-woocommerce-multi-vendor' ),
            $status_html
        );

        $meta_list_associate_vendor = wp_kses_post( implode( '. ', $meta_list_associate_vendor ) );
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
                '_vendor_order_item_id',
                '_vendor_id',
                'Sold By'
            )
        );
        if ($line_items) {
            foreach ($line_items as $item_id => $item) {
                $product = $item->get_product();
                $product_link = $product ? admin_url('post.php?post=' . $item->get_product_id() . '&action=edit') : '';
                $thumbnail = $product ? apply_filters('mvx_admin_commission_order_item_thumbnail', $product->get_image(array(50, 50), array('title' => '')), $item_id, $item) : '';
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
        $shipping_items_meta_details  =   [];
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
                $refunded = $order->get_total_refunded_for_item($item_id, 'shipping');

                if ($meta_data = $item->get_formatted_meta_data('')) {
                    foreach ($meta_data as $meta_id => $meta) {
                        if (in_array($meta->key, $hidden_order_itemmeta, true)) {
                            continue;
                        }
                        $shipping_items_meta_details[]  =   array(
                            'display_key'   =>  wp_kses_post($meta->display_key),
                            'display_value' =>  wp_kses_post(force_balance_tags($meta->display_value)),
                            

                        );
                    }
                }

                $shipping_items_details   =   array(
                    'shipping_text'   =>  esc_html($item->get_name() ? $item->get_name() : __('Shipping', 'dc-woocommerce-multi-vendor') ),
                    'meta_data' =>  $shipping_items_meta_details,
                    'refunded_amount'   =>  $refunded ? '<small class="refunded">-' . wc_price($refunded, array('currency' => $order->get_currency())) . '</small>' : '',
                    'shipping_price'    =>  wc_price($item->get_total(), array('currency' => $order->get_currency())),
                    'refunded_shipping' =>  $refunded ? '<small class="refunded">-' . wc_price($refunded, array('currency' => $order->get_currency())) . '</small>' : ''
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


        // shipping method
        $shipping_methods = WC()->shipping() ? WC()->shipping->load_shipping_methods() : array();




        $payment_details = array(
            'commission_id' => $commission_id,
            'commission_order_id'   => $commission_order_id,
            'commission_type_object'    =>  $commission_type_object,
            'vendor_edit_link'  => sprintf('?page=%s&ID=%s&name=vendor-personal', 'mvx#&submenu=vendor', $vendor->id),
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
            'meta_list_associate_vendor'    =>  $meta_list_associate_vendor,
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
            'notes_data'    =>  $notes_data,
            'shipping_items_details'    =>  $shipping_items_details
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

    public function mvx_export_csv_for_report_product_chart($request) {
        global $MVX;
        $product_list = $request->get_param('product_list') ? ($request->get_param('product_list')) : array();
        $csv_data_data = array();

        foreach ($product_list as $value_details) {
            $csv_data_data[] = array(
                'Product Name'      =>  $value_details['title'],
                'Net Sales'     =>  $value_details['gross'],
                'Admin Earning'      =>  $value_details['admin_earning'],
                'Vendor Earning'    =>  $value_details['vendor_earning'],
            );
        }
        return rest_ensure_response($csv_data_data);
    }

    public function mvx_export_csv_for_report_vendor_chart($request) {
        global $MVX;
        $vendor_list = $request->get_param('vendor_list') ? ($request->get_param('vendor_list')) : array();
        $csv_data_data = array();

        foreach ($vendor_list as $value_details) {
            $csv_data_data[] = array(
                'Vendor Name'       =>  $value_details['defalt_title'],
                'Net Sales'         =>  $value_details['gross'],
                'Admin Earning'     =>  $value_details['admin_earning'],
                'Vendor Earning'    =>  $value_details['vendor_earning'],
            );
        }
        return rest_ensure_response($csv_data_data);
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
        //$option_lists[] = array('value' => 'all', 'label' => __('Show All Commission', 'dc-woocommerce-multi-vendor'));
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

                $action_display = "
                    <div class='mvx-vendor-action-icon'>
                        <span class='dashicons dashicons-edit'></span>
                        <span class='dashicons dashicons-no'></span>
                    </div>
                ";

                if (get_post_meta($commission_value, '_paid_status', true) == "paid") {
                    $status_display = "<p class='commission-status-paid'>" . __('Paid', 'dc-woocommerce-multi-vendor') . "</p>";
                } else {
                    $status_display = "<p class='commission-status-unpaid'>" . ucfirst(get_post_meta($commission_value, '_paid_status', true)) . "</p>";
                }

                $commission_list[] = array(
                    'id'            =>  $commission_value,
                    'title'         =>  '<a href="' . sprintf('?page=%s&CommissionID=%s', 'mvx#&submenu=commission', $commission_value) . '">#' . $commission_details->post_title . '</a>',
                    'order_id'      =>  '<a href="' . esc_url($edit_url) . '">#' . $order_id . '</a>',
                    'product'       =>  $product_list,
                    'vendor'        =>  $vendor_list,
                    'amount'        =>  $commission_amount,
                    'net_earning'   =>  $net_earning,
                    'status'        =>  $status_display,
                    'date'          =>  $commission_details->post_modified,
                    'action'        =>  $action_display
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
                        'zone_name' => "<a href='". sprintf('?page=%s&ID=%s&name=%s&zone_id=%s', 'mvx#&submenu=vendor', $vendor_id, 'vendor_shipping', $vendor_shipping_zones['zone_id']) ."'>". $vendor_shipping_zones['zone_name'] ."</a>",
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

    public function mvx_product_list_option() {
        $option_lists[] = array('value' => 'all', 'label' => __('All Product', 'dc-woocommerce-multi-vendor'));
        $products = get_posts( array( 'post_type' => 'product', 'posts_per_page' => -1, 'fields' => 'ids' ) );
        if ($products) {
            foreach($products as $product_id) {
                $product = wc_get_product($product_id);
                $option_lists[] = array(
                    'value' => sanitize_text_field($product_id),
                    'label' => $product->get_name()
                );
            }
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
            $redirect_to = apply_filters('mvx_add_new_vendor_redirect_url', admin_url('admin.php?page=vendors&ID='.$user_id. '&name=vendor-personal'));
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
        $modulename = $request->get_param('modulename') ? ( $request->get_param('modulename') ) : 0;

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
                if ($modulename == 'vendor-personal') {
                    $userdata = array(
                        'ID' => $user_id,
                        /*'user_login' => isset( $model['user_login'] ) ? sanitize_user( $model['user_login'] ) : '',
                        'user_pass' => isset( $model['password'] ) ? sanitize_text_field( wp_unslash( $model['password'] ) ) : '',
                        'user_email' => isset( $model['user_email'] ) ? sanitize_email( $model['user_email'] ) : '',
                        'user_nicename' => isset( $model['user_nicename'] ) ? sanitize_text_field( wp_unslash( $model['user_nicename'] ) ) : '',
                        'display_name' => isset( $model['display_name'] ) && isset($model['display_name']['value']) ? $model['display_name']['value'] : '',
                        'first_name' => isset( $model['first_name'] ) ? sanitize_text_field( wp_unslash( $model['first_name'] ) ) : '',
                        'last_name' => isset( $model['last_name'] ) ? sanitize_text_field( wp_unslash( $model['last_name'] ) ) : '',*/
                    );

                    if (isset($model['user_login']) && !empty($model['user_login'])) {
                        $userdata['user_login'] = isset( $model['user_login'] ) ? sanitize_user( $model['user_login'] ) : '';
                    }
                    if (isset($model['last_name']) && !empty($model['last_name'])) {
                        $userdata['last_name'] = isset( $model['last_name'] ) ? sanitize_text_field( wp_unslash( $model['last_name'] ) ) : '';
                    }
                    if (isset($model['first_name']) && !empty($model['first_name'])) {
                        $userdata['first_name'] = isset( $model['first_name'] ) ? sanitize_text_field( wp_unslash( $model['first_name'] ) ) : '';
                    }
                    if (isset($model['user_nicename']) && !empty($model['user_nicename'])) {
                        isset( $model['user_nicename'] ) ? sanitize_text_field( wp_unslash( $model['user_nicename'] ) ) : '';
                    }

                    if (isset($model['user_email']) && !empty($model['user_email'])) {
                        $userdata['user_email'] = isset( $model['user_email'] ) ? sanitize_email( $model['user_email'] ) : '';
                    }
                    if (isset($model['user_pass']) && !empty($model['user_pass'])) {
                        $userdata['user_pass'] = isset( $model['password'] ) ? sanitize_text_field( wp_unslash( $model['password'] ) ) : '';
                    }
                    if (isset($model['display_name']['value']) && !empty($model['display_name']['value'])) {
                        $userdata['display_name'] = isset( $model['display_name'] ) && isset($model['display_name']['value']) ? $model['display_name']['value'] : '';
                    }

                    $user_id = wp_update_user( $userdata ) ;
                }

                if ($modulename == 'vendor-store' || $modulename == 'vendor-social' || $modulename == 'vendor-payments') {
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
        if ($modulename == 'vendor-policy') {
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
        global $MVX;
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

            $vendor_profile_image = get_user_meta($user->data->ID, '_vendor_profile_image', true);
            if(isset($vendor_profile_image)) $image_info = wp_get_attachment_image_src( $vendor_profile_image , array(32, 32) );

            $name_display = "<div class='mvx-vendor-icon-name'><img src='". $MVX->plugin_url.'assets/images/dclogo.png' ."' width='20' height='20' ></img><a href='". sprintf('?page=%s&ID=%s&name=vendor-personal', 'mvx#&submenu=vendor', $user->data->ID) ."'>" . $user->data->display_name . "</a>|   |<a href='".$vendor->permalink."'>Shop</a></div>";

            $action_display = "
            <div class='mvx-vendor-action-icon'>
                <span class='dashicons dashicons-edit'></span>
                <span class='dashicons dashicons-no'></span>
            </div>";

            $user_list[] = apply_filters('mvx_list_table_vendors_columns_data', array(
                'ID' => $user->data->ID,
                'name' => $name_display,
                'email' => $user->data->user_email,
                'registered' => get_date_from_gmt( $user->data->user_registered ),
                'products' => $product_count,
                'status' => $status,
                'permalink' => $vendor_permalink,
                'username' => $user->data->user_login,
                'action'    => $action_display 
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
                $form_data[$key]['hidden'] = false;
            }
        }
        mvx_update_option('mvx_new_vendor_registration_form_data', $form_data);
        die;
    }

    public function mvx_get_registration_forms_data() {
        $mvx_vendor_registration_form_data = mvx_get_option('mvx_new_vendor_registration_form_data') ? mvx_get_option('mvx_new_vendor_registration_form_data') : [];
        return rest_ensure_response( $mvx_vendor_registration_form_data );
    }

    public function mvx_save_dashpages($req) {
        $all_details = array();
        $modulename = $req->get_param('modulename');
        $modulename = str_replace("-", "_", $modulename);
        $get_managements_data = $req->get_param( 'model' );
        $optionname = 'mvx_'.$modulename.'_tab_settings';

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
