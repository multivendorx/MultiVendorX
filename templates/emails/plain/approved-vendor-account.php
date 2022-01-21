<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/plain/approved-vendor-account.php
 *
 * @author 		Multivendor X
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */
 
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $MVX;

echo "= " . $email_heading . " =\n\n";
echo sprintf( __("Congratulations! Your vendor application on %s has been approved!", 'dc-woocommerce-multi-vendor' ), get_option( 'blogname' ) );
echo '\n';
echo sprintf( __( "Application Status: %s", 'dc-woocommerce-multi-vendor' ), 'Approved' );
echo '\n';
echo sprintf( __( "Applicant Username: %s", 'dc-woocommerce-multi-vendor' ), $user_login ); 
echo '\n';
echo _e('You have been cleared for landing! Congratulations and welcome aboard!', 'dc-woocommerce-multi-vendor');

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
echo apply_filters( 'mvx_email_footer_text', get_option( 'mvx_email_footer_text' ) );

?>