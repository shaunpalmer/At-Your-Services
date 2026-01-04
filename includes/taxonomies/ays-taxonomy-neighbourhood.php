<?php
/**
 * Taxonomy: Neighbourhood
 *
 * Registers the 'neighbourhood' taxonomy for the At Your Service plugin.
 *
 * @package AtYourService
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Ays_Taxonomy_Neighbourhood
 *
 * Handles registration of the Neighbourhood taxonomy.
 */
class Ays_Taxonomy_Neighbourhood {

    /**
     * Constructor: Hooks into 'init' to register the taxonomy.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_taxonomy' ) );
    }

    /**
     * Registers the 'neighbourhood' taxonomy.
     *
     * @return void
     */
    public function register_taxonomy() {

        $labels = array(
            'name'                       => __( 'Neighbourhoods', 'atyourservice' ),
            'singular_name'              => __( 'Neighbourhood', 'atyourservice' ),
            'menu_name'                  => __( 'Neighbourhoods', 'atyourservice' ),
            'all_items'                  => __( 'All Neighbourhoods', 'atyourservice' ),
            'parent_item'                => __( 'Parent Neighbourhood', 'atyourservice' ),
            'parent_item_colon'          => __( 'Parent Neighbourhood:', 'atyourservice' ),
            'new_item_name'              => __( 'New Neighbourhood Name', 'atyourservice' ),
            'add_new_item'               => __( 'Add New Neighbourhood', 'atyourservice' ),
            'edit_item'                  => __( 'Edit Neighbourhood', 'atyourservice' ),
            'update_item'                => __( 'Update Neighbourhood', 'atyourservice' ),
            'view_item'                  => __( 'View Neighbourhood', 'atyourservice' ),
            'separate_items_with_commas' => __( 'Separate neighbourhoods with commas', 'atyourservice' ),
            'add_or_remove_items'        => __( 'Add or remove neighbourhoods', 'atyourservice' ),
            'choose_from_most_used'      => __( 'Choose from the most used neighbourhoods', 'atyourservice' ),
            'popular_items'              => __( 'Popular Neighbourhoods', 'atyourservice' ),
            'search_items'               => __( 'Search Neighbourhoods', 'atyourservice' ),
            'not_found'                  => __( 'No neighbourhoods found.', 'atyourservice' ),
            'no_terms'                   => __( 'No neighbourhoods', 'atyourservice' ),
            'items_list'                 => __( 'Neighbourhoods list', 'atyourservice' ),
            'items_list_navigation'      => __( 'Neighbourhoods list navigation', 'atyourservice' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'public'            => true,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => false,
            'rewrite'           => array( 'slug' => 'neighbourhood' ),
        );

        // Associate taxonomy with the 'location' and 'service' custom post types
        register_taxonomy( 'neighbourhood', array( 'location', 'service' ), $args );
    }
}

// Initialize the class
new Ays_Taxonomy_Neighbourhood();
