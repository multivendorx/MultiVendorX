<?php
/**
 * MultiVendorX Store Util Class
 *
 * @package MultiVendorX
 */

namespace MultiVendorX\Store;

use MultiVendorX\Utill;

defined( 'ABSPATH' ) || exit;

/**
 * MultiVendorX Store Util Class.
 *
 * @class       StoreUtil class
 * @version     5.0.0
 * @author      MultiVendorX
 */
class StoreUtil {

	/**
	 * Add store users
	 *
	 * @param array $args Array of arguments.
	 */
	public static function add_store_users( $args ) {
		global $wpdb;
		$table = "{$wpdb->prefix}" . Utill::TABLES['store_users'];

		$store_id = $args['store_id'] ?? 0;
		$role_id  = $args['role_id'] ?? '';
		$owners   = $args['users'] ?? array();

		// Remove old users not in list.
		$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->prepare(
                "DELETE FROM {$table} WHERE store_id = %d AND role_id = %s AND user_id NOT IN (" . implode( ',', array_map( 'intval', $owners ) ) . ')', // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared
                $store_id,
                $role_id
            )
		);

		// Insert new users.
		foreach ( $owners as $user_id ) {
			$exists = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $wpdb->prepare(
                    "SELECT ID FROM {$table} WHERE store_id = %d AND role_id = %s AND user_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                    $store_id,
                    $role_id,
                    $user_id
                )
			);

			if ( ! $exists ) {
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
					$table,
					array(
						'store_id' => $store_id,
						'user_id'  => $user_id,
						'role_id'  => $role_id,
                    ),
                    array( '%d', '%d', '%s' )
				);
			}
		}

		if ( ! empty( $wpdb->last_error ) && MultiVendorX()->show_advanced_log ) {
			MultiVendorX()->util->log( 'Database operation failed', 'ERROR' );
		}
	}

	/**
	 * Get store users
	 *
	 * @param int $store_id Store ID.
	 * @return array
	 */
	public static function get_store_users( $store_id ) {
		global $wpdb;
		$table = "{$wpdb->prefix}" . Utill::TABLES['store_users'];

		$primary_owner_id = self::get_primary_owner( $store_id );
		$users            = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->prepare(
                "SELECT user_id FROM $table WHERE store_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $store_id
            ),
            ARRAY_A
		);
		$user_ids         = wp_parse_id_list( wp_list_pluck( $users, 'user_id' ) );

		if ( ! empty( $wpdb->last_error ) && MultiVendorX()->show_advanced_log ) {
			MultiVendorX()->util->log( 'Database operation failed', 'ERROR' );
		}

		return array(
			'users'         => $user_ids,
			'primary_owner' => $primary_owner_id,
		);
	}

	/**
	 * Get all stores
	 *
	 * @return array
	 */
	public static function get_stores() {
		global $wpdb;

		$table = "{$wpdb->prefix}" . Utill::TABLES['store'];
		$store = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            "SELECT * FROM {$table}", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            ARRAY_A
		);

		if ( ! empty( $wpdb->last_error ) && MultiVendorX()->show_advanced_log ) {
			MultiVendorX()->util->log( 'Database operation failed', 'ERROR' );
		}

		return $store ? $store : array();
	}

	/**
	 * Get store tabs
	 *
	 * @param int $store_id Store ID.
	 * @return array
	 */
    public function get_store_tabs( $store_id ) {

        $tabs = array(
            'products' => array(
                'title' => __( 'Products', 'multivendorx' ),
                'url'   => $this->get_store_url( $store_id ),
            ),
        );

        return apply_filters( 'multivendorx_store_tabs', $tabs, $store_id );
    }

    /**
     * Get store URL
     *
     * @param int    $store_id Store ID.
     * @param string $tab Optional tab.
     * @param bool   $placeholder_string Whether to return a placeholder URL.
     * @return string
     */
    public function get_store_url( $store_id = null, $tab = '', $placeholder_string = false ) {

        $store_base = MultiVendorX()->setting->get_setting( 'store_url', 'store' );
        $store_slug = '';

        if ( $store_id ) {
            $store_data = new Store( $store_id );
            $store_slug = $store_data->exists() ? sanitize_title( $store_data->get( 'slug' ) ) : '';
        }

        if ( ! $store_id && ! $placeholder_string ) {
            return '';
        }

        // Pretty permalink enabled.
        if ( get_option( Utill::WORDPRESS_SETTINGS['permalink'] ) ) {
            $path = '/' . $store_base . '/';

            if ( $store_slug ) {
                $path .= $store_slug . '/';
            }

            if ( $tab && $store_slug ) {
                $tab   = untrailingslashit( trim( $tab, " \n\r\t\v\0/\\" ) );
                $path .= $tab . '/';
            }

            $url = home_url( $path );
        } else {
            $url = home_url( '/?' . $store_base . '=' );

            if ( $store_slug ) {
                $url .= $store_slug;
            }

            if ( $tab && $store_slug ) {
                $tab  = untrailingslashit( trim( $tab, " \n\r\t\v\0/\\" ) );
                $url .= '&tab=' . $tab;
            }
        }

        return apply_filters(
            'multivendorx_get_store_url',
            $url,
            $store_base,
            $store_id,
            $tab
        );
    }

    /**
     * Get store capability
     *
     * @return array
     */
    public static function get_store_capability() {
        $capabilities = array(
            'products'      => array(
                'label'      => 'Manage products',
                'desc'       => 'Allow stores to create, edit, and control their product listings, including uploading media and publishing items for sale.',
                'capability' => array(
                    'read_products'           => 'View products',
                    'add_products'            => 'Add products',
                    'publish_products'        => 'Publish products',
                    'edit_products'           => 'Edit products',
                    'edit_published_products' => 'Edit published products',
                    'edit_approved_products'  => 'Review edits on approved products',
                    'upload_files'            => 'Upload files',
                ),
            ),
            'orders'        => array(
                'label'      => 'Manage orders',
                'desc'       => 'Define how stores interact with customer orders, from viewing and updating details to adding order notes or processing cancellations.',
                'capability' => array(
                    'view_shop_orders'     => 'View orders',
                    'edit_shop_orders'     => 'Edit orders',
                    'delete_shop_orders'   => 'Delete orders',
                    'add_shop_orders_note' => 'Add order notes',
                ),
            ),
            'coupons'       => array(
                'label'      => 'Coupon management',
                'desc'       => 'Enable stores to create and manage discount codes, adjust coupon settings, and track active promotions.',
                'capability' => array(
                    'add_shop_coupons'  => 'Add coupons',
                    'read_shop_coupons' => 'View coupons',
                    'edit_shop_coupons' => 'Edit coupons',
                    'publish_coupons'   => 'Publish coupons',
                ),
            ),
            'analytics'     => array(
                'label'      => 'Analytics & report',
                'desc'       => 'Give stores access to performance insights, sales data editing, and export options for business tracking and analysis.',
                'capability' => array(
                    'read_shop_report'   => 'View reports',
                    'export_shop_report' => 'Export data',
                ),
            ),
            'inventory'     => array(
                'label'      => 'Inventory management',
                'desc'       => 'Let stores monitor stock levels, update quantities, and set alerts to prevent overselling or stockouts.',
                'capability' => array(
                    'read_inventory'    => 'View inventory',
                    'edit_inventory'    => 'Track stock',
                    'edit_stock_alerts' => 'Set stock alerts',
                ),
            ),
            'commission'    => array(
                'label'      => 'Commission & earning',
                'desc'       => 'Provide stores with tools to review their earnings, track commission history, and request withdrawals when eligible.',
                'capability' => array(
                    'read_shop_earning'       => 'View earning',
                    'edit_withdrawl_request'  => 'Request withdrawl',
                    'view_commission_history' => 'Commission history',
                    'view_transactions'       => 'View Transactions',
                ),
            ),
            'store_support' => array(
                'label'      => 'Store support & engagement',
                'desc'       => 'Manage customer communication, questions, followers, and store feedback.',
                'capability' => array(
                    'view_support_tickets'     => 'View support tickets',
                    'reply_support_tickets'    => 'Reply to support tickets',
                    'view_customer_questions'  => 'View customer questions',
                    'reply_customer_questions' => 'Reply to customer questions',
                    'view_store_followers'     => 'View store followers',
                    'view_store_reviews'       => 'View store reviews',
                    'reply_store_reviews'      => 'Reply to store reviews',
                ),
            ),
            'resources'     => array(
                'label'      => 'Learning & tools',
                'desc'       => 'Access documentation, guides, and helpful tools for store growth.',
                'capability' => array(
                    'view_documentation' => 'View documentation',
                    'access_tools'       => 'Access tools',
                ),
            ),
            'settings'      => array(
                'label'      => 'Store settings',
                'desc'       => 'Control store configuration, preferences, and operational settings.',
                'capability' => array(
                    'manage_store_settings' => 'Manage store settings',
                ),
            ),

        );

        /**
         * Filter store capabilities.
         *
         * Allows developers to add, modify, or remove store capabilities.
         *
         * @param array $capabilities
         */
        return apply_filters( 'multivendorx_store_capabilities', $capabilities );
    }

    /**
     * Get store registration form data.
     *
     * @param int $store_id Store ID.
     *
     * @return array
     */
    public static function get_store_registration_form( $store_id ) {
        $store = Store::get_store( $store_id );

        // Get core fields from store object.
        $core_fields = array(
            'name'        => 'Store Name',
            'description' => 'Store Description',
            'status'      => 'status',
        );

        $core_data             = array();
        $all_registration_data = array();
        foreach ( $core_fields as $field_key => $field_label ) {
            $core_data[ $field_label ]           = $store->get( $field_key );
            $all_registration_data[ $field_key ] = $store->get( $field_key );
        }

        $all_registration_data['id'] = $store_id;

        // Get registration form data (serialized meta).
		$store_meta     = $store->get_meta( Utill::STORE_SETTINGS_KEYS['registration_data'] );
		$submitted_data = ! empty( $store_meta ) ? maybe_unserialize( $store_meta ) : array();

        $meta_keys = array(
            Utill::STORE_SETTINGS_KEYS['phone'],
            Utill::STORE_SETTINGS_KEYS['paypal_email'],
            Utill::STORE_SETTINGS_KEYS['address_1'],
            Utill::STORE_SETTINGS_KEYS['address_2'],
            Utill::STORE_SETTINGS_KEYS['city'],
            Utill::STORE_SETTINGS_KEYS['state'],
            Utill::STORE_SETTINGS_KEYS['country'],
            Utill::STORE_SETTINGS_KEYS['postcode'],
        );

        foreach ( $meta_keys as $key ) {
            $meta_value = $store->get_meta( $key );
            if ( ! empty( $meta_value ) ) {
                $submitted_data[ $key ] = $meta_value;
            }
        }

        // Fetch form settings.
        $form_settings = MultiVendorX()->setting->get_option(
            'multivendorx_store_registration_form_settings',
            array()
        );

        // Build map: field_name => field_label.
        $name_label_map   = array();
        $option_label_map = array();
        if ( isset( $form_settings['store_registration_from']['formfieldlist'] ) ) {
            foreach ( $form_settings['store_registration_from']['formfieldlist'] as $field ) {
                if ( ! empty( $field['name'] ) && ! empty( $field['label'] ) ) {
                    $name_label_map[ $field['name'] ] = $field['label'];
                }
                if ( ! empty( $field['options'] ) ) {
                    foreach ( $field['options'] as $key => $options ) {
                        $option_label_map[ $options['value'] ] = $options['label'];
                    }
                }
            }
        }

        $primary_owner_id   = self::get_primary_owner( $store_id );
        $primary_owner_info = get_userdata( $primary_owner_id );

        // Prepare structured response.
        $response = array(
            'core_data'              => $core_data,
            'registration_data'      => array(),
            'all_registration_data'  => $all_registration_data,
            'primary_owner_info'     => $primary_owner_info,
            'store_application_note' => $store->get_meta( 'store_reject_note' ),
			'store_permanent_reject' => 'permanently_rejected' === $store->get( Utill::STORE_SETTINGS_KEYS['status'] ),
        );

        $registration_meta_map = array(
            'store-phone'  => Utill::STORE_SETTINGS_KEYS['phone'],
            'store-paypal' => Utill::STORE_SETTINGS_KEYS['paypal_email'],
        );
        $reverse_map           = array_flip( $registration_meta_map );

        foreach ( $submitted_data as $field_name => $field_value ) {
            if ( isset( $reverse_map[ $field_name ] ) ) {
                $field_name = $reverse_map[ $field_name ];
            }

            $label = $name_label_map[ $field_name ] ?? $field_name;
            $value = is_array( $field_value )
                ? implode(
                    ', ',
                    array_map(
                        fn( $val ) => $option_label_map[ $val ] ?? $val,
                        $field_value
                    )
                )
                : ( $option_label_map[ $field_value ] ?? $field_value );

            if ( strpos( $field_name, 'attachment' ) !== false ) {
                $attachment_id   = absint( $field_value );
                $attachment_type = get_post_mime_type( $attachment_id );
                $value           = array(
                    'attachment_type' => $attachment_type,
                    'attachment'      => wp_get_attachment_url( $attachment_id ),
                );
            }

            $response['all_registration_data'][ $field_name ] = $field_value;
            if ( in_array( $field_name, $meta_keys, true ) ) {
                $response['core_data'][ $label ] = $field_value;
            } else {
                $response['registration_data'][ $label ] = $value;
            }
        }

        return $response;
    }

	/**
	 * Create attachment from array of files.
	 *
	 * @param mixed $files_array The files array from $_FILES.
	 * @return int|\WP_Error
	 */
	public static function create_attachment_from_files_array( $files_array ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		// Handle the file upload.
		$upload = wp_handle_upload( $files_array, array( 'test_form' => false ) );

		// Prepare the attachment.
		$file_path = $upload['file'];
		$file_name = basename( $file_path );
		$file_type = wp_check_filetype( $file_name, null );

		// Create attachment post.
		$attachment = array(
			'guid'           => $upload['url'],
			'post_mime_type' => $file_type['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', $file_name ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		// Insert attachment into the media library.
		$attachment_id = wp_insert_attachment( $attachment, $file_path );

		if ( ! is_wp_error( $attachment_id ) ) {
			// Generate metadata for the attachment, and update the attachment.
			$attachment_data = wp_generate_attachment_metadata( $attachment_id, $file_path );
			wp_update_attachment_metadata( $attachment_id, $attachment_data );

			return $attachment_id; // Return the attachment ID.
		}

		return 0;
	}

	/**
	 * Get primary owner for a store.
	 *
	 * @param int $store_id Store ID.
	 *
	 * @return int User ID.
	 */
	public static function get_primary_owner( $store_id ) {
		global $wpdb;
		$table_name    = $wpdb->prefix . Utill::TABLES['store_users'];
		$primary_owner = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->prepare(
                "SELECT primary_owner FROM $table_name WHERE store_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $store_id
            )
		);

		if ( ! empty( $wpdb->last_error ) && MultiVendorX()->show_advanced_log ) {
			MultiVendorX()->util->log( 'Database operation failed', 'ERROR' );
		}

		return $primary_owner;
	}


	/**
	 * Whether the current user may read/manage a specific store's data.
	 *
	 * True for site administrators, or for a user who is the store's primary
	 * owner or a listed staff member. Used to scope REST access to a single
	 * store's records rather than trusting a bare `edit_stores`/`manage_options`
	 * capability check, since `edit_stores` is also granted to the `store_owner`
	 * role itself.
	 *
	 * @param int $store_id Store ID.
	 * @return bool
	 */
	public static function current_user_can_manage_store( $store_id ) {
		if ( Utill::current_user_has_capability( array( 'manage_options' ) ) ) {
			return true;
		}

		$store_id = (int) $store_id;
		if ( ! $store_id ) {
			return false;
		}

		$user_id = MultiVendorX()->current_user_id;
		if ( ! $user_id ) {
			return false;
		}

		if ( (int) self::get_primary_owner( $store_id ) === $user_id ) {
			return true;
		}

		$store_users = self::get_store_users( $store_id );
		return in_array( $user_id, (array) $store_users['users'], true );
	}

	/**
	 * Set primary owner for a store.
	 *
	 * @param int $user_id User ID.
	 * @param int $store_id Store ID.
	 *
	 * @return void
	 */
	public static function set_primary_owner( $user_id, $store_id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . Utill::TABLES['store_users'];

		// Check if store_id already exists.
		$exists = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->prepare(
                "SELECT ID FROM $table_name WHERE store_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $store_id
            )
		);

		if ( $exists ) {
			// Update.
			$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $table_name,
                array( 'primary_owner' => $user_id ),
                array( 'store_id' => $store_id ),
                array( '%d' ),
                array( '%d' )
			);
		} else {
			// Insert.
			$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
                $table_name,
                array(
					'store_id'      => $store_id,
					'user_id'       => $user_id,
					'role_id'       => 'store_owner',
					'primary_owner' => $user_id,
                ),
                array( '%d', '%d', '%s', '%d' )
			);
		}

		if ( ! empty( $wpdb->last_error ) && MultiVendorX()->show_advanced_log ) {
			MultiVendorX()->util->log( 'Database operation failed', 'ERROR' );
		}
	}

	/**
	 * Get store records from the database based on given filters.
	 *
	 * @param array $args Filter options like 'ID', 'status', 'name', 'slug', 'description', 'who_created', 'create_time', 'limit', 'offset', 'count'.
	 * @return array|int List of stores (array) or count (int) if 'count' is set.
	 */
	public static function get_store_information( $args = array() ) {
		global $wpdb;

		$where = array();

		if ( isset( $args['ID'] ) ) {
			$ids     = is_array( $args['ID'] ) ? $args['ID'] : array( $args['ID'] );
			$ids     = implode( ',', array_map( 'intval', $ids ) );
			$where[] = "ID IN ($ids)";
		}

        if ( isset( $args['exclude_ids'] ) ) {
            $ids     = is_array( $args['exclude_ids'] ) ? $args['exclude_ids'] : array( $args['exclude_ids'] );
            $ids     = implode( ',', array_map( 'intval', $ids ) );
            $where[] = "ID NOT IN ($ids)";
        }

		if ( isset( $args['status'] ) ) {
			$where[] = "status = '" . esc_sql( $args['status'] ) . "'";
		}

		if ( isset( $args['name'] ) ) {
			$where[] = "name LIKE '%" . esc_sql( $args['name'] ) . "%'";
		}

		if ( isset( $args['slug'] ) ) {
			$where[] = "slug = '" . esc_sql( $args['slug'] ) . "'";
		}

		if ( isset( $args['searchField'] ) ) {
			$search  = esc_sql( $args['searchField'] );
			$where[] = "(name LIKE '%$search%')";
		}

		if ( isset( $args['start_date'] ) && isset( $args['end_date'] ) ) {
			$where[] = "create_time BETWEEN '" . esc_sql( $args['start_date'] ) . "' AND '" . esc_sql( $args['end_date'] ) . "'";
		}

		$table = $wpdb->prefix . Utill::TABLES['store'];

		if ( isset( $args['count'] ) ) {
			$query = "SELECT COUNT(*) FROM {$table}";
		} else {
			$query = "SELECT * FROM {$table}";
		}

		if ( ! empty( $where ) ) {
			$condition = $args['condition'] ?? ' AND ';
			$query    .= ' WHERE ' . implode( $condition, $where );
		}

		// ADD SORTING SUPPORT HERE.
		if ( ! empty( $args['order_by'] ) ) {
			// Only allow safe columns to sort by (avoid SQL injection).
			$allowed_columns = array( 'ID', 'name', 'status', 'slug', 'create_time' );
			$order_by        = in_array( $args['order_by'], $allowed_columns, true ) ? $args['order_by'] : 'ID';
			$order           = ( isset( $args['order'] ) && strtolower( $args['order'] ) === 'desc' ) ? 'DESC' : 'ASC';
			$query          .= " ORDER BY {$order_by} {$order}";
		}

		// Keep your pagination logic.
		if ( isset( $args['limit'] ) && isset( $args['offset'] ) && empty( $args['count'] ) ) {
			$limit  = intval( $args['limit'] );
			$offset = intval( $args['offset'] );
			$query .= " LIMIT $limit OFFSET $offset";
		}

		if ( isset( $args['count'] ) ) {
			$results = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
		} else {
			$results = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
		}

		/** Centralized error logging (only once) */
		if ( ! empty( $wpdb->last_error ) && MultiVendorX()->show_advanced_log ) {
			MultiVendorX()->util->log( 'Database operation failed', 'ERROR' );
		}

		return $results ?? ( isset( $args['count'] ) ? 0 : array() );
	}

    /**
     * Get all product IDs that should be excluded based on conditions.
     *
     * @param int  $product_id Product ID.
     * @param int  $store_id Store ID.
     * @param bool $check_payouts Whether to check for payouts.
     * @return boolean
     */
    public static function get_excluded_products( $product_id = '', $store_id = '', $check_payouts = false ) {

        $store_id = ! empty( $store_id ) ? $store_id : get_post_meta( $product_id, Utill::POST_META_SETTINGS['store_id'], true );

        if ( ! $store_id ) {
            return apply_filters( 'multivendorx_get_excluded_products', false, $product_id );
        }
        $store = Store::get_store( $store_id );
        if ( empty( $store ) ) {
            return false;
        }
        $status      = $store->get( 'status' );
        $permissions = MultiVendorX()->util->get_permissions();
        if ( empty( $permissions ) ) {
            return false;
        }
        if ( $check_payouts ) {
            if ( $permissions['disable_payouts'] && ( in_array( $status, array( 'suspended', 'under_review' ), true ) || $permissions['hide_for_compliance'] ) ) {
                return true;
            }
        } else {
            if ( $permissions['disable_product_upload'] && ( 'under_review' === $status || $permissions['hide_for_compliance'] ) ) {
                return true;
            }

            if ( $permissions['hide_store_products'] && ( in_array( $status, array( 'suspended', 'under_review' ), true ) || $permissions['hide_for_compliance'] ) ) {
                return true;
            }

            if ( 'suspended' === $status && $permissions['disable_checkout'] ) {
                return true;
            }
        }

        return apply_filters( 'multivendorx_get_excluded_products', false, $product_id, $store_id );
    }

    /**
     * Get store visitors statistics based on date range.
     *
     * @param int   $store_id Store ID.
     * @param array $args     Arguments (start_date, end_date).
     * @return array
     */
    public static function get_store_visitors( $store_id, $args = array() ) {
        global $wpdb;

        $table_name = $wpdb->prefix . Utill::TABLES['visitors_stats'];

        $start_date = ! empty( $args['start_date'] ) ? $args['start_date'] : null;
        $end_date   = ! empty( $args['end_date'] ) ? $args['end_date'] : null;

        $query = "
            SELECT COUNT(DISTINCT user_id) as total
            FROM {$table_name}
            WHERE store_id = %d
        ";

        $params = array( $store_id );

        if ( $start_date && $end_date ) {
            $query   .= ' AND created BETWEEN %s AND %s';
            $params[] = $start_date;
            $params[] = $end_date;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
        $result = $wpdb->get_var( $wpdb->prepare( $query, $params ) );

        return (int) $result;
    }

	/**
	 * Get formatted phone number from phone meta data.
	 *
	 * @param mixed $phone_meta Phone meta data (could be serialized or array).
	 * @return string Formatted phone number with country code.
	 */
	public static function get_phone( $phone_meta ) {
		$data = maybe_unserialize( $phone_meta );

		$country = $data['country_code'] ?? '';
		$phone   = $data['phone'] ?? $data['whatsapp_number'] ?? '';

		return $country . ' ' . $phone;
	}

	/**
	 * Get specific store information for the current store.
	 *
	 * Retrieves store details including name, description, contact info, social media links,
	 * policies, and store tabs HTML for the store based on the URL slug.
	 *
	 * @return array|null Array of store information or null if store not found.
	 */
	public static function get_specific_store_info() {
		$store_slug = get_query_var( MultiVendorX()->setting->get_setting( 'store_url', 'store' ) );
		if ( empty( $store_slug ) ) {
			return;
		}
		$store_obj = Store::get_store( $store_slug, 'slug' );
        if ( ! $store_obj ) {
            return;
        }
        $all_store_meta = $store_obj->get_all_meta();
		$store_phone    = self::get_phone( $store_obj->get_meta( 'phone' ) );
        $store_whatsapp = self::get_phone( $store_obj->get_meta( 'whatsapp_number' ) );

		ob_start();
		MultiVendorX()->util->get_template( 'store/store-tabs.php', array( 'store_id' => $store_obj->get_id() ) );
		$tabs_html = ob_get_clean();

		$info = array(
			'storeName'          => $store_obj->get( 'name' ),
			'storeDescription'   => $store_obj->get( 'description' ),
			'storeSlug'          => $store_slug,
			'storeId'            => $store_obj->get_id(),
			'storeEmail'         => $all_store_meta['store_email']['primary'] ?? '',
			'storePhone'         => $store_phone,
			'facebook'           => $all_store_meta['facebook'] ?? '',
			'twitter'            => $all_store_meta['twitter'] ?? '',
			'linkedin'           => $all_store_meta['linkedin'] ?? '',
			'youtube'            => $all_store_meta['youtube'] ?? '',
			'instagram'          => $all_store_meta['instagram'] ?? '',
			'pinterest'          => $all_store_meta['pinterest'] ?? '',
			'storeLogo'          => $all_store_meta['image'] ?? '',
			'storeBanner'        => $all_store_meta['banner'] ?? '',
			'storePolicy'        => $all_store_meta['store_policy'] ?? '',
			'shippingPolicy'     => $all_store_meta['shipping_policy'] ?? '',
			'refundPolicy'       => $all_store_meta['refund_policy'] ?? '',
			'cancellationPolicy' => $all_store_meta['cancellation_policy'] ?? '',
			'storeAddress'       => $all_store_meta['address'] ?? '',
			'storeTabs'          => $tabs_html,
            'whatsapp'           => $store_whatsapp,
            'whatsapp_message'   => $all_store_meta['whatsapp_pre_filled'] ?? '',
            'page_id'            => $all_store_meta['page_id'] ?? '',
		);
		/**
		 * Filter store info before returning.
		 *
		 * @param array $info
		 * @param object $store_obj
		 */
		return apply_filters( 'multivendorx_store_info', $info, $store_obj );
	}

    /**
     * Get store IDs within a given radius from a location.
     *
     * Uses the Haversine formula to calculate distance between
     * the provided latitude/longitude and store coordinates
     * stored in store meta.
     *
     * @param float  $lat    Latitude of the search origin.
     * @param float  $lng    Longitude of the search origin.
     * @param float  $radius Search radius distance.
     * @param string $unit   Distance unit. Accepts 'kilometers' or 'miles'.
     *
     * @return array List of store IDs within the radius.
     */
    public static function get_stores_by_radius( $lat, $lng, $radius, $unit = 'kilometers' ) {
        global $wpdb;

        $store_table = $wpdb->prefix . Utill::TABLES['store'];
        $meta_table  = $wpdb->prefix . Utill::TABLES['store_meta'];

        // Determine earth radius based on unit.
        $earth_radius = ( 'miles' === $unit ) ? 3959 : 6371;

        $lat_key = Utill::STORE_SETTINGS_KEYS['location_lat'];
        $lng_key = Utill::STORE_SETTINGS_KEYS['location_lng'];

        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $sql = $wpdb->prepare(
            "
            SELECT s.ID,
            (
                %f * ACOS(
                    COS( RADIANS( %f ) ) *
                    COS( RADIANS( lat.meta_value ) ) *
                    COS( RADIANS( lng.meta_value ) - RADIANS( %f ) ) +
                    SIN( RADIANS( %f ) ) *
                    SIN( RADIANS( lat.meta_value ) )
                )
            ) AS distance
            FROM {$store_table} s
            INNER JOIN {$meta_table} lat
                ON s.ID = lat.store_id
                AND lat.meta_key = %s
                AND lat.meta_value != ''
            INNER JOIN {$meta_table} lng
                ON s.ID = lng.store_id
                AND lng.meta_key = %s
                AND lng.meta_value != ''
            WHERE s.status = 'active'
            HAVING distance <= %f
            ORDER BY distance ASC
            ",
            $earth_radius,
            $lat,
            $lng,
            $lat,
            $lat_key,
            $lng_key,
            $radius
        );
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
        return $wpdb->get_col( $sql );
    }

    public static function reassign_attachments_to_new_owner( $old_owner, $new_owner ) {
        if ( ! $old_owner || ! $new_owner || $old_owner == $new_owner ) {
            return;
        }

        $attachments = get_posts(
            array(
				'post_type'      => 'attachment',
				'posts_per_page' => -1,
				'author'         => $old_owner,
				'fields'         => 'ids',
            )
        );

        if ( empty( $attachments ) ) {
            return;
        }

        foreach ( $attachments as $attachment_id ) {
            wp_update_post(
                array(
					'ID'          => $attachment_id,
					'post_author' => $new_owner,
                )
            );
        }
    }

    public static function get_approval_queue_count() {
        $pending_stores   = (int) self::get_store_information(
            array(
				'count'  => true,
				'status' => 'pending',
            )
        );
        $product_query    = wc_get_products(
            array(
				'status'     => 'pending',
				'limit'      => 1,
				'paginate'   => true,
				'meta_query' => array(
					array(
						'key'     => 'multivendorx_store_id',
						'compare' => 'EXISTS',
					),
				),
            )
        );
        $pending_products = ! empty( $product_query->total ) ? (int) $product_query->total : 0;

        $query = new \WP_Query(
            array(
				'post_type'      => 'shop_coupon',
				'post_status'    => 'pending',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'     => 'multivendorx_store_id',
						'compare' => 'EXISTS',
					),
				),
            )
        );

        $pending_coupons = (int) $query->found_posts;

        $all_stores               = self::get_store_information();
        $pending_withdrawal_count = $pending_deactivation_request_count = 0;

        foreach ( $all_stores as $store ) {
            $store_meta = Store::get_store( (int) $store['ID'] );

            if ( ! empty( $store_meta->meta_data[ Utill::STORE_SETTINGS_KEYS['request_withdrawal_amount'] ] ) ) {
                ++$pending_withdrawal_count;
            }

            if ( ! empty( $store_meta->meta_data[ Utill::STORE_SETTINGS_KEYS['deactivation_reason'] ] ) ) {
                ++$pending_deactivation_request_count;
            }
        }

        $total =
            $pending_stores +
            $pending_products +
            $pending_coupons +
            $pending_withdrawal_count +
            $pending_deactivation_request_count;

        return apply_filters(
            'multivendorx_approval_queue_count',
            $total
        );
    }

    public static function get_compliance_tab_count() {
        $total = 0;
        return apply_filters(
            'multivendorx_compliance_count',
            $total
        );
    }

    public static function get_customer_tab_count() {
        $total = 0;
        return apply_filters(
            'multivendorx_customer_tab_count',
            $total
        );
    }

    /**
     * Get store IDs by meta key and/or value.
     *
     * @param string $meta_key   Meta key (required).
     * @param mixed  $meta_value Meta value (optional).
     * @param bool   $like       Whether to use LIKE for meta_value. Default false.
     *
     * @return int[] List of store IDs.
     */
    public static function get_store_by_meta( string $meta_key, $meta_value = null, bool $like = false ): array {
        if ( empty( $meta_key ) ) {
            return array();
        }

        global $wpdb;
        $table = $wpdb->prefix . Utill::TABLES['store_meta'];

        // Start building the query
        $conditions = array( 'meta_key = %s' );
        $params     = array( $meta_key );

        // Handle meta_value if provided
        if ( null !== $meta_value ) {
            // Serialize non-scalar values (arrays/objects)
            if ( ! is_scalar( $meta_value ) ) {
                $meta_value = maybe_serialize( $meta_value );
            }

            if ( $like ) {
                $conditions[] = 'meta_value LIKE %s';
                $params[]     = '%' . $wpdb->esc_like( (string) $meta_value ) . '%';
            } else {
                $conditions[] = 'meta_value = %s';
                $params[]     = $meta_value;
            }
        }

        // Join conditions with AND
        $where_clause = implode( ' AND ', $conditions );

        // Direct query execution
        $sql     = "SELECT DISTINCT store_id FROM {$table} WHERE {$where_clause}";
        $results = $wpdb->get_col( $wpdb->prepare( $sql, $params ) );

        return $results ? array_map( 'intval', $results ) : array();
    }
}
