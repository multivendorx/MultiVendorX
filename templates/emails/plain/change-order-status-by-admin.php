<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/plain/change-order-status-by-admin.php
 *
 * @author 		Multivendor X
 * @package 	dc-product-vendor/Templates
 * @version   	3.7.2
 */

if ( !defined( 'ABSPATH' ) ) exit; 
global $MVX;


echo "= " . $email_heading . " =\n\n";

echo sprintf( __( "Hi there! This is to notify that an order #%s status has been changed on %s.",  'dc-woocommerce-multi-vendor' ), $order_id, get_option( 'blogname' ) );
echo '\n'; 

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
echo apply_filters( 'mvx_email_footer_text', get_option( 'mvx_email_footer_text' ) );