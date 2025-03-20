<?php

namespace MultiVendorX;

/**
 * Catalog Modules Class
 *
 * @version		2.2.0
 * @package		MultivendorX
 * @author 		MultiVendorX
 */

class Modules {
    /**
     * Option's table key for active module list.
     * @var string
     */
    const ACTIVE_MODULES_DB_KEY = "mvx_all_active_module_list";

    /**
     * List of all module.
     * @var array
     */
    private $modules = [];

    /**
     * List of all active module.
     * @var array
     */
    private $active_modules = [];

    /**
     * State for modules are activated or not.
     * @var bool
     */
    private static $module_activated = false;

    /**
     * Container that store the object of active module
     * @var array
     */

    private $container = [];

    function __construct() {
    }

    /**
     * Get list of all multivendorX module.
     * @return array
     */
    public function get_all_modules() {
        if ( ! $this->modules ) {

            $this->modules = apply_filters( 'multivendorx_modules', [
                'simple' => [
                    'id'             => 'simple',
                    'module_file'    => MVX()->plugin_path . 'modules/Simple/Module.php',
                    'module_class'   => 'MultiVendorX\simple\Module',
                ],
                'variable' => [
                    'id'             => 'variable',
                    'module_file'    => MVX()->plugin_path . 'modules/Variable/Module.php',
                    'module_class'   => 'MultiVendorX\variable\Module',
                ],
                'external'    => [
                    'id'             => 'external',
                    'module_file'    => MVX()->plugin_path . 'modules/External/Module.php',
                    'module_class'   => 'MultiVendorX\external\Module',
                ],
            ]);
        }
    }

    /**
     * Load all active modules
     * @return void
     */
    public function load_active_modules() {
        if ( self::$module_activated ) {
            return;
        }

        $active_modules    = $this->get_active_modules();
        $all_modules       = $this->get_all_modules();
        $activated_modules = [];

        foreach ( $active_modules as $modules_id ) {
            if ( ! isset( $all_modules[ $modules_id ] ) ) {
                continue;
            }

            $module = $all_modules[ $modules_id ];


            // Store the module as active module
            if ( file_exists( $module['module_file'] ) ) {
                $activated_modules[] = $modules_id;
            }

            // Activate the module
            if ( file_exists( $module['module_file'] ) && ! in_array( $modules_id, $this->container ) ) {
                require_once $module['module_file'];

                $module_class = $module['module_class'];
                $this->container[ $modules_id ] = new $module_class();

                /**
                 * Module activation hook
                 * 
                 * @param object $name module object
                 */
                do_action( 'multivendorx_activated_module_' . $modules_id, $this->container[ $modules_id ] );
            }
        }
    }
    /**
     * Get all active modules
     * @return array
     */
    public function get_active_modules() {

        // If active modules are loaded return it
        if ( $this->active_modules ) {
            return $this->active_modules;
        }

        $this->active_modules = MVx()->setting->get_option( self::ACTIVE_MODULES_DB_KEY, [] );

        return $this->active_modules;
    }

    /**
     * Activate modules
     * @param array $modules
     * @return array|mixed
     */
    public function activate_modules( $modules ) {
        $active_modules = $this->get_active_modules();

        $this->active_modules = array_unique( array_merge( $active_modules, $modules ) );

        update_option( self::ACTIVE_MODULES_DB_KEY, $this->active_modules );

        self::$module_activated = false;

        $this->load_active_modules();

        return $this->active_modules;
    }

    /**
     * Defactivate modules.
     * @param array $modules
     * @return void
     */
    public function deactivate_modules( $modules ) {
        $active_modules = $this->get_active_modules();

        foreach ( $modules as $module_id ) {
            $active_modules = array_diff( $active_modules, [ $module_id ] );
        }

        $active_modules = array_values( $active_modules );

        $this->active_modules = $active_modules;
        error_log("active modules : ".print_r($this->active_modules,true));

        update_option( self::ACTIVE_MODULES_DB_KEY, $this->active_modules );

        add_action(
            'shutdown', function () use ( $modules ) {
                foreach ( $modules as $module_id ) {
                    /**
                     * Module deactivation hook
                     * @param object $module deactivated module object
                     */
                    do_action( 'multivendorx_activated_module_' . $module_id, $this->container[$module_id] );
                }
            }
        );

        return $this->active_modules;
    }

    /**
     * Check a module is active or not
     * @param mixed $module_id
     * @return bool
     */
    public function is_active( $module_id ) {
        $active_modules = $this->get_active_modules();

        return in_array( $module_id, $active_modules, true );
    }
    
}