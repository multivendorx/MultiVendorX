<?php
/**
 * Vendor Product search form.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/widget/vendor-product-searchform.php
 *
 * @author 		Multivendor X
 * @package 	dc-product-vendor/Templates
 * @version     3.5.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<form role="search" method="get" class="mvx-vproduct-search woocommerce-product-search" action="">
	<label class="screen-reader-text" for="woocommerce-product-search-field-<?php echo isset( $index ) ? absint( $index ) : 0; ?>"><?php esc_html_e( 'Search for:', 'dc-woocommerce-multi-vendor' ); ?></label>
	<input type="search" id="woocommerce-product-search-field-<?php echo isset( $index ) ? absint( $index ) : 0; ?>" class="search-field" placeholder="<?php echo esc_attr__( 'Search products&hellip;', 'dc-woocommerce-multi-vendor' ); ?>" value="<?php echo esc_attr(get_search_query()); ?>" name="s" />
	<button type="submit" value="<?php echo esc_attr_x( 'Search', 'submit button', 'dc-woocommerce-multi-vendor' ); ?>"><?php echo esc_html_x( 'Search', 'submit button', 'dc-woocommerce-multi-vendor' ); ?></button>
	<input type="hidden" name="post_type" value="product" />
</form>