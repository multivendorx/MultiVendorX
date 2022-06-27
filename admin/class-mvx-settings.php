<?php

class MVX_Settings {

    /**
     * Start up
     */
    public function __construct() {
        // Admin menu
        add_action( 'admin_menu', array( $this, 'add_settings_page' ), 100 );
        add_filter( 'admin_body_class', array( $this, 'mvx_add_loading_classes' ) );
    }

    /**
     * Add options page
     */
    public function add_settings_page() {
        global $submenu;
        $slug = 'mvx';
        $dashboard = add_menu_page( __( 'MultiVendorX', 'multivendorx' ), __( 'MultiVendorX', 'multivendorx' ), 'manage_woocommerce', $slug, [ $this, 'mvx_modules_callback' ],  'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 auto 20"><g fill="#9EA3A8" fill-rule="nonzero"><path class="cls-1" d="M8.64,0h-1A2.25,2.25,0,0,0,5.37,2.24v.93l.39.09v-1A1.86,1.86,0,0,1,7.62.38h1A1.86,1.86,0,0,1,10.5,2.24V3.63l.39,0V2.24A2.25,2.25,0,0,0,8.64,0Z" transform="translate(-1.14)"/><path class="cls-1" d="M13.44,3.54c-7.37,1.89-10.46-2-12,.39-.86,1.34.2,6.09,1,10.4A10.21,10.21,0,0,0,8,16a10.06,10.06,0,0,0,5.8-1.82C14.91,9.87,15.63,3,13.44,3.54ZM5.38,8.26V7.9H5.9v.53H5.38ZM4.5,7.08h.22V7.3H4.5Zm-1.42.31h.48v.48H3.08ZM4,8.63H3.47V8.12H4Zm.06-.74H3.79V7.63h.26Zm.2-.59H3.89V6.94h.36Zm.09.24h.55V8.1H4.34Zm7.07,5.54H10.9v-.51h.51Zm-.25-.78v-.25h.25v.25Zm-1.82-.91,1.12,1.32H10v.76h.77V13l.63.75H9.45L8.2,12.38,7,13.78H5l2-2.39L5.32,9.34V8.81H4.87l-.41-.48h.71v.3h.94v-.3h.23L8.2,10.41l2.23-2.55a1.81,1.81,0,0,1,1.34-.61h1.15Zm1.15,1V12h.4v.4Zm.08.5v.42h-.43v-.42ZM4.68,9.45V9h.43v.42Z" transform="translate(-1.14)"/></g></svg>' ), 50 );

        if ( current_user_can( 'manage_woocommerce' ) ) {

            $submenu[ $slug ][] = [ __( 'Dashboard', 'multivendorx' ), 'manage_woocommerce', 'admin.php?page=' . $slug . '#&submenu=dashboard' ];

            $submenu[ $slug ][] = [ __( 'Work Board', 'multivendorx' ), 'manage_woocommerce', 'admin.php?page=' . $slug . '#&submenu=work-board&name=activity-reminder' ];

            $submenu[ $slug ][] = [ __( 'Modules', 'multivendorx' ), 'manage_woocommerce', 'admin.php?page=' . $slug . '#&submenu=modules' ];

            $submenu[ $slug ][] = [ __( 'Vendors', 'multivendorx' ), 'manage_woocommerce', 'admin.php?page=' . $slug . '#&submenu=vendor' ];
            
            $submenu[ $slug ][] = [ __( 'Payments', 'multivendorx' ), 'manage_woocommerce', 'admin.php?page=' . $slug . '#&submenu=payment&name=payment-masspay' ];

            $submenu[ $slug ][] = [ __( 'Commission', 'multivendorx' ), 'manage_woocommerce', 'admin.php?page=' . $slug . '#&submenu=commission' ];

            $submenu[ $slug ][] = [ __( 'Settings', 'multivendorx' ), 'manage_woocommerce', 'admin.php?page=' . $slug . '#&submenu=settings&name=settings-general' ];

            $submenu[ $slug ][] = [ __( 'Analytics', 'multivendorx' ), 'manage_woocommerce', 'admin.php?page=' . $slug . '#&submenu=analytics&name=admin-overview' ];
            
            $submenu[ $slug ][] = [ __( 'Status and Tools', 'multivendorx' ), 'manage_woocommerce', 'admin.php?page=' . $slug . '#&submenu=status-tools&name=database-tools' ];

            $submenu[ $slug ][] = [ __( 'Help & Support', 'multivendorx' ), 'manage_woocommerce', 'https://multivendorx.com/' ];

            add_submenu_page( null, __( 'Transaction Details', 'multivendorx' ), __( 'Transaction Details', 'multivendorx' ), 'manage_woocommerce', 'mvx-transaction-details', array( $this, 'mvx_transaction_details' ) );
        }
    }

    public function mvx_add_loading_classes( $classes ) {
        $screen = get_current_screen();
        $page_details = array('toplevel_page_mvx');
        if ( in_array($screen->id, $page_details)) {
            $classes .= ' mvx-page';
        }
        return $classes;
    }

    public function mvx_modules_callback() {
        echo '<div id="mvx-admin-dashboard"></div>';
    }

    public function mvx_transaction_details() {
        global $MVX;
        ?>
        <div class="wrap blank-wrap"><h3><?php esc_html_e( 'Transaction Details', 'multivendorx' ); ?></h3></div>
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
                    <p class="mvx-headding-wrapper"><?php echo __( 'Unfortunately transaction details are not found. You may try again later.', 'multivendorx' ); ?></p>
                <?php }
            } else {
                ?>
                <p class="mvx-headding-wrapper"><?php echo __( 'Unfortunately transaction details are not found. You may try again later.', 'multivendorx' ); ?></p> 
            <?php } ?>
        </div>
        <?php
    }
    
}
