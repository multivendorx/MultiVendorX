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
class Refund extends \WP_REST_Controller {


    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'refunds';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ), 10 );
        add_filter( 'woocommerce_rest_shop_order_schema', array( $this, 'add_refund_status' ) );
    }

    public function add_refund_status( $schema ) {
        $schema['properties']['status']['enum'][] = 'refund-requested';
        return $schema;
    }

    /**
     * Register the routes for the objects of the controller.
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
     * Get all refunds filtered by store, search, and date.
     *
     * @param \WP_REST_Request $request REST request object containing filters like:
     *                                   'row', 'page', 'store_id', 'search_action',
     *                                   'search_value', 'order_by', 'order', 'start_date', 'end_date'.
     * @return \WP_REST_Response|\WP_Error
     * @throws \Exception If an unexpected error occurs while fetching refunds.
     */
    public function get_items( $request ) {
        $nonce = $request->get_header( 'X-WP-Nonce' );
        if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            $error = new \WP_Error(
                'invalid_nonce',
                __( 'Invalid nonce', 'multivendorx' ),
                array( 'status' => 403 )
            );

            if ( is_wp_error( $error ) ) {
                MultiVendorX()->util->log( $error );
            }

            return $error;
        }

        try {
            // Parameters.
            $limit         = max( 100, (int) $request->get_param( 'row' ) );
            $page          = max( 1, (int) $request->get_param( 'page' ) );
            $store_id      = $request->get_param( 'store_id' );
            $search_action = strtolower( $request->get_param( 'search_action' ) );
            $search_value  = strtolower( trim( $request->get_param( 'search_value' ) ) );
            $order_by      = $request->get_param( 'order_by' );
            $order         = strtolower( $request->get_param( 'order' ) ) === 'asc' ? 'ASC' : 'DESC';
            $start_date    = $request->get_param( 'start_date' );
            $end_date      = $request->get_param( 'end_date' );

            // Pagination offset (Woo requires this).
            $offset = ( $page - 1 ) * $limit;

            // Build meta query.
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

            // Date filter (BETWEEN only, site timezone aware).
            $date_filter = '';
            $normalized  = Utill::normalize_date_range( $start_date, $end_date );

            if ( $normalized['start_date'] && $normalized['end_date'] ) {
                $date_filter = $normalized['start_date'] . '...' . $normalized['end_date'];
            }

            // Count query (for correct totals).
            $count_args = array(
                'type'       => 'shop_order_refund',
                'meta_query' => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                'return'     => 'ids',
            );

            if ( $date_filter ) {
                $count_args['date_created'] = $date_filter;
            }

            $total = count( wc_get_orders( $count_args ) );

            // Build main query.
            $args = array(
                'type'       => 'shop_order_refund',
                'meta_query' => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                'limit'      => $limit,
                'offset'     => $offset,
                'return'     => 'objects',
                'paginate'   => false,
            );

            if ( $date_filter ) {
                $args['date_created'] = $date_filter;
            }

            if ( in_array( $order_by, array( 'date', 'order_id' ), true ) ) {
                $args['orderby'] = 'order_id' === $order_by ? 'ID' : 'date';
                $args['order']   = $order;
            }

            // Fetch refunds.
            $refunds = wc_get_orders( $args );

            // Search filtering (after fetch).
            if ( $search_action && $search_value ) {
                $refunds = array_filter(
                    $refunds,
                    function ( $refund ) use ( $search_action, $search_value ) {
                        $order = wc_get_order( $refund->get_parent_id() );
                        if ( ! $order ) {
                            return false;
                        }

                        switch ( $search_action ) {
                            case 'order_id':
                                return (string) $order->get_id() === $search_value;

                            case 'customer':
                                $name  = strtolower( $order->get_formatted_billing_full_name() );
                                $email = strtolower( $order->get_billing_email() );
                                return ( false !== strpos( $name, $search_value ) ) || ( false !== strpos( $email, $search_value ) );

                            default:
                                return true;
                        }
                    }
                );
                $refunds = array_values( $refunds );
            }

            // Build response data.
            $refund_list = array_map(
                function ( $refund ) {
                    $store_id   = $refund->get_meta( Utill::POST_META_SETTINGS['store_id'] );
                    $store      = new Store( $store_id );
                    $store_name = $store->exists() ? $store->get( 'name' ) : '';

                    $order = wc_get_order( $refund->get_parent_id() );

                    $customer_id    = $order ? $order->get_customer_id() : 0;
                    $customer_name  = $order ? $order->get_formatted_billing_full_name() : '';
                    $customer_email = $order ? $order->get_billing_email() : '';

                    return array(
                        'refund_id'          => $refund->get_id(),
                        'store_id'           => $store_id,
                        'store_name'         => $store_name,
                        'order_id'           => $refund->get_parent_id(),
                        'amount'             => $refund->get_amount(),
                        'reason'             => $refund->get_reason(),
                        'customer_reason'    => $order ? $order->get_meta( Utill::ORDER_META_SETTINGS['customer_refund_reason'], true ) : '',
                        'currency'           => $refund->get_currency(),
                        'date_created'       => $refund->get_date_created()
                            ? Utill::multivendorx_rest_prepare_date_response( $refund->get_date_created()->date_i18n( 'Y-m-d H:i:s' ) )
                            : '',
                        'date_created_gmt'   => $refund->get_date_created()
                            ? Utill::multivendorx_rest_prepare_date_response( $refund->get_date_created()->date_i18n( 'Y-m-d H:i:s' ), true )
                            : '',
                        'status'             => $refund->get_status(),
                        'customer_id'        => $customer_id,
                        'customer_name'      => $customer_name,
                        'customer_email'     => $customer_email,
                        'customer_edit_link' => $customer_id
                            ? admin_url( 'user-edit.php?user_id=' . $customer_id )
                            : '',
                    );
                },
                $refunds
            );

            // Manual sort for order_id if needed.
            if ( 'order_id' === $order_by ) {
                usort(
                    $refund_list,
                    fn ( $a, $b ) =>
                        ( 'ASC' === $order )
                            ? $a['order_id'] <=> $b['order_id']
                            : $b['order_id'] <=> $a['order_id']
                );
            }

            $response = rest_ensure_response( $refund_list );
            $response->header( 'X-WP-Total', $total );
            $response->header( 'X-WP-TotalPages', (int) ceil( $total / $limit ) );

            return $response;
        } catch ( \Exception $e ) {
            MultiVendorX()->util->log( $e );

            return new \WP_Error(
                'server_error',
                __( 'Unexpected server error', 'multivendorx' ),
                array( 'status' => 500 )
            );
        }
    }

    /**
     * Create a new refund.
     *
     * @param object $request Full data about the request.
     */
        public function update_item( $request ) {
        $refund_info = $request->get_param( 'payload' );

        $order_id               = $refund_info['orderId'] ? absint( $refund_info['orderId'] ) : 0;
        $refund_amount          = wc_format_decimal( $refund_info['refundAmount'], wc_get_price_decimals() );
        $items                  = $refund_info['items'] ?? array();
        $refund_reason          = sanitize_text_field( $refund_info['reason'] );
        $restock_refunded_items = 'true' === $refund_info['restock'];
        $refund                 = false;
        $response_data          = array();

        try {
            $order = wc_get_order( $order_id );

            $parent_order_id  = $order->get_parent_id();
            $parent_order     = wc_get_order( $parent_order_id );
            $parent_items_ids = array_keys( $parent_order->get_items( array( 'line_item', 'fee', 'shipping' ) ) );

            $max_refund = wc_format_decimal( $order->get_total() - $order->get_total_refunded(), wc_get_price_decimals() );

            if ( ! $refund_amount || $max_refund < $refund_amount || $refund_amount < 0 ) {
                return new \WP_Error( 'invalid_amount', __( 'Invalid refund amount.', 'multivendorx' ), array( 'status' => 400 ) );
            }

            // Prepare line items which we are refunding.
            $line_items        = array();
            $parent_line_items = array();

            $item_keys = array_keys( $items );

            foreach ( $item_keys as $item_id ) {
                $line_items[ $item_id ] = array(
                    'qty'          => 0,
                    'refund_total' => 0,
                    'refund_tax'   => array(),
                );
                $parent_item_id         = $this->get_store_parent_order_item_id( $item_id );
                if ( $parent_item_id && in_array( $parent_item_id, $parent_items_ids, true ) ) {
                    $parent_line_items[ $parent_item_id ] = array(
                        'qty'          => 0,
                        'refund_total' => 0,
                        'refund_tax'   => array(),
                    );
                }
            }

            foreach ( $items as $item_id => $value ) {
                $qty   = isset( $value['qty'] ) ? max( $value['qty'], 0 ) : 0;
                $total = isset( $value['total'] ) ? $value['total'] : 0;
                $tax   = isset( $value['tax'] ) ? $value['tax'] : 0;

                $line_items[ $item_id ]['qty']          = $qty;
                $line_items[ $item_id ]['refund_total'] = wc_format_decimal( $total );
                $line_items[ $item_id ]['refund_tax']   = wc_format_decimal( $tax );

                $parent_item_id = $this->get_store_parent_order_item_id( $item_id );

                if ( $parent_item_id && in_array( $parent_item_id, $parent_items_ids, true ) ) {
                    $parent_line_items[ $parent_item_id ]['qty']          = $qty;
                    $parent_line_items[ $parent_item_id ]['refund_total'] = wc_format_decimal( $total );
                    $parent_line_items[ $parent_item_id ]['refund_tax']   = wc_format_decimal( $tax );
                }
            }

            if ( $line_items ) {
                // Create the refund object.
                $refund = wc_create_refund(
                    array(
                        'amount'         => $refund_amount,
                        'reason'         => $refund_reason,
                        'order_id'       => $order_id,
                        'line_items'     => $line_items,
                        'refund_payment' => false,
                        'restock_items'  => $restock_refunded_items,
                    )
                );
            }

            if ( ! empty( $parent_line_items ) ) {
                if ( apply_filters( 'multivendorx_allow_refund_parent_order', true ) ) {
                    $parent_refund = wc_create_refund(
                        array(
                            'amount'         => $refund_amount,
                            'reason'         => $refund_reason,
                            'order_id'       => $parent_order_id,
                            'line_items'     => $parent_line_items,
                            'refund_payment' => false,
                            'restock_items'  => $restock_refunded_items,
                        )
                    );
                }
            }

            if ( is_wp_error( $refund ) ) {
                return new \WP_Error( 'refund_failed', $refund->get_error_message(), array( 'status' => 400 ) );
            }
            if ( is_wp_error( $parent_refund ) ) {
                return new \WP_Error( 'refund_failed', $parent_refund->get_error_message(), array( 'status' => 400 ) );
            }

            do_action( 'multivendorx_sub_order_refunded', $order_id, $refund->get_id() );

            if ( did_action( 'woocommerce_order_fully_refunded' ) ) {
                $response_data['status'] = 'fully_refunded';
            }

            return rest_ensure_response(
                array(
                    'success'       => true,
                    'response_data' => $response_data,
                )
            );
        } catch ( Exception $e ) {
            return new \WP_Error( 'refund_failed', __( 'Refund Failed', 'multivendorx' ), array( 'status' => 400 ) );
        }
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