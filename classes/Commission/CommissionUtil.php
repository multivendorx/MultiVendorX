<?php

namespace MultiVendorX\Commission;

use MultiVendorX\Vendor\VendorUtil;
use MultiVendorX\Utility\Utility;

defined('ABSPATH') || exit;

/**
 * MVX Commission class
 *
 * @version		2.2.0
 * @package		MultivendorX
 * @author 		MultiVendorX
 */

class CommissionUtil {

    /**
     * Get a single commission row from databse by using commission id.
     * Return stdClass object represent a single row.
     * @param   mixed $id
     * @return  array | object | \stdClass
     */
    public static function get_commission_db( $id ) {
        global $wpdb;
        $commission = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mvx_commission WHERE ID = %d", $id )
        );
        return $commission ?? new \stdClass();
    }

    /**
     * Get the commission object of Commission class.
     * @param   int $id Commission ID
     * @return  Commission Commission Object
     */
    public static function get_commission( $id ) {
        return new Commission( $id );
    }

    /**
     * Get array of commission object based on filter.
     * @param   array $filter
     * @param   bool $object default true. If false function return array of id. 
     * @return  array array of Commission object or array of commission id. 
     */
    public static function get_commissions( $filter = [], $object = true ) {
        global $wpdb;

        // Remove the fields key from filter if object is set
        if ( $object && isset( $filter['fields'] ) ) {
            unset( $filter['fields'] );
        }

        // Handle fields seperatly if present in filter.
        // Preaper column for databse query.
        if ( isset( $filter['fields'] ) && is_array( $filter['fields'] ) ) {
            $fields = implode( ", ", $filter['fields']);
        } else {
            $fields = "*";
        }

        // Handle limit and offset seperatly
        $page       = $filter['page'] ?? 0;
        $perpage    = $filter['perpage'] ?? 0;

        unset( $filter['page'] );
        unset( $filter['perpage'] );

        // Preaper predicate
        $predicate = [];
        foreach( $filter as $column => $value ) {
            // BETWEEN or IN condition
            if ( is_array( $value ) ) {
                // Check for BETWEEN 
                if ( $value['compare'] === "BETWEEN" ) {
                    $start_value    = Utility::add_single_quots( $value['value'][0] );
                    $end_value      = Utility::add_single_quots( $value['value'][1] ); 
                    $predicate[]    = "{$column} BETWEEN {$start_value} AND {$end_value}";
                }
                // Check for IN or NOT IN
                if ( $value['compare'] === "IN" || $value['compare'] === "NOT IN" ) {
                    $compare        = $value['compare'];
                    $in_touple      = " (" . implode( ', ', array_map( [Utility::class, 'add_single_quots'] , $value['value'] ) ) . ") ";
                    $predicate[]    = "{$column} {$compare} {$in_touple}";
                }
            } else {
                // = condition
                $value          = Utility::add_single_quots( $value );
                $predicate[]    = "{$column} = {$value}";
            }
        }

        // Preaper query
        $query = "SELECT {$fields} FROM {$wpdb->prefix}mvx_commission";

        if ( !empty( $predicate ) ) {
            $query .= " WHERE " . implode( " AND ", $predicate );
        }

        // Pagination support
        if ( $page && $perpage && $perpage != -1 ) {
            $limit  = $perpage;
            $offset = ( $page - 1 ) * $perpage;
            $query .= " LIMIT {$limit} OFFSET {$offset}";
        }

        
        // Database query for commission
        $commissions = $wpdb->get_results( $query );

        // If return type is not object of Commission class return query result.
        if ( !$object ) {
            return $commissions;
        }

        // If return array of Commission object.
        return array_map( function( $commission ) {
            return new Commission( $commission );
        }, $commissions );
    }
}