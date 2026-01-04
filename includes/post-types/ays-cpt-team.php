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
        add_action( 'init', array( $this, 'register_post_type' ) );
    }

    /**
     * Registers the 'team' custom post type.
     *
     * @return void
     */
    public function register_post_type() {
        $labels = array(
            'name'                  => __( 'Teams', 'atyourservice' ),
            'singular_name'         => __( 'Team', 'atyourservice' ),
            'menu_name'             => __( 'Teams', 'atyourservice' ),
            'name_admin_bar'        => __( 'Team', 'atyourservice' ),
            'add_new'               => __( 'Add New', 'atyourservice' ),
            'add_new_item'          => __( 'Add New Team', 'atyourservice' ),
            'new_item'              => __( 'New Team', 'atyourservice' ),
            'edit_item'             => __( 'Edit Team', 'atyourservice' ),
            'view_item'             => __( 'View Team', 'atyourservice' ),
            'all_items'             => __( 'All Teams', 'atyourservice' ),
            'search_items'          => __( 'Search Teams', 'atyourservice' ),
            'parent_item_colon'     => __( 'Parent Team:', 'atyourservice' ),
            'not_found'             => __( 'No teams found.', 'atyourservice' ),
            'not_found_in_trash'    => __( 'No teams found in Trash.', 'atyourservice' ),
            'featured_image'        => __( 'Team Image', 'atyourservice' ),
            'set_featured_image'    => __( 'Set featured image', 'atyourservice' ),
            'remove_featured_image' => __( 'Remove featured image', 'atyourservice' ),
            'use_featured_image'    => __( 'Use as featured image', 'atyourservice' ),
            'archives'              => __( 'Team Archives', 'atyourservice' ),
            'insert_into_item'      => __( 'Insert into team', 'atyourservice' ),
            'uploaded_to_this_item' => __( 'Uploaded to this team', 'atyourservice' ),
            'filter_items_list'     => __( 'Filter teams list', 'atyourservice' ),
            'items_list_navigation' => __( 'Teams list navigation', 'atyourservice' ),
            'items_list'            => __( 'Teams list', 'atyourservice' ),
            'attributes'            => __( 'Team Attributes', 'atyourservice' ),
        );

        $args = array(
            'label'                 => __( 'Teams', 'atyourservice' ),
            'labels'                => $labels,
            'description'           => __( 'Teams within the organization.', 'atyourservice' ),
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
    }
}

// Initialize the class
new Ays_CPT_Team();

