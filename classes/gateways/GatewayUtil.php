<?php

namespace MultiVendorX\Gateways;

defined('ABSPATH') || exit;

/**
 * Demo plugin Install
 * 
 * MVX_Paymet_Gateway abstruct class.
 *
 * @author 		MultiVendorX
 * @package     MultiVendorX/Gatways
 * @version     0.0.1
 */

abstract class GatewayUtil {
    /**
     * Get transaction total of commissions.
     * @param   mixed $vendor multivendorx vendor object
     * @param   array $commissions array of multivendorx commission object. 
     * @return  int | float total transaction amount.
     */
    public static function get_transaction_total( $vendor, $commissions ) {
        $transaction_total = 0;

        // Calculate total amount
        foreach ( $commissions as $commission ) {
            $transaction_total += $commission->get_total_amount_include_refund();
        }

        return apply_filters( 'mvx_commission_transaction_amount', $transaction_total, $vendor, $commissions );
    }

    /**
     * Get transfer charge of the vendor.
     * @param   mixed $vendor multivendorx vendor object
     * @param   mixed $commissions array of multivendorx commission object.
     * @return  int | float transfer charge.
     */
    public function get_transfer_charge( $vendor, $commissions ) {
        global $MVX;

        $transfer_charge = 0;

        // Get no of order threshold from global settings.
        $number_of_orders = get_mvx_global_settings('no_of_orders');
        $number_of_orders = $number_of_orders ? $number_of_orders : 0;

        // check total no of transaction count is learger then no of order threshold, then assign transfer charge. 
        if ( count( $MVX->transaction->get_transactions( $vendor->term_id ) ) > $number_of_orders ) {
            $transfer_charge = (float) get_mvx_global_settings('commission_transfer');
        }

        return apply_filters( 'mvx_commission_transfer_charge_amount', $transfer_charge, $vendor, $commissions );
    }

    /**
     * Get getway charge of payment getway.
     * @return mixed getway charge
     */
    public static function get_gateway_charge( $vendor, $commissions, $gateway_id ) {
        $gateway_charge = 0;
        
        // If getway charge is enabled then process otherwise return.
        $gateway_charge_enabled = get_mvx_global_settings( 'payment_gateway_charge' );

        if ( !empty( $gateway_charge_enabled ) ) {
            // Calculate vendor-wise order total.
            
            $default_gateway_charge_value = get_mvx_global_settings( "default_gateway_charge_value" );
            
            // Find current payment gateway's fixed and percent charge value.
            $gateway_fixed_value = $gateway_percent_value = 0;
            if( $default_gateway_charge_value ) {
                foreach ( $default_gateway_charge_value as $key => $value ) {
                    if ( $value['key'] == "fixed_gayeway_amount_{$gateway_id}" && $value['value'] ) {
                        $gateway_fixed_value = $value['value'];
                    } else if ( $value['key'] == "percent_gayeway_amount_{$gateway_id}" && $value['value'] ) {
                        $gateway_percent_value = $value['value'];
                    }
                }
            }

            // get getway charge type from global settings.
            $gateway_charge_type = get_mvx_global_settings( 'payment_gateway_charge_type' );
            $gateway_charge_type = $gateway_charge_type ? $gateway_charge_type['value'] : '';

            // get getway charge carrier.
            $gateway_charge_carrier = get_mvx_global_settings( 'gateway_charges_cost_carrier' );
            $gateway_charge_carrier = $gateway_charge_carrier ? $gateway_charge_carrier['value'] : '';

            // calculate gateway charge base on charge carrier.
            if ( $gateway_charge_carrier === 'separate' ) {
                if ( $gateway_charge_type === 'percent' ) {
                    $gateway_charge = self::get_transaction_total($vendor, $commissions ) * $gateway_percent_value / 100;
                } else if ( $gateway_charge_type === 'fixed_with_percentage' ) {
                    $gateway_charge = self::get_transaction_total($vendor, $commissions ) * $gateway_percent_value / 100 + floatval( $gateway_fixed_value );
                } else {
                    $gateway_charge = floatval( $gateway_fixed_value );
                }
            } else if ( $gateway_charge_carrier === 'vendor' ) {
                foreach ( $commissions as $commission ) {
                    $vendor_order   = wc_get_order( $commission->get_data( 'order_id' ) );
                    $order_total    = $vendor_order->get_total();
    
                    if ( apply_filters( 'mvx_gateway_charge_with_refunded_order_amount', true ) && $vendor_order->get_total_refunded() ) {
                        $order_total = $order_total - $vendor_order->get_total_refunded();
                    }
    
                    if ( $gateway_charge_type === 'percent' ) {
                        $gateway_charge += $order_total * $gateway_percent_value / 100;
                    } else if ( $gateway_charge_type === 'fixed_with_percentage' ) {
                        $gateway_charge += $order_total * $gateway_percent_value / 100 + floatval( $gateway_fixed_value );
                    } else {
                        $gateway_charge += floatval( $gateway_fixed_value );
                    }
                }
            }
        }

        return apply_filters('mvx_commission_gateway_charge_amount', $gateway_charge, $vendor, $commissions );
    }
}