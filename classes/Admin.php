<?php
namespace MultiVendorX;


class Admin{
    /**
     * Admin page submenu Payments page constructor function
     */
    public function __construct()
    {
        add_action('admin_menu',[$this,'add_menu']);
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_script' ]);
    }

    /**
     * Add menu in admin panel
     */
    public function add_menu(){
        add_menu_page(
            'MultiVendorX',
            'MultiVendorX',
            'manage_woocommerce',
            'multivendorx',
            [$this,'menu_page_callback'],
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><g fill="#a7aaad" fill-rule="nonzero"><path d="M10.8,0H9.5C8,0,6.7,1.3,6.7,2.8V4C6.9,4,7,4,7.2,4.1V2.8c0-1.3,1-2.3,2.3-2.3h1.3
        c1.3,0,2.3,1,2.3,2.3v1.7c0.2,0,0.3,0,0.5,0V2.8C13.6,1.3,12.3,0,10.8,0z"/><path d="M16.8,4.4C7.6,6.8,3.7,1.9,1.8,4.9c-1.1,1.7,0.3,7.6,1.2,13c2,1.3,4.4,2.1,7,2.1
        c2.7,0,5.2-0.8,7.3-2.3C18.6,12.3,19.5,3.7,16.8,4.4z M6.7,10.3V9.9h0.7v0.4v0.2H6.7V10.3z M5.6,8.9h0.3v0.3H5.6V8.9z M3.9,9.2h0.6
        v0.6H3.9V9.2z M5,10.8H4.3v-0.6H5V10.8z M5.1,9.9H4.7V9.5h0.3V9.9z M5.3,9.1H4.9V8.7h0.5V9.1z M5.4,9.4h0.7v0.7H5.4V9.4z
         M14.3,16.3h-0.6v-0.6h0.6V16.3z M13.9,15.4v-0.3h0.3v0.3H13.9z M11.7,14.2l1.4,1.6h-0.6v1h1v-0.6l0.8,0.9h-2.4l-1.6-1.7l-1.6,1.7
        H6.3l2.5-3l-2.2-2.6V11H6.1l-0.5-0.6h0.9v0.4h1.2v-0.4h0.3l2.3,2.6L13,9.8c0.4-0.5,1-0.8,1.7-0.8h1.4L11.7,14.2z M13.1,15.5V15h0.5
        v0.5H13.1z M13.2,16.1v0.5h-0.5v-0.5H13.2z M5.9,11.8v-0.5h0.2h0.3v0.3v0.2H5.9z"/></g></svg>'),
            50
        );

        add_submenu_page(
            'multivendorx',
            __( 'Dashboard', 'multivendorx' ),
            __( 'Dashboard', 'multivendorx' ),
            'manage_woocommerce',
            'multivendorx#&tab=dashboard',
            '__return_null'
        );

        add_submenu_page(
            'multivendorx',
            __( 'Work Board', 'multivendorx' ),
            __( 'Work Board', 'multivendorx' ),
            'manage_woocommerce',
            'multivendorx#&tab=work-board&subtab=activity-reminder',
            '__return_null'
        );

        add_submenu_page(
            'multivendorx',
            __( 'Modules', 'multivendorx' ),
            __( 'Modules', 'multivendorx' ),
            'manage_woocommerce',
            'multivendorx#&tab=modules',
            '__return_null'
        );

        add_submenu_page(
            'multivendorx',
            __('Vendors', 'multivendorx'),
            __('Vendors', 'multivendorx'),
            'manage_woocommerce',
            'multivendorx#&tab=vendor',
            '__return_null'
        );

        add_submenu_page(
            'multivendorx',
            __( 'Payments', 'multivendorx' ),
            __( 'Payments', 'multivendorx' ),
            'manage_woocommerce',
            'multivendorx#&tab=payment&subtab=payment-masspay',
            '__return_null'
        );

        add_submenu_page(
            'multivendorx',
            __( 'Commissions', 'multivendorx' ),
            __( 'Commissions', 'multivendorx' ),
            'manage_woocommerce',
            'multivendorx#&tab=Commissions',
            '__return_null'
        );

        add_submenu_page(
            'multivendorx',
            __( 'Settings', 'multivendorx' ),
            __( 'Settings', 'multivendorx' ),
            'manage_woocommerce',
            'multivendorx#&tab=settings&subtab=settings_general_tab',
            '__return_null'
        );

        add_submenu_page(
            'multivendorx',
            __( 'Analytics', 'multivendorx' ),
            __( 'Analytics', 'multivendorx' ),
            'manage_woocommerce',
            'multivendorx#&tab=analytics&subtab=admin-overview',
            '__return_null'
        );

        add_submenu_page(
            'multivendorx',
            __( 'Status and Tools', 'multivendorx' ),
            __( 'Status and Tools', 'multivendorx' ),
            'manage_woocommerce',
            'multivendorx#&tab=status-tools&subtab=database-tools',
            '__return_null'
        );
        add_submenu_page(
            'multivendorx',
            __( 'Advertising', 'multivendorx' ),
            __( 'Advertising', 'multivendorx' ),
            'manage_woocommerce',
            'multivendorx#&tab=advertisement',
            '__return_null'
        );
        add_submenu_page(
            'multivendorx',
            __( 'Membership', 'multivendorx' ),
            __( 'Membership', 'multivendorx' ),
            'manage_woocommerce',
            'multivendorx#&tab=membership&subtab=payment-membership-message',
            '__return_null'
        );
        
        add_submenu_page(
            'multivendorx',
            __( '<div id="help-and-support">Help & Support</div>', 'multivendorx' ),
            __( '<div id="help-and-support">Help & Support</div>', 'multivendorx' ),
            'manage_woocommerce',
            'https://multivendorx.com/'
        );
        add_submenu_page(
            'multivendorx',
            __( 'License', 'multivendorx' ),
            __( 'License', 'multivendorx' ),
            'manage_woocommerce',
            'multivendorx#&tab=pro-license',
            '__return_null'
        );

        remove_submenu_page( 'multivendorx', 'multivendorx' );

    }

    /**
     * Callback function for menu page
     * @return void
     */
    public function menu_page_callback(){
        echo '<div id="mvx-admin-dashboard"></div>';
    }

    
    /**
     * Enque javascript and css
     * @return void
     */

     public function enqueue_script() {
        if ( get_current_screen()->id !== 'toplevel_page_multivendorx' ) return ;

        // Support for media
        wp_enqueue_media();

        // Enque script and style
        wp_enqueue_style('mvx_admin_css', MVX()->plugin_url . 'build/index.css');
        wp_enqueue_script('mvx_admin_script', MVX()->plugin_url. 'build/index.js', [ 'wp-element'], '1.0.0', true);

        // Preapere page list. Will move to utility function. !!!!!!!!
        $page_list = [];
        $pages = get_pages();
        $woocommerce_pages = array(wc_get_page_id('shop'), wc_get_page_id('cart'), wc_get_page_id('checkout'), wc_get_page_id('myaccount'));
        if($pages){
            foreach ($pages as $page) {
                if (!in_array($page->ID, $woocommerce_pages)) {
                    $page_list[] = array(
                        'value'=> $page->ID,
                        'label'=> $page->post_title,
                        'key'=> $page->ID,
                    );
                }
            }
        };

        // Get all tab setting's database value
        $settings_value = [];
        $tabs_names     = [ 'settings_general_tab','new_vendor_registration_form','seller_dashboard_tab','store_tab','disbursement_tab', 'commissions_tab', 'spmv_pages_tab', 'products_capability_tab','order_tab','products_tab','settings_min_max_tab','social_tab','settings_identity_verification_tab','settings_vendor_invoice_tab','settings_advertising_tab','settings_wholesale_tab','settings_store_support_tab','settings_store_inventory_tab','settings_live_chat_tab','refund_management_tab','policy_tab','review_management_tab','payment_masspay_tab'];
        foreach ( $tabs_names as $tab_name ) {
            $settings_value[ $tab_name ] = MVX()->setting->get_option( 'mvx_' . $tab_name . '_settings' );
        }

        // MVX-PRO feature
        $available_emails = apply_filters('mvx_pdf_invoice_attachment_to_email_available', array(
            'new_order'                 => __('New order','mvx-pro'),
            'cancelled_order'           => __('Cancelled order','mvx-pro'), 
            'customer_processing_order' => __('Customer processing order','mvx-pro'),
            'customer_completed_order'  => __('Customer completed order','mvx-pro'),
            'customer_invoice'          => __('Customer invoice','mvx-pro'),
            'customer_refunded_order'   => __('Customer refunded order','mvx-pro'))
        );

        $available_emails_filtered = array();
        if (!empty($available_emails)) {
            $available_emails_filtered[] = array('key' => 'disabled', 'label' => __('Disabled', 'mvx-pro'), 'value' => 'disabled');
            foreach ($available_emails as $key => $available_email) {
                $available_emails_filtered[] = array('key' => $key, 'label' => $available_email, 'value' => $key );
            }
        }

        // Get order status
        $order_statuses = array();
        if (function_exists('wc_get_order_statuses')) {
            $wc_get_order_statuses = wc_get_order_statuses();
            if (empty($wc_get_order_statuses)) {
                $wc_get_order_statuses = array();
            }
            foreach ($wc_get_order_statuses as $key => $value) {
                $order_statuses[] = array('key' => $key, 'label' => $value, 'value' => $key );
            }
        }

        // Get active plugin lists
        $active_plugins_list = array();
        $plugins_list = MVX()->setting->get_option('active_plugins');
        foreach ($plugins_list as $item) {
            $parts = explode("/", $item); // Split string by "/"
            $key = strtolower($parts[0]); // Convert to lowercase
            $active_plugins_list[$key] = true; // Assign true as value
        }

        // error_log("Sttings Database value : ".print_r($settings_value,true));

        wp_localize_script( 'mvx_admin_script', 'appLocalizer', apply_filters('mvx_module_complete_settings', [
            // Required in new code
            'apiUrl'        => untrailingslashit(get_rest_url()),
            'restUrl'       => MVX()->rest_namespace,
            'nonce'         => wp_create_nonce( 'wp_rest' ),
            'pageList'      => $page_list,
            'settings_databases_value'  => $settings_value,
            'pro_settings_list'=> '',
            'active_plugins_list'=>$active_plugins_list,
            'template1'     => MVX()->plugin_url . 'src/assets/images/template1.jpg',
            'template2'     => MVX()->plugin_url  . 'src/assets/images/template2.jpg',
            'template3'     => MVX()->plugin_url  . 'src/assets/images/template3.jpg',
            'admin_widget_url'=> admin_url("widgets.php"),
            'modules_page_url'=>admin_url( '?page=multivendorx#&tab=modules' ),
            'available_emails_filtered'=>$available_emails_filtered ,
            'order_statuses'=>$order_statuses,
            'open_uploader'            =>  'Upload Image',
            'woocommerce_currency'=> get_woocommerce_currency(),
            'identity_verification_settings_url'=> admin_url('admin.php?page=multivendorx#&tab=settings&subtab=verification'),
            'spmv_settings_url'  => admin_url('admin.php?page=multivendorx#&tab=settings&subtab=spmv_pages'),
            'store_inventory_settings_url' => admin_url('admin.php?page=multivendorx#&tab=settings&subtab=store_inventory'),
            'min_max_settings_url' => admin_url('admin.php?page=multivendorx#&tab=settings&subtab=min_max_quantities'),
            'wc_mvx_paypal_settings_url' => admin_url('admin.php?page=wc-settings&tab=checkout&section=mvx_paypal_marketplace'),
            'wc_shipping_settings_url' => admin_url('admin.php?page=wc-settings&tab=shipping'),
            'wc_shipping_by_distance_settings_url' => admin_url('admin.php?page=wc-settings&tab=shipping&section=mvx_product_shipping_by_distance'),
            'wc_shipping_by_country_settings_url' => admin_url('admin.php?page=wc-settings&tab=shipping&section=mvx_product_shipping_by_country'),
            'vendor_invoice_settings_url' => admin_url('admin.php?page=multivendorx#&tab=settings&subtab=invoice'),
            'marketplace_refunds_settings_url' => admin_url('admin.php?page=multivendorx#&tab=settings&subtab=refunds'),
            'store_settings_url' => admin_url('admin.php?page=multivendorx#&tab=settings&subtab=store'),
            'policy_settings_url' => admin_url('admin.php?page=multivendorx#&tab=settings&subtab=policy'),
            'review_management_settings_url' => admin_url('admin.php?page=multivendorx#&tab=settings&subtab=review_management'),
            'product_advertising_settings_url' => admin_url('admin.php?page=multivendorx#&tab=settings&subtab=product_advertising'),
            'wholesale_settings_url' => admin_url('admin.php?page=multivendorx#&tab=settings&subtab=wholesale'),
            'live_chat_settings_url' => admin_url('admin.php?page=multivendorx#&tab=settings&subtab=live_chat'),
            'store_support_settings_url' => admin_url('admin.php?page=multivendorx#&tab=settings&subtab=store_support'),
            'seo_settings_url' => admin_url('admin.php?page=multivendorx#&tab=settings&subtab=seo'),
            'social_settings_url' => admin_url('admin.php?page=multivendorx#&tab=settings&subtab=social'),
            'is_SitePress_active' => class_exists( 'SitePress' ) ? true : false,
            'is_ACF_active' => class_exists( 'ACF' ) ? true : false,
            'is_mvx_pro_active' => is_plugin_active('mvx-pro/mvx-pro.php') ? true : false,
        ] ) );

     }
}
