<?php
// Custom Post Type: FAQ


if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Ays_CPT_FAQ {

    public function __construct() {
        add_action( 'init', array( $this, 'register_faq_post_type' ) );

    }

    public function register_faq_post_type() {

    $labels = array(
            'name'                  => __( 'FAQS','ays' ),
            'singular_name'         => __( 'FAQS','ays' ),
            'menu_name'             => __( 'FAQS','ays' ),
            'name_admin_bar'        => __( 'FAQS','ays' ),
            'add_new'               => __( 'Add New', 'ays' ),
            'add_new_item'          => __( 'Add New faq', 'ays' ),
            'new_item'              => __( 'New faq', 'ays' ),
            'edit_item'             => __( 'Edit faq', 'ays' ),
            'view_item'             => __( 'View faq', 'ays' ),
            'all_items'             => __( 'All Faq', 'ays' ),
            'search_items'          => __( 'Search faqs', 'ays' ),
            'parent_item_colon'     => __( 'Parent faq:', 'ays' ),
            'not_found'             => __( 'No faq found.', 'ays' ),
            'not_found_in_trash'    => __( 'No faq found in Trash.', 'ays' ),
            'featured_image'        => __( 'faq Image', 'ays' ),
            'set_featured_image'    => __( 'Set featured image', 'ays' ),
            'remove_featured_image' => __( 'Remove featured image', 'ays' ),
            'use_featured_image'    => __( 'Use as featured image', 'ays' ),
            'archives'              => __( 'faq Archives', 'ays' ),
            'insert_into_item'      => __( 'Insert into faq', 'ays' ),
            'uploaded_to_this_item' => __( 'Uploaded to this faq', 'ays' ),
            'filter_items_list'     => __( 'Filter faq list', 'ays' ),
            'items_list_navigation' => __( 'faq list navigation', 'ays' ),
            'items_list'            => __( 'faq list', 'ays' ),
        );


        $args = array(
            'label'                 => __( 'faq', 'ays' ),
            'labels'                => $labels,
            'description'           => __( 'Different kinds of faq', 'ays' ),
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_rest'          => true, // Enables Gutenberg editor support
            'rest_base'             => 'faq',
            'has_archive'           => false,
            'show_in_menu'          => true, // Ensure it shows in Appearance > Menus
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-paperclip',
            "supports" => array( "title", "editor", "thumbnail", "excerpt", "custom-fields", "revisions", "page-attributes","post-formats" ),
            'taxonomies'            => array( 'category', 'post_tag', 'service_type', 'price_range', 'location' ),
            'rewrite'               => array( 'slug' => 'faq', 'with_front' => true ),
            'hierarchical'          => true,
            'exclude_from_search'   => false,
            'capability_type'       => 'page',
            'map_meta_cap'          => true,
        );

        register_post_type( 'faq', $args );

        /**
         * Captain's Log: 'faq' post type registered. Ready for new missions.
         */
    }
}
// Initialize the class
new Ays_CPT_FAQ();

// While we're venturing through the coding galaxy, it's important to keep our code as clear as possible to avoid any clashes with the "Klingons" of syntax errors. By following best practices and keeping our code well-organized, we'll ensure a smooth voyage through the development universe.

// As Captain Picard would say: "Let's make sure everything is shipshape before we engage."

// If you have any further questions or need assistance with other parts of your plugin, please let me know. I'm here to help ensure your plugin is ready to boldly go where no plugin has gone before! 🖖

