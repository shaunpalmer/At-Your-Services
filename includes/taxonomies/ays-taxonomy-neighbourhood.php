<?php
/**
 * Taxonomy: Neighbourhood
 * 
 * Registers the "Neighbourhood" taxonomy for the "Location" and "Service" custom post types.
 *
 * @package AtYourService
 * @since 1.0.0
 */

/**
 * Beam me up, Scotty! This section handles the Neighbourhood taxonomy.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Ays_Taxonomy_Neighbourhood {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_neighbourhood_taxonomy' ) );
    }

    /**
     * Registers the "neighbourhood" taxonomy.
     */
    public function register_neighbourhood_taxonomy() {

        $labels = array(
            'name'                       => __( 'Neighbourhoods', 'ays' ),
            'singular_name'              => __( 'Neighbourhood', 'ays' ),
            'menu_name'                  => __( 'Neighbourhoods', 'ays' ),
            'all_items'                  => __( 'All Neighbourhoods', 'ays' ),
            'parent_item'                => __( 'Parent Neighbourhood', 'ays' ),
            'parent_item_colon'          => __( 'Parent Neighbourhood:', 'ays' ),
            'new_item_name'              => __( 'New Neighbourhood Name', 'ays' ),
            'add_new_item'               => __( 'Add New Neighbourhood', 'ays' ),
            'edit_item'                  => __( 'Edit Neighbourhood', 'ays' ),
            'update_item'                => __( 'Update Neighbourhood', 'ays' ),
            'view_item'                  => __( 'View Neighbourhood', 'ays' ),
            'separate_items_with_commas' => __( 'Separate neighbourhoods with commas', 'ays' ),
            'add_or_remove_items'        => __( 'Add or remove neighbourhoods', 'ays' ),
            'choose_from_most_used'      => __( 'Choose from the most used neighbourhoods', 'ays' ),
            'popular_items'              => __( 'Popular Neighbourhoods', 'ays' ),
            'search_items'               => __( 'Search Neighbourhoods', 'ays' ),
            'not_found'                  => __( 'No neighbourhoods found.', 'ays' ),
            'no_terms'                   => __( 'No neighbourhoods', 'ays' ),
            'items_list'                 => __( 'Neighbourhoods list', 'ays' ),
            'items_list_navigation'      => __( 'Neighbourhoods list navigation', 'ays' ),
        );

        $args = array(
            'hierarchical'      => true, // Like categories
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true, // Enables Gutenberg editor support
            'public'            => true,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => false,
            'rewrite'           => array( 'slug' => 'neighbourhood' ),
        );

        // Associate taxonomy with the 'location' and 'service' custom post types
        register_taxonomy( 'neighbourhood', array( 'location', 'service' ), $args );

        /**
         * Captain's Log: 'Neighbourhood' taxonomy registered via class. All systems nominal.
         * the area of a town that surrounds someone's home, or the people who live in this area
         */
    }
}

// Initialize the class.
new Ays_Taxonomy_Neighbourhood();
