<?php
/**
 * Custom Post Type: Location
 *
 * Registers the 'location' custom post type for the At Your Service plugin.
 *
 * @package AtYourService
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Ays_CPT_Location
 *
 * Handles registration of the Location custom post type.
 */
class Ays_CPT_Location {

    /**
     * Constructor: Hooks into 'init' to register the post type.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
    }

    /**
     * Registers the 'location' custom post type.
     *
     * @return void
     */
    public function register_post_type() {

        $labels = array(
            'name'                  => __( 'Locations', 'atyourservice' ),
            'singular_name'         => __( 'Location', 'atyourservice' ),
            'menu_name'             => __( 'Locations', 'atyourservice' ),
            'name_admin_bar'        => __( 'Location', 'atyourservice' ),
            'add_new'               => __( 'Add New', 'atyourservice' ),
            'add_new_item'          => __( 'Add New Location', 'atyourservice' ),
            'new_item'              => __( 'New Location', 'atyourservice' ),
            'edit_item'             => __( 'Edit Location', 'atyourservice' ),
            'view_item'             => __( 'View Location', 'atyourservice' ),
            'all_items'             => __( 'All Locations', 'atyourservice' ),
            'search_items'          => __( 'Search Locations', 'atyourservice' ),
            'parent_item_colon'     => __( 'Parent Location:', 'atyourservice' ),
            'not_found'             => __( 'No locations found.', 'atyourservice' ),
            'not_found_in_trash'    => __( 'No locations found in Trash.', 'atyourservice' ),
            'featured_image'        => __( 'Location Image', 'atyourservice' ),
            'set_featured_image'    => __( 'Set featured image', 'atyourservice' ),
            'remove_featured_image' => __( 'Remove featured image', 'atyourservice' ),
            'use_featured_image'    => __( 'Use as featured image', 'atyourservice' ),
            'archives'              => __( 'Location Archives', 'atyourservice' ),
            'insert_into_item'      => __( 'Insert into location', 'atyourservice' ),
            'uploaded_to_this_item' => __( 'Uploaded to this location', 'atyourservice' ),
            'filter_items_list'     => __( 'Filter locations list', 'atyourservice' ),
            'items_list_navigation' => __( 'Locations list navigation', 'atyourservice' ),
            'items_list'            => __( 'Locations list', 'atyourservice' ),
        );

        $args = array(
            'label'                 => __( 'Location', 'atyourservice' ),
            'labels'                => $labels,
            'description'           => __( 'Business or service locations.', 'atyourservice' ),
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_rest'          => true,
            'rest_base'             => 'locations',
            'has_archive'           => true,
            'show_in_menu'          => true,
            'menu_position'         => 6,
            'menu_icon'             => 'dashicons-location',
            'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ),
            'taxonomies'            => array( 'category', 'post_tag', 'neighbourhood' ),
            'rewrite'               => array( 'slug' => 'location', 'with_front' => true ),
            'hierarchical'          => false,
            'exclude_from_search'   => false,
            'capability_type'       => 'post',
            'map_meta_cap'          => true,
        );

        register_post_type( 'location', $args );
    }
}

// Initialize the class
new Ays_CPT_Location();

