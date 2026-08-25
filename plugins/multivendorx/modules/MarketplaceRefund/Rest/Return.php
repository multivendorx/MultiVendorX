<?php
/**
 * Modules class file
 *
 * @package MultiVendorX
 */

namespace MultiVendorX\MarketplaceRefund;

use MultiVendorX\Utill;
use MultiVendorX\Store\Store;

defined( 'ABSPATH' ) || exit;

/**
 * MultiVendorX REST API Refund controller.
 *
 * @class       Module class
 * @version     5.0.0
 * @author      MultiVendorX
 */
class Return_Rest extends \WP_REST_Controller {


    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'returns';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ), 10 );
        add_filter( 'woocommerce_rest_shop_order_schema', array( $this, 'add_return_status' ) );
    }

    public function add_return_status( $schema ) {
        $schema['properties']['status']['enum'][] = 'refund-requested';
        return $schema;
    }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route(
            MultiVendorX()->rest_namespace,
            '/' . $this->rest_base . '/(?P<id>\d+)',
            array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_items', ),
                    'permission_callback' => array( $this, 'get_items_permissions_check', ),
                ),
                array(
                'methods'             => \WP_REST_Server::EDITABLE,
                'callback'            => array( $this, 'update_item' ),
                'permission_callback' => array( $this, 'update_item_permissions_check' ),
            ),
            )
        );
    }

    /**
     * Get all refunds filtered by store, search, and date.
     *
     * @param object $request Full details about the request.
     */
    public function get_items_permissions_check( $request ) {
        return current_user_can( 'read_shop_orders' ) || current_user_can( 'edit_shop_orders' );// phpcs:ignore WordPress.WP.Capabilities.Unknown
    }

    /**
     * Update an existing refund.
     *
     * @param object $request Full details about the request.
     */
    public function update_item_permissions_check( $request ) {
        return current_user_can( 'edit_shop_orders' );// phpcs:ignore WordPress.WP.Capabilities.Unknown
    }
    
    /**
    * Get customer return requests.
    *
    * @param WP_REST_Request $request Request object.
    *
    * @return WP_REST_Response|WP_Error
    */
    public function get_items( $request ) {

	    $nonce = $request->get_header( 'X-WP-Nonce' );

	    if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {

		$error = new \WP_Error( 'invalid_nonce', __( 'Invalid nonce', 'multivendorx' ),
                array(
				'status' => 403,
			    )
		    );

		if ( is_wp_error( $error ) ) {
			MultiVendorX()->util->log( $error );
		}
		return $error;
	    }
	    try {
		// Parameters.
            $limit = max( 1, (int) (
            $request->get_param( 'per_page' ) ?: $request->get_param( 'row' ) ?: 10 )
            );

            $page = max( 1, (int) $request->get_param( 'page' ) );
            $store_id      = $request->get_param( 'store_id' );
            $search_action = strtolower( $request->get_param( 'search_action' ) );
            $search_value  = strtolower( trim( $request->get_param( 'search_value' ) ?: $request->get_param( 'search' ) )
            );
            $order_by      = $request->get_param( 'order_by' );
            $order         = strtolower( $request->get_param( 'order' ) ) === 'asc' ? 'ASC' : 'DESC';
            $start_date = $request->get_param( 'start_date' );
            $end_date   = $request->get_param( 'end_date' );
        
            $offset = ( $page - 1 ) * $limit;
            $meta_query = array();

            if ( ! empty( $store_id ) ) {
                $meta_query[] = array(
                    'key'     => Utill::POST_META_SETTINGS['store_id'],
                    'value'   => $store_id,
                    'compare' => '=',
                );
            } else {
                $meta_query[] = array(
                    'key'     => Utill::POST_META_SETTINGS['store_id'],
                    'compare' => 'EXISTS',
                );
            }

            $status = 'return-requested';
            $date_filter = '';
            $normalized = Utill::normalize_date_range( $start_date, $end_date );
            
            if ( $normalized['start_date'] && $normalized['end_date'] ) {

                $date_filter = $normalized['start_date'] . '...' . $normalized['end_date'];
            }

            $count_args = array(
                'status'     => $status,
                'meta_query' => $meta_query,
                'return'     => 'ids',
            );

            if ( $date_filter ) {
                $count_args['date_created'] = $date_filter;
            }

            $total = count( wc_get_orders( $count_args ) );
            $args = array(
                'status'     => $status,
                'meta_query' => $meta_query,
                'limit'      => $limit,
                'offset'     => $offset,
                'return'     => 'objects',
                'paginate'   => false,
            );

            if ( $date_filter ) { 
                $args['date_created'] = $date_filter; 
            }

            if ( in_array( $order_by, array( 'date', 'order_id', ), true ) ) {

                $args['orderby'] = 'order_id' === $order_by ? 'ID' : 'date';
                $args['order'] = $order;
            }

            $orders = wc_get_orders( $args );

            if ( $search_action && $search_value ) {

                $orders = array_filter( $orders, function ( $order ) use (
                        $search_action, $search_value ) {

                        if ( ! $order ) {
                            return false;
                        }

                        switch ( $search_action ) {

                            case 'order_id': return ( string ) $order->get_id() === $search_value;

                            case 'customer':
                                $name = strtolower( $order->get_formatted_billing_full_name() );
                                $email = strtolower( $order->get_billing_email() );
                                return ( false !== strpos( $name, $search_value ) || false !== strpos( $email, $search_value ) );

                            default:
                                return true;
                        }
                    }
                );

                $orders = array_values( $orders );
            }

            $return_list = array_map(
                function ( $order ) {
                    $store_id = $order->get_meta(
                        Utill::POST_META_SETTINGS['store_id']
                    );
                    $store = new Store( $store_id );
                    $store_name = $store->exists() ? $store->get( 'name' ) : '';
                    $customer_id = $order->get_customer_id();
                    $customer_name = $order->get_formatted_billing_full_name();
                    $customer_email = $order->get_billing_email();
                    $return_products = $order->get_meta( '_customer_return_product', true );
                    $return_reason = $order->get_meta( '_customer_return_reason', true );
                    $return_additional_info = $order->get_meta( '_customer_return_additional_info', true );
                    $return_images = $order->get_meta( '_customer_return_product_imgs', true );

                    if ( ! is_array( $return_images ) ) {
                        $return_images = empty( $return_images ) ? array() : array( $return_images );
                    }

                    $products = array();

                    if ( is_array( $return_products ) ) {

                        foreach ( $return_products as $product_id ) {
                            $product = wc_get_product( $product_id );

                            if ( ! $product ) {
                                continue;
                            }

                            $products[] = array(
                                'id'    => $product->get_id(),
                                'name'  => $product->get_name(),
                                'image' => wp_get_attachment_image_url(
                                    $product->get_image_id(),
                                    'thumbnail'
                                ),
                            );
                        }
                    }

                    return array(
                        'id' => $order->get_id(),
                        'order_id' => $order->get_id(),
                        'store_id' => $store_id,
                        'store_name' => $store_name,
                        'total' => $order->get_total(),
                        'currency' => $order->get_currency(),
                        'commission_amount' => 0,
                        'meta_data' => array(
                        array(
                            'key'   => '_customer_return_reason',
                            'value' => $return_reason,
                        ),
                        array(
                            'key'   => '_customer_return_additional_info',
                            'value' => $return_additional_info,
                            ),
                        ),

                        'return_images' => $return_images,                         
                        'products' => $products,
                        'date_created' => $order->get_date_created() ? Utill::multivendorx_rest_prepare_date_response( 
                        $order->get_date_created()->date_i18n( 'Y-m-d H:i:s' ) ) : '',

                        'date_created_gmt' => $order->get_date_created() ? Utill::multivendorx_rest_prepare_date_response(
                        $order->get_date_created()->date_i18n( 'Y-m-d H:i:s' ), true ) : '',

                        'status' => $order->get_status(),
                        'customer_id' => $customer_id,
                        'customer_name' => $customer_name,
                        'customer_email' => $customer_email,
                        'customer_edit_link' => $customer_id ? admin_url( 'user-edit.php?user_id=' . $customer_id ): '',
                    );
                },
                $orders
            );

            if ( 'order_id' === $order_by ) {
                usort(
                    $return_list,
                    fn ( $a, $b ) => ( 'ASC' === $order ) ? $a['order_id'] <=> $b['order_id'] : $b['order_id'] <=> $a['order_id']
                );
            }
            $response = rest_ensure_response( $return_list );
            $response->header( 'X-WP-Total', $total );
            $response->header( 'X-WP-TotalPages', (int) ceil( $total / $limit ) );
            return $response;
	    } catch ( \Exception $e ) {

		MultiVendorX()->util->log( $e );
		return new \WP_Error(
			'server_error',
			__( 'Unexpected server error', 'multivendorx' ),
			    array( 'status' => 500, ) );
	        }
    }

    public function update_item( $request ) {

        $order_id = absint( $request['id'] );

        $decision = sanitize_key( $request->get_param( 'decision' ) );

        $note = sanitize_textarea_field( $request->get_param( 'note' ) );

        if ( ! in_array( $decision, array( 'approve', 'reject', ), true ) ) {
            return new \WP_Error( 'invalid_decision', __( 'Invalid return decision.','multivendorx' ),
                array(
                    'status' => 400,
                )
            );
        }

        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return new \WP_Error( 'order_not_found', __( 'Order not found.', 'multivendorx' ),
                array(
                    'status' => 404,
                )
            );
        }

        if ( 'return-requested' !== $order->get_status() ) {
            return new \WP_Error( 'invalid_status', __(
                'This return request has already been processed.',
                'multivendorx'
            ),
                array(
                    'status' => 400,
                )
            );
        }

        if ( 'approve' === $decision ) {
            $order->update_meta_data(
                '_customer_return_order',
                'return_accept'
            );

            $order->update_meta_data(
                '_customer_return_admin_note',
                $note
            );

            $order->set_status(
                'return-accepted'
            );

            $order->add_order_note(
                __(
                    'Site admin accepted the return request.',
                    'multivendorx'
                )
            );

        } else {

            $order->update_meta_data(
                '_customer_return_order',
                'return_reject'
            );

            $order->update_meta_data(
                '_customer_return_admin_note',
                $note
            );

            $order->set_status(
                'return-rejected'
            );

            $order->add_order_note(
                __(
                    'Site admin rejected the return request.',
                    'multivendorx'
                )
            );
        }
            $order->save();

            return rest_ensure_response(
            array(
                'success'  => true,
                'order_id' => $order_id,
                'decision' => $decision,
                'status'   => $order->get_status(),
            )
        );
    }

	/**
	 * Get parent order item id from store order item id
	 *
	 * @param int $item_id Store order item id.
	 * @return int
	 */
	public function get_store_parent_order_item_id( $item_id ) {
		global $wpdb;
		$store_item_id = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->order_itemmeta} WHERE meta_key=%s AND order_item_id=%d",
                'store_order_item_id',
                absint( $item_id )
            )
		);

		if ( ! empty( $wpdb->last_error ) && MultiVendorX()->show_advanced_log ) {
			MultiVendorX()->util->log( 'Database operation failed', 'ERROR' );
		}

		return $store_item_id;
	}
}