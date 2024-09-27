<?php

namespace MultiVendorX\Utility;

use Automattic\WooCommerce\Utilities\OrderUtil as WCOrderUtil;

defined('ABSPATH') || exit;

/**
 * MVX Utility class
 *
 * @version		2.2.0
 * @package		MultivendorX
 * @author 		MultiVendorX
 */

class Utility {

    /**
     * LOG method of MultiVendorX.
     * @param string
     * @return void
     */
    public static function LOG( $string ) {
        global $MVX;

        $string = var_export( $string, true );
        $file   = $MVX->plugin_path . 'log/product_vendor.log';
        
        if (file_exists($file)) {
            // Open the file to get existing content
            $current = file_get_contents($file);
            if ($current) {
                // Append a new content to the file
                $current .= "$string" . "\r\n";
                $current .= "-------------------------------------\r\n";
            } else {
                $current = "$string" . "\r\n";
                $current .= "-------------------------------------\r\n";
            }
            // Write the contents back to the file
            file_put_contents($file, $current);
        }
    }

    /**
     * Utility function add aditional single quote in a string.
     * @param   string $string
     * @return  string
     */
    public static function add_single_quots( $string ) {
        if ( is_string( $string) ) {
            return "'$string'";
        }
        return $string;
    }
}