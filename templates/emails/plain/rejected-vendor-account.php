<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/plain/rejected-vendor-account.php
 *
 * @author 		Multivendor X
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $MVX;

echo $email_heading . "\n\n";

echo sprintf( __( "Thanks for creating an account as Pending Vendor on %s. But your request has been rejected due to some reason.",  'dc-woocommerce-multi-vendor' ), $blogname ) . "\n\n";

echo "\n****************************************************\n\n";

echo apply_filters( 'mvx_email_footer_text', get_option( 'mvx_email_footer_text' ) );