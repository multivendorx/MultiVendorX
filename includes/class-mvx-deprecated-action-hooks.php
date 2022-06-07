<?php
/**
 * Deprecated action hooks
 *
 * @package WooCommerce\Abstracts
 * @since   3.0.0
 * @version 3.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles deprecation notices and triggering of legacy action hooks.
 */
class MVX_Deprecated_Action_Hooks extends WC_Deprecated_Hooks {

	/**
	 * Array of deprecated hooks we need to handle. Format of 'new' => 'old'.
	 *
	 * @var array
	 */
	protected $deprecated_hooks = array(
		'after_mvx_vendor_description'               => 'after_wcmp_vendor_description',
		'mvx_rest_api_request'                       => 'wcmp_rest_api_request',
		 'mvx_rest_api'                              => 'wcmp_rest_api',
		 'mvx_todo_done_pending_transaction'         => 'wcmp_todo_done_pending_transaction',
		 'mvx_before_update_shipping_method'         => 'wcmp_before_update_shipping_method',
		 'mvx_vendor_details_update'                 => 'wcmp_vendor_details_update',
		 'mvx_rest_insert_vendor_review'             => 'wcmp_rest_insert_vendor_review',
         'before_mvx_orders_list_query_bind'         => 'before_wcmp_orders_list_query_bind',
         'mvx_vendor_order_edit_status'              => 'wcmp_vendor_order_edit_status',
         'mvx_orders_list_do_handle_bulk_actions'    => 'wcmp_orders_list_do_handle_bulk_actions',
         'mvx_orders_list_do_handle_filter_actions'  => 'wcmp_orders_list_do_handle_filter_actions',
         'before_mvx_products_list_query_bind'       => 'before_wcmp_products_list_query_bind',
         'mvx_products_list_do_handle_bulk_actions'  => 'wcmp_products_list_do_handle_bulk_actions',
         'mvx_product_qna_after_question_submitted'  => 'wcmp_product_qna_after_question_submitted',
         'mvx_product_qna_after_vote_submitted'      => 'wcmp_product_qna_after_vote_submitted',
         'mvx_product_qna_after_update_answer_submitted'  => 'wcmp_product_qna_after_update_answer_submitted',
         'mvx_vendor_shipping_'  => 'wcmp_vendor_shipping_',
         'mvx_order_refunded'  => 'wcmp_order_refunded',
         'mvx_after_translated_new_product'  => 'wcmp_after_translated_new_product',
         'after_mvx_calculate_commission'  => 'after_wcmp_calculate_commission',
         'mvx_create_commission_refund_after_commission_note'  => ' wcmp_create_commission_refund_after_commission_note',
         'mvx_commission_fully_refunded'  => 'wcmp_commission_fully_refunded',
         'mvx_commission_partially_refunded'  => 'wcmp_commission_partially_refunded',
         'mvx_after_create_commission_refunds'  => 'wcmp_after_create_commission_refunds',
         'mvx_vendor_commission_created'  => 'wcmp_vendor_commission_created',
         'mvx_after_update_vendor_role_capability'  => 'wcmp_after_update_vendor_role_capability',
         'mvx_orders_migration_order_created'  => 'wcmp_orders_migration_order_created',
         'mvx_add_shipping_package_meta_data'  => 'wcmp_add_shipping_package_meta_data',
         'mvx_orders_migration_order_created'  => 'wcmp_orders_migration_order_created',
         'mvx_frontend_enqueue_scripts'  => 'wcmp_frontend_enqueue_scripts',
         'mvx_vendor_shop_page_policies'  => 'wcmp_vendor_shop_page_policies',
         'mvx_store_widget_contents'  => 'wcmp_store_widget_contents',
         'mvx_vendor_shop_page_'  => 'wcmp_vendor_shop_page_',
         'mvx_jqvmap_enqueue_scripts'  => 'wcmp_jqvmap_enqueue_scripts',
         'mvx_add_shipping_package_meta_data'  => 'wcmp_add_shipping_package_meta_data',
         'mvx_prevent_vendor_order_emails_trigger_action'  => 'wcmp_prevent_vendor_order_emails_trigger_action',
         'mvx_after_suborder_details'  => 'wcmp_after_suborder_details',
         'mvx_checkout_vendor_order_processed'  => 'wcmp_checkout_vendor_order_processed',
         'mvx_checkout_create_order'  => 'wcmp_checkout_create_order',
         'mvx_vendor_create_order_line_item'  => 'wcmp_vendor_create_order_line_item',
         'mvx_vendor_create_order_shipping_item'  => 'wcmp_vendor_create_order_shipping_item',
         'mvx_checkout_create_order_coupon_item'  => 'wcmp_checkout_create_order_coupon_item',
         'mvx_order_refunded'  => 'wcmp_order_refunded',
         'mvx_after_suborder_details'  => 'wcmp_after_suborder_details',
         'mvx_vendor_order_on_cancelled_commission'  => 'wcmp_vendor_order_on_cancelled_commission',
         'mvx_transaction_update_meta_data'  => 'wcmp_transaction_update_meta_data',
         'mvx_transaction_email_notification'  => 'wcmp_transaction_email_notification',
         'mvx_commission_update_commission_meta'  => 'wcmp_commission_update_commission_meta',
         'mvx_commission_before_save_commission_total'  => 'wcmp_commission_before_save_commission_total',
         'mvx_commission_after_save_commission_total'  => 'wcmp_commission_after_save_commission_total',
         'mvx_admin_commission_before_order_item_'  => 'wcmp_admin_commission_before_order_item_',
         'mvx_admin_commission_before_order_itemmeta'  => 'wcmp_admin_commission_before_order_itemmeta',
         'mvx_admin_commission_after_order_itemmeta'  => 'wcmp_admin_commission_after_order_itemmeta',
         'mvx_admin_commission_admin_order_item_values'  => 'wcmp_admin_commission_admin_order_item_values',
         'mvx_admin_commission_order_item_'  => 'wcmp_admin_commission_order_item_',
         'mvx_admin_commission_order_items_after_line_items'  => 'wcmp_admin_commission_order_items_after_line_items',
         'mvx_admin_commission_before_order_itemmeta'  => 'wcmp_admin_commission_before_order_itemmeta',
         'mvx_admin_commission_order_item_values'  => 'wcmp_admin_commission_order_item_values',

	);

	/**
	 * Array of versions on each hook has been deprecated.
	 *
	 * @var array
	 */
	protected $deprecated_version = array(
		'after_wcmp_vendor_description'                   => '4.0.0',
		'wcmp_rest_api_request'                           => '4.0.0',
		'wcmp_rest_api'                                   => '4.0.0',
		'wcmp_todo_done_pending_transaction'              => '4.0.0',
		'wcmp_before_update_shipping_method'              => '4.0.0',
		'wcmp_vendor_details_update'                      => '4.0.0',
		'wcmp_rest_insert_vendor_review'                  => '4.0.0',
		'before_wcmp_orders_list_query_bind'              => '4.0.0',
		'wcmp_vendor_order_edit_status'                   => '4.0.0',
		'wcmp_orders_list_do_handle_bulk_actions1'        => '4.0.0',
		'wcmp_orders_list_do_handle_filter_actions'       => '4.0.0',
		'before_wcmp_products_list_query_bind'            => '4.0.0',
		'wcmp_products_list_do_handle_bulk_actions'       => '4.0.0',
		'wcmp_product_qna_after_question_submitted'       => '4.0.0',
		'after_wcmp_calculate_commission'                 => '4.0.0',
		'wcmp_product_qna_after_update_answer_submitted'  => '4.0.0',
		'wcmp_vendor_shipping_'                           => '4.0.0',
		'wcmp_order_refunded'                             => '4.0.0',
		'wcmp_after_translated_new_product'               => '4.0.0',
		'wcmp_create_commission_refund_after_commission_note'  => '4.0.0',
		'wcmp_commission_fully_refunded'                  => '4.0.0',
		'wcmp_commission_partially_refunded'              => '4.0.0',
		'wcmp_after_create_commission_refunds'            => '4.0.0',
		'wcmp_vendor_commission_created'                  => '4.0.0',
		'wcmp_after_update_vendor_role_capability'        => '4.0.0',
		'wcmp_orders_migration_order_created'             => '4.0.0',
		'wcmp_add_shipping_package_meta_data'             => '4.0.0',
		'wcmp_orders_migration_order_created'             => '4.0.0',
		'wcmp_frontend_enqueue_scripts'                   => '4.0.0',
		'wcmp_vendor_shop_page_policies'                  => '4.0.0',
		'wcmp_store_widget_contents'                      => '4.0.0',
		'wcmp_vendor_shop_page_'                          => '4.0.0',
		'wcmp_jqvmap_enqueue_scripts'             => '4.0.0',
		'wcmp_add_shipping_package_meta_data'                   => '4.0.0',
		'wcmp_prevent_vendor_order_emails_trigger_action'                  => '4.0.0',
		'wcmp_after_suborder_details'                      => '4.0.0',
		'wcmp_checkout_vendor_order_processed'                          => '4.0.0',
		'wcmp_checkout_create_order'             => '4.0.0',
		'wcmp_vendor_create_order_line_item'                   => '4.0.0',
		'wcmp_vendor_create_order_shipping_item'                  => '4.0.0',
		'wcmp_checkout_create_order_coupon_item'                      => '4.0.0',
		'wcmp_order_refunded'                          => '4.0.0',
		'wcmp_after_suborder_details'             => '4.0.0',
		'wcmp_vendor_order_on_cancelled_commission'                   => '4.0.0',
		'wcmp_transaction_update_meta_data'                  => '4.0.0',
		'wcmp_transaction_email_notification'                      => '4.0.0',
		'wcmp_commission_update_commission_meta'                          => '4.0.0',
		'wcmp_commission_before_save_commission_total'             => '4.0.0',
		'wcmp_commission_after_save_commission_total'                   => '4.0.0',
		'wcmp_admin_commission_before_order_item_'                  => '4.0.0',
		'wcmp_admin_commission_before_order_itemmeta'                      => '4.0.0',
		'wcmp_admin_commission_after_order_itemmeta'                          => '4.0.0',
		'wcmp_admin_commission_admin_order_item_values'             => '4.0.0',
		'wcmp_admin_commission_order_item_'                   => '4.0.0',
		'wcmp_admin_commission_order_items_after_line_items'                  => '4.0.0',
		'wcmp_admin_commission_before_order_itemmeta'                      => '4.0.0',
		'wcmp_admin_commission_order_item_values'                          => '4.0.0',

	);

	/**
	 * Hook into the new hook so we can handle deprecated hooks once fired.
	 *
	 * @param string $hook_name Hook name.
	 */
	public function hook_in( $hook_name ) {
		add_action( $hook_name, array( $this, 'maybe_handle_deprecated_hook' ), -1000, 8 );
	}

	/**
	 * If the old hook is in-use, trigger it.
	 *
	 * @param  string $new_hook          New hook name.
	 * @param  string $old_hook          Old hook name.
	 * @param  array  $new_callback_args New callback args.
	 * @param  mixed  $return_value      Returned value.
	 * @return mixed
	 */
	public function handle_deprecated_hook( $new_hook, $old_hook, $new_callback_args, $return_value ) {
		if ( has_action( $old_hook ) ) {
			$this->display_notice( $old_hook, $new_hook );
			$return_value = $this->trigger_hook( $old_hook, $new_callback_args );
		}
		return $return_value;
	}

	/**
	 * Fire off a legacy hook with it's args.
	 *
	 * @param  string $old_hook          Old hook name.
	 * @param  array  $new_callback_args New callback args.
	 * @return mixed
	 */
	protected function trigger_hook( $old_hook, $new_callback_args ) {
		switch ( $old_hook ) {
			case 'woocommerce_order_add_shipping':
			case 'woocommerce_order_add_fee':
				$item_id  = $new_callback_args[0];
				$item     = $new_callback_args[1];
				$order_id = $new_callback_args[2];
				if ( is_a( $item, 'WC_Order_Item_Shipping' ) || is_a( $item, 'WC_Order_Item_Fee' ) ) {
					do_action( $old_hook, $order_id, $item_id, $item );
				}
				break;
			case 'woocommerce_order_add_coupon':
				$item_id  = $new_callback_args[0];
				$item     = $new_callback_args[1];
				$order_id = $new_callback_args[2];
				if ( is_a( $item, 'WC_Order_Item_Coupon' ) ) {
					do_action( $old_hook, $order_id, $item_id, $item->get_code(), $item->get_discount(), $item->get_discount_tax() );
				}
				break;
			case 'woocommerce_order_add_tax':
				$item_id  = $new_callback_args[0];
				$item     = $new_callback_args[1];
				$order_id = $new_callback_args[2];
				if ( is_a( $item, 'WC_Order_Item_Tax' ) ) {
					do_action( $old_hook, $order_id, $item_id, $item->get_rate_id(), $item->get_tax_total(), $item->get_shipping_tax_total() );
				}
				break;
			case 'woocommerce_add_shipping_order_item':
				$item_id  = $new_callback_args[0];
				$item     = $new_callback_args[1];
				$order_id = $new_callback_args[2];
				if ( is_a( $item, 'WC_Order_Item_Shipping' ) ) {
					do_action( $old_hook, $order_id, $item_id, $item->legacy_package_key );
				}
				break;
			case 'woocommerce_add_order_item_meta':
				$item_id  = $new_callback_args[0];
				$item     = $new_callback_args[1];
				$order_id = $new_callback_args[2];
				if ( is_a( $item, 'WC_Order_Item_Product' ) ) {
					do_action( $old_hook, $item_id, $item->legacy_values, $item->legacy_cart_item_key );
				}
				break;
			case 'woocommerce_add_order_fee_meta':
				$item_id  = $new_callback_args[0];
				$item     = $new_callback_args[1];
				$order_id = $new_callback_args[2];
				if ( is_a( $item, 'WC_Order_Item_Fee' ) ) {
					do_action( $old_hook, $order_id, $item_id, $item->legacy_fee, $item->legacy_fee_key );
				}
				break;
			case 'woocommerce_order_edit_product':
				$item_id  = $new_callback_args[0];
				$item     = $new_callback_args[1];
				$order_id = $new_callback_args[2];
				if ( is_a( $item, 'WC_Order_Item_Product' ) ) {
					do_action( $old_hook, $order_id, $item_id, $item, $item->get_product() );
				}
				break;
			case 'woocommerce_order_update_coupon':
			case 'woocommerce_order_update_shipping':
			case 'woocommerce_order_update_fee':
			case 'woocommerce_order_update_tax':
				if ( ! is_a( $item, 'WC_Order_Item_Product' ) ) {
					do_action( $old_hook, $order_id, $item_id, $item );
				}
				break;
			default:
				do_action_ref_array( $old_hook, $new_callback_args );
				break;
		}
	}
}
