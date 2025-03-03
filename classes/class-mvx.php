<?php

use Automattic\WooCommerce\Utilities\OrderUtil as WCOrderUtil;

/**
 * MVX Main Class
 *
 * @version		2.2.0
 * @package		MultivendorX
 * @author 		MultiVendorX
 */
if (!defined('ABSPATH')) {
    exit;
}

final class MVX {

    public $plugin_url;
    public $plugin_path;
    public $version;
    public $token;
    public $library;
    public $shortcode;
    public $admin;
    public $endpoints;
    public $frontend;
    public $vendor_hooks;
    public $template;
    public $ajax;
    public $taxonomy;
    public $product;
    private $file;
    public $settings;
    public $mvx_wp_fields;
    public $user;
    public $order;
    public $report;
    public $vendor_caps;
    public $vendor_dashboard;
    public $transaction;
    public $postcommission;
    public $email;
    public $review_rating;
    public $coupon;
    public $more_product_array = array();
    public $payment_gateway;
    public $mvx_frontend_lib;
    public $cron_job;
    public $product_qna;
    public $commission;
    public $shipping_gateway;
    public $ledger;
    public $vendor_rest_api;
    public $deprecated_hook_handlers = array();
    public $deprecated_funtions;
    public $multivendor_migration;
    public $mvx_usage_tracker ;
    public $upgrade;
    public $mvx_modules;
    public $hpos_is_enabled = false;

    /**
     * Class construct
     * @param object $file
     */
    public function __construct($file) {
        $this->file = $file;
        $this->plugin_url = trailingslashit(plugins_url('', $plugin = $file));
        $this->plugin_path = trailingslashit(dirname($file));
        $this->token = MVX_PLUGIN_TOKEN;
        $this->version = MVX_PLUGIN_VERSION;

        // Intialize MVX Widgets
        $this->init_custom_widgets();
        // Intialize Stripe library
        $this->init_stripe_library();
        // Init payment gateways
        $this->init_payment_gateway();
        // includes functions
        $this->includes();
        // Intialize Crons
        $this->init_cron_job();
        // Load Woo helper
        $this->load_woo_helper();
        // Init package
        $this->init_packages();

        // Intialize MVX
        add_action('init', array(&$this, 'init'));

        add_action('admin_init', array(&$this, 'mvx_admin_init'));
        
        // Secure commission notes
        add_filter('comments_clauses', array(&$this, 'exclude_order_comments'), 10, 1);
        add_filter('comment_feed_where', array(&$this, 'exclude_order_comments_from_feed_where'));
        
        // Add mvx namespace support along with WooCommerce.
        add_filter( 'woocommerce_rest_is_request_to_rest_api', 'mvx_namespace_approve', 10, 1 );
        // Load Vendor Shipping
        if ( !defined('WP_ALLOW_MULTISITE')) {
            add_action( 'woocommerce_loaded', array( &$this, 'load_vendor_shipping' ) );
        }else{
            $this->load_vendor_shipping();
        }
        // Disable woocommerce admin from vendor backend
        //add_filter( 'woocommerce_admin_disabled', array( &$this, 'mvx_remove_woocommerce_admin_from_vendor' ) );

        add_action( 'jwt_auth_token_before_dispatch', array( &$this,'mvx_modify_jwt_auth_plugin_response' ),  20, 2 );

        require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
        $mvx_pro_is_active = is_plugin_active('mvx-pro/mvx-pro.php') ? true : false;
        $this->mvx_modules = [
            [
                'label' =>  __('Marketplace Types', 'multivendorx'),
                'options'       =>  [
                    [
                        'id'           => 'simple',
                        'name'         => __( 'Simple (Downloadable & Virtual)', 'multivendorx' ),
                        'description'  => __( 'Covers the vast majority of any tangible products you may sell or ship i.e books', 'multivendorx' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/simple-product',
                        'parent_category' => __( 'Marketplace Types.', 'multivendorx' ),
                    ],
                    [
                        'id'           => 'variable',
                        'name'         => __( 'Variable', 'multivendorx' ),
                        'description'  => __( 'A product with variations, like different SKU, price, stock option, etc.', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/pricing',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/variable-product',
                    ],
                    [
                        'id'           => 'external',
                        'name'         => __( 'External', 'multivendorx' ),
                        'description'  => __( 'Grants vendor the option to  list and describe on admin website but sold elsewhere', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/pricing',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/external-product/',
                    ],
                    [
                        'id'           => 'grouped',
                        'name'         => __( 'Grouped', 'multivendorx' ),
                        'description'  => __( 'A cluster of simple related products that can be purchased individually', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/grouped-product',
                    ],
                    [
                        'id'           => 'booking',
                        'name'         => __( 'Booking', 'multivendorx' ),
                        'description'  => __( 'Allow customers to book appointments, make reservations or rent equipment etc', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WooCommerce Booking', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/docs/knowledgebase/appointment-product/',
                                'is_active' => is_plugin_active('woocommerce-bookings/woocommerce-bookings.php') ? true :false,
                            ),
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/booking-product',
                    ],
                    [
                        'id'           => 'appointment',
                        'name'         => __( 'Appointments', 'multivendorx' ),
                        'description'  => __( 'Allow customers to book appointments', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WooCommerce Appointment', 'multivendorx'),
                                'plugin_link'   => 'https://bookingwp.com/plugins/woocommerce-appointments/',
                                'is_active'     => is_plugin_active('woocommerce-appointments/woocommerce-appointments.php') ? true :false,
                            ),
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/appointment-product/',
                    ],
                    [
                        'id'           => 'subscription',
                        'name'         => __( 'Subscription', 'multivendorx' ),
                        'description'  => __( 'Let customers subscribe to your products or services and pay weekly, monthly or yearly ', 'multivendorx' ),  
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WooCommerce Subscription', 'multivendorx'),
                                'plugin_link'   => 'https://woocommerce.com/products/woocommerce-subscriptions/',
                                'is_active' => is_plugin_active('woocommerce-subscriptions/woocommerce-subscriptions.php') ? true : false,
                            ),
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/pricing',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/subscription-product',
                    ],
                    [
                        'id'           => 'accommodation',
                        'name'         => __( 'Accommodation', 'multivendorx' ),
                        'description'  => __( 'Grant your guests the ability to quickly book overnight stays in a few clicks', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('WooCommerce Accommodation & Booking', 'multivendorx'),
                                'plugin_link'   => 'https://woocommerce.com/products/woocommerce-accommodation-bookings/',
                                'is_active' => is_plugin_active('woocommerce-bookings/woocommerce-bookings.php') && is_plugin_active('woocommerce-accommodation-bookings/woocommerce-accommodation-bookings.php') ? true : false,
                            ),
                            array(
                                'plugin_name'   => __('WooCommerce Booking', 'multivendorx'),
                                'plugin_link'   => 'https://woocommerce.com/products/woocommerce-bookings/',
                                'is_active' => is_plugin_active('woocommerce-bookings/woocommerce-bookings.php') ? true :false,
                            ),
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/accommodation-product',
                    ],
                    [
                        'id'           => 'bundle',
                        'name'         => __( 'Bundle', 'multivendorx' ),
                        'description'  => __( 'Offer personalized product bundles, bulk discount packages, and assembled products.', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('Product Bundle', 'multivendorx'),
                                'plugin_link'   => 'https://woocommerce.com/products/product-bundles/',
                                'is_active' => is_plugin_active('woocommerce-product-bundles/woocommerce-product-bundles.php') ? true : false,
                            ),
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/bundle-product',
                    ],
                    [
                        'id'           => 'auction',
                        'name'         => __( 'Auction', 'multivendorx' ),
                        'description'  => __( 'Implement an auction system similar to eBay on your store', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('Simple Auction', 'multivendorx'),
                                'plugin_link'   => '',
                                'is_active' => is_plugin_active('woocommerce-simple-auctions/woocommerce-simple-auctions.php') ? true : false,
                            ),
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/auction-product',
                    ],
                    [
                        'id'           => 'rental-pro',
                        'name'         => __( 'Rental Pro', 'multivendorx' ),
                        'description'  => __( 'Perfect for those desiring to offer rental, booking, or real state agencies or services.', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('Rental Pro', 'multivendorx'),
                                'plugin_link'   => 'https://codecanyon.net/item/rnb-woocommerce-rental-booking-system/14835145?ref=redqteam',
                                'is_active' => is_plugin_active('woocommerce-rental-and-booking/redq-rental-and-bookings.php') ? true : false,
                            ),
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/rental-product',
                    ],
                    [
                        'id'           => 'gift-card',
                        'name'         => __( 'Gift Cards', 'multivendorx' ),
                        'description'  => __( "Activate this module to offer gift cards, boosting your store's earnings and attracting fresh clientele.", 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('YITH WooCommerce Gift Cards', 'multivendorx'),
                                'plugin_link'   => 'https://wordpress.org/plugins/yith-woocommerce-gift-cards/',
                                'is_active' => is_plugin_active('yith-woocommerce-gift-cards/init.php') ? true :false,
                            ),
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/gift-card/',
                    ],
                ]
            ],
            [
                'label' =>  __('Seller management ', 'multivendorx'),
                'options'       =>  [
                    [
                        'id'           => 'identity-verification',
                        'name'         => __( 'Seller Identity Verification', 'multivendorx' ),
                        'description'  => __( 'Verify vendors on the basis of Id documents, Address  and Social Media Account  ', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/identity-verification/',
                        'mod_link'     => admin_url('admin.php?page=mvx#&submenu=settings&name=settings-identity-verification'),
                    ],
                ]
            ],
            [
                'label' =>  __('Product management ', 'multivendorx'),
                'options'       =>  [
                    [
                        'id'           => 'spmv',
                        'name'         => __( 'Single Product Multiple Vendor', 'multivendorx' ),
                        'description'  => __( 'Lets multiple vendors sell the same products ', 'multivendorx' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/single-product-multiple-vendors-spmv',
                        'mod_link'     => admin_url('admin.php?page=mvx#&submenu=settings&name=spmv-pages'),
                    ],
                    [
                        'id'           => 'import-export',
                        'name'         => __( 'Import Export  ', 'multivendorx' ),
                        'description'  => __( 'Helps vendors seamlessly import or export product data using CSV etc', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/import-export',
                    ],
                    [
                        'id'           => 'store-inventory',
                        'name'         => __( 'Store Inventory', 'multivendorx' ),
                        'description'  => __( 'Present vendors with the choice to handle normal product quantities, set low inventory and no inventory alarms and manage a subscriber list for the unavailable products.', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/store-inventory',
                        'mod_link'     => admin_url('admin.php?page=mvx#&submenu=settings&name=settings-store-inventory'),
                    ],
                    [
                        'id'           => 'min-max',
                        'name'         => __( 'Min Max Quantities', 'multivendorx' ),
                        'description'  => __( 'Set a minimum or maximum purchase quantity or amount for the products of your marketplace.', 'multivendorx' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://multivendorx.com/docs/non-knowledgebase/min-max-quantities/',
                        'mod_link'     => admin_url('admin.php?page=mvx#&submenu=settings&name=settings-min-max'),
                    ],
                ]
            ],
            [
                'label' =>  __('Payment', 'multivendorx'),
                'options'       =>  [
                    [
                        'id'           => 'bank-payment',
                        'name'         => __( 'Bank Transfer', 'multivendorx' ),
                        'description'  => __( "Manually transfer money directly to the vendor's bank account.", 'multivendorx' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/direct-bank-transfer/',
                    ],
                    [
                        'id'           => 'paypal-masspay',
                        'name'         => __( 'PayPal Masspay', 'multivendorx' ),
                        'description'  => __( 'Schedule payment to multiple vendors at the same time.', 'multivendorx' ),
                        'plan'         => 'free',
                       
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/paypal-masspay/',
                    ],
                    [
                        'id'           => 'paypal-payout',
                        'name'         => __( 'PayPal Payout', 'multivendorx' ),
                        'description'  => __( 'Send payments automatically to multiple vendors as per scheduled', 'multivendorx' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/paypal-payout',
                        'mod_link'     => admin_url('admin.php?page=mvx#&submenu=payment&name=payment-payout'),
                    ],
                    [
                        'id'           => 'paypal-marketplace',
                        'name'         => __( 'PayPal Marketplace (Real time Split)', 'multivendorx' ),
                        'description'  => __( 'Using  split payment pay vendors instantly after a completed order ', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active' => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/paypal-marketplace-real-time-split/',
                        'mod_link'     => admin_url('admin.php?page=wc-settings&tab=checkout&section=mvx_paypal_marketplace'),
                    ],
                    [
                        'id'           => 'stripe-connect',
                        'name'         => __( 'Stripe Connect', 'multivendorx' ),
                        'description'  => __( 'Connect to vendors stripe account and make hassle-free transfers as scheduled.', 'multivendorx' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/stripe-connect',
                        'mod_link'     => admin_url('admin.php?page=mvx#&submenu=payment&name=payment-stripe-connect'),
                    ],
                    [
                        'id'           => 'stripe-marketplace',
                        'name'         => __( 'Stripe Marketplace (Real time Split)', 'multivendorx' ),
                        'description'  => __( 'Real-Time Split payments pays vendor directly after a completed order', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active' => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/stripe-marketplace',
                        'mod_link'     => admin_url('admin.php?page=mvx#&submenu=payment&name=payment-stripe-connect'),
                    ],
                    [
                        'id'           => 'mangopay',
                        'name'         => __( 'Mangopay', 'multivendorx' ),
                        'description'  => __( 'Gives the benefit of both realtime split transfer and scheduled distribution', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active' => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/mangopay',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'razorpay',
                        'name'         => __( 'Razorpay', 'multivendorx' ),
                        'description'  => __( 'For clients looking to pay multiple Indian vendors instantly', 'multivendorx' ),
                        'plan'         => 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('MVX Razorpay Split Payment', 'multivendorx'),
                                'plugin_link'   => 'https://wordpress.org/plugins/mvx-razorpay-split-payment/',
                                'is_active' => is_plugin_active('wcmp-razorpay-split-payment/mvx-razorpay-checkout-gateway.php') ? true :false,
                            )
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/payment/',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ]
                ]
            ],
            [
                'label' =>  __('Shipping', 'multivendorx'),
                'options'       =>  [
                    [
                        'id'           => 'zone-shipping',
                        'name'         => __( 'Zone-Wise Shipping', 'multivendorx' ),
                        'description'  => __( 'Limit vendors to sell in selected zones', 'multivendorx' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/shipping-by-zone/',
                        'mod_link'     => admin_url('admin.php?page=wc-settings&tab=shipping'),
                        'parent_category' => __( 'Shipping.', 'multivendorx' ),
                    ],
                    [
                        'id'           => 'distance-shipping',
                        'name'         => __( 'Distance Shipping', 'multivendorx' ),
                        'description'  => __( 'Calculate Rates based on distance between the vendor store and drop location', 'multivendorx' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/distance-shipping',
                        'mod_link'     => admin_url('admin.php?page=wc-settings&tab=shipping&section=mvx_product_shipping_by_distance'),
                    ],
                    [
                        'id'           => 'country-shipping',
                        'name'         => __( 'Country-Wise Shipping', 'multivendorx' ),
                        'description'  => __( 'Let vendors choose and manage shipping, to countries of their choice', 'multivendorx' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/country-shipping',
                        'mod_link'     => admin_url('admin.php?page=wc-settings&tab=shipping&section=mvx_product_shipping_by_country'),
                    ],
                    [
                        'id'           => 'weight-shipping',
                        'name'         => __( 'Weight Wise Shipping (using Table Rate Shipping)', 'multivendorx' ),
                        'description'  => __( 'Vendors can create shipping rates based on price, weight and quantity', 'multivendorx' ),
                        'plan'         => 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('Table Rate Shipping', 'multivendorx'),
                                'plugin_link'   => 'https://woocommerce.com/products/table-rate-shipping/',
                                'is_active' => is_plugin_active('woocommerce-table-rate-shipping/woocommerce-table-rate-shipping.php') ?true : false,
                            )
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/weight-shipping',
                        'mod_link'     => admin_url('admin.php?page=wc-settings&tab=shipping'),
                    ],
                    [
                        'id'           => 'per-product-shipping',
                        'name'         => __( 'Per Product Shipping', 'multivendorx' ),
                        'description'  => __( 'let vendors add shipping cost to specific products', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('Per Product Shipping', 'multivendorx'),
                                'plugin_link'   => 'https://woocommerce.com/products/per-product-shipping/',
                                'is_active' => is_plugin_active('woocommerce-shipping-per-product/woocommerce-shipping-per-product.php') ?true : false,
                            ),
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/per-product-shipping',
                        'mod_link'     => admin_url('admin.php?page=wc-settings&tab=shipping'),
                    ],
                ]
            ],
            [
                'label' =>  __('Order Managemnet', 'multivendorx'),
                'options'       =>  [
                    [
                        'id'           => 'invoice',
                        'name'         => __( 'Invoice & Packing slip', 'multivendorx' ),
                        'description'  => __( 'Send invoice and packaging slips to vendor', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/invoice-packing-slip',
                        'mod_link'     => admin_url('admin.php?page=mvx#&submenu=settings&name=settings-vendor-invoice'),
                    ],
                    [
                        'id'           => 'marketplace-refund',
                        'name'         => __( 'Marketplace Refund', 'multivendorx' ),
                        'description'  => __( 'Enable customer refund requests & Let vendors manage customer refund ', 'multivendorx' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/marketplace-refund',
                        'mod_link'     => admin_url('admin.php?page=mvx#&submenu=settings&name=refund-management'),
                    ],
                ]
            ],
            [
                'label' =>  __('Store Managemnet', 'multivendorx'),
                'options'       =>  [
                    [
                        'id'           => 'store-location',
                        'name'         => __( 'Store Location', 'multivendorx' ),
                        'description'  => __( "If enabled customers can view a vendor's store location", 'multivendorx' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/store-location',
                        'mod_link'     => admin_url('admin.php?page=mvx#&submenu=settings&name=store'),
                    ],
                    [
                        'id'           => 'store-policy',
                        'name'         => __( 'Store Policy', 'multivendorx' ),
                        'description'  => __( 'Offers vendors the option to set individual store specific policies', 'multivendorx' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/store-policy',
                        'mod_link'     => admin_url('admin.php?page=mvx#&submenu=settings&name=policy'),
                    ],
                    [
                        'id'           => 'follow-store',
                        'name'         => __( 'Follow Store', 'multivendorx' ),
                        'description'  => __( 'Permit customers to follow store, receive updates & lets vendors keep track of customers', 'multivendorx' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/follow-store',
                    ],
                    [
                        'id'           => 'store-review',
                        'name'         => __( 'Store Review', 'multivendorx' ),
                        'description'  => __( 'Allows customers to rate and review stores and their purchased products', 'multivendorx' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/store-review',
                        'mod_link'     => admin_url('admin.php?page=mvx#&submenu=settings&name=review-management'),
                    ],
                    [
                        'id'           => 'business-hours',
                        'name'         => __( 'Business Hours', 'multivendorx' ),
                        'description'  => __( 'Gives vendors the option to set and manage business timings', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/business-hours/',
                    ],
                    [
                        'id'           => 'mvx-blocks',
                        'name'         => __( 'Gutenberg Blocks', 'multivendorx' ),
                        'description'  => __( 'Lets you add widgets using Gutenberg block editor. Use the block to register our widget area on any page or post using the Gutenberg editor.', 'multivendorx' ),
                        'plan'         => 'free',
                    ],
                    [
                        'id'           => 'advertisement',
                        'name'         => __( 'Advertise Product', 'multivendorx' ),
                        'description'  => __( 'Enable the option of paid advertisiment by letting vendors advertise their products on your website.', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/advertise-product/',
                        'mod_link'     => admin_url('admin.php?page=mvx#&submenu=settings&name=settings-advertising'),
                    ],
                ]
            ],
            [
                'label' =>  __('Store Component', 'multivendorx'),
                'options'       =>  [
                    [
                        'id'           => 'vacation',
                        'name'         => __( 'Vacation', 'multivendorx' ),
                        'description'  => __( 'On vacation mode, vendor can allow / disable sale & notify customer accordingly', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/vacation',
                    ],
                    [
                        'id'           => 'staff-manager',
                        'name'         => __( 'Staff Manager', 'multivendorx' ),
                        'description'  => __( 'Lets vendors hire and manage staff to support store', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/staff-manager',
                    ],
                    [
                        'id'           => 'wholesale',
                        'name'         => __( 'Wholesale', 'multivendorx' ),
                        'description'  => __( 'Set wholesale price and quantity for customers ', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/wholesale',
                        'mod_link'     => admin_url('admin.php?page=mvx#&submenu=settings&name=settings-wholesale'),
                    ],
                    [
                        'id'           => 'live-chat',
                        'name'         => __( 'Live Chat', 'multivendorx' ),
                        'description'  => __( 'Allows real-time messaging between vendors and customers', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/live-chat',
                        'mod_link'     => admin_url('admin.php?page=mvx#&submenu=settings&name=settings-live-chat'),
                    ],
                    [
                        'id'           => 'store-support',
                        'name'         => __('Store Support', 'multivendorx'),
                        'description'  => __('Streamline order support with vendor-customer ticketing system.', 'multivendorx'),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/store-support/',
                        'mod_link'     => admin_url('admin.php?page=mvx#&submenu=settings&name=settings-store-support'),
                    ],
                ]
            ],
            [
                'label' =>  __('Analytics', 'multivendorx'),
                'options'       =>  [
                    [
                        'id'           => 'store-analytics',
                        'name'         => __( 'Store Analytics', 'multivendorx' ),
                        'description'  => __( 'Gives vendors detailed store report & connect to google analytics', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/store-analytics',
                        'mod_link'     => admin_url('admin.php?page=mvx-setting-admin'),
                    ],
                    [
                        'id'           => 'store-seo',
                        'name'         => __( 'Store SEO  ', 'multivendorx' ),
                        'description'  => __( 'Lets vendors manage their store SEOs using Rank Math and Yoast SEO', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/store-seo',
                        'mod_link'     => admin_url('admin.php?page=mvx#&submenu=settings&name=settings-seo'),
                    ],
                ]
            ],
            [
                'label' =>  __('Marketplace Membership', 'multivendorx'),
                'options'       =>  [
                    [
                        'id'           => 'marketplace-membership',
                        'name'         => __( 'Marketplace Membership', 'multivendorx' ),
                        'description'  => __( 'Lets Admin create marketplace memberships levels and manage vendor-wise individual capablity  ', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/marketplace-memberhsip',
                        'mod_link'     => admin_url('admin.php?page=mvx#&submenu=settings&name=settings-vendor-membership'),
                    ],
                ]
            ],
            [
                'label' =>  __('Notifictaion', 'multivendorx'),
                'options'       =>  [
                    [
                        'id'           => 'announcement',
                        'name'         => __( 'Announcement', 'multivendorx' ),
                        'description'  => __( 'Lets admin broadcast important news to sellers', 'multivendorx' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/announcement/',                        
                        'mod_link'     => admin_url('admin.php?page=mvx#&submenu=work-board&name=announcement'),
                    ],
                    [
                        'id'           => 'report-abuse',
                        'name'         => __( 'Report Abuse', 'multivendorx' ),
                        'description'  => __( 'Lets customers report false products', 'multivendorx' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/report-abuse',                        
                        'mod_link'     => admin_url('admin.php?page=mvx#&submenu=work-board&name=report-abuse'),
                    ],
                    [
                        'id'           => 'knowladgebase',
                        'name'         => __( 'Knowledgebase', 'multivendorx' ),
                        'description'  => __( 'Admin can share tutorials and othe vendor-specific information with vendors', 'multivendorx' ),
                        'plan'         => 'free',
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/knowledgebase/',
                        'mod_link'     => admin_url('admin.php?page=mvx#&submenu=work-board&name=knowladgebase'),
                    ],
                ]
            ],
            [
                'label' =>  __('Third Party Compatibility', 'multivendorx'),
                'options'       =>  [
                    [
                        'id'           => 'elementor',
                        'name'         => __( 'Elementor', 'multivendorx' ),
                        'description'  => __( 'Create Sellers Pages using Elementors drag and drop feature ', 'multivendorx' ),
                        'plan'         => 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('Elementor Website Builder', 'multivendorx'),
                                'plugin_link'   => 'https://wordpress.org/plugins/elementor/',
                                'is_active' => is_plugin_active('elementor/elementor.php') ? true :false,
                            ),
                            array(
                                'plugin_name'   => __('Elementor Pro', 'multivendorx'),
                                'plugin_link'   => 'https://elementor.com/pricing/',
                                'is_active' => is_plugin_active('elementor-pro/elementor-pro.php') ? true : false,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/mvx-elementor',
                        'parent_category' => __( 'Third Party Compatibility', 'multivendorx' ),
                    ],
                    [
                        'id'           => 'buddypress',
                        'name'         => __( 'Buddypress', 'multivendorx' ),
                        'description'  => __( 'Allows stores to have a social networking feature', 'multivendorx' ),
                        'plan'         => 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('Buddypress', 'multivendorx'),
                                'plugin_link'   => 'https://wordpress.org/plugins/buddypress/',
                                'is_active' => is_plugin_active('buddypress/bp-loader.php') ? true : false,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/mvx-buddypress',
                        'mod_link'     => admin_url('admin.php?page=mvx#&submenu=settings&name=social'),
                    ],
                    [
                        'id'           => 'wpml',
                        'name'         => __( 'WPML', 'multivendorx' ),
                        'description'  => __( 'Gives vendors the option of selling their product in different languages', 'multivendorx' ),
                        'plan'         => 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('The WordPress Multilingual Plugin', 'multivendorx'),
                                'plugin_link'   => 'https://wpml.org/',
                                'is_active' => class_exists( 'SitePress' ) ? true : false,
                            ),
                            array(
                                'plugin_name'   => __('WooCommerce Multilingual – run WooCommerce with WPML', 'multivendorx'),
                                'plugin_link'   => 'https://wordpress.org/plugins/woocommerce-multilingual/',
                                'is_active'     => is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php') ? true : false,
                            )
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/mvx-wpml',
                    ],
                    [
                        'id'           => 'advance-custom-field',
                        'name'         => __( 'Advance Custom field', 'multivendorx' ),
                        'description'  => __( 'Allows for an on demand product field in Add Product section', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('Advanced custom fields', 'multivendorx'),
                                'plugin_link'   => 'https://wordpress.org/plugins/advanced-custom-fields/',
                                'is_active' => class_exists('ACF') ? true : false,
                            ),
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/mvx-acf',
                        'category'  => 'store boosters',
                    ],
                    [
                        'id'           => 'geo-my-wp',
                        'name'         => __( 'GEOmyWP', 'multivendorx' ),
                        'description'  => __( 'Offer vendor the option to attach location info along with their products', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('Geo My wp', 'multivendorx'),
                                'plugin_link'   => 'https://wordpress.org/plugins/geo-my-wp/',
                                'is_active' => is_plugin_active('geo-my-wp/geo-my-wp.php') ? true :false,
                            ),
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/geo-my-wp',
                    ],
                    [
                        'id'           => 'toolset-types',
                        'name'         => __( 'Toolset Types', 'multivendorx' ),
                        'description'  => __( "Allows admin to create custom fields, and taxonomy for vendor's product field", 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('Toolset', 'multivendorx'),
                                'plugin_link'   => 'https://toolset.com/',
                                'is_active' => is_plugin_active('types/wpcf.php') ? true :false,
                            ),
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                    ],
                    [
                        'id'           => 'wp-affiliate',
                        'name'         => __( 'WP Affiliate', 'multivendorx' ),
                        'description'  => __( 'Launch affiliate programme into your marketplace', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('AffiliateWP', 'multivendorx'),
                                'plugin_link'   => 'https://affiliatewp.com/',
                                'is_active' => is_plugin_active('affiliate-wp/affiliate-wp.php') ? true :false,
                            ),
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/affiliate-product/',
                    ],
                    [
                        'id'           => 'product-addon',
                        'name'         => __( 'Product Addon', 'multivendorx' ),
                        'description'  => __( 'Offer add-ons like gift wrapping, special messages etc along with primary products', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('Product Add-Ons', 'multivendorx'),
                                'plugin_link'   => 'https://woocommerce.com/products/product-add-ons/',
                                'is_active' => is_plugin_active('woocommerce-product-addons/woocommerce-product-addons.php') ? true :false,
                            ),
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/mvx-product-addon',
                    ],
                    [
                        'id'           => 'shipstation-module',
                        'name'         => __( 'Shipstation', 'multivendorx' ),
                        'description'  => __( 'Shipstation', 'multivendorx' ),
                        'plan'         => apply_filters('is_mvx_pro_plugin_inactive', true) ? 'pro' : 'free',
                        'required_plugin_list' => array(
                            array(
                                'plugin_name'   => __('MultivendorX Pro', 'multivendorx'),
                                'plugin_link'   => 'https://multivendorx.com/',
                                'is_active'     => $mvx_pro_is_active,
                            ),
                        ),
                        'doc_link'     => 'https://multivendorx.com/docs/knowledgebase/shipstation/',
                    ],
                ]
            ],
        ];
    }

    public function mvx_modify_jwt_auth_plugin_response($data, $user) {
        $data['roles'] = $user->roles;
        $data['store_id'] = $user->ID;
        return $data;
    }
    
    public function exclude_order_comments($clauses) {
        $clauses['where'] .= ( $clauses['where'] ? ' AND ' : '' ) . " comment_type != 'commission_note' ";
        return $clauses;
    }

    public function exclude_order_comments_from_feed_where($where) {
        return $where . ( $where ? ' AND ' : '' ) . " comment_type != 'commission_note' ";
    }

    /**
     * Initialize plugin on WP init
     */
    function init() {
        if(version_compare(WC_VERSION, '8.3.0', '>=')){
            $this->hpos_is_enabled = $this->hpos_is_enabled();
        } 
        if (is_user_mvx_pending_vendor(get_current_vendor_id()) || is_user_mvx_rejected_vendor(get_current_vendor_id()) || is_user_mvx_vendor(get_current_vendor_id())) {
            show_admin_bar(apply_filters('mvx_show_admin_bar', false));
        }
       
        // Init MVX API
        $this->init_mvx_rest_api();
        // Init library
        $this->load_class('library');
        $this->library = new MVX_Library();

        //Init endpoints
        $this->load_class('endpoints');
        $this->endpoints = new MVX_Endpoints();
        // Init custom capabilities
        $this->init_custom_capabilities();

        // Init product vendor custom post types
        $this->init_custom_post();

        $this->load_class('payment-gateways');
        $this->payment_gateway = new MVX_Payment_Gateways();

        $this->load_class('seller-review-rating');
        $this->review_rating = new MVX_Seller_Review_Rating();
        // Init ajax
        if (defined('DOING_AJAX')) {
            $this->load_class('ajax');
            $this->ajax = new MVX_Ajax();
        }
        // Init main admin action class 
        if (is_admin()) {
            $this->load_class('admin');
            $this->admin = new MVX_Admin();
        }
        if (!is_admin() || defined('DOING_AJAX')) {
            // Init main frontend action class
            $this->load_class('frontend');
            $this->frontend = new MVX_Frontend();
            // Init shortcode
            $this->load_class('shortcode');
            $this->shortcode = new MVX_Shortcode();
            //Vendor Dashboard Hooks
            $this->load_class('vendor-hooks');
            $this->vendor_hooks = new MVX_Vendor_Hooks();
        }
        // Init templates
        $this->load_class('template');
        $this->template = new MVX_Template();
        add_filter('template_include', array($this, 'template_loader'), 15);
        // Init vendor action class
        $this->load_class('vendor-details');
        // Init Calculate commission class
        $this->load_class('calculate-commission');
        $this->commission = new MVX_Calculate_Commission();
        // Init Calculate commission class
        $this->load_class('order');
        $this->order = new MVX_Order();
        // Init product vendor taxonomies
        $this->init_taxonomy();
        // Init product action class 
        $this->load_class('product');
        $this->product = new MVX_Product();
        // Init Product QNA
        $this->load_class('product-qna');
        $this->product_qna = new MVX_Product_QNA();
        // Init email activity action class 
        $this->load_class('email');
        $this->email = new MVX_Email();

        $this->load_class('upgrade');
        $this->upgrade = new MVX_Upgrade();

        // MVX Fields Lib
        $this->mvx_wp_fields = $this->library->load_wp_fields();
        // Load Jquery style
        $this->library->load_jquery_style_lib();
        // Init user roles
        $this->init_user_roles();
        // Init custom reports
        $this->init_custom_reports();
        // Init vendor dashboard
        $this->init_vendor_dashboard();
        // Init vendor coupon
        $this->init_vendor_coupon();
        // Init Ledger
        $this->init_ledger();
        
        include_once $this->plugin_path . '/includes/class-mvx-deprecated-action-hooks.php';
        include_once $this->plugin_path . '/includes/class-mvx-deprecated-filter-hooks.php';
        include_once $this->plugin_path . '/includes/mvx-deprecated-funtions.php';
        $this->deprecated_hook_handlers['actions'] = new MVX_Deprecated_Action_Hooks();
        $this->deprecated_hook_handlers['filters'] = new MVX_Deprecated_Filter_Hooks();
        // rewrite endpoint for followers details
        add_rewrite_endpoint( 'followers', EP_ALL );

        if (!wp_next_scheduled('migrate_spmv_multivendor_table') && !get_option('spmv_multivendor_table_migrated', false)) {
            wp_schedule_event(time(), 'every_5minute', 'migrate_spmv_multivendor_table');
        }
        do_action('mvx_init');

         // Init Text Domain
         $this->load_plugin_textdomain();
    }
    
    // Initializing Rest API
    function init_mvx_rest_api() {
        include_once ($this->plugin_path . "/api/class-mvx-rest-controller.php" );
        $this->vendor_rest_api = new MVX_REST_API();
    }
    
    // Initializing Packages
    function init_packages() {
        include_once ($this->plugin_path . "/packages/Packages.php" );
        // Migration
        include_once ($this->plugin_path . "/classes/migration/class-mvx-migration.php" );
        $this->multivendor_migration = new MVX_Migrator();
        // track users data
        include_once ($this->plugin_path . "/classes/class-mvx-usage-tracker.php" );
        $this->mvx_usage_tracker = new MVX_Plugin_Usage_Tracker($this->plugin_path);
    }

    /**
     * plugin admin init callback
     */
    function mvx_admin_init() {
        $previous_plugin_version = mvx_get_option('dc_product_vendor_plugin_db_version');
        /* Migrate MVX data */
        do_mvx_data_migrate($previous_plugin_version, $this->version);
    }
    
    /**
     * Include required core files used in admin and on the frontend.
     */
    public function includes() {
        /**
         * Core functionalities.
         */
        include_once ( $this->plugin_path . "/includes/mvx-order-functions.php" );
        include_once ( $this->plugin_path . "/includes/mvx-hooks-functions.php" );
        // Query classes
        include_once ( $this->plugin_path . '/classes/query/class-mvx-vendor-query.php' );
    }

    /**
     * Load vendor shop page template
     * @param type $template
     * @return type
     */
    function template_loader($template) {
        if (mvx_is_store_page()) {
            $template = $this->template->store_locate_template('taxonomy-dc-vendor-shop.php');
        }
        return $template;
    }

    /**
     * Load Localisation files.
     *
     * Note: the first-loaded translation file overrides any following ones if the same translation is present
     *
     * @access public
     * @return void
     */
    public function load_plugin_textdomain() {
        if ( version_compare( $GLOBALS['wp_version'], '6.7', '<' ) ) {
            load_plugin_textdomain('multivendorx', false, plugin_basename(dirname(dirname(__FILE__))) . '/languages');
        } else {
            load_textdomain( 'multivendorx', WP_LANG_DIR . '/plugins/dc-woocommerce-multi-vendor-' . determine_locale() . '.mo' );
        }
    }

    /**
     * Helper method to load other class
     * @param type $class_name
     * @param type $dir
     */
    public function load_class($class_name = '', $dir = '') {
        if ('' != $class_name && '' != $this->token) {
            if (!$dir)
                require_once ( 'class-' . esc_attr($this->token) . '-' . esc_attr($class_name) . '.php' );
            else
                require_once ( trailingslashit( $dir ) . 'class-' . esc_attr($this->token) . '-' . strtolower($dir) . '-' . esc_attr($class_name) . '.php' );
        }
    }

    /**
     * Sets a constant preventing some caching plugins from caching a page. Used on dynamic pages
     *
     * @access public
     * @return void
     */
    function nocache() {
        if (!defined('DONOTCACHEPAGE')) {
            // WP Super Cache constant
            define("DONOTCACHEPAGE", "true");
        }
    }

    /**
     * Get Ajax URL.
     *
     * @return string
     */
    public function ajax_url() {
        return admin_url('admin-ajax.php', 'relative');
    }

    /**
     * Init MVX User and define users roles
     *
     * @access public
     * @return void
     */
    function init_user_roles() {
        $this->load_class('user');
        $this->user = new MVX_User();
    }

    /**
     * Init MVX product vendor taxonomy.
     *
     * @access public
     * @return void
     */
    function init_taxonomy() {
        $this->load_class('taxonomy');
        $this->taxonomy = new MVX_Taxonomy();
        register_activation_hook(__FILE__, 'flush_rewrite_rules');
    }

    /**
     * Init MVX product vendor post type.
     *
     * @access public
     * @return void
     */
    function init_custom_post() {
        /* Commission post type */
        $this->load_class('post-commission');

        $this->postcommission = new MVX_Commission();
        /* transaction post type */
        $this->load_class('post-transaction');
        $this->transaction = new mvx_transaction();
        /* Flush wp rewrite rule and update permalink structure */
        register_activation_hook(__FILE__, 'flush_rewrite_rules');
    }

    /**
     * Init MVX vendor reports.
     *
     * @access public
     * @return void
     */
    function init_custom_reports() {
        // Init custom report
        $this->load_class('report');
        $this->report = new MVX_Report();
    }

    /**
     * Init MVX vendor widgets.
     *
     * @access public
     * @return void
     */
    function init_custom_widgets() {
        $this->load_class('widget-init');
        new MVX_Widget_Init();
    }

    /**
     * Init MVX vendor capabilities.
     *
     * @access public
     * @return void
     */
    function init_custom_capabilities() {
        $this->load_class('capabilities');
        $this->vendor_caps = new MVX_Capabilities();
    }

    /**
     * Init MVX Dashboard Function
     *
     * @access public
     * @return void
     */
    function init_vendor_dashboard() {
        $this->load_class('vendor-dashboard');
        $this->vendor_dashboard = new MVX_Admin_Dashboard();
    }

    /**
     * Init Cron Job
     * 
     * @access public
     * @return void
     */
    function init_cron_job() {
        add_filter('cron_schedules', array($this, 'add_mvx_corn_schedule'));
        $this->load_class('cron-job');
        $this->cron_job = new MVX_Cron_Job();
    }

    private function init_payment_gateway() {
        $this->load_class('payment-gateway');
    }
    
    /**
     * MVX Shipping
     * 
     * Load vendor shipping
     * @since  3.2.2 
     * @access public
     * @package MultiVendorX/Classes/Shipping
    */
    public function load_vendor_shipping() {
        $this->load_class( 'shipping-gateway' );
        $this->shipping_gateway = new MVX_Shipping_Gateway();
        MVX_Shipping_Gateway::load_class( 'shipping-zone', 'helpers' );
    }

    public function mvx_remove_woocommerce_admin_from_vendor() {
        if (is_user_mvx_vendor(get_current_user_id())) {
            return true;
        }
    }
    
    /**
     * MVX Woo Helper
     * 
     * Load woo helper
     * @since  3.2.3
     * @access public
     * @package MultiVendorX/Include/Woo_Helper
    */
    public function load_woo_helper() {
        //common woo methods
        if ( ! class_exists( 'MVX_Woo_Helper' ) ) {
            require_once ( $this->plugin_path . 'includes/class-mvx-woo-helper.php' );
        }
    }

    /**
     * Init Vendor Coupon
     *
     * @access public
     * @return void
     */
    function init_vendor_coupon() {
        $this->load_class('coupon');
        $this->coupon = new MVX_Coupon();
    }
    
    /**
     * Init Ledger
     *
     * @access public
     * @return void
     */
    function init_ledger() {
        $this->load_class('ledger');
        $this->ledger = new MVX_Ledger();
    }

    /**
     * Add MVX weekly and monthly corn schedule
     *
     * @access public
     * @param schedules array
     * @return schedules array
     */
    function add_mvx_corn_schedule($schedules) {
        $schedules['weekly'] = array(
            'interval' => 604800,
            'display' => __('Every 7 Days', 'multivendorx')
        );
        $schedules['monthly'] = array(
            'interval' => 2592000,
            'display' => __('Every 1 Month', 'multivendorx')
        );
        $schedules['fortnightly'] = array(
            'interval' => 1296000,
            'display' => __('Every 15 Days', 'multivendorx')
        );
        $schedules['every_5minute'] = array(
                'interval' => 5*60, // in seconds
                'display'  => __( 'Every 5 minute', 'multivendorx' )
        );
        
        return $schedules;
    }

    /**
     * Return data for script handles.
     * @since  3.0.6 
     * @param  string $handle
     * @param  array $default params
     * @return array|bool
     */
    public function mvx_get_script_content($handle, $default) {
        global $MVX;

        switch ($handle) {
            case 'frontend_js' :
                $params = array(
                    'ajax_url' => $this->ajax_url(),
                    'messages' => array(
                        'confirm_dlt_pro' => __("Are you sure and want to delete this Product?\nYou can't undo this action ...", 'multivendorx'),
                        'report_abuse_msg' => __('Report has been sent', 'multivendorx'),
                    ),
                    'frontend_nonce' => wp_create_nonce('mvx-frontend')
                );
                break;
            
            case 'mvx_frontend_vdashboard_js' :
            case 'mvx_single_product_multiple_vendors' :
            case 'mvx_customer_qna_js' :
            case 'mvx_new_vandor_announcements_js' :
                $params = array(
                    'ajax_url' => $this->ajax_url(),
                    'dashboard_nonce' => wp_create_nonce('mvx-dashboard'),
                    'vendors_nonce' => wp_create_nonce('mvx-vendors'),
                );
                break;
            
            case 'mvx_seller_review_rating_js' :
                $params = array(
                    'ajax_url' => $this->ajax_url(),
                    'review_nonce' => wp_create_nonce('mvx-review'),
                    'messages' => array(
                        'rating_error_msg_txt' => __('Please rate the vendor', 'multivendorx'),
                        'review_error_msg_txt' => __('Please review your vendor and minimum 10 Character required', 'multivendorx'),
                        'review_success_msg_txt' => __('Your review submitted successfully', 'multivendorx'),
                        'review_failed_msg_txt' => __('Error in system please try again later', 'multivendorx'),
                    ),
                );
                break;
            
            case 'mvx-vendor-shipping' :
            case 'mvx_vendor_shipping' :    
                $params = array(
                    'ajaxurl'	=> $this->ajax_url(),
                    'security' => wp_create_nonce('mvx-shipping'),
                    'i18n' 	=> array(
			'deleteShippingMethodConfirmation'	=> __( 'Are you absolutely sure to delete this shipping method?', 'multivendorx' ),
                    ),
                    'everywhere_else_option'  => __( 'Everywhere Else', 'multivendorx' ),
                    'multiblock_delete_confirm' => __( "Are you sure and want to delete this 'Block'?\nYou can't undo this action ...", "multivendorx" ),
                    'mvx_multiblick_addnew_help' => __( 'Add New Block', 'multivendorx' ),
                    'mvx_multiblick_remove_help' => __( 'Remove Block', 'multivendorx' ),
                );
                break;
            case 'mvx-meta-boxes' :
                $params = array(
                    'coupon_meta' => array( 
                        'coupon_code' => array(
                            'generate_button_text' => esc_html__( 'Generate coupon code', 'multivendorx' ),
                            'characters'           => apply_filters( 'mvx_coupon_code_generator_characters', 'ABCDEFGHJKMNPQRSTUVWXYZ23456789' ),
                            'char_length'          => apply_filters( 'mvx_coupon_code_generator_character_length', 8 ),
                            'prefix'               => apply_filters( 'mvx_coupon_code_generator_prefix', '' ),
                            'suffix'               => apply_filters( 'mvx_coupon_code_generator_suffix', '' ),
                        )
                    )
                );
                break;
                
            default:
                $params = array('ajax_url' => $this->ajax_url(), 'types_nonce' => wp_create_nonce('mvx-types'));
        }
        if ($default && is_array($default)) $params = array_merge($default,$params);
        return apply_filters('mvx_get_script_content', $params, $handle);
    }

    /**
     * Localize a MVX script once.
     * @since  3.0.6 
     * @param  string $handle
     */
    public function localize_script($handle, $params = array(), $object = '') {
        if ( $data = $this->mvx_get_script_content($handle, $params) ) {
            $name = str_replace('-', '_', $handle) . '_script_data';
            if ($object) {
                $name = str_replace('-', '_', $object) . '_script_data';
            }
            wp_localize_script($handle, $name, apply_filters($name, $data));
        }
    }
    
    /**
     * init Stripe library.
     *
     * @access public
     */
    public function init_stripe_library() {
        global $MVX;
        $load_library = mvx_is_module_active('stripe-connect') ? true : false;
        if (apply_filters('mvx_load_stripe_library', $load_library)) {
            $stripe_dependencies = WC_Dependencies_Product_Vendor::stripe_dependencies();
            if ($stripe_dependencies['status']) {
                if (!class_exists("Stripe\Stripe")) {
                    require_once( $this->plugin_path . 'lib/Stripe/init.php' );
                }
            }else{
                switch ($stripe_dependencies['module']) {
                    case 'phpversion':
                        add_action('admin_notices', array($this, 'mvx_stripe_phpversion_required_notice'));
                        break;
                    case 'curl':
                        add_action('admin_notices', array($this, 'mvx_stripe_curl_required_notice'));
                        break;
                    case 'mbstring':
                        add_action('admin_notices', array($this, 'mvx_stripe_mbstring_required_notice'));
                        break;
                    case 'json':
                        add_action('admin_notices', array($this, 'mvx_stripe_json_required_notice'));
                        break;
                    default:
                        break;
                }
            }
        }
    }

    public function mvx_stripe_phpversion_required_notice() {
        ?>
        <div id="message" class="error">
            <p><?php printf(__("%sMVX Stripe Gateway%s requires PHP 5.3.29 or greater. We recommend upgrading to PHP %s or greater.", 'multivendorx' ), '<strong>', '</strong>', '5.6' ); ?></p>
        </div>
        <?php
    }
    
    public function mvx_stripe_curl_required_notice() {
        ?>
        <div id="message" class="error">
            <p><?php printf(__("%sMVX Stripe gateway depends on the %s PHP extension. Please enable it, or ask your hosting provider to enable it.", 'multivendorx' ), '<strong>', '</strong>', 'curl' ); ?></p>
        </div>
        <?php
    }
    
    public function mvx_stripe_mbstring_required_notice() {
        ?>
        <div id="message" class="error">
            <p><?php printf(__("%sMVX Stripe gateway depends on the %s PHP extension. Please enable it, or ask your hosting provider to enable it.", 'multivendorx' ), '<strong>', '</strong>', 'mbstring' ); ?></p>
        </div>
        <?php
    }
    
    public function mvx_stripe_json_required_notice() {
        ?>
        <div id="message" class="error">
            <p><?php printf(__("%sMVX Vendor Membership Stripe gateway depends on the %s PHP extension. Please enable it, or ask your hosting provider to enable it.", 'multivendorx' ), '<strong>', '</strong>', 'json' ); ?></p>
        </div>
        <?php
    }

    /**
     * Parse update notice from readme file.
     * Code adapted from W3 Total Cache and Woocommerce
     * 
     * @param  string $content
     * @param  string $new_version
     * @return string
     */
    private static function parse_update_notice_old($content, $new_version) {
        // Output Upgrade Notice.
        $matches = null;
        $regexp = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote(MVX_PLUGIN_VERSION) . '\s*=|$)~Uis';
        $upgrade_notice = '';

        if (preg_match($regexp, $content, $matches)) {
            $notices = (array) preg_split('~[\r\n]+~', trim($matches[2]));

            // Convert the full version strings to minor versions.
            $notice_version_parts = explode('.', trim($matches[1]));
            $current_version_parts = explode('.', MVX_PLUGIN_VERSION);

            if (3 !== sizeof($notice_version_parts)) {
                return;
            }

            $notice_version = $notice_version_parts[0] . '.' . $notice_version_parts[1];
            $current_version = $current_version_parts[0] . '.' . $current_version_parts[1];

            // Check the latest stable version and ignore trunk.
            if (version_compare($current_version, $notice_version, '<')) {

                $upgrade_notice .= '<div class="mvx_plugin_upgrade_notice dashicons-before">';

                foreach ($notices as $index => $line) {
                    $upgrade_notice .= preg_replace('~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line);
                }

                $upgrade_notice .= '</div> ';
            }
        }

        return wp_kses_post($upgrade_notice);
    }

    
    /**
     * Helper function to get whether custom order tables are enabled or not.
     *
     * This method can be removed, and we can directly use WC OrderUtil::custom_orders_table_usage_is_enabled method in future
     * if we set the minimum wc version requirements to 8.0
     *
     *
     * @return bool
     */
    public static function hpos_is_enabled(): bool {
        return version_compare( WC_VERSION, '8.3.0', '>=' ) ? WCOrderUtil::custom_orders_table_usage_is_enabled() : false;
    }
}
