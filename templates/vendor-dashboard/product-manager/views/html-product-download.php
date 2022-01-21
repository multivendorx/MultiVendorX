<?php

/**
 * Add Downloadable file template
 * Override this template by copying it to yourtheme/dc-product-vendor/vendor-dashboard/product-manager/views/html-product-download.php
 *
 * @author  Multivendor X
 * @package     MVX/Templates
 * @version   3.3.0
 */
defined( 'ABSPATH' ) || exit;
?>
<tr>
	<td class="sort"><span class="sortable-icon"></span></td>
	<td class="file_name">
		<input type="text" class="input_text form-control" placeholder="<?php esc_attr_e( 'File name', 'dc-woocommerce-multi-vendor' ); ?>" name="_wc_file_names[]" value="<?php echo esc_attr( $file['name'] ); ?>" />
		<input type="hidden" name="_wc_file_hashes[]" value="<?php echo esc_attr( $key ); ?>" />
	</td>
	<td class="file_url"><input type="text" class="input_text form-control" placeholder="<?php esc_attr_e( 'http://', 'dc-woocommerce-multi-vendor' ); ?>" name="_wc_file_urls[]" value="<?php echo esc_attr( $file['file'] ); ?>" /></td>
	<td class="file_url_choose"><a href="#" class="button upload_file_button" data-choose="<?php esc_attr_e( 'Choose file', 'dc-woocommerce-multi-vendor' ); ?>" data-update="<?php esc_attr_e( 'Insert file URL', 'dc-woocommerce-multi-vendor' ); ?>" title="<?php echo esc_html__( 'Choose file', 'dc-woocommerce-multi-vendor' ); ?>"><i class="mvx-font ico-upload-image-icon"></i></a></td>
	<td><a href="#" class="delete" title="<?php esc_html_e( 'Delete', 'dc-woocommerce-multi-vendor' ); ?>"><i class="mvx-font ico-delete-icon"></i></a></td>
</tr>