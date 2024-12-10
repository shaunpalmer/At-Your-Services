<?php
namespace ays\includes\posttypes;
 //Exit if accessed directly: Check for bugs and past
if(!defined('ABSPATH')){
	exit;
}
/**
 * Custom Post Type: Location
 *
 * Registers the "Location" custom post type.
 *
 * @package AtYourService
 * @since 1.0.0
 */

/**
 * Beam me up, Scotty! This section handles the Location custom post type.
 * If you find any tribbles in here, blame Q.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Ays_CPT_Location {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_location_post_type' ) );
    }

    /**
     * Registers the "location" custom post type.
     */
    public function register_location_post_type() {

        $labels = array(
            'name'                  => __( 'Locations', 'ays' ),
            'singular_name'         => __( 'Location', 'ays' ),
            'menu_name'             => __( 'Locations', 'ays' ),
            'name_admin_bar'        => __( 'Location', 'ays' ),
            'add_new'               => __( 'Add New', 'ays' ),
            'add_new_item'          => __( 'Add New Location', 'ays' ),
            'new_item'              => __( 'New Location', 'ays' ),
            'edit_item'             => __( 'Edit Location', 'ays' ),
            'view_item'             => __( 'View Location', 'ays' ),
            'all_items'             => __( 'All Locations', 'ays' ),
            'search_items'          => __( 'Search Locations', 'ays' ),
            'parent_item_colon'     => __( 'Parent Location:', 'ays' ),
            'not_found'             => __( 'No locations found.', 'ays' ),
            'not_found_in_trash'    => __( 'No locations found in Trash.', 'ays' ),
            'featured_image'        => __( 'Location Image', 'ays' ),
            'set_featured_image'    => __( 'Set featured image', 'ays' ),
            'remove_featured_image' => __( 'Remove featured image', 'ays' ),
            'use_featured_image'    => __( 'Use as featured image', 'ays' ),
            'archives'              => __( 'Location Archives', 'ays' ),
            'insert_into_item'      => __( 'Insert into location', 'ays' ),
            'uploaded_to_this_item' => __( 'Uploaded to this location', 'ays' ),
            'filter_items_list'     => __( 'Filter locations list', 'ays' ),
            'items_list_navigation' => __( 'Locations list navigation', 'ays' ),
            'items_list'            => __( 'Locations list', 'ays' ),
        );

        $args = array(
            'label'                 => __( 'Location', 'ays' ),
            'labels'                => $labels,
            'description'           => __( 'Business or service locations.', 'ays' ),
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_rest'          => true, // Enables Gutenberg editor support
            'rest_base'             => 'locations',
            'has_archive'           => true, // Enable archive page
            'show_in_menu'          => true, // Ensures it shows in the admin menu
            'menu_position'         => 6, // Position after Reviews (which is 5)
            'menu_icon'             => 'dashicons-location', // Dashicon for location
            'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes' ),
            'taxonomies'            => array( 'category', 'post_tag','neighbourhood' ), // Add taxonomies if needed
            'rewrite'               => array( 'slug' => 'location', 'with_front' => true ),
            'hierarchical'          => false, // Typically false for CPTs like locations
            'exclude_from_search'   => false,
            'capability_type'       => 'post', // Use 'post' capabilities
            'map_meta_cap'          => true,
            'show_in_nav_menus'     => true, // Show administration menu area
        );

        register_post_type( 'location', $args );

        /**
         * Captain's Log: 'Location' post type registered. Ready for new missions.
         */
    }
}

// Initialize the class.
new Ays_CPT_Location();