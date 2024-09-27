<?php

namespace MultiVendorX\Order;
use MultiVendorX\Commission\Commission;
use MultiVendorX\MultiVendorX;

defined('ABSPATH') || exit;

/**
 * @class 		MVX Vendor Order Class
 *
 * @version		3.4.0
 * @package		MultivendorX
 * @author 		MultiVendorX
 */
class VendorOrder {
    private $id          = 0;
    private $vendor_id   = 0;
    private $order       = null;
    private $commission  = null; 
    
    /**
     * Get the order if ID is passed, otherwise the order is new and empty.
     * @param  int | object Order to read.
     */
    public function __construct( $order = 0 ) {
        if ( $order instanceof \WC_Order || $order instanceof \WC_Order_Refund ) {
            $this->id = absint( $order->get_id() );
            $this->order = $order;
        } else {
            $this->id = absint( $order );
            $this->order = wc_get_order( $this->id );
        }

        $this->vendor_id = $this->order ? absint( $this->order->get_meta( '_vendor_id', true) ) : 0;
    }

    /**
     * Check the order is vendor order or not.
     * If the order is vendor order return true else false.
     * @param   bool $current_vendor
     * @return  bool
     */
    public function is_vendor_order($current_vendor = false) {
        if(!$this->vendor_id) {
            return false;
        }
        if($current_vendor) {
            return $this->vendor_id === get_current_user_id();
        }
        return true;
    }
    
    /**
     * Get the props of vendor order.
     * Retrives data from vendor meta.
     * @param  string $prop
     * @return mixed
     */
    public function get_prop( $prop ) {
        return  $this->order->get_meta( $prop, true);
    }

    /**
     * Get the vendeor id of vendor order.
     * @return int
     */
    public function get_vendor_id() {
        return $this->vendor_id;
    }
    
    /**
     * Get vendor objet if the order is vendoer order.
     * Otherwise it return false.
     * @return object | null
     */
    public function get_vendor() {
        return get_mvx_vendor($this->vendor_id);
    }

    /**
     * Get the WC_Order object.
     * @return bool|\WC_Order|\WC_Order_Refund
     */
    public function get_order() {
        return $this->order;
    }

    /**
     * Get the commisssion object from vendor order
     * @return Commission
     */
    public function get_commission() {
        if ( $this->commission === null ) {
            $commission_id      = (int) $this->get_prop( '_commission_id' ); 
            $this->commission   = new Commission( $commission_id );
        }
        return $this->commission;
    }
}
