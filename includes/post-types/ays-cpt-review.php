<?php
// Custom Post Type: Review

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Ays_CPT_Review {

    public function __construct() {
        add_action( 'init', array( $this, 'register_Review_post_type' ) );
   
    }

    public function register_Review_post_type() {

    $labels = array(
            'name'                  => __( 'review','ays' ),
            'singular_name'         => __( 'review','ays' ),
            'menu_name'             => __( 'review','ays' ),
            'name_admin_bar'        => __( 'review','ays' ),
            'add_new'               => __( 'Add New', 'ays' ),
            'add_new_item'          => __( 'Add New review', 'ays' ),
            'new_item'              => __( 'New review', 'ays' ),
            'edit_item'             => __( 'Edit review', 'ays' ),
            'view_item'             => __( 'View review', 'ays' ),
            'all_items'             => __( 'All review', 'ays' ),
            'search_items'          => __( 'Search review', 'ays' ),
            'parent_item_colon'     => __( 'Parent review:', 'ays' ),
            'not_found'             => __( 'No review found.', 'ays' ),
            'not_found_in_trash'    => __( 'No review found in Trash.', 'ays' ),
            'featured_image'        => __( 'review Image', 'ays' ),
            'set_featured_image'    => __( 'Set featured image', 'ays' ),
            'remove_featured_image' => __( 'Remove featured image', 'ays' ),
            'use_featured_image'    => __( 'Use as featured image', 'ays' ),
            'archives'              => __( 'review Archives', 'ays' ),
            'insert_into_item'      => __( 'Insert into review', 'ays' ),
            'uploaded_to_this_item' => __( 'Uploaded to this review', 'ays' ),
            'filter_items_list'     => __( 'Filter review list', 'ays' ),
            'items_list_navigation' => __( 'review list navigation', 'ays' ),
            'items_list'            => __( 'review list', 'ays' ),
        );


        $args = array(
            'label'                 => __( 'review', 'ays' ),
            'labels'                => $labels,
            'description'           => __( 'Different kinds of review', 'ays' ),
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_rest'          => true, // Enables Gutenberg editor support
            'rest_base'             => 'review',
            'has_archive'           => false,
            'show_in_menu'          => true, // Ensure it shows in Appearance > Menus
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-star-half',
            "supports" => array( "title", "editor", "thumbnail", "excerpt", "custom-fields", "revisions", "page-attributes","post-formats" ),
            'taxonomies'            => array( 'category', 'post_tag', 'service_type', 'location' ),
            'rewrite'               => array( 'slug' => 'review', 'with_front' => true ),
            'hierarchical'          => true,
            'exclude_from_search'   => false,
            'capability_type'       => 'page',
            'map_meta_cap'          => true,
        );

        register_post_type( 'review', $args );

        /**
         * Captain's Log: 'review' post type registered. Ready for new missions.
         */
    }
}
// Initialize the class
new Ays_CPT_Review();



// While we're venturing through the coding galaxy, it's important to keep our code as clear as possible to avoid any clashes with the "Klingons" of syntax errors. By following best practices and keeping our code well-organized, we'll ensure a smooth voyage through the development universe.

// As Captain Picard would say: "Let's make sure everything is shipshape before we engage."

// If you have any further questions or need assistance with other parts of your plugin, please let me know. I'm here to help ensure your plugin is ready to boldly go where no plugin has gone before! 🖖