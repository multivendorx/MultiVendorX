<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * MVX Admin Class
 *
 * @version     2.2.0
 * @package     MVX
 * @author      Multivendor X
 */
class MVX_Admin {

    public $settings;

    public function __construct() {
        // Admin script and style
        add_action('admin_enqueue_scripts', array(&$this, 'enqueue_admin_script'), 30);
        add_action('admin_bar_menu', array(&$this, 'add_toolbar_items'), 100);
        add_action('current_screen', array($this, 'conditonal_includes'));
        if (mvx_is_module_active('spmv') && get_mvx_vendor_settings('is_singleproductmultiseller', 'spmv_pages')) {
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
        if (!get_option('_is_dismiss_mvx40_notice', false) && current_user_can('manage_options')) {
            add_action('admin_notices', array(&$this, 'mvx_service_page_notice'));
        }
    }

    /**
     * Display MVX service notice in admin panel
     */
    public function mvx_service_page_notice() {
        ?>
        <div class="updated mvx_admin_new_banner">
            <div class="round"></div>
            <div class="round1"></div>
            <div class="round2"></div>
            <div class="round3"></div>
            <div class="round4"></div>
            <div class="mvx_banner-content">
                <span class="txt"><?php esc_html_e('Your settings migration cron is running. Please wait.', 'multivendorx') ?>  </span>
                <div class="rightside">        
                    <a href="https://wc-marketplace.com/latest-release/" target="_blank" class="mvx_btn_service_claim_now"><?php esc_html_e('Checkout latest release', 'multivendorx'); ?></a>
                    <button onclick="dismiss_servive_notice(event);" type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
                </div>

            </div>
        </div>
        <style type="text/css">.clearfix{clear:both}.mvx_admin_new_banner.updated{border-left:0}.mvx_admin_new_banner{box-shadow:0 3px 1px 1px rgba(0,0,0,.2);padding:10px 30px;background:#fff;position:relative;overflow:hidden;clear:both;border-top:2px solid #8abee5;text-align:left;background-size:contain}.mvx_admin_new_banner .round{width:200px;height:200px;position:absolute;border-radius:100%;border:30px solid rgba(157,42,255,.05);top:-150px;left:73px;z-index:1}.mvx_admin_new_banner .round1{position:absolute;border-radius:100%;border:45px solid rgba(194,108,144,.05);bottom:-82px;right:-58px;width:180px;height:180px;z-index:1}.mvx_admin_new_banner .round2,.mvx_admin_new_banner .round3{border-radius:100%;width:180px;height:180px;position:absolute;z-index:1}.mvx_admin_new_banner .round2{border:18px solid rgba(194,108,144,.05);top:35px;left:249px}.mvx_admin_new_banner .round3{border:45px solid rgba(31,194,255,.05);top:2px;right:40%}.mvx_admin_new_banner .round4{position:absolute;border-radius:100%;border:31px solid rgba(31,194,255,.05);top:11px;left:-49px;width:100px;height:100px;z-index:1}.mvx_banner-content{display: -webkit-box;display: -moz-box;display: -ms-flexbox;display: -webkit-flex;display: flex;align-items:center}.mvx_admin_new_banner .txt{color:#333;font-size:15px;line-height:1.4;width:calc(100% - 345px);position:relative;z-index:2;display:inline-block;font-weight:400;float:left;padding-left:8px}.mvx_admin_new_banner .link,.mvx_admin_new_banner .mvx_btn_service_claim_now{font-weight:400;display:inline-block;z-index:2;padding:0 20px;position:relative}.mvx_admin_new_banner .rightside{float:right;width:345px}.mvx_admin_new_banner .mvx_btn_service_claim_now{cursor:pointer;background:#8abee5;height:40px;color:#fff;font-size:20px;text-align:center;border:none;margin:5px 13px;border-radius:5px;text-decoration:none;line-height:40px}.mvx_admin_new_banner button:hover{opacity:.8;transition:.5s}.mvx_admin_new_banner .link{font-size:18px;line-height:49px;background:0 0;height:50px}.mvx_admin_new_banner .link a{color:#333;text-decoration:none}@media (max-width:990px) {.mvx_admin_new_banner::before{left:-4%;top:-12%}}@media (max-width:767px) {.mvx_admin_new_banner::before{left:0;top:0;transform:rotate(0);width:10px}.mvx_admin_new_banner .txt{width:400px;max-width:100%;text-align:center;padding:0;margin:0 auto 5px;float:none;display:block;font-size:17px;line-height:1.6}.mvx_admin_new_banner .rightside{width:100%;padding-left:10px;text-align:center;box-sizing:border-box}.mvx_admin_new_banner .mvx_btn_service_claim_now{margin:10px 0}.mvx_banner-content{display:block}}.mvx_admin_new_banner button.notice-dismiss{z-index:1;position:absolute;top:50%;transform:translateY(-50%)}</style>
        <script type="text/javascript">
            function dismiss_servive_notice(e, i) {
                jQuery.post(ajaxurl, {action: "dismiss_mvx_servive_notice"}, function (e) {
                    e && (jQuery(".mvx_admin_new_banner").addClass("hidden"), void 0 !== i && (window.open(i, '_blank')))
                })
            }
        </script>
        <?php
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
                __('Vendor Shop Base', 'multivendorx'), // setting title
                array(&$this, 'mvx_taxonomy_slug_input'), // display callback
                'permalink', // settings page
                'optional'                                      // settings section
        );
    }

    function mvx_taxonomy_slug_input() {
        $permalinks = get_option('dc_vendors_permalinks');
        ?>
        <input name="dc_product_vendor_taxonomy_slug" type="text" class="regular-text code" value="<?php if (isset($permalinks['vendor_shop_base'])) echo esc_attr($permalinks['vendor_shop_base']); ?>" placeholder="<?php esc_attr_e('vendor slug', 'multivendorx') ?>" />
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
                        'title' => __('Frontend  Dashboard', 'multivendorx'),
                        'href' => get_permalink(mvx_vendor_dashboard_page_id()),
                        'meta' => array(
                            'title' => __('Frontend Dashboard', 'multivendorx'),
                            'target' => '_blank',
                            'class' => 'shop-settings'
                        ),
                    )
            );
            $admin_bar->add_menu(
                    array(
                        'id' => 'shop_settings',
                        'title' => __('Storefront', 'multivendorx'),
                        'href' => mvx_get_vendor_dashboard_endpoint_url(get_mvx_vendor_settings('mvx_store_settings_endpoint', 'seller_dashbaord', 'storefront')),
                        'meta' => array(
                            'title' => __('Storefront', 'multivendorx'),
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
                    if (0 === strpos($menu_item[0], _x('Commissions', 'Admin menu name', 'multivendorx'))) {
                        $order_count = isset( mvx_count_commission()->unpaid ) ? mvx_count_commission()->unpaid : 0;
                        $submenu['mvx'][$key][0] .= ' <span class="awaiting-mod update-plugins count-' . $order_count . '"><span class="processing-count">' . number_format_i18n($order_count) . '</span></span>';
                    }
                    if (0 === strpos($menu_item[0], _x('To-do List', 'Admin menu name', 'multivendorx'))) {
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
        wp_enqueue_script('media-upload');
        wp_enqueue_media();
        wp_enqueue_editor();
        wp_enqueue_script( 'mce-view' );
        $MVX->library->load_upload_lib();
        $MVX->library->load_mapbox_api();
        wp_enqueue_style( 'site-health' );
        wp_enqueue_script( 'site-health' );
        // get all settings fileds
        $settings_fields = mvx_admin_backend_settings_fields_details();
        // get all tab settings fileds
        $mvx_all_backend_tab_list = mvx_admin_backend_tab_settings();
        if (!empty($settings_fields)) {
            foreach ($settings_fields as $settings_key => $settings_value) {
                foreach ($settings_value as $inter_key => $inter_value) {
                    $change_settings_key    =   str_replace("-", "_", $settings_key);
                    $option_name = 'mvx_'.$change_settings_key.'_tab_settings';
                    $database_value = get_option($option_name) ? get_option($option_name) : array();
                    if (!empty($database_value)) {
                        if (isset($inter_value['key']) && array_key_exists($inter_value['key'], $database_value)) {
                            if (empty($settings_fields[$settings_key][$inter_key]['database_value'])) {
                               $settings_fields[$settings_key][$inter_key]['database_value'] = mvx_string_wpml($database_value[$inter_value['key']]);
                            }
                        }
                    }
                }
            }
        }
        $page_details = array('toplevel_page_mvx');
        if (in_array($screen->id, array('product', 'edit-product'))) {
            wp_register_script('mvx-admin-product-js', $MVX->plugin_url . 'assets/admin/js/product' . $suffix . '.js', array('jquery'), $MVX->version, true);
            wp_enqueue_script('mvx-admin-product-js');
        }
        wp_enqueue_script(
            'mvx-modules-build-frontend',
            $MVX->plugin_url . 'mvx-modules/build/index.js',
            ['wp-element'],
            time(),
            true
        );
        // select product list
        $question_product_selection_wordpboard = array();
        $product_query = new WP_Query(array('posts_per_page' => -1, 'post_type' => 'product', 'post_status' => 'publish'));
        if ($product_query->get_posts()) {
            $question_product_selection_wordpboard = mvx_convert_select_structure($product_query->get_posts(), '', true);
        }
        $commission_bulk_list_action = mvx_convert_select_structure(array('mark_paid' => __('Mark paid', 'multivendorx')));
        // Commission csv header
        $commission_header = mvx_convert_select_structure(
            apply_filters('mvx_vendor_commission_data_header',array(
                __('Recipient', 'multivendorx'),
                __('Currency', 'multivendorx'),
                __('Commission', 'multivendorx'),
                __('Shipping', 'multivendorx'),
                __('Tax', 'multivendorx'),
                __('Total', 'multivendorx'),
                __('Status', 'multivendorx'),
                )
            ), true);
        $commission_status_list_action = mvx_convert_select_structure(mvx_get_commission_statuses());
        $select_option_delete = mvx_convert_select_structure(array('delete' => __('Delete', 'multivendorx')));
        // product report chart data for csv
        $report_product_header = mvx_convert_select_structure(
            apply_filters('mvx_product_report_data_header',array(
                __('Product Name', 'multivendorx'),
                __('Net Sales', 'multivendorx'),
                __('Admin Earning', 'multivendorx'),
                __('Vendor Earning', 'multivendorx'),
                )
            ), true);
        // vendor report chart data for csv
        $report_vendor_header = mvx_convert_select_structure(
            apply_filters('mvx_vendor_report_data_header',array(
                __('Vendor Name', 'multivendorx'),
                __('Net Sales', 'multivendorx'),
                __('Admin Earning', 'multivendorx'),
                __('Vendor Earning', 'multivendorx'),
                )
            ), true);
        
        $global_string = array(
            'close'                 =>  __('Close', 'multivendorx'),
            'edit'                  =>  __('Edit', 'multivendorx'),
            'shop'                  =>  __('Shop', 'multivendorx'),
            'download_csv'          =>  __('Download CSV', 'multivendorx'),
            'confirm_delete'        =>  __('Confirm delete?', 'multivendorx'),
            'save_changes'          =>  __('Save Changes', 'multivendorx'),
            'confirm_dismiss'       =>  __('Are you sure to dismiss?', 'multivendorx'),
            'confirm_approve'       =>  __('Are you sure to approve?', 'multivendorx'),
            'multivendorx_text'     =>  __('MultivendorX', 'multivendorx'),
            'multivendorx_url'      =>  'https://multivendorx.com/',
            'bulk_action'           =>  __('Bulk actions', 'multivendorx'),
            'all_dates'             =>  __('All Dates', 'multivendorx'),
            'select_all'            =>  __('Select All', 'multivendorx'),
            'back'                  =>  __('Back', 'multivendorx'),
            'publish'               =>  __('Publish', 'multivendorx'),
            'all'                   =>  __('All', 'multivendorx'),
            'published'             =>  __('Published', 'multivendorx'),
            'pending'               =>  __('Pending', 'multivendorx'),
            'save'                  =>  __('Save', 'multivendorx'),
            'saving'                =>  __('Saving..', 'multivendorx'),
            'open_uploader'         =>  __('Open Uploader', 'multivendorx'),
            'favorite_color'         =>  __('Select your favorite color', 'multivendorx'),
        );

        $workboard_string = array(
            'workboard8'                 =>  __('Vendor Name', 'multivendorx'),
            'workboard25'                 =>  __('Add Announcement', 'multivendorx'),
            'workboard26'                 =>  __('Search Announcement', 'multivendorx'),
            'workboard27'                 =>  __('Add Knowladgebase', 'multivendorx'),
            'workboard28'                 =>  __('Search Knowledgebase', 'multivendorx'),
            'workboard30'                 =>  __('Filter by vendor', 'multivendorx'),
            'workboard31'                 =>  __('Filter by product', 'multivendorx'),
            'workboard32'                 =>  __('Search Question', 'multivendorx'),
            'workboard33'                 =>  __('Search status', 'multivendorx'),
        );

        $analytics_page_string = array(
            'analytics1'             =>  __('Date range', 'multivendorx'),
            'analytics2'             =>  __('Net Sales', 'multivendorx'),
            'analytics3'             =>  __('Show', 'multivendorx'),
            'analytics4'             =>  __('Order Count', 'multivendorx'),
            'analytics5'             =>  __('Item Sold', 'multivendorx'),
            'analytics6'             =>  __('Vendor', 'multivendorx'),
            'analytics7'             =>  __('Commission Details', 'multivendorx'),
            'analytics8'             =>  __('Vendor Details', 'multivendorx'),
            'analytics9'             =>  __('Date', 'multivendorx'),
            'analytics10'             =>  __('Product Title', 'multivendorx'),
            'analytics11'             =>  __('Admin Earning', 'multivendorx'),
            'analytics12'             =>  __('Vendor Earning', 'multivendorx'),
            'analytics13'             =>  __('Gross Sales', 'multivendorx'),
            'analytics14'             =>  __('Vendor Name', 'multivendorx'),
            'analytics15'             =>  __('Commission ID', 'multivendorx'),
            'analytics16'             =>  __('Order ID', 'multivendorx'),
            'analytics17'             =>  __('Product', 'multivendorx'),
            'analytics18'             =>  __('Amount', 'multivendorx'),
            'analytics19'             =>  __('Net Earning', 'multivendorx'),
            'analytics20'             =>  __('Status', 'multivendorx'),
            'analytics21'             =>  __('Type', 'multivendorx'),
            'analytics22'             =>  __('Reference ID', 'multivendorx'),
            'analytics23'             =>  __('Credit', 'multivendorx'),
            'analytics24'             =>  __('Debit', 'multivendorx'),
            'analytics25'             =>  __('Balance', 'multivendorx'),
            'analytics26'                 =>  __('Products', 'multivendorx'),
        );

        $module_page_string = array(
            'module1'             =>  __('Module', 'multivendorx'),
            'module2'             =>  __('Customize your marketplace site by enabling the module that you prefer', 'multivendorx'),
            'module3'             =>  __('Total Modules :', 'multivendorx'),
            'module4'             =>  __('Active', 'multivendorx'),
            'module5'             =>  __('Inactive', 'multivendorx'),
            'module6'             =>  __('Search modules', 'multivendorx'),
            'module7'             =>  __('Search by Category', 'multivendorx'),
            'module8'             =>  __('Requires:', 'multivendorx'),
            'module9'             =>  __('Warning !!', 'multivendorx'),
            'module10'             =>  __('Please active required first to use', 'multivendorx'),
            'module11'             =>  __('module', 'multivendorx'),
            'module12'             =>  __('Cancel', 'multivendorx'),
            'module13'             =>  __('Upgrade To Pro', 'multivendorx'),
            'module14'             =>  __('To use this paid module, Please visit', 'multivendorx'),
            'module15'             =>  __('Site', 'multivendorx'),
        );

        $commission_page_string     =   array(
            'details'   =>  __('details', 'multivendorx'),
            'general'   =>  __('General', 'multivendorx'),
            'associated_order'   =>  __('Associated order', 'multivendorx'),
            'order_status'   =>  __('Order status', 'multivendorx'),
            'order_details'         =>  __('Order Details', 'multivendorx'),
            'commission_status'   =>  __('Commission Status', 'multivendorx'),
            'vendor_details'   =>  __('Vendor details', 'multivendorx'),
            'email'   =>  __('Email Address', 'multivendorx'),
            'payment_mode'   =>  __('Payment mode', 'multivendorx'),
            'commission_data'   =>  __('Commission data', 'multivendorx'),
            'commission_amount'   =>  __('Commission amount', 'multivendorx'),
            'shipping'   =>  __('Shipping', 'multivendorx'),
            'tax'   =>  __('Tax', 'multivendorx'),
            'commission'   =>  __('Commission', 'multivendorx'),
            'total'   =>  __('Total', 'multivendorx'),
            'refunded'   =>  __('Refunded', 'multivendorx'),
            'commission_notes'   =>  __('Commission Notes', 'multivendorx'),
            'search_commission'   =>  __('Search Commission', 'multivendorx'),
            'show_commission_status'   =>  __('Show Commission Status', 'multivendorx'),
            'show_all_vendor'   =>  __('Show All Vendor', 'multivendorx'),
            'bulk_action'   =>  __('Bulk Action', 'multivendorx'),
            'calculated_coupon' =>  __('Commission calculated including coupon.', 'multivendorx'),
            'calculated_shipping' =>  __('Commission total calcutated including shipping charges.', 'multivendorx'),
            'calculated_tax' =>  __('Commission total calcutated including tax charges.', 'multivendorx'),
            'all'  =>  __('All', 'multivendorx'),
            'paid'  =>  __('Paid', 'multivendorx'),
            'unpaid'  =>  __('Unpaid', 'multivendorx'),
            'edit_commission'   =>  __('Edit Commission', 'multivendorx'),
            'status'   =>  __('Status', 'multivendorx')
        );

        $vendor_page_string     =   array(
            'shipping1'   =>  __('Choose the shipping method you wish to add. Only shipping methods which support zones are listed', 'multivendorx'),
            'shipping2'   =>  __('Lets you charge a rate for shipping.', 'multivendorx'),
            'shipping3'   =>  __('You can add multiple shipping methods within this zone. Only customers within the zone will see them', 'multivendorx'),
            'add_shipping_methods'  =>  __('Add shipping method', 'multivendorx'),
            'zone_name'  =>  __('Zone Name', 'multivendorx'),
            'zone_region'  =>  __('Zone Region', 'multivendorx'),
            'specific_state'  =>  __('Select specific states', 'multivendorx'),
            'postcode'  =>  __('Set your postcode', 'multivendorx'),
            'comma_separated'  =>  __('Postcodes need to be comma separated', 'multivendorx'),
            'shipping_methods'  =>  __('Shipping methods', 'multivendorx'),
            'title'  =>  __('Title', 'multivendorx'),
            'email'  =>  __('Email', 'multivendorx'),
            'enabled'  =>  __('Enabled', 'multivendorx'),
            'none'  =>  __('None', 'multivendorx'),
            'description'  =>  __('Description', 'multivendorx'),
            'edit'  =>  __('Edit', 'multivendorx'),
            'delete'  =>  __('Delete', 'multivendorx'),
            'differnet_method'  =>  __('Differnet method', 'multivendorx'),
            'cost'  =>  __('Cost', 'multivendorx'),
            'taxable'  =>  __('Taxable', 'multivendorx'),
            'method_title'  =>  __('Method Title', 'multivendorx'),
            'approve'  =>  __('Approve', 'multivendorx'),
            'reject'  =>  __('Reject', 'multivendorx'),
            'enter_location'  =>  __('Enter a location', 'multivendorx'),
            'vendors'  =>  __('Vendors', 'multivendorx'),
            'add_vendor'  =>  __('Add Vendor', 'multivendorx'),
            'search_vendor'  =>  __('Search Vendors', 'multivendorx'),
            'edit_vendor'  =>  __('Edit Vendor', 'multivendorx'),
            'add_new'  =>  __('Add New', 'multivendorx'),
            'describe_yourself'  =>  __('Describe yourself here...', 'multivendorx'),
            'optional_note'  =>  __('Optional note for acceptance / rejection', 'multivendorx'),
        );

        $status_and_tools_string = array(
            'database-tools'    =>  array(
                array(
                    'name'              =>  __('Clear Transients', 'multivendorx'),
                    'key'               =>  'transients',
                    'headline_text'     =>  __('MultivendorX Vendors Transients', 'multivendorx'),
                    'description_text'  =>  __('This button clears all vendor dashboards transient cache', 'multivendorx')
                ),
                array(
                    'name'              =>  __('Reset Database', 'multivendorx'),
                    'key'               =>  'visitor',
                    'headline_text'     =>  __('Reset Visitors Stats Table', 'multivendorx'),
                    'description_text'  =>  __('Use this tool to clear all the table data of MultivendorX visitors stats', 'multivendorx')
                ),
                array(
                    'name'              =>  __('Order Migrate', 'multivendorx'),
                    'key'               =>  'migrate_order',
                    'headline_text'     =>  __('Migrate Previous Marketplace Data', 'multivendorx'),
                    'description_text'  =>  __('With this tool, you can create missing sub orders', 'multivendorx')
                ),
                array(
                    'name'              =>  __('Multivendor Migrate', 'multivendorx'),
                    'key'               =>  'migrate',
                    'headline_text'     =>  __('Multivendor Migration', 'multivendorx'),
                    'description_text'  =>  __('With this tool, you can transfer valuable data from your previous marketplace', 'multivendorx')
                ),
            ),
            'system-info'   =>  __('System Info', 'multivendorx'),
            'copy-system-info'   =>  __('Copy System Info to Clipboard', 'multivendorx'),
            'copied'   =>  __('Copied!', 'multivendorx'),
            'error-log'   =>  __('Error Log', 'multivendorx'),
            'copied-text'   =>  __('If you have enabled, errors will be stored in a log file. Here you can find the last 100 lines in reversed order so that you or the MultivendorX support team can view it easily. The file cannot be edited here', 'multivendorx'),
            'copy-log-clipboard'   =>  __('Copy Log to Clipboard', 'multivendorx'),
        );

        $settings_page_string = array(
            'registration_form_title'       =>  __('Registration form title', 'multivendorx'),
            'registration_form_title_desc'  =>  __('Type the form title you want the vendor to see. eg registrazione del venditore', 'multivendorx'),
            'registration_form_desc'        =>  __('Registration form description', 'multivendorx'),
            'registration1'                  =>  __('Add guidelines or valuable information applicable for registration.', 'multivendorx'),
            'registration2'                  =>  __('Write questions applicable to your marketplace', 'multivendorx'),
            'registration3'                  =>  __('Select your preferred question format. Read doc to know more about each format.', 'multivendorx'),
            'registration4'                  =>  __('Placeholder', 'multivendorx'),
            'registration5'                  =>  __('Tooltip description', 'multivendorx'),
            'registration6'                  =>  __('Leave this section blank or add examples of an answer here.', 'multivendorx'),
            'registration7'                  =>  __('Add more information or specific instructions here.', 'multivendorx'),
            'registration8'                  =>  __('Characters Limit', 'multivendorx'),
            'registration9'                  =>  __('Restrict vendor descriptions to a certain number of characters.', 'multivendorx'),
            'registration10'                  =>  __('File Type', 'multivendorx'),
            'registration11'                  =>  __('Multiple', 'multivendorx'),
            'registration12'                  =>  __('Maximum file size', 'multivendorx'),
            'registration13'                  =>  __('Add limitation for file size', 'multivendorx'),
            'registration14'                  =>  __('Acceptable file types', 'multivendorx'),
            'registration15'                  =>  __('Choose preferred file size.', 'multivendorx'),
            'registration16'                  =>  __('reCAPTCHA Type', 'multivendorx'),
            'registration17'                  =>  __('reCAPTCHA v3', 'multivendorx'),
            'registration18'                  =>  __('reCAPTCHA v2', 'multivendorx'),
            'registration19'                  =>  __('Site key', 'multivendorx'),
            'registration20'                  =>  __('Secret key', 'multivendorx'),
            'registration21'                  =>  __('Recaptcha Script', 'multivendorx'),
            'registration22'                  =>  __('Write titles for your options here.', 'multivendorx'),
            'registration23'                  =>  __('This section is available for developers who might want to mark the labels they create.', 'multivendorx'),
            'registration24'                  =>  __('', 'multivendorx'),
            'registration25'                  =>  __('Require', 'multivendorx'),
            'registration26'                  =>  __('To get', 'multivendorx'),
            'registration27'                  =>  __('reCAPTCHA', 'multivendorx'),
            'registration28'                  =>  __('script, register your site with google account', 'multivendorx'),
            'registration29'                  =>  __('Register', 'multivendorx'),
            'question-format'                 => array(
                array(
                    'icon'  =>  'icon-yes',
                    'value' => 'select_question_type',
                    'label' =>  __('Select question type', 'multivendorx')
                ),
                array(
                    'icon'  =>  'icon-form-textbox',
                    'value' => 'textbox',
                    'label' =>  __('Textbox', 'multivendorx')
                ),
                array(
                    'icon'  =>  'icon-form-email',
                    'value' => 'email',
                    'label' =>  __('Email', 'multivendorx')
                ),
                array(
                    'icon'  =>  'icon-form-url',
                    'value' => 'url',
                    'label' =>  __('Url', 'multivendorx')
                ),
                array(
                    'icon'  =>  'icon-form-textarea',
                    'value' => 'textarea',
                    'label' =>  __('Textarea', 'multivendorx')
                ),
                array(
                    'icon'  =>  'icon-form-checkboxes',
                    'value' => 'checkboxes',
                    'label' =>  __('Checkboxes', 'multivendorx')
                ),
                array(
                    'icon'  =>  'icon-form-multi-select',
                    'value' => 'multi-select',
                    'label' =>  __('Multi Select', 'multivendorx')
                ),
                array(
                    'icon'  =>  'icon-form-radio',
                    'value' => 'radio',
                    'label' =>  __('Radio', 'multivendorx')
                ),
                array(
                    'icon'  =>  'icon-form-dropdown',
                    'value' => 'dropdown',
                    'label' =>  __('Dropdown', 'multivendorx')
                ),
                array(
                    'icon'  =>  'icon-form-recaptcha',
                    'value' => 'recapta',
                    'label' =>  __('Recapta', 'multivendorx')
                ),
                array(
                    'icon'  =>  'icon-form-attachment',
                    'value' => 'attachment',
                    'label' =>  __('Attachment', 'multivendorx')
                ),
                array(
                    'icon'  =>  'icon-form-section',
                    'value' => 'section',
                    'label' =>  __('Section', 'multivendorx')
                ),
                array(
                    'icon'  =>  'icon-form-store-description',
                    'value' => 'vendor_description',
                    'label' =>  __('Store Description', 'multivendorx')
                ),
                array(
                    'icon'  =>  'icon-form-address01',
                    'value' => 'vendor_address_1',
                    'label' =>  __('Address 1', 'multivendorx')
                ),
                array(
                    'icon'  =>  'icon-form-address02',
                    'value' => 'vendor_address_2',
                    'label' =>  __('Address 2', 'multivendorx')
                ),
                array(
                    'icon'  =>  'icon-form-phone',
                    'value' => 'vendor_phone',
                    'label' =>  __('Phone', 'multivendorx')
                ),
                array(
                    'icon'  =>  'icon-form-country',
                    'value' => 'vendor_country',
                    'label' =>  __('Country', 'multivendorx')
                ),
                array(
                    'icon'  =>  'icon-form-state',
                    'value' => 'vendor_state',
                    'label' =>  __('State', 'multivendorx')
                ),
                array(
                    'icon'  =>  'icon-form-city',
                    'value' => 'vendor_city',
                    'label' =>  __('City', 'multivendorx')
                ),
                array(
                    'icon'  =>  'icon-form-postcode',
                    'value' => 'vendor_postcode',
                    'label' =>  __('Postcode', 'multivendorx')
                ),
                array(
                    'icon'  =>  'icon-form-paypal-email',
                    'value' => 'vendor_paypal_email',
                    'label' =>  __('PayPal Email', 'multivendorx')
                )
            )
        );

        $report_page_string = array(
            'vendor_select' =>  __('Select your vendor to view transaction details', 'multivendorx'),
            'choose_vendor' =>  __('Search Vendors', 'multivendorx'),
            'choose_product'    =>  __('Search Product', 'multivendorx'),
            'performance'    =>  __('Performance', 'multivendorx'),
            'charts'    =>  __('Charts', 'multivendorx'),
            'net_sales'    =>  __('Charts', 'multivendorx'),
            'order_count'    =>  __('Order Count', 'multivendorx'),
            'item_sold'    =>  __('Item Sold', 'multivendorx'),
            'download_csv'  =>  __('Download CSV', 'multivendorx'),
            'leaderboards'  =>  __('Leaderboards', 'multivendorx')
        );

        $pending_question_bulk = array(
            array(
                'value' => 'verified',
                'label' => __('Verified', 'multivendorx')
            ),
            array(
                'value' => 'rejected',
                'label' => __('Rejected', 'multivendorx')
            ),
        );

        $post_bulk_status = array(
            array(
                'value' => 'pending',
                'label' => __('Pending', 'multivendorx')
            ),
            array(
                'value' => 'publish',
                'label' => __('Published', 'multivendorx')
            ),
        );

        $question_selection_wordpboard = array(
            array(
                'value' => 'unanswer',
                'label' => __('Unanswered', 'multivendorx')
            ),
            array(
                'value' => 'all',
                'label' => __('All Q&As', 'multivendorx')
            ),
        );

        $task_board_bulk_status = array(
            array(
                'value' => 'approve',
                'label' => __('Approve', 'multivendorx')
            ),
            array(
                'value' => 'dismiss',
                'label' => __('Dismiss', 'multivendorx')
            ),
        );

        $columns_followers = array(
            array(
                'name'      =>  __('Customer Name', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "name",
            ),
            array(
                'name'      =>  __('Date', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "time",
            )
        );

        $columns_zone_shipping = array(
            array(
                'name'      =>  __('Zone name', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "zone_name",
            ),
            array(
                'name'      =>  __('Region(s)', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "region",
            ),
            array(
                'name'      =>  __('Shipping method(s)', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "shipping_method",
            )
        );

        $columns_vendor = array(
            array(
                'name'      =>  __('Name', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "name",
            ),
            array(
                'name'      =>  __('', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'cell'  =>  'cell',
                'ignoreRowClick'=> true,
                'allowOverflow'=> true,
                'button'=> true,
                'last_action'   =>  'eyeicon_trigger'
            ),
            array(
                'name'      =>  __('Email', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "email",
            ),
            array(
                'name'      =>  __('Registered', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "registered",
            ),
            array(
                'name'      =>  __('Products', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "products",
            ),
            array(
                'name'      =>  __('Status', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "status",
            ),
            array(
                'name'      =>  __('Action', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'cell'  =>  'cell',
                'ignoreRowClick'=> true,
                'allowOverflow'=> true,
                'button'=> true,
                'last_action'   =>  'last_action_trigger'
            )
        );

        $columns_commission = array(
            array(
                'name'      =>  __('Commission ID', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "commission_id",
            ),
            array(
                'name'      =>  __('Order ID', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "order_id",
            ),
            array(
                'name'      =>  __('Product', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "product",
            ),
            array(
                'name'      =>  __('Vendor', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "vendor",
            ),
            array(
                'name'      =>  __('Amount', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "amount",
            ),
            array(
                'name'      =>  __('Net Earning', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "net_earning",
            ),
            array(
                'name'      =>  __('Status', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "status",
            ),
            array(
                'name'      =>  __('Date', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "date",
            ),
            array(
                'name'      =>  __('Action', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'cell'  =>  'cell',
                'ignoreRowClick'=> true,
                'allowOverflow'=> true,
                'button'=> true,
            )
        );

        // word board section start
        $columns_announcement = array(
            array(
                'name'      =>  __('Title', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "title",
            ),
            array(
                'name'      =>  __('Vendors', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "vendor",
            ),
            array(
                'name'      =>  __('Date', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "date",
            ),
            array(
                'name'      =>  __('Action', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'cell'  =>  'cell',
                'ignoreRowClick'=> true,
                'allowOverflow'=> true,
                'button'=> true,
            )
        );

        $columns_questions = array(
            array(
                'name'      =>  __('Question by', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "question_by",
            ),
            array(
                'name'      =>  __('Product Name', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "product_name",
            ),
            array(
                'name'      =>  __('Date', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "question_date",
            ),
            array(
                'name'      =>  __('Status', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "question_status",
            ),
            array(
                'name'      =>  __('Question details', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "question_details",
            ),
            array(
                'name'      =>  __('Action', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'cell'  =>  'cell',
                'ignoreRowClick'=> true,
                'allowOverflow'=> true,
                'button'=> true,
            )
        );

        $columns_knowledgebase = array(
            array(
                'name'      =>  __('Title', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "title",
            ),
            array(
                'name'      =>  __('Date', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "date",
            ),
            array(
                'name'      =>  __('Action', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'cell'  =>  'cell',
                'ignoreRowClick'=> true,
                'allowOverflow'=> true,
                'button'=> true,
            )
        );

        $columns_store_review = array(
            array(
                'name'      =>  __('Customer', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "author",
            ),
            array(
                'name'      =>  __('Vendor', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "user_id",
            ),
            array(
                'name'      =>  __('Content', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "content",
            ),
            array(
                'name'      =>  __('Time', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "time",
            ),
            array(
                'name'      =>  __('Review', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "review",
            ),
            array(
                'name'      =>  __('Action', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'cell'  =>  'cell',
                'ignoreRowClick'=> true,
                'allowOverflow'=> true,
                'button'=> true,
            )
        );

        $columns_report_abuse = array(
            array(
                'name'      =>  __('Reason', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "reason",
            ),
            array(
                'name'      =>  __('Product', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "product",
            ),
            array(
                'name'      =>  __('Vendor', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "vendor",
            ),
            array(
                'name'      =>  __('Reported by', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "reported_by",
            ),
            array(
                'name'      =>  __('Action', 'multivendorx'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'cell'  =>  'cell',
                'ignoreRowClick'=> true,
                'allowOverflow'=> true,
                'button'=> true,
            )
        );
        // word board section end
        $select_module_category_option = array(
            array(
                'value' => 'payment',
                'label' => __('Payment', 'multivendorx')
            ),
            array(
                'value' => 'shipping',
                'label' => __('Shipping', 'multivendorx')
            ),
            array(
                'value' => 'vendor_store_boosters',
                'label' => __('Vendor Store Boosters', 'multivendorx')
            ),
            array(
                'value' => 'notifictaion',
                'label' => __('Notifictaion', 'multivendorx')
            ),
            array(
                'value' => 'marketplace_products',
                'label' => __('Marketplace Products', 'multivendorx')
            ),
            array(
                'value' => 'third_party_compartibility',
                'label' => __('Third Party Compartibility', 'multivendorx')
            )
        );
        
        wp_localize_script( 'mvx-modules-build-frontend', 'appLocalizer', apply_filters('mvx_module_complete_settings', [
            'apiUrl' => home_url( '/wp-json' ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'marker_icon' => $MVX->plugin_url . 'assets/images/store-marker.png',
            'mvx_logo' => $MVX->plugin_url.'assets/images/dclogo.svg',
            'google_api'    =>  get_mvx_global_settings('google_api_key'),
            'mapbox_api'    =>  get_mvx_global_settings('mapbox_api_key'),
            'location_provider'    =>  get_mvx_global_settings('choose_map_api'),
            'multivendor_logo' => $MVX->plugin_url.'assets/images/multivendorX.png',
            'knowledgebase' => 'https://multivendorx.com/knowledgebase/',
            'knowledgebase_title' => __('MVX knowledge Base', 'multivendorx'),
            'search_module' =>  __('Search Modules', 'multivendorx'),
            'marketplace_text' => __('MultiVendorX', 'multivendorx'),
            'search_module_placeholder' => __('Search Modules', 'multivendorx'),
            'pro_text' => __('Pro', 'multivendorx'),
            'documentation_extra_text' => __('For more info, please check the', 'multivendorx'),
            'documentation_text' => __('DOC', 'multivendorx'),
            'settings_text' => __('Settings', 'multivendorx'),
            'admin_mod_url' => admin_url('admin.php?page=modules'),
            'admin_setup_widget_option' => admin_url( 'index.php?page=mvx-setup' ),
            'admin_migration_widget_option' => admin_url( 'index.php?page=mvx-setup' ),
            'multivendor_migration_link' => admin_url('index.php?page=mvx-migrator'),
            'add_announcement_link' =>  admin_url('admin.php?page=mvx#&submenu=work-board&name=announcement&create=announcement'),
            'announcement_back' =>  admin_url('admin.php?page=mvx#&submenu=work-board&name=announcement'),

            'add_knowladgebase_link' =>  admin_url('admin.php?page=mvx#&submenu=work-board&name=knowladgebase&create=knowladgebase'),
            'knowladgebase_back' =>  admin_url('admin.php?page=mvx#&submenu=work-board&name=knowladgebase'),

            'settings_fields' => apply_filters('mvx-settings-fileds-details', $settings_fields),
            'countries'                 => wp_json_encode( array_merge( WC()->countries->get_allowed_country_states(), WC()->countries->get_shipping_country_states() ) ),
            'mvx_all_backend_tab_list' => $mvx_all_backend_tab_list,
            'default_logo'                  => $MVX->plugin_url.'assets/images/WP-stdavatar.png',
            'right_logo'                    => $MVX->plugin_url.'assets/images/right_tick.jpg',
            'cross_logo'                    => $MVX->plugin_url.'assets/images/cross_tick.png',
            'commission_bulk_list_option'   =>  $commission_bulk_list_action,
            'commission_header'             => $commission_header,
            'commission_status_list_action' =>  $commission_status_list_action,
            'commission_page_string'        =>  $commission_page_string,
            'vendor_page_string'            =>  $vendor_page_string,
            'status_and_tools_string'       =>  $status_and_tools_string,
            'settings_page_string'          =>  $settings_page_string,
            'global_string'                 =>  $global_string,
            'workboard_string'              =>  $workboard_string,
            'module_page_string'            =>  $module_page_string,
            'analytics_page_string'         =>  $analytics_page_string,
            'report_product_header'         =>  $report_product_header,
            'report_vendor_header'          =>  $report_vendor_header,
            'report_page_string'            =>  $report_page_string,
            'post_bulk_status'              =>  $post_bulk_status,
            'question_selection_wordpboard' =>  $question_selection_wordpboard,
            'question_product_selection_wordpboard' =>  $question_product_selection_wordpboard,
            'pending_question_bulk'         =>  $pending_question_bulk,
            'task_board_bulk_status'        =>  $task_board_bulk_status,
            'columns_announcement'          =>  $columns_announcement,
            'columns_questions'             =>  $columns_questions,
            'columns_knowledgebase'         =>  $columns_knowledgebase,
            'columns_store_review'          =>  $columns_store_review,
            'columns_vendor'                =>  $columns_vendor,
            'columns_followers'             =>  $columns_followers,
            'columns_zone_shipping'         =>  $columns_zone_shipping,
            'select_option_delete'    =>  $select_option_delete,
            'columns_commission'                    =>  $columns_commission,
            'columns_report_abuse'                  =>  $columns_report_abuse,
            'select_module_category_option'         =>  $select_module_category_option,
            'errors_log'                            =>  $this->get_error_log_rows(100),
        ] ) );

        if ( in_array($screen->id, $page_details)) {
            wp_enqueue_style('mvx_admin_css', $MVX->plugin_url . 'assets/admin/css/admin' . $suffix . '.css', array(), $MVX->version);
            wp_enqueue_style('mvx_admin_rsuite_css', $MVX->plugin_url . 'assets/admin/css/rsuite-default' . '.min' . '.css', array(), $MVX->version);
        }
        
        //backend spmv
        if (mvx_is_module_active('spmv')) {
            wp_register_script('mvx_admin_product_auto_search_js', $MVX->plugin_url . 'assets/admin/js/admin-product-auto-search' . $suffix . '.js', array('jquery'), $MVX->version, true);
            wp_enqueue_script('mvx_admin_product_auto_search_js');
            wp_localize_script('mvx_admin_product_auto_search_js', 'mvx_admin_product_auto_search_js_params', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'search_products_nonce' => wp_create_nonce('search-products'),
            ));
        }

        // hide media list view access for vendor
        $user = wp_get_current_user();
        if (in_array('dc_vendor', $user->roles)) {
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
        if (is_user_mvx_vendor(get_current_vendor_id()) && in_array($screen->id, array('edit-product'))) {
            $custom_css = "
            .inline-edit-product .inline-edit-categories, .bulk-edit-product .inline-edit-categories{
                display: none;
            }";
            wp_add_inline_style( 'woocommerce_admin_styles', $custom_css );
        }        
    }

    public function get_error_log_rows( $limit = -1 ) {
        $wp_filesystem  = $this->get_filesystem();
        $log_path = ini_get( 'error_log' );

        $contents = $wp_filesystem->get_contents_array( $log_path );

        if ( -1 === $limit ) {
            return join( '', $contents );
        }

        return is_array($contents) ? join( '', array_slice( $contents, -$limit ) ) : '';
    }

    public function get_filesystem() {
        global $wp_filesystem;
        if ( empty( $wp_filesystem ) ) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            WP_Filesystem();
        }
        return $wp_filesystem;
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
        if ( $post && wp_get_post_parent_id( $post->ID ) )
            $actions['regenerate_order_commissions'] = __('Regenerate order commissions', 'multivendorx');
        if ( $post && !wp_get_post_parent_id( $post->ID ) )
            $actions['regenerate_suborders'] = __('Regenerate suborders', 'multivendorx');
        if (is_user_mvx_vendor(get_current_user_id())) {
            if (isset($actions['regenerate_order_commissions'])) unset($actions['regenerate_order_commissions']);
            if (isset($actions['send_order_details'])) unset( $actions['send_order_details'] );
            if (isset($actions['send_order_details_admin'])) unset( $actions['send_order_details_admin'] );
            if (isset($actions['regenerate_suborders'])) unset($actions['regenerate_suborders']);
        }
        return $actions;
    }

    /**
     * Regenerate order commissions
     * @param Object $order
     * @since 3.0.2
     */
    public function regenerate_order_commissions($order) {
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
            $order->add_order_note( __( 'Regenerated order commission.', 'multivendorx') );
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
    
    public function add_mvx_screen_ids($screen_ids) {
        $screen_ids[] = 'toplevel_page_dc-vendor-shipping';
        return $screen_ids;
    }

    public function mvx_vendor_shipping_admin_capability($current_id) {
        if ( !is_user_mvx_vendor($current_id) ) {
            if ( isset($_POST['vendor_id'] )) {
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
        if ( empty( $sub_orders ) )
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