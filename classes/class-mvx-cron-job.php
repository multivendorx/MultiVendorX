<?php

/**
 * MVX Cron Job Class
 *
 * @version		2.2.0
 * @package		MVX
 * @author 		Multivendor X
 */
class MVX_Cron_Job {

    public function __construct() {
        add_action('masspay_cron_start', array(&$this, 'do_mass_payment'));
        // vendor weekly order stats reports
        add_action('vendor_weekly_order_stats', array(&$this, 'vendor_weekly_order_stats_report'));
        // vendor monthly order stats reports
        add_action('vendor_monthly_order_stats', array(&$this, 'vendor_monthly_order_stats_report'));
        // migrate all products having parent-child concept
        add_action('migrate_spmv_multivendor_table', array(&$this, 'migrate_spmv_multivendor_table'));
        // vendor Custom date order stats reports
        add_action('vendor_custom_date_order_stats', array(&$this, 'vendor_custom_date_order_stats_report'));
        // bind spmv excluded products mapping 
        add_action('mvx_spmv_excluded_products_map', array(&$this, 'mvx_spmv_excluded_products_map'));
        // bind spmv excluded products mapping 
        add_action('mvx_spmv_product_meta_update', array(&$this, 'mvx_spmv_product_meta_update'));
        // Reset product mapping
        add_action('mvx_reset_product_mapping_data', array(&$this, 'mvx_reset_product_mapping_data'), 10, 1);

        add_action('migrate_multivendor_table', array(&$this, 'migrate_multivendor_table'));
        // MVX order migration
        add_action('mvx_orders_migration', array(&$this, 'mvx_orders_migration'));

        $this->mvx_clear_scheduled_event();
    }

    /**
     * Clear scheduled event
     */
    function mvx_clear_scheduled_event() {
        $cron_hook_identifier = apply_filters('mvx_cron_hook_identifier', array(
            'masspay_cron_start',
            'vendor_weekly_order_stats',
            'vendor_monthly_order_stats',
            'vendor_custom_date_order_stats',
            'migrate_spmv_multivendor_table',
            'mvx_spmv_excluded_products_map',
            'mvx_spmv_product_meta_update',
        ));
        if ($cron_hook_identifier) {
            foreach ($cron_hook_identifier as $cron_hook) {
                $timestamp = wp_next_scheduled($cron_hook);
                if ($timestamp && apply_filters('mvx_unschedule_'. $cron_hook . '_cron_event', false)) {
                    wp_unschedule_event($timestamp, $cron_hook);
                }
            }
        }
    }

    /**
     * Calculate the amount and selete payment method.
     *
     *
     */
    function do_mass_payment() {
        global $MVX;
        $payment_admin_settings = get_option('mvx_payment_settings_name');
        if (!isset($payment_admin_settings['mvx_disbursal_mode_admin'])) {
            return;
        }
        $commission_to_pay = array();
        $commissions = $this->get_query_commission();
        if ($commissions && is_array($commissions)) {
            foreach ($commissions as $commission) {
                $commission_id = $commission->ID;
                $vendor_term_id = get_post_meta($commission_id, '_commission_vendor', true);
                $order_id = get_post_meta( $commission_id ,'_commission_order_id', true );
                $order = wc_get_order( $order_id );
                if( is_a( $order, 'WC_Order' ) && !in_array( $order->get_status(), apply_filters( 'mvx_cron_mass_payment_exclude_order_statuses',array( 'failed', 'cancelled' ) ) ) ) {
                    $commission_to_pay[$vendor_term_id][] = $commission_id;
                }
            }
        }
        foreach ($commission_to_pay as $vendor_term_id => $commissions) {
            $vendor = get_mvx_vendor_by_term($vendor_term_id);
            if ($vendor) {
                $payment_method = get_user_meta($vendor->id, '_vendor_payment_mode', true);
                if ($payment_method && $payment_method != 'direct_bank') {
                    if (array_key_exists($payment_method, $MVX->payment_gateway->payment_gateways)) {
                        $MVX->payment_gateway->payment_gateways[$payment_method]->process_payment($vendor, $commissions);
                    }
                }
            }
        }
    }

    /**
     * Get Commissions
     *
     * @return object $commissions
     */
    public function get_query_commission() {
        $args = array(
            'post_type' => 'dc_commission',
            'post_status' => array('publish', 'private'),
            'meta_key' => '_paid_status',
            'meta_value' => 'unpaid',
            'posts_per_page' => 5
        );
        $commissions = get_posts($args);
        return $commissions;
    }

    /**
     * Weekly order stats report
     *
     * 
     */
    public function vendor_weekly_order_stats_report() {
        global $MVX;
        $vendors = get_mvx_vendors();
        if ($vendors && apply_filters('mvx_enabled_vendor_weekly_report_mail', true)) {
            foreach ($vendors as $key => $vendor_obj) {
                if ($vendor_obj->user_data->user_email) {
                    $order_data = array();
                    $vendor = get_mvx_vendor($vendor_obj->id);
                    $is_block = get_user_meta($vendor->id, '_vendor_turn_off', true);
                    if($is_block) continue;
                    $email = WC()->mailer()->emails['WC_Email_Vendor_Orders_Stats_Report'];
                    $vendor_weekly_stats = $vendor->get_vendor_orders_reports_of('vendor_stats', array('vendor_id' => $vendor->id));
                    $transaction_details = $MVX->transaction->get_transactions($vendor->term_id, date('Y-m-d', strtotime('-7 days')), date('Y-m-d'));
                    if (is_array($vendor_weekly_stats)) {
                        $vendor_weekly_stats['total_transaction'] = array_sum(wp_list_pluck($transaction_details, 'total_amount'));
                    }
                    $report_data = array(
                        'period' => __('weekly', 'dc-woocommerce-multi-vendor'),
                        'start_date' => date('Y-m-d', strtotime('-7 days')),
                        'end_date' => @date('Y-m-d'),
                        'stats' => $vendor_weekly_stats,
                    );
                    $attachments = array();
                    $vendor_weekly_orders = $vendor->get_vendor_orders_reports_of('', array('vendor_id' => $vendor->id));
                    if ($vendor_weekly_orders && count($vendor_weekly_orders) > 0) {
                        foreach ($vendor_weekly_orders as $key => $data) {
                            if ($data->commission_id != 0 && $data->commission_id != '') {
                                $order_data[$data->commission_id] = $key;
                            }
                        }
                        if (count($order_data) > 0) {
                            $report_data['order_data'] = $order_data;
                            $args = array(
                                'filename' => 'OrderReports-' . $report_data['start_date'] . '-To-' . $report_data['end_date'] . '.csv',
                                'action' => 'temp',
                            );
                            $report_csv = $MVX->vendor_dashboard->generate_csv($order_data, $vendor, $args);
                            if ($report_csv)
                                $attachments[] = $report_csv;
                            if ($email->trigger($vendor, $report_data, $attachments)) {
                                $email->find[] = $vendor->page_title;
                                $email->replace[] = '{STORE_NAME}';
                                if (file_exists($report_csv)) {
                                    @unlink($report_csv);
                                }
                            } else {
                                if (file_exists($report_csv)) {
                                    @unlink($report_csv);
                                }
                            }
                        } else {
                            if (apply_filters('mvx_send_vendor_weekly_zero_order_stats_report', true, $vendor)) {
                                $report_data['order_data'] = $order_data;
                                if ($email->trigger($vendor, $report_data, $attachments)) {
                                    $email->find[] = $vendor->page_title;
                                    $email->replace[] = '{STORE_NAME}';
                                }
                            }
                        }
                    } else {
                        if (apply_filters('mvx_send_vendor_weekly_zero_order_stats_report', true, $vendor)) {
                            $report_data['order_data'] = $order_data;
                            if ($email->trigger($vendor, $report_data, $attachments)) {
                                $email->find[] = $vendor->page_title;
                                $email->replace[] = '{STORE_NAME}';
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Monthly order stats report
     *
     * 
     */
    public function vendor_monthly_order_stats_report() {
        global $MVX;
        $vendors = get_mvx_vendors();
        if ($vendors && apply_filters('mvx_enabled_vendor_monthly_report_mail', true)) {
            foreach ($vendors as $key => $vendor_obj) {
                if ($vendor_obj->user_data->user_email) {
                    $order_data = array();
                    $vendor = get_mvx_vendor($vendor_obj->id);
                    $is_block = get_user_meta($vendor->id, '_vendor_turn_off', true);
                    if($is_block) continue;
                    $email = WC()->mailer()->emails['WC_Email_Vendor_Orders_Stats_Report'];
                    $vendor_monthly_stats = $vendor->get_vendor_orders_reports_of('vendor_stats', array('vendor_id' => $vendor->id, 'start_date' => date('Y-m-d H:i:s', strtotime('-30 days'))));
                    $transaction_details = $MVX->transaction->get_transactions($vendor->term_id, date('Y-m-d', strtotime('-30 days')), date('Y-m-d'));
                    if (is_array($vendor_monthly_stats)) {
                        $vendor_monthly_stats['total_transaction'] = array_sum(wp_list_pluck($transaction_details, 'total_amount'));
                    }
                    $report_data = array(
                        'period' => __('monthly', 'dc-woocommerce-multi-vendor'),
                        'start_date' => date('Y-m-d', strtotime('-30 days')),
                        'end_date' => @date('Y-m-d'),
                        'stats' => $vendor_monthly_stats,
                    );
                    $attachments = array();
                    $vendor_monthly_orders = $vendor->get_vendor_orders_reports_of('', array('vendor_id' => $vendor->id, 'start_date' => date('Y-m-d H:i:s', strtotime('-30 days'))));
                    if ($vendor_monthly_orders && count($vendor_monthly_orders) > 0) {
                        foreach ($vendor_monthly_orders as $key => $data) {
                            if ($data->commission_id != 0 && $data->commission_id != '') {
                                $order_data[$data->commission_id] = $key;
                            }
                        }
                        if (count($order_data) > 0) {
                            $report_data['order_data'] = $order_data;
                            $args = array(
                                'filename' => 'OrderReports-' . $report_data['start_date'] . '-To-' . $report_data['end_date'] . '.csv',
                                'action' => 'temp',
                            );
                            $report_csv = $MVX->vendor_dashboard->generate_csv($order_data, $vendor, $args);
                            if ($report_csv)
                                $attachments[] = $report_csv;
                            if ($email->trigger($vendor, $report_data, $attachments)) {
                                $email->find[] = $vendor->page_title;
                                $email->replace[] = '{STORE_NAME}';
                                if (file_exists($report_csv)) {
                                    @unlink($report_csv);
                                }
                            } else {
                                if (file_exists($report_csv)) {
                                    @unlink($report_csv);
                                }
                            }
                        } else {
                            if (apply_filters('mvx_send_vendor_monthly_zero_order_stats_report', true, $vendor)) {
                                $report_data['order_data'] = $order_data;
                                if ($email->trigger($vendor, $report_data, $attachments)) {
                                    $email->find[] = $vendor->page_title;
                                    $email->replace[] = '{STORE_NAME}';
                                }
                            }
                        }
                    } else {
                        if (apply_filters('mvx_send_vendor_monthly_zero_order_stats_report', true, $vendor)) {
                            $report_data['order_data'] = $order_data;
                            if ($email->trigger($vendor, $report_data, $attachments)) {
                                $email->find[] = $vendor->page_title;
                                $email->replace[] = '{STORE_NAME}';
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Order stats report by custom date
     *
     * 
     */
    public function vendor_custom_date_order_stats_report() {
        global $MVX;
        $custom_date_order_stat_report_mail = get_mvx_vendor_settings( 'custom_date_order_stat_report_mail', 'general' ) ? get_mvx_vendor_settings( 'custom_date_order_stat_report_mail', 'general' ) : 0;
        $vendors = get_mvx_vendors();
        if ($vendors && $custom_date_order_stat_report_mail && apply_filters('mvx_enabled_vendor_custom_date_report_mail', true)) {
            $strtotime = strtotime('-'. $custom_date_order_stat_report_mail .' days');
            foreach ($vendors as $key => $vendor_obj) {
                if ($vendor_obj->user_data->user_email) {
                    $order_data = array();
                    $vendor = get_mvx_vendor($vendor_obj->id);
                    $is_block = get_user_meta($vendor->id, '_vendor_turn_off', true);
                    if($is_block) continue;
                    $email = WC()->mailer()->emails['WC_Email_Vendor_Orders_Stats_Report'];
                    $vendor_custom_date_stats = $vendor->get_vendor_orders_reports_of('vendor_stats', array('vendor_id' => $vendor->id, 'start_date' => date('Y-m-d H:i:s', $strtotime)));
                    $transaction_details = $MVX->transaction->get_transactions($vendor->term_id, date('Y-m-d', $strtotime), date('Y-m-d'));
                    if (is_array($vendor_custom_date_stats)) {
                        $vendor_custom_date_stats['total_transaction'] = array_sum(wp_list_pluck($transaction_details, 'total_amount'));
                    }
                    $report_data = array(
                        'period' => __('monthly', 'dc-woocommerce-multi-vendor'),
                        'start_date' => date('Y-m-d', $strtotime),
                        'end_date' => @date('Y-m-d'),
                        'stats' => $vendor_custom_date_stats,
                    );

                    $attachments = array();
                    $vendor_monthly_orders = $vendor->get_vendor_orders_reports_of('', array('vendor_id' => $vendor->id, 'start_date' => date('Y-m-d H:i:s', $strtotime)));
                    if ($vendor_monthly_orders && count($vendor_monthly_orders) > 0) {
                        foreach ($vendor_monthly_orders as $key => $data) {
                            if ($data->commission_id != 0 && $data->commission_id != '') {
                                $order_data[$data->commission_id] = $key;
                            }
                        }
                        if (count($order_data) > 0) {
                            $report_data['order_data'] = $order_data;
                            $args = array(
                                'filename' => 'OrderReports-' . $report_data['start_date'] . '-To-' . $report_data['end_date'] . '.csv',
                                'action' => 'temp',
                            );
                            $report_csv = $MVX->vendor_dashboard->generate_csv($order_data, $vendor, $args);
                            if ($report_csv)
                                $attachments[] = $report_csv;
                            if ($email->trigger($vendor, $report_data, $attachments)) {
                                $email->find[] = $vendor->page_title;
                                $email->replace[] = '{STORE_NAME}';
                                if (file_exists($report_csv)) {
                                    @unlink($report_csv);
                                }
                            } else {
                                if (file_exists($report_csv)) {
                                    @unlink($report_csv);
                                }
                            }
                        } else {
                            if (apply_filters('mvx_send_vendor_monthly_zero_order_stats_report', true, $vendor)) {
                                $report_data['order_data'] = $order_data;
                                if ($email->trigger($vendor, $report_data, $attachments)) {
                                    $email->find[] = $vendor->page_title;
                                    $email->replace[] = '{STORE_NAME}';
                                }
                            }
                        }
                    } else {
                        if (apply_filters('mvx_send_vendor_monthly_zero_order_stats_report', true, $vendor)) {
                            $report_data['order_data'] = $order_data;
                            if ($email->trigger($vendor, $report_data, $attachments)) {
                                $email->find[] = $vendor->page_title;
                                $email->replace[] = '{STORE_NAME}';
                            }
                        }
                    }
                }
            }
        }
    }

    public function migrate_spmv_multivendor_table() {
        global $MVX, $wpdb;
        $length = apply_filters('mvx_migrate_spmv_multivendor_table_length', 50);
        $args = apply_filters('mvx_migrate_spmv_table_products_query_args', array(
            'numberposts' => $length,
            'post_type' => 'product',
            'meta_key' => '_mvx_child_product',
            'meta_value' => '1',
            'fields' => 'id=>parent',
        ));
        $products = get_posts($args);

        if($products){
            foreach ($products as $product_id => $parent_id) {
                if($parent_id){
                    delete_post_meta($product_id, '_mvx_child_product');
                    wp_update_post(array('ID' => $product_id, 'post_parent' => 0), true);
                    $data = array('product_id' => $product_id);
                    if(get_post_meta($product_id, '_mvx_spmv_map_id', true) || get_post_meta($parent_id, '_mvx_spmv_map_id', true)){
                        $product_map_id = (get_post_meta($product_id, '_mvx_spmv_map_id', true)) ? get_post_meta($product_id, '_mvx_spmv_map_id', true) : 0;
                        $product_map_id = (get_post_meta($parent_id, '_mvx_spmv_map_id', true)) ? get_post_meta($parent_id, '_mvx_spmv_map_id', true) : $product_map_id;
                        $data['product_map_id'] = $product_map_id;
                    }
                    
                    $map_id = mvx_spmv_products_map($data, 'insert');
                    if($map_id){
                        $data['product_map_id'] = $map_id;
                        $data['product_id'] = $parent_id;
                        mvx_spmv_products_map($data, 'insert');
                        //update meta
                        update_post_meta($product_id, '_mvx_spmv_product', true);
                        update_post_meta($parent_id, '_mvx_spmv_product', true);
                        update_post_meta($product_id, '_mvx_spmv_map_id', $map_id);
                        update_post_meta($parent_id, '_mvx_spmv_map_id', $map_id);
                    }
                }else{
                    delete_post_meta($product_id, '_mvx_child_product');
                }
            }
            // SPMV terms object update
            do_mvx_spmv_set_object_terms();
            $exclude_spmv_products = get_mvx_spmv_excluded_products_map_data();
            set_transient('mvx_spmv_exclude_products_data', $exclude_spmv_products, YEAR_IN_SECONDS);

        }else{
            update_option('spmv_multivendor_table_migrated', true);
            wp_clear_scheduled_hook('migrate_spmv_multivendor_table');
        }
    }

    public function mvx_spmv_excluded_products_map() {
        do_mvx_spmv_set_object_terms();
        $exclude_spmv_products = get_mvx_spmv_excluded_products_map_data();
        set_transient('mvx_spmv_exclude_products_data', $exclude_spmv_products, YEAR_IN_SECONDS);
    }
    
    public function mvx_spmv_product_meta_update() {
        $products_map_data = get_mvx_spmv_products_map_data();
        if($products_map_data){
            foreach ($products_map_data as $product_map_id => $product_ids) {
                if($product_ids){
                    foreach ($product_ids as $product_id) {
                        $is_mvx_spmv_product = get_post_meta($product_id, '_mvx_spmv_product', true);
                        $has_mvx_spmv_map_id = get_post_meta($product_id, '_mvx_spmv_map_id', true);
                        if(!$is_mvx_spmv_product || !$has_mvx_spmv_map_id){
                            update_post_meta($product_id, '_mvx_spmv_product', true);
                            update_post_meta($product_id, '_mvx_spmv_map_id', $product_map_id);
                        }
                    }
                }
            }
            do_mvx_spmv_set_object_terms();
            $exclude_spmv_products = get_mvx_spmv_excluded_products_map_data();
            set_transient('mvx_spmv_exclude_products_data', $exclude_spmv_products, YEAR_IN_SECONDS);
            update_option('mvx_spmv_product_meta_migrated', true);
        }
    }
    
    public function mvx_reset_product_mapping_data($map_id){
        do_mvx_spmv_set_object_terms($map_id);
        $exclude_spmv_products = get_mvx_spmv_excluded_products_map_data();
        set_transient('mvx_spmv_exclude_products_data', $exclude_spmv_products, YEAR_IN_SECONDS);
    }
    
    public function mvx_orders_migration() {
        global $MVX, $wpdb;

        $vendors = get_mvx_vendors();
        if ($vendors) {
            foreach ($vendors as $vendor) {
                $vendor_orders = get_mvx_vendor_orders(array('vendor_id' => $vendor->id));
                if($vendor_orders){
                    $vendor_done_commissions_ids = array();
                    foreach ($vendor_orders as $vorder) {
                        if(!in_array($vorder->commission_id, $vendor_done_commissions_ids)){
                            $vendor_done_commissions_ids[] = $vorder->commission_id;
                            $commission_specific_orders = get_mvx_vendor_orders(array('vendor_id' => $vendor->id, 'commission_id' => $vorder->commission_id));
                            $items = array();
                            $commission_specific_orders_ids_done = array();
                            foreach ($commission_specific_orders as $corder) {
                                $order = wc_get_order($corder->order_id);
                                if(!$order){
                                    continue;
                                }
                                $vendor_specific_order_migrated = (get_post_meta($corder->order_id, '_mvx_vendor_specific_order_migrated', true)) ? get_post_meta($corder->order_id, '_mvx_vendor_specific_order_migrated', true) : array();
                                if(in_array($vendor->id, $vendor_specific_order_migrated) || in_array($corder->order_id, $commission_specific_orders_ids_done)){
                                    continue;
                                }
                                $vendor_specific_order_migrated[] = $vendor->id;
                                $commission_specific_orders_ids_done[] = $corder->order_id;

                                $items = $order->get_items();
                                $vendor_items = array();
                                foreach ($items as $item_id => $item) {
                                    if (isset($item['product_id']) && $item['product_id'] !== 0) {
                                        // check vendor product
                                        $has_vendor = get_mvx_product_vendors($item['product_id']);
                                        if ($has_vendor && $has_vendor->id == $vendor->id) {
                                            $vendor_items['commission'] = $corder->commission_amount;
                                            $vendor_items['commission_rate'] = array();
                                            $vendor_items[$item_id] = $item;
                                        }
                                    }
                                }
                                try {
                                    // migrate old orders
                                    require_once ( $MVX->plugin_path . '/classes/class-mvx-order.php' );
                                    $vendor_order_id = MVX_Order::create_vendor_order(array(
                                            'order_id' => $vorder->order_id,
                                            'vendor_id' => $vendor->id,
                                            'posted_data' => array(),
                                            'line_items' => $vendor_items
                                    ), true);
                                    // mark as shipped
                                    $shippers = get_post_meta($vorder->order_id, 'dc_pv_shipped', true) ? get_post_meta($vorder->order_id, 'dc_pv_shipped', true) : array();
                                    if (in_array($vendor->id, $shippers)) {
                                        update_post_meta($vendor_order_id, 'dc_pv_shipped', $shippers);
                                        // set new meta shipped
                                        update_post_meta($vendor_order_id, 'mvx_vendor_order_shipped', 1);
                                    }
                                    // add commission id in order meta
                                    update_post_meta($vendor_order_id, '_commission_id', $vorder->commission_id);
                                    // add order id with commission meta
                                    update_post_meta($vorder->commission_id, '_commission_order_id', $vendor_order_id);
                                    // for track BW vendor order-commission
                                    update_post_meta($vendor_order_id, '_old_order_id', $vorder->order_id);
                                    // prevent duplication
                                    update_post_meta($corder->order_id, '_mvx_vendor_specific_order_migrated', $vendor_specific_order_migrated);
                                    do_action( 'mvx_orders_migration_order_created', $vendor_order_id, $vorder );  
                                } catch (Exception $exc) {
                                    doProductVendorLOG("Error in order migration create order :".$exc->getMessage());
                                }
                            }
                        }
                    }
                }
            }
        }
        update_option('mvx_orders_table_migrated', true);
        wp_clear_scheduled_hook('mvx_orders_migration');
    }

}
