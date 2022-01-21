<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/plain/vemdor-followed.php
 *
 * @author 		Multivendor X
 * @package 	dc-product-vendor/Templates
 * @version   	0.0.1
 */
 
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $MVX;

echo "= " . $email_heading . " =\n\n";

echo sprintf( __( "Hi there! This is to notify that a new product has been submitted in %s.",  'dc-woocommerce-multi-vendor' ), get_option( 'blogname' ) );

if($post->post_type == 'product') {
	$product = wc_get_product( $post->ID );
	$product_link = $product ? $product->get_permalink() : '';
	$title = esc_html__( 'Product', 'dc-woocommerce-multi-vendor' );
	echo sprintf( __( "A new product is created %s: %s",  'dc-woocommerce-multi-vendor' ), $title, $product_link ) . "\n\n";
	echo '\n';
} else {
	esc_html_e('A new coupon is created:', 'dc-woocommerce-multi-vendor');
	echo '\n';
}
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
echo apply_filters( 'mvx_email_footer_text', get_option( 'mvx_email_footer_text' ) );
?>