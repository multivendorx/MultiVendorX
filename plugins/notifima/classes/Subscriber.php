<?php
/**
 * Subscriber class file.
 *
 * @package Notifima
 */

namespace Notifima;

defined( 'ABSPATH' ) || exit;

/**
 * Notifima Subscriber class
 *
 * @class       Subscriber class
 * @version     3.0.0
 * @author      MultiVendorX
 */
class Subscriber {

    /**
     * Subscriber constructor.
     */
    public function __construct() {
        add_action( 'notifima_start_notification_cron_job', array( $this, 'send_instock_notification_cron' ) );
        add_action( 'woocommerce_update_product', array( $this, 'send_instock_notification' ), 10, 2 );
        add_action( 'delete_post', array( $this, 'delete_product_subscribers' ) );
        add_action( 'notifima_start_subscriber_migration', array( Install::class, 'subscriber_migration' ) );

        if ( Install::is_migration_running() ) {
            $this->register_post_statuses();
        }
    }

    /**
     * Function to register the post status.
     *
     * @return void
     */
    public function register_post_statuses() {
        register_post_status(
            'woo_mailsent',
            array(
				'label'                     => _x( 'Mail Sent', 'woostockalert', 'notifima' ),
				'public'                    => true,
				'exclude_from_search'       => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true, /* translators: %s: count */
				'label_count'               => _n_noop( 'Mail Sent <span class="count">( %s )</span>', 'Mail Sent <span class="count">( %s )</span>', 'notifima' ),
			)
        );

        register_post_status(
            'woo_subscribed',
            array(
				'label'                     => _x( 'Subscribed', 'woostockalert', 'notifima' ),
				'public'                    => true,
				'exclude_from_search'       => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true, /* translators: %s: count */
				'label_count'               => _n_noop( 'Subscribed <span class="count">( %s )</span>', 'Subscribed <span class="count">( %s )</span>', 'notifima' ),
			)
        );

        register_post_status(
            'woo_unsubscribed',
            array(
				'label'                     => _x( 'Unsubscribed', 'woostockalert', 'notifima' ),
				'public'                    => true,
				'exclude_from_search'       => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true, /* translators: %s: count */
				'label_count'               => _n_noop( 'Unsubscribed <span class="count">( %s )</span>', 'Unsubscribed <span class="count">( %s )</span>', 'notifima' ),
			)
        );
    }

    /**
     * Send instock notification on every product's subscriber if product is instock.
     * It will run every hour through corn job.
     *
     * @return void
     */
    public function send_instock_notification_cron() {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $product_ids = $wpdb->get_col(
            $wpdb->prepare(
                "
                SELECT DISTINCT product_id
                FROM {$wpdb->prefix}notifima_subscribers
                WHERE status = %s
                LIMIT %d
                ",
                'subscribed',
                50
            )
        );
        if ( empty( $product_ids ) ) {
            return;
        }

        foreach ( $product_ids as $product_id ) {
            $product = wc_get_product( $product_id );

            if ( $product ) {
                $this->send_instock_notification( $product_id, $product );
            }
        }
    }

    /**
     * Send instock notification of a product's all subscribers on 'woocommerce_update_product' hook
     *
     * @param  int    $product_id product id.
     * @param  object $product the product object.
     * @return void
     */
    public function send_instock_notification( $product_id, $product ) {
        $related_products = self::get_related_product( $product );

        foreach ( $related_products as $related_product ) {
            $this->notify_all_product_subscribers( wc_get_product( $related_product ) );
        }
    }

    /**
     * Send notification to all subscriber, subscribed to a particular product.
     *
     * @param  \WC_Product $product the product object.
     * @return void
     */
    public function notify_all_product_subscribers( $product ) {

        if ( ! $product || $product->is_type( 'variable' ) ) {
            return;
        }

        if ( self::is_product_outofstock( $product ) ) {
            return;
        }

        $product_subscribers = self::get_product_subscribers_email( $product->get_id() );

        if ( ! empty( $product_subscribers ) ) {
            $email = WC()->mailer()->emails['Product_Back_In_Stock_Email'];

            foreach ( $product_subscribers as $subscribe_id => $to ) {
                $email->trigger( $to, $product );
                self::update_subscriber( $subscribe_id, 'mailsent' );
            }

            delete_post_meta( $product->get_id(), 'no_of_subscribers' );
        }
    }

    /**
     * Insert a subscriber to a product.
     *
     * @param string $subscriber_email The email address of the subscriber.
     * @param int    $product_id       The ID of the WooCommerce product.
     * @return \WP_Error|bool|int
     */
    public static function insert_subscriber( $subscriber_email, $product_id ) {
        global $wpdb;

        // Get current user id.
        $user_id = Notifima()->current_user_id;

        // Check the email is already register or not.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $subscriber = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}notifima_subscribers 
                WHERE product_id = %d
                AND email = %s",
                array( $product_id, $subscriber_email )
            )
        );

        // Update the status and create time of the subscriber row.
        if ( $subscriber ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $response = $wpdb->update(
                "{$wpdb->prefix}notifima_subscribers",
                array(
                    'status'      => 'subscribed',
                    'create_time' => current_time( 'mysql' ),
                ),
                array( 'id' => $subscriber->id )
            );
            return $response;
        }

        // Insert new subscriber.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $response = $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}notifima_subscribers
                ( product_id, user_id, email, status )
                VALUES ( %d, %d, %s, %s )
                ON DUPLICATE KEY UPDATE
                status = %s",
                array( $product_id, $user_id, $subscriber_email, 'subscribed', 'subscribed' )
            )
        );

        // Update the product subscriber count after new subscriber insert.
        if ( $response ) {
            self::update_product_subscriber_count( $product_id );
        }

        return $response;
    }

    /**
     * Function that unsubscribe a particular user if the user is already subscribed
     *
     * @param int    $product_id     The product ID to unsubscribe from.
     * @param string $customer_email The subscriber's email address.
     * @return bool
     */
    public static function remove_subscriber( $product_id, $customer_email ) {
        // Check the user is already subscribed or not.
        $unsubscribe_post = self::is_already_subscribed( $customer_email, $product_id );

        if ( $unsubscribe_post ) {
            if ( is_array( $unsubscribe_post ) ) {
                $unsubscribe_post = $unsubscribe_post[0];
            }

            self::update_subscriber( $unsubscribe_post, 'unsubscribed' );
            self::update_product_subscriber_count( $product_id );

            return true;
        }

        return false;
    }

    /**
     * Delete subscriber on product delete.
     *
     * @param  int $post_id the id of product.
     * @return void
     */
    public static function delete_product_subscribers( $post_id ) {
        global $wpdb;

        if ( get_post_type( $post_id ) !== 'product' ) {
            return;
        }

        // Delete subscriber of deleted product.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->delete( $wpdb->prefix . 'notifima_subscribers', array( 'product_id' => $post_id ) );
        delete_post_meta( $post_id, 'no_of_subscribers' );
    }

    /**
     * Delete a subscriber from database.
     *
     * @param int    $product_id The product ID.
     * @param string $email      The subscriber's email address.
     * @return void
     */
    public static function delete_subscriber( $product_id, $email ) {
        global $wpdb;

        // Delete subscriber of deleted product.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->delete(
            $wpdb->prefix . 'notifima_subscribers',
            array(
				'product_id' => $product_id,
				'email'      => $email,
			)
        );

        self::update_product_subscriber_count( $product_id );
    }

    /**
     * Check if a user subscribed to a product.
     * If the user subscribed to the product it return the subscription ID, Or null.
     *
     * @param  mixed $subscriber_email The subscriber's email.
     * @param  mixed $product_id The product id.
     * @return array | string Subscription ID | null
     */
    public static function is_already_subscribed( $subscriber_email, $product_id ) {
        global $wpdb;

        // Get the result from custom subscribers table.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}notifima_subscribers
                WHERE product_id = %d
                AND email = %s
                AND status = %s",
                array( $product_id, $subscriber_email, 'subscribed' )
            )
        );
    }

    /**
     * Update the subscriber count for a product.
     *
     * @param  mixed $product_id The Product id.
     * @return void
     */
    public static function update_product_subscriber_count( $product_id ) {
        global $wpdb;

        // Get subscriber count.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $subscriber_count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}notifima_subscribers
                WHERE product_id = %d
                AND status = %s",
                $product_id,
                'subscribed'
            )
        );

        // Update subscriber count in product's meta.
        update_post_meta( $product_id, 'no_of_subscribers', $subscriber_count );
    }

    /**
     * Update the status of notifima subscriber.
     *
     * @param int    $notifima_id The ID of the subscriber row.
     * @param string $status      The new status to set (e.g., 'subscribed', 'unsubscribed').
     * @return \WP_Error|int
     */
    public static function update_subscriber( $notifima_id, $status ) {
        global $wpdb;

        // Update subscriber status.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->update(
            "{$wpdb->prefix}notifima_subscribers",
            array( 'status' => $status ),
            array( 'id' => $notifima_id )
        );

        return $notifima_id;
    }

    /**
     * Trigger the email for a indivisual customer in time of subscribe.
     * If additional_alert_email setting is set it will send to admin.
     *
     * @param WC_Product $product         The WooCommerce product object.
     * @param string     $customer_email  The customer's email address.
     * @return void
     */
    public static function insert_subscriber_email_trigger( $product, $customer_email ) {
        // Get email object.
        $admin_mail = WC()->mailer()->emails['Admin_New_Subscriber_Email'];
        $cust_mail  = WC()->mailer()->emails['Subscriber_Confirmation_Email'];

        // Get additional email from global setting.
        $additional_email = Notifima()->setting->get_setting( 'additional_alert_email' );

        // Add vendor's email.
        if ( Utill::is_multivendorx_active() ) {
            $store_id = get_post_meta( $product->get_id(), 'multivendorx_store_id', true );
            $store    = new \MultiVendorX\Store\Store( $store_id );

            if ( $store ) {
                $store_email       = sanitize_email( $store->get( 'email' ) );
                $additional_email .= ', ' . $store_email;
            }
        }

        // Trigger the additional email.
        if ( ! empty( $additional_email ) ) {
            $admin_mail->trigger( $additional_email, $product, $customer_email );
        }

        // Trigger customer email.
        $cust_mail->trigger( $customer_email, $product );
    }

    /**
     * Get the email of all subscriber of a particular product.
     *
     * @param  int $product_id The Product ID.
     * @return array array of email
     */
    public static function get_product_subscribers_email( $product_id ) {
        global $wpdb;

        if ( ! $product_id || $product_id <= '0' ) {
            return array();
        }

        $emails = array();

        // Migration is over use custom subscription table for information.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $subscriber_rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, email from {$wpdb->prefix}notifima_subscribers
                WHERE product_id = %d AND status = %s",
                array( $product_id, 'subscribed' )
            )
        );

        // Key the email list by subscriber row ID.
        foreach ( $subscriber_rows as $subscriber_row ) {
            $emails[ $subscriber_row->id ] = $subscriber_row->email;
        }

        return $emails;
    }

    /**
     * Get all child ids if a prodcut is variable else get product id
     *
     * @param  mixed $product The woocommerce product object.
     * @return array
     */
    public static function get_related_product( $product ) {
        // If product is not woocommerce product object.
        if ( is_numeric( $product ) ) {
            $product = wc_get_product( $product );
        }

        $product_ids = array();

        switch ( $product->get_type() ) {
            case 'variable':
                if ( $product->has_child() ) {
                    $product_ids = $product->get_children();
                } else {
                    $product_ids[] = $product->get_id();
                }
                break;
            case 'simple':
                $product_ids[] = $product->get_id();
                break;
            default:
                $product_ids[] = $product->get_id();
        }

        // WPML support - get all translated product IDs for each product ID.
        $wpml_product_ids = array();
        foreach ( $product_ids as $pid ) {
            $trid = apply_filters( 'wpml_element_trid', null, $pid, 'post_product' );
            if ( $trid ) {
                $translations = apply_filters( 'wpml_get_element_translations', null, $trid, 'post_product' );
                foreach ( $translations as $translation ) {
                    if ( ! empty( $translation->element_id ) ) {
                        $wpml_product_ids[] = (int) $translation->element_id;
                    }
                }
            } else {
                $wpml_product_ids[] = $pid;
            }
        }
        return array_unique( $wpml_product_ids );
    }

    /**
     * Bias variable is used to controll biasness of outcome in uncertain input
     * Bias = true->product outofstock | Bias = false->product instock
     *
     * @param  \WC_Product $product The Product object.
     * @return mixed
     */
    public static function is_product_outofstock( $product ) {

        // A variation product is already a WC_Product_Variation instance when is_type() reports
        // 'variation', so its own stock methods can be used directly - no need to re-fetch it by ID.
        if ( $product->is_type( 'variation' ) ) {
            $manage_stock   = $product->managing_stock();
            $stock_quantity = intval( $product->get_stock_quantity() );
            $stock_status   = $product->get_stock_status();
        } else {
            $manage_stock   = $product->get_manage_stock();
            $stock_quantity = $product->get_stock_quantity();
            $stock_status   = $product->get_stock_status();
        }

        $is_enable_backorders = Notifima()->setting->get_setting( 'is_enable_backorders' );

        if ( $manage_stock ) {
            if ( $stock_quantity <= 0 || $stock_quantity <= (int) get_option( 'woocommerce_notify_no_stock_amount' ) ) {
                return true;
            }
        } elseif ( 'onbackorder' === $stock_status && 'out_of_stock_and_backorder' === $is_enable_backorders ) {
            return true;
        } elseif ( 'outofstock' === $stock_status ) {
            return true;
        }

        return false;
    }
}
