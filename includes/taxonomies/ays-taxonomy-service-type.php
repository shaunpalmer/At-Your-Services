<?php
/**
 * Taxonomy: Service Type
 *
 * Registers the 'service_type' taxonomy for the At Your Service plugin.
 *
 * @package AtYourService
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Ays_Taxonomy_Service_Type
 *
 * Handles registration of the Service Type taxonomy.
 */
class Ays_Taxonomy_Service_Type {

    /**
     * Constructor: Hooks into 'init' to register the taxonomy.
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
            'name'              => __( 'Service Types', 'atyourservice' ),
            'singular_name'     => __( 'Service Type', 'atyourservice' ),
            'search_items'      => __( 'Search Service Types', 'atyourservice' ),
            'all_items'         => __( 'All Service Types', 'atyourservice' ),
            'parent_item'       => __( 'Parent Service Type', 'atyourservice' ),
            'parent_item_colon' => __( 'Parent Service Type:', 'atyourservice' ),
            'edit_item'         => __( 'Edit Service Type', 'atyourservice' ),
            'update_item'       => __( 'Update Service Type', 'atyourservice' ),
            'add_new_item'      => __( 'Add New Service Type', 'atyourservice' ),
            'new_item_name'     => __( 'New Service Type Name', 'atyourservice' ),
            'menu_name'         => __( 'Service Types', 'atyourservice' ),
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
    }
}

// Initialize the class
new Ays_Taxonomy_Service_Type();
