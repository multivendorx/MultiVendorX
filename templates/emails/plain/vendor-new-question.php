<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/plain/vendor-new-question.php
 *
 * @author 		Multivendor X
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */
 
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $MVX;
$question = isset( $question ) ? $question : '';
echo $email_heading . "\n\n"; 
echo sprintf(  __( "Hi %s,",  'dc-woocommerce-multi-vendor' ), $vendor->page_title ); 
echo '\n\n';
echo sprintf(  __( "A new query has been added by your potential buyer - %s",  'dc-woocommerce-multi-vendor' ), $customer_name ); 
echo '\n';
echo sprintf(  __( "Product name : %s",  'dc-woocommerce-multi-vendor' ), $product_name );
echo '\n';
echo sprintf(  __( "Query : %s",  'dc-woocommerce-multi-vendor' ), $question );
echo '\n';
$question_link = apply_filters( 'mvx_vendor_plain_question_redirect_link', esc_url( mvx_get_vendor_dashboard_endpoint_url(get_mvx_vendor_settings('mvx_vendor_products_qnas_endpoint', 'seller_dashbaord', 'products-qna'))) ); 
echo sprintf(  __( "You can approve or reject query from here : %s",  'dc-woocommerce-multi-vendor' ), $question_link );
echo '\n\n';
echo sprintf( __( 'Note: Quick replies help to maintain a friendly customer-buyer relationship', 'dc-woocommerce-multi-vendor'));

echo apply_filters( 'mvx_email_footer_text', get_option( 'mvx_email_footer_text' ) );