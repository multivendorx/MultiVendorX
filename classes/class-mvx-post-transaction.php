<?php

if (!defined('ABSPATH'))
    exit;

/**
 * @class       MVX Transaction Class
 *
 * @version     2.2.0
 * @package     MVX
 * @author      Multivendor X
 */
class mvx_transaction {

    private $post_type;

    public function __construct() {
        $this->post_type = 'mvx_transaction';
        $this->register_post_type();
        $this->register_post_status();
    }

    /**
     * Register commission post type
     *
     * @access private
     * @return void
     */
    private function register_post_type() {
        global $MVX;
        if (post_type_exists($this->post_type)) {
            return;
        }
        $labels = array(
            'name' => _x('Transactions', 'post type general name', 'dc-woocommerce-multi-vendor'),
            'singular_name' => _x('Transaction', 'post type singular name', 'dc-woocommerce-multi-vendor'),
            'add_new' => _x('Add New', $this->post_type, 'dc-woocommerce-multi-vendor'),
            'add_new_item' => sprintf(__('Add New %s', 'dc-woocommerce-multi-vendor'), __('Transaction', 'dc-woocommerce-multi-vendor')),
            'edit_item' => sprintf(__('Edit %s', 'dc-woocommerce-multi-vendor'), __('Transaction', 'dc-woocommerce-multi-vendor')),
            'new_item' => sprintf(__('New %s', 'dc-woocommerce-multi-vendor'), __('Transaction', 'dc-woocommerce-multi-vendor')),
            'all_items' => sprintf(__('All %s', 'dc-woocommerce-multi-vendor'), __('Transaction', 'dc-woocommerce-multi-vendor')),
            'view_item' => sprintf(__('View %s', 'dc-woocommerce-multi-vendor'), __('Transaction', 'dc-woocommerce-multi-vendor')),
            'search_items' => sprintf(__('Search %s', 'dc-woocommerce-multi-vendor'), __('Transactions', 'dc-woocommerce-multi-vendor')),
            'not_found' => sprintf(__('No %s found', 'dc-woocommerce-multi-vendor'), __('Transactions', 'dc-woocommerce-multi-vendor')),
            'not_found_in_trash' => sprintf(__('No %s found In trash', 'dc-woocommerce-multi-vendor'), __('Transactions', 'dc-woocommerce-multi-vendor')),
            'parent_item_colon' => '',
            'all_items' => __('Transactions', 'dc-woocommerce-multi-vendor'),
            'menu_name' => __('Transactions', 'dc-woocommerce-multi-vendor')
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => true,
            'exclude_from_search' => true,
            'show_ui' => false,
            'show_in_menu' => false,
            'show_in_nav_menus' => false,
            'query_var' => false,
            'rewrite' => true,
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => true,
            'supports' => array('title', 'editor', 'comments', 'custom-fields', 'excerpt'),
            'menu_position' => 57,
            'menu_icon' => $MVX->plugin_url . '/assets/images/dualcube.png'
        );

        register_post_type($this->post_type, $args);
    }

    /**
     * Register transaction status
     * 
     * @access private
     * @return void
     */
    private function register_post_status() {
        register_post_status('mvx_processing', array(
            'label' => _x('Processing', $this->post_type, 'dc-woocommerce-multi-vendor'),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Processing <span class="count">(%s)</span>', 'Processing <span class="count">(%s)</span>', 'dc-woocommerce-multi-vendor'),
        ));

        register_post_status('mvx_completed', array(
            'label' => _x('Completed', $this->post_type, 'dc-woocommerce-multi-vendor'),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>', 'dc-woocommerce-multi-vendor'),
        ));
        register_post_status('mvx_canceled', array(
            'label' => _x('Canceled', $this->post_type, 'dc-woocommerce-multi-vendor'),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Canceled <span class="count">(%s)</span>', 'Canceled <span class="count">(%s)</span>', 'dc-woocommerce-multi-vendor'),
        ));
    }

    /**
     * Create new transaction
     *
     * @param object $transaction_data
     * @param $transaction_status
     * @param $mode
     * @param bool $paypal_response
     * 
     * @return int $transaction_id
     */
    public function insert_new_transaction($transaction_data, $transaction_status, $mode, $paypal_response = false) {
        global $MVX;
        $trans_id = false;
        if (!empty($transaction_data)) {
            foreach ($transaction_data as $vendor_id => $transaction_detail) {
                $trans_details = array(
                    'post_type' => $this->post_type,
                    'post_title' => sprintf(__('Transaction - %s', 'dc-woocommerce-multi-vendor'), strftime(_x('%B %e, %Y @ %I:%M %p', 'Transaction date parsed by strftime', 'dc-woocommerce-multi-vendor'), current_time( 'timestamp' ))),
                    'post_status' => $transaction_status,
                    'ping_status' => 'closed',
                    'post_author' => $vendor_id
                );
                $trans_id = wp_insert_post($trans_details);
                if ($trans_id) {
                    update_post_meta($trans_id, 'transaction_mode', $mode);
                    if (!isset($transaction_detail['transfer_charge'])) {
                        $transaction_detail['transfer_charge'] = 0;
                    }
                    update_post_meta($trans_id, 'amount', $transaction_detail['amount'] - $transaction_detail['transfer_charge']);
                    update_post_meta($trans_id, 'transfer_charge', $transaction_detail['transfer_charge']);
                    if ($paypal_response) {
                        update_post_meta($trans_id, 'paypal_response', $paypal_response);
                    }
                    update_post_meta($trans_id, 'commission_detail', $transaction_detail['commission_detail']);
                    if ($transaction_status != 'mvx_processing') {
                        $email_admin = WC()->mailer()->emails['WC_Email_Vendor_Commission_Transactions'];
                        $email_admin->trigger($trans_id, $vendor_id);
                        $commission_id = false;
                        foreach ($transaction_detail['commission_detail'] as $commission_id => $order_id) {
                            update_post_meta($commission_id, '_paid_request', $mode);
                            mvx_paid_commission_status($commission_id);
                        }
                    } else {
                        $commission_id = false;
                        foreach ($transaction_detail['commission_detail'] as $commission_id => $order_id) {
                            mvx_paid_commission_status($commission_id);
                            update_post_meta($commission_id, '_paid_request', $mode);
                        }
                    }
                }
            }
        }
        return $trans_id;
    }

    /**
     * Get transaction item total for vendor
     * 
     * @param int $transaction_id
     * @param $vendor
     * @return $item_total
     */
    public function get_transaction_item_totals($transaction_id, $vendor) {
        global $MVX;
        $item_totals = array();
        $transaction_amount = get_post_meta($transaction_id, 'amount', true);
        $transfer_charge = get_post_meta($transaction_id, 'transfer_charge', true);
        $gateway_charge = get_post_meta($transaction_id, 'gateway_charge', true);
        $transaction_mode = get_post_meta($transaction_id, 'transaction_mode', true);
        $item_totals['date'] = array('label' => __('Date of request', 'dc-woocommerce-multi-vendor'), 'value' => mvx_date(get_post($transaction_id)->post_date));
        $item_totals['amount'] = array('label' => __('Amount', 'dc-woocommerce-multi-vendor'), 'value' => wc_price($transaction_amount));
        if ($transfer_charge) {
            $item_totals['transfer_fee'] = array('label' => __('Transfer Fee', 'dc-woocommerce-multi-vendor'), 'value' => wc_price($transfer_charge));
        }
        if($gateway_charge){
            $item_totals['gateway_charge'] = array('label' => __('Gateway Fee', 'dc-woocommerce-multi-vendor'), 'value' => wc_price($gateway_charge));
        }

        if ($transaction_mode == 'direct_bank') {
            $item_totals['via'] = array('label' => __('Transaction Mode', 'dc-woocommerce-multi-vendor'), 'value' => __('Direct Bank', 'dc-woocommerce-multi-vendor'));
            $item_totals['bank_account_type'] = array('label' => __('Bank Account Type', 'dc-woocommerce-multi-vendor'), 'value' => get_user_meta($vendor->id, '_vendor_bank_account_type', true));
            $item_totals['bank_account_name'] = array('label' => __('Bank Account Number', 'dc-woocommerce-multi-vendor'), 'value' => get_user_meta($vendor->id, '_vendor_bank_account_number', true));
            $item_totals['bank_name'] = array('label' => __('Bank Name', 'dc-woocommerce-multi-vendor'), 'value' => get_user_meta($vendor->id, '_vendor_bank_name', true));
            $item_totals['aba_routing_number'] = array('label' => __('ABA Routing Number', 'dc-woocommerce-multi-vendor'), 'value' => get_user_meta($vendor->id, '_vendor_aba_routing_number', true));
            $item_totals['bank_address'] = array('label' => __('Bank Address', 'dc-woocommerce-multi-vendor'), 'value' => get_user_meta($vendor->id, '_vendor_bank_address', true));
            $item_totals['destination_currency'] = array('label' => __('Destination Currency', 'dc-woocommerce-multi-vendor'), 'value' => get_user_meta($vendor->id, '_vendor_destination_currency', true));
            $item_totals['iban'] = array('label' => __('IBAN', 'dc-woocommerce-multi-vendor'), 'value' => get_user_meta($vendor->id, '_vendor_iban', true));
            $item_totals['account_holder_name'] = array('label' => __('Account Holder Name', 'dc-woocommerce-multi-vendor'), 'value' => get_user_meta($vendor->id, '_vendor_account_holder_name', true));
        } else if ($transaction_mode == 'paypal_masspay') {
            $item_totals['via'] = array('label' => __('Transaction Mode', 'dc-woocommerce-multi-vendor'), 'value' => __('PayPal Masspay', 'dc-woocommerce-multi-vendor'));
            $item_totals['paypal_email'] = array('label' => __('PayPal Email', 'dc-woocommerce-multi-vendor'), 'value' => get_user_meta($vendor->id, '_vendor_paypal_email', true));
        } else if ($transaction_mode == 'paypal_payout') {
            $item_totals['via'] = array('label' => __('Transaction Mode', 'dc-woocommerce-multi-vendor'), 'value' => __('PayPal Payout', 'dc-woocommerce-multi-vendor'));
            $item_totals['paypal_email'] = array('label' => __('PayPal Email', 'dc-woocommerce-multi-vendor'), 'value' => get_user_meta($vendor->id, '_vendor_paypal_email', true));
        } else if ($transaction_mode == 'stripe_masspay') {
            $item_totals['via'] = array('label' => __('Transaction Mode', 'dc-woocommerce-multi-vendor'), 'value' => __('Stripe Connect', 'dc-woocommerce-multi-vendor'));
        } else if ($transaction_mode == 'manual') {
            $item_totals['via'] = array('label' => __('Transaction Mode', 'dc-woocommerce-multi-vendor'), 'value' => __('Manual', 'dc-woocommerce-multi-vendor'));
        }
        return apply_filters('mvx_transaction_item_totals', $item_totals, $transaction_id);
    }

    /**
     * Get transaction item details
     *
     * @param int $transaction_id
     */
    public function get_transaction_item_details($transaction_id) {
        global $MVX;
        $commission_details = array();
        $commissions = get_post_meta($transaction_id, 'commission_detail', true);
        $title = array();
        if (is_array($commissions)) {
            foreach ($commissions as $commission_id) {
                $mvx_order = get_mvx_order_by_commission( $commission_id );
                if( $mvx_order ){
                    $order = $mvx_order->get_order();
                    $line_items = $order->get_items( 'line_item' );
                    foreach ( $line_items as $item_id => $item ) {
                        $title[] = esc_html( $item->get_name() );
                    }
                }
            }
        }
        $status = get_post_status($transaction_id);
        if($status == 'mvx_completed'){
            $transaction_status = __('Completed', 'dc-woocommerce-multi-vendor');
        } else if($status == 'mvx_processing'){
            $transaction_status = __('Processing', 'dc-woocommerce-multi-vendor');
        }else{
            $transaction_status = __('Cancelled', 'dc-woocommerce-multi-vendor');
        }
        
        $amount = (float) get_post_meta($transaction_id, 'amount', true) - (float) get_post_meta($transaction_id, 'transfer_charge', true) - (float) get_post_meta($transaction_id, 'gateway_charge', true);
        $commission_details['body'][$commission_id][]['Commission'] = implode(', ', $commissions);
        $commission_details['body'][$commission_id][]['Products'] = implode(', ', $title);
        $commission_details['body'][$commission_id][]['Status'] = $transaction_status;
        $commission_details['body'][$commission_id][]['Amount'] = wc_price($amount);
        $commission_details['header'] = array(__('Commission ID', 'dc-woocommerce-multi-vendor'), __('Products', 'dc-woocommerce-multi-vendor'), __('Status', 'dc-woocommerce-multi-vendor'), __('Amount', 'dc-woocommerce-multi-vendor'));
        return apply_filters( 'mvx_transaction_item_details', $commission_details, $transaction_id );
    }

    /**
     * Get transactions for a period
     */
    public function get_transactions($vendor_term_id = false, $start_date = false, $end_date = false, $transaction_status = false, $offset = false, $no_of = false) {
        global $MVX;

        if (!$no_of)
            $no_of = -1;
        if (!$transaction_status)
            $transaction_status = 'any';

        $args = array(
            'post_type' => 'mvx_transaction',
            'post_status' => $transaction_status,
            'posts_per_page' => $no_of
        );
        if ($offset)
            $args['offset'] = $offset;

        if (isset($vendor_term_id))
            $args['author'] = $vendor_term_id;
        if ($start_date) {
            $start_year = date('Y', strtotime($start_date));
            $start_month = date('n', strtotime($start_date));
            $start_day = date('j', strtotime($start_date));
        }

        if ($end_date) {
            $end_year = date('Y', strtotime($end_date));
            $end_month = date('n', strtotime($end_date));
            $end_day = date('j', strtotime($end_date));
        }


        if ($start_date && !$end_date) {
            $args['date_query'] = array(
                array(
                    'year' => $start_year,
                    'month' => $start_month,
                    'day' => $start_day,
                ),
            );
        } else if ($start_date && $end_date) {
            $args['date_query'] = array(
                array(
                    'after' => array(
                        'year' => $start_year,
                        'month' => $start_month,
                        'day' => $start_day,
                    ),
                    'before' => array(
                        'year' => $end_year,
                        'month' => $end_month,
                        'day' => $end_day,
                    ),
                    'inclusive' => true,
                ),
            );
        }
        $transactions = new WP_Query($args);
        $transactions = $transactions->get_posts();
        $transaction_details = array();

        if ($transactions) {
            foreach ($transactions as $transaction_key => $transaction) {

                $transaction_details[$transaction->ID]['post_date'] = $transaction->post_date;
                if ($transaction->post_status == 'mvx_completed') {
                    $transaction_details[$transaction->ID]['status'] = __('Completed', 'dc-woocommerce-multi-vendor');
                } else if ($transaction->post_status == 'mvx_processing') {
                    $transaction_details[$transaction->ID]['status'] = __('Processing', 'dc-woocommerce-multi-vendor');
                }
                $transaction_details[$transaction->ID]['post_status'] = $transaction->post_status;
                $transaction_details[$transaction->ID]['vendor_id'] = $transaction->post_author;
                $transaction_details[$transaction->ID]['commission'] = floatval(get_post_meta($transaction->ID, 'amount', true)) + floatval(get_post_meta($transaction->ID, 'transfer_charge', true));
                $transaction_details[$transaction->ID]['amount'] = get_post_meta($transaction->ID, 'amount', true);
                $transaction_details[$transaction->ID]['transfer_charge'] = get_post_meta($transaction->ID, 'transfer_charge', true);
                $transaction_details[$transaction->ID]['commission_details'] = get_post_meta($transaction->ID, 'commission_detail', true);
                $transaction_details[$transaction->ID]['total_amount'] = floatval(get_post_meta($transaction->ID, 'amount', true)) - floatval(get_post_meta($transaction->ID, 'transfer_charge', true)) - floatval(get_post_meta($transaction->ID, 'gateway_charge', true));
                $transaction_details[$transaction->ID]['id'] = $transaction->ID;
                $mode = get_post_meta($transaction->ID, 'transaction_mode', true);
                if ($mode == 'paypal_masspay') {
                    $transaction_details[$transaction->ID]['mode'] = __('PayPal', 'dc-woocommerce-multi-vendor');
                } else if ($mode == 'direct_bank') {
                    $transaction_details[$transaction->ID]['mode'] = __('Direct Bank Transfer', 'dc-woocommerce-multi-vendor');
                }
            }
        }
        return apply_filters('mvx_get_transaction_details', $transaction_details);
    }

    /**
     * Create transaction from commissions
     *
     * @param array $commission_ids
     */
    public function create_transactions($commission_ids) {
        global $MVX;
        $transaction_datas = array();
        if (!empty($commission_ids)) {
            foreach ($commission_ids as $commission_id) {
                $vendor_id = get_post_meta($commission_id, '_commission_vendor', true);
                $vendor = get_mvx_vendor_by_term($vendor_id);
                $paid_status = get_post_meta($commission_id, '_paid_status', true);
                $order_id = get_post_meta($commission_id, '_commission_order_id', true);
                $order = new WC_Order($order_id);
                $vendor_shipping = get_post_meta($commission_id, '_shipping', true);
                $vendor_tax = get_post_meta($commission_id, '_tax', true);
                $due_vendor = $vendor->mvx_get_vendor_part_from_order($order, $vendor_id);

                if (!$vendor_shipping)
                    $vendor_shipping = $due_vendor['shipping'];
                if (!$vendor_tax)
                    $vendor_tax = $due_vendor['tax'];

                $amount = get_post_meta($commission_id, '_commission_amount', true);
                $vendor_due = 0;
                $vendor_due = (float) $amount + (float) $vendor_shipping + (float) $vendor_tax;
                $transaction_datas[$vendor_id]['commission_detail'][$commission_id] = $order_id;

                if (!isset($transaction_datas[$vendor_id]['amount']))
                    $transaction_datas[$vendor_id]['amount'] = $vendor_due;
                else
                    $transaction_datas[$vendor_id]['amount'] += $vendor_due;
            }
            $this->insert_new_transaction($transaction_datas, 'mvx_completed', 'manual');
        }
    }

}