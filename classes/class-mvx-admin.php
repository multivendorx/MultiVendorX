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

        // get all settings fileds
        $settings_fields = mvx_admin_backend_settings_fields_details();


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
        }


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

        /*$marketplace_manager_endpoint = [
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
        ];*/

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
                'tabname'       =>  'registration',
                'tablabel'      =>  __('Registration Form', 'dc-woocommerce-multi-vendor'),
                'classname'     =>  'form',
                'description'   =>  __('Customise Your Own Seller Registration Form for Your Marketplace', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-registration',
                'modelname'     =>  'registration'
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
                'tabname'       =>  'reviewmanagement',
                'tablabel'      =>  __('Reviews & Rating', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __('Manage Settings For Product and Store Review', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-reviewmanagement',
                'modelname'     =>  'review-management'
            ),
        );


        $payment_page_endpoint = array(
            array(
                'tabname'       =>  'paypal_masspay',
                'tablabel'      =>  __('PayPal Masspay', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __('Schedule payment to multiple vendors at the same time', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-masspay',
                'modelname'     =>  'payment-masspay'
            ),
            array(
                'tabname'       =>  'paypal_payout',
                'tablabel'      =>  __('PayPal Payout', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __('Send payments automatically to multiple vendors as per scheduled', 'dc-woocommerce-multi-vendor'),
                'activeclass'   =>  'settings-active-payout',
                'modelname'     =>  'payment-payout'
            ),
            array(
                'tabname'       =>  'stripe_connect',
                'tablabel'      =>  __('Stripe Connect', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'description'   =>  __('Connect to vendors stripe account and make hassle-free transfers as scheduled', 'dc-woocommerce-multi-vendor'),
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
                'description'   =>  __('View the Overall Performance of The Site', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'modelname'     =>  'admin_overview'
            ),
            array(
                'tabname'       =>  'vendor',
                'tablabel'      =>  __('Vendor', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('Get Reports on Vendor Sales', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'modelname'     =>  'vendor'
            ),
            array(
                'tabname'       =>  'product',
                'tablabel'      =>  __('Product', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('View Porduct Sales', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/save_dashpages',
                'classname'     =>  'form',
                'modelname'     =>  'product'
            ),
            array(
                'tabname'       =>  'transaction_history',
                'tablabel'      =>  __('Transaction History', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('Get Detailed Reports On Vendor Commission', 'dc-woocommerce-multi-vendor'),
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
                'description'   =>  __('Keeps track of all important marketplace chores', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'classname'     =>  'form',
                'modelname'     =>  'activity_reminder'
            ),
            array(
                'tabname'       =>  'announcement',
                'tablabel'      =>  __('Announcement', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('Announcements are visible only to vendors through the vendor dashboard(message section). You may use this section to broadcast your announcements.', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'classname'     =>  'form',
                'modelname'     =>  'announcement'
            ),
            array(
                'tabname'       =>  'knowladgebase',
                'tablabel'      =>  __('Knowladgebase', 'dc-woocommerce-multi-vendor'),
                'description'   =>  __('"Knowledgebase" section is visible only to vendors through the vendor dashboard. You may use this section to onboard your vendors. Share tutorials, best practices, "how to" guides or whatever you feel is appropriate with your vendors.', 'dc-woocommerce-multi-vendor'),
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
            ),
            array(
                'tabname'       =>  'question_ans',
                'tablabel'      =>  __('Question & Answer', 'dc-woocommerce-multi-vendor'),
                'apiurl'        =>  'mvx_module/v1/update_vendor',
                'classname'     =>  'form',
                'modelname'     =>  'question_ans'
            )
        );

        $mvx_all_backend_tab_list = array(
            'dashboard-page'                    => $dashboard_page_endpoint,
            //'marketplace-manager'               => $marketplace_manager_endpoint,
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
        $page_details = array('toplevel_page_mvx');
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
            'email'   =>  __('Email Address', 'dc-woocommerce-multi-vendor'),
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

        $post_bulk_status = array(
                array(
                    'value' => 'pending',
                    'label' => __('Pending', 'dc-woocommerce-multi-vendor')
                ),
                array(
                    'value' => 'published',
                    'label' => __('Published', 'dc-woocommerce-multi-vendor')
                ),
            );


        wp_localize_script( 'mvx-modules-build-frontend', 'appLocalizer', [
            'apiUrl' => home_url( '/wp-json' ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'mvx_logo' => $MVX->plugin_url.'assets/images/dclogo.png',
            'multivendor_logo' => $MVX->plugin_url.'assets/images/multivendorX.png',
            'knowledgebase' => 'https://wc-marketplace.com/knowledgebase/',
            'knowledgebase_title' => __('MVX knowledge Base', 'dc-woocommerce-multi-vendor'),
            'marketplace_text' => __('MultiVendorX', 'dc-woocommerce-multi-vendor'),
            'search_module_placeholder' => __('Search Modules', 'dc-woocommerce-multi-vendor'),
            'pro_text' => __('PRO', 'dc-woocommerce-multi-vendor'),
            'documentation_extra_text' => __('For more info, please check the', 'dc-woocommerce-multi-vendor'),
            'documentation_text' => __('DOC', 'dc-woocommerce-multi-vendor'),
            'settings_text' => __('Settings', 'dc-woocommerce-multi-vendor'),
            'admin_mod_url' => admin_url('admin.php?page=modules'),
            'admin_setup_widget_option' => admin_url( 'index.php?page=mvx-setup' ),
            'admin_migration_widget_option' => admin_url( 'index.php?page=mvx-setup' ),
            'multivendor_migration_link' => admin_url('index.php?page=mvx-migrator'),
            'settings_fields' => apply_filters('mvx-settings-fileds-details', $settings_fields),
            'countries'                 => wp_json_encode( array_merge( WC()->countries->get_allowed_country_states(), WC()->countries->get_shipping_country_states() ) ),
            'mvx_all_backend_tab_list' => $mvx_all_backend_tab_list,
            'default_logo'                  => $MVX->plugin_url.'assets/images/WP-stdavatar.png',
            'right_logo'                    => $MVX->plugin_url.'assets/images/right_tick.jpg',
            'cross_logo'                    => $MVX->plugin_url.'assets/images/cross_tick.png',
            'commission_bulk_list_option'   =>  $commission_bulk_list_action,
            'commission_header'             => $commission_header,
            //'shipping_options'  => $shipping_options_list,
            //'vendor_default_shipping_options'   => $vendor_default_shipping_options,
            'commission_status_list_action' =>  $commission_status_list_action,
            'commission_page_string'        =>  $commission_page_string,
            'report_product_header'         =>  $report_product_header,
            'report_vendor_header'          =>  $report_vendor_header,
            'report_page_string'            =>  $report_page_string,
            'post_bulk_status'              =>  $post_bulk_status
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
