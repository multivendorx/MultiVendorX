<?php

namespace MultiVendorX;

use Automattic\WooCommerce\Utilities\OrderUtil as WCOrderUtil;

/**
 * MVX Main Class
 *
 * @version		2.2.0
 * @package		MultivendorX
 * @author 		MultiVendorX
 */
defined('ABSPATH') || exit;


final class MultiVendorX {
    private static $instance = null;
    private $file            = '';
    private $container       = [];

    /**
     * Class construct
     * @param object $file
     */
    public function __construct($file) {
        $this->file = $file;
        $this->container[ 'plugin_url' ]     = trailingslashit( plugins_url( '', $file ) );
        $this->container[ 'plugin_path' ]    = trailingslashit( dirname( $file ) );
        $this->container[ 'version' ]        = MVX_PLUGIN_VERSION;
        $this->container[ 'rest_namespace' ] = MVX_REST_NAMESPACE;

        register_activation_hook( $file, [ $this, 'activate' ] );
        register_deactivation_hook( $file, [ $this, 'deactivate' ] );

        add_action( 'before_woocommerce_init', [ $this, 'declare_compatibility' ] );
        add_action( 'woocommerce_loaded', [ $this, 'init_plugin' ] );
        add_action( 'plugins_loaded', [ $this, 'is_woocommerce_loaded'] );
    }

    /**
     * Placeholder for activation function.
     * @return void
     */
    public function activate() {
        $this->container['install'] = new Install\Installer();

        flush_rewrite_rules();
    }

    /**
     * Placeholder for deactivation function.
     * @return void
     */
    public function deactivate() {
        delete_option('dc_product_vendor_plugin_page_install');
        delete_option('mvx_flushed_rewrite_rules');
    }

    /**
     * Add High Performance Order Storage Support
     * @return void
     */
    public function declare_compatibility() {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility ( 'custom_order_tables', WP_CONTENT_DIR.'/plugins/dc-woocommerce-multi-vendor/dc_product_vendor.php', true );
        
    }

    public function init_plugin() {
        
        /**
         * Should be romoved letter 
         */
        $file = $this->file;
        global $MVX;
        require_once( 'class-mvx.php' );
        /* recheck plugin install */
        add_action('plugins_loaded', 'activate_mvx_plugin');
        /* Initiate plugin main class */
        $MVX = new \MVX($file);
        $GLOBALS['MVX'] = $MVX;
        if (is_admin() && !defined('DOING_AJAX')) {
            add_filter('plugin_action_links_' . plugin_basename($file), 'mvx_action_links');
        }
        
        
        $this->includes_files();
        $this->init_hooks();
        
        do_action( 'multivendorx_loaded' );
    }

    private function includes_files() {
        
    }

    private function init_hooks() {
        add_action('init', [$this, 'plugin_init']);
        add_action('init', [$this, 'init_classes']);
        add_action('admin_init', [$this, 'redirect_to_mvx_setup'], 5);
    }

    public function plugin_init() {
        $this->admin_setup_wizard();
    }

    /**
     * Init all multivendorx classess.
     * Access this classes using magic method.
     * @return void
     */
    public function init_classes() {
        $this->container['utility']     = new Utility\Utility();
        $this->container['order']       = new Order\OrderManager();
        $this->container['commission']  = new Commission\CommissionManager();
        $this->container['gateways']    = new Gateways\GatewaysManager();
        $this->container['restapi']	 	= new Api\Rest();
    }

    
    /**
     * Load admin setup wizard class.
     * @return void
     */
    private function admin_setup_wizard() {
        $current_page = filter_input(INPUT_GET, 'page');
        if ($current_page && $current_page == 'mvx-setup') {
            $this->container['SetupWizard'] = new Admin\SetupWizard();
        }
    }
    
    /**
     * Redirect to multivendero setup page.
     * Delete WooCommerce activation redirect transient.
     * @return void
     */
    public function redirect_to_mvx_setup() {
        if ( get_transient( '_wc_activation_redirect' ) ) {
            delete_transient( '_wc_activation_redirect' );
            return;
        }
        if ( get_transient( '_mvx_activation_redirect' ) ) {
            delete_transient( '_mvx_activation_redirect' );
            if ( filter_input(INPUT_GET, 'page') === 'mvx-setup'
            || filter_input(INPUT_GET, 'activate-multi')
            || apply_filters( 'mvx_prevent_automatic_wizard_redirect', false )
            ) {
                return;
            }
            wp_safe_redirect( admin_url( 'index.php?page=mvx-setup' ) );
            exit;
        }
    }
    
    /**
     * Check whether Woocommerce is installed
     * @return bool
     */
    public function is_woocommerce_installed() {
        $plugin_dir = plugin_dir_path($this->file);
        return file_exists($plugin_dir . '../woocommerce/woocommerce.php');
    }

    /**
     * Check whether woocommerce is active
     * @return bool
     */
    public function is_woocommerce_active() {
        return class_exists( '\WooCommerce' );
    }
    
    /**
     * Take action based on if woocommerce is not loaded
     * @return void
     */
    public function is_woocommerce_loaded() {
        if ( did_action( 'woocommerce_loaded' ) || ! is_admin() ) {
            return;
        }
        add_action('admin_notices', [$this, 'woocommerce_admin_notice']);
    }

    /**
     * Display Woocommerce inactive notice.
     * @return void
     */
    function woocommerce_admin_notice() {

        ?>
        <div class="error">
            <p>
                <?php _e('MultiVendorX plugin requires <a href='
                . ($this->is_woocommerce_installed() ?
                    esc_url( network_admin_url('plugins.php?s=woocommerce.php&plugin_status=all') ) :
                    esc_url( network_admin_url('plugin-install.php?s=woocommerce&tab=search&type=term') ) )
                . '>WooCommerce</a> plugins to be active!', 'multivendorx');
                ?>
            </p>
        </div>
        <?php
    }

    /**
     * Magic getter function to get the reference of class.
     * Accept class name, If valid return reference, else Wp_Error. 
     * @param   mixed $class
     * @return  object | \WP_Error
     */
    public function __get( $class ) {
        if ( array_key_exists( $class, $this->container ) ) {
            return $this->container[ $class ];
        }
        return new \WP_Error(sprintf('Call to unknown class %s.', $class));
    }

    /**
     * Initializes the MultiVendorX class.
     * Checks for an existing instance
     * And if it doesn't find one, create it.
     * @param mixed $file
     * @return object | null
     */
    public static function init($file) {
        if ( self::$instance === null ) {
            self::$instance = new self($file);
        }
        return self::$instance;
    }
}