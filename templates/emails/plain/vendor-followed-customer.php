<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/MultiVendorX/emails/plain/vemdor-followed-customer.php
 *
 * @author 		MultiVendorX
 * @package MultiVendorX/Templates
 * @version   	0.0.1
 */
 
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $MVX;

echo "= " . $email_heading . " =\n\n";

echo sprintf( __('Dear %s,',  'multivendorx' ), $vendor->page_title );
echo '\n';
esc_html_e('We hope this message finds you well.', 'multivendorx');
echo '\n';
echo sprintf( __( "We wanted to inform you that you have a new follower %s, who has chosen to follow your profile to stay updated on your offerings.",  'multivendorx' ), $customer->user_login );
echo '\n';
esc_html_e('This reflects positively on the quality and appeal of your products/services, and we encourage you to engage with your followers to further enhance your presence on our platform.', 'multivendorx');
echo '\n';

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
echo apply_filters( 'mvx_email_footer_text', get_option( 'mvx_email_footer_text' ) );
?>