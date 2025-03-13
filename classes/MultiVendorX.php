<?php

namespace MultiVendorX;

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
    private $file = '';
    private $container = [];
    
    /**
     * Class construct
     * @param object $file
     */
    public function __construct($file) {
        require_once trailingslashit(dirname($file)).'mvx-config.php';

		$this->file = $file;
        $this->container[ 'plugin_url' ]     = trailingslashit( plugins_url( '', $plugin = $file ) );
        $this->container[ 'plugin_path' ]    = trailingslashit( dirname( $file ) );
        $this->container[ 'version' ]        = MVX_PLUGIN_VERSION;
        $this->container[ 'rest_namespace' ] = MVX_REST_NAMESPACE;
		$this->container[ 'block_paths' ]    = [];
        add_action( 'woocommerce_loaded', [ $this, 'init_plugin' ] );
    }


    public function init_plugin() {
        
        /**
         * Should be romoved letter 
         */
        $this->init_classes();
        add_action( 'init', [ $this, 'multivendorx_setup_wizard' ] );
        do_action( 'multivendorx_loaded' );
    }

    public function init_classes() {
        $this->container['setting']     = new Setting();
        $this->container['admin']    	= new Admin();
		$this->container['modules']	 	= new Modules();
        $this->container['restapi']	 	= new Rest();
		// Load all active modules
		// $this->container['modules']->load_active_modules();
	}

     /**
     * Load admin setup wizard class.
     * @return void
     */
    public function multivendorx_setup_wizard() {
        $current_page = filter_input(INPUT_GET, 'page');
        if ($current_page && $current_page == 'multivendorx_setup') {
            $this->container['SetupWizard'] = new SetupWizard();
        }
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