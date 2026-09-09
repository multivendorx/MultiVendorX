<?php
/**
 * Block class file
 *
 * @package MultiVendorX
 */

namespace MultiVendorX;

defined( 'ABSPATH' ) || exit;

/**
 * MultiVendorX Block class
 *
 * @class       Block class
 * @version     5.0.0
 * @author      MultiVendorX
 */
class Block {
    /**
     * Array of blocks to be registered.
     *
     * @var array
     */
    private $blocks;

    /**
     * Constructor for Block class
     *
     * @return void
     */
    public function __construct() {
        // Register block category.
        add_filter( 'block_categories_all', array( $this, 'register_block_category' ) );
        // Register the block.
        add_action( 'init', array( $this, 'register_blocks' ) );
        // Localize the script for block.
        add_action( 'enqueue_block_assets', array( $this, 'enqueue_all_block_assets' ) );
        // Localize in frontend.
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }
    /**
     * Get the list of initialized blocks.
     *
     * If the blocks have not been initialized yet, this method calls
     * `initialize_blocks()` to populate them.
     *
     * @return array List of blocks.
     */
    public function get_blocks() {
        if ( is_null( $this->blocks ) ) {
            $this->blocks = $this->initialize_blocks();
        }
        return $this->blocks;
    }

    /**
     * Initialize blocks based on active modules.
     *
     * @return array List of blocks with their configuration.
     */
    public function initialize_blocks() {
        $blocks     = array();
        $textdomain = 'multivendorx';

        $block_base_path = FrontendScripts::get_asset_path( 'file' ) . 'js/block/';

        $exclude_blocks = array( 'setup-wizard' );
        if ( ! is_dir( $block_base_path ) ) {
            return $blocks;
        }

        $folders = glob( $block_base_path . '*', GLOB_ONLYDIR );
        foreach ( $folders as $folder ) {
            $block_name = basename( $folder );
            if ( in_array( $block_name, $exclude_blocks, true ) ) {
                continue;
            }

            if ( file_exists( $folder . '/block.json' ) ) {
                $blocks[] = array(
                    'name'       => $block_name,
                    'textdomain' => $textdomain,
                    'block_path' => $block_base_path,
                );
            }
        }

        return apply_filters( 'multivendorx_initialize_blocks', $blocks );
    }

    /**
     * Enqueue assets and localize scripts for all registered blocks.
     *
     * @return void
     */
    public function enqueue_all_block_assets() {
        if ( is_admin() && function_exists( 'get_current_screen' ) ) {
            $screen = get_current_screen();
            if ( $screen && $screen->is_block_editor ) {
                FrontendScripts::load_scripts();
                FrontendScripts::enqueue_script( 'multivendorx-vendor-script' );
                foreach ( $this->get_blocks() as $block_script ) {
                    FrontendScripts::localize_scripts( $block_script['textdomain'] . '-' . $block_script['name'] . '-editor-script' );
                }
            }
        }
    }
	/**
	 * Enqueue frontend scripts for registered blocks.
	 *
	 * Iterates through all registered block scripts and enqueues their
	 * JavaScript files only if the block exists in the current post content.
	 * Also localizes the scripts with necessary data.
	 *
	 * @global WP_Post $post Current post object.
	 *
	 * @return void
	 */
    public function enqueue_scripts() {
        global $post;
        FrontendScripts::load_scripts();

        $has_multivendorx_block = false;

        foreach ( $this->get_blocks() as $block_script ) {
            $block_name = $block_script['textdomain'] . '/' . $block_script['name'];

            if ( has_block( $block_name, $post ) ) {
                $has_multivendorx_block = true;
                $handle                 = $block_script['textdomain'] . '-' . $block_script['name'] . '-view-script';
                // FrontendScripts::enqueue_script( $handle );
                FrontendScripts::localize_scripts( $handle );
            }
        }

        // Only ship the shared vendor bundle on pages that actually contain one of our blocks.
        if ( $has_multivendorx_block ) {
            FrontendScripts::enqueue_script( 'multivendorx-vendor-script' );
        }
    }

    /**
     * Register MultiVendorX block category in the block editor.
     *
     * @param array $categories Existing block categories.
     * @return array Modified block categories.
     */
    public function register_block_category( $categories ) {
        // Adding a new category.
        $categories[] = array(
            'slug'  => 'multivendorx',
            'title' => 'MultiVendorX Store Sidebar',
        );

        $categories[] = array(
            'slug'  => 'multivendorx-store-shop',
            'title' => 'MultiVendorX Store Page Builder',
        );

        $categories[] = array(
            'slug'  => 'multivendorx-shortcodes',
            'title' => 'MultiVendorX Shortcodes',
        );

        return $categories;
    }

    /**
     * Register all defined blocks.
     *
     * @return void
     */
    public function register_blocks() {
        foreach ( $this->get_blocks() as $block ) {
            register_block_type( $block['block_path'] . $block['name'] );
        }
    }
}
