<?php
/**
 * MultiVendorX Rest API
 *
 * @package MultiVendorX
 */

namespace MultiVendorX\RestAPI;

use MultiVendorX\RestAPI\Controllers\ImportDummyData;
use MultiVendorX\RestAPI\Controllers\Settings;
use MultiVendorX\RestAPI\Controllers\Dashboard;
use MultiVendorX\RestAPI\Controllers\Stores;
use MultiVendorX\RestAPI\Controllers\Commissions;
use MultiVendorX\RestAPI\Controllers\Status;
use MultiVendorX\RestAPI\Controllers\Notifications;
use MultiVendorX\RestAPI\Controllers\Transactions;
use MultiVendorX\RestAPI\Controllers\Tour;
use MultiVendorX\RestAPI\Controllers\Logs;
use MultiVendorX\RestAPI\Controllers\Tracking;

use MultiVendorX\Store\Store;
use MultiVendorX\Commission\CommissionUtil;
use MultiVendorX\RestAPI\Controllers\Migrations;
use MultiVendorX\Store\StoreUtil;
use MultiVendorX\Utill;

defined( 'ABSPATH' ) || exit;

/**
 * MultiVendorX Main Rest class
 *
 * @version     5.0.0
 * @package     MultiVendorX
 * @author      MultiVendorX
 */
class Rest {

    /**
     * Container for all our classes
     *
     * @var array
     */
    private $container = array();
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_classes();
        add_action( 'rest_api_init', array( $this, 'register_rest_api_routes' ), 10 );
        add_filter( 'woocommerce_rest_check_permissions', array( $this, 'grant_woocommerce_rest_permission' ), 10, 4 );
        add_filter( 'woocommerce_rest_shop_order_object_query', array( $this, 'query_shop_order_modify' ), 10, 2 );
        add_filter( 'woocommerce_rest_product_object_query', array( $this, 'query_product_modify' ), 10, 2 );
        add_filter( 'woocommerce_rest_shop_coupon_object_query', array( $this, 'query_shop_coupon_filter_meta' ), 10, 2 );
        add_filter( 'woocommerce_rest_prepare_product_object', array( $this, 'prepare_product_add_store_data' ), 10, 2 );
        add_filter( 'woocommerce_rest_prepare_shop_order_object', array( $this, 'prepare_shop_order_filter_meta' ), 10, 3 );
        add_filter( 'woocommerce_rest_prepare_shop_coupon_object', array( $this, 'prepare_shop_coupon_filter_meta' ), 10, 3 );
        add_filter( 'woocommerce_rest_pre_insert_shop_coupon_object', array( $this, 'pre_insert_shop_coupon_fix_status' ), 10, 3 );
        add_filter( 'woocommerce_analytics_products_query_args', array( $this, 'analytics_products_filter_low_stock_meta' ), 10, 1 );
        add_action( 'woocommerce_rest_insert_product_object', array( $this, 'generate_sku_data_in_product' ), 10, 3 );
        add_action( 'woocommerce_rest_insert_shop_coupon_object', array( $this, 'send_notifications' ), 10, 2 );
        add_filter( 'woocommerce_rest_product_shipping_class_query', array( $this, 'filter_shipping_classes_by_meta' ), 10, 2 );
    }

    /**
     * Add store data to WooCommerce product API response
     *
     * @param object $response API response.
     * @param object $product  Product object.
     */
    public function prepare_product_add_store_data( $response, $product ) {
        $product_id = $product->get_id();
        $store_id   = (int) get_post_meta(
            $product_id,
            Utill::POST_META_SETTINGS['store_id'],
            true
        );

        // Default response values.
        $response->data['store_id']   = '';
        $response->data['store_name'] = '';
        $response->data['store_slug'] = '';

        if ( $store_id > 0 ) {
            $store = new Store( $store_id );
            if ( ! $store->exists() ) {
                return;
            }
            $response->data['store_id']   = $store_id;
            $response->data['store_name'] = (string) $store->get( Utill::STORE_SETTINGS_KEYS['name'] );
            $response->data['store_slug'] = (string) $store->get( Utill::STORE_SETTINGS_KEYS['slug'] );
            apply_filters( 'multivendorx_rest_prepare_product_add_store_data', $response, $product, $store );
        }

        return $response;
    }

    /**
     * Filter low stock reports by meta key existence.
     *
     * @param array $args WP_Query arguments.
     */
    public function analytics_products_filter_low_stock_meta( $args ) {
        $meta_key = $args['meta_key'] ?? '';

        if ( Utill::POST_META_SETTINGS['store_id'] === $meta_key ) {
            $meta_query = array(
                'key'     => Utill::POST_META_SETTINGS['store_id'],
                'compare' => 'EXISTS',
            );
            /* phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query */
            $args['meta_query']   = $args['meta_query'] ?? array();
            $args['meta_query'][] = $meta_query;
            /* phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query */
        }

        return $args;
    }

    /**
     * Filter WooCommerce orders by meta key existence.
     *
     * @param array  $args    WP_Query arguments.
     * @param object $request REST API request object.
     */
    public function query_shop_order_modify( $args, $request ) {
        if ( ! empty( $request['meta_value'] ) ) {
            $args['meta_query'][] = array(
                'key'   => sanitize_text_field( $request['meta_key'] ),
                'value' => sanitize_text_field( $request['meta_value'] ),
            );
        }

        $meta_key      = $request->get_param( 'meta_key' ) ?? '';
        $meta_value    = $request->get_param( 'value' ) ?? '';
        $refund_status = $request->get_param( 'refund_status' ) ?? '';

        if ( empty( $meta_key ) && empty( $refund_status ) ) {
            return $args;
        }

        /* phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query */
        $args['meta_query'] = $args['meta_query'] ?? array();
        /* phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query */

        // Store meta filter.
        if ( ! empty( $meta_key ) ) {
            $condition = array(
                'key'     => sanitize_key( $meta_key ),
                'compare' => ! empty( $meta_value ) ? '=' : 'EXISTS',
            );

            if ( ! empty( $meta_value ) ) {
                $condition['value'] = sanitize_text_field( $meta_value );
            }

            $args['meta_query'][] = $condition;
        }

        // Refund status filter.
        if ( ! empty( $refund_status ) ) {
            $args['meta_query'][] = array(
                'key'     => '_customer_refund_order',
                'value'   => sanitize_text_field( $refund_status ),
                'compare' => '=',
            );
        }

        // Ensure relation when multiple meta queries exist.
        if ( count( $args['meta_query'] ) > 1 && empty( $args['meta_query']['relation'] ) ) {
            $args['meta_query']['relation'] = 'AND';
        }

        return $args;
    }

    /**
     * Filter WooCommerce products by meta key existence.
     *
     * @param array $args    WP_Query arguments.
     * @param array $request REST API request object.
     * @return array Modified WP_Query arguments.
     */
    public function query_product_modify( $args, $request ) {
        if ( ! empty( $request['meta_value'] ) ) {
            $args['meta_query'][] = array(
                'key'   => sanitize_text_field( $request['meta_key'] ),
                'value' => sanitize_text_field( $request['meta_value'] ),
            );
        }

        $meta_key = $request['meta_key'] ?? '';

        if ( Utill::POST_META_SETTINGS['store_id'] !== $meta_key ) {
            return $args;
        }

        $value = $request['value'] ?? '';

        $meta_condition = array(
            'key' => Utill::POST_META_SETTINGS['store_id'],
        );

        if ( ! empty( $value ) ) {
            $meta_condition['value']   = sanitize_text_field( $value );
            $meta_condition['compare'] = '=';
        } else {
            $meta_condition['compare'] = 'EXISTS';
        }
        /* phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query */
        $args['meta_query'] = $args['meta_query'] ?? array();
        /* phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query */
        $args['meta_query'][] = $meta_condition;

        // Ensure relation is set when multiple meta queries exist.
        if ( count( $args['meta_query'] ) > 1 && empty( $args['meta_query']['relation'] ) ) {
            $args['meta_query']['relation'] = 'AND';
        }

        $category = $request->get_param( 'cat' );
        $operator = strtoupper( $request->get_param( 'operator' ) ?? 'IN' );

        if ( ! empty( $category ) ) {
            $slugs = array_map( 'sanitize_title', explode( ',', $category ) );

            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $slugs,
                'operator' => in_array( $operator, array( 'IN', 'NOT IN' ), true )
                    ? $operator
                    : 'IN',
            );
        }

        $store_slug = $request->get_param( 'store_slug' );

        if ( ! empty( $store_slug ) ) {
            $store = StoreUtil::get_store_information(
                array(
					'slug' => sanitize_text_field( $store_slug ),
                )
            );

            // Safely get first store row.
            $store_row = is_array( $store ) ? reset( $store ) : null;

            if ( ! empty( $store_row ) && ! empty( $store_row['ID'] ) ) {
                $args['meta_query'][] = array(
                    'key'     => Utill::POST_META_SETTINGS['store_id'],
                    'value'   => (string) $store_row['ID'],
                    'compare' => '=',
                );
            }
        }

        return $args;
    }

    /**
     * Filter WooCommerce coupons by meta key existence.
     *
     * @param array $args    WP_Query arguments.
     * @param array $request REST API request object.
     */
    public function query_shop_coupon_filter_meta( $args, $request ) {
        $meta_query = array();
        $meta_key   = $request['meta_key'] ?? '';
        $value      = $request['value'] ?? '';

        // Filter by store ID.
        if ( Utill::POST_META_SETTINGS['store_id'] === $meta_key ) {
            $condition = array(
                'key' => Utill::POST_META_SETTINGS['store_id'],
            );

            if ( ! empty( $value ) ) {
                $condition['value']   = sanitize_text_field( $value );
                $condition['compare'] = '=';
            } else {
                $condition['compare'] = 'EXISTS';
            }

            $meta_query[] = $condition;
        }

        // Filter by discount type.
        $discount_type = $request['discount_type'] ?? '';

        if ( ! empty( $discount_type ) ) {
            $meta_query[] = array(
                'key'     => 'discount_type',
                'value'   => sanitize_text_field( $discount_type ),
                'compare' => '=',
            );
        }

        // Merge with existing meta_query.
        if ( ! empty( $meta_query ) ) {
            /* phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query */
            $args['meta_query'] = $args['meta_query'] ?? array();
            /* phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query */
            foreach ( $meta_query as $condition ) {
                $args['meta_query'][] = $condition;
            }

            if ( count( $args['meta_query'] ) > 1 && empty( $args['meta_query']['relation'] ) ) {
                $args['meta_query']['relation'] = 'AND';
            }
        }

        $store_slug = $request->get_param( 'store_slug' );

        if ( ! empty( $store_slug ) ) {
            $store = StoreUtil::get_store_information(
                array(
					'slug' => sanitize_text_field( $store_slug ),
                )
            );

            // Safely get first store row.
            $store_row = is_array( $store ) ? reset( $store ) : null;

            if ( ! empty( $store_row ) && ! empty( $store_row['ID'] ) ) {
                $args['meta_query'][] = array(
                    'key'     => Utill::POST_META_SETTINGS['store_id'],
                    'value'   => (string) $store_row['ID'],
                    'compare' => '=',
                );
            }
        }

        return $args;
    }

    /**
     * Give permission based on active store and user role.
     *
     * @param bool   $permission Current permission status.
     * @param string $context Request context.
     * @param int    $object_id Object ID.
     * @param string $post_type Post type.
     */
    public function grant_woocommerce_rest_permission( $permission, $context, $object_id, $post_type ) {
        // Decide on $context (the effective operation WooCommerce is about to perform),
        // not the raw transport verb — a client can dispatch a write through a request
        // whose $_SERVER['REQUEST_METHOD'] is GET via _method / X-HTTP-Method-Override,
        // and $context already reflects the real, post-override operation.
        $public_post_types = array(
            'product',
            'shop_coupon',
            'product_cat',
        );

        if ( 'read' === $context && in_array( $post_type, $public_post_types, true ) ) {
            return true;
        }

        $private_post_types = array(
            'user',
            'bookable_resource',
            'wc_appointment',
            'product_variation',
            'product_shipping_class',
            'attributes',
            'product_tag',
        );

        if ( is_user_logged_in() && 'read' === $context && in_array( $post_type, $private_post_types, true ) ) {
            return true;
        }

        if ( 'read' === $context && 'payment_gateways' === $post_type ) {
            return Utill::current_user_has_capability( array( 'edit_stores' ) );
        }

        $user_id = MultiVendorX()->current_user_id;

        // Fetch custom user meta.
        $active_store = MultiVendorX()->active_store;

        // Get all users for that store.
        $users = StoreUtil::get_store_users( $active_store );

        if ( is_array( $users ) && ! empty( $users['users'] ) && in_array( $user_id, $users['users'], true ) ) {
            return true;
        }

        return apply_filters( 'multivendorx_store_rest_permission', $permission, $user_id, $context, $object_id, $post_type );
    }

    /**
        $authenticated_only_post_types = array_merge( $public_post_types, array( 'user', 'payment_gateways' ) );
     *
     * @param WP_REST_Response $response REST API response object.
     * @param WC_Order         $order    Order object.
     * @param WP_REST_Request  $request  REST API request object.
     * @return WP_REST_Response
     */
    public function prepare_shop_order_filter_meta( $response, $order, $request ) {
        unset( $request );
        $store_id = (int) $order->get_meta( Utill::POST_META_SETTINGS['store_id'] );

        if ( $store_id > 0 ) {
            $store = new Store( $store_id );
            if ( $store->exists() ) {
                $phone_meta      = $store->get_meta( Utill::STORE_SETTINGS_KEYS['phone'] );
                $formatted_phone = '';
                if ( ! empty( $phone_meta['country_code'] ) && ! empty( $phone_meta['phone'] ) ) {
                    $formatted_phone = $phone_meta['country_code'] . '-' . $phone_meta['phone'];
                }

                $response->data['store_id']      = $store_id;
                $response->data['store_name']    = (string) $store->get( Utill::STORE_SETTINGS_KEYS['name'] );
                $response->data['store_slug']    = (string) $store->get( Utill::STORE_SETTINGS_KEYS['slug'] );
                $response->data['store_address'] = (string) $store->get_meta( Utill::STORE_SETTINGS_KEYS['address'] );
                $response->data['store_phone']   = $formatted_phone;
            }
        }
        $response->data['paid_status'] = $order->is_paid();

        $commission_id = (int) $order->get_meta( Utill::ORDER_META_SETTINGS['commission_id'] );

        if ( $commission_id > 0 ) {
            $commission = CommissionUtil::get_commission_db( $commission_id );

            if ( ! empty( $commission->ID ) ) {
                $response->data['commission_amount'] = (float) $commission->store_earning;
                $response->data['commission_total']  = (float) $commission->store_payable;
            }
        }

        $refunds      = $order->get_refunds();
        $refund_items = array();

        // Product refunds.
        foreach ( $order->get_items() as $item_id => $item ) {
            $refunded_tax = 0.0;

            foreach ( $refunds as $refund ) {
                foreach ( $refund->get_items() as $refund_item ) {
                    if ( (int) $refund_item->get_meta( '_refunded_item_id' ) === $item_id ) {
                        $refunded_tax += array_sum( $refund_item->get_taxes()['total'] ?? array() );
                    }
                }
            }

            $refund_items[] = array(
                'item_id'             => $item_id,
                'type'                => 'product',
                'name'                => $item->get_name(),
                'refunded_line_total' => (float) -1 * $order->get_total_refunded_for_item( $item_id ),
                'refunded_tax'        => (float) $refunded_tax,
            );
        }

        // Shipping refunds.
        foreach ( $order->get_items( 'shipping' ) as $item_id => $item ) {
            $refunded_tax = 0.0;

            foreach ( $refunds as $refund ) {
                foreach ( $refund->get_items( 'shipping' ) as $refund_item ) {
                    if ( (int) $refund_item->get_meta( '_refunded_item_id' ) === $item_id ) {
                        $refunded_tax += array_sum( $refund_item->get_taxes()['total'] ?? array() );
                    }
                }
            }

            $refund_items[] = array(
                'item_id'               => $item_id,
                'type'                  => 'shipping',
                'name'                  => $item->get_name(),
                'refunded_shipping'     => (float) -1 * $order->get_total_refunded_for_item( $item_id, 'shipping' ),
                'refunded_shipping_tax' => (float) $refunded_tax,
            );
        }

        $response->data['refund_items'] = $refund_items;

        // Order notes.
        $response->data['order_notes'] = wc_get_order_notes(
            array( 'order_id' => $order->get_id() )
        );

        if ( ! empty( $response->data['refunds'] ) ) {
            foreach ( $response->data['refunds'] as &$refund_data ) {
                $refund = wc_get_order( $refund_data['id'] );
                if ( ! $refund ) {
                    continue;
                }
                $username = get_the_author_meta( 'user_login', $refund->get_refunded_by() );

				$refund_data['label'] = sprintf(
                    /* translators: 1: refund id, 2: date, 3: user */
                    __( 'Refund #%1$s - %2$s by %3$s', 'multivendorx' ),
                    $refund->get_id(),
                    wc_format_datetime( $refund->get_date_created() ),
                    $username ?? __( 'system', 'multivendorx' )
				);
            }
        }

        return $response;
    }

    /**
     * Filter WooCommerce coupons by meta key existence.
     *
     * @param WP_REST_Response $response REST API response.
     * @param WC_Coupon        $coupon   Coupon object.
     * @param WP_REST_Request  $request  Request object.
     * @return WP_REST_Response
     */
    public function prepare_shop_coupon_filter_meta( $response, $coupon, $request ) {
        unset( $request );

        $store_id = $coupon->get_meta( Utill::POST_META_SETTINGS['store_id'] );

        if ( $store_id ) {
            // Get store information.
            $store = new Store( $store_id );
            if ( ! $store->exists() ) {
                return $response;
            }
            $store_name = $store->get( Utill::STORE_SETTINGS_KEYS['name'] );
            $store_slug = $store->get( Utill::STORE_SETTINGS_KEYS['slug'] );

            // Add store data to API response.
            $response->data['store_name'] = $store_name ? $store_name : '';
            $response->data['store_slug'] = $store_slug ? $store_slug : '';
            $response->data['store_id']   = $store_id;
        }

        return $response;
    }

    /**
     * Fix coupon status when creating or updating via REST API.
     *
     * @param object $coupon   Coupon object.
     * @param array  $request  Request object.
     * @param bool   $creating  True when creating, false when updating.
     */
    public function pre_insert_shop_coupon_fix_status( $coupon, $request, $creating ) {
        unset( $creating );
        $status = sanitize_text_field( $request['status'] ) ?? '';

	    if ( ! empty( $status ) ) {
            $allowed = array( 'publish', 'draft', 'pending', 'private' );

            if ( in_array( $status, $allowed, true ) ) {
                wp_update_post(
                    array(
						'ID'          => $coupon->get_id(),
						'post_status' => $status,
                    )
                );
            }
        }

        return $coupon;
    }

    /**
     * Generate a product SKU based on its title
     *
     * @param object $product Product data.
     */
    protected function generate_product_sku( $product ) {
        if ( $product ) {
            switch ( MultiVendorX()->setting->get_setting( 'sku_generator', '' ) ) {
                case 'slugs':
                    $product_sku = $product->get_slug();
                    break;

                case 'ids':
                    $product_sku = $product->get_id();
                    break;

                default:
                    $product_sku = $product->get_sku();
            }
        }
        return $product_sku;
    }

    /**
     * Generate a variation SKU based on its attributes
     *
     * @param array $variation Variation data.
     */
    protected function generate_variation_sku( $variation = array() ) {
        if ( $variation ) {
            $variation_sku = '';
            if ( 'slugs' === MultiVendorX()->setting->get_setting( 'sku_generator', '' ) ) {
                switch ( MultiVendorX()->setting->get_setting( 'sku_generator_attribute_spaces', '' ) ) {
                    case 'underscore':
                        $variation['attributes'] = str_replace( ' ', '_', $variation['attributes'] );
                        break;

                    case 'dash':
                        $variation['attributes'] = str_replace( ' ', '-', $variation['attributes'] );
                        break;

                    case 'none':
                        $variation['attributes'] = str_replace( ' ', '', $variation['attributes'] );
                        break;
                }
                $separator     = apply_filters( 'multivendorx_sku_generator_attribute_separator', $this->get_sku_separator() );
                $variation_sku = implode( $separator, $variation['attributes'] );
                $variation_sku = str_replace( 'attribute_', '', $variation_sku );
            }
            if ( 'ids' === MultiVendorX()->setting->get_setting( 'sku_generator', '' ) ) {
                $variation_sku = $variation['variation_id'] ? $variation['variation_id'] : '';
            }
        }
        return $variation_sku;
    }

    /**
     * Get the separator used between parent / variation SKUs
     *
     * @return string
     */
    private function get_sku_separator() {
        return apply_filters( 'multivendorx_sku_separator', '-' );
    }

    /**
     * Save Variation SKU.
     *
     * @param int         $variation_id    Variation ID.
     * @param WC_Product  $parent_product  Parent product.
     * @param string|null $parent_sku      Optional parent SKU to use instead of the product's SKU.
     */
    protected function multivendorx_save_variation_sku( $variation_id, $parent_product, $parent_sku = null ) {
        $variation = wc_get_product( $variation_id );

        if ( ! $variation || ! $variation->is_type( 'variation' ) ) {
            return;
        }

        $parent_sku = $parent_sku ?? $parent_product->get_sku();

        if ( ! empty( $parent_sku ) ) {
            $variation_data = $parent_product->get_available_variation( $variation );
            if ( ! empty( $variation_data ) ) {
                $variation_sku = $this->generate_variation_sku( $variation_data );
                $sku           = $parent_sku . $this->get_sku_separator() . $variation_sku;

                try {
                    $sku = wc_product_generate_unique_sku( $variation_id, $sku );
                    $variation->set_sku( $sku );
                    $variation->save();
                } catch ( WC_Data_Exception $exception ) {
                    wc_add_notice( __( 'Variation SKU is not generated!', 'multivendorx' ), 'error' );
                }
            }
        }
    }

    /**
     * Save generated SKU
     *
     * @param object $product WC_Product object.
     */
    public function multivendorx_save_generated_sku( $product ) {
        if ( is_numeric( $product ) ) {
            $product = wc_get_product( absint( $product ) );
        }

        if ( ! $product ) {
            return;
        }

        if ( 'never' === MultiVendorX()->setting->get_setting( 'sku_generator', '' ) ) {
            return;
        }

        $product_sku = $this->generate_product_sku( $product );
        if ( $product->is_type( 'variable' ) ) {
            foreach ( $product->get_children() as $variation_id ) {
                $this->multivendorx_save_variation_sku(
                    $variation_id,
                    $product,
                    $product_sku
                );
            }
        }

        try {
            $product_sku = wc_product_generate_unique_sku( $product->get_id(), $product_sku );
            $product->set_sku( $product_sku );
            $product->save();
        } catch ( \WC_Data_Exception $exception ) {
            wc_add_notice( __( 'SKU is not generated!', 'multivendorx' ), 'error' );
        }
    }
    /**
     * Generate SKU data for a product if not being created.
     *
     * Calls the internal SKU save method when updating a product.
     *
     * @param \WC_Product      $product  The WooCommerce product object.
     * @param \WP_REST_Request $request  The REST request object.
     * @param bool             $creating True if the product is being created; false if updating.
     */
    public function generate_sku_data_in_product( $product, $request, $creating ) {
        if ( ! defined( 'REST_REQUEST' ) ) {
            return;
        }

        $referer = filter_input( INPUT_SERVER, 'HTTP_REFERER', FILTER_SANITIZE_URL ) ?? '';
        $path    = wp_parse_url( $referer, PHP_URL_PATH );
		if ( false === strpos( $path, 'products' ) ) {
			return;
		}

		$old_status = $product->get_status();
		$new_status = $request->get_param( 'status' );

		$store = new Store(
            get_post_meta( $product->get_id(), Utill::POST_META_SETTINGS['store_id'], true )
		);
        if ( ! $store->exists() ) {
            return;
        }

		if ( isset( $creating ) && true === $creating && 'pending' === $new_status ) {
            MultiVendorX()->notifications->send_notification_helper(
                'product_submitted',
                $store,
                null,
                array(
					'product_name' => $product->get_name(),
					'category'     => 'activity',
				)
            );
		}

		if ( 'pending' === $old_status && 'publish' === $new_status ) {
            MultiVendorX()->notifications->send_notification_helper(
                'product_approved',
                $store,
                null,
                array(
					'product_name' => $product->get_name(),
					'category'     => 'activity',
				)
            );
		}

		if ( 'publish' === $old_status && 'draft' === $new_status ) {
            MultiVendorX()->notifications->send_notification_helper(
                'product_rejected',
                $store,
                null,
                array(
					'product_name' => $product->get_name(),
					'category'     => 'activity',
				)
            );
		}

		if ( 'publish' === $new_status && ( 'pending' === $old_status || 'draft' === $old_status ) ) {
			$followers = $store->meta_data[ Utill::STORE_SETTINGS_KEYS['followers'] ] ?? array();

			foreach ( $followers as $follower ) {
				$user = get_user_by( 'id', $follower['id'] );
				if ( ! $user ) {
					continue;
				}

				do_action(
					'multivendorx_notify_store_new_product_to_followers',
					'store_new_product_to_followers',
					array(
						'customer_email' => $user->user_email,
						'customer_phone' => get_user_meta( $follower['id'], 'billing_phone', true ),
						'store_name'     => $store->get( Utill::STORE_SETTINGS_KEYS['name'] ),
						'customer_name'  => $user->display_name,
						'product_name'   => $product->get_name(),
						'category'       => 'notification',
					)
				);
			}
		}

        if ( ! $creating ) {
            $this->multivendorx_save_generated_sku( $product );
        }
    }

	/**
	 * Send notifications to store followers when a coupon is published.
	 *
	 * This function triggers during REST requests when a coupon changes status
	 * from 'pending' or 'draft' to 'publish'. Followers of the store associated
	 * with the coupon are notified via the 'multivendorx_notify_store_new_coupon_to_followers' action.
	 *
	 * @param WC_Coupon       $coupon  Coupon object.
	 * @param WP_REST_Request $request REST request instance.
	 * @return void
	 */
	public function send_notifications( $coupon, $request ) {
		if ( ! defined( 'REST_REQUEST' ) ) {
			return;
		}

        $referer = filter_input( INPUT_SERVER, 'HTTP_REFERER', FILTER_SANITIZE_URL ) ?? '';
		$path    = wp_parse_url( $referer, PHP_URL_PATH ) ?? '';

		if ( false === strpos( $path, 'coupons' ) ) {
			return;
		}

		$old_status = get_post_status( $coupon->get_id() );
		$new_status = $request->get_param( 'status' );

		$store_id = get_post_meta( $coupon->get_id(), Utill::POST_META_SETTINGS['store_id'], true );
		$store    = new Store( $store_id );
        if ( ! $store->exists() ) {
            return;
        }

		if ( 'publish' === $new_status && ( 'pending' === $old_status || 'draft' === $old_status ) ) {
			$followers = $store->meta_data[ Utill::STORE_SETTINGS_KEYS['followers'] ] ?? array();

			foreach ( $followers as $follower ) {
				$user = get_user_by( 'id', $follower['id'] );

				if ( ! $user ) {
					continue;
				}

				do_action(
                    'multivendorx_notify_store_new_coupon_to_followers',
                    'store_new_coupon_to_followers',
                    array(
						'customer_email' => $user->user_email,
						'customer_phone' => get_user_meta( $follower['id'], 'billing_phone', true ),
						'store_name'     => $store->get( Utill::STORE_SETTINGS_KEYS['name'] ),
						'customer_name'  => $user->display_name,
						'coupon_code'    => $coupon->get_code(),
						'category'       => 'notification',
                    )
				);
			}
		}
	}

    /**
     * Initialize all REST API controller classes.
     */
    public function init_classes() {
        $this->container = array(
            'settings'          => new Settings(),
            'dashboard'         => new Dashboard(),
            'store'             => new Stores(),
            'commission'        => new Commissions(),
            'status'            => new Status(),
            'transaction'       => new Transactions(),
            'notifications'     => new Notifications(),
            'tour'              => new Tour(),
            'logs'              => new Logs(),
            'import_dummy_data' => new ImportDummyData(),
            'Migration'         => new Migrations(),
            'Tracking'          => new Tracking(),
        );
    }

    /**
     * Register REST API routes.
     */
    public function register_rest_api_routes() {
        register_rest_route(
            MultiVendorX()->rest_namespace,
            '/store-products',
            array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'fetch_store_products' ),
                    'permission_callback' => '__return_true',
                ),
            )
        );

        register_meta(
            'comment',
            'store_rating',
            array(
				'type'         => 'number',
				'single'       => true,
				'show_in_rest' => true, // Important to show in REST API.
				'description'  => 'Customer rating for the store',
			)
        );

        // Register store_rating_id meta.
        register_meta(
            'comment',
            'store_rating_id',
            array(
				'type'         => 'number',
				'single'       => true,
				'show_in_rest' => true,
				'description'  => 'Store ID associated with the rating',
			)
        );

        register_meta(
            'user',
            'multivendorx_dashboard_tasks',
            array(
				'type'         => 'array',
				'single'       => true,
				'show_in_rest' => array(
					'schema'        => array(
						'type'  => 'array',
						'items' => array( 'type' => 'string' ),
					),
					'auth_callback' => function () {
                        return Utill::current_user_has_capability( array( 'edit_users' ) );
					},
				),
			)
        );

        foreach ( $this->container as $controller ) {
            if ( method_exists( $controller, 'register_routes' ) ) {
                $controller->register_routes();
            }
        }
    }
    /**
     * Fetch and render products for a store with optional search.
     *
     * @param \WP_REST_Request $request REST request containing 'storeId' and optional 'search'.
     * @return string HTML of the WooCommerce product loop.
     */
    public function fetch_store_products( $request ) {

        ob_start();

        $store_id = $request->get_param( 'storeId' );
        $search   = sanitize_text_field( $request->get_param( 'search' ) );
        /* phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query */
        $args = array(
            'post_type'   => 'product',
            'post_status' => 'publish',
            's'           => $search,
            'meta_query'  => array(
                array(
                    'key'     => Utill::POST_META_SETTINGS['store_id'],
                    'value'   => $store_id,
                    'compare' => '=',
                ),
            ),
        );
        /* phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query */
        $custom_query = new \WP_Query( $args );

        if ( $custom_query->have_posts() ) {
            echo '<div class="woocommerce">';
            woocommerce_product_loop_start();

            while ( $custom_query->have_posts() ) {
                $custom_query->the_post();

                do_action( 'woocommerce_shop_loop' );
                wc_get_template_part( 'content', 'product' );
            }

            woocommerce_product_loop_end();
            echo '</div>';
        } else {
            do_action( 'woocommerce_no_products_found' );
        }

        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * Get REST API controller class.
     *
     * @param  string $class Class name to retrieve.
     */
    public function __get( $class ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.classFound
        if ( array_key_exists( $class, $this->container ) ) {
            return $this->container[ $class ];
        }

        return new \WP_Error( sprintf( 'Call to unknown class %s.', $class ) );
    }

    public function filter_shipping_classes_by_meta( $args, $request ) {
        $meta_key   = sanitize_text_field( $request->get_param( 'meta_key' ) );
        $meta_value = sanitize_text_field( $request->get_param( 'meta_value' ) );

        $not_exists_query = array(
            'key'     => $meta_key,
            'compare' => 'NOT EXISTS',
        );

        $store_query = array(
            'key'     => $meta_key,
            'value'   => $meta_value,
            'compare' => '=',
        );

        // Shipping module disabled: show marketplace shipping classes only.
        if ( ! MultiVendorX()->modules->is_active( 'store-shipping' ) ) {
            $args['meta_query'] = array( $not_exists_query );
            return $args;
        }

        $visibility = MultiVendorX()->setting->get_setting( 'shipping_class_access', 'store' );

        if ( 'both' === $visibility ) {
            $args['meta_query'] = array(
                'relation' => 'OR',
                $not_exists_query,
                $store_query,
            );
        } elseif ( 'store' === $visibility ) {
            $args['meta_query'] = array( $store_query );
        }

        return $args;
    }
}
