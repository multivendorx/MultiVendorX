<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('WC_Email_Vendor_New_Order')) :

    /**
     * New Order Email
     *
     * An email sent to the admin when a new order is received/paid for.
     *
     * @class 		WC_Email_New_Order
     * @version		2.0.0
     * @package		WooCommerce/Classes/Emails
     * @author 		WooThemes
     * @extends 	WC_Email
     */
    class WC_Email_Vendor_New_Order extends WC_Email {
        public $order;
        /**
         * Constructor
         */
        function __construct() {
            global $MVX;
            $this->id = 'vendor_new_order';
            $this->title = __('Vendor New order', 'dc-woocommerce-multi-vendor');
            $this->description = __('New order notification emails are sent when order is processing.', 'dc-woocommerce-multi-vendor');

            $this->placeholders = array(
                '{order_date}'   => '',
                '{order_number}' => '',
            );

            $this->template_html = 'emails/vendor-new-order.php';
            $this->template_plain = 'emails/plain/vendor-new-order.php';
            $this->template_base = $MVX->plugin_path . 'templates/';

            // Call parent constructor
            parent::__construct();
        }

        /**
         * Get email subject.
         *
         * @since  3.1.0
         * @return string
         */
        public function get_default_subject() {
            return apply_filters('mvx_vendor_new_order_email_subject', __('[{site_title}] New vendor order ({order_number}) - {order_date}', 'dc-woocommerce-multi-vendor'), $this->object);
        }

        /**
         * Get email heading.
         *
         * @since  3.1.0
         * @return string
         */
        public function get_default_heading() {
            return apply_filters('mvx_vendor_new_order_email_heading', __('New vendor order', 'dc-woocommerce-multi-vendor'), $this->object);
        }

        /**
         * trigger function.
         *
         * @access public
         * @return void
         */
        function trigger($order_id) {
            if( $order_id ) {
                $vendor_id = get_post_meta($order_id, '_vendor_id', true);
                $vendor = get_mvx_vendor($vendor_id);
                if ($vendor) {
                    $this->object = $this->order = wc_get_order($order_id);
                    $vendor_email = $vendor->user_data->user_email;

                    $this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
                    $this->placeholders['{order_number}'] = $this->object->get_order_number();
                    $this->vendor_email = $vendor_email;
                    $this->vendor_id = $vendor_id;
                    $this->recipient = $vendor_email;
                }

                if (!$this->is_enabled() || !$this->get_recipient()) {
                    return;
                }

                $result = $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
                return $result;
            }
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
                'vendor_id' => $this->vendor_id,
                'order' => $this->order,
                'blogname' => $this->get_blogname(),
                'sent_to_admin' => false,
                'plain_text' => false,
                'email'         => $this,
                    ), 'dc-product-vendor/', $this->template_base);
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
                'vendor_id' => $this->vendor_id,
                'order' => $this->order,
                'blogname' => $this->get_blogname(),
                'sent_to_admin' => false,
                'plain_text' => true,
                'email'         => $this,
                    ), 'dc-product-vendor/', $this->template_base);
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
                    'title' => __('Enable/Disable', 'dc-woocommerce-multi-vendor'),
                    'type' => 'checkbox',
                    'label' => __('Enable this email notification.', 'dc-woocommerce-multi-vendor'),
                    'default' => 'yes'
                ),
                'subject' => array(
                    'title' => __('Subject', 'dc-woocommerce-multi-vendor'),
                    'type' => 'text',
                    'description' => sprintf(__('This controls the email subject line. Leave it blank to use the default subject: <code>%s</code>.', 'dc-woocommerce-multi-vendor'), $this->get_default_subject()),
                    'placeholder' => '',
                    'default' => ''
                ),
                'heading' => array(
                    'title' => __('Email Heading', 'dc-woocommerce-multi-vendor'),
                    'type' => 'text',
                    'description' => sprintf(__('This controls the main heading contained within the email notification. Leave it blank to use the default heading: <code>%s</code>.', 'dc-woocommerce-multi-vendor'), $this->get_default_heading()),
                    'placeholder' => '',
                    'default' => ''
                ),
                'email_type' => array(
                    'title' => __('Email Type', 'dc-woocommerce-multi-vendor'),
                    'type' => 'select',
                    'description' => __('Choose which format of email to be sent.', 'dc-woocommerce-multi-vendor'),
                    'default' => 'html',
                    'class' => 'email_type',
                    'options' => array(
                        'plain' => __('Plain Text', 'dc-woocommerce-multi-vendor'),
                        'html' => __('HTML', 'dc-woocommerce-multi-vendor'),
                        'multipart' => __('Multipart', 'dc-woocommerce-multi-vendor'),
                    )
                )
            );
        }

    }
  
    endif;
