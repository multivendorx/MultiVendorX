<?php
namespace MultiVendorX;


class Admin{
    /**
     * Admin page submenu Payments page constructor function
     */
    public function __construct()
    {
        add_action( 'admin_menu', [ $this, 'add_multivendorx_menu' ]);
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_multivendorx_script' ]);
    }

    /**
     * Add menu in admin panel
     */
    public function add_multivendorx_menu(){
        add_menu_page(
            'MultiVendorX',
            'MultiVendorX',
            'manage_woocommerce',
            'multivendorx',
            [ $this, 'menu_page_callback' ],
            "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMCAyMCI+PGcgZmlsbD0iI2E3YWFhZCIgZmlsbC1ydWxlPSJub256ZXJvIj48cGF0aCBkPSJNMTAuOCwwSDkuNUM4LDAsNi43LDEuMyw2LjcsMi44VjRDNi45LDQsNyw0LDcuMiw0LjFWMi44YzAtMS4zLDEtMi4zLDIuMy0yLjNoMS4zCiAgICAgICAgYzEuMywwLDIuMywxLDIuMywyLjN2MS43YzAuMiwwLDAuMywwLDAuNSwwVjIuOEMxMy42LDEuMywxMi4zLDAsMTAuOCwweiIvPjxwYXRoIGQ9Ik0xNi44LDQuNEM3LjYsNi44LDMuNywxLjksMS44LDQuOWMtMS4xLDEuNywwLjMsNy42LDEuMiwxM2MyLDEuMyw0LjQsMi4xLDcsMi4xCiAgICAgICAgYzIuNywwLDUuMi0wLjgsNy4zLTIuM0MxOC42LDEyLjMsMTkuNSwzLjcsMTYuOCw0LjR6IE02LjcsMTAuM1Y5LjloMC43djAuNHYwLjJINi43VjEwLjN6IE01LjYsOC45aDAuM3YwLjNINS42VjguOXogTTMuOSw5LjJoMC42CiAgICAgICAgdjAuNkgzLjlWOS4yeiBNNSwxMC44SDQuM3YtMC42SDVWMTAuOHogTTUuMSw5LjlINC43VjkuNWgwLjNWOS45eiBNNS4zLDkuMUg0LjlWOC43aDAuNVY5LjF6IE01LjQsOS40aDAuN3YwLjdINS40VjkuNHoKICAgICAgICAgTTE0LjMsMTYuM2gtMC42di0wLjZoMC42VjE2LjN6IE0xMy45LDE1LjR2LTAuM2gwLjN2MC4zSDEzLjl6IE0xMS43LDE0LjJsMS40LDEuNmgtMC42djFoMXYtMC42bDAuOCwwLjloLTIuNGwtMS42LTEuN2wtMS42LDEuNwogICAgICAgIEg2LjNsMi41LTNsLTIuMi0yLjZWMTFINi4xbC0wLjUtMC42aDAuOXYwLjRoMS4ydi0wLjRoMC4zbDIuMywyLjZMMTMsOS44YzAuNC0wLjUsMS0wLjgsMS43LTAuOGgxLjRMMTEuNywxNC4yeiBNMTMuMSwxNS41VjE1aDAuNQogICAgICAgIHYwLjVIMTMuMXogTTEzLjIsMTYuMXYwLjVoLTAuNXYtMC41SDEzLjJ6IE01LjksMTEuOHYtMC41aDAuMmgwLjN2MC4zdjAuMkg1Ljl6Ii8+PC9nPjwvc3ZnPg==",
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
            __( 'Vendors', 'multivendorx' ),
            __( 'Vendors', 'multivendorx' ),
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
            'multivendorx#&tab=settings&subtab=settings-general',
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
        echo '<div id="multivendorx-admin-dashboard"></div>';
    }

    
    /**
     * Enque javascript and css
     * @return void
     */

     public function enqueue_multivendorx_script() {
        if ( get_current_screen()->id !== 'toplevel_page_multivendorx' ) return ;

        // Support for media
        wp_enqueue_media();

        // Enque script and style
        wp_enqueue_style('multivendorx_admin_css', MultiVendorX()->plugin_url . 'build/index.css');
        wp_enqueue_script('multivendorx_admin_script', MultiVendorX()->plugin_url. 'build/index.js', [ 'wp-element' ], '1.0.0', true);

        // Preapere page list. Will move to utility function. !!!!!!!!
        $page_list = [];
        $pages = get_pages();
        $woocommerce_pages = array(wc_get_page_id( 'shop' ), wc_get_page_id( 'cart' ), wc_get_page_id( 'checkout' ), wc_get_page_id( 'myaccount' ));
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
        $tabs_names     = [ 'settings-general', 'new-vendor-registration-form' ,'seller-dashboard', 'store', 'disbursement', 'commissions', 'spmv-pages', 'products-capability', 'order', 'products', 'settings-min-max', 'social', 'settings-identity-verification', 'settings-vendor-invoice', 'settings-advertising', 'settings-wholesale', 'settings-store-support', 'settings-store-inventory', 'settings-live-chat', 'refund-management', 'policy', 'review-management', 'payment-masspay'];
        foreach ( $tabs_names as $tab_name ) {
            $settings_value[ $tab_name ] = MultiVendorX()->setting->get_option( 'multivendorx-' . $tab_name . '-settings' );
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
        $plugins_list = MultiVendorX()->setting->get_option('active_plugins');
        foreach ($plugins_list as $item) {
            $parts = explode("/", $item); // Split string by "/"
            $key = strtolower($parts[0]); // Convert to lowercase
            $active_plugins_list[$key] = true; // Assign true as value
        }

        // error_log("Sttings Database value : ".print_r($settings_value,true));

        wp_localize_script( 'multivendorx_admin_script', 'appLocalizer', apply_filters('mvx_module_complete_settings', [
            // Required in new code
            'apiUrl'        => untrailingslashit(get_rest_url()),
            'restUrl'       => MultiVendorX()->rest_namespace,
            'nonce'         => wp_create_nonce( 'wp_rest' ),
            'pageList'      => $page_list,
            'settings_databases_value'  => $settings_value,
            'pro_settings_list'=> '',
            'active_plugins_list'=>$active_plugins_list,
            'template1'     => MultiVendorX()->plugin_url . 'src/assets/images/template1.jpg',
            'template2'     => MultiVendorX()->plugin_url  . 'src/assets/images/template2.jpg',
            'template3'     => MultiVendorX()->plugin_url  . 'src/assets/images/template3.jpg',
            'admin_widget_url'=> admin_url( 'widgets.php' ),
            'modules_page_url'=>admin_url( '?page=multivendorx#&tab=modules' ),
            'order_statuses'=>$order_statuses,
            'open_uploader'            =>  'Upload Image',
            'woocommerce_currency'=> get_woocommerce_currency(),
            'is_SitePress_active' => class_exists( 'SitePress' ),
            'is_ACF_active' => class_exists( 'ACF' ),
            'is_mvx_pro_active' => is_plugin_active('mvx-pro/mvx-pro.php'),
        ] ) );

     }
}
