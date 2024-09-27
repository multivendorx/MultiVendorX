<?php

namespace MultiVendorX\Commission;

use MultiVendorX\MultiVendorX;
use MultiVendorX\Utility\Utility;
use MultiVendorX\Vendor\VendorUtil as VendorUtil;

defined('ABSPATH') || exit;

/**
 * MVX Main Commission class
 *
 * @version		2.2.0
 * @package		MultivendorX
 * @author 		MultiVendorX
 */
class CommissionManager {
    function __construct() {
        new Hooks();
    }

    /**
     * Calculate the commission and insert or update in database.
     * @param   mixed $order
     * @param   mixed $commission_id
     * @param   mixed $recalculate
     * @return  mixed
     */
    public function calculate_commission( $order = null , $commission_id = null, $recalculate = true ) {
        global $MVX;
        global $wpdb;

        if ( $order ) {
            $vendor_id = $order->get_meta('_vendor_id');
            $vendor = VendorUtil::get_vendor( $vendor_id );

            $commission_type = mvx_get_settings_value( $MVX->vendor_caps->payment_cap['commission_type'] );

            $commission_amount = $shipping_amount = $tax_amount = $shipping_tax_amount = 0;
            $commission_rates = [];

            // if recalculate is set
            if( $recalculate ) {
                foreach ( $order->get_items() as $item_id => $item ) {
                    $product_id = $item['variation_id'] ? $item['variation_id'] : $item['product_id'];

                    $item_commission = $this->get_item_commission( $product_id, $item_id, $item, $order, $vendor );
                    $commission_values = $this->get_commission_amount( $product_id, $item, $vendor );


                    $commission_rate = [
                        'mode' => $MVX->vendor_caps->payment_cap['revenue_sharing_mode'],
                        'type' => $commission_type,
                        'commission_val' => (float) ( $commission_values['commission_val'] ?? 0 ),
                        'commission_fixed' => (float) ( $commission_values['commission_fixed'] ?? 0 )
                    ];
                    
                    wc_update_order_item_meta( $item_id, '_vendor_item_commission', $item_commission );
                    $commission_amount += floatval($item_commission);
                    $commission_rates[$item_id] = $commission_rate;
                }
            } else {
                $commission_rates = $order->get_meta( 'order_items_commission_rates', true );
                foreach ( $order->get_items() as $item_id => $item ) {
                    $product = $item->get_product();
                    $meta_data = $item->get_meta_data();
                    // get item commission
                    foreach ( $meta_data as $meta ) {
                        if ( $meta->key == '_vendor_item_commission' ) {
                            $commission_amount += floatval( $meta->value );
                        }
                        if ( $meta->key == '_vendor_order_item_id' ) {
                            $order_item_id = absint( $meta->value );
                            if ( isset( $commission_rates[$order_item_id] ) ) {
                                $rate = $commission_rates[$order_item_id];
                                $commission_rates[$item_id] = $rate;
                                unset( $commission_rates[$order_item_id] ); // update with vendor order item id for further use
                            }
                        }
                    }
                }
            }

            // fixed + percentage per vendor's order
            if ( $commission_type == 'fixed_with_percentage_per_vendor' ) {
                $commission_amount = (float) $order->get_total() * ( (float) $MVX->vendor_caps->payment_cap['default_percentage'] / 100 ) + (float) $MVX->vendor_caps->payment_cap['fixed_with_percentage_per_vendor'];
            }
            
            // Action hook to adjust items commission rates before save.
            $order->update_meta_data('order_items_commission_rates', apply_filters('mvx_vendor_order_items_commission_rates', $commission_rates, $order));
            
            // $order->save(); // Avoid using save() if it will save letter in same flow.

            // transfer shipping charges
            if ($MVX->vendor_caps->vendor_payment_settings('give_shipping') && !get_user_meta($vendor_id, '_vendor_give_shipping', true)) {
                $shipping_amount = $order->get_shipping_total();
            }
            // transfer tax charges
            foreach ( $order->get_items( 'tax' ) as $key => $tax ) { 
                if ($MVX->vendor_caps->vendor_payment_settings('give_tax') && $MVX->vendor_caps->vendor_payment_settings('give_shipping') && !get_user_meta($vendor_id, '_vendor_give_shipping', true) && !get_user_meta($vendor_id, '_vendor_give_tax', true)) {
                    $tax_amount += $tax->get_tax_total();
                    $shipping_tax_amount = $tax->get_shipping_tax_total();
                } else if ($MVX->vendor_caps->vendor_payment_settings('give_tax') && !get_user_meta($vendor_id, '_vendor_give_tax', true)) {
                    $tax_amount += $tax->get_tax_total();
                    $shipping_tax_amount = 0;
                } else {
                    $tax_amount = 0;
                    $shipping_tax_amount = 0;
                }

                if ($MVX->vendor_caps->vendor_payment_settings('give_tax') && get_mvx_global_settings('commission_calculation_on_tax') ) {
                    $tax_rate_id    = $tax->get_rate_id();
                    $tax_percent    = \WC_Tax::get_rate_percent( $tax_rate_id );
                    $tax_rate       = str_replace( '%', '', $tax_percent );
                    if ( $tax_rate ) {
                        $tax_amount = ( $commission_amount * $tax_rate ) / 100;
                    }
                }
            }

            $include_coupon     = 0 < $order->get_total_discount() && isset( $MVX->vendor_caps->payment_cap['commission_include_coupon'] );
            $include_shipping   = 0 < $shipping_amount && $MVX->vendor_caps->vendor_payment_settings('give_shipping') && !get_user_meta($vendor_id, '_vendor_give_shipping', true);
            $include_tax        = 0 < $tax_amount && $MVX->vendor_caps->vendor_payment_settings('give_tax') && !get_user_meta($vendor_id, '_vendor_give_tax', true);

            $commission_total = (float) $commission_amount + (float) $shipping_amount + (float) $tax_amount + (float) $shipping_tax_amount;
            $commission_total = apply_filters( 'mvx_commission_total_amount', $commission_total, $commission_id );

            // insert | update commission into commission table.
            $data = [
                'order_id'          => $order->get_id(),
                'vendor_id'         => $vendor_id,
                'include_coupon'    => $include_coupon,
                'include_shipping'  => $include_shipping,
                'include_tax'       => $include_tax,
                'commission_amount' => $commission_amount,
                'shipping'          => $shipping_amount,
                'tax'               => $tax_amount,
                'commission_total'  => $commission_total,
                'paid_status'       => 'unpaid'
            ];
            $format = [ "%d", "%d", "%d", "%d", "%d", "%f", "%f", "%f", "%f", "%s" ];

            if ( ! $commission_id ) {
                $commission_id = $wpdb->insert( $wpdb->prefix . 'mvx_commission', $data, $format );
            } else {
                $wpdb->update( $wpdb->prefix . 'mvx_commission', $data, ['ID' => $commission_id], $format );
            }

            return $commission_id;
        }
        return false;
    }

    /**
     * Get commission value of a item.
     * @param   int $product_id
     * @param   int $item_id
     * @param   object $item
     * @param   object $order
     * @param   object $vendor
     * @return  float
     */
    public function get_item_commission( $product_id, $item_id, $item, $order, $vendor ) {
        global $MVX;

        $amount = 0;
        $commission = [];
        $product_value_total = 0;

        // Check order coupon created by vendor or not
        $order_coupon_author_is_vendor = false;
        if ( $order->get_coupon_codes() ) {
            foreach ( $order->get_coupon_codes() as $coupon_code ) {
                $coupon = new \WC_Coupon($coupon_code);
                $order_coupon_author_is_vendor = $coupon && VendorUtil::is_user_vendor( get_post_field ( 'post_author', $coupon->get_id() ) ) ? true : false;
            }
        }

        // Calculate item total based on condition
        if ( $MVX->vendor_caps->vendor_payment_settings( 'commission_include_coupon' ) ) {
            $line_total = $order->get_item_total( $item, false, false ) * $item['qty'];
            if ( $MVX->vendor_caps->vendor_payment_settings( 'admin_coupon_excluded' ) && !$order_coupon_author_is_vendor ) {
                $line_total = $order->get_item_subtotal( $item, false, false ) * $item['qty'];
            }
        } else {
            $line_total = $order->get_item_subtotal( $item, false, false ) * $item['qty'];
        }

        // Filter the item total before calculating item commission.
        $line_total = apply_filters( 'mvx_get_commission_line_total', $line_total, $item, $order );

        if ( $product_id && $vendor ) {
            
            // Get the commission info of the product.
            $commission = $this->get_commission_amount( $product_id, $item, $vendor );

            // Filter to adjust commission before use.
            $commission = apply_filters('mvx_get_commission_amount', $commission, $product_id, $vendor->term_id, $item, $order);
            
            $commission_type = mvx_get_settings_value($MVX->vendor_caps->payment_cap['commission_type']);

            if ( !empty($commission) && $commission_type == 'fixed_with_percentage' ) {
                $amount = (float) $line_total * ( (float) $commission['commission_val'] / 100 ) + (float) $commission['commission_fixed'];
            } else if ( !empty($commission) && $commission_type == 'fixed_with_percentage_qty' ) {
                $amount = (float) $line_total * ( (float) $commission['commission_val'] / 100 ) + ((float) $commission['commission_fixed'] * $item['qty']);
            } else if ( !empty($commission) && $commission_type == 'percent' ) {
                $amount = (float) $line_total * ( (float) $commission['commission_val'] / 100 );
            } else if ( !empty($commission) && $commission_type == 'fixed' ) {
                $amount = (float) $commission['commission_val'] * $item['qty'];
            } elseif ( $commission_type == 'commission_by_product_price' ) {
                $amount = $this->get_commission_as_per_product_price( $product_id, $line_total, $item['qty'] );
            } elseif ($commission_type == 'commission_by_purchase_quantity') {
                $amount = $this->get_commission_by_quantity_rule( $product_id, $line_total, $item['qty'] );
            }

            if ( isset( $MVX->vendor_caps->payment_cap['revenue_sharing_mode'] ) ) {
                if ( $MVX->vendor_caps->payment_cap['revenue_sharing_mode'] == 'revenue_sharing_mode_admin' ) {
                    $amount = (float) $line_total - (float) $amount;
                    if ( $amount < 0 ) {
                        $amount = 0;
                    }
                }
            }

            $product_value_total += $item->get_total();

            if ( apply_filters( 'mvx_admin_pay_commission_more_than_order_amount', true ) && $amount > $product_value_total) {
                $amount = $product_value_total;
            }
        }

        return apply_filters( 'vendor_commission_amount', $amount, $product_id, $item, $order );
    }

    /**
     * Get the commission amount associate with a product.
     * @param   mixed $product_id
     * @param   mixed $variation_id
     * @param   mixed $item
     * @param   mixed $vendor
     * @return  array | bool
     */
    public function get_commission_amount( $product_id, $item, $vendor ) {
        global $MVX;
        $data = [];
        $product = wc_get_product( $product_id );
        
        if ( $product && $vendor ) {
            $commission_type = mvx_get_settings_value( $MVX->vendor_caps->payment_cap['commission_type'] );

            if ( $commission_type == 'fixed_with_percentage' ) {
                $data['commission_val'] = $product->get_meta('_commission_percentage_per_product', true);
                $data['commission_fixed'] = $product->get_meta('_commission_fixed_with_percentage', true);

                if ( ! empty($data['commission_val'] ) ) {
                    return $data;
                } else {
                    $category_wise_commission = $this->get_category_wise_commission( $product );
                    if ( $category_wise_commission && $category_wise_commission->commission_percentage || $category_wise_commission->fixed_with_percentage ) {
                        return [
                            'commission_val' => $category_wise_commission->commission_percentage,
                            'commission_fixed' => $category_wise_commission->fixed_with_percentage
                        ];
                    }

                    $vendor_commission_percentage = get_user_meta($vendor->id, '_vendor_commission_percentage', true);
                    $vendor_commission_fixed_with_percentage = get_user_meta($vendor->id, '_vendor_commission_fixed_with_percentage', true);
                    if ( $vendor_commission_percentage > 0 ) {
                        return [
                            'commission_val' => $vendor_commission_percentage,
                            'commission_fixed' => $vendor_commission_fixed_with_percentage
                        ]; // Use vendor user commission percentage 
                    } else {
                        $default_commission = $this->get_default_commission();
                        if ( ! empty($default_commission) ) {
                            return [
                                'commission_val' => $default_commission['percent_amount'],
                                'commission_fixed' => $default_commission['fixed_ammount']
                            ];
                        }
                        return false;
                    }
                }
            } else if ( $commission_type == 'fixed_with_percentage_qty' ) {
                $data['commission_val'] = $product->get_meta( '_commission_percentage_per_product', true );
                $data['commission_fixed'] = $product->get_meta( '_commission_fixed_with_percentage_qty', true );

                if (!empty($data['commission_val'])) {
                    return $data; // Use product commission percentage first
                } else {
                    $category_wise_commission = $this->get_category_wise_commission( $product );
                    if ( $category_wise_commission && $category_wise_commission->commission_percentage || $category_wise_commission->fixed_with_percentage_qty ) {
                        return [
                            'commission_val' => $category_wise_commission->commission_percentage,
                            'commission_fixed' => $category_wise_commission->fixed_with_percentage_qty
                        ];
                    }

                    $vendor_commission_percentage = get_user_meta($vendor->id, '_vendor_commission_percentage', true);
                    $vendor_commission_fixed_with_percentage = get_user_meta($vendor->id, '_vendor_commission_fixed_with_percentage_qty', true);
                    if ($vendor_commission_percentage > 0) {
                        return [
                            'commission_val' => $vendor_commission_percentage,
                            'commission_fixed' => $vendor_commission_fixed_with_percentage
                        ]; // Use vendor user commission percentage 
                    } else {
                        $default_commission = $this->get_default_commission();
                        if ( ! empty($default_commission ) ) {
                            return [
                                'commission_val' => $default_commission['percent_amount'],
                                'commission_fixed' => $default_commission['fixed_ammount']
                            ];
                        }
                    }
                }
            } else {
                $data['commission_val'] = $product->get_meta( '_product_vendors_commission', true );
                if ( ! empty($data['commission_val'] ) ) {
                    return $data; // Use product commission percentage first
                } else {
                    if ( $category_wise_commission = $this->get_category_wise_commission( $product )->commision ) {
                        return ['commission_val' => $category_wise_commission];
                    }
                    $vendor_commission = get_user_meta($vendor->id, '_vendor_commission', true);
                    if ( $vendor_commission > 0 ) {
                        return ['commission_val' => $vendor_commission]; // Use vendor user commission percentage 
                    } else {
                        $default_commission = $this->get_default_commission();
                        return isset($default_commission['default_commission']) ? ['commission_val' => $default_commission['default_commission']] : false; // Use default commission
                    }
                }
            }
        }
        return false;
    }

    /**
     * Calculate category lebel commission of a product.
     * @param   object $product
     * @return  \stdClass | null
     */
    public function get_category_wise_commission( $product ) {
        global $MVX;

        // Get the terms => ['product_cat'] of the prodcut.
        $terms = get_the_terms( $product->get_id(), 'product_cat' );
        if ( !$terms || is_wp_error( $terms ) ) {
            return null;
        }

        // Find the max commission value term amoung all terms.
        $commission_type = mvx_get_settings_value($MVX->vendor_caps->payment_cap['commission_type']);
        $max_commission_amount = PHP_INT_MIN;
        $max_commission_term = null;

        foreach ( $terms as $term ) {
            // calculate current term's commission.
            $total_commission_amount = 0;
            if ( $commission_type == 'fixed_with_percentage' ) {
                $commission_percentage = (float) get_term_meta( $term->term_id, 'commission_percentage', true );
                $fixed_with_percentage = (float) get_term_meta( $term->term_id, 'fixed_with_percentage', true );
                $total_commission_amount = $commission_percentage + $fixed_with_percentage;
            } else if ( $commission_type == 'fixed_with_percentage_qty' ) {
                $commission_percentage = (float) get_term_meta( $term->term_id, 'commission_percentage', true );
                $fixed_with_percentage_qty = (float) get_term_meta( $term->term_id, 'fixed_with_percentage_qty', true );
                $total_commission_amount = $commission_percentage + $fixed_with_percentage_qty;
            } else {
                $total_commission_amount = (float) get_term_meta( $term->term_id, 'commision', true );
            }
            
            // compare current term's commission with previously store term's commission.
            if ( $total_commission_amount > $max_commission_amount ) {
                $max_commission_amount = $total_commission_amount;
                $max_commission_term = $term;
            }
        }

        // Store commission value of maximum commission category.
        $category_wise_commission = new \stdClass();
        $category_wise_commission->commision = (float) ( get_term_meta( $max_commission_term->term_id, 'commision', true ) ?? 0 );
        $category_wise_commission->commission_percentage = (float) ( get_term_meta( $max_commission_term->term_id, 'commission_percentage', true ) ?? 0 );
        $category_wise_commission->fixed_with_percentage = (float) ( get_term_meta( $max_commission_term->term_id, 'fixed_with_percentage', true ) ?? 0 );
        $category_wise_commission->fixed_with_percentage_qty = (float) ( get_term_meta( $max_commission_term->term_id, 'fixed_with_percentage_qty', true ) ?? 0 );

        // Filter hook to adjust category wise commission after calculation.
        return apply_filters( 'mvx_category_wise_commission', $category_wise_commission, $product );
    }

    /**
     * Get the default / global-label commission.
     * @return array
     */
    public function get_default_commission() {
        global $MVX;
        $commission_amount = [];
        $commission_type = mvx_get_settings_value( $MVX->vendor_caps->payment_cap['commission_type'] );
        $default_commission_settings = get_mvx_global_settings( 'default_commission' );
        if ( is_array( $default_commission_settings ) ) {
            switch ( $commission_type ) {
                case "fixed":
                case "percent":
                    $commission_amount = [ 'default_commission' => $default_commission_settings[0]['value'] ];
                break;
                case "fixed_with_percentage":
                case "fixed_with_percentage_qty":
                    foreach ( $default_commission_settings as $value ) {
                        if ( isset( $value['key'] ) && isset( $value['value'] ) ) {
                            $commission_amount[ $value['key'] ] = $value['value'];
                        }
                    }
            }
        }
        return $commission_amount;
    }

    /**
     * Get commission amount as per product price
     * @param   mixed $product_id
     * @param   mixed $line_total
     * @param   mixed $item_quantity
     * @return  float|int
     */
    public function get_commission_as_per_product_price( $product_id = 0, $line_total = 0, $item_quantity = 0 ) {
        $commission_options = mvx_get_option( 'mvx_commissions_tab_settings', [] );
        $vendor_commission_by_products = $commission_options['vendor_commission_by_products'];
        if ( ! is_array( $vendor_commission_by_products ) ) $vendor_commission_by_products = [];
        $commission_rule = [];

        if ( ! empty( $vendor_commission_by_products ) ) {
            $matched_rule_price = 0;
            foreach ( $vendor_commission_by_products as $vendor_commission_product_rule ) {
                $rule_price = $vendor_commission_product_rule['cost'];
                $rule = isset( $vendor_commission_product_rule['rule'] ) ? $vendor_commission_product_rule['rule']['value'] : '';
                
                if ( ( $rule == 'upto' ) && ( (float) $line_total <= (float)$rule_price ) && ( !$matched_rule_price || ( (float)$rule_price <= (float)$matched_rule_price ) ) ) {
                    $matched_rule_price  = $rule_price;
                    $commission_rule['mode'] = isset($vendor_commission_product_rule['type']) ? $vendor_commission_product_rule['type']['value'] : '';
                    $commission_rule['commission_val'] = $vendor_commission_product_rule['commission'];
                    $commission_rule['commission_fixed'] = isset( $vendor_commission_product_rule['commission_fixed'] ) ? $vendor_commission_product_rule['commission_fixed'] : $vendor_commission_product_rule['commission'];
                } elseif ( ( $rule == 'greater' ) && ( (float) $line_total > (float)$rule_price ) && ( !$matched_rule_price || ( (float)$rule_price >= (float)$matched_rule_price ) ) ) {
                    $matched_rule_price = $rule_price;
                    $commission_rule['mode'] = isset($vendor_commission_product_rule['type']) ? $vendor_commission_product_rule['type']['value'] : '';
                    $commission_rule['commission_val'] = $vendor_commission_product_rule['commission'];
                    $commission_rule['commission_fixed'] = isset( $vendor_commission_product_rule['commission_fixed'] ) ? $vendor_commission_product_rule['commission_fixed'] : $vendor_commission_product_rule['commission'];
                }
            }
        }

        $amount = 0;
        if ( !empty( $commission_rule ) ) {
            if ( $commission_rule['mode'] == 'percent_fixed' ) {
                $amount = (float) $line_total * ( (float) $commission_rule['commission_val'] / 100 ) + (float) $commission_rule['commission_fixed'];
            } else if ( $commission_rule['mode'] == 'percent' ) {
                $amount = (float) $line_total * ( (float) $commission_rule['commission_val'] / 100 );
            } else if ( $commission_rule['mode'] == 'fixed' ) {
                $amount = (float) $commission_rule['commission_fixed'] * $item_quantity;
            }
        }
        return $amount;
    }

    /**
     * Get commission amount as per product quantity
     * @param mixed $product_id
     * @param mixed $line_total
     * @param mixed $item_quantity
     * @return mixed
     */
    public function get_commission_by_quantity_rule( $product_id = 0, $line_total = 0, $item_quantity = 0 ) {
        $mvx_variation_commission_options = mvx_get_option( 'mvx_variation_commission_options', [] );
        $vendor_commission_quantity_rules = $mvx_variation_commission_options['vendor_commission_by_quantity'];
        if ( ! is_array( $vendor_commission_quantity_rules ) ) $vendor_commission_quantity_rules = [];

        if ( ! $product_id ) return false;

        $commission_rule = [];
        $matched_rule_quantity = 0;
        foreach ( $vendor_commission_quantity_rules as $vendor_commission_quantity_rule ) {
            $rule_quantity = $vendor_commission_quantity_rule['quantity'];
            $rule = isset($vendor_commission_quantity_rule['rule']) ? $vendor_commission_quantity_rule['rule']['value'] : '';

            if ( ( $rule == 'upto' ) && ( (float) $item_quantity <= (float)$rule_quantity ) && ( !$matched_rule_quantity || ( (float)$rule_quantity <= (float)$matched_rule_quantity ) ) ) {
                $matched_rule_quantity      = $rule_quantity;
                $commission_rule['mode']    = isset($vendor_commission_quantity_rule['type']) ? $vendor_commission_quantity_rule['type']['value'] : '';
                $commission_rule['commission_val'] = $vendor_commission_quantity_rule['commission'];
                $commission_rule['commission_fixed']   = isset( $vendor_commission_quantity_rule['commission_fixed'] ) ? $vendor_commission_quantity_rule['commission_fixed'] : 0;
            } elseif( ( $rule == 'greater' ) && ( (float) $item_quantity > (float)$rule_quantity ) && ( !$matched_rule_quantity || ( (float)$rule_quantity >= (float)$matched_rule_quantity ) ) ) {
                $matched_rule_quantity      = $rule_quantity;
                $commission_rule['mode']    = isset($vendor_commission_quantity_rule['type']) ? $vendor_commission_quantity_rule['type']['value'] : '';
                $commission_rule['commission_val'] = $vendor_commission_quantity_rule['commission'];
                $commission_rule['commission_fixed']   = isset( $vendor_commission_quantity_rule['commission_fixed'] ) ? $vendor_commission_quantity_rule['commission_fixed'] : 0;
            }
        }

        $amount = 0;
        if ( ! empty( $commission_rule ) ) {
            if ( $commission_rule['mode'] == 'percent_fixed' ) {
                $amount = (float) $line_total * ( (float) $commission_rule['commission_val'] / 100 ) + (float) $commission_rule['commission_fixed'];
            } else if ( $commission_rule['mode'] == 'percent' ) {
                $amount = (float) $line_total * ( (float) $commission_rule['commission_val'] / 100 );
            } else if ( $commission_rule['mode'] == 'fixed' ) {
                $amount = (float) $commission_rule['commission_fixed'];
            }
        }
        return apply_filters('mvx_quantity_wise_commission_amount_modify', $amount, $product_id, $line_total, $item_quantity, $commission_rule);
    }

    /**
     * Summary of calculate_commission_refunds
     * @param   mixed $vendor_order
     * @param   mixed $refund_id
     * @return  void
     */
    public function calculate_commission_refunds( $vendor_order, $refund_id ) {
        // $refund = new \WC_Order_Refund( $refund_id );
        $commission_id = $vendor_order->get_props( '_commission_id' );
        $vendor_id = $vendor_order->get_props( '_vendor_id', true );
        
        $commission_amount = get_post_meta( $commission_id, '_commission_amount', true);
        $included_coupon = get_post_meta( $commission_id, '_commission_include_coupon', true) ? true : false;
        $included_tax = get_post_meta( $commission_id, '_commission_total_include_tax', true) ? true : false;
        $items_commission_rates = $vendor_order->get_meta( 'order_items_commission_rates', true);
        
        $refunded_total = $refunds = $global_refunds = $commission_refunded_items = array();

        if($commission_id){
            $line_items_commission_refund = $global_commission_refund = 0;
            foreach ($vendor_order->get_refunds() as $_refund) {
                $line_items_refund = $shipping_item_refund = $tax_item_refund = $amount = $refund_item_totals = 0;
                // if commission refund exists
                if ($_refund->get_meta('_refunded_commissions', true)) {
                    $commission_amt = get_post_meta($_refund->get_id(), '_refunded_commissions', true);
                    $refunds[$_refund->get_id()][$commission_id] = $commission_amt[$commission_id];
                }
                /** WC_Order_Refund items **/
                foreach ($_refund->get_items() as $item_id => $item) { 
                    $refunded_item_id = $item['refunded_item_id'];
                    $refund_amount = $item['line_total'];
                    $refunded_item_id = $item['refunded_item_id'];
                    
                    if ($refund_amount != 0) { 
                        $refunded_total[$commission_id] += $refund_amount;
                        $line_items_refund += $refund_amount;
                        
                        if(isset($items_commission_rates[$refunded_item_id])){
                            if ($items_commission_rates[$refunded_item_id]['type'] == 'fixed_with_percentage') {
                                $amount = (float) $refund_amount * ( (float) $items_commission_rates[$refunded_item_id]['commission_val'] / 100 ) + (float) $items_commission_rates[$refunded_item_id]['commission_fixed'];
                            } else if ($items_commission_rates[$refunded_item_id]['type'] == 'fixed_with_percentage_qty') {
                                $amount = (float) $refund_amount * ( (float) $items_commission_rates[$refunded_item_id]['commission_val'] / 100 ) + ((float) $items_commission_rates[$refunded_item_id]['commission_fixed'] * $item['quantity']);
                            } else if ($items_commission_rates[$refunded_item_id]['type'] == 'percent') {
                                $amount = (float) $refund_amount * ( (float) $items_commission_rates[$refunded_item_id]['commission_val'] / 100 );
                            } else if ($items_commission_rates[$refunded_item_id]['type'] == 'fixed') {
                                $amount = (float) $items_commission_rates[$refunded_item_id]['commission_val'] * $item['quantity'];
                            }
                            if (isset($items_commission_rates[$refunded_item_id]['mode']) && $items_commission_rates[$refunded_item_id]['mode'] == 'admin') {
                                $amount = (float) $refund_amount - (float) $amount;
                            }
                            $line_items_commission_refund += $amount;
                            $refund_item_totals += $amount;
                            $commission_refunded_items[$_refund->get_id()][$refunded_item_id] = $amount;
                        }
                    }
                }
                // add items total refunds
                $refunds[$_refund->get_id()][$commission_id]['line_item'] = $refund_item_totals;
                
                if($line_items_commission_refund != 0){
                    update_post_meta( $commission_id, '_commission_refunded_items', $line_items_commission_refund );
                    update_post_meta( $commission_id, '_commission_refunded_items_data', $commission_refunded_items );
                }
                
                /** WC_Order_Refund shipping **/
                $refund_shipping_totals = 0;
                foreach ($_refund->get_items('shipping') as $item_id => $item) { 
                    if ( 0 < get_post_meta($commission_id, '_shipping', true) && get_post_meta($commission_id, '_commission_total_include_shipping', true) ){
                        if($item['total'] != 0){
                            $shipping_item_refund += $item['total'];
                            $refund_shipping_totals += $item['total'];
                        }
                    }
                }
                if($shipping_item_refund != 0){
                    $amount = $shipping_item_refund;
                    if( $refund_shipping_totals )
                        $refunds[$_refund->get_id()][$commission_id]['shipping'] = $refund_shipping_totals;
                    update_post_meta( $commission_id, '_commission_refunded_shipping', $shipping_item_refund );
                }
                
                /** WC_Order_Refund tax **/
                $refund_tax_totals = 0;
                foreach ($_refund->get_items('tax') as $item_id => $item) { 
                    if ( 0 < get_post_meta($commission_id, '_tax', true) && get_post_meta($commission_id, '_commission_total_include_tax', true) ){
                        if($item['tax_total'] != 0 || $item['shipping_tax_total'] != 0){
                            $tax_item_refund += $item['tax_total'] + $item['shipping_tax_total'];
                            $refund_tax_totals += $item['tax_total'] + $item['shipping_tax_total'];
                        }
                    }
                }
                if($tax_item_refund != 0){
                    $amount = $tax_item_refund;
                    if( $refund_tax_totals )
                        $refunds[$_refund->get_id()][$commission_id]['tax'] = $refund_tax_totals;
                    update_post_meta( $commission_id, '_commission_refunded_tax', $tax_item_refund );
                }
                
                // if global refund applied in this refund
                $refund_amount = $_refund->get_amount() - abs( $line_items_refund );
                if ( !$_refund->get_items() && !$_refund->get_items('shipping') && !$_refund->get_items('tax') ) {
                    $global_refunds[$_refund->get_id()] = $_refund;
                }
                
            }
   
            // global refund calculation
            foreach ( $global_refunds as $_refund ) {
                //$rate_to_refund = $_refund->get_amount() / $order->get_total();
                //$commission_total = MVX_Commission::commission_totals($commission_id, 'edit');

                if(!$_refund->get_meta('_refunded_commissions', true)){
                    $refunds[$_refund->get_id()][$commission_id]['global'] = $_refund->get_amount() * -1;
                    $global_commission_refund += $_refund->get_amount() * -1;
                }else{
                    $refunded_commission = $_refund->get_meta('_refunded_commissions', true);
                    $refunded_commission_amt_data = isset($refunded_commission[$commission_id]) ? $refunded_commission[$commission_id] : array();
                    $refunded_commission_amt = array_sum($refunded_commission_amt_data);
                    $global_commission_refund += $refunded_commission_amt;
                }
            }
            if($global_commission_refund != 0){
                update_post_meta( $commission_id, '_commission_refunded_global', $global_commission_refund );
            }
       
            // update the refunded commissions in the order to easy manage these in future
            $refunded_amt_total = 0;
            if($refunds) :
                foreach ( $refunds as $_refund_id => $commissions_refunded ) {
                    $comm_refunded_amt = $commissions_refunded_total = 0;
                    foreach ( $commissions_refunded as $commission_id => $data_amount ) {
                        $amount = array_sum($data_amount);
                        $commissions_refunded_total = $amount;
                        if( -($amount) != 0 ){
                            $comm_refunded_amt += $amount;
                            $note = sprintf( __( 'Refunded %s from commission', 'multivendorx' ), wc_price( abs( $amount ) ) );
                            if($_refund_id == $refund_id){
                                MVX_Commission::add_commission_note($commission_id, $note, $vendor_id);
                                /**
                                 * Action hook after add commission refund note.
                                 *
                                 * @since 3.4.0
                                 */
                                do_action( 'mvx_create_commission_refund_after_commission_note', $commission_id, $data_amount, $refund_id, $vendor_order );
                            }
                            //update_post_meta( $commission_id, '_commission_amount', $amount );

                            //if( $amount == 0 ) update_post_meta($commission_id, '_paid_status', 'cancelled');
                        }
                    }
                    $refunded_amt_total += $comm_refunded_amt;

                    update_post_meta( $_refund_id, '_refunded_commissions', $commissions_refunded );
                    update_post_meta( $_refund_id, '_refunded_commissions_total', $commissions_refunded_total );
                }
                
                update_post_meta( $commission_id, '_commission_refunded_data', $refunds );
                update_post_meta( $commission_id, '_commission_refunded', $refunded_amt_total );
                // Trigger notification emails.
                if ( MVX_Commission::commission_totals($commission_id, 'edit') == 0  ) {
                    do_action( 'mvx_commission_fully_refunded', $commission_id, $vendor_order );
                    update_post_meta($commission_id, '_paid_status', 'refunded'); 
                } else {
                    do_action( 'mvx_commission_partially_refunded', $commission_id, $vendor_order );
                    update_post_meta($commission_id, '_paid_status', 'partial_refunded');
                }
                /**
                 * Action hook after commission refund save.
                 *
                 * @since 3.4.0
                 */
                do_action('mvx_after_create_commission_refunds', $vendor_order, $commission_id);
            endif;
        }
    }
}
