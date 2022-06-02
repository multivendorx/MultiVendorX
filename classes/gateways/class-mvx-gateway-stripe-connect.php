<?php

if (!defined('ABSPATH')) {
    exit;
}
use Stripe\Stripe;
use Stripe\Transfer;
use Stripe\OAuth;

class MVX_Gateway_Stripe_Connect extends MVX_Payment_Gateway {

    public $id;
    public $gateway_title;
    public $payment_gateway;
    public $message = array();
    private $client_id;
    private $client_secret;
    private $test_mode = false;
    private $payout_mode = 'true';
    private $reciver_email;
    private $api_endpoint;
    private $token_endpoint;
    private $access_token;
    private $token_type;

    public function __construct() {
        $this->id = 'stripe_masspay';
        $this->gateway_title = __('Stripe connect', 'multivendorx');
        $this->payment_gateway = $this->id;
        $this->enabled = mvx_is_module_active('stripe-connect') ? 'Enable' : '';
        // Disconnect Vendor stripe account
        add_action('before_mvx_vendor_dashboard', array($this, 'disconnect_stripe_account'));
        // Stripe authorization
        add_action('wp_ajax_marketplace_stripe_authorize', array(&$this, 'marketplace_stripe_authorize')); 
        // stripe return back to dashboard page
        add_action('wp_ajax_marketplace_stripe_authorize?error=access_denied', array(&$this, 'marketplace_stripe_return_business')); 
    }
    
    public function gateway_logo() { global $MVX; return $MVX->plugin_url . 'assets/images/'.$this->id.'.png'; }

    public function process_payment($vendor, $commissions = array(), $transaction_mode = 'auto', $transfer_args = array()) {
        $this->vendor = $vendor;
        $this->commissions = $commissions;
        $this->currency = get_woocommerce_currency();
        $this->transaction_mode = $transaction_mode;
        $this->is_connected = get_user_meta($this->vendor->id, 'vendor_connected', true);
        $this->stripe_user_id = get_user_meta($this->vendor->id, 'stripe_user_id', true);
        $this->is_testmode = get_mvx_vendor_settings('testmode') ? true : false;
        $this->secret_key = $this->is_testmode ? get_mvx_vendor_settings('test_secret_key') : get_mvx_vendor_settings('live_secret_key');
        
        if ($this->validate_request()) {
            $transfer_obj = $this->process_stripe_payment($transfer_args);
            if($transfer_obj){
                $this->record_transaction();
                if ($this->transaction_id) {
                    return array('message' => __('New transaction has been initiated', 'multivendorx'), 'type' => 'success', 'transaction_id' => $this->transaction_id, 'transfer_obj' => $transfer_obj);
                }
            } else{
                return $this->message;
            }
        } else{
            return $this->message;
        }
    }

    public function validate_request() {
        global $MVX;
        if ($this->enabled != 'Enable') {
            $this->message[] = array('message' => __('Invalid payment method', 'multivendorx'), 'type' => 'error');
            return false;
        } else if (!$this->is_connected && !$this->stripe_user_id) {
            $this->message[] = array('message' => __('Please connect with stripe account', 'multivendorx'), 'type' => 'error');
            return false;
        } else if (!$this->secret_key) {
            $this->message[] = array('message' => __('Stripe setting is not configured properly please contact site administrator', 'multivendorx'), 'type' => 'error');
            return false;
        }
        if ($this->transaction_mode != 'admin') {
            /* handel thesold time */
            $threshold_time = get_mvx_global_settings('commission_threshold_time') ? get_mvx_global_settings('commission_threshold_time') : 0;
            if ($threshold_time > 0) {
                foreach ($this->commissions as $index => $commission) {
                    if (intval((date('U') - get_the_date('U', $commission)) / (3600 * 24)) < $threshold_time) {
                        unset($this->commissions[$index]);
                    }
                }
            }
            /* handel thesold amount */
            $thesold_amount = get_mvx_global_settings('commission_threshold') ? get_mvx_global_settings('commission_threshold') : 0;
            if ($this->get_transaction_total() > $thesold_amount) {
                return true;
            } else {
                $this->message[] = array('message' => __('Minimum thesold amount to withdrawal commission is ' . $thesold_amount, 'multivendorx'), 'type' => 'error');
                return false;
            }
        }
        return parent::validate_request();
    }

    private function process_stripe_payment($args) {
        try {
            Stripe::setApiKey($this->secret_key);
            $transfer_args = array(
                'amount' => $this->get_stripe_amount(),
                'currency' => $this->currency,
                'destination' => $this->stripe_user_id,
                'description' => apply_filters('mvx_stripe_description_at_paid_time', 'Commissions are ' . implode(",", $this->commissions) . ' paid from ' . get_bloginfo('title'), $this->commissions),
            );
            $transfer_args = wp_parse_args($args, $transfer_args);
            return Transfer::create($transfer_args);
        } catch (\Stripe\Error\InvalidRequest $e) {
            $this->message[] = array('message' => $e->getMessage(), 'type' => 'error');
            doProductVendorLOG(print_r($e->getMessage(), true));
        } catch (\Stripe\Error\Authentication $e) {
            $this->message[] = array('message' => $e->getMessage(), 'type' => 'error');
            doProductVendorLOG(print_r($e->getMessage(), true));
        } catch (\Stripe\Error\ApiConnection $e) {
            $this->message[] = array('message' => $e->getMessage(), 'type' => 'error');
            doProductVendorLOG(print_r($e->getMessage(), true));
        } catch (\Stripe\Error\Base $e) {
            $this->message[] = array('message' => $e->getMessage(), 'type' => 'error');
            doProductVendorLOG(print_r($e->getMessage(), true));
        } catch (Exception $e) {
            $this->message[] = array('message' => $e->getMessage(), 'type' => 'error');
            doProductVendorLOG(print_r($e->getMessage(), true));
        }
        return false;
    }
    
    private function get_stripe_amount(){
        $amount_to_pay = round($this->get_transaction_total() - $this->transfer_charge($this->transaction_mode) - $this->gateway_charge(), 2);
        switch (strtoupper($this->currency)) {
            // Zero decimal currencies.
            case 'BIF' :
            case 'CLP' :
            case 'DJF' :
            case 'GNF' :
            case 'JPY' :
            case 'KMF' :
            case 'KRW' :
            case 'MGA' :
            case 'PYG' :
            case 'RWF' :
            case 'VND' :
            case 'VUV' :
            case 'XAF' :
            case 'XOF' :
            case 'XPF' :
                $amount_to_pay = absint($amount_to_pay);
                break;
            default :
                $amount_to_pay = round($amount_to_pay, 2) * 100; // In cents.
                break;
        }
        return $amount_to_pay;
    }
    
    public function disconnect_stripe_account() {
        if (isset($_POST['disconnect_stripe'])) {
            $user = wp_get_current_user();
            $user_id = $user->ID;
            $vendor = get_mvx_vendor($user_id);

            $stripe_settings = mvx_is_module_active('stripe-connect') ? 'Enable' : '';

            $stripe_user_id = get_user_meta($user_id, 'stripe_user_id', true);
            
            if (isset($stripe_settings) && $stripe_settings != 'Enable' && empty($stripe_user_id)) {
                return;
            }
  
            $testmode = get_mvx_vendor_settings('testmode') ? true : false;
            $client_id = $testmode ? get_mvx_vendor_settings('test_client_id') : get_mvx_vendor_settings('live_client_id');
            $secret_key = $testmode ? get_mvx_vendor_settings('test_secret_key') : get_mvx_vendor_settings('live_secret_key');
            $token_request_body = array(
                'client_id' => $client_id,
                'stripe_user_id' => $stripe_user_id
                    );

            Stripe::setApiKey($secret_key);
            
            try {
                $resp = OAuth::deauthorize($token_request_body);
                if ($vendor && isset($resp->stripe_user_id)) {
                    delete_user_meta($user_id, 'vendor_connected');
                    delete_user_meta($user_id, 'admin_client_id');
                    delete_user_meta($user_id, 'access_token');
                    delete_user_meta($user_id, 'refresh_token');
                    delete_user_meta($user_id, 'stripe_publishable_key');
                    delete_user_meta($user_id, 'stripe_user_id');
                    wc_add_notice(__('Your account has been disconnected', 'multivendorx'), 'success');
                } else {
                    wc_add_notice(__('Unable to disconnect your account please try again', 'multivendorx'), 'error');
                }
            } catch (\Stripe\Error\OAuth\OAuthBase $e) {
                doProductVendorLOG("Stripe deauthorize error: " . $e->getMessage());
                doProductVendorLOG(json_encode($resp));
                wc_add_notice($e->getMessage(), 'error');
            }
        }
    }
    
    public function marketplace_stripe_authorize(){
        $stripe_settings = mvx_is_module_active('stripe-connect') ? 'Enable' : '';
        if (isset($stripe_settings) && $stripe_settings == 'Enable') {

            $testmode = get_mvx_vendor_settings('testmode') ? true : false;
            $client_id = $testmode ? get_mvx_vendor_settings('test_client_id') : get_mvx_vendor_settings('live_client_id');
            $secret_key = $testmode ? get_mvx_vendor_settings('test_secret_key') : get_mvx_vendor_settings('live_secret_key');
            if (isset($client_id) && isset($secret_key)) {
                if (isset($_REQUEST['code'])) {
                    $code = wc_clean($_REQUEST['code']);
                    if (!is_user_logged_in()) {
                        if (isset($_REQUEST['state'])) {
                            $user_id = wc_clean($_REQUEST['state']);
                        }
                    } else {
                        $user_id = get_current_user_id();
                    }
                    $token_request_body = array(
                        'client_id' => $client_id,
                        'grant_type' => 'authorization_code',
                        'code' => $code
                    );

                    Stripe::setApiKey($secret_key);
                    try {
                        $resp = OAuth::token($token_request_body);
                        if (!isset($resp->error)) {
                            update_user_meta($user_id, 'vendor_connected', 1);
                            update_user_meta($user_id, 'admin_client_id', $client_id);
                            update_user_meta($user_id, 'access_token', $resp->access_token);
                            update_user_meta($user_id, 'refresh_token', $resp->refresh_token);
                            update_user_meta($user_id, 'stripe_publishable_key', $resp->stripe_publishable_key);
                            update_user_meta($user_id, 'stripe_user_id', $resp->stripe_user_id);
                            update_user_meta($user_id, '_vendor_payment_mode', 'stripe_masspay');
                            wp_redirect(mvx_get_vendor_dashboard_endpoint_url(get_mvx_vendor_settings('mvx_vendor_billing_endpoint', 'seller_dashbaord', 'vendor-billing' )));
                            exit();
                        } else {
                            update_user_meta($user_id, 'vendor_connected', 0);
                            wp_redirect(mvx_get_vendor_dashboard_endpoint_url(get_mvx_vendor_settings('mvx_vendor_billing_endpoint', 'seller_dashbaord', 'vendor-billing' )));
                            exit();
                        }
                    } catch (\Stripe\Error\OAuth\OAuthBase $e) {
                        doProductVendorLOG("Stripe authorize error: " . $e->getMessage());
                        doProductVendorLOG(json_encode($resp));
                        update_user_meta($user_id, 'vendor_connected', 0);
                        wp_redirect(mvx_get_vendor_dashboard_endpoint_url(get_mvx_vendor_settings('mvx_vendor_billing_endpoint', 'seller_dashbaord', 'vendor-billing' )));
                        exit();
                    }
                } elseif (isset($_REQUEST['error'])) {
                    wp_redirect(mvx_get_vendor_dashboard_endpoint_url(get_mvx_vendor_settings('mvx_vendor_billing_endpoint', 'seller_dashbaord', 'vendor-billing' )));
                    exit();
                }
            }
        }
    }

    public function marketplace_stripe_return_business() {
        wp_redirect(mvx_get_vendor_dashboard_endpoint_url(get_mvx_vendor_settings('mvx_vendor_billing_endpoint', 'seller_dashbaord', 'vendor-billing' )));
        exit();
    }   

}
