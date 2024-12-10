<?php 
namespace ays\includes\ays_blocks;
/**
 * Class Ays_Block_Team
 *
 * Registers the 'team' block type for the 'At Your Service' plugin.
 *
 * @package At Your Service
 * @author  Shaun Palmer
 * @since 0.1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
// Team block content, added from prior requirements.
class Ays_Block_Team extends Ays_Block_Base {
    /**
     * Constructor to initialize the team block.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_block' ) );
    }

    /**
     * Registers the team block type.
     */
    public function register_block() {
        // Set the path to the JavaScript file for this block.
        $script_path = plugins_url( '/src/blocks/ays-block-team.js', dirname( __FILE__ ) );

        // Register the block script.
        wp_register_script(
            'ays-block-team-script',
            $script_path,
            array( 'wp-blocks', 'wp-element', 'wp-editor' ),
            filemtime( plugin_dir_path( __FILE__ ) . '../../src/blocks/ays-block-team.js' ),
            true
        );

        // Register the block type.
        register_block_type( 'ays/block-team', array(
            'editor_script' => 'ays-block-team-script',
            'attributes'    => array(
                'teamMemberName' => array(
                    'type' => 'string',
                    'default' => 'Enter Team Member Name...'
                ),
                'role' => array(
                    'type' => 'string',
                    'default' => 'Role Description'
                ),
                'teamMemberImage' => array(
                    'type' => 'string',
                    'default' => ''
                ),
                'description' => array(
                    'type' => 'string',
                    'default' => 'Team Member Description'
                ),
            ),
            'render_callback' => array( $this, 'render_block' ),
        ) );
    }

    /**
     * Render callback function for the team block.
     *
     * @param array $attributes Attributes passed to the block.
     * @return string Rendered block content.
     */
    public function render_block( $attributes ) {
        $team_member_name = esc_html( $attributes['teamMemberName'] );
        $role = esc_html( $attributes['role'] );
        $team_member_image = esc_url( $attributes['teamMemberImage'] );
        $description = esc_html( $attributes['description'] );

        $image_html = $team_member_image ? "<img src='{$team_member_image}' alt='{$team_member_name}' class='ays-team-member-image' />" : '';

        return "<div class='ays-block-base ays-block-team'>{$image_html}<h3>{$team_member_name}</h3><p><strong>Role:</strong> {$role}</p><p>{$description}</p></div>";
    }
}

// Initialize the team block.
new Ays_Block_Team();