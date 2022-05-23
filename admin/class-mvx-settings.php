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

       /* add_menu_page(
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

        add_submenu_page( 'mvx', __( 'Settings', 'dc-woocommerce-multi-vendor' ), __( 'Settings', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'general-settings', array( $this, 'mvx_general_settings_callback' ) );

        add_submenu_page( 'mvx', __( 'Payment Configuration', 'dc-woocommerce-multi-vendor' ), __( 'Payment Configuration', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'payment-configuration', array( $this, 'mvx_payment_configureation_callback' ) );

        add_submenu_page( 'mvx', __( 'Advanced Marketplce Settings', 'dc-woocommerce-multi-vendor' ), __( 'Advanced Marketplce Settings', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'advance-marketplace-settings', array( $this, 'mvx_advanced_marketplce_settings_callback' ) );

        add_submenu_page( 'mvx', __( 'Marketplace Analytics', 'dc-woocommerce-multi-vendor' ), __( 'Marketplace Analytics', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'marketplace-analytics-settings', array( $this, 'mvx_marketplce_analytics_settings_callback' ) );

        add_submenu_page( 'mvx', __( 'Vendors', 'dc-woocommerce-multi-vendor' ), __( 'Vendors', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'vendors', array( $this, 'mvx_vendors' ) );
        // transaction details page
        add_submenu_page( null, __( 'Transaction Details', 'dc-woocommerce-multi-vendor' ), __( 'Transaction Details', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'mvx-transaction-details', array( $this, 'mvx_transaction_details' ) );

        add_submenu_page( 'mvx', __( 'Work Board', 'dc-woocommerce-multi-vendor' ), __( 'Work Board', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'work_board', array( $this, 'mvx_workboard' ) );

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
*/
        /* sort mvx submenu */
        /*if ( isset( $submenu['mvx'] ) ) {
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
        }*/







        global $submenu;
        $slug = 'mvx';
        $dashboard = add_menu_page( __( 'MultiVendorX', 'dc-woocommerce-multi-vendor' ), __( 'MultiVendorX', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', $slug, [ $this, 'mvx_modules_callback' ],  'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 auto 20"><g fill="#9EA3A8" fill-rule="nonzero"><path class="cls-1" d="M8.64,0h-1A2.25,2.25,0,0,0,5.37,2.24v.93l.39.09v-1A1.86,1.86,0,0,1,7.62.38h1A1.86,1.86,0,0,1,10.5,2.24V3.63l.39,0V2.24A2.25,2.25,0,0,0,8.64,0Z" transform="translate(-1.14)"/><path class="cls-1" d="M13.44,3.54c-7.37,1.89-10.46-2-12,.39-.86,1.34.2,6.09,1,10.4A10.21,10.21,0,0,0,8,16a10.06,10.06,0,0,0,5.8-1.82C14.91,9.87,15.63,3,13.44,3.54ZM5.38,8.26V7.9H5.9v.53H5.38ZM4.5,7.08h.22V7.3H4.5Zm-1.42.31h.48v.48H3.08ZM4,8.63H3.47V8.12H4Zm.06-.74H3.79V7.63h.26Zm.2-.59H3.89V6.94h.36Zm.09.24h.55V8.1H4.34Zm7.07,5.54H10.9v-.51h.51Zm-.25-.78v-.25h.25v.25Zm-1.82-.91,1.12,1.32H10v.76h.77V13l.63.75H9.45L8.2,12.38,7,13.78H5l2-2.39L5.32,9.34V8.81H4.87l-.41-.48h.71v.3h.94v-.3h.23L8.2,10.41l2.23-2.55a1.81,1.81,0,0,1,1.34-.61h1.15Zm1.15,1V12h.4v.4Zm.08.5v.42h-.43v-.42ZM4.68,9.45V9h.43v.42Z" transform="translate(-1.14)"/></g></svg>' ), 50 );

        if ( current_user_can( 'manage_woocommerce' ) ) {

            $submenu[ $slug ][] = [ __( 'Dashboard', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'admin.php?page=' . $slug . '#&submenu=dashboard' ];


            $submenu[ $slug ][] = [ __( 'Work Board', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'admin.php?page=' . $slug . '#&submenu=work-board&name=activity-reminder' ];

            $submenu[ $slug ][] = [ __( 'Modules', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'admin.php?page=' . $slug . '#&submenu=modules' ];

            $submenu[ $slug ][] = [ __( 'Vendors', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'admin.php?page=' . $slug . '#&submenu=vendor' ];
            
            $submenu[ $slug ][] = [ __( 'Payments', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'admin.php?page=' . $slug . '#&submenu=payment&name=payment-masspay' ];


            $submenu[ $slug ][] = [ __( 'Commission', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'admin.php?page=' . $slug . '#&submenu=commission' ];



            $submenu[ $slug ][] = [ __( 'Settings', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'admin.php?page=' . $slug . '#&submenu=settings&name=settings-general' ];

            //$submenu[ $slug ][] = [ __( 'Pro Module Settings', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'admin.php?page=' . $slug . '#&submenu=advance&name=buddypress' ];

            $submenu[ $slug ][] = [ __( 'Analytics', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'admin.php?page=' . $slug . '#&submenu=analytics&name=admin-overview' ];
            
            $submenu[ $slug ][] = [ __( 'Status and Tools', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'admin.php?page=' . $slug . '#&submenu=status-tools&name=database-tools' ];

            $submenu[ $slug ][] = [ __( 'Help & Support', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'https://multivendorx.com/' ];

            add_submenu_page( null, __( 'Transaction Details', 'dc-woocommerce-multi-vendor' ), __( 'Transaction Details', 'dc-woocommerce-multi-vendor' ), 'manage_woocommerce', 'mvx-transaction-details', array( $this, 'mvx_transaction_details' ) );

        }

    }

    public function mvx_modules_callback() {
        echo '<div id="mvx-admin-dashboard"></div>';
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
