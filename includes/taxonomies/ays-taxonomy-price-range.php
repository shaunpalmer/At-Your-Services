<?php
/**
 * Taxonomy: Price Range
 *
 * Registers the 'price_range' taxonomy for the At Your Service plugin.
 *
 * @package AtYourService
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Ays_Taxonomy_Price_Range
 *
 * Handles registration of the Price Range taxonomy.
 */
class Ays_Taxonomy_Price_Range {

    /**
     * Constructor: Hooks into 'init' to register the taxonomy.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_taxonomy' ) );
    }

    /**
     * Registers the 'price_range' taxonomy.
     *
     * @return void
     */
    public function register_taxonomy() {
        $labels = array(
            'name'              => __( 'Price Ranges', 'atyourservice' ),
            'singular_name'     => __( 'Price Range', 'atyourservice' ),
            'search_items'      => __( 'Search Price Ranges', 'atyourservice' ),
            'all_items'         => __( 'All Price Ranges', 'atyourservice' ),
            'parent_item'       => __( 'Parent Price Range', 'atyourservice' ),
            'parent_item_colon' => __( 'Parent Price Range:', 'atyourservice' ),
            'edit_item'         => __( 'Edit Price Range', 'atyourservice' ),
            'update_item'       => __( 'Update Price Range', 'atyourservice' ),
            'add_new_item'      => __( 'Add New Price Range', 'atyourservice' ),
            'new_item_name'     => __( 'New Price Range Name', 'atyourservice' ),
            'menu_name'         => __( 'Price Ranges', 'atyourservice' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'public'            => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'price-range' ),
        );

        register_taxonomy( 'price_range', array( 'service' ), $args );
    }
}

// Initialize the class
new Ays_Taxonomy_Price_Range();

