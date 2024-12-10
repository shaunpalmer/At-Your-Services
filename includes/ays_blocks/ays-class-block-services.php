<?php 
namespace ays\includes\ays_blocks;
/**
 * Class Ays_Block_Services
 *
 * Registers the 'service' block type for the 'At Your Service' plugin.
 *
 * @package At Your Service
 * author  Shaun Palmer
 * @since 0.1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
class Ays_Block_Services extends Ays_Block_Base {
    /**
     * Constructor to initialize the service block.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_block' ) );
    }

    /**
     * Registers the service block type.
     */
    public function register_block() {
        // Set the path to the JavaScript file for this block.
        $script_path = plugins_url( '/src/blocks/ays-block-service.js', dirname( __FILE__ ) );

        // Register the block script.
        wp_register_script(
            'ays-block-service-script',
            $script_path,
            array( 'wp-blocks', 'wp-element', 'wp-editor' ),
            filemtime( plugin_dir_path( __FILE__ ) . '../../src/blocks/ays-block-service.js' ),
            true
        );

        // Register the block type.
        register_block_type( 'ays/block-service', array(
            'editor_script' => 'ays-block-service-script',
            'attributes'    => array(
                'serviceName' => array(
                    'type' => 'string',
                    'default' => 'Enter Service Name...'
                ),
                'description' => array(
                    'type' => 'string',
                    'default' => 'Service Description'
                ),
                'serviceImage' => array(
                    'type' => 'string',
                    'default' => ''
                ),
            ),
            'render_callback' => array( $this, 'render_block' ),
        ) );
    }

    /**
     * Render callback function for the service block.
     *
     * @param array $attributes Attributes passed to the block.
     * @return string Rendered block content.
     */
    public function render_block( $attributes ) {
        $service_name = esc_html( $attributes['serviceName'] );
        $service_image = esc_url( $attributes['serviceImage'] );
        $description = esc_html( $attributes['description'] );

        $image_html = $service_image ? "<img src='{$service_image}' alt='{$service_name}' class='ays-service-image' />" : '';

        return "<div class='ays-block-base ays-block-service'>{$image_html}<h3>{$service_name}</h3><p>{$description}</p></div>";
    }
}

// Initialize the service block.
new Ays_Block_Services();