<?php

namespace MultiVendorX;

defined('ABSPATH') || exit;

/**
 * Catalog Setting API class
 */
class Setting {
    /**
     * Container store global setting
     * @var array
     */
    private $settings = [];

    /**
     * Contain global key of all settings
     * @var array
     */
    private $settings_keys = [];

    /**
     * Construct function for load setting
     */
    function __construct() {
        // Load all settings
        $this->load_settings();
    }

    /**
     * Load all setting from option table
     * @param mixed $fource
     * @return void
     */
    private function load_settings( $fource = true ) {

        // If settings are loaded previously and not force to load
        if ( ! $fource && $this->settings ) {
            return;
        }

        $setting_keys = $this->get_settings_keys();

        // Get all setting from option table
        foreach( $setting_keys as $key) {
            $this->settings[ $key ] = get_option( $key, [] );
        }
    }

    /**
     * Get all register setting key
     * @return array 
     */
    private function get_settings_keys() {

        // Settings key are avialable
        if ( $this->settings_keys ) {
            return $this->settings_keys;
        }

        /**
         * Filter for register settings key's
         * @var array setting keys
         */
        $this->settings_keys = apply_filters( 'multivendorx_register_settings_keys', [
            'multivendorx-settings-general-settings',
            'multivendorx-disbursement-settings',
            'multivendorx-commissions-settings',
            'multivendorx-spmv-pages-settings',
            'multivendorx-products-capability-settings',
            'multivendorx-seller-dashboard-settings',
            'multivendorx-products-settings',
            'multivendorx-refund-management-settings',
            'multivendorx-settings-identity-verification-settings',
            'multivendorx-invoice-default-settings',
            'multivendorx-store-settings',
            'multivendorx-settings-advertising-settings',
            'multivendorx-settings-live-chat-settings',
            'multivendorx-settings-store-inventory-settings',
            'multivendorx-settings-min-max-settings',
            'multivendorx-review-management-settings',
            'multivendorx-new-vendor-registration-form-settings',
            'multivendorx-order-settings',
            'multivendorx-social-settings',
            'multivendorx-settings-vendor-invoice-settings',
            'multivendorx-settings-wholesale-settings',
            'multivendorx-settings-store-support-settings',
            'multivendorx-policy-settings',
            'multivendorx-payment-masspay-settings'
        ]);

        return $this->settings_keys;
    }

    /**
     * Get the setting that was previously added.
     * If setting is not present it return defalult value 
     * @param string $key setting key
     * @param string $default setting value
     * @param mixed $option_key option table's key
     * @return mixed
     */
    public function get_setting( $key, $default = '', $option_key = null ) {

        // If option key is not provided
        if ( ! $option_key ) {
            $option_key = $this->get_option_key( $key );
        }

        $setting = $this->settings[ $option_key ] ?? [];

        return $setting[ $key ] ?? $default;
    }

    /**
     * Update the setting that was already added.
     * @param string $key setting key
     * @param string $value setting value
     * @param string $option_key option table's key
     * @return void
     */
    public function update_setting( $key, $value, $option_key = null ) {

        // If option key is not provided
        if ( ! $option_key ) {
            $option_key = $this->get_option_key( $key );
        }

        // Get the setting array from setting settings container
        $setting = $this->settings[ $option_key ] ?? [];

        // Update setting in setting container
        $setting[ $key ] = $value;
        $this->settings[ $option_key ] = $setting;

        // Update the setting in database
        update_option( $option_key,  $setting );
    }

    function get_option( $key, $default_val = '', $lang_code = '' ) {
        $option_val = get_option( $key, $default_val );
        
        // // WPML Support
        // if ( defined( 'ICL_SITEPRESS_VERSION' ) && ! ICL_PLUGIN_INACTIVE && class_exists( 'SitePress' ) ) {
        //     global $sitepress;
        //     if( !$lang_code ) {
        //         $current_language = $sitepress->get_current_language();
        //     } else {
        //         $current_language = $lang_code;
        //     }
        //     $option_val = get_option( $key . '_' . $current_language, $option_val );
        // }
        
        return $option_val;
    }

    /**
     * Update the value in option table.
     * If key does't exist it create it.
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function update_option( $key, $value ) {
        
        // Check key exist in register settings keys
        if ( in_array( $key, $this->get_settings_keys() ) ) {
            
            // Update the container
            $this->settings[ $key ] = $value;
        }

        // Update the option
        update_option( $key, $value );
    }

    /**
     * Find option key from setting container
     * @param mixed $key setting key
     * @return string
     */
    private function get_option_key( $key ) {
        foreach ( $this->settings as $option_key => $setting ) {     
            // Key exist in a particular setting.
            if ( is_array( $setting ) && array_key_exists( $key, $setting ) ) {
                return $option_key;
            }
        }

        return 'multivendorx-extra-settings';
    }
}