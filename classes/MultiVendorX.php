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

    /**
     * Class construct
     * @param object $file
     */
    public function __construct($file) {
        
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