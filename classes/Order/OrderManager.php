<?php

namespace MultiVendorX\Order;

use \MultiVendorX\Vendor\VendorUtil as VendorUtil;

defined('ABSPATH') || exit;

/**
 * MVX Main Order class
 *
 * @version		2.2.0
 * @package		MultivendorX
 * @author 		MultiVendorX
 */

class OrderManager {
    function __construct() {
        // Filter the query of order table before it is fetch.
        add_filter('woocommerce_order_query_args', [&$this, 'set_filter_order_query']);

        new Hooks();
        new Admin();
        new Frontend();
    }

    /**
     * A special function that filter the query in time of getting all order.
     * By default it trim the vendeor order (parent id is not 0) if query not contain 'parent'.
     * filter 'mvx_order_parent_filter' to filter based on parent.
     * @param   mixed $query
     * @return  mixed
     */
    public function set_filter_order_query($query) {
        $parent_id = apply_filters('mvx_order_parent_filter', 0);
        if (!$query['parent'] && $parent_id >= 0) {
            $query['parent'] = $parent_id;
        }
        return $query;
    }

    /**
     * Get array of suborders if available.
     * Return array of suborder as WC_order object.
     * Or Array of suborder's id if $object is false.
     * @param   int | \WC_Order $order
     * @param   array $args
     * @param   boolean $object
     * @return  object array of suborders.
     */
    public function get_suborders($order, $args = [], $object = true) {
        return wc_get_orders(['parent' => is_numeric($order) ? $order : $order->get_id()]);
    }

    /**
     * Create vendor order from a order.
     * It group item based on vendor then create suborder for each group.
     * @param   int $order_id
     * @param   object $order
     * @return  void
     */
    public function create_vendor_orders($order_id, $order) {
        $item_info = self::grup_item_vendor_based($order);

        foreach ($item_info as $vendor_id => $items) {
            $vendor_order = self::create_sub_order($order, $vendor_id, $items);

            // hook after vendor order create.
            do_action('mvx_checkout_vendor_order_processed', $vendor_order->get_id(), $vendor_order, $order);

            $vendor_order->save();
        }
    }

    /**
     * Create suborder of a main order.
     * @param   object $parent_order
     * @param   int $vendor_id
     * @param   array $items
     * @return  object
     */
    public static function create_sub_order($parent_order, $vendor_id, $items) {
        $meta = [
            'cart_hash',
            'customer_id',
            'currency',
            'prices_include_tax',
            'customer_user',
            'customer_ip_address',
            'customer_user_agent',
            'customer_note',
            'payment_method',
            'payment_method_title',
            'status',
            'billing_country',
            'billing_first_name',
            'billing_last_name',
            'billing_company',
            'billing_address_1',
            'billing_address_2',
            'billing_city',
            'billing_state',
            'billing_postcode',
            'billing_email',
            'billing_phone',
            'shipping_country',
            'shipping_first_name',
            'shipping_last_name',
            'shipping_company',
            'shipping_address_1',
            'shipping_address_2',
            'shipping_city',
            'shipping_state',
            'shipping_postcode',
        ];

        try {
            $order = new \WC_Order();

            // set meta value of suborder from parent order.
            foreach ($meta as $key) {
                if (is_callable([$order, "set_{$key}"])) {
                    $order->{"set_{$key}"}($parent_order->{"get_{$key}"}());
                }
            }

            self::create_line_item($order, $items);
            self::create_shipping_item($order, $items);
            self::create_coupon_item($order, $items);

            // save other details for suborder.
            $order->set_created_via('mvx_vendor_order');
            $order->update_meta_data('_vendor_id', $vendor_id);
            $order->set_parent_id($parent_order->get_id());
            $order->calculate_totals();

            /**
             * Action hook to adjust order before save.
             * @since 3.4.0
             */
            do_action('mvx_checkout_create_order', $parent_order, $order, $items);

            wp_update_post(
                [
                    'ID' => $order->get_id(),
                    'post_author' => $vendor_id,
                ]
            );

            return $order;

        } catch (\Exception $e) {
            return new \WP_Error('Faild to create vendor order', $e->getMessage());
        }
    }

    /**
     * Get the basic info of a order items.
     * It grup the item based on vendoer.
     * @param   object $order
     * @return  array
     */
    public static function grup_item_vendor_based( $order ) {
        $items = $order->get_items();

        // Group each item.
        $grouped_items = [];
        foreach ($items as $item_id => $item) {
            if (isset($item['product_id']) && $item['product_id'] !== 0) {
                $vendor = VendorUtil::get_products_vendor($item['product_id']);
                if ($vendor) {
                    $grouped_items[$vendor->id][$item_id] = $item;
                }
            }
        }

        // Structure data for grouped item.
        $item_info = [];
        foreach ($grouped_items as $vendor_id => $items) {
            $item_info[$vendor_id] = [
                'vendor_id'         => $vendor_id,
                'parent_order'      => $order,
                'line_items'        => $items,
            ];
        }

        return $item_info;
    }

    /**
     * Create new line items for vendor order
     * @param   object $order
     * @param   array $items
     * @return  void
     */
    public static function create_line_item( $order, $items ) {
        $line_items = $items['line_items'];

        // Iterate through each item and create a order's line item.
        foreach ( $line_items as $item_id => $order_item ) {
            if ( isset( $order_item['product_id'] ) && $order_item['product_id'] !== 0 ) {
                $product = wc_get_product( $order_item['product_id'] );
                $item = new \WC_Order_Item_Product();

                $item->set_props(
                    [
                        'quantity'      => $order_item['quantity'],
                        'variation'     => $order_item['variation'],
                        'subtotal'      => $order_item['line_subtotal'],
                        'total'         => $order_item['line_total'],
                        'subtotal_tax'  => $order_item['line_subtotal_tax'],
                        'total_tax'     => $order_item['line_tax'],
                        'taxes'         => $order_item['line_tax_data'],
                    ]
                );

                if ($product) {
                    $item->set_props(
                        [
                            'name'          => $order_item->get_name(),
                            'tax_class'     => $order_item->get_tax_class(),
                            'product_id'    => $order_item->get_product_id(),
                            'variation_id'  => $order_item->get_variation_id(),
                        ]
                    );
                }

                $item->set_backorder_meta();
                $item->add_meta_data('_vendor_order_item_id', $item->get_product_id());

                // Copy all metadata from order's item to new created item.
                $metadata = $order_item->get_meta_data();
                if ( $metadata ) {
                    foreach ($metadata as $meta) {
                        $item->add_meta_data($meta->key, $meta->value);
                    }
                }

                // Action hook before new item save.
                do_action('mvx_vendor_create_order_line_item', $item, $item->get_product_id(), $order_item, $order);

                $order->add_item( $item );
            }
        }
    }

    /**
     * Create new shipping items for vendor order
     * @param   object $order
     * @param   array $items
     * @return  void
     */
    public static function create_shipping_item( $order, $items ) {
        $vendor_id = $items['vendor_id'];
        $parent_order = $items['parent_order'];

        $shipping_items = $parent_order->get_items('shipping');
                
        foreach ( $shipping_items as $item_id => $item ) {
            $shipping_vendor_id = $item->get_meta('vendor_id', true);
            if ( $shipping_vendor_id == $vendor_id ) {
                $shipping = new \WC_Order_Item_Shipping();
                $shipping->set_props(
                    [
                        'method_title'  => $item['method_title'],
                        'method_id'     => $item['method_id'],
                        'instance_id'   => $item['instance_id'],
                        'taxes'         => $item['taxes'],
                        'total'         => wc_format_decimal($item['total']),
                    ]
                );

                foreach ($item->get_meta_data() as $key => $value) {
                    $shipping->add_meta_data($key, $value, true);
                }

                $shipping->add_meta_data('vendor_id', $vendor_id, true);
                $item->add_meta_data('_vendor_order_shipping_item_id', $item_id );

                // Action hook to adjust item before save.
                do_action('mvx_vendor_create_order_shipping_item', $shipping, $item_id, $item, $order);

                $order->add_item( $shipping );
            }
        }
    }

    /**
     * Create new coupon items for vendor order
     * @param   object $order
     * @param   array $items
     * @return  void
     */
    public static function create_coupon_item($order, $items) {
        $parent_order = $items['parent_order'];
        $line_items = $items['line_items'];

        // Store the product id of suborder's item.
        $product_ids = [];
        foreach( $line_items as $item ) {
            $product_ids[] = $item->get_product_id();
        }

        foreach( $parent_order->get_coupons() as $coupon ) {
            if( !in_array( 
                    $coupon->get_discount_type(),
                    apply_filters( 'mvx_order_available_coupon_types', [ 'fixed_product', 'percent', 'fixed_cart' ], $order, $coupon )
                )
            ) continue;

            $coupon_products = $coupon->get_product_ids();

            $match_coupon_product = array_intersect($product_ids, $coupon_products);

            if( $match_coupon_product ) {
                $item = new \WC_Order_Item_Coupon();
                $item->set_props(
                    array(
                        'code'         => $coupon->get_code(),
                        'discount'     => $coupon->get_discount_amount(),
                        'discount_tax' => $coupon->get_discount_tax(),
                    )
                );

                // Avoid storing used_by - it's not needed and can get large.
                $coupon_data = $coupon->get_data();
                unset( $coupon_data['used_by'] );
                $item->add_meta_data( 'coupon_data', $coupon_data );

                // Action hook to adjust item before save.
                do_action( 'mvx_checkout_create_order_coupon_item', $item, $coupon, $order);

                // Add item to order and save.
                $order->add_item( $item );
            }
        }
    }
}