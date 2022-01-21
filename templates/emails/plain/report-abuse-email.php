<?php
/**
 * The template for displaying report abuse via customer.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/plain/report-abuse-email.php
 *
 * @author 	Multivendor X
 * @package 	dc-product-vendor/Templates
 * @version   3.3.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $MVX;
$name = isset( $object['name'] ) ? $object['name'] : '';
$from_email = isset( $object['email'] ) ? $object['email'] : '';
$user_message = isset( $object['msg'] ) ? $object['msg'] : '';
$product = wc_get_product( absint( $object['product_id'] ) );
echo $email_heading . "\n\n"; 
$message = sprintf(__("User %s (%s) is reporting an abuse on the following product: \n", 'dc-woocommerce-multi-vendor'), $name, $from_email);
$message .= sprintf(__("Product details: %s (ID: #%s) \n", 'dc-woocommerce-multi-vendor'), $product->get_title(), $product->get_id());

$message .= sprintf(__("Vendor shop: %s \n", 'dc-woocommerce-multi-vendor'), $vendor->page_title);

$message .= sprintf(__("Message: %s\n", 'dc-woocommerce-multi-vendor'), $user_message);
$message .= "\n\n\n";

$message .= sprintf(__("Product page:: %s\n", 'dc-woocommerce-multi-vendor'), get_the_permalink($product->get_id));

echo apply_filters( 'mvx_email_footer_text', get_option( 'mvx_email_footer_text' ) );