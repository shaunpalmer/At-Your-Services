<?php
// Custom Post Type: Service

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Ays_CPT_Service {

    public function __construct() {
        add_action( 'init', array( $this, 'register_service_post_type' ) );
        // Removed 'register_taxonomies' action
    }

    public function register_service_post_type() {

    $labels = array(
            'name'                  => __( 'Services', 'ays' ),
            'singular_name'         => __( 'Service', 'ays' ),
            'menu_name'             => __( 'Services', 'ays' ),
            'name_admin_bar'        => __( 'Service', 'ays' ),
            'add_new'               => __( 'Add New', 'ays' ),
            'add_new_item'          => __( 'Add New Service', 'ays' ),
            'new_item'              => __( 'New Service', 'ays' ),
            'edit_item'             => __( 'Edit Service', 'ays' ),
            'view_item'             => __( 'View Service', 'ays' ),
            'all_items'             => __( 'All Services', 'ays' ),
            'search_items'          => __( 'Search Services', 'ays' ),
            'parent_item_colon'     => __( 'Parent Service:', 'ays' ),
            'not_found'             => __( 'No services found.', 'ays' ),
            'not_found_in_trash'    => __( 'No services found in Trash.', 'ays' ),
            'featured_image'        => __( 'Service Image', 'ays' ),
            'set_featured_image'    => __( 'Set featured image', 'ays' ),
            'remove_featured_image' => __( 'Remove featured image', 'ays' ),
            'use_featured_image'    => __( 'Use as featured image', 'ays' ),
            'archives'              => __( 'Service Archives', 'ays' ),
            'insert_into_item'      => __( 'Insert into service', 'ays' ),
            'uploaded_to_this_item' => __( 'Uploaded to this service', 'ays' ),
            'filter_items_list'     => __( 'Filter services list', 'ays' ),
            'items_list_navigation' => __( 'Services list navigation', 'ays' ),
            'items_list'            => __( 'Services list', 'ays' ),
        );


        $args = array(
            'label'                 => __( 'Services', 'ays' ),
            'labels'                => $labels,
            'description'           => __( 'Different kinds of services', 'ays' ),
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_rest'          => true,
            'rest_base'             => 'services',
            'has_archive'           => false,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-admin-users',
            "supports" => array( "title", "editor", "thumbnail", "excerpt", "custom-fields", "revisions", "page-attributes","post-formats" ),
            'taxonomies'            => array( 'category', 'post_tag', 'service_type', 'price_range', 'location' ),
            'rewrite'               => array( 'slug' => 'services', 'with_front' => true ),
            'hierarchical'          => true,
            'exclude_from_search'   => false,
            'capability_type'       => 'post',
            'map_meta_cap'          => true,
        );

        register_post_type( 'service', $args );

        /**
         * Captain's Log: 'service' post type registered. Ready for new missions.
         */
    }
}
// Initialize the class
new Ays_CPT_Service();

// While we're venturing through the coding galaxy, it's important to keep our code as clear as possible to avoid any clashes with the "Klingons" of syntax errors. By following best practices and keeping our code well-organized, we'll ensure a smooth voyage through the development universe.

// As Captain Picard would say: "Let's make sure everything is shipshape before we engage."

// If you have any further questions or need assistance with other parts of your plugin, please let me know. I'm here to help ensure your plugin is ready to boldly go where no plugin has gone before! 🖖

