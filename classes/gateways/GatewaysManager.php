<?php

namespace MultiVendorX\Gateways;

defined('ABSPATH') || exit;

/**
 * Demo plugin Install
 * 
 * Manage all payment getway. Provide all payment related necessary funcion.
 *
 * @author 		MultiVendorX
 * @package     MultiVendorX/Gateways
 * @version     0.0.1
 */

class GatewaysManager {
    private $scheduled_container = [];
    private $realtime_container = [];

    public function __construct() {
        // Register realtime and scheduled gateways.
        add_filter( 'woocommerce_payment_gateways', [$this, 'register_realtime_payment_gateway'] );

        $this->register_scheduled_payment_gateway();
    }
    
    /**
     * Register realtime payment gateways.
     * @param   mixed $gateway
     * @return  mixed
     */
    public function register_realtime_payment_gateway( $gateway ) {

        $realtime_gateways = apply_filters( 'register_realtime_payment_gateway', [] );
        
        // Register custom realtime gateways. Woocommerce gateways registration.
        // foreach ( $realtime_gateways as $realtime_gateway ) {
        //     $realtime_gateway = new $realtime_gateways();

        //     $this->realtime_container[] = $realtime_gateway->id;

        //     $gateway[] = $realtime_gateway;
        // }

        // Action hook after scheduled payment gateways register.
        do_action( 'realtime_payment_gateway_registered', $this->scheduled_container );
        
        return $gateway;
    }
    
    /**
     * Register scheduled payment gateyas
     * @return void
     */
    public function register_scheduled_payment_gateway() {

        $scheduled_gateways = apply_filters( 'register_scheduled_payment_gateway', [] );

        // Register custom scheduled gateways
        foreach ( $scheduled_gateways as $shedule_gateway ) {
            // Create the gateway object form string.
            $gateway_object = new $shedule_gateway();
            $this->scheduled_container[ $gateway_object->id ] = $gateway_object;
        }

        // Action hook after scheduled payment gateways register.
        do_action( 'scheduled_payment_gateway_registered', $this->scheduled_container );
    }

    /**
     * Get the payment gateway object.
     * @param   mixed $gateway_id
     * @return  object | null
     */
    public function get_payment_gateway( $gateway_id ) {
        return $this->scheduled_container[$gateway_id] ?? null;
    }

    /**
     * Get all key of register payment gateways.
     * @return array
     */
    public function get_payment_gateways() {
        $payment_gateways = [];

        // Get all woocommerce payment gateways
        $woocommerce_payment_gateways = WC()->payment_gateways()->payment_gateways();

        // Filter realtime payment gateways from woocommerce payment gateways
        foreach( $this->realtime_container as $realtime_gateway_id ) {
            $realtime_gateway = $woocommerce_payment_gateways[ $realtime_gateway_id ];
            if ( $realtime_gateway ) {
                $payment_gateways[] = $realtime_gateway;
            }
        }

        // Merge realtime gateways and scheduled gateways.
        array_merge( $payment_gateways, array_values( $this->scheduled_container ) );

        return $payment_gateways;
    }
}
