<?php
/**
 * Block class file.
 *
 * @package MooWoodle
 */

namespace MooWoodle;

use MooWoodle\FrontendScripts;

defined( 'ABSPATH' ) || exit;
/**
 * MooWoodle Block class
 *
 * @class       Block class
 * @version     3.3.0
 * @author      DualCube
 */
class Block {
    /**
     * Array of blocks to be registered.
     *
     * @var array
     */
    private $blocks = array();

    /**
     * Constructor for Block class
     *
     * @return void
     */
    public function __construct() {
        // Register the block.
        add_action( 'init', array( $this, 'register_blocks' ) );
        // Localize the script for block.
        add_action( 'enqueue_block_assets', array( $this, 'enqueue_blocks_assets' ) );
        // Localize in frontend.
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
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
        if ( empty( $this->blocks ) ) {
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
        $textdomain = 'moowoodle';

        $block_base_path = FrontendScripts::get_asset_path( 'file' ) . 'js/block/';

        if ( ! is_dir( $block_base_path ) ) {
            return $blocks;
        }

        $block_directories = glob( $block_base_path . '*', GLOB_ONLYDIR );
        if ( ! empty( $block_directories ) ) {
            foreach ( $block_directories as $block_directory ) {
                $block_name = basename( $block_directory );
                if ( file_exists( trailingslashit( $block_directory ) . '/block.json' ) ) {
                    $blocks[] = array(
                        'name'       => $block_name,
                        'textdomain' => $textdomain,
                        'block_path' => $block_base_path,
                    );
                }
            }
        }

        return apply_filters( 'moowoodle_initialize_blocks', $blocks );
    }

    /**
     * Enqueue assets and localize scripts for all registered blocks.
     *
     * @return void
     */
    public function enqueue_blocks_assets() {
        if ( is_admin() && function_exists( 'get_current_screen' ) ) {
            $screen = get_current_screen();
            if ( $screen && $screen->is_block_editor ) {
                FrontendScripts::enqueue_frontend_assets();
                FrontendScripts::enqueue_script( 'moowoodle-vendor' );
                foreach ( $this->get_blocks() as $block_config ) {
                    FrontendScripts::localize_scripts( $block_config['textdomain'] . '-' . $block_config['name'] . '-editor' );
                    FrontendScripts::localize_scripts( $block_config['textdomain'] . '-' . $block_config['name'] );
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
        if ( empty( $post ) || ! $post instanceof \WP_Post ) {
            return;
        }
        FrontendScripts::enqueue_frontend_assets();
        $has_moowoodle_block = false;
        foreach ( $this->get_blocks() as $block_config ) {
            $block_name = $block_config['textdomain'] . '/' . $block_config['name'];
            if ( has_block( $block_name, $post ) ) {
                $has_moowoodle_block = true;
                $handle = $block_config['textdomain'] . '-' . $block_config['name'];
                FrontendScripts::enqueue_script( $handle );
                FrontendScripts::localize_scripts( $handle );
            }
        }

        if ( $has_moowoodle_block ) {
           FrontendScripts::enqueue_script( 'moowoodle-vendor' );
        }
    }

    /**
     * Register all defined blocks.
     *
     * @return void
     */
    public function register_blocks() {
        foreach ( $this->get_blocks() as $block_config ) {
            register_block_type( $block_config['block_path'] . $block_config['name'] );
        }
    }
}
