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
	);

	/**
	 * Array of versions on each hook has been deprecated.
	 *
	 * @var array
	 */
	protected $deprecated_version = array(
		'after_wcmp_vendor_description'                   => '4.0.0',
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
