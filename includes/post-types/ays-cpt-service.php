<?php
/**
 * Custom Post Type: Service
 *
 * Registers the 'service' custom post type for the At Your Service plugin.
 *
 * @package AtYourService
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Ays_CPT_Service
 *
 * Handles registration of the Service custom post type.
 */
class Ays_CPT_Service {

    /**
     * Constructor: Hooks into 'init' to register the post type.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
    }

    /**
     * Registers the 'service' custom post type.
     *
     * @return void
     */
    public function register_post_type() {

        $labels = array(
            'name'                  => __( 'Services', 'atyourservice' ),
            'singular_name'         => __( 'Service', 'atyourservice' ),
            'menu_name'             => __( 'Services', 'atyourservice' ),
            'name_admin_bar'        => __( 'Service', 'atyourservice' ),
            'add_new'               => __( 'Add New', 'atyourservice' ),
            'add_new_item'          => __( 'Add New Service', 'atyourservice' ),
            'new_item'              => __( 'New Service', 'atyourservice' ),
            'edit_item'             => __( 'Edit Service', 'atyourservice' ),
            'view_item'             => __( 'View Service', 'atyourservice' ),
            'all_items'             => __( 'All Services', 'atyourservice' ),
            'search_items'          => __( 'Search Services', 'atyourservice' ),
            'parent_item_colon'     => __( 'Parent Service:', 'atyourservice' ),
            'not_found'             => __( 'No services found.', 'atyourservice' ),
            'not_found_in_trash'    => __( 'No services found in Trash.', 'atyourservice' ),
            'featured_image'        => __( 'Service Image', 'atyourservice' ),
            'set_featured_image'    => __( 'Set featured image', 'atyourservice' ),
            'remove_featured_image' => __( 'Remove featured image', 'atyourservice' ),
            'use_featured_image'    => __( 'Use as featured image', 'atyourservice' ),
            'archives'              => __( 'Service Archives', 'atyourservice' ),
            'insert_into_item'      => __( 'Insert into service', 'atyourservice' ),
            'uploaded_to_this_item' => __( 'Uploaded to this service', 'atyourservice' ),
            'filter_items_list'     => __( 'Filter services list', 'atyourservice' ),
            'items_list_navigation' => __( 'Services list navigation', 'atyourservice' ),
            'items_list'            => __( 'Services list', 'atyourservice' ),
        );

        $args = array(
            'label'                 => __( 'Services', 'atyourservice' ),
            'labels'                => $labels,
            'description'           => __( 'Different kinds of services offered by the business.', 'atyourservice' ),
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_rest'          => true,
            'rest_base'             => 'services',
            'has_archive'           => false,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-admin-users',
            'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes', 'post-formats' ),
            'taxonomies'            => array( 'category', 'post_tag', 'service_type', 'price_range', 'location' ),
            'rewrite'               => array( 'slug' => 'services', 'with_front' => true ),
            'hierarchical'          => true,
            'exclude_from_search'   => false,
            'capability_type'       => 'post',
            'map_meta_cap'          => true,
        );

        register_post_type( 'service', $args );
    }
}

// Initialize the class
new Ays_CPT_Service();

