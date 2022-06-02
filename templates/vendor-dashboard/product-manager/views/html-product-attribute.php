<?php

/**
 * Add Attribute template
 * Override this template by copying it to yourtheme/dc-product-vendor/vendor-dashboard/product-manager/views/html-product-attribute.php
 *
 * @author  Multivendor X
 * @package     MVX/Templates
 * @version   3.3.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div data-taxonomy="<?php echo esc_attr($attribute->get_taxonomy()); ?>" class="mvx-metabox-wrapper woocommerce_attribute wc-metabox closed <?php echo esc_attr(implode(' ', $metabox_class)); ?>" rel="<?php echo esc_attr($attribute->get_position()); ?>">
    <div class="mvx-metabox-title variation-title" data-toggle="collapse" data-target="#attribute_<?php echo esc_attr( $i ); ?>"  aria-expanded="false" aria-controls="collapseExample">
        <div class="variation-select-group">
            <span class="sortable-icon"></span>
            <strong class="attribute_name"><?php echo esc_html(wc_attribute_label($attribute->get_name())); ?></strong>
        </div>
        <div class="mvx-metabox-action variation-action">
            <i class="mvx-font ico-up-arrow-icon"></i>
            <a href="#" class="remove_row delete remove-attribute"><?php esc_html_e('Remove', 'multivendorx'); ?></a>
        </div>
    </div>
    
    <div class="mvx-metabox-content woocommerce_attribute_data wc-metabox-content collapse" id="attribute_<?php echo esc_attr( $i ); ?>">
        <table cellpadding="0" cellspacing="0" class="table">
            <tbody>
                <tr>
                    <td class="attribute_name">
                        <label><?php esc_html_e('Name', 'multivendorx'); ?>:</label>

                        <?php if ($attribute->is_taxonomy()) : ?>
                            <strong><?php echo esc_html(wc_attribute_label($attribute->get_name())); ?></strong>
                            <input type="hidden" name="wc_attributes[<?php echo esc_attr($i); ?>][name]" value="<?php echo esc_attr($attribute->get_name()); ?>" />
                            <input type="hidden" name="wc_attributes[<?php echo esc_attr($i); ?>][tax_name]" value="<?php echo esc_attr($attribute->get_name()); ?>" />
                        <?php else : ?>
                            <input type="text" class="attribute_name form-control" name="wc_attributes[<?php echo esc_attr($i); ?>][name]" value="<?php echo esc_attr($attribute->get_name()); ?>" />
                        <?php endif; ?>

                        <input type="hidden" name="wc_attributes[<?php echo esc_attr($i); ?>][position]" class="attribute_position" value="<?php echo esc_attr($attribute->get_position()); ?>" />
                    </td>
                    <td rowspan="3" width="65%">
                        <label><?php esc_html_e('Value(s)', 'multivendorx'); ?>:</label>
                        <?php
                        if ($attribute->is_taxonomy() && $attribute_taxonomy = $attribute->get_taxonomy_object()) {
                            $attribute_types = wc_get_attribute_types();

                            if (!array_key_exists($attribute_taxonomy->attribute_type, $attribute_types)) {
                                $attribute_taxonomy->attribute_type = 'select';
                            }

                            if ('select' === $attribute_taxonomy->attribute_type) {
                                ?>
                                <select multiple="multiple" data-placeholder="<?php esc_attr_e('Select terms', 'multivendorx'); ?>" class="multiselect attribute_values wc-enhanced-select form-control" name="wc_attributes[<?php echo esc_attr($i); ?>][value][]">
                                    <?php
                                    $args = array(
                                        'orderby' => 'name',
                                        'hide_empty' => 0,
                                    );
                                    $all_terms = get_terms($attribute->get_taxonomy(), apply_filters('woocommerce_product_attribute_terms', $args));
                                    if ($all_terms) {
                                        foreach ($all_terms as $term) {
                                            $options = $attribute->get_options();
                                            $options = !empty($options) ? $options : array();
                                            echo '<option value="' . esc_attr($term->term_id) . '" ' . selected(in_array($term->term_id, $options, true), true, false) . '>' . esc_attr(apply_filters('woocommerce_product_attribute_term_name', $term->name, $term)) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                                <div class="button-group">
                                    <button class="btn btn-default plus select_all_attributes"><?php esc_html_e('Select all', 'multivendorx'); ?></button>
                                    <button class="btn btn-default minus select_no_attributes"><?php esc_html_e('Select none', 'multivendorx'); ?></button>
                                </div>
                                <?php
                            }

                            do_action('mvx_afm_product_option_terms', $attribute_taxonomy, $i);
                        } else {
                            /* translators: %s: WC_DELIMITER */
                            ?><textarea class="form-control" name="wc_attributes[<?php echo esc_attr($i); ?>][value]" cols="5" rows="5" placeholder="<?php printf(esc_attr__('Enter some text, or some attributes by "%s" separating values.', 'multivendorx'), WC_DELIMITER); ?>"><?php echo esc_textarea(wc_implode_text_attributes($attribute->get_options())); ?></textarea><?php
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label><input type="checkbox" class="checkbox" <?php checked($attribute->get_visible(), true); ?> name="wc_attributes[<?php echo esc_attr($i); ?>][visibility]" value="1" /> <?php esc_html_e('Visible on the product page', 'multivendorx'); ?></label>
                    </td>
                </tr>
                <?php
                $enable_variation = apply_filters( 'attribute_tab_enable_variation_checkbox', array( 'variable' ) );
                if ( call_user_func_array( "mvx_is_allowed_product_type", $enable_variation ) ) :
                $show_classes = implode( ' ', preg_filter( '/^/', 'show_if_', $enable_variation ) );
                ?>
                <tr>
                    <td>
                        <div class="enable_variation <?php echo $show_classes; ?>">
                            <label><input type="checkbox" class="checkbox" <?php checked($attribute->get_variation(), true); ?> name="wc_attributes[<?php echo esc_attr($i); ?>][variation]" value="1" /> <?php esc_html_e('Used for variations', 'multivendorx'); ?></label>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
                <?php do_action('mvx_afm_after_product_attribute_settings', $attribute, $i); ?>
            </tbody>
        </table>
    </div>
</div>