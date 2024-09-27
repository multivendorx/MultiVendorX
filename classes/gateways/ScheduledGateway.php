<?php

namespace MultiVendorX\Gateways;

use MultiVendorX\Commission\CommissionUtil;
use MultiVendorX\Vendor\VendorUtil;

defined('ABSPATH') || exit;

/**
 * Provide utility function for scheduled getway.
 *
 * @author 		MultiVendorX
 * @package     MultiVendorX/Gateways
 * @version     0.0.1
 */

abstract class ScheduledGateway {
    public $id                  = '';
    public $title               = '';
    public $description         = '';
    public $enabled             = true;
    public $test_mode           = false;
    public $admin_form_fields   = [];
    public $vendor_form_fields  = [];
    private $error_messages     = [];
    
    /**
     * Abstruct function of process payment.
     * Should overwrite in derived class.
     * @param   array $payment_data information for process payment.
     * @return  array payment details after process payment.
     */
    public function process_payment( $payment_data ) {
        return [];
    }

    /**
     * Process payment commission wise. Process a single vendor's commission.
     * Process a single vendor's selected commission passed in commission_ids array.
     * If commission_ids array is not passed it process all commission of provided vendor id.
     * @param   int $vendor_id
     * @param   array $commission_ids
     * @return  void
     */
    public function payment_commisssions( $vendor_id, $commission_ids = null ) {
        // get vendor object.
        $vendor = VendorUtil::get_vendor( $vendor_id );

        $filter = [ 'vendor_id' => $vendor_id ];

        // If commission ids present filter based on provided commission id.
        if ( $commission_ids ) {
            $filter['ID']   = [
                'compare'   => 'IN',
                'value'     => $commission_ids
            ];
        }
        
        // get array of commission object.
        $commissions = CommissionUtil::get_commissions( $filter );

        // Prepare data before process payment.
        GatewayUtil::get_gateway_charge($vendor, $commissions, $this->id );

        // $this->process_payment();
    }

    public function payment_vendors() {
        // $this->process_payment();
    }

    public function get_process_data() {

    }

    public function after_process_payment() {

    }

    /**
     * Get setting value from admin gateway setting tab.
     * @param   string $key 
     * @param   mixed $default optional param, default is set to null.
     * @param   string $gateway_id optional param, default is set to caller object's id.
     * @return  mixed | null 
     */
    public function get_admin_setting( $key, $default = null, $gateway_id = null ) {
        // if gateway id is not provided set object's id to gateway id.
        $gateway_id = $gateway_id ?? $this->id;
    }

    /**
     * Get setting value from vendor gateway setting tab.
     * @param   string $key 
     * @param   mixed $default optional param, default is set to null.
     * @param   string $gateway_id optional param, default is set to caller object's id.
     * @return  mixed | null 
     */
    public function get_vendor_setting( $key, $default = null, $gateway_id = null ) {
        // if gateway id is not provided set object's id to gateway id.
        $gateway_id = $gateway_id ?? $this->id;
    }

    /**
     * Set the error message which will display after payment process.
     * @param mixed $error_message
     * @return void
     */
    public function set_error_message( $error_message ) {
        $this->error_messages[] = $error_message;
    }
}
 