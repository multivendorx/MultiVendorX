<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/plain/suspend-vendor-account.php
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

echo sprintf( __("Hello %s,", 'dc-woocommerce-multi-vendor' ), $user_login );

echo sprintf( __("'We are sorry to inform you that your following account with us on %s has been suspended.", 'dc-woocommerce-multi-vendor' ), get_option( 'blogname' ) );
echo '\n';

echo sprintf( __( "Username: %s", 'dc-woocommerce-multi-vendor' ), $user_login );
echo '\n';
echo _e( "Status: Suspended", 'dc-woocommerce-multi-vendor' ); 
echo '\n';
echo sprintf( __( "Login URL: %s", 'dc-woocommerce-multi-vendor' ), mvx_get_vendor_dashboard_endpoint_url( 'dashboard' ) ); 
echo '\n';
echo _e('Kindly contact your Administrator for further details.', 'dc-woocommerce-multi-vendor', 'dc-woocommerce-multi-vendor');

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
echo apply_filters( 'mvx_email_footer_text', get_option( 'mvx_email_footer_text' ) );

?>