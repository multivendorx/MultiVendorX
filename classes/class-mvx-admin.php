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
                <span class="txt"><?php esc_html_e('Your settings migration cron is running. Please wait.', 'dc-woocommerce-multi-vendor') ?>  </span>
                <div class="rightside">        
                    <a href="https://wc-marketplace.com/latest-release/" target="_blank" class="mvx_btn_service_claim_now"><?php esc_html_e('Checkout latest release', 'dc-woocommerce-multi-vendor'); ?></a>
                    <button onclick="dismiss_servive_notice(event);" type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
                </div>

            </div>
        </div>
        <style type="text/css">.clearfix{clear:both}.mvx_admin_new_banner.updated{border-left:0}.mvx_admin_new_banner{box-shadow:0 3px 1px 1px rgba(0,0,0,.2);padding:10px 30px;background:#fff;position:relative;overflow:hidden;clear:both;border-top:2px solid #8abee5;text-align:left;background-size:contain}.mvx_admin_new_banner .round{width:200px;height:200px;position:absolute;border-radius:100%;border:30px solid rgba(157,42,255,.05);top:-150px;left:73px;z-index:1}.mvx_admin_new_banner .round1{position:absolute;border-radius:100%;border:45px solid rgba(194,108,144,.05);bottom:-82px;right:-58px;width:180px;height:180px;z-index:1}.mvx_admin_new_banner .round2,.mvx_admin_new_banner .round3{border-radius:100%;width:180px;height:180px;position:absolute;z-index:1}.mvx_admin_new_banner .round2{border:18px solid rgba(194,108,144,.05);top:35px;left:249px}.mvx_admin_new_banner .round3{border:45px solid rgba(31,194,255,.05);top:2px;right:40%}.mvx_admin_new_banner .round4{position:absolute;border-radius:100%;border:31px solid rgba(31,194,255,.05);top:11px;left:-49px;width:100px;height:100px;z-index:1}.mvx_banner-content{display: -webkit-box;display: -moz-box;display: -ms-flexbox;display: -webkit-flex;display: flex;align-items:center}.mvx_admin_new_banner .txt{color:#333;font-size:15px;line-height:1.4;width:calc(100% - 345px);position:relative;z-index:2;display:inline-block;font-weight:400;float:left;padding-left:8px}.mvx_admin_new_banner .link,.mvx_admin_new_banner .mvx_btn_service_claim_now{font-weight:400;display:inline-block;z-index:2;padding:0 20px;position:relative}.mvx_admin_new_banner .rightside{float:right;width:345px}.mvx_admin_new_banner .mvx_btn_service_claim_now{cursor:pointer;background:#8abee5;height:40px;color:#fff;font-size:20px;text-align:center;border:none;margin:5px 13px;border-radius:5px;text-decoration:none;line-height:40px}.mvx_admin_new_banner button:hover{opacity:.8;transition:.5s}.mvx_admin_new_banner .link{font-size:18px;line-height:49px;background:0 0;height:50px}.mvx_admin_new_banner .link a{color:#333;text-decoration:none}@media (max-width:990px){.mvx_admin_new_banner::before{left:-4%;top:-12%}}@media (max-width:767px){.mvx_admin_new_banner::before{left:0;top:0;transform:rotate(0);width:10px}.mvx_admin_new_banner .txt{width:400px;max-width:100%;text-align:center;padding:0;margin:0 auto 5px;float:none;display:block;font-size:17px;line-height:1.6}.mvx_admin_new_banner .rightside{width:100%;padding-left:10px;text-align:center;box-sizing:border-box}.mvx_admin_new_banner .mvx_btn_service_claim_now{margin:10px 0}.mvx_banner-content{display:block}}.mvx_admin_new_banner button.notice-dismiss{z-index:1;position:absolute;top:50%;transform:translateY(-50%)}</style>
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
                        'href' => mvx_get_vendor_dashboard_endpoint_url(get_mvx_vendor_settings('mvx_store_settings_endpoint', 'seller_dashbaord', 'storefront')),
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
        wp_enqueue_script('media-upload');
        wp_enqueue_media();
        wp_enqueue_editor();
        wp_enqueue_script( 'mce-view' );
        $MVX->library->load_upload_lib();

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


        wp_register_script('mvx-admin-to-do-js', $MVX->plugin_url . 'assets/admin/js/mvx-to-do-action' . $suffix . '.js', array('jquery'), $MVX->version, true);
        wp_enqueue_script('mvx-admin-to-do-js');

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

        $pending_question_bulk = array(
            array(
                'value' => 'verified',
                'label' => __('Verified', 'dc-woocommerce-multi-vendor')
            ),
            array(
                'value' => 'rejected',
                'label' => __('Rejected', 'dc-woocommerce-multi-vendor')
            ),
        );

        $store_review_bulk = array(
            array(
                'value' => 'delete',
                'label' => __('Delete', 'dc-woocommerce-multi-vendor')
            )
        );

        $post_bulk_status = array(
            array(
                'value' => 'pending',
                'label' => __('Pending', 'dc-woocommerce-multi-vendor')
            ),
            array(
                'value' => 'publish',
                'label' => __('Published', 'dc-woocommerce-multi-vendor')
            ),
        );

        $task_board_bulk_status = array(
            array(
                'value' => 'approve',
                'label' => __('Approve', 'dc-woocommerce-multi-vendor')
            ),
            array(
                'value' => 'dismiss',
                'label' => __('Dismiss', 'dc-woocommerce-multi-vendor')
            ),
        );


        $columns_announcement = array(
            array(
                'name'      =>  __('Title', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "title",
                
            ),
            array(
                'name'      =>  __('Vendors', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "vendor",
                
            ),
            array(
                'name'      =>  __('Date', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "date",
            ),
            array(
                'name'      =>  __('Action', 'dc-woocommerce-multi-vendor'),
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
                'name'      =>  __('Title', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "title",
                
            ),
            array(
                'name'      =>  __('Date', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "date",
            ),
            array(
                'name'      =>  __('Action', 'dc-woocommerce-multi-vendor'),
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
                'name'      =>  __('Customer', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "author",
                
            ),
            array(
                'name'      =>  __('Vendor', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "user_id",
                
            ),
            array(
                'name'      =>  __('Content', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "content",
                
            ),
            array(
                'name'      =>  __('Time', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "time",
                
            ),
            array(
                'name'      =>  __('Review', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "review",
                
            ),
            array(
                'name'      =>  __('Action', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'cell'  =>  'cell',
                'ignoreRowClick'=> true,
                'allowOverflow'=> true,
                'button'=> true,
            )
        );

        $columns_vendor = array(
            array(
                'name'      =>  __('Name', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "name",
                
            ),
            array(
                'name'      =>  __('', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'cell'  =>  'cell',
                'ignoreRowClick'=> true,
                'allowOverflow'=> true,
                'button'=> true,
                'last_action'   =>  'eyeicon_trigger'
            ),
            array(
                'name'      =>  __('Email', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "email",
            ),
            array(
                'name'      =>  __('Registered', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "registered",
            ),
            array(
                'name'      =>  __('Products', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "products",
            ),
            array(
                'name'      =>  __('Status', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "status",
            ),
            array(
                'name'      =>  __('Action', 'dc-woocommerce-multi-vendor'),
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
                'name'      =>  __('Commission ID', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "commission_id",
                
            ),
            array(
                'name'      =>  __('Order ID', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "order_id",
            ),
            array(
                'name'      =>  __('Product', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "product",
            ),
            array(
                'name'      =>  __('Vendor', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "vendor",
            ),
            array(
                'name'      =>  __('Amount', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "amount",
            ),
            array(
                'name'      =>  __('Net Earning', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "net_earning",
            ),
            array(
                'name'      =>  __('Status', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "status",
            ),
            array(
                'name'      =>  __('Date', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "date",
            ),
            array(
                'name'      =>  __('Action', 'dc-woocommerce-multi-vendor'),
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
                'name'      =>  __('Reason', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "reason",
                
            ),
            array(
                'name'      =>  __('Product', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "product",
            ),
            array(
                'name'      =>  __('Vendor', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "vendor",
            ),
            array(
                'name'      =>  __('Reported by', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'selector_choice'  => "reported_by",
            ),
            array(
                'name'      =>  __('Action', 'dc-woocommerce-multi-vendor'),
                'selector'  =>  '',
                'sortable'  =>  true,
                'cell'  =>  'cell',
                'ignoreRowClick'=> true,
                'allowOverflow'=> true,
                'button'=> true,
            )
        );


        $select_module_category_option = array(
            array(
                'value' => 'payment',
                'label' => __('Payment', 'dc-woocommerce-multi-vendor')
            ),
            array(
                'value' => 'shipping',
                'label' => __('Shipping', 'dc-woocommerce-multi-vendor')
            ),
            array(
                'value' => 'vendor_store_boosters',
                'label' => __('Vendor Store Boosters', 'dc-woocommerce-multi-vendor')
            ),
            array(
                'value' => 'notifictaion',
                'label' => __('Notifictaion', 'dc-woocommerce-multi-vendor')
            ),
            array(
                'value' => 'marketplace_products',
                'label' => __('Marketplace Products', 'dc-woocommerce-multi-vendor')
            ),
            array(
                'value' => 'third_party_compartibility',
                'label' => __('Third Party Compartibility', 'dc-woocommerce-multi-vendor')
            )
        );


        $vendor_list_page_bulk_list_options = array();
        $vendor_bulk_list = array(
            'delete' => __('Delete', 'dc-woocommerce-multi-vendor'),
        );
        if ($vendor_bulk_list) {
            foreach($vendor_bulk_list as $bulk_key => $bulk_value) {
                $vendor_list_page_bulk_list_options[] = array(
                    'value' => $bulk_key,
                    'label' => $bulk_value
                );
            }
        }


        wp_localize_script( 'mvx-modules-build-frontend', 'appLocalizer', apply_filters('mvx_module_complete_settings', [
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
            'post_bulk_status'              =>  $post_bulk_status,
            'pending_question_bulk'         =>  $pending_question_bulk,
            'store_review_bulk'             =>  $store_review_bulk,
            'task_board_bulk_status'        =>  $task_board_bulk_status,
            'columns_announcement'          =>  $columns_announcement,
            'columns_knowledgebase'         =>  $columns_knowledgebase,
            'columns_store_review'          =>  $columns_store_review,
            'columns_vendor'                =>  $columns_vendor,
            'vendor_list_page_bulk_list_options'    =>  $vendor_list_page_bulk_list_options,
            'columns_commission'                    =>  $columns_commission,
            'columns_report_abuse'                  =>  $columns_report_abuse,
            'select_module_category_option'         =>  $select_module_category_option,
            'errors_log'                            =>  $this->get_error_log_rows(100),
        ] ) );

        if ( in_array($screen->id, $page_details)) {
            wp_enqueue_style('mvx_admin_css', $MVX->plugin_url . 'assets/admin/css/admin' . '' . '.css', array(), $MVX->version);
            wp_enqueue_style('mvx_admin_rsuite_css', $MVX->plugin_url . 'assets/admin/css/rsuite-default' . '.min' . '.css', array(), $MVX->version);
        }

        //wp_enqueue_style('fffffffffffff', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css', array(), $MVX->version);
        

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
