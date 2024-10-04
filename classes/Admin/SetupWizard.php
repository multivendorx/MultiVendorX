<?php

namespace MultiVendorX\Admin;

/**
 * Setup Wizard Class
 * 
 * @since 2.7.7
 * @package MultiVendorX
 * @author MultivendorX
 */

defined('ABSPATH') || exit;

class SetupWizard {

    public function __construct() {
        add_action('admin_menu', [$this, 'admin_menus']);
        add_action('admin_enqueue_scripts', [ $this, 'setup_scripts']);
    }

    /**
     * Add admin menus/screens.
     */
    public function admin_menus() {
        add_dashboard_page('', '', 'manage_options', 'mvx-setup', [$this, 'setup_wizard']);
    }

    /**
     * Show the setup wizard.
     */
    public function setup_wizard() {
        global $MVX;
        if (filter_input(INPUT_GET, 'page') !== 'mvx-setup') {
            return;
        }

        // if (!$MVX->multivendor_migration->mvx_is_marketplace()) {
        //     unset( $default_steps['introduction-migration'], $default_steps['store-process'] );
        // }
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
        $this->render_setup_wizard();
    }

    function render_setup_wizard() {
        ?>
        <div id="mvx_setup_wizard"></div>
        <?php
    }

    public function setup_scripts() {
        $current_screen = get_current_screen();

        if ( $current_screen->id === 'dashboard_page_mvx-setup' ) {
            wp_enqueue_script('setup_wizard_js', MVX()->plugin_url . 'mvx-modules/build/blocks/setupWizard/index.js', [ 'jquery', 'jquery-blockui', 'wp-element', 'wp-i18n' ], MVX()->version, true);
            wp_localize_script(
                'setup_wizard_js', 'appLocalizer', [
                'apiUrl' => untrailingslashit(get_rest_url()),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'redirect_url' => admin_url(),
                'woocommerce_installed' => MVX()->is_woocommerce_installed(),
            ]);
        }
    }

    /**
     * Get the URL for the next step's screen.
     * @param string step   slug (default: current step)
     * @return string       URL for next step if a next step exists.
     *                      Admin URL if it's the last step.
     *                      Empty string on failure.
     * @since 2.7.7
     */
    // public function get_next_step_link($step = '') {
    //     if (!$step) {
    //         $step = $this->step;
    //     }

    //     $keys = array_keys($this->steps);
    //     if (end($keys) === $step) {
    //         return admin_url();
    //     }

    //     $step_index = array_search($step, $keys);
    //     if (false === $step_index) {
    //         return '';
    //     }

    //     return add_query_arg('step', $keys[$step_index + 1]);
    // }

    /**
     * Migration Introduction step.
     */
    // public function mvx_migration_introduction() {
    //     global $MVX;
    //     $MVX->multivendor_migration->mvx_migration_first_step( $this->get_next_step_link() );
    // }
    
    // public function mvx_migration_store_process() {
    //     global $MVX;
    //     $MVX->multivendor_migration->mvx_migration_third_step( $this->get_next_step_link() );
    // }

}