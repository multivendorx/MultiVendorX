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
    }

    public function mvx_vendor_details($request) {
        $vendor_id = $request->get_param( 'vendor_id' );
        $uniquename = $request->get_param( 'uniquename' );
        return 
        print_r($uniquename);die;
    }
    
    public function mvx_all_vendor_details() {
        $user_list = array();
        $user_query = new WP_User_Query(array('role' => 'dc_vendor', 'orderby' => 'registered', 'order' => 'ASC'));
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
            $user_list[] = apply_filters('mvx_list_table_vendors_columns_data', array(
                'ID' => $user->data->ID,
                'name' => $user->data->display_name,
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
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'paypal-masspay',
                        'name'         => __( 'PayPal Masspay', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Schedule payment to multiple vendors at the same time.', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                       
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'paypal-payout',
                        'name'         => __( 'PayPal Payout', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Send payments automatically to multiple vendors as per scheduled', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'paypal-marketplace',
                        'name'         => __( 'PayPal Marketplace (Real time Split)', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Using  split payment pay vendors instantly after a completed order ', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active' => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'stripe-connect',
                        'name'         => __( 'Stripe Connect', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Connect to vendors stripe account and make hassle-free transfers as scheduled.', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'stripe-marketplace',
                        'name'         => __( 'Stripe Marketplace (Real time Split)', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Real-Time Split payments pays vendor directly after a completed order', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active' => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'mangopay',
                        'name'         => __( 'Mangopay', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Gives the benefit of both realtime split transfer and scheduled distribution', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active' => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'razorpay',
                        'name'         => __( 'Razorpay', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'For clients looking to pay multiple Indian vendors instantly', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WCMp Razorpay Split Payment', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wordpress.org/plugins/wcmp-razorpay-split-payment/',
                                'is_active' => is_plugin_active('wcmp-razorpay-split-payment/wcmp-razorpay-checkout-gateway.php') ? true :false,
                            )
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
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
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'zone-wise-shipping',
                        'name'         => __( 'Zone-Wise Shipping', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Limit vendors to sell in selected zones', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                        'parent_category' => __( 'Shipping.', 'dc-woocommerce-multi-vendor' ),
                    ],
                    [
                        'id'           => 'distance-shipping',
                        'name'         => __( 'Distance Shipping', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Calculate Rates based on distance between the vendor store and drop location', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'country-wise-shipping',
                        'name'         => __( 'Country-Wise Shipping', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Let vendors choose and manage shipping, to countries of their choice', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'weight-wise-shipping',
                        'name'         => __( 'Weight Wise Shipping (using Table Rate Shipping)', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Vendors can create shipping rates based on price, weight and quantity', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'per-product-shipping',
                        'name'         => __( 'Per Product Shipping', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'let vendors add shipping cost to specific products', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
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
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
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
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'vendor-vacation',
                        'name'         => __( 'Vacation', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'On vacation mode, vendor can allow / disable sale & notify customer accordingly', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'business-hours',
                        'name'         => __( 'Business Hours', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Gives vendors the option to set and manage business timings', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'vendor-staff',
                        'name'         => __( 'Staff Manager', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Lets vendors hire and manage staff to support store', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'store-follow',
                        'name'         => __( 'Follow Store', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Permit customers to follow store, receive updates & lets vendors keep track of customers', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'vendor-review',
                        'name'         => __( 'Store Review', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Allows customers to rate and review stores and their purchased products', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'import-export',
                        'name'         => __( 'Product Import/Export  ', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Helps vendors seamlessly import or export product data using CSV etc', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'refund',
                        'name'         => __( 'Marketplace Refund', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Enable customer refund requests & Let vendors manage customer refund ', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'single-product-multiple-vendor',
                        'name'         => __( 'Single Product Multiple Vendor', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Lets multiple vendors sell the same products ', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
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
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
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
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'invoice-management',
                        'name'         => __( 'Invoice', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Send invoice and packaging slips to vendor', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'wholesale',
                        'name'         => __( 'Wholesale', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Set wholesale price and quantity for customers ', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'report-abuse',
                        'name'         => __( 'Report Abuse', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Lets customers report false products', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'live-chat',
                        'name'         => __( 'Live Chat', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Allows real-time messaging between vendors and customers', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'seller-subscription',
                        'name'         => __( 'Makertplace  Membership', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Lets Admin create marketplace memberships levels and manage vendor-wise individual capablity  ', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'store-location',
                        'name'         => __( 'Store Location', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( "If enabled customers can view a vendor's store location", 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'store-policy',
                        'name'         => __( 'Store Policy', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Offers vendors the option to set individual store specific policies', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
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
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'knowladgebase',
                        'name'         => __( 'Knowladgebase', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Admin can share tutorials and othe vendor-specific information with vendors', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
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
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                        'parent_category' => __( 'Marketplace Products.', 'dc-woocommerce-multi-vendor' ),
                    ],
                    [
                        'id'           => 'Variable',
                        'name'         => __( 'Variable', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'A product with variations, like different SKU, price, stock option, etc.', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'External',
                        'name'         => __( 'External', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Grants vendor the option to  list and describe on admin website but sold elsewhere', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'grouped',
                        'name'         => __( 'Grouped', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'A cluster of simple related products that can be purchased individually', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WC Marketplace Pro', 'dc-woocommerce-multi-vendor'),
                                'plugin_link'   => 'https://wc-marketplace.com/addons/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://wc-marketplace.com/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'booking',
                        'name'         => __( 'Booking', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Allow customers to book appointments, make reservations or rent equipment etc', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
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
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'subscription',
                        'name'         => __( 'Subscription', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Let customers subscribe to your products or services and pay weekly, monthly or yearly ', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
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
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'accommodation',
                        'name'         => __( 'Accommodation', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Grant your guests the ability to quickly book overnight stays in a few clicks', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
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
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'product-bundle',
                        'name'         => __( 'Bundle', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Offer personalized product bundles, bulk discount packages, and assembled products.', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
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
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'simple-auction',
                        'name'         => __( 'Auction', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Implement an auction system similar to eBay on your store', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
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
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'rental-pro',
                        'name'         => __( 'Rental', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Perfect for those desiring to offer rental, booking, or real state agencies or services.', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
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
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
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
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
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
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
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
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'acf',
                        'name'         => __( 'Advance Custom field', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Allows for an on demand product field in Add Product section', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
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
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                        'category'  => 'store boosters',
                    ],
                    [
                        'id'           => 'geo-my-wp',
                        'name'         => __( 'GEOmyWP', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Offer vendor the option to attach location info along with their products', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
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
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'toolset',
                        'name'         => __( 'Toolset Types', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( "Allows admin to create custom fields, and taxonomy for vendor's product field", 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
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
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'affiliate',
                        'name'         => __( 'WP Affiliate', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Launch affiliate programme into your marketplace', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
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
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
                    ],
                    [
                        'id'           => 'product-addons',
                        'name'         => __( 'Product Addon', 'dc-woocommerce-multi-vendor' ),
                        'description'  => __( 'Offer add-ons like gift wrapping, special messages etc along with primary products', 'dc-woocommerce-multi-vendor' ),
                        'plan'         => apply_filters('is_wcmp_pro_plugin_inactive', true) ? 'pro' : 'free',
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
                        'mod_link'     => admin_url('admin.php?page=wcmp-setting-admin'),
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
        $is_module_active = get_option('wcmp_all_active_module_list', true);
        $is_active = $is_module_active && is_array($is_module_active) && in_array($module_name, $is_module_active) ? true : false;
        return $is_active;
    }

}
