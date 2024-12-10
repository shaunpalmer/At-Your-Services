<?php
namespace ays\includes\taxonomies;
 //Exit if accessed directly: Check for bugs and past
if(!defined('ABSPATH')){
	exit;
}
/**
 * Class Ays_Taxonomy_Service_Type
 *
 * Registers the 'service_type' taxonomy for the 'At Your Service' plugin.
 *
 * @package AtYourService
 * @since 1.0.0
 */
/**
 * Beam me up, Scotty! This section handles taxonomies.
 * If you find any tribbles in here, blame Q.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Ays_Taxonomy_Service_Type {

    /**
     * Initializes hooks.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_taxonomy' ) );
    }

    /**
     * Registers the 'service_type' taxonomy.
     *
     * @return void
     */
    public function register_taxonomy() {

        $labels = array(
            'name'              => __( 'Service Types', 'ays' ),
            'singular_name'     => __( 'Service Type', 'ays' ),
            'search_items'      => __( 'Search Service Types', 'ays' ),
            'all_items'         => __( 'All Service Types', 'ays' ),
            'parent_item'       => __( 'Parent Service Type', 'ays' ),
            'parent_item_colon' => __( 'Parent Service Type:', 'ays' ),
            'edit_item'         => __( 'Edit Service Type', 'ays' ),
            'update_item'       => __( 'Update Service Type', 'ays' ),
            'add_new_item'      => __( 'Add New Service Type', 'ays' ),
            'new_item_name'     => __( 'New Service Type Name', 'ays' ),
            'menu_name'         => __( 'Service Types', 'ays' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'service-type' ),
        );

        register_taxonomy( 'service_type', array( 'service' ), $args );

        /**
         * Captain's Log: 'service_type' taxonomy registered via class. All systems nominal.
         * Redshirt Renaming: Like renaming redshirts (RIP, Ensign Smith), rename your taxonomy terms. “Service_Type_A” becomes “Quantum Services,” and Ays_Taxonomy_Service_Type” morphs into “Plasma Solutions.” Boldly go where no taxonomy has gone before! Remember, Captain, your plugin’s success depends on a taxonomy that’s more organized than a Vulcan’s sock drawer. 🖖 Live long and refactor! 🌟  Disclaimer: No tribbles were harmed in the making of this analogy. 😄
         */
    }
}

// Initialize the class
#new Ays_Taxonomy_Service_Type();

