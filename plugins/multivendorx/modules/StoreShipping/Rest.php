<?php
/**
 * MultiVendorX REST API Zone Shipping Controller.
 *
 * @package MultiVendorX
 */

namespace MultiVendorX\StoreShipping;

use MultiVendorX\StoreShipping\Util;
use MultiVendorX\Utill;

defined( 'ABSPATH' ) || exit;

/**
 * MultiVendorX REST API Zone Shipping Controller.
 *
 * @class       Module class
 * @version     5.0.0
 * @author      MultiVendorX
 */
class Rest extends \WP_REST_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'zone-shipping';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ), 10 );
    }

    /**
     * Register the routes for shipping methods.
     */
    public function register_routes() {
        register_rest_route(
            MultiVendorX()->rest_namespace,
            '/' . $this->rest_base,
            array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
        );

        register_rest_route(
            MultiVendorX()->rest_namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'id' => array( 'required' => true ),
					),
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'id' => array( 'required' => true ),
					),
				),
			)
        );
    }

    /**
     * Get shipping methods permissions check
     *
     * @param object $request Request object.
     */
    public function get_items_permissions_check( $request ) {
        return Utill::current_user_has_capability( array( 'manage_options', 'edit_stores' ) );
    }

    /**
     * Check permission for shipping method REST API requests.
     *
     * @param object $request Request object.
     * @return true|\WP_Error
     */
    public function permissions_check( $request ) {
        return Utill::current_user_has_capability( array( 'edit_stores' ) );
    }

    /**
     * Get all shipping methods
     *
     * @param object $request Request object.
     */
    public function get_items( $request ) {
        $nonce = $request->get_header( 'X-WP-Nonce' );
        if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            $error = new \WP_Error( 'invalid_nonce', __( 'Invalid nonce', 'multivendorx' ), array( 'status' => 403 ) );

            // Log the error.
            if ( is_wp_error( $error ) ) {
                MultiVendorX()->util->log( $error );
            }

            return $error;
        }
        try {
            $store_id = $request->get_param( 'store_id' );
            $zones    = Util::get_zones( $store_id );
            return rest_ensure_response( $zones );
        } catch ( \Exception $e ) {
            MultiVendorX()->util->log( $e );

            return new \WP_Error( 'server_error', __( 'Unexpected server error', 'multivendorx' ), array( 'status' => 500 ) );
        }
    }

    /**
     * Create shipping method
     *
     * @param object $request Request object.
     */
    public function create_item( $request ) {
        $nonce = $request->get_header( 'X-WP-Nonce' );
        if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            $error = new \WP_Error( 'invalid_nonce', __( 'Invalid nonce', 'multivendorx' ), array( 'status' => 403 ) );

            // Log the error.
            if ( is_wp_error( $error ) ) {
                MultiVendorX()->util->log( $error );
            }

            return $error;
        }
        try {
            $store_id  = intval( $request->get_param( 'store_id' ) );
            $zone_id   = intval( $request->get_param( 'zone_id' ) );
            $method_id = sanitize_text_field( $request->get_param( 'method_id' ) );
            $settings  = $request->get_param( 'settings' );
            // Validate required fields.
            if ( empty( $method_id ) ) {
                return rest_ensure_response(
                    array(
                        'success' => false,
                        'message' => __( 'Shipping method is required', 'multivendorx' ),
                    )
                );
            }

            if ( empty( $zone_id ) ) {
                return rest_ensure_response(
                    array(
                        'success' => false,
                        'message' => __( 'Zone ID is required', 'multivendorx' ),
                    )
                );
            }

            if ( empty( $store_id ) ) {
                return rest_ensure_response(
                    array(
                        'success' => false,
                        'message' => __( 'Store ID is required', 'multivendorx' ),
                    )
                );
            }

            // Prepare store object.
            $store = new \MultiVendorX\Store\Store( $store_id );

            // Sanitize settings.
            $clean_settings = is_array( $settings )
                ? array_map( 'sanitize_text_field', $settings )
                : array();

            // Build dynamic meta key using method_id.
            $meta_key = sprintf( '%s_%d', $method_id, $zone_id );

            // Save only the settings as meta value.
            $store->update_meta( $meta_key, $clean_settings );
            $store->save();
            // Process settings in Shipping class.
            $shipping = new Zone_Shipping();
            $shipping->set_post_data( $settings );
            $shipping->process_admin_options();
            // Clear WooCommerce shipping cache.
            \WC_Cache_Helper::get_transient_version( 'shipping', true );

            // Return response.
			return rest_ensure_response(
                array(
					'success'  => true,
					'message'  => __( 'Shipping method settings saved successfully', 'multivendorx' ),
					'store_id' => $store_id,
					'zone_id'  => $zone_id,
					'meta_key' => $meta_key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                )
			);
        } catch ( \Exception $e ) {
            MultiVendorX()->util->log( $e );

            return new \WP_Error( 'server_error', __( 'Unexpected server error', 'multivendorx' ), array( 'status' => 500 ) );
        }
    }

    /**
     * Get single shipping method by id
     *
     * @param object $request Request object.
     */
    public function get_item( $request ) {
        $nonce = $request->get_header( 'X-WP-Nonce' );
        if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            $error = new \WP_Error( 'invalid_nonce', __( 'Invalid nonce', 'multivendorx' ), array( 'status' => 403 ) );

            // Log the error.
            if ( is_wp_error( $error ) ) {
                MultiVendorX()->util->log( $error );
            }

            return $error;
        }
        try {
            $store_id  = $request->get_param( 'store_id' );
            $method_id = $request->get_param( 'method_id' );
            $zone_id   = $request->get_param( 'zone_id' );

            // Validate required params.
            if ( empty( $store_id ) || empty( $method_id ) || empty( $zone_id ) ) {
                return rest_ensure_response(
                    array(
                        'success' => false,
                        'message' => __( 'store_id, method_id and zone_id are required', 'multivendorx' ),
                    )
                );
            }

            $method = Util::get_shipping_method( $store_id, $method_id, $zone_id );

            return rest_ensure_response( $method );
        } catch ( \Exception $e ) {
            MultiVendorX()->util->log( $e );

            return new \WP_Error( 'server_error', __( 'Unexpected server error', 'multivendorx' ), array( 'status' => 500 ) );
        }
    }

    /**
     * Update shipping method
     *
     * @param object $request Request object.
     */
    public function update_item( $request ) {
        $nonce = $request->get_header( 'X-WP-Nonce' );
        if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            $error = new \WP_Error( 'invalid_nonce', __( 'Invalid nonce', 'multivendorx' ), array( 'status' => 403 ) );

            // Log the error.
            if ( is_wp_error( $error ) ) {
                MultiVendorX()->util->log( $error );
            }

            return $error;
        }
        try {
            $store_id  = intval( $request->get_param( 'store_id' ) );
            $zone_id   = intval( $request->get_param( 'zone_id' ) );
            $method_id = sanitize_text_field( $request->get_param( 'method_id' ) );
            $settings  = $request->get_param( 'settings' );

            if ( ! $method_id || ! $zone_id || ! $store_id || ! $settings ) {
                return rest_ensure_response(
                    array(
                        'success' => false,
                        'message' => __( 'Missing required parameters', 'multivendorx' ),
                    )
                );
            }

            // -------------------------
            // Save or update shipping method in store meta instead of old table
            // -------------------------
            $updated = Util::update_shipping_method(
                array(
                    'store_id'  => $store_id,
                    'zone_id'   => $zone_id,
                    'method_id' => $method_id,
                    'settings'  => $settings,
                )
            );

            if ( ! $updated ) {
                return rest_ensure_response(
                    array(
                        'success' => false,
                        'message' => __( 'Failed to save shipping method', 'multivendorx' ),
                    )
                );
            }

            // Process settings in Shipping class.
            $shipping = new Zone_Shipping();
            $shipping->set_post_data( $settings );
            $shipping->process_admin_options();

            // Clear WooCommerce shipping cache.
            \WC_Cache_Helper::get_transient_version( 'shipping', true );

            return rest_ensure_response(
                array(
                    'success' => true,
                    'message' => __( 'Shipping method saved successfully', 'multivendorx' ),
                    'data'    => $updated,
                )
            );
        } catch ( \Exception $e ) {
            MultiVendorX()->util->log( $e );

            return new \WP_Error( 'server_error', __( 'Unexpected server error', 'multivendorx' ), array( 'status' => 500 ) );
        }
    }

    /**
     * Delete shipping method
     *
     * @param object $request Request data.
     */
    public function delete_item( $request ) {
        $nonce = $request->get_header( 'X-WP-Nonce' );
        if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            $error = new \WP_Error( 'invalid_nonce', __( 'Invalid nonce', 'multivendorx' ), array( 'status' => 403 ) );

            // Log the error.
            if ( is_wp_error( $error ) ) {
                MultiVendorX()->util->log( $error );
            }

            return $error;
        }
        try {
            $store_id  = intval( $request->get_param( 'store_id' ) );
            $zone_id   = intval( $request->get_param( 'zone_id' ) );
            $method_id = sanitize_text_field( $request->get_param( 'method_id' ) );

            if ( ! $store_id || ! $zone_id || ! $method_id ) {
                return rest_ensure_response(
                    array(
                        'success' => false,
                        'message' => __( 'Missing required parameters', 'multivendorx' ),
                    )
                );
            }

            $result = Util::delete_shipping_method( $store_id, $zone_id, $method_id );

            if ( is_wp_error( $result ) ) {
                return rest_ensure_response(
                    array(
                        'success' => false,
                        'message' => $result->get_error_message(),
                    )
                );
            }

            return rest_ensure_response(
                array(
                    'success' => true,
                    'message' => __( 'Shipping method deleted successfully', 'multivendorx' ),
                )
            );
        } catch ( \Exception $e ) {
            MultiVendorX()->util->log( $e );

            return new \WP_Error( 'server_error', __( 'Unexpected server error', 'multivendorx' ), array( 'status' => 500 ) );
        }
    }
}