<?php 
namespace ays\includes\ays_blocks;
#includes\blocks\ays-class-block-base.php
/**
 * Class Ays_Block_Base
 *
 * Registers the base block type for the 'At Your Service' plugin.
 *
 * @package At Your Service
 * @author  Shaun Palmer
 * @since 0.1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

#This is required if remove fatal error will occur
if ( ! defined( 'AYS_PLUGIN_PATH' ) ) {
    define( 'AYS_PLUGIN_PATH', wp_normalize_path( plugin_dir_path( __FILE__ ) ) );
} 

class Ays_Block_Base {
    /**
     * Constructor to initialize the base block.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_block' ) );
    }

    /**
     * Registers the base block type.
     */
    public function register_block() {
        // Set the path to the JavaScript file for this block.
        $script_path = plugins_url( '/src/blocks/ays-block-base.js', dirname( __FILE__ ) );

        // Register the block script.
        wp_register_script(
            'ays-block-base-script',
            $script_path,
            array( 'wp-blocks', 'wp-element', 'wp-editor' ),
            filemtime( plugin_dir_path( __FILE__ ) . '../../src/blocks/ays-block-base.js' ),
            true
        );

        // Register the block type.
        register_block_type( 'ays/block-base', array(
            'editor_script' => 'ays-block-base-script',
            'attributes'    => array(), // Define default attributes if needed.
            'render_callback' => array( $this, 'render_block' ),
        ) );
    }

    /**
     * Render callback function for the base block.
     *
     * @param array $attributes Attributes passed to the block.
     * @return string Rendered block content.
     */
    public function render_block( $attributes ) {
        // Default content for the base block.
        return '<div class="ays-block-base">Base Block Content</div>';
    }
}

// Initialize the base block.
new Ays_Block_Base();