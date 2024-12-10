<?php
namespace ays\includes\Classes;
/** docblocks
 * Registers queuing scripts and styles for the 'At Your Service' plugin.
 *
 * @package At Your Service
 * @author  Shaun Palmer
 * @since 0.1.3
 */
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Define the plugin path if not already defined.
 * AYS_PLUGIN_PATH provides the absolute file system path to the plugin's directory.
 * It is used when we need to access files directly on the server, such as including templates or other PHP files.
 */
if ( ! defined( 'AYS_PLUGIN_PATH' ) ) {
    define( 'AYS_PLUGIN_PATH', wp_normalize_path( plugin_dir_path( __FILE__ ) ) );
}

/**
 * Define the plugin URL if not already defined.
 * AYS_PLUGIN_URL provides the URL to the plugin's directory.
 * It is used when referencing assets that need to be accessible in the browser, such as JavaScript, CSS, or image files.
 */
if ( ! defined( 'AYS_PLUGIN_URL' ) ) {
    define( 'AYS_PLUGIN_URL', wp_normalize_path( plugins_url( '/', __FILE__ ) ) );
}

if (!class_exists('Ays_Enqueue_Scripts')) {
    
    /**
     * Class Ays_Enqueue_Scripts
     * 
     * Handles enqueuing scripts and styles for the block editor.
     */
    class Ays_Enqueue_Scripts {
        /**
         * Constructor to initialize enqueuing scripts and styles.
         */
        public function __construct() {
            add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
        }

        /**
         * Enqueue block editor scripts and styles.
         * 
         * This method registers and enqueues JavaScript and CSS for the block editor, ensuring
         * that the necessary assets are available for use in the WordPress editor.
         */
        public function enqueue_editor_assets() {
            // Register and enqueue the JavaScript for the base block.
            wp_register_script(
                'ays-block-base-script',
                AYS_PLUGIN_URL . 'src/blocks/ays-block-base.js',
                array( 'wp-blocks', 'wp-element', 'wp-editor' ),
                filemtime( AYS_PLUGIN_PATH . 'src/blocks/ays-block-base.js' ),
                true
            );
            wp_enqueue_script( 'ays-block-base-script' );

            // Optional: Register and enqueue styles for the block editor.
            wp_register_style(
                'ays-block-base-style-editor',
                AYS_PLUGIN_URL . AYS_DS . 'assets/css/editor-styles.css',
                array(),
                filemtime( AYS_PLUGIN_PATH . 'assets/css/editor-styles.css' ) 
            );
            wp_enqueue_style( 'ays-block-base-style-editor' );
        }
    }
}

// Initialize the enqueue class.
new Ays_Enqueue_Scripts();