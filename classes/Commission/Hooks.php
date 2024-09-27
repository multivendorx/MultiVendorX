<?php

namespace MultiVendorX\Commission;
use MultiVendorX\Order\VendorOrder as VendorOrder;

defined('ABSPATH') || exit;

/**
 * MVX Commission Hooks class
 *
 * @version		2.2.0
 * @package		MultivendorX
 * @author 		MultiVendorX
 */
class Hooks {
    function __construct() {
        add_action( 'mvx_checkout_vendor_order_processed', [&$this, 'create_commission'], 10, 3 );
        // add_action( 'woocommerce_order_refunded', [$this, 'create_commission_refunds'], 99, 2 );
    }

    /**
     * Create commission of vendor order.
     * @param   int $vendor_order_id
     * @param   object $vendor_order
     * @param   object $main_order
     * @return  void
     */
    public function create_commission( $vendor_order_id, $vendor_order, $main_order ) {

        $processed = $vendor_order->get_meta( '_commissions_processed', true );

        if ( ! $processed ) {
            
            $commission_id = MVX()->commission->calculate_commission( $vendor_order );
            $vendor_order->update_meta_data( '_commission_id', $commission_id );
            $vendor_order->update_meta_data( '_commissions_processed', 'yes' );
            
            // Action hook after commission processed.
            do_action( 'mvx_after_calculate_commission', $commission_id, $vendor_order, $main_order );
        }
    }
    
    /**
     * Create refunds for vendor commission
     * @param mixed $order_id
     * @param mixed $refund_id
     * @return void
     */
    public function create_commission_refunds( $order_id, $refund_id ) {
        $vendor_order = new VendorOrder();
        if( $vendor_order->is_vendor_order() ) {
            $commission_id = MVX()->commission->calculate_commission_refunds( $vendor_order, $refund_id );
            /**
             * Action hook after commission refund save.
             * @since 3.4.0
             */
            do_action( 'mvx_after_create_commission_refunds', $order_id, $commission_id );
        }
    }
}