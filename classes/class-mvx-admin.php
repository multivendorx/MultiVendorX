<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * MVX Admin Class
 *
 * @version		2.2.0
 * @package		MVX
 * @author 		Multivendor X
 */
class MVX_Admin {

    public $settings;

    public function __construct() {
        // Admin script and style
        add_action('admin_enqueue_scripts', array(&$this, 'enqueue_admin_script'), 30);
        add_action('dualcube_admin_footer', array(&$this, 'dualcube_admin_footer_for_mvx'));
        add_action('admin_bar_menu', array(&$this, 'add_toolbar_items'), 100);
        add_action('admin_head', array(&$this, 'admin_header'));
        add_action('current_screen', array($this, 'conditonal_includes'));
        if (get_mvx_vendor_settings('is_singleproductmultiseller', 'general') == 'Enable') {
            add_action('admin_enqueue_scripts', array($this, 'mvx_kill_auto_save'));
        }
        $this->load_class('settings');
        $this->settings = new MVX_Settings();
        add_filter('woocommerce_hidden_order_itemmeta', array(&$this, 'add_hidden_order_items'));

        add_action('admin_menu', array(&$this, 'mvx_admin_menu'));
        add_action('admin_head', array($this, 'mvx_submenu_count'));
        add_action('wp_dashboard_setup', array(&$this, 'mvx_remove_wp_dashboard_widget'));
        add_filter('woocommerce_order_actions', array(&$this, 'woocommerce_order_actions'));
        add_action('woocommerce_order_action_regenerate_order_commissions', array(&$this, 'regenerate_order_commissions'));
        add_action('woocommerce_order_action_regenerate_suborders', array(&$this, 'regenerate_suborders'));
        add_filter('woocommerce_screen_ids', array(&$this, 'add_mvx_screen_ids'));
        // vendor shipping capability
        add_filter('mvx_current_vendor_id', array(&$this, 'mvx_vendor_shipping_admin_capability'));
        add_filter('mvx_dashboard_shipping_vendor', array(&$this, 'mvx_vendor_shipping_admin_capability'));
        add_filter('woocommerce_menu_order_count', array(&$this, 'woocommerce_admin_end_order_menu_count'));
        
        $this->actions_handler();
    }
    
    public function actions_handler(){
        // Export pending bank transfers request in admin end
        if ( ! empty( $_POST ) && isset( $_REQUEST[ 'mvx_admin_bank_transfer_export_nonce' ] ) && wp_verify_nonce( $_REQUEST[ 'mvx_admin_bank_transfer_export_nonce' ], 'mvx_todo_pending_bank_transfer_export' ) ) {
            $transactions_ids = isset( $_POST['transactions_ids'] ) ? json_decode( wc_clean($_POST['transactions_ids'] ) ) : array();
            if( !$transactions_ids ) return false;
            // Set filename
            $date = date('Y-m-d H:i:s');
            $filename = apply_filters( 'mvx_admin_export_pending_bank_transfer_file_name', 'Admin-Pending-Bank-Transfer-' . $date, $_POST );
            $filename = $filename.'.csv';
            // Set page headers to force download of CSV
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-type: text/x-csv");
            header("Content-Disposition: File Transfar");
            //header("Content-Type: application/octet-stream");
            //header("Content-Type: application/download");
            header("Content-Disposition: attachment;filename={$filename}");
            header("Content-Transfer-Encoding: binary");
            // Set CSV headers
            $headers = apply_filters( 'mvx_admin_export_pending_bank_transfer_csv_headers',array(
                'dor'               => __( 'Date of request', 'dc-woocommerce-multi-vendor' ),
                'trans_id'          => __( 'Transaction ID', 'dc-woocommerce-multi-vendor' ),
                'commission_ids'    => __( 'Commission IDs', 'dc-woocommerce-multi-vendor' ),
                'vendor'            => __( 'Vendor', 'dc-woocommerce-multi-vendor' ),
                'amount'            => __( 'Amount', 'dc-woocommerce-multi-vendor' ),
                'bank_details'      => __( 'Bank Details', 'dc-woocommerce-multi-vendor' ),
            ), $transactions_ids, $_POST );
            $exporter_data = array();
            foreach ( $transactions_ids as $trans_id ) {
                $commission_ids = (array)get_post_meta( $trans_id, 'commission_detail', true );
                $vendor = get_mvx_vendor_by_term( get_post_field( 'post_author', $trans_id ) );
                $account_details = array();
                if ( $vendor ) :
                    $account_details = apply_filters( 'mvx_admin_export_pending_bank_transfer_vendor_account_details', array(
                        'account_name'   => get_user_meta( $vendor->id, '_vendor_account_holder_name', true ),
                        'account_number' => get_user_meta( $vendor->id, '_vendor_bank_account_number', true ),
                        'account_type'   => get_user_meta( $vendor->id, '_vendor_bank_account_type', true ),
                        'bank_name'      => get_user_meta( $vendor->id, '_vendor_bank_name', true ),
                        'iban'           => get_user_meta( $vendor->id, '_vendor_iban', true ),
                        'routing_number' => get_user_meta( $vendor->id, '_vendor_aba_routing_number', true ),
                    ), $transactions_ids, $_POST, $vendor );
                endif;
                $bank_details = '';
                if( $account_details ) {
                    foreach ( $account_details as $key => $value ) {
                        if( $key == 'account_name' ) {
                            $bank_details .= __( 'Account Holder Name', 'dc-woocommerce-multi-vendor' ) . ': ' . $value . ' | ';
                        }elseif( $key == 'account_number' ){
                            $bank_details .= __( 'Account Number', 'dc-woocommerce-multi-vendor' ) . ': ' . $value . ' | ';;
                        }elseif( $key == 'account_type' ){
                            $bank_details .= __( 'Account Type', 'dc-woocommerce-multi-vendor' ) . ': ' . $value . ' | ';
                        }elseif( $key == 'bank_name' ){
                            $bank_details .= __( 'Bank Name', 'dc-woocommerce-multi-vendor' ) . ': ' . $value . ' | ';
                        }elseif( $key == 'iban' ){
                            $bank_details .= __( 'IBAN', 'dc-woocommerce-multi-vendor' ) . ': ' . $value . ' | ';
                        }elseif( $key == 'routing_number' ){
                            $bank_details .= __( 'Routing Number', 'dc-woocommerce-multi-vendor' ) . ': ' . $value;
                        }else{
                            $bank_details .= apply_filters( 'mvx_admin_export_pending_bank_transfer_vendor_bank_details', $bank_details, $account_details, $_POST );
                        }
                    }
                }
                $amount = get_post_meta( $trans_id, 'amount', true );
                $transfer_charge = get_post_meta( $trans_id, 'transfer_charge', true );
                $gateway_charge = get_post_meta( $trans_id, 'gateway_charge', true );
                $transaction_amt = $amount - $transfer_charge - $gateway_charge;
                $exporter_data[] = apply_filters( 'mvx_admin_export_pending_bank_transfer_csv_row_data', array(
                    'date'              => get_the_date( 'Y-m-d', $trans_id ),
                    'trans_id'          => '#' . $trans_id,
                    'commission_ids'    => '#' . implode(', #', $commission_ids),
                    'vendor'            => get_user_meta( $vendor->id, '_vendor_page_title', true ),
                    'amount'            => $transaction_amt,
                    'bank_details'      => $bank_details,
                ), $transactions_ids, $_POST, $vendor );
            }
            
            // Initiate output buffer and open file
            ob_start();
            $file = fopen("php://output", 'w');

            // Add headers to file
            fputcsv( $file, $headers );
            // Add data to file
            if ( $exporter_data ) {
                foreach ( $exporter_data as $edata ) {
                    fputcsv( $file, $edata );
                }
            } else {
                fputcsv( $file, array( __('Sorry, no pending bank transaction data is available.', 'dc-woocommerce-multi-vendor') ) );
            }

            // Close file and get data from output buffer
            fclose($file);
            $csv = ob_get_clean();
            // Send CSV to browser for download
            echo $csv;
            die();
        }
    }
    
    function add_hidden_order_items($order_items) {
        $order_items[] = '_give_tax_to_vendor';
        $order_items[] = '_give_shipping_to_vendor';
        // and so on...
        return $order_items;
    }

    function conditonal_includes() {
        $screen = get_current_screen();

        if (in_array($screen->id, array('options-permalink'))) {
            $this->permalink_settings_init();
            $this->permalink_settings_save();
        }
    }

    function permalink_settings_init() {
        // Add our settings
        add_settings_field(
                'dc_product_vendor_taxonomy_slug', // id
                __('Vendor Shop Base', 'dc-woocommerce-multi-vendor'), // setting title
                array(&$this, 'mvx_taxonomy_slug_input'), // display callback
                'permalink', // settings page
                'optional'                                      // settings section
        );
    }

    function mvx_taxonomy_slug_input() {
        $permalinks = get_option('dc_vendors_permalinks');
        ?>
        <input name="dc_product_vendor_taxonomy_slug" type="text" class="regular-text code" value="<?php if (isset($permalinks['vendor_shop_base'])) echo esc_attr($permalinks['vendor_shop_base']); ?>" placeholder="<?php esc_attr_e('vendor slug', 'dc-woocommerce-multi-vendor') ?>" />
        <?php
    }

    function permalink_settings_save() {
        if (!is_admin()) {
            return;
        }
        // We need to save the options ourselves; settings api does not trigger save for the permalinks page
        if (isset($_POST['permalink_structure']) || isset($_POST['dc_product_vendor_taxonomy_slug'])) {

            // Cat and tag bases
            $dc_product_vendor_taxonomy_slug = wc_clean($_POST['dc_product_vendor_taxonomy_slug']);
            $permalinks = get_option('dc_vendors_permalinks');

            if (!$permalinks) {
                $permalinks = array();
            }

            $permalinks['vendor_shop_base'] = untrailingslashit($dc_product_vendor_taxonomy_slug);
            update_option('dc_vendors_permalinks', $permalinks);
        }
    }

    /**
     * Add Toolbar for vendor user 
     *
     * @access public
     * @param admin bar
     * @return void
     */
    function add_toolbar_items($admin_bar) {
        $user = wp_get_current_user();
        if (is_user_mvx_vendor($user)) {
            $admin_bar->add_menu(
                    array(
                        'id' => 'vendor_dashboard',
                        'title' => __('Frontend  Dashboard', 'dc-woocommerce-multi-vendor'),
                        'href' => get_permalink(mvx_vendor_dashboard_page_id()),
                        'meta' => array(
                            'title' => __('Frontend Dashboard', 'dc-woocommerce-multi-vendor'),
                            'target' => '_blank',
                            'class' => 'shop-settings'
                        ),
                    )
            );
            $admin_bar->add_menu(
                    array(
                        'id' => 'shop_settings',
                        'title' => __('Storefront', 'dc-woocommerce-multi-vendor'),
                        'href' => mvx_get_vendor_dashboard_endpoint_url(get_mvx_vendor_settings('mvx_store_settings_endpoint', 'vendor', 'general', 'storefront')),
                        'meta' => array(
                            'title' => __('Storefront', 'dc-woocommerce-multi-vendor'),
                            'target' => '_blank',
                            'class' => 'shop-settings'
                        ),
                    )
            );
        }
    }

    function load_class($class_name = '') {
        global $MVX;
        if ('' != $class_name) {
            require_once ($MVX->plugin_path . 'admin/class-' . esc_attr($MVX->token) . '-' . esc_attr($class_name) . '.php');
        } // End If Statement
    }

// End load_class()

    /**
     * Add dualcube footer text on plugin settings page
     *
     * @access public
     * @param admin bar
     * @return void
     */
    function dualcube_admin_footer_for_mvx() {
        global $MVX;
        ?>
        <div style="clear: both"></div>
        <div id="dc_admin_footer">
            <?php _e('Powered by', 'dc-woocommerce-multi-vendor'); ?> <a href="https://wc-marketplace.com/" target="_blank"><img src="<?php echo $MVX->plugin_url . 'assets/images/dualcube.png'; ?>"></a><?php _e('Multivendor X', 'dc-woocommerce-multi-vendor'); ?> &copy; <?php echo date('Y'); ?>
        </div>
        <?php
    }

    /**
     * Add css on admin header
     *
     * @access public
     * @return void
     */
    function admin_header() {
        $screen = get_current_screen();
        if (is_user_logged_in()) {
            if (isset($screen->id) && in_array($screen->id, array('edit-dc_commission', 'edit-mvx_university', 'edit-mvx_vendor_notice'))) {
                ?>
                <script>
                    jQuery(document).ready(function ($) {
                        var target_ele = $(".wrap .wp-header-end");
                        var targethtml = target_ele.html();
                        //targethtml = targethtml + '<a href="<?php echo trailingslashit(get_admin_url()) . 'admin.php?page=mvx-setting-admin'; ?>" class="page-title-action">Back To MVX Settings</a>';
                        //target_ele.html(targethtml);
                <?php if (in_array($screen->id, array('edit-mvx_university'))) { ?>
                            target_ele.before('<p><b><?php echo __('"Knowledgebase" section is visible only to vendors through the vendor dashboard. You may use this section to onboard your vendors. Share tutorials, best practices, "how to" guides or whatever you feel is appropriate with your vendors.', 'dc-woocommerce-multi-vendor'); ?></b></p>');
                <?php } ?>
                });

                </script>
                <?php
            }
        }
    }

    public function mvx_admin_menu() {
        if (is_user_mvx_vendor(get_current_vendor_id())) {
            remove_menu_page('edit.php');
            remove_menu_page('edit-comments.php');
            remove_menu_page('tools.php');
        }
    }

    public function mvx_submenu_count() {
        global $submenu;
        if (isset($submenu['mvx'])) {
            if (apply_filters('mvx_submenu_show_necesarry_count', true) && current_user_can('manage_woocommerce') ) {
                foreach ($submenu['mvx'] as $key => $menu_item) {
                    if (0 === strpos($menu_item[0], _x('Commissions', 'Admin menu name', 'dc-woocommerce-multi-vendor'))) {
                        $order_count = isset( mvx_count_commission()->unpaid ) ? mvx_count_commission()->unpaid : 0;
                        $submenu['mvx'][$key][0] .= ' <span class="awaiting-mod update-plugins count-' . $order_count . '"><span class="processing-count">' . number_format_i18n($order_count) . '</span></span>';
                    }
                    if (0 === strpos($menu_item[0], _x('To-do List', 'Admin menu name', 'dc-woocommerce-multi-vendor'))) {
                        $to_do_list_count = mvx_count_to_do_list();
                        $submenu['mvx'][$key][0] .= ' <span class="awaiting-mod update-plugins count-' . $to_do_list_count . '"><span class="processing-count">' . number_format_i18n($to_do_list_count) . '</span></span>';
                    }
                }
            }
        }
    }

    /**
     * Admin Scripts
     */
    public function enqueue_admin_script() {
        global $MVX;
        $screen = get_current_screen();
        $suffix = defined('MVX_SCRIPT_DEBUG') && MVX_SCRIPT_DEBUG ? '' : '.min';
        wp_enqueue_script( 'mce-view' );
        wp_enqueue_script('media-upload');
        wp_enqueue_media();
        wp_enqueue_editor();
        wp_enqueue_script( 'mce-view' );
        $MVX->library->load_upload_lib();
        // Find country list
        $woo_countries = new WC_Countries();
        $countries = $woo_countries->get_allowed_countries();
        $country_list = [];
        foreach ($countries as $countries_key => $countries_value) {
            $country_list[] = array(
                'lebel' => $countries_key,
                'value' => $countries_value
            );
        }

        // Find MVX created pages
        $pages = get_pages();
        $woocommerce_pages = array(wc_get_page_id('shop'), wc_get_page_id('cart'), wc_get_page_id('checkout'), wc_get_page_id('myaccount'));
        $pages_array = array();
        if($pages){
            foreach ($pages as $page) {
                if (!in_array($page->ID, $woocommerce_pages)) {
                    $pages_array[] = array(
                        'value'=> $page->ID,
                        'label'=> $page->post_title,
                        'key'=> $page->ID,
                    );
                }
            }
        }

        $default_nested_data = array(
            array(
                'nested_datas'  => array(
                    (Object)[]
                )
            )
        );

        $woo_countries = new WC_Countries();
        $countries = $woo_countries->get_allowed_countries();
        $country_list = [];
        foreach ($countries as $countries_key => $countries_value) {
            $country_list[] = array(
                'label' => $countries_value,
                'value' => $countries_key
            );
        }



        //vendor_country_code
        //vendor_state_code

        $user = null;
        $mvx_shipping_by_distance = $mvx_shipping_by_country = $vendor_default_shipping_options = '';
        $display_name_option = $shipping_options_list = $showdisplayname = $showpayment_method = array();

        if(isset($_GET['AnnouncementID']) && absint($_GET['AnnouncementID']) > 0) {
            $post_details = get_post($_GET['AnnouncementID']);
            $announcement_title =   $post_details->post_title;
            $announcement_url   =   get_post_meta($post_details->ID, '_wcmp_vendor_notices_url', true) ? get_post_meta($post_details->ID, '_wcmp_vendor_notices_url', true) : '';
            $announcement_content   =   $post_details->post_content;
            //$announcement_vendors   =   
        }


        if(isset($_GET['ID']) && absint($_GET['ID']) > 0) {
            $user = get_user_by("ID", $_GET['ID']);
                        
            // display name for vendor start

            if(isset($user->display_name)) {
                if ($user->user_login) {
                    $display_name_option[] = array(
                        'value'=> $user->user_login,
                        'label'=> $user->user_login,
                        'key'=> $user->user_login,
                    );
                }
                if ($user->first_name) {
                    $display_name_option[] = array(
                        'value'=> $user->first_name,
                        'label'=> $user->first_name,
                        'key'=> $user->first_name,
                    );
                }
                if ($user->last_name) {
                    $display_name_option[] = array(
                        'value'=> $user->last_name,
                        'label'=> $user->last_name,
                        'key'=> $user->last_name,
                    );
                }

                if ($user->first_name && $user->last_name) {
                    $display_name_option[] = array(
                        'value'=> $user->first_name . " " . $user->last_name,
                        'label'=> $user->first_name . " " . $user->last_name,
                        'key'=> $user->first_name . " " . $user->last_name,
                    );
                     $display_name_option[] = array(
                        'value'=> $user->last_name . " " . $user->first_name,
                        'label'=> $user->last_name . " " . $user->first_name,
                        'key'=> $user->last_name . " " . $user->first_name,
                    );
                }
            }

            foreach ($display_name_option as $display_key => $display_value) {
                if ($display_value['value'] && $display_value['value'] == $user->display_name) {
                    $showdisplayname[]  = $display_name_option[$display_key];
                }
            }


            // set option vendor payment method
            $payment_admin_settings = get_option('mvx_commission-configuration_tab_settings');
            $payment_mode = array('payment_mode' => __('Payment Mode', 'dc-woocommerce-multi-vendor'));
            if ($payment_admin_settings && isset($payment_admin_settings['payment_method_disbursement']) && !empty($payment_admin_settings['payment_method_disbursement']) && in_array('paypal_masspay', $payment_admin_settings['payment_method_disbursement'])) {
                $payment_mode['paypal_masspay'] = __('PayPal Masspay', 'dc-woocommerce-multi-vendor');
            }
            if ($payment_admin_settings && isset($payment_admin_settings['payment_method_disbursement']) && !empty($payment_admin_settings['payment_method_disbursement']) && in_array('paypal_payout', $payment_admin_settings['payment_method_disbursement'])) {
                $payment_mode['paypal_payout'] = __('PayPal Payout', 'dc-woocommerce-multi-vendor');
            }
            if ($payment_admin_settings && isset($payment_admin_settings['payment_method_disbursement']) && !empty($payment_admin_settings['payment_method_disbursement']) && in_array('stripe_masspay', $payment_admin_settings['payment_method_disbursement'])) {
                $payment_mode['stripe_masspay'] = __('Stripe Connect', 'dc-woocommerce-multi-vendor');
            }
            if ($payment_admin_settings && isset($payment_admin_settings['payment_method_disbursement']) && !empty($payment_admin_settings['payment_method_disbursement']) && in_array('direct_bank', $payment_admin_settings['payment_method_disbursement'])) {
                $payment_mode['direct_bank'] = __('Direct Bank', 'dc-woocommerce-multi-vendor');
            }
            $vendor_payment_mode_select = apply_filters('mvx_vendor_payment_mode', $payment_mode);
            $vendor_payment_method_display_section  =   array();
            foreach ($vendor_payment_mode_select as $selectkey => $selectvalue) {
                $vendor_payment_method_display_section[]    =   array(
                    'label' =>  $selectvalue,
                    'value' =>  $selectkey
                );
            }

            $payment_method = get_user_meta($_GET['ID'], '_vendor_payment_mode', true);
            foreach ($vendor_payment_method_display_section as $payment_key => $payment_value) {
                if ($payment_value['value'] && $payment_value['value'] == $payment_method) {
                    $showpayment_method  = $vendor_payment_method_display_section[$payment_key];
                }
            }

            $commission_value = get_user_meta($_GET['ID'], '_vendor_commission', true);
            $vendor_paypal_email = get_user_meta($_GET['ID'], '_vendor_paypal_email', true);

            $vendor_bank_name = get_user_meta($_GET['ID'], '_vendor_bank_name', true);
            $vendor_aba_routing_number = get_user_meta($_GET['ID'], '_vendor_aba_routing_number', true);
            $vendor_destination_currency = get_user_meta($_GET['ID'], '_vendor_destination_currency', true);
            $vendor_bank_address = get_user_meta($_GET['ID'], '_vendor_bank_address', true);
            $vendor_iban = get_user_meta($_GET['ID'], '_vendor_iban', true);
            $vendor_account_holder_name = get_user_meta($_GET['ID'], '_vendor_account_holder_name', true);
            $vendor_bank_account_number = get_user_meta($_GET['ID'], '_vendor_bank_account_number', true);


            $_vendor_shipping_policy = get_user_meta( $user->data->ID, 'vendor_shipping_policy', true ) ? get_user_meta( $user->data->ID, 'vendor_shipping_policy', true ) : __( 'No policy found', 'dc-woocommerce-multi-vendor' );
            $_vendor_refund_policy = get_user_meta( $user->data->ID, 'vendor_refund_policy', true ) ? get_user_meta( $user->data->ID, 'vendor_refund_policy', true ) : __( 'No policy found', 'dc-woocommerce-multi-vendor' );
            $_vendor_cancellation_policy = get_user_meta( $user->data->ID, 'vendor_cancellation_policy', true ) ? get_user_meta( $user->data->ID, 'vendor_cancellation_policy', true ) : __( 'No policy found', 'dc-woocommerce-multi-vendor' );


            $vendor_phone = get_user_meta( $user->data->ID, '_vendor_phone', true ) ? get_user_meta( $user->data->ID, '_vendor_phone', true ) : '';
            $vendor_address_1 = get_user_meta( $user->data->ID, '_vendor_address_1', true ) ? get_user_meta( $user->data->ID, '_vendor_address_1', true ) : '';
            $vendor_address_2 = get_user_meta( $user->data->ID, '_vendor_address_2', true ) ? get_user_meta( $user->data->ID, '_vendor_address_2', true ) : '';
            $vendor_city = get_user_meta( $user->data->ID, '_vendor_city', true ) ? get_user_meta( $user->data->ID, '_vendor_city', true ) : '';
            $vendor_postcode = get_user_meta( $user->data->ID, '_vendor_postcode', true ) ? get_user_meta( $user->data->ID, '_vendor_postcode', true ) : '';




            $vendor_country_code = get_user_meta( $user->data->ID, '_vendor_country_code', true ) ? get_user_meta( $user->data->ID, '_vendor_country_code', true ) : '';
            $vendor_state_code = get_user_meta( $user->data->ID, '_vendor_state_code', true ) ? get_user_meta( $user->data->ID, '_vendor_state_code', true ) : '';

            // display country value from database
            $vendor_country_code_data = $vendor_state_code_data = array();
            foreach ($country_list as $display_country_key => $display_country_value) {
                if ($display_country_value['value'] && $display_country_value['value'] == $vendor_country_code) {
                    $vendor_country_code_data[]  = $country_list[$display_country_key];
                }
            }

            // display state value from database
            $state_list = wc_clean( wp_unslash( WC()->countries->get_states($vendor_country_code) ) );
            if ($state_list) {
                foreach ($state_list as $display_state_key => $display_state_value) {
                    if ($display_state_key && $display_state_key == $vendor_state_code) {
                        $vendor_state_code_data[]  = [
                            'label' => $display_state_value,
                            'value' => $display_state_key
                        ];
                    }
                }
            }
            

            $user_vendor = get_mvx_vendor($user->data->ID);

            $current_offset = get_user_meta($user->data->ID, 'gmt_offset', true);
            $tzstring = get_user_meta($user->data->ID, 'timezone_string', true);
            // Remove old Etc mappings. Fallback to gmt_offset.
            if (false !== strpos($tzstring, 'Etc/GMT')) {
                $tzstring = '';
            }


            $vendor_fb_profile = get_user_meta($user->data->ID, '_vendor_fb_profile', true) ? get_user_meta($user->data->ID, '_vendor_fb_profile', true) : '';
            $vendor_twitter_profile = get_user_meta($user->data->ID, '_vendor_twitter_profile', true) ? get_user_meta($user->data->ID, '_vendor_twitter_profile', true) : '';
            $vendor_linkdin_profile = get_user_meta($user->data->ID, '_vendor_linkdin_profile', true) ? get_user_meta($user->data->ID, '_vendor_linkdin_profile', true) : '';
            $vendor_youtube_profile = get_user_meta($user->data->ID, '_vendor_youtube', true) ? get_user_meta($user->data->ID, '_vendor_youtube', true) : '';
            $vendor_instagram_profile = get_user_meta($user->data->ID, '_vendor_instagram', true) ? get_user_meta($user->data->ID, '_vendor_instagram', true) : '';

            if (empty($tzstring)) { // Create a UTC+- zone if no timezone string exists
                $check_zone_info = false;
                if (0 == $current_offset) {
                    $tzstring = 'UTC+0';
                } elseif ($current_offset < 0) {
                    $tzstring = 'UTC' . $current_offset;
                } else {
                    $tzstring = 'UTC+' . $current_offset;
                }
            }

            // Shipping options
            
            $vendor_default_shipping_options_database_value = get_user_meta($_GET['ID'], 'vendor_shipping_options', true) ? get_user_meta($_GET['ID'], 'vendor_shipping_options', true) : '';
            $shipping_options = apply_filters('mvx_vendor_shipping_option_to_vendor', array(
                'distance_by_zone' => __('Shipping by Zone', 'dc-woocommerce-multi-vendor'),
            ) );
            if (get_mvx_vendor_settings( 'enabled_distance_by_shipping_for_vendor', 'general' ) && 'Enable' === get_mvx_vendor_settings( 'enabled_distance_by_shipping_for_vendor', 'general' )) {
                $shipping_options['distance_by_shipping'] = __('Shipping by Distance', 'dc-woocommerce-multi-vendor');
            }
            if (get_mvx_vendor_settings( 'enabled_shipping_by_country_for_vendor', 'general' ) && 'Enable' === get_mvx_vendor_settings( 'enabled_shipping_by_country_for_vendor', 'general' )) {
                $shipping_options['shipping_by_country'] = __('Shipping by Country', 'dc-woocommerce-multi-vendor');
            }
            foreach ($shipping_options as $shipping_key => $shipping_value) {
                $shipping_options_list[] = array(
                    'value' => sanitize_text_field($shipping_key),
                    'label' => sanitize_text_field($shipping_value)
                );
            }

            $vendor_default_shipping_options = array();
            foreach ($shipping_options_list as $key => $value) {
                if ($value['value'] == $vendor_default_shipping_options_database_value) {
                    $vendor_default_shipping_options[] = $shipping_options_list[$key];
                }
            }

            $shipping_distance_rate = mvx_get_user_meta( $_GET['ID'], '_mvx_shipping_by_distance_rates', true ) ? mvx_get_user_meta( $_GET['ID'], '_mvx_shipping_by_distance_rates', true ) : $default_nested_data;

            $mvx_shipping_by_distance = mvx_get_user_meta( $_GET['ID'], '_mvx_shipping_by_distance', true ) ? get_user_meta( $_GET['ID'], '_mvx_shipping_by_distance', true ) : array();

            $mvx_shipping_by_country = mvx_get_user_meta( $_GET['ID'], '_mvx_shipping_by_country', true ) ? mvx_get_user_meta( $_GET['ID'], '_mvx_shipping_by_country', true ) : '';

            $shipping_country_rate = mvx_get_user_meta( $_GET['ID'], '_mvx_country_shipping_rates', true ) ? mvx_get_user_meta( $_GET['ID'], '_mvx_country_shipping_rates', true ) : $default_nested_data;

        }


        $settings_fields = [
            'settings-general'  =>  [
                [
                    'key'       => 'approve_vendor',
                    'type'      => 'toggle_rectangle',
                    'class'     => 'mvx-toggle-radio-switcher',
                    'label'     => __( 'Approve Vendor', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Lets you either check vendor information manually or sends vendors directly to the dashboard as soon as the registration is complete', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'name'  => 'approve_vendor',
                            'key' => 'manually',
                            'label' => __('Manually', 'dc-woocommerce-multi-vendor'),
                            'value' => 'manually'
                        ),
                        array(
                            'name'  => 'approve_vendor',
                            'key'   => 'automatically',
                            'label' => __('Automatically', 'dc-woocommerce-multi-vendor'),
                            'value' => 'automatically'
                        )
                    ),
                    'database_value' => '',
                ],
                [
                    'key'    => 'vendors_backend_access',
                    'label'   => __( "Vendor's Backend Access", 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'props'     => array(
                        'disabled'  => true
                    ),
                    'options' => array(
                        array(
                            'key'=> "vendors_backend_access",
                            'label'=> __('Limit vendors from accessing their backened', 'dc-woocommerce-multi-vendor'),
                            'value'=> "vendors_backend_access"
                        ),
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'display_product_seller',
                    'label'   => __( "Display Product Seller", 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'props'     => array(
                        'disabled'  => true
                    ),
                    'options' => array(
                        array(
                            'key'=> "display_product_seller",
                            'label'=> __("Showcase the product vendor's name", 'dc-woocommerce-multi-vendor'),
                            'value'=> "display_product_seller"
                        ),
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'category_pyramid_guide',
                    'label'   => __( "Category Pyramid Guide (CPG)", 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'props'     => array(
                        'disabled'  => true
                    ),
                    'options' => array(
                        array(
                            'key'=> "category_pyramid_guide",
                            'label'=> __("CPG option helps vendor's to identify the correct categories for their products", 'dc-woocommerce-multi-vendor'),
                            'value'=> "category_pyramid_guide"
                        ),
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'       =>  'vendor_list_page',
                    'type'      =>  'blocktext',
                    'label'     =>  __( 'Vendor List Page', 'dc-woocommerce-multi-vendor' ),
                    'blocktext'      =>  __( "Use the following shortcode to display vendor's list on your site <a href='https://www.w3schools.com'>Learn More</a> <code>[wcmp_vendorlist]</code>", 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'registration_page',
                    'type'      => 'select',
                    'label'     => __( 'Registration Page', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Choose which page your want to display your registration form on', 'dc-woocommerce-multi-vendor' ),
                    'options' => $pages_array,
                    'database_value' => '',
                ],
                [
                    'key'       => 'vendor_dashboard_page',
                    'type'      => 'select',
                    'label'     => __( 'Vendor Dashboard Page', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Choose which page your want to display your Vendor Dashboard on', 'dc-woocommerce-multi-vendor' ),
                    'options' => $pages_array,
                    'database_value' => '',
                ],
                [
                    'key'       => 'avialable_shortcodes',
                    'type'      => 'table',
                    'label'     => __( 'Avialable Shortcodes', 'dc-woocommerce-multi-vendor' ),
                    'label_options' =>  array(
                        __('Variable', 'dc-woocommerce-multi-vendor'),
                        __('Description', 'dc-woocommerce-multi-vendor'),
                    ),
                    'options' => array(
                        array(
                            'variable'=> "<code>mvx_vendor</code>",
                            'description'=> __('At Left', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'variable'=> "vendor_registration",
                            'description'=> __('At Right', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'variable'=> "vendor_coupons",
                            'description'=> __('At Right', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'variable'=> "mvx_recent_products",
                            'description'=> __('At Right', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'variable'=> "mvx_products",
                            'description'=> __('At Right', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'variable'=> "mvx_featured_products",
                            'description'=> __('At Right', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'variable'=> "mvx_sale_products",
                            'description'=> __('At Right', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'variable'=> "mvx_top_rated_products",
                            'description'=> __('At Right', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'variable'=> "mvx_best_selling_products",
                            'description'=> __('At Right', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'variable'=> "mvx_product_category",
                            'description'=> __('At Right', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'variable'=> "mvx_vendorslist",
                            'description'=> __('At Right', 'dc-woocommerce-multi-vendor'),
                        ),
                    ),
                    'database_value' => '',
                ],
            ],
            'social'    =>  [
                [
                    'key'    => 'buddypress_enabled',
                    'label'   => __( 'Buddypress', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'checkbox',
                    'class'     => 'mvx-toggle-checkbox',
                    'options' => array(
                        array(
                            'key'=> "buddypress_enabled",
                            'label'=> __('', 'dc-woocommerce-multi-vendor'),
                            'value'=> "buddypress_enabled"
                        )
                    ),
                    'database_value' => array(),
                ],
            ],
            'seller-dashbaord'  =>  [
                [
                    'key'    => 'mvx_new_dashboard_site_logo',
                    'label'   => __( 'Branding Logo', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'file',
                    'width' =>  75,
                    'height'    => 75,
                    'desc' => __('Upload Brand Image as Logo', 'dc-woocommerce-multi-vendor'),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'vendor_color_scheme_picker',
                    'type'      => 'radio_color',
                    'label'     => __( 'Color Scheme', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Select your prefered colou scheme', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'name'  => 'vendor_color_scheme_picker',
                            'key' => 'outer_space_blue',
                            'label' => __('Outer Space', 'dc-woocommerce-multi-vendor'),
                            'color' => array('#202528', '#333b3d','#3f85b9', '#316fa8'),
                            'value' => 'outer_space_blue'
                        ),
                        array(
                            'name'  => 'vendor_color_scheme_picker',
                            'key'   => 'green_lagoon',
                            'label' => __('Green Lagoon', 'dc-woocommerce-multi-vendor'),
                            'color' => array('#171717', '#212121', '#009788','#00796a'),
                            'value' => 'green_lagoon'
                        ),
                        array(
                            'name'  => 'vendor_color_scheme_picker',
                            'key'   => 'old_west',
                            'label' => __('Old West', 'dc-woocommerce-multi-vendor'),
                            'color' => array('#46403c', '#59524c', '#c7a589', '#ad8162'),
                            'value' => 'old_west'
                        ),
                        array(
                            'name'  => 'vendor_color_scheme_picker',
                            'key'   => 'wild_watermelon',
                            'label' => __('Wild Watermelon', 'dc-woocommerce-multi-vendor'),
                            'color' => array('#181617', '#353130', '#fd5668', '#fb3f4e'),
                            'value' => 'wild_watermelon'
                        ),
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'setup_wizard_introduction',
                    'type'      => 'wpeditor',
                    'class'     =>  'mvx-setting-wpeditor-class',
                    'label'     => __( 'Vendor Setup wizard Introduction Message', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( "Type an message to introduce your vendor's to their dashboard", 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'separator_content',
                    'type'      => 'section',
                    'label'     => "",
                ],
                [
                    'key'       => 'mvx_vendor_announcements_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Announcements', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor announcements page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-announcements', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_store_settings_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Storefront', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Used as site logo on vendor dashboard pages', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('storefront', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_profile_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Profile', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor profile management page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('profile', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_policies_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Policies', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor policies page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-policies', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_billing_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Billing', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor billing page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-billing', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_shipping_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Shipping', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor shipping page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-shipping', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_report_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Report', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor report page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-report', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_banking_overview_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Banking Overview', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor banking overview page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('banking-overview', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_add_product_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Add Product', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for add new product page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('add-product', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_edit_product_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Edit Product', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for edit product page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('edit-product', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_products_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Products List', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for products list page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('products', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_add_coupon_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Add Coupon', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for add new coupon page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('add-coupon', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_coupons_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Coupons List', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for coupons list page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('coupons', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_orders_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Orders', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor orders page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-orders', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_withdrawal_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Widthdrawals', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor widthdrawals page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-withdrawal', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_transaction_details_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Transaction Details', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for transaction details page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('transaction-details', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_knowledgebase_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Knowledgebase', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor knowledgebase page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-knowledgebase', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_tools_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Tools', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor tools page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-tools', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_products_qnas_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Products Q&As', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor products Q&As page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('products-qna', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'separator_content',
                    'type'      => 'section',
                    'label'     => "",
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_dashboard_custom_css',
                    'type'      => 'textarea',
                    'label'     => __( 'Custom CSS', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Apply Custom CSS to Change Dashboard Design', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
            ],
            'store' =>  [
                [
                    'key'       => 'mvx_vendor_shop_template',
                    'type'      => 'radio_select',
                    'label'     => __( 'Store Header', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( "Select a template for vendor's store", 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'name'  => 'mvx_vendor_shop_template',
                            'key' => 'template1',
                            'label' => __('Outer Space', 'dc-woocommerce-multi-vendor'),
                            'color' => $MVX->plugin_url.'assets/images/template1.png',
                            'width' => 50,
                            'height'=> 60,
                            'value' => 'template1'
                        ),
                        array(
                            'name'  => 'mvx_vendor_shop_template',
                            'key'   => 'template2',
                            'label' => __('Green Lagoon', 'dc-woocommerce-multi-vendor'),
                            'color' => $MVX->plugin_url.'assets/images/template2.png',
                            'width' => 50,
                            'height'=> 60,
                            'value' => 'template2'
                        ),
                        array(
                            'name'  => 'mvx_vendor_shop_template',
                            'key'   => 'template3',
                            'label' => __('Old West', 'dc-woocommerce-multi-vendor'),
                            'color' => $MVX->plugin_url.'assets/images/template3.png',
                            'width' => 50,
                            'height'=> 60,
                            'value' => 'template3'
                        ),
                    ),
                    'database_value' => '',
                ],
                [
                    'key'    => 'enable_store_map_for_vendor',
                    'label'   => __( 'Store Location', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "enable_store_map_for_vendor",
                            'label'=> __("Tap to dispay the location of  the vendors' shops", 'dc-woocommerce-multi-vendor'),
                            'value'=> "enable_store_map_for_vendor"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'choose_map_api',
                    'type'      => 'select',
                    'bydefault' =>  'google_map_set',
                    'label'     => __( 'Location Provider', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Select prefered location Provider', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'key'=> "google_map_set",
                            'label'=> __('Google map', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('google_map_set', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "mapbox_api_set",
                            'selected'  => true,
                            'label'=> __('Mapbox map', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('mapbox_api_set', 'dc-woocommerce-multi-vendor'),
                        )
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'google_api_key',
                    'type'      => 'text',
                    'depend'    => 'choose_map_api',
                    'dependvalue'       =>  'google_map_set',
                    'label'     => __( 'Google Map API key', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __('<a href="https://developers.google.com/maps/documentation/javascript/get-api-key#get-an-api-key" target="_blank">Click here to generate key</a>','dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mapbox_api_key',
                    'type'      => 'text',
                    'depend'    => 'choose_map_api',
                    'dependvalue'       =>  'mapbox_api_set',
                    'label'     => __( 'Mapbox access token', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __('<a href="https://docs.mapbox.com/help/getting-started/access-tokens/" target="_blank">Click here to generate access token</a>','dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'show_related_products',
                    'type'      => 'select',
                    'label'     => __( 'Related Product', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Tap to let  customers view other product related to the  product they are seeing', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'key'=> "all_related",
                            'label'=> __('Related Products from Entire Store', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('all_related', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "vendors_related",
                            'selected'  => true,
                            'label'=> __("Related Products from Vendor's Store", 'dc-woocommerce-multi-vendor'),
                            'value'=> __('vendors_related', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "disable",
                            'selected'  => true,
                            'label'=> __("Disable", 'dc-woocommerce-multi-vendor'),
                            'value'=> __('disable', 'dc-woocommerce-multi-vendor'),
                        )
                    ),
                    'database_value' => '',
                ],
                [
                    'key'    => 'is_enable_store_sidebar',
                    'label'   => __( 'Store Sidebar', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_enable_store_sidebar",
                            'label'=> __('Tap to display sidebar section for vendor shop page. Select her to add vendor shop widget', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_enable_store_sidebar"
                        ),
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'store_sidebar_position',
                    'type'      => 'toggle_rectangle',
                    'label'     => __( 'Store Sidebar Position', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Decide where your want your store sidebar to be displayed', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'name'  =>  'store_sidebar_position',
                            'key'=> "left",
                            'label'=> __('At Left', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('At Left', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'name'  =>  'store_sidebar_position',
                            'key'=> "right",
                            'label'=> __('At Right', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('At Right', 'dc-woocommerce-multi-vendor'),
                        ),
                    ),
                    'database_value' => '',
                ],
            ],
            'products'  =>  [
                [
                    'key'    => 'product_types',
                    'label'   => __( 'Product Types', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'parent_class'  => 'mvx-toggle-checkbox-header',
                    'type'    => 'checkbox',
                    'desc' => __('lets vendors transforms simple products into either nontangible virtual product or into a product that can be downloaded', 'dc-woocommerce-multi-vendor'),
                    'options' => array(
                        array(
                            'key'=> "simple",
                            'label'=> __('Simple', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('Simple', 'dc-woocommerce-multi-vendor'),
                            'value'=> "simple"
                        ),
                        array(
                            'key'=> "variable",
                            'label'=> __('Variable', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('Variable', 'dc-woocommerce-multi-vendor'),
                            'value'=> "variable"
                        ),
                        array(
                            'key'=> "external",
                            'label'=> __('External', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('External', 'dc-woocommerce-multi-vendor'),
                            'value'=> "external"
                        ),
                        array(
                            'key'=> "grouped",
                            'label'=> __('Grouped', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('Grouped', 'dc-woocommerce-multi-vendor'),
                            'value'=> "grouped"
                        )
                    ),
                    'database_value' => array(),
                ],
            ],
            'products-capability'   =>  [
                [
                    'key'    => 'is_submit_product',
                    'label'   => __( 'Submit Products', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_submit_product",
                            'label'=> __('This option enables vendors to not only add new products but to also submit them for your approval', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_submit_product"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'is_published_product',
                    'label'   => __( 'Publish Products', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_published_product",
                            'label'=> __('Vendors can publish their product on site without your approval', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_published_product"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'is_edit_delete_published_product',
                    'label'   => __( 'Edit Published Products', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_edit_delete_published_product",
                            'label'=> __('This option  lets the vendor correct a published product', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_edit_delete_published_product"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'publish_and_submit_products',
                    'label'   => __( 'Publish and Submit Products', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "publish_and_submit_products",
                            'label'=> __('Allowa vendors to  make their products live while submitting it to your for correction', 'dc-woocommerce-multi-vendor'),
                            'value'=> "publish_and_submit_products"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'is_submit_coupon',
                    'label'   => __( 'Submit Coupons', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_submit_coupon",
                            'label'=> __('This option enables vendors to create their own coupons', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_submit_coupon"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'is_publish_coupon',
                    'label'   => __( 'Publish Coupons', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_publish_coupon",
                            'label'=> __('With this option vendors can make their coupons live on your site', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_publish_coupon"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'is_edit_coupon',
                    'label'   => __( 'Edit Coupons', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_edit_coupon",
                            'label'=> __('Vendors can edit an re-use a published coupon', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_edit_coupon"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'is_upload_files',
                    'label'   => __( 'Upload Media Files', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_upload_files",
                            'label'=> __('Let Vendors upload media like ebooks, music, video, images etc', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_upload_files"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'import_product',
                    'label'   => __( 'Import Product', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "import_product",
                            'label'=> __('Import product data from your computer', 'dc-woocommerce-multi-vendor'),
                            'value'=> "import_product"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'export_product',
                    'label'   => __( 'Export Product', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "export_product",
                            'label'=> __('Export product data to your computer', 'dc-woocommerce-multi-vendor'),
                            'value'=> "export_product"
                        )
                    ),
                    'database_value' => array(),
                ],
            ],
            'commissions'   =>  [
                [
                    'key'       => 'revenue_sharing_mode',
                    'type'      => 'toggle_rectangle',
                    'class'     => 'mvx-toggle-radio-switcher',
                    'label'     => __( 'Revenue Sharing Mode', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Select how you want the commission to be split', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'name'  => 'revenue_sharing_mode',
                            'key' => 'revenue_sharing_mode_admin',
                            'label' => __('Admin fees', 'dc-woocommerce-multi-vendor'),
                            'value' => 'revenue_sharing_mode_admin'
                        ),
                        array(
                            'name'  => 'revenue_sharing_mode',
                            'key'   => 'revenue_sharing_mode_vendor',
                            'label' => __('Vendor commissions', 'dc-woocommerce-multi-vendor'),
                            'value' => 'revenue_sharing_mode_vendor'
                        )
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'commission_type',
                    'type'      => 'select',
                    'label'     => __( 'Commission Type', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Choose the Commission Option prefered by you. For better undrestanding read doc', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'key'=> "choose_commission_type",
                            'label'=> __('Choose Commission Type', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('choose_commission_type', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "fixed",
                            'label'=> __('Fixed Amount', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('fixed', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "percent",
                            'label'=> __('Percentage', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('percent', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "fixed_with_percentage",
                            'label'=> __('%age + Fixed (per transaction)', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('fixed_with_percentage', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "fixed_with_percentage_qty",
                            'label'=> __('%age + Fixed (per unit)', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('fixed_with_percentage_qty', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "commission_by_product_price",
                            'label'=> __('Commission By Product Price', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('commission_by_product_price', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "commission_by_purchase_quantity",
                            'label'=> __('Commission By Purchase Quantity', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('commission_by_purchase_quantity', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "fixed_with_percentage_per_vendor",
                            'label'=> __('%age + Fixed (per vendor)', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('fixed_with_percentage_per_vendor', 'dc-woocommerce-multi-vendor'),
                        ),
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'default_commission',
                    'type'      => 'number',
                    'label'     => __( 'Commission Value', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __('The commission amount added here will be applicable for all commissions. In case the your commission type is fixed the', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'default_percentage',
                    'type'      => 'number',
                    'label'     => __( 'Commission Percentage', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __('This will be the default percentage paid to vendors if product and vendor specific commission is not set', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'fixed_with_percentage',
                    'type'      => 'number',
                    'label'     => __( 'Fixed Amount', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __('Fixed (per transaction)', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'fixed_with_percentage_qty',
                    'type'      => 'number',
                    'label'     => __( 'Fixed Amount', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __('Fixed (per unit)', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'fixed_with_percentage_per_vendor',
                    'type'      => 'number',
                    'label'     => __( 'Fixed Amount', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __('Fixed (per vendor)', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'    => 'payment_method_disbursement',
                    'label'   => __( 'Commission Disbursement Method', 'dc-woocommerce-multi-vendor' ),
                    'desc'  =>  __( "display only enabled payment gateways. To enable your choosen disbursement type click here (link module page)", 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "paypal_masspay",
                            'label'=> __('PayPal Masspay (Stop Waiting and Pay Vendors Immediately with PayPal Real-Time Split Payment using <a href="https://wc-marketplace.com/product/mvx-paypal-marketplace/">MVX PayPal Marketplace</a>. Please visit our site)', 'dc-woocommerce-multi-vendor'),
                            'value'=> "paypal_masspay"
                        ),
                        array(
                            'key'=> "paypal_payout",
                            'label'=> __('Paypal Payout', 'dc-woocommerce-multi-vendor'),
                            'value'=> "paypal_payout"
                        ),
                        array(
                            'key'=> "stripe_masspay",
                            'label'=> __('Stripe Connect', 'dc-woocommerce-multi-vendor'),
                            'value'=> "stripe_masspay"
                        ),
                        array(
                            'key'=> "direct_bank",
                            'label'=> __('Direct Bank Transfer', 'dc-woocommerce-multi-vendor'),
                            'value'=> "direct_bank"
                        ),
                        array(
                            'key'=> "razorpay_block",
                            'label'=> __('Razorpay', 'dc-woocommerce-multi-vendor'),
                            'value'=> "razorpay_block"
                        )
                    ),
                    'database_value' => array(),
                ],

                [
                    'key'       => 'payment_gateway_charge',
                    'label'     => __( 'Payment Gateway Charge', 'dc-woocommerce-multi-vendor' ),
                    'desc'  =>  __( "Add the charges inccured during online payment processing", 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'      => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "payment_gateway_charge",
                            'label'=> __('If checked, you can set payment gateway charge to the vendor for commission disbursement.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "payment_gateway_charge"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'gateway_charges_cost_carrier',
                    'type'      => 'select',
                    'label'     => __( 'Who bear the gateway charges', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __('You can decide who will bear the gateways charges incase of using any automatic payment', 'dc-woocommerce-multi-vendor'),
                    'options' => array(
                        array(
                            'key'=> "vendor",
                            'label'=> __('Vendor', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('vendor', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "admin",
                            'label'=> __('Site owner', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('admin', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "separate",
                            'label'=> __('Separately', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('separate', 'dc-woocommerce-multi-vendor'),
                        )
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'payment_gateway_charge_type',
                    'type'      => 'select',
                    'label'     => __( 'Gateway Charge Type', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __('Choose your preferred gateway charge type.', 'dc-woocommerce-multi-vendor'),
                    'options' => array(
                        array(
                            'key'=> "percent",
                            'label'=> __('Percentage', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('percent', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "fixed",
                            'label'=> __('Fixed Amount', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('fixed', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "fixed_with_percentage",
                            'label'=> __('%age + Fixed', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('fixed_with_percentage', 'dc-woocommerce-multi-vendor'),
                        )
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'masspay_percentage_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Percentage', 'dc-woocommerce-multi-vendor' ),
                    'desc'  => __('PayPal Masspay (Stop Waiting and Pay Vendors Immediately with PayPal Real-Time Split Payment using <a href="https://wc-marketplace.com/product/mvx-paypal-marketplace/">MVX PayPal Marketplace</a>. Please visit our site)', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'masspay_fixed_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Fixed', 'dc-woocommerce-multi-vendor' ),
                    'desc'  => __('PayPal Masspay (Stop Waiting and Pay Vendors Immediately with PayPal Real-Time Split Payment using <a href="https://wc-marketplace.com/product/mvx-paypal-marketplace/">MVX PayPal Marketplace</a>. Please visit our site)', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'payout_percentage_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Percentage', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'payout_fixed_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Fixed', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'stripe_percentage_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Percentage', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'stripe_fixed_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Fixed', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'bank_percentage_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Percentage', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'bank_fixed_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Fixed', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
            ],
            'dashbaord-management'   => [
                /*[
                    'key'    => 'is_backend_diabled',
                    'label'   => __( 'Disallow Vendors wp-admin Access', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'hints' => __('If unchecked vendor will have access to backend', 'dc-woocommerce-multi-vendor'),
                    'props'     => array(
                        'disabled'  => true
                    ),
                    'options' => array(
                        array(
                            'key'=> "reactjs",
                            'label'=> __('Get <a href="//wc-marketplace.com/product/mvx-frontend-manager/">Advanced Frontend Manager</a> to offer a single dashboard for all vendor purpose and eliminate their backend access requirement <code>example.com/category/my-category/</code>.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "reactjs"
                        ),
                    ),
                    'database_value' => array(),
                ],*/
                [
                    'key'    => 'mvx_new_dashboard_site_logo',
                    'label'   => __( 'Dashbaord Brand Logo', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'file',
                    'width' =>  75,
                    'height'    => 75,
                    'desc' => __('Used as site logo on vendor dashboard pages', 'dc-woocommerce-multi-vendor'),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'vendor_color_scheme_picker',
                    'type'      => 'radio_color',
                    'label'     => __( 'Dashboard Color Scheme', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Used as site logo on vendor dashboard pages', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'name'  => 'vendor_color_scheme_picker',
                            'key' => 'outer_space_blue',
                            'label' => __('Outer Space', 'dc-woocommerce-multi-vendor'),
                            'color' => array('#202528', '#333b3d','#3f85b9', '#316fa8'),
                            'value' => 'outer_space_blue'
                        ),
                        array(
                            'name'  => 'vendor_color_scheme_picker',
                            'key'   => 'green_lagoon',
                            'label' => __('Green Lagoon', 'dc-woocommerce-multi-vendor'),
                            'color' => array('#171717', '#212121', '#009788','#00796a'),
                            'value' => 'green_lagoon'
                        ),
                        array(
                            'name'  => 'vendor_color_scheme_picker',
                            'key'   => 'old_west',
                            'label' => __('Old West', 'dc-woocommerce-multi-vendor'),
                            'color' => array('#46403c', '#59524c', '#c7a589', '#ad8162'),
                            'value' => 'old_west'
                        ),
                        array(
                            'name'  => 'vendor_color_scheme_picker',
                            'key'   => 'wild_watermelon',
                            'label' => __('Wild Watermelon', 'dc-woocommerce-multi-vendor'),
                            'color' => array('#181617', '#353130', '#fd5668', '#fb3f4e'),
                            'value' => 'wild_watermelon'
                        ),
                    ),
                    'database_value' => '',
                ],

                [
                    'key'       => 'mvx_vendor_shop_template',
                    'type'      => 'radio_select',
                    'label'     => __( 'Vendor Shop Template', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Used as site logo on vendor dashboard pages', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'name'  => 'mvx_vendor_shop_template',
                            'key' => 'template1',
                            'label' => __('Outer Space', 'dc-woocommerce-multi-vendor'),
                            'color' => $MVX->plugin_url.'assets/images/template1.png',
                            'width' => 50,
                            'height'=> 60,
                            'value' => 'template1'
                        ),
                        array(
                            'name'  => 'mvx_vendor_shop_template',
                            'key'   => 'template2',
                            'label' => __('Green Lagoon', 'dc-woocommerce-multi-vendor'),
                            'color' => $MVX->plugin_url.'assets/images/template2.png',
                            'width' => 50,
                            'height'=> 60,
                            'value' => 'template2'
                        ),
                        array(
                            'name'  => 'mvx_vendor_shop_template',
                            'key'   => 'template3',
                            'label' => __('Old West', 'dc-woocommerce-multi-vendor'),
                            'color' => $MVX->plugin_url.'assets/images/template3.png',
                            'width' => 50,
                            'height'=> 60,
                            'value' => 'template3'
                        ),
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'setup_wizard_introduction',
                    'type'      => 'wpeditor',
                    'class'     =>  'mvx-setting-wpeditor-class',
                    'label'     => __( 'Introduction step', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Add some introduction or welcome speech to your vendor. This section display in vendor store setup wizard introduction step.', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_dashboard_custom_css',
                    'type'      => 'textarea',
                    'label'     => __( 'Custom CSS', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Will be applicable on vendor frontend', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
            ],
            'dashbaord-pages'   => [
               
                [
                    'key'       => 'mvx_vendor',
                    'type'      => 'select',
                    'label'     => __( 'Vendor Dashboard', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Choose your preferred page for vendor dashboard', 'dc-woocommerce-multi-vendor' ),
                    'options' => $pages_array,
                    'database_value' => '',
                ],
                [
                    'key'       => 'vendor_registration',
                    'type'      => 'select',
                    'label'     => __( 'Vendor Dashboard', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Choose your preferred page for vendor registration', 'dc-woocommerce-multi-vendor' ),
                    'options' => $pages_array,
                    'database_value' => '',
                ],
            ],
            'dashbaord-endpoints'   => [
                [
                    'key'       => 'mvx_vendor_announcements_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Announcements', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor announcements page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-announcements', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_store_settings_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Storefront', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Used as site logo on vendor dashboard pages', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('storefront', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_profile_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Profile', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor profile management page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('profile', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_policies_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Policies', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor policies page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-policies', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_billing_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Billing', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor billing page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-billing', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_shipping_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Shipping', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor shipping page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-shipping', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_report_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Report', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor report page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-report', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_banking_overview_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Banking Overview', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor banking overview page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('banking-overview', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_add_product_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Add Product', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for add new product page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('add-product', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_edit_product_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Edit Product', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for edit product page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('edit-product', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_products_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Products List', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for products list page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('products', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_add_coupon_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Add Coupon', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for add new coupon page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('add-coupon', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_coupons_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Coupons List', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for coupons list page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('coupons', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_orders_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Orders', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor orders page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-orders', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_withdrawal_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Widthdrawals', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor widthdrawals page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-withdrawal', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_transaction_details_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Transaction Details', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for transaction details page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('transaction-details', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_knowledgebase_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Knowledgebase', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor knowledgebase page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-knowledgebase', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_tools_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Tools', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor tools page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('vendor-tools', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mvx_vendor_products_qnas_endpoint',
                    'type'      => 'text',
                    'label'     => __( 'Vendor Products Q&As', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Set endpoint for vendor products Q&As page', 'dc-woocommerce-multi-vendor' ),
                    'placeholder'   => __('products-qna', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],                
            ],
            'store-management'   => [
                [
                    'key'    => 'is_enable_store_sidebar',
                    'label'   => __( 'Enable Store Sidebar', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'hints' => __('If unchecked vendor will have access to backend', 'dc-woocommerce-multi-vendor'),
                    'options' => array(
                        array(
                            'key'=> "is_enable_store_sidebar",
                            'label'=> __('Uncheck this to disable vendor store sidebar..', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_enable_store_sidebar"
                        ),
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'store_sidebar_position',
                    'type'      => 'select',
                    'label'     => __( 'Store Sidebar Position', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'If you are not sure where to add widget, just go to admin <a href=".admin_url("widgets.php")." terget="_blank">widget</a> section and add your preferred widgets to <b>vendor store sidebar</b>.', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'key'=> "left",
                            'label'=> __('At Left', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('At Left', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "right",
                            'label'=> __('At Right', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('At Right', 'dc-woocommerce-multi-vendor'),
                        ),
                    ),
                    'database_value' => '',
                ],

                [
                    'key'    => 'store_follow_enabled',
                    'label'   => __( 'Enable Store Follow', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'hints' => __('If unchecked vendor will have access to backend', 'dc-woocommerce-multi-vendor'),
                    'options' => array(
                        array(
                            'key'=> "store_follow_enabled",
                            'label'=> __('Checked this to enable store follow.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "store_follow_enabled"
                        ),
                    ),
                    'database_value' => array(),
                ],

                [
                    'key'    => 'store_follow_section',
                    'label'   => __( '', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'section',
                    'database_value' => array(),
                ],

                [
                    'key'    => 'store_follow_enabled',
                    'label'   => __( 'Enable Store Follow', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'parent_class'  => 'mvx-toggle-checkbox-header',
                    'type'    => 'checkbox',
                    'hints' => __('Checked this to enable store follow..', 'dc-woocommerce-multi-vendor'),
                    'options' => array(
                        array(
                            'key'=> "store_follow_enabled",
                            'label'=> __('store follow 1', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('store details', 'dc-woocommerce-multi-vendor'),
                            'value'=> "store_follow_enabled"
                        ),
                        array(
                            'key'=> "store_follow_enabled1",
                            'label'=> __('store follow 2', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('store details', 'dc-woocommerce-multi-vendor'),
                            'value'=> "store_follow_enabled1"
                        ),
                        array(
                            'key'=> "store_follow_enabled2",
                            'label'=> __('store follow 3', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('store details', 'dc-woocommerce-multi-vendor'),
                            'value'=> "store_follow_enabled2"
                        ),
                        array(
                            'key'=> "store_follow_enabled3",
                            'label'=> __('store follow 4', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('store details', 'dc-woocommerce-multi-vendor'),
                            'value'=> "store_follow_enabled3"
                        ),
                        array(
                            'key'=> "store_follow_enabled4",
                            'label'=> __('store follow 5', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('store details', 'dc-woocommerce-multi-vendor'),
                            'value'=> "store_follow_enabled4"
                        ),
                        array(
                            'key'=> "store_follow_enabled5",
                            'label'=> __('store follow 6', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('store details', 'dc-woocommerce-multi-vendor'),
                            'value'=> "store_follow_enabled5"
                        ),
                        array(
                            'key'=> "store_follow_enabled6",
                            'label'=> __('store follow 7', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('store details', 'dc-woocommerce-multi-vendor'),
                            'value'=> "store_follow_enabled6"
                        ),
                        array(
                            'key'=> "store_follow_enabled7",
                            'label'=> __('store follow 8', 'dc-woocommerce-multi-vendor'),
                            'hints'=>   __('store details', 'dc-woocommerce-multi-vendor'),
                            'value'=> "store_follow_enabled7"
                        ),
                    ),
                    'database_value' => array(),
                ],
            ],
            'product-settings'  => [
                [
                    'key'       => 'type_options',
                    'type'      => 'toggle_rectangle',
                    'class'     => 'mvx-toggle-radio-switcher',
                    'label'     => __( 'Type Options ', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Type Options ', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'name'  => 'type_options',
                            'key' => 'virtual_type',
                            'label' => __('Virtual', 'dc-woocommerce-multi-vendor'),
                            'value' => 'virtual_type'
                        ),
                        array(
                            'name'  => 'type_options',
                            'key'   => 'download_type',
                            'label' => __('Downloadable', 'dc-woocommerce-multi-vendor'),
                            'value' => 'download_type'
                        )
                    ),
                    'database_value' => '',
                ],
            ],
            'product-capability'    => [
                [
                    'key'    => 'is_submit_product',
                    'label'   => __( 'Submit Products', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_submit_product",
                            'label'=> __('Allow vendors to submit products for approval/publishing.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_submit_product"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'is_published_product',
                    'label'   => __( 'Publish Products', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_published_product",
                            'label'=> __('If checked, products uploaded by vendors will be directly published without admin approval.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_published_product"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'is_edit_delete_published_product',
                    'label'   => __( 'Edit Published Products', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_edit_delete_published_product",
                            'label'=> __('Allow vendors to edit published products.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_edit_delete_published_product"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'publish_and_submit_products',
                    'label'   => __( 'Publish and Submit Products', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "publish_and_submit_products",
                            'label'=> __('Publish and Submit Products.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "publish_and_submit_products"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'is_submit_coupon',
                    'label'   => __( 'Submit Coupons', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_submit_coupon",
                            'label'=> __('Allow vendors to create coupons.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_submit_coupon"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'is_upload_files',
                    'label'   => __( 'Upload Media Files', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_upload_files",
                            'label'=> __('Allow vendors to upload media files.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_upload_files"
                        )
                    ),
                    'database_value' => array(),
                ],
            ],
            'spmv-pages'    => [
                [
                    'key'    => 'is_singleproductmultiseller',
                    'label'   => __( 'Allow Vendor to Copy Products', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_singleproductmultiseller",
                            'label'=> __('Let vendors search for product sold on your site and sell them from theirs', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_singleproductmultiseller"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'singleproductmultiseller_show_order',
                    'type'      => 'select',
                    'label'     => __( 'Shop Page product display based on', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => stripslashes(__('Select which SMPV Product to Display In the Shop Page', 'dc-woocommerce-multi-vendor')),
                    'options' => array(
                        array(
                            'key'=> "min-price",
                            'label'=> __('Min Price', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('min-price', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "max-price",
                            'label'=> __('Max Price', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('max-price', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "top-rated-vendor",
                            'label'=> __('Top rated vendor', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('top-rated-vendor', 'dc-woocommerce-multi-vendor'),
                        )
                    ),
                    'database_value' => '',
                ],
            ],
            'commission-configuration'  => [
                [
                    'key'       => 'revenue_sharing_mode',
                    'type'      => 'toggle_rectangle',
                    'class'     => 'mvx-toggle-radio-switcher',
                    'label'     => __( 'Revenue Sharing Mode', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Revenue Sharing Mode dashboard pages', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'name'  => 'revenue_sharing_mode',
                            'key' => 'revenue_sharing_mode_admin',
                            'label' => __('Admin fees', 'dc-woocommerce-multi-vendor'),
                            'value' => 'revenue_sharing_mode_admin'
                        ),
                        array(
                            'name'  => 'revenue_sharing_mode',
                            'key'   => 'revenue_sharing_mode_vendor',
                            'label' => __('Vendor commissions', 'dc-woocommerce-multi-vendor'),
                            'value' => 'revenue_sharing_mode_vendor'
                        )
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'commission_type',
                    'type'      => 'select',
                    'label'     => __( 'Commission Type', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Choose your preferred commission type. It will affect all commission calculations.', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'key'=> "choose_commission_type",
                            'label'=> __('Choose Commission Type', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('choose_commission_type', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "fixed",
                            'label'=> __('Fixed Amount', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('fixed', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "percent",
                            'label'=> __('Percentage', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('percent', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "fixed_with_percentage",
                            'label'=> __('%age + Fixed (per transaction)', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('fixed_with_percentage', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "fixed_with_percentage_qty",
                            'label'=> __('%age + Fixed (per unit)', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('fixed_with_percentage_qty', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "commission_by_product_price",
                            'label'=> __('Commission By Product Price', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('commission_by_product_price', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "commission_by_purchase_quantity",
                            'label'=> __('Commission By Purchase Quantity', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('commission_by_purchase_quantity', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "fixed_with_percentage_per_vendor",
                            'label'=> __('%age + Fixed (per vendor)', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('fixed_with_percentage_per_vendor', 'dc-woocommerce-multi-vendor'),
                        ),
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'default_commission',
                    'type'      => 'number',
                    'label'     => __( 'Commission Value', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __('This will be the default commission(in percentage or fixed) paid to vendors if product and vendor-specific commission is not set.', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'default_percentage',
                    'type'      => 'number',
                    'label'     => __( 'Commission Percentage', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __('This will be the default percentage paid to vendors if product and vendor specific commission is not set', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'fixed_with_percentage',
                    'type'      => 'number',
                    'label'     => __( 'Fixed Amount', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __('Fixed (per transaction)', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'fixed_with_percentage_qty',
                    'type'      => 'number',
                    'label'     => __( 'Fixed Amount', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __('Fixed (per unit)', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'fixed_with_percentage_per_vendor',
                    'type'      => 'number',
                    'label'     => __( 'Fixed Amount', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __('Fixed (per vendor)', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'    => 'payment_method_disbursement',
                    'label'   => __( 'Commission Disbursement Method', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "paypal_masspay",
                            'label'=> __('PayPal Masspay (Stop Waiting and Pay Vendors Immediately with PayPal Real-Time Split Payment using <a href="https://wc-marketplace.com/product/mvx-paypal-marketplace/">MVX PayPal Marketplace</a>. Please visit our site)', 'dc-woocommerce-multi-vendor'),
                            'value'=> "paypal_masspay"
                        ),
                        array(
                            'key'=> "paypal_payout",
                            'label'=> __('Paypal Payout', 'dc-woocommerce-multi-vendor'),
                            'value'=> "paypal_payout"
                        ),
                        array(
                            'key'=> "stripe_masspay",
                            'label'=> __('Stripe Connect', 'dc-woocommerce-multi-vendor'),
                            'value'=> "stripe_masspay"
                        ),
                        array(
                            'key'=> "direct_bank",
                            'label'=> __('Direct Bank Transfer', 'dc-woocommerce-multi-vendor'),
                            'value'=> "direct_bank"
                        ),
                        array(
                            'key'=> "razorpay_block",
                            'label'=> __('Razorpay', 'dc-woocommerce-multi-vendor'),
                            'value'=> "razorpay_block"
                        )
                    ),
                    'database_value' => array(),
                ],

                [
                    'key'       => 'payment_gateway_charge',
                    'label'     => __( 'Payment Gateway Charge', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'      => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "payment_gateway_charge",
                            'label'=> __('If checked, you can set payment gateway charge to the vendor for commission disbursement.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "payment_gateway_charge"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'gateway_charges_cost_carrier',
                    'type'      => 'select',
                    'label'     => __( 'Who bear the gateway charges', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __('Choose your preferred gateway charges carrier.', 'dc-woocommerce-multi-vendor'),
                    'options' => array(
                        array(
                            'key'=> "vendor",
                            'label'=> __('Vendor', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('vendor', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "admin",
                            'label'=> __('Site owner', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('admin', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "separate",
                            'label'=> __('Separately', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('separate', 'dc-woocommerce-multi-vendor'),
                        )
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'payment_gateway_charge_type',
                    'type'      => 'select',
                    'label'     => __( 'Gateway Charge Type', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __('Choose your preferred gateway charge type.', 'dc-woocommerce-multi-vendor'),
                    'options' => array(
                        array(
                            'key'=> "percent",
                            'label'=> __('Percentage', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('percent', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "fixed",
                            'label'=> __('Fixed Amount', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('fixed', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "fixed_with_percentage",
                            'label'=> __('%age + Fixed', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('fixed_with_percentage', 'dc-woocommerce-multi-vendor'),
                        )
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'masspay_percentage_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Percentage', 'dc-woocommerce-multi-vendor' ),
                    'desc'  => __('PayPal Masspay (Stop Waiting and Pay Vendors Immediately with PayPal Real-Time Split Payment using <a href="https://wc-marketplace.com/product/mvx-paypal-marketplace/">MVX PayPal Marketplace</a>. Please visit our site)', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'masspay_fixed_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Fixed', 'dc-woocommerce-multi-vendor' ),
                    'desc'  => __('PayPal Masspay (Stop Waiting and Pay Vendors Immediately with PayPal Real-Time Split Payment using <a href="https://wc-marketplace.com/product/mvx-paypal-marketplace/">MVX PayPal Marketplace</a>. Please visit our site)', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'payout_percentage_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Percentage', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'payout_fixed_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Fixed', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'stripe_percentage_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Percentage', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'stripe_fixed_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Fixed', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'bank_percentage_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Percentage', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'bank_fixed_gateway_charge',
                    'type'      => 'number',
                    'label'     => __( '', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __( 'In Fixed', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
            ],
       
            'review-management'   => [
                [
                    'key'    => 'is_sellerreview',
                    'label'   => __( 'Enable Vendor Review', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_sellerreview",
                            'label'=> __('Buyers can rate and review vendor.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_sellerreview"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'is_sellerreview_varified',
                    'label'   => __( 'Review only store users?', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_sellerreview_varified",
                            'label'=> __('Only buyers, purchased from the vendor can rate.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_sellerreview_varified"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'product_review_sync',
                    'label'   => __( 'Product review sync?', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "product_review_sync",
                            'label'=> __('Enable this to allow vendor\'s products review consider as store review.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "product_review_sync"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'mvx_review_categories',
                    'type'      => 'nested',
                    'label'     => __( 'Review Categories :', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Used as site logo on vendor dashboard pages', 'dc-woocommerce-multi-vendor' ),
                    'parent_options' => array(
                        array(
                            'key'=>'category',
                            'type'=> "text",
                            'class' => "nested-parent-class",
                            'name' => "nested-parent-name",
                            'label'=> __('category', 'dc-woocommerce-multi-vendor'),
                            'value'=> "category"
                        )
                    ),
                    'child_options' => array(
                    ),
                    'database_value' => $default_nested_data,
                ],
            ],
            'report-settings'   => [
                [
                    'key'       => 'custom_date_order_stat_report_mail',
                    'type'      => 'number',
                    'label'     => __( 'Set custom date for order stat report mail', 'dc-woocommerce-multi-vendor' ),
                    'hints'     => __( 'Email will send as per select dates ( put is blank for disabled it )', 'dc-woocommerce-multi-vendor' ),
                    'placeholder' => __('in days', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
            ],
            'tast-list' => [

            ],
            'store-review'  => [

            ],
            'report-abuse'  => [

            ],

            'admin_overview'  => [

            ],

            'vendor'  => [

            ],

            'product'  => [

            ],
            'transaction_history'  => [

            ],

            'policy'  => [
                [
                    'key'       => 'store-policy',
                    'type'      => 'wpeditor',
                    'class'     =>  'mvx-setting-wpeditor-class',
                    'desc'      => __('Site will reflect admin created policy. However vendors can edit and override store policies', 'dc-woocommerce-multi-vendor'),
                    'label'     => __( 'Store Policy', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'shipping_policy',
                    'type'      => 'wpeditor',
                    'class'     =>  'mvx-setting-wpeditor-class',
                    'desc'      => __('Site will reflect admin created policy. However vendors can edit and override store policies', 'dc-woocommerce-multi-vendor'),
                    'label'     => __( 'Shipping Policy', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'refund_policy',
                    'type'      => 'wpeditor',
                    'class'     =>  'mvx-setting-wpeditor-class',
                    'desc'      => __('Site will reflect admin created policy. However vendors can edit and override store policies', 'dc-woocommerce-multi-vendor'),
                    'label'     => __( 'Refund Policy', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'cancellation_policy',
                    'type'      => 'wpeditor',
                    'class'     =>  'mvx-setting-wpeditor-class',
                    'desc'      => __('Site will reflect admin created policy. However vendors can edit and override store policies', 'dc-woocommerce-multi-vendor'),
                    'label'     => __( 'Cancellation / Return / Exchange Policy', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
            ],
            'disbursement'  => [
                [
                    'key'    => 'commission_include_coupon',
                    'label'   => __( 'Who will bear the Coupon Cost', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "commission_include_coupon",
                            'label'=> __('Tap to let vendor bear the coupon discount charges created by them', 'dc-woocommerce-multi-vendor'),
                            'value'=> "commission_include_coupon"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'admin_coupon_excluded',
                    'label'   => __( 'Exclude Admin Created Coupon', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "admin_coupon_excluded",
                            'label'=> __('Bear the coupon discount charges of the coupons created by you', 'dc-woocommerce-multi-vendor'),
                            'value'=> "admin_coupon_excluded"
                        )
                    ),
                    'database_value' => array(),
                ],
                
                [
                    'key'    => 'give_tax',
                    'label'   => __( 'Tax', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "give_tax",
                            'label'=> __('Tap to let vendor collect & manage tax amount', 'dc-woocommerce-multi-vendor'),
                            'value'=> "give_tax"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'give_shipping',
                    'label'   => __( 'Shipping', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "give_shipping",
                            'label'=> __('Tap to let vendors collect shipping charges', 'dc-woocommerce-multi-vendor'),
                            'value'=> "give_shipping"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'choose_payment_mode_automatic_disbursal',
                    'label'   => __( 'Disbursement Schedule', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "choose_payment_mode_automatic_disbursal",
                            'label'=> __('Schedule when vendors would recive their commission', 'dc-woocommerce-multi-vendor'),
                            'value'=> "choose_payment_mode_automatic_disbursal"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'commission_threshold',
                    'label'   => __( 'Disbursement Threshold', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'number',
                    'desc'  =>  __('Add the minimum value required before payment is disbursed to the vendor', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'    => 'withdrawal_request',
                    'label'   => __( 'Allow Withdrawal Request', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "withdrawal_request",
                            'label'=> __('Let vendors withdraw payment prior to reaching the agreed disbursement value', 'dc-woocommerce-multi-vendor'),
                            'value'=> "withdrawal_request"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'commission_threshold_time',
                    'label'   => __( 'Withdrawal Locking Period', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'number',
                    'desc' => __('Refers to the minimum numbers of day required before a vendor can send withdrawal request', 'dc-woocommerce-multi-vendor'),
                    'placeholder'   => __('in days', 'dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'order_withdrawl_status',
                    'type'      => 'multi-select',
                    'label'     => __( 'Available Order Status for Withdrawal', 'dc-woocommerce-multi-vendor' ),
                    'desc'        => __( 'Withdrawal Request would be available in case of these Order Statuses', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'key'=> "on-hold",
                            'label'=> __('On hold', 'dc-woocommerce-multi-vendor'),
                            'value'=> "on-hold"
                        ),
                        array(
                            'key'=> "processing",
                            'label'=> __('Processing', 'dc-woocommerce-multi-vendor'),
                            'value'=> "processing"
                        ),
                        array(
                            'key'=> "completed",
                            'label'=> __('Completed', 'dc-woocommerce-multi-vendor'),
                            'value'=> "completed"
                        ),
                    ),
                    'database_value' => '',
                ]
            ],
            'suborder-configure'    => [
                [
                    'key'    => 'hide_suborder_for_customer',
                    'label'   => __( 'Hide Sub order for customers', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "hide_suborder_for_customer",
                            'label'=> __('If enabled customer cant see suborders', 'dc-woocommerce-multi-vendor'),
                            'value'=> "hide_suborder_for_customer"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'hide_suborder_for_admin',
                    'label'   => __( 'Hide Sub order for admin', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "hide_suborder_for_admin",
                            'label'=> __('If enabled admin cant see suborders', 'dc-woocommerce-multi-vendor'),
                            'value'=> "hide_suborder_for_admin"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'disallow_vendor_order_status',
                    'label'   => __( 'Disallow vendor to change order status', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "disallow_vendor_order_status",
                            'label'=> __('If enabled vendor can not chnage order status from frontend.', 'dc-woocommerce-multi-vendor'),
                            'value'=> "disallow_vendor_order_status"
                        )
                    ),
                    'database_value' => array(),
                ],
            ],
            'store-location' => [
                [
                    'key'    => 'enable_store_map_for_vendor',
                    'label'   => __( 'Enable store map for vendors', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "enable_store_map_for_vendor",
                            'label'=> __('', 'dc-woocommerce-multi-vendor'),
                            'value'=> "enable_store_map_for_vendor"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'choose_map_api',
                    'type'      => 'select',
                    'bydefault' =>  'google_map_set',
                    'label'     => __( 'Choose Your Map', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Choose your preferred map.', 'dc-woocommerce-multi-vendor' ),
                    'options' => array(
                        array(
                            'key'=> "google_map_set",
                            'label'=> __('Google map', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('google_map_set', 'dc-woocommerce-multi-vendor'),
                        ),
                        array(
                            'key'=> "mapbox_api_set",
                            'selected'  => true,
                            'label'=> __('Mapbox map', 'dc-woocommerce-multi-vendor'),
                            'value'=> __('mapbox_api_set', 'dc-woocommerce-multi-vendor'),
                        )
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'google_api_key',
                    'type'      => 'text',
                    'depend'    => 'choose_map_api',
                    'dependvalue'       =>  'google_map_set',
                    'label'     => __( 'Google Map API key', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __('<a href="https://developers.google.com/maps/documentation/javascript/get-api-key#get-an-api-key" target="_blank">Click here to generate key</a>','dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
                [
                    'key'       => 'mapbox_api_key',
                    'type'      => 'text',
                    'depend'    => 'choose_map_api',
                    'dependvalue'       =>  'mapbox_api_set',
                    'label'     => __( 'Mapbox access token', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __('<a href="https://docs.mapbox.com/help/getting-started/access-tokens/" target="_blank">Click here to generate access token</a>','dc-woocommerce-multi-vendor'),
                    'database_value' => '',
                ],
            ],
            'refund-management'   => [
                /*[
                    'key'    => 'disable_refund_customer_end',
                    'label'   => __( 'Enable refund request for customer', 'dc-woocommerce-multi-vendor' ),
                    'class'     => 'mvx-toggle-checkbox',
                    'type'    => 'checkbox',
                    'options' => array(
                        array(
                            'key'=> "disable_refund_customer_end",
                            'label'=> __('Remove capability to customer from refund request', 'dc-woocommerce-multi-vendor'),
                            'value'=> "disable_refund_customer_end"
                        )
                    ),
                    'database_value' => array(),
                ],*/
                [
                    'key'    => 'customer_refund_status',
                    'label'   => __( 'Available Status for Refund', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'checkbox',
                    'desc'  =>  __("Customers would be able to avail refund only if their order is at the following stage/s", 'dc-woocommerce-multi-vendor'),
                    'class'     => 'mvx-toggle-checkbox',
                    'options' => array(
                        array(
                            'key'=> "refund_method_pending",
                            'label'=> __('Pending', 'dc-woocommerce-multi-vendor'),
                            'value'=> "refund_method_pending"
                        ),
                        array(
                            'key'=> "refund_method_on-hold",
                            'label'=> __('On hold', 'dc-woocommerce-multi-vendor'),
                            'value'=> "refund_method_on-hold"
                        ),
                        array(
                            'key'=> "refund_method_processing",
                            'label'=> __('Processing', 'dc-woocommerce-multi-vendor'),
                            'value'=> "refund_method_processing"
                        ),
                        array(
                            'key'=> "refund_method_completed",
                            'label'=> __('Completed', 'dc-woocommerce-multi-vendor'),
                            'value'=> "refund_method_completed"
                        ),
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'refund_days',
                    'type'      => 'number',
                    'label'     => __( 'Refund Claim Period (In Days)', 'dc-woocommerce-multi-vendor' ),
                    'hints'     => __( 'The duration till which the refund request is available/valid', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'refund_order_msg',
                    'type'      => 'textarea',
                    'label'     => __( 'Reasons For Refund', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'Add reasons for refund. Use || to seperate reasons. Options will appear as a radion button to customers', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
            ],
            'payment-stripe_connect' => [
                [
                    'key'    => 'testmode',
                    'label'   => __( 'Enable Test Mode', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'checkbox',
                    'class'     => 'mvx-toggle-checkbox',
                    'options' => array(
                        array(
                            'key'=> "testmode",
                            'label'=> __('', 'dc-woocommerce-multi-vendor'),
                            'value'=> "testmode"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'       => 'test_client_id',
                    'type'      => 'label',
                    'label'     => __( 'Config redirect URI', 'dc-woocommerce-multi-vendor' ),
                    'valuename' => '<code>' . admin_url('admin-ajax.php') . "?action=marketplace_stripe_authorize". '</code>',
                    'desc' => '<a href="https://dashboard.stripe.com/account/applications/settings" target="_blank">'.__('Copy the URI and configured stripe redirect URI with above.', 'dc-woocommerce-multi-vendor').'</a>',
                    'database_value' => '',
                ],
                [
                    'key'       => 'test_client_id',
                    'type'      => 'text',
                    'label'     => __( 'Test Client ID', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'live_client_id',
                    'type'      => 'text',
                    'label'     => __( 'Live Client ID', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'test_publishable_key',
                    'type'      => 'text',
                    'label'     => __( 'Test Publishable key', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'live_publishable_key',
                    'type'      => 'text',
                    'label'     => __( 'Live Publishable key', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'test_secret_key',
                    'type'      => 'text',
                    'label'     => __( 'Test Secret key', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'live_secret_key',
                    'type'      => 'text',
                    'label'     => __( 'Live Secret key', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],

            ],
            'advance-buddypress' => [
                [
                    'key'    => 'profile_sync',
                    'label'   => __( 'Vendor Capability Sync', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'checkbox',
                    'class'     => 'mvx-toggle-checkbox',
                    'options' => array(
                        array(
                            'key'=> "profile_sync",
                            'label'=> __('Ignore if BuddyPress is not active', 'dc-woocommerce-multi-vendor'),
                            'value'=> "profile_sync"
                        )
                    ),
                    'database_value' => array(),
                ],
            ],
            'payment-payout' => [
                [
                    'key'       => 'client_id',
                    'type'      => 'text',
                    'label'     => __( 'Client ID', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'client_secret',
                    'type'      => 'text',
                    'label'     => __( 'Client Secret', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'    => 'is_asynchronousmode',
                    'label'   => __( 'Enable Asynchronous Mode', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'checkbox',
                    'class'     => 'mvx-toggle-checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_asynchronousmode",
                            'label'=> __('', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_asynchronousmode"
                        )
                    ),
                    'database_value' => array(),
                ],
                [
                    'key'    => 'is_testmode',
                    'label'   => __( 'Enable Test Mode', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'checkbox',
                    'class'     => 'mvx-toggle-checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_testmode",
                            'label'=> __('', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_testmode"
                        )
                    ),
                    'database_value' => array(),
                ],
            ],
            'payment-masspay' => [
                [
                    'key'       => 'api_username',
                    'type'      => 'text',
                    'label'     => __( 'API Username', 'dc-woocommerce-multi-vendor' ),
                    'hints'     => __( 'Number of Days for the refund period.', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'api_pass',
                    'type'      => 'text',
                    'label'     => __( 'API Password', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'api_signature',
                    'type'      => 'text',
                    'label'     => __( 'API Signature', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => '',
                ],
                [
                    'key'    => 'is_testmode',
                    'label'   => __( 'Enable Test Mode', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'checkbox',
                    'class'     => 'mvx-toggle-checkbox',
                    'options' => array(
                        array(
                            'key'=> "is_testmode",
                            'label'=> __('', 'dc-woocommerce-multi-vendor'),
                            'value'=> "is_testmode"
                        )
                    ),
                    'database_value' => array(),
                ],
            ],

            'vendor_personal' => [
                [
                    'key'       => 'user_login',
                    'type'      => 'text',
                    'label'     => __( 'Username (required)', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __('Usernames cannot be changed.', 'dc-woocommerce-multi-vendor'),
                    'props'     => array(
                        'required'  => true
                    ),
                    'database_value' => isset($user->user_login) ? $user->user_login : '',
                ],
                [
                    'key'       => 'password',
                    'type'      => 'password',
                    'label'     => __( 'Password', 'dc-woocommerce-multi-vendor' ),
                    'desc'     => __('Keep it blank for not to update.', 'dc-woocommerce-multi-vendor'),
                    'props'     => array(
                        'required'  => true
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'first_name',
                    'type'      => 'text',
                    'label'     => __( 'First Name', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => isset($user->first_name) ? $user->first_name : '',
                ],
                [
                    'key'       => 'last_name',
                    'type'      => 'text',
                    'label'     => __( 'Last Name', 'dc-woocommerce-multi-vendor' ),
                    'database_value' => isset($user->last_name) ? $user->last_name : '',
                ],
                [
                    'key'       => 'user_email',
                    'type'      => 'email',
                    'label'     => __( 'Email (required)', 'dc-woocommerce-multi-vendor' ),
                    'props'     => array(
                        'required'  => true
                    ),
                    'database_value' => isset($user->user_email) ? $user->user_email : '',
                ],
                [
                    'key'       => 'user_nicename',
                    'type'      => 'text',
                    'label'     => __( 'Nick Name (required)', 'dc-woocommerce-multi-vendor' ),
                    'props'     => array(
                        'required'  => true
                    ),
                    'database_value' => isset($user->user_nicename) ? $user->user_nicename : '',
                ],
                isset($_GET['name']) && $_GET['name'] == 'add_new' ? [] : [
                    'key'       => 'display_name',
                    'type'      => 'select',
                    'label'     => __( 'Display name', 'dc-woocommerce-multi-vendor' ),
                    'desc'      => __( 'If you are not sure where to add widget, just go to admin <a href=".admin_url("widgets.php")." terget="_blank">widget</a> section and add your preferred widgets to <b>vendor store sidebar</b>.', 'dc-woocommerce-multi-vendor' ),
                    'options' => $display_name_option,
                    'restricted_page'   => '?page=vendors&name=add_new',
                    'database_value' => isset($showdisplayname) ? $showdisplayname : '',
                ],
                [
                    'key'    => 'vendor_profile_image',
                    'label'   => __( 'Profile Image', 'dc-woocommerce-multi-vendor' ),
                    'type'    => 'file',
                    'width' =>  75,
                    'height'    => 75,
                    'database_value' => array(),
                ],
                
            ],
            'create_announcement'   =>  [
                [
                    'key'       => 'announcement_title',
                    'type'      => 'text',
                    'label'     => __( 'Title (required)', 'dc-woocommerce-multi-vendor' ),
                    'props'     => array(
                        'required'  => true
                    ),
                    'database_value' => '',
                ],
                [
                    'key'       => 'announcement_url',
                    'type'      => 'url',
                    'label'     => __( 'Enter Url', 'dc-woocommerce-multi-vendor' ),
                    'props'     => array(
                        'required'  => true
                    ),
                    'database_value' => '',
                ],
                [
                    'label' => __('Enter Content', 'dc-woocommerce-multi-vendor'),
                    'type' => 'wpeditor', 
                    'key' => 'announcement_content', 
                    'database_value' => ''
                ],
                [
                    'key'       => 'announcement_vendors',
                    'type'      => 'multi-select',
                    'label'     => __( 'Vendors', 'dc-woocommerce-multi-vendor' ),
                    'options' => ($MVX->vendor_rest_api->mvx_show_vendor_name()->data),
                    'database_value' => '',
                ]
            ],
            'update_announcement'   =>  [
                [
                    'key'       => 'announcement_title',
                    'type'      => 'text',
                    'label'     => __( 'Title (required)', 'dc-woocommerce-multi-vendor' ),
                    'props'     => array(
                        'required'  => true
                    ),
                    'database_value' => isset($announcement_title) ? $announcement_title : '',
                ],
                [
                    'key'       => 'announcement_url',
                    'type'      => 'url',
                    'label'     => __( 'Enter Url', 'dc-woocommerce-multi-vendor' ),
                    'props'     => array(
                        'required'  => true
                    ),
                    'database_value' => isset($announcement_url) ? $announcement_url : '',
                ],
                [
                    'label' => __('Enter Content', 'dc-woocommerce-multi-vendor'),
                    'type' => 'wpeditor', 
                    'key' => 'announcement_content', 
                    'database_value' => isset($announcement_content) ? $announcement_content : ''
                ],
                [
                    'key'       => 'announcement_vendors',
                    'type'      => 'multi-select',
                    'label'     => __( 'Vendors', 'dc-woocommerce-multi-vendor' ),
                    'options' => ($MVX->vendor_rest_api->mvx_show_vendor_name()->data),
                    'database_value' => '',
                ]
            ],
            'vendor_store' => [
                [
                    'label' => __('Store Name *', 'dc-woocommerce-multi-vendor'),
                    'type' => 'text',
                    'key' => 'vendor_page_title',
                    'database_value' => isset($user_vendor->page_title) ? $user_vendor->page_title : '' 
                ],
                [
                    'label' => __('Store Slug *', 'dc-woocommerce-multi-vendor'),
                    'type' => 'text',
                    'key' => 'vendor_page_slug',
                    'desc' => sprintf(__('Store URL will be something like - %s', 'dc-woocommerce-multi-vendor'), trailingslashit(get_home_url()) . 'vendor_slug'),
                    'database_value' => isset($user_vendor->page_slug) ? $user_vendor->page_slug : '',
                ],
                [
                    'label' => __('Store Description', 'dc-woocommerce-multi-vendor'),
                    'type' => 'wpeditor', 
                    'key' => 'vendor_description', 
                    'database_value' => isset($vendor_description) ? $vendor_description : ''
                ],

                [
                    'label' => __('Phone', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'number', 
                    'key' => 'vendor_phone', 
                    'database_value' => isset($vendor_phone) ? $vendor_phone : ''
                ],
                [
                    'label' => __('Address', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'text', 
                    'key' => 'vendor_address_1', 
                    'database_value' => isset($vendor_address_1) ? $vendor_address_1 : ''
                ],
                [
                    'label' => '', 
                    'type' => 'text', 
                    'key' => 'vendor_address_2', 
                    'database_value' => isset($vendor_address_2) ? $vendor_address_2 : ''
                ],
                [
                    'label' => __('Country', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'country', 
                    'key' => 'vendor_country', 
                    'class' => 'country_to_state regular-text', 
                    'options' => $country_list, 
                    'database_value' => isset($vendor_country_code_data) ? $vendor_country_code_data : ''
                ],
                [
                    'label' => __('State', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'state', 
                    'key' => 'vendor_state', 
                    'class' => 'regular-text', 
                    'options' => array(), 
                    'database_value' => isset($vendor_state_code_data) ? $vendor_state_code_data : ''
                ],
                [
                    'label' => __('City', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'text', 
                    'key' => 'vendor_city', 
                    'database_value' => isset($vendor_city) ? $vendor_city : ''
                ],
                [
                    'label' => __('ZIP code', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'text', 
                    'key' => 'vendor_postcode', 
                    'database_value' => isset($vendor_postcode) ? $vendor_postcode : ''
                ],
                [
                    'label' => __('Timezone', 'dc-woocommerce-multi-vendor'),
                    'type' => 'text', 
                    'key' => 'timezone_string',
                    'props'     => array(
                        'disabled'  => true
                    ),
                    'database_value' => isset($tzstring) ? $tzstring : '', 
                ],
            ],



            'vendor_social' => [
                [
                    'label' => __('Facebook', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'url', 
                    'key' => 'vendor_fb_profile', 
                    'database_value' => isset($vendor_fb_profile) ? $vendor_fb_profile : ''
                ],
                [
                    'label' => __('Twitter', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'url', 
                    'key' => 'vendor_twitter_profile', 
                    'database_value' => isset($vendor_twitter_profile) ? $vendor_twitter_profile : ''
                ],
                [
                    'label' => __('LinkedIn', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'url', 
                    'key' => 'vendor_linkdin_profile', 
                    'database_value' => isset($vendor_linkdin_profile) ? $vendor_linkdin_profile : ''
                ],
                [
                    'label' => __('YouTube', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'url', 
                    'key' => 'vendor_youtube', 
                    'database_value' => isset($vendor_youtube_profile) ? $vendor_youtube_profile : ''
                ],
                [
                    'label' => __('Instagram', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'url', 
                    'key' => 'vendor_instagram', 
                    'database_value' => isset($vendor_instagram_profile) ? $vendor_instagram_profile : ''
                ],
            ],
            'vendor_application' => [

            ],
            'vendor_shipping' => [

            ],
            'vendor_followers' => [

            ],

            'vendor_payments'   =>  [
                [
                    'key'       => 'vendor_payment_mode',
                    'type'      => 'select',
                    'label'     => __( 'Choose Payment Method', 'dc-woocommerce-multi-vendor' ),
                    'options'   => isset($vendor_payment_method_display_section) ? $vendor_payment_method_display_section : array(),
                    'database_value' => isset($showpayment_method) ? $showpayment_method : '',
                ],
                [
                    'label' => __('Commission Amount', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'number', 
                    'key' => 'vendor_commission',
                    'placeholder' => '0.00',
                    'database_value' => isset($commission_value) ? $commission_value : ''
                ],
                [
                    'label' => __('Paypal Email', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'text', 
                    'key' => 'vendor_paypal_email',
                    'placeholder' => '0.00',
                    'depend'    => 'vendor_payment_mode',
                    'dependvalue'       =>  'paypal_masspay',
                    'database_value' => isset($vendor_paypal_email) ? $vendor_paypal_email : ''
                ],
                [
                    'label' => __('Paypal Email', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'text', 
                    'key' => 'vendor_paypal_email',
                    'placeholder' => '0.00',
                    'depend'    => 'vendor_payment_mode',
                    'dependvalue'       =>  'paypal_payout',
                    'database_value' => isset($vendor_paypal_email) ? $vendor_paypal_email : ''
                ],

               /* [
                    'label' => __('Account type', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'select', 
                    'key' => 'vendor_bank_account_type', 
                    'label_for' => 'vendor_bank_account_type', 
                    'name' => 'vendor_bank_account_type', 
                    'options' => $vendor_bank_account_type_select, 
                    'database_value' => $vendor_obj->bank_account_type, 
                ],*/
                [
                    'label' => __('Bank Name', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'text',
                    'depend'    => 'vendor_payment_mode',
                    'dependvalue'       =>  'direct_bank',
                    'key' => 'vendor_bank_name', 
                    'database_value' => isset($vendor_bank_name) ? $vendor_bank_name : '' 
                ],

                [
                    'label' => __('ABA Routing Number', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'text',
                    'depend'    => 'vendor_payment_mode',
                    'dependvalue'       =>  'direct_bank',
                    'key' => 'vendor_aba_routing_number', 
                    'database_value' => isset($vendor_aba_routing_number) ? $vendor_aba_routing_number : ''
                ],

                [
                    'label' => __('Destination Currency', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'text',
                    'depend'    => 'vendor_payment_mode',
                    'dependvalue'       =>  'direct_bank',
                    'key' => 'vendor_destination_currency', 
                    'database_value' => isset($vendor_destination_currency) ? $vendor_destination_currency : ''
                ],
                [
                    'label' => __('Bank Address', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'textarea', 
                    'key' => 'vendor_bank_address', 
                    'depend'    => 'vendor_payment_mode',
                    'dependvalue'       =>  'direct_bank',
                    'rows'=>'6', 
                    'cols'=>'53', 
                    'database_value' => isset($vendor_bank_address) ? $vendor_bank_address : ''
                ],
                [
                    'label' => __('IBAN', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'text',
                    'depend'    => 'vendor_payment_mode',
                    'dependvalue'       =>  'direct_bank',
                    'key' => 'vendor_iban', 
                    'database_value' => isset($vendor_iban) ? $vendor_iban : ''
                ],
                [
                    'label' => __('Account Holder Name', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'text',
                    'depend'    => 'vendor_payment_mode',
                    'dependvalue'       =>  'direct_bank',
                    'key' => 'vendor_account_holder_name', 
                    'database_value' => isset($vendor_account_holder_name) ? $vendor_account_holder_name : ''
                ],
                [
                    'label' => __('Account Number', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'text',
                    'depend'    => 'vendor_payment_mode',
                    'dependvalue'       =>  'direct_bank',
                    'key' => 'vendor_bank_account_number', 
                    'database_value' => isset($vendor_bank_account_number) ? $vendor_bank_account_number : ''
                ],
            ],

            'vendor_policy' => [
                [
                    'label' => __('Shipping Policy', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'wpeditor', 
                    'key' => 'vendor_shipping_policy', 
                    'database_value' => isset($_vendor_shipping_policy) ? $_vendor_shipping_policy : ''
                ],
                [
                    'label' => __('Refund Policy', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'wpeditor', 
                    'key' => 'vendor_refund_policy', 
                    'database_value' => isset($_vendor_refund_policy) ? $_vendor_refund_policy : ''
                ],
                [
                    'label' => __('Cancellation/Return/Exchange Policy', 'dc-woocommerce-multi-vendor'),
                     'type' => 'wpeditor', 
                     'key' => 'vendor_cancellation_policy', 
                     'database_value' => isset($_vendor_cancellation_policy) ? $_vendor_cancellation_policy : ''
                 ],
            ],

            'distance_shipping' => [
                [
                    'label' => __('Default Cost', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'text', 
                    'key' => 'mvx_byd_default_cost',
                    'placeholder' => '0.00',
                    'database_value' => isset($mvx_shipping_by_distance['_default_cost']) ? $mvx_shipping_by_distance['_default_cost'] : ''
                ],
                [
                    'label' => __('Max Distance (km)', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'text',
                    'placeholder' => __('No Limit', 'dc-woocommerce-multi-vendor'), 
                    'key' => 'mvx_byd_max_distance', 
                    'database_value' => isset($mvx_shipping_by_distance['_max_distance']) ? $mvx_shipping_by_distance['_max_distance'] : ''
                ],
                [
                    'label' => __('Enable Local Pickup', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'checkbox', 
                    'key' => 'mvx_byd_enable_local_pickup',
                    'options' => array(
                        array(
                            'key'=> "mvx_byd_enable_local_pickup",
                            'label'=> __('', 'dc-woocommerce-multi-vendor'),
                            'value'=> "mvx_byd_enable_local_pickup"
                        ),
                    ),
                    'database_value' => isset($mvx_shipping_by_distance['_enable_local_pickup']) ? $mvx_shipping_by_distance['_enable_local_pickup'] : ''
                ],
                [
                    'label' => __('Local Pickup Cost', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'text', 
                    'key' => 'mvx_byd_local_pickup_cost', 
                    'placeholder' => '0.00',
                    'database_value' => isset($mvx_shipping_by_distance['_local_pickup_cost']) ? $mvx_shipping_by_distance['_local_pickup_cost'] : ''
                ],
                [
                    'key'       => 'mvx_shipping_by_distance_rates',
                    'type'      => 'nested',
                    'label'     => __( 'Distance-Cost Rules:', 'dc-woocommerce-multi-vendor' ),
                    'parent_options' => array(
                        array(
                            'key'=>'mvx_distance_rule',
                            'type'=> "select",
                            'class' => "nested-parent-class",
                            'name' => "nested-parent-name",
                            'label'=> __('Distance Rule', 'dc-woocommerce-multi-vendor'),
                            'options' => array(
                                array(
                                    'key'=> "up_to",
                                    'label'=> __('Distance up to', 'dc-woocommerce-multi-vendor'),
                                    'value'=> __('up_to', 'dc-woocommerce-multi-vendor'),
                                ),
                                array(
                                    'key'=> "more_than",
                                    'label'=> __('Distance more than', 'dc-woocommerce-multi-vendor'),
                                    'value'=> __('more_than', 'dc-woocommerce-multi-vendor'),
                                ),
                            ),
                        ),
                        array(
                            'key'   => 'mvx_distance_unit',
                            'type'  => "text",
                            'class' => "nested-parent-class",
                            'name'  => "nested-parent-name",
                            'label' => __('Distance', 'dc-woocommerce-multi-vendor') . ' ( '. __('km', 'dc-woocommerce-multi-vendor') .' )', 
                        ),
                        array(
                            'key'   => 'mvx_distance_price',
                            'type'  => "text",
                            'class' => "nested-parent-class",
                            'name'  => "nested-parent-name",
                            'label' => __('Cost', 'dc-woocommerce-multi-vendor') . ' ('.get_woocommerce_currency_symbol().')',
                        ),
                    ),
                    'child_options' => array(
                        
                    ),
                    'database_value' => isset($_GET['ID']) ? $shipping_distance_rate : $default_nested_data,
                ]
            ],

            'activity_reminder' =>  [],
            'announcement'  =>  [],
            'knowladgebase' =>  [],
            'store_review'  =>  [],
            'report_abuse'  =>  [],

            'country_shipping' => [

                [
                    'label' => __('Default Shipping Price', 'dc-woocommerce-multi-vendor'), 
                    'placeholder' => '0.00', 
                    'type' => 'text', 
                    'class' => 'col-md-6 col-sm-9', 
                    'key' => 'mvx_shipping_type_price', 
                    'database_value' => isset($mvx_shipping_by_country['_mvx_shipping_type_price']) ? $mvx_shipping_by_country['_mvx_shipping_type_price'] : '', 
                ],

                [
                    'label' => __('Per Product Additional Price', 'dc-woocommerce-multi-vendor'), 
                    'placeholder' => '0.00', 
                    'type' => 'text', 
                    'class' => 'col-md-6 col-sm-9', 
                    'key' => 'mvx_additional_product', 
                    'database_value' => isset($mvx_shipping_by_country['_mvx_additional_product']) ? $mvx_shipping_by_country['_mvx_additional_product'] : '',
                    'desc' => __('If a customer buys more than one type product from your store, first product of the every second type will be charged with this price', 'dc-woocommerce-multi-vendor') 
                ],

                [
                    'label' => __('Per Qty Additional Price', 'dc-woocommerce-multi-vendor'), 
                    'placeholder' => '0.00', 
                    'type' => 'text', 
                    'class' => 'col-md-6 col-sm-9', 
                    'key' => 'mvx_additional_qty', 
                    'database_value' => isset($mvx_shipping_by_country['_mvx_additional_qty']) ? $mvx_shipping_by_country['_mvx_additional_qty'] : '', 
                    'hints' => __('Every second product of same type will be charged with this price', 'dc-woocommerce-multi-vendor'),
                ],

                [
                    'label' => __('Free Shipping Minimum Order Amount', 'dc-woocommerce-multi-vendor'), 
                    'placeholder' => __( 'NO Free Shipping', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'text', 
                    'class' => 'col-md-6 col-sm-9', 
                    'key' => 'mvx_byc_free_shipping_amount', 
                    'database_value' => isset($mvx_shipping_by_country['_free_shipping_amount']) ? $mvx_shipping_by_country['_free_shipping_amount'] : '', 
                    'hints' => __('Free shipping will be available if order amount more than this. Leave empty to disable Free Shipping.', 'dc-woocommerce-multi-vendor') 
                ],

                [
                    'label' => __('Enable Local Pickup', 'dc-woocommerce-multi-vendor'), 
                    'type' => 'checkbox', 
                    'class' => 'mvx-checkbox mvx_ele', 
                    'key' => 'mvx_byc_enable_local_pickup', 
                    'options' => array(
                        array(
                            'key'=> "mvx_byc_enable_local_pickup",
                            'label'=> __('', 'dc-woocommerce-multi-vendor'),
                            'value'=> "mvx_byc_enable_local_pickup"
                        ),
                    ),
                    'database_value' => isset($mvx_shipping_by_country['_enable_local_pickup']) ? $mvx_shipping_by_country['_enable_local_pickup'] : '' 
                ],

                [
                    'label' => __('Local Pickup Cost', 'dc-woocommerce-multi-vendor'), 
                    'placeholder' => '0.00', 
                    'type' => 'text', 
                    'class' => 'col-md-6 col-sm-9', 
                    'key' => 'mvx_byc_local_pickup_cost', 
                    'database_value' => isset($mvx_shipping_by_country['_local_pickup_cost']) ? $mvx_shipping_by_country['_local_pickup_cost'] : '' 
                ],

                [
                    'key'       => 'mvx_country_shipping_rates',
                    'type'      => 'nested',
                    'label'     => __( 'Shipping Rates by Country', 'dc-woocommerce-multi-vendor' ),
                    'desc' => __( 'Add the countries you deliver your products to. You can specify states as well. If the shipping price is same except some countries, there is an option Everywhere Else, you can use that.', 'dc-woocommerce-multi-vendor' ),
                    'parent_options' => array(
                        array(
                            'key'       =>'mvx_country_to',
                            'type'      => "country",
                            'class'     => "nested-parent-class",
                            'name'      => "nested-parent-name",
                            'label'     => __('Country', 'dc-woocommerce-multi-vendor'),
                            'options'   => $country_list
                        ),
                        array(
                            'key'           => 'mvx_country_to_price',
                            'type'          => "text",
                            'class'         => "nested-parent-class",
                            'name'          => "nested-parent-name",
                            'placeholder'   => '0.00 (' . __('Free Shipping', 'dc-woocommerce-multi-vendor') . ')',
                            'label'         => __('Cost', 'dc-woocommerce-multi-vendor') . ' ('.get_woocommerce_currency_symbol().')',
                        ),
                    ),
                    'child_options' => array(
                        array(
                            'key'       =>'mvx_state_to',
                            'type'      => "state",
                            'class'     => "nested-parent-class",
                            'name'      => "nested-parent-name",
                            'label'     => __('State', 'dc-woocommerce-multi-vendor'),
                            'options'   => array()
                        ),
                        array(
                            'key'   => 'mvx_state_to_price',
                            'type'  => "text",
                            'class' => "nested-parent-class",
                            'name'  => "nested-parent-name",
                            'placeholder' => '0.00 (' . __('Free Shipping', 'dc-woocommerce-multi-vendor') . ')',
                            'label' => __('Cost', 'dc-woocommerce-multi-vendor') . ' ('.get_woocommerce_currency_symbol().')',
                        ),
                    ),
                    'database_value' => isset($_GET['ID']) ? $shipping_country_rate : $default_nested_data,
                ]
            ],
        ];

        $dashboard_page_endpoint = [
            [
                'tabname'       =>  'modules',
                'tablabel'      =>  __('Modules', 'dc-woocommerce-multi-vendor'),
                'classname'     =>  'form',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'modules',
            ],
            [
                'tabname'       =>  'help',
                'tablabel'      =>  __('Help', 'dc-woocommerce-multi-vendor'),
                'classname'     =>  'form',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'help',
            ],
            [
                'tabname'       =>  'setup-widget',
                'tablabel'      =>  __('Setup Widget', 'dc-woocommerce-multi-vendor'),
                'classname'     =>  'form',
                'link'          =>  admin_url( 'index.php?page=mvx-setup' ),
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'setup-widget',
            ],
            [
                'tabname'       =>  'migration',
                'tablabel'      =>  __('Migration', 'dc-woocommerce-multi-vendor'),
                'classname'     =>  'form',
                'link'          =>  admin_url('index.php?page=mvx-migrator'),
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'migration',
            ]
        ];

        $marketplace_manager_endpoint = [
            [
                'tabname'       =>  'tast-list',
                'tablabel'      =>  __('My Task List', 'dc-woocommerce-multi-vendor'),
                'classname'     =>  'form',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'tast-list',
            ],
            [
                'tabname'       =>  'store-review',
                'tablabel'      =>  __('Store Review', 'dc-woocommerce-multi-vendor'),
                'classname'     =>  'form',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'store-review',
            ],
            [
                'tabname'       =>  'report-abuse',
                'tablabel'      =>  __('Report Abuse', 'dc-woocommerce-multi-vendor'),
                'classname'     =>  'form',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'report-abuse',
            ]
        ];

        $general_settings_page_endpoint = array(
            array(
                'tabname'       =>  'settings-general',
                'tablabel'      =>  __('General', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-management',
                'modelname'     =>  'settings-general'
            ),
            array(
                'tabname'       =>  'social',
                'tablabel'      =>  __('Social', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-management',
                'modelname'     =>  'social'
            ),
            array(
                'tabname'       =>  'seller-dashbaord',
                'tablabel'      =>  __('Seller Dashbaord', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __("Manage the appearence of  Your Vendor's Dashboard", 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-management',
                'modelname'     =>  'seller-dashbaord'
            ),
            array(
                'tabname'       =>  'store',
                'tablabel'      =>  __('Store', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __("Shows Customer The Location of a Particular Store or Vendor", 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-management',
                'modelname'     =>  'store'
            ),
            array(
                'tabname'       =>  'products',
                'tablabel'      =>  __('Products', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __("Shows Customer The Location of a Particular products or Vendor", 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-management',
                'modelname'     =>  'products'
            ),
            array(
                'tabname'       =>  'products-capability',
                'tablabel'      =>  __('Products Capability', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __("Manage the Capabilities You Want Your Vendors to Have", 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-management',
                'modelname'     =>  'products-capability'
            ),
            array(
                'tabname'       =>  'spmv',
                'tablabel'      =>  __('SPMV', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __("Let's Your Vendor Publish Fellow Vendor's Product as Theirs", 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-spmv',
                'modelname'     =>  'spmv-pages'
            ),
            array(
                'tabname'       =>  'commissions',
                'tablabel'      =>  __('Commissions', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __("Configure Commission Settings to Customise Your Commission Plan", 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-spmv',
                'modelname'     =>  'commissions'
            ),
            array(
                'tabname'       =>  'disbursement',
                'tablabel'      =>  __('Disbursement', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __('Manage Payment and Disbursement', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-disbursement',
                'modelname'     =>  'disbursement'
            ),
            array(
                'tabname'       =>  'policy',
                'tablabel'      =>  __('Policy', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __('Add Policies For Your Site', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-policy',
                'modelname'     =>  'policy'
            ),
            array(
                'tabname'       =>  'refunds',
                'tablabel'      =>  __('Refunds', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-refunds',
                'modelname'     =>  'refund-management'
            ),
            array(
                'tabname'       =>  'registration',
                'tablabel'      =>  __('Registration Form', 'dc-woocommerce-multi-vendor'),
                'classname'     =>  'form',
                'description'   =>  __('Customise Your Own Seller Registration Form for Your Marketplace', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-registration',
            ),
            array(
                'tabname'       =>  'management',
                'tablabel'      =>  __('Dashbaord Management', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-management',
                'modelname'     =>  'dashbaord-management'
            ),
            array(
                'tabname'       =>  'dashpages',
                'tablabel'      =>  __('Dashbaord pages', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-dashpages',
                'modelname'     =>  'dashbaord-pages'
            ),
            array(
                'tabname'       =>  'dashendpoint',
                'tablabel'      =>  __('Dashbaord endpoint', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-dashendpoint',
                'modelname'     =>  'dashbaord-endpoints'
            ),
            array(
                'tabname'       =>  'storemanagement',
                'tablabel'      =>  __('Store Management', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-storemanagement',
                'modelname'     =>  'store-management'
            ),
            array(
                'tabname'       =>  'productsettings',
                'tablabel'      =>  __('Product Settings', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-productsettings',
                'modelname'     =>  'product-settings'
            ),
            array(
                'tabname'       =>  'productcapability',
                'tablabel'      =>  __('Products Capability', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-productcapability',
                'modelname'     =>  'product-capability'
            ),
            /*array(
                'tabname'       =>  'commissionconfiguration',
                'tablabel'      =>  __('Commission Configuration', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-commissionconfiguration',
                'modelname'     =>  'commission-configuration'
            ),*/
            
            

            array(
                'tabname'       =>  'suborderconfigure',
                'tablabel'      =>  __('Sub Order Configuration', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-suborderconfigure',
                'modelname'     =>  'suborder-configure'
            ),
            array(
                'tabname'       =>  'reportsettings',
                'tablabel'      =>  __('Report Settings', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-reportsettings',
                'modelname'     =>  'report-settings'
            ),
            array(
                'tabname'       =>  'reviewmanagement',
                'tablabel'      =>  __('Review Management', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-reviewmanagement',
                'modelname'     =>  'review-management'
            ),
            
            array(
                'tabname'       =>  'store-location',
                'tablabel'      =>  __('Store Location', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-store-location',
                'modelname'     =>  'store-location'
            ),
        );


        $payment_page_endpoint = array(
            array(
                'tabname'       =>  'paypal_masspay',
                'tablabel'      =>  __('PayPal Masspay', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-masspay',
                'modelname'     =>  'payment-masspay'
            ),
            array(
                'tabname'       =>  'paypal_payout',
                'tablabel'      =>  __('PayPal Payout', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-payout',
                'modelname'     =>  'payment-payout'
            ),
            array(
                'tabname'       =>  'stripe_connect',
                'tablabel'      =>  __('Stripe Connect', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-stripe_connect',
                'modelname'     =>  'payment-stripe_connect'
            )
        );

        $advance_page_endpoint = array(
            array(
                'tabname'       =>  'buddypress',
                'tablabel'      =>  __('Buddypress', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __('Default description', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-buddypress',
                'modelname'     =>  'advance-buddypress'
            )
        );

        $analytics_page_endpoint = array(
            array(
                'tabname'       =>  'admin_overview',
                'tablabel'      =>  __('Overview', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'modelname'     =>  'admin_overview'
            ),
            array(
                'tabname'       =>  'vendor',
                'tablabel'      =>  __('Vendor', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'modelname'     =>  'vendor'
            ),
            array(
                'tabname'       =>  'product',
                'tablabel'      =>  __('Product', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'modelname'     =>  'product'
            ),
            array(
                'tabname'       =>  'transaction_history',
                'tablabel'      =>  __('Transaction History', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'modelname'     =>  'transaction_history'
            )
        );

        $marketplace_vendors = array(
            array(
                'tabname'       =>  'vendor_personal',
                'tablabel'      =>  __('Personal', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'classname'     =>  'form',
                'modelname'     =>  'vendor_personal'
            ),
            array(
                'tabname'       =>  'vendor_store',
                'tablabel'      =>  __('Store', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'classname'     =>  'form',
                'modelname'     =>  'vendor_store'
            ),
            array(
                'tabname'       =>  'vendor_social',
                'tablabel'      =>  __('Social', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'classname'     =>  'form',
                'modelname'     =>  'vendor_social'
            ),
            array(
                'tabname'       =>  'vendor_payments',
                'tablabel'      =>  __('Payment', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'classname'     =>  'form',
                'modelname'     =>  'vendor_payments'
            ),
            array(
                'tabname'       =>  'vendor_application',
                'tablabel'      =>  __('Vendor Application', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'classname'     =>  'form',
                'modelname'     =>  'vendor_application'
            ),
            array(
                'tabname'       =>  'vendor_shipping',
                'tablabel'      =>  __('Vendor Shipping', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'classname'     =>  'form',
                'modelname'     =>  'vendor_shipping'
            ),
            array(
                'tabname'       =>  'vendor_followers',
                'tablabel'      =>  __('Vendor Followers', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'classname'     =>  'form',
                'modelname'     =>  'vendor_followers'
            ),
            array(
                'tabname'       =>  'vendor_policy',
                'tablabel'      =>  __('Vendor Policy', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'classname'     =>  'form',
                'modelname'     =>  'vendor_policy'
            ),

        );

        $marketplace_workboard = array(
            array(
                'tabname'       =>  'activity_reminder',
                'tablabel'      =>  __('Activity Reminder', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'classname'     =>  'form',
                'modelname'     =>  'activity_reminder'
            ),
            array(
                'tabname'       =>  'announcement',
                'tablabel'      =>  __('Announcement', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'classname'     =>  'form',
                'modelname'     =>  'announcement'
            ),
            array(
                'tabname'       =>  'knowladgebase',
                'tablabel'      =>  __('Knowladgebase', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'classname'     =>  'form',
                'modelname'     =>  'knowladgebase'
            ),
            array(
                'tabname'       =>  'store_review',
                'tablabel'      =>  __('Store Review', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'classname'     =>  'form',
                'modelname'     =>  'store_review'
            ),
            array(
                'tabname'       =>  'report_abuse',
                'tablabel'      =>  __('Report Abuse', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'classname'     =>  'form',
                'modelname'     =>  'report_abuse'
            )
        );

        $mvx_all_backend_tab_list = array(
            'dashboard-page'                    => $dashboard_page_endpoint,
            'marketplace-manager'               => $marketplace_manager_endpoint,
            'marketplace-advance-settings'      => $advance_page_endpoint,
            'marketplace-analytics'             => $analytics_page_endpoint,
            'marketplace-payments'              => $payment_page_endpoint,
            'marketplace-general-settings'      => $general_settings_page_endpoint,
            'marketplace-vendors'               => $marketplace_vendors,
            'marketplace-workboard'             => $marketplace_workboard
        );

        if (!empty($settings_fields)) {
            foreach ($settings_fields as $settings_key => $settings_value) {
                foreach ($settings_value as $inter_key => $inter_value) {
                    $option_name = 'mvx_'.$settings_key.'_tab_settings';
                    $database_value = get_option($option_name) ? get_option($option_name) : array();
                    if (!empty($database_value)) {
                        if (isset($inter_value['key']) && array_key_exists($inter_value['key'], $database_value)) {
                            if (empty($settings_fields[$settings_key][$inter_key]['database_value'])) {
                               $settings_fields[$settings_key][$inter_key]['database_value'] = $database_value[$inter_value['key']];
                            }
                        }
                    }
                }
            }
        }
        //print_r($screen->id);die;
        $page_details = array('mvx_page_modules', 'mvx_page_marketplace-analytics-settings', 'mvx_page_general-settings', 'mvx_page_payment-configuration', 'mvx_page_advance-marketplace-settings', 'mvx_page_marketplace-manager-settings', 'mvx_page_vendors', 'mvx_page_commission', 'mvx_page_work_board');
        wp_enqueue_script(
            'mvx-modules-build-frontend',
            $MVX->plugin_url . 'mvx-modules/build/index.js',
            ['wp-element'],
            time(),
            true
        );

        $commission_bulk_list_action = array();
        $commission_bulk_list = array(
            'mark_paid' => __('Mark paid', 'dc-woocommerce-multi-vendor'),
            //'export' => __('Export', 'dc-woocommerce-multi-vendor')
        );
        if ($commission_bulk_list) {
            foreach($commission_bulk_list as $bulk_key => $bulk_value) {
                $commission_bulk_list_action[] = array(
                    'value' => $bulk_key,
                    'label' => $bulk_value
                );
            }
        }

        // Commission header
        $commission_header = [];
        $headers = apply_filters('mvx_vendor_commission_data_header',array(
            'Recipient',
            'Currency',
            'Commission',
            'Shipping',
            'Tax',
            'Total',
            'Status',
        ));
        foreach ($headers as $headerskey => $headersvalue) {
            $commission_header[] = array(
                'label' => $headersvalue,
                'key' => $headersvalue
            );
        }

        $commission_status_list_action = array();
        $commission_status = mvx_get_commission_statuses();
        foreach ($commission_status as $status_key => $status_value) {
            $commission_status_list_action[] = array(
                'value' => $status_key,
                'label' => $status_value
            );
        }

        $commission_page_string     =   array(
            'details'   =>  __('details', 'dc-woocommerce-multi-vendor'),
            'general'   =>  __('General', 'dc-woocommerce-multi-vendor'),
            'associated_order'   =>  __('Associated order', 'dc-woocommerce-multi-vendor'),
            'order_status'   =>  __('Order status', 'dc-woocommerce-multi-vendor'),
            'commission_status'   =>  __('Commission Status', 'dc-woocommerce-multi-vendor'),
            'vendor_details'   =>  __('Vendor details', 'dc-woocommerce-multi-vendor'),
            'email'   =>  __('Email', 'dc-woocommerce-multi-vendor'),
            'payment_mode'   =>  __('Payment mode', 'dc-woocommerce-multi-vendor'),
            'commission_data'   =>  __('Commission data', 'dc-woocommerce-multi-vendor'),
            'commission_amount'   =>  __('Commission amount', 'dc-woocommerce-multi-vendor'),
            'shipping'   =>  __('Shipping', 'dc-woocommerce-multi-vendor'),
            'tax'   =>  __('Tax', 'dc-woocommerce-multi-vendor'),
            'commission'   =>  __('Commission', 'dc-woocommerce-multi-vendor'),
            'total'   =>  __('Total', 'dc-woocommerce-multi-vendor'),
            'refunded'   =>  __('Refunded', 'dc-woocommerce-multi-vendor'),
            'commission_notes'   =>  __('Commission Notes', 'dc-woocommerce-multi-vendor'),
            'search_commission'   =>  __('Search Commission', 'dc-woocommerce-multi-vendor'),
            'show_commission_status'   =>  __('Show Commission Status', 'dc-woocommerce-multi-vendor'),
            'show_all_vendor'   =>  __('Show All Vendor', 'dc-woocommerce-multi-vendor'),
            'bulk_action'   =>  __('Bulk Action', 'dc-woocommerce-multi-vendor'),
        );

        $report_page_string = array(
            'vendor_select' =>  __('Select your vendor to view transaction details', 'dc-woocommerce-multi-vendor'),
            'choose_vendor' =>  __('Search Vendors', 'dc-woocommerce-multi-vendor'),
            'choose_product'    =>  __('Search Product', 'dc-woocommerce-multi-vendor'),
            'performance'    =>  __('Performance', 'dc-woocommerce-multi-vendor'),
            'charts'    =>  __('Charts', 'dc-woocommerce-multi-vendor'),
            'net_sales'    =>  __('Charts', 'dc-woocommerce-multi-vendor'),
            'order_count'    =>  __('Order Count', 'dc-woocommerce-multi-vendor'),
            'item_sold'    =>  __('Item Sold', 'dc-woocommerce-multi-vendor'),
            'download_csv'  =>  __('Download CSV', 'dc-woocommerce-multi-vendor'),
            'leaderboards'  =>  __('Leaderboards', 'dc-woocommerce-multi-vendor')
        );

        // product report chart data
        $report_product_header = [];
        $headers = apply_filters('mvx_vendor_commission_data_header',array(
            __('Product Name', 'dc-woocommerce-multi-vendor'),
            __('Net Sales', 'dc-woocommerce-multi-vendor'),
            __('Admin Earning', 'dc-woocommerce-multi-vendor'),
            __('Vendor Earning', 'dc-woocommerce-multi-vendor'),
        ));
        foreach ($headers as $headerskey => $headersvalue) {
            $report_product_header[] = array(
                'label' => $headersvalue,
                'key' => $headersvalue
            );
        }

        // vendor report chart data
        $report_vendor_header = [];
        $headers = apply_filters('mvx_vendor_commission_data_header',array(
            __('Vendor Name', 'dc-woocommerce-multi-vendor'),
            __('Net Sales', 'dc-woocommerce-multi-vendor'),
            __('Admin Earning', 'dc-woocommerce-multi-vendor'),
            __('Vendor Earning', 'dc-woocommerce-multi-vendor'),
        ));
        foreach ($headers as $headerskey => $headersvalue) {
            $report_vendor_header[] = array(
                'label' => $headersvalue,
                'key' => $headersvalue
            );
        }


        wp_localize_script( 'mvx-modules-build-frontend', 'appLocalizer', [
            'apiUrl' => home_url( '/wp-json' ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'mvx_logo' => $MVX->plugin_url.'assets/images/dclogo.png',
            'multivendor_logo' => $MVX->plugin_url.'assets/images/multivendorX.png',
            'knowledgebase' => 'https://wc-marketplace.com/knowledgebase/',
            'knowledgebase_title' => __('MVX knowledge Base', 'dc-woocommerce-multi-vendor'),
            'marketplace_text' => __('Multivendor X', 'dc-woocommerce-multi-vendor'),
            'search_module_placeholder' => __('Search Modules', 'dc-woocommerce-multi-vendor'),
            'pro_text' => __('PRO', 'dc-woocommerce-multi-vendor'),
            'documentation_extra_text' => __('For more info, please check the', 'dc-woocommerce-multi-vendor'),
            'documentation_text' => __('DOC', 'dc-woocommerce-multi-vendor'),
            'settings_text' => __('Settings', 'dc-woocommerce-multi-vendor'),
            'admin_mod_url' => admin_url('admin.php?page=modules'),
            'admin_setup_widget_option' => admin_url( 'index.php?page=mvx-setup' ),
            'admin_migration_widget_option' => admin_url( 'index.php?page=mvx-setup' ),
            'multivendor_migration_link' => admin_url('index.php?page=mvx-migrator'),
            'settings_fields' => $settings_fields,
            'countries'                 => wp_json_encode( array_merge( WC()->countries->get_allowed_country_states(), WC()->countries->get_shipping_country_states() ) ),
            'mvx_all_backend_tab_list' => $mvx_all_backend_tab_list,
            'default_logo'  => $MVX->plugin_url.'assets/images/WP-stdavatar.png',
            'right_logo'    => $MVX->plugin_url.'assets/images/right_tick.jpg',
            'cross_logo'    => $MVX->plugin_url.'assets/images/cross_tick.png',
            'commission_bulk_list_option'   =>  $commission_bulk_list_action,
            'commission_header' => $commission_header,
            'shipping_options'  => $shipping_options_list,
            'vendor_default_shipping_options'   => $vendor_default_shipping_options,
            'commission_status_list_action' =>  $commission_status_list_action,
            'commission_page_string'    =>  $commission_page_string,
            'report_product_header' =>  $report_product_header,
            'report_vendor_header'  =>  $report_vendor_header,
            'report_page_string'    =>  $report_page_string
        ] );

        if ( in_array($screen->id, $page_details)) {
            wp_enqueue_style('mvx_admin_css', $MVX->plugin_url . 'assets/admin/css/admin' . '' . '.css', array(), $MVX->version);
            wp_enqueue_style('mvx_admin_rsuite_css', $MVX->plugin_url . 'assets/admin/css/rsuite-default' . '.min' . '.css', array(), $MVX->version);
        }

        
        $mvx_admin_screens = apply_filters('mvx_enable_admin_script_screen_ids', array(
            'dc_commission',
            'product',
            'edit-product',
            'edit-shop_order',
            'user-edit',
            'profile',
            'users',
            'toplevel_page_dc-vendor-shipping',
            'widgets',
	    ));

        // hide media list view access for vendor
        $user = wp_get_current_user();
        if(in_array('dc_vendor', $user->roles)){
            $custom_css = "
            .view-switch .view-list{
                    display: none;
            }";
            wp_add_inline_style( 'media-views', $custom_css );
        }
   

        if (in_array($screen->id, array('user-edit', 'profile'))) :
            $MVX->library->load_qtip_lib();
            $MVX->library->load_upload_lib();
            wp_enqueue_script('edit_user_js');
        endif;

        if (in_array($screen->id, array('users'))) :
            wp_enqueue_script('dc_users_js');
        endif;


        if (is_user_mvx_vendor(get_current_vendor_id())) {
            wp_enqueue_script('mvx_vendor_js');
        }
        
        // hide coupon allow free shipping option for vendor
        if (is_user_mvx_vendor(get_current_vendor_id())) {
            $custom_css = "
            #general_coupon_data .free_shipping_field{
                    display: none;
            }";
            wp_add_inline_style( 'woocommerce_admin_styles', $custom_css );
            wp_enqueue_script('mvx_vendor_js');
        }
        
        // hide product cat from quick & bulk edit
        if(is_user_mvx_vendor(get_current_vendor_id()) && in_array($screen->id, array('edit-product'))){
            $custom_css = "
            .inline-edit-product .inline-edit-categories, .bulk-edit-product .inline-edit-categories{
                display: none;
            }";
            wp_add_inline_style( 'woocommerce_admin_styles', $custom_css );
        }        
    }

    function mvx_kill_auto_save() {
        if ('product' == get_post_type()) {
            wp_dequeue_script('autosave');
        }
    }

    /**
     * Remove wp dashboard widget for vendor
     * @global array $wp_meta_boxes
     */
    public function mvx_remove_wp_dashboard_widget() {
        global $wp_meta_boxes;
        if (is_user_mvx_vendor(get_current_vendor_id())) {
            unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);
            unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
            unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
            unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
        }
    }

    public function woocommerce_order_actions($actions) {
        global $post;
        if( $post && wp_get_post_parent_id( $post->ID ) )
            $actions['regenerate_order_commissions'] = __('Regenerate order commissions', 'dc-woocommerce-multi-vendor');
        if( $post && !wp_get_post_parent_id( $post->ID ) )
            $actions['regenerate_suborders'] = __('Regenerate suborders', 'dc-woocommerce-multi-vendor');
        if(is_user_mvx_vendor(get_current_user_id())){
            if(isset($actions['regenerate_order_commissions'])) unset($actions['regenerate_order_commissions']);
            if(isset($actions['send_order_details'])) unset( $actions['send_order_details'] );
            if(isset($actions['send_order_details_admin'])) unset( $actions['send_order_details_admin'] );
            if(isset($actions['regenerate_suborders'])) unset($actions['regenerate_suborders']);
        }
        return $actions;
    }

    /**
     * Regenerate order commissions
     * @param Object $order
     * @since 3.0.2
     */
    public function regenerate_order_commissions($order) {
        global $wpdb, $MVX;
        if ( !wp_get_post_parent_id( $order->get_id() ) ) {
            return;
        }
        if (!in_array($order->get_status(), apply_filters( 'mvx_regenerate_order_commissions_statuses', array( 'on-hold', 'processing', 'completed' ), $order ))) {
            return;
        }
        
        delete_post_meta($order->get_id(), '_commissions_processed');
        $commission_id = get_post_meta($order->get_id(), '_commission_id', true) ? get_post_meta($order->get_id(), '_commission_id', true) : '';
        if ($commission_id) {
            wp_delete_post($commission_id, true);
        }
        delete_post_meta($order->get_id(), '_commission_id');
        // create vendor commission
        $commission_id = MVX_Commission::create_commission($order->get_id());
        if ($commission_id) {
            // Add order note
            $order->add_order_note( __( 'Regenerated order commission.', 'dc-woocommerce-multi-vendor') );
            /**
             * Action filter to recalculate commission with modified settings.
             *
             * @since 3.5.0
             */
            $recalculate = apply_filters( 'mvx_regenerate_order_commissions_by_new_commission_rate', false, $order );
            // Calculate commission
            MVX_Commission::calculate_commission($commission_id, $order, $recalculate);
            update_post_meta($commission_id, '_paid_status', 'unpaid');

            // add commission id with associated vendor order
            update_post_meta($order->get_id(), '_commission_id', absint($commission_id));
            // Mark commissions as processed
            update_post_meta($order->get_id(), '_commissions_processed', 'yes');
        }
    }

    public function regenerate_suborders($order) {
        global $MVX;
        $MVX->order->mvx_manually_create_order_item_and_suborder($order->get_id(), '', true);
    }
    
    public function add_mvx_screen_ids($screen_ids){
        $screen_ids[] = 'toplevel_page_dc-vendor-shipping';
        return $screen_ids;
    }

    public function mvx_vendor_shipping_admin_capability($current_id){
        if( !is_user_mvx_vendor($current_id) ){
            if( isset($_POST['vendor_id'] )){
                $current_id = isset($_POST['vendor_id']) ? absint($_POST['vendor_id']) : 0;
            } else {
                $current_id = isset($_GET['ID']) ? absint($_GET['ID']) : 0;
            }
        } 
        return $current_id;
    }

    public function woocommerce_admin_end_order_menu_count( $processing_orders ) {
        $args = array(
        'post_status' => array('wc-processing'),
        );
        $sub_orders = mvx_get_orders( $args, 'ids', true );
        if( empty( $sub_orders ) )
            $sub_orders = array();

        $processing_orders = count(wc_get_orders(array(
            'status'  => 'processing',
            'return'  => 'ids',
            'limit'   => -1,
            'exclude' => $sub_orders,
            )));

        return $processing_orders;
    }

}
