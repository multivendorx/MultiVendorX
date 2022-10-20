<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/MultiVendorX/emails/plain/admin-new-vendor-account.php
 *
 * @author 		MultiVendorX
 * @package MultiVendorX/Templates
 * @version   0.0.1
 */
 
global $MVX;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

$vendor_application_admin_url = apply_filters('mvx_admin_new_vendor_email_vendor_application_url', admin_url( 'admin.php?page=vendors&s='.$user_object->user_login ));

echo "= " . $email_heading . " =\n\n";

echo sprintf( __( "A new user has applied to be a vendor on %s. His/her email is %s.", 'multivendorx' ), esc_html( $blogname ), esc_html( $user_object->user_email ) );

echo sprintf( __( 'You can access vendor application here: %s.',  'multivendorx' ), esc_url( $vendor_application_admin_url ) ) . "\n\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'mvx_email_footer_text', get_option( 'mvx_email_footer_text' ) );