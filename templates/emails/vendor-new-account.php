<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/vendor-new-account.php
 *
 * @author 		Multivendor X
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
global  $MVX;
?>
<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php printf( esc_html__( "Thanks for creating an account on %s. We will process your application and revert shortly.",  'dc-woocommerce-multi-vendor' ), esc_html( $blogname ), esc_html( $user_login ) ); ?></p>
<?php if ( get_option( 'woocommerce_registration_generate_password' ) == 'yes' && $password_generated ) : ?>
<p><?php printf( esc_html__( "Your password has been automatically generated: %s",  'dc-woocommerce-multi-vendor' ), '<strong>' . esc_html( $user_pass ) . '</strong>' ); ?></p>
<?php endif; ?>
<p><?php printf( esc_html__( 'You can access your account area here: %s.',  'dc-woocommerce-multi-vendor' ), get_permalink( mvx_vendor_dashboard_page_id() ) ); ?></p>

<?php do_action( 'mvx_email_footer' ); ?>