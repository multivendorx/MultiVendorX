<?php

/**
 * MVX Report Banking Overview
 *
 * @author 		MultiVendorX
 * @category    Vendor
 * @package MultiVendorX/Reports
 * @version     3.5.0
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MVX_Report_Banking_overview extends WC_Admin_Report {

    /**
     * Output the report
     */
    public function output_report() {
        global $wpdb, $woocommerce, $MVX;
        $vendor = $vendor_id = $order_items = false;

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

        wp_localize_script('mvx_report_js', 'mvx_report_banking', array(
            'start_date' => $start_date,
            'end_date' => $end_date
        ));

        $table = __( 'Please Select a vendor first', 'multivendorx' );
        if (isset($_POST['banking_overview_vendor'])) {
            $vendor_id = absint($_POST['banking_overview_vendor']);
        }

        if ($vendor_id && $vendor) {
            $option = '<option value="' . $vendor_id . '" selected="selected">' . $vendor->page_title . '</option>';
        } else {
            $option = '<option></option>';
        }

        include( $MVX->plugin_path . '/classes/reports/views/html-mvx-report-banking-overview.php');
    }

}
