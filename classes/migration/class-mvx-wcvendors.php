<?php

/**
 * WC vendors to MVX migration class 
 * 
 * @author 		MultiVendorX
 * @package MultiVendorX/core
 * @version   	3.6
 */

class MVX_WCVendors {
	
	public function __construct() {}
	
	// Get all WC vendor
	public function get_marketplace_vendor() {
		$seller_query = new WP_User_Query( array(
			'role'	  => 'vendor',
		) );
		$marketplace_get_vendors = $seller_query->get_results();
		return $marketplace_get_vendors;
	}

	// store migrate	
	public function store_product_vendor_migrate( $vendor_id ) {
		global $MVX;
		$user = new WP_User(absint($vendor_id));
		if( !$vendor_id ) return false;
		if(!in_array('dc_vendor', $user->roles)) {

			$user->set_role('dc_vendor');
			$user->remove_cap( 'vendor');
			$vendor = get_mvx_vendor($vendor_id);

			$term_id = get_user_meta( $vendor_id, '_vendor_term_id', true);
			$shipping_class_id = get_user_meta( $vendor_id, 'shipping_class_id', true );
			wp_update_term( absint($term_id), 'dc_vendor_shop' );
			wp_update_term( absint($shipping_class_id), 'product_shipping_class' );

			$this->store_vendor_data_migrate( $vendor_id );

			// commission
			$commission_fixed   = get_user_meta( $vendor_id, '_wcv_commission_amount', true );
			$commission_percent = get_user_meta( $vendor_id, '_wcv_commission_percent', true );

			if (get_user_meta( $vendor_id, '_wcv_commission_amount', true )) {
				update_user_meta( $vendor_id, '_vendor_commission', get_user_meta( $vendor_id, '_wcv_commission_amount', true ) );
			}
			if (get_user_meta( $vendor_id, '_wcv_commission_percent', true )) {
				update_user_meta( $vendor_id, '_vendor_commission', get_user_meta( $vendor_id, '_wcv_commission_percent', true ) );
			}
			
			$this->store_product_migrate( $vendor_id, $term_id );
		}
		return true;
	}

	public function store_product_migrate( $vendor_id, $term_id ) {
		global $MVX;
		$vendor_products = $MVX->multivendor_migration->mvx_get_products_by_vendor( $vendor_id );
		if($vendor_products) {
			foreach($vendor_products as $product ) {
				wp_delete_object_term_relationships($product->ID, $MVX->taxonomy->taxonomy_name);
				wp_set_object_terms($product->ID, (int) $term_id, $MVX->taxonomy->taxonomy_name, true);

				$commission_percentage = get_post_meta( $product->ID, 'wcv_commission_percent', true);
				$commission_fixed      = get_post_meta( $product->ID, 'wcv_commission_amount', true);

				if ($MVX->vendor_caps->payment_cap['commission_type'] == 'fixed_with_percentage') {
					update_post_meta($product->ID, '_commission_fixed_with_percentage', $commission_fixed);
					update_post_meta($product->ID, '_commission_percentage_per_product', $commission_percentage);
				} elseif ($MVX->vendor_caps->payment_cap['commission_type'] == 'fixed_with_percentage_qty') {
					update_post_meta($product->ID, '_commission_fixed_with_percentage_qty', $commission_fixed);
					update_post_meta($product->ID, '_commission_percentage_per_product', $commission_percentage);
				} else {
					update_post_meta($product->ID, '_commission_per_product', $commission_fixed);
					update_post_meta($product->ID, '_commission_fixed_with_percentage', $commission_percentage);
				}
			}
		}
	}

	public function store_vendor_data_migrate( $vendor_id ) {
		// Store Policy
		$wcv_shipping = (array) get_user_meta( $vendor_id, '_wcv_shipping', true );
		if (isset($wcv_shipping['shipping_policy'])) {
			update_user_meta( $vendor_id, '_vendor_shipping_policy', $wcv_shipping['shipping_policy'] ); 
		}
		if (isset($wcv_shipping['return_policy'])) {
			update_user_meta( $vendor_id, '_vendor_refund_policy', $wcv_shipping['return_policy'] ); 
		}
		// social
		if (get_user_meta($vendor_id, '_wcv_twitter_username', true )) {
			update_user_meta($vendor_id, '_vendor_twitter_profile', get_user_meta($vendor_id, '_wcv_twitter_username', true ));
		}
		if (get_user_meta($vendor_id, '_wcv_facebook_url', true )) {
			update_user_meta($vendor_id, '_vendor_fb_profile', get_user_meta($vendor_id, '_wcv_facebook_url', true ));
		}
		if (get_user_meta($vendor_id, '_wcv_linkedin_url', true )) {
			update_user_meta($vendor_id, '_vendor_linkdin_profile', get_user_meta($vendor_id, '_wcv_linkedin_url', true ));
		}
		if (get_user_meta($vendor_id, '_wcv_youtube_url', true )) {
			update_user_meta($vendor_id, '_vendor_youtube', get_user_meta($vendor_id, '_wcv_youtube_url', true ));
		}
		if (get_user_meta($vendor_id, '_wcv_instagram_username', true )) {
			update_user_meta($vendor_id, '_vendor_instagram', get_user_meta($vendor_id, '_wcv_instagram_username', true ));
		}
		// store address and details
		if (get_user_meta( $vendor_id, '_wcv_store_phone', true )) {
			update_user_meta( $vendor_id, '_vendor_phone', get_user_meta( $vendor_id, '_wcv_store_phone', true ) );
		}
		if (get_user_meta( $vendor_id, '_wcv_store_address1', true )) {
			update_user_meta( $vendor_id, '_vendor_address_1', get_user_meta( $vendor_id, '_wcv_store_address1', true ) );
		}
		if (get_user_meta( $vendor_id, '_wcv_store_address2', true )) {
			update_user_meta( $vendor_id, '_vendor_address_2', get_user_meta( $vendor_id, '_wcv_store_address2', true ) );
		}
		if (get_user_meta( $vendor_id, '_wcv_store_city', true )) {
			update_user_meta( $vendor_id, '_vendor_city', get_user_meta( $vendor_id, '_wcv_store_city', true ) );
		}
		if (get_user_meta( $vendor_id, '_wcv_store_postcode', true )) {
			update_user_meta( $vendor_id, '_vendor_postcode', get_user_meta( $vendor_id, '_wcv_store_postcode', true ) );
		}
		if (get_user_meta( $vendor_id, '_wcv_store_state', true )) {
			update_user_meta( $vendor_id, '_vendor_state', get_user_meta( $vendor_id, '_wcv_store_state', true ) );
		}
		if (get_user_meta( $vendor_id, '_wcv_store_country', true )) {
			update_user_meta( $vendor_id, '_vendor_country', get_user_meta( $vendor_id, '_wcv_store_country', true ) );
		}
	}

	public function store_order_migrate() {
		global $MVX;
		$wcvendors_get_vendors = $this->get_marketplace_vendor();
		if( empty( $wcvendors_get_vendors ) ) {

			$woocommerce_orders = get_posts( array(
				'numberposts' => -1,
				'post_type'   => wc_get_order_types(),
				'post_status' => array_keys( wc_get_order_statuses() ),
				'post_parent'    => 0
			) );
			if (!empty($woocommerce_orders)) {
				foreach ($woocommerce_orders as $woocommerce_order) {
					$order_id = $woocommerce_order->ID;
					$order = wc_get_order($order_id);
					if(!$order) continue;
					$_mvx_vendor_specific_order_migrated = $order->get_meta( '_mvx_vendor_specific_order_migrated', true) ? $order->get_meta( '_mvx_vendor_specific_order_migrated', true) : array();
					$set_order_id_migration = array();
					if ( !in_array($order_id, $_mvx_vendor_specific_order_migrated) ) {

						$set_order_id_migration[] = $order_id;

						// Remove previous added items
						$line_items = $order->get_items();
						$shipping_items = $order->get_items('shipping');

						foreach ($line_items as $key_items => $value_items) {
							wc_delete_order_item_meta( $key_items, '_vendor_id' );
						}

						foreach ($shipping_items as $key_shipping => $value_shipping) {
							wc_delete_order_item_meta( $key_shipping, 'method_slug' ); 
						}

						$suborder_create = $MVX->order->mvx_manually_create_order_item_and_suborder($order_id, '', true);
						$order->update_meta_data('_mvx_vendor_specific_order_migrated', $set_order_id_migration);
						$order->save();
					}
				}
			}
			// Paid those commission which is already paid in wcvendors
			$this->mvx_paid_commission_from_previous_marketplace();
			// Deactive plugins
			$this->deactive_previous_multivendor();

			mvx_update_option('mvx_migration_orders_table_migrated', true);
			wp_clear_scheduled_hook('migrate_multivendor_order_table');
		}
	}

	public function mvx_paid_commission_from_previous_marketplace() {
		global $wpdb;
		$paid_status = 'paid';
		$wcvendors_vendor_paid_order_list = $wpdb->get_results($wpdb->prepare("SELECT order_id FROM `{$wpdb->prefix}pv_commission` WHERE status = %s", $paid_status ));
		if ($wcvendors_vendor_paid_order_list) {
			foreach ($wcvendors_vendor_paid_order_list as $key_commission => $value_commission) {
				if ( wp_get_post_parent_id( $value_commission->order_id ) == 0 ) {
					$mvx_suborders = get_mvx_suborders($value_commission->order_id);
					if ( $mvx_suborders ) {
						foreach ( $mvx_suborders as $suborder ) {
							$commission_id = get_post_meta( $suborder->get_id(), '_commission_id', true );
							mvx_paid_commission_status($commission_id);
						}
					}
				}
			}
		}
	}

	// Deactive wc vendor multivendor
	public function deactive_previous_multivendor() {
		// WC vendor free deactive
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		if ( is_plugin_active('wc-vendors/class-wc-vendors.php') ) {
	    	deactivate_plugins('wc-vendors/class-wc-vendors.php');    
	    }
	}

}
