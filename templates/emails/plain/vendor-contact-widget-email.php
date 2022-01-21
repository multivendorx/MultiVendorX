<?php
/**
 * The template for displaying vendor contact email via customer.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/plain/vendor-contact-widget-email.php
 *
 * @author 	Multivendor X
 * @package 	dc-product-vendor/Templates
 * @version   3.3.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $MVX;
$name = isset( $object['name'] ) ? $object['name'] : '';
$message = isset( $object['message'] ) ? $object['message'] : '';
echo $email_heading . "\n\n"; 
printf(__( "Hello %s,\n\nA customer is trying to contact you. Details are as follows:", 'dc-woocommerce-multi-vendor' ),  $vendor->page_title); 
echo "****************************************************\n\n";
echo __( 'Name', 'dc-woocommerce-multi-vendor' ).' : '.$name;
echo "\n";
echo __( 'Message', 'dc-woocommerce-multi-vendor' ).' : '.$message;

echo apply_filters( 'mvx_email_footer_text', get_option( 'mvx_email_footer_text' ) );