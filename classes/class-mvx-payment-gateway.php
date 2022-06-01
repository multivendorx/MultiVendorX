<?php

if (!defined('ABSPATH')) {
    exit;
}

abstract class MVX_Payment_Gateway {
    /* is enable gateway */

    public $enabled = 'Enable';
    /* Gateway id */
    public $payment_gateway;
    public $gateway_title = '';
    /* MVX vendor object */
    public $vendor;
    /* array of commission ids */
    public $commissions = array();
    /* Transaction id */
    public $transaction_id;
    public $currency;
    public $transaction_mode;

    public function gateway_logo() { global $MVX; return $MVX->plugin_url . 'assets/images/gateway_logo.png'; }
    
    public function validate_request() {
        return true;
    }

    public function process_payment($vendor, $commissions = array(), $transaction_mode = 'auto') {
        return array();
    }

    public function record_transaction() {
        if ($this->transaction_mode == 'manual' && $this->payment_gateway == 'direct_bank') {
            $commission_status = 'mvx_processing';
        } else {
            $commission_status = 'mvx_completed';
        }
        $transaction_args = array(
            'post_type' => 'mvx_transaction',
            'post_title' => sprintf(__('Transaction - %s', 'multivendorx'), strftime(_x('%B %e, %Y @ %I:%M %p', 'Transaction date parsed by strftime', 'multivendorx'), current_time( 'timestamp' ))),
            'post_status' => $commission_status,
            'ping_status' => 'closed',
            'post_author' => $this->vendor->term_id
        );
        $this->transaction_id = wp_insert_post($transaction_args);
        if (!is_wp_error($this->transaction_id) && $this->transaction_id) {
            $this->update_meta_data($commission_status);
            $this->email_notify($commission_status);
            $this->add_commission_note($this->commissions, sprintf(__('Commission paid via %s <a href="%s">(ID : %s)</a>', 'multivendorx'), $this->gateway_title, get_admin_url('mvx-transaction-details') . 'admin.php?page=mvx-transaction-details&trans_id=' . $this->transaction_id, $this->transaction_id));
        }
    }

    public function get_transaction_total() {
        $transaction_total = 0;
        if (is_array($this->commissions)) {
            foreach ($this->commissions as $commission) {
                $commission_amount = MVX_Commission::commission_totals($commission, 'edit');
                //$commission_amount = get_mvx_vendor_order_amount(array('commission_id' => $commission, 'vendor_id' => $this->vendor->id));
                $transaction_total += (float) $commission_amount;
            }
        }
        return apply_filters('mvx_commission_transaction_amount', $transaction_total, $this->vendor->id, $this->commissions, $this->payment_gateway);
    }

    public function transfer_charge() {
        global $MVX;
        $transfer_charge = 0;
        if ($this->transaction_mode == 'manual') {
            $no_of_orders = get_mvx_global_settings('no_of_orders') ? get_mvx_global_settings('no_of_orders') : 0;
            if (count($MVX->transaction->get_transactions($this->vendor->term_id)) > $no_of_orders) {
                $transfer_charge = (float) get_mvx_global_settings('commission_transfer');
            }
        }
        return apply_filters('mvx_commission_transfer_charge_amount', $transfer_charge, $this->get_transaction_total(), $this->vendor, $this->commissions, $this->payment_gateway);
    }

    public function gateway_charge() {
        $gateway_charge = $admin_gateway_charge = $global_charges = 0;
        $is_enable_gateway_charge = get_mvx_global_settings('payment_gateway_charge');
        $order_totals = $this->vendor_wise_order_total();
        if ($is_enable_gateway_charge == 'Enable') {
            $payment_gateway_charge_type = get_mvx_global_settings('payment_gateway_charge_type') ? get_mvx_global_settings('payment_gateway_charge_type')['value'] : '';
            $gateway_charge_amount = floatval(get_mvx_global_settings("percent_gayeway_amount_{$this->payment_gateway}"));
            $carrier = get_mvx_global_settings('gateway_charges_cost_carrier') ? get_mvx_global_settings('gateway_charges_cost_carrier')['value'] : '';
            if ($gateway_charge_amount) {
                foreach ($order_totals as $order_id => $details) {
                    $order = wc_get_order($order_id);
                    if (apply_filters('mvx_gateway_charge_with_refunded_order_amount', true)) {
                        $order_total =  $details['order_total'];
                        $vendor_total = $details['vendor_total'];
                    } else {
                        $order_total = $order->get_total_refunded() ? ( $details['order_total'] - $order->get_total_refunded() ) : $details['order_total'];
                        $vendor_total = $order->get_total_refunded() ? ( $details['vendor_total'] - $order->get_total_refunded() ) : $details['vendor_total'];
                    }
                    $order_gateway_charge = 0;
                    $vendor_ratio = ($vendor_total / $order_total);
                    if ('percent' === $payment_gateway_charge_type) {
                        $parcentize_charges = ($order_total * $gateway_charge_amount) / 100;
                        $order_gateway_charge = ($vendor_ratio) ? $vendor_ratio * $parcentize_charges : $parcentize_charges;
                    }else if ('fixed_with_percentage' === $payment_gateway_charge_type) {
                        $gateway_fixed_charge_amount = floatval(get_mvx_global_settings("fixed_gayeway_amount_{$this->payment_gateway}"));
                        $parcentize_charges = (($order_total * $gateway_charge_amount) / 100 );
                        $fixed_charges = floatval($gateway_fixed_charge_amount) / count($details['order_marchants']);
                        $order_gateway_charge = ($vendor_ratio) ? ($vendor_ratio * $parcentize_charges) + $fixed_charges : ($parcentize_charges + $fixed_charges);
                    }else{
                        $fixed_charges = floatval($gateway_charge_amount) / count($details['order_marchants']);
                        $order_gateway_charge = $fixed_charges;
                    }
                    $gateway_charge += $order_gateway_charge; 
                }
                
                if($carrier == 'separate'){
                    //$gateway_charge = 0;
                    if ('percent' === $payment_gateway_charge_type) {
                        $gateway_charge = ($this->get_transaction_total() * $gateway_charge_amount) / 100;
                    }else if ('fixed_with_percentage' === $payment_gateway_charge_type) {
                        $gateway_fixed_charge_amount = floatval(get_mvx_global_settings("fixed_gayeway_amount_{$this->payment_gateway}"));
                        $gateway_charge = (($this->get_transaction_total() * $gateway_charge_amount) / 100 ) + floatval($gateway_fixed_charge_amount);
                    }else{
                        $gateway_charge = floatval($gateway_charge_amount);
                    }
                }
                
                if($carrier == 'admin')
                    $gateway_charge = 0;
            }
        }
        return apply_filters('mvx_commission_gateway_charge_amount', $gateway_charge, $order_totals, $this->vendor, $this->commissions, $this->get_transaction_total(), $this->payment_gateway);
    }
    
    public function vendor_wise_order_total(){
        $vendor_wise_order_total = array();
        if (is_array($this->commissions)) {
            foreach ($this->commissions as $commission) {
                $order_id = get_post_meta($commission, '_commission_order_id', true);
                $order_charges = mvx_get_vendor_specific_order_charge($order_id);
                $vendor_wise_order_total[$order_id] = array(
                    'order_total'       => $order_charges['order_total'],
                    'vendor_total'      => $order_charges[$this->vendor->id],
                    'order_marchants'   => $order_charges['order_marchants'],
                );
            }
        }
        return apply_filters('mvx_vendor_wise_order_total', $vendor_wise_order_total, $this->vendor, $this->commissions, $this->payment_gateway, $this->get_transaction_total());
    }

    public function update_meta_data($commission_status = 'mvx_processing') {
        update_post_meta($this->transaction_id, 'transaction_mode', $this->payment_gateway);
        update_post_meta($this->transaction_id, 'payment_mode', $this->transaction_mode);
        $transfar_charge = $this->transfer_charge($this->transaction_mode);
        update_post_meta($this->transaction_id, 'transfer_charge', $transfar_charge);
        $gateway_charge = $this->gateway_charge();
        update_post_meta($this->transaction_id, 'gateway_charge', $gateway_charge);
        $transaction_amount = $this->get_transaction_total();
        update_post_meta($this->transaction_id, 'amount', $transaction_amount);
        $total_amount = $transaction_amount - $transfar_charge - $gateway_charge;
        update_post_meta($this->transaction_id, 'total_amount', $total_amount);
        update_post_meta($this->transaction_id, 'commission_detail', $this->commissions);

        foreach ($this->commissions as $commission) {
            update_post_meta($commission, '_paid_request', $this->payment_gateway);
            if ($commission_status == 'mvx_completed') {
                mvx_paid_commission_status($commission);
                update_post_meta($this->transaction_id, 'paid_date', date("Y-m-d H:i:s"));
            }
        }
        do_action('mvx_transaction_update_meta_data', $commission_status, $this->transaction_id, $this->vendor, $this);
    }

    public function email_notify($commission_status = 'mvx_processing') {
        switch ($this->payment_gateway) {
            case 'direct_bank':
                $email_vendor = WC()->mailer()->emails['WC_Email_Vendor_Direct_Bank'];
                $email_vendor->trigger($this->transaction_id, $this->vendor->term_id);
                if(!current_user_can('administrator')) :
                $email_admin = WC()->mailer()->emails['WC_Email_Admin_Widthdrawal_Request'];
                $email_admin->trigger($this->transaction_id, $this->vendor->term_id);
                endif;
                break;
            case 'paypal_masspay':
            case 'paypal_payout':
            case 'stripe_masspay':
                if($commission_status != 'mvx_processing'){
                    $email_admin = WC()->mailer()->emails['WC_Email_Vendor_Commission_Transactions'];
                    $email_admin->trigger($this->transaction_id, $this->vendor->term_id);
                }else{
                    $email_admin = WC()->mailer()->emails['WC_Email_Admin_Widthdrawal_Request'];
                    $email_admin->trigger($this->transaction_id, $this->vendor->term_id);
                }
                break;
            default :
                break;
        }
        do_action('mvx_transaction_email_notification', $this->payment_gateway, $commission_status, $this->transaction_id, $this->vendor);
    }

    public function add_commission_note($commissions, $note = '') {
        if (is_array($commissions)) {
            foreach ($commissions as $commission) {
                MVX_Commission::add_commission_note($commission, $note);
            }
        } else {
            MVX_Commission::add_commission_note($commissions, $note);
        }
    }

}
