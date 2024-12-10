<?php
namespace ays\includes\taxonomies;
 //Exit if accessed directly: Check for bugs and past
if(!defined('ABSPATH')){
	exit;
}
/**
 * Class Ays_Taxonomy_Price_Range
 *
 * Registers the 'price_range' taxonomy for the 'At Your Service' plugin.
 *
 * @package AtYourService
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

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
            'name'              => __( 'Price Ranges', 'ays' ),
            'singular_name'     => __( 'Price Range', 'ays' ),
            'search_items'      => __( 'Search Price Ranges', 'ays' ),
            'all_items'         => __( 'All Price Ranges', 'ays' ),
            'parent_item'       => __( 'Parent Price Range', 'ays' ),
            'parent_item_colon' => __( 'Parent Price Range:', 'ays' ),
            'edit_item'         => __( 'Edit Price Range', 'ays' ),
            'update_item'       => __( 'Update Price Range', 'ays' ),
            'add_new_item'      => __( 'Add New Price Range', 'ays' ),
            'new_item_name'     => __( 'New Price Range Name', 'ays' ),
            'menu_name'         => __( 'Price Ranges', 'ays' ),
        );

        $args = array(
            'hierarchical'      => true, // Set to false if you prefer a non-hierarchical taxonomy (like tags)
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'public'            => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'price-range' ),
        );

        register_taxonomy( 'price_range', array( 'service' ), $args );

        /**
         * Captain's Log: 'price_range' taxonomy registered successfully.
         * Ready to categorize services by price.
         */
    }
}

// Initialize the class.
new Ays_Taxonomy_Price_Range();

