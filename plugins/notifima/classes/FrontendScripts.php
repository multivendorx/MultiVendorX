<?php
/**
 * FrontendScripts class file.
 *
 * @package Notifima
 */

namespace Notifima;

defined( 'ABSPATH' ) || exit;

/**
 * Notifima FrontendScripts class
 *
 * @class       FrontendScripts class
 * @version     3.0.0
 * @author      MultiVendorX
 */
class FrontendScripts {

    /**
     * Cached admin settings.
     *
     * @var array|null
     */
    private static $settings_cache = null;

    /**
     * FrontendScripts constructor.
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_load_scripts' ) );
    }

    /**
	 * Get the build path for assets based on environment.
	 *
	 * @param string $path_type   Either 'url' or 'file'.
	 * @param string $plugin_path Optional. Plugin filesystem path override.
	 * @param string $plugin_url  Optional. Plugin URL override.
	 * @return string Relative path to the build directory.
	 */
    public static function get_asset_path( $path_type = 'url', $plugin_path = '', $plugin_url = '' ) {
        $build_path = 'assets/';
        if ( '' === $plugin_path ) {
            $plugin_path = Notifima()->plugin_path;
        }
        if ( '' === $plugin_url ) {
            $plugin_url = Notifima()->plugin_url;
        }

        return 'file' === $path_type
            ? $plugin_path . $build_path
            : $plugin_url . $build_path;
    }


    /**
	 * Register and store a script for later use.
	 *
	 * @param string $handle       Unique script handle.
	 * @param string $path         URL to the script file.
	 * @param array  $deps         Optional. Script dependencies. Default empty array.
	 * @param string $version      Optional. Script version. Default empty string.
	 */
    public static function register_script( $handle, $path, $deps = array(), $version = '' ) {
        wp_register_script( $handle, $path, $deps, $version, true );
        wp_set_script_translations( $handle, 'notifima' );
    }

    /**
	 * Register and store a style for later use.
	 *
	 * @param string $handle   Unique style handle.
	 * @param string $path     URL to the style file.
	 * @param array  $deps     Optional. Style dependencies. Default empty array.
	 * @param string $version  Optional. Style version. Default empty string.
	 */
    public static function register_style( $handle, $path, $deps = array(), $version = '' ) {
        wp_register_style( $handle, $path, $deps, $version );
    }

    /**
	 * Register frontend scripts using filters and enqueue required external scripts.
	 *
	 * Loads block assets and additional scripts defined through the `notifima_register_scripts` filter.
	 */
    public static function register_frontend_scripts() {
        $version = Notifima()->version;

        $block_scripts = array(
            'subscribe-form',
        );

        $register_scripts = apply_filters(
            'notifima_register_scripts',
            array()
        );

        foreach ( $block_scripts as $handle ) {
            $asset_path = self::get_asset_path( 'file' ) . "js/block/{$handle}/index.asset.php";
            $asset      = file_exists( $asset_path )
                ? include $asset_path
                : array(
					'dependencies' => array(),
					'version'      => $version,
				);

            $register_scripts[ "notifima-{$handle}" ] = array(
                'src'  => self::get_asset_path() . "js/block/{$handle}/index.js",
                'deps' => $asset['dependencies'],
            );
        }

        foreach ( $register_scripts as $name => $props ) {
            self::register_script( $name, $props['src'], $props['deps'], $props['version'] ?? $version );
        }
    }

    /**
	 * Register frontend styles using filters.
	 *
	 * Allows style registration through `notifima_register_styles` filter.
	 */
    public static function register_frontend_styles() {
        $version         = Notifima()->version;
        $register_styles = apply_filters(
            'notifima_register_styles',
            array(
                'notifima-frontend-style' => array(
					'src' => self::get_asset_path() . 'styles/public/' . NOTIFIMA_PLUGIN_SLUG . '-frontend.min.css',
				),
			)
        );
        foreach ( $register_styles as $name => $props ) {
            self::register_style( $name, $props['src'], array(), $props['version'] ?? $version );
        }
    }

    /**
     * Register/queue frontend scripts.
     */
    public static function load_scripts() {
        self::register_frontend_scripts();
        self::register_frontend_styles();
    }

    /**
	 * Register/queue admin scripts.
	 */
	public static function admin_load_scripts() {
        self::register_admin_scripts();
		self::register_admin_styles();
    }

    /**
	 * Register admin scripts using filters.
	 *
	 * Loads admin-specific JavaScript assets and chunked dependencies.
	 */
    public static function register_admin_scripts() {
		$version          = Notifima()->version;
        $index_asset_path = self::get_asset_path( 'file' ) . 'js/index.asset.php';
        $index_asset      = file_exists( $index_asset_path )
            ? include $index_asset_path
            : array(
                'dependencies' => array(),
                'version'      => $version,
            );

        $vendor_asset_path = self::get_asset_path( 'file' ) . 'js/vendors.asset.php';
        $vendor_asset      = file_exists( $vendor_asset_path )
            ? include $vendor_asset_path
            : array(
                'dependencies' => array(),
                'version'      => $version,
            );

		$register_scripts = apply_filters(
            'admin_notifima_register_scripts',
            array(
                'notifima-components-script' => array(
                	'src'  => self::get_asset_path() . 'js/vendors.js',
                	'deps' => $vendor_asset['dependencies'],
                ),
				'notifima-admin-script'      => array(
					'src'  => self::get_asset_path() . 'js/index.js',
					'deps' => $index_asset['dependencies'],
				),
            )
        );
		foreach ( $register_scripts as $name => $props ) {
			self::register_script( $name, $props['src'], $props['deps'], $props['version'] ?? $version );
		}
	}

    /**
	 * Register admin styles using filters.
	 *
	 * Allows style registration through `admin_notifima_register_styles` filter.
	 */
    public static function register_admin_styles() {
		$version         = Notifima()->version;
		$register_styles = apply_filters(
            'admin_notifima_register_styles',
            array(
				'notifima-admin-style' => array(
					'src' => self::get_asset_path() . 'styles/index.css',
				),
			)
        );

		foreach ( $register_styles as $name => $props ) {
			self::register_style( $name, $props['src'], array(), $props['version'] ?? $version );
		}
	}

    /**
     * Get the cached settings for every registered admin settings tab.
     *
     * @return array
     */
    public static function get_admin_settings() {
        if ( null !== self::$settings_cache ) {
            return self::$settings_cache;
        }

        $settings = array();

        $tabs_names = apply_filters(
            'notifima_additional_tabs_names',
            array_keys( Utill::NOTIFIMA_SETTINGS )
        );

        foreach ( $tabs_names as $tab_name ) {
            $option_name           = str_replace( '-', '_', 'notifima_' . $tab_name . '_settings' );
            $settings[ $tab_name ] = Notifima()->setting->get_option( $option_name );
        }

        self::$settings_cache = $settings;
        return self::$settings_cache;
    }

    /**
	 * Localize all scripts.
	 *
	 * @param string $handle Script handle the data will be attached to.
	 */
    public static function localize_scripts( $handle ) {

        $base_rest = array(
            'apiUrl'  => untrailingslashit( get_rest_url() ),
            'restUrl' => Notifima()->rest_namespace,
            'nonce'   => wp_create_nonce( 'wp_rest' ),
        );

        $settings_data = array(
            'settings_databases_value' => self::get_admin_settings(),
        );

        $localize_scripts =
            array(
                'notifima-admin-script'   => array(
                    'object_name'  => 'appLocalizer',
                    'use_rest'     => true,
                    'use_settings' => true,
					'data'         => array(
                        'admin_url'     => admin_url(),
						'export_button' => wp_nonce_url( admin_url( 'admin-ajax.php?action=export_subscribers' ), 'export_subscribers_nonce' ),
						'khali_dabba'   => Utill::is_khali_dabba(),
						'tab_name'      => __( 'Notifima', 'notifima' ),
						'pro_url'       => esc_url( NOTIFIMA_PRO_SHOP_URL ),
						'free_version'  => Notifima()->version,
                        'pro_data'      => apply_filters(
                            'notifima_update_pro_data',
                            array(
								'version'         => false,
								'manage_plan_url' => NOTIFIMA_PRO_SHOP_URL,
                            )
                        ),
					),
                ),
                'notifima-subscribe-form' => array(
                    'object_name' => 'subscription',
                    'use_rest'    => true,
                    'data'        => apply_filters(
                        'notifima_subscribe_form_localize_data',
                        array(
                            'khali_dabba'  => Utill::is_khali_dabba(),
                            'lead_time'    => Notifima()->frontend->get_product_lead_time(),
                            'display_type' => Notifima()->setting->get_setting( 'display_subscription_form_as', 'inline' ),
                        )
                    ),
                ),
			);

        $localize_scripts = apply_filters( 'notifima_localize_scripts', $localize_scripts );
        $config           = $localize_scripts[ $handle ] ?? array();

        $localized_data = array();

        if ( ! empty( $config['use_rest'] ) ) {
            $localized_data = array_merge( $localized_data, $base_rest );
        }

        if ( ! empty( $config['use_settings'] ) ) {
            $localized_data = array_merge( $localized_data, $settings_data );
        }

        if ( ! empty( $config['data'] ) ) {
            $localized_data = array_merge( $localized_data, $config['data'] );
        }

        if ( isset( $localize_scripts[ $handle ] ) ) {
            $props = $localize_scripts[ $handle ];
            self::localize_script( $handle, $props['object_name'], $localized_data );
        }
    }

    /**
	 * Localizes a registered script with data for use in JavaScript.
	 *
	 * @param string $handle Script handle the data will be attached to.
	 * @param string $name   JavaScript object name.
	 * @param array  $data   Data to be made available in JavaScript.
	 */
    public static function localize_script( $handle, $name, $data = array() ) {
		wp_localize_script( $handle, $name, $data );
	}

	/**
	 * Enqueues a registered script.
	 *
	 * @param string $handle Handle of the registered script to enqueue.
	 */
    public static function enqueue_script( $handle ) {
		wp_enqueue_script( $handle );
	}

	/**
	 * Enqueues a registered style.
	 *
	 * @param string $handle Handle of the registered style to enqueue.
	 */
    public static function enqueue_style( $handle ) {
		wp_enqueue_style( $handle );
	}
}
