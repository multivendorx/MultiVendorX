<?php

namespace MultiVendorX\Vendor;

defined('ABSPATH') || exit;

/**
 * MVX Vendor Util class
 *
 * @version		2.2.0
 * @package		MultivendorX
 * @author 		MultiVendorX
 */

class VendorUtil {
    /**
     * Get the vendoer object of product
     * @param   mixed $product_id
     * @return  object
     */
    public static function get_products_vendor( $product_id ) {
        global $MVX;
        $vendor_data = false;
        if ( $product_id > 0 ) {
            $vendors_data = wp_get_post_terms( $product_id, $MVX->taxonomy->taxonomy_name );
            foreach ( $vendors_data as $vendor ) {
                $vendor_obj = self::get_vendor_by_term( $vendor->term_id );
                if ( $vendor_obj ) {
                    $vendor_data = $vendor_obj;
                }
            }
            if ( ! $vendor_data ) {
                $product_obj = get_post( $product_id );
                if ( is_object( $product_obj ) ) {
                    $author_id = $product_obj->post_author;
                    if ( $author_id ) {
                        $vendor_data = self::get_vendor( $author_id );
                    }
                }
            }
        }
        return $vendor_data;
    }

    /**
     * Get individual vendor info by term id
     * @param   int $term_id ID of term
     * @return  object | null
     */
    public static function get_vendor_by_term( $term_id ) {
        $vendor = null;
        if ( ! empty( $term_id ) ) {
            $user_id = get_term_meta( $term_id, '_vendor_user_id', true );
            if ( self::is_user_vendor( $user_id ) ) {
                $vendor = self::get_vendor( $user_id );
            }
        }
        return $vendor;
    }

    /**
     * Get the vendor object from vendor id.
     * If the id is not a vendor id it return null.
     * @param   mixed $vendor_id
     * @return  \MVX_Vendor|null
     */
    public static function get_vendor( $vendor_id = 0 ) {
        $vendor = null;
        $vendor_id = $vendor_id ? $vendor_id : self::get_current_vendor_id();
        if ( self::is_user_vendor( $vendor_id ) ) {
            $vendor = new \MVX_Vendor( absint( $vendor_id ) );
        }
        return $vendor;
    }

    /**
     * Check if a user is vendor or not.
     * @param   mixed $user
     * @return  mixed
     */
    public static function is_user_vendor( $user ) {
        if ( $user && !empty( $user ) ) {
            if ( ! is_object( $user ) ) {
                $user = new \WP_User( absint($user) );
            }
            return apply_filters( 'is_user_mvx_vendor', ( is_array($user->roles) && in_array('dc_vendor', $user->roles) ), $user );
        }
        return false;
    }

    /**
     * get current logged in vendor id
     * @return int
     */
    public static function get_current_vendor_id() {
        return apply_filters( 'mvx_current_loggedin_vendor_id', get_current_user_id() );
    }
}