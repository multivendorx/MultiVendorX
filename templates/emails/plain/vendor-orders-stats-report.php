<?php
/**
 * The template for displaying vendor stats report.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/plain/vendor-orders-stats-report.php
 *
 * @author 	Multivendor X
 * @package 	dc-product-vendor/Templates
 * @version   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $MVX;
$t_sale = isset($report_data['stats']['sales_total']) ? $report_data['stats']['sales_total'] : 0;
$t_earning = isset($report_data['stats']['earning']) ? $report_data['stats']['earning'] : 0;
$t_withdrawal = isset($report_data['stats']['withdrawal']) ? $report_data['stats']['withdrawal'] : 0;
$t_orders_no = isset($report_data['stats']['orders_no']) ? $report_data['stats']['orders_no'] : 0;
echo $email_heading . "\n\n"; 
printf(__( 'Hello %s,<br>Your %s store orders report stats are as follows:', 'dc-woocommerce-multi-vendor' ),  $vendor->page_title, $report_data['period']); 
echo "****************************************************\n\n";

printf(__( '%s sale: %s', 'dc-woocommerce-multi-vendor' ), ucfirst($report_data['period']), wc_price($t_sale));
printf(__( '%s earning: %s', 'dc-woocommerce-multi-vendor' ), ucfirst($report_data['period']), wc_price($t_earning));
printf(__( '%s withdrawal: %s', 'dc-woocommerce-multi-vendor' ), ucfirst($report_data['period']), wc_price($t_withdrawal));
printf(__( '%s no of orders: %s', 'dc-woocommerce-multi-vendor' ), ucfirst($report_data['period']), $t_orders_no);
echo __( 'Period', 'dc-woocommerce-multi-vendor' ).' : '.isset($report_data['period']) ? ucfirst($report_data['period']) : '';
echo __( 'From Date', 'dc-woocommerce-multi-vendor' ).' : '.isset($report_data['start_date']) ? $report_data['start_date'] : '';
echo __( 'To Date', 'dc-woocommerce-multi-vendor' ).' : '.isset($report_data['end_date']) ? $report_data['end_date'] : '';

echo "\n****************************************************\n";
if($attachments && count($attachments) > 0 && $report_data['order_data'] && count($report_data['order_data']) > 0 ){
    echo __( 'Please find your report attachment', 'dc-woocommerce-multi-vendor' );
}else{
    echo __( 'There is no stats report available.', 'dc-woocommerce-multi-vendor' );
}
echo "\n****************************************************\n\n";
echo apply_filters( 'mvx_email_footer_text', get_option( 'mvx_email_footer_text' ) );