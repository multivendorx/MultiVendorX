<?php
/**
 * Setup Wizard Class
 * 
 * @since 2.7.7
 * @package MultiVendorX
 * @author MultivendorX
 */
if (!defined('ABSPATH')) {
    exit;
}

class MVX_Admin_Setup_Wizard {

    /** @var string Currenct Step */
    private $step = '';

    /** @var array Steps for the setup wizard */
    private $steps = array();

    public function __construct() {
        add_action('admin_menu', array($this, 'admin_menus'));
        add_action('admin_init', array($this, 'setup_wizard'));
    }

    /**
     * Add admin menus/screens.
     */
    public function admin_menus() {
        add_dashboard_page('', '', 'manage_options', 'mvx-setup', '');
    }

    /**
     * Show the setup wizard.
     */
    public function setup_wizard() {
        global $MVX;
        if (filter_input(INPUT_GET, 'page') != 'mvx-setup') {
            return;
        }

        if (!WC_Dependencies_Product_Vendor::is_woocommerce_active()) {
            if (isset($_POST['submit'])) {
                $this->install_woocommerce();
            }
            $this->install_woocommerce_view();
            exit();
        }
        $default_steps = array(
            'introduction' => array(
                'name' => __('Introduction', 'multivendorx'),
                'view' => array($this, 'mvx_setup_introduction'),
                'handler' => '',
            ),
            'store' => array(
                'name' => __('Store Setup', 'multivendorx'),
                'view' => array($this, 'mvx_setup_store'),
                'handler' => array($this, 'mvx_setup_store_save')
            ),
            'commission' => array(
                'name' => __('Commission Setup', 'multivendorx'),
                'view' => array($this, 'mvx_setup_commission'),
                'handler' => array($this, 'mvx_setup_commission_save')
            ),
            'payments' => array(
                'name' => __('Payments', 'multivendorx'),
                'view' => array($this, 'mvx_setup_payments'),
                'handler' => array($this, 'mvx_setup_payments_save')
            ),
            'capability' => array(
                'name' => __('Capability', 'multivendorx'),
                'view' => array($this, 'mvx_setup_capability'),
                'handler' => array($this, 'mvx_setup_capability_save')
            ),
            'import-dummy-data' => array(
                'name' => __('Import Dummy Data', 'multivendorx'),
                'view' => array($this, 'mvx_setup_dummy_data'),
                'handler' => array($this, 'mvx_setup_dummy_data_import')
            ),
            'introduction-migration' => array(
                'name' => __('Migration', 'multivendorx' ),
                'view' => array($this, 'mvx_migration_introduction'),
                'handler' => '',
            ),
            'store-process' => array(
                'name' => __('Processing', 'multivendorx'),
                'view' => array($this, 'mvx_migration_store_process'),
                'handler' => ''
            ),
            'next_steps' => array(
                'name' => __('Ready!', 'multivendorx'),
                'view' => array($this, 'mvx_setup_ready'),
                'handler' => '',
            ),
        );
        if (!$MVX->multivendor_migration->mvx_is_marketplace()) {
            unset( $default_steps['introduction-migration'], $default_steps['store-process'] );
        } 
        $this->steps = apply_filters('mvx_setup_wizard_steps', $default_steps);
        $current_step = filter_input(INPUT_GET, 'step');
        $this->step = $current_step ? sanitize_key($current_step) : current(array_keys($this->steps));
        $suffix = defined('MVX_SCRIPT_DEBUG') && MVX_SCRIPT_DEBUG ? '' : '.min';
        wp_register_script('jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array('jquery'), '2.70', true);
        wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip' . $suffix . '.js', array( 'jquery' ), WC_VERSION, true );
        wp_register_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full' . $suffix . '.js', array( 'jquery' ), '1.0.0' );
        wp_register_script('wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array('jquery', 'selectWoo'), WC_VERSION);
        wp_localize_script('wc-enhanced-select', 'wc_enhanced_select_params', array(
            'i18n_no_matches' => _x('No matches found', 'enhanced select', 'multivendorx'),
            'i18n_ajax_error' => _x('Loading failed', 'enhanced select', 'multivendorx'),
            'i18n_input_too_short_1' => _x('Please enter 1 or more characters', 'enhanced select', 'multivendorx'),
            'i18n_input_too_short_n' => _x('Please enter %qty% or more characters', 'enhanced select', 'multivendorx'),
            'i18n_input_too_long_1' => _x('Please delete 1 character', 'enhanced select', 'multivendorx'),
            'i18n_input_too_long_n' => _x('Please delete %qty% characters', 'enhanced select', 'multivendorx'),
            'i18n_selection_too_long_1' => _x('You can only select 1 item', 'enhanced select', 'multivendorx'),
            'i18n_selection_too_long_n' => _x('You can only select %qty% items', 'enhanced select', 'multivendorx'),
            'i18n_load_more' => _x('Loading more results&hellip;', 'enhanced select', 'multivendorx'),
            'i18n_searching' => _x('Searching&hellip;', 'enhanced select', 'multivendorx'),
            'ajax_url' => admin_url('admin-ajax.php'),
            'search_products_nonce' => wp_create_nonce('search-products'),
            'search_customers_nonce' => wp_create_nonce('search-customers'),
        ));

        wp_enqueue_style('woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION);
        wp_enqueue_style('wc-setup', WC()->plugin_url() . '/assets/css/wc-setup.css', array('dashicons', 'install'), WC_VERSION);
        wp_enqueue_style('mvx_admin_css', $MVX->plugin_url . 'assets/admin/css/admin' . $suffix . '.css', array(), $MVX->version);
        wp_register_script('wc-setup', WC()->plugin_url() . '/assets/js/admin/wc-setup' . $suffix . '.js', array('jquery', 'wc-enhanced-select', 'jquery-blockui', 'jquery-tiptip'), WC_VERSION);
        wp_register_script('mvx-setup', $MVX->plugin_url . '/assets/admin/js/setup-wizard.js', array('wc-setup'), WC_VERSION);
        wp_localize_script('wc-setup', 'wc_setup_params', array(
            'locale_info' => json_encode(include( WC()->plugin_path() . '/i18n/locale-info.php' )),
        ));

        if (!empty($_POST['save_step']) && isset($this->steps[$this->step]['handler'])) {
            call_user_func($this->steps[$this->step]['handler'], $this);
        }

        ob_start();
        $this->setup_wizard_header();
        $this->setup_wizard_steps();
        $this->setup_wizard_content();
        $this->setup_wizard_footer();
        exit();
    }

    /**
     * Content for install woocommerce view
     */
    public function install_woocommerce_view() {
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
            <head>
                <meta name="viewport" content="width=device-width" />
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <title><?php esc_html_e('MultivendorX &rsaquo; Setup Wizard', 'multivendorx'); ?></title>
                <?php do_action('admin_print_styles'); ?>
                <?php do_action('admin_head'); ?>
            </head>
            <body class="mvx-setup wp-core-ui">
                <h1 id="mvx-logo"><a href="https://multivendorx.com/"><img src="<?php echo trailingslashit(plugins_url('multivendorx')); ?>assets/images/widget-multivendorX.svg" alt="MultivendorX" /></a></h1>
                <div class="mvx-install-woocommerce">
                    <p><?php esc_html_e('MultivendorX requires WooCommerce plugin to be active!', 'multivendorx'); ?></p>
                    <form method="post" action="" name="mvx_install_woocommerce">
                        <?php submit_button(__('Install WooCommerce', 'multivendorx'), 'primary', 'mvx_install_woocommerce'); ?>
        <?php wp_nonce_field('mvx-install-woocommerce'); ?>
                    </form>
                </div>
            </body>
        </html>
        <?php
    }

    /**
     * Install woocommerce if not exist
     * @throws Exception
     */
    public function install_woocommerce() {
        check_admin_referer('mvx-install-woocommerce');
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
        require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        WP_Filesystem();
        $skin = new Automatic_Upgrader_Skin;
        $upgrader = new WP_Upgrader($skin);
        $installed_plugins = array_map(array(__CLASS__, 'format_plugin_slug'), array_keys(get_plugins()));
        $plugin_slug = 'woocommerce';
        $plugin = $plugin_slug . '/' . $plugin_slug . '.php';
        $installed = false;
        $activate = false;
        // See if the plugin is installed already
        if (in_array($plugin_slug, $installed_plugins)) {
            $installed = true;
            $activate = !is_plugin_active($plugin);
        }
        // Install this thing!
        if (!$installed) {
            // Suppress feedback
            ob_start();

            try {
                $plugin_information = plugins_api('plugin_information', array(
                    'slug' => $plugin_slug,
                    'fields' => array(
                        'short_description' => false,
                        'sections' => false,
                        'requires' => false,
                        'rating' => false,
                        'ratings' => false,
                        'downloaded' => false,
                        'last_updated' => false,
                        'added' => false,
                        'tags' => false,
                        'homepage' => false,
                        'donate_link' => false,
                        'author_profile' => false,
                        'author' => false,
                    ),
                ));

                if (is_wp_error($plugin_information)) {
                    throw new Exception($plugin_information->get_error_message());
                }

                $package = $plugin_information->download_link;
                $download = $upgrader->download_package($package);

                if (is_wp_error($download)) {
                    throw new Exception($download->get_error_message());
                }

                $working_dir = $upgrader->unpack_package($download, true);

                if (is_wp_error($working_dir)) {
                    throw new Exception($working_dir->get_error_message());
                }

                $result = $upgrader->install_package(array(
                    'source' => $working_dir,
                    'destination' => WP_PLUGIN_DIR,
                    'clear_destination' => false,
                    'abort_if_destination_exists' => false,
                    'clear_working' => true,
                    'hook_extra' => array(
                        'type' => 'plugin',
                        'action' => 'install',
                    ),
                ));

                if (is_wp_error($result)) {
                    throw new Exception($result->get_error_message());
                }

                $activate = true;
            } catch (Exception $e) {
                printf(
                        __('%1$s could not be installed (%2$s). <a href="%3$s">Please install it manually by clicking here.</a>', 'multivendorx'), 'WooCommerce', $e->getMessage(), esc_url(admin_url('plugin-install.php?tab=search&s=woocommerce'))
                );
                exit();
            }

            // Discard feedback
            ob_end_clean();
        }

        wp_clean_plugins_cache();
        // Activate this thing
        if ($activate) {
            try {
                $result = activate_plugin($plugin);

                if (is_wp_error($result)) {
                    throw new Exception($result->get_error_message());
                }
            } catch (Exception $e) {
                printf(
                        __('%1$s was installed but could not be activated. <a href="%2$s">Please activate it manually by clicking here.</a>', 'multivendorx'), 'WooCommerce', admin_url('plugins.php')
                );
                exit();
            }
        }
        wp_safe_redirect(admin_url('index.php?page=mvx-setup'));
    }

    /**
     * Get slug from path
     * @param  string $key
     * @return string
     */
    private static function format_plugin_slug($key) {
        $slug = explode('/', $key);
        $slug = explode('.', end($slug));
        return $slug[0];
    }

    /**
     * Get the URL for the next step's screen.
     * @param string step   slug (default: current step)
     * @return string       URL for next step if a next step exists.
     *                      Admin URL if it's the last step.
     *                      Empty string on failure.
     * @since 2.7.7
     */
    public function get_next_step_link($step = '') {
        if (!$step) {
            $step = $this->step;
        }

        $keys = array_keys($this->steps);
        if (end($keys) === $step) {
            return admin_url();
        }

        $step_index = array_search($step, $keys);
        if (false === $step_index) {
            return '';
        }

        return add_query_arg('step', $keys[$step_index + 1]);
    }

    /**
     * Setup Wizard Header.
     */
    public function setup_wizard_header() {
        global $MVX;
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
            <head>
                <meta name="viewport" content="width=device-width" />
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <title><?php esc_html_e('MultivendorX &rsaquo; Setup Wizard', 'multivendorx'); ?></title>
                <?php wp_print_scripts('wc-setup'); ?>
                <?php wp_print_scripts('mvx-setup'); ?>
                <?php do_action('admin_print_styles'); ?>

            </head>
            <body class="wc-setup wp-core-ui">
                <h1 id="wc-logo"><a href="https://multivendorx.com/"><img src="<?php echo esc_url($MVX->plugin_url); ?>assets/images/widget-multivendorX.svg" alt="MultivendorX" /></a></h1>
                <?php
            }

    /**
     * Output the steps.
     */
    public function setup_wizard_steps() {
        $ouput_steps = $this->steps;
        array_shift($ouput_steps);
        ?>
        <ol class="wc-setup-steps">
            <?php foreach ($ouput_steps as $step_key => $step) : ?>
                <li class="<?php
                if ($step_key === $this->step) {
                    echo 'active';
                } elseif (array_search($this->step, array_keys($this->steps)) > array_search($step_key, array_keys($this->steps))) {
                    echo 'done';
                }
                ?>"><?php echo esc_html($step['name']); ?></li>
        <?php endforeach; ?>
        </ol>
        <?php
    }

    /**
     * Output the content for the current step.
     */
    public function setup_wizard_content() {
        echo '<div class="wc-setup-content">';
        call_user_func($this->steps[$this->step]['view'], $this);
        echo '</div>';
    }

    /**
     * Introduction step.
     */
    public function mvx_setup_introduction() {
        ?>
        <h1><?php esc_html_e('Welcome to the MultivendorX family!', 'multivendorx'); ?></h1>
        <p><?php echo wp_kses_post('Thank you for choosing MultivendorX! This quick setup wizard will help you configure the basic settings and you will have your marketplace ready in no time. <strong>It’s completely optional and shouldn’t take longer than five minutes.</strong>', 'multivendorx'); ?></p>
        <p><?php esc_html_e("If you don't want to go through the wizard right now, you can skip and return to the WordPress dashboard. Come back anytime if you change your mind!", 'multivendorx'); ?></p>
        <p class="wc-setup-actions step">
            <a href="<?php echo esc_url(admin_url()); ?>" class="button button-large"><?php esc_html_e('Not right now', 'multivendorx'); ?></a>
            <a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="btn red-btn button button-large button-next"><?php esc_html_e("Let's go!", 'multivendorx'); ?></a>
        </p>
        <?php
    }

    /**
     * Store setup content
     */
    public function mvx_setup_store() {
        ?>
        <h1><?php esc_html_e('Store setup', 'multivendorx'); ?></h1>
        <div class="mvx-setting-section-divider">&nbsp;</div>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="vendor_store_url"><?php esc_html_e('Store URL', 'multivendorx'); ?></label></th>
                    <td class="mvx-store-setup">
                        <?php
                        $permalinks = mvx_get_option('dc_vendors_permalinks');
                        $vendor_slug = empty($permalinks['vendor_shop_base']) ? _x('', 'slug', 'multivendorx') : $permalinks['vendor_shop_base'];
                        ?>
                        <input type="text" id="vendor_store_url" name="vendor_store_url" placeholder="<?php esc_attr_e('vendor', 'multivendorx'); ?>" value="<?php echo esc_attr( $vendor_slug ); ?>" />
                        <p class="description"><?php esc_html_e('Define vendor store URL (' . site_url() . '/[this-text]/[seller-name])', 'multivendorx') ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="is_single_product_multiple_vendor"><?php esc_html_e('Single Product Multiple Vendors', 'multivendorx'); ?></label></th>
                    <td>
                        <?php $is_single_product_multiple_vendor = get_mvx_global_settings('is_singleproductmultiseller') ? 'Enable' : ''; ?>
                        <input type="checkbox" <?php checked($is_single_product_multiple_vendor, 'Enable'); ?> id="is_single_product_multiple_vendor" name="is_single_product_multiple_vendor" class="input-checkbox" value="Enable" />
                    </td>
                </tr>
            </table>
            <p class="wc-setup-actions step">
                <a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'multivendorx'); ?></a>
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Continue', 'multivendorx'); ?>" name="save_step" />
        <?php wp_nonce_field('mvx-setup'); ?>
            </p>
        </form>
        <?php
    }

    /**
     * commission setup content
     */
    public function mvx_setup_commission() {
        $payment_settings = mvx_get_option('mvx_commissions_tab_settings');
        if( isset($payment_settings['commission_type']['value']) ){
            $commission_type = $payment_settings['commission_type']['value'];
        }else{
            $commission_type = 'percent';
        }

        $default_commission = '';
        $fixed_with_percentage = '';

        if( isset($payment_settings['default_commission'][0]['value']) ){
            if( $commission_type === 'fixed' || $commission_type === 'percent' ){
                $default_commission    = $payment_settings['default_commission'][0]['value'];
            }else{
                $default_commission    = $payment_settings['default_commission'][1]['value'];
                $fixed_with_percentage = $payment_settings['default_commission'][0]['value'];
            }
        }
        ?>
        <h1><?php esc_html_e('Commission Setup', 'multivendorx'); ?></h1>
        <div class="mvx-setting-section-divider">&nbsp;</div>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="revenue_sharing_mode"><?php esc_html_e('Revenue Sharing Mode', 'multivendorx'); ?></label></th>
                    <td>
                        <?php
                        $revenue_sharing_mode = isset($payment_settings['revenue_sharing_mode']) ? $payment_settings['revenue_sharing_mode'] : 'revenue_sharing_mode_vendor';
                        ?>
                        <label><input type="radio" <?php checked($revenue_sharing_mode, 'revenue_sharing_mode_admin'); ?> id="revenue_sharing_mode" name="revenue_sharing_mode" class="input-radio" value="revenue_sharing_mode_admin" /> <?php esc_html_e('Admin fees', 'multivendorx'); ?></label><br/>
                        <label><input type="radio" <?php checked($revenue_sharing_mode, 'revenue_sharing_mode_vendor'); ?> id="revenue_sharing_mode" name="revenue_sharing_mode" class="input-radio" value="revenue_sharing_mode_vendor" /> <?php esc_html_e('Vendor Commissions', 'multivendorx'); ?></label>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="commission_type"><?php esc_html_e('Commission Type', 'multivendorx'); ?></label></th>
                    <td>
                        <select id="commission_type" name="commission_type" class="wc-enhanced-select">
                            <option value="fixed" data-fields="#tr_default_commission" <?php selected($commission_type, 'fixed'); ?>><?php esc_html_e('Fixed Amount', 'multivendorx'); ?></option>
                            <option value="percent" data-fields="#tr_default_percentage" <?php selected($commission_type, 'percent'); ?>><?php esc_html_e('Percentage', 'multivendorx'); ?></option>
                            <option value="fixed_with_percentage" data-fields="#tr_default_percentage,#tr_fixed_with_percentage" <?php selected($commission_type, 'fixed_with_percentage'); ?>><?php esc_html_e('%age + Fixed (per transaction)', 'multivendorx'); ?></option>
                            <option value="fixed_with_percentage_qty" data-fields="#tr_default_percentage,#tr_fixed_with_percentage_qty" <?php selected($commission_type, 'fixed_with_percentage_qty'); ?>><?php esc_html_e('%age + Fixed (per unit)', 'multivendorx'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr id="tr_default_commission" class="mvx_commission_type_fields">
                    <th scope="row"><label for="default_commission"><?php esc_html_e('Commission value', 'multivendorx'); ?></label></th>
                    <td>
                        <input type="text" id="default_commission" name="default_commission" placeholder="" value="<?php echo esc_attr($default_commission); ?>" />
                    </td>
                </tr>

                <tr id="tr_default_percentage" class="mvx_commission_type_fields">
                    <th scope="row"><label for="default_percentage"><?php esc_html_e('Commission Percentage', 'multivendorx'); ?></label></th>
                    <td>
                        <input type="text" id="default_percentage" name="default_percentage" placeholder="" value="<?php echo esc_attr($default_commission); ?>" />
                    </td>
                </tr>

                <tr id="tr_fixed_with_percentage" class="mvx_commission_type_fields">
                    <th scope="row"><label for="fixed_with_percentage"><?php esc_html_e('Fixed Amount', 'multivendorx'); ?></label></th>
                    <td>
                        <input type="text" id="fixed_with_percentage" name="fixed_with_percentage" placeholder="" value="<?php echo esc_attr($fixed_with_percentage); ?>" />
                    </td>
                </tr>

                <tr id="tr_fixed_with_percentage_qty" class="mvx_commission_type_fields">
                    <th scope="row"><label for="fixed_with_percentage_qty"><?php esc_html_e('Fixed Amount', 'multivendorx'); ?></label></th>
                    <td>
                        <input type="text" id="fixed_with_percentage_qty" name="fixed_with_percentage_qty" placeholder="" value="<?php echo esc_attr($fixed_with_percentage); ?>" />
                    </td>
                </tr>

            </table>
            <p class="wc-setup-actions step">
                <a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'multivendorx'); ?></a>
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Continue', 'multivendorx'); ?>" name="save_step" />
        <?php wp_nonce_field('mvx-setup'); ?>
            </p>
        </form>
        <?php
    }

    /**
     * payment setup content
     */
    public function mvx_setup_payments() {
        $payment_settings = mvx_get_option('mvx_commissions_tab_settings');
        $disbursement_settings = mvx_get_option('mvx_disbursement_tab_settings');
        $gateways = $this->get_payment_methods();
        ?>
        <h1><?php esc_html_e('Payments', 'multivendorx'); ?></h1>
        <div class="mvx-setting-section-divider">&nbsp;</div>
        <form method="post" class="wc-wizard-payment-gateway-form">
            <h3 class='mvx-pay-heading'><?php esc_html_e('Allowed Payment Methods', 'multivendorx'); ?></h3>
            <ul class="wc-wizard-services wc-wizard-payment-gateways">
                        <?php foreach ($gateways as $gateway_id => $gateway): ?>
                    <li class="wc-wizard-service-item wc-wizard-gateway <?php echo esc_attr($gateway['class']); ?>">
                        <div class="wc-wizard-service-name">
                            <label>
    <?php echo esc_html($gateway['label']); ?>
                            </label>
                        </div>
                        <div class="wc-wizard-gateway-description">
                    <?php echo wp_kses_post(wpautop($gateway['description'])); ?>
                        </div>
                        <div class="wc-wizard-service-enable">
                            <span class="wc-wizard-service-toggle disabled">
                                <?php
                                $is_enable_gateway = mvx_is_module_active($gateway_id) ? 'Enable' : '';
                                ?>
                                <input type="checkbox" <?php checked($is_enable_gateway, 'Enable') ?> name="payment_method_<?php echo esc_attr($gateway_id); ?>" class="input-checkbox" value="Enable" />
                                <label htmlFor={`mvx-toggle-switch-${student.id}`}></label>
                            </span>
                        </div>
                    </li>
<?php endforeach; ?>
            </ul>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="mvx_disbursal_mode_admin"><?php esc_html_e('Disbursal Schedule', 'multivendorx'); ?></label></th>
                    <td>
                        <?php
                        $mvx_disbursal_mode_admin = isset($disbursement_settings['choose_payment_mode_automatic_disbursal']) ? 'Enable' : '';
                        ?>
                        <input type="checkbox" data-field="#tr_payment_schedule" <?php checked($mvx_disbursal_mode_admin, 'Enable'); ?> id="mvx_disbursal_mode_admin" name="mvx_disbursal_mode_admin" class="input-checkbox" value="Enable" />
                        <p class="description"><?php esc_html_e('If checked, automatically vendors commission will disburse.', 'multivendorx') ?></p>
                    </td>
                </tr>
                <tr id="tr_payment_schedule">
                    <th scope="row"><label for="payment_schedule"><?php esc_html_e('Set Schedule', 'multivendorx'); ?></label></th>
                    <?php
                    $payment_schedule = isset($disbursement_settings['payment_schedule']) ? $disbursement_settings['payment_schedule'] : 'monthly';
                    ?>
                    <td>
                        <label><input type="radio" <?php checked($payment_schedule, 'weekly'); ?> id="payment_schedule" name="payment_schedule" class="input-radio" value="weekly" /> <?php esc_html_e('Weekly', 'multivendorx'); ?></label><br/>
                        <label><input type="radio" <?php checked($payment_schedule, 'daily'); ?> id="payment_schedule" name="payment_schedule" class="input-radio" value="daily" /> <?php esc_html_e('Daily', 'multivendorx'); ?></label><br/>
                        <label><input type="radio" <?php checked($payment_schedule, 'monthly'); ?> id="payment_schedule" name="payment_schedule" class="input-radio" value="monthly" /> <?php esc_html_e('Monthly', 'multivendorx'); ?></label><br/>
                        <label><input type="radio" <?php checked($payment_schedule, 'fortnightly'); ?> id="payment_schedule" name="payment_schedule" class="input-radio" value="fortnightly" /> <?php esc_html_e('Fortnightly', 'multivendorx'); ?></label><br/>
                        <label><input type="radio" <?php checked($payment_schedule, 'hourly'); ?> id="payment_schedule" name="payment_schedule" class="input-radio" value="hourly" /> <?php esc_html_e('Hourly', 'multivendorx'); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="mvx_disbursal_mode_vendor"><?php esc_html_e('Withdrawal Request', 'multivendorx'); ?></label></th>
                    <td>
                        <?php
                        $mvx_disbursal_mode_vendor = isset($disbursement_settings['withdrawal_request']) ? 'Enable' : '';
                        ?>
                        <input type="checkbox" <?php checked($mvx_disbursal_mode_vendor, 'Enable'); ?> id="mvx_disbursal_mode_vendor" name="mvx_disbursal_mode_vendor" class="input-checkbox" value="Enable" />
                        <p class="description"><?php esc_html_e('Vendors can request for commission withdrawal.', 'multivendorx') ?></p>
                    </td>
                </tr>
            </table>
            <p class="wc-setup-actions step">
                <a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'multivendorx'); ?></a>
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Continue', 'multivendorx'); ?>" name="save_step" />
        <?php wp_nonce_field('mvx-setup'); ?>
            </p>
        </form>
        <?php
    }

    /**
     * capability setup content
     */
    public function mvx_setup_capability() {
        $capabilities_settings = mvx_get_option('mvx_products_capability_tab_settings');
        ?>
        <h1><?php esc_html_e('Capability', 'multivendorx'); ?></h1>
        <div class="mvx-setting-section-divider">&nbsp;</div>
        <form method="post">
            <table class="form-table">
                <?php
                $is_submit_product = isset($capabilities_settings['is_submit_product']) ? 'Enable' : '';
                ?>
                <tr>
                    <th scope="row"><label for="is_submit_product"><?php esc_html_e('Submit Products', 'multivendorx'); ?></label></th>
                    <td>
                        <input type="checkbox" <?php checked($is_submit_product, 'Enable'); ?> id="is_submit_product" name="is_submit_product" class="input-checkbox" value="Enable">
                        <p class="description"><?php esc_html_e('Allow vendors to submit products for approval/publishing.', 'multivendorx'); ?></p>
                    </td>
                </tr>
                <?php
                $is_published_product = isset($capabilities_settings['is_published_product']) ? 'Enable' : '';
                ?>
                <tr>
                    <th scope="row"><label for="is_published_product"><?php esc_html_e('Publish Products', 'multivendorx'); ?></label></th>
                    <td>
                        <input type="checkbox" <?php checked($is_published_product, 'Enable'); ?> id="is_published_product" name="is_published_product" class="input-checkbox" value="Enable">
                        <p class="description"><?php esc_html_e('If checked, products uploaded by vendors will be directly published without admin approval.', 'multivendorx'); ?></p>
                    </td>
                </tr>
                <?php
                $is_edit_delete_published_product = isset($capabilities_settings['is_edit_delete_published_product']) ? 'Enable' : '';
                ?>
                <tr>
                    <th scope="row"><label for="is_edit_delete_published_product"><?php esc_html_e('Edit Publish Products', 'multivendorx'); ?></label></th>
                    <td>
                        <input type="checkbox" <?php checked($is_edit_delete_published_product, 'Enable'); ?> id="is_edit_delete_published_product" name="is_edit_delete_published_product" class="input-checkbox" value="Enable">
                        <p class="description"><?php esc_html_e('Allow vendors to Edit published products.', 'multivendorx'); ?></p>
                    </td>
                </tr>
                <?php
                $is_submit_coupon = isset($capabilities_settings['is_submit_coupon']) ? 'Enable' : '';
                ?>
                <tr>
                    <th scope="row"><label for="is_submit_coupon"><?php esc_html_e('Submit Coupons', 'multivendorx'); ?></label></th>
                    <td>
                        <input type="checkbox" <?php checked($is_submit_coupon, 'Enable'); ?> id="is_submit_coupon" name="is_submit_coupon" class="input-checkbox" value="Enable">
                        <p class="description"><?php esc_html_e('Allow vendors to create coupons.', 'multivendorx'); ?></p>
                    </td>
                </tr>
                <?php
                $is_published_coupon = isset($capabilities_settings['is_published_coupon']) ? 'Enable' : '';
                ?>
                <tr>
                    <th scope="row"><label for="is_published_coupon"><?php esc_html_e('Publish Coupons', 'multivendorx'); ?></label></th>
                    <td>
                        <input type="checkbox" <?php checked($is_published_coupon, 'Enable'); ?> id="is_published_coupon" name="is_published_coupon" class="input-checkbox" value="Enable">
                        <p class="description"><?php esc_html_e('If checked, coupons added by vendors will be directly published without admin approval.', 'multivendorx'); ?></p>
                    </td>
                </tr>
                <?php
                $is_edit_delete_published_coupon = isset($capabilities_settings['is_edit_delete_published_coupon']) ? 'Enable' : '';
                ?>
                <tr>
                    <th scope="row"><label for="is_edit_delete_published_coupon"><?php esc_html_e('Edit Publish Coupons', 'multivendorx'); ?></label></th>
                    <td>
                        <input type="checkbox" <?php checked($is_edit_delete_published_coupon, 'Enable'); ?> id="is_edit_delete_published_coupon" name="is_edit_delete_published_coupon" class="input-checkbox" value="Enable">
                        <p class="description"><?php esc_html_e('Allow Vendor To edit delete published shop coupons.', 'multivendorx'); ?></p>
                    </td>
                </tr>
                <?php
                $is_upload_files = isset($capabilities_settings['is_upload_files']) ? 'Enable' : '';
                ?>
                <tr>
                    <th scope="row"><label for="is_upload_files"><?php esc_html_e('Upload Media Files', 'multivendorx'); ?></label></th>
                    <td>
                        <input type="checkbox" <?php checked($is_upload_files, 'Enable'); ?> id="is_upload_files" name="is_upload_files" class="input-checkbox" value="Enable">
                        <p class="description"><?php esc_html_e('Allow vendors to upload media files.', 'multivendorx'); ?></p>
                    </td>
                </tr>

            </table>
            <p class="wc-setup-actions step">
                <a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'multivendorx'); ?></a>
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Continue', 'multivendorx'); ?>" name="save_step" />
                <?php wp_nonce_field('mvx-setup'); ?>
            </p>
        </form>
        <?php
    }

    /**
     * Dummy data setup content
     */
    public function mvx_setup_dummy_data(){
        $plugin_check = array(
            'booking' => '',
            'subscription' => '',
            'rental' => '',
            'auction' => ''
        );
        if(! is_plugin_active('woocommerce-bookings/woocommerce-bookings.php')){
            $plugin_check['booking'] = 'inactive';
        }
        if(! is_plugin_active('woocommerce-subscriptions/woocommerce-subscriptions.php')){
            $plugin_check['subscription'] = 'inactive';
        }
        if(! is_plugin_active('woocommerce-rental-and-booking/redq-rental-and-bookings.php')){
            $plugin_check['rental'] = 'inactive';
        }
        if(! is_plugin_active('woocommerce-simple-auctions/woocommerce-simple-auctions.php')){
            $plugin_check['auction'] = 'inactive';
        }
        ?>
        <h1><?php esc_html_e('Import Dummy Data', 'multivendorx'); ?></h1>
        <div class="mvx-setting-section-divider">&nbsp;</div>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="marketplace_type"><?php esc_html_e('Marketplace Type', 'multivendorx'); ?></label></th>
                    <td>
                        <select name="marketplace_type" id="marketplace_type" class="wc-enhanced-select">
                            <option value="niche"><?php esc_html_e('Niche Marketplace', 'multivendorx'); ?></option>
                            <option value="booking" data-plugin="<?php echo $plugin_check['booking']; ?>"><?php esc_html_e('Booking Marketplace', 'multivendorx'); ?></option>
                            <option value="subscription" data-plugin="<?php echo $plugin_check['subscription']; ?>"><?php esc_html_e('Subscription Marketplace', 'multivendorx'); ?></option>
                            <option value="rental" data-plugin="<?php echo $plugin_check['rental']; ?>"><?php esc_html_e('Rental Marketplace', 'multivendorx'); ?></option>
                            <option value="auction" data-plugin="<?php echo $plugin_check['auction']; ?>"><?php esc_html_e('Auction Marketplace', 'multivendorx'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('Dummy data will be imported based on the marketplace type.', 'multivendorx')?></p>
                        <p id="booking-error" class="woocommerce-error" style="display: none;"><?php esc_html_e('WooCommerce Bookings pulgin is required to continue.', 'multivendorx')?></p>
                        <p id="subscription-error" class="woocommerce-error" style="display: none;"><?php esc_html_e('WooCommerce Subscriptions pulgin is required to continue.', 'multivendorx')?></p>
                        <p id="rental-error" class="woocommerce-error" style="display: none;"><?php esc_html_e('WooCommerce Booking & Rental pulgin is required to continue.', 'multivendorx')?></p>
                        <p id="auction-error" class="woocommerce-error" style="display: none;"><?php esc_html_e('WooCommerce Simple Auction pulgin is required to continue.', 'multivendorx')?></p>
                        
                    </td>
                </tr>
                
            </table>
            <p class="wc-setup-actions step">
                <a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button button-large button-next"><?php esc_html_e('Skip this step', 'multivendorx'); ?></a>
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e('Continue', 'multivendorx'); ?>" name="save_step" />
                <?php wp_nonce_field('mvx-setup'); ?>
            </p>
            <script>
            jQuery(document).ready(function($) {
                $('#marketplace_type').change(function() {
                    $('.woocommerce-error').hide();
                    $('.button-next').prop('disabled', false);

                    var selectedValue = $(this).val();
                    var selectedOption = $(this).find('option:selected');
                    var pluginCheck = selectedOption.data('plugin');

                    if (selectedValue === 'booking' && pluginCheck === 'inactive') {
                        $('#booking-error').show();
                        $('.button-next').prop('disabled', true);
                    } else if (selectedValue === 'subscription' && pluginCheck === 'inactive') {
                        $('#subscription-error').show();
                        $('.button-next').prop('disabled', true);
                    } else if (selectedValue === 'rental' && pluginCheck === 'inactive') {
                        $('#rental-error').show();
                        $('.button-next').prop('disabled', true);
                    } else if (selectedValue === 'auction' && pluginCheck === 'inactive') {
                        $('#auction-error').show();
                        $('.button-next').prop('disabled', true);
                    }
                });
            });
            </script>
        </form>
        <?php
    }

    /**
     * Ready to go content
     */
    public function mvx_setup_ready() {
        ?>
        
        <div class="mvx-all-done-page-header-sec">
            <i className="mvx-font icon-yes"></i></a>
            <h1 class="mvx-title"><?php esc_html_e('Yay! All done! ', 'multivendorx'); ?></h1>
            <a href="https://twitter.com/share" class="twitter-button" data-url="<?php echo site_url(); ?>" data-text="Hey Guys! Our new marketplace is now live and ready to be ransacked! Check it out at" data-via="wc_marketplace" data-size="large"><i class="mvx-font icon-twitter-setup-widget"></i> Tweet</a>
            <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
        </div>
        
        <div class="woocommerce-message woocommerce-tracker">
            <p><?php esc_html_e("Your marketplace is ready. It's time to bring some sellers on your platform and start your journey. We wish you all the success for your business, you will be great!", "multivendorx") ?></p>
        </div>
        <div class="wc-setup-next-steps">
            <div class="wc-setup-next-steps-first">
                <h2><?php esc_html_e( 'Next steps', 'multivendorx' ); ?></h2>
                <ul>
                    <li class="setup-product"><a class="button button-primary btn-red" href="<?php echo esc_url( admin_url( 'admin.php?page=mvx#&submenu=settings&name=registration' ) ); ?>"><?php esc_html_e( 'Create your vendor registration form', 'multivendorx' ); ?></a></li>
                </ul>
            </div>
            <div class="wc-setup-next-steps-last">
                <h2><?php _e( 'Learn more', 'multivendorx' ); ?></h2>
                <ul>
                    <li> <i class="mvx-font icon-watch-setup-widget"></i><a href="https://www.youtube.com/c/MultivendorX"><?php esc_html_e( 'Watch the tutorial videos', 'multivendorx' ); ?></a></li>
                    <li> <i class="mvx-font icon-help-setup-widget"></i><a href="https://multivendorx.com/knowledgebase/mvx-setup-guide/?utm_source=mvx_plugin&utm_medium=setup_wizard&utm_campaign=new_installation&utm_content=documentation"><?php esc_html_e( 'Looking for help to get started', 'multivendorx' ); ?></a></li>
                    <li> <i class="mvx-font icon-Learn-more-setup-widget"></i><a href="https://multivendorx.com/best-revenue-model-marketplace-part-one/?utm_source=mvx_plugin&utm_medium=setup_wizard&utm_campaign=new_installation&utm_content=blog"><?php esc_html_e( 'Learn more about revenue models', 'multivendorx' ); ?></a></li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * save store settings
     */
    public function mvx_setup_store_save() {
        check_admin_referer('mvx-setup');
        $general_settings = mvx_get_option('mvx_spmv_pages_tab_settings') ? mvx_get_option('mvx_spmv_pages_tab_settings') : array();
        $vendor_permalink = filter_input(INPUT_POST, 'vendor_store_url');
        $is_single_product_multiple_vendor = filter_input(INPUT_POST, 'is_single_product_multiple_vendor');
        if ($is_single_product_multiple_vendor) {
            $general_settings['is_singleproductmultiseller'] = array('is_singleproductmultiseller');
        } else if (isset($general_settings['is_singleproductmultiseller'])) {
            unset($general_settings['is_singleproductmultiseller']);
        }
        mvx_update_option('mvx_spmv_pages_tab_settings', $general_settings);
        if ($vendor_permalink) {
            $permalinks = mvx_get_option('dc_vendors_permalinks', array());
            $permalinks['vendor_shop_base'] = untrailingslashit($vendor_permalink);
            mvx_update_option('dc_vendors_permalinks', $permalinks);
            flush_rewrite_rules();
        }
        wp_redirect(esc_url_raw($this->get_next_step_link()));
        exit;
    }

    /**
     * save commission settings
     */
    public function mvx_setup_commission_save() {
        check_admin_referer('mvx-setup');
        $payment_settings = mvx_get_option('mvx_commissions_tab_settings');
        $revenue_sharing_mode = filter_input(INPUT_POST, 'revenue_sharing_mode');
        $commission_type = filter_input(INPUT_POST, 'commission_type');
        $default_commission = filter_input(INPUT_POST, 'default_commission');
        $default_percentage = filter_input(INPUT_POST, 'default_percentage');
        $fixed_with_percentage = filter_input(INPUT_POST, 'fixed_with_percentage');
        $fixed_with_percentage_qty = filter_input(INPUT_POST, 'fixed_with_percentage_qty');
        if ($revenue_sharing_mode) {
            $payment_settings['revenue_sharing_mode'] = $revenue_sharing_mode;
        }
        if ($commission_type) {
            switch($commission_type){
                case 'fixed':
                    $payment_settings['commission_type'] = array(
                        'value'=> __('fixed', 'multivendorx'),
                        'label'=> __('Fixed Amount', 'multivendorx'),
                        'index'=> 1,
                    );
                    $payment_settings['default_commission'] = $default_commission;
                    $payment_settings['default_commission'] = array(
                        0 => array (
                                'key' => 'fixed_ammount',
                                'value' => $default_commission,
                                )
                    );
                    break;

                case 'percent':
                    $payment_settings['commission_type'] = array(
                        'value'=> __('percent', 'multivendorx'),
                        'label'=> __('Percentage', 'multivendorx'),
                        'index'=> 2,
                    );
                    $payment_settings['default_percentage'] = $default_percentage;
                    $payment_settings['default_commission'] = array(
                        0 => array (
                            'key' => 'percent_amount',
                            'value' => $default_percentage
                        )
                    );
                    break;

                case 'fixed_with_percentage':
                    $payment_settings['commission_type'] = array(
                        'value'=> __('fixed_with_percentage', 'multivendorx'),
                        'label'=> __('%age + Fixed (per transaction)', 'multivendorx'),
                        'index'=> 3,
                    );
                    $payment_settings['fixed_with_percentage'] = $fixed_with_percentage;
                    $payment_settings['default_commission'] = array(
                        0 => array (
                            'key' => 'percent_amount',
                            'value' => $fixed_with_percentage
                        ),
                        1 => array (
                            'key' => 'percent_amount',
                            'value' => $default_percentage
                        )
                    );
                    break;

                case 'fixed_with_percentage_qty':
                    $payment_settings['commission_type'] = array(
                        'value'=> __('fixed_with_percentage_qty', 'multivendorx'),
                        'label'=> __('%age + Fixed (per unit)', 'multivendorx'),
                        'index'=> 4,
                    );
                    $payment_settings['fixed_with_percentage_qty'] = $fixed_with_percentage_qty;
                    $payment_settings['default_commission'] = array(
                        0 => array (
                            'key' => 'fixed_ammount',
                            'value' => $fixed_with_percentage_qty
                        ),
                        1 => array (
                            'key' => 'percent_amount',
                            'value' => $default_percentage
                        )
                    );
                    break;
            }
        }
        mvx_update_option('mvx_commissions_tab_settings', $payment_settings);
        
        wp_redirect(esc_url_raw($this->get_next_step_link()));
        exit;
    }

    /**
     * save payment settings
     */
    public function mvx_setup_payments_save() {
        check_admin_referer('mvx-setup');
        $gateways = $this->get_payment_methods();
        //$payment_settings = mvx_get_option('mvx_commissions_tab_settings');
        $active_module_list = mvx_get_option('mvx_all_active_module_list') ? get_option('mvx_all_active_module_list') : array();
        $disbursement_settings = mvx_get_option('mvx_disbursement_tab_settings');
        $mvx_disbursal_mode_admin = filter_input(INPUT_POST, 'mvx_disbursal_mode_admin');
        $mvx_disbursal_mode_vendor = filter_input(INPUT_POST, 'mvx_disbursal_mode_vendor');
        
        if ($mvx_disbursal_mode_admin) {
            $disbursement_settings['choose_payment_mode_automatic_disbursal'] = array('choose_payment_mode_automatic_disbursal');
            $payment_schedule = filter_input(INPUT_POST, 'payment_schedule');
            if ($payment_schedule) {
                $disbursement_settings['payment_schedule'] = $payment_schedule;
                $schedule = wp_get_schedule('masspay_cron_start');
                if ($schedule != $payment_schedule) {
                    if (wp_next_scheduled('masspay_cron_start')) {
                        $timestamp = wp_next_scheduled('masspay_cron_start');
                        wp_unschedule_event($timestamp, 'masspay_cron_start');
                    }
                    wp_schedule_event(time(), $payment_schedule, 'masspay_cron_start');
                }
            }
        } else if (isset($disbursement_settings['choose_payment_mode_automatic_disbursal'])) {
            unset($disbursement_settings['choose_payment_mode_automatic_disbursal']);
            if (wp_next_scheduled('masspay_cron_start')) {
                $timestamp = wp_next_scheduled('masspay_cron_start');
                wp_unschedule_event($timestamp, 'masspay_cron_start');
            }
        }

        if ($mvx_disbursal_mode_vendor) {
            $disbursement_settings['withdrawal_request'] = array('withdrawal_request');
        } else if (isset($disbursement_settings['withdrawal_request'])) {
            unset($disbursement_settings['withdrawal_request']);
        }

        foreach ($gateways as $gateway_id => $gateway) {
            $is_enable_gateway = filter_input(INPUT_POST, 'payment_method_' . $gateway_id);
            if ($is_enable_gateway) {
                //$payment_settings['payment_method_disbursement'][$gateway_id] = str_replace('payment_method_', '', $is_enable_gateway);
                array_push($active_module_list, $gateway_id);
                if (!empty($gateway['repo-slug'])) {
                    wp_schedule_single_event(time() + 10, 'woocommerce_plugin_background_installer', array($gateway_id, $gateway));
                }
            } else if (mvx_is_module_active($gateway_id)) {
                unset($active_module_list[$gateway_id]);
            }
        }
        //mvx_update_option('mvx_commissions_tab_settings', $payment_settings);
        mvx_update_option( 'mvx_all_active_module_list', $active_module_list );
        mvx_update_option('mvx_disbursement_tab_settings', $disbursement_settings);
        wp_redirect(esc_url_raw($this->get_next_step_link()));
        exit;
    }

    /**
     * save capability settings
     * @global object $mvx
     */
    public function mvx_setup_capability_save() {
        global $MVX;
        check_admin_referer('mvx-setup');
        $capability_settings = mvx_get_option('mvx_products_capability_tab_settings') ? mvx_get_option('mvx_products_capability_tab_settings') : array();

        $is_submit_product = filter_input(INPUT_POST, 'is_submit_product');
        $is_published_product = filter_input(INPUT_POST, 'is_published_product');
        $is_edit_delete_published_product = filter_input(INPUT_POST, 'is_edit_delete_published_product');
        $is_submit_coupon = filter_input(INPUT_POST, 'is_submit_coupon');
        $is_published_coupon = filter_input(INPUT_POST, 'is_published_coupon');
        $is_edit_delete_published_coupon = filter_input(INPUT_POST, 'is_edit_delete_published_coupon');
        $is_upload_files = filter_input(INPUT_POST, 'is_upload_files');

        if ($is_submit_product) {
            $capability_settings['is_submit_product'] = array('is_submit_product');
        } else if (isset($capability_settings['is_submit_product'])) {
            unset($capability_settings['is_submit_product']);
        }
        if ($is_published_product) {
            $capability_settings['is_published_product'] = array('is_published_product');
        } else if (isset($capability_settings['is_published_product'])) {
            unset($capability_settings['is_published_product']);
        }
        if ($is_edit_delete_published_product) {
            $capability_settings['is_edit_delete_published_product'] = array('is_edit_delete_published_product');
        } else if (isset($capability_settings['is_edit_delete_published_product'])) {
            unset($capability_settings['is_edit_delete_published_product']);
        }
        if ($is_submit_coupon) {
            $capability_settings['is_submit_coupon'] = array('is_submit_coupon');
        } else if (isset($capability_settings['is_submit_coupon'])) {
            unset($capability_settings['is_submit_coupon']);
        }
        if ($is_published_coupon) {
            $capability_settings['is_published_coupon'] = array('is_published_coupon');
        } else if (isset($capability_settings['is_published_coupon'])) {
            unset($capability_settings['is_published_coupon']);
        }
        if ($is_edit_delete_published_coupon) {
            $capability_settings['is_edit_delete_published_coupon'] = array('is_edit_delete_published_coupon');
        } else if (isset($capability_settings['is_edit_delete_published_coupon'])) {
            unset($capability_settings['is_edit_delete_published_coupon']);
        }
        if ($is_upload_files) {
            $capability_settings['is_upload_files'] = array('is_upload_files');
        } else if (isset($capability_settings['is_upload_files'])) {
            unset($capability_settings['is_upload_files']);
        }
        mvx_update_option('mvx_products_capability_tab_settings', $capability_settings);
        $MVX->vendor_caps->update_mvx_vendor_role_capability();
        wp_redirect(esc_url_raw($this->get_next_step_link()));
        exit;
    }

    /**
     * import dummy data
     */
    public function mvx_setup_dummy_data_import(){
        check_admin_referer('mvx-setup');
        $marketplace_type = filter_input(INPUT_POST, 'marketplace_type');

        $this->import_vendors();
        $product_ids = $this->import_products( $marketplace_type );
        $this->import_commissions();
        if( $product_ids ){
            $this->import_orders( $product_ids, $marketplace_type );
            $this->import_reviews($product_ids);
        }
        
        wp_redirect(esc_url_raw($this->get_next_step_link()));
        exit;
    }

    /**
     * Import the Dummy vendors
     */
    public function import_vendors(){
        // load the vendor data xml file
        $xml = simplexml_load_file( MVX_PLUGIN_DUMMY_DATA . '\vendors.xml' );
        if( ! $xml ){
            return;
        }
        
        // importing vendors
        foreach( $xml->vendor as $vendor){
            $userdata = array(
                'user_login'    => (string) $vendor->username,
                'user_pass'     => (string) $vendor->password,
                'user_email'    => (string) $vendor->email,
                'user_nicename' => (string) $vendor->nickname,
                'first_name'    => (string) $vendor->firstname,
                'last_name'     => (string) $vendor->lastname,
                'role'          => 'dc_vendor',
            );

            $user_id = wp_insert_user( $userdata ) ;

            if ( is_wp_error( $user_id ) ) {
                return;
            }

            foreach( $vendor->images->image as $image ){
                $src    =  plugins_url( $image->src );
                $upload = wc_rest_upload_image_from_url( esc_url_raw($src) );

                if( is_wp_error( $upload )){
                    continue;
                }
                $attachment_id = wc_rest_set_uploaded_image_as_attachment( $upload, $user_id );

                if ( wp_attachment_is_image( $attachment_id ) ) {
                    $parent = get_post($attachment_id);
                    $url    = $parent->guid;

                    $size       = @getimagesize($url);
                    $image_type = ( $size ) ? $size['mime'] : 'image/jpeg';
                    $object     = array(
                        'ID'             => $attachment_id,
                        'post_title'     => basename($url),
                        'post_mime_type' => $image_type,
                        'guid'           => $url,
                        'post_status'    => 'inherit',
                        );

                    $attachment_id = wp_insert_attachment($object, $url);

                    $metadata = wp_generate_attachment_metadata($attachment_id, $url);
                    wp_update_attachment_metadata($attachment_id, $metadata);

                    if ( 'store' == $image->position ) {
                        update_user_meta( $user_id, '_vendor_image', $attachment_id );   
                    } elseif ( 'cover' == $image->position ) {
                        update_user_meta( $user_id, '_vendor_banner', $attachment_id );
                    }
                }
            }
        }
    }

    /**
     * Import Dummy products
     * @global object $mvx
     */
    public function import_products($marketplace_type){
        global $MVX;

        $xml = simplexml_load_file( MVX_PLUGIN_DUMMY_DATA . '\\'. $marketplace_type .'_products.xml' );
        if( ! $xml ){
            return;
        }
        
        $product_ids = array();

        // importing products
        foreach( $xml->item as $product){

            // Get vendor Id
            $vendor = get_user_by( 'slug', (string) $product->author );
            if ($vendor) {
                $vendor_id = $vendor->ID;
            }else {
                $vendor    = get_user_by( 'slug', 'admin');
                $vendor_id = $vendor->ID;
            }

            $product_data = array(
                'post_title'    => (string) $product->title, // Product Title
                'post_content'  => (string) $product->content, // Product Description
                'post_excerpt'  => (string) $product->excerpt, // Short Description
                'post_author'   => $vendor_id, // Vendor Id
                'post_name'     => (string) $product->post_name, // post name
                'post_status'   => (string) $product->status, // Publish the product
                'post_type'     => (string) $product->post_type, // WooCommerce product type
            );

            $product_id = wp_insert_post( $product_data );

            if ( is_wp_error( $product_id ) ) {
                return null;
            }
            // set the product type
            wp_set_object_terms( $product_id, (string) $product->product_type, 'product_type' );

            // Set product vendor
            $vendor = get_mvx_vendor( $vendor_id );
            if($vendor){
                wp_set_object_terms( $product_id, absint($vendor->term_id), $MVX->taxonomy->taxonomy_name );
            }
            // Set product category
            foreach( $product->categories->category as $category){
                $category_name = (string) $category;
                $term          = term_exists( $category_name, 'product_cat' );

                // If the category doesn't exist, create it
                if ( ! $term ) {
                    $term = wp_insert_term( $category_name, 'product_cat' );
                }

                if ( ! is_wp_error( $term ) ) {
                    // Get the term ID 
                    $term_id = is_array( $term ) ? $term['term_id'] : $term;

                    // Assign the category to the product
                    wp_set_object_terms( $product_id, (int)$term_id, 'product_cat', true );
                }
            }

            // Set product meta data
            foreach( $product->meta->postmeta as $meta){
                update_post_meta( $product_id, (string) $meta->meta_key, (string) $meta->meta_value );
            }

            array_push( $product_ids, $product_id);
            
            foreach( $product->images->image as $image ){
                $src    = plugins_url( $image->src );
                $upload = wc_rest_upload_image_from_url( esc_url_raw($src) );

                if( is_wp_error( $upload )){
                    continue;
                }
                $attachment_id = wc_rest_set_uploaded_image_as_attachment( $upload );

                if ( wp_attachment_is_image( $attachment_id ) ) {
                    $parent = get_post($attachment_id);
                    $url    = $parent->guid;

                    $size       = @getimagesize($url);
                    $image_type = ( $size ) ? $size['mime'] : 'image/jpeg';
                    $object     = array(
                        'ID' => $attachment_id,
                        'post_title' => basename($url),
                        'post_mime_type' => $image_type,
                        'guid' => $url,
                        'post_status'    => 'inherit',
                        );

                    $attachment_id = wp_insert_attachment($object, $url);

                    $metadata = wp_generate_attachment_metadata($attachment_id, $url);
                    wp_update_attachment_metadata($attachment_id, $metadata);

                    update_post_meta( $product_id, '_thumbnail_id', $attachment_id );
                }
            }
        }
        return $product_ids;
    }

    /**
     * Import commissions data
     */
    public function import_commissions(){
        // importing commission structure
        $xml = simplexml_load_file( MVX_PLUGIN_DUMMY_DATA . '\commissions.xml' );
        if( ! $xml){
            return;
        }

        foreach( $xml->commission as $commission ) {
            $category_name = (string) $commission->category;
            $term          = term_exists( $category_name, 'product_cat' );

            // If the category doesn't exist, create it
            if ( ! $term ) {
                $term = wp_insert_term( $category_name, 'product_cat' );
            }

            if ( is_wp_error( $term ) ) {
                return;
            }
            // Get the term ID 
            $term_id = is_array( $term ) ? $term['term_id'] : $term;

            // Get commission type
            $commission_type_value = get_mvx_vendor_settings('commission_type', 'commissions') && !empty(get_mvx_vendor_settings('commission_type', 'commissions')) ? mvx_get_settings_value(get_mvx_vendor_settings('commission_type', 'commissions')) : '';
            if ($commission_type_value) {
                switch($commission_type_value){
                    case 'fixed':        
                    case 'percent':
                        add_term_meta( $term_id, 'commision', floatval((string) $commission->value));
                        break;
    
                    case 'fixed_with_percentage':        
                    case 'fixed_with_percentage_qty':
                        add_term_meta( $term_id, 'commission_percentage', floatval((string) $commission->value));
                        break;
                }
            }
        }
    }

    /**
     * Import dummy orders
     * @global object $mvx
     */
    public function import_orders( $product_ids, $marketplace_type ){
        global $MVX;
        $product_ids = array_reverse( $product_ids );

        // importing orders
        $xml = simplexml_load_file( MVX_PLUGIN_DUMMY_DATA . '\orders.xml' );
        if( ! $xml){
            return;
        }

        foreach( $xml->order as $order){
            $new_order = wc_create_order();
            $order_id = $new_order->get_id();

            $product_id = array_pop( $product_ids );
            $quantity   = $order->quantity;
            $new_order->add_product( wc_get_product( $product_id ), $quantity );

            $address = array(
                'first_name' => (string) $order->first_name,
                'last_name'  => (string) $order->last_name,
                'email'      => (string) $order->email,
                'address_1'  => (string) $order->address_1,
                'address_2'  => (string) $order->address_2,
                'city'       => (string) $order->city,
                'state'      => (string) $order->state,
                'postcode'   => (string) $order->postcode,
                'country'    => (string) $order->country,
            );

            $new_order->set_billing_address( $address );
            $new_order->set_shipping_address( $address );
            $order_items = $new_order->get_items();
            $cost = intval(get_post_meta( $product_id, '_wc_booking_block_cost', true )) + intval(get_post_meta( $product_id, '_wc_booking_cost', true ));

            if( $marketplace_type == 'booking' ){
                $props = array(
                    'customer_id'   => '1',
                    'product_id'    => $product_id,
                    'resource_id'   => '',
                    'person_counts' => 'a:0:{}',
                    'cost'          => $cost,
                    'start'         => strtotime( current_time( 'Y-m-d' ). '00:00:00' ),
                    'end'           => strtotime( current_time( 'Y-m-d' ). '23:59:59' ),
                    'all_day'       => 1,
                );

                $booking = new WC_Booking( $props );
                $booking->set_order_id( $order_id );
                $item = reset( $order_items );
                $item['subtotal'] = $cost;
                $item['total'] = $cost;
                $item_id = key( $order_items );
                $booking->set_order_item_id( $item_id );
                $booking->set_cost(intval(get_post_meta( $product_id, '_wc_booking_block_cost', true )) + intval(get_post_meta( $product_id, '_wc_booking_cost', true )));
                $booking->set_status( 'unpaid' );
                $booking->save();
            }

            $shipping_item = new WC_Order_Item_Shipping();
            $shipping_item->set_method_title( 'Free shipping' );
            $shipping_item->set_method_id( 'free shipping' );

            // Set the shipping cost to 0
            $shipping_item->set_total( 0 );

            foreach( $order_items as $item_id => $item ){
                $vendor = get_mvx_product_vendors( $item['product_id'] );
                if ($vendor) {
                    if ( !wc_get_order_item_meta( $item_id, 'Sold by') ) 
                        wc_add_order_item_meta( $item_id, 'Sold by', $vendor->page_title);
                    if ( !wc_get_order_item_meta( $item_id, '_vendor_id' ) ) 
                        wc_add_order_item_meta( $item_id, '_vendor_id', $vendor->id);
                }
            }

            // Add the shipping item to the order
            $shipping_item->save();
            $new_order->add_item( $shipping_item );
        
            $new_order->set_payment_method( (string) $order->payment_method );
            $new_order->set_payment_method_title( (string) $order->payment_method_title );

            $new_order->update_meta_data('has_mvx_sub_order', true);
            $vendor_items = array();
            foreach ($order_items as $item_id => $item) {
                $has_vendor = get_mvx_product_vendors($item['product_id']);
                if ($has_vendor) {
                    $variation_id = isset($item['variation_id']) && !empty($item['variation_id']) ? $item['variation_id'] : 0;
                    $item_commission = $MVX->commission->get_item_commission($item['product_id'], $variation_id, $item, $order_id, $item_id);
                    $commission_values = $MVX->commission->get_commission_amount($item['product_id'], $has_vendor->term_id, $variation_id, $item_id, $order);
                    $commission_rate = array('mode' => $MVX->vendor_caps->payment_cap['revenue_sharing_mode'], 'type' => $MVX->vendor_caps->payment_cap['commission_type']);
                    $commission_rate['commission_val'] = isset($commission_values['commission_val']) ? $commission_values['commission_val'] : 0;
                    $commission_rate['commission_fixed'] = isset($commission_values['commission_fixed']) ? $commission_values['commission_fixed'] : 0;
                    $item['commission'] = $item_commission;
                    $item['commission_rate'] = $commission_rate;
                    $vendor_items[$has_vendor->id][$item_id] = $item;
                }
            }

            if (count($vendor_items) != 0){
                // update parent order meta
                $new_order->update_meta_data('has_mvx_sub_order', true);

                foreach ($vendor_items as $vendor_id => $items) {
                    $vendor_orderid = MVX_Order::create_vendor_order($new_order, array(
                                'order_id' => $order_id,
                                'vendor_id' => $vendor_id,
                                'line_items' => $items
                    ));

                    $vendor_order = wc_get_order( $vendor_orderid );
                    $commission_id = MVX_Commission::create_commission($vendor_order);
                    if ($commission_id) {
                        // Calculate commission
                        MVX_Commission::calculate_commission($commission_id, $vendor_order);
                        // add commission id with associated vendor order
                        $vendor_order->update_meta_data('_commission_id', $commission_id );
                        // Mark commissions as processed
                        $vendor_order->update_meta_data('_commissions_processed', 'yes' );
                        $vendor_order->save();
                    }
                }
            }
        
            $new_order->calculate_totals();
            $new_order->update_status( 'processing', 'Order imported' );
            $new_order->save();
        }
    }

    /**
     * Import dummy reviews
     */
    public function import_reviews( $product_ids ){
        $product_ids = array_reverse( $product_ids );

        // importing reviews
        $xml = simplexml_load_file( MVX_PLUGIN_DUMMY_DATA . '\reviews.xml' );
        if( ! $xml){
            return;
        }

        foreach ( $xml->review as $review ) {
            $product_id = array_pop( $product_ids );

            $comment_data = array(
                'comment_post_ID'      => $product_id,
                'comment_author'       => (string) $review->reviewer,
                'comment_author_email' => (string) $review->email,
                'comment_content'      => (string) $review->comment,
                'comment_type'         => 'review',
                'comment_approved'     => 1,
            );
        
            $comment_id = wp_insert_comment( $comment_data );

            if ( is_wp_error( $comment_id ) ) {
                return;
            }
            update_comment_meta( $comment_id, 'rating', intval( $review->rating ) );

            $average_rating = intval( get_post_meta( $product_id, '_wc_average_rating', true ) );
            $review_count   = intval( get_post_meta( $product_id, '_wc_review_count', true ) );

            if($review_count == 1){
                $average_rating = intval( $review->rating );
            } else{
                $average_rating = ( ( $average_rating * ( $review_count - 1 ) ) + intval( $review->rating ) ) / $review_count;
            }

            update_post_meta( $product_id, '_wc_average_rating', $average_rating );
        }
    }

    /**
     * Migration Introduction step.
     */
    public function mvx_migration_introduction() {
        global $MVX;
        $MVX->multivendor_migration->mvx_migration_first_step( $this->get_next_step_link() );
    }
    
    public function mvx_migration_store_process() {
        global $MVX;
        $MVX->multivendor_migration->mvx_migration_third_step( $this->get_next_step_link() );
    }

    /**
     * Setup Wizard Footer.
     */
    public function setup_wizard_footer() {
        if ('next_steps' === $this->step) :
            ?>
            <a class="wc-return-to-dashboard" href="<?php echo esc_url(admin_url()); ?>"><?php esc_html_e('Return to the WordPress Dashboard', 'multivendorx'); ?></a>
        <?php endif; ?>
            </body>
        </html>
        <?php
    }

    public function get_payment_methods() {
        $methods = array(
            'paypal_masspay' => array(
                'label' => __('Paypal Masspay', 'multivendorx'),
                'description' => __('Pay via paypal masspay', 'multivendorx'),
                'class' => 'featured featured-row-last'
            ),
            'paypal_payout' => array(
                'label' => __('Paypal Payout', 'multivendorx'),
                'description' => __('Pay via paypal payout', 'multivendorx'),
                'class' => 'featured featured-row-first'
            ),
            'direct_bank' => array(
                'label' => __('Direct Bank Transfer', 'multivendorx'),
                'description' => __('', 'multivendorx'),
                'class' => ''
            ),
            'stripe_masspay' => array(
                'label' => __('Stripe Connect', 'multivendorx'),
                'description' => __('', 'multivendorx'),
                //'repo-slug' => 'marketplace-stripe-gateway',
                'class' => ''
            )
        );
        return $methods;
    }

}

new MVX_Admin_Setup_Wizard();