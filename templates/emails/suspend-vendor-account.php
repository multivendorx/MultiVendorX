<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/suspend-vendor-account.php
 *
 * @author 		Multivendor X
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */
 
global $MVX;
//
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
?>
<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php printf( esc_html__('Hello %s,', 'dc-woocommerce-multi-vendor'), $user_login ); ?> <p>

<p><?php printf( esc_html__('We are sorry to inform you that your following account with us on %s has been suspended.', 'dc-woocommerce-multi-vendor'), get_option( 'blogname' ) ); ?> <p>
<p>
	<?php printf( esc_html__( "Username: %s",  'dc-woocommerce-multi-vendor' ), $user_login ); ?><br/>
	<?php _e( "Status: Suspended",  'dc-woocommerce-multi-vendor' ); ?><br/>
	<?php printf( esc_html__( "Login URL: %s",  'dc-woocommerce-multi-vendor' ), mvx_get_vendor_dashboard_endpoint_url( 'dashboard' ) ); ?><br/>
	
</p>
<p><?php _e('Kindly contact your Administrator for further details. ', 'dc-woocommerce-multi-vendor'); ?> <p>
<?php do_action( 'mvx_email_footer' );?>