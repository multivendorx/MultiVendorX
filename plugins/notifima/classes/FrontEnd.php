<?php
/**
 * Frontend class file.
 *
 * @package Notifima
 */

namespace Notifima;

defined( 'ABSPATH' ) || exit;

/**
 * Notifima Frontend class
 *
 * @class       Frontend class
 * @version     3.0.0
 * @author      MultiVendorX
 */
class FrontEnd {

    /**
     * Frontend constructor.
     */
    public function __construct() {
        // enqueue scripts.
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );

        add_action( 'woocommerce_simple_add_to_cart', array( $this, 'display_product_subscription_form' ), 10 );
        add_filter( 'woocommerce_grouped_product_list_column_price', array( $this, 'append_grouped_product_subscription_form' ), 10, 2 );
        add_action( 'woocommerce_after_variations_form', array( $this, 'display_product_subscription_form' ), 10 );
    }


    /**
     * Enqueue frontend JavaScript. And Send Localize data.
     *
     * @return void
     */
    public function enqueue_frontend_scripts() {
        if ( is_product() || has_shortcode( get_post()->post_content, 'notifima_subscription_form' ) ) {
            FrontendScripts::load_scripts();
            FrontendScripts::admin_load_scripts();
            FrontendScripts::enqueue_script( 'notifima-components-script' );
			FrontendScripts::enqueue_style( 'notifima-components-style' );
			FrontendScripts::enqueue_script( 'notifima-subscribe-form' );
            FrontendScripts::localize_scripts( 'notifima-subscribe-form' );
        }
        FrontendScripts::enqueue_style( 'notifima-frontend-style' );
    }

    /**
     * Display product subscription form if product is out of stock
     *
     * @param   WC_Product|null $product_obj The WooCommerce product object. Defaults to global product if null.
     * @return  void
     */
    public function display_product_subscription_form( $product_obj = null ) {
        global $product;
        $product_obj = is_int( $product_obj ) ? wc_get_product( $product_obj ) : ( $product_obj ? $product_obj : $product );

        if ( empty( $product_obj ) ) {
            return;
        }

        if ( 'yes' === $product_obj->get_meta( Utill::NOTIFIMA_PRODUCT_META['product_discontinued'] ) ) {
            return;
        }

        $guest_subscription_enabled = Notifima()->setting->get_setting( 'is_guest_subscriptions_enable', '' );
        if ( 'logged_in' === $guest_subscription_enabled && ! is_user_logged_in() ) {
            return;
        }

        $backorders_enabled = Notifima()->setting->get_setting( 'is_enable_backorders', '' );

        $stock_status = $product_obj->get_stock_status();
        if ( 'onbackorder' === $stock_status && 'out_of_stock' === $backorders_enabled ) {
            return;
        }

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_subscription_form() already runs every dynamic value through esc_attr(); the surrounding markup is static.
        echo $this->get_subscription_form( $product_obj );
    }

    /**
     * Display Request Stock Form for grouped product.
     *
     * @param string $value default html.
     * @param object $child individual child of grouped product.
     */
    public function append_grouped_product_subscription_form( $value, $child ) {
        return $value . $this->get_subscription_form( $child );
    }

    /**
     * Get subscription form HTML content for a particular product.
     * If the product is not outofstock it return empty string.
     *
     * @param  mixed $product   product variable.
     * @param  mixed $variation variation variable default null.
     * @return string HTML of subscription form.
     */
    public function get_subscription_form( $product, $variation = null ) {
        if ( ! $product->is_type( 'variable' ) && ! Subscriber::is_product_outofstock( $variation ? $variation : $product ) ) {
            return '';
        }

        if ( $variation && 'yes' === $variation->get_meta( Utill::NOTIFIMA_PRODUCT_META['product_discontinued'] ) ) {
            return '';
        }

        $user_email = '';

        if ( is_user_logged_in() ) {
            $user_email = Notifima()->current_user->user_email;
        }

        $settings_array        = Utill::get_form_settings_array();
        $shown_interest_text   = $settings_array['shown_interest_text'];
        $is_enable_no_interest = Notifima()->setting->get_setting( 'is_enable_no_interest', '' );

        $get_shown_interest = static function ( $product_id ) use ( $is_enable_no_interest, $shown_interest_text ) {
            $interested_person = max( 0, (int) get_post_meta( $product_id, 'no_of_subscribers', true ) );

            if ( 'show_count' !== $is_enable_no_interest || $interested_person <= 0 || ! $shown_interest_text ) {
                return '';
            }

            return str_replace( '%no_of_subscribed%', $interested_person, $shown_interest_text );
        };

        // Variable products.
        if ( $product->is_type( 'variable' ) ) {
            $html = '';

            foreach ( $product->get_children() as $variation_id ) {
                $variation = wc_get_product( $variation_id );

                if ( ! $variation || ! Subscriber::is_product_outofstock( $variation ) || 'yes' === $variation->get_meta( Utill::NOTIFIMA_PRODUCT_META['product_discontinued'] ) ) {
                    continue;
                }

                $html .= sprintf(
                    '<div
                        id="notifima-subscribe-form-%d"
                        data-product-id="%d"
                        data-variation-id="%d"
                        data-product-title="%s"
                        data-user-email="%s"
                        data-shown-interest="%s"
                        style="display:none"
                    ></div>',
                    esc_attr( $variation->get_id() ),
                    esc_attr( $product->get_id() ),
                    esc_attr( $variation->get_id() ),
                    esc_attr( $product->get_title() ),
                    esc_attr( $user_email ),
                    esc_attr( $get_shown_interest( $variation->get_id() ) )
                );
            }

            return $html;
        }

        // Simple & grouped products.
        return sprintf(
            '<div
                class="notifima-subscribe-form"
                data-product-id="%d"
                data-variation-id="%d"
                data-product-title="%s"
                data-user-email="%s"
                data-shown-interest="%s"
            ></div>',
            esc_attr( $product->get_id() ),
            esc_attr( $variation ? $variation->get_id() : 0 ),
            esc_attr( $product->get_title() ),
            esc_attr( $user_email ),
            esc_attr(
                $get_shown_interest(
                    $variation ? $variation->get_id() : $product->get_id()
                )
            )
        );
    }

    /**
     * Get the lead time text for the current product.
     *
     * @return string
     */
    public function get_product_lead_time() {
        $product_id = get_queried_object_id();
        $product    = wc_get_product( $product_id );

        if ( ! $product instanceof \WC_Product ) {
            return '';
        }

        $display_lead_times = Notifima()->setting->get_setting( 'display_lead_times', array() );

        if (
            ! empty( $display_lead_times ) &&
            in_array( $product->get_stock_status(), $display_lead_times, true )
        ) {
            return Notifima()->setting->get_setting( 'lead_time_static_text', '' );
        }

        return '';
    }
}
