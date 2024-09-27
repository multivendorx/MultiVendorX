<?php

namespace MultiVendorX\Commission;

use MultiVendorX\Vendor\VendorUtil as VendorUtil;
use MultiVendorX\Commission\CommissionUtil as CommissionUtil;

defined('ABSPATH') || exit;

/**
 * MVX Commission class
 *
 * @version		2.2.0
 * @package		MultivendorX
 * @author 		MultiVendorX
 */

class Commission {
    // Member variable of commission class.
    private $id;
    private $commission;

    /**
     * Constructor function.
     * @param int | object $commission commission id
     */
    public function __construct( $commission ) {
        if ( is_int( $commission ) ) {
            $this->id           = $commission;
            $this->commission   = CommissionUtil::get_commission_db( $commission );
        } else {
            $this->id           = $commission->ID;
            $this->commission   = $commission;
        }
    }

    /**
     * Get commission id.
     * @return mixed
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Get commission information.
     * @param   string $key
     * @return  mixed
     */
    public function get_data( $key ) {
        return $this->commission->{ $key };
    }

    /**
     * Get the vendor of the commission.
     * @return \MVX_Vendor|null
     */
    public function get_vendor() {
        return VendorUtil::get_vendor( $this->get_data('vendor_id') );
    }

    /**
     * Get the commission amount include refund
     * @return mixed commission amount include refund
     */
    public function get_amount_include_refund() {
        $commission_amount       = $this->get_data( 'commission_amount' );
        $commission_refunded     = $this->get_data( 'commission_refunded' );
        return $commission_amount + $commission_refunded;
    }

    /**
     * Get the total commission amount include refund.
     * @return float total amount include refund.
     */
    public function get_total_amount_include_refund() {
        $commission_total       = $this->get_data( 'commission_total' );
        $commission_refunded    = $this->get_data( 'commission_refunded' );
        return $commission_total + $commission_refunded;
    }
}