<?php
/**
 * MultiVendorX Module class file
 *
 * @package MultiVendorX
 */

namespace MultiVendorX\MarketplaceRefund;

/**
 * MultiVendorX Refund Module class
 *
 * @class       Module class
 * @version     5.0.0
 * @author      MultiVendorX
 */
class Module {
    /**
     * Container contain all helper class
     *
     * @var array
     */
    private $container = array();

    /**
     * Contain reference of the class
     *
     * @var Module|null
     */
    private static $instance = null;

    /**
     * Simple class constructor function
     */
    public function __construct() {
        // Init helper classes.
        $this->init_classes();
    }

    /**
     * Init helper classes
     *
     * @return void
     */
    public function init_classes() {
        $this->container['frontend'] = new Frontend();
        $this->container['admin']    = new Admin();
        $this->container['refund']   = new Refund();
        $this->container['return']   = new Return_Rest();
    }

    /**
     * Magic getter function to get the reference of class.
     * Accept class name, If valid return reference, else Wp_Error.
     *
     * @param   mixed $class Name of the class to retrieve from the container.
     * @return  object | \WP_Error
     */
    public function __get( $class ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.classFound
        if ( array_key_exists( $class, $this->container ) ) {
            return $this->container[ $class ];
        }
        return new \WP_Error( sprintf( 'Call to unknown class %s.', $class ) );
    }

    /**
     * Magic setter function to store a reference of a class.
     * Accepts a class name as the key and stores the instance in the container.
     *
     * @param string $class The class name or key to store the instance.
     * @param object $value The instance of the class to store.
     */
    public function __set( $class, $value ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.classFound
        $this->container[ $class ] = $value;
    }

    /**
     * Initializes Simple class.
     * Checks for an existing instance
     * And if it doesn't find one, create it.
     *
     * @return object | null
     */
    public static function init() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
