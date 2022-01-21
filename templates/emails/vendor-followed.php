<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/vendor-followed.php
 *
 * @author 		Multivendor X
 * @package 	dc-product-vendor/Templates
 * @version   	3.7
 */

if ( !defined( 'ABSPATH' ) ) exit; 
global  $MVX;

if($post->post_type == 'shop_coupon') $title = esc_html__( 'Coupon', 'dc-woocommerce-multi-vendor' );
else  $title = esc_html__( 'Product', 'dc-woocommerce-multi-vendor' );

$vendor = get_mvx_vendor( $post->post_author );
$product = wc_get_product( $post->ID );
$product_link = $product ? $product->get_permalink() : '';
$product_name = $product->get_title();

?>
<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

	<p><?php printf( esc_html__( "Hi there! This is a notification about a new %s on %s.",  'dc-woocommerce-multi-vendor' ), $title, get_option( 'blogname' ) ); ?></p>
	<p>
		<?php 
        if ($product_link) {
            printf( esc_html__( "%s title: %s",  'dc-woocommerce-multi-vendor' ), $title, $product_link ); 
        }
        ?><br/>
		<?php printf( esc_html__( "Submitted by: %s",  'dc-woocommerce-multi-vendor' ), $vendor->page_title ); ?><br/><br/>
	</p>

<?php do_action( 'mvx_email_footer' ); ?>