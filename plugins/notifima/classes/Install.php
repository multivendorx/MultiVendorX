<?php
/**
 * Install class file.
 *
 * @package Notifima
 */

namespace Notifima;

defined( 'ABSPATH' ) || exit;

/**
 * Notifima Install class
 *
 * @class       Install class
 * @version     3.0.0
 * @author      MultiVendorX
 */
class Install {

    /**
     * Map old status of post table to new status of subscriber table
     *
     * @var array
     */
    public const STATUS_MAP = array(
        'woo_subscribed'   => 'subscribed',
        'woo_unsubscribed' => 'unsubscribed',
        'woo_mailsent'     => 'mailsent',
    );

    /**
     * Used for check migration is running or not.
     *
     * @var bool | null
     */
    public static $migration_running = null;

    /**
     * Class constructor
     */
    public function __construct() {
		// phpcs:ignore WordPress.WP.CronInterval.ChangeDetected, WordPress.WP.CronInterval.CronSchedulesInterval -- 10-minute interval is intentional; the stock-notification cron batches subscribers and needs to run more often than the default schedules allow.
        add_filter( 'cron_schedules', array( $this, 'register_custom_schedule' ) );
        add_action( 'init', array( $this, 'run_migration' ) );
    }

    /**
     * Run first-time install or version-gated migration on plugin init.
     *
     * @return void
     */
    public function run_migration() {
        $previous_version = get_option( 'notifima_version', false );
        if ( ! $previous_version ) {
            $this->create_database_table();
            $this->set_default_settings();
            $this->old_migration();
        } else {
            $this->do_migration( $previous_version );
        }
        $this->start_cron_job();

        update_option( 'notifima_version', NOTIFIMA_PLUGIN_VERSION );

        do_action( 'notifima_updated' );
    }

    /**
     * Handles old data migration for plugin upgrade.
     */
    public static function old_migration() {
        global $wpdb;

        self::migration_from_old_to_new_3_0_0();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . 'stockalert_subscribers' ) ) ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
            $wpdb->query(
                "ALTER TABLE `{$wpdb->prefix}stockalert_subscribers` RENAME TO `{$wpdb->prefix}notifima_subscribers`"
            );
        } else {
            self::create_database_table();
            // Migrate subscriber data from post table.
            wp_clear_scheduled_hook( 'notifima_start_subscriber_migration' );
            wp_schedule_single_event( time(), 'notifima_start_subscriber_migration' );
            update_option( 'notifima_migration_running', true );

            // If corn is disabled.
            if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
                self::subscriber_migration();
            }
        }
    }

    /**
     * Runs the database migration process.
     *
     * @param string $previous_version The previously installed plugin version.
     */
    public static function do_migration( $previous_version ) {

        // write migration code from 3.0.1.
        if ( version_compare( $previous_version, '3.1.0', '<' ) ) {
            $appearance_settings = get_option( 'notifima_appearance_settings', array() );

            $appearance_settings['is_double_optin']               = ! empty( $appearance_settings['is_double_optin'] ) ? 'confirm_via_email' : 'subscribe_immediately';
            $appearance_settings['is_enable_no_interest']         = ! empty( $appearance_settings['is_enable_no_interest'] ) ? 'show_count' : 'hide_count';
            $appearance_settings['is_enable_backorders']          = ! empty( $appearance_settings['is_enable_backorders'] ) ? 'out_of_stock_and_backorder' : 'out_of_stock';
            $appearance_settings['is_guest_subscriptions_enable'] = ! empty( $appearance_settings['is_guest_subscriptions_enable'] ) ? 'logged_in' : 'everyone';
            $appearance_settings['display_subscription_form_as']  = 'inline';

            $mailchimp_settings = get_option( 'notifima_mailchimp_settings', array() );
            if ( ! is_array( $mailchimp_settings ) ) {
                $mailchimp_settings = array();
            }

            update_option( Utill::NOTIFIMA_SETTINGS['automation'], array_merge( $appearance_settings, $mailchimp_settings ) );

            $automation_settings = get_option( Utill::NOTIFIMA_SETTINGS['automation'], array() );

            $automation_settings['is_mailchimp_enable'] = ! empty( $automation_settings['is_mailchimp_enable'] ) ? 'mailchimp' : 'store_only';

            $automation_settings['mailchimp'] = array(
                'options'  => $automation_settings['mailchimp_list_options'] ?? array(),
                'selected' => $automation_settings['selected_mailchimp_list'] ?? '',
            );

            update_option( Utill::NOTIFIMA_SETTINGS['automation'], $automation_settings );

            delete_option( 'notifima_mailchimp_settings' );

            $email_settings = get_option( 'notifima_email_settings', array() );

            $email_settings['additional_alert_email'] = ! empty( $appearance_settings['additional_alert_email'] ) ? $appearance_settings['additional_alert_email'] : '';

            update_option( Utill::NOTIFIMA_SETTINGS['notifications'], $email_settings );

            $customer_messages_settings = get_option( 'notifima_form_submission_settings', array() );

            update_option( Utill::NOTIFIMA_SETTINGS['customer-messages'], $customer_messages_settings );

            $registration_form = array(
                array(
                    'id'          => 1,
                    'type'        => 'email',
                    'label'       => Notifima()->default_value['alert_text'],
                    'required'    => false,
                    'name'        => 'email',
                    'placeholder' => Notifima()->default_value['email_placeholder_text'],
                    'readonly'    => true,
                ),
                array(
                    'id'    => 2,
                    'type'  => 'button',
                    'label' => Notifima()->default_value['subscribe_button_text'],
                    'text'  => Notifima()->default_value['subscribe_button_text'],
                    'name'  => 'submit',
                ),
            );

            $registration_from_settings['form_tabs'] = array(
                'personalize_layout_template' => array(
                    'formfieldlist' => $registration_form,
                ),
            );
            update_option( Utill::NOTIFIMA_SETTINGS['subscription-form-designer'], $registration_from_settings );
        }
    }

    /**
     * If migration is running it return true, otherwise false.
     *
     * @return mixed
     */
    public static function is_migration_running() {
        if ( null === self::$migration_running ) {
            self::$migration_running = get_option( 'notifima_migration_running', false );
        }

        return self::$migration_running;
    }

    /**
     * Migrate subscriber from post table to subscribe migration.
     *
     * @return void
     */
    public static function subscriber_migration() {
        global $wpdb;

        try {
            // Get woosubscribe post and post meta.
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.SlowDBQuery.slow_db_query_meta_key
            $legacy_subscriber_rows = $wpdb->get_results(
                "SELECT posts.ID as id,
                    posts.post_date as date,
                    posts.post_title as email,
                    posts.post_status as status,
                    posts.post_author as user_id,
                    pm.meta_value as product_id
                FROM {$wpdb->prefix}posts as posts, {$wpdb->prefix}postmeta as pm
                WHERE posts.post_type = 'woostockalert'
                AND pm.post_id = posts.ID
                AND pm.meta_key = 'wooinstock_product_id'
                ",
                ARRAY_A
            );

            // Prepare insert placeholders and values (kept separate so every value is passed through $wpdb->prepare()).
            $row_placeholders = array();
            $row_values       = array();

            foreach ( $legacy_subscriber_rows as $legacy_subscriber_row ) {
                $row_placeholders[] = '( %d, %d, %s, %s, %s )';
                $row_values[]       = $legacy_subscriber_row['product_id'];
                $row_values[]       = $legacy_subscriber_row['user_id'];
                $row_values[]       = $legacy_subscriber_row['email'];
                $row_values[]       = self::STATUS_MAP[ $legacy_subscriber_row['status'] ];
                $row_values[]       = $legacy_subscriber_row['date'];
            }

            // If result exist then insert those result into custom table.
            if ( $row_values ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $wpdb->query(
                    $wpdb->prepare(
                        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- placeholder count is built dynamically (5 per row) and always matches $row_values; phpcs can't verify that statically.
                        "INSERT IGNORE INTO {$wpdb->prefix}notifima_subscribers (product_id, user_id, email, status, create_time ) VALUES " . implode( ', ', $row_placeholders ),
                        $row_values
                    )
                );
            }

            // Delete the post seperatly, If there is problem in migration post will not delete permanently.
            foreach ( $legacy_subscriber_rows as $legacy_subscriber_row ) {
                wp_delete_post( $legacy_subscriber_row['id'] );
            }

            // Get subscriber count.
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $subscriber_counts = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT product_id, COUNT(*) as count from {$wpdb->prefix}notifima_subscribers
                    WHERE status = %s
                    GROUP BY product_id",
                    array( 'subscribed' )
                )
            );

            // Update subscriber count.
            foreach ( $subscriber_counts as $count_data ) {
                update_post_meta( $count_data->product_id, 'no_of_subscribers', $count_data->count );
            }

            delete_option( 'notifima_migration_running' );
            self::$migration_running = null;
        } catch ( \Exception $e ) {
            Utill::log( $e->getMessage() );
        }
    }

    /**
     * Create database table for subscriber.
     *
     * @return void
     */
    private static function create_database_table() {
        global $wpdb;

        $collate = '';

        if ( $wpdb->has_cap( 'collation' ) ) {
            $collate = $wpdb->get_charset_collate();
        }

        $sql_subscribers = 'CREATE TABLE IF NOT EXISTS `' . $wpdb->prefix . "notifima_subscribers` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `product_id` bigint(20) NOT NULL,
                `user_id` bigint(20) NOT NULL DEFAULT 0,
                `email` varchar(50) NOT NULL,
                `status` varchar(20) NOT NULL,
                `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_product_email_status (product_id, email, status),
                PRIMARY KEY (`id`)
            ) $collate;";

        // Include upgrade functions if not loaded.
        if ( ! function_exists( 'dbDelta' ) ) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        dbDelta( $sql_subscribers );
    }

    /**
     * Function that schedules the notification cron job.
     *
     * @return void
     */
    private function start_cron_job() {
        wp_clear_scheduled_hook( 'notifima_start_notification_cron_job' );

        if ( ! wp_next_scheduled( 'notifima_start_notification_cron_job' ) ) {
            wp_schedule_event(
                time(),
                'notifima_ten_minutes',
                'notifima_start_notification_cron_job'
            );
        }

        update_option( 'notifima_cron_start', true );
    }


    /**
     * Set default settings for the plugin or module.
     *
     * @return void
     */
    private function set_default_settings() {

        // Default messages for settings array.
        $automation_settings = array(
            'is_enable_backorders'          => 'out_of_stock',
            'is_enable_no_interest'         => 'hide_count',
            'is_double_optin'               => 'subscribe_immediately',
            'is_remove_admin_email'         => false,
            'double_opt_in_success'         => Notifima()->default_value['double_opt_in_success'],
            'shown_interest_text'           => Notifima()->default_value['shown_interest_text'],
            'is_guest_subscriptions_enable' => 'logged_in',
            'lead_time_format'              => 'static',
            'display_subscription_form_as'  => 'inline',
            // Form customization settings.
            'email_placeholder_text'        => Notifima()->default_value['email_placeholder_text'],
            'alert_text'                    => Notifima()->default_value['alert_text'],
            'unsubscribe_button_text'       => Notifima()->default_value['unsubscribe_button_text'],
        );

        update_option( Utill::NOTIFIMA_SETTINGS['automation'], $automation_settings );

        $customer_messages_settings = array(
            'alert_success'             => Notifima()->default_value['alert_success'],
            'alert_email_exist'         => Notifima()->default_value['alert_email_exist'],
            'valid_email'               => Notifima()->default_value['valid_email'],
            // Translators: This message display user sucessfully unregistered.
            'alert_unsubscribe_message' => Notifima()->default_value['alert_unsubscribe_message'],
        );

        update_option( Utill::NOTIFIMA_SETTINGS['customer-messages'], $customer_messages_settings );

        $notifications_settings = array(
            'ban_email_domain_text'  => Notifima()->default_value['ban_email_domain_text'],
            'ban_email_address_text' => Notifima()->default_value['ban_email_address_text'],
            'additional_alert_email' => get_option( 'admin_email' ),
        );

        update_option( Utill::NOTIFIMA_SETTINGS['notifications'], $notifications_settings );

        $registration_form = array(
			array(
				'id'          => 1,
				'type'        => 'email',
				'label'       => Notifima()->default_value['alert_text'],
				'required'    => false,
				'name'        => 'email',
				'placeholder' => Notifima()->default_value['email_placeholder_text'],
				'readonly'    => true,
			),
			array(
				'id'    => 2,
				'type'  => 'button',
				'label' => Notifima()->default_value['subscribe_button_text'],
				'text'  => Notifima()->default_value['subscribe_button_text'],
				'name'  => 'submit',
			),
        );

        $registration_from_settings['form_tabs'] = array(
            'personalize_layout_template' => array(
                'formfieldlist' => $registration_form,
            ),
        );
        update_option( Utill::NOTIFIMA_SETTINGS['subscription-form-designer'], $registration_from_settings );
    }

    /**
     * Data migration function. Run on installation time.
     *
     * @return void
     */
    private static function migration_from_old_to_new_3_0_0() {
        global $wpdb;
        $previous_version = get_option( 'woo_stock_manager_version', '' );

        // Default messages for settings array.
        $appearance_settings = get_option( 'notifima_appearance_settings', array() );
        $submit_settings     = get_option( 'notifima_form_submission_settings', array() );
        $email_settings      = get_option( 'notifima_email_settings', array() );

        if ( version_compare( $previous_version, '2.5.0', '<' ) ) {
            // Used to check the plugin version before 2.1.0.
            $dc_was_installed = get_option( 'dc_product_stock_alert_activate' );
            // Used to check the plugin version before 2.3.0.
            $woo_was_installed = get_option( 'woo_product_stock_alert_activate' );

            // Equevelent to check plugin version <= 2.3.0.
            if ( $dc_was_installed || $woo_was_installed ) {
                $product_ids_with_legacy_subscriber_meta = get_posts(
                    array(
						'post_type'   => 'product',
						'post_status' => 'publish',
						'fields'      => 'ids',
                        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						'meta_query'  => array(
							array(
								'key'     => '_product_subscriber',
								'compare' => 'EXISTS',
							),
						),
                    )
                );

                // Database migration for subscriber data before version 2.3.0.
                foreach ( $product_ids_with_legacy_subscriber_meta as $parent_product_id ) {
                    $current_product_ids = Subscriber::get_related_product( wc_get_product( $parent_product_id ) );
                    foreach ( $current_product_ids as $product_id ) {
                        $product_subscribers = get_post_meta( $product_id, '_product_subscriber', true );
                        if ( $product_subscribers && ! empty( $product_subscribers ) ) {
                            foreach ( $product_subscribers as $subscriber_email ) {
                                Subscriber::insert_subscriber( $subscriber_email, $product_id );
                            }
                        }
                        delete_post_meta( $product_id, '_product_subscriber' );
                    }
                }

                // Settings array for version upto 2.0.0.
                $dc_plugin_settings = get_option( 'dc_woo_product_stock_alert_general_settings_name', array() );

                // Settings array for version from 2.1.0 to 2.2.0.
                $mvx_general_tab_settings       = get_option( 'mvx_woo_stock_alert_general_tab_settings', array() );
                $mvx_customization_tab_settings = get_option( 'mvx_woo_stock_alert_form_customization_tab_settings', array() );
                $mvx_submition_tab_settings     = get_option( 'mvx_woo_stock_alert_form_submission_tab_settings', array() );

                if ( $dc_plugin_settings ) {
                    delete_option( 'dc_woo_product_stock_alert_general_settings_name' );
                }
                if ( $mvx_general_tab_settings ) {
                    delete_option( 'mvx_woo_stock_alert_general_tab_settings' );
                }
                if ( $mvx_customization_tab_settings ) {
                    delete_option( 'mvx_woo_stock_alert_form_customization_tab_settings' );
                }
                if ( $mvx_submition_tab_settings ) {
                    delete_option( 'mvx_woo_stock_alert_form_submission_tab_settings' );
                }

                // Settings arrays for version 2.3.0.
                // For version 2.3.0 'woo_product_stock_alert_version' was set.
                $woo_general_tab_settings       = array();
                $woo_customization_tab_settings = array();
                $woo_submition_tab_settings     = array();
                $woo_email_tab_settings         = array();

                if ( get_option( 'woo_product_stock_alert_version' ) ) {
                    delete_option( 'woo_product_stock_alert_version' );
                    $woo_general_tab_settings       = get_option( 'woo_stock_alert_general_tab_settings', array() );
                    $woo_customization_tab_settings = get_option( 'woo_stock_alert_form_customization_tab_settings', array() );
                    $woo_submition_tab_settings     = get_option( 'woo_stock_alert_form_submission_tab_settings', array() );
                    $woo_email_tab_settings         = get_option( 'woo_stock_alert_email_tab_settings', array() );
                    if ( $woo_general_tab_settings ) {
                        delete_option( 'woo_stock_alert_general_tab_settings' );
                    }
                    if ( $woo_customization_tab_settings ) {
                        delete_option( 'woo_stock_alert_form_customization_tab_settings' );
                    }
                    if ( $woo_submition_tab_settings ) {
                        delete_option( 'woo_stock_alert_form_submission_tab_settings' );
                    }
                    if ( $woo_email_tab_settings ) {
                        delete_option( 'woo_stock_alert_email_tab_settings' );
                    }
                }

                // Merge all setting array.
                $tab_settings = array_merge(
                    $dc_plugin_settings,
                    $mvx_general_tab_settings,
                    $mvx_customization_tab_settings,
                    $mvx_submition_tab_settings,
                    $woo_general_tab_settings,
                    $woo_customization_tab_settings,
                    $woo_submition_tab_settings,
                    $woo_email_tab_settings
                );

                // Replace all default value by previous settings.
                foreach ( $appearance_settings as $key => $value ) {
                    if ( isset( $tab_settings[ $key ] ) && '' !== $tab_settings[ $key ] ) {
                        $appearance_settings[ $key ] = $tab_settings[ $key ];
                    }
                }

                foreach ( $submit_settings as $key => $value ) {
                    if ( isset( $tab_settings[ $key ] ) && '' !== $tab_settings[ $key ] ) {
                        $submit_settings[ $key ] = $tab_settings[ $key ];
                    }
                }

                delete_option( 'dc_product_stock_alert_installed' );
                delete_option( 'woo_product_stock_alert_installed' );
                delete_option( 'dc_product_stock_alert_activate' );
                delete_option( 'woo_product_stock_alert_activate' );
            }

            if ( version_compare( $previous_version, '2.4.2', '==' ) ) {
                $appearance_settings = get_option( 'woo_stock_manager_general_tab_settings', null ) ?? $appearance_settings;
                $submit_settings     = get_option( 'woo_stock_manager_form_submission_tab_settings', null ) ?? $submit_settings;
                $email_settings      = get_option( 'woo_stock_manager_email_tab_settings', null ) ?? $email_settings;
            }

            // Get customization_tab_setting and merge with general setting.
            $customization_tab_setting = get_option( 'woo_stock_manager_form_customization_tab_settings', array() );
            $appearance_settings       = array_merge( $appearance_settings, $customization_tab_setting );
            delete_option( 'woo_stock_manager_form_customization_tab_settings' );
        }

        if ( version_compare( $previous_version, '2.5.5', '<=' ) ) {
            $appearance_settings['is_guest_subscriptions_enable'] = 'logged_in';
        }

        if ( version_compare( $previous_version, '2.5.12', '<=' ) ) {
            $appearance_settings['lead_time_format'] = 'static';
        }

        $previous_appearance_settings = get_option( 'woo_stock_manager_appearance_tab_settings', array() );
        $previous_submit_settings     = get_option( 'woo_stock_manager_form_submission_tab_settings', array() );
        $previous_email_settings      = get_option( 'woo_stock_manager_email_tab_settings', array() );

        if ( version_compare( $previous_version, '2.5.14', '<=' ) ) {
            unset(
                $previous_appearance_settings['button_background_color'],
                $previous_appearance_settings['button_text_color'],
                $previous_appearance_settings['button_border_color'],
                $previous_appearance_settings['button_border_size'],
                $previous_appearance_settings['button_border_radious'],
                $previous_appearance_settings['button_font_size'],
                $previous_appearance_settings['button_padding'],
                $previous_appearance_settings['button_margin'],
                $previous_appearance_settings['button_background_color_onhover'],
                $previous_appearance_settings['button_text_color_onhover'],
                $previous_appearance_settings['button_border_color_onhover'],
                $previous_appearance_settings['button_text'],
                $previous_appearance_settings['button_font_width'],
            );
        }

        $previous_appearance_settings = get_option( 'woo_stock_manager_appearance_tab_settings', $previous_appearance_settings );
        $previous_submit_settings     = get_option( 'woo_stock_manager_form_submission_tab_settings', $previous_submit_settings );
        $previous_email_settings      = get_option( 'woo_stock_manager_email_tab_settings', $previous_email_settings );

        if ( version_compare( $previous_version, '3.0.0', '<=' ) ) {
            $previous_mailchimp_settings                = get_option( 'woo_stock_manager_mailchimp_tab_settings', array() );
            $appearance_settings['is_mailchimp_enable'] = ! empty( $previous_mailchimp_settings ['is_mailchimp_enable'] ) ? 'mailchimp' : 'store_only';

            $appearance_settings['mailchimp'] = array(
                'options'  => $previous_mailchimp_settings ['mailchimp_list_options'] ?? array(),
                'selected' => $previous_mailchimp_settings ['selected_mailchimp_list'] ?? '',
            );
            update_option( 'notifima_mailchimp_settings', $previous_mailchimp_settings );

            $version_key = get_option( 'woo_stock_manager_version', '' );
            update_option( 'notifima_version', $version_key );

            $appearance_settings['is_double_optin']               = ! empty( $previous_appearance_settings['is_double_optin'] ) ? 'confirm_via_email' : 'subscribe_immediately';
            $appearance_settings['is_enable_no_interest']         = ! empty( $previous_appearance_settings['is_enable_no_interest'] ) ? 'show_count' : 'hide_count';
            $appearance_settings['is_enable_backorders']          = ! empty( $previous_appearance_settings['is_enable_backorders'] ) ? 'out_of_stock_and_backorder' : 'out_of_stock';
            $appearance_settings['is_guest_subscriptions_enable'] = ! empty( $previous_appearance_settings['is_guest_subscriptions_enable'] ) ? 'logged_in' : 'everyone';
            $appearance_settings['display_subscription_form_as']  = 'inline';

            delete_option( 'woo_stock_manager_appearance_tab_settings' );
            delete_option( 'woo_stock_manager_form_submission_tab_settings' );
            delete_option( 'woo_stock_manager_email_tab_settings' );
            delete_option( 'woo_stock_manager_mailchimp_tab_settings' );
            delete_option( 'woo_stock_manager_version' );

            // Shortcode migration.
            $shortcodes_to_replace = array(
                '[display_stock_manager_form' => '[notifima_subscription_form',
                '[display_stock_alert_form'   => '[notifima_subscription_form',
            );

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $posts = $wpdb->get_results(
                "SELECT ID, post_content FROM {$wpdb->posts} 
                 WHERE post_content LIKE '%display_stock_manager_form%' 
                 OR post_content LIKE '%display_stock_alert_form%'"
            );

            foreach ( $posts as $post ) {
                $updated_content = $post->post_content;

                foreach ( $shortcodes_to_replace as $old => $new ) {
                    $updated_content = str_replace( $old, $new, $updated_content );
                }

                if ( $updated_content !== $post->post_content ) {
                    wp_update_post(
                        array(
							'ID'           => $post->ID,
							'post_content' => $updated_content,
                        )
                    );
                }
            }
        }

        update_option( Utill::NOTIFIMA_SETTINGS['automation'], array_merge( $previous_appearance_settings, $appearance_settings ) );
        update_option( Utill::NOTIFIMA_SETTINGS['customer-messages'], array_merge( $previous_submit_settings, $submit_settings ) );

        $email_settings['additional_alert_email'] = ! empty( $previous_appearance_settings['additional_alert_email'] ) ? $previous_appearance_settings['additional_alert_email'] : '';
        update_option( Utill::NOTIFIMA_SETTINGS['notifications'], array_merge( $previous_email_settings, $email_settings ) );
    }

    /**
     * Add additional schedule interval.
     *
     * @param array $schedules All schedules.
     * @return array Modified schedules.
     */
    public function register_custom_schedule( $schedules ) {
        $schedules['notifima_ten_minutes'] = array(
            'interval' => 10 * MINUTE_IN_SECONDS,
            'display'  => __( 'Every 10 Minutes', 'notifima' ),
        );

        return $schedules;
    }
}
