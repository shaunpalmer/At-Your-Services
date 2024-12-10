<?php
namespace ays\includes\taxonomies;
 //Exit if accessed directly: Check for bugs and past
if(!defined('ABSPATH')){
	exit;
}
/**
 * Taxonomy: Department Type
 *
 * Registers the "department_type" taxonomy for the "team" custom post type.
 *
 * @package AtYourService
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Ays_Taxonomy_Department_Type {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_department_type_taxonomy' ) );
    }

    /**
     * Registers the "department_type" taxonomy.
     */
    public function register_department_type_taxonomy() {

        $labels = array(
            'name'                       => __( 'Department Types', 'ays' ),
            'singular_name'              => __( 'Department Type', 'ays' ),
            'menu_name'                  => __( 'Department Types', 'ays' ),
            'all_items'                  => __( 'All Department Types', 'ays' ),
            'parent_item'                => __( 'Parent Department Type', 'ays' ),
            'parent_item_colon'          => __( 'Parent Department Type:', 'ays' ),
            'new_item_name'              => __( 'New Department Type Name', 'ays' ),
            'add_new_item'               => __( 'Add New Department Type', 'ays' ),
            'edit_item'                  => __( 'Edit Department Type', 'ays' ),
            'update_item'                => __( 'Update Department Type', 'ays' ),
            'view_item'                  => __( 'View Department Type', 'ays' ),
            'separate_items_with_commas' => __( 'Separate Department Types with commas', 'ays' ),
            'add_or_remove_items'        => __( 'Add or remove Department Types', 'ays' ),
            'choose_from_most_used'      => __( 'Choose from the most used Department Types', 'ays' ),
            'popular_items'              => __( 'Popular Department Types', 'ays' ),
            'search_items'               => __( 'Search Department Types', 'ays' ),
            'not_found'                  => __( 'No Department Types found.', 'ays' ),
            'no_terms'                   => __( 'No Department Types', 'ays' ),
            'items_list'                 => __( 'Department Types list', 'ays' ),
            'items_list_navigation'      => __( 'Department Types list navigation', 'ays' ),
        );

        $args = array(
            'hierarchical'      => true, // Make it hierarchical if needed
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true, // Enables Gutenberg editor support
            'public'            => true,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => false,
            'rewrite'           => array( 'slug' => 'department-type' ),
        );

        register_taxonomy( 'department_type', array( 'team' ), $args );

        /**
         * Captain's Log: 'department_type' taxonomy registered successfully.
         * Our crew is ready to classify themselves by department type.
         */
    }
}

// Initialize the class.
new Ays_Taxonomy_DepartmentType();