<?php
/**
 * Notifima REST API Settings controller.
 *
 * @package Notifima
 */

namespace Notifima\RestAPI\Controllers;

use Notifima\Subscriber;
use Notifima\Utill;

defined( 'ABSPATH' ) || exit;

/**
 * Subscribers REST API controller.
 *
 * @class       RESTAPI class
 * @version     PRODUCT_VERSION
 * @author      MultiVendorX
 */
class Subscribers extends \WP_REST_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'subscribers';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route(
            Notifima()->rest_namespace,
            '/' . $this->rest_base,
            array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_items' ),
                    'permission_callback' => array( $this, 'get_items_permissions_check' ),
                ),
            )
        );

        register_rest_route(
            Notifima()->rest_namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            array(
                array(
                    'methods'             => \WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'update_item' ),
                    'permission_callback' => array( $this, 'update_item_permissions_check' ),
                    'args'                => array(
                        'id' => array( 'required' => true ),
                    ),
                ),
            )
        );
    }

    /**
     * Check if a given request has access to get items.
     *
     * @param \WP_REST_Request $request The REST request object.
     */
    public function get_items_permissions_check( $request ) {
        return Utill::current_user_has_capability( array( 'manage_options' ), 'get_subscribers' );
    }

    /**
     * Check if a given request has access to update items.
     *
     * @param \WP_REST_Request $request The REST request object.
     * @return bool|\WP_Error
     */
    public function update_item_permissions_check( $request ) {
        // Site admins/store managers can always manage any subscriber record.
        if ( true === Utill::current_user_has_capability( 'manage_options' ) ) {
            return true;
        }

        $user_id = Notifima()->current_user_id;

        // Logged-out visitor: only allowed when the store opts in to guest subscriptions.
        if ( 0 === $user_id ) {
            if ( 'everyone' === Notifima()->setting->get_setting( 'is_guest_subscriptions_enable', '' ) ) {
                return true;
            }

            return new \WP_Error(
                'notifima_forbidden_subscriber_action',
                __( 'You must be logged in to manage stock alert subscriptions.', 'notifima' ),
                array( 'status' => 401 )
            );
        }

        // Any logged-in user needs at least the base `read` capability.
        $has_read_capability = Utill::current_user_has_capability( 'read' );

        if ( is_wp_error( $has_read_capability ) ) {
            return $has_read_capability;
        }

        // `subscribe` opts an email into alerts - same trust level as the
        // guest flow above, not a mutation of someone else's existing data.
        if ( 'unsubscribe' !== $request->get_param( 'action' ) ) {
            return true;
        }

        // `unsubscribe` mutates an existing record - require the requester to
        // own the target email address.
        $customer_email = sanitize_email( (string) $request->get_param( 'customer_email' ) );

        // No email supplied - the handler falls back to the requester's own email.
        if ( empty( $customer_email ) ) {
            return true;
        }

        $current_user = Notifima()->current_user;

        if ( ! empty( $current_user->user_email ) && 0 === strcasecmp( $current_user->user_email, $customer_email ) ) {
            return true;
        }

        return new \WP_Error(
            'notifima_forbidden_subscriber_action',
            __( 'You are not allowed to modify this subscription.', 'notifima' ),
            array( 'status' => 403 )
        );
    }

    /**
     * Retrieve subscribers.
     *
     * @param \WP_REST_Request $request The request object.
     */
    public function get_items( $request ) {
        $nonce_check = Utill::validate_nonce( $request );

        if ( is_wp_error( $nonce_check ) ) {
            return $nonce_check;
        }

        try {
            $args = array(
                'query'       => array(
                    'post_type'      => array( 'product', 'product_variation' ),
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'fields'         => 'ids',
                ),
                'subscribers' => array(),
            );

            /**
             * Allow Pro to modify product and subscriber arguments.
             */
            $args = apply_filters(
                'notifima_subscribers_args',
                $args,
                $request
            );

            $product_ids = get_posts( $args['query'] );

            $subscriber_args = array_merge(
                array(
                    'product_ids' => $product_ids,
                ),
                $args['subscribers']
            );

            $subscriber_records = Utill::get_subscribers( $subscriber_args );

            $subscriber_items = array();

            // Built once - the same product is commonly shared by many subscriber rows,
            // so this avoids a repeated wc_get_product() lookup per row (see performance.md).
            $product_cache = array();

            $status_labels = array(
                'mailsent'     => __( 'Mail Sent', 'notifima' ),
                'subscribed'   => __( 'Subscribed', 'notifima' ),
                'unsubscribed' => __( 'Unsubscribed', 'notifima' ),
            );

            foreach ( $subscriber_records as $subscriber ) {
                if ( ! array_key_exists( $subscriber->product_id, $product_cache ) ) {
                    $product_cache[ $subscriber->product_id ] = wc_get_product( $subscriber->product_id );
                }
                $product = $product_cache[ $subscriber->product_id ];

                $image = get_the_post_thumbnail_url( $subscriber->product_id, 'full' );
                $user  = get_user_by( 'email', $subscriber->email );
                $date  = wp_date(
                    get_option( 'date_format' ),
                    strtotime( $subscriber->create_time )
                );

                $status_key        = $subscriber->status;
                $subscriber_status = $status_labels[ $status_key ] ?? '-';

                $subscriber_items[] = apply_filters(
                    'notifima_all_subscribers_list',
                    array(
                        'id'         => $subscriber->id,
                        'date'       => $date,
                        'email'      => $subscriber->email,
                        'status'     => $subscriber_status,
                        'status_key' => $status_key,
                        'reg_user'   => $user ? __( 'Yes', 'notifima' ) : __( 'No', 'notifima' ),
                        'user_link'  => $user ? get_edit_user_link( $user->ID ) : '',
                        'product'    => $product ? $product->get_name() : '',
                        'product_id' => $product ? $product->get_id() : '',
                        'image'      => $image ? $image : wc_placeholder_img_src(),
                    ),
                    $subscriber
                );
            }

            $response = rest_ensure_response( $subscriber_items );

            $total_subscribers = 0;

            foreach ( array( 'subscribed', 'unsubscribed', 'mailsent' ) as $status ) {
                $count = Utill::get_subscribers(
                    array(
                        'count'       => true,
                        'product_ids' => $product_ids,
                        'status'      => $status,
                    )
                );

                $total_subscribers += $count;
                $response->header( 'X-' . ucfirst( $status ), $count );
            }

            $response->header( 'X-Total', $total_subscribers );

            return $response;
        } catch ( \Exception $e ) {
            return new \WP_Error(
                'server_error',
                __( 'Unexpected server error', 'notifima' ),
                array( 'status' => 500 ),
            );
        }
    }

    /**
     * Update a subscriber.
     *
     * @param \WP_REST_Request $request The request object.
     */
    public function update_item( $request ) {
        $nonce_check = Utill::validate_nonce( $request );

        if ( is_wp_error( $nonce_check ) ) {
            return $nonce_check;
        }

        try {
            $action = $request->get_param( 'action' );

            if ( 'subscribe' === $action ) {
                return $this->subscribe_user( $request );
            } elseif ( 'unsubscribe' === $action ) {
                return $this->unsubscribe_user( $request );
            }

            return rest_ensure_response( false );
        } catch ( \Exception $e ) {
            return new \WP_Error(
                'server_error',
                __( 'Unexpected server error', 'notifima' ),
                array( 'status' => 500 )
            );
        }
    }

    /**
     * Subscribe a user through the REST API.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function subscribe_user( $request ) {
        $customer_email = $request->get_param( 'customer_email' );
        $product_id     = absint( $request->get_param( 'product_id' ) );
        $product_title  = sanitize_text_field( $request->get_param( 'product_title' ) );
        $variation_id   = absint( $request->get_param( 'variation_id' ) );

        $settings_array = Utill::get_form_settings_array();

        do_action( 'notifima_before_subscribe_product', $customer_email, $product_id, $variation_id );

        if ( ! is_email( $customer_email ) ) {
            return rest_ensure_response(
                array(
                    'status'  => false,
                    'message' => $settings_array['valid_email'],
                )
            );
        }

        if ( ! $product_id ) {
            return rest_ensure_response(
                array(
                    'status'  => false,
                    'message' => __( 'Invalid product.', 'notifima' ),
                )
            );
        }

        $product_id = $variation_id > 0 ? $variation_id : $product_id;

        if ( Subscriber::is_already_subscribed( $customer_email, $product_id ) ) {
            $message = str_replace(
                array( '%product_title%', '%customer_email%' ),
                array( $product_title, $customer_email ),
                $settings_array['alert_email_exist']
            );

            return rest_ensure_response(
                array(
                    'status'             => false,
                    'message'            => $message,
                    'already_subscribed' => true,
                    'customer_email'     => $customer_email,
                    'product_id'         => $product_id,
                    'variation_id'       => $variation_id,
                    'unsubscribe_button' => array(
                        'text' => $settings_array['unsubscribe_button_text'],
                    ),
                )
            );
        }

        $subscription_status = apply_filters(
            'notifima_eligible_to_subscribe',
            array(
                'status'  => true,
                'message' => '',
            ),
            $customer_email,
            $product_id
        );

        if ( ! $subscription_status['status'] ) {
            return rest_ensure_response( $subscription_status );
        }

        Subscriber::insert_subscriber( $customer_email, $product_id );
        Subscriber::insert_subscriber_email_trigger(
            wc_get_product( $product_id ),
            $customer_email
        );

        do_action( 'notifima_subscriber_added', $customer_email );

        $message = str_replace(
            array( '%product_title%', '%customer_email%' ),
            array( $product_title, $customer_email ),
            $settings_array['alert_success']
        );

        return rest_ensure_response(
            array(
                'status'  => true,
                'message' => $message,
            )
        );
    }

    /**
     * Unsubscribe a user through the REST API.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function unsubscribe_user( $request ) {
        $customer_email = sanitize_email( $request->get_param( 'customer_email' ) );
        $product_id     = absint( $request->get_param( 'product_id' ) );
        $variation_id   = absint( $request->get_param( 'variation_id' ) );

        $current_user = Notifima()->current_user;

        if ( ! empty( $current_user ) && empty( $customer_email ) ) {
            $customer_email = $current_user->user_email;
        }

        if ( empty( $customer_email ) ) {
            return rest_ensure_response(
                array(
                    'status'  => false,
                    'message' => __( 'Customer email is required.', 'notifima' ),
                )
            );
        }

        if ( ! $product_id ) {
            return rest_ensure_response(
                array(
                    'status'  => false,
                    'message' => __( 'Invalid product.', 'notifima' ),
                )
            );
        }

        $product = wc_get_product( $product_id );

        if ( $product && $product->is_type( 'variable' ) && $variation_id > 0 ) {
            $success = Subscriber::remove_subscriber( $variation_id, $customer_email );
        } else {
            $success = Subscriber::remove_subscriber( $product_id, $customer_email );
        }

        if ( ! $success ) {
            return rest_ensure_response(
                array(
                    'status'  => false,
                    'message' => __( 'Something went wrong. Please try again.', 'notifima' ),
                )
            );
        }

        $settings_array = Utill::get_form_settings_array();

        $success_msg = str_replace(
            '%customer_email%',
            $customer_email,
            $settings_array['alert_unsubscribe_message']
        );

        return rest_ensure_response(
            array(
                'status'  => true,
                'message' => $success_msg,
            )
        );
    }
}
