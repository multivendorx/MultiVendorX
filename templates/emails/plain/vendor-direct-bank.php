<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/MultiVendorX/emails/vendor-direct-bank.php
 *
 * @author 		MultiVendorX
 * @package MultiVendorX/Templates
 * @version   0.0.1
 */

 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $MVX;
echo $email_heading . "\n\n"; 
$amount = floatval(get_post_meta($transaction_id, 'amount', true)) - floatval(get_post_meta($transaction_id, 'transfer_charge', true)) - floatval(get_post_meta($transaction_id, 'gateway_charge', true));		
echo apply_filters( 'mvx_thankyou_transaction_received_text', sprintf(__( 'Dear %s,<br>I hope this email finds you well. We would like to inform you that we have received your request for commission withdrawal.<br>Our administrative team will be processing your commission withdrawal shortly.', 'multivendorx'), $vendor->page_title), $transaction_id );

echo "****************************************************\n\n";




$commission_details  = $MVX->transaction->get_transaction_item_details($transaction_id); 
if(!empty($commission_details['body'])) {
	foreach ( $commission_details['body'] as $commission_detail ) {	
		foreach($commission_detail as $details) {
			foreach($details as $detail_key => $detail) {
					echo $detail_key .' : '. $detail.'\n'; 
			}
		}
	}
}
echo "----------\n\n";
if ( $totals =  $MVX->transaction->get_transaction_item_totals($transaction_id, $vendor) ) {
	foreach ( $totals as $total ) {
		echo $total['label'] .' : '. $total['value'].'\n';
	}
}
echo "\n****************************************************\n\n";
echo apply_filters( 'mvx_email_footer_text', get_option( 'mvx_email_footer_text' ) );