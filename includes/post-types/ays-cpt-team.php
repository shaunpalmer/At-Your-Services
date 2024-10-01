<?php
/**
 * Class Ays_CPT_Team
 *
 * Registers the 'team' custom post type for the 'At Your Service' plugin.
 *
 * @package AtYourService
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Ays_CPT_Team {

    /**
     * Constructor: Hooks into 'init' to register the post type.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'ays_register_team_post_type' ) );
    }

    /**
     * Registers the 'team' custom post type.
     *
     * @return void
     */
    public function ays_register_team_post_type() {
        $labels = array(
            'name'                  => __( 'Teams', 'ays' ),
            'singular_name'         => __( 'Team', 'ays' ),
            'menu_name'             => __( 'Teams', 'ays' ),
            'name_admin_bar'        => __( 'Team', 'ays' ),
            'add_new'               => __( 'Add New', 'ays' ),
            'add_new_item'          => __( 'Add New Team', 'ays' ),
            'new_item'              => __( 'New Team', 'ays' ),
            'edit_item'             => __( 'Edit Team', 'ays' ),
            'view_item'             => __( 'View Team', 'ays' ),
            'all_items'             => __( 'All Teams', 'ays' ),
            'search_items'          => __( 'Search Teams', 'ays' ),
            'parent_item_colon'     => __( 'Parent Team:', 'ays' ),
            'not_found'             => __( 'No teams found.', 'ays' ),
            'not_found_in_trash'    => __( 'No teams found in Trash.', 'ays' ),
            'featured_image'        => __( 'Team Image', 'ays' ),
            'set_featured_image'    => __( 'Set featured image', 'ays' ),
            'remove_featured_image' => __( 'Remove featured image', 'ays' ),
            'use_featured_image'    => __( 'Use as featured image', 'ays' ),
            'archives'              => __( 'Team Archives', 'ays' ),
            'insert_into_item'      => __( 'Insert into team', 'ays' ),
            'uploaded_to_this_item' => __( 'Uploaded to this team', 'ays' ),
            'filter_items_list'     => __( 'Filter teams list', 'ays' ),
            'items_list_navigation' => __( 'Teams list navigation', 'ays' ),
            'items_list'            => __( 'Teams list', 'ays' ),
            'attributes'            => __( 'Team Attributes', 'ays' ),
        );

        $args = array(
            'label'                 => __( 'Teams', 'ays' ),
            'labels'                => $labels,
            'description'           => __( 'Different teams in our organization', 'ays' ),
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_rest'          => true,
            'rest_base'             => 'teams',
            'has_archive'           => false,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-groups',
            'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions' ),
            'taxonomies'            => array( 'category', 'post_tag', 'team_type', 'price_range', 'location' ),
            'rewrite'               => array( 'slug' => 'teams', 'with_front' => true ),
            'hierarchical'          => false,
            'exclude_from_search'   => false,
            'capability_type'       => 'post',
            'map_meta_cap'          => true,
            'show_in_nav_menus'     => true,
            'show_in_admin_bar'     => true,
            'can_export'            => true,
            'delete_with_user'      => false,
            'query_var'             => true,
            '_builtin'              => false,
            'show_in_quick_edit'    => true,
        );

        register_post_type( 'team', $args );

        /**
         * Captain's Log: 'team' post type registered successfully.
         * Our crew is ready for their missions.
         */
    }
}

// Initialize the class
new Ays_CPT_Team();

// Why do programmers prefer dark mode?
// Because light attracts bugs! 🐛💻

