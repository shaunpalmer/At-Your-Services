<?php 
namespace ays\includes\ays_blocks;
/**
 * Class Ays_Block_FAQ
 *
 * Registers the 'faq' block type for the 'At Your Service' plugin.
 *
 * @package At Your Service
 * @author  Shaun Palmer
 * @since 0.1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Ays_Block_FAQ extends Ays_Block_Base {
    /**
     * Constructor to initialize the FAQ block.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_block' ) );
    }

    /**
     * Registers the FAQ block type.
     */
    public function register_block() {
        // Set the path to the JavaScript file for this block.
        $script_path = plugins_url( '/src/blocks/ays-block-faq.js', dirname( __FILE__ ) );

        // Register the block script.
        wp_register_script(
            'ays-block-faq-script',
            $script_path,
            array( 'wp-blocks', 'wp-element', 'wp-editor' ),
            filemtime( plugin_dir_path( __FILE__ ) . '../../src/blocks/ays-block-faq.js' ),
            true
        );

        // Register the block type.
        register_block_type( 'ays/block-faq', array(
            'editor_script' => 'ays-block-faq-script',
            'attributes'    => array(
                'question' => array(
                    'type' => 'string',
                    'default' => 'Enter FAQ Question...'
                ),
                'answer' => array(
                    'type' => 'string',
                    'default' => 'Enter FAQ Answer...'
                ),
            ),
            'render_callback' => array( $this, 'render_block' ),
        ) );
    }

    /**
     * Render callback function for the FAQ block.
     *
     * @param array $attributes Attributes passed to the block.
     * @return string Rendered block content.
     */
    public function render_block( $attributes ) {
        $question = esc_html( $attributes['question'] );
        $answer = esc_html( $attributes['answer'] );

        return "<div class='ays-block-faq'><h4>{$question}</h4><p>{$answer}</p></div>";
    }
}

// Initialize the FAQ block.
new Ays_Block_FAQ();