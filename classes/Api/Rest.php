<?php

namespace MultiVendorX\Api;

use MultiVendorX\Order\OrderManager;

class Rest {
    /**
     * Rest class constructor function
     */
    public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_rest_apis' ] );
    }

    /**
     * Register rest api
     * @return void
     */
    function register_rest_apis() {
        register_rest_route( MVX()->rest_namespace, '/get_store', [
            'methods'               => \WP_REST_Server::READABLE,
            'callback'              => [ $this, 'store_settings' ],
            'permission_callback'   => [ $this, 'permissions_check' ],
        ] );

        register_rest_route( MVX()->rest_namespace, '/set_store', [
            'methods'               => \WP_REST_Server::EDITABLE,
            'callback'              => [ $this, 'save_store_settings' ],
            'permission_callback'   => [ $this, 'permissions_check' ],
        ] );

        register_rest_route( MVX()->rest_namespace, '/get_commission_setting', [
            'methods'               => \WP_REST_Server::READABLE,
            'callback'              => [ $this, 'get_commission_setting' ],
            'permission_callback'   => [ $this, 'permissions_check' ],
        ] );

        register_rest_route( MVX()->rest_namespace, '/set_commission_setting', [
            'methods'               => \WP_REST_Server::EDITABLE,
            'callback'              => [ $this, 'set_commission_setting' ],
            'permission_callback'   => [ $this, 'permissions_check' ],
        ] );
        
        register_rest_route( MVX()->rest_namespace, '/get_payment_setting', [
            'methods'               => \WP_REST_Server::READABLE,
            'callback'              => [ $this, 'get_payment_setting' ],
            'permission_callback'   => [ $this, 'permissions_check' ],
        ] );
        
        register_rest_route( MVX()->rest_namespace, '/set_payment_setting', [
            'methods'               => \WP_REST_Server::EDITABLE,
            'callback'              => [ $this, 'set_payment_setting' ],
            'permission_callback'   => [ $this, 'permissions_check' ],
        ] );
        
        register_rest_route( MVX()->rest_namespace, '/get_capability_setting', [
            'methods'               => \WP_REST_Server::READABLE,
            'callback'              => [ $this, 'get_capability_setting' ],
            'permission_callback'   => [ $this, 'permissions_check' ],
        ] );
        
        register_rest_route( MVX()->rest_namespace, '/set_capability_setting', [
            'methods'               => \WP_REST_Server::EDITABLE,
            'callback'              => [ $this, 'set_capability_setting' ],
            'permission_callback'   => [ $this, 'permissions_check' ],
        ] );
        
        register_rest_route( MVX()->rest_namespace, '/get_active_plugins', [
            'methods'               => \WP_REST_Server::READABLE,
            'callback'              => [ $this, 'get_active_plugins' ],
            'permission_callback'   => [ $this, 'permissions_check' ],
        ] );
        
        register_rest_route( MVX()->rest_namespace, '/import_dummy_data', [
            'methods'               => \WP_REST_Server::EDITABLE,
            'callback'              => [ $this, 'import_dummy_data' ],
            'permission_callback'   => [ $this, 'permissions_check' ],
        ] );

        register_rest_route( MVX()->rest_namespace, '/install_woocommerce', [
            'methods'               => \WP_REST_Server::READABLE,
            'callback'              => [ $this, 'install_woocommerce' ],
            'permission_callback'   => [ $this, 'permissions_check' ],
        ] );
    }

    function store_settings( $request ){
        $nonce = $request->get_header('X-WP-Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new \WP_Error('rest_invalid_nonce', __('Invalid nonce', 'multivendorx'), array('status' => 403));
        }

        $permalinks = mvx_get_option('dc_vendors_permalinks');
        $vendor_slug = empty($permalinks['vendor_shop_base']) ? _x('', 'slug', 'multivendorx') : $permalinks['vendor_shop_base'];
        $is_single_product_multiple_vendor = get_mvx_global_settings('is_singleproductmultiseller') ? 'Enable' : '';
        $data = array(
            'vendor_slug'                        => $vendor_slug,
            'is_single_product_multiple_vendor'  => $is_single_product_multiple_vendor,
            'site_url'                           => site_url()
        );

        return rest_ensure_response( $data );
    }

    function save_store_settings( $request ){
        $nonce = $request->get_header('X-WP-Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new \WP_Error('rest_invalid_nonce', __('Invalid nonce', 'multivendorx'), array('status' => 403));
        }

        $general_settings = mvx_get_option('mvx_spmv_pages_tab_settings') ? mvx_get_option('mvx_spmv_pages_tab_settings') : array();
        $vendor_permalink = $request->get_param('vendor_store_url');
        $is_single_product_multiple_vendor = $request->get_param('is_single_product_multiple_vendor');

        if ($is_single_product_multiple_vendor) {
            $general_settings['is_singleproductmultiseller'] = array('is_singleproductmultiseller');
        } else if (isset($general_settings['is_singleproductmultiseller'])) {
            unset($general_settings['is_singleproductmultiseller']);
        }

        mvx_update_option('mvx_spmv_pages_tab_settings', $general_settings);
        if ($vendor_permalink) {
            $permalinks = mvx_get_option('dc_vendors_permalinks', array());
            $permalinks['vendor_shop_base'] = untrailingslashit($vendor_permalink);
            mvx_update_option('dc_vendors_permalinks', $permalinks);
            flush_rewrite_rules();
        }

        return rest_ensure_response(array(
            'success' => true,
            'message' => __('Store settings updated successfully.', 'multivendorx'),
        ));
    }

    public function get_commission_setting( $request ){
        $nonce = $request->get_header('X-WP-Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new \WP_Error('rest_invalid_nonce', __('Invalid nonce', 'multivendorx'), array('status' => 403));
        }
        $payment_settings = mvx_get_option('mvx_commissions_tab_settings');

        if(isset($payment_settings['revenue_sharing_mode'])){
            $revenue_sharing_mode = $payment_settings['revenue_sharing_mode'];
        }else{
            $revenue_sharing_mode = 'revenue_sharing_mode_vendor';
        }

        if( isset($payment_settings['commission_type']['value']) ){
            $commission_type = $payment_settings['commission_type']['value'];
        }else{
            $commission_type = 'percent';
        }
        
        if( isset($payment_settings['default_commission'][0]['value']) ){
            if( $commission_type === 'fixed' ){
                $fixed      = $payment_settings['default_commission'][0]['value'];
                $percentage = '';
            }elseif( $commission_type === 'percent' ){
                $fixed      = '';
                $percentage = $payment_settings['default_commission'][0]['value'];
            }else{
                $fixed      = $payment_settings['default_commission'][1]['value'];
                $percentage = $payment_settings['default_commission'][0]['value'];
            }
        }

        $data = array(
            'revenue_sharing_mode'=> $revenue_sharing_mode,
            'commission_type'     => $commission_type,
            'fixed'               => $fixed,
            'percentage'          => $percentage
        );

        return rest_ensure_response( $data );
    }

    public function set_commission_setting( $request ){
        $nonce = $request->get_header('X-WP-Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new \WP_Error('rest_invalid_nonce', __('Invalid nonce', 'multivendorx'), array('status' => 403));
        }

        $payment_settings     = mvx_get_option('mvx_commissions_tab_settings');
        $revenue_sharing_mode = $request->get_param('revenue_sharing_mode');
        $commission_type      = $request->get_param('commission_type');
        $default_commission   = $request->get_param('fixed');
        $default_percentage   = $request->get_param('percentage');

        if ($revenue_sharing_mode) {
            $payment_settings['revenue_sharing_mode'] = $revenue_sharing_mode;
        }
        if ($commission_type) {
            switch($commission_type){
                case 'fixed':
                    $payment_settings['commission_type'] = array(
                        'value'=> __('fixed', 'multivendorx'),
                        'label'=> __('Fixed Amount', 'multivendorx'),
                        'index'=> 1,
                    );
                    $payment_settings['default_commission'] = $default_commission;
                    $payment_settings['default_commission'] = array(
                        0 => array (
                                'key' => 'fixed_ammount',
                                'value' => $default_commission,
                                )
                    );
                    break;

                case 'percent':
                    $payment_settings['commission_type'] = array(
                        'value'=> __('percent', 'multivendorx'),
                        'label'=> __('Percentage', 'multivendorx'),
                        'index'=> 2,
                    );
                    $payment_settings['default_percentage'] = $default_percentage;
                    $payment_settings['default_commission'] = array(
                        0 => array (
                            'key' => 'percent_amount',
                            'value' => $default_percentage
                        )
                    );
                    break;

                case 'fixed_with_percentage':
                    $payment_settings['commission_type'] = array(
                        'value'=> __('fixed_with_percentage', 'multivendorx'),
                        'label'=> __('%age + Fixed (per transaction)', 'multivendorx'),
                        'index'=> 3,
                    );
                    $payment_settings['fixed_with_percentage'] = $default_commission;
                    $payment_settings['default_commission'] = array(
                        0 => array (
                            'key' => 'percent_amount',
                            'value' => $default_percentage
                        ),
                        1 => array (
                            'key' => 'percent_amount',
                            'value' => $default_commission
                        )
                    );
                    break;

                case 'fixed_with_percentage_qty':
                    $payment_settings['commission_type'] = array(
                        'value'=> __('fixed_with_percentage_qty', 'multivendorx'),
                        'label'=> __('%age + Fixed (per unit)', 'multivendorx'),
                        'index'=> 4,
                    );
                    $payment_settings['fixed_with_percentage_qty'] = $default_commission;
                    $payment_settings['default_commission'] = array(
                        0 => array (
                            'key' => 'fixed_ammount',
                            'value' => $default_percentage
                        ),
                        1 => array (
                            'key' => 'percent_amount',
                            'value' => $default_commission
                        )
                    );
                    break;
            }
        }
        mvx_update_option('mvx_commissions_tab_settings', $payment_settings);

        return rest_ensure_response(array(
            'success' => true,
            'message' => __('Commission settings updated successfully.', 'multivendorx'),
        ));
    }

    public function get_payment_setting( $request ){
        $nonce = $request->get_header('X-WP-Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new \WP_Error('rest_invalid_nonce', __('Invalid nonce', 'multivendorx'), array('status' => 403));
        }

        $disbursement_settings = mvx_get_option('mvx_disbursement_tab_settings');
        $mvx_disbursal_mode_admin = isset($disbursement_settings['choose_payment_mode_automatic_disbursal']);
        if ( isset($disbursement_settings['payment_schedule']) ){
            $payment_schedule = $disbursement_settings['payment_schedule'];
        }else{
            $payment_schedule = 'monthly';
        }
        $mvx_disbursal_mode_vendor = isset($disbursement_settings['withdrawal_request']);
        $is_enable_gateway = array(
            'paypal_masspay'=> mvx_is_module_active('paypal_masspay'),
            'paypal_payout' => mvx_is_module_active('paypal_payout'),
            'direct_bank'   => mvx_is_module_active('direct_bank'),
            'stripe_masspay'=> mvx_is_module_active('stripe_masspay'),

        );
        $data = array(
            'mvx_disbursal_mode_admin'  => $mvx_disbursal_mode_admin,
            'payment_schedule'          => $payment_schedule,
            'mvx_disbursal_mode_vendor' => $mvx_disbursal_mode_vendor,
            'is_enable_gateway'         => $is_enable_gateway,
        );

        return rest_ensure_response( $data );
    }

    public function set_payment_setting( $request ) {
        $nonce = $request->get_header('X-WP-Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new \WP_Error('rest_invalid_nonce', __('Invalid nonce', 'multivendorx'), array('status' => 403));
        }

        $gateways = $this->get_payment_methods();
        $active_module_list = mvx_get_option('mvx_all_active_module_list') ? get_option('mvx_all_active_module_list') : array();

        $disbursement_settings = mvx_get_option('mvx_disbursement_tab_settings');
        $mvx_disbursal_mode_admin = $request->get_param('mvx_disbursal_mode_admin');
        $mvx_disbursal_mode_vendor = $request->get_param('mvx_disbursal_mode_vendor');
        $is_module_active = $request->get_param('is_module_active');

        if ($mvx_disbursal_mode_admin) {
            $disbursement_settings['choose_payment_mode_automatic_disbursal'] = array('choose_payment_mode_automatic_disbursal');
            $payment_schedule = $request->get_param('payment_schedule');
            if ($payment_schedule) {
                $disbursement_settings['payment_schedule'] = $payment_schedule;
                $schedule = wp_get_schedule('masspay_cron_start');
                if ($schedule != $payment_schedule) {
                    if (wp_next_scheduled('masspay_cron_start')) {
                        $timestamp = wp_next_scheduled('masspay_cron_start');
                        wp_unschedule_event($timestamp, 'masspay_cron_start');
                    }
                    wp_schedule_event(time(), $payment_schedule, 'masspay_cron_start');
                }
            }
        } else if (isset($disbursement_settings['choose_payment_mode_automatic_disbursal'])) {
            unset($disbursement_settings['choose_payment_mode_automatic_disbursal']);
            if (wp_next_scheduled('masspay_cron_start')) {
                $timestamp = wp_next_scheduled('masspay_cron_start');
                wp_unschedule_event($timestamp, 'masspay_cron_start');
            }
        }

        if ($mvx_disbursal_mode_vendor) {
            $disbursement_settings['withdrawal_request'] = array('withdrawal_request');
        } else if (isset($disbursement_settings['withdrawal_request'])) {
            unset($disbursement_settings['withdrawal_request']);
        }
        
        foreach ($gateways as $gateway_id => $gateway) {
            $is_enable_gateway = $is_module_active[$gateway_id];
            if ($is_enable_gateway) {
                //$payment_settings['payment_method_disbursement'][$gateway_id] = str_replace('payment_method_', '', $is_enable_gateway);
                array_push($active_module_list, $gateway_id);
                if (!empty($gateway['repo-slug'])) {
                    wp_schedule_single_event(time() + 10, 'woocommerce_plugin_background_installer', array($gateway_id, $gateway));
                }
            } else if (mvx_is_module_active($gateway_id)) {
                if (($key = array_search($gateway_id, $active_module_list)) !== false) {
                    unset($active_module_list[$key]);
                }
            }
        }

        //mvx_update_option('mvx_commissions_tab_settings', $payment_settings);
        mvx_update_option( 'mvx_all_active_module_list', $active_module_list );
        mvx_update_option('mvx_disbursement_tab_settings', $disbursement_settings);

        return rest_ensure_response(array(
            'success' => true,
            'message' => __('Payment settings updated successfully.', 'multivendorx'),
        ));
    }

    function get_capability_setting( $request ){
        $nonce = $request->get_header('X-WP-Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new \WP_Error('rest_invalid_nonce', __('Invalid nonce', 'multivendorx'), array('status' => 403));
        }

        $capabilities_settings = mvx_get_option('mvx_products_capability_tab_settings');
        $data = array(
            'is_submit_product'               => isset($capabilities_settings['is_submit_product']),
            'is_published_product'            => isset($capabilities_settings['is_published_product']),
            'is_edit_delete_published_product'=> isset($capabilities_settings['is_edit_delete_published_product']),
            'is_submit_coupon'                => isset($capabilities_settings['is_submit_coupon']),
            'is_published_coupon'             => isset($capabilities_settings['is_published_coupon']),
            'is_edit_delete_published_coupon' => isset($capabilities_settings['is_edit_delete_published_coupon']),
            'is_upload_files'                 => isset($capabilities_settings['is_upload_files']),
        );

        return rest_ensure_response( $data );
    }

    public function set_capability_setting( $request ){
        $nonce = $request->get_header('X-WP-Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new \WP_Error('rest_invalid_nonce', __('Invalid nonce', 'multivendorx'), array('status' => 403));
        }

        global $MVX;
        $capability_settings = mvx_get_option('mvx_products_capability_tab_settings') ? mvx_get_option('mvx_products_capability_tab_settings') : array();

        $is_submit_product = $request->get_param('is_submit_product');
        $is_published_product = $request->get_param('is_published_product');
        $is_edit_delete_published_product = $request->get_param('is_edit_delete_published_product');
        $is_submit_coupon = $request->get_param('is_submit_coupon');
        $is_published_coupon = $request->get_param('is_published_coupon');
        $is_edit_delete_published_coupon = $request->get_param('is_edit_delete_published_coupon');
        $is_upload_files = $request->get_param('is_upload_files');

        if ($is_submit_product) {
            $capability_settings['is_submit_product'] = array('is_submit_product');
        } else if (isset($capability_settings['is_submit_product'])) {
            unset($capability_settings['is_submit_product']);
        }
        if ($is_published_product) {
            $capability_settings['is_published_product'] = array('is_published_product');
        } else if (isset($capability_settings['is_published_product'])) {
            unset($capability_settings['is_published_product']);
        }
        if ($is_edit_delete_published_product) {
            $capability_settings['is_edit_delete_published_product'] = array('is_edit_delete_published_product');
        } else if (isset($capability_settings['is_edit_delete_published_product'])) {
            unset($capability_settings['is_edit_delete_published_product']);
        }
        if ($is_submit_coupon) {
            $capability_settings['is_submit_coupon'] = array('is_submit_coupon');
        } else if (isset($capability_settings['is_submit_coupon'])) {
            unset($capability_settings['is_submit_coupon']);
        }
        if ($is_published_coupon) {
            $capability_settings['is_published_coupon'] = array('is_published_coupon');
        } else if (isset($capability_settings['is_published_coupon'])) {
            unset($capability_settings['is_published_coupon']);
        }
        if ($is_edit_delete_published_coupon) {
            $capability_settings['is_edit_delete_published_coupon'] = array('is_edit_delete_published_coupon');
        } else if (isset($capability_settings['is_edit_delete_published_coupon'])) {
            unset($capability_settings['is_edit_delete_published_coupon']);
        }
        if ($is_upload_files) {
            $capability_settings['is_upload_files'] = array('is_upload_files');
        } else if (isset($capability_settings['is_upload_files'])) {
            unset($capability_settings['is_upload_files']);
        }
        mvx_update_option('mvx_products_capability_tab_settings', $capability_settings);
        $MVX->vendor_caps->update_mvx_vendor_role_capability();
        file_put_contents( plugin_dir_path(__FILE__) . "/error.log", date("d/m/Y H:i:s", time()) . ":orders:  : " . var_export($capability_settings, true) . "\n", FILE_APPEND);

        return rest_ensure_response(array(
            'success' => true,
            'message' => __('Capability settings updated successfully.', 'multivendorx'),
        ));
    }

    public function get_active_plugins( $request ){
        $nonce = $request->get_header('X-WP-Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new \WP_Error('rest_invalid_nonce', __('Invalid nonce', 'multivendorx'), array('status' => 403));
        }

        $data = array(
            'booking' => is_plugin_active('woocommerce-bookings/woocommerce-bookings.php'),
            'subscription' => is_plugin_active('woocommerce-subscriptions/woocommerce-subscriptions.php'),
            'rental' => is_plugin_active('woocommerce-rental-and-booking/redq-rental-and-bookings.php'),
            'auction' => is_plugin_active('woocommerce-simple-auctions/woocommerce-simple-auctions.php')
        );

        return rest_ensure_response( $data );
    }

    public function import_dummy_data( $request ){
        $nonce = $request->get_header('X-WP-Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new \WP_Error('rest_invalid_nonce', __('Invalid nonce', 'multivendorx'), array('status' => 403));
        }

        $marketplace_type = $request->get_param('marketplace_type');

        $this->import_vendors();
        $product_ids = $this->import_products( $marketplace_type );
        $this->import_commissions();
        if( $product_ids ){
            $this->import_orders( $product_ids, $marketplace_type );
            $this->import_reviews($product_ids);
        }
        
        return rest_ensure_response(array(
            'success' => true,
            'message' => __('Dummy data imported successfully.', 'multivendorx'),
        ));
    }

    /**
     * Import the Dummy vendors
     */
    public function import_vendors(){
        // load the vendor data xml file
        $xml = simplexml_load_file( MVX_PLUGIN_DUMMY_DATA . '/vendors.xml' );
        if( ! $xml ){
            return;
        }
        
        // importing vendors
        foreach( $xml->vendor as $vendor){
            $userdata = array(
                'user_login'    => (string) $vendor->username,
                'user_pass'     => (string) $vendor->password,
                'user_email'    => (string) $vendor->email,
                'user_nicename' => (string) $vendor->nickname,
                'first_name'    => (string) $vendor->firstname,
                'last_name'     => (string) $vendor->lastname,
                'role'          => 'dc_vendor',
            );

            $user_id = wp_insert_user( $userdata ) ;

            if ( is_wp_error( $user_id ) ) {
                return;
            }

            foreach( $vendor->images->image as $image ){
                $src    =  plugins_url( $image->src );
                $upload = wc_rest_upload_image_from_url( esc_url_raw($src) );

                if( is_wp_error( $upload )){
                    continue;
                }
                $attachment_id = wc_rest_set_uploaded_image_as_attachment( $upload, $user_id );

                if ( wp_attachment_is_image( $attachment_id ) ) {
                    $parent = get_post($attachment_id);
                    $url    = $parent->guid;

                    $size       = @getimagesize($url);
                    $image_type = ( $size ) ? $size['mime'] : 'image/jpeg';
                    $object     = array(
                        'ID'             => $attachment_id,
                        'post_title'     => basename($url),
                        'post_mime_type' => $image_type,
                        'guid'           => $url,
                        'post_status'    => 'inherit',
                        );

                    $attachment_id = wp_insert_attachment($object, $url);

                    $metadata = wp_generate_attachment_metadata($attachment_id, $url);
                    wp_update_attachment_metadata($attachment_id, $metadata);

                    if ( 'store' == $image->position ) {
                        update_user_meta( $user_id, '_vendor_image', $attachment_id );   
                    } elseif ( 'cover' == $image->position ) {
                        update_user_meta( $user_id, '_vendor_banner', $attachment_id );
                    }
                }
            }
        }
    }

    /**
     * Import Dummy products
     * @global object $mvx
     */
    public function import_products($marketplace_type){
        global $MVX;

        $xml = simplexml_load_file( MVX_PLUGIN_DUMMY_DATA . '//'. $marketplace_type .'_products.xml' );
        if( ! $xml ){
            return;
        }
        
        $product_ids = array();

        // importing products
        foreach( $xml->item as $product){

            // Get vendor Id
            $vendor = get_user_by( 'slug', (string) $product->author );
            if ($vendor) {
                $vendor_id = $vendor->ID;
            }else {
                $vendor    = get_user_by( 'slug', 'admin');
                $vendor_id = $vendor->ID;
            }

            $product_data = array(
                'post_title'    => (string) $product->title, // Product Title
                'post_content'  => (string) $product->content, // Product Description
                'post_excerpt'  => (string) $product->excerpt, // Short Description
                'post_author'   => $vendor_id, // Vendor Id
                'post_name'     => (string) $product->post_name, // post name
                'post_status'   => (string) $product->status, // Publish the product
                'post_type'     => (string) $product->post_type, // WooCommerce product type
            );

            $product_id = wp_insert_post( $product_data );

            if ( is_wp_error( $product_id ) ) {
                return null;
            }
            // set the product type
            wp_set_object_terms( $product_id, (string) $product->product_type, 'product_type' );

            // Set product vendor
            $vendor = get_mvx_vendor( $vendor_id );
            if($vendor){
                wp_set_object_terms( $product_id, absint($vendor->term_id), $MVX->taxonomy->taxonomy_name );
            }
            // Set product category
            foreach( $product->categories->category as $category){
                $category_name = (string) $category;
                $term          = term_exists( $category_name, 'product_cat' );

                // If the category doesn't exist, create it
                if ( ! $term ) {
                    $term = wp_insert_term( $category_name, 'product_cat' );
                }

                if ( ! is_wp_error( $term ) ) {
                    // Get the term ID 
                    $term_id = is_array( $term ) ? $term['term_id'] : $term;

                    // Assign the category to the product
                    wp_set_object_terms( $product_id, (int)$term_id, 'product_cat', true );
                }
            }

            // Set product meta data
            foreach( $product->meta->postmeta as $meta){
                update_post_meta( $product_id, (string) $meta->meta_key, (string) $meta->meta_value );
            }

            array_push( $product_ids, $product_id);
            
            foreach( $product->images->image as $image ){
                $src    = plugins_url( $image->src );
                $upload = wc_rest_upload_image_from_url( esc_url_raw($src) );

                if( is_wp_error( $upload )){
                    continue;
                }
                $attachment_id = wc_rest_set_uploaded_image_as_attachment( $upload );

                if ( wp_attachment_is_image( $attachment_id ) ) {
                    $parent = get_post($attachment_id);
                    $url    = $parent->guid;

                    $size       = @getimagesize($url);
                    $image_type = ( $size ) ? $size['mime'] : 'image/jpeg';
                    $object     = array(
                        'ID' => $attachment_id,
                        'post_title' => basename($url),
                        'post_mime_type' => $image_type,
                        'guid' => $url,
                        'post_status'    => 'inherit',
                        );

                    $attachment_id = wp_insert_attachment($object, $url);

                    $metadata = wp_generate_attachment_metadata($attachment_id, $url);
                    wp_update_attachment_metadata($attachment_id, $metadata);

                    update_post_meta( $product_id, '_thumbnail_id', $attachment_id );
                }
            }
        }
        return $product_ids;
    }

    /**
     * Import commissions data
     */
    public function import_commissions(){
        // importing commission structure
        $xml = simplexml_load_file( MVX_PLUGIN_DUMMY_DATA . '/commissions.xml' );
        if( ! $xml){
            return;
        }

        foreach( $xml->commission as $commission ) {
            $category_name = (string) $commission->category;
            $term          = term_exists( $category_name, 'product_cat' );

            // If the category doesn't exist, create it
            if ( ! $term ) {
                $term = wp_insert_term( $category_name, 'product_cat' );
            }

            if ( is_wp_error( $term ) ) {
                return;
            }
            // Get the term ID 
            $term_id = is_array( $term ) ? $term['term_id'] : $term;

            // Get commission type
            $commission_type_value = get_mvx_vendor_settings('commission_type', 'commissions') && !empty(get_mvx_vendor_settings('commission_type', 'commissions')) ? mvx_get_settings_value(get_mvx_vendor_settings('commission_type', 'commissions')) : '';
            if ($commission_type_value) {
                switch($commission_type_value){
                    case 'fixed':        
                    case 'percent':
                        add_term_meta( $term_id, 'commision', floatval((string) $commission->value));
                        break;
    
                    case 'fixed_with_percentage':        
                    case 'fixed_with_percentage_qty':
                        add_term_meta( $term_id, 'commission_percentage', floatval((string) $commission->value));
                        break;
                }
            }
        }
    }

    /**
     * Import dummy orders
     * @global object $mvx
     */
    public function import_orders( $product_ids, $marketplace_type ){
        global $MVX;
        $product_ids = array_reverse( $product_ids );

        // importing orders
        $xml = simplexml_load_file( MVX_PLUGIN_DUMMY_DATA . '/orders.xml' );
        if( ! $xml){
            return;
        }

        foreach( $xml->order as $order){
            $new_order = wc_create_order();
            $order_id = $new_order->get_id();

            $product_id = array_pop( $product_ids );
            $quantity   = $order->quantity;
            $new_order->add_product( wc_get_product( $product_id ), $quantity );

            $address = array(
                'first_name' => (string) $order->first_name,
                'last_name'  => (string) $order->last_name,
                'email'      => (string) $order->email,
                'address_1'  => (string) $order->address_1,
                'address_2'  => (string) $order->address_2,
                'city'       => (string) $order->city,
                'state'      => (string) $order->state,
                'postcode'   => (string) $order->postcode,
                'country'    => (string) $order->country,
            );

            $new_order->set_billing_address( $address );
            $new_order->set_shipping_address( $address );
            $order_items = $new_order->get_items();
            $cost = intval(get_post_meta( $product_id, '_wc_booking_block_cost', true )) + intval(get_post_meta( $product_id, '_wc_booking_cost', true ));

            if( $marketplace_type == 'booking' ){
                $props = array(
                    'customer_id'   => '1',
                    'product_id'    => $product_id,
                    'resource_id'   => '',
                    'person_counts' => 'a:0:{}',
                    'cost'          => $cost,
                    'start'         => strtotime( current_time( 'Y-m-d' ). '00:00:00' ),
                    'end'           => strtotime( current_time( 'Y-m-d' ). '23:59:59' ),
                    'all_day'       => 1,
                );

                $booking = new \WC_Booking( $props );
                $booking->set_order_id( $order_id );
                $item = reset( $order_items );
                $item['subtotal'] = $cost;
                $item['total'] = $cost;
                $item_id = key( $order_items );
                $booking->set_order_item_id( $item_id );
                $booking->set_cost(intval(get_post_meta( $product_id, '_wc_booking_block_cost', true )) + intval(get_post_meta( $product_id, '_wc_booking_cost', true )));
                $booking->set_status( 'unpaid' );
                $booking->save();
            }

            $shipping_item = new \WC_Order_Item_Shipping();
            $shipping_item->set_method_title( 'Free shipping' );
            $shipping_item->set_method_id( 'free shipping' );

            // Set the shipping cost to 0
            $shipping_item->set_total( 0 );

            foreach( $order_items as $item_id => $item ){
                $vendor = get_mvx_product_vendors( $item['product_id'] );
                if ($vendor) {
                    if ( !wc_get_order_item_meta( $item_id, 'Sold by') ) 
                        wc_add_order_item_meta( $item_id, 'Sold by', $vendor->page_title);
                    if ( !wc_get_order_item_meta( $item_id, '_vendor_id' ) ) 
                        wc_add_order_item_meta( $item_id, '_vendor_id', $vendor->id);
                }
            }

            // Add the shipping item to the order
            $shipping_item->save();
            $new_order->add_item( $shipping_item );
        
            $new_order->set_payment_method( (string) $order->payment_method );
            $new_order->set_payment_method_title( (string) $order->payment_method_title );

            $new_order->update_meta_data('has_mvx_sub_order', true);
            $vendor_items = array();
            foreach ($order_items as $item_id => $item) {
                $has_vendor = get_mvx_product_vendors($item['product_id']);
                if ($has_vendor) {
                    $variation_id = isset($item['variation_id']) && !empty($item['variation_id']) ? $item['variation_id'] : 0;
                    $item_commission = $MVX->commission->get_item_commission($item['product_id'], $variation_id, $item, $order_id, $item_id);
                    $commission_values = $MVX->commission->get_commission_amount($item['product_id'], $has_vendor->term_id, $variation_id, $item_id, $order);
                    $commission_rate = array('mode' => $MVX->vendor_caps->payment_cap['revenue_sharing_mode'], 'type' => $MVX->vendor_caps->payment_cap['commission_type']);
                    $commission_rate['commission_val'] = isset($commission_values['commission_val']) ? $commission_values['commission_val'] : 0;
                    $commission_rate['commission_fixed'] = isset($commission_values['commission_fixed']) ? $commission_values['commission_fixed'] : 0;
                    $item['commission'] = $item_commission;
                    $item['commission_rate'] = $commission_rate;
                    $vendor_items[$has_vendor->id][$item_id] = $item;
                }
            }

            if (count($vendor_items) != 0){
                // update parent order meta
                $new_order->update_meta_data('has_mvx_sub_order', true);

                $order_manager = new OrderManager;
                $order_manager->create_vendor_orders( $order_id, $new_order );
                $sub_orders = $order_manager->get_suborders($new_order);

                foreach ($sub_orders as $sub_order) {
                    $commission_id = \MVX_Commission::create_commission($sub_order);
                    if ($commission_id) {
                        // Calculate commission
                        \MVX_Commission::calculate_commission($commission_id, $sub_order);
                        // add commission id with associated vendor order
                        $sub_order->update_meta_data('_commission_id', $commission_id );
                        // Mark commissions as processed
                        $sub_order->update_meta_data('_commissions_processed', 'yes' );
                        $sub_order->save();
                    }
                }
            }
        
            $new_order->calculate_totals();
            $new_order->update_status( 'processing', 'Order imported' );
            $new_order->save();
        }
    }

    /**
     * Import dummy reviews
     */
    public function import_reviews( $product_ids ){
        $product_ids = array_reverse( $product_ids );

        // importing reviews
        $xml = simplexml_load_file( MVX_PLUGIN_DUMMY_DATA . '/reviews.xml' );
        if( ! $xml){
            return;
        }

        foreach ( $xml->review as $review ) {
            $product_id = array_pop( $product_ids );

            $comment_data = array(
                'comment_post_ID'      => $product_id,
                'comment_author'       => (string) $review->reviewer,
                'comment_author_email' => (string) $review->email,
                'comment_content'      => (string) $review->comment,
                'comment_type'         => 'review',
                'comment_approved'     => 1,
            );
        
            $comment_id = wp_insert_comment( $comment_data );

            if ( is_wp_error( $comment_id ) ) {
                return;
            }
            update_comment_meta( $comment_id, 'rating', intval( $review->rating ) );

            $average_rating = intval( get_post_meta( $product_id, '_wc_average_rating', true ) );
            $review_count   = intval( get_post_meta( $product_id, '_wc_review_count', true ) );

            if($review_count == 1){
                $average_rating = intval( $review->rating );
            } else{
                $average_rating = ( ( $average_rating * ( $review_count - 1 ) ) + intval( $review->rating ) ) / $review_count;
            }

            update_post_meta( $product_id, '_wc_average_rating', $average_rating );
        }
    }

    public function install_woocommerce( $request ){
        $nonce = $request->get_header('X-WP-Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new \WP_Error('rest_invalid_nonce', __('Invalid nonce', 'multivendorx'), array('status' => 403));
        }

        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
        require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        WP_Filesystem();
        $skin = new \Automatic_Upgrader_Skin;
        $upgrader = new \WP_Upgrader($skin);
        $installed_plugins = array_map([__CLASS__, 'format_plugin_slug'], array_keys(get_plugins()));
        $plugin_slug = 'woocommerce';
        $plugin = $plugin_slug . '/' . $plugin_slug . '.php';
        $installed = false;
        $activate = false;
        // See if the plugin is installed already
        if (in_array($plugin_slug, $installed_plugins)) {
            $installed = true;
            $activate = !is_plugin_active($plugin);
        }
        // Install this thing!
        if (!$installed) {
            // Suppress feedback
            ob_start();

            try {
                $plugin_information = plugins_api('plugin_information', array(
                    'slug' => $plugin_slug,
                    'fields' => array(
                        'short_description' => false,
                        'sections' => false,
                        'requires' => false,
                        'rating' => false,
                        'ratings' => false,
                        'downloaded' => false,
                        'last_updated' => false,
                        'added' => false,
                        'tags' => false,
                        'homepage' => false,
                        'donate_link' => false,
                        'author_profile' => false,
                        'author' => false,
                    ),
                ));

                if (is_wp_error($plugin_information)) {
                    throw new \Exception($plugin_information->get_error_message());
                }

                $package = $plugin_information->download_link;
                $download = $upgrader->download_package($package);

                if (is_wp_error($download)) {
                    throw new \Exception($download->get_error_message());
                }

                $working_dir = $upgrader->unpack_package($download, true);

                if (is_wp_error($working_dir)) {
                    throw new \Exception($working_dir->get_error_message());
                }

                $result = $upgrader->install_package(array(
                    'source' => $working_dir,
                    'destination' => WP_PLUGIN_DIR,
                    'clear_destination' => false,
                    'abort_if_destination_exists' => false,
                    'clear_working' => true,
                    'hook_extra' => array(
                        'type' => 'plugin',
                        'action' => 'install',
                    ),
                ));

                if (is_wp_error($result)) {
                    throw new \Exception($result->get_error_message());
                }

                $activate = true;
            } catch (\Exception $e) {
                printf(
                        __('%1$s could not be installed (%2$s). <a href="%3$s">Please install it manually by clicking here.</a>', 'multivendorx'), 'WooCommerce', $e->getMessage(), esc_url(admin_url('plugin-install.php?tab=search&s=woocommerce'))
                );
                exit();
            }

            // Discard feedback
            ob_end_clean();
        }

        wp_clean_plugins_cache();
        // Activate this thing
        if ($activate) {
            try {
                $result = activate_plugin($plugin);

                if (is_wp_error($result)) {
                    throw new \Exception($result->get_error_message());
                }
            } catch (\Exception $e) {
                printf(
                        __('%1$s was installed but could not be activated. <a href="%2$s">Please activate it manually by clicking here.</a>', 'multivendorx'), 'WooCommerce', admin_url('plugins.php')
                );
                exit();
            }
        }

        return rest_ensure_response(array(
            'success' => true,
            'message' => __('Woocommerce installed successfully.', 'multivendorx'),
            'redirect' => admin_url('index.php?page=mvx-setup'),
        ));
    }
    
    /**
     * Get slug from path
     * @param  string $key
     * @return string
     */
    private static function format_plugin_slug($key) {
        $slug = explode('/', $key);
        $slug = explode('.', end($slug));
        return $slug[0];
    }

    public function get_payment_methods() {
        $methods = array(
            'paypal_masspay' => array(
                'label' => __('Paypal Masspay', 'multivendorx'),
                'description' => __('Pay via paypal masspay', 'multivendorx'),
                'class' => 'featured featured-row-last'
            ),
            'paypal_payout' => array(
                'label' => __('Paypal Payout', 'multivendorx'),
                'description' => __('Pay via paypal payout', 'multivendorx'),
                'class' => 'featured featured-row-first'
            ),
            'direct_bank' => array(
                'label' => __('Direct Bank Transfer', 'multivendorx'),
                'description' => __('', 'multivendorx'),
                'class' => ''
            ),
            'stripe_masspay' => array(
                'label' => __('Stripe Connect', 'multivendorx'),
                'description' => __('', 'multivendorx'),
                //'repo-slug' => 'marketplace-stripe-gateway',
                'class' => ''
            )
        );
        return $methods;
    }

    public function permissions_check() {
        return true;
    }
}