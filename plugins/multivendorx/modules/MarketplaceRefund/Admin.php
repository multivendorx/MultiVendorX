<?php
/**
 * MultiVendorX Admin class file
 *
 * @package MultiVendorX
 */

namespace MultiVendorX\MarketplaceRefund;

/**
 * MultiVendorX Refund Admin class
 *
 * @class       Admin class
 * @version     5.0.0
 * @author      MultiVendorX
 */
class Admin {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_status_for_refund_and_return' ) );
        add_filter( 'wc_order_statuses', array( $this, 'add_refund_and_return_requested_to_order_statuses' ) );
        add_action( 'woocommerce_process_shop_order_meta', array( $this, 'multivendorx_refund_order_status_save' ) );
    }

    /**
     * Register the refund status.
     *
     * @return void
     */
    public function register_status_for_refund_and_return() {
        register_post_status(
            'wc-refund-requested',
            array(
				'label'                     => _x( 'Refund Requested', 'Order status', 'multivendorx' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
                // translators: %s: Number of orders with the "Refund Requested" status.
				'label_count'               => _n_noop( 'Refund Requested (%s)', 'Refund Requested (%s)', 'multivendorx' ),
            )
        );
        register_post_status(
            'wc-refund-accepted',
            array(
                'label'                     => _x( 'Refund Accepted', 'Order status', 'multivendorx' ),
                'public'                    => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                // translators: %s: Number of orders with the "Refund Accepted" status.
                'label_count'               => _n_noop( 'Refund Accepted (%s)', 'Refund Accepted (%s)', 'multivendorx' ),
            )
        );
        register_post_status(
            'wc-refund-rejected',
            array(
                'label'                     => _x( 'Refund Rejected', 'Order status', 'multivendorx' ),
                'public'                    => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                // translators: %s: Number of orders with the "Refund Rejected" status.
                'label_count'               => _n_noop( 'Refund Rejected (%s)', 'Refund Rejected (%s)', 'multivendorx' ),
            )
        );
        register_post_status(
            'wc-return-requested',
            array(
                'label'                     => _x( 'Return Requested', 'Order status',
                'multivendorx'),
                'public'                    => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop('Return Requested (%s)',  'Return Requested (%s)', 'multivendorx' ),
            )
        );

        register_post_status(
            'wc-return-accepted',
            array(
                'label'                     => _x( 'Return Accepted', 'Order status',
                'multivendorx'),
                'public'                    => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( 'Return Accepted (%s)', 'Return Accepted (%s)', 'multivendorx' ),
            )
        );

        register_post_status(
            'wc-return-rejected',
             array(
                'label'                     => _x( 'Return Rejected', 'Order status',
                'multivendorx' ),
                'public'                    => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( 'Return Rejected (%s)', 'Return Rejected (%s)', 'multivendorx' ),
            )
        );
    }

    /**
     * Add the refund status to the order statuses.
     *
     * @param array $order_statuses Order statuses.
     * @return array
     */
    public function add_refund_and_return_requested_to_order_statuses( $order_statuses ) {
        $new_statuses = array();

        foreach ( $order_statuses as $key => $label ) {
            $new_statuses[ $key ] = $label;

            if ( 'wc-on-hold' === $key ) {
                $new_statuses['wc-refund-requested'] = _x( 'Refund Requested', 'Order status', 'multivendorx' );
                $new_statuses['wc-refund-accepted']  = _x( 'Refund Accepted', 'Order status', 'multivendorx' );
                $new_statuses['wc-refund-rejected']  = _x( 'Refund Rejected', 'Order status', 'multivendorx' );
                $new_statuses['wc-return-requested'] = _x( 'Return Requested', 'Order status', 'multivendorx' );
                $new_statuses['wc-return-accepted'] = _x( 'Return Accepted', 'Order status', 'multivendorx' );
                $new_statuses['wc-return-rejected'] = _x( 'Return Rejected', 'Order status', 'multivendorx' );
            }
        }

        return $new_statuses;
    }

    /**
     * Save the refund status of an order.
     *
     * @param int $order_id Order ID.
     * @return void
     */
    public function multivendorx_refund_order_status_save( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( empty( $order_id ) || ( $order_id && $order->get_type() !== 'shop_order' ) ) {
			return;
        }
        if ( $order->get_parent_id() === 0 ) {
			return;
        }
        if ( ! filter_input( INPUT_POST, 'cust_refund_status', FILTER_DEFAULT ) ) {
			return $order_id;
        }
        $refund_order_customer = sanitize_text_field( filter_input( INPUT_POST, 'refund_order_customer', FILTER_DEFAULT ) );
        if ( ! empty( $refund_order_customer ) ) {
            $order->update_meta_data( '_customer_refund_order', $refund_order_customer );
            $order->save();
            // Trigger customer email.
            if ( in_array( $refund_order_customer, array( 'refund_reject', 'refund_accept' ), true ) ) {
                $admin_reason = sanitize_text_field(
                    filter_input( INPUT_POST, 'refund_admin_reason_text', FILTER_DEFAULT )
                );

                $refund_details = array(
                    'admin_reason' => $admin_reason ? $admin_reason : '',
                );

                $order_status  = '';
                $refund_status = sanitize_text_field( filter_input( INPUT_POST, 'refund_order_customer', FILTER_DEFAULT ) );
                if ( 'refund_accept' === $refund_status ) {
                    $order_status = __( 'accepted', 'multivendorx' );
                } elseif ( 'refund_reject' === $refund_status ) {
                    $order_status = __( 'rejected', 'multivendorx' );
                }
                // Comment note for suborder.
                $comment_id = $order->add_order_note( __( 'Site admin ', 'multivendorx' ) . $order_status . __( ' refund request for order #', 'multivendorx' ) . $order_id . ' .' );
                // user info.
                $user_info = get_userdata( MultiVendorX()->current_user_id );
                wp_update_comment(
                    array(
						'comment_ID'           => $comment_id,
						'comment_author'       => $user_info->user_name,
						'comment_author_email' => $user_info->user_email,
                    )
                );

                // Comment note for parent order.
                $parent_order_id   = $order->get_parent_id();
                $parent_order      = wc_get_order( $parent_order_id );
                $comment_id_parent = $parent_order->add_order_note( __( 'Site admin ', 'multivendorx' ) . $order_status . __( ' refund request for order #', 'multivendorx' ) . $order_id . '.' );
                wp_update_comment(
                    array(
						'comment_ID'           => $comment_id_parent,
						'comment_author'       => $user_info->user_name,
						'comment_author_email' => $user_info->user_email,
                    )
                );
            }
        }
    }
}
