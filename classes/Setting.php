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
        // $this->load_settings();
    }

    function mvx_get_option( $key, $default_val = '', $lang_code = '' ) {
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
}