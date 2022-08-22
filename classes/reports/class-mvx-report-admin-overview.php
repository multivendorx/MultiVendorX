<?php

/**
 * MVX Report Admin Overview
 *
 * @author      Multivendor X
 * @category    Vendor
 * @package MultivendorX/Reports
 * @version     3.5.0
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MVX_Report_Admin_overview extends WC_Admin_Report {

    function __construct() {}

    /**
     * Output the report
     */
    public function output_report() {
        global $wpdb, $woocommerce, $MVX;

        $ranges = array(
            'year' => __('Year', 'multivendorx'),
            'last_month' => __('Last Month', 'multivendorx'),
            'month' => __('This Month', 'multivendorx'),
            '7day' => __('Last 7 Days', 'multivendorx')
        );

        $current_range = ( isset($_GET['range']) && !empty($_GET['range']) ) ? sanitize_text_field($_GET['range']) : '7day';

        if (!in_array($current_range, array('custom', 'year', 'last_month', 'month', '7day'))) {
            $current_range = '7day';
        }

        $this->calculate_current_range($current_range);

        $start_date = $this->start_date;
        $end_date = $this->end_date;
        $end_date = strtotime('+1 day', $end_date);

        $sales = $gross_sales = $vendor_earning = $admin_earning = $pending_vendors = $vendors = $products = $transactions = 0;
        
        $args = apply_filters('mvx_report_admin_overview_query_args', array(
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

        $qry = new WP_Query($args);
        $orders = apply_filters('mvx_report_admin_overview_orders', $qry->get_posts());

         if ( !empty( $orders ) ) {
            foreach ( $orders as $order_obj ) {
                $order = wc_get_order($order_obj->ID);
                $sales += $order->get_subtotal();
                $mvx_suborders = get_mvx_suborders($order_obj->ID);
                if(!empty($mvx_suborders)) {
                    foreach ($mvx_suborders as $suborder) {
                        $vendor_order = mvx_get_order($suborder->get_id());
                        if( $vendor_order ){
                            $gross_sales += $suborder->get_total( 'edit' );
                            $vendor_earning += $vendor_order->get_commission_total('edit');
                        }
                    }
                }
            }
            $admin_earning = $gross_sales - $vendor_earning;
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
            $vendors = count($user_query->results);
        
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
            
        include( $MVX->plugin_path . '/classes/reports/views/html-mvx-report-by-admin-overview.php');
    }

}
