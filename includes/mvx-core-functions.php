<?php

if (!function_exists('get_mvx_vendor_settings')) {

    /**
     * get plugin settings
     * @return array
     */
    function get_mvx_vendor_settings($key = '', $tab = '', $default = false) {
        if (empty($key) && empty($tab)) {
            return $default;
        }
        if (empty($tab)) {
            return get_mvx_global_settings($key, $default);
        }
        if (empty($key)) {
            return get_option("wcmp_{$tab}_tab_settings", $default);
        }
        if (!empty($key) && !empty($tab)) {
            $settings = get_option("mvx_{$tab}_tab_settings", $default);
        }
        if (!isset($settings[$key]) || empty($settings[$key])) {
            return $default;
        }
        return $settings[$key];
    }

}

if (!function_exists('get_mvx_global_settings')) {

    function get_mvx_global_settings($key = '', $default = false) {
        $options = array();
        $all_options = apply_filters('mvx_all_admin_options', array(
            'mvx_settings_general_tab_settings',
            'mvx_social_tab_settings',
            'mvx_vendor_registration_form_data',
            'mvx_seller_dashbaord_tab_settings',
            'mvx_store_tab_settings',
            'mvx_products_tab_settings',
            'mvx_products_capability_tab_settings',
            'mvx_spmv_pages_tab_settings',
            'mvx_commissions_tab_settings',
            'mvx_disbursement_tab_settings',
            'mvx_policy_tab_settings',
            'mvx_refund_management_tab_settings',
            'mvx_review_management_tab_settings',
            'mvx_payment_masspay_tab_settings',
            'mvx_payment_payout_tab_settings',
            'mvx_payment_stripe_connect_tab_settings',
                )
        );
        foreach ($all_options as $option_name) {
            $options = array_merge($options, get_option($option_name, array()));
        }
        if (empty($key)) {
            return $options;
        }
        if (!isset($options[$key]) || empty($options[$key])) {
            return $default;
        }
        return $options[$key];
    }

}

if (!function_exists('update_mvx_vendor_settings')) {

    function update_mvx_vendor_settings($name = '', $value = '', $tab = '', $subtab = '') {
        if (empty($name) || empty($value)) {
            return;
        }
        if (!empty($subtab)) {
            $option_name = "mvx_{$tab}_{$subtab}_settings_name";
            $settings = get_option("mvx_{$tab}_{$subtab}_settings_name");
        } else {
            $option_name = "mvx_{$tab}_settings_name";
            $settings = get_option("mvx_{$tab}_settings_name");
        }
        $settings[$name] = $value;
        update_option($option_name, $settings);
    }

}

if (!function_exists('delete_mvx_vendor_settings')) {

    function delete_mvx_vendor_settings($name = '', $tab = '', $subtab = '') {
        if (empty($name)) {
            return;
        }
        if (!empty($subtab)) {
            $option_name = "mvx_{$tab}_{$subtab}_settings_name";
            $settings = get_option("mvx_{$tab}_{$subtab}_settings_name");
        } else {
            $option_name = "mvx_{$tab}_settings_name";
            $settings = get_option("mvx_{$tab}_settings_name");
        }
        unset($settings[$name]);
        update_option($option_name, $settings);
    }

}

if (!function_exists('is_user_mvx_pending_vendor')) {

    /**
     * Check if user is pending vendor
     * @param userid or WP_User object
     * @return boolean
     */
    function is_user_mvx_pending_vendor($user) {
        if ($user && !empty($user)) {
            if (!is_object($user)) {
                $user = new WP_User(absint($user));
            }
            return ( is_array($user->roles) && in_array('dc_pending_vendor', $user->roles) );
        } else {
            return false;
        }
    }

}

if (!function_exists('is_user_mvx_rejected_vendor')) {

    /**
     * Check if user is vendor
     * @param userid or WP_User object
     * @return boolean
     */
    function is_user_mvx_rejected_vendor($user) {
        if ($user && !empty($user)) {
            if (!is_object($user)) {
                $user = new WP_User(absint($user));
            }
            return ( is_array($user->roles) && in_array('dc_rejected_vendor', $user->roles) );
        } else {
            return false;
        }
    }

}

if (!function_exists('is_user_mvx_vendor')) {

    /**
     * Check if user is vendor
     * @param userid or WP_User object
     * @return boolean
     */
    function is_user_mvx_vendor($user) {
        if ($user && !empty($user)) {
            if (!is_object($user)) {
                $user = new WP_User(absint($user));
            }
            return apply_filters('is_user_mvx_vendor', ( is_array($user->roles) && in_array('dc_vendor', $user->roles)), $user);
        } else {
            return false;
        }
    }

}

if (!function_exists('get_mvx_vendors')) {

    /**
     * Get all vendors
     * @param args Array of args
     * @param return type `object`/`id'
     * @return arr Array of ids/vendors
     */
    function get_mvx_vendors($args = array(), $return = 'object') {
        $vendors_array = array();
        $args = wp_parse_args($args, array('role' => 'dc_vendor', 'fields' => 'ids', 'orderby' => 'registered', 'order' => 'ASC'));
        $user_query = new WP_User_Query($args);
        if( $return === 'object' ){
            if (!empty($user_query->results)) {
                foreach ($user_query->results as $vendor_id) {
                    $vendors_array[] = get_mvx_vendor($vendor_id);
                }
            }
        }else{
            $vendors_array = $user_query->results;
        }
        
        return apply_filters('get_mvx_vendors', $vendors_array, $return);
    }

}

if (!function_exists('get_mvx_vendor')) {

    /**
     * Get individual vendor info by ID
     * @param  int $vendor_id ID of vendor
     * @return obj            Vendor object
     */
    function get_mvx_vendor($vendor_id = 0) {
        $vendor = false;
        $vendor_id = $vendor_id ? $vendor_id : get_current_vendor_id();
        if (is_user_mvx_vendor($vendor_id)) {
            if( !class_exists( 'MVX_Vendor' ) ) {
                global $MVX;
                include_once ( $MVX->plugin_path . "/classes/class-mvx-vendor-details.php" );
            }
            $vendor = new MVX_Vendor(absint($vendor_id));
        }
        return $vendor;
    }

}

if (!function_exists('get_mvx_vendor_by_term')) {

    /**
     * Get individual vendor info by term id
     * @param $term_id ID of term
     */
    function get_mvx_vendor_by_term($term_id) {
        $vendor = false;
        if (!empty($term_id)) {
            $user_id = get_term_meta($term_id, '_vendor_user_id', true);
            if (is_user_mvx_vendor($user_id)) {
                $vendor = get_mvx_vendor($user_id);
            }
        }
        return $vendor;
    }

}
if (!function_exists('get_mvx_vendor_by_store_url')) {

    function get_mvx_vendor_by_store_url($store_url) {
        global $MVX;
        $vendor = false;
        $termslug = basename($store_url);
        $term = get_term_by('slug', $termslug, $MVX->taxonomy->taxonomy_name);
        if ($term) {
            $vendor = get_mvx_vendor_by_term($term->term_id);
        }
        return $vendor;
    }

}

if (!function_exists('get_mvx_product_vendors')) {

    /**
     * Get vendors for product
     * @param  int $product_id Product ID
     * @return arr             Array of product vendors
     */
    function get_mvx_product_vendors($product_id = 0) {
        global $MVX;
        $vendor_data = false;
        if ($product_id > 0) {
            $vendors_data = wp_get_post_terms($product_id, $MVX->taxonomy->taxonomy_name);
            foreach ($vendors_data as $vendor) {
                $vendor_obj = get_mvx_vendor_by_term($vendor->term_id);
                if ($vendor_obj) {
                    $vendor_data = $vendor_obj;
                }
            }
            if (!$vendor_data) {
                $product_obj = get_post($product_id);
                if (is_object($product_obj)) {
                    $author_id = $product_obj->post_author;
                    if ($author_id) {
                        $vendor_data = get_mvx_vendor($author_id);
                    }
                }
            }
        }
        return $vendor_data;
    }

}

if (!function_exists('doProductVendorLOG')) {

    /**
     * Write to log file
     */
    function doProductVendorLOG($str) {
        global $MVX;
        $file = $MVX->plugin_path . 'log/product_vendor.log';
        if (file_exists($file)) {
//            $temphandle = @fopen($file, 'w+'); // @codingStandardsIgnoreLine.
//            @fclose($temphandle); // @codingStandardsIgnoreLine.
//            if (defined('FS_CHMOD_FILE')) {
//                @chmod($file, FS_CHMOD_FILE); // @codingStandardsIgnoreLine.
//            }
            // Open the file to get existing content
            $current = file_get_contents($file);
            if ($current) {
                // Append a new content to the file
                $current .= "$str" . "\r\n";
                $current .= "-------------------------------------\r\n";
            } else {
                $current = "$str" . "\r\n";
                $current .= "-------------------------------------\r\n";
            }
            // Write the contents back to the file
            file_put_contents($file, $current);
        }
    }

}

if (!function_exists('is_vendor_dashboard')) {

    /**
     * check if vendor dashboard page
     * @return boolean
     */
    function is_vendor_dashboard() {
        $is_vendor_dashboard = false;
        if ( function_exists('icl_object_id') ) {
            return is_page( icl_object_id( mvx_vendor_dashboard_page_id(), 'page', true ) );
        } else {
            return is_page(mvx_vendor_dashboard_page_id());
        }
        return apply_filters('is_mvx_vendor_dashboard', $is_vendor_dashboard);
    }

}

if (!function_exists('mvx_vendor_dashboard_page_id')) {

    /**
     * Get vendor dashboard page id
     * @return int
     */
    function mvx_vendor_dashboard_page_id($language_code = '', $url = false) {
        if (get_mvx_vendor_settings('mvx_vendor', 'vendor', 'general')) {
            if ( defined( 'ICL_SITEPRESS_VERSION' ) && ! ICL_PLUGIN_INACTIVE && class_exists( 'SitePress' ) ) {
                if( !$language_code ) {
                    global $sitepress;
                    $language_code = $sitepress->get_current_language();
                }
                if( $language_code ) {
                    if( defined('DOING_AJAX') ) {
                        do_action( 'wpml_switch_language', $language_code );
                    }
                    if ($url) {
                        $mvx_page =  get_permalink(icl_object_id( get_mvx_vendor_settings('mvx_vendor', 'vendor', 'general'), 'page', true, $language_code ));
                        $mvx_page = apply_filters( 'wpml_permalink', $mvx_page, $language_code );
                    } else {
                        $mvx_page =  icl_object_id( get_mvx_vendor_settings('mvx_vendor', 'vendor', 'general'), 'page', true, $language_code );
                        $mvx_page = apply_filters( 'wpml_permalink', $mvx_page, $language_code );
                    }
                    return $mvx_page;
                } else {
                    if ($url) {
                        return  get_permalink(icl_object_id( get_mvx_vendor_settings('mvx_vendor', 'vendor', 'general'), 'page', true ));
                    } else {
                        return  icl_object_id( get_mvx_vendor_settings('mvx_vendor', 'vendor', 'general'), 'page', true );
                    }
                }
            } else {
                if ($url) {
                    return get_permalink( (int) get_mvx_vendor_settings('mvx_vendor', 'vendor', 'general') );
                } else {
                    return (int) get_mvx_vendor_settings('mvx_vendor', 'vendor', 'general');
                }
            }
        }
        return false;
    }
}

if (!function_exists('is_page_vendor_registration')) {

    /**
     * check if vendor registration page
     * @return boolean
     */
    function is_page_vendor_registration() {
        $is_vendor_registration = false;
        if (mvx_vendor_registration_page_id()) {
            $is_vendor_registration = is_page(mvx_vendor_registration_page_id()) ? true : false;
        }
        return apply_filters('is_mvx_vendor_registration', $is_vendor_registration);
    }

}

if (!function_exists('mvx_vendor_registration_page_id')) {

    /**
     * Get vendor Registration page id
     * @return type
     */
    function mvx_vendor_registration_page_id() {
        if (get_mvx_vendor_settings('vendor_registration', 'vendor', 'general')) {
            if (function_exists('icl_object_id')) {
                return icl_object_id((int) get_mvx_vendor_settings('vendor_registration', 'vendor', 'general'), 'page', false, ICL_LANGUAGE_CODE);
            }
            return (int) get_mvx_vendor_settings('vendor_registration', 'vendor', 'general');
        }
        return false;
    }

}

if (!function_exists('get_vendor_from_an_order')) {

    /**
     * Get vendor from a order
     * @param WC_Order $order or order id
     * @return type
     */
    function get_vendor_from_an_order($order) {
        $vendors = array();
        if (!is_object($order)) {
            $order = new WC_Order($order);
        }
        $items = $order->get_items('line_item');
        foreach ($items as $item_id => $item) {
            $vendor_id = wc_get_order_item_meta($item_id, '_vendor_id', true);
            if ($vendor_id) {
                $term_id = get_user_meta($vendor_id, '_vendor_term_id', true);
                if (!in_array($term_id, $vendors)) {
                    $vendors[] = $term_id;
                }
            } else {
                $product_id = wc_get_order_item_meta($item_id, '_product_id', true);
                if ($product_id) {
                    $product_vendors = get_mvx_product_vendors($product_id);
                    if ($product_vendors && !in_array($product_vendors->term_id, $vendors)) {
                        $vendors[] = $product_vendors->term_id;
                    }
                }
            }
        }
        return $vendors;
    }

}

if (!function_exists('is_vendor_page')) {

    /**
     * check if vendor pages
     * @return boolean
     */
    function is_vendor_page() {
        _deprecated_function('is_vendor_page', '2.7.7', 'is_vendor_dashboard or is_page_vendor_registration');
        return apply_filters('is_mvx_vendor_page', (is_vendor_dashboard() || is_page_vendor_registration()));
    }

}

if (!function_exists('is_vendor_order_by_product_page')) {

    /**
     * Check if vendor order page
     * @return boolean
     */
    function is_vendor_order_by_product_page() {
        return is_mvx_endpoint_url(get_mvx_vendor_settings('mvx_vendor_orders_endpoint', 'vendor', 'general', 'vendor-orders'));
    }

}

if (!function_exists('MVX_action_links')) {

    /**
     * Product Vendor Action Links Function
     * @param plugin links
     * @return plugin links
     */
    function MVX_action_links($links) {
        $plugin_links = array(
            '<a href="' . admin_url('admin.php?page=mvx#&submenu=dashboard&name=help') . '">' . __('Settings', 'dc-woocommerce-multi-vendor') . '</a>');
        return array_merge($plugin_links, $links);
    }

}


if (!function_exists('mvx_get_all_blocked_vendors')) {

    /**
     * mvx_get_all_blocked_vendors Function
     *
     * @access public
     * @return plugin array
     */
    function mvx_get_all_blocked_vendors() {
        $vendors = get_mvx_vendors();
        $blocked_vendor = array();
        if (!empty($vendors) && is_array($vendors)) {
            foreach ($vendors as $vendor_key => $vendor) {
                if (is_a($vendor, 'MVX_Vendor')) {
                    $is_block = get_user_meta($vendor->id, '_vendor_turn_off', true);
                    if ($is_block) {
                        $blocked_vendor[] = $vendor;
                    }
                }
            }
        }
        return $blocked_vendor;
    }

}

if (!function_exists('mvx_get_vendors_due_from_order')) {

    /**
     * Get vendor due from an order.
     * @param WC_Order $order or order id
     * @return array
     */
    function mvx_get_vendors_due_from_order($order) {
        if (!is_object($order)) {
            $order = new WC_Order($order);
        }
        $items = $order->get_items('line_item');
        $vendors_array = array();
        if ($items) {
            foreach ($items as $item_id => $item) {
                $product_id = wc_get_order_item_meta($item_id, '_product_id', true);
                if ($product_id) {
                    $vendor = get_mvx_product_vendors($product_id);
                    if (!empty($vendor) && isset($vendor->term_id)) {
                        $vendors_array[$vendor->term_id] = $vendor->mvx_get_vendor_part_from_order($order, $vendor->term_id);
                    }
                }
            }
        }
        return $vendors_array;
    }

}

if (!function_exists('get_mvx_vendor_orders')) {

    function get_mvx_vendor_orders($args = array()) {
        global $wpdb;
        $query = '';
        if (isset($args['order_id'])) {
            if (is_object($args['order_id'])) {
                $args['order_id'] = $args['order_id']->get_id();
            }
        }
        if (isset($args['product_id'])) {
            if (is_object($args['product_id'])) {
                $args['product_id'] = $args['product_id']->get_id();
            }
        }
        if (isset($args['commission_id'])) {
            if (is_object($args['commission_id'])) {
                $args['commission_id'] = $args['commission_id']->ID;
            }
        }
        if (isset($args['vendor_id'])) {
            if (is_object($args['vendor_id'])) {
                $args['vendor_id'] = $args['vendor_id']->id;
            }
        }
        if (!empty($args)) {
            foreach ($args as $key => $arg) {
                if (!$wpdb->get_var( $wpdb->prepare( "SHOW COLUMNS FROM `{$wpdb->prefix}mvx_vendor_orders` LIKE %s", $key  ))) {
                    unset($args[$key]);
                }
            }
            $query .= ' WHERE ';
            $query .= implode(' AND ', array_map(
                            function ($v, $k) {
                        return sprintf("%s = '%s'", $k, $v);
                    }, $args, array_keys($args)
            ));
            $query = apply_filters('get_mvx_vendor_orders_query_where', $query);
        }
        return $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}mvx_vendor_orders %s", $query));
    }

}

if (!function_exists('get_mvx_vendor_order_amount')) {

    /**
     * @since 2.6.6
     * @global object $MVX
     * @param int $vendor_id
     * @param array $args
     * @param bool $check_caps
     * @return array
     */
    function get_mvx_vendor_order_amount($args = array(), $vendor_id = false, $check_caps = true) {
        global $MVX;
        if ($vendor_id) {
            $args['vendor_id'] = $vendor_id;
        }
        if (isset($args['vendor_id'])) {
            $vendor_id = $args['vendor_id'];
        }
        if (!isset($args['is_trashed'])) {
            $args['is_trashed'] = '';
        }
        $vendor_orders_in_order = get_mvx_vendor_orders($args);

        if (!empty($vendor_orders_in_order)) {
            $shipping_amount = array_sum(wp_list_pluck($vendor_orders_in_order, 'shipping'));
            $tax_amount = array_sum(wp_list_pluck($vendor_orders_in_order, 'tax'));
            $shipping_tax_amount = array_sum(wp_list_pluck($vendor_orders_in_order, 'shipping_tax_amount'));
            $commission_amount = array_sum(wp_list_pluck($vendor_orders_in_order, 'commission_amount'));
            $total = $commission_amount + $shipping_amount + $tax_amount + $shipping_tax_amount;
        } else {
            $shipping_amount = 0;
            $tax_amount = 0;
            $shipping_tax_amount = 0;
            $commission_amount = 0;
            $total = 0;
        }
        if ($check_caps && $MVX && $vendor_id) {
            $amount = array(
                'commission_amount' => $commission_amount,
            );
            if ($MVX->vendor_caps->vendor_payment_settings('give_shipping') && !get_user_meta($vendor_id, '_vendor_give_shipping', true)) {
                $amount['shipping_amount'] = $shipping_amount;
            } else {
                $amount['shipping_amount'] = 0;
            }
            if ($MVX->vendor_caps->vendor_payment_settings('give_tax') && $MVX->vendor_caps->vendor_payment_settings('give_shipping') && !get_user_meta($vendor_id, '_vendor_give_shipping', true) && !get_user_meta($vendor_id, '_vendor_give_tax', true)) {
                $amount['tax_amount'] = $tax_amount;
                $amount['shipping_tax_amount'] = $shipping_tax_amount;
            } else if ($MVX->vendor_caps->vendor_payment_settings('give_tax') && !get_user_meta($vendor_id, '_vendor_give_tax', true)) {
                $amount['tax_amount'] = $tax_amount;
                $amount['shipping_tax_amount'] = 0;
            } else {
                $amount['tax_amount'] = 0;
                $amount['shipping_tax_amount'] = 0;
            }
            $amount['total'] = $amount['commission_amount'] + $amount['shipping_amount'] + $amount['tax_amount'] + $amount['shipping_tax_amount'];
            return $amount;
        } else {
            return array(
                'commission_amount' => $commission_amount,
                'shipping_amount' => $shipping_amount,
                'tax_amount' => $tax_amount,
                'shipping_tax_amount' => $shipping_tax_amount,
                'total' => $total
            );
        }
    }

}

if (!function_exists('mvx_get_vendors_form_order')) {

    function mvx_get_vendors_form_order($order) {
        if (!is_object($order)) {
            $order = new WC_Order($order);
        }
        $items = $order->get_items('line_item');
        $vendors_array = array();
        if ($items) {
            foreach ($items as $item_id => $item) {
                $product_id = wc_get_order_item_meta($item_id, '_product_id', true);
                if ($product_id) {
                    $vendor = get_mvx_product_vendors($product_id);
                    if (!empty($vendor) && isset($vendor->term_id)) {
                        $vendors_array[$vendor->term_id] = $vendor;
                    }
                }
            }
        }
        return $vendors_array;
    }

}

if (!function_exists('activate_mvx_plugin')) {

    /**
     * On activation, include the installer and run it.
     *
     * @access public
     * @return void
     */
    function activate_mvx_plugin() {
        //if (!get_option('dc_product_vendor_plugin_installed')) {
        require_once( 'class-mvx-install.php' );
        new MVX_Install();
        update_option('dc_product_vendor_plugin_installed', 1);
        //}
    }

}

if (!function_exists('deactivate_mvx_plugin')) {

    /**
     * On deactivation delete page install option
     */
    function deactivate_mvx_plugin() {
        delete_option('dc_product_vendor_plugin_page_install');
        delete_option('mvx_flushed_rewrite_rules');
    }

}

if (!function_exists('mvx_check_if_another_vendor_plugin_exits')) {

    /**
     * On activation, check if another vendor plugin installed.
     *
     * @access public
     * @return void
     */
    function mvx_check_if_another_vendor_plugin_exits() {
        require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
        // deactivate marketplace stripe gateway
        if (version_compare(get_option('dc_product_vendor_plugin_db_version'), '3.1.0', '<')) {
            
        } else {
            if (is_plugin_active('marketplace-stripe-gateway/marketplace-stripe-gateway.php')) {
                deactivate_plugins('marketplace-stripe-gateway/marketplace-stripe-gateway.php');
            }
        }
        $vendor_arr = array();
        $vendor_arr[] = 'dokan-lite/dokan.php';
        $vendor_arr[] = 'wc-vendors/class-wc-vendors.php';
        $vendor_arr[] = 'yith-woocommerce-product-vendors/init.php';
        foreach ($vendor_arr as $plugin) {
            if (is_plugin_active($plugin)) {
                deactivate_plugins('dc-woocommerce-multi-vendor/dc_product_vendor.php');
                exit(__('Another Multivendor Plugin is allready Activated Please deactivate first to install this plugin', 'dc-woocommerce-multi-vendor'));
            }
        }
    }

}

if (!function_exists('mvxArrayToObject')) {

    /**
     * Convert php array to object
     * @param array $d
     * @return object
     */
    function mvxArrayToObject($d) {
        if (is_array($d)) {
            /*
             * Return array converted to object
             * Using __FUNCTION__ (Magic constant)
             * for recursive call
             */
            return (object) array_map(__FUNCTION__, $d);
        } else {
            // Return object
            return $d;
        }
    }

}

if (!function_exists('mvx_paid_commission_status')) {

    function mvx_paid_commission_status($commission_id) {
        global $wpdb;
        update_post_meta($commission_id, '_paid_status', 'paid', 'unpaid');
        update_post_meta($commission_id, '_paid_date', time());
        $wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}mvx_vendor_orders` SET commission_status = 'paid', commission_paid_date = now() WHERE commission_id = %d", $commission_id ) );
    }

}

if (!function_exists('mvx_rangeWeek')) {

    /**
     * Calculate start date and end date of a week
     * @param date $datestr
     * @return array
     */
    function mvx_rangeWeek($datestr) {
        date_default_timezone_set(date_default_timezone_get());
        $dt = strtotime($datestr);
        $res['start'] = date('N', $dt) == 1 ? date('Y-m-d', $dt) : date('Y-m-d', strtotime('last monday', $dt));
        $res['end'] = date('N', $dt) == 7 ? date('Y-m-d', $dt) : date('Y-m-d', strtotime('next sunday', $dt));
        return $res;
    }

}

if (!function_exists('mvx_role_exists')) {

    /**
     * Check if role exist or not
     * @param string $role
     * @return boolean
     */
    function mvx_role_exists($role) {
        if (!empty($role)) {
            return $GLOBALS['wp_roles']->is_role($role);
        }
        return false;
    }

}

if (!function_exists('mvx_seller_review_enable')) {

    /**
     * Check if vendor review enable or not
     * @param type $vendor_term_id
     * @return type
     */
    function mvx_seller_review_enable($vendor_term_id) {
        $is_enable = false;
        $current_user = wp_get_current_user();
        if ($current_user->ID > 0) {
            if (mvx_is_module_active('vendor-review')) {
                if (get_mvx_vendor_settings('is_sellerreview_varified', 'general') == 'Enable') {
                    $is_enable = mvx_find_user_purchased_with_vendor($current_user->ID, $vendor_term_id);
                } else {
                    $is_enable = true;
                }
            }
        }
        return apply_filters('mvx_seller_review_enable', $is_enable);
    }

}

if (!function_exists('mvx_find_user_purchased_with_vendor')) {

    /**
     * Check if a user purchase product from given vendor or not
     * @param type $user_id
     * @param type $vendor_term_id
     * @return boolean
     */
    function mvx_find_user_purchased_with_vendor($user_id, $vendor_term_id) {
        $is_purchased_with_vendor = false;
        $order_lits = mvx_get_all_order_of_user($user_id);
        foreach ($order_lits as $order) {
            $vendors = get_vendor_from_an_order($order->ID);
            if (!empty($vendors) && is_array($vendors)) {
                if (in_array($vendor_term_id, $vendors)) {
                    $is_purchased_with_vendor = true;
                    break;
                }
            }
        }
        return $is_purchased_with_vendor;
    }

}

if (!function_exists('mvx_get_vendor_dashboard_nav_item_css_class')) {

    function mvx_get_vendor_dashboard_nav_item_css_class($endpoint, $force_active = false) {
        global $wp;
        $cssClass = array(
            'nav-link',
            'mvx-venrod-dashboard-nav-link',
            'mvx-venrod-dashboard-nav-link--' . $endpoint
        );
        $current = isset($wp->query_vars[$endpoint]);
        if ('dashboard' === $endpoint && ( isset($wp->query_vars['page']) || empty($wp->query_vars) )) {
            $current = true; // Dashboard is not an endpoint, so needs a custom check.
        }
        if ($current || $force_active) {
            $cssClass[] = 'active';
        }
        $cssClass = apply_filters('mvx_vendor_dashboard_nav_item_css_class', $cssClass, $endpoint);
        return $cssClass;
    }

}

if (!function_exists('mvx_get_vendor_dashboard_endpoint_url')) {
    function mvx_get_vendor_dashboard_endpoint_url($endpoint, $value = '', $withvalue = false, $lang_code = '') {
        global $wp;
        $permalink =  mvx_vendor_dashboard_page_id($lang_code, true);
        if (empty($value)) {
            $value = isset($wp->query_vars[$endpoint]) && !empty($wp->query_vars[$endpoint]) && $withvalue ? $wp->query_vars[$endpoint] : '';
        }
        if (get_option('permalink_structure')) {
            if (strstr($permalink, '?')) {
                $query_string = '?' . parse_url($permalink, PHP_URL_QUERY);
                $permalink = current(explode('?', $permalink));
            } else {
                $query_string = '';
            }
            if ($endpoint == 'dashboard') {
                $url = trailingslashit($permalink) . $query_string;
            } else {
                $endpoint = defined( 'ICL_SITEPRESS_VERSION' ) && ! ICL_PLUGIN_INACTIVE && class_exists( 'SitePress' ) ? apply_filters( 'wpml_translate_single_string', $endpoint, 'MVX', $endpoint, $lang_code ) : $endpoint;
                $url = trailingslashit($permalink) . $endpoint . '/' . $value . $query_string;
            }
        } else {
            if ($endpoint == 'dashboard') {
                $url = $permalink;
            } else {
                $endpoint = defined( 'ICL_SITEPRESS_VERSION' ) && ! ICL_PLUGIN_INACTIVE && class_exists( 'SitePress' ) ? apply_filters( 'wpml_translate_single_string', $endpoint, 'MVX', $endpoint, $lang_code ) : $endpoint;
                $url = add_query_arg($endpoint, $value, $permalink);
            }
        }

        return apply_filters('mvx_get_vendor_dashboard_endpoint_url', $url, $endpoint, $value, $permalink);
    }
}

if (!function_exists('is_mvx_endpoint_url')) {

    /**
     * is_wc_endpoint_url - Check if an endpoint is showing.
     * @param  string $endpoint
     * @return bool
     */
    function is_mvx_endpoint_url($endpoint = false) {
        global $wp, $MVX;
        $mvx_endpoints = $MVX->endpoints->mvx_query_vars;

        if ($endpoint !== false) {
            if (!isset($mvx_endpoints[$endpoint])) {
                return false;
            } else {
                $endpoint_var = $mvx_endpoints[$endpoint];
            }

            return isset($wp->query_vars[$endpoint_var['endpoint']]);
        } else {
            foreach ($mvx_endpoints as $key => $value) {
                if (isset($wp->query_vars[$key])) {
                    return true;
                }
            }

            return false;
        }
    }

}

if (!function_exists('mvx_get_all_order_of_user')) {

    /**
     * Get all order of a customer
     * @param int $user_id
     * @return array
     */
    function mvx_get_all_order_of_user($user_id) {
        $order_lits = array();
        $customer_orders = get_posts(array(
            'numberposts' => -1,
            'meta_key' => '_customer_user',
            'meta_value' => $user_id,
            'post_type' => wc_get_order_types(),
            'post_status' => array_keys(wc_get_order_statuses()),
        ));
        if (is_array($customer_orders) && count($customer_orders) > 0) {
            $order_lits = $customer_orders;
        }
        return $order_lits;
    }

}

if (!function_exists('mvx_review_is_from_verified_owner')) {

    /**
     * Check if given comment from verified customer or not
     * @param object $comment
     * @param int $vendor_term_id
     * @return boolean
     */
    function mvx_review_is_from_verified_owner($comment, $vendor_term_id) {
        $user_id = $comment->user_id;
        return mvx_find_user_purchased_with_vendor($user_id, $vendor_term_id);
    }

}

if (!function_exists('mvx_get_vendor_review_info')) {

    /**
     * Get vendor review information
     * @global type $wpdb
     * @param type $vendor_term_id
     * @param type $type values vendor-rating/product-rating
     * @return type
     */
    function mvx_get_vendor_review_info($vendor_term_id, $type = 'vendor-rating' ) {
        global $wpdb;
        $default_rating = apply_filters( 'mvx_vendor_review_rating_info_default_type', $type, $vendor_term_id );
        $rating_result_array = array(
            'total_rating' => 0,
            'avg_rating' => 0,
            'rating_type' => $default_rating,
        );

        if ( $default_rating === 'product-rating' ) {
            $vendor = get_mvx_vendor_by_term( $vendor_term_id );
            $vendor_products = wp_list_pluck( $vendor->get_products_ids(), 'ID' );
            $rating = $rating_pro_count = 0;
            if( $vendor_products ) {
                foreach( $vendor_products as $product_id ) {
                    if( get_post_meta( $product_id, '_wc_average_rating', true ) ) {
                        $rating += get_post_meta( $product_id, '_wc_average_rating', true );
                        $rating_pro_count++;
                    };
                }
            }
            if( $rating_pro_count ) {
                $rating_result_array['total_rating'] = $rating_pro_count;
                $rating_result_array['avg_rating'] = $rating / $rating_pro_count;
            }
        } else {
            $args_default = array(
                'status' => 'approve',
                'type' => 'mvx_vendor_rating',
                'meta_key' => 'vendor_rating_id',
                'meta_value' => get_mvx_vendor_by_term($vendor_term_id)->id,
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => 'vendor_rating_id',
                        'value' => get_mvx_vendor_by_term($vendor_term_id)->id
                    ),
                    array(
                        'key' => 'vendor_rating',
                        'value' => '',
                        'compare' => '!='
                    )
                )
            );
            $args = apply_filters('mvx_vendor_review_rating_args_to_fetch', $args_default);
            $rating = $product_rating = 0;
            $comments = get_comments($args);
            // If product review sync enabled
            if (get_mvx_vendor_settings('product_review_sync', 'general') && get_mvx_vendor_settings('product_review_sync', 'general') == 'Enable') {
                $vendor = get_mvx_vendor_by_term( $vendor_term_id );
                $args_default_for_product = apply_filters('mvx_vendors_product_review_info_args', array(
                    'status' => 'approve',
                    'type' => 'review',
                    'post__in' => wp_list_pluck($vendor->get_products_ids(), 'ID' ),
                    'author__not_in' => array($vendor->id)
                ) );
		$product_review_count = !empty($vendor->get_products_ids()) ? get_comments($args_default_for_product) : array();
                if (!empty($product_review_count)) {
                    $comments = array_merge(get_comments($args), $product_review_count);
                }
            }
            if ($comments && count($comments) > 0) {
                foreach ($comments as $comment) {
                    $rating += floatval(get_comment_meta($comment->comment_ID, 'vendor_rating', true));
		    if (get_mvx_vendor_settings('product_review_sync', 'general') && get_mvx_vendor_settings('product_review_sync', 'general') == 'Enable') {
                        $product_rating += floatval(get_comment_meta($comment->comment_ID, 'rating', true));
                    }
                }
		$rating = $rating + $product_rating;
                $rating_result_array['total_rating'] = count($comments);
                $rating_result_array['avg_rating'] = $rating / count($comments);
            }
        }

        return $rating_result_array;
    }

}

if (!function_exists('mvx_sort_by_rating_multiple_product')) {

    /**
     * Sort product by products ratings
     * @param type $more_product_array
     * @return type
     */
    function mvx_sort_by_rating_multiple_product($more_product_array) {
        $more_product_array2 = array();
        $j = 0;
        foreach ($more_product_array as $more_product) {

            if ($j == 0) {
                $more_product_array2[] = $more_product;
            } elseif ($more_product['is_vendor'] == 0) {
                $more_product_array2[] = $more_product;
            } elseif ($more_product['rating_data']['avg_rating'] == 0) {
                $more_product_array2[] = $more_product;
            } elseif ($more_product['rating_data']['avg_rating'] > 0) {
                if (isset($more_product_array2[0]['rating_data']['avg_rating'])) {
                    $i = 0;
                    while ($more_product_array2[$i]['rating_data']['avg_rating'] >= $more_product['rating_data']['avg_rating']) {
                        $i++;
                    }
                    if ($i == 0) {
                        array_unshift($more_product_array2, $more_product);
                    } elseif ($i == (count($more_product_array2) - 1)) {
                        if (isset($more_product_array2[$i]['rating_data']['avg_rating']) && $more_product_array2[$i]['rating_data']['avg_rating'] <= $more_product['rating_data']['avg_rating']) {
                            $temp = $more_product_array2[$i];
                            $more_product_array2[$i] = $more_product;
                            array_push($more_product_array2, $temp);
                        } else {
                            array_push($more_product_array2, $more_product);
                        }
                    } else {
                        $array_1 = array_slice($more_product_array2, 0, $i);
                        $array_2 = array_slice($more_product_array2, $i);
                        array_push($array_1, $more_product);
                        $more_product_array2 = array_merge($array_1, $array_2);
                    }
                } else {
                    array_unshift($more_product_array2, $more_product);
                }
            }
            $j++;
        }
        return $more_product_array2;
    }

}

if (!function_exists('mvx_remove_comments_section_from_vendor_dashboard')) {

    /**
     * Remove comments from vendor dashbord
     */
    function mvx_remove_comments_section_from_vendor_dashboard() {

        if (is_vendor_dashboard()) {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    $('div#comments,section#comments').remove();
                });
            </script>
            <?php

        }
    }

}

if (!function_exists('do_mvx_data_migrate')) {

    /**
     * Migrate Old MVX data
     * @param string $previous_plugin_version
     */
    function do_mvx_data_migrate($previous_plugin_version = '', $new_plugin_version = '') {
        global $MVX, $wpdb, $wp_roles;
        if ($previous_plugin_version) {
            if ($previous_plugin_version <= '2.6.0' && !get_option('mvx_database_upgrade')) {
                $old_pages = get_option('mvx_pages_settings_name');
                if (isset($old_pages['vendor_dashboard'])) {
                    wp_update_post(array('ID' => $old_pages['vendor_dashboard'], 'post_content' => '[mvx_vendor]'));
                    update_option('mvx_product_vendor_vendor_page_id', get_option('mvx_product_vendor_vendor_dashboard_page_id'));
                    $mvx_product_vendor_vendor_page_id = get_option('mvx_product_vendor_vendor_page_id');
                    update_mvx_vendor_settings('mvx_vendor', $mvx_product_vendor_vendor_page_id, 'vendor', 'general');
                }
                /* remove unwanted vendor caps */
                $args = array('role' => 'dc_vendor', 'fields' => 'ids', 'orderby' => 'registered', 'order' => 'ASC');
                $user_query = new WP_User_Query($args);
                if (!empty($user_query->results)) {
                    foreach ($user_query->results as $vendor_id) {
                        $user = new WP_User($vendor_id);
                        if ($user) {
                            if ($user->has_cap('edit_others_products')) {
                                $user->remove_cap('edit_others_products');
                            }
                            if ($user->has_cap('delete_others_products')) {
                                $user->remove_cap('delete_others_products');
                            }
                            if ($user->has_cap('edit_others_shop_coupons')) {
                                $user->remove_cap('edit_others_shop_coupons');
                            }
                            if ($user->has_cap('delete_others_shop_coupons')) {
                                $user->remove_cap('delete_others_shop_coupons');
                            }
                        }
                    }
                }
                #region settings tab general data migrate
                if (get_mvx_vendor_settings('is_singleproductmultiseller', 'general', 'singleproductmultiseller') && get_mvx_vendor_settings('is_singleproductmultiseller', 'general', 'singleproductmultiseller') == 'Enable') {
                    update_mvx_vendor_settings('is_singleproductmultiseller', 'Enable', 'general');
                }
                delete_mvx_vendor_settings('is_singleproductmultiseller', 'general', 'singleproductmultiseller');
                if (mvx_is_module_active('vendor-review')) {
                    update_mvx_vendor_settings('is_sellerreview', 'Enable', 'general');
                }
                delete_mvx_vendor_settings('is_sellerreview', 'general', 'sellerreview');
                if (get_mvx_vendor_settings('is_sellerreview_varified', 'general', 'sellerreview') == 'Enable' && get_mvx_vendor_settings('is_sellerreview_varified', 'general', 'sellerreview')) {
                    update_mvx_vendor_settings('is_sellerreview_varified', 'Enable', 'general');
                }
                delete_mvx_vendor_settings('is_sellerreview_varified', 'general', 'sellerreview');
                if (mvx_is_module_active('store-policy')) {
                    update_mvx_vendor_settings('is_policy_on', 'Enable', 'general');
                }
                delete_mvx_vendor_settings('is_policy_on', 'general', 'policies');
                if (get_mvx_vendor_settings('is_customer_support_details', 'general', 'customer_support_details') && get_mvx_vendor_settings('is_customer_support_details', 'general', 'customer_support_details') == 'Enable') {
                    update_mvx_vendor_settings('is_customer_support_details', 'Enable', 'general');
                }
                delete_mvx_vendor_settings('is_customer_support_details', 'general', 'customer_support_details');
                #endregion
                #region migrate other data
                if (get_mvx_vendor_settings('can_vendor_edit_policy_tab_label', 'capabilities') == 'Enable') {
                    update_mvx_vendor_settings('can_vendor_edit_policy_tab_label', 'Enable', 'general', 'policies');
                }
                delete_mvx_vendor_settings('can_vendor_edit_policy_tab_label', 'capabilities');
                if (get_mvx_vendor_settings('can_vendor_edit_cancellation_policy', 'capabilities') == 'Enable') {
                    update_mvx_vendor_settings('can_vendor_edit_cancellation_policy', 'Enable', 'general', 'policies');
                }
                delete_mvx_vendor_settings('can_vendor_edit_cancellation_policy', 'capabilities');
                if (get_mvx_vendor_settings('can_vendor_edit_refund_policy', 'capabilities') == 'Enable') {
                    update_mvx_vendor_settings('can_vendor_edit_refund_policy', 'Enable', 'general', 'policies');
                }
                delete_mvx_vendor_settings('can_vendor_edit_refund_policy', 'capabilities');
                if (get_mvx_vendor_settings('can_vendor_edit_shipping_policy', 'capabilities') == 'Enable') {
                    update_mvx_vendor_settings('can_vendor_edit_shipping_policy', 'Enable', 'general', 'policies');
                }
                delete_mvx_vendor_settings('can_vendor_edit_refund_policy', 'capabilities');

                if (get_mvx_vendor_settings('simple', 'product') == 'Enable') {
                    update_mvx_vendor_settings('simple', 'Enable', 'capabilities', 'product');
                }
                delete_mvx_vendor_settings('simple', 'product');
                if (get_mvx_vendor_settings('variable', 'product') == 'Enable') {
                    update_mvx_vendor_settings('variable', 'Enable', 'capabilities', 'product');
                }
                delete_mvx_vendor_settings('variable', 'product');
                if (get_mvx_vendor_settings('grouped', 'product') == 'Enable') {
                    update_mvx_vendor_settings('grouped', 'Enable', 'capabilities', 'product');
                }
                delete_mvx_vendor_settings('grouped', 'product');
                if (get_mvx_vendor_settings('external', 'product') == 'Enable') {
                    update_mvx_vendor_settings('external', 'Enable', 'capabilities', 'product');
                }
                delete_mvx_vendor_settings('external', 'product');
                if (get_mvx_vendor_settings('virtual', 'product') == 'Enable') {
                    update_mvx_vendor_settings('virtual', 'Enable', 'capabilities', 'product');
                }
                delete_mvx_vendor_settings('virtual', 'product');
                if (get_mvx_vendor_settings('downloadable', 'product') == 'Enable') {
                    update_mvx_vendor_settings('downloadable', 'Enable', 'capabilities', 'product');
                }
                delete_mvx_vendor_settings('downloadable', 'product');

                /* Capability tab */
                if (get_mvx_vendor_settings('is_submit_product', 'capabilities') == 'Enable') {
                    update_mvx_vendor_settings('is_submit_product', 'Enable', 'capabilities', 'product');
                }
                delete_mvx_vendor_settings('is_submit_product', 'capabilities');
                if (get_mvx_vendor_settings('is_published_product', 'capabilities') == 'Enable') {
                    update_mvx_vendor_settings('is_published_product', 'Enable', 'capabilities', 'product');
                }
                delete_mvx_vendor_settings('is_published_product', 'capabilities');
                if (get_mvx_vendor_settings('is_upload_files', 'capabilities') == 'Enable') {
                    update_mvx_vendor_settings('is_upload_files', 'Enable', 'capabilities', 'product');
                }
                delete_mvx_vendor_settings('is_upload_files', 'capabilities');
                if (get_mvx_vendor_settings('is_submit_coupon', 'capabilities') == 'Enable') {
                    update_mvx_vendor_settings('is_submit_coupon', 'Enable', 'capabilities', 'product');
                }
                delete_mvx_vendor_settings('is_submit_coupon', 'capabilities');
                if (get_mvx_vendor_settings('is_published_coupon', 'capabilities') == 'Enable') {
                    update_mvx_vendor_settings('is_published_coupon', 'Enable', 'capabilities', 'product');
                }
                delete_mvx_vendor_settings('is_published_coupon', 'capabilities');
                if (get_mvx_vendor_settings('is_edit_published_product', 'capabilities') == 'Enable') {
                    update_mvx_vendor_settings('is_edit_published_product', 'Enable', 'capabilities', 'product');
                }
                delete_mvx_vendor_settings('is_edit_published_product', 'capabilities');

                if (!get_mvx_vendor_settings('is_edit_delete_published_product', 'capabilities', 'product')) {
                    update_mvx_vendor_settings('is_edit_delete_published_product', 'Enable', 'capabilities', 'product');
                }
                if (!get_mvx_vendor_settings('is_edit_delete_published_coupon', 'capabilities', 'product')) {
                    update_mvx_vendor_settings('is_edit_delete_published_coupon', 'Enable', 'capabilities', 'product');
                }

                $mvx_pages = get_option('mvx_pages_settings_name');
                $mvx_old_pages = array(
//                'vendor_dashboard' => 'mvx_product_vendor_vendor_dashboard_page_id'
                    'shop_settings' => 'mvx_product_vendor_shop_settings_page_id'
                    , 'view_order' => 'mvx_product_vendor_vendor_orders_page_id'
                    , 'vendor_order_detail' => 'mvx_product_vendor_vendor_order_detail_page_id'
                    , 'vendor_transaction_thankyou' => 'mvx_product_vendor_transaction_widthdrawal_page_id'
                    , 'vendor_transaction_detail' => 'mvx_product_vendor_transaction_details_page_id'
                    , 'vendor_policies' => 'mvx_product_vendor_policies_page_id'
                    , 'vendor_billing' => 'mvx_product_vendor_billing_page_id'
                    , 'vendor_shipping' => 'mvx_product_vendor_shipping_page_id'
                    , 'vendor_report' => 'mvx_product_vendor_report_page_id'
                    , 'vendor_widthdrawals' => 'mvx_product_vendor_widthdrawals_page_id'
                    , 'vendor_university' => 'mvx_product_vendor_university_page_id'
                    , 'vendor_announcements' => 'mvx_product_vendor_announcements_page_id'
                );
                foreach ($mvx_old_pages as $page_slug => $page_option) {
                    $trash_status = wp_trash_post(get_option($page_option));
                    if ($trash_status) {
                        delete_option($page_option);
                        unset($mvx_pages[$page_slug]);
                    }
                }
                update_option('mvx_pages_settings_name', $mvx_pages);

                #region update page option
                if (get_mvx_vendor_settings('mvx_vendor', 'pages')) {
                    update_mvx_vendor_settings('mvx_vendor', get_mvx_vendor_settings('mvx_vendor', 'pages'), 'vendor', 'general');
                }
                if (get_mvx_vendor_settings('vendor_registration', 'pages')) {
                    update_mvx_vendor_settings('vendor_registration', get_mvx_vendor_settings('vendor_registration', 'pages'), 'vendor', 'general');
                }
                $MVX->load_class('endpoints');
                $endpoints = new MVX_Endpoints();
                $endpoints->add_mvx_endpoints();
                flush_rewrite_rules();
                delete_option('mvx_pages_settings_name');
                update_option('mvx_database_upgrade', 'done');
                #endregion
            }
            if ($previous_plugin_version <= '2.6.5') {
                if ($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}mvx_vendor_orders';")) {
                    if (!$wpdb->get_var("SHOW COLUMNS FROM `{$wpdb->prefix}mvx_vendor_orders` LIKE 'commission_status';")) {
                        $wpdb->query("ALTER TABLE {$wpdb->prefix}mvx_vendor_orders ADD `commission_status` varchar(100) NOT NULL DEFAULT 'unpaid';");
                    }
                    if (!$wpdb->get_var("SHOW COLUMNS FROM `{$wpdb->prefix}mvx_vendor_orders` LIKE 'quantity';")) {
                        $wpdb->query("ALTER TABLE {$wpdb->prefix}mvx_vendor_orders ADD `quantity` bigint(20) NOT NULL DEFAULT 1;");
                    }
                    if (!$wpdb->get_var("SHOW COLUMNS FROM `{$wpdb->prefix}mvx_vendor_orders` LIKE 'variation_id';")) {
                        $wpdb->query("ALTER TABLE {$wpdb->prefix}mvx_vendor_orders ADD `variation_id` bigint(20) NOT NULL DEFAULT 0;");
                    }
                    if (!$wpdb->get_var("SHOW COLUMNS FROM `{$wpdb->prefix}mvx_vendor_orders` LIKE 'shipping_tax_amount';")) {
                        $wpdb->query("ALTER TABLE {$wpdb->prefix}mvx_vendor_orders ADD `shipping_tax_amount` varchar(255) NOT NULL DEFAULT 0;");
                    }
                    if (!$wpdb->get_var("SHOW COLUMNS FROM `{$wpdb->prefix}mvx_vendor_orders` LIKE 'line_item_type';")) {
                        $wpdb->query("ALTER TABLE {$wpdb->prefix}mvx_vendor_orders ADD `line_item_type` longtext NULL;");
                    }
                    if (!$wpdb->get_var("SHOW COLUMNS FROM `{$wpdb->prefix}mvx_vendor_orders` LIKE 'commission_paid_date';")) {
                        $wpdb->query("ALTER TABLE {$wpdb->prefix}mvx_vendor_orders ADD `commission_paid_date` timestamp NULL;");
                    }
                }
            }
            if ($previous_plugin_version <= '2.7.3') {
                if ($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}mvx_vendor_orders';")) {
                    $wpdb->query("ALTER TABLE `{$wpdb->prefix}mvx_vendor_orders` DROP INDEX `vendor_orders`;");
                    $wpdb->query("ALTER TABLE `{$wpdb->prefix}mvx_vendor_orders` ADD CONSTRAINT `vendor_orders` UNIQUE (order_id, vendor_id, commission_id, order_item_id);");
                }
            }
            if ($previous_plugin_version <= '2.7.5') {
                if (!class_exists('WP_Roles')) {
                    return;
                }

                if (!isset($wp_roles)) {
                    $wp_roles = new WP_Roles();
                }
                $wp_roles->add_cap('dc_vendor', 'assign_product_terms', true);
                $wp_roles->add_cap('dc_vendor', 'read_product', true);
                $wp_roles->add_cap('dc_vendor', 'read_shop_coupon', true);
                /** remove user wise capability * */
                $args = array('role' => 'dc_vendor', 'fields' => 'ids', 'orderby' => 'registered', 'order' => 'ASC');
                $user_query = new WP_User_Query($args);
                if (!empty($user_query->results)) {
                    foreach ($user_query->results as $vendor_id) {
                        $user = new WP_User($vendor_id);
                        if ($user && !get_user_meta($vendor_id, 'vendor_group_id', true)) {
                            $user->remove_cap('publish_products');
                            $user->remove_cap('assign_product_terms');
                            $user->remove_cap('read_product');
                            $user->remove_cap('read_shop_coupon');
                            $user->remove_cap('read_shop_coupons');
                            $user->remove_cap('edit_posts');
                            $user->remove_cap('edit_shop_coupon');
                            $user->remove_cap('delete_shop_coupon');
                            $user->remove_cap('upload_files');
                            $user->remove_cap('edit_published_products');
                            $user->remove_cap('delete_published_products');
                            $user->remove_cap('edit_product');
                            $user->remove_cap('delete_product');
                            $user->remove_cap('edit_products');
                            $user->remove_cap('delete_products');
                            $user->remove_cap('publish_shop_coupons');
                            $user->remove_cap('edit_shop_coupons');
                            $user->remove_cap('delete_shop_coupons');
                            $user->remove_cap('edit_published_shop_coupons');
                            $user->remove_cap('delete_published_shop_coupons');
                        }
                    }
                }
            }
            if ($previous_plugin_version <= '2.7.7') {
                $wpdb->delete($wpdb->prefix . 'mvx_products_map', array('product_title' => 'AUTO-DRAFT'));
            }
            if (version_compare($previous_plugin_version, '2.7.8', '<=')) {
                update_option('users_can_register', 1);
                delete_option('_is_dismiss_service_notice');
                if (apply_filters('mvx_do_schedule_cron_vendor_weekly_order_stats', true) && !wp_next_scheduled('vendor_weekly_order_stats')) {
                    wp_schedule_event(time(), 'weekly', 'vendor_weekly_order_stats');
                }
                if (apply_filters('mvx_do_schedule_cron_vendor_weekly_order_stats', true) && !wp_next_scheduled('vendor_monthly_order_stats')) {
                    wp_schedule_event(time(), 'monthly', 'vendor_monthly_order_stats');
                }
                $collate = '';
                if ($wpdb->has_cap('collation')) {
                    $collate = $wpdb->get_charset_collate();
                }
                $wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}mvx_vistors_stats`;");
                $create_tables_query = array();
                // mvx_visitors_stats table 
                $create_tables_query[$wpdb->prefix . 'mvx_visitors_stats'] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "mvx_visitors_stats` (
                    `ID` bigint(20) NOT NULL AUTO_INCREMENT,
                    `vendor_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
                    `user_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
                    `user_cookie` varchar(255) NOT NULL,
                    `session_id` varchar(191) NOT NULL,
                    `ip` varchar(60) NOT NULL,
                    `lat` varchar(60) NOT NULL,
                    `lon` varchar(60) NOT NULL,
                    `city` text NOT NULL,
                    `zip` varchar(20) NOT NULL,
                    `regionCode` text NOT NULL,
                    `region` text NOT NULL,
                    `countryCode` text NOT NULL,
                    `country` text NOT NULL,
                    `isp` text NOT NULL,
                    `timezone` varchar(255) NOT NULL,
                    `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,				
                    PRIMARY KEY (`ID`),
                    CONSTRAINT visitor UNIQUE (vendor_id, session_id),
                    KEY vendor_id (vendor_id),
                    KEY user_id (user_id),
                    KEY user_cookie (user_cookie),
                    KEY session_id (session_id),
                    KEY ip (ip)
                    ) $collate;";
                // mvx_cust_questions table 
                $create_tables_query[$wpdb->prefix . 'mvx_cust_questions'] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "mvx_cust_questions` (
                    `ques_ID` bigint(20) NOT NULL AUTO_INCREMENT,
                    `product_ID` BIGINT UNSIGNED NOT NULL DEFAULT '0',
                    `ques_details` text NOT NULL,
                    `ques_by` BIGINT UNSIGNED NOT NULL DEFAULT '0',
                    `ques_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `ques_vote` longtext NULL,
                    PRIMARY KEY (`ques_ID`)
                    ) $collate;";
                // mvx_cust_answers table 
                $create_tables_query[$wpdb->prefix . 'mvx_cust_answers'] = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "mvx_cust_answers` (
                    `ans_ID` bigint(20) NOT NULL AUTO_INCREMENT,
                    `ques_ID` BIGINT UNSIGNED NOT NULL DEFAULT '0',
                    `ans_details` text NOT NULL,
                    `ans_by` BIGINT UNSIGNED NOT NULL DEFAULT '0',
                    `ans_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `ans_vote` longtext NULL,
                    PRIMARY KEY (`ans_ID`),
                    CONSTRAINT ques_id UNIQUE (ques_ID)
                    ) $collate;";

                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                foreach ($create_tables_query as $table => $create_table_query) {
                    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
                        $wpdb->query(str_replace( 'rn', '', wc_clean(wp_unslash(esc_sql($create_table_query)))));
                    }
                }
                if (get_mvx_vendor_settings('sold_by_catalog', 'frontend') && get_mvx_vendor_settings('sold_by_catalog', 'frontend') == 'Enable') {
                    update_mvx_vendor_settings('sold_by_catalog', 'Enable', 'general');
                }
            }
            if (version_compare($previous_plugin_version, '3.0.1', '<=')) {
                $vendors = get_mvx_vendors();
                if ($vendors) {
                    foreach ($vendors as $vendor) {
                        delete_user_meta($vendor->id, 'timezone_string');
                    }
                }
            }
            if (version_compare($previous_plugin_version, '3.0.3', '<=')) {
                $collate = '';
                if ($wpdb->has_cap('collation')) {
                    $collate = $wpdb->get_charset_collate();
                }
                $create_table_query = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mvx_cust_answers` (
		`ans_ID` bigint(20) NOT NULL AUTO_INCREMENT,
		`ques_ID` BIGINT UNSIGNED NOT NULL DEFAULT '0',
                `ans_details` text NOT NULL,
		`ans_by` BIGINT UNSIGNED NOT NULL DEFAULT '0',
		`ans_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `ans_vote` longtext NULL,
		PRIMARY KEY (`ans_ID`),
                CONSTRAINT ques_id UNIQUE (ques_ID)
		) $collate;";
                $wpdb->query($wpdb->prepare("%s", $create_table_query));
            }
            if (version_compare($previous_plugin_version, '3.0.5', '<=')) {
                $max_index_length = 191;
                $wpdb->query("ALTER TABLE `{$wpdb->prefix}mvx_visitors_stats` DROP INDEX `user_cookie`, ADD INDEX `user_cookie`(user_cookie({$max_index_length}))");
            }
            if (version_compare($previous_plugin_version, '3.1.0', '<=')) {
                /* Migrate vendor data application */
                $args = array('post_type' => 'mvx_vendorrequest', 'numberposts' => -1, 'post_status' => 'publish');
                $vendor_applications = get_posts($args);
                if ($vendor_applications) :
                    foreach ($vendor_applications as $application) {
                        $user_id = get_post_meta($application->ID, 'user_id', true);
                        $application_data = get_post_meta($application->ID, 'mvx_vendor_fields', true);
                        if (update_user_meta($user_id, 'mvx_vendor_fields', $application_data)) {
                            wp_delete_post($application->ID, true);
                        }
                    }
                endif;
                if (post_type_exists('mvx_vendorrequest')) {
                    unregister_post_type('mvx_vendorrequest');
                }
            }
            if (version_compare($previous_plugin_version, '3.1.5', '<')) {
                // new vendor shipping setting value based on payment shipping settings
                if (!get_mvx_vendor_settings('is_vendor_shipping_on', 'general') && get_mvx_vendor_settings('give_shipping', 'payment') && 'Enable' === get_mvx_vendor_settings('give_shipping', 'payment')) {
                    update_mvx_vendor_settings('is_vendor_shipping_on', 'Enable', 'general');
                } else {
                    $settings = get_option("mvx_general_settings_name");
                    if (isset($settings['is_vendor_shipping_on']))
                        unset($settings['is_vendor_shipping_on']);
                    update_option('mvx_general_settings_name', $settings);
                }
            }
            if (version_compare($previous_plugin_version, '3.2.0', '<=')) {
                if ($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}mvx_products_map';")) {
                    if (!$wpdb->get_var("SHOW COLUMNS FROM `{$wpdb->prefix}mvx_products_map` LIKE 'product_map_id';") && !$wpdb->get_var("SHOW COLUMNS FROM `{$wpdb->prefix}mvx_products_map` LIKE 'product_id';")) {
                        $wpdb->query("DELETE FROM `{$wpdb->prefix}mvx_products_map`;");
                        $wpdb->query("ALTER TABLE `{$wpdb->prefix}mvx_products_map` AUTO_INCREMENT = 1;");
                        if (!$wpdb->get_var("SHOW COLUMNS FROM `{$wpdb->prefix}mvx_products_map` LIKE 'product_map_id';") && $wpdb->get_var("SHOW COLUMNS FROM `{$wpdb->prefix}mvx_products_map` LIKE 'product_title';")) {
                            $wpdb->query("ALTER TABLE `{$wpdb->prefix}mvx_products_map` CHANGE `product_title` `product_map_id` BIGINT UNSIGNED NOT NULL DEFAULT 0;");
                        }
                        if (!$wpdb->get_var("SHOW COLUMNS FROM `{$wpdb->prefix}mvx_products_map` LIKE 'product_id';") && $wpdb->get_var("SHOW COLUMNS FROM `{$wpdb->prefix}mvx_products_map` LIKE 'product_ids';")) {
                            $wpdb->query("ALTER TABLE `{$wpdb->prefix}mvx_products_map` CHANGE `product_ids` `product_id` BIGINT UNSIGNED NOT NULL DEFAULT 0;");
                        }
                    }
                }
                if (apply_filters('mvx_do_schedule_cron_mvx_spmv_excluded_products_map', true) && !wp_next_scheduled('mvx_spmv_excluded_products_map')) {
                    wp_schedule_event(time(), 'every_5minute', 'mvx_spmv_excluded_products_map');
                }
                // Add delete caps for vendor, specially for media
                $dc_role = get_role('dc_vendor');
                if (!$dc_role->has_cap('delete_posts'))
                    $dc_role->add_cap('delete_posts');
            }
            if (version_compare($previous_plugin_version, '3.2.1', '<=')) {
                if (!wp_next_scheduled('mvx_spmv_product_meta_update') && !get_option('mvx_spmv_product_meta_migrated', false)) {
                    wp_schedule_event(time(), 'hourly', 'mvx_spmv_product_meta_update');
                }
            }
            if (version_compare($previous_plugin_version, '3.2.2', '<=')) {
                // shipping migration for beta
                if(!get_option('mvx_322_vendor_shipping_data_migrated')){
                    $vendors = get_mvx_vendors();
                    $MVX->shipping_gateway->load_shipping_methods();
                    if ($vendors) {
                        foreach ($vendors as $vendor) {
                            $vendor_shipping_data = get_user_meta($vendor->id, 'vendor_shipping_data', true);
                            if(!$vendor_shipping_data) continue;
                            $shipping_class_id = get_user_meta($vendor->id, 'shipping_class_id', true);
                            
                            $raw_zones = WC_Shipping_Zones::get_zones();
                            $raw_zones[] = array('id' => 0);
                            foreach ($raw_zones as $raw_zone) {
                                $zone = new WC_Shipping_Zone($raw_zone['id']);
                                $raw_methods = $zone->get_shipping_methods();
                                foreach ($raw_methods as $raw_method) {
                                    if ($raw_method->id == 'flat_rate' && isset($raw_method->instance_form_fields["class_cost_" . $shipping_class_id])) {
                                        $instance_field = $raw_method->instance_form_fields["class_cost_" . $shipping_class_id];
                                        $instance_settings = $raw_method->instance_settings["class_cost_" . $shipping_class_id];
                                        if($instance_settings){
                                            $instance_id = $zone->add_shipping_method( wc_clean( 'mvx_vendor_shipping' ) );
           
                                            $data = array(
                                                'zone_id'   => $raw_zone['id'],
                                                'method_id' => $raw_method->id,
                                                'vendor_id' => $vendor->id
                                            );
                                            $added_shipping_instance_id = MVX_Shipping_Zone::add_shipping_methods($data);
                                            if($added_shipping_instance_id){
                                                $args = array(
                                                    'instance_id'   => $added_shipping_instance_id,
                                                    'method_id'     => $raw_method->id,
                                                    'zone_id'       => $raw_zone['id'],
                                                    'vendor_id'     => $vendor->id,
                                                    'settings'      => array(
                                                        'title'         => $raw_method->instance_settings['title'],
                                                        'description'   => '',
                                                        'cost'          => '',
                                                        'tax_status'    => 'none',
                                                        'class_cost_'.$shipping_class_id => $instance_settings,
                                                        'calculation_type' => ''
                                                    )
                                                );
                                                $result = MVX_Shipping_Zone::update_shipping_method($args);
                                            }
                                            // remove vendor shipping cost data
                                            $option_name = "woocommerce_" . $raw_method->id . "_" . $raw_method->instance_id . "_settings";
                                            $shipping_details = get_option($option_name);
                                            $class = "class_cost_" . $shipping_class_id;
                                            $shipping_details[$class] = '';
                                            update_option($option_name, $shipping_details);
                                        }
                                    }
                                }
                            }
                        }
                        WC_Cache_Helper::get_transient_version('shipping', true);
                        update_option('mvx_322_vendor_shipping_data_migrated', true);
                    }
                }
            }
            if (version_compare($previous_plugin_version, '3.4.0', '<=')) {
                $args = array('role' => 'dc_vendor', 'fields' => 'ids', 'orderby' => 'registered', 'order' => 'ASC');
                $user_query = new WP_User_Query($args);
                if ( !get_option( 'user_mvx_vendor_role_updated' ) && !empty( $user_query->results ) ) {
                    foreach ( $user_query->results as $vendor_id ) {
                        $user = new WP_User( $vendor_id );
                        $user->add_cap( 'edit_shop_orders' );
                        $user->add_cap( 'read_shop_orders' );
                        $user->add_cap( 'delete_shop_orders' );
                        $user->add_cap( 'publish_shop_orders' );
                        $user->add_cap( 'edit_published_shop_orders' );
                        $user->add_cap( 'delete_published_shop_orders' );
                        $user->remove_cap( 'add_shop_orders' );
                    }
                    update_option( 'user_mvx_vendor_role_updated', true );
                }
                
                if( !get_option('mvx_orders_table_migrated') && !wp_next_scheduled('mvx_orders_migration') ){
                    wp_schedule_event( time(), 'hourly', 'mvx_orders_migration' );
                }
            }
            if (version_compare($previous_plugin_version, '3.5.0', '<=')) {
                if (!$wpdb->get_var("SHOW COLUMNS FROM `{$wpdb->prefix}mvx_cust_questions` LIKE 'status';")) {
                    $wpdb->query("ALTER TABLE {$wpdb->prefix}mvx_cust_questions ADD `status` text NOT NULL;");
                }
            }
            /* Migrate commission data into table */
            do_mvx_commission_data_migrate();
        }
        update_option('dc_product_vendor_plugin_db_version', $new_plugin_version);
    }

}

if (!function_exists('vendor_orders_sort')) {

    /**
     * 
     * @param type $a
     * @param type $b
     * @return type
     * sort vendor order
     */
    function vendor_orders_sort($a, $b) {
        return $a[0] - $b[0];
    }

}

if (!function_exists('sksort')) {

    /**
     * Multilevel sort by subarry key
     * @param array $array
     * @param string $subkey
     * @param Boolean $sort_ascending
     */
    function sksort(&$array, $subkey = "id", $sort_ascending = false) {
        if (count($array)) {
            $temp_array[key($array)] = array_shift($array);
        }
        foreach ($array as $key => $val) {
            $offset = 0;
            $found = false;
            foreach ($temp_array as $tmp_key => $tmp_val) {
                if (!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey])) {
                    $temp_array = array_merge((array) array_slice($temp_array, 0, $offset), array($key => $val), array_slice($temp_array, $offset)
                    );
                    $found = true;
                }
                $offset++;
            }
            if (!$found) {
                $temp_array = array_merge($temp_array, array($key => $val));
            }
        }
        if ($sort_ascending) {
            $array = array_reverse($temp_array);
        } else {
            $array = $temp_array;
        }
    }

}

if (!function_exists('do_mvx_commission_data_migrate')) {

    function do_mvx_commission_data_migrate() {
        global $wpdb;
        /* Update Commission Order Table */
        if (get_option('commission_data_migrated')) {
            return;
        }
        $offset = get_option('dc_commission_offset_to_migrate') ? get_option('dc_commission_offset_to_migrate') : 0;
        $args = array(
            'post_type' => 'dc_commission',
            'post_status' => array('private'),
            'posts_per_page' => 50,
            'order' => 'asc',
            'offset' => $offset
        );

        if (isset(wp_count_posts('dc_commission')->private) && wp_count_posts('dc_commission')->private >= $offset * 50) {
            $commissions = get_posts($args);
            $commissions_to_migrate = array();
            foreach ($commissions as $commission) {
                $commissions_to_migrate[$commission->ID] = array(
                    'order_id' => get_post_meta($commission->ID, '_commission_order_id', true),
                    'products' => get_post_meta($commission->ID, '_commission_product', true),
                    'vendor_id' => get_post_meta($commission->ID, '_commission_vendor', true),
                    'commission_amount' => get_post_meta($commission->ID, '_commission_amount', true),
                    'shipping_amount' => get_post_meta($commission->ID, '_shipping', true),
                    'tax_amount' => get_post_meta($commission->ID, '_tax', true),
                    'paid_status' => get_post_meta($commission->ID, '_paid_status', true)
                );
            }
            $update_data = array();
            foreach ($commissions_to_migrate as $commission_id => $data) {
                $product_count = count($data['products']);
                foreach ($data['products'] as $product_id) {
                    if ($data['vendor_id']) {
                        $vendor = get_mvx_vendor_by_term($data['vendor_id']);
                        $update_data[] = array(
                            'order_id' => $data['order_id'],
                            'commission_id' => $commission_id,
                            'vendor_id' => $vendor->id,
                            'shipping_status' => in_array($vendor->id, (array) get_post_meta($data['order_id'], 'dc_pv_shipped', true)) ? 1 : 0,
                            'product_id' => $product_id,
                            'commission_amount' => round(($data['commission_amount'] / $product_count), 2),
                            'shipping' => round(($data['shipping_amount'] / $product_count), 2),
                            'tax' => round(($data['tax_amount'] / $product_count), 2),
                            'commission_status' => $data['paid_status'],
                            'quantity' => 1,
                            'shipping_tax_amount' => 0,
                            'variation_id' => 0,
                            'line_item_type' => 'product'
                        );
                    }
                }
            }
            foreach ($update_data as $update) {
                if ($wpdb->get_var( $wpdb->prepare("SELECT ID FROM `{$wpdb->prefix}mvx_vendor_orders` WHERE order_id = %d AND commission_id = %d AND vendor_id = %d AND product_id = %d", $update['order_id'], $update['commission_id'], $update['vendor_id'], $update['product_id'] ))) {
                    $wpdb->query($wpdb->prepare("UPDATE `{$wpdb->prefix}mvx_vendor_orders` SET shipping_status = %d, commission_amount = %d, shipping = %d, tax = %d, commission_status = %d, quantity = %d, variation_id = %d, shipping_tax_amount = %d, line_item_type = %d WHERE order_id = %d AND commission_id = %d AND vendor_id = %d AND product_id = %d", $update['shipping_status'], $update['commission_amount'], $update['shipping'], $update['tax'], $update['commission_status'], $update['quantity'], $update['variation_id'], $update['shipping_tax_amount'], $update['line_item_type'], $update['order_id'], $update['commission_id'], $update['vendor_id'], $update['product_id'] ));
                } else {
                    $wpdb->query(
                            $wpdb->prepare(
                                    "INSERT INTO `{$wpdb->prefix}mvx_vendor_orders` 
                                        ( order_id
                                        , commission_id
                                        , commission_amount
                                        , vendor_id
                                        , shipping_status
                                        , order_item_id
                                        , product_id
                                        , variation_id
                                        , tax
                                        , line_item_type
                                        , quantity
                                        , commission_status
                                        , shipping
                                        , shipping_tax_amount
                                        ) VALUES ( %d
                                        , %d
                                        , %s
                                        , %d
                                        , %s
                                        , %d
                                        , %d 
                                        , %d
                                        , %s
                                        , %s
                                        , %d
                                        , %s
                                        , %s
                                        , %s
                                        ) ON DUPLICATE KEY UPDATE `created` = now()"
                                    , $update['order_id']
                                    , $update['commission_id']
                                    , $update['commission_amount']
                                    , $update['vendor_id']
                                    , $update['shipping_status']
                                    , 0
                                    , $update['product_id']
                                    , 0
                                    , $update['tax']
                                    , 'product'
                                    , 1
                                    , $update['commission_status']
                                    , $update['shipping']
                                    , $update['shipping_tax_amount']
                            )
                    );
                }
            }
            $offset++;
            update_option('dc_commission_offset_to_migrate', $offset);
        } else {
            update_option('commission_data_migrated', '1');
        }
    }

}

if (!function_exists('mvx_unpaid_commission_count')) {

    /**
     * Count unpaid commisssion
     * @return int
     */
    function mvx_unpaid_commission_count() {
        return count(array_unique(wp_list_pluck(get_mvx_vendor_orders(array('commission_status' => 'unpaid', 'is_trashed' => '')), 'commission_id')));
    }

}

if (!function_exists('mvx_count_commission')) {

    function mvx_count_commission() {
        $args = array(
            'posts_per_page' => -1,
            'post_type' => 'dc_commission',
            'post_status' => array('private', 'publish')
        );
        $commission_id = wp_list_pluck(get_posts($args), 'ID');
        $commission_count = new stdClass();
        $commission_count->paid = $commission_count->unpaid = $commission_count->reverse = 0;
        foreach ($commission_id as $id) {
            $commission_status = get_post_meta($id, '_paid_status', true);
            if ($commission_status) {
                switch ($commission_status) {
                    case 'paid':
                        $commission_count->paid += 1;
                        break;
                    case 'unpaid':
                        $commission_count->unpaid += 1;
                        break;
                    case 'reverse':
                        $commission_count->reverse += 1;
                        break;
                }
            }
        }
        return $commission_count;
    }

}

if (!function_exists('mvx_count_to_do_list')) {

    function mvx_count_to_do_list() {
        global $MVX;
        $to_do_list_count = 0;

        // pending vendors
        $get_pending_vendors = get_users('role=dc_pending_vendor');
        $to_do_list_count += count( $get_pending_vendors );

        $vendor_ids = get_mvx_vendors(array(), 'ids');

        // pending coupons
        $args = array(
            'posts_per_page' => -1,
            'author__in' => $vendor_ids,
            'post_type' => 'shop_coupon',
            'post_status' => 'pending',
            'meta_query' => array(
                array(
                    'key' => '_dismiss_to_do_list',
                    'compare' => 'NOT EXISTS',
                ),
            )
        );
        $get_pending_coupons = new WP_Query($args);
        $to_do_list_count += count($get_pending_coupons->get_posts());

        // pending products
        $args = array(
            'posts_per_page' => -1,
            'author__in' => $vendor_ids,
            'post_type' => 'product',
            'post_status' => 'pending',
            'meta_query' => array(
                array(
                    'key' => '_dismiss_to_do_list',
                    'compare' => 'NOT EXISTS',
                ),
            )
        );
        $get_pending_products = new WP_Query($args);
        $to_do_list_count += count($get_pending_products->get_posts());

        // pending bank transfer
        $args = array(
            'post_type' => 'mvx_transaction',
            'post_status' => 'mvx_processing',
            'meta_key' => 'transaction_mode',
            'meta_value' => 'direct_bank',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_dismiss_to_do_list',
                    'compare' => 'NOT EXISTS',
                ),
            )
        );
        $transactions = get_posts($args);
        $to_do_list_count += count($transactions);

        return $to_do_list_count; 
    }

}

if (!function_exists('mvx_process_order')) {

    /**
     * Calculate shipping, tax and insert into mvx_vendor_orders table whenever an order is created.
     * 
     * @since 2.7.6
     * @global onject $wpdb
     * @param int $order_id
     * @param WC_Order object $order
     */
    function mvx_process_order($order_id, $order = null) {
        global $wpdb;
        if (!$order)
            $order = wc_get_order($order_id);
        if (get_post_meta($order_id, '_mvx_order_processed', true) && !$order) {
            return;
        }
        $vendor_shipping_array = get_post_meta($order_id, 'dc_pv_shipped', true);
        $mark_ship = 0;
        $items = $order->get_items('line_item');
        $shipping_items = $order->get_items('shipping');
        $vendor_shipping = array();
        foreach ($shipping_items as $shipping_item_id => $shipping_item) {
            $order_item_shipping = new WC_Order_Item_Shipping($shipping_item_id);
            $shipping_vendor_id = $order_item_shipping->get_meta('vendor_id', true);
            $vendor_shipping[$shipping_vendor_id] = array(
                'shipping' => $order_item_shipping->get_total()
                , 'shipping_tax' => $order_item_shipping->get_total_tax()
                , 'package_qty' => $order_item_shipping->get_meta('package_qty', true)
            );
        }
        foreach ($items as $order_item_id => $item) {
            $line_item = new WC_Order_Item_Product($item);
            $product_id = $item['product_id'];
            $variation_id = isset($item['variation_id']) ? $item['variation_id'] : 0;
            if ($product_id) {
                $product_vendors = get_mvx_product_vendors($product_id);
                $product = wc_get_product($product_id);
                if ($product_vendors) {
                    if (isset($product_vendors->id) && is_array($vendor_shipping_array)) {
                        if (in_array($product_vendors->id, $vendor_shipping_array)) {
                            $mark_ship = 1;
                        }
                    }
                    $shipping_amount = $shipping_tax_amount = $tax_amount = 0;
                    if (!empty($vendor_shipping) && isset($vendor_shipping[$product_vendors->id]) && $product_vendors->is_transfer_shipping_enable() && $product->needs_shipping()) {
                        $shipping_amount = (float) round(($vendor_shipping[$product_vendors->id]['shipping'] / $vendor_shipping[$product_vendors->id]['package_qty']) * $line_item->get_quantity(), 2);
                        $shipping_tax_amount = (float) round(($vendor_shipping[$product_vendors->id]['shipping_tax'] / $vendor_shipping[$product_vendors->id]['package_qty']) * $line_item->get_quantity(), 2);
                    }
                    if ($product_vendors->is_transfer_tax_enable() && $line_item->get_total_tax()) {
                        $tax_amount = $line_item->get_total_tax();
                    }
                    $wpdb->query(
                            $wpdb->prepare(
                                    "INSERT INTO `{$wpdb->prefix}mvx_vendor_orders` 
                                        ( order_id
                                        , commission_id
                                        , vendor_id
                                        , shipping_status
                                        , order_item_id
                                        , product_id
                                        , variation_id
                                        , tax
                                        , line_item_type
                                        , quantity
                                        , commission_status
                                        , shipping
                                        , shipping_tax_amount
                                        ) VALUES ( %d
                                        , %d
                                        , %d
                                        , %s
                                        , %d
                                        , %d 
                                        , %d
                                        , %s
                                        , %s
                                        , %d
                                        , %s
                                        , %s
                                        , %s
                                        ) ON DUPLICATE KEY UPDATE `created` = now()"
                                    , $order_id
                                    , 0
                                    , $product_vendors->id
                                    , $mark_ship
                                    , $order_item_id
                                    , $product_id
                                    , $variation_id
                                    , $tax_amount
                                    , 'product'
                                    , $line_item->get_quantity()
                                    , 'unpaid'
                                    , $shipping_amount
                                    , $shipping_tax_amount
                            )
                    );
                }
            }
        }
        update_post_meta($order_id, '_mvx_order_processed', true);
        do_action('mvx_order_processed', $order);
    }

}

if (!function_exists('get_current_vendor_id')) {

    /**
     * get current logged in vendor id
     * @return int
     */
    function get_current_vendor_id() {
        return apply_filters('mvx_current_loggedin_vendor_id', get_current_user_id());
    }

}

if (!function_exists('get_current_vendor')) {

    /**
     * get current logged in vendor
     * @return MVX_Vendor object
     */
    function get_current_vendor() {
        return get_mvx_vendor() ? get_mvx_vendor() : false;
    }

}

if (!function_exists('MVXGenerateTaxonomyHTML')) {

    function MVXGenerateTaxonomyHTML($taxonomy, $product_categories = array(), $categories = array(), $nbsp = '') {

        foreach ($product_categories as $cat) {
            if (apply_filters('is_visible_mvx_frontend_product_cat', true, $cat->term_id, $taxonomy)) {
                echo '<option value="' . esc_attr($cat->term_id) . '"' . selected(in_array($cat->term_id, $categories), true, false) . '>' . $nbsp . esc_html($cat->name) . '</option>';
            }
            $product_child_categories = get_terms($taxonomy, 'orderby=name&hide_empty=0&parent=' . absint($cat->term_id));
            if ($product_child_categories) {
                MVXGenerateTaxonomyHTML($taxonomy, $product_child_categories, $categories, $nbsp . '<span class="sub-cat-pre">&nbsp;&nbsp;&nbsp;&nbsp;</span>');
            }
        }
    }

}

if (!function_exists('mvx_get_vendor_profile_completion')) {

    /**
     * Get vendor profile completion
     * @param int $vendor_id
     * @return array profile_completion
     */
    function mvx_get_vendor_profile_completion($vendor_id) {
        $profile_completion = array('todo' => '', 'progress' => 0);
        $vendor = get_mvx_vendor($vendor_id);
        if ($vendor) {
            $progress_fields = array(
                '_vendor_page_title' => array(
                    'label' => __('Store Name', 'dc-woocommerce-multi-vendor'),
                    'link' => mvx_get_vendor_dashboard_endpoint_url(get_mvx_vendor_settings('mvx_store_settings_endpoint', 'vendor', 'general', 'storefront'))
                ),
                '_vendor_image' => array(
                    'label' => __('Store Image', 'dc-woocommerce-multi-vendor'),
                    'link' => mvx_get_vendor_dashboard_endpoint_url(get_mvx_vendor_settings('mvx_store_settings_endpoint', 'vendor', 'general', 'storefront'))
                ),
                '_vendor_banner' => array(
                    'label' => __('Store Cover Image', 'dc-woocommerce-multi-vendor'),
                    'link' => mvx_get_vendor_dashboard_endpoint_url(get_mvx_vendor_settings('mvx_store_settings_endpoint', 'vendor', 'general', 'storefront'))
                ),
                '_vendor_payment_mode' => array(
                    'label' => __('Payment Method', 'dc-woocommerce-multi-vendor'),
                    'link' => mvx_get_vendor_dashboard_endpoint_url(get_mvx_vendor_settings('mvx_vendor_billing_endpoint', 'vendor', 'general', 'vendor-billing'))
                ),
                '_vendor_added_product' => array(
                    'label' => __('Product', 'dc-woocommerce-multi-vendor'),
                    'link' => mvx_get_vendor_dashboard_endpoint_url(get_mvx_vendor_settings('mvx_add_product_endpoint', 'vendor', 'general', 'add-product'))
                ),
            );
            if (wc_shipping_enabled() && $vendor->is_shipping_enable()) {
                $progress_fields['vendor_shipping_data'] = array(
                    'label' => __('Shipping Data', 'dc-woocommerce-multi-vendor'),
                    'link' => mvx_get_vendor_dashboard_endpoint_url(get_mvx_vendor_settings('mvx_vendor_shipping_endpoint', 'vendor', 'general', 'vendor-shipping'))
                );
            }
            $progress_fields = apply_filters('mvx_vendor_profile_completion_progress_fields', $progress_fields, $vendor->id);
            // initial vendor progress
            if (is_user_mvx_vendor($vendor_id)) {
                $progress = 1;
                $no_of_fields = count($progress_fields) + 1;
            } else {
                $progress = 0;
                $no_of_fields = count($progress_fields);
            }

            $todo = array();
            foreach ($progress_fields as $key => $value) {
                $has_value = get_user_meta($vendor->id, $key, true);
                if ($key == '_vendor_added_product') {
                    if ($has_value || count($vendor->get_products_ids()) > 0) {
                        $progress++;
                    } else {
                        $todo[] = $value;
                    }
                } elseif( $key == 'vendor_shipping_data' ) {
                    if( has_vendor_config_shipping_methods() ) {
                        $progress++;
                    } else {
                        $todo[] = $value;
                    }
                } else {
                    if ($has_value) {
                        $progress++;
                    } else {
                        $todo[] = $value;
                    }
                }
            }
            if ($todo && count($todo) > 0) {
                $random_todo = array_rand($todo);
                $profile_completion['todo'] = $todo[$random_todo];
            } else {
                $profile_completion['todo'] = '';
            }
            $profile_completion['progress'] = number_format((float) (($progress / $no_of_fields) * 100), 0);
        }
        return apply_filters('mvx_vendor_profile_completion_progress_array', $profile_completion, $vendor->id);
    }

}

if (!function_exists('get_attachment_id_by_url')) {

    /**
     * Get an attachment ID by URL.
     * 
     * @param string $url
     * @return int Attachment ID on success, 0 on failure
     */
    function get_attachment_id_by_url($url) {
        $attachment_id = 0;
        $upload_dir = wp_get_upload_dir();
        if (false !== strpos($url, $upload_dir['baseurl'] . '/')) {
            $file = basename($url);
            $args = array(
                'post_type' => 'attachment',
                'post_status' => 'inherit',
                'fields' => 'ids',
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'value' => $file,
                        'compare' => 'LIKE',
                        'key' => '_wp_attached_file',
                    ),
                    array(
                        'value' => $file,
                        'compare' => 'LIKE',
                        'key' => '_wp_attachment_metadata',
                    )
                )
            );
            $attachment_query = new WP_Query($args);
            if ($attachment_query->have_posts()) {
                foreach ($attachment_query->posts as $attachment_id) {
                    $meta = wp_get_attachment_metadata($attachment_id);
                    $original_file = basename($meta['file']);
                    $cropped_image_files = wp_list_pluck($meta['sizes'], 'file');
                    if ($original_file === $file || in_array($file, $cropped_image_files)) {
                        $attachment_id = $attachment_id;
                        break;
                    }
                }
            }
        }
        return $attachment_id;
    }

}

if (!function_exists('get_customer_questions_and_answers')) {

    /**
     * Get Customer Questions and Answers.
     * 
     * @param int $vendor_id
     * @param int $product_id
     * @param array $args
     * @return array $qna_data, if no vendor return false
     */
    function get_customer_questions_and_answers($vendor_id, $product_id = '', $args = array()) {
        if ($vendor_id) {
            $default = array(
                'hide_empty_ans' => 1,
                'keyword' => '',
                'order' => 'ASC',
                'limit' => -1
            );
            $args = wp_parse_args($args, $default);
            $qna_data = array();
            $order = array();
            $vendor = get_mvx_vendor($vendor_id);
            $cust_qna_data = get_term_meta($vendor->term_id, '_customer_qna_data', true);
            if ($product_id && $cust_qna_data) {
                foreach ($cust_qna_data as $key => $qna) {
                    if ($product_id == $qna['product_ID']) {
                        $qna_data[$key] = $qna;
                    }
                }
            } else {
                $qna_data = $cust_qna_data;
            }
            // for data sorting
            if ($qna_data) {
                foreach ($qna_data as $key => $data) {
                    // date wise
                    $order[$key] = $data['qna_created'];
                }
            }
            if ($qna_data && count($qna_data) > 0) {
                // order by created date
                if (strtolower($args['order']) == 'asc') {
                    array_multisort($order, SORT_ASC, $qna_data);
                } else {
                    array_multisort($order, SORT_DESC, $qna_data);
                }
                // answers wise
                if ($args['hide_empty_ans'] == 0) {
                    $qna_data = array_filter($qna_data, function($data) {
                        return ( $data['cust_answer'] == '' );
                    });
                } elseif ($args['hide_empty_ans'] == 1) {
                    $qna_data = array_filter($qna_data, function($data) {
                        return ( $data['cust_answer'] != '' );
                    });
                }
                // keyword wise
                $keyword = strtolower($args['keyword']);
                if ($keyword) {
                    $qna_data = array_filter($qna_data, function($data) use ($keyword) {
                        return ( strpos(strtolower($data['cust_question']), $keyword) !== false );
                    });
                }
                // limit
                if ($args['limit'] != -1) {
                    $qna_data = array_slice($qna_data, 0, absint($args['limit']));
                }
            }
            return $qna_data;
        } else {
            return false;
        }
    }

}

if (!function_exists('get_visitor_ip_data')) {

    /**
     * Get visitor IP information.
     *
     */
    function get_visitor_ip_data() {
        if (!class_exists('WC_Geolocation', false)) {
            include_once( WC_ABSPATH . 'includes/class-wc-geolocation.php' );
        }
        $e = new WC_Geolocation();
        $ip_address = $e->get_ip_address();
        if ($ip_address) {
            if (get_transient('mvx_' . $ip_address)) {
                $data = get_transient('mvx_' . $ip_address);
                if ($data->status != 'error')
                    return $data;
            }
            $service_endpoint = 'http://ip-api.com/json/%s';
            $response = wp_safe_remote_get(sprintf($service_endpoint, $ip_address), array('timeout' => 2));
            if (!is_wp_error($response) && $response['body']) {
                set_transient('mvx_' . $ip_address, json_decode($response['body']), 2 * MONTH_IN_SECONDS);
                return json_decode($response['body']);
            } else {
                $data = new stdClass();
                $data->status = 'error';
                set_transient('mvx_' . $ip_address, $data, 2 * MONTH_IN_SECONDS);
                return $data;
            }
        }
    }

}

if (!function_exists('mvx_save_visitor_stats')) {

    /**
     * Save vistor stats for vendor.
     * 
     * @since 3.0.0
     * @param int $vendor_id
     * @param array $data
     */
    function mvx_save_visitor_stats($vendor_id, $data) {
        global $wpdb;
        $wpdb->query(
                $wpdb->prepare(
                        "INSERT INTO `{$wpdb->prefix}mvx_visitors_stats` 
                        ( vendor_id
                        , user_id
                        , user_cookie
                        , session_id
                        , ip
                        , lat
                        , lon
                        , city
                        , zip
                        , regionCode
                        , region
                        , countryCode
                        , country
                        , isp
                        , timezone
                        ) VALUES ( %d
                        , %d
                        , %s
                        , %s
                        , %s
                        , %s
                        , %s
                        , %s
                        , %s 
                        , %s
                        , %s
                        , %s
                        , %s
                        , %s
                        , %s
                        ) ON DUPLICATE KEY UPDATE `created` = now()"
                        , $vendor_id
                        , $data->user_id
                        , $data->user_cookie
                        , $data->session_id
                        , $data->query
                        , $data->lat
                        , $data->lon
                        , $data->city
                        , $data->zip
                        , $data->region
                        , $data->regionName
                        , $data->countryCode
                        , $data->country
                        , $data->isp
                        , $data->timezone
                )
        );
    }

}

if (!function_exists('mvx_get_visitor_stats')) {

    /**
     * Get vistors stats for vendor.
     * 
     * @since 3.0.0
     * @param int $vendor_id
     * @param string $query_where
     * @param string $query_filter
     * @return array $data
     */
    function mvx_get_visitor_stats($vendor_id, $query_where = '', $query_filter = '') {
        global $wpdb;
        if ($vendor_id) {
            $results = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM {$wpdb->prefix}mvx_visitors_stats WHERE " . wp_unslash(esc_sql($query_where)) . " vendor_id=%d ". wp_unslash(esc_sql($query_filter)) . " ", $vendor_id ) 
            );
            return $results;
        } else {
            return false;
        }
    }

}

if (!function_exists('get_color_shade')) {

    /**
     * Get color shade hexcode.
     * 
     * @since 3.0.0
     * @param string $hexcolor
     * @param int $percent
     * @return string $hexcolor
     */
    function get_color_shade($hex, $percent) {
        // validate hex string
        $hex = preg_replace('/[^0-9a-f]/i', '', $hex);
        $new_hex = '#';
        if (strlen($hex) < 6) {
            $hex = $hex[0] + $hex[0] + $hex[1] + $hex[1] + $hex[2] + $hex[2];
        }
        // convert to decimal and change luminosity
        for ($i = 0; $i < 3; $i++) {
            $dec = hexdec(substr($hex, $i * 2, 2));
            $dec = min(max(0, $dec + $dec * $percent), 255);
            $new_hex .= str_pad(dechex($dec), 2, 0, STR_PAD_LEFT);
        }
        return $new_hex;
    }

}

if (!function_exists('get_mvx_product_policies')) {

    /**
     * Get product policies.
     * 
     * @since 3.0.0
     * @param int $product_id
     * @return array $policies
     */
    function get_mvx_product_policies($product_id = 0) {
        $product = wc_get_product($product_id);
        $policies = array();
        if ($product) {
            $shipping_policy = get_mvx_vendor_settings('shipping_policy');
            $refund_policy = get_mvx_vendor_settings('refund_policy');
            $cancellation_policy = get_mvx_vendor_settings('cancellation_policy');
            if (apply_filters('mvx_vendor_can_overwrite_policies', true) && $vendor = get_mvx_product_vendors($product->get_id())) {
                $shipping_policy = get_user_meta($vendor->id, '_vendor_shipping_policy', true) ? get_user_meta($vendor->id, '_vendor_shipping_policy', true) : $shipping_policy;
                $refund_policy = get_user_meta($vendor->id, '_vendor_refund_policy', true) ? get_user_meta($vendor->id, '_vendor_refund_policy', true) : $refund_policy;
                $cancellation_policy = get_user_meta($vendor->id, '_vendor_cancellation_policy', true) ? get_user_meta($vendor->id, '_vendor_cancellation_policy', true) : $cancellation_policy;
            }
            if (get_post_meta($product->get_id(), '_mvx_shipping_policy', true)) {
                $shipping_policy = get_post_meta($product->get_id(), '_mvx_shipping_policy', true);
            }
            if (get_post_meta($product->get_id(), '_mvx_refund_policy', true)) {
                $refund_policy = get_post_meta($product->get_id(), '_mvx_refund_policy', true);
            }
            if (get_post_meta($product->get_id(), '_mvx_cancallation_policy', true)) {
                $cancellation_policy = get_post_meta($product->get_id(), '_mvx_cancallation_policy', true);
            }
            if (!empty($shipping_policy)) {
                $policies['shipping_policy'] = $shipping_policy;
            }
            if (!empty($refund_policy)) {
                $policies['refund_policy'] = $refund_policy;
            }
            if (!empty($cancellation_policy)) {
                $policies['cancellation_policy'] = $cancellation_policy;
            }
        }
        return $policies;
    }

}

if (!function_exists('get_mvx_vendor_dashboard_visitor_stats_data')) {

    /**
     * Get vendor visitor stats data.
     * 
     * @since 3.0.0
     * @param int $vendor
     * @return array $stats_data_visitors
     */
    function get_mvx_vendor_dashboard_visitor_stats_data($vendor_id = '') {
        if ($vendor_id) {
            if (get_transient('mvx_visitor_stats_data_' . $vendor_id)) {
                $data = get_transient('mvx_visitor_stats_data_' . $vendor_id);
                if ((isset($data[7]['map_stats']) && !empty($data[7]['map_stats'])) || (isset($data[30]['map_stats']) && !empty($data[30]['map_stats'])))
                    return $data;
            }

            $visitor_map_filter_attr = apply_filters('mvx_vendor_visitors_map_filter_attr', array(
                '7' => __('Last 7 days', 'dc-woocommerce-multi-vendor'),
                '30' => __('Last 30 days', 'dc-woocommerce-multi-vendor'),
            ));
            $stats_data_visitors = array(
                'lang' => array('visitors' => __(' visitors', 'dc-woocommerce-multi-vendor'))
            );
            $color_palet = get_mvx_vendor_settings('vendor_color_scheme_picker', 'seller_dashbaord', 'outer_space_blue');
            $color_palet_array = array('outer_space_blue' => '#316fa8', 'green_lagoon' => '#00796a', 'old_west' => '#ad8162', 'wild_watermelon' => '#fb3f4e');
            $color_shade = apply_filters('mvx_visitor_map_widget_primary_color_n_shade', array($color_palet_array[$color_palet], '0.2'));
            $primary_color = isset($color_shade[0]) ? $color_shade[0] : $color_palet_array[$color_palet];
            $shade = isset($color_shade[1]) ? $color_shade[1] : '0.2';
            if ($visitor_map_filter_attr) {
                foreach ($visitor_map_filter_attr as $period => $value) {
                    $st_dt = date('Y-m-d H:i:s', strtotime("-{$period} days"));
                    $en_dt = date('Y-m-d 23:59:59');
                    $where = "created BETWEEN '{$st_dt}' AND '{$en_dt}' AND ";
                    $filter = "GROUP BY user_cookie";
                    $visitor_data = mvx_get_visitor_stats($vendor_id, $where, $filter);
                    $data_visitors = array('map_stats' => array(), 'data_stats' => '<tr><td class="no_data" colspan="2">' . __('No Data', 'dc-woocommerce-multi-vendor') . '</td></tr>');
                    if ($visitor_data) {
                        $users = array();
                        $unique_data_visitors = array();
                        foreach ($visitor_data as $key => $data) {
                            if ($data->user_id == 0) {
                                $unique_data_visitors[] = $data;
                            } else {
                                if (!in_array($data->user_id, $users)) {
                                    $users[] = $data->user_id;
                                    $unique_data_visitors[] = $data;
                                }
                            }
                        }
                        $map_stats = array();
                        foreach ($unique_data_visitors as $key => $data) {
                            $country_code = strtolower($data->countryCode);
                            if (isset($map_stats[$country_code])) {
                                $map_stats[$country_code]['hits_count'] = $map_stats[$country_code]['hits_count'] + 1;
                            } else {
                                $map_stats[$country_code]['hits_count'] = 1;
                            }
                            $map_stats[$country_code]['hits_percent'] = round(($map_stats[$country_code]['hits_count'] / count($unique_data_visitors)) * 100, 2);
                        }
                        $hits = array();
                        foreach ($map_stats as $key => $data) {
                            $hits[$key] = $data['hits_count'];
                        }
                        array_multisort($hits, SORT_DESC, $map_stats);

                        $i = 1;
                        $new_color = '';
                        $data_stats = '';
                        if ($map_stats) {
                            $data_stats .= '<thead><tr><th>' . __('Country', 'dc-woocommerce-multi-vendor') . '</th><th>' . __('% Users', 'dc-woocommerce-multi-vendor') . '</td></tr></thead><tbody>';
                            foreach (array_slice($map_stats, 0, 5) as $key => $value) {
                                if ($i == 1) {
                                    $map_stats[$key]['color'] = $primary_color;
                                    $new_color = $primary_color;
                                    $data_stats .= '<tr><td class="region" bgcolor="' . $new_color . '">' . strtoupper($key) . '</td><td>' . $value['hits_percent'] . '%</td></tr>';
                                } else {
                                    $new_color = get_color_shade($new_color, $shade);
                                    $map_stats[$key]['color'] = $new_color;
                                    $data_stats .= '<tr><td class="region" bgcolor="' . $new_color . '">' . strtoupper($key) . '</td><td>' . $value['hits_percent'] . '%</td></tr>';
                                }
                                $i++;
                            }
                            $data_stats .= '</tbody>';
                        }
                        $data_visitors['map_stats'] = $map_stats;
                        $data_visitors['data_stats'] = $data_stats;
                    }
                    $stats_data_visitors[$period] = $data_visitors;
                }
            }
            set_transient('mvx_visitor_stats_data_' . $vendor_id, $stats_data_visitors, 12 * HOUR_IN_SECONDS);
        }
        return $stats_data_visitors;
    }

}

if (!function_exists('get_mvx_vendor_dashboard_stats_reports_data')) {

    /**
     * Get vendor dashboard stats reports data.
     * 
     * @since 3.0.0
     * @param object $vendor
     * @return array $stats_reports_data
     */
    function get_mvx_vendor_dashboard_stats_reports_data($vendor = '') {
        if (empty($vendor))
            $vendor = get_current_vendor();
        if ($vendor) {
            if (get_transient('mvx_stats_report_data_' . $vendor->id)) {
                return get_transient('mvx_stats_report_data_' . $vendor->id);
            }

            $stats_report_data = array();
            $mvx_stats_table = array();
            $raw_stats_data = array();
            $stats_difference = array();
            $today = @date('Y-m-d 00:00:00', strtotime("+1 days"));
            $stats_reports_periods = apply_filters('mvx_vendor_stats_reports_periods', array(
                '7' => __('Last 7 days', 'dc-woocommerce-multi-vendor'),
                '30' => __('Last 30 days', 'dc-woocommerce-multi-vendor'),
            ));
            if ($stats_reports_periods) {
                foreach ($stats_reports_periods as $key => $value) {
                    $stats_report_data[$key]['_mvx_stats_period'] = $stats_reports_periods[$key];
                    $args = array(
                        'start_date' => date('Y-m-d 00:00:00', strtotime("-$key days")),
                        'end_date' => $today,
                        'is_trashed' => ''
                    );
                    $vendor_current_stats = $vendor->get_vendor_orders_reports_of('vendor_stats', $args);
                    $raw_stats_data['current'] = $vendor_current_stats;
                    $mvx_stats_table['current_traffic_no'] = $vendor_current_stats['traffic_no'];
                    $mvx_stats_table['current_coupon_total'] = wc_price($vendor_current_stats['coupon_total'], array('decimals' => 0));
                    $mvx_stats_table['current_earning'] = wc_price($vendor_current_stats['earning'], array('decimals' => 0));
                    $mvx_stats_table['current_sales_total'] = wc_price($vendor_current_stats['sales_total'], array('decimals' => 0));
                    $mvx_stats_table['current_withdrawal'] = wc_price($vendor_current_stats['withdrawal'], array('decimals' => 0));
                    $mvx_stats_table['current_orders_no'] = $vendor_current_stats['orders_no'];
                    // previous data
                    $previous_days_range = $key * 2;
                    $args = array(
                        'start_date' => date('Y-m-d 00:00:00', strtotime("-$previous_days_range days")),
                        'end_date' => date('Y-m-d 00:00:00', strtotime("-$key days")),
                        'is_trashed' => ''
                    );
                    $vendor_previous_stats = $vendor->get_vendor_orders_reports_of('vendor_stats', $args);
                    $raw_stats_data['previous'] = $vendor_previous_stats;
                    $mvx_stats_table['previous_traffic_no'] = $vendor_previous_stats['traffic_no'];
                    $mvx_stats_table['previous_coupon_total'] = wc_price($vendor_previous_stats['coupon_total'], array('decimals' => 0));
                    $mvx_stats_table['previous_earning'] = wc_price($vendor_previous_stats['earning'], array('decimals' => 0));
                    $mvx_stats_table['previous_sales_total'] = wc_price($vendor_previous_stats['sales_total'], array('decimals' => 0));
                    $mvx_stats_table['previous_withdrawal'] = wc_price($vendor_previous_stats['withdrawal'], array('decimals' => 0));
                    $mvx_stats_table['previous_orders_no'] = $vendor_previous_stats['orders_no'];

                    $stats_report_data[$key]['_mvx_stats_table'] = $mvx_stats_table;
                    $stats_report_data[$key]['_raw_stats_data'] = $raw_stats_data;
                    foreach ($vendor_previous_stats as $prev_key => $prev_value) {
                        if ($prev_value != 0) {
                            if ($prev_key == 'orders_no') {
                                $stats_difference['_mvx_diff_' . $prev_key] = ($vendor_current_stats[$prev_key] - $vendor_previous_stats[$prev_key]);
                            } else {
                                $stats_difference['_mvx_diff_' . $prev_key] = round((($vendor_current_stats[$prev_key] - $vendor_previous_stats[$prev_key]) / $vendor_previous_stats[$prev_key]) * 100);
                            }
                        } else {
                            $stats_difference['_mvx_diff_' . $prev_key] = 'no_data';
                        }
                    }
                    $aov = 0;
                    if ($vendor_current_stats['orders_no'] != 0) {
                        $aov = $vendor_current_stats['sales_total'] / $vendor_current_stats['orders_no'];
                    }
                    $stats_report_data[$key]['stats_difference'] = $stats_difference;
                    $stats_report_data[$key]['_mvx_stats_aov'] = wc_price($aov, array('decimals' => 0));
                    $stats_report_data[$key]['_mvx_stats_lang_up'] = __('is up by', 'dc-woocommerce-multi-vendor');
                    $stats_report_data[$key]['_mvx_stats_lang_down'] = __('is down by', 'dc-woocommerce-multi-vendor');
                    $stats_report_data[$key]['_mvx_stats_lang_are_up'] = __('are up by', 'dc-woocommerce-multi-vendor');
                    $stats_report_data[$key]['_mvx_stats_lang_are_down'] = __('are down by', 'dc-woocommerce-multi-vendor');
                    $stats_report_data[$key]['_mvx_stats_lang_same'] = __('remains same', 'dc-woocommerce-multi-vendor');
                    $stats_report_data[$key]['_mvx_stats_lang_no_amount'] = __('no amount', 'dc-woocommerce-multi-vendor');
                    $stats_report_data[$key]['_mvx_stats_lang_no_prev'] = __('no prior data', 'dc-woocommerce-multi-vendor');
                }
            } else {
                $days_range = apply_filters('mvx_vendor_stats_default_days_range', 7);
                $stats_report_data[$key]['_mvx_stats_period'] = printf(__('Last %d days', 'dc-woocommerce-multi-vendor'), $days_range);
                $args = array(
                    'start_date' => date('Y-m-d 00:00:00', strtotime("-$days_range days")),
                    'end_date' => $today,
                    'is_trashed' => ''
                );
                $vendor_current_stats = $vendor->get_vendor_orders_reports_of('vendor_stats', $args);
                $raw_stats_data['current'] = $vendor_current_stats;
                $mvx_stats_table['current_traffic_no'] = $vendor_current_stats['traffic_no'];
                $mvx_stats_table['current_coupon_total'] = wc_price($vendor_current_stats['coupon_total'], array('decimals' => 0));
                $mvx_stats_table['current_earning'] = wc_price($vendor_current_stats['earning'], array('decimals' => 0));
                $mvx_stats_table['current_sales_total'] = wc_price($vendor_current_stats['sales_total'], array('decimals' => 0));
                $mvx_stats_table['current_withdrawal'] = wc_price($vendor_current_stats['withdrawal'], array('decimals' => 0));
                $mvx_stats_table['current_orders_no'] = $vendor_current_stats['orders_no'];
                // previous data
                $previous_days_range = $days_range * 2;
                $args = array(
                    'start_date' => date('Y-m-d 00:00:00', strtotime("-$previous_days_range days")),
                    'end_date' => date('Y-m-d 00:00:00', strtotime("-$days_range days")),
                    'is_trashed' => ''
                );
                $vendor_previous_stats = $vendor->get_vendor_orders_reports_of('vendor_stats', $args);
                $raw_stats_data['previous'] = $vendor_previous_stats;
                $mvx_stats_table['previous_traffic_no'] = $vendor_previous_stats['traffic_no'];
                $mvx_stats_table['previous_coupon_total'] = wc_price($vendor_previous_stats['coupon_total'], array('decimals' => 0));
                $mvx_stats_table['previous_earning'] = wc_price($vendor_previous_stats['earning'], array('decimals' => 0));
                $mvx_stats_table['previous_sales_total'] = wc_price($vendor_previous_stats['sales_total'], array('decimals' => 0));
                $mvx_stats_table['previous_withdrawal'] = wc_price($vendor_previous_stats['withdrawal'], array('decimals' => 0));
                $mvx_stats_table['previous_orders_no'] = $vendor_previous_stats['orders_no'];

                $stats_report_data[$days_range]['_mvx_stats_table'] = $mvx_stats_table;
                $stats_report_data[$days_range]['_raw_stats_data'] = $raw_stats_data;
                foreach ($vendor_previous_stats as $prev_key => $prev_value) {
                    if ($prev_value != 0) {
                        if ($prev_key == 'orders_no') {
                            $stats_difference['_mvx_diff_' . $prev_key] = ($vendor_current_stats[$prev_key] - $vendor_previous_stats[$prev_key]);
                        } else {
                            $stats_difference['_mvx_diff_' . $prev_key] = round((($vendor_current_stats[$prev_key] - $vendor_previous_stats[$prev_key]) / $vendor_previous_stats[$prev_key]) * 100);
                        }
                    } else {
                        $stats_difference['_mvx_diff_' . $prev_key] = 'no_data';
                    }
                }
                $stats_report_data[$key]['stats_difference'] = $stats_difference;
                $aov = 0;
                if ($vendor_current_stats['orders_no'] != 0) {
                    $aov = $vendor_current_stats['sales_total'] / $vendor_current_stats['orders_no'];
                }
                $stats_report_data[$days_range]['_mvx_stats_aov'] = wc_price($aov, array('decimals' => 0));
                $stats_report_data[$days_range]['_mvx_stats_lang_up'] = __('is up by ', 'dc-woocommerce-multi-vendor');
                $stats_report_data[$days_range]['_mvx_stats_lang_down'] = __('is down by ', 'dc-woocommerce-multi-vendor');
                $stats_report_data[$days_range]['_mvx_stats_lang_are_up'] = __('are up by', 'dc-woocommerce-multi-vendor');
                $stats_report_data[$days_range]['_mvx_stats_lang_are_down'] = __('are down by', 'dc-woocommerce-multi-vendor');
                $stats_report_data[$days_range]['_mvx_stats_lang_same'] = __('remains same', 'dc-woocommerce-multi-vendor');
                $stats_report_data[$days_range]['_mvx_stats_lang_no_amount'] = __('no amount', 'dc-woocommerce-multi-vendor');
                $stats_report_data[$days_range]['_mvx_stats_lang_no_prev'] = __('no prior data', 'dc-woocommerce-multi-vendor');
            }
            set_transient('mvx_stats_report_data_' . $vendor->id, $stats_report_data, DAY_IN_SECONDS);
            return $stats_report_data;
        }
    }

}

if (!function_exists('mvx_date')) {

    /**
     * MVX date formatter function
     * @param DateTime $date
     * @return date string
     */
    function mvx_date($date) {
        $date = wc_string_to_datetime($date)->setTimezone(new DateTimeZone('UTC'));
        $date = wc_string_to_datetime($date)->setTimezone(new DateTimeZone(get_vendor_timezone_string()));
        return $date->date_i18n( get_option('date_format'), $date );
    }

}


if (!function_exists('get_vendor_timezone_string')) {

    /**
     * MVX timezone string
     * @param $vendor_id int Vendor ID
     * @return Timezone string
     */
    function get_vendor_timezone_string($vendor_id = '') {
        if (!$vendor_id)
            $vendor_id = get_current_user_id();
        // If site timezone string exists, return it.
        if ($timezone = get_user_meta($vendor_id, 'timezone_string', true) ? get_user_meta($vendor_id, 'timezone_string', true) : get_option('timezone_string')) {
            return $timezone;
        }
        // Get UTC offset, if it isn't set then return UTC.
        if (0 === ( $utc_offset = intval(get_user_meta($vendor_id, 'gmt_offset', true) ? get_user_meta($vendor_id, 'gmt_offset', true) : get_option('gmt_offset', 0)) )) {
            return 'UTC';
        }

        // Adjust UTC offset from hours to seconds.
        $utc_offset *= 3600;


        // Attempt to guess the timezone string from the UTC offset.
        if ($timezone = timezone_name_from_abbr('', $utc_offset)) {
            return $timezone;
        }

        // Last try, guess timezone string manually.
        foreach (timezone_abbreviations_list() as $abbr) {
            foreach ($abbr as $city) {
                if ((bool) date('I') === (bool) $city['dst'] && $city['timezone_id'] && intval($city['offset']) === $utc_offset) {
                    return $city['timezone_id'];
                }
            }
        }

        // Fallback to wc_timezone_string.
        return 'UTC';
    }

}

if (!function_exists('is_commission_requested_for_withdrawals')) {

    /**
     * MVX commission requested for withdrawals
     * @return True/false bool
     */
    function is_commission_requested_for_withdrawals($commission_id) {
        if (!$commission_id)
            return false;
        $args = array(
            'post_type' => 'mvx_transaction',
            'posts_per_page' => -1,
            'post_status' => 'mvx_processing',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'commission_detail',
                    'value' => sprintf(':"%s";', $commission_id),
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'commission_detail',
                    'value' => sprintf(';i:%d;', $commission_id),
                    'compare' => 'LIKE'
                )
            )
        );
        $transactions = new WP_Query($args);
        $have_transactions = $transactions->get_posts();
        if ($have_transactions) {
            return true;
        } else {
            return false;
        }
    }

}

if (!function_exists('get_mvx_vendor_order_shipping_method')) {

    /**
     * MVX vendor order shipping method
     * @param Order ID $order_id
     * @param Vendor ID $vendor_id
     * @return shipping method object on success or else false
     */
    function get_mvx_vendor_order_shipping_method($order_id, $vendor_id = '') {
        if (!$order_id)
            return false;
        $order = wc_get_order($order_id);
        if (!$order)
            return false;
        $vendor_id = !empty($vendor_id) ? $vendor_id : get_current_user_id();
        foreach ($order->get_shipping_methods() as $shipping_method) {
            $meta_data = $shipping_method->get_formatted_meta_data('');
            foreach ($meta_data as $meta_id => $meta) :
                if (!in_array($meta->key, array('vendor_id'), true)) {
                    continue;
                }

                if ($meta->value && $meta->value == $vendor_id)
                    return $shipping_method;

            endforeach;
        }
        return false;
    }

}

if (!function_exists('get_url_from_upload_field_value')) {

    /**
     * Returns image url from dc-wp-field upload value.
     *
     * @param string $type (default: 'image')
     * @param string/array $size (default: 'full')
     * @return string
     */
    function get_url_from_upload_field_value($value, $size = 'full', $protocol = false) {
        global $wp_version;
    
        if (!$value)
            return false;
        $attach_id = $image = '';
        if (!is_numeric($value)) {
            if (version_compare($wp_version, '4.0.0', '>=')) {
                $attach_id = attachment_url_to_postid($value);
            }else{
                $attach_id = get_attachment_id_by_url($value);
            }
            if ($attach_id == 0) { /* if no attachment id found from attachment url */
                $image = $value;
            }
        } else {
            $attach_id = $value;
        }
        $image_attributes = wp_get_attachment_image_src(absint($attach_id), $size, true);
        if (is_array($image_attributes) && count($image_attributes)) {
            $image = $image_attributes[0];
        }

        $image = apply_filters('mvx_image_url_from_upload_field_value_src', $image);
        if (!$protocol)
            return str_replace(array('https://', 'http://'), '//', $image);
        else
            return $image;
    }

}


if (!function_exists('mvx_get_latlng_distance')) {
    /*
     * calculates the distance between two points (given the latitude/longitude of those points).
     * lat1, lon1 = Latitude and Longitude of point 1
     * lat2, lon2 = Latitude and Longitude of point 2
     * unit = the unit you desire for results            
      where: 'M' is statute miles (default)
      'K' is kilometers
      'N' is nautical miles
     */

    function mvx_get_latlng_distance($lat1, $lon1, $lat2, $lon2, $unit = 'M') {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            do_action('mvx_get_latlng_distance', $lat1, $lon1, $lat2, $lon2, $unit, $dist);
            return $miles;
        }
    }

}


if (!function_exists('mvx_get_vendor_list_map_store_data')) {

    function mvx_get_vendor_list_map_store_data($vendors, $request) {
        global $MVX;
        $location = isset($request['locationText']) ? $request['locationText'] : '';
        $distance_type = isset($request['distanceSelect']) ? $request['distanceSelect'] : 'M';
        $radius = isset($request['radiusSelect']) ? $request['radiusSelect'] : '5';
        $map_stores = array('vendors' => array(), 'stores' => array());

        foreach ($vendors as $vendor_id) {
            $vendor = get_mvx_vendor($vendor_id);
            $store_lat = get_user_meta($vendor_id, '_store_lat', true);
            $store_lng = get_user_meta($vendor_id, '_store_lng', true);
            $image = $vendor->get_image() ? $vendor->get_image('image', array(125, 125)) : $MVX->plugin_url . 'assets/images/WP-stdavatar.png';
            $rating_info = mvx_get_vendor_review_info($vendor->term_id);
            $rating = round($rating_info['avg_rating'], 2);
            $count = intval($rating_info['total_rating']);
            if ($count > 0) {
                $rating_html = '<div itemprop="reviewRating" class="star-rating" style="float:none;">
		<span style="width:' . (( $rating / 5 ) * 100) . '%"><strong itemprop="ratingValue">' . $rating . '</strong> </span>
                </div>';
            } else {
                $rating_html = __('No Rating Yet', 'dc-woocommerce-multi-vendor');
            }
            $info_html = '<div class="info-store-wrapper"> 
                        <div class="store-img-wrap">
                            <img src="' . $image . '" class="info-store-img" /> 
                            <a href="' . $vendor->get_permalink() . '">' . __('Visit', 'dc-woocommerce-multi-vendor') . '</a>
                        </div>
                        <div class="info-store-header">
                            <p class="store-name">' . $vendor->page_title . '</p>
                            <ul>
                                <li>' . $rating_html . '</li>
                                <li>' . $vendor->user_data->data->user_email . '</li>
                                <li>' . $vendor->phone . '</li>
                            </ul>
                        </div> 
                    </div> ';

            if ((isset($request['mvx_vlist_center_lat']) && !empty($request['mvx_vlist_center_lat'])) && (isset($request['mvx_vlist_center_lng']) && !empty($request['mvx_vlist_center_lng']))) {
                if (!empty($radius) && ((!empty($store_lat) && !empty($store_lng)) || (!empty($location) && !empty($store_lat) && !empty($store_lng)))) {
                    $distance = mvx_get_latlng_distance($request['mvx_vlist_center_lat'], $request['mvx_vlist_center_lng'], $store_lat, $store_lng, $distance_type);
                    if ($distance < $radius) {
                        $map_stores['stores'][] = array(
                            'store_name' => $vendor->page_title,
                            'store_url' => $vendor->get_permalink(),
                            'location' => array('lat' => $store_lat, 'lng' => $store_lng),
                            'info_html' => apply_filters('mvx_vendor_list_map_vendor_info_html', $info_html, $vendor),
                        );
                        $map_stores['vendors'][] = $vendor_id;
                    }
                } elseif (empty($location) && empty($radius)) {
                    if (!empty($store_lat) && !empty($store_lng)) {
                        $map_stores['stores'][] = array(
                            'store_name' => $vendor->page_title,
                            'store_url' => $vendor->get_permalink(),
                            'location' => array('lat' => $store_lat, 'lng' => $store_lng),
                            'info_html' => apply_filters('mvx_vendor_list_map_vendor_info_html', $info_html, $vendor),
                        );
                    }
                    $map_stores['vendors'][] = $vendor_id;
                }
            } else {
                if (!empty($store_lat) && !empty($store_lng)) {
                    $map_stores['stores'][] = array(
                        'store_name' => $vendor->page_title,
                        'store_url' => $vendor->get_permalink(),
                        'location' => array('lat' => $store_lat, 'lng' => $store_lng),
                        'info_html' => apply_filters('mvx_vendor_list_map_vendor_info_html', $info_html, $vendor),
                    );
                }
                $map_stores['vendors'][] = $vendor_id;
            }
        }
        return apply_filters('mvx_get_vendor_list_map_store_data', $map_stores, $vendors, $request);
    }

}

if (!function_exists('mvx_get_vendor_specific_order_charge')) {

    function mvx_get_vendor_specific_order_charge($order) {
        $vendor_specific_admin_commision = array();
        if (!$order) {
            return $vendor_specific_admin_commision;
        }
        if (!is_object($order))
            $order = wc_get_order($order);
        if ($order) :
            $vendor_specific_admin_commision['order_total'] = $order->get_total();
            $items = $order->get_items('line_item');
            $marchants = array();
            foreach ($items as $order_item_id => $item) {
                $line_item = new WC_Order_Item_Product($item);
                $product_id = $item['product_id'];
                if ($product_id) {
                    $product_vendors = get_mvx_product_vendors($product_id);
                    if (!empty($product_vendors) && isset($product_vendors->term_id)) {
                        $marchants[] = $product_vendors->id;
                        $line_item_total = $line_item->get_total() + $line_item->get_total_tax(); // Item Total + Item Tax

                        if (isset($vendor_specific_admin_commision[$product_vendors->id]))
                            $vendor_specific_admin_commision[$product_vendors->id] = $vendor_specific_admin_commision[$product_vendors->id] + $line_item_total;
                        else
                            $vendor_specific_admin_commision[$product_vendors->id] = $line_item_total;
                    }else {
                        $post = get_post($product_id);
                        $marchants[] = $post->post_author;
                    }
                }
            }
            $vendor_specific_admin_commision['order_marchants'] = array_unique($marchants);

            $shipping_items = $order->get_items('shipping');
            foreach ($shipping_items as $shipping_item_id => $shipping_item) {
                $order_item_shipping = new WC_Order_Item_Shipping($shipping_item_id);
                $shipping_vendor_id = $order_item_shipping->get_meta('vendor_id', true);
                if ($shipping_vendor_id > 0) {
                    $shipping_item_total = $order_item_shipping->get_total() + $order_item_shipping->get_total_tax(); // Shipping Total + Shipping Tax

                    if (isset($vendor_specific_admin_commision[$shipping_vendor_id]))
                        $vendor_specific_admin_commision[$shipping_vendor_id] = $vendor_specific_admin_commision[$shipping_vendor_id] + $shipping_item_total;
                    else
                        $vendor_specific_admin_commision[$shipping_vendor_id] = $shipping_item_total;
                }
            }
        endif;

        return $vendor_specific_admin_commision;
    }

}

if (!function_exists('mvx_get_geocoder_components')) {

    function mvx_get_geocoder_components($components = array()) {
        $address_components = array(
            'street_number' => '',
            'street_name' => '',
            'street' => '',
            'premise' => '',
            'neighborhood' => '',
            'city' => '',
            'county' => '',
            'region_name' => '',
            'region_code' => '',
            'country_name' => '',
            'country_code' => '',
            'postcode' => '',
            'address' => '',
            'formatted_address' => '',
            'latitude' => '',
            'longitude' => '',
        );

        foreach ($components as $key => $component) {
            $component = (object) $component;
            $type = implode(",", $component->types);
            if ($type == 'street_number' && !empty($component->long_name)) {
                $address_components['street_number'] = $component->long_name;
            } elseif ($type == 'route' && !empty($component->long_name)) {
                $address_components['street_name'] = $component->long_name;
                $address_components['street'] = !empty($address_components['street_number']) ? $address_components['street_number'] . ' ' . $component->long_name : $component->long_name;
            } elseif ($type == 'subpremise' && !empty($component->long_name)) {
                $address_components['premise'] = $component->long_name;
            } elseif ($type == 'neighborhood,political' && !empty($component->long_name)) {
                $address_components['neighborhood'] = $component->long_name;
            } elseif ($type == 'locality,political' && !empty($component->long_name)) {
                $address_components['city'] = $component->long_name;
            } elseif ($type == 'administrative_area_level_2,political' && !empty($component->long_name)) {
                $address_components['city'] = $component->long_name;
            } elseif ($type == 'administrative_area_level_1,political') {
                $address_components['region_name'] = $component->long_name;
                $address_components['region_code'] = $component->short_name;
            } elseif ($type == 'country,political') {
                $address_components['country_name'] = $component->long_name;
                $address_components['country_code'] = $component->short_name;
            } elseif ($type == 'postal_code' && !empty($component->long_name)) {
                $address_components['postcode'] = $component->long_name;
            }
        }
        return $address_components;
    }

}


if (!function_exists('mvx_get_available_product_types')) {

    function mvx_get_available_product_types() {
        global $MVX;
        $available_product_types = array();
        $terms = get_terms('product_type');
        foreach ($terms as $term) {
            if ($term->name == 'simple' && $MVX->vendor_caps->vendor_can('simple')) {
                $available_product_types['simple'] = __('Simple product', 'dc-woocommerce-multi-vendor');
                if ($MVX->vendor_caps->vendor_can('virtual')) {
                    $available_product_types['virtual'] = __('Virtual product', 'dc-woocommerce-multi-vendor');
                }
                if ($MVX->vendor_caps->vendor_can('downloadable')) {
                    $available_product_types['downloadable'] = __('Downloadable product', 'dc-woocommerce-multi-vendor');
                }
            } elseif ($term->name == 'variable' && $MVX->vendor_caps->vendor_can('variable')) {
                $available_product_types['variable'] = __('Variable product', 'dc-woocommerce-multi-vendor');
            } elseif ($term->name == 'grouped' && $MVX->vendor_caps->vendor_can('grouped')) {
                $available_product_types['grouped'] = __('Grouped product', 'dc-woocommerce-multi-vendor');
            } elseif ($term->name == 'external' && $MVX->vendor_caps->vendor_can('external')) {
                $available_product_types['external'] = __('External/Affiliate product', 'dc-woocommerce-multi-vendor');
            } else {
                
            }
        }
        return apply_filters('mvx_get_available_product_types', $available_product_types, $terms);
    }

}

if (!function_exists('mvx_spmv_products_map')) {

    function mvx_spmv_products_map($data = array(), $action = 'insert') {
        global $wpdb;
        if ($data) {
            $table = $wpdb->prefix . 'mvx_products_map';
            if ($action == 'insert') {
                $wpdb->insert($table, $data);
                if (!isset($data['product_map_id'])) {
                    $inserted_id = $wpdb->insert_id;
                    $wpdb->update(esc_sql($table), array('product_map_id' => $inserted_id), array('product_id' => $data['product_id']));
                    return $inserted_id;
                } else {
                    return $data['product_map_id'];
                }
            } else {
                do_action('mvx_spmv_products_map_do_action', $action, $data);
                return false;
            }
        }
        return false;
    }

}

if (!function_exists('get_mvx_spmv_products_map_data')) {

    function get_mvx_spmv_products_map_data($map_id = '') {
        global $wpdb;
        $products_map_data = array();
        $results = $wpdb->get_results("SELECT product_map_id FROM {$wpdb->prefix}mvx_products_map");
        if ($results) {
            $product_map_ids = array_unique(wp_list_pluck($results, 'product_map_id'));
            foreach ($product_map_ids as $product_map_id) {
                $product_ids = $wpdb->get_results($wpdb->prepare("SELECT product_id FROM {$wpdb->prefix}mvx_products_map WHERE product_map_id=%d", $product_map_id));
                $products_map_data[$product_map_id] = wp_list_pluck($product_ids, 'product_id');
            }
        }
        if ($map_id) {
            return isset($products_map_data[$map_id]) ? $products_map_data[$map_id] : array();
        }
       
        return $products_map_data;
    }

}

if (!function_exists('do_mvx_spmv_set_object_terms')) {

    function do_mvx_spmv_set_object_terms($map_id = '') {
        global $MVX;
        if ($map_id) {
            $products_map_data_ids = get_mvx_spmv_products_map_data($map_id);
            $product_array_price = $top_rated_vendors = array();
            foreach ($products_map_data_ids as $product_id) {
                $product = wc_get_product($product_id);
                if ($product) {
                    $product_visibility_terms = get_the_terms($product->get_id(), 'product_visibility');
                    if ($product_visibility_terms) {
                        $term_taxonomy_ids = wp_list_pluck($product_visibility_terms, 'term_taxonomy_id');
                        $product_visibility_terms = wc_get_product_visibility_term_ids();
                        // Hide product_visibility_not_in products
                        if (in_array($product_visibility_terms['exclude-from-catalog'], $term_taxonomy_ids) || ('yes' === get_option('woocommerce_hide_out_of_stock_items') && in_array($product_visibility_terms['outofstock'], $term_taxonomy_ids)))
                            continue;

                        $product_array_price[$product->get_id()] = $product->get_price();
                        // top rated vendor
                        $product_vendor = get_mvx_product_vendors($product->get_id());
                        if ($product_vendor) {
                            $rating_val_array = mvx_get_vendor_review_info($product_vendor->term_id);
                            $rating = round($rating_val_array['avg_rating'], 1);
                            $top_rated_vendors[$product->get_id()] = $rating;
                        }
                    } else {
                        $product_array_price[$product->get_id()] = $product->get_price();
                        // top rated vendor
                        $product_vendor = get_mvx_product_vendors($product->get_id());
                        if ($product_vendor) {
                            $rating_val_array = mvx_get_vendor_review_info($product_vendor->term_id);
                            $rating = round($rating_val_array['avg_rating'], 1);
                            $top_rated_vendors[$product->get_id()] = $rating;
                        }
                    }
                }
            }
            $min_price_product = ($product_array_price) ? array_search(min($product_array_price), $product_array_price) : 0;
            $min_price_product = apply_filters('mvx_spmv_filtered_min_price_product', $min_price_product, $map_id);
            $max_price_product = ($product_array_price) ? array_search(max($product_array_price), $product_array_price) : 0;
            $max_price_product = apply_filters('mvx_spmv_filtered_max_price_product', $max_price_product, $map_id);
            $top_rated_vendor_product = ($top_rated_vendors) ? array_search(max($top_rated_vendors), $top_rated_vendors) : 0;
            $top_rated_vendor_product = apply_filters('mvx_spmv_filtered_top_rated_vendor_product', $top_rated_vendor_product, $map_id);
            $spmv_terms = $MVX->taxonomy->get_mvx_spmv_terms();
            if ($spmv_terms) {
                foreach ($spmv_terms as $term) {
                    if ($term->slug == 'min-price') {
                        $min_product_map_id = get_post_meta($min_price_product, '_mvx_spmv_map_id', true);
                        $object_ids = get_objects_in_term($term->term_id, $MVX->taxonomy->mvx_spmv_taxonomy);
                        if($min_product_map_id == $map_id){
                            foreach ($products_map_data_ids as $product_id) {
                                if ($min_price_product != $product_id && in_array($product_id, $object_ids)) {
                                    wp_remove_object_terms($product_id, $term->term_id, $MVX->taxonomy->mvx_spmv_taxonomy);
                                }
                            }
                        }
                        wp_set_object_terms($min_price_product, (int) $term->term_id, $MVX->taxonomy->mvx_spmv_taxonomy, true);
                    } elseif ($term->slug == 'max-price') {
                        $max_product_map_id = get_post_meta($max_price_product, '_mvx_spmv_map_id', true);
                        $object_ids = get_objects_in_term($term->term_id, $MVX->taxonomy->mvx_spmv_taxonomy);
                        if($max_product_map_id == $map_id){
                            foreach ($products_map_data_ids as $product_id) {
                                if ($max_price_product != $product_id && in_array($product_id, $object_ids)) {
                                    wp_remove_object_terms($product_id, $term->term_id, $MVX->taxonomy->mvx_spmv_taxonomy);
                                }
                            }
                        }
                        wp_set_object_terms($max_price_product, (int) $term->term_id, $MVX->taxonomy->mvx_spmv_taxonomy, true);
                    } elseif ($term->slug == 'top-rated-vendor') {
                        $top_rated_vendor_map_id = get_post_meta($top_rated_vendor_product, '_mvx_spmv_map_id', true);
                        $object_ids = get_objects_in_term($term->term_id, $MVX->taxonomy->mvx_spmv_taxonomy);
                        if($top_rated_vendor_map_id == $map_id){
                            foreach ($products_map_data_ids as $product_id) {
                                if ($top_rated_vendor_product != $product_id && in_array($product_id, $object_ids)) {
                                    wp_remove_object_terms($product_id, $term->term_id, $MVX->taxonomy->mvx_spmv_taxonomy);
                                }
                            }
                        }
                        wp_set_object_terms($top_rated_vendor_product, (int) $term->term_id, $MVX->taxonomy->mvx_spmv_taxonomy, true);
                    } else {
                        do_action('mvx_spmv_set_object_terms_handler', $term, $map_id, $products_map_data_ids);
                    }
                }
            }
        } else {
            $products_map_data = get_mvx_spmv_products_map_data();
            if ($products_map_data) {
                foreach ($products_map_data as $product_map_id => $product_ids) {
                    $product_array_price = $top_rated_vendors = array();
                    foreach ($product_ids as $product_id) {
                        $product = wc_get_product($product_id);
                        if ($product) {
                            $product_visibility_terms = get_the_terms($product->get_id(), 'product_visibility');
                            if ($product_visibility_terms) {
                                $term_taxonomy_ids = wp_list_pluck($product_visibility_terms, 'term_taxonomy_id');
                                $product_visibility_terms = wc_get_product_visibility_term_ids();
                                // Hide product_visibility_not_in products
                                if (in_array($product_visibility_terms['exclude-from-catalog'], $term_taxonomy_ids) || ('yes' === get_option('woocommerce_hide_out_of_stock_items') && in_array($product_visibility_terms['outofstock'], $term_taxonomy_ids)))
                                    continue;

                                $product_array_price[$product->get_id()] = $product->get_price();
                                // top rated vendor
                                $product_vendor = get_mvx_product_vendors($product->get_id());
                                if ($product_vendor) {
                                    $rating_val_array = mvx_get_vendor_review_info($product_vendor->term_id);
                                    $rating = round($rating_val_array['avg_rating'], 1);
                                    $top_rated_vendors[$product->get_id()] = $rating;
                                }
                            } else {
                                $product_array_price[$product->get_id()] = $product->get_price();
                                // top rated vendor
                                $product_vendor = get_mvx_product_vendors($product->get_id());
                                if ($product_vendor) {
                                    $rating_val_array = mvx_get_vendor_review_info($product_vendor->term_id);
                                    $rating = round($rating_val_array['avg_rating'], 1);
                                    $top_rated_vendors[$product->get_id()] = $rating;
                                }
                            }
                        }
                    }
                    $min_price_product = ($product_array_price) ? array_search(min($product_array_price), $product_array_price) : 0;
                    $min_price_product = apply_filters('mvx_spmv_filtered_min_price_product', $min_price_product, $product_map_id);
                    $max_price_product = ($product_array_price) ? array_search(max($product_array_price), $product_array_price) : 0;
                    $max_price_product = apply_filters('mvx_spmv_filtered_max_price_product', $max_price_product, $product_map_id);
                    $top_rated_vendor_product = ($top_rated_vendors) ? array_search(max($top_rated_vendors), $top_rated_vendors) : 0;
                    $top_rated_vendor_product = apply_filters('mvx_spmv_filtered_top_rated_vendor_product', $top_rated_vendor_product, $product_map_id);
                    $spmv_terms = $MVX->taxonomy->get_mvx_spmv_terms(array('orderby' => 'id'));
                    if ($spmv_terms) {
                        foreach ($spmv_terms as $term) {
                            if ($term->slug == 'min-price') {
                                $min_product_map_id = get_post_meta($min_price_product, '_mvx_spmv_map_id', true);
                                $object_ids = get_objects_in_term($term->term_id, $MVX->taxonomy->mvx_spmv_taxonomy);
                                if($min_product_map_id == $product_map_id){
                                    foreach ($product_ids as $product_id) {
                                        if ($min_price_product != $product_id && in_array($product_id, $object_ids)) {
                                            wp_remove_object_terms($product_id, $term->term_id, $MVX->taxonomy->mvx_spmv_taxonomy);
                                        }
                                    }
                                }
                                wp_set_object_terms($min_price_product, (int) $term->term_id, $MVX->taxonomy->mvx_spmv_taxonomy, true);
                            } elseif ($term->slug == 'max-price') {
                                $max_product_map_id = get_post_meta($max_price_product, '_mvx_spmv_map_id', true);
                                $object_ids = get_objects_in_term($term->term_id, $MVX->taxonomy->mvx_spmv_taxonomy);
                                if($max_product_map_id == $product_map_id){
                                    foreach ($product_ids as $product_id) {
                                        if ($max_price_product != $product_id && in_array($product_id, $object_ids)) {
                                            wp_remove_object_terms($product_id, $term->term_id, $MVX->taxonomy->mvx_spmv_taxonomy);
                                        }
                                    }
                                }
                                wp_set_object_terms($max_price_product, (int) $term->term_id, $MVX->taxonomy->mvx_spmv_taxonomy, true);
                            } elseif ($term->slug == 'top-rated-vendor') {
                                $top_rated_vendor_map_id = get_post_meta($top_rated_vendor_product, '_mvx_spmv_map_id', true);
                                $object_ids = get_objects_in_term($term->term_id, $MVX->taxonomy->taxonomy_name);
                                if($top_rated_vendor_map_id == $product_map_id){
                                    foreach ($product_ids as $product_id) {
                                        if ($top_rated_vendor_product != $product_id && in_array($product_id, $object_ids)) {
                                            wp_remove_object_terms($product_id, $term->term_id, $MVX->taxonomy->mvx_spmv_taxonomy);
                                        }
                                    }
                                }
                                wp_set_object_terms($top_rated_vendor_product, (int) $term->term_id, $MVX->taxonomy->mvx_spmv_taxonomy, true);
                            } else {
                                do_action('mvx_spmv_set_object_terms_handler', $term, $map_id, $products_map_data);
                            }
                        }
                    }
                }
            }
        }
    }

}

if (!function_exists('get_mvx_spmv_excluded_products_map_data')) {

    function get_mvx_spmv_excluded_products_map_data() {
        global $MVX, $wpdb;
        $spmv_terms = $MVX->taxonomy->get_mvx_spmv_terms(array('orderby' => 'id'));
        if ($spmv_terms) {
            $exclude_spmv_products = array();
            foreach ($spmv_terms as $term) {
                if ($term->slug == 'min-price') {
                    $object_ids = get_objects_in_term($term->term_id, $MVX->taxonomy->mvx_spmv_taxonomy);
                    if ($object_ids) {
                        foreach ($object_ids as $product_id) {
                            if ($product_id && get_post_status($product_id) == 'publish') {
                                $product_map_id = get_post_meta($product_id, '_mvx_spmv_map_id', true);
                                if ($product_map_id) {
                                    $products_map_data_ids = get_mvx_spmv_products_map_data($product_map_id);
                                    $excludes = array_diff($products_map_data_ids, array($product_id));
                                    if ($excludes) {
                                        foreach ($excludes as $id) {
                                            $exclude_spmv_products[$term->slug][] = $id;
                                        }
                                    }
                                }
                            }
                        }
                    }
                } elseif ($term->slug == 'max-price') {
                    $object_ids = get_objects_in_term($term->term_id, $MVX->taxonomy->mvx_spmv_taxonomy);
                    if ($object_ids) {
                        foreach ($object_ids as $product_id) {
                            if ($product_id && get_post_status($product_id) == 'publish') {
                                $product_map_id = get_post_meta($product_id, '_mvx_spmv_map_id', true);
                                if ($product_map_id) {
                                    $products_map_data_ids = get_mvx_spmv_products_map_data($product_map_id);
                                    $excludes = array_diff($products_map_data_ids, array($product_id));

                                    if ($excludes) {
                                        foreach ($excludes as $id) {
                                            $exclude_spmv_products[$term->slug][] = $id;
                                        }
                                    }
                                }
                            }
                        }
                    }
                } elseif ($term->slug == 'top-rated-vendor') {
                    $object_ids = get_objects_in_term($term->term_id, $MVX->taxonomy->mvx_spmv_taxonomy);
                    if ($object_ids) {
                        foreach ($object_ids as $product_id) {
                            if ($product_id && get_post_status($product_id) == 'publish') {
                                $product_map_id = get_post_meta($product_id, '_mvx_spmv_map_id', true);
                                if ($product_map_id) {
                                    $products_map_data_ids = get_mvx_spmv_products_map_data($product_map_id);
                                    $excludes = array_diff($products_map_data_ids, array($product_id));

                                    if ($excludes) {
                                        foreach ($excludes as $id) {
                                            $exclude_spmv_products[$term->slug][] = $id;
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $object_ids = get_objects_in_term($term->term_id, $MVX->taxonomy->mvx_spmv_taxonomy);
                    do_action('mvx_spmv_term_exclude_products_handler', $exclude_spmv_products, $object_ids, $term);
                }
            }

            return apply_filters('mvx_spmv_term_exclude_products_data', $exclude_spmv_products, $spmv_terms);
        }
        return false;
    }

}

if (!function_exists('mvx_get_available_commission_types')) {

    function mvx_get_available_commission_types($default = array()) {
        global $MVX;
        $available_commission_types = array();
        if ($default)
            $available_commission_types = $default;
        $available_commission_types['fixed'] = __('Fixed Amount', 'dc-woocommerce-multi-vendor');
        $available_commission_types['percent'] = __('Percentage', 'dc-woocommerce-multi-vendor');
        $available_commission_types['fixed_with_percentage'] = __('%age + Fixed (per transaction)', 'dc-woocommerce-multi-vendor');
        $available_commission_types['fixed_with_percentage_qty'] = __('%age + Fixed (per unit)', 'dc-woocommerce-multi-vendor');
        $available_commission_types['commission_by_product_price'] = __('Commission By Product Price', 'dc-woocommerce-multi-vendor');
        $available_commission_types['commission_by_purchase_quantity'] = __('Commission By Purchase Quantity', 'dc-woocommerce-multi-vendor');
        $available_commission_types['fixed_with_percentage_per_vendor'] = __('%age + Fixed (per vendor)', 'dc-woocommerce-multi-vendor');
        
        return apply_filters('mvx_get_available_commission_types', $available_commission_types);
    }

}

if (!function_exists('mvx_list_categories')) {

    function mvx_list_categories($args = array()) {
        global $wp_version;
        $defaults = array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => true,
            'exclude' => array(),
            'exclude_tree' => array(),
            'include' => array(),
            'number' => '',
            'fields' => 'all',
            'slug' => '',
            'parent' => 0,
            'hierarchical' => true,
            'child_of' => 0,
            'childless' => false,
            'get' => '',
            'name__like' => '',
            'description__like' => '',
            'pad_counts' => false,
            'offset' => '',
            'search' => '',
            'show_count' => false,
            'taxonomy' => 'product_cat',
            'show_option_none' => __('No categories', 'dc-woocommerce-multi-vendor'),
            'style' => 'list',
            'selected' => '',
            'list_class' => '',
            'cat_link' => false,
            'cache_domain' => 'core',
            'html_list' => false,
            'echo' => false
        );

        $r = apply_filters( 'before_mvx_list_categories_query_args', wp_parse_args($args, $defaults), $args );

        $taxonomy = $r['taxonomy'];

        if (!taxonomy_exists($taxonomy)) {
            return false;
        }

        if (version_compare($wp_version, '4.5.0', '>=')) {
            // Since 4.5.0, taxonomies should be passed via the ‘taxonomy’ argument in the $args array
            $categories = get_terms($r);
        } else {
            // Prior to 4.5.0, the first parameter of get_terms() was a taxonomy or list of taxonomies
            $categories = get_terms($taxonomy, $r);
        }

        if (is_wp_error($categories)) {
            $categories = array();
        } else {
            $categories = (array) $categories;
            foreach (array_keys($categories) as $k) {
                _make_cat_compat($categories[$k]);
            }
        }
        // for No html output
        if (!$r['html_list'])
            return $categories;

        $output = '';
        $list_class = apply_filters('mvx_list_categories_list_style_classes', $r['list_class']);
        if (empty($categories)) {
            if (!empty($r['show_option_none'])) {
                if ('list' == $r['style']) {
                    $output .= '<li class="' . $list_class . ' cat-item-none">' . $r['show_option_none'] . '</li>';
                } else {
                    $output .= $r['show_option_none'];
                }
            }
        } else {
            foreach ($categories as $key => $cat) {
                $list_class = empty($r['list_class']) ? 'cat-item cat-item-' . $cat->term_id : $r['list_class'] . ' cat-item cat-item-' . $cat->term_id;
                $child_terms = get_term_children($cat->term_id, $taxonomy);
                // show count
                $inner_html = '';
                if ($r['show_count'] || $child_terms) {
                    $inner_html .= ' <span class="pull-right">';
                    if ($r['show_count']) {
                        $inner_html .= '<span class="count ' . apply_filters('mvx_list_categories_show_count_style_classess', 'badge badge-primary badge-pill ', $cat) . '">' . $cat->count . '</span>';
                    }

                    if ($child_terms) {
                        $list_class .= ' has-children';
                        //$inner_html .= ' <i class="mvx-font ico-right-arrow-icon"></i>';
                    }
                    $inner_html .= '</span>';
                }
                // has selected term
                if(!empty($r['selected']) && $cat->term_id == $r['selected'] ) $list_class .= ' active';
                $list_class = apply_filters('mvx_list_categories_list_style_classes', $list_class, $cat);
                $link = apply_filters('mvx_list_categories_get_term_link', ($r['cat_link']) ? $r['cat_link'] : get_term_link($cat->term_id, $taxonomy), $cat, $r);
                if ('list' == $r['style']) {
                    //<li><a href="#"><span>Grocery & Gourmet Foods</span></a></li>
                    $output .= "<li class='$list_class' data-term-id='$cat->term_id' data-taxonomy='$taxonomy'><a href='$link'><span>" . apply_filters('mvx_list_categories_term_name', $cat->name, $cat) . "</span></a>$inner_html</li>";
                } else {
                    $output .= "<a class='$list_class' href='$link' data-term-id='$cat->term_id' data-taxonomy='$taxonomy'>" . apply_filters('mvx_list_categories_term_name', $cat->name, $cat) . "$inner_html</a>";
                }
            }
        }

        /**
         * Filters the HTML output of a taxonomy list.
         *
         * @since 3.2.0
         *
         * @param string $output HTML output.
         * @param array  $r   An array of taxonomy-listing arguments.
         */
        $html = apply_filters('mvx_list_categories', $output, $r);

        if ($r['echo']) {
            echo $html;
        } else {
            return $html;
        }
    }

}

if (!function_exists('mvx_get_shipping_zone')) {

    function mvx_get_shipping_zone($zoneID = '') {
        global $MVX;
        $zones = array();
        if( !class_exists( 'MVX_Shipping_Zone' ) ) {
            $MVX->load_vendor_shipping();
        }
        if ( isset($zoneID) && $zoneID != '' ) {
                $zones = MVX_Shipping_Zone::get_zone($zoneID);
        } else {
                $zones = MVX_Shipping_Zone::get_zones();
        }
        return $zones;
    }

}

if (!function_exists('mvx_get_shipping_methods')) {

    function mvx_get_shipping_methods() {
        $vendor_shippings = array();
        foreach ( WC()->shipping->load_shipping_methods() as $method ) {
            if ( ! array_key_exists( $method->id, apply_filters( 'mvx_vendor_shipping_methods', array(
                'flat_rate' => __('Flat Rate', 'dc-woocommerce-multi-vendor'),
                'local_pickup' => __('Local Pickup', 'dc-woocommerce-multi-vendor'),
                'free_shipping' => __('Free Shipping', 'dc-woocommerce-multi-vendor')
            ) ) ) ) {
                    continue;
            }
            $vendor_shippings[$method->id] = $method;
        }
        return $vendor_shippings;
    }

}

if (!function_exists('mvx_convert_to_array')) {

    function mvx_convert_to_array($a) {
        return (array) $a;
    }

}

if (!function_exists('mvx_state_key_alter')) {

    function mvx_state_key_alter(&$value, $key) {
        $value = array_combine(
                array_map(function($k) use ($key) {
                    return $key . ':' . $k;
                }, array_keys($value)), $value
        );
    }

}

if (!function_exists('get_vendor_shipping_classes')) {

    function get_vendor_shipping_classes() {
        $vendor_user_id = apply_filters('mvx_dashboard_shipping_vendor', get_current_vendor_id());

        $shipping_classes = array();

        if ($vendor_user_id) {
            $shipping_class_id = get_user_meta($vendor_user_id, 'shipping_class_id', true);
            if ($shipping_class_id) {
                $shipping_classes[] = get_term($shipping_class_id);
            }
        }

        return $shipping_classes;
    }

}

//------------------ Afm core module--------------------//

if ( ! function_exists( 'current_vendor_can' ) ) {

    /**
     * Check if vendor has a certain capability 
     * @param string | ARRAY_N $capability 
     * @return boolean TRUE only if all passed capabilities are true for current vendor
     */
    function current_vendor_can( $capability ) {
        $current_vendor_id = '';
        if(is_user_mvx_vendor(get_current_user_id())) $current_vendor_id = get_current_user_id();
        
        if ( ! $current_vendor_id || empty( $capability ) ) {
            return false;
        }
        $vendor_role = get_role( 'dc_vendor' );
        $capabilities = isset( $vendor_role->capabilities ) ? $vendor_role->capabilities : array();

        if ( is_array( $capability ) ) {
            foreach ( $capability as $cap ) {
                if ( ! array_key_exists( $cap, $capabilities ) || ! $capabilities[$cap] ) {
                    return false;
                }
            }
            return true;
        }
        return array_key_exists( $capability, $capabilities ) && $capabilities[$capability];
    }

}

if ( ! function_exists( 'mvx_is_allowed_vendor_shipping' ) ) {

    function mvx_is_allowed_vendor_shipping() {
        global $MVX;
        if ( version_compare( $MVX->version, '3.1.5', '<' ) && mvx_is_module_active('vendor-shipping') != true ) {
            // new vendor shipping setting value based on payment shipping settings
            if ( 'Enable' === get_mvx_vendor_settings( 'give_shipping', 'payment' ) ) {
                update_mvx_vendor_settings( 'is_vendor_shipping_on', 'Enable', 'general' );
            }
        }
        return true === mvx_is_module_active('vendor-shipping');
    }

}

if ( ! function_exists( 'mvx_get_post_permalink_html' ) ) {

    function mvx_get_post_permalink_html( $id ) {
        if ( ! $id )
            return '';
        $post = get_post( $id );
        if ( ! $post )
            return '';

        list($permalink, $post_name) = mvx_get_post_permalink( $post->ID );

        $view_link = false;
        $preview_target = '';

        if ( current_user_can( 'read_post', $post->ID ) ) {
            if ( 'draft' === $post->post_status || empty( $post->post_name ) ) {
                $view_link = get_preview_post_link( $post );
                $preview_target = " target='wp-preview-{$post->ID}'";
            } else {
                if ( 'publish' === $post->post_status || 'attachment' === $post->post_type ) {
                    $view_link = get_permalink( $post );
                } else {
                    // Allow non-published (private, future) to be viewed at a pretty permalink, in case $post->post_name is set
                    $view_link = str_replace( array( '%pagename%', '%postname%' ), $post->post_name, $permalink );
                }
            }
        }

        if ( mb_strlen( $post_name ) > 34 ) {
            $post_name_abridged = mb_substr( $post_name, 0, 16 ) . '&hellip;' . mb_substr( $post_name, -16 );
        } else {
            $post_name_abridged = $post_name;
        }
        $post_type = get_post_type( $post );
        $post_name_html = '<span id="afm-' . $post_type . '-name">' . esc_html( $post_name_abridged ) . '</span>';
        $display_link = str_replace( array( '%pagename%', '%postname%' ), $post_name_html, esc_html( urldecode( $permalink ) ) );

        $return = '';
        if ( $post_type === 'shop_coupon' ) {
            $type = __('coupon', 'dc-woocommerce-multi-vendor');
        } else {
            $type = __('product', 'dc-woocommerce-multi-vendor');
        }
        if ( false === strpos( $view_link, 'preview=true' ) ) {
            $return .= '<label>' . __( sprintf( __('View %s', 'dc-woocommerce-multi-vendor'), $type ) ) . ":</label>\n";
        } else {
            $return .= '<label>' . __( sprintf( __('View %s', 'dc-woocommerce-multi-vendor'), $type ) ) . ":</label>\n";
        }
        $return .= '<span id="afm-' . $post_type . '-permalink"><a href="' . esc_url( $view_link ) . '"' . $preview_target . '>' . $display_link . "</a></span>";

        return $return;
    }

}

if ( ! function_exists( 'mvx_get_post_permalink' ) ) {

    function mvx_get_post_permalink( $id ) {
        $post = get_post( $id );
        if ( ! $post )
            return array( '', '' );

        $original_status = $post->post_status;
        $original_date = $post->post_date;
        $original_name = $post->post_name;

        // Hack: get_permalink() would return ugly permalink for drafts, so we will fake that our post is published.
        if ( in_array( $post->post_status, array( 'draft', 'pending', 'future' ) ) ) {
            $post->post_status = 'publish';
            $post->post_name = sanitize_title( $post->post_name ? $post->post_name : $post->post_title, $post->ID );
        }

        $post->post_name = wp_unique_post_slug( $post->post_name, $post->ID, $post->post_status, $post->post_type, $post->post_parent );

        $post->filter = 'sample';

        $permalink = get_permalink( $post, true );

        // Replace custom post_type Token with generic pagename token for ease of use.
        $permalink = str_replace( "%$post->post_type%", '%pagename%', $permalink );
        $permalink = array( $permalink, $post->post_name );

        $post->post_status = $original_status;
        $post->post_date = $original_date;
        $post->post_name = $original_name;
        unset( $post->filter );
        return $permalink;
    }

}

if ( ! function_exists( 'mvx_default_product_types' ) ) {

    function mvx_default_product_types() {
        return array(
            'simple'   => __( 'Simple product', 'dc-woocommerce-multi-vendor' ),
        );
    }

}

if ( ! function_exists( 'mvx_get_product_types' ) ) {

    function mvx_get_product_types() {
        return apply_filters( 'mvx_product_type_selector', mvx_default_product_types() );
    }
}

if ( ! function_exists( 'mvx_is_allowed_product_type' ) ) {
    /*
     * @params MIXED
     * string or array 
     * 
     */

    function mvx_is_allowed_product_type() {
        $product_types = mvx_get_product_types();
        foreach ( func_get_args() as $arg ) {
            //typecast normal string params to array
            $a_arg = (array) $arg;
            foreach ( $a_arg as $key ) {
                if ( apply_filters( 'mvx_is_allowed_product_type_check', array_key_exists( $key, $product_types ), $key, $product_types ) ) {
                    return true;
                }
            }
        }
        return false;
    }

}

if ( ! function_exists( 'get_current_vendor_shipping_classes' ) ) {

    function get_current_vendor_shipping_classes() {
        $current_vendor_id = get_current_user_id();
        $shipping_options = array();
        if ( $current_vendor_id ) {
            $shipping_classes = get_terms( 'product_shipping_class', array( 'hide_empty' => 0 ) );
            foreach ( $shipping_classes as $shipping_class ) {
                if ( apply_filters( 'mvx_allowed_only_vendor_shipping_class', true ) ) {
                    $vendor_id = absint( get_term_meta( $shipping_class->term_id, 'vendor_id', true ) );
                    if ( $vendor_id === $current_vendor_id ) {
                        $shipping_options[$shipping_class->term_id] = $shipping_class->name;
                    }
                } else {
                    $shipping_options[$shipping_class->term_id] = $shipping_class->name;
                }
            }
        }
        return apply_filters( 'current_vendor_shipping_classes', $shipping_options );
    }

}

if ( ! function_exists( 'mvx_get_product_terms_HTML' ) ) {

    function mvx_get_product_terms_HTML( $taxonomy, $id = null, $add_cap = false, $hierarchical = true ) {
        $terms = array();
        $product_terms = get_terms( apply_filters( "mvx_get_product_terms_{$taxonomy}_query_args", array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'orderby'    => 'name',
            'parent'     => 0,
            'fields'     => 'id=>name',
            ) ) );
        if ( ( empty( $product_terms ) || is_wp_error( $product_terms ) ) && ! $add_cap ) {
            return false;
        }
        $term_id_list = wp_get_post_terms( $id, $taxonomy, array( 'fields' => 'ids' ) );
        if ( ! empty( $term_id_list ) && ! is_wp_error( $term_id_list ) ) {
            $terms = $term_id_list;
        } else {
            $terms = array();
        }
        $terms = isset( $_POST['tax_input'][$taxonomy] ) ? wp_parse_id_list( $_POST['tax_input'][$taxonomy] ) : $terms;
        $terms = apply_filters( 'mvx_get_product_terms_html_selected_terms', $terms, $taxonomy, $id );
        if ( $hierarchical ) {
            return generate_hierarchical_taxonomy_html( $taxonomy, $product_terms, $terms, $add_cap );
        } else {
            return generate_non_hierarchical_taxonomy_html( $taxonomy, $product_terms, $terms, $add_cap );
        }
    }

}
if ( ! function_exists( 'generate_non_hierarchical_taxonomy_html' ) ) {

    function generate_non_hierarchical_taxonomy_html( $taxonomy, $product_terms, $seleted_terms, $add_cap ) {
        $html = '';
        if ( ! empty( $product_terms ) || $add_cap ) {
            ob_start();
            ?>
            <select multiple = "multiple" data-placeholder = "<?php esc_attr_e( 'Select', 'dc-woocommerce-multi-vendor' ); ?>" class = "multiselect form-control <?php echo $taxonomy; ?>" name = "tax_input[<?php echo $taxonomy; ?>][]">
                <?php
                foreach ( $product_terms as $term_id => $term_name ) {
                    echo '<option value="' . $term_id . '" ' . selected( in_array( $term_id, $seleted_terms ), true, false ) . '>' . $term_name . '</option>';
                }
                ?>
            </select>
            <?php
            $html = ob_get_clean();
        }
        return $html;
    }

}

if ( ! function_exists( 'generate_hierarchical_taxonomy_html' ) ) {

    function generate_hierarchical_taxonomy_html( $taxonomy, $terms, $post_terms, $add_cap, $level = 0, $max_depth = 2 ) {
        $max_depth = apply_filters( 'mvx_generate_hierarchical_taxonomy_html_max_depth', 5, $taxonomy, $terms, $post_terms, $level );
        $tax_html_class = ($level == 0) ? 'taxonomy-widget ' . $taxonomy . ' level-' . $level : '';
        $tax_html = '<ul class="'.$tax_html_class.'">';
        foreach ( $terms as $term_id => $term_name ) {
            $child_html = '';
            if ( $max_depth > $level ) {
                $child_terms = get_terms( array(
                    'taxonomy'   => $taxonomy,
                    'hide_empty' => false,
                    'orderby'    => 'name',
                    'parent'     => absint( $term_id ),
                    'fields'     => 'id=>name',
                    ) );
                if ( ! empty( $child_terms ) && ! is_wp_error( $child_terms ) ) {
                    $child_html = generate_hierarchical_taxonomy_html( $taxonomy, $child_terms, $post_terms, $add_cap, $level + 1 );
                }
            }

            $tax_html .= '<li><label><input type="checkbox" name="tax_input[' . $taxonomy . '][]" value="' . $term_id . '" ' . checked( in_array( $term_id, $post_terms ), true, false ) . '> ' . $term_name . $child_html . '</label></li>';
        }
        $tax_html .= '</ul>';
        if ( $add_cap ) {
            $label = '';
            switch ( $taxonomy ) {
                case 'product_cat':
                    $label = __( 'Add new product category', 'dc-woocommerce-multi-vendor' );
                    break;
                default:
                    $label = __( 'Add new item', 'dc-woocommerce-multi-vendor' );
            }
            $tax_html .= '<a href="#">' . $label . '</a>';
        }
        return $tax_html;
    }

}


if ( ! function_exists( 'mvx_generate_term_breadcrumb_html' ) ) {

    function mvx_generate_term_breadcrumb_html( $args = array() ) {
        $args = wp_parse_args( $args, apply_filters( 'mvx_term_breadcrumb_defaults', array(
                'term_id'               => 0,
                'term_list'             => array(),
                'taxonomy'              => 'product_cat',
                'delimiter'             => '&nbsp;&#47;&nbsp;',
                'wrap_before'           => '<ul class="mvx-breadcrumb breadcrumb">',
                'wrap_after'            => '</ul>',
                'wrap_child_before'     => '<li>',
                'wrap_child_after'      => '</li>',
                'link'                  => false,
                'before'                => '',
                'after'                 => '',
                'echo'                  => false,
        ) ) );
        if(!$args['term_list']){
            $hierarchy = get_ancestors( $args['term_id'], $args['taxonomy'] );
            $hierarchy = array_reverse($hierarchy);
            $hierarchy[] = $args['term_id'];
        }else{
            $hierarchy = $args['term_list'];
        }
        $breadcrumbs = array();
        foreach ( $hierarchy as $id ) {
            $term = get_term( $id, $args['taxonomy'] );
            $breadcrumbs[]= array( $term->name, apply_filters( 'mvx_generate_term_breadcrumb_crumb_link', $args['link'], $term, $args) );
        }
        
        $html = '';
        if ( ! empty( $breadcrumbs ) ) {
            $html .= $args['wrap_before'];
            foreach ( $breadcrumbs as $key => $crumb ) {
                $html .= $args['wrap_child_before'];
                $html .= $args['before'];
                if ( ! empty( $crumb[1] ) && sizeof( $breadcrumbs ) !== $key + 1 ) {
                    $html .= '<a href="' . esc_url( $crumb[1] ) . '">' . esc_html( $crumb[0] ) . '</a>';
                } else {
                    $html .= esc_html( $crumb[0] );
                }
                $html .= $args['after'];
                if ( sizeof( $breadcrumbs ) !== $key + 1 ) {
                    $html .= $args['delimiter'];
                }
                $html .= $args['wrap_child_after'];
            }
            $html .= $args['wrap_after'];
        }
        
        $html = apply_filters( 'mvx_generate_term_breadcrumb_html', $html, $args );
        
        if($args['echo']){
            echo $html;
        }else{
            return $html;
        }
    }
}

if ( ! function_exists( 'is_product_mvx_spmv' ) ) {

    function is_product_mvx_spmv( $product_id = '' ) {
        $is_mvx_spmv_product = false;
        if($product_id){
            $is_mvx_spmv_product = (get_post_meta(absint($product_id), '_mvx_spmv_product', true)) ? true : false;
        }
        return apply_filters( 'mvx_is_product_in_spmv', $is_mvx_spmv_product, $product_id );
    }
}

if ( ! function_exists( 'get_mvx_different_terms_hierarchy' ) ) {

    function get_mvx_different_terms_hierarchy( $term_list = array(), $taxonomy = 'product_cat' ) {
        $terms_hierarchy_arr = array();
        if($term_list) {
            $flag = 0;
            foreach ($term_list as $term_id) {
                $hierarchy = get_ancestors( $term_id, $taxonomy );
                if($hierarchy){
                    if(!array_key_exists(end($hierarchy), $terms_hierarchy_arr)){
                        $terms_hierarchy_arr[end($hierarchy)] = $term_id;
                    }elseif(array_key_exists(end($hierarchy), $terms_hierarchy_arr) && $terms_hierarchy_arr[end($hierarchy)] < $term_id){ // if terms has same parent
                        $terms_hierarchy_arr[end($hierarchy)] = $term_id;
                    }
                }elseif(!array_key_exists($term_id, $terms_hierarchy_arr)){
                    $terms_hierarchy_arr[$flag] = $term_id;
                }
                $flag++;
            }
            // check same hierarchy duplication
            foreach ($terms_hierarchy_arr as $term_id) {
                if(array_key_exists($term_id, $terms_hierarchy_arr)){
                    $key = array_search($term_id, $terms_hierarchy_arr);
                    unset($terms_hierarchy_arr[$key]);
                }
            }
            return $terms_hierarchy_arr;
        }
        return false;
    }
}

if ( ! function_exists( 'is_current_vendor_coupon' ) ) {

    function is_current_vendor_coupon( $coupon_id = 0, $vendor_id = 0 ) {
        global $MVX;
        if ( ! $vendor_id ) {
            $vendor_id = get_current_user_id();
        }

        if ( ! ( $coupon_id && $vendor_id && is_user_mvx_vendor( $vendor_id ) ) ) {
            return false;
        }

        $coupon = new WC_Coupon( $coupon_id );
        $coupon_post = get_post( $coupon_id );
        $coupon_author_id = absint( $coupon_post->post_author );

        if ( ! $coupon || $vendor_id !== $coupon_author_id ) {
            return false;
        }
        //the coupon is valid and belongs to the current vendor
        return true;
    }

}

if ( ! function_exists( 'has_vendor_config_shipping_methods' ) ) {

    function has_vendor_config_shipping_methods() {
        $vendor_shipping_zones = mvx_get_shipping_zone();
        $flag = array();
        if( $vendor_shipping_zones ) :
            foreach ( $vendor_shipping_zones as $zone ) {
                $vendor_shipping_methods = $zone['shipping_methods'];
                if( !empty( $vendor_shipping_methods ) ) {
                    $flag[] = 'true';
                }
            }
        endif;
        return (in_array( 'true', $flag) ) ? true : false;
    }

}

if ( ! function_exists( 'mvx_get_price_to_display' ) ) {
    /**
     * Returns the price including or excluding tax, based on the 'woocommerce_tax_display_shop' setting.
     *
     * @since  3.3.1
     * @param  WC_Product $product WC_Product object.
     * @param  array      $args Optional arguments to pass product quantity and price.
     * @return float
     */
    function mvx_get_price_to_display( $product, $args = array() ) {
        $args = wp_parse_args(
                $args, array(
                        'qty'   => 1,
                        'price' => $product->get_price(),
                )
        );

        $price = $args['price'];
        $qty   = $args['qty'];
        $price_html = '';
        if ( 'incl' === get_option( 'woocommerce_tax_display_shop' ) && $product->is_taxable() && wc_prices_include_tax() ) {
            $price_html = wc_price( $price * $qty );
        }else{
            $price_html = $product->get_price_html();
        }
        return apply_filters( 'mvx_get_price_to_display', $price_html, $product, $args );
    }
}

if (!function_exists('is_mvx_version_less_3_4_0')) {

    /**
     * Check Multivendor X current version is less than 3.4.0
     *
     * @return boolean true/false
     */
    function is_mvx_version_less_3_4_0() {
        $current_mvx = get_option('dc_product_vendor_plugin_db_version');
        return version_compare( $current_mvx, '3.4.0', '<' );
    }
}

if (!function_exists('mvx_get_commission_statuses')) {
    /**
     * Get all commission statuses.
     *
     * @since 3.4.0
     * @return array
     */
    function mvx_get_commission_statuses() {
        $commission_statuses = array(
            'paid'              => __( 'Paid', 'dc-woocommerce-multi-vendor' ),
            'unpaid'            => __( 'Unpaid', 'dc-woocommerce-multi-vendor' ),
            'refunded'          => __( 'Refunded', 'dc-woocommerce-multi-vendor' ),
            'partial_refunded'  => __( 'Partial refunded', 'dc-woocommerce-multi-vendor' ),
            'reverse'           => __( 'Reverse', 'dc-woocommerce-multi-vendor' ),
        );
        return apply_filters( 'mvx_get_commission_statuses', $commission_statuses );
    }
}

if (!function_exists('mvx_get_product_link')) {
    /**
     * Get product link.
     *
     * @since 3.4.0
     * @param integer $product_id
     * @return string url
     */
    function mvx_get_product_link( $product_id ) {
        $link = '';
        if ( current_user_can('edit_published_products') && get_mvx_vendor_settings('is_edit_delete_published_product', 'capabilities', 'product') == 'Enable' ) {
            $link = esc_url(mvx_get_vendor_dashboard_endpoint_url(get_mvx_vendor_settings('mvx_edit_product_endpoint', 'vendor', 'general', 'edit-product'), $product_id));
        }
        return apply_filters('mvx_get_product_link', $link, $product_id);
    }
}

if (!function_exists('get_mvx_default_payment_gateways')) {
    /**
     * Get default payment gateways.
     *
     * @since 3.4.0
     * @return array payment gateways
     */
    function get_mvx_default_payment_gateways() {
        $default_gateways = apply_filters('automatic_payment_method', 
        array(
            'paypal_masspay' => __('PayPal Masspay', 'dc-woocommerce-multi-vendor'), 
            'paypal_payout' => __('Paypal Payout', 'dc-woocommerce-multi-vendor'), 
            'stripe_masspay' => __('Stripe Connect', 'dc-woocommerce-multi-vendor'), 
            'direct_bank' => __('Direct Bank Transfer', 'dc-woocommerce-multi-vendor'),
            )
        );
        return apply_filters( 'mvx_default_payment_gateways', $default_gateways );
    }
}

if (!function_exists('get_mvx_available_payment_gateways')) {
    /**
     * Get available payment gateways.
     *
     * @since 3.4.0
     * @return array payment gateways
     */
    function get_mvx_available_payment_gateways() {
        $available_gateways = array();
        $payment_admin_settings = get_option('mvx_payment_settings_name');
        $default_gateways = get_mvx_default_payment_gateways();
        foreach ($default_gateways as $key => $lable) {
            $gateway_settings_key = 'payment_method_' . $key;
            if (isset($payment_admin_settings[$gateway_settings_key]) && $payment_admin_settings[$gateway_settings_key] = 'Enable') {
                $available_gateways[$key] = $lable;
            }
        }
        return apply_filters( 'mvx_available_payment_gateways', $available_gateways );
    }
}

if (!function_exists('is_mvx_vendor_completed_store_setup')) {
    /**
     * Check vendor store setup.
     *
     * @since 3.4.0
     * @param object $user 
     * @return boolean 
     */
    function is_mvx_vendor_completed_store_setup( $user ) {
        if( $user ){
            $is_completed = get_user_meta( $user->ID, '_vendor_is_completed_setup_wizard', true );
            $is_skipped = get_user_meta( $user->ID, '_vendor_skipped_setup_wizard', true );
            $store_name = get_user_meta( $user->ID, '_vendor_page_title', true );
            $country = get_user_meta( $user->ID, '_vendor_country', true );
            $payment_mode = get_user_meta( $user->ID, '_vendor_payment_mode', true );
            if( $is_skipped ) return true;
            if( $store_name && $country && $payment_mode ) return true;
            if( $is_completed || !apply_filters('mvx_vendor_store_setup_wizard_enabled', true) ) return true;
        }
        return false;
    }
}

if (!function_exists('get_mvx_ledger_types')) {
    /**
     * Get available ledger types.
     *
     * @return array types 
     */
    function get_mvx_ledger_types() {
        return apply_filters( 'mvx_ledger_types', array(
            'commission'    => __( 'Commission', 'dc-woocommerce-multi-vendor' ),
            'refund'        => __( 'Refund', 'dc-woocommerce-multi-vendor' ),
            'withdrawal'    => __( 'Withdrawal', 'dc-woocommerce-multi-vendor' ),
        ) );
    }
}

if (!function_exists('get_mvx_more_spmv_products')) {
    /**
     * Get available SPMV products.
     *
     * @param int $product_id 
     * @return array $products 
     */
    function get_mvx_more_spmv_products( $product_id = 0 ) {
        if( !$product_id ) return array();
        $more_products = array();
        $has_product_map_id = get_post_meta( $product_id, '_mvx_spmv_map_id', true );
        if( $has_product_map_id ){
            $products_map_data_ids = get_mvx_spmv_products_map_data( $has_product_map_id );
            $mapped_products = array_diff( $products_map_data_ids, array( $product_id ) );
            if( $mapped_products && count( $mapped_products ) >= 1 ){
                $i = 0;
                foreach ( $mapped_products as $p_id ) {
                    $p_author = absint( get_post_field( 'post_author', $p_id ) );
                    $p_obj = wc_get_product( $p_id );
                    if( $p_obj ){
                        if ( !$p_obj->is_visible() || get_post_status ( $p_id ) != 'publish' ) continue;
                        if ( is_user_mvx_pending_vendor( $p_author ) || is_user_mvx_rejected_vendor( $p_author ) && absint( get_post_field( 'post_author', $product_id ) ) == $p_author ) continue;
                        $product_vendor = get_mvx_product_vendors( $p_id );
                        if ( $product_vendor ){
                            $more_products[$i]['seller_name'] = $product_vendor->page_title;
                            $more_products[$i]['is_vendor'] = 1;
                            $more_products[$i]['shop_link'] = $product_vendor->permalink;
                            $more_products[$i]['rating_data'] = mvx_get_vendor_review_info( $product_vendor->term_id );
                        } else {
                            $user_info = get_userdata($p_author);
                            $more_products[$i]['seller_name'] = isset( $user_info->data->display_name ) ? $user_info->data->display_name : '';
                            $more_products[$i]['is_vendor'] = 0;
                            $more_products[$i]['shop_link'] = get_permalink(wc_get_page_id('shop'));
                            $more_products[$i]['rating_data'] = 'admin';
                        }
                        $currency_symbol = get_woocommerce_currency_symbol();
                        $regular_price_val = $p_obj->get_regular_price();
                        $sale_price_val = $p_obj->get_sale_price();
                        $price_val = $p_obj->get_price();
                        $more_products[$i]['product_name'] = $p_obj->get_title();
                        $more_products[$i]['regular_price_val'] = $regular_price_val;
                        $more_products[$i]['sale_price_val'] = $sale_price_val;
                        $more_products[$i]['price_val'] = $price_val;
                        $more_products[$i]['product_id'] = $p_obj->get_id();
                        $more_products[$i]['product_type'] = $p_obj->get_type();
                        if ($p_obj->get_type() == 'variable') {
                            $more_products[$i]['_min_variation_price'] = get_post_meta( $p_obj->get_id(), '_min_variation_price', true );
                            $more_products[$i]['_max_variation_price'] = get_post_meta( $p_obj->get_id(), '_max_variation_price', true );
                            $variable_min_sale_price = get_post_meta( $p_obj->get_id(), '_min_variation_sale_price', true );
                            $variable_max_sale_price = get_post_meta( $p_obj->get_id(), '_max_variation_sale_price', true );
                            $more_products[$i]['_min_variation_sale_price'] = $variable_min_sale_price ? $variable_min_sale_price : $more_products[$i]['_min_variation_price'];
                            $more_products[$i]['_max_variation_sale_price'] = $variable_max_sale_price ? $variable_max_sale_price : $more_products[$i]['_max_variation_price'];
                            $more_products[$i]['_min_variation_regular_price'] = get_post_meta( $p_obj->get_id(), '_min_variation_regular_price', true );
                            $more_products[$i]['_max_variation_regular_price'] = get_post_meta( $p_obj->get_id(), '_max_variation_regular_price', true );
                        }
                    }
                    $i++;
                }
            }
        }
        return apply_filters( 'mvx_more_spmv_products_data', $more_products, $product_id );
    }
}

/**
 * Get failed and pending order commission ID
 *
 * @return commission ID
 */

function mvx_failed_pending_order_commission() {
    // find failed and pending order
    $failed_and_pending_orders = mvx_get_orders( array('post_status' => array( 'wc-failed', 'wc-pending' )), 'ids', true );
    $commission_id = array();
    if (!empty($failed_and_pending_orders)) {
        foreach ($failed_and_pending_orders as $order_id) {
            $commission_id[] = mvx_get_order_commission_id($order_id);
        }
    }
    return $commission_id;
}

function mvx_get_option( $key, $default_val = '', $lang_code = '' ) {
    $option_val = get_option( $key, $default_val );
    
    // WPML Support
    if ( defined( 'ICL_SITEPRESS_VERSION' ) && ! ICL_PLUGIN_INACTIVE && class_exists( 'SitePress' ) ) {
        global $sitepress;
        if( !$lang_code ) {
            $current_language = $sitepress->get_current_language();
        } else {
            $current_language = $lang_code;
        }
        $option_val = get_option( $key . '_' . $current_language, $option_val );
    }
    
    return $option_val;
}

function mvx_update_option( $key, $option_val ) {
    // WPML Support
    if ( defined( 'ICL_SITEPRESS_VERSION' ) && ! ICL_PLUGIN_INACTIVE && class_exists( 'SitePress' ) ) {
        global $sitepress;
        $current_language = $sitepress->get_current_language();
        update_option( $key . '_' . $current_language, $option_val );
    } else {
        update_option( $key, $option_val );
    }
}

function mvx_get_user_meta( $user_id, $key, $is_single = true ) {
    $meta_val = get_user_meta( $user_id, $key, $is_single );
    // WPML Support
    if ( defined( 'ICL_SITEPRESS_VERSION' ) && ! ICL_PLUGIN_INACTIVE && class_exists( 'SitePress' ) ) {
        global $sitepress;
        $current_language = $sitepress->get_current_language();
        $option_val_wpml = get_user_meta( $user_id, $key . '_' . $current_language, $is_single );
        if( $option_val_wpml ) $meta_val = $option_val_wpml;
    }
    
    return $meta_val;
}

function mvx_update_user_meta( $user_id, $key, $meta_val ) {
    // WPML Support
    if ( defined( 'ICL_SITEPRESS_VERSION' ) && ! ICL_PLUGIN_INACTIVE && class_exists( 'SitePress' ) ) {
        global $sitepress;
        $current_language = $sitepress->get_current_language();
        update_user_meta( $user_id, $key . '_' . $current_language, $meta_val );
    } else {
        update_user_meta( $user_id, $key, $meta_val );
    }
}

if (!function_exists('mvx_is_store_page')) {
    /**
     * Check if it's a store page
     *
     * @return bool
     */
    function mvx_is_store_page() {
        global $MVX;
        $vendor = false;
        if (get_queried_object()) {
            $vendor_id = is_tax($MVX->taxonomy->taxonomy_name) ? get_queried_object()->term_id : false;
            $vendor = $vendor_id ? get_mvx_vendor_by_term($vendor_id) : false;
        } else {
            $store_id = get_query_var('author');
            if ($store_id) {
                $vendor = get_mvx_vendor($store_id);
            }
        }
        if ($vendor) {
            return true;
        }
        return false;
    }
}

if (!function_exists('mvx_find_shop_page_vendor')) {
    /**
     * find vendor id from vendor shop page
     *
     * @return store_id
     */
    function mvx_find_shop_page_vendor() {
        $store_id = false;
        if (get_queried_object()) {
            $vendor_id = get_queried_object()->term_id;
            $store = get_mvx_vendor_by_term($vendor_id);
            $store_id = $store ? $store->id : false;;
        } else {
            $store_id = get_query_var('author');
        }
        return $store_id;
    }
}

if (!function_exists('mvx_get_attachment_url')) {
    /**
     * MVX get attachment URL by ID
     */
    function mvx_get_attachment_url( $attachment_id ) {
        $attachment_url = '';
        if( $attachment_id && is_numeric( $attachment_id ) ) {
            $attachment_url = wp_get_attachment_url( $attachment_id );
        } else {
            $attachment_url = $attachment_id;
        }
        return $attachment_url;
    }
    
}

if (!function_exists('mvx_mapbox_api_enabled')) {
    function mvx_mapbox_api_enabled() {
        $get_choose_map = get_mvx_vendor_settings('choose_map_api') ? get_mvx_vendor_settings('choose_map_api') : '';
        $mapbox_api_key = get_mvx_vendor_settings('mapbox_api_key') ? get_mvx_vendor_settings('mapbox_api_key') : '';
        $mapbox_enabled = !empty($mapbox_api_key) && !empty($get_choose_map) && $get_choose_map == 'mapbox_api_set' ? $mapbox_api_key : false;
        return $mapbox_enabled;
    }
}

if (!function_exists('mvx_mapbox_design_switcher')) {
    function mvx_mapbox_design_switcher() {
        if (mvx_mapbox_api_enabled()) {
            $map_styles_option = apply_filters('mvx_mapbox_map_style_switcher', 
                array(
                    'satellite' => array(
                        'id' => 'satellite-v9',
                        'name' => 'rtoggle',
                        'value' => __('Satellite', 'dc-woocommerce-multi-vendor'),
                        'checked' => 'yes'
                    ),
                    'light' => array(
                        'id' => 'light-v10',
                        'name' => 'rtoggle',
                        'value' => __('Light', 'dc-woocommerce-multi-vendor'),
                    ),
                    'dark' => array(
                        'id' => 'dark-v10',
                        'name' => 'rtoggle',
                        'value' => __('Dark', 'dc-woocommerce-multi-vendor'),
                    ),
                    'streets' => array(
                        'id' => 'streets-v11',
                        'name' => 'rtoggle',
                        'value' => __('Streets', 'dc-woocommerce-multi-vendor'),
                    ),
                    'outdoors' => array(
                        'id' => 'outdoors-v11',
                        'name' => 'rtoggle',
                        'value' => __('Outdoors', 'dc-woocommerce-multi-vendor'),
                    ),
                ) 
            );
            ?>
            <div id="menu">
                <?php foreach ($map_styles_option as $map_key => $map_value) { ?>
                    <input id="<?php echo $map_value['id'] ?>" type="radio" name="<?php echo $map_value['name'] ?>" value="<?php echo $map_key ?>" <?php if( isset($map_value['checked']) && $map_value['checked']){ echo 'checked="checked"'; } ?> >
                    <label for="<?php echo $map_value['id'] ?>"><?php echo esc_html($map_value['value']); ?></label>
                <?php } ?>
            </div>
            <?php
        }
    }
}
if (!function_exists('mvx_vendor_distance_by_shipping_settings')) {
    function mvx_vendor_distance_by_shipping_settings( $vendor_id = 0 ) {
        global $MVX;
        $mvx_shipping_by_distance = get_user_meta( $vendor_id, '_mvx_shipping_by_distance', true ) ? get_user_meta( $vendor_id, '_mvx_shipping_by_distance', true ) : array();
        $MVX->mvx_wp_fields->dc_generate_form_field( apply_filters( 'mvx_marketplace_settings_fields_shipping_distance', array(
            "mvx_byd_default_cost" => array('label' => __('Default Cost', 'dc-woocommerce-multi-vendor'), 'name' => 'mvx_shipping_by_distance[_default_cost]', 'placeholder' => '0.00', 'type' => 'text', 'class' => 'col-md-6 col-sm-9', 'label_class' => 'mvx_title mvx_ele mvx_store_shipping_distance_fields', 'value' => isset($mvx_shipping_by_distance['_default_cost']) ? $mvx_shipping_by_distance['_default_cost'] : '' ),

            "mvx_byd_max_distance" => array('label' => __('Max Distance (km)', 'dc-woocommerce-multi-vendor'), 'name' => 'mvx_shipping_by_distance[_max_distance]', 'placeholder' => __('No Limit', 'dc-woocommerce-multi-vendor'), 'type' => 'text', 'class' => 'col-md-6 col-sm-9', 'label_class' => 'mvx_title mvx_ele mvx_store_shipping_distance_fields', 'value' => isset($mvx_shipping_by_distance['_max_distance']) ? $mvx_shipping_by_distance['_max_distance'] : '' ),
            "mvx_byd_enable_local_pickup" => array('label' => __('Enable Local Pickup', 'dc-woocommerce-multi-vendor'), 'name' => 'mvx_shipping_by_distance[_enable_local_pickup]', 'type' => 'checkbox', 'class' => 'mvx-checkbox mvx_ele mvx_store_shipping_distance_fields', 'label_class' => 'mvx_title checkbox_title checkbox-title mvx_ele mvx_store_shipping_distance_fields', 'value' => 'yes', 'dfvalue' => isset($mvx_shipping_by_distance['_enable_local_pickup']) ? 'yes' : '' ),

            "mvx_byd_local_pickup_cost" => array('label' => __('Local Pickup Cost', 'dc-woocommerce-multi-vendor'), 'name' => 'mvx_shipping_by_distance[_local_pickup_cost]', 'placeholder' => '0.00', 'type' => 'text', 'class' => 'col-md-6 col-sm-9', 'label_class' => 'mvx_title mvx_ele mvx_store_shipping_distance_fields', 'value' => isset($mvx_shipping_by_distance['_local_pickup_cost']) ? $mvx_shipping_by_distance['_local_pickup_cost'] : '' ),
        ) ) );

        $mvx_shipping_by_distance_rates = get_user_meta( $vendor_id, '_mvx_shipping_by_distance_rates', true ) ? get_user_meta( $vendor_id, '_mvx_shipping_by_distance_rates', true ) : array();
        $MVX->mvx_wp_fields->dc_generate_form_field(
            apply_filters( 'mvx_settings_fields_shipping_rates_by_distance', array( 
                    "mvx_shipping_by_distance_rates" => array(
                        'label'       => __('Distance-Cost Rules', 'dc-woocommerce-multi-vendor'), 
                        'type'        => 'multiinput',
                        'class'       => 'form-group',
                        'value'       => $mvx_shipping_by_distance_rates,
                        'options' => array(
                            "mvx_distance_rule" => array( 
                                'label' => __('Distance Rule', 'dc-woocommerce-multi-vendor'), 
                                'type' => 'select', 
                                'class' => 'col-md-6 col-sm-9', 
                                'label_class' => '', 
                                'options' => array(
                                    'up_to' => __('Distance up to', 'dc-woocommerce-multi-vendor'),
                                    'more_than' => __('Distance more than', 'dc-woocommerce-multi-vendor')
                                )
                            ),
                            "mvx_distance_unit" => array( 
                                'label' => __('Distance', 'dc-woocommerce-multi-vendor') . ' ( '. __('km', 'dc-woocommerce-multi-vendor') .' )', 
                                'type' => 'number', 
                                'class' => 'col-md-6 col-sm-9', 
                                'label_class' => ''
                            ),
                            "mvx_distance_price" => array( 
                                'label' => __('Cost', 'dc-woocommerce-multi-vendor') . ' ('.get_woocommerce_currency_symbol().')', 
                                'type' => 'number', 
                                'placeholder' => '0.00 (' . __('Free Shipping', 'dc-woocommerce-multi-vendor') . ')',
                                'class' => 'col-md-6 col-sm-9', 
                                'label_class' => '' 
                            ),
                        )
                    )
                ) 
            )
        );
    }
}

if (!function_exists('mvx_vendor_different_type_shipping_options')) {
    function mvx_vendor_different_type_shipping_options( $vendor_id = 0) {
        $vendor_shipping_options = get_user_meta($vendor_id, 'vendor_shipping_options', true) ? get_user_meta($vendor_id, 'vendor_shipping_options', true) : '';
        $shipping_options = apply_filters('mvx_vendor_shipping_option_to_vendor', array(
            'distance_by_zone' => __('Shipping by Zone', 'dc-woocommerce-multi-vendor'),
        ) );
        if (get_mvx_vendor_settings( 'enabled_distance_by_shipping_for_vendor', 'general' ) && 'Enable' === get_mvx_vendor_settings( 'enabled_distance_by_shipping_for_vendor', 'general' )) {
            $shipping_options['distance_by_shipping'] = __('Shipping by Distance', 'dc-woocommerce-multi-vendor');
        }
        if (get_mvx_vendor_settings( 'enabled_shipping_by_country_for_vendor', 'general' ) && 'Enable' === get_mvx_vendor_settings( 'enabled_shipping_by_country_for_vendor', 'general' )) {
            $shipping_options['shipping_by_country'] = __('Shipping by Country', 'dc-woocommerce-multi-vendor');
        }
        ?>
        <label for="shipping-options"><?php esc_html_e( 'Shipping Options', 'dc-woocommerce-multi-vendor' ); ?></label>
        <select class="form-control inline-select" id="shipping-options" name="shippping-options">
            <?php foreach ( $shipping_options as $value => $label ) : ?>
                <option value="<?php echo esc_attr( $value ); ?>" <?php echo selected( $vendor_shipping_options, $value, false ); ?>><?php echo esc_html( $label ); ?></option>
            <?php endforeach; ?>
        </select>
        <?php
    }
}

if (!function_exists('mvx_vendor_shipping_by_country_settings')) {
    function mvx_vendor_shipping_by_country_settings( $vendor_id = 0 ) {
        global $MVX;
        $mvx_shipping_by_country = mvx_get_user_meta( $vendor_id, '_mvx_shipping_by_country', array() ) ? mvx_get_user_meta( $vendor_id, '_mvx_shipping_by_country', array() )[0] : '';
        $mvx_country_rates       = mvx_get_user_meta( $vendor_id, '_mvx_country_rates', array() ) ? mvx_get_user_meta( $vendor_id, '_mvx_country_rates', array() ) : '';
        $mvx_state_rates         = mvx_get_user_meta( $vendor_id, '_mvx_state_rates', array() ) ? mvx_get_user_meta( $vendor_id, '_mvx_state_rates', array() ) : '';
        $MVX->mvx_wp_fields->dc_generate_form_field (
            apply_filters( 'mvx_settings_fields_shipping_by_country', array(
                "mvx_shipping_type_price" => array('label' => __('Default Shipping Price', 'dc-woocommerce-multi-vendor'), 'name' => 'mvx_shipping_by_country[_mvx_shipping_type_price]', 'placeholder' => '0.00', 'type' => 'text', 'class' => 'col-md-6 col-sm-9', 'label_class' => 'mvx_title mvx_ele', 'value' => isset($mvx_shipping_by_country['_mvx_shipping_type_price']) ? $mvx_shipping_by_country['_mvx_shipping_type_price'] : '', 'hints' => __('This is the base price and will be the starting shipping price for each product', 'dc-woocommerce-multi-vendor') ),
                "mvx_additional_product" => array('label' => __('Per Product Additional Price', 'dc-woocommerce-multi-vendor'), 'name' => 'mvx_shipping_by_country[_mvx_additional_product]', 'placeholder' => '0.00', 'type' => 'text', 'class' => 'col-md-6 col-sm-9', 'label_class' => 'mvx_title mvx_ele', 'value' => isset($mvx_shipping_by_country['_mvx_additional_product']) ? $mvx_shipping_by_country['_mvx_additional_product'] : '', 'hints' => __('If a customer buys more than one type product from your store, first product of the every second type will be charged with this price', 'dc-woocommerce-multi-vendor') ),
                "mvx_additional_qty" => array('label' => __('Per Qty Additional Price', 'dc-woocommerce-multi-vendor'), 'name' => 'mvx_shipping_by_country[_mvx_additional_qty]', 'placeholder' => '0.00', 'type' => 'text', 'class' => 'col-md-6 col-sm-9', 'label_class' => 'mvx_title mvx_ele', 'value' => isset($mvx_shipping_by_country['_mvx_additional_qty']) ? $mvx_shipping_by_country['_mvx_additional_qty'] : '', 'hints' => __('Every second product of same type will be charged with this price', 'dc-woocommerce-multi-vendor') ),
                "mvx_byc_free_shipping_amount" => array('label' => __('Free Shipping Minimum Order Amount', 'dc-woocommerce-multi-vendor'), 'name' => 'mvx_shipping_by_country[_free_shipping_amount]', 'placeholder' => __( 'NO Free Shipping', 'dc-woocommerce-multi-vendor'), 'type' => 'text', 'class' => 'col-md-6 col-sm-9', 'label_class' => 'mvx_title mvx_ele', 'value' => isset($mvx_shipping_by_country['_free_shipping_amount']) ? $mvx_shipping_by_country['_free_shipping_amount'] : '', 'hints' => __('Free shipping will be available if order amount more than this. Leave empty to disable Free Shipping.', 'dc-woocommerce-multi-vendor') ),
                "mvx_byc_enable_local_pickup" => array('label' => __('Enable Local Pickup', 'dc-woocommerce-multi-vendor'), 'name' => 'mvx_shipping_by_country[_enable_local_pickup]', 'type' => 'checkbox', 'class' => 'mvx-checkbox mvx_ele', 'label_class' => 'mvx_title checkbox_title checkbox-title mvx_ele', 'value' => 'yes', 'dfvalue' => isset($mvx_shipping_by_country['_enable_local_pickup']) ? 'yes' : '' ),
                "mvx_byc_local_pickup_cost" => array('label' => __('Local Pickup Cost', 'dc-woocommerce-multi-vendor'), 'name' => 'mvx_shipping_by_country[_local_pickup_cost]', 'placeholder' => '0.00', 'type' => 'text', 'class' => 'col-md-6 col-sm-9', 'label_class' => 'mvx_title mvx_ele', 'value' => isset($mvx_shipping_by_country['_local_pickup_cost']) ? $mvx_shipping_by_country['_local_pickup_cost'] : '' ),
            ) )
        );

        $mvx_shipping_rates = array();
        $state_options = array();
        if ( $mvx_country_rates ) {
            foreach ( $mvx_country_rates[0] as $country => $country_rate ) {
                $mvx_shipping_state_rates = array();
                $state_options = array();
                if ( !empty( $mvx_state_rates[0] ) && isset( $mvx_state_rates[0][$country] ) ) {
                    foreach ( $mvx_state_rates[0][$country] as $state => $state_rate ) {
                        $state_options[$state] = $state;
                        $mvx_shipping_state_rates[] = array( 
                            'mvx_state_to' => $state, 
                            'mvx_state_to_price' => $state_rate, 
                            'option_values' => $state_options 
                        );
                    }
                }
                $mvx_shipping_rates[] = array( 
                    'mvx_country_to' => $country, 
                    'mvx_country_to_price' => $country_rate, 
                    'mvx_shipping_state_rates' => $mvx_shipping_state_rates 
                );
            }   
        }
        $every_where = array('everywhere' => __('Everywhere Else', 'dc-woocommerce-multi-vendor'));
        $MVX->mvx_wp_fields->dc_generate_form_field( 
            apply_filters( 'mvx_settings_fields_shipping_rates_by_country', array( 
                "mvx_shipping_rates" => array(
                    'label' => __('Shipping Rates by Country', 'dc-woocommerce-multi-vendor') , 
                    'type' => 'multiinput', 
                    'value' => $mvx_shipping_rates, 
                    'desc' => __( 'Add the countries you deliver your products to. You can specify states as well. If the shipping price is same except some countries, there is an option Everywhere Else, you can use that.', 'dc-woocommerce-multi-vendor' ),
                    'desc_class' => 'instructions', 
                    'options' => array(
                        "mvx_country_to" => array(
                            'label' => __('Country', 'dc-woocommerce-multi-vendor'), 
                            'type' => 'select',
                            'class' => 'col-md-6 col-sm-9 mvx_country_to_select', 
                            'options' => array_merge($every_where, WC()->countries->get_shipping_countries())
                        ),
                        "mvx_country_to_price" => array(
                            'label' => __('Cost', 'dc-woocommerce-multi-vendor') . '('.get_woocommerce_currency_symbol().')', 
                            'type' => 'text',
                            'dfvalue' => 0,
                            'placeholder' => '0.00',
                            'class' => 'col-md-6 col-sm-9', 
                        ),
                        "mvx_shipping_state_rates" => array(
                            'label' => __('State Shipping Rates', 'dc-woocommerce-multi-vendor'), 
                            'type' => 'multiinput', 
                            'label_class' => 'mvx_title mvx_shipping_state_rates_label', 
                            'options' => array(
                                "mvx_state_to" => array( 
                                    'label' => __('State', 'dc-woocommerce-multi-vendor'), 
                                    'type' => 'select', 'class' => 'col-md-6 col-sm-9 mvx_state_to_select', 
                                    'options' => $state_options 
                                ),
                                "mvx_state_to_price" => array( 
                                    'label' => __('Cost', 'dc-woocommerce-multi-vendor') . '('.get_woocommerce_currency_symbol().')', 
                                    'type' => 'text', 
                                    'dfvalue' => 0,
                                    'placeholder' => '0.00 (' . __('Free Shipping', 'dc-woocommerce-multi-vendor') . ')', 
                                    'class' => 'col-md-6 col-sm-9', 
                                ),
                            ) 
                        )   
                    ) 
                )
            ) ) 
        );
    }
}

if (!function_exists('is_customer_not_given_review_to_vendor')) {
    function is_customer_not_given_review_to_vendor( $vendor_id = 0, $customer_id = 0) {
        $vendor = get_mvx_vendor($vendor_id);
        $reviews_lists = $vendor->get_reviews_and_rating(0);
        $vendor_review_user_id = wp_list_pluck($reviews_lists, 'user_id');
        if (in_array($customer_id, $vendor_review_user_id)) {
            return false;
        }
        return true;
    }
}


if (!function_exists('mvx_admin_backend_settings_fields_details')) {
    function mvx_admin_backend_settings_fields_details() {
        global $MVX;
        // Find country list
        $woo_countries = new WC_Countries();
        $countries = $woo_countries->get_allowed_countries();
        $country_list = $pages_array = $country_list = [];
        foreach ($countries as $countries_key => $countries_value) {
            $country_list[] = array(
                'lebel' => $countries_key,
                'value' => $countries_value
            );
        }

        // Find MVX created pages
        $pages = get_pages();
        $woocommerce_pages = array(wc_get_page_id('shop'), wc_get_page_id('cart'), wc_get_page_id('checkout'), wc_get_page_id('myaccount'));
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

        // default nested fields
        $default_nested_data = array(
            array(
                'nested_datas'  => array(
                    (Object)[]
                )
            )
        );

        $settings_fields = [
            'settings-general'  =>  [
                [
                    'key'       => 'approve_vendor',
                    'type'      => 'toggle_rectangle',
                    'class'     => 'mvx-toggle-radio-switcher',
                    'label'     => __( 'Approve Vendor', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Lets you either check vendor information manually or sends vendors directly to the dashboard as soon as the registration is complete', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'name'  => 'approve_vendor',
                            'key' => 'manually',
                            'label' => __('Manually', 'dc-woocommerce-multi-vendor'),
                            'value' => 'manually'
                        ),
                        array(
                            'name'  => 'approve_vendor',
                            'key'   => 'automatically',
                            'label' => __('Automatically', 'dc-woocommerce-multi-vendor'),
                            'value' => 'automatically'
                        )
                    ),
                    'database_value' => '',
                ],
                [
                    'key'    => 'vendors_backend_access',
                    'label'   => __( "Vendor's Backend Access", 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'props'     => array(
                        'disabled'  => true
                    ),
                    'options' => array(
                        array(
                            'key'=> "vendors_backend_access",
                            'label'=> __('Limit vendors from accessing their backened', 'dc-woocommerce-multi-vendor'),
                            'value'=> "vendors_backend_access"
                        ),
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'display_product_seller',
                    'label'   => __( "Display Product Seller", 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'props'     => array(
                        'disabled'  => true
                    ),
                    'options' => array(
                        array(
                            'key'=> "display_product_seller",
                            'label'=> __("Showcase the product vendor's name", 'dc-woocommerce-multi-vendor'),
                            'value'=> "display_product_seller"
                        ),
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'category_pyramid_guide',
                    'label'   => __( "Category Pyramid Guide (CPG)", 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'props'     => array(
                        //'disabled'  => true
                    ),
                    'options' => array(
                        array(
                            'key'=> "category_pyramid_guide",
                            'label'=> __("CPG option helps vendor's to identify the correct categories for their products", 'dc-woocommerce-multi-vendor'),
                            'value'=> "category_pyramid_guide"
                        ),
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'       =>  'vendor_list_page',
                    'type'      =>  'blocktext',
                    'label'     =>  __( 'no_label', 'dc-woocommerce-multi-vendor' ),
                    'blocktext'      =>  __( "Use the <code>[wcmp_vendorlist]</code> shortcode to display vendor's list on your site <a href='https://www.w3schools.com'>Learn More</a>", 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'registration_page',
                    'type'      => 'select',
                    'label'     => __( 'Registration Page', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Select the page on which you have inserted [seller_registration] shortcode .', 'dc-woocommerce-multi-vendor' ),
                    'options' => $pages_array,
                    'database_value' => '',
                ],
                [
                    'key'       => 'vendor_dashboard_page',
                    'type'      => 'select',
                    'label'     => __( 'Vendor Dashboard Page', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Select the page on which you have inserted [mvx_seller] shortcode .', 'dc-woocommerce-multi-vendor' ),
                    'options' => $pages_array,
                    'database_value' => '',
                ],
                [
                    'key'       => 'avialable_shortcodes',
                    'type'      => 'table',
                    'label'     => __( 'Avialable Shortcodes', 'dc-woocommerce-multi-vendor' ),
                    'label_options' =>  array(
                        __('Shortcodes', 'dc-woocommerce-multi-vendor'),
                        __('Description', 'dc-woocommerce-multi-vendor'),
                    ),
                    'options' => array(
                        array(
                            'variable'=> "<code>mvx_vendor</code>",
                            'description'=> __('Enables you to create a seller dashboard ', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'variable'=> "<code>vendor_registration</code>",
                            'description'=> __('Creates a page where the vendor registration form is available', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'variable'=> "<code>vendor_coupons</code>",
                            'description'=> __('Lets you view  a brief summary of the coupons created by the seller and number of times it has been used by the customers', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'variable'=> "<code>mvx_recent_products</code>",
                            'description'=> __('Allows you to glance at the recent products added by seller', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'variable'=> "<code>mvx_products</code>",
                            'description'=> __('Displays the products added by seller', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'variable'=> "<code>mvx_featured_products</code>",
                            'description'=> __('Exhibits featured products added by the seller', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'variable'=> "<code>mvx_sale_products</code>",
                            'description'=> __('Allows you to see the products put on sale by a seller', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'variable'=> "<code>mvx_top_rated_products</code>",
                            'description'=> __('Displays the top rated products of the seller', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'variable'=> "<code>mvx_best_selling_products</code>",
                            'description'=> __('Offers you the option of viewing the best selling products of the vendor', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'variable'=> "<code>mvx_product_category</code>",
                            'description'=> __('Lets you see the product categories used by the vendor', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'variable'=> "<code>mvx_vendorslist</code>",
                            'description'=> __('Shows customers a list of available seller.', 'dc-woocommerce-multi-vendor'),
                        ),
                    ),
                    'database_value' => '',
                ],

            ],
            'social'    =>  [
                [
                    'key'    => 'buddypress_enabled',
                    'label'   => __( 'Buddypress', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'checkbox',
                    'class'     => 'mvx-toggle-checkbox',
                    'options' => array(
                        array(
                            'key'=> "buddypress_enabled",
                            'label'=> __('Allows sellers to sell products on their buddypress profile while connecting with their customers', 'dc-woocommerce-multi-vendor'),
                            'value'=> "buddypress_enabled"
                        )
                    ),
                    'database_value' => array(),
                ],
            ],
            'registration'  =>  [
            ],
            'seller-dashbaord'  =>  [
                [
                    'key'    => 'mvx_new_dashboard_site_logo',
                    'label'   => __( 'Branding Logo', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'file',
                    'width' =>  75,
                    'height'    => 75,
                    'desc' => __('Upload Brand Image as Logo', 'dc-woocommerce-multi-vendor'),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'vendor_color_scheme_picker',
                    'type'      => 'radio_color',
                    'label'     => __( 'Color Scheme', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Select your prefered colou scheme', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'name'  => 'vendor_color_scheme_picker',
                            'key' => 'outer_space_blue',
                            'label' => __('Outer Space', 'dc-woocommerce-multi-vendor'),
                            'color' => array('#202528', '#333b3d','#3f85b9', '#316fa8'),
                            'value' => 'outer_space_blue'
                        ),
                        array(
                            'name'  => 'vendor_color_scheme_picker',
                            'key'   => 'green_lagoon',
                            'label' => __('Green Lagoon', 'dc-woocommerce-multi-vendor'),
                            'color' => array('#171717', '#212121', '#009788','#00796a'),
                            'value' => 'green_lagoon'
                        ),
                        array(
                            'name'  => 'vendor_color_scheme_picker',
                            'key'   => 'old_west',
                            'label' => __('Old West', 'dc-woocommerce-multi-vendor'),
                            'color' => array('#46403c', '#59524c', '#c7a589', '#ad8162'),
                            'value' => 'old_west'
                        ),
                        array(
                            'name'  => 'vendor_color_scheme_picker',
                            'key'   => 'wild_watermelon',
                            'label' => __('Wild Watermelon', 'dc-woocommerce-multi-vendor'),
                            'color' => array('#181617', '#353130', '#fd5668', '#fb3f4e'),
                            'value' => 'wild_watermelon'
                        ),
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'setup_wizard_introduction',
                    'type'      => 'wpeditor',
                    'class'     =>  'mvx-setting-wpeditor-class',
                    'label'     => __( 'Vendor Setup wizard Introduction Message', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( "Type an message to introduce your vendor's to their dashboard", 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'separator_content',
                    'type'      => 'section',
                    'label'     => "",
                ],
                [
                    'key'       => 'mvx_vendor_announcements_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Announcements Endpoint', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor announcements page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-announcements', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_store_settings_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Storefront Endpoint', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Used as site logo on vendor dashboard pages', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('storefront', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_profile_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Seller Profile Endpoint', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor profile management page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('profile', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_policies_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Seller Policies Endpoint', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor policies page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-policies', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_billing_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Seller Billing Endpoint', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor billing page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-billing', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_shipping_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Shipping Endpoint', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor shipping page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-shipping', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_report_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Seller Report Endpoint', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor report page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-report', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_banking_overview_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Banking Overview Endpoint', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor banking overview page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('banking-overview', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_add_product_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Add Product Endpoint', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for add new product page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('add-product', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_edit_product_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Edit Product Endpoint', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for edit product page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('edit-product', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_products_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Products List Endpoint', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for products list page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('products', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_add_coupon_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Add Coupon Endpoint', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for add new coupon page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('add-coupon', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_coupons_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Coupons List Endpoint', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for coupons list page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('coupons', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_orders_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Orders Endpoint', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor orders page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-orders', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_withdrawal_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Widthdrawals Endpoint', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor widthdrawals page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-withdrawal', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_transaction_details_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Transaction Details Endpoint', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for transaction details page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('transaction-details', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_knowledgebase_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Seller Knowledgebase Endpoint', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor knowledgebase page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-knowledgebase', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_tools_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Seller Tools Endpoint', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor tools page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-tools', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_products_qnas_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Seller Products Q&As Endpoint', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor products Q&As page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('products-qna', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'separator_content',
                    'type'      => 'section',
                    'label'     => "",
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_dashboard_custom_css',
                    'type'      => 'textarea',
                    'label'     => __( 'Custom CSS', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Apply custom CSS to change sashboard design', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
            ],
            'store' =>  [
                [
                    'key'       => 'mvx_vendor_shop_template',
                    'type'      => 'radio_select',
                    'label'     => __( 'Store Header', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( "Select store banner style", 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'name'  => 'mvx_vendor_shop_template',
                            'key' => 'template1',
                            'label' => __('Outer Space', 'dc-woocommerce-multi-vendor'),
                            'color' => $MVX->plugin_url.'assets/images/template1.png',
                            'width' => 50,
                            'height'=> 60,
                            'value' => 'template1'
                        ),
                        array(
                            'name'  => 'mvx_vendor_shop_template',
                            'key'   => 'template2',
                            'label' => __('Green Lagoon', 'dc-woocommerce-multi-vendor'),
                            'color' => $MVX->plugin_url.'assets/images/template2.png',
                            'width' => 50,
                            'height'=> 60,
                            'value' => 'template2'
                        ),
                        array(
                            'name'  => 'mvx_vendor_shop_template',
                            'key'   => 'template3',
                            'label' => __('Old West', 'dc-woocommerce-multi-vendor'),
                            'color' => $MVX->plugin_url.'assets/images/template3.png',
                            'width' => 50,
                            'height'=> 60,
                            'value' => 'template3'
                        ),
                    ),
                    'database_value' => '',
                ],
                [
                    'key'    => 'enable_store_map_for_vendor',
                    'label'   => __( 'Store Location', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "enable_store_map_for_vendor",
                            'label'=> __("Tap to dispay the location of  the vendors' shops", 'dc-woocommerce-multi-vendor'),
                            'value'=> "enable_store_map_for_vendor"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'choose_map_api',
                    'type'      => 'select',
                    'bydefault' =>  'google_map_set',
                    'label'     => __( 'Location Provider', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Select prefered location Provider', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'key'=> "google_map_set",
                            'label'=> __('Google map', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('google_map_set', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "mapbox_api_set",
                            'selected'  => true,
                            'label'=> __('Mapbox map', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('mapbox_api_set', 'dc-woocommerce-multi-vendor'),
                        )
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'google_api_key',
                    'type'      => 'text',
                    'depend'    => 'choose_map_api',
                    'dependvalue'       =>  'google_map_set',
                    'label'     => __( 'Google Map API key', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __('<a href="https://developers.google.com/maps/documentation/javascript/get-api-key#get-an-api-key" target="_blank">Click here to generate key</a>','dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mapbox_api_key',
                    'type'      => 'text',
                    'depend'    => 'choose_map_api',
                    'dependvalue'       =>  'mapbox_api_set',
                    'label'     => __( 'Mapbox access token', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __('<a href="https://docs.mapbox.com/help/getting-started/access-tokens/" target="_blank">Click here to generate access token</a>','dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'show_related_products',
                    'type'      => 'select',
                    'label'     => __( 'Related Product', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Tap to let  customers view other product related to the  product they are seeing', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'key'=> "all_related",
                            'label'=> __('Related Products from Entire Store', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('all_related', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "vendors_related",
                            'selected'  => true,
                            'label'=> __("Related Products from Vendor's Store", 'dc-woocommerce-multi-vendor'),
                            'value'=> __('vendors_related', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "disable",
                            'selected'  => true,
                            'label'=> __("Disable", 'dc-woocommerce-multi-vendor'),
                            'value'=> __('disable', 'dc-woocommerce-multi-vendor'),
                        )
                    ),
                    'database_value' => '',
                ],
                [
                    'key'    => 'is_enable_store_sidebar',
                    'label'   => __( 'Store Sidebar', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_enable_store_sidebar",
                            'label'=> __('Tap to display sidebar section for vendor shop page. Select her to add vendor shop widget', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_enable_store_sidebar"
                        ),
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'store_sidebar_position',
                    'type'      => 'toggle_rectangle',
                    'label'     => __( 'Store Sidebar Position', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Decide where your want your store sidebar to be displayed', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'name'  =>  'store_sidebar_position',
                            'key'=> "left",
                            'label'=> __('At Left', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('At Left', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'name'  =>  'store_sidebar_position',
                            'key'=> "right",
                            'label'=> __('At Right', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('At Right', 'dc-woocommerce-multi-vendor'),
                        ),
                    ),
                    'database_value' => '',
                ],
            ],
            'products'  =>  [
                [
                    'key'    => 'product_types',
                    'label'   => __( 'Product Types', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'parent_class'  => 'mvx-toggle-checkbox-header',
                    'type'    => 'checkbox',
                    'select_deselect'   =>  true,
                    'desc' => __('lets vendors transforms simple products into either nontangible virtual product or into a product that can be downloaded', 'dc-woocommerce-multi-vendor'),
                    'options' => array(
                        array(
                            'key'=> "simple",
                            'label'=> __('Simple', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('Simple', 'dc-woocommerce-multi-vendor'),
                            'value'=> "simple"
                        ),
                        array(
                            'key'=> "variable",
                            'label'=> __('Variable', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('Variable', 'dc-woocommerce-multi-vendor'),
                            'value'=> "variable"
                        ),
                        array(
                            'key'=> "external",
                            'label'=> __('External', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('External', 'dc-woocommerce-multi-vendor'),
                            'value'=> "external"
                        ),
                        array(
                            'key'=> "grouped",
                            'label'=> __('Grouped', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('Grouped', 'dc-woocommerce-multi-vendor'),
                            'value'=> "grouped"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'type_options',
                    'label'   => __( 'Type options', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'parent_class'  => 'mvx-toggle-checkbox-header',
                    'select_deselect'   =>  true,
                    'type'    => 'checkbox',
                    'desc' => __('lets vendors transforms products into either non-tangible vitual products or product that can be downloaded', 'dc-woocommerce-multi-vendor'),
                    'options' => array(
                        array(
                            'key'=> "virtual",
                            'label'=> __('Virtual', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('Virtual', 'dc-woocommerce-multi-vendor'),
                            'value'=> "virtual"
                        ),
                        array(
                            'key'=> "downloadable",
                            'label'=> __('Downloadable', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('Downloadable', 'dc-woocommerce-multi-vendor'),
                            'value'=> "downloadable"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'products_fields',
                    'type'      => 'checkbox',
                    'class'     => 'mvx-toggle-checkbox',
                    'parent_class'  => 'mvx-toggle-checkbox-header',
                    'select_deselect'   =>  true,
                    'label'     => __( 'Product Fields ', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'name'  => 'products_fields',
                            'key' => 'general',
                            'label' => __('Generl', 'dc-woocommerce-multi-vendor'),
                            'value' => 'general'
                        ),
                        array(
                            'name'  => 'products_fields',
                            'key'   => 'inventory',
                            'label' => __('Inventory', 'dc-woocommerce-multi-vendor'),
                            'value' => 'inventory'
                        ),
                        array(
                            'name'  => 'products_fields',
                            'key'   => 'linked_product',
                            'label' => __('Linked Product', 'dc-woocommerce-multi-vendor'),
                            'value' => 'linked_product'
                        ),
                        array(
                            'name'  => 'products_fields',
                            'key'   => 'attribute',
                            'label' => __('Attribute', 'dc-woocommerce-multi-vendor'),
                            'value' => 'attribute'
                        ),
                        array(
                            'name'  => 'products_fields',
                            'key'   => 'advance',
                            'label' => __('Advance', 'dc-woocommerce-multi-vendor'),
                            'value' => 'advance'
                        ),
                        array(
                            'name'  => 'products_fields',
                            'key'   => 'policies',
                            'label' => __('Policies', 'dc-woocommerce-multi-vendor'),
                            'value' => 'policies'
                        ),
                        array(
                            'name'  => 'products_fields',
                            'key'   => 'product_tag',
                            'label' => __('Product Tag', 'dc-woocommerce-multi-vendor'),
                            'value' => 'product_tag'
                        ),
                        array(
                            'name'  => 'products_fields',
                            'key'   => 'GTIN',
                            'label' => __('GTIN', 'dc-woocommerce-multi-vendor'),
                            'value' => 'GTIN'
                        )
                    ),
                    'database_value' => '',
                ],
            ],
            'products-capability'   =>  [
                [
                    'key'    => 'is_submit_product',
                    'label'   => __( 'Submit Products', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_submit_product",
                            'label'=> __('This option enables vendors to not only add new products but to also submit them for your approval', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_submit_product"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'is_published_product',
                    'label'   => __( 'Publish Products', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_published_product",
                            'label'=> __('Vendors can publish their product on site without your approval', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_published_product"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'is_edit_delete_published_product',
                    'label'   => __( 'Edit Published Products', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_edit_delete_published_product",
                            'label'=> __('This option  lets the vendor correct a published product', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_edit_delete_published_product"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'publish_and_submit_products',
                    'label'   => __( 'Publish and Submit Products', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "publish_and_submit_products",
                            'label'=> __('Allowa vendors to  make their products live while submitting it to your for correction', 'dc-woocommerce-multi-vendor'),
                            'value'=> "publish_and_submit_products"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'is_submit_coupon',
                    'label'   => __( 'Submit Coupons', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_submit_coupon",
                            'label'=> __('This option enables vendors to create their own coupons', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_submit_coupon"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'is_publish_coupon',
                    'label'   => __( 'Publish Coupons', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_publish_coupon",
                            'label'=> __('With this option vendors can make their coupons live on your site', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_publish_coupon"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'is_edit_coupon',
                    'label'   => __( 'Edit Coupons', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_edit_coupon",
                            'label'=> __('Vendors can edit an re-use a published coupon', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_edit_coupon"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'is_upload_files',
                    'label'   => __( 'Upload Media Files', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_upload_files",
                            'label'=> __('Let Vendors upload media like ebooks, music, video, images etc', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_upload_files"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'import_product',
                    'label'   => __( 'Import Product', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "import_product",
                            'label'=> __('Import product data from your computer', 'dc-woocommerce-multi-vendor'),
                            'value'=> "import_product"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'export_product',
                    'label'   => __( 'Export Product', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "export_product",
                            'label'=> __('Export product data to your computer', 'dc-woocommerce-multi-vendor'),
                            'value'=> "export_product"
                        )
                    ),
                    'database_value' => array(),
                ],
            ],
            'commissions'   =>  [
                [
                    'key'       => 'revenue_sharing_mode',
                    'type'      => 'toggle_rectangle',
                    'class'     => 'mvx-toggle-radio-switcher',
                    'label'     => __( 'Revenue Sharing Mode', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Select how you want the commission to be split', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'name'  => 'revenue_sharing_mode',
                            'key' => 'revenue_sharing_mode_admin',
                            'label' => __('Admin fees', 'dc-woocommerce-multi-vendor'),
                            'value' => 'revenue_sharing_mode_admin'
                        ),
                        array(
                            'name'  => 'revenue_sharing_mode',
                            'key'   => 'revenue_sharing_mode_vendor',
                            'label' => __('Vendor commissions', 'dc-woocommerce-multi-vendor'),
                            'value' => 'revenue_sharing_mode_vendor'
                        )
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'commission_type',
                    'type'      => 'select',
                    'label'     => __( 'Commission Type', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Choose the Commission Option prefered by you. For better undrestanding read doc', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'key'=> "choose_commission_type",
                            'label'=> __('Choose Commission Type', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('choose_commission_type', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "fixed",
                            'label'=> __('Fixed Amount', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('fixed', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "percent",
                            'label'=> __('Percentage', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('percent', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "fixed_with_percentage",
                            'label'=> __('%age + Fixed (per transaction)', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('fixed_with_percentage', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "fixed_with_percentage_qty",
                            'label'=> __('%age + Fixed (per unit)', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('fixed_with_percentage_qty', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "commission_by_product_price",
                            'label'=> __('Commission By Product Price', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('commission_by_product_price', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "commission_by_purchase_quantity",
                            'label'=> __('Commission By Purchase Quantity', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('commission_by_purchase_quantity', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "fixed_with_percentage_per_vendor",
                            'label'=> __('%age + Fixed (per vendor)', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('fixed_with_percentage_per_vendor', 'dc-woocommerce-multi-vendor'),
                        ),
                    ),
                    'database_value' => '',
                ],
                // default commissions
                [
                    'key'       => 'default_commission',
                    'type'      => 'multi_number',
                    'depend'    => 'commission_type',
                    'dependvalue'       =>  'fixed',
                    'label'     => __( 'Commission Value', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __('The commission amount added here will be applicable for all commissions. In case the your commission type is fixed the', 'dc-woocommerce-multi-vendor'),
                    'options' => array(
                        array(
                            'name'  => 'default_commission',
                            'key' => 'fixed_ammount',
                            'type'      => 'number',
                            'label' => __('Fixed', 'dc-woocommerce-multi-vendor'),
                            'value' => 'fixed_ammount'
                        )
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'default_commission',
                    'type'      => 'multi_number',
                    'depend'    => 'commission_type',
                    'dependvalue'       =>  'percent',
                    'label'     => __( 'Commission Value', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __('The commission amount added here will be applicable for all commissions. In case the your commission type is fixed the', 'dc-woocommerce-multi-vendor'),
                    'options' => array(
                        array(
                            'name'  => 'default_commission',
                            'key'   => 'percent_amount',
                            'type'      => 'number',
                            'label' => __('Percentage', 'dc-woocommerce-multi-vendor'),
                            'value' => 'percent_amount'
                        )
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'default_commission',
                    'type'      => 'multi_number',
                    'depend'    => 'commission_type',
                    'dependvalue'       =>  'fixed_with_percentage',
                    'label'     => __( 'Commission Value', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __('The commission amount added here will be applicable for all commissions. In case the your commission type is fixed the', 'dc-woocommerce-multi-vendor'),
                    'options' => array(
                        array(
                            'name'  => 'default_commission',
                            'key' => 'fixed_ammount',
                            'type'      => 'number',
                            'label' => __('Fixed', 'dc-woocommerce-multi-vendor'),
                            'value' => 'fixed_ammount'
                        ),
                        array(
                            'name'  => 'default_commission',
                            'key'   => 'percent_amount',
                            'type'      => 'number',
                            'label' => __('Percentage', 'dc-woocommerce-multi-vendor'),
                            'value' => 'percent_amount'
                        )
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'default_commission',
                    'type'      => 'multi_number',
                    'depend'    => 'commission_type',
                    'dependvalue'       =>  'fixed_with_percentage_qty',
                    'label'     => __( 'Commission Value', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __('The commission amount added here will be applicable for all commissions. In case the your commission type is fixed the', 'dc-woocommerce-multi-vendor'),
                    'options' => array(
                        array(
                            'name'  => 'default_commission',
                            'key' => 'fixed_ammount',
                            'type'      => 'number',
                            'label' => __('Fixed', 'dc-woocommerce-multi-vendor'),
                            'value' => 'fixed_ammount'
                        ),
                        array(
                            'name'  => 'default_commission',
                            'key'   => 'percent_amount',
                            'type'      => 'number',
                            'label' => __('Percentage', 'dc-woocommerce-multi-vendor'),
                            'value' => 'percent_amount'
                        )
                    ),
                    'database_value' => '',
                ],
                [
                    'key'    => 'payment_method_disbursement',
                    'label'   => __( 'Commission Disbursement Method', 'dc-woocommerce-multi-vendor' ),
                    'desc'  =>  __( "display only enabled payment gateways. To enable your choosen disbursement type click here (link module page)", 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'right_content' =>  true,
                    'options' => array(
                        array(
                            'key'=> "paypal_masspay",
                            'label'=> __('PayPal Masspay (Stop Waiting and Pay Vendors Immediately with PayPal Real-Time Split Payment using <a href="https://wc-marketplace.com/product/mvx-paypal-marketplace/">MVX PayPal Marketplace</a>. Please visit our site)', 'dc-woocommerce-multi-vendor'),
                            'value'=> "paypal_masspay"
                        ),
                        array(
                            'key'=> "paypal_payout",
                            'label'=> __('Paypal Payout', 'dc-woocommerce-multi-vendor'),
                            'value'=> "paypal_payout"
                        ),
                        array(
                            'key'=> "stripe_masspay",
                            'label'=> __('Stripe Connect', 'dc-woocommerce-multi-vendor'),
                            'value'=> "stripe_masspay"
                        ),
                        array(
                            'key'=> "direct_bank",
                            'label'=> __('Direct Bank Transfer', 'dc-woocommerce-multi-vendor'),
                            'value'=> "direct_bank"
                        ),
                        array(
                            'key'=> "razorpay_block",
                            'label'=> __('Razorpay', 'dc-woocommerce-multi-vendor'),
                            'value'=> "razorpay_block"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'separator_content',
                    'type'      => 'section',
                    'label'     => "",
                ],
                [
                    'key'       => 'payment_gateway_charge',
                    'label'     => __( 'Payment Gateway Charge', 'dc-woocommerce-multi-vendor' ),
                    'desc'  =>  __( "Add the charges inccured during online payment processing", 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'      => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "payment_gateway_charge",
                            'label'=> __('If checked, you can set payment gateway charge to the vendor for commission disbursement.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "payment_gateway_charge"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'gateway_charges_cost_carrier',
                    'type'      => 'select',
                    'label'     => __( 'Who bear the gateway charges', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __('You can decide who will bear the gateways charges incase of using any automatic payment', 'dc-woocommerce-multi-vendor'),
                    'options' => array(
                        array(
                            'key'=> "vendor",
                            'label'=> __('Vendor', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('vendor', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "admin",
                            'label'=> __('Site owner', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('admin', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "separate",
                            'label'=> __('Separately', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('separate', 'dc-woocommerce-multi-vendor'),
                        )
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'payment_gateway_charge_type',
                    'type'      => 'select',
                    'label'     => __( 'Gateway Charge Type', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __('Choose your preferred gateway charge type.', 'dc-woocommerce-multi-vendor'),
                    'options' => array(
                        array(
                            'key'=> "percent",
                            'label'=> __('Percentage', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('percent', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "fixed",
                            'label'=> __('Fixed Amount', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('fixed', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "fixed_with_percentage",
                            'label'=> __('%age + Fixed', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('fixed_with_percentage', 'dc-woocommerce-multi-vendor'),
                        )
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'masspay_percentage_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Percentage', 'dc-woocommerce-multi-vendor' ),
                    'desc'  => __('PayPal Masspay (Stop Waiting and Pay Vendors Immediately with PayPal Real-Time Split Payment using <a href="https://wc-marketplace.com/product/mvx-paypal-marketplace/">MVX PayPal Marketplace</a>. Please visit our site)', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'masspay_fixed_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Fixed', 'dc-woocommerce-multi-vendor' ),
                    'desc'  => __('PayPal Masspay (Stop Waiting and Pay Vendors Immediately with PayPal Real-Time Split Payment using <a href="https://wc-marketplace.com/product/mvx-paypal-marketplace/">MVX PayPal Marketplace</a>. Please visit our site)', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'payout_percentage_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Percentage', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'payout_fixed_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Fixed', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'stripe_percentage_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Percentage', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'stripe_fixed_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Fixed', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'bank_percentage_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Percentage', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'bank_fixed_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Fixed', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
            ],
            'dashbaord-management'   => [
                /*[
                    'key'    => 'is_backend_diabled',
                    'label'   => __( 'Disallow Vendors wp-admin Access', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'hints' => __('If unchecked vendor will have access to backend', 'dc-woocommerce-multi-vendor'),
                    'props'     => array(
                        'disabled'  => true
                    ),
                    'options' => array(
                        array(
                            'key'=> "reactjs",
                            'label'=> __('Get <a href="//wc-marketplace.com/product/mvx-frontend-manager/">Advanced Frontend Manager</a> to offer a single dashboard for all vendor purpose and eliminate their backend access requirement <code>example.com/category/my-category/</code>.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "reactjs"
                        ),
                    ),
                    'database_value' => array(),
                ],*/
                [
                    'key'    => 'mvx_new_dashboard_site_logo',
                    'label'   => __( 'Dashbaord Brand Logo', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'file',
                    'width' =>  75,
                    'height'    => 75,
                    'desc' => __('Used as site logo on vendor dashboard pages', 'dc-woocommerce-multi-vendor'),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'vendor_color_scheme_picker',
                    'type'      => 'radio_color',
                    'label'     => __( 'Dashboard Color Scheme', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Used as site logo on vendor dashboard pages', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'name'  => 'vendor_color_scheme_picker',
                            'key' => 'outer_space_blue',
                            'label' => __('Outer Space', 'dc-woocommerce-multi-vendor'),
                            'color' => array('#202528', '#333b3d','#3f85b9', '#316fa8'),
                            'value' => 'outer_space_blue'
                        ),
                        array(
                            'name'  => 'vendor_color_scheme_picker',
                            'key'   => 'green_lagoon',
                            'label' => __('Green Lagoon', 'dc-woocommerce-multi-vendor'),
                            'color' => array('#171717', '#212121', '#009788','#00796a'),
                            'value' => 'green_lagoon'
                        ),
                        array(
                            'name'  => 'vendor_color_scheme_picker',
                            'key'   => 'old_west',
                            'label' => __('Old West', 'dc-woocommerce-multi-vendor'),
                            'color' => array('#46403c', '#59524c', '#c7a589', '#ad8162'),
                            'value' => 'old_west'
                        ),
                        array(
                            'name'  => 'vendor_color_scheme_picker',
                            'key'   => 'wild_watermelon',
                            'label' => __('Wild Watermelon', 'dc-woocommerce-multi-vendor'),
                            'color' => array('#181617', '#353130', '#fd5668', '#fb3f4e'),
                            'value' => 'wild_watermelon'
                        ),
                    ),
                    'database_value' => '',
                ],

                [
                    'key'       => 'mvx_vendor_shop_template',
                    'type'      => 'radio_select',
                    'label'     => __( 'Vendor Shop Template', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Used as site logo on vendor dashboard pages', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'name'  => 'mvx_vendor_shop_template',
                            'key' => 'template1',
                            'label' => __('Outer Space', 'dc-woocommerce-multi-vendor'),
                            'color' => $MVX->plugin_url.'assets/images/template1.png',
                            'width' => 50,
                            'height'=> 60,
                            'value' => 'template1'
                        ),
                        array(
                            'name'  => 'mvx_vendor_shop_template',
                            'key'   => 'template2',
                            'label' => __('Green Lagoon', 'dc-woocommerce-multi-vendor'),
                            'color' => $MVX->plugin_url.'assets/images/template2.png',
                            'width' => 50,
                            'height'=> 60,
                            'value' => 'template2'
                        ),
                        array(
                            'name'  => 'mvx_vendor_shop_template',
                            'key'   => 'template3',
                            'label' => __('Old West', 'dc-woocommerce-multi-vendor'),
                            'color' => $MVX->plugin_url.'assets/images/template3.png',
                            'width' => 50,
                            'height'=> 60,
                            'value' => 'template3'
                        ),
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'setup_wizard_introduction',
                    'type'      => 'wpeditor',
                    'class'     =>  'mvx-setting-wpeditor-class',
                    'label'     => __( 'Introduction step', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Add some introduction or welcome speech to your vendor. This section display in vendor store setup wizard introduction step.', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_dashboard_custom_css',
                    'type'      => 'textarea',
                    'label'     => __( 'Custom CSS', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Will be applicable on vendor frontend', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
            ],
            'dashbaord-pages'   => [
               
                [
                    'key'       => 'mvx_vendor',
                    'type'      => 'select',
                    'label'     => __( 'Vendor Dashboard', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Choose your preferred page for vendor dashboard', 'dc-woocommerce-multi-vendor' ),
                    'options' => $pages_array,
                    'database_value' => '',
                ],
                [
                    'key'       => 'vendor_registration',
                    'type'      => 'select',
                    'label'     => __( 'Vendor Dashboard', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Choose your preferred page for vendor registration', 'dc-woocommerce-multi-vendor' ),
                    'options' => $pages_array,
                    'database_value' => '',
                ],
            ],
            'store-management'   => [
                [
                    'key'    => 'is_enable_store_sidebar',
                    'label'   => __( 'Enable Store Sidebar', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'hints' => __('If unchecked vendor will have access to backend', 'dc-woocommerce-multi-vendor'),
                    'options' => array(
                        array(
                            'key'=> "is_enable_store_sidebar",
                            'label'=> __('Uncheck this to disable vendor store sidebar..', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_enable_store_sidebar"
                        ),
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'store_sidebar_position',
                    'type'      => 'select',
                    'label'     => __( 'Store Sidebar Position', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'If you are not sure where to add widget, just go to admin <a href=".admin_url("widgets.php")." terget="_blank">widget</a> section and add your preferred widgets to <b>vendor store sidebar</b>.', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'key'=> "left",
                            'label'=> __('At Left', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('At Left', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "right",
                            'label'=> __('At Right', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('At Right', 'dc-woocommerce-multi-vendor'),
                        ),
                    ),
                    'database_value' => '',
                ],

                [
                    'key'    => 'store_follow_enabled',
                    'label'   => __( 'Enable Store Follow', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'hints' => __('If unchecked vendor will have access to backend', 'dc-woocommerce-multi-vendor'),
                    'options' => array(
                        array(
                            'key'=> "store_follow_enabled",
                            'label'=> __('Checked this to enable store follow.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "store_follow_enabled"
                        ),
                    ),
                    'database_value' => array(),
                ],

                [
                    'key'    => 'store_follow_section',
                    'label'   => __( '', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'section',
                    'database_value' => array(),
                ],

                [
                    'key'    => 'store_follow_enabled',
                    'label'   => __( 'Enable Store Follow', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'parent_class'  => 'mvx-toggle-checkbox-header',
                    'type'    => 'checkbox',
                    'hints' => __('Checked this to enable store follow..', 'dc-woocommerce-multi-vendor'),
                    'options' => array(
                        array(
                            'key'=> "store_follow_enabled",
                            'label'=> __('store follow 1', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('store details', 'dc-woocommerce-multi-vendor'),
                            'value'=> "store_follow_enabled"
                        ),
                        array(
                            'key'=> "store_follow_enabled1",
                            'label'=> __('store follow 2', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('store details', 'dc-woocommerce-multi-vendor'),
                            'value'=> "store_follow_enabled1"
                        ),
                        array(
                            'key'=> "store_follow_enabled2",
                            'label'=> __('store follow 3', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('store details', 'dc-woocommerce-multi-vendor'),
                            'value'=> "store_follow_enabled2"
                        ),
                        array(
                            'key'=> "store_follow_enabled3",
                            'label'=> __('store follow 4', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('store details', 'dc-woocommerce-multi-vendor'),
                            'value'=> "store_follow_enabled3"
                        ),
                        array(
                            'key'=> "store_follow_enabled4",
                            'label'=> __('store follow 5', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('store details', 'dc-woocommerce-multi-vendor'),
                            'value'=> "store_follow_enabled4"
                        ),
                        array(
                            'key'=> "store_follow_enabled5",
                            'label'=> __('store follow 6', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('store details', 'dc-woocommerce-multi-vendor'),
                            'value'=> "store_follow_enabled5"
                        ),
                        array(
                            'key'=> "store_follow_enabled6",
                            'label'=> __('store follow 7', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('store details', 'dc-woocommerce-multi-vendor'),
                            'value'=> "store_follow_enabled6"
                        ),
                        array(
                            'key'=> "store_follow_enabled7",
                            'label'=> __('store follow 8', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('store details', 'dc-woocommerce-multi-vendor'),
                            'value'=> "store_follow_enabled7"
                        ),
                    ),
                    'database_value' => array(),
                ],
            ],
            'product-settings'  => [
                [
                    'key'       => 'type_options',
                    'type'      => 'toggle_rectangle',
                    'class'     => 'mvx-toggle-radio-switcher',
                    'label'     => __( 'Type Options ', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Type Options ', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'name'  => 'type_options',
                            'key' => 'virtual_type',
                            'label' => __('Virtual', 'dc-woocommerce-multi-vendor'),
                            'value' => 'virtual_type'
                        ),
                        array(
                            'name'  => 'type_options',
                            'key'   => 'download_type',
                            'label' => __('Downloadable', 'dc-woocommerce-multi-vendor'),
                            'value' => 'download_type'
                        )
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'products_fields',
                    'type'      => 'toggle_rectangle',
                    'class'     => 'mvx-toggle-radio-switcher',
                    'label'     => __( 'Product Fields ', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'name'  => 'products_fields',
                            'key' => 'general',
                            'label' => __('Generl', 'dc-woocommerce-multi-vendor'),
                            'value' => 'general'
                        ),
                        array(
                            'name'  => 'products_fields',
                            'key'   => 'inventory',
                            'label' => __('Inventory', 'dc-woocommerce-multi-vendor'),
                            'value' => 'inventory'
                        ),
                        array(
                            'name'  => 'products_fields',
                            'key'   => 'linked_product',
                            'label' => __('Linked Product', 'dc-woocommerce-multi-vendor'),
                            'value' => 'linked_product'
                        ),
                        array(
                            'name'  => 'products_fields',
                            'key'   => 'attribute',
                            'label' => __('Attribute', 'dc-woocommerce-multi-vendor'),
                            'value' => 'attribute'
                        ),
                        array(
                            'name'  => 'products_fields',
                            'key'   => 'advance',
                            'label' => __('Advance', 'dc-woocommerce-multi-vendor'),
                            'value' => 'advance'
                        ),
                        array(
                            'name'  => 'products_fields',
                            'key'   => 'policies',
                            'label' => __('Policies', 'dc-woocommerce-multi-vendor'),
                            'value' => 'policies'
                        ),
                        array(
                            'name'  => 'products_fields',
                            'key'   => 'product_tag',
                            'label' => __('Product Tag', 'dc-woocommerce-multi-vendor'),
                            'value' => 'product_tag'
                        ),
                        array(
                            'name'  => 'products_fields',
                            'key'   => 'GTIN',
                            'label' => __('GTIN', 'dc-woocommerce-multi-vendor'),
                            'value' => 'GTIN'
                        )
                    ),
                    'database_value' => '',
                ],
            ],
            
            'product-capability'    => [
                [
                    'key'    => 'is_submit_product',
                    'label'   => __( 'Submit Products', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_submit_product",
                            'label'=> __('Allow vendors to submit products for approval/publishing.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_submit_product"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'is_published_product',
                    'label'   => __( 'Publish Products', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_published_product",
                            'label'=> __('If checked, products uploaded by vendors will be directly published without admin approval.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_published_product"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'is_edit_delete_published_product',
                    'label'   => __( 'Edit Published Products', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_edit_delete_published_product",
                            'label'=> __('Allow vendors to edit published products.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_edit_delete_published_product"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'publish_and_submit_products',
                    'label'   => __( 'Publish and Submit Products', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "publish_and_submit_products",
                            'label'=> __('Publish and Submit Products.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "publish_and_submit_products"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'is_submit_coupon',
                    'label'   => __( 'Submit Coupons', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_submit_coupon",
                            'label'=> __('Allow vendors to create coupons.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_submit_coupon"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'is_upload_files',
                    'label'   => __( 'Upload Media Files', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_upload_files",
                            'label'=> __('Allow vendors to upload media files.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_upload_files"
                        )
                    ),
                    'database_value' => array(),
                ],
            ],
            'spmv-pages'    => [
                [
                    'key'    => 'is_singleproductmultiseller',
                    'label'   => __( 'Allow Vendor to Copy Products', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_singleproductmultiseller",
                            'label'=> __('Let vendors search for product sold on your site and sell them from theirs', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_singleproductmultiseller"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'singleproductmultiseller_show_order',
                    'type'      => 'select',
                    'label'     => __( 'Shop Page product display based on', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => stripslashes(__('Select which SMPV Product to Display In the Shop Page', 'dc-woocommerce-multi-vendor')),
                    'options' => array(
                        array(
                            'key'=> "min-price",
                            'label'=> __('Min Price', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('min-price', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "max-price",
                            'label'=> __('Max Price', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('max-price', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "top-rated-vendor",
                            'label'=> __('Top rated vendor', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('top-rated-vendor', 'dc-woocommerce-multi-vendor'),
                        )
                    ),
                    'database_value' => '',
                ],
            ],
            'commission-configuration'  => [
                [
                    'key'       => 'revenue_sharing_mode',
                    'type'      => 'toggle_rectangle',
                    'class'     => 'mvx-toggle-radio-switcher',
                    'label'     => __( 'Revenue Sharing Mode', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Revenue Sharing Mode dashboard pages', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'name'  => 'revenue_sharing_mode',
                            'key' => 'revenue_sharing_mode_admin',
                            'label' => __('Admin fees', 'dc-woocommerce-multi-vendor'),
                            'value' => 'revenue_sharing_mode_admin'
                        ),
                        array(
                            'name'  => 'revenue_sharing_mode',
                            'key'   => 'revenue_sharing_mode_vendor',
                            'label' => __('Vendor commissions', 'dc-woocommerce-multi-vendor'),
                            'value' => 'revenue_sharing_mode_vendor'
                        )
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'commission_type',
                    'type'      => 'select',
                    'label'     => __( 'Commission Type', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Choose your preferred commission type. It will affect all commission calculations.', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'key'=> "choose_commission_type",
                            'label'=> __('Choose Commission Type', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('choose_commission_type', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "fixed",
                            'label'=> __('Fixed Amount', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('fixed', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "percent",
                            'label'=> __('Percentage', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('percent', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "fixed_with_percentage",
                            'label'=> __('%age + Fixed (per transaction)', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('fixed_with_percentage', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "fixed_with_percentage_qty",
                            'label'=> __('%age + Fixed (per unit)', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('fixed_with_percentage_qty', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "commission_by_product_price",
                            'label'=> __('Commission By Product Price', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('commission_by_product_price', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "commission_by_purchase_quantity",
                            'label'=> __('Commission By Purchase Quantity', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('commission_by_purchase_quantity', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "fixed_with_percentage_per_vendor",
                            'label'=> __('%age + Fixed (per vendor)', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('fixed_with_percentage_per_vendor', 'dc-woocommerce-multi-vendor'),
                        ),
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'default_commission',
                    'type'      => 'number',
                    'label'     => __( 'Commission Value', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __('This will be the default commission(in percentage or fixed) paid to vendors if product and vendor-specific commission is not set.', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'default_percentage',
                    'type'      => 'number',
                    'label'     => __( 'Commission Percentage', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __('This will be the default percentage paid to vendors if product and vendor specific commission is not set', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'fixed_with_percentage',
                    'type'      => 'number',
                    'label'     => __( 'Fixed Amount', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __('Fixed (per transaction)', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'fixed_with_percentage_qty',
                    'type'      => 'number',
                    'label'     => __( 'Fixed Amount', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __('Fixed (per unit)', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'fixed_with_percentage_per_vendor',
                    'type'      => 'number',
                    'label'     => __( 'Fixed Amount', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __('Fixed (per vendor)', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'    => 'payment_method_disbursement',
                    'label'   => __( 'Commission Disbursement Method', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "paypal_masspay",
                            'label'=> __('PayPal Masspay (Stop Waiting and Pay Vendors Immediately with PayPal Real-Time Split Payment using <a href="https://wc-marketplace.com/product/mvx-paypal-marketplace/">MVX PayPal Marketplace</a>. Please visit our site)', 'dc-woocommerce-multi-vendor'),
                            'value'=> "paypal_masspay"
                        ),
                        array(
                            'key'=> "paypal_payout",
                            'label'=> __('Paypal Payout', 'dc-woocommerce-multi-vendor'),
                            'value'=> "paypal_payout"
                        ),
                        array(
                            'key'=> "stripe_masspay",
                            'label'=> __('Stripe Connect', 'dc-woocommerce-multi-vendor'),
                            'value'=> "stripe_masspay"
                        ),
                        array(
                            'key'=> "direct_bank",
                            'label'=> __('Direct Bank Transfer', 'dc-woocommerce-multi-vendor'),
                            'value'=> "direct_bank"
                        ),
                        array(
                            'key'=> "razorpay_block",
                            'label'=> __('Razorpay', 'dc-woocommerce-multi-vendor'),
                            'value'=> "razorpay_block"
                        )
                    ),
                    'database_value' => array(),
                ],

                [
                    'key'       => 'payment_gateway_charge',
                    'label'     => __( 'Payment Gateway Charge', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'      => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "payment_gateway_charge",
                            'label'=> __('If checked, you can set payment gateway charge to the vendor for commission disbursement.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "payment_gateway_charge"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'gateway_charges_cost_carrier',
                    'type'      => 'select',
                    'label'     => __( 'Who bear the gateway charges', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __('Choose your preferred gateway charges carrier.', 'dc-woocommerce-multi-vendor'),
                    'options' => array(
                        array(
                            'key'=> "vendor",
                            'label'=> __('Vendor', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('vendor', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "admin",
                            'label'=> __('Site owner', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('admin', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "separate",
                            'label'=> __('Separately', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('separate', 'dc-woocommerce-multi-vendor'),
                        )
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'payment_gateway_charge_type',
                    'type'      => 'select',
                    'label'     => __( 'Gateway Charge Type', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __('Choose your preferred gateway charge type.', 'dc-woocommerce-multi-vendor'),
                    'options' => array(
                        array(
                            'key'=> "percent",
                            'label'=> __('Percentage', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('percent', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "fixed",
                            'label'=> __('Fixed Amount', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('fixed', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "fixed_with_percentage",
                            'label'=> __('%age + Fixed', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('fixed_with_percentage', 'dc-woocommerce-multi-vendor'),
                        )
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'masspay_percentage_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Percentage', 'dc-woocommerce-multi-vendor' ),
                    'desc'  => __('PayPal Masspay (Stop Waiting and Pay Vendors Immediately with PayPal Real-Time Split Payment using <a href="https://wc-marketplace.com/product/mvx-paypal-marketplace/">MVX PayPal Marketplace</a>. Please visit our site)', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'masspay_fixed_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Fixed', 'dc-woocommerce-multi-vendor' ),
                    'desc'  => __('PayPal Masspay (Stop Waiting and Pay Vendors Immediately with PayPal Real-Time Split Payment using <a href="https://wc-marketplace.com/product/mvx-paypal-marketplace/">MVX PayPal Marketplace</a>. Please visit our site)', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'payout_percentage_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Percentage', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'payout_fixed_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Fixed', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'stripe_percentage_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Percentage', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'stripe_fixed_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Fixed', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'bank_percentage_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Percentage', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'bank_fixed_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Fixed', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
            ],
       
            'review-management'   => [
                [
                    'key'       =>  'vendor_rating_page',
                    'type'      =>  'blocktext',
                    'label'     =>  __( 'no_label', 'dc-woocommerce-multi-vendor' ),
                    'blocktext'      =>  __( "<b>Admin needs to enable product review from woocommerce settings</b>", 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'    => 'is_sellerreview',
                    'label'   => __( 'Vendor Review', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_sellerreview",
                            'label'=> __('This option lets buyers rate and review a vendor.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_sellerreview"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'is_sellerreview_varified',
                    'label'   => __( 'Buyer only reviews', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_sellerreview_varified",
                            'label'=> __('Accept only verified buyer reviews, Other cant review vendors', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_sellerreview_varified"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'product_review_sync',
                    'label'   => __( 'Product Rating Sync', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "product_review_sync",
                            'label'=> __('Store Rating will be calcuated based on Store Rating + Product Rating', 'dc-woocommerce-multi-vendor'),
                            'value'=> "product_review_sync"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'mvx_review_categories',
                    'type'      => 'nested',
                    'label'     => __( 'Rating Parameters', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Specify parameters for which you want to have ratings, e.g. Packaging, Delivery, Behaviour, Policy etc', 'dc-woocommerce-multi-vendor' ),
                    'parent_options' => array(
                        array(
                            'key'=>'category',
                            'type'=> "text",
                            'class' => "nested-parent-class",
                            'name' => "nested-parent-name",
                            'label'=> __('category', 'dc-woocommerce-multi-vendor'),
                            'value'=> "category"
                        )
                    ),
                    'child_options' => array(
                    ),
                    'database_value' => $default_nested_data,
                ],
            ],
            'report-settings'   => [
                [
                    'key'       => 'custom_date_order_stat_report_mail',
                    'type'      => 'number',
                    'label'     => __( 'Set custom date for order stat report mail', 'dc-woocommerce-multi-vendor' ),
                    'hints'     => __( 'Email will send as per select dates ( put is blank for disabled it )', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __('in days', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
            ],
            'tast-list' => [

            ],
            'store-review'  => [

            ],
            'report-abuse'  => [

            ],

            'admin-overview'  => [

            ],

            'vendor'  => [

            ],

            'product'  => [

            ],
            'transaction-history'  => [

            ],

            'policy'  => [
                [
                    'key'       => 'store-policy',
                    'type'      => 'wpeditor',
                    'class'     =>  'mvx-setting-wpeditor-class',
                    'desc'      => __('Site will reflect admin created policy. However vendors can edit and override store policies', 'dc-woocommerce-multi-vendor'),
                    'label'     => __( 'Store Policy', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'shipping_policy',
                    'type'      => 'wpeditor',
                    'class'     =>  'mvx-setting-wpeditor-class',
                    'desc'      => __('Site will reflect admin created policy. However vendors can edit and override store policies', 'dc-woocommerce-multi-vendor'),
                    'label'     => __( 'Shipping Policy', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'refund_policy',
                    'type'      => 'wpeditor',
                    'class'     =>  'mvx-setting-wpeditor-class',
                    'desc'      => __('Site will reflect admin created policy. However vendors can edit and override store policies', 'dc-woocommerce-multi-vendor'),
                    'label'     => __( 'Refund Policy', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'cancellation_policy',
                    'type'      => 'wpeditor',
                    'class'     =>  'mvx-setting-wpeditor-class',
                    'desc'      => __('Site will reflect admin created policy. However vendors can edit and override store policies', 'dc-woocommerce-multi-vendor'),
                    'label'     => __( 'Cancellation / Return / Exchange Policy', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
            ],
            'disbursement'  => [
                [
                    'key'    => 'commission_include_coupon',
                    'label'   => __( 'Who will bear the Coupon Cost', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "commission_include_coupon",
                            'label'=> __('Tap to let vendor bear the coupon discount charges created by them', 'dc-woocommerce-multi-vendor'),
                            'value'=> "commission_include_coupon"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'admin_coupon_excluded',
                    'label'   => __( 'Exclude Admin Created Coupon', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "admin_coupon_excluded",
                            'label'=> __('Bear the coupon discount charges of the coupons created by you', 'dc-woocommerce-multi-vendor'),
                            'value'=> "admin_coupon_excluded"
                        )
                    ),
                    'database_value' => array(),
                ],
                
                [
                    'key'    => 'give_tax',
                    'label'   => __( 'Tax', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "give_tax",
                            'label'=> __('Tap to let vendor collect & manage tax amount', 'dc-woocommerce-multi-vendor'),
                            'value'=> "give_tax"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'give_shipping',
                    'label'   => __( 'Shipping', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "give_shipping",
                            'label'=> __('Tap to let vendors collect shipping charges', 'dc-woocommerce-multi-vendor'),
                            'value'=> "give_shipping"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'choose_payment_mode_automatic_disbursal',
                    'label'   => __( 'Disbursement Schedule', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "choose_payment_mode_automatic_disbursal",
                            'label'=> __('Schedule when vendors would recive their commission', 'dc-woocommerce-multi-vendor'),
                            'value'=> "choose_payment_mode_automatic_disbursal"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'commission_threshold',
                    'label'   => __( 'Disbursement Threshold', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'number',
                    'desc'  =>  __('Add the minimum value required before payment is disbursed to the vendor', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'    => 'withdrawal_request',
                    'label'   => __( 'Allow Withdrawal Request', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "withdrawal_request",
                            'label'=> __('Let vendors withdraw payment prior to reaching the agreed disbursement value', 'dc-woocommerce-multi-vendor'),
                            'value'=> "withdrawal_request"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'commission_threshold_time',
                    'label'   => __( 'Withdrawal Locking Period', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'number',
                    'desc' => __('Refers to the minimum numbers of day required before a vendor can send withdrawal request', 'dc-woocommerce-multi-vendor'),
                    'placeholder'   => __('in days', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'order_withdrawl_status',
                    'type'      => 'multi-select',
                    'label'     => __( 'Available Order Status for Withdrawal', 'dc-woocommerce-multi-vendor' ),
                    'desc'        => __( 'Withdrawal Request would be available in case of these Order Statuses', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'key'=> "on-hold",
                            'label'=> __('On hold', 'dc-woocommerce-multi-vendor'),
                            'value'=> "on-hold"
                        ),
                        array(
                            'key'=> "processing",
                            'label'=> __('Processing', 'dc-woocommerce-multi-vendor'),
                            'value'=> "processing"
                        ),
                        array(
                            'key'=> "completed",
                            'label'=> __('Completed', 'dc-woocommerce-multi-vendor'),
                            'value'=> "completed"
                        ),
                    ),
                    'database_value' => '',
                ]
            ],
            'suborder-configure'    => [
                [
                    'key'    => 'hide_suborder_for_customer',
                    'label'   => __( 'Hide Sub order for customers', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "hide_suborder_for_customer",
                            'label'=> __('If enabled customer cant see suborders', 'dc-woocommerce-multi-vendor'),
                            'value'=> "hide_suborder_for_customer"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'hide_suborder_for_admin',
                    'label'   => __( 'Hide Sub order for admin', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "hide_suborder_for_admin",
                            'label'=> __('If enabled admin cant see suborders', 'dc-woocommerce-multi-vendor'),
                            'value'=> "hide_suborder_for_admin"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'disallow_vendor_order_status',
                    'label'   => __( 'Disallow vendor to change order status', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "disallow_vendor_order_status",
                            'label'=> __('If enabled vendor can not chnage order status from frontend.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "disallow_vendor_order_status"
                        )
                    ),
                    'database_value' => array(),
                ],
            ],
            'store-location' => [
                [
                    'key'    => 'enable_store_map_for_vendor',
                    'label'   => __( 'Enable store map for vendors', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "enable_store_map_for_vendor",
                            'label'=> __('', 'dc-woocommerce-multi-vendor'),
                            'value'=> "enable_store_map_for_vendor"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'choose_map_api',
                    'type'      => 'select',
                    'bydefault' =>  'google_map_set',
                    'label'     => __( 'Choose Your Map', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Choose your preferred map.', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'key'=> "google_map_set",
                            'label'=> __('Google map', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('google_map_set', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "mapbox_api_set",
                            'selected'  => true,
                            'label'=> __('Mapbox map', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('mapbox_api_set', 'dc-woocommerce-multi-vendor'),
                        )
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'google_api_key',
                    'type'      => 'text',
                    'depend'    => 'choose_map_api',
                    'dependvalue'       =>  'google_map_set',
                    'label'     => __( 'Google Map API key', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __('<a href="https://developers.google.com/maps/documentation/javascript/get-api-key#get-an-api-key" target="_blank">Click here to generate key</a>','dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mapbox_api_key',
                    'type'      => 'text',
                    'depend'    => 'choose_map_api',
                    'dependvalue'       =>  'mapbox_api_set',
                    'label'     => __( 'Mapbox access token', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __('<a href="https://docs.mapbox.com/help/getting-started/access-tokens/" target="_blank">Click here to generate access token</a>','dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
            ],
            'refund-management'   => [
                /*[
                    'key'    => 'disable_refund_customer_end',
                    'label'   => __( 'Enable refund request for customer', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "disable_refund_customer_end",
                            'label'=> __('Remove capability to customer from refund request', 'dc-woocommerce-multi-vendor'),
                            'value'=> "disable_refund_customer_end"
                        )
                    ),
                    'database_value' => array(),
                ],*/
                [
                    'key'    => 'customer_refund_status',
                    'label'   => __( 'Available Status for Refund', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'checkbox',
                    'select_deselect'   =>  true,
                    'desc'  =>  __("Customers would be able to avail refund only if their order is at the following stage/s", 'dc-woocommerce-multi-vendor'),
                    'class'     => 'mvx-toggle-checkbox',
                    'options' => array(
                        array(
                            'key'=> "refund_method_pending",
                            'label'=> __('Pending', 'dc-woocommerce-multi-vendor'),
                            'value'=> "refund_method_pending"
                        ),
                        array(
                            'key'=> "refund_method_on-hold",
                            'label'=> __('On hold', 'dc-woocommerce-multi-vendor'),
                            'value'=> "refund_method_on-hold"
                        ),
                        array(
                            'key'=> "refund_method_processing",
                            'label'=> __('Processing', 'dc-woocommerce-multi-vendor'),
                            'value'=> "refund_method_processing"
                        ),
                        array(
                            'key'=> "refund_method_completed",
                            'label'=> __('Completed', 'dc-woocommerce-multi-vendor'),
                            'value'=> "refund_method_completed"
                        ),
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'refund_days',
                    'type'      => 'number',
                    'label'     => __( 'Refund Claim Period (In Days)', 'dc-woocommerce-multi-vendor' ),
                    'props'     => array(
                        'max'  => 365
                    ),
                    'hints'     => __( 'The duration till which the refund request is available/valid', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'refund_order_msg',
                    'type'      => 'textarea',
                    'label'     => __( 'Reasons For Refund', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Add reasons for refund. Use || to seperate reasons. Options will appear as a radion button to customers', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
            ],
            'payment-stripe-connect' => [
                [
                    'key'    => 'testmode',
                    'label'   => __( 'Enable Test Mode', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'checkbox',
                    'class'     => 'mvx-toggle-checkbox',
                    'options' => array(
                        array(
                            'key'=> "testmode",
                            'label'=> __('', 'dc-woocommerce-multi-vendor'),
                            'value'=> "testmode"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'test_client_id',
                    'type'      => 'blocktext',
                    'label'     => __( 'Config redirect URI', 'dc-woocommerce-multi-vendor' ),
                    'valuename' => '<code>' . admin_url('admin-ajax.php') . "?action=marketplace_stripe_authorize". '</code>',
                    'blocktext' => '<code>' . admin_url('admin-ajax.php') . "?action=marketplace_stripe_authorize". '</code><a href="https://dashboard.stripe.com/account/applications/settings" target="_blank">'.__('Copy the URI and configured stripe redirect URI with above.', 'dc-woocommerce-multi-vendor').'</a>',
                    'database_value' => '',
                ],
                [
                    'key'       => 'test_client_id',
                    'type'      => 'text',
                    'label'     => __( 'Test Client ID', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'live_client_id',
                    'type'      => 'text',
                    'label'     => __( 'Live Client ID', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'test_publishable_key',
                    'type'      => 'text',
                    'label'     => __( 'Test Publishable key', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'live_publishable_key',
                    'type'      => 'text',
                    'label'     => __( 'Live Publishable key', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'test_secret_key',
                    'type'      => 'text',
                    'label'     => __( 'Test Secret key', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'live_secret_key',
                    'type'      => 'text',
                    'label'     => __( 'Live Secret key', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],

            ],
            'buddypress' => [
                [
                    'key'    => 'profile_sync',
                    'label'   => __( 'Vendor Capability Sync', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'checkbox',
                    'class'     => 'mvx-toggle-checkbox',
                    'options' => array(
                        array(
                            'key'=> "profile_sync",
                            'label'=> __('Ignore if BuddyPress is not active', 'dc-woocommerce-multi-vendor'),
                            'value'=> "profile_sync"
                        )
                    ),
                    'database_value' => array(),
                ],
            ],
            'payment-payout' => [
                [
                    'key'       => 'client_id',
                    'type'      => 'text',
                    'label'     => __( 'Client ID', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'client_secret',
                    'type'      => 'text',
                    'label'     => __( 'Client Secret', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'    => 'is_asynchronousmode',
                    'label'   => __( 'Enable Asynchronous Mode', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'checkbox',
                    'class'     => 'mvx-toggle-checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_asynchronousmode",
                            'label'=> __('', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_asynchronousmode"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'is_testmode',
                    'label'   => __( 'Enable Test Mode', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'checkbox',
                    'class'     => 'mvx-toggle-checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_testmode",
                            'label'=> __('', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_testmode"
                        )
                    ),
                    'database_value' => array(),
                ],
            ],
            'payment-masspay' => [
                [
                    'key'       => 'api_username',
                    'type'      => 'text',
                    'label'     => __( 'API Username', 'dc-woocommerce-multi-vendor' ),
                    'hints'     => __( 'Number of Days for the refund period.', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'api_pass',
                    'type'      => 'text',
                    'label'     => __( 'API Password', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'api_signature',
                    'type'      => 'text',
                    'label'     => __( 'API Signature', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'    => 'is_testmode',
                    'label'   => __( 'Enable Test Mode', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'checkbox',
                    'class'     => 'mvx-toggle-checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_testmode",
                            'label'=> __('', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_testmode"
                        )
                    ),
                    'database_value' => array(),
                ],
            ],
            'create_announcement'   =>  [
                [
                    'key'       => 'announcement_title',
                    'type'      => 'text',
                    'label'     => __( 'Title (required)', 'dc-woocommerce-multi-vendor' ),
                    'props'     => array(
                        'required'  => true
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'announcement_url',
                    'type'      => 'url',
                    'label'     => __( 'Enter Url', 'dc-woocommerce-multi-vendor' ),
                    'props'     => array(
                        'required'  => true
                    ),
                    'database_value' => '',
                ],
                [
                    'label' => __('Enter Content', 'dc-woocommerce-multi-vendor'),
                    'type' => 'wpeditor', 
                    'key' => 'announcement_content', 
                    'database_value' => ''
                ],
                [
                    'key'       => 'announcement_vendors',
                    'type'      => 'multi-select',
                    'label'     => __( 'Vendors', 'dc-woocommerce-multi-vendor' ),
                    'options' => ($MVX->vendor_rest_api->mvx_show_vendor_name()->data),
                    'database_value' => '',
                ]
            ],
            'create_knowladgebase'   =>  [
                [
                    'key'       => 'knowladgebase_title',
                    'type'      => 'text',
                    'label'     => __( 'Title (required)', 'dc-woocommerce-multi-vendor' ),
                    'props'     => array(
                        'required'  => true
                    ),
                    'database_value' => '',
                ],
                [
                    'label' => __('Enter Content', 'dc-woocommerce-multi-vendor'),
                    'type' => 'wpeditor', 
                    'key' => 'knowladgebase_content', 
                    'database_value' => ''
                ],
            ],
            'vendor_add_personal' => [
                [
                    'key'       => 'user_login',
                    'type'      => 'text',
                    'label'     => __( 'Username (required)', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __('Usernames cannot be changed.', 'dc-woocommerce-multi-vendor'),
                    'props'     => array(
                        'required'  => true
                    ),
                    'database_value' => '',
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
                    'database_value' => '',
                ],
                [
                    'key'       => 'last_name',
                    'type'      => 'text',
                    'label'     => __( 'Last Name', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'user_email',
                    'type'      => 'email',
                    'label'     => __( 'Email (required)', 'dc-woocommerce-multi-vendor' ),
                    'props'     => array(
                        'required'  => true
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'user_nicename',
                    'type'      => 'text',
                    'label'     => __( 'Nick Name (required)', 'dc-woocommerce-multi-vendor' ),
                    'props'     => array(
                        'required'  => true
                    ),
                    'database_value' => '',
                ],
                [
                    'key'    => 'vendor_profile_image',
                    'label'   => __( 'Profile Image', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'file',
                    'width' =>  75,
                    'height'    => 75,
                    'database_value' => array(),
                ],
                
            ],
            'activity-reminder' =>  [],
            'announcement'  =>  [],
            'knowladgebase' =>  [],
            'store_review'  =>  [],
            'report-abuse'  =>  [],
            'question-ans'  =>  [],
        ];

        return $settings_fields;
    }
}


if (!function_exists('mvx_admin_backend_tab_settings')) {
    function mvx_admin_backend_tab_settings() {
        $dashboard_page_endpoint = [
            /*[
                'icon'          =>  'ico-store-icon',
                'modulename'       =>  'modules',
                'tablabel'      =>  __('Modules', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
            ],*/
            [
                'icon'          =>  'ico-store-icon',
                'modulename'       =>  'help',
                'tablabel'      =>  __('Help', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
            ],
            [
                'icon'          =>  'ico-store-icon',
                'modulename'       =>  'setup-widget',
                'tablabel'      =>  __('Setup Widget', 'dc-woocommerce-multi-vendor'),
                'link'          =>  admin_url( 'index.php?page=mvx-setup' ),
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
            ],
            [
                'icon'          =>  'ico-store-icon',
                'modulename'       =>  'migration',
                'tablabel'      =>  __('Migration', 'dc-woocommerce-multi-vendor'),
                'link'          =>  admin_url('index.php?page=mvx-migrator'),
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
            ]
        ];

                $general_settings_page_endpoint = array(
            array(
                'tablabel'       =>  __('General', 'dc-woocommerce-multi-vendor'),
                'apiurl'         =>  'mvx_module/v1/save_dashpages',
                'description'    =>  __('Configure the basic setting of the marketplace.', 'dc-woocommerce-multi-vendor'),
                'icon'           =>  'icon-tab-general',
                'modulename'     =>  'settings-general'
            ),
            array(
                'tablabel'      =>  __('Social', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __('Create a platform for seller-customer interaction', 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'icon-tab-social',
                'modulename'    =>  'social'
            ),
            array(
                'tablabel'      =>  __('Registration Form', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('Customise personalised seller registration form for marketplace', 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'icon-tab-registration-form',
                'modulename'    => 'registration'
            ),
            array(
                'tablabel'      =>  __('Seller Dashbaord', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __("Manage the appearance of your seller's dashboard", 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'icon-tab-seller-dashbaord',
                'modulename'    =>  'seller-dashbaord'
            ),
            array(
                'tablabel'      =>  __('Store', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __("Manage setting related to the sellers shop", 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'icon-tab-store',
                'modulename'    =>  'store'
            ),
            array(
                'tablabel'      =>  __('Products', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __("Select the type of product that best suits your marketplace", 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'icon-tab-products',
                'modulename'    =>  'products'
            ),
            array(
                'tablabel'      =>  __('Products Capability', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __("Manage product-related capabilities that you want sellers to have.", 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'icon-tab-products-capability',
                'modulename'    =>  'products-capability'
            ),
            array(
                'tablabel'      =>  __('SPMV(Single Product Multiple Vendor)', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __("Give sellers the option to add other seller's products into their store invendtory", 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'icon-tab-SPMV',
                'modulename'    =>  'spmv-pages'
            ),
            array(
                'tablabel'      =>  __('Commissions', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __("Configure commission settings to customise your commission plan", 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'icon-tab-commissions',
                'modulename'    =>  'commissions'
            ),
            array(
                'tablabel'      =>  __('Disbursement', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __('Manage payment and disbursement setting of your site', 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'icon-tab-disbursement',
                'modulename'    =>  'disbursement'
            ),
            array(
                'tablabel'      =>  __('Policy', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __('Add policies that are applicable to your site', 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'icon-tab-policy',
                'modulename'    =>  'policy'
            ),
            array(
                'tablabel'      =>  __('Refunds', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __('Set conditions for refund requests', 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'icon-tab-refunds',
                'modulename'    =>  'refund-management'
            ),
            array(
                'tablabel'      =>  __('Reviews & Rating', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __('Manage settings for product and store review', 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'icon-tab-reviews-and-rating',
                'modulename'    =>  'review-management'
            ),
        );


        $payment_page_endpoint = array(
            array(
                'tablabel'      =>  __('PayPal Masspay', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __('PayPal Payout makes it easy for you to pay multiple sellers at the sametime', 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'payment-masspay'
            ),
            array(
                'tablabel'      =>  __('PayPal Payout', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __('PayPal Payout makes it easy for you to pay multiple sellers at the sametime', 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'payment-payout'
            ),
            array(
                'tablabel'      =>  __('Stripe Connect', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __('Connect to vendors stripe account and make hassle-free transfers as scheduled', 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'payment-stripe-connect'
            )
        );

        $advance_page_endpoint = array(
            array(
                'tablabel'      =>  __('Buddypress', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'buddypress'
            )
        );

        $analytics_page_endpoint = array(
            array(
                'tablabel'      =>  __('Overview', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('View the Overall Performance of The Site', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'admin-overview'
            ),
            array(
                'tablabel'      =>  __('Vendor', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('Get Reports on Vendor Sales', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'vendor'
            ),
            array(
                'tablabel'      =>  __('Product', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('View Porduct Sales', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'product'
            ),
            array(
                'tablabel'      =>  __('Transaction History', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('Get Detailed Reports On Vendor Commission', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'transaction-history'
            )
        );

        $marketplace_vendors = array(
            array(
                'tablabel'      =>  __('Personal', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'vendor-personal'
            ),
            array(
                'tablabel'      =>  __('Store', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'vendor-store'
            ),
            array(
                'tablabel'      =>  __('Social', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'vendor-social'
            ),
            array(
                'tablabel'      =>  __('Payment', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'vendor-payments'
            ),
            array(
                'tablabel'      =>  __('Vendor Application', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'vendor-application'
            ),
            array(
                'tablabel'      =>  __('Vendor Shipping', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'vendor-shipping'
            ),
            array(
                'tablabel'      =>  __('Vendor Followers', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'vendor-followers'
            ),
            array(
                'tablabel'      =>  __('Vendor Policy', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'vendor-policy'
            ),

        );

        $marketplace_workboard = array(
            array(
                'tablabel'      =>  __('Taskboard', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('Keeps track of all important marketplace chores', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'activity-reminder'
            ),
            array(
                'tablabel'      =>  __('Announcement', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('Broadcast important messages, news and announcements to single or multiple sellers.Read more to learn about this feature.', 'dc-woocommerce-multi-vendor'),//neda
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'announcement'
            ),
            array(
                'tablabel'      =>  __('Knowledgebase', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('"Share tutorials, best practices, "how-to" guides or whatever you feel is appropriate with your vendors. Read More ', 'dc-woocommerce-multi-vendor'),//neda
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'knowladgebase'
            ),
            array(
                'tablabel'      =>  __('Store Review', 'dc-woocommerce-multi-vendor'),
               'description'   =>  __('View feeckback received from cutomers. ', 'dc-woocommerce-multi-vendor'),//neda
                 'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'store-review'
            ),
            array(
                'tablabel'      =>  __('Report Abuse', 'dc-woocommerce-multi-vendor'),
               'description'   =>  __('Keep track of complaints and reports filed by customers.  ', 'dc-woocommerce-multi-vendor'),//neda
                 'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'report-abuse'
            ),
            array(
                'tablabel'      =>  __('Question & Answer', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('View and publish questions sent to sellers by their customers.', 'dc-woocommerce-multi-vendor'),//neda
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'question-ans'
            )
        );

        $mvx_all_backend_tab_list = array(
            'dashboard-page'                    => $dashboard_page_endpoint,
            'marketplace-advance-settings'      => $advance_page_endpoint,
            'marketplace-analytics'             => $analytics_page_endpoint,
            'marketplace-payments'              => $payment_page_endpoint,
            'marketplace-general-settings'      => $general_settings_page_endpoint,
            'marketplace-vendors'               => $marketplace_vendors,
            'marketplace-workboard'             => $marketplace_workboard
        );
        return $mvx_all_backend_tab_list;
    }
}

if (!function_exists('mvx_admin_backend_tab_settings')) {
    function mvx_admin_backend_tab_settings() {
        $dashboard_page_endpoint = [
            /*[
                'icon'          =>  'ico-store-icon',
                'modulename'       =>  'modules',
                'tablabel'      =>  __('Modules', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
            ],*/
            [
                'icon'          =>  'ico-store-icon',
                'modulename'       =>  'help',
                'tablabel'      =>  __('Help', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
            ],
            [
                'icon'          =>  'ico-store-icon',
                'modulename'       =>  'setup-widget',
                'tablabel'      =>  __('Setup Widget', 'dc-woocommerce-multi-vendor'),
                'link'          =>  admin_url( 'index.php?page=mvx-setup' ),
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
            ],
            [
                'icon'          =>  'ico-store-icon',
                'modulename'       =>  'migration',
                'tablabel'      =>  __('Migration', 'dc-woocommerce-multi-vendor'),
                'link'          =>  admin_url('index.php?page=mvx-migrator'),
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
            ]
        ];

                $general_settings_page_endpoint = array(
            array(
                'tablabel'       =>  __('General', 'dc-woocommerce-multi-vendor'),
                'apiurl'         =>  'mvx_module/v1/save_dashpages',
                'description'    =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'icon'           =>  'icon-tab-general',
                'modulename'     =>  'settings-general'
            ),
            array(
                'tablabel'      =>  __('Social', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'icon-tab-social',
                'modulename'    =>  'social'
            ),
            array(
                'tablabel'      =>  __('Registration Form', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('Customise Your Own Seller Registration Form for Your Marketplace', 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'icon-tab-registration-form',
                'modulename'    => 'registration'
            ),
            array(
                'tablabel'      =>  __('Seller Dashbaord', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __("Manage the appearence of  Your Vendor's Dashboard", 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'icon-tab-seller-dashbaord',
                'modulename'    =>  'seller-dashbaord'
            ),
            array(
                'tablabel'      =>  __('Store', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __("Shows Customer The Location of a Particular Store or Vendor", 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'icon-tab-store',
                'modulename'    =>  'store'
            ),
            array(
                'tablabel'      =>  __('Products', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __("Shows Customer The Location of a Particular products or Vendor", 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'icon-tab-products',
                'modulename'    =>  'products'
            ),
            array(
                'tablabel'      =>  __('Products Capability', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __("Manage the Capabilities You Want Your Vendors to Have", 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'icon-tab-products-capability',
                'modulename'    =>  'products-capability'
            ),
            array(
                'tablabel'      =>  __('SPMV', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __("Let's Your Vendor Publish Fellow Vendor's Product as Theirs", 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'icon-tab-SPMV',
                'modulename'    =>  'spmv-pages'
            ),
            array(
                'tablabel'      =>  __('Commissions', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __("Configure Commission Settings to Customise Your Commission Plan", 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'icon-tab-commissions',
                'modulename'    =>  'commissions'
            ),
            array(
                'tablabel'      =>  __('Disbursement', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __('Manage Payment and Disbursement', 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'icon-tab-disbursement',
                'modulename'    =>  'disbursement'
            ),
            array(
                'tablabel'      =>  __('Policy', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __('Add Policies For Your Site', 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'icon-tab-policy',
                'modulename'    =>  'policy'
            ),
            array(
                'tablabel'      =>  __('Refunds', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'icon-tab-refunds',
                'modulename'    =>  'refund-management'
            ),
            array(
                'tablabel'      =>  __('Reviews & Rating', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __('Manage Settings For Product and Store Review', 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'icon-tab-reviews-and-rating',
                'modulename'    =>  'review-management'
            ),
        );


        $payment_page_endpoint = array(
            array(
                'tablabel'      =>  __('PayPal Masspay', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __('Schedule payment to multiple vendors at the same time', 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'payment-masspay'
            ),
            array(
                'tablabel'      =>  __('PayPal Payout', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __('Send payments automatically to multiple vendors as per scheduled', 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'payment-payout'
            ),
            array(
                'tablabel'      =>  __('Stripe Connect', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __('Connect to vendors stripe account and make hassle-free transfers as scheduled', 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'payment-stripe-connect'
            )
        );

        $advance_page_endpoint = array(
            array(
                'tablabel'      =>  __('Buddypress', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'buddypress'
            )
        );

        $analytics_page_endpoint = array(
            array(
                'tablabel'      =>  __('Overview', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('View the Overall Performance of The Site', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'admin-overview'
            ),
            array(
                'tablabel'      =>  __('Vendor', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('Get Reports on Vendor Sales', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'vendor'
            ),
            array(
                'tablabel'      =>  __('Product', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('View Porduct Sales', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'product'
            ),
            array(
                'tablabel'      =>  __('Transaction History', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('Get Detailed Reports On Vendor Commission', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'transaction-history'
            )
        );

        $marketplace_vendors = array(
            array(
                'tablabel'      =>  __('Personal', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'vendor-personal'
            ),
            array(
                'tablabel'      =>  __('Store', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'vendor-store'
            ),
            array(
                'tablabel'      =>  __('Social', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'vendor-social'
            ),
            array(
                'tablabel'      =>  __('Payment', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'vendor-payments'
            ),
            array(
                'tablabel'      =>  __('Vendor Application', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'vendor-application'
            ),
            array(
                'tablabel'      =>  __('Vendor Shipping', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'vendor-shipping'
            ),
            array(
                'tablabel'      =>  __('Vendor Followers', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'vendor-followers'
            ),
            array(
                'tablabel'      =>  __('Vendor Policy', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'vendor-policy'
            ),

        );

        $marketplace_workboard = array(
            array(
                'tablabel'      =>  __('Activity Reminder', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('Keeps track of all important marketplace chores', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'activity-reminder'
            ),
            array(
                'tablabel'      =>  __('Announcement', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('Announcements are visible only to vendors through the vendor dashboard(message section). You may use this section to broadcast your announcements.', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'announcement'
            ),
            array(
                'tablabel'      =>  __('Knowladgebase', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('"Knowledgebase" section is visible only to vendors through the vendor dashboard. You may use this section to onboard your vendors. Share tutorials, best practices, "how to" guides or whatever you feel is appropriate with your vendors.', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'knowladgebase'
            ),
            array(
                'tablabel'      =>  __('Store Review', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'store-review'
            ),
            array(
                'tablabel'      =>  __('Report Abuse', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'report-abuse'
            ),
            array(
                'tablabel'      =>  __('Question & Answer', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'icon'          =>  'ico-store-icon',
                'modulename'     =>  'question-ans'
            )
        );

        $mvx_all_backend_tab_list = array(
            'dashboard-page'                    => $dashboard_page_endpoint,
            'marketplace-advance-settings'      => $advance_page_endpoint,
            'marketplace-analytics'             => $analytics_page_endpoint,
            'marketplace-payments'              => $payment_page_endpoint,
            'marketplace-general-settings'      => $general_settings_page_endpoint,
            'marketplace-vendors'               => $marketplace_vendors,
            'marketplace-workboard'             => $marketplace_workboard
        );
        return $mvx_all_backend_tab_list;
    }
}

if (!function_exists('mvx_is_module_active')) {

    /**
     * check is module active
     * @return array
     */
    function mvx_is_module_active($module_name = '') {
        global $MVX;
        if (empty($module_name)) {
            return false;
        }
        return $MVX->vendor_rest_api->is_current_module_active($module_name);   
    }
}