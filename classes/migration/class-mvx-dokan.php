<?php

/**
 * Doakn Multivendor to MVX migration class 
 *
 * @author 		MultiVendorX
 * @package MultiVendorX/core
 * @version     3.6
 */

class MVX_Dokan {
	
	public function __construct() {}
	
	// Get all dokan vendor
	public function get_marketplace_vendor() {
		$seller_query = new WP_User_Query( array(
			'role'	  => 'seller',
		) );
		$marketplace_get_vendors = $seller_query->get_results();
		return $marketplace_get_vendors;
	}

	// store migrate	
	public function store_product_vendor_migrate( $vendor_id ) {
		$user = new WP_User(absint($vendor_id));
		if( !$vendor_id ) return false;
		if(!in_array('dc_vendor', $user->roles)) {
			// set vendor role
			$user->set_role('dc_vendor');
			// remove revious plugin role
			$user->remove_cap( 'seller');
			$vendor = get_mvx_vendor($vendor_id);
			if( !$vendor ) return false;
			$term_id = get_user_meta( $vendor_id, '_vendor_term_id', true);
			$shipping_class_id = get_user_meta( $vendor_id, 'shipping_class_id', true );
			wp_update_term( absint($term_id), 'dc_vendor_shop' );
			wp_update_term( absint($shipping_class_id), 'product_shipping_class' );
			
			// save store details
			$this->store_vendor_data_migrate( $vendor_id );
			// store product migration
			$this->store_product_migrate( $vendor_id, $term_id );
			// commission setup
			if (get_user_meta( $vendor_id, 'dokan_admin_percentage', true )) {
				update_user_meta( $vendor_id, '_vendor_commission', get_user_meta( $vendor_id, 'dokan_admin_percentage', true ) );
			}
			$vendor->update_page_slug(wc_clean($user->data->user_nicename));
			$user_details = get_user_meta($vendor_id, 'dokan_profile_settings', true);
			// update image
			if ( !empty($user_details) && isset($user_details['gravatar'])) {
				update_user_meta($vendor_id, '_vendor_image', $user_details['gravatar']);
			}
			// update banner
			if ( !empty($user_details) && isset($user_details['banner'])) {
				update_user_meta($vendor_id, '_vendor_banner', $user_details['banner']);
			}
			// update store name
			if ( !empty($user_details) && isset($user_details['store_name'])) {
				$vendor->update_page_title(wc_clean($user_details['store_name']));
			}
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
			}
		}
	}

	public function store_vendor_data_migrate( $vendor_id ) {
		$vendor_data = get_user_meta( $vendor_id, 'dokan_profile_settings', true );
		if (isset($vendor_data)) {
			// Store Policy
			$shipping_policy = get_user_meta( $vendor_id, '_dps_ship_policy', true );
			$_dps_refund_policy = get_user_meta( $vendor_id, '_dps_refund_policy', true );
			if (isset($shipping_policy)) {
				update_user_meta( $vendor_id, '_vendor_shipping_policy', $shipping_policy ); 
			}
			if (isset($_dps_refund_policy)) {
				update_user_meta( $vendor_id, '_vendor_refund_policy', $_dps_refund_policy ); 
			}
			// social
			$dokan_profile = get_user_meta( $vendor_id, 'dokan_profile_settings', true );
			if (isset($dokan_profile['social']) && !empty($dokan_profile['social'])) {
				update_user_meta($vendor_id, '_vendor_fb_profile', $dokan_profile['social']['fb']);
				update_user_meta($vendor_id, '_vendor_twitter_profile', $dokan_profile['social']['twitter']);
				update_user_meta($vendor_id, '_vendor_linkdin_profile', $dokan_profile['social']['linkedin']);
				update_user_meta($vendor_id, '_vendor_youtube', $dokan_profile['social']['youtube']);
				update_user_meta($vendor_id, '_vendor_instagram', $dokan_profile['social']['instagram']);
			}
			// store address and details
			if (isset( $vendor_data['phone'] )) {
				update_user_meta( $vendor_id, '_vendor_phone', wc_clean( $vendor_data['phone'] ) );
			}
			if (isset( $vendor_data['street_1'] )) {
				update_user_meta( $vendor_id, '_vendor_address_1', wc_clean( $vendor_data['street_1'] ) );
			}
			if (isset( $vendor_data['street_2'] )) {
				update_user_meta( $vendor_id, '_vendor_address_2', wc_clean( $vendor_data['street_2'] ) );
			}
			if (isset( $vendor_data['city'] )) {
				update_user_meta( $vendor_id, '_vendor_city', wc_clean( $vendor_data['city'] ) );
			}
			if (isset( $vendor_data['zip'] )) {
				update_user_meta( $vendor_id, '_vendor_postcode', wc_clean( $vendor_data['zip'] ) );
			}
			if (isset( $vendor_data['state'] )) {
				update_user_meta( $vendor_id, '_vendor_state', wc_clean( $vendor_data['state'] ) );
			}
			if (isset( $vendor_data['country'] )) {
				update_user_meta( $vendor_id, '_vendor_country', wc_clean( $vendor_data['country'] ) );
			}
			// location
			if (isset($store_setting['location']) && !empty($store_setting['location'])) {
				update_user_meta($vendor_id, '_store_location', wc_clean($store_setting['location']));
			}
		}
	}

	public function store_order_migrate() {
		global $MVX;
		$dokan_get_vendors = $this->get_marketplace_vendor();
		if( empty( $dokan_get_vendors ) ) {
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
			// Paid those commission which is already paid in Dokan
			$this->mvx_paid_commission_from_previous_marketplace();
			// Deactive plugins
			$this->deactive_previous_multivendor();

			mvx_update_option('mvx_migration_orders_table_migrated', true);
    		wp_clear_scheduled_hook('migrate_multivendor_order_table');
    	}
	}

	public function mvx_paid_commission_from_previous_marketplace() {
		global $wpdb;
		$status_true = 1;
		$dokan_vendor_paid_user_list = $wpdb->get_results($wpdb->prepare("SELECT user_id FROM `{$wpdb->prefix}dokan_withdraw` WHERE status = %d", $status_true ));
		if ($dokan_vendor_paid_user_list) {
			foreach ($dokan_vendor_paid_user_list as $key_user => $value_user) {

				$woocommerce_orders = get_posts( array(
					'numberposts' => -1,
					'author' => $value_user->user_id
				) );

				if ( !empty($woocommerce_orders) && wp_get_post_parent_id( $woocommerce_orders->ID ) != 0 ) {
					$commission_id = get_post_meta( $woocommerce_orders->ID, '_commission_id', true );
					mvx_paid_commission_status($commission_id);
				}
			}
		}
	}
	// Deactive dokan multivendor
	public function deactive_previous_multivendor() {
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		// dokan free deactive
		if ( is_plugin_active('dokan-lite/dokan.php') ) {
	    	deactivate_plugins('dokan-lite/dokan.php');    
	    }
	    // dokan pro deactive
	    if ( is_plugin_active('dokan-pro/dokan-pro.php') ) {
	    	deactivate_plugins('dokan-pro/dokan-pro.php');    
	    }
	}

}
