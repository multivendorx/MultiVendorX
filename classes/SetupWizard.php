<?php

namespace MultiVendorX;

/**
 * Setup Wizard Class
 * 
 */
if (!defined('ABSPATH')) {
    exit;
}

class SetupWizard {

    public function __construct() {

        add_action( 'admin_menu', [$this, 'admin_menus'] );
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts'] );
    }

    /**
     * Add admin menus/screens.
     */
    public function admin_menus() {
        add_dashboard_page('', '', 'manage_options', 'multivendorx_setup', [$this, 'render_setup_wizard']);
    }
    
    function render_setup_wizard() {
        ?>
        <div id="multivendorx_setup_wizard">
        </div>
        <?php
    }
    
    function admin_scripts() {
        $current_screen = get_current_screen();

        if ( $current_screen->id === 'dashboard_page_multivendorx_setup' ) {
            wp_enqueue_script('setup-wizard-script', MVX()->plugin_url . 'build/blocks/setupWizard/index.js', [ 'jquery', 'jquery-blockui', 'wp-element', 'wp-i18n', 'react-jsx-runtime'  ], MVX()->version, true);
            wp_set_script_translations( 'setup-wizard-script', 'multivendorx' );
            wp_enqueue_style('setup-wizard-style', MVX()->plugin_url . 'build/blocks/setupWizard/index.css');
            wp_localize_script(
                'setup-wizard-script', 'appLocalizer', [
                'apiUrl' => untrailingslashit(get_rest_url()),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'restUrl' => MVX()->rest_namespace,
                'redirect_url' => admin_url() . 'admin.php?page=multivendorx#&tab=modules',
                'adminUrl'  => esc_url(admin_url()),
                'siteUrl'  => site_url(),
                'registration_form_url' => esc_url( admin_url( 'admin.php?page=multivendorx#&tab=settings&subtab=registration' )),
            ]);
        }
    }

}
