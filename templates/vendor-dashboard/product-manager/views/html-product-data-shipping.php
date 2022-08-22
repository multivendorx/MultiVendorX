<?php

/**
 * Shipping product tab template
 *
 * Used by mvx-afm-add-product.php template
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/vendor-dashboard/product-manager/views/html-product-data-shipping.php
 *
 * @author 		MultiVendorX
 * @package MultiVendorX/Templates
 * @version   3.3.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div role="tabpanel" class="tab-pane fade" id="shipping_product_data">
    <div class="row-padding"> 
        <?php if ( wc_product_weight_enabled() ) : ?> 
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_weight"><?php esc_html_e( 'Weight', 'multivendorx' ) . ' (' . get_option( 'woocommerce_weight_unit' ) . ')' ?></label>
                <div class="col-md-6 col-sm-9">
                    <input class="form-control" type="text" id="_weight" name="_weight" value="<?php echo isset($_POST['_weight']) ? absint($_POST['_weight']) : $product_object->get_weight( 'edit' ); ?>" placeholder="<?php echo wc_format_localized_decimal( 0 ); ?>" />
                </div>
            </div> 
        <?php endif; ?>
        <?php if ( wc_product_dimensions_enabled() ) : ?> 
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="product_length"><?php printf( __( 'Dimensions (%s)', 'multivendorx' ), get_option( 'woocommerce_dimension_unit' ) ); ?></label>
                <div class="col-md-6 col-sm-9">
                    <div class="row">
                        <div class="col-md-4">
                            <input class="form-control col-md-4" id="product_length" placeholder="<?php esc_attr_e( 'Length', 'multivendorx' ); ?>" class="input-text wc_input_decimal" size="6" type="text" name="_length" value="<?php echo isset($_POST['_length']) ? absint($_POST['_length']) : esc_attr( wc_format_localized_decimal( $product_object->get_length( 'edit' ) ) ); ?>" />
                        </div>
                        <div class="col-md-4">
                            <input class="form-control col-md-4" placeholder="<?php esc_attr_e( 'Width', 'multivendorx' ); ?>" class="input-text wc_input_decimal" size="6" type="text" name="_width" value="<?php echo isset($_POST['_width']) ? absint($_POST['_width']) : esc_attr( wc_format_localized_decimal( $product_object->get_width( 'edit' ) ) ); ?>" />
                        </div>
                        <div class="col-md-4">
                            <input class="form-control col-md-4" placeholder="<?php esc_attr_e( 'Height', 'multivendorx' ); ?>" class="input-text wc_input_decimal last" size="6" type="text" name="_height" value="<?php echo isset($_POST['_height']) ? absint($_POST['_height']) : esc_attr( wc_format_localized_decimal( $product_object->get_height( 'edit' ) ) ); ?>" />
                        </div>
                    </div>
                </div>
            </div> 
        <?php endif; ?>
        <?php do_action( 'mvx_afm_product_options_dimensions', $post->ID, $product_object, $post ); ?> 
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="product_shipping_class"><?php esc_html_e( 'Shipping class', 'multivendorx' ); ?></label>
            <div class="col-md-6 col-sm-9">
                <select name="product_shipping_class" id="product_shipping_class" class="form-control regular-select">
                    <?php foreach ( get_current_vendor_shipping_classes() as $key => $class_name  ) : ?>
                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $product_object->get_shipping_class_id( 'edit' ), $key ); ?>><?php echo esc_html( $class_name ); ?></option>
                    <?php endforeach; ?>
                    <option value="-1"><?php esc_html_e( 'No shipping class', 'multivendorx' ); ?></option>
                </select>
            </div>
        </div> 
        <?php do_action( 'mvx_afm_product_options_shipping', $post->ID, $product_object, $post ); ?> 
    </div>
</div>