<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/plain/new-admin-product.php
 *
 * @author 		Multivendor X
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */

if ( !defined( 'ABSPATH' ) ) exit; 
global $MVX;


echo "= " . $email_heading . " =\n\n";

echo sprintf( __( "Hi there! This is to notify that a new product has been submitted in %s.",  'dc-woocommerce-multi-vendor' ), get_option( 'blogname' ) );
echo '\n'; 
echo sprintf( __( "Product title: %s",  'dc-woocommerce-multi-vendor' ), $product_name ); 
echo '\n';
echo sprintf( __( "Submitted by: %s",  'dc-woocommerce-multi-vendor' ), 'Site Administrator' ); 
echo '\n';
$product_link = apply_filters( 'mvx_email_admin_new_product_link', esc_url( mvx_get_vendor_dashboard_endpoint_url( get_mvx_vendor_settings( 'mvx_edit_product_endpoint', 'seller_dashbaord', 'edit-product' ), $post_id ) ) );
if($submit_product) {
	printf( __( "Edit product: %s",  'dc-woocommerce-multi-vendor' ), $product_link ); 
} else {
	printf( __( "View product: %s",  'dc-woocommerce-multi-vendor' ), get_permalink($post_id)); 
}

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
echo apply_filters( 'mvx_email_footer_text', get_option( 'mvx_email_footer_text' ) );