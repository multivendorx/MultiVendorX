<?php

if (!defined('ABSPATH'))
exit; // Exit if accessed directly

if (!class_exists('WC_Email_Vendor_Followed_Customer')) :

/**
 * New Order Email
 *
 * An email sent to the vendor when a customer followed a vendor.
 *
 * @class 		WC_Email_Vendor_Followed_Customer
 * @version		3.7
 * @package		WooCommerce/Classes/Emails
 * @author 		WooThemes
 * @extends 	WC_Email
 */
class WC_Email_Vendor_Followed_Customer extends WC_Email {
    var $vendor, $customer;
    /**
     * Constructor
     */
    function __construct() {
        global $MVX;
        $this->id = 'vendor_followed_customer';
        $this->title = __('Vendor Followed From Customer', 'multivendorx');
        $this->description = __('Vendor Followed customer emails are sent to the vendor when a customer followed a vendor.', 'multivendorx');
        $this->template_html = 'emails/vendor-followed-customer.php';
        $this->template_plain = 'emails/plain/vendor-followed-customer.php';
        $this->template_base = $MVX->plugin_path . 'templates/';

        // Call parent constructor
        parent::__construct();
    }

    /**
     * Get email subject.
     *
     * @since  3.7
     * @return string
     */
    public function get_default_subject() {
        return apply_filters('mvx_vendor_followed_customer_email_subject', __("You've Gained a New Follower: Check Out Your Latest Fan!'", 'multivendorx'), $this->object);
    }

    /**
     * Get email heading.
     *
     * @since  3.7
     * @return string
     */
    public function get_default_heading() {
        return apply_filters('mvx_vendor_followed_customer_email_heading', __('Congratulations! {customer_name} is now following you!', 'multivendorx'), $this->object);
    }

    /**
     * trigger function.
     *
     * @access public
     * @return void
     */
    function trigger($vendor_id, $customer_id) {
        $this->vendor = get_mvx_vendor($vendor_id);
        $this->customer = get_userdata($customer_id);
        $vendor_email = $this->vendor->user_data->user_email;
        if($this->vendor && !isset($vendor_email)) return;
        $this->recipient = $vendor_email;
        $this->find[] = '{customer_name}';
        $this->replace[] = $this->customer->user_login;

        if (!$this->is_enabled() || !$this->get_recipient()) {
            return;
        }

        $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
    }

    /**
     * get_content_html function.
     *
     * @access public
     * @return string
     */
    function get_content_html() {
        return wc_get_template_html($this->template_html, array(
            'email_heading' => $this->get_heading(),
            'vendor' => $this->vendor,
            'customer' => $this->customer,
            'sent_to_admin' => false,
            'plain_text' => false,
            'email'         => $this,
                ), 'MultiVendorX/', $this->template_base);
    }

    /**
     * get_content_plain function.
     *
     * @access public
     * @return string
     */
    function get_content_plain() {
        return wc_get_template_html($this->template_plain, array(
            'email_heading' => $this->get_heading(),
            'vendor' => $this->vendor,
            'customer' => $this->customer,
            'sent_to_admin' => false,
            'plain_text' => true,
            'email'         => $this,
                ), 'MultiVendorX/', $this->template_base);
    }
}

endif;
