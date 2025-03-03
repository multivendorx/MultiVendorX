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
     * Get all active modules
     * @return array
     */
    public function get_active_modules() {

        // If active modules are loaded return it
        if ( $this->active_modules ) {
            return $this->active_modules;
        }

        $this->active_modules = MVx()->setting->mvx_get_option( self::ACTIVE_MODULES_DB_KEY, [] );

        return $this->active_modules;
    }

    
}