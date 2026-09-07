<?php
/**
 * Class Country_Shipping
 *
 * @package multivendorx
 */

namespace MultiVendorX\StoreShipping;

use MultiVendorX\Utill;

defined( 'ABSPATH' ) || exit;

/**
 * MultiVendorX Country Shipping Module.
 *
 * @class       Module class
 * @version     5.0.0
 * @author      MultiVendorX
 */
class Country_Shipping extends \WC_Shipping_Method {


    /**
     * Constructor for your shipping class
     *
     * @access public
     *
     * @return void
     */
    public function __construct() {
        $this->id = 'multivendorx-country-shipping';

        $shipping_modules          = MultiVendorX()->setting->get_setting( 'shipping_modules', array() );
        $country_shipping_settings = $shipping_modules['country-wise-shipping'] ?? array();

        // Enable/Disable.
        $this->enabled = ( ! empty( $country_shipping_settings['enable'] ) && $country_shipping_settings['enable'] )
            ? 'yes'
            : 'no';

        // Set title from module settings.
        $this->title = $country_shipping_settings['country_shipping_method_name']
            ?? $this->get_option( 'title' );

        if ( empty( $this->title ) ) {
            $this->title = __( 'Shipping Cost', 'multivendorx' );
        }

        // Tax setting.
        $taxable_shipping = MultiVendorX()->setting->get_setting( 'taxable', array() );
        $this->tax_status = ( ! empty( $taxable_shipping ) && in_array( 'taxable', $taxable_shipping, true ) )
            ? 'taxable'
            : 'none';
    }
    /**
     * Override admin options to show a custom message instead of settings
     */
    public function admin_options() {
        // Dynamic URL to your MultiVendorX shipping settings.
        $url = admin_url( 'admin.php?page=multivendorx#&tab=settings&subtab=shipping' );
		?>
        <h2><?php echo esc_html( $this->method_title ); ?></h2>
        <p>
            <?php
            // Translation-ready message with a dynamic URL.
            echo wp_kses_post(
                sprintf(
                    /* translators: %s: URL to MultiVendorX shipping settings page */
                    __( 'This shipping method is fully managed in the <a href="%s" target="_blank">MultiVendorX Shipping Settings</a>.', 'multivendorx' ),
                    esc_url( $url )
                )
            );
            ?>
        </p>
		<?php
    }


    /**
     * Checking is gateway enabled or not
     */
    public function is_method_enabled() {
        return 'yes' === $this->enabled;
    }


    /**
     * Calculate shipping
     *
     * @param  array $package Package.
     *
     * @return void
     */
    public function calculate_shipping( $package = array() ) {
        $products            = $package['contents'];
        $destination_country = $package['destination']['country'] ?? '';
        $destination_state   = $package['destination']['state'] ?? '';

        if ( empty( $products ) ) {
            return;
        }

        $store_id = (int) ( $package['store_id'] ?? 0 );

        if ( ! $store_id || ! self::is_shipping_enabled_for_seller( $store_id ) ) {
            return;
        }

        $result = $this->calculate_per_seller( $products, $destination_country, $destination_state, $store_id );

        if ( ! $result['is_shipping_available'] ) {
            return;
        }

        $this->add_store_shipping_rates( $store_id, $result['amount'], $result['is_free_shipping'] );
    }

    /**
     * Check if shipping for this product is enabled
     *
     * @param  int $store_id Store ID.
     *
     * @return boolean
     */
    public static function is_shipping_enabled_for_seller( $store_id ) {
        $store            = new \MultiVendorX\Store\Store( $store_id );
        $shipping_options = $store->meta_data[ Utill::STORE_SETTINGS_KEYS['shipping_options'] ] ?? '';
        return Utill::STORE_SETTINGS_KEYS['shipping_by_country'] === $shipping_options;
    }

    /**
     * Calculate shipping cost for a single store.
     *
     * @param array  $products            Store products.
     * @param string $destination_country Destination country.
     * @param string $destination_state   Destination state.
     * @param int    $store_id            Store ID.
     *
     * @return array
     */
    public function calculate_per_seller( $products, $destination_country, $destination_state, $store_id ) {
        $amount = 0.0;
        $price  = array();

        $store = new \MultiVendorX\Store\Store( $store_id );
        $meta  = $store->meta_data; // All store meta data.

        $multivendorx_free_shipping_amount = isset( $meta[ Utill::STORE_SETTINGS_KEYS['country_free_shipping_amount'] ] ) ? $meta[ Utill::STORE_SETTINGS_KEYS['country_free_shipping_amount'] ] : '';
        $multivendorx_free_shipping_amount = apply_filters( 'multivendorx_free_shipping_minimum_order_amount', $multivendorx_free_shipping_amount, $store_id );

        $default_shipping_price     = isset( $meta[ Utill::STORE_SETTINGS_KEYS['country_shipping_type_price'] ] ) ? $meta[ Utill::STORE_SETTINGS_KEYS['country_shipping_type_price'] ] : 0;
        $default_shipping_add_price = isset( $meta[ Utill::STORE_SETTINGS_KEYS['country_additional_product'] ] ) ? $meta[ Utill::STORE_SETTINGS_KEYS['country_additional_product'] ] : 0;
        $default_shipping_qty_price = isset( $meta[ Utill::STORE_SETTINGS_KEYS['country_additional_qty'] ] ) ? $meta[ Utill::STORE_SETTINGS_KEYS['country_additional_qty'] ] : 0;

        // Resolve country/state shipping rates first, so we know whether this destination is covered at all.
        $raw_data                    = $meta[ Utill::STORE_SETTINGS_KEYS['country_shipping_rates'] ] ?? array();
        $multivendorx_shipping_rates = is_string( $raw_data ) ? json_decode( $raw_data, true ) : $raw_data;
        $multivendorx_shipping_rates = is_array( $multivendorx_shipping_rates ) ? $multivendorx_shipping_rates : array();

        $state_rate      = 0;
        $country_rate    = null;
        $everywhere_rate = null;

        foreach ( $multivendorx_shipping_rates as $rate ) {
            if ( empty( $rate['country'] ) ) {
                continue;
            }
            if ( $rate['country'] === $destination_country ) {
                $country_rate = $rate;
                break;
            }
            if ( 'everywhere' === $rate['country'] ) {
                $everywhere_rate = $rate;
            }
        }

        // If rate rules exist but neither the destination country nor an "everywhere" rule matches, shipping isn't available.
        if ( ! empty( $multivendorx_shipping_rates ) && ! $country_rate && ! $everywhere_rate ) {
            return array(
                'amount'                => 0,
                'is_shipping_available' => false,
                'is_free_shipping'      => false,
            );
        }

        if ( $country_rate ) {
            if ( $destination_state && ! empty( $country_rate['states'] ) ) {
                $state_found = false;
                foreach ( $country_rate['states'] as $state ) {
                    if ( $state['state'] === $destination_state ) {
                        $state_rate  = floatval( $state['cost'] );
                        $state_found = true;
                        break;
                    }
                }
                if ( ! $state_found && isset( $everywhere_rate ) ) {
                    $state_rate = floatval( $everywhere_rate['cost'] );
                }
            } else {
                $state_rate = floatval( $country_rate['cost'] );
            }
        } elseif ( $everywhere_rate ) {
            $state_rate = floatval( $everywhere_rate['cost'] );
        }

        $downloadable_count  = 0;
        $products_total_cost = 0;

        foreach ( $products as $product ) {
            // Check virtual/downloadable.
            if ( isset( $product['variation_id'] ) ) {
                $is_virtual      = get_post_meta( $product['variation_id'], '_virtual', true );
                $is_downloadable = get_post_meta( $product['variation_id'], '_downloadable', true );
            } else {
                $is_virtual      = get_post_meta( $product['product_id'], '_virtual', true );
                $is_downloadable = get_post_meta( $product['product_id'], '_downloadable', true );
            }

            if ( 'yes' === $is_virtual || 'yes' === $is_downloadable ) {
                ++$downloadable_count;
                continue;
            }

            // Use default quantity-based shipping price.
            $shipping_qty_price = $default_shipping_qty_price;

            $price[ $store_id ]['default'] = floatval( $default_shipping_price );

            // Additional quantity price.
            if ( $product['quantity'] > 1 ) {
                $price[ $store_id ]['qty'][] = ( ( $product['quantity'] - 1 ) * floatval( $shipping_qty_price ) );
            } else {
                $price[ $store_id ]['qty'][] = 0;
            }

            // Calculate total product cost.
            $line_subtotal      = (float) $product['line_subtotal'];
            $line_total         = (float) $product['line_total'];
            $discount_total     = $line_subtotal - $line_total;
            $line_subtotal_tax  = (float) $product['line_subtotal_tax'];
            $line_total_tax     = (float) $product['line_tax'];
            $discount_tax_total = $line_subtotal_tax - $line_total_tax;

            if ( apply_filters( 'multivendorx_free_shipping_threshold_consider_tax', true ) ) {
                $total = $line_subtotal + $line_subtotal_tax;
            } else {
                $total = $line_subtotal;
            }

            if ( WC()->cart->display_prices_including_tax() ) {
                $products_total_cost += round( $total - ( $discount_total + $discount_tax_total ), wc_get_price_decimals() );
            } else {
                $products_total_cost += round( $total - $discount_total, wc_get_price_decimals() );
            }
        }

        // Check free shipping threshold (destination is already confirmed available above).
        if ( $multivendorx_free_shipping_amount && ( $multivendorx_free_shipping_amount <= $products_total_cost ) ) {
            return array(
                'amount'                => 0,
                'is_shipping_available' => true,
                'is_free_shipping'      => true,
            );
        }

        // Additional product cost.
        $price[ $store_id ]['add_product'] = count( $products ) > 1
            ? floatval( $default_shipping_add_price ) * ( count( $products ) - ( 1 + $downloadable_count ) )
            : 0;

        $price[ $store_id ]['state_rates'] = $state_rate;

        // Sum up total shipping amount.
        if ( ! empty( $price ) ) {
            foreach ( $price as $s_id => $value ) {
                $amount += (
                    ( isset( $value['default'] ) ? $value['default'] : 0 )
                    + ( isset( $value['qty'] ) ? array_sum( $value['qty'] ) : 0 )
                    + $value['add_product']
                    + ( isset( $value['state_rates'] ) ? $value['state_rates'] : 0 )
                );
            }
        }

        $amount = apply_filters( 'multivendorx_shipping_country_calculate_amount', $amount, $price, $products, $destination_country, $destination_state );

        return array(
            'amount'                => $amount,
            'is_shipping_available' => true,
            'is_free_shipping'      => false,
        );
    }


    /**
     * Add shipping rates for a store.
     *
     * @param int   $store_id         Store ID.
     * @param float $amount           Shipping amount.
     * @param bool  $is_free_shipping Whether free shipping applies.
     *
     * @return void
     */
    public function add_store_shipping_rates( $store_id, $amount, $is_free_shipping = false ) {
        $store = new \MultiVendorX\Store\Store( $store_id );
        $meta  = $store->meta_data;

        $tax_rate = ( 'none' === $this->tax_status ) ? false : '';
        $tax_rate = apply_filters( 'multivendorx_is_apply_tax_on_shipping_rates', $tax_rate );

        // Country-wise shipping.
        if ( $is_free_shipping ) {
            $this->add_rate(
                array(
                    'id'    => $this->id . ':' . $store_id,
                    'label' => __( 'Free Shipping', 'multivendorx' ),
                    'cost'  => 0,
                    'taxes' => $tax_rate,
                )
            );
        } elseif ( $amount > 0 ) {
            $this->add_rate(
                array(
                    'id'    => $this->id . ':' . $store_id,
                    'label' => $this->title,
                    'cost'  => $amount,
                    'taxes' => $tax_rate,
                )
            );
        }

        // Local pickup.
        $local_pickup_cost = $meta[ Utill::STORE_SETTINGS_KEYS['country_local_pickup_cost'] ] ?? 0;

        if ( $local_pickup_cost ) {
            $this->add_rate(
                array(
                    'id'    => 'local_pickup:' . $store_id,
                    'label' => __( 'Pickup from Store', 'multivendorx' ),
                    'cost'  => $local_pickup_cost,
                    'taxes' => $tax_rate,
                )
            );
        }
    }
}