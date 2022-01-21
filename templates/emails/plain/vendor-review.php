<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/plain/vendor-review.php
 *
 * @author 		Multivendor X
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $MVX;
$review = isset( $review ) ? $review : '';
$rating = isset( $rating ) ? absint($rating) : 0;
echo $email_heading . "\n"; 
echo "****************************************\n\n";
echo __( "Hi,",  'dc-woocommerce-multi-vendor' ); 
echo "\n\n";
echo sprintf(  __( "A new Review has been added by - %s",  'dc-woocommerce-multi-vendor' ), $customer_name );
echo "\n"; 
echo sprintf(  __( "Vendor Name : %s",  'dc-woocommerce-multi-vendor' ), $vendor->page_title );
echo "\n";
	if(!empty($rating)){
		echo sprintf(  __( "Rating : %s out of 5",  'dc-woocommerce-multi-vendor' ), $rating );
echo "\n";
	}
echo sprintf(  __( "Comment : %s",  'dc-woocommerce-multi-vendor' ), $review );
echo "\n";

echo apply_filters( 'mvx_email_footer_text', get_option( 'mvx_email_footer_text' ) );