<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WC_Email_Admin_Widthdrawal_Request')) :

    /**
     * New Commission Email
     *
     * An email sent to the admin when a new order is received/paid for.
     *
     * @class 		WC_Email_Vendor_Direct_Bank
     * @version		2.0.0
     * @package		WooCommerce/Classes/Emails
     * @extends 	WC_Email
     *
     * @property DC_Commission $object
     */
    class WC_Email_Admin_Widthdrawal_Request extends WC_Email {

        /**
         * Constructor
         */
        function __construct() {
            global $MVX;
            $this->id = 'admin_widthdrawal_request';
            $this->title = __('Withdrawal request to Admin from Vendor by BAC', 'multivendorx');
            $this->description = __('New commissions withdrawal request have been submitted.', 'multivendorx');

            //$this->heading = __('New Commission Withdrawal Request', 'multivendorx');
            //$this->subject = __('[{site_title}] Commission Widthdrawal Request', 'multivendorx');

            $this->template_base = $MVX->plugin_path . 'templates/';
            $this->template_html = 'emails/admin-widthdrawal-request.php';
            $this->template_plain = 'emails/plain/admin-widthdrawal-request.php';


            // Call parent constructor
            parent::__construct();
        }

        /**
         * trigger function.
         *
         * @access public
         *
         * @param Commission $commission Commission paid
         */
        function trigger($trans_id, $vendor_term_id) {

            if (!isset($trans_id) && !isset($vendor_term_id)) {
                return;
            }
            
            $this->object = get_post($trans_id);

            $this->vendor = get_mvx_vendor_by_term($vendor_term_id);

            $commissions = get_post_meta($trans_id, 'commission_detail', true);

            $this->commissions = $commissions;

            $this->transaction_id = $trans_id;

            $this->transaction_mode = get_post_meta($trans_id, 'transaction_mode', true);

            $this->recipient = get_option('admin_email');
            if ( $this->is_enabled() && $this->get_recipient() ) {
                $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
            }
        }

        /**
         * Get email subject.
         *
         * @access  public
         * @return string
         */
        public function get_default_subject() {
            return apply_filters('mvx_admin_widthdrawal_request_email_subject', __('[{site_title}] Commission Widthdrawal Request', 'multivendorx'), $this->object);
        }

        /**
         * Get email heading.
         *
         * @access  public
         * @return string
         */
        public function get_default_heading() {
            return apply_filters('mvx_admin_widthdrawal_request_email_heading', __('New Commission Withdrawal Request', 'multivendorx'), $this->object);
        }

        /**
         * get_content_html function.
         *
         * @access public
         * @return string
         */
        function get_content_html() {
            global $MVX;
            ob_start();
            wc_get_template($this->template_html, array(
                'commissions' => $this->commissions,
                'email_heading' => $this->get_heading(),
                'transaction_mode' => $this->transaction_mode,
                'vendor' => $this->vendor,
                'transaction_id' => $this->transaction_id,
                'sent_to_admin' => false,
                'plain_text' => false,
                'email'         => $this,
                    ), 'dc-product-vendor/', $this->template_base);
            return ob_get_clean();
        }

        /**
         * get_content_plain function.
         *
         * @access public
         * @return string
         */
        function get_content_plain() {
            ob_start();
            wc_get_template($this->template_plain, array(
                'commissions' => $this->commissions,
                'email_heading' => $this->get_heading(),
                'transaction_mode' => $this->transaction_mode,
                'vendor' => $this->vendor,
                'transaction_id' => $this->transaction_id,
                'sent_to_admin' => false,
                'plain_text' => true,
                'email'         => $this,
                    ), 'dc-product-vendor/', $this->template_base);
            return ob_get_clean();
        }

        /**
         * Initialise Settings Form Fields
         *
         * @access public
         * @return void
         */
        function init_form_fields() {
            global $MVX;
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'multivendorx'),
                    'type' => 'checkbox',
                    'label' => __('Enable notification for this email', 'multivendorx'),
                    'default' => 'yes'
                ),
                'subject' => array(
                    'title' => __('Subject', 'multivendorx'),
                    'type' => 'text',
                    'description' => sprintf(__('This controls the email subject line. Leave it blank to use the default subject: <code>%s</code>.', 'multivendorx'), $this->get_default_subject()),
                    'placeholder' => '',
                    'default' => ''
                ),
                'heading' => array(
                    'title' => __('Email Heading', 'multivendorx'),
                    'type' => 'text',
                    'description' => sprintf(__('This controls the main heading contained in the email notification. Leave it blank to use the default heading: <code>%s</code>.', 'multivendorx'), $this->get_default_heading()),
                    'placeholder' => '',
                    'default' => ''
                ),
                'email_type' => array(
                    'title' => __('Email Type', 'multivendorx'),
                    'type' => 'select',
                    'description' => __('Choose format for the email that will be sent.', 'multivendorx'),
                    'default' => 'html',
                    'class' => 'email_type wc-enhanced-select',
                    'options' => $this->get_email_type_options()
                )
            );
        }

    }

    endif;

