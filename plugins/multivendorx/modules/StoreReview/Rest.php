<?php
/**
 * MultiVendorX REST API Store Review Controller.
 *
 * @package MultiVendorX
 */

namespace MultiVendorX\StoreReview;

use MultiVendorX\Store\Store;
use MultiVendorX\Store\StoreUtil;
use MultiVendorX\StoreReview\Util;
use MultiVendorX\Utill;

defined( 'ABSPATH' ) || exit;

/**
 * MultiVendorX REST API Store Review Controller.
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
    protected $rest_base = 'reviews';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ), 10 );
    }

    /**
     * Register the routes for store reviews.
     */
    public function register_routes() {
        register_rest_route(
            MultiVendorX()->rest_namespace,
            '/' . $this->rest_base,
            array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_items' ),
                    'permission_callback' => '__return_true',
                ),
                array(
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => array( $this, 'create_item' ),
                    'permission_callback' => array( $this, 'create_item_permissions_check' ),
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
                    'permission_callback' => array( $this, 'update_item_permissions_check' ),
                    'args'                => array(
                        'id' => array( 'required' => true ),
                    ),
                ),
                array(
                    'methods'             => \WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'update_item' ),
                    'permission_callback' => array( $this, 'update_item_permissions_check' ),
                ),
                array(
                    'methods'             => \WP_REST_Server::DELETABLE,
                    'callback'            => array( $this, 'delete_item' ),
                    'permission_callback' => array( $this, 'delete_item_permissions_check' ),
                    'args'                => array(
                        'id' => array( 'required' => true ),
                    ),
                ),
            )
        );
    }

    /**
     * Check permission for REST API requests.
     *
     * @param object $request Request data.
     * @return true|\WP_Error
     */
    public function create_item_permissions_check( $request ) {
        return Utill::current_user_has_capability( array( 'customer', 'edit_stores', 'manage_options' ) );
    }

    /**
     * PUT permission.
     *
     * @param object $request Request data.
     * @return bool
     */
    public function update_item_permissions_check( $request ) {
        return Utill::current_user_has_capability( array( 'edit_stores', 'manage_options' ) );
    }

    /**
     * Delete permission.
     *
     * @param object $request Request data.
     * @return bool
     */
    public function delete_item_permissions_check( $request ) {
        return Utill::current_user_has_capability( array( 'manage_options' ) );
    }

    /**
     * Get review items with optional pagination, date filters, and counters
     *
     * @param object $request Request data.
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
            if ( $request->get_param( 'overview' ) ) {
                return rest_ensure_response( $this->calculate_store_rating_summary( intval( $store_id ) ) );
            }
			$limit          = intval( $request->get_param( 'row' ) ) ? intval( $request->get_param( 'row' ) ) : 0;
			$page           = intval( $request->get_param( 'page' ) ) ? intval( $request->get_param( 'page' ) ) : 1;
            $offset         = ( $page - 1 ) * $limit;
            $status         = sanitize_text_field( $request->get_param( 'status' ) );
            $order_by       = sanitize_text_field( $request->get_param( 'order_by' ) );
            $order          = sanitize_text_field( $request->get_param( 'order' ) );
            $overall_rating = $request->get_param( 'overall_rating' );
            $sec_fetch_site = $request->get_header( 'sec_fetch_site' );
            $referer        = $request->get_header( 'referer' );

            $can_manage = $store_id
                ? StoreUtil::current_user_can_manage_store( intval( $store_id ) )
                : Utill::current_user_has_capability( array( 'manage_options' ) );

            if ( ! $can_manage ) {
                $status = 'approved';
            }

            $range = Utill::normalize_date_range(
                $request->get_param( 'start_date' ),
                $request->get_param( 'end_date' )
            );
            $args  = array();

            // --- Step 3: Apply Store Filter ---.
            if ( $store_id ) {
                $args['store_id'] = intval( $store_id );
            }

            // --- Step 5: Add Filters (status, date, pagination) ---.
            if ( $status ) {
                $args['status'] = $status;
            }

            $args['limit']  = $limit;
            $args['offset'] = $offset;

            if ( ! empty( $range['start_date'] ) ) {
                $args['start_date'] = $range['start_date'];
            }

            if ( ! empty( $range['end_date'] ) ) {
                $args['end_date'] = $range['end_date'];
            }

            // --- Step 5.2: Filter by Overall Rating (like "4 stars & up") ---.
            if ( null !== $overall_rating && '' !== $overall_rating ) {
                $rating = floatval( $overall_rating );

                // Prevent invalid or below 1 ratings.
                if ( $rating < 1 ) {
                    $rating = 1;
                }

                // Pass to query args.
                $args['overall_rating'] = $rating;
            }

            // --- Step 5.1: Add Sorting ---.
            if ( ! empty( $order_by ) && ! empty( $order ) ) {
                $args['order_by']  = $order_by;
                $args['order_dir'] = ! empty( $order ) ? strtoupper( $order ) : 'DESC';
            } else {
                // Fallback default sort.
                $args['order_by']  = 'date_created';
                $args['order_dir'] = 'DESC';
            }

			if ( 'same-origin' === $sec_fetch_site && preg_match( '#/dashboard/?$#', $referer ) && get_transient( Utill::MULTIVENDORX_TRANSIENT_KEYS['review_transient'] . $store_id ) ) {
                    return get_transient( Utill::MULTIVENDORX_TRANSIENT_KEYS['review_transient'] . $store_id );
            }
            // --- Step 6: Fetch Review Data ---.
            $reviews = Util::get_review_information( $args );

            // --- Step 7: Format Data for Response ---.
            $formatted = array_map( array( $this, 'prepare_rest_item_for_response' ), $reviews ? $reviews : array() );

            // --- Step 8: Get Status Counters ---.
            $base_args = $args;
            unset( $base_args['limit'], $base_args['offset'], $base_args['status'] );

            if ( Utill::current_user_has_capability( array( 'manage_options' ) ) ) {
                unset( $base_args['store_id'] );
            }

            $base_args['count'] = true;

            $all_args  = $base_args;
            $all_count = Util::get_review_information( $all_args );

            $pending_args           = $base_args;
            $pending_args['status'] = 'pending';
            $pending_count          = Util::get_review_information( $pending_args );

            $approved_args           = $base_args;
            $approved_args['status'] = 'approved';
            $approved_count          = Util::get_review_information( $approved_args );

            $rejected_args           = $base_args;
            $rejected_args['status'] = 'rejected';
            $rejected_count          = Util::get_review_information( $rejected_args );

            $response = rest_ensure_response( $formatted );
            $response->header( 'X-WP-Total', $all_count );
            $response->header( 'X-WP-Status-Pending', $pending_count );
            $response->header( 'X-WP-Status-Approved', $approved_count );
            $response->header( 'X-WP-Status-Rejected', $rejected_count );

            if ( 'same-origin' === $sec_fetch_site && preg_match( '#/dashboard/?$#', $referer ) ) {
                set_transient(
                    Utill::MULTIVENDORX_TRANSIENT_KEYS['review_transient'] . $store_id,
                    $response,
                    DAY_IN_SECONDS
                );
            }

            return $response;
        } catch ( \Exception $e ) {
            MultiVendorX()->util->log( $e );

            return new \WP_Error( 'server_error', __( 'Unexpected server error', 'multivendorx' ), array( 'status' => 500 ) );
        }
    }

    /**
     * Get a single item.
     *
     * @param object $request Full data about the request.
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
            $store_id = $request->get_param( 'store_id' );
            if ( $store_id ) {
                $overall = Util::get_overall_rating( $store_id );
                return rest_ensure_response( $overall );
            }

            $review_id = $request->get_param( 'id' );

            // --- Step 6: Fetch Review Data ---.
            $review = reset( Util::get_review_information( array( 'review_id' => $review_id ) ) );

            $response = rest_ensure_response( array() );

            if ( ! $review ) {
                return $response;
            }

            if ( ! StoreUtil::current_user_can_manage_store( $review['store_id'] ?? 0 ) ) {
                return new \WP_Error(
                    'rest_forbidden',
                    __( 'You are not allowed to view this review.', 'multivendorx' ),
                    array( 'status' => 403 )
                );
            }

            $response->set_data( $this->prepare_rest_item_for_response( $review ) );
            return $response;
        } catch ( \Exception $e ) {
            MultiVendorX()->util->log( $e );

            return new \WP_Error( 'server_error', __( 'Unexpected server error', 'multivendorx' ), array( 'status' => 500 ) );
        }
    }

	/**
	 * Create a new store review via REST API.
	 *
	 * Validates nonce, checks required fields, prevents duplicate reviews,
	 * handles file uploads, calculates overall rating, and stores review data.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|\WP_Error
	 */
    public function create_item( $request ) {
        $nonce = $request->get_header( 'X-WP-Nonce' );
        if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            return new \WP_Error(
                'invalid_nonce',
                __( 'Invalid nonce', 'multivendorx' ),
                array( 'status' => 403 )
            );
        }

        try {
            $user_id = MultiVendorX()->current_user_id;

            $store_id       = absint( $request->get_param( 'store_id' ) );
            $review_title   = sanitize_text_field( $request->get_param( 'review_title' ) );
            $review_content = sanitize_textarea_field( $request->get_param( 'review_content' ) );
            $ratings        = (array) $request->get_param( 'rating' );

            if ( ! $store_id || empty( $ratings ) ) {
                return new \WP_Error(
                    'missing_fields',
                    __( 'Missing required fields.', 'multivendorx' ),
                    array( 'status' => 400 )
                );
            }

            if ( Util::has_reviewed( $store_id, $user_id ) ) {
                return new \WP_Error(
                    'already_reviewed',
                    __( 'You have already reviewed this store.', 'multivendorx' ),
                    array( 'status' => 400 )
                );
            }

            $order_id = Util::is_verified_buyer( $store_id, $user_id );

            $overall = array_sum( array_map( 'intval', $ratings ) ) / count( $ratings );

            $uploaded_images = array();
            $files           = $_FILES['review_images'] ?? null;

            if ( ! empty( ( $files['name'] )[0] ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                // Normalize + sanitize
                $file_names  = array_map( 'sanitize_file_name', (array) ( $files['name'] ?? array() ) );
                $file_types  = (array) ( $files['type'] ?? array() );
                $file_tmp    = (array) ( $files['tmp_name'] ?? array() );
                $file_errors = array_map( 'intval', (array) ( $files['error'] ?? array() ) );
                $file_sizes  = array_map( 'intval', (array) ( $files['size'] ?? array() ) );

                foreach ( $file_names as $index => $name ) {
                    $tmp   = $file_tmp[ $index ] ?? '';
                    $type  = $file_types[ $index ] ?? '';
                    $error = $file_errors[ $index ] ?? UPLOAD_ERR_NO_FILE;
                    $size  = $file_sizes[ $index ] ?? 0;

                    if ( $error !== UPLOAD_ERR_OK ) {
                        continue;
                    }

                    $file   = array(
                        'name'     => $name,
                        'type'     => sanitize_mime_type( $type ),
                        'tmp_name' => $tmp,
                        'error'    => $error,
                        'size'     => $size,
                    );
                    $upload = wp_handle_upload( $file, array( 'test_form' => false ) );

                    if ( ! empty( $upload['error'] ) || empty( $upload['url'] ) ) {
                        continue;
                    }
                    $uploaded_images[] = esc_url_raw( $upload['url'] );
                }
            }

            $review_id = Util::insert_review(
                $store_id,
                $user_id,
                $review_title,
                $review_content,
                $overall,
                $order_id,
                $uploaded_images
            );

            Util::insert_ratings( $review_id, $ratings );

            $review = reset(
                Util::get_review_information( array( 'review_id' => $review_id ) )
            );

            if ( ! $review ) {
                return new \WP_Error(
                    'creation_failed',
                    __( 'Review could not be created.', 'multivendorx' ),
                    array( 'status' => 500 )
                );
            }

            $response = rest_ensure_response(
                $this->prepare_rest_item_for_response( $review )
            );

            $response->set_status( 201 );

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
     * Update a single item.
     *
     * @param object $request Full data about the request.
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
            // Get review ID.
            $id = absint( $request->get_param( 'id' ) );
            if ( ! $id ) {
                return new \WP_Error(
                    'invalid_id',
                    __( 'Invalid review ID', 'multivendorx' ),
                    array( 'status' => 400 )
                );
            }

            // Fetch review info (replace this with your correct util function).
            $review = reset( Util::get_review_information( array( 'id' => $id ) ) );
            if ( ! $review ) {
                return new \WP_Error(
                    'not_found',
                    __( 'Review not found', 'multivendorx' ),
                    array( 'status' => 404 )
                );
            }

            if ( ! StoreUtil::current_user_can_manage_store( $review['store_id'] ?? 0 ) ) {
                return new \WP_Error(
                    'rest_forbidden',
                    __( 'You are not allowed to manage this review.', 'multivendorx' ),
                    array( 'status' => 403 )
                );
            }

            // Fields that can be updated.
            $reply  = $request->get_param( 'reply' );
            $status = $request->get_param( 'status' );

            $data_to_update = array();

            // Save reply text.
            if ( isset( $reply ) ) {
                $data_to_update['reply']      = sanitize_textarea_field( $reply );
                $data_to_update['reply_date'] = current_time( 'mysql' );
            }

            // Save status (Pending / Approved / Rejected).
            if ( isset( $status ) ) {
                $allowed = array( 'Pending', 'Approved', 'Rejected' );
                if ( in_array( $status, $allowed, true ) ) {
                    $data_to_update['status'] = sanitize_text_field( $status );
                } else {
                    return new \WP_Error(
                        'invalid_status',
                        __( 'Invalid status value', 'multivendorx' ),
                        array( 'status' => 400 )
                    );
                }
            }

            if ( empty( $data_to_update ) ) {
                return new \WP_Error(
                    'nothing_to_update',
                    __( 'No valid fields to update', 'multivendorx' ),
                    array( 'status' => 400 )
                );
            }

            // 💾 Save to DB (replace with your helper function).
            $updated = Util::update_review( $id, $data_to_update );

            $store = new Store( $review->store_id );
            $user  = get_user_by( 'id', $review->customer_id );
            do_action(
                'multivendorx_notify_review_reply',
                'review_reply',
                array(
                    'customer_email' => $user->user_email,
					'customer_phone' => get_user_meta( $review->customer_id, 'billing_phone', true ),
                    'store_name'     => $store->exists() ? $store->get( Utill::STORE_SETTINGS_KEYS['name'] ) : '',
                    'customer_name'  => $user->display_name,
                    'category'       => 'activity',
                )
            );

            if ( ! $updated ) {
                return rest_ensure_response( array( 'success' => false ) );
            }

            return rest_ensure_response(
                array(
                    'success' => true,
                    'data'    => array(
                        'id'      => $id,
                        'updated' => $data_to_update,
                    ),
                )
            );
        } catch ( \Exception $e ) {
            MultiVendorX()->util->log( $e );

            return new \WP_Error( 'server_error', __( 'Unexpected server error', 'multivendorx' ), array( 'status' => 500 ) );
        }
    }

    /**
     * Delete a single item.
     *
     * @param object $request Full data about the request.
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
            // 🔹 Get review ID.
            $id = absint( $request->get_param( 'id' ) );
            if ( ! $id ) {
                return new \WP_Error(
                    'invalid_id',
                    __( 'Invalid review ID', 'multivendorx' ),
                    array( 'status' => 400 )
                );
            }

            // 🔹 Fetch the review (to confirm it exists).
            $review = reset( Util::get_review_information( array( 'review_id' => $id ) ) );
            if ( ! $review ) {
                return new \WP_Error(
                    'not_found',
                    __( 'Review not found', 'multivendorx' ),
                    array( 'status' => 404 )
                );
            }

            // Delete via Util helper (this also deletes rating rows).
            $deleted = Util::delete_review( $id );

            if ( ! $deleted ) {
                return new \WP_Error(
                    'delete_failed',
                    __( 'Failed to delete review', 'multivendorx' ),
                    array( 'status' => 500 )
                );
            }

            // Success response.
            return rest_ensure_response( array( 'success' => true ) );
        } catch ( \Exception $e ) {
            MultiVendorX()->util->log( $e );

            return new \WP_Error( 'server_error', __( 'Unexpected server error', 'multivendorx' ), array( 'status' => 500 ) );
        }
    }

    /**
     * Prepare a review item for REST API response.
     *
     * @param array $review Review data array.
     * @return array Formatted review data.
     */
    public function prepare_rest_item_for_response( $review ) {
        $customer      = get_userdata( $review['customer_id'] );
        $customer_name = $customer ? $customer->display_name : __( 'Guest', 'multivendorx' );
        $store_obj     = MultiVendorX()->store->get_store( (int) $review['store_id'] );

        return array(
            'id'                => (int) $review['review_id'],
            'store_id'          => (int) $review['store_id'],
            'store_name'        => $store_obj ? $store_obj->get( 'name' ) : '',
            'customer_id'       => (int) $review['customer_id'],
            'customer_name'     => $customer_name,
            'order_id'          => (int) $review['order_id'],
            'overall_rating'    => round( floatval( $review['overall_rating'] ), 2 ),
            'review_title'      => sanitize_text_field( $review['review_title'] ),
            'review_content'    => wp_strip_all_tags( $review['review_content'] ),
            'status'            => ucfirst( sanitize_text_field( $review['status'] ) ),
            'reported'          => (int) $review['reported'],
            'reply'             => $review['reply'] ?? '',
            'reply_date'        => Utill::multivendorx_rest_prepare_date_response( $review['reply_date'] ) ?? '',
            'date_created'      => Utill::multivendorx_rest_prepare_date_response( $review['date_created'] ),
            'date_modified'     => Utill::multivendorx_rest_prepare_date_response( $review['date_modified'] ),
            'reply_date_gmt'    => Utill::multivendorx_rest_prepare_date_response( $review['reply_date'], true ) ?? '',
            'date_created_gmt'  => Utill::multivendorx_rest_prepare_date_response( $review['date_created'], true ),
            'date_modified_gmt' => Utill::multivendorx_rest_prepare_date_response( $review['date_modified'], true ),
        );
    }
	/**
	 * Calculate store rating summary data.
	 *
	 * Retrieves rating parameters, computes average ratings,
	 * overall rating, total reviews, and rating breakdown.
	 *
	 * @param int $store_id Store ID.
	 *
	 * @return array
	 */
    private function calculate_store_rating_summary( $store_id ) {

        if ( ! $store_id ) {
            return array();
        }

        $parameters = MultiVendorX()->setting->get_setting(
            'ratings_parameters',
            array()
        );

        $averages = Util::get_avg_ratings( $store_id, $parameters );
        $overall  = Util::get_overall_rating( $store_id );

        $reviews       = Util::get_reviews_by_store( $store_id );
        $total_reviews = count( $reviews );

        $breakdown = array(
            5 => 0,
            4 => 0,
            3 => 0,
            2 => 0,
            1 => 0,
        );

        foreach ( $reviews as $review ) {
            $rating = (int) round( floatval( $review->overall_rating ) );

            if ( $rating < 1 ) {
                $rating = 1;
            } elseif ( $rating > 5 ) {
                $rating = 5;
            }

            ++$breakdown[ $rating ];
        }

        return array(
            'averages'      => $averages,
            'overall'       => (float) round( $overall, 1 ),
            'total_reviews' => (int) $total_reviews,
            'breakdown'     => $breakdown,
        );
    }
}
