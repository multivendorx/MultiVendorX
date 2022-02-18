<?php

class MVX_Settings {

    /**
     * Start up
     */
    public function __construct() {
        // Admin menu
        add_action( 'admin_menu', array( $this, 'add_settings_page' ), 100 );
    }

    /**
     * Add options page
     */
    public function add_settings_page() {
        global $MVX, $submenu;

        add_menu_page(
            __( 'MVX', 'dc-woocommerce-multi-vendor' )
            , __( 'MVX', 'dc-woocommerce-multi-vendor' )
            , 'manage_woocommerce'
            , 'mvx'
            , null
            , $MVX->plugin_url . 'assets/images/dualcube.png'
            , 45
        );
        add_submenu_page( 'mvx', __( 'Dashboard', 'dc-woocommerce-multi-vendor' ), __( 'Dashboard', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'modules', array( $this, 'mvx_modules_callback' ) );

        add_submenu_page( 'mvx', __( 'Commission', 'dc-woocommerce-multi-vendor' ), __( 'Commission', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'commission', array( $this, 'mvx_commission_callback' ) );

        add_submenu_page( 'mvx', __( 'Marketplace Manager', 'dc-woocommerce-multi-vendor' ), __( 'Marketplace Manager', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'marketplace-manager-settings', array( $this, 'mvx_marketplce_manager_settings_callback' ) );

        add_submenu_page( 'mvx', __( 'General Settings', 'dc-woocommerce-multi-vendor' ), __( 'General Settings', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'general-settings', array( $this, 'mvx_general_settings_callback' ) );

        add_submenu_page( 'mvx', __( 'Payment Configuration', 'dc-woocommerce-multi-vendor' ), __( 'Payment Configuration', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'payment-configuration', array( $this, 'mvx_payment_configureation_callback' ) );

        add_submenu_page( 'mvx', __( 'Advanced Marketplce Settings', 'dc-woocommerce-multi-vendor' ), __( 'Advanced Marketplce Settings', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'advance-marketplace-settings', array( $this, 'mvx_advanced_marketplce_settings_callback' ) );

        add_submenu_page( 'mvx', __( 'Marketplace Analytics', 'dc-woocommerce-multi-vendor' ), __( 'Marketplace Analytics', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'marketplace-analytics-settings', array( $this, 'mvx_marketplce_analytics_settings_callback' ) );

        add_submenu_page( 'mvx', __( 'Vendors', 'dc-woocommerce-multi-vendor' ), __( 'Vendors', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'vendors', array( $this, 'mvx_vendors' ) );
        // transaction details page
        add_submenu_page( null, __( 'Transaction Details', 'dc-woocommerce-multi-vendor' ), __( 'Transaction Details', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'mvx-transaction-details', array( $this, 'mvx_transaction_details' ) );

        // Assign priority incrmented by 1
        $mvx_submenu_priority = array(
            'commission' => 2,
            'advance-marketplace-settings' => 6,
            'vendors' => 1,
            'marketplace-manager-settings' => 3,
            'marketplace-analytics-settings' => 7,
            'payment-configuration' => 5,
            //'mvx-extensions' => 8,
            'modules' => 0,
            'general-settings' => 4
		);

        /* sort mvx submenu */
        if ( isset( $submenu['mvx'] ) ) {
        	$mvx_submenu_priority = apply_filters( 'mvx_submenu_items', $mvx_submenu_priority, $submenu['mvx'] );
        	$submenu_mvx_sort = array();
        	$submenu_mvx_sort_duplicates = array();
        	foreach($submenu['mvx'] as $menu_items) {
        		if (isset($mvx_submenu_priority[$menu_items[2]]) && ($mvx_submenu_priority[$menu_items[2]] >= 0) && !isset($submenu_mvx_sort[$mvx_submenu_priority[$menu_items[2]]])) $submenu_mvx_sort[$mvx_submenu_priority[$menu_items[2]]] = $menu_items;
				else $submenu_mvx_sort_duplicates[] = $menu_items;
        	}
        	
        	ksort($submenu_mvx_sort);
        	
        	$submenu_mvx_sort = array_merge($submenu_mvx_sort, $submenu_mvx_sort_duplicates);
            //print_r($submenu_mvx_sort);die;
            unset($submenu_mvx_sort[8]);
        	$submenu['mvx'] = $submenu_mvx_sort;
        }
    }

    public function mvx_modules_callback() {
        echo '<div id="mvx-modules-admin-dashboard-display"></div>';
    }

    public function mvx_commission_callback() {
        echo '<div id="mvx-modules-admin-commission-display"></div>';
    }

    public function mvx_payment_configureation_callback() {
        echo '<div id="mvx-modules-admin-payment-display"></div>';
    }

    public function mvx_advanced_marketplce_settings_callback() {
        echo '<div id="mvx-modules-admin-advance-display"></div>';
    }

    public function mvx_marketplce_analytics_settings_callback() {
        echo '<div id="mvx-modules-admin-analytics-display"></div>';
    }

    public function mvx_marketplce_manager_settings_callback() {
        echo '<div id="mvx-modules-admin-manager-display"></div>';
    }

    public function mvx_general_settings_callback() {
        echo '<div id="mvx-modules-admin-dashboard-general-settings"></div>';
    }

    public function mvx_vendors() {
        echo '<div id="mvx-vendor-section"></div>';
    }

    public function mvx_transaction_details() {
        global $MVX;
        ?>
        <div class="wrap blank-wrap"><h3><?php esc_html_e( 'Transaction Details', 'dc-woocommerce-multi-vendor' ); ?></h3></div>
        <div class="wrap mvx-settings-wrap panel-body">
            <?php
            $_is_trans_details_page = isset( $_REQUEST['page'] ) ? wc_clean($_REQUEST['page']) : '';
            $trans_id = isset( $_REQUEST['trans_id'] ) ? absint( $_REQUEST['trans_id'] ) : 0;
            if ( $_is_trans_details_page == 'mvx-transaction-details' && $trans_id != 0 ) {
                $transaction = get_post( $trans_id );
                if ( isset( $transaction->post_type ) && $transaction->post_type == 'mvx_transaction' ) {
                    $vendor = get_mvx_vendor_by_term( $transaction->post_author ) ? get_mvx_vendor_by_term( $transaction->post_author ) : get_mvx_vendor( $transaction->post_author );
                    $commission_details = $MVX->transaction->get_transaction_item_details( $trans_id );
                    ?>
                    <table class="widefat fixed striped">
                        <?php
                        if ( ! empty( $commission_details['header'] ) ) {
                            echo '<thead><tr>';
                            foreach ( $commission_details['header'] as $header_val ) {
                                echo '<th>' . $header_val . '</th>';
                            }
                            echo '</tr></thead>';
                        }
                        echo '<tbody>';
                        if ( ! empty( $commission_details['body'] ) ) {

                            foreach ( $commission_details['body'] as $commission_detail ) {
                                echo '<tr>';
                                foreach ( $commission_detail as $details ) {
                                    foreach ( $details as $detail_key => $detail ) {
                                        echo '<td>' . $detail . '</td>';
                                    }
                                }
                                echo '</tr>';
                            }
                        }
                        if ( $totals = $MVX->transaction->get_transaction_item_totals( $trans_id, $vendor ) ) {
                            foreach ( $totals as $total ) {
                                echo '<tr><td colspan="3" >' . $total['label'] . '</td><td>' . $total['value'] . '</td></tr>';
                            }
                        }
                        echo '</tbody>';
                        ?>
                    </table>
                <?php } else { ?>
                    <p class="mvx_headding3"><?php echo __( 'Unfortunately transaction details are not found. You may try again later.', 'dc-woocommerce-multi-vendor' ); ?></p>
                <?php }
            } else {
                ?>
                <p class="mvx_headding3"><?php echo __( 'Unfortunately transaction details are not found. You may try again later.', 'dc-woocommerce-multi-vendor' ); ?></p> 
            <?php } ?>
        </div>
        <?php
    }

    
}
