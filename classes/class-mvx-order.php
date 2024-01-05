<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @class 		MVX Order Class
 *
 * @version		3.4.0
 * @package		MultivendorX
 * @author 		MultiVendorX
 */
class MVX_Order {

    public function __construct() {
        global $MVX, $PAGE;
        // Init MVX Vendor Order class
        $MVX->load_class('vendor-order');
        // Add extra vendor_id to shipping packages
        add_action('woocommerce_checkout_create_order_line_item', array(&$this, 'add_meta_date_in_order_line_item'), 10, 4);
        add_action('woocommerce_checkout_create_order_shipping_item', array(&$this, 'add_meta_date_in_shipping_package'), 10, 4);
        add_action('woocommerce_analytics_update_order_stats', array(&$this, 'woocommerce_analytics_remove_suborder'));
        
        if (is_mvx_version_less_3_4_0()) {
            
        } else {
            // filters order list table
            if($MVX->hpos_is_enabled){
                add_action('current_screen', function($screen) {
                    if('woocommerce_page_wc-orders' === $screen->id) {
                        add_filter( 'woocommerce_orders_table_query_clauses', array($this, 'wc_order_list_filter') );
                    }
                });
            } else { // before wc version 8.3.0
                add_filter('request', array($this, 'wc_order_list_filter_old'), 10, 1);
            }
            
            add_action('admin_head', array($this, 'count_processing_order'), 5);
            add_filter('admin_body_class', array( $this, 'add_admin_body_class' ));
            add_filter('views_edit-shop_order', array($this, 'shop_order_statuses_get_views') );
            add_filter('wp_count_posts', array($this, 'shop_order_count_orders'), 99, 3 );
            // customer's order list (my account)
            add_filter('woocommerce_my_account_my_orders_query', array($this, 'woocommerce_my_account_my_orders_query'), 99);
            add_filter('woocommerce_account_orders_columns', array($this, 'woocommerce_my_account_my_orders_columns'), 99);
            add_action('woocommerce_my_account_my_orders_column_mvx_suborder', array($this, 'woocommerce_my_account_my_orders_column_mvx_suborder'), 99);
            add_filter( 'woocommerce_customer_available_downloads', array($this, 'woocommerce_customer_available_downloads'), 99);
            add_action('mvx_frontend_enqueue_scripts', array($this, 'mvx_frontend_enqueue_scripts'));
            if( !is_user_mvx_vendor( get_current_user_id() ) ) {
                if($MVX->hpos_is_enabled){
                    add_filter('manage_woocommerce_page_wc-orders_columns', array($this, 'mvx_shop_order_columns'), 99);
                    add_action('manage_woocommerce_page_wc-orders_custom_column', array($this, 'mvx_show_shop_order_columns'), 99, 2);
                } else { // before wc version 8.3.0
                    add_filter('manage_shop_order_posts_columns', array($this, 'mvx_shop_order_columns'), 99);
                    add_action('manage_shop_order_posts_custom_column', array($this, 'mvx_show_shop_order_columns'), 99, 2);
                }
            }
            if(apply_filters('mvx_parent_order_to_vendor_order_status_synchronization', true))
                add_action('woocommerce_order_status_changed', array($this, 'mvx_parent_order_to_vendor_order_status_synchronization'), 90, 4);
            if(apply_filters('mvx_vendor_order_to_parent_order_status_synchronization', true))
                add_action('woocommerce_order_status_changed', array($this, 'mvx_vendor_order_to_parent_order_status_synchronization'), 99, 4);
            // MVX create orders
            add_action('woocommerce_saved_order_items', array(&$this, 'mvx_create_orders_from_backend'), 10, 2 );
            if(version_compare(WC_VERSION, '8.3.0', '>=')){
                add_action('woocommerce_new_order', array(&$this, 'mvx_create_orders_hpos'), 10, 2);
            } else { // before wc version 8.3.0
                add_action('woocommerce_checkout_order_processed', array(&$this, 'mvx_create_orders'), 10, 3);
            }
            add_action('woocommerce_after_checkout_validation', array($this, 'mvx_check_order_awaiting_payment'));
            add_action( 'woocommerce_rest_insert_shop_order_object',array($this,'mvx_create_orders_via_rest_callback'), 10, 3 );
            // Add product for sub order
            add_action( 'woocommerce_ajax_order_items_added',array($this, 'woocommerce_ajax_order_items_added'), 10, 2 );
            add_action( 'woocommerce_before_delete_order_item',array($this, 'woocommerce_before_delete_order_item') );
            // Order Refund
            add_action('woocommerce_order_refunded', array($this, 'mvx_order_refunded'), 10, 2);
            add_action('woocommerce_refund_deleted', array($this, 'mvx_refund_deleted'), 10, 2);
            // Customer Refund request
            add_action( 'woocommerce_order_details_after_order_table', array( $this, 'mvx_refund_btn_customer_my_account'), 10 );
            add_action( 'wp', array( $this, 'mvx_handler_cust_requested_refund' ) );
            add_action( 'add_meta_boxes', array( $this, 'mvx_refund_order_status_customer_meta' ) );
            add_action( 'save_post', array( $this, 'mvx_refund_order_status_save' ) );
            $this->init_prevent_trigger_vendor_order_emails();
            // Order Trash 
            add_action( 'trashed_post', array( $this, 'trash_mvx_suborder' ), 10, 1 );
            // Order Delete 
            add_action( 'before_delete_post', array( $this, 'delete_mvx_suborder' ), 10, 1 );
            // Restrict default order edit caps for vendor
            add_action( 'admin_enqueue_scripts', array( $this, 'mvx_vendor_order_backend_restriction' ), 99 );
            add_action( 'add_meta_boxes', array( $this, 'remove_meta_boxes' ), 99 );
            add_action( 'admin_menu', array( $this, 'remove_admin_menu' ), 99 );
            // restrict stock managements for sub-orders
            add_filter( 'woocommerce_can_reduce_order_stock', array($this, 'woocommerce_can_reduce_order_stock'), 99, 2 );
            add_filter( 'woocommerce_hidden_order_itemmeta', array($this, 'woocommerce_hidden_order_itemmeta'), 99 );
            add_filter( 'woocommerce_order_item_get_formatted_meta_data', array($this, 'woocommerce_hidden_order_item_get_formatted_meta_data'), 99 );
            add_action( 'woocommerce_order_status_changed', array($this, 'mvx_vendor_order_status_changed_actions'), 99, 4 );
            add_action( 'woocommerce_rest_shop_order_object_query', array($this, 'mvx_exclude_suborders_from_rest_api_call'), 99, 2 );
            add_filter( "woocommerce_rest_shop_order_object_query", array($this, 'mvx_suborder_hide' ), 99 , 2 );
            // customer list report section
            add_filter( "woocommerce_customer_get_total_spent_query", array($this, 'woocommerce_customer_exclude_suborder_query' ), 10 , 2 );
            //refund table action
            $this->mvx_refund_table_action();
        }
    }

    /**
     * Add order line item meta
     *
     * @param item_id, cart_item
     * @return void 
     */

    public function add_meta_date_in_order_line_item($item, $item_key, $values, $order) {
        if ( $order &&  $order->get_parent_id() == 0 || (function_exists('wcs_is_subscription') && wcs_is_subscription( $order )) ) {
            $general_cap = apply_filters('mvx_sold_by_text', __('Sold By', 'multivendorx'));
            $vendor = get_mvx_product_vendors($item['product_id']);
            if ($vendor) {
                $item->add_meta_data($general_cap, $vendor->page_title);
                $item->add_meta_data('_vendor_id', $vendor->id);
            }
        }
    }

    /**
     * 
     * @param object $item
     * @param sting $package_key as $vendor_id
     */
    public function add_meta_date_in_shipping_package($item, $package_key, $package, $order) {
        $vendor_id = ( isset( $package['vendor_id'] ) && $package['vendor_id'] ) ? $package['vendor_id'] : $package_key;
        if (!mvx_get_order($order->get_id()) && is_user_mvx_vendor($vendor_id)) {
            $item->add_meta_data('vendor_id', $vendor_id, true);
            $package_qty = array_sum(wp_list_pluck($package['contents'], 'quantity'));
            $item->add_meta_data('package_qty', $package_qty, true);
            do_action('mvx_add_shipping_package_meta');
        }
    }

    /**
     * 
     * Woocommerce admin dashboard restrict dual order report 
     */
    public function woocommerce_analytics_remove_suborder($order_id){
        global $wpdb;
        $order = wc_get_order($order_id);
        if ($order->get_parent_id()) {
            $wpdb->delete( $wpdb->prefix.'wc_order_stats', array( 'order_id' => $order_id ) );
            \WC_Cache_Helper::get_transient_version( 'woocommerce_reports', true );
        }
        // Only for version 3.5.4
        $post_id = $wpdb->get_results("SELECT order_id FROM {$wpdb->prefix}wc_order_stats WHERE (parent_id != 0)");
        if (!empty($post_id)) {
           foreach ($post_id as $key => $value) {
                $wpdb->delete( $wpdb->prefix.'wc_order_stats', array( 'order_id' => $value->order_id ) );
                \WC_Cache_Helper::get_transient_version( 'woocommerce_reports', true );
            } 
        }
    }
    public function wc_order_list_filter_old($query) {
        global $typenow;
        $user = wp_get_current_user();
        if ('shop_order' == $typenow) {
            if (current_user_can('administrator') && empty($_REQUEST['s'])) {
                $query['post_parent'] = 0;
            } elseif (current_user_can('shop_manager') && empty($_REQUEST['s'])) {
                $query['post_parent'] = 0;
            } elseif(in_array('dc_vendor', $user->roles)) {
                $query['author'] = $user->ID;
            }
            return apply_filters("mvx_shop_order_query_request", $query);
        }
        return $query;
    }

    public function wc_order_list_filter($where_cloas) {
        global $wpdb;
        $where_cloas['where'] .= ' AND ' . $wpdb->prefix . 'wc_orders.parent_order_id = 0';
        return $where_cloas;
    }
    
    public function init_prevent_trigger_vendor_order_emails(){
        $prevent_vendor_order_emails = apply_filters('mvx_prevent_vendor_order_emails_trigger', array(
            'recipient' => array(
                'cancelled_order',
                ),
            'enabled' => array(
                'customer_on_hold_order', 
                'customer_processing_order', 
                'customer_refunded_order', 
                'customer_partially_refunded_order', 
                'customer_completed_order',
                ),
            'disabled' => array(
                'new_order',
                'customer_on_hold_order',
                'customer_processing_order',
            )
        ));
        if($prevent_vendor_order_emails) :
            foreach ($prevent_vendor_order_emails as $prevent => $email_ids) {
                switch ($prevent) {
                    case 'recipient':
                        if($email_ids){
                            foreach ($email_ids as $email_id) {
                                add_filter( 'woocommerce_email_recipient_'.$email_id, array($this, 'woocommerce_email_recipient'), 99, 2 );
                            }
                        }
                        break;
                    case 'enabled':
                        if($email_ids){
                            foreach ($email_ids as $email_id) {
                                add_filter( 'woocommerce_email_enabled_'.$email_id, array($this, 'woocommerce_email_enabled'), 99, 2 );
                            }
                        }
                        break;
                    case 'disabled':
                        if($email_ids){
                            foreach ($email_ids as $email_id) {
                                add_filter( 'woocommerce_email_enabled_'.$email_id, array($this, 'woocommerce_email_disabled'), 99, 2 );
                            }
                        }
                        break;
                    default:
                        do_action('mvx_prevent_vendor_order_emails_trigger_action', $email_ids, $prevent);
                        break;
                }
            }
        endif;
    }
    
    public function woocommerce_email_recipient($recipient, $object ){
        if(!$object) return $recipient;
        $is_migrated_order = $object->get_meta('_order_migration', true);
        if($is_migrated_order) return false;
        return $object instanceof WC_Order && $object->get_parent_id() ? false : $recipient;
    }
    
    public function woocommerce_email_disabled($enabled, $object ){
        if(!$object) return $enabled;
        $is_vendor_order = ($object) ? mvx_get_order($object->get_id()) : false;
        $is_migrated_order = $object->get_meta('_order_migration', true);
        if($is_migrated_order) return false;
        return $object instanceof WC_Order && $object->get_parent_id() && $is_vendor_order ? false : $enabled;
    }
    
    public function woocommerce_email_enabled($enabled, $object ){
        if(!$object) return $enabled;
        $is_vendor_order = ($object) ? mvx_get_order($object->get_id()) : false;
        $is_migrated_order = $object->get_meta('_order_migration', true);
        if($is_migrated_order) return false;
        
        if ( $object instanceof WC_Order && $object->get_parent_id() && $is_vendor_order ) return $enabled;

        return $enabled;
    }

    public function mvx_shop_order_columns($columns) {

        $order_title_number = version_compare(WC_VERSION, '3.3.0', '>=') ? 'order_number' : 'order_title';
        if ((!isset($_GET['post_status']) || ( isset($_GET['post_status']) && 'trash' != $_GET['post_status'] ))) {
            $suborder = array('mvx_suborder' => __('Suborders', 'multivendorx'));
            $title_number_pos = array_search($order_title_number, array_keys($columns));
            $columns = array_slice($columns, 0, $title_number_pos + 1, true) + $suborder + array_slice($columns, $title_number_pos + 1, count($columns) - 1, true);
        }
        return $columns;
    }

    /**
     * Output custom columns for orders
     *
     * @param  string $column
     */
    public function mvx_show_shop_order_columns($column, $post_id) {
        global $MVX;
        remove_filter( 'woocommerce_orders_table_query_clauses', array($this, 'wc_order_list_filter') );
        switch ($column) {
                case 'mvx_suborder' :
                $mvx_suborders = get_mvx_suborders($post_id);
                if ($mvx_suborders) {
                    echo '<ul class="mvx-order-vendor" style="margin:0px;">';
                    foreach ($mvx_suborders as $suborder) {
                        if($suborder->get_type() == 'shop_order_refund') continue;
                        $vendor = get_mvx_vendor($suborder->get_meta( '_vendor_id', true ));
                        $vendor_page_title = ($vendor) ? $vendor->page_title : __('Deleted vendor', 'multivendorx');
                        if($MVX->hpos_is_enabled){
                            $order_uri = apply_filters('mvx_admin_vendor_shop_order_edit_url', esc_url('admin.php?page=wc-orders&action=edit&id=' . $suborder->get_id() . ''), $suborder->get_id());
                        } else {
                            $order_uri = apply_filters('mvx_admin_vendor_shop_order_edit_url', esc_url('post.php?post=' . $suborder->get_id() . '&action=edit'), $suborder->get_id());
                        }
                        printf('<li><mark class="%s tips" data-tip="%s">%s</mark> <strong><a href="%s">#%s</a></strong> &ndash; <small class="mvx-order-for-vendor">%s %s</small></li>', sanitize_title($suborder->get_status()), $suborder->get_status(), $suborder->get_status(), $order_uri, $suborder->get_order_number(), _x('for', 'Order table details', 'multivendorx'), $vendor_page_title
                        );

                        do_action('mvx_after_suborder_details', $suborder);
                    }
                    echo '<ul>';
                } else {
                    echo '<span class="na">&ndash;</span>';
                }
                break;
        }
    }
    
    public function mvx_create_orders_hpos($order_id, $order) {
        $posted_data = array();
        $this->mvx_create_orders($order_id, $posted_data, $order, false);
        
    }

    public function mvx_create_orders($order_id, $posted_data, $order, $backend = false) {
        global $MVX;
        //check parent order exist
        if ($order->get_parent_id() != 0)
            return false;

        // $order = wc_get_order($order_id);
        $items = $order->get_items();
        $vendor_items = array();
        foreach ($items as $item_id => $item) {
            if (isset($item['product_id']) && $item['product_id'] !== 0) {
                // check vendor product
                $has_vendor = get_mvx_product_vendors($item['product_id']);
                if ($has_vendor) {
                    $variation_id = isset($item['variation_id']) && !empty($item['variation_id']) ? $item['variation_id'] : 0;
                    $variation = isset($item['variation']) && !empty($item['variation']) ? $item['variation'] : array();
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
        }
        // if there is no vendor available
        if (count($vendor_items) == 0)
            return false;
        // update parent order meta
        $order->update_meta_data('has_mvx_sub_order', true);
        $order->save();
        $vendor_orders = array();
        foreach ($vendor_items as $vendor_id => $items) {
            if (!empty($items)) {
                $vendor_orders[] = self::create_vendor_order($order, array(
                            'order_id' => $order_id,
                            'vendor_id' => $vendor_id,
                            'posted_data' => $posted_data,
                            'line_items' => $items
                ), $backend);
            }
        }
        if ($vendor_orders) :
            foreach ($vendor_orders as $vendor_order_id) {
                do_action('mvx_checkout_vendor_order_processed', $vendor_order_id, $posted_data, $order);
            }
        endif;
    }
    
    public function mvx_create_orders_from_backend( $order_id, $items ){
        $this->mvx_manually_create_order_item_and_suborder($order_id, $items, false);
    }
    
    public function mvx_manually_create_order_item_and_suborder( $order_id = 0, $items = '', $is_sub_create = false ) {
        $order = wc_get_order($order_id);
        if(!$order) return;

        $items = $order->get_items();
        foreach ($items as $key => $value) {
            if ( $order || (function_exists('wcs_is_subscription') && wcs_is_subscription( $order )) ) {
                $general_cap = apply_filters('mvx_sold_by_text', __('Sold By', 'multivendorx'));
                $vendor = get_mvx_product_vendors($value['product_id']);
                if ($vendor) {
                    if ( !wc_get_order_item_meta( $key, '_vendor_id' ) ) 
                        wc_add_order_item_meta($key, '_vendor_id', $vendor->id);
                    
                    if ( !wc_get_order_item_meta( $key, $general_cap ) ) 
                        wc_add_order_item_meta($key, $general_cap, $vendor->page_title);
                }
            }
        }
        
        // $has_sub_order = $order->get_meta( 'has_mvx_sub_order', true) ? true : false;
        $suborders = get_mvx_suborders( $order_id, false, false);
        if ($is_sub_create) {
            if ($suborders) {
                foreach ( $suborders as $v_order_id ) {
                    wp_delete_post($v_order_id, true);
                }
            }
            $this->mvx_create_orders($order_id, array(), $order, true);
        }
    }

    public function mvx_create_orders_via_rest_callback( $order, $request, $creating ) {
        global $MVX;
        $items = $order->get_items();
        foreach ($items as $key => $value) {
            if ( $order && $order->get_parent_id() == 0 || (function_exists('wcs_is_subscription') && wcs_is_subscription( $order )) ) {
                $general_cap = apply_filters('mvx_sold_by_text', __('Sold By', 'multivendorx'));
                $vendor = get_mvx_product_vendors($value['product_id']);
                if ($vendor) {
                    wc_add_order_item_meta($key, '_vendor_id', $vendor->id);
                    wc_add_order_item_meta($key, $general_cap, $vendor->page_title);
                }
            }
        }
        if( $order && $order->get_meta('_created_via', true ) !== 'rest-api' ) return;
        $this->mvx_create_orders($order->get_id(), array(), $order, true);
    }

    public function woocommerce_ajax_order_items_added( $added_items, $order ) {
        foreach ( $added_items as $item_id => $item_data ) {
            $parent_order = wc_get_order( $order->get_parent_id() );
            $suborder_id = false;
            $suborders = get_mvx_suborders($order->get_id());
            if (!empty($suborders)) {
                foreach ($suborders as $key_order => $value_order) {
                    $vendor_of_order = $value_order->get_meta('_vendor_id', true );
                    if ($vendor_of_order == get_mvx_product_vendors($item_data->get_product_id())->id) {
                        $suborder_id = $value_order->get_id();
                    }
                }
            }
            $item = new WC_Order_Item_Product();
            $product = wc_get_product( $item_data->get_product_id() );
            $item->set_props(
                array(
                    'quantity'     => $item_data->get_quantity(),
                    'variation'    => $item_data->get_variation_id(),
                    'subtotal'     => $item_data->get_subtotal(),
                    'total'        => $item_data->get_total(),
                    'subtotal_tax' => $item_data->get_subtotal_tax(),
                    'total_tax'    => $item_data->get_total_tax(),
                    'taxes'        => $item_data->get_taxes(),
                    )
                );
            if ( $product ) {
                $item->set_props(
                    array(
                        'name'         => $product->get_name(),
                        'tax_class'    => $product->get_tax_class(),
                        'product_id'   => $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id(),
                        'variation_id' => $product->is_type( 'variation' ) ? $product->get_id() : 0,
                        )
                    );
            }
            $item->set_backorder_meta();
            if ($parent_order) {
                $parent_order->add_item( $item );
                $parent_order->save();
                $parent_order->calculate_totals();
            } elseif ($suborders && $suborder_id) {
                $suborder_object = wc_get_order( $suborder_id );
                $suborder_object->add_item( $item );
                $suborder_object->save();
                $suborder_object->calculate_totals();
            }
        }
    }
    
    public function woocommerce_before_delete_order_item( $item_id ) {
        global $MVX;
        $parent_item_id = $MVX->order->get_vendor_parent_order_item_id($item_id);
        if ($parent_item_id) {
            wc_delete_order_item( $parent_item_id );
        }
    }
    /**
     * Create a new vendor order programmatically
     *
     * Returns a new vendor_order object on success which can then be used to add additional data.
     *
     * @since 
     * @param array $args
     * @param boolean $data_migration (default: false) for data migration
     * @return MVX_Order|WP_Error
     */
    public static function create_vendor_order($order, $args = array(), $data_migration = false) {
        global $MVX;
        
        $default_args = array(
            'vendor_id' => 0,
            'order_id' => 0,
            'posted_data' => array(),
            'vendor_order_id' => 0,
            'line_items' => array()
        );

        $args = wp_parse_args($args, $default_args);
        // $order = wc_get_order($args['order_id']);
        if (!$order) return false;
        $data = array();

        if ($args['vendor_order_id'] > 0) {
            $updating = true;
            $data['ID'] = $args['vendor_order_id'];
        } else {
            $updating = false;
            $data = apply_filters('mvx_create_vendor_order_new_order', array(
                'post_date' => gmdate('Y-m-d H:i:s', $order->get_date_created('edit')->getOffsetTimestamp()),
                'post_date_gmt' => gmdate('Y-m-d H:i:s', $order->get_date_created('edit')->getTimestamp()),
                'post_type' => 'shop_order',
                'post_status' => 'wc-' . ( $order->get_status('edit') ? $order->get_status('edit') : apply_filters('mvx_create_vendor_order_default_order_status', 'pending') ),
                'ping_status' => 'closed',
                'post_author' => absint($args['vendor_id']),
                'post_title' => sprintf(__('Vendor Order &ndash; %s', 'multivendorx'), date(_x('F j, Y @ h:i a', 'Commission date parsed by date', 'multivendorx'), current_time('timestamp'))),
                'post_password' => uniqid('mvx_order_'),
                'post_parent' => absint($args['order_id']),
                'post_excerpt' => isset($args['posted_data']['order_comments']) ? $args['posted_data']['order_comments'] : '',
                    )
            );
        }

        if ($updating) {
            $vendor_order_id = wp_update_post($data);
        } else {
            if($MVX->hpos_is_enabled){
                $vendor_order_id = insert_mvx_vendor_order_data($order, $args['vendor_id']);
            } else {
                $vendor_order_id = wp_insert_post($data, true);
            }
            $args['vendor_order_id'] = $vendor_order_id;
        }

        if (is_wp_error($vendor_order_id)) {
            return $vendor_order_id;
        }

        $vendor_order = wc_get_order($vendor_order_id);

        $checkout_fields = array();
        if( !$data_migration ){
            $wc_checkout = WC()->checkout();
            $checkout_fields = !is_admin() && !wp_doing_ajax() ? $wc_checkout->checkout_fields : array();
        }
        
        self::create_mvx_order_line_items($vendor_order, $args);
        if( $data_migration ){
            self::create_mvx_order_shipping_lines($vendor_order, array(), array(), $args, $data_migration);
            self::create_mvx_order_coupon_lines( $vendor_order, array(), $args );
        }else{
            self::create_mvx_order_shipping_lines($vendor_order, WC()->session->get('chosen_shipping_methods'), WC()->shipping->get_packages(), $args, $data_migration);
            self::create_mvx_order_coupon_lines( $vendor_order, WC()->cart, $args );
        }
        
        //self::create_mvx_order_tax_lines( $vendor_order, $args );
        // Add customer checkout fields data to vendor order
        if (empty($checkout_fields)) {
            $types = array('billing', 'shipping');
            foreach ($types as $type) {
                $vendor_order->set_address($order->get_address($type), $type);
            }
        }

        if (!empty($wc_checkout)) {
            foreach ($checkout_fields as $section => $checkout_meta_keys) {
                if ('account' != $section) {
                    foreach ($checkout_meta_keys as $order_meta_key => $order_meta_values) {
                        $meta_key = 'shipping' == $section || 'billing' == $section ? '_' . $order_meta_key : $order_meta_key;
                        $meta_value_to_save = isset($args['posted_data'][$order_meta_key]) ? $args['posted_data'][$order_meta_key] : $order->get_meta(  $meta_key, true);
                        $vendor_order->update_meta_data($meta_key, $meta_value_to_save);
                        $vendor_order->save();
                    }
                }
            }
        }
        // Add vendor order meta data
        $order_meta = apply_filters('mvx_vendor_order_metas', array(
            '_payment_method',
            '_payment_method_title',
            '_customer_user',
            '_prices_include_tax',
            '_order_currency',
            '_order_key',
            '_customer_ip_address',
            '_customer_user_agent',
        ));

        foreach ($order_meta as $key) {
            $vendor_order->update_meta_data($key, $order->get_meta( $key, true));
        }

        $vendor_order->update_meta_data('_mvx_order_version', $MVX->version);
        $vendor_order->update_meta_data('_vendor_id', absint($args['vendor_id']));
        $vendor_order->update_meta_data('_created_via', 'mvx_vendor_order');
        if($data_migration)
            $vendor_order->update_meta_data('_order_migration', true);

        /**
         * Action hook to adjust order before save.
         *
         * @since 3.4.0
         */
        do_action('mvx_checkout_create_order', $order, $vendor_order, $args);

        // Save the order.
        $v_order_id = $vendor_order->save();
        $vendor_order = wc_get_order($v_order_id);
        do_action('mvx_checkout_update_order_meta', $v_order_id, $args);
        $vendor_order->calculate_totals();
        return $v_order_id;
    }

    /**
     * Add line items to the order.
     *
     * @param WC_Order $order Order instance.
     * @param WC_Cart  $cart  Cart instance.
     */
    public static function create_mvx_order_line_items($order, $args) {
        $line_items = $args['line_items'];
        $commission_rate_items = array();
        foreach ($line_items as $order_item) {
            if (isset($order_item['product_id']) && $order_item['product_id'] !== 0) {
                $item = new WC_Order_Item_Product();
                $product = wc_get_product($order_item['product_id']);

                $item->set_props(
                        array(
                            'quantity' => $order_item['quantity'],
                            'variation' => $order_item['variation'],
                            'subtotal' => $order_item['line_subtotal'],
                            'total' => $order_item['line_total'],
                            'subtotal_tax' => $order_item['line_subtotal_tax'],
                            'total_tax' => $order_item['line_tax'],
                            'taxes' => $order_item['line_tax_data'],
                        )
                );

                if ($product) {
                    $item->set_props(
                            array(
                                'name' => $order_item->get_name(),
                                'tax_class' => $order_item->get_tax_class(),
                                'product_id' => $order_item->get_product_id(),
                                'variation_id' => $order_item->get_variation_id(),
                            )
                    );
                }

                $item->set_backorder_meta();
                $item->add_meta_data('_vendor_order_item_id', $item->get_product_id());
                // Add commission data
                $item->add_meta_data('_vendor_item_commission', $order_item['commission']);
                
                $metadata = $order_item->get_meta_data();
                if ( $metadata ) {
                    foreach ( $metadata as $meta ) {
                        $item->add_meta_data( $meta->key, $meta->value );
                    }
                }

//                $item->add_meta_data('_vendor_id', $args['vendor_id']);
//                // BW compatibility with old meta.
//                $vendor = get_mvx_vendor($args['vendor_id']);
//                $general_cap = apply_filters('mvx_sold_by_text', __('Sold By', 'multivendorx'));
//                $item->add_meta_data($general_cap, $vendor->page_title);


                do_action('mvx_vendor_create_order_line_item', $item, $item->get_product_id(), $order_item, $order);
                // Add item to order and save.
                $order->add_item($item);
                // temporary commission rate save with order_item_id
                if(isset($order_item['commission_rate']) && $order_item['commission_rate'])
                    $commission_rate_items[$item->get_product_id()] = $order_item['commission_rate'];
            }
        }
        /**
         * Temporary commission rates save for vendor order.
         *
         * @since 3.1.2.0
         */
        $order->update_meta_data('order_items_commission_rates', $commission_rate_items);
        $order->save();
    }

    /**
     * Add shipping lines to the order.
     *
     * @param WC_Order $order                   Order Instance.
     * @param array    $chosen_shipping_methods Chosen shipping methods.
     * @param array    $packages                Packages.
     */
    public static function create_mvx_order_shipping_lines($order, $chosen_shipping_methods, $packages, $args = array(), $migration = false) {
        $vendor_id = isset($args['vendor_id']) ? $args['vendor_id'] : 0;
        $parent_order_id = isset($args['order_id']) ? $args['order_id'] : 0;

        if(!$migration){
        
            foreach ($packages as $package_key => $package) {
                $pkg_vendor_id = ( isset( $package['vendor_id'] ) && $package['vendor_id'] ) ? $package['vendor_id'] : $package_key;
                if ($pkg_vendor_id == $vendor_id && isset($chosen_shipping_methods[$package_key], $package['rates'][$chosen_shipping_methods[$package_key]])) {
                    $shipping_rate = $package['rates'][$chosen_shipping_methods[$package_key]];
                    $item = new WC_Order_Item_Shipping();
                    $item->legacy_package_key = $pkg_vendor_id; // @deprecated For legacy actions.
                    $item->set_props(
                            array(
                                'method_title' => $shipping_rate->label,
                                'method_id' => $shipping_rate->method_id,
                                'instance_id' => $shipping_rate->instance_id,
                                'total' => wc_format_decimal($shipping_rate->cost),
                                'taxes' => array(
                                    'total' => $shipping_rate->taxes,
                                ),
                            )
                    );

                    foreach ($shipping_rate->get_meta_data() as $key => $value) {
                        $item->add_meta_data($key, $value, true);
                    }

                    $item->add_meta_data('vendor_id', $pkg_vendor_id, true);
                    $package_qty = array_sum(wp_list_pluck($package['contents'], 'quantity'));
                    $item->add_meta_data('package_qty', $package_qty, true);
                    // add parent item_id in meta
                    $parent_shipping_item_id = get_vendor_parent_shipping_item_id( $parent_order_id, $vendor_id );
                    if( $parent_shipping_item_id ) $item->add_meta_data('_vendor_order_shipping_item_id', $parent_shipping_item_id );
                    
                    /**
                     * Action hook to adjust item before save.
                     *
                     * @since 3.4.0
                     */
                    do_action('mvx_vendor_create_order_shipping_item', $item, $package_key, $package, $order);

                    // Add item to order and save.
                    $order->add_item($item);
                }
            }
        }else{
            // Backward compatibilities for MVX old orders
            $parent_order = wc_get_order($parent_order_id);
            if($parent_order){
                $shipping_items = $parent_order->get_items('shipping');
                
                foreach ($shipping_items as $item_id => $item) {
                    $shipping_vendor_id = $item->get_meta('vendor_id', true);
                    if($shipping_vendor_id == $vendor_id){
                        $shipping = new WC_Order_Item_Shipping();
                        $shipping->set_props(
                                array(
                                    'method_title' => $item['method_title'],
                                    'method_id' => $item['method_id'],
                                    'instance_id' => $item['instance_id'],
                                    'total' => wc_format_decimal($item['total']),
                                    'taxes' => $item['taxes'],
                                )
                        );

                        foreach ($item->get_meta_data() as $key => $value) {
                            $shipping->add_meta_data($key, $value, true);
                        }

                        $shipping->add_meta_data('vendor_id', $vendor_id, true);
                        $package_qty = $item->get_meta('package_qty', true);
                        $shipping->add_meta_data('package_qty', $package_qty, true);
                        // add parent item_id in meta
                        $item->add_meta_data('_vendor_order_shipping_item_id', $item_id );
                        $order->add_item($shipping);
                    }
                }
            }
        }
    }
    
    /**
     * Add coupon lines to the order.
     *
     * @param WC_Order $order Order Instance.
     * @param WC_Cart  $cart  Cart instance.
     * @param MVX Order $args  Arguments.
     */
    public static function create_mvx_order_coupon_lines( $order, $cart, $args ) {
        // Find cart products
        $cart_product_ids = array();
        if ( $order && $order->get_items() ) :
            foreach ( $order->get_items() as $item_id => $item_values ) {
                $cart_product_ids[] = $item_values->get_product_id();
            }
        endif;
        if( $cart && $cart->get_coupons() ) :
            foreach ( $cart->get_coupons() as $code => $coupon ) {
                if( !in_array( $coupon->get_discount_type(), apply_filters( 'mvx_order_available_coupon_types', array( 'fixed_product', 'percent', 'fixed_cart' ), $order, $cart ) ) ) continue;
                $coupon_products = get_post_meta( $coupon->get_id(), 'product_ids', true ) ? explode(",", get_post_meta( $coupon->get_id(), 'product_ids', true ) ) : array();
                if (!empty($coupon_products)) {
                    $match_coupon_product = array_intersect($cart_product_ids, $coupon_products);
                    if (!$match_coupon_product) continue;             
                }
                $item = new WC_Order_Item_Coupon();
                $item->set_props(
                    array(
                        'code'         => $code,
                        'discount'     => $cart->get_coupon_discount_amount( $code ),
                        'discount_tax' => $cart->get_coupon_discount_tax_amount( $code ),
                    )
                );
                // Avoid storing used_by - it's not needed and can get large.
                $coupon_data = $coupon->get_data();
                unset( $coupon_data['used_by'] );
                $item->add_meta_data( 'coupon_data', $coupon_data );
                /**
                 * Action hook to adjust item before save.
                 *
                 * @since 3.4.3
                 */
                do_action( 'mvx_checkout_create_order_coupon_item', $item, $code, $coupon, $order, $args );
                // Add item to order and save.
                $order->add_item( $item );
            }
        endif;
    }

    /**
     * Add tax lines to the order.
     *
     * @param WC_Order $order Order instance.
     * @param WC_Cart  $cart  Cart instance.
     */
    public static function create_mvx_order_tax_lines($order, $vendor_order_data) {
        $line_items = $vendor_order_data['line_items'];
        $item_total_tax = 0;
        foreach ($line_items as $item_id => $order_item) {
            $item_total_tax += (float) $order_item['line_total'];
        }


        foreach (array_keys($cart->get_cart_contents_taxes() + $cart->get_shipping_taxes() + $cart->get_fee_taxes()) as $tax_rate_id) {
            if ($tax_rate_id && apply_filters('woocommerce_cart_remove_taxes_zero_rate_id', 'zero-rated') !== $tax_rate_id) {
                $item = new WC_Order_Item_Tax();
                $item->set_props(
                        array(
                            'rate_id' => $tax_rate_id,
                            'tax_total' => $cart->get_tax_amount($tax_rate_id),
                            'shipping_tax_total' => $cart->get_shipping_tax_amount($tax_rate_id),
                            'rate_code' => WC_Tax::get_rate_code($tax_rate_id),
                            'label' => WC_Tax::get_rate_label($tax_rate_id),
                            'compound' => WC_Tax::is_compound($tax_rate_id),
                        )
                );

                /**
                 * Action hook to adjust item before save.
                 *
                 * @since 3.0.0
                 */
                do_action('woocommerce_checkout_create_order_tax_item', $item, $tax_rate_id, $order);

                // Add item to order and save.
                $order->add_item($item);
            }
        }
    }

    public function mvx_parent_order_to_vendor_order_status_synchronization($order_id, $old_status, $new_status, $order) {
        global $MVX;
        if(!$order_id) return;
        // Check order have status
        if (empty($new_status)) {
            $new_status = $order->get_status('edit');
        }    
        $status_to_sync = apply_filters('mvx_parent_order_to_vendor_order_statuses_to_sync',array('on-hold', 'pending', 'processing', 'cancelled', 'failed'));
        if( in_array($new_status, $status_to_sync) ) :
            if ($order->get_parent_id() || $order->get_meta( 'mvx_vendor_order_status_synchronized', true))
                return false;
            
            remove_action( 'woocommerce_order_status_completed', 'wc_paying_customer' );
            // Check if order have sub-order
            $mvx_suborders = get_mvx_suborders($order_id);
            if ($mvx_suborders) {
                foreach ($mvx_suborders as $suborder) {
                    $suborder->update_status($new_status, _x('Update via parent order: ', 'Order note', 'multivendorx'));
                }
                $order->update_meta_data('mvx_vendor_order_status_synchronized', true);
                $order->save();
                add_action( 'woocommerce_order_status_completed', 'wc_paying_customer' );
            }
        endif;
    }
    
    public function mvx_vendor_order_to_parent_order_status_synchronization($order_id, $old_status, $new_status, $order){
        $is_vendor_order = ($order_id) ? mvx_get_order($order_id) : false;
        if ($is_vendor_order && current_user_can('administrator') && $new_status != $old_status && apply_filters('mvx_vendor_notified_when_admin_change_status', true)) {
            $email_admin = WC()->mailer()->emails['WC_Email_Admin_Change_Order_Status'];
            $vendor_id = $order->get_meta( '_vendor_id', true);
            $vendor = get_mvx_vendor($vendor_id);
            $email_admin->trigger($order_id, $new_status, $vendor);
        }
        // parent order synchronization
        $parent_order_id = $order->get_parent_id();
        if($parent_order_id){
            remove_action('woocommerce_order_status_changed', array($this, 'mvx_parent_order_to_vendor_order_status_synchronization'), 90, 4);
            $status_to_sync = apply_filters('mvx_vendor_order_to_parent_order_statuses_to_sync',array('completed', 'refunded'));

            $mvx_suborders = get_mvx_suborders( $parent_order_id );
            $new_status_count  = 0;
            $suborder_count    = count( $mvx_suborders );
            $suborder_statuses = array();
            $suborder_totals = 0;
            foreach ( $mvx_suborders as $suborder ) {
                $suborder_totals += $suborder->get_total();
                $suborder_status = $suborder->get_status( 'edit' );
                if ( $new_status == $suborder_status ) {
                    $new_status_count ++;
                }

                if ( ! isset( $suborder_statuses[ $suborder_status ] ) ) {
                    $suborder_statuses[ $suborder_status ] = 1;
                } else {
                    $suborder_statuses[ $suborder_status ] ++;
                }
            }

            $parent_order = wc_get_order( $parent_order_id );
            if($parent_order->get_total() == wc_format_decimal($suborder_totals)) {
                if ( $suborder_count == $new_status_count && in_array( $new_status, $status_to_sync ) ) {
                    $parent_order->update_status( $new_status, _x( "Sync from vendor's suborders: ", 'Order note', 'multivendorx' ) );
                } elseif ( $suborder_count != 0 ) {
                    /**
                     * If the parent order have only 1 suborder I can sync it with the same status.
                     * Otherwise I set the parent order to processing
                     */
                    $status = array_unique(array_keys($suborder_statuses));
                    if ( $suborder_count == 1 ) {
                        $new_status = isset($status[0]) ? $status[0] : $new_status;
                        $parent_order->update_status( $new_status, _x( "Sync from vendor's suborders: ", 'Order note', 'multivendorx' ) );
                    } /**
                     * Check only for suborder > 1 to exclude orders without suborder
                     */
                    elseif ( $suborder_count > 1 ) {
                        $check = 0;
//                        foreach ( $status_to_sync as $status ) {
//                            if ( ! empty( $suborder_statuses[ $status ] ) ) {
//                                $check += $suborder_statuses[ $status ];
//                            }
//                        }
                        if( count($status) == 1 && isset($status[0]) ) {
                            $parent_order->update_status( $new_status, _x( "Sync from vendor's suborders: ", 'Order note', 'multivendorx' ) );
                        }
                    }
                }
            }
            add_action('woocommerce_order_status_changed', array($this, 'mvx_parent_order_to_vendor_order_status_synchronization'), 90, 4);
        }
    }

    public function mvx_check_order_awaiting_payment() {
        // Insert or update the post data
        $order_id = absint(WC()->session->order_awaiting_payment);

        // Resume the unpaid order if its pending
        if ($order_id > 0) {
            $order = wc_get_order($order_id);
            if ($order && $order->has_status(array('pending', 'failed'))) {
                $mvx_suborders = get_mvx_suborders($order_id);
                if ($mvx_suborders) {
                    foreach ($mvx_suborders as $suborder) {
                        $commission_id = $suborder->get_meta('_commission_id', true );
                        wp_delete_post( $commission_id, true );
                        wc_delete_shop_order_transients($suborder->get_id());
                        wp_delete_post($suborder->get_id(), true);
                    }
                }
            }
        }
    }

    /**
     * Handle a refund via the edit order screen.
     * Called after wp_ajax_woocommerce_refund_line_items action
     *
     * @use woocommerce_order_refunded action
     * @see woocommerce\includes\class-wc-ajax.php:2295
     */
    public function mvx_order_refunded($order_id, $parent_refund_id) {
        $order = wc_get_order($order_id);
        if ($order->get_parent_id()) { 
            $create_vendor_refund = false;
            $create_refund = true;
            $refund = false;
            $parent_line_item_refund = 0;
            $refund_amount = wc_format_decimal(sanitize_text_field($_POST['refund_amount']));
            $refund_reason = !empty($_POST['refund_reason']) ? sanitize_text_field($_POST['refund_reason']) : '';
            $line_item_qtys = !empty($_POST['line_item_qtys']) ? json_decode(sanitize_text_field(stripslashes($_POST['line_item_qtys'])), true) : array();
            $line_item_totals = !empty($_POST['line_item_totals']) ? json_decode(sanitize_text_field(stripslashes($_POST['line_item_totals'])), true) : array();
            $line_item_tax_totals = !empty($_POST['line_item_tax_totals']) ? json_decode(sanitize_text_field(stripslashes($_POST['line_item_tax_totals'])), true) : array();
            $api_refund = !empty($_POST['api_refund']) && $_POST['api_refund'] === 'true' ? true : false;
            $restock_refunded_items = !empty($_POST['restock_refunded_items']) && $_POST['restock_refunded_items'] === 'true' ? true : false;
            $order = wc_get_order($order_id);
            $parent_order_total = wc_format_decimal($order->get_total());
            $mvx_suborders = get_mvx_suborders($order_id);

            //calculate line items total from parent order
            foreach ($line_item_totals as $item_id => $total) {
                // check if there have vendor line item to refund
                $item = $order->get_item($item_id);
                if( ( $item->get_meta('_vendor_id') || $item->get_meta('vendor_id') ) && $total != 0) $create_vendor_refund = true;
                $parent_line_item_refund += wc_format_decimal($total);
            }
            
            foreach ($mvx_suborders as $suborder) {
                $suborder_items_ids = array_keys($suborder->get_items( array( 'line_item', 'fee', 'shipping' ) ));
                $suborder_total = wc_format_decimal($suborder->get_total());
                $max_refund = wc_format_decimal($suborder_total - $suborder->get_total_refunded());
                $child_line_item_refund = 0;

                // Prepare line items which we are refunding
                $line_items = array();
                $item_ids = array_unique(array_merge(array_keys($line_item_qtys, $line_item_totals)));

                foreach ($item_ids as $item_id) {
                    $child_item_id = $this->get_vendor_order_item_id($item_id);
                    if ($child_item_id && in_array($child_item_id, $suborder_items_ids)) {
                        $line_items[$child_item_id] = array(
                            'qty' => 0,
                            'refund_total' => 0,
                            'refund_tax' => array()
                        );
                    }
                }

                foreach ($line_item_qtys as $item_id => $qty) {
                    $child_item_id = $this->get_vendor_order_item_id($item_id);
                    if ($child_item_id && in_array($child_item_id, $suborder_items_ids)) {
                        $line_items[$child_item_id]['qty'] = max($qty, 0);
                    }
                }

                foreach ($line_item_totals as $item_id => $total) {
                    $child_item_id = $this->get_vendor_order_item_id($item_id);
                    if ($child_item_id && in_array($child_item_id, $suborder_items_ids)) {
                        $total = wc_format_decimal($total);
                        $child_line_item_refund += $total;
                        $line_items[$child_item_id]['refund_total'] = $total;
                    }
                }

                foreach ($line_item_tax_totals as $item_id => $tax_totals) {
                    // check if there have vendor line item to refund
                    $item = $order->get_item($item_id);
                    if($item->get_meta('vendor_id')){
                        foreach ($tax_totals as $value) {
                            if($value != 0) $create_vendor_refund = true;
                        }
                    }
                    $child_item_id = $this->get_vendor_order_item_id($item_id);
                    if ($child_item_id && in_array($child_item_id, $suborder_items_ids)) {
                        $line_items[$child_item_id]['refund_tax'] = array_map('wc_format_decimal', $tax_totals);
                    }
                }

                //calculate refund amount percentage
                $suborder_refund_amount = ( ( ( $refund_amount - $parent_line_item_refund ) * $suborder_total ) / $parent_order_total );
                $suborder_total_refund = wc_format_decimal($child_line_item_refund + $suborder_refund_amount);

                if (!$refund_amount || $max_refund < $suborder_total_refund || 0 > $suborder_total_refund) {
                    /**
                     * Invalid refund amount.
                     * Check if suborder total != 0 create a partial refund, exit otherwise
                     */
                    $surplus = wc_format_decimal($suborder_total_refund - $max_refund);
                    $suborder_total_refund = $suborder_total_refund - $surplus;
                    $create_refund = $suborder_total_refund > 0 ? true : false;
                }

                if ($create_vendor_refund && $create_refund && $suborder_total_refund != 0 ) {
                    // Create the refund object
                    $refund = wc_create_refund(array(
                        'amount' => $suborder_total_refund,
                        'reason' => $refund_reason,
                        'order_id' => $suborder->get_id(),
                        'line_items' => $line_items,
                        )
                    );
                    do_action( 'mvx_order_refunded', $order->get_id(), $refund->get_id() );
                    if($refund)
                        add_post_meta($refund->get_id(), '_parent_refund_id', $parent_refund_id);
                }
            }
        }
    }

    /**
     * Handle a refund via the edit order screen.
     */
    public static function mvx_refund_deleted($refund_id, $parent_order_id) {
        check_ajax_referer('order-item', 'security');

        if (!current_user_can('edit_shop_orders')) {
            wp_die( -1 );
        }
        $parent_order = wc_get_order($parent_order_id);
        if (!$parent_order->get_parent_id()) {
            global $wpdb;
            $child_refund_ids = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key=%s AND meta_value=%s", '_parent_refund_id', $refund_id));

            foreach ($child_refund_ids as $child_refund_id) {
                if ($child_refund_id && 'shop_order_refund' === get_post_type($child_refund_id)) {
                    $order_id = wp_get_post_parent_id($child_refund_id);
                    $assoc_commission_id = $order->get_meta( '_commission_id', true );
                    // delete associated refund commission meta data
                    $commission_refunded_data = get_post_meta( $assoc_commission_id, '_commission_refunded_data', true );
                    if( isset($commission_refunded_data[$child_refund_id]) ) unset($commission_refunded_data[$child_refund_id]);
                    $commission_refunded_data = ( $commission_refunded_data ) ? $commission_refunded_data : array();
                    update_post_meta( $assoc_commission_id, '_commission_refunded_data', $commission_refunded_data );
                    $commission_refunded_items_data = get_post_meta( $assoc_commission_id, '_commission_refunded_items_data', true );
                    if( isset($commission_refunded_items_data[$child_refund_id]) ) unset($commission_refunded_items_data[$child_refund_id]);
                    $commission_refunded_items_data = ( $commission_refunded_items_data ) ? $commission_refunded_items_data : array();
                    update_post_meta( $assoc_commission_id, '_commission_refunded_items_data', $commission_refunded_items_data );
                    $refunded_commission_amount = get_refund_commission_amount($child_refund_id);
                    $commission_refunded_items = get_post_meta( $assoc_commission_id, '_commission_refunded_items', true );
                    if( $commission_refunded_items ){
                        update_post_meta( $assoc_commission_id, '_commission_refunded_items', ($commission_refunded_items - $refunded_commission_amount) );
                    }
                    $commission_refunded = get_post_meta( $assoc_commission_id, '_commission_refunded', true );
                    if( $commission_refunded ){
                        update_post_meta( $assoc_commission_id, '_commission_refunded', ($commission_refunded - $refunded_commission_amount) );
                    }
                    
                    wc_delete_shop_order_transients($order_id);
                    wp_delete_post($child_refund_id);
                }
            }
        }elseif(is_mvx_vendor_order($parent_order_id)){
            $order = wc_get_order($parent_order_id);
            $assoc_commission_id = $order->get_meta( '_commission_id', true );
            // delete associated refund commission meta data
            $commission_refunded_data = get_post_meta( $assoc_commission_id, '_commission_refunded_data', true );
            if( isset($commission_refunded_data[$refund_id]) ) unset($commission_refunded_data[$refund_id]);
            $commission_refunded_data = ( $commission_refunded_data ) ? $commission_refunded_data : array();
            update_post_meta( $assoc_commission_id, '_commission_refunded_data', $commission_refunded_data );
            $commission_refunded_items_data = get_post_meta( $assoc_commission_id, '_commission_refunded_items_data', true );
            if( isset($commission_refunded_items_data[$refund_id]) ) unset($commission_refunded_items_data[$refund_id]);
            $commission_refunded_items_data = ( $commission_refunded_items_data ) ? $commission_refunded_items_data : array();
            update_post_meta( $assoc_commission_id, '_commission_refunded_items_data', $commission_refunded_items_data );
            $refunded_commission_amount = get_refund_commission_amount($refund_id);
            $commission_refunded_items = get_post_meta( $assoc_commission_id, '_commission_refunded_items', true );
            if( $commission_refunded_items ){
                update_post_meta( $assoc_commission_id, '_commission_refunded_items', ($commission_refunded_items - $refunded_commission_amount) );
            }
            $commission_refunded = get_post_meta( $assoc_commission_id, '_commission_refunded', true );
            if( $commission_refunded ){
                update_post_meta( $assoc_commission_id, '_commission_refunded', ($commission_refunded - $refunded_commission_amount) );
            }

            wc_delete_shop_order_transients($parent_order_id);
            wp_delete_post($refund_id);
        }
    }
    
    public function get_vendor_order_item_id( $item_id ) {
        global $wpdb;
        $vendor_item_id = $wpdb->get_var( $wpdb->prepare( "SELECT order_item_id FROM {$wpdb->order_itemmeta} WHERE meta_key=%s AND meta_value=%d", '_vendor_order_item_id', absint( $item_id ) ) );
        // check for shipping
        if( !$vendor_item_id ){
            $vendor_item_id = $wpdb->get_var( $wpdb->prepare( "SELECT order_item_id FROM {$wpdb->order_itemmeta} WHERE meta_key=%s AND meta_value=%d", '_vendor_order_shipping_item_id', absint( $item_id ) ) );
        }
        return $vendor_item_id;
    }

    public static function exclude_coping_order_data() {
        return apply_filters('mvx_exclude_coping_orders', array(
            'id', 'parent_id', 'created_via', 'date_created', 'date_modified', 'status', 'discount_total', 'discount_tax', 'shipping_total', 'shipping_tax',
            'cart_tax', 'total', 'total_tax', 'order_key', 'date_completed', 'date_paid', 'number', 'meta_data', 'line_items', 'tax_lines', 'shipping_lines',
            'fee_lines', 'coupon_lines'
        ));
    }

    public function count_processing_order() {
        global $wpdb;

        $count = 0;
        $status = 'wc-processing';
        $order_statuses = array_keys(wc_get_order_statuses());

        if (!in_array($status, $order_statuses)) {
            return 0;
        }

        $cache_key = WC_Cache_Helper::get_cache_prefix('orders') . $status;
        $cache_group = 'mvx_order';

        $cached = wp_cache_get($cache_group . '_' . $cache_key, $cache_group);

        if ($cached) {
            return 0;
        }

        foreach (wc_get_order_types('order-count') as $type) {
            $count += $wpdb->get_var($wpdb->prepare("SELECT COUNT( * ) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s AND post_parent = 0", $type, $status));
        }

        wp_cache_set($cache_key, $count, 'counts');
        wp_cache_set($cache_group . '_' . $cache_key, true, $cache_group);
    }
    
    public function shop_order_statuses_get_views($views){
        $user = wp_get_current_user();
        if(current_user_can( 'administrator' ) || in_array('administrator', $user->roles) || in_array('dc_vendor', $user->roles)){
            unset($views['mine']);
        }
        return $views;
    }
    
    public function shop_order_count_orders($counts, $type, $perm = ''){
        global $wpdb;
        $user = wp_get_current_user();
        if($type == 'shop_order' && current_user_can( 'administrator' )){
            $post_statuses = wc_get_order_statuses();
            foreach ($counts as $status => $count) {
                if( array_key_exists($status, $post_statuses) && $count > 0 ){
                    $actual_counts = $wpdb->get_var($wpdb->prepare("SELECT COUNT( * ) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s AND post_parent = 0", $type, $status));
                    if($actual_counts != $count){
                        $counts->$status = $actual_counts;
                    }
                }
            }
        }elseif($type == 'shop_order' && in_array('dc_vendor', $user->roles)) {
            $post_statuses = wc_get_order_statuses();
            foreach ($counts as $status => $count) {
                if( array_key_exists($status, $post_statuses) && $count > 0 ){
                    $actual_counts = $wpdb->get_var($wpdb->prepare("SELECT COUNT( * ) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s AND post_parent != 0 AND post_author = %s", $type, $status, $user->ID));
                    if($actual_counts != $count){
                        $counts->$status = $actual_counts;
                    }
                }
            }
        }
        return $counts;
    }
    
    public function trash_mvx_suborder( $order_id ) {
        $order = wc_get_order($order_id);
        if ( $order->get_parent_id() == 0 ) {
            $mvx_suborders = get_mvx_suborders($order_id);
            if ( $mvx_suborders ) {
                foreach ( $mvx_suborders as $suborder ) {
                    wp_trash_post( $suborder->get_id() );
                }
            }
        }
    }
    
    public function delete_mvx_suborder( $order_id ) {
        $order = wc_get_order($order_id);
        if ( $order->get_parent_id() == 0 ) {
            $parent_order = wc_get_order($order_id);
            $mvx_suborders = get_mvx_suborders($order_id);
            if ( $mvx_suborders ) {
                foreach ( $mvx_suborders as $suborder ) {
                    $commission_id = $suborder->get_meta('_commission_id', true );
                    wp_delete_post( $commission_id, true );
                    wp_delete_post( $suborder->get_id(), true );
                }
            }
        }
    }
    
    public function mvx_vendor_order_backend_restriction(){
        if(is_user_mvx_vendor(get_current_user_id())){
            $inline_css = "
                #order_data .order_data_column a.edit_address { display: none; }
                #order_data .order_data_column .wc-customer-user label a{ display: none; }
                #woocommerce-order-items .woocommerce_order_items_wrapper table.woocommerce_order_items th.line_tax .delete-order-tax{ display: none; }
                #woocommerce-order-items .wc-order-edit-line-item-actions a, #woocommerce-order-items .wc-order-edit-line-item-actions a { display: none; }
                #woocommerce-order-items .add-items .button.add-line-item, #woocommerce-order-items .add-items .button.add-coupon { display: none; }
                .mvx_vendor_admin.post-type-shop_order .wrap .page-title-action{ display: none; }
                .mvx_vendor_admin #menu-posts-shop_order .wp-submenu li:last-child, .mvx_vendor_admin .menu-icon-shop_order.opensub li:last-child{ display: none; }
                ";
            wp_add_inline_style('woocommerce_admin_styles', $inline_css);
        }
    }
    
    public function remove_meta_boxes(){
        global $post;
        if( $post && $post->post_type != 'shop_order' ) return;
        if( !is_user_mvx_vendor( get_current_user_id() ) ) return;
        remove_meta_box( 'postcustom', 'shop_order', 'normal' );
        remove_meta_box( 'woocommerce-order-downloads', 'shop_order', 'normal' );
    }
    
    public function remove_admin_menu(){
        global $submenu;
        if( isset( $submenu['edit.php?post_type=shop_order'] ) ){
            foreach ( $submenu['edit.php?post_type=shop_order'] as $key => $menu ) {
                if( $menu[2] == 'post-new.php?post_type=shop_order' ){
                    unset( $submenu['edit.php?post_type=shop_order'][$key] );
                }
            }
        }
    }

    public function woocommerce_my_account_my_orders_query( $query ){
        if(!isset($query['post_parent'])){
            $query['post_parent'] = 0;
        }
        return $query;
    }
    
    public function woocommerce_my_account_my_orders_columns( $columns ) {
        $suborder_column['mvx_suborder'] = __( 'Suborders', 'multivendorx' );
        $columns = array_slice($columns, 0, 1, true) + $suborder_column + array_slice($columns, 1, count($columns) - 1, true);
        return $columns;
    }
    
    public function woocommerce_my_account_my_orders_column_mvx_suborder( $order ) {
        $mvx_suborders = get_mvx_suborders($order->get_id());

        if ($mvx_suborders) {
            echo '<ul class="mvx-order-vendor" style="margin:0px;list-style:none;">';
            foreach ($mvx_suborders as $suborder) {
                $vendor = get_mvx_vendor(get_post_field('post_author', $suborder->get_id()));
                $order_uri = esc_url( $suborder->get_view_order_url() );
                printf('<li><strong><a href="%s" title="%s">#%s</a></strong> &ndash; <small class="mvx-order-for-vendor">%s %s</small></li>', $order_uri, sanitize_title($suborder->get_status()), $suborder->get_order_number(), _x('for', 'Order table details', 'multivendorx'), $vendor->page_title
                );
                do_action('mvx_after_suborder_details', $suborder);
            }
            echo '<ul>';
        } else {
            echo '<span class="na">&ndash;</span>';
        }
    }

    public function woocommerce_customer_available_downloads( $downloads ) {
       $parent_downloads = array();
       foreach( $downloads as $download ) {
           if( !wp_get_post_parent_id( $download['order_id'] ) )
               $parent_downloads[] = $download;
       }
       return $parent_downloads;
   }
    
    public function mvx_frontend_enqueue_scripts(){
        if(is_account_page()){
            $styles = '/***********************  MVX Suborder Icon ***********************/
            .woocommerce-MyAccount-orders.account-orders-table .mvx-order-vendor mark{
                display: block;
                text-indent: -9999px;
                position: relative;
                height: 1em;
                width: 1em;
                background: 0 0;
                font-size: 1.4em;
                margin: 0 auto
            }
            .woocommerce-MyAccount-orders.account-orders-table .mvx-order-vendor mark:after{
                font-family: WooCommerce;
                speak: none;
                font-weight: 400;
                font-variant: normal;
                text-transform: none;
                line-height: 1;
                -webkit-font-smoothing: antialiased;
                margin: 0;
                text-indent: 0;
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                text-align: center
            }

            .woocommerce-MyAccount-orders.account-orders-table .mvx-order-vendor mark {
                float: left;
                margin-right: 8px;
                font-size: 1.1em;
                margin-top: 2px;
            }

            /* Suborder Icon */

            .woocommerce-MyAccount-orders.account-orders-table .mvx-order-vendor mark.pending:after{
                content: "\e012";
                color: #ffba00
            }

            .woocommerce-MyAccount-orders.account-orders-table .mvx-order-vendor mark.trash:after{
                content: "\e602";
                color: #a00
            }

            .woocommerce-MyAccount-orders.account-orders-table .mvx-order-vendor mark.completed:after{
                content: "\e015";
                color: #2ea2cc
            }

            .woocommerce-MyAccount-orders.account-orders-table .mvx-order-vendor mark.on-hold:after{
                content: "\e033";
                color: #999
            }

            .woocommerce-MyAccount-orders.account-orders-table .mvx-order-vendor mark.failed:after{
                content: "\e016";
                color: #d0c21f
            }

            .woocommerce-MyAccount-orders.account-orders-table .mvx-order-vendor mark.cancelled:after{
                content: "\e013";
                color: #a00
            }

            .woocommerce-MyAccount-orders.account-orders-table .mvx-order-vendor mark.processing:after{
                content: "\e011";
                color: #73a724
            }

            .woocommerce-MyAccount-orders.account-orders-table .mvx-order-vendor mark.refunded:after {
                content: "\e014";
                color: #999
            }';
            wp_add_inline_style('woocommerce-inline', $styles);
        }
    }
    
    public function add_admin_body_class( $body_classes ){
        if ( is_user_mvx_vendor(get_current_user_id() ) ) {
            $body_classes .= ' mvx_vendor_admin';
        }
        return $body_classes;
    }
    
    public function woocommerce_can_reduce_order_stock( $reduce_stock, $order ){
        $is_vendor_order = ( $order ) ? mvx_get_order( $order->get_id() ) : false;
        return $order instanceof WC_Order &&  $order->get_parent_id() && $is_vendor_order ? false : $reduce_stock;
    }
    
    public function woocommerce_hidden_order_itemmeta( $itemmeta ) {
        if ( is_user_mvx_vendor(get_current_user_id() ) ) {
            $itemmeta[] = '_vendor_item_commission';
            $itemmeta[] = 'commission';
            $itemmeta[] = '_vendor_id';
            $itemmeta[] = 'vendor_id';
            $itemmeta[] = '_vendor_order_item_id';
            $itemmeta[] = 'Sold By';
        }elseif (current_user_can( 'administrator' ) ){
            $itemmeta[] = 'commission';
        }
        return $itemmeta;
    }
    
    public function woocommerce_hidden_order_item_get_formatted_meta_data( $formatted_meta ) {
        if( $formatted_meta ){
            foreach ( $formatted_meta as $key => $meta ) {
                if( $meta->key == 'commission' ) unset($formatted_meta[$key]);
            }
        }
        return $formatted_meta;
    }
    
    public function mvx_vendor_order_status_changed_actions( $order_id, $old_status, $new_status, $order){
        if( !$order_id || !is_mvx_vendor_order( $order ) ) return;
        if( $new_status == 'cancelled' ){
            $commission_id = $order->get_meta( '_commission_id', true );
            do_action( 'mvx_vendor_order_on_cancelled_commission', $commission_id, $order_id );
            if( $commission_id ) wp_trash_post( $commission_id );
        }
        // stock increase when suborder mark as completed
        if ($order->get_parent_id() && $new_status == 'completed') {
            
            $items = $order->get_items();
            foreach ($items as $item_id => $item) {
                if (isset($item['product_id']) && $item['product_id'] !== 0) {
                    // check vendor product
                    $has_vendor = get_mvx_product_vendors($item['product_id']);
                    $stock_status = get_post_meta($item['product_id'], '_manage_stock', true) && get_post_meta($item['product_id'], '_manage_stock', true) == 'yes' ? true : false;
                    if ($has_vendor && $stock_status) {
                        $product = wc_get_product($item['product_id']);
                        $quantity = $product->get_stock_quantity();
                        update_post_meta($item['product_id'], '_stock', absint($quantity + $item['qty']));
                    }
                }
            }
        }
    }
    
    public function mvx_exclude_suborders_from_rest_api_call( $args, $request ){
        if( apply_filters( 'mvx_exclude_suborders_from_rest_api_call', true, $args, $request ) )
            $args['parent'] = ( isset( $args['parent'] ) && $args['parent'] ) ? $args['parent'][] = 0 : array( 0 );
        if( apply_filters( 'mvx_fetch_all_suborders_from_rest_api_call', false, $args, $request ) )
            $args['parent_exclude'] = ( isset( $args['parent_exclude'] ) && $args['parent_exclude'] ) ? $args['parent_exclude'][] = 0 : array( 0 );
        
        if( apply_filters( 'mvx_remove_suborders_from_rest_api_call', true, $args, $request ) ) {
            $suborders = mvx_get_orders( array(), 'ids', true );
            $args['post__not_in'] = array( $suborders );
        }
        return apply_filters( 'mvx_exclude_suborders_from_rest_api_call_query_args', $args, $request );
    }

    public function mvx_refund_btn_customer_my_account( $order ){
        global $MVX;
        if( !is_wc_endpoint_url( 'view-order' ) ) return;
        if( !mvx_get_order( $order->get_id() ) ) return;
        if( !mvx_is_module_active( 'marketplace-refund' ) ) return;
        $refund_settings = mvx_get_option( 'mvx_refund_management_tab_settings', true );
        if ( get_mvx_vendor_settings('disable_refund_customer_end', 'refund_management') && !empty(get_mvx_vendor_settings('disable_refund_customer_end', 'refund_management')) ) return;
        $refund_reason_options = get_mvx_global_settings('refund_order_msg') ? explode( "||", get_mvx_global_settings('refund_order_msg') ) : array();
        $refund_button_text = apply_filters( 'mvx_customer_my_account_refund_request_button_text', __( 'Request a refund', 'multivendorx' ), $order );
        // Print refund messages, if any
        if( mvx_get_customer_refund_order_msg( $order, $refund_settings ) ) {
            $msg_data = mvx_get_customer_refund_order_msg( $order, $refund_settings );
            $type = isset( $msg_data['type'] ) ? $msg_data['type'] : 'info';
            ?>
            <div class="woocommerce-Message woocommerce-Message--<?php echo $type; ?> woocommerce-<?php echo $type; ?>">
                <?php echo $msg_data['msg']; ?>
            </div>
            <?php
            return;
        }
        ?>
        <p><button type="button" class="button" id="cust_request_refund_btn" name="cust_request_refund_btn" value="<?php echo $refund_button_text; ?>"><?php echo $refund_button_text; ?></button></p>
        <div id="mvx-myac-order-refund-wrap" class="mvx-myac-order-refund-wrap">
            <form method="POST">
            <?php wp_nonce_field( 'customer_request_refund', 'cust-request-refund-nonce' ); ?>
            <fieldset>
                <legend><?php echo apply_filters( 'mvx_customer_my_account_refund_reason_label', __('Please mention your reason for refund', 'multivendorx'), $order ); ?></legend>

                <?php 
                if( $refund_reason_options ) {
                    foreach( $refund_reason_options as $index => $reason ) {
                        echo '<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <label class="refund_reason_option" for="refund_reason_option-'.$index.'">
                            <input type="radio" class="woocommerce-Input input-radio" name="refund_reason_option" id="refund_reason_option-'.$index.'" value="'.$index.'" />
                            '.esc_html( $reason ).'
                        </label></p>';
                    }
                    // Add others reason
                    echo '<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <label class="refund_reason_option" for="refund_reason_option-other">
                            <input type="radio" class="woocommerce-Input input-radio" name="refund_reason_option" id="refund_reason_option-other" value="others" />
                            '.__( 'Others reason', 'multivendorx' ).'
                        </label></p>';
                        ?>
                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide cust-rr-other">
                        <label for="refund_reason_other"><?php _e( 'Refund reason', 'multivendorx' ); ?></label>
                        <input type="text" class="woocommerce-Input input-text" name="refund_reason_other" id="refund_reason_other" autocomplete="off" />
                    </p>
                        <?php
                }else{
                    ?>
                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <label for="refund_reason_other"><?php _e( 'Refund reason', 'multivendorx' ); ?></label>
                        <input type="text" class="woocommerce-Input input-text" name="refund_reason_other" id="refund_reason_other" autocomplete="off" />
                    </p>
                    <?php
                }
                ?>
                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                    <label for="additional_info"><?php _e( 'Provide additional information', 'multivendorx' ); ?></label>
                    <textarea class="woocommerce-Input input-text" name="refund_request_addi_info" id="refund_request_addi_info"></textarea>
                </p>
                
                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                    <button type="submit" class="button" name="cust_request_refund_sbmt" value="<?php _e( 'Submit', 'multivendorx' ); ?>"><?php _e( 'Submit', 'multivendorx' ); ?></button>
                </p>
            </fieldset>
            </form>
        </div>
        <?php
        // scripts
        wp_add_inline_script( 'woocommerce', '( function( $ ) {
            $("#mvx-myac-order-refund-wrap").hide();
            $("#mvx-myac-order-refund-wrap .cust-rr-other").hide();
            $("#mvx-myac-order-refund-wrap .refund_reason_option input").on("click", function(){
                var others_checked = $("input:radio[name=refund_reason_option]:checked").val();
                if(others_checked == "others"){
                    $("#mvx-myac-order-refund-wrap .cust-rr-other").show();
                }else{
                    $("#mvx-myac-order-refund-wrap .cust-rr-other").hide();
                }
            });
			$("#cust_request_refund_btn").click(function(){
				$("#mvx-myac-order-refund-wrap").slideToggle();
			});
		} )( jQuery );' );
    }

    public function mvx_handler_cust_requested_refund() {
        global $wp;
        $nonce_value = isset($_REQUEST['cust-request-refund-nonce']) ? wc_get_var( $_REQUEST['cust-request-refund-nonce'], wc_get_var( $_REQUEST['_wpnonce'], '' ) ) : ''; // @codingStandardsIgnoreLine.

		if ( ! wp_verify_nonce( $nonce_value, 'customer_request_refund' ) ) {
			return;
        }
        // If no refund reason is selected
        if ( !isset( $_REQUEST['refund_reason_option'] ) ) {
            wc_add_notice( __( 'Kindly choose a refund reason', 'multivendorx' ) , 'error' );
            return;
        }
        if( !isset( $wp->query_vars['view-order'] ) ) return;
        $order_id = $wp->query_vars['view-order'];
        $order = wc_get_order( $order_id );
        $reason_option = isset( $_REQUEST['refund_reason_option'] ) ? wc_clean( wp_unslash($_REQUEST['refund_reason_option'])) : '';
        $refund_reason_other = isset( $_REQUEST['refund_reason_other'] ) ? wc_clean( wp_unslash($_REQUEST['refund_reason_other'])) : '';
        $refund_request_addi_info = isset( $_REQUEST['refund_request_addi_info'] ) ? wc_clean( wp_unslash($_REQUEST['refund_request_addi_info'])) : '';
        $refund_settings = mvx_get_option( 'mvx_refund_management_tab_settings', true );
        $refund_reason_options = ( isset( $refund_settings['refund_order_msg'] ) && $refund_settings['refund_order_msg'] ) ? explode( "||", $refund_settings['refund_order_msg'] ) : array();
        $refund_reason = ( $reason_option == 'others' ) ? $refund_reason_other : (isset( $refund_reason_options[$reason_option] ) ? $refund_reason_options[$reason_option] : ''); 
        $refund_details = array(
            'refund_reason' => $refund_reason,
            'addi_info' => $refund_request_addi_info,
        );
        // update customer refunt request 
        $order->update_meta_data('_customer_refund_order', wc_clean( wp_unslash( 'refund_request' ) ));
        $order->update_meta_data('_customer_refund_reason', wc_clean( wp_unslash( $refund_reason ) ));
        $order->save();
        $comment_id = $order->add_order_note( __('Customer requested a refund ', 'multivendorx') .$order_id.' .' );
        // user info
        $user_info = get_userdata(get_current_user_id());
        wp_update_comment(array('comment_ID' => $comment_id, 'comment_author' => $user_info->user_name, 'comment_author_email' => $user_info->user_email));

        // parent order
        $parent_order_id = $order->get_parent_id();
        $parent_order = wc_get_order( $parent_order_id );
        $comment_id_parent = $parent_order->add_order_note( __('Customer requested a refund for ', 'multivendorx') .$order_id.'.'  );
        wp_update_comment(array('comment_ID' => $comment_id_parent, 'comment_author' => $user_info->user_name, 'comment_author_email' => $user_info->user_email));

        $mail = WC()->mailer()->emails['WC_Email_Customer_Refund_Request'];
        // order vendor
        $vendor_id = $order->get_meta( '_vendor_id', true );
        $vendor_user_info = get_userdata($vendor_id);
        $mail->trigger( $vendor_user_info->user_email, $order_id, $refund_details );
        wc_add_notice( __( 'Refund request successfully placed.', 'multivendorx' ) );
    }

    public function mvx_refund_order_status_customer_meta(){
        global $post;
        if( $post && $post->post_type != 'shop_order' ) return;
        if( isset($_GET['id']) && !mvx_get_order( $_GET['id'] ) ) return;
        add_meta_box( 'refund_status_customer', __('Customer refund status', 'multivendorx'),  array( $this, 'mvx_order_customer_refund_dd' ), 'shop_order', 'side', 'core' );
    }

    public function mvx_order_customer_refund_dd(){
        global $post;
        $sub_order = wc_get_order($post->ID);
        $refund_status = $sub_order->get_meta('_customer_refund_order', true ) ?? '';
        $refund_statuses = array( 
            '' => __('Refund Status','multivendorx'),
            'refund_request' => __('Refund Requested', 'multivendorx'), 
            'refund_accept' => __('Refund Accepted','multivendorx'), 
            'refund_reject' => __('Refund Rejected','multivendorx') 
        );
        ?>
        <select id="refund_order_customer" name="refund_order_customer" onchange='refund_admin_reason(this.value);'>
            <?php foreach ( $refund_statuses as $key => $value ) { ?>
            <option value="<?php echo $key; ?>" <?php selected( $refund_status, $key ); ?> ><?php echo $value; ?></option>
            <?php } ?>
        </select>
        <div class="reason_select_by_admin" id="reason_select_by_admin" style='display:none;'>
            <label for="additional_massage"><?php _e( 'Please Provide Some Reason', 'multivendorx' ); ?></label>
            <textarea class="woocommerce-Input input-text" name="refund_admin_reason_text" id="refund_admin_reason_text"></textarea>
        </div>
        <button type="submit" class="button cust-refund-status button-default" name="cust_refund_status" value="<?php echo __('Update status', 'multivendorx'); ?>"><?php echo __('Update status', 'multivendorx'); ?></button>
        <script>
            function refund_admin_reason(val){
                var element = document.getElementById('reason_select_by_admin');
                if( val == 'refund_accept' || val == 'refund_reject' )
                    element.style.display='block';
                else  
                    element.style.display='none';
            }
        </script>
        <?php
    }

    public function mvx_refund_order_status_save( $post_id ){
        global $post;
        if( empty($post) || ( $post && $post->post_type != 'shop_order' ) ) return;
        if( !mvx_get_order( $post_id ) ) return;
        if( !isset( $_POST['cust_refund_status'] ) ) $post_id;
        if( isset( $_POST['refund_order_customer'] ) && $_POST['refund_order_customer'] ) {
            update_post_meta( $post_id, '_customer_refund_order', wc_clean( wp_unslash( $_POST['refund_order_customer'] ) ) );
            // trigger customer email
            if( in_array( $_POST['refund_order_customer'], array( 'refund_reject', 'refund_accept' ) ) ) {

                $refund_details = array(
                    'admin_reason' => isset( $_POST['refund_admin_reason_text'] ) ? wc_clean($_POST['refund_admin_reason_text']) : '',
                    );
                
                $order_status = '';
                if( $_POST['refund_order_customer'] == 'refund_accept' ) {
                    $order_status = __( 'accepted', 'multivendorx' );
                }elseif( $_POST['refund_order_customer'] == 'refund_reject') {
                    $order_status = __( 'rejected', 'multivendorx' );
                }
                // Comment note for suborder
                $order = wc_get_order( $post_id );
                $comment_id = $order->add_order_note( __('Site admin ', 'multivendorx') . $order_status. __(' refund request for order #', 'multivendorx') .$post_id.' .' );
                // user info
                $user_info = get_userdata(get_current_user_id());
                wp_update_comment(array('comment_ID' => $comment_id, 'comment_author' => $user_info->user_name, 'comment_author_email' => $user_info->user_email));

                // Comment note for parent order
                $parent_order_id = $order->get_parent_id();
                $parent_order = wc_get_order( $parent_order_id );
                $comment_id_parent = $parent_order->add_order_note( __('Site admin ', 'multivendorx') . $order_status. __(' refund request for order #', 'multivendorx') . $post_id .'.' );
                wp_update_comment(array('comment_ID' => $comment_id_parent, 'comment_author' => $user_info->user_name, 'comment_author_email' => $user_info->user_email));

                $mail = WC()->mailer()->emails['WC_Email_Customer_Refund_Request'];
                $mail->trigger( sanitize_email($_POST['_billing_email']), $post_id, $refund_details, 'customer' );
            }
        }
    }

    public function mvx_suborder_hide( $args, $request ){
        $woocommerce_orders = mvx_get_orders( array('post_status' => array('wc-processing', 'wc-completed', 'wc-on-hold')), 'ids', true );
        $args['post__not_in'] = $woocommerce_orders;
        return $args;
    }

    public function woocommerce_customer_exclude_suborder_query( $query, $customer ) {
        global $wpdb;
        $statuses = array_map( 'esc_sql', wc_get_is_paid_statuses() );
        $query = "SELECT SUM(meta2.meta_value)
        FROM $wpdb->posts as posts
        LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
        LEFT JOIN {$wpdb->postmeta} AS meta2 ON posts.ID = meta2.post_id
        WHERE   meta.meta_key       = '_customer_user'
        AND     meta.meta_value     = '" . esc_sql( $customer->get_id() ) . "'
        AND     posts.post_type     = 'shop_order'
        AND     posts.post_parent   = 0
        AND     posts.post_status   IN ( 'wc-" . implode( "','wc-", $statuses ) . "' )
        AND     meta2.meta_key      = '_order_total'";
        return $query;
    }

    public function get_vendor_parent_order_item_id( $item_id ) {
        global $wpdb;
        $vendor_item_id = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->order_itemmeta} WHERE meta_key=%s AND order_item_id=%d", '_vendor_order_item_id', absint( $item_id ) ) );
        // check for shipping
        if( !$vendor_item_id ){
            $vendor_item_id = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->order_itemmeta} WHERE meta_key=%s AND order_item_id=%d", '_vendor_order_shipping_item_id', absint( $item_id ) ) );
        }
        return $vendor_item_id;
    }

     public function mvx_refund_table_action() { 
        $refund_url = mvx_get_vendor_dashboard_endpoint_url(get_mvx_vendor_settings('mvx_refund_req_endpoint', 'seller_dashbaord', 'refund-request'));
        $refund_redirect_url = apply_filters('mvx_vendor_redirect_after_refund_action', $refund_url);
        $wpnonce = isset( $_REQUEST['_wpnonce'] ) ? $_REQUEST['_wpnonce'] : '';
        $order_id = isset( $_REQUEST['order_id'] ) ? (int) $_REQUEST['order_id'] : 0;
        $order = wc_get_order($order_id);

        if ($wpnonce && wp_verify_nonce($wpnonce, 'mvx_accept_refund') && $order) {
            $order->update_meta_data('_customer_refund_order', wc_clean( wp_unslash( 'refund_accept' ) ));
            $order->save();
            wc_add_notice(__('Changed status to Refund Accepted', 'multivendorx'), 'success');
            wp_redirect(esc_url(mvx_get_vendor_dashboard_endpoint_url(get_mvx_vendor_settings('mvx_vendor_orders_endpoint', 'seller_dashbaord', 'vendor-orders'), $order_id)));
            exit;
        }
        if ($wpnonce && wp_verify_nonce($wpnonce, 'mvx_reject_refund') && $order) {
            $order->update_meta_data('_customer_refund_order', wc_clean( wp_unslash( 'refund_reject' ) ));
            $order->save();
            wc_add_notice(__('Changed status to Refund rejected', 'multivendorx'), 'error');
            wp_redirect( $refund_redirect_url );
            exit;
        }
        if ($wpnonce && wp_verify_nonce($wpnonce, 'mvx_pending_refund') && $order) {
            update_post_meta( $order_id, '_customer_refund_order', wc_clean( wp_unslash( 'refund_request' ) ) );
            $order->update_meta_data('_customer_refund_order', wc_clean( wp_unslash( 'refund_request' ) ));
            $order->save();
            wc_add_notice(__('Changed status to Refund Pending', 'multivendorx'), 'error');
            wp_redirect( $refund_redirect_url );
            exit;
        }
    }
}
