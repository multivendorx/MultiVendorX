<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/plain/new-coupon.php
 *
 * @author 		Multivendor X
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */
 
if ( !defined( 'ABSPATH' ) ) exit; 
global  $MVX;

echo "= " . $email_heading . " =\n\n";

echo sprintf( __( "Hi there! This is to notify that a new product has been submitted in %s.",  'dc-woocommerce-multi-vendor' ), get_option( 'blogname' ) ); 
echo '\n'; 
echo sprintf(  __( "Product title: %s",  'dc-woocommerce-multi-vendor' ), $coupon_name ); 
echo '\n'; 
echo sprintf(  __( "Submitted by: %s",  'dc-woocommerce-multi-vendor' ), $vendor_name ); 
echo '\n'; 
$coupon_link = apply_filters( 'mvx_email_vendor_new_coupon_link', esc_url( get_edit_post_link( $post_id )));
echo sprintf(  __( "Edit product: %s",  'dc-woocommerce-multi-vendor' ), $coupon_link ); 
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
echo apply_filters( 'mvx_email_footer_text', get_option( 'mvx_email_footer_text' ) );