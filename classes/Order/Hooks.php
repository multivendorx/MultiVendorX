<?php

namespace MultiVendorX\Order;

defined('ABSPATH') || exit;

/**
 * MVX Order Hook class
 *
 * @version		2.2.0
 * @package		MultivendorX
 * @author 		MultiVendorX
 */

class Hooks {
    function __construct() {
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'add_metadata_for_line_item'], 10, 4);
        add_action('woocommerce_checkout_create_order_shipping_item', [&$this, 'add_metadate_for_shipping_item'], 10, 4);
        add_action('woocommerce_analytics_update_order_stats', [&$this, 'remove_suborder_analytics'], 10, 1);

        // Create vendoer order after valid checkout processed.
        add_action('woocommerce_checkout_order_processed', [&$this, 'create_vendor_order'], 1, 3);
        add_action('woocommerce_store_api_checkout_order_processed', [&$this, 'create_vendor_order_block_support'], 1, 1);

        // After crate suborder order try to sync order status.
        add_action('woocommerce_order_status_changed', [&$this, 'parent_order_to_vendor_order_status_sync'], 10, 4);
        add_action('woocommerce_order_status_changed', [&$this, 'vendor_order_to_parent_order_status_sync'], 10, 4);
    }
    
    /**
     * Add metadata for line item in time of checkout process.
     * @param   mixed $item
     * @param   mixed $item_key
     * @param   mixed $values
     * @param   mixed $order
     * @return  void
     */
    public function add_metadata_for_line_item($item, $item_key, $values, $order) {
        if ( $order &&  $order->get_parent_id() == 0 ) {
            $vendor = get_mvx_product_vendors($item['product_id']);
            if ($vendor) {
                $item->add_meta_data('_sold_by', $vendor->page_title);
                $item->add_meta_data('_vendor_id', $vendor->id);
            }
        }
    }

    /**
     * Add metadata for shipping item in time of checkout process.
     * @param mixed $item
     * @param mixed $package_key
     * @param mixed $package
     * @param mixed $order
     * @return void
     */
    public function add_metadate_for_shipping_item($item, $package_key, $package, $order) {
        $vendor_id = $package['vendor_id'] ?? $package_key;
        if( $order && $order->get_parent_id() == 0 ) {
            $item->add_meta_data('vendor_id', $vendor_id, true);
            $package_qty = array_sum(wp_list_pluck($package['contents'], 'quantity'));
            $item->add_meta_data('package_qty', $package_qty, true);
            do_action('mvx_add_shipping_package_meta');
        }
    }

    /**
     * Woocommerce admin dashboard restrict dual order report 
     * @param   int $order_id
     * @return  void
     */
    public function remove_suborder_analytics($order_id) {
        global $wpdb;
        $order = new VendorOrder($order_id);
        if ( $order->is_vendor_order() ) {
            $wpdb->delete( $wpdb->prefix.'wc_order_stats', [ 'order_id' => $order_id ] );
            \WC_Cache_Helper::get_transient_version( 'woocommerce_reports', true );
        }
    }

    /**
     * Create the vendor orders of a main order for block support.
     * @param   object $order
     * @return  void
     */
    public function create_vendor_order_block_support( $order ) {
        $this->create_vendor_order($order->get_id(), [], $order);
    }


    /**
     * Create the vendor orders of a main order.
     * @param   mixed $order_id
     * @param   mixed $old_status
     * @param   mixed $new_status
     * @param   mixed $order
     * @return  void
     */
    public function create_vendor_order( $order_id, $posted_data, $order ) {

        if ( $order->get_parent_id() || $order->get_meta('has_mvx_sub_order') ) {
            return;
        }

        MVX()->order->create_vendor_orders($order_id, $order);

        $order->update_meta_data('has_mvx_sub_order', true);
        $order->save();
    }

    /**
     * Sync vendor order based on parent order status change for first time.
     * Except first time whenever parent order status change it skip for vendor order. 
     * @param   mixed $order_id
     * @param   mixed $old_status
     * @param   mixed $new_status
     * @param   mixed $order
     * @return  void
     */
    public function parent_order_to_vendor_order_status_sync($order_id, $old_status, $new_status, $order) {
        if( !$order_id ) return;

        if ( empty($new_status) ) {
            $new_status = $order->get_status('edit');
        }
        
        // If order is not a main order or sync before then return.
        if ( $order->get_parent_id() || $order->get_meta('mvx_vendor_order_status_synchronized', true) )
            return;

        remove_action('woocommerce_order_status_completed', 'wc_paying_customer');

        // Check if order have suborder then sync
        $suborders = MVX()->order->get_suborders($order);
        if ( $suborders ) {
            foreach ( $suborders as $suborder ) {
                $suborder->update_status($new_status, _x('Update via parent order: ', 'Order note', 'multivendorx'));
            }
            $order->update_meta_data('mvx_vendor_order_status_synchronized', true);
            $order->save();
        }

        add_action('woocommerce_order_status_completed', 'wc_paying_customer');
    }

    /**
     * Sync parent order base on vendor order.
     * If all vendor order is in same status then change the parent order.
     * @param   mixed $order_id
     * @param   mixed $old_status
     * @param   mixed $new_status
     * @param   mixed $order
     * @return  void
     */
    public function vendor_order_to_parent_order_status_sync($order_id, $old_status, $new_status, $order) {
        $vendoer_order = new VendorOrder($order);

        if( $vendoer_order->is_vendor_order() ) {
            if ( current_user_can('administrator')
                && $new_status != $old_status
                && apply_filters('mvx_vendor_notified_when_admin_change_status', true)
            ) {
                $email_admin = WC()->mailer()->emails['WC_Email_Admin_Change_Order_Status'];
                $vendor = $vendoer_order->get_vendor();
                $email_admin->trigger($order_id, $new_status, $vendor);
            }

            $parent_order_id = $order->get_parent_id();
            if( $parent_order_id ) {
                // Remove the action to prevent recursion call.
                remove_action('woocommerce_order_status_changed', [$this, 'parent_order_to_vendor_order_status_sync'], 10, 4);

                $suborders = MVX()->order->get_suborders($parent_order_id);
                $all_status_equal = true;
                foreach( $suborders as $suborder) {
                    if ($suborder->get_status('edit') != $new_status) {
                        $all_status_equal = false;
                        break;
                    }
                }

                if( $all_status_equal ) {
                    $parent_order = wc_get_order( $parent_order_id );
                    $parent_order->update_status( $new_status, _x( "Sync from vendor's suborders: ", 'Order note', 'multivendorx' ) );
                }

                // Add the action back.
                add_action('woocommerce_order_status_changed', [$this, 'parent_order_to_vendor_order_status_sync'], 10, 4);
            }
        }
    }
}