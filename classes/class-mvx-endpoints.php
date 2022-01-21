<?php

/**
 * Define Endpoints of MVX
 * @version 2.5.4
 * @author Multivendor X
 */
class MVX_Endpoints {

    /** @public array Query vars to add to wp */
    public $mvx_query_vars = array();

    /**
     * Class Constructor
     */
    function __construct() {
        add_action('init', array(&$this, 'add_mvx_endpoints'), 15);
        if (!is_admin()) {
            add_filter('query_vars', array($this, 'add_mvx_query_vars'), 0);
            add_action('parse_request', array($this, 'mvx_parse_request'), 0);
            add_action('pre_get_posts', array(&$this, 'mvx_pre_get_posts'));
        }

        if (!get_option('mvx_flushed_rewrite_rules')) {
            flush_rewrite_rules();
            update_option('mvx_flushed_rewrite_rules', true);
        }
    }

    /**
     * Init query vars by loading options.
     */
    public function init_mvx_query_vars() {
        // Query vars to add to WP.
        $this->mvx_query_vars = apply_filters('mvx_endpoints_query_vars', array(
            'vendor-announcements' => array(
                'label' => __('Vendor Announcements', 'dc-woocommerce-multi-vendor'),
                'endpoint' => get_mvx_vendor_settings('mvx_vendor_announcements_endpoint', 'vendor', 'general', 'vendor-announcements')
            )
            , 'profile' => array(
                'label' => __('Profile management', 'dc-woocommerce-multi-vendor'),
                'endpoint' => get_mvx_vendor_settings('mvx_profile_endpoint', 'vendor', 'general', 'profile'),
                'icon' => 'mvx-font ico-user-icon'
            )
            , 'storefront' => array(
                'label' => __('Storefront', 'dc-woocommerce-multi-vendor'),
                'endpoint' => get_mvx_vendor_settings('mvx_store_settings_endpoint', 'vendor', 'general', 'storefront')
            )
            , 'vendor-billing' => array(
                'label' => __('Vendor Billing', 'dc-woocommerce-multi-vendor'),
                'endpoint' => get_mvx_vendor_settings('mvx_vendor_billing_endpoint', 'vendor', 'general', 'vendor-billing')
            )
            , 'vendor-policies' => array(
                'label' => __('Vendor Policies', 'dc-woocommerce-multi-vendor'),
                'endpoint' => get_mvx_vendor_settings('mvx_vendor_policies_endpoint', 'vendor', 'general', 'vendor-policies')
            )
            , 'vendor-shipping' => array(
                'label' => __('Vendor Shipping', 'dc-woocommerce-multi-vendor'),
                'endpoint' => get_mvx_vendor_settings('mvx_vendor_shipping_endpoint', 'vendor', 'general', 'vendor-shipping')
            )
            , 'vendor-report' => array(
                'label' => __('Vendor Report', 'dc-woocommerce-multi-vendor'),
                'endpoint' => get_mvx_vendor_settings('mvx_vendor_report_endpoint', 'vendor', 'general', 'vendor-report')
            )
            , 'banking-overview' => array(
                'label' => __('Banking Overview', 'dc-woocommerce-multi-vendor'),
                'endpoint' => get_mvx_vendor_settings('mvx_vendor_banking_overview_endpoint', 'vendor', 'general', 'banking-overview')
            )
            , 'add-product' => array(
                'label' => __('Add Product', 'dc-woocommerce-multi-vendor'),
                'endpoint' => get_mvx_vendor_settings('mvx_add_product_endpoint', 'vendor', 'general', 'add-product')
            )
            , 'edit-product' => array(
                'label' => __('Edit Product', 'dc-woocommerce-multi-vendor'),
                'endpoint' => get_mvx_vendor_settings('mvx_edit_product_endpoint', 'vendor', 'general', 'edit-product')
            )
            , 'products' => array(
                'label' => __('Products', 'dc-woocommerce-multi-vendor'),
                'endpoint' => get_mvx_vendor_settings('mvx_products_endpoint', 'vendor', 'general', 'products')
            )
            , 'add-coupon' => array(
                'label' => __('Add Coupon', 'dc-woocommerce-multi-vendor'),
                'endpoint' => get_mvx_vendor_settings('mvx_add_coupon_endpoint', 'vendor', 'general', 'add-coupon')
            )
            , 'coupons' => array(
                'label' => __('Coupons', 'dc-woocommerce-multi-vendor'),
                'endpoint' => get_mvx_vendor_settings('mvx_coupons_endpoint', 'vendor', 'general', 'coupons')
            )
            , 'vendor-orders' => array(
                'label' => __('Vendor Orders', 'dc-woocommerce-multi-vendor'),
                'endpoint' => get_mvx_vendor_settings('mvx_vendor_orders_endpoint', 'vendor', 'general', 'vendor-orders')
            )
            , 'vendor-withdrawal' => array(
                'label' => __('Vendor Withdrawals', 'dc-woocommerce-multi-vendor'),
                'endpoint' => get_mvx_vendor_settings('mvx_vendor_withdrawal_endpoint', 'vendor', 'general', 'vendor-withdrawal')
            )
            , 'transaction-details' => array(
                'label' => __('Transaction Details', 'dc-woocommerce-multi-vendor'),
                'endpoint' => get_mvx_vendor_settings('mvx_transaction_details_endpoint', 'vendor', 'general', 'transaction-details')
            )
            , 'vendor-knowledgebase' => array(
                'label' => __('Vendor Knowledgebase', 'dc-woocommerce-multi-vendor'),
                'endpoint' => get_mvx_vendor_settings('mvx_vendor_knowledgebase_endpoint', 'vendor', 'general', 'vendor-knowledgebase')
            )
            , 'vendor-tools' => array(
                'label' => __('Vendor Tools', 'dc-woocommerce-multi-vendor'),
                'endpoint' => get_mvx_vendor_settings('mvx_vendor_tools_endpoint', 'vendor', 'general', 'vendor-tools')
            )
            , 'products-qna' => array(
                'label' => __('Vendor Products Q&As', 'dc-woocommerce-multi-vendor'),
                'endpoint' => get_mvx_vendor_settings('mvx_vendor_products_qnas_endpoint', 'vendor', 'general', 'products-qna')
            )
            , 'rejected-vendor-reapply' => array(
                'label' => __('Resubmit Application', 'dc-woocommerce-multi-vendor'),
                'endpoint' => 'rejected-vendor-reapply'
            )
        ));
    }

    /**
     * Endpoint mask describing the places the endpoint should be added.
     *
     * @since 2.6.2
     * @return int
     */
    protected function get_mvx_endpoints_mask() {
        if ('page' === get_option('show_on_front')) {
            $page_on_front = get_option('page_on_front');
            if ($page_on_front == mvx_vendor_dashboard_page_id()) {
                return EP_ROOT | EP_PAGES;
            }
        }

        return EP_PAGES;
    }

    /**
     * Add endpoints for query vars.
     */
    public function add_mvx_endpoints() {
        $this->init_mvx_query_vars();
        $mask = $this->get_mvx_endpoints_mask();
        foreach ($this->mvx_query_vars as $key => $var) {
            if (!empty($var['endpoint'])) {
                add_rewrite_endpoint($var['endpoint'], $mask);
            }
        }
    }

    /**
     * Add query vars.
     *
     * @access public
     * @param array $vars
     * @return array
     */
    public function add_mvx_query_vars($vars) {
        foreach ($this->mvx_query_vars as $key => $var) {
            $vars[] = $key;
        }
        return $vars;
    }

    /**
     * Parse the request and look for query vars - endpoints may not be supported.
     */
    public function mvx_parse_request() {
        global $wp;
        // Map query vars to their keys, or get them if endpoints are not supported
        foreach ($this->mvx_query_vars as $key => $var) {
            if (isset($_GET[$var['endpoint']])) {
                $wp->query_vars[$key] = $_GET[$var['endpoint']];
            } elseif (isset($wp->query_vars[$var['endpoint']])) {
                $wp->query_vars[$key] = $wp->query_vars[$var['endpoint']];
            }
        }
    }

    /**
     * Fix Vendor dashboard end points on home page
     * @param Object $q
     */
    public function mvx_pre_get_posts($q) {
        // Fix for endpoints on the homepage
        if ($q->is_home() && 'page' === get_option('show_on_front') && absint(get_option('page_on_front')) !== absint($q->get('page_id'))) {
            $_query = wp_parse_args($q->query);
            if (!empty($_query) && array_intersect(array_keys($_query), array_keys($this->mvx_query_vars))) {
                $q->is_page = true;
                $q->is_home = false;
                $q->is_singular = true;
                $q->set('page_id', (int) get_option('page_on_front'));
                add_filter('redirect_canonical', '__return_false');
            }
        }
    }

    public function get_current_endpoint() {
        global $wp;
        foreach ($this->mvx_query_vars as $key => $value) {
            if (isset($wp->query_vars[$key])) {
                return $key;
            }
        }
        return '';
    }
    
    public function get_current_endpoint_var() {
        global $wp;
        $endpoint_var = NULL;
        $current_endpoint = $this->get_current_endpoint();
        if(isset($wp->query_vars[$current_endpoint]) && !empty($wp->query_vars[$current_endpoint])){
            $endpoint_var = $wp->query_vars[$current_endpoint];
        }
        return $endpoint_var;
    }

}
