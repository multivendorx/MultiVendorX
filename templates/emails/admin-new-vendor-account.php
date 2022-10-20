<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/MultiVendorX/emails/admin-new-vendor-account.php
 *
 * @author 		MultiVendorX
 * @package MultiVendorX/Templates
 * @version   0.0.1
 */
 
global $MVX;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
$vendor_application_admin_url = apply_filters('mvx_admin_new_vendor_email_vendor_application_url', admin_url( 'admin.php?page=vendors&s='.$user_object->user_login ));
?>
<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php printf( esc_html__( "A new user has applied to be a vendor on %s. His/her email is %s.", 'multivendorx' ), esc_html( $blogname ), '<strong>' . esc_html( $user_object->user_email ) . '</strong>' ); ?></p>

<p><?php printf( esc_html__( "You can access vendor application here: %s.", 'multivendorx' ), esc_url( $vendor_application_admin_url ) ); ?></p>

<?php do_action( 'mvx_email_footer' ); ?>