<?php
/**
 * Custom Post Type: Review
 *
 * Registers the 'review' custom post type for the At Your Service plugin.
 *
 * @package AtYourService
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Ays_CPT_Review
 *
 * Handles registration of the Review custom post type.
 */
class Ays_CPT_Review {

    /**
     * Constructor: Hooks into 'init' to register the post type.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
    }

    /**
     * Registers the 'review' custom post type.
     *
     * @return void
     */
    public function register_post_type() {

        $labels = array(
            'name'                  => __( 'Reviews', 'atyourservice' ),
            'singular_name'         => __( 'Review', 'atyourservice' ),
            'menu_name'             => __( 'Reviews', 'atyourservice' ),
            'name_admin_bar'        => __( 'Review', 'atyourservice' ),
            'add_new'               => __( 'Add New', 'atyourservice' ),
            'add_new_item'          => __( 'Add New Review', 'atyourservice' ),
            'new_item'              => __( 'New Review', 'atyourservice' ),
            'edit_item'             => __( 'Edit Review', 'atyourservice' ),
            'view_item'             => __( 'View Review', 'atyourservice' ),
            'all_items'             => __( 'All Reviews', 'atyourservice' ),
            'search_items'          => __( 'Search Reviews', 'atyourservice' ),
            'parent_item_colon'     => __( 'Parent Review:', 'atyourservice' ),
            'not_found'             => __( 'No reviews found.', 'atyourservice' ),
            'not_found_in_trash'    => __( 'No reviews found in Trash.', 'atyourservice' ),
            'featured_image'        => __( 'Review Image', 'atyourservice' ),
            'set_featured_image'    => __( 'Set featured image', 'atyourservice' ),
            'remove_featured_image' => __( 'Remove featured image', 'atyourservice' ),
            'use_featured_image'    => __( 'Use as featured image', 'atyourservice' ),
            'archives'              => __( 'Review Archives', 'atyourservice' ),
            'insert_into_item'      => __( 'Insert into review', 'atyourservice' ),
            'uploaded_to_this_item' => __( 'Uploaded to this review', 'atyourservice' ),
            'filter_items_list'     => __( 'Filter reviews list', 'atyourservice' ),
            'items_list_navigation' => __( 'Reviews list navigation', 'atyourservice' ),
            'items_list'            => __( 'Reviews list', 'atyourservice' ),
        );

        $args = array(
            'label'                 => __( 'Review', 'atyourservice' ),
            'labels'                => $labels,
            'description'           => __( 'Customer reviews and testimonials.', 'atyourservice' ),
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_rest'          => true,
            'rest_base'             => 'review',
            'has_archive'           => false,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-star-filled',
            'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes', 'post-formats' ),
            'taxonomies'            => array( 'category', 'post_tag', 'service_type', 'location' ),
            'rewrite'               => array( 'slug' => 'review', 'with_front' => true ),
            'hierarchical'          => true,
            'exclude_from_search'   => false,
            'capability_type'       => 'page',
            'map_meta_cap'          => true,
        );

        register_post_type( 'review', $args );
    }
}

// Initialize the class
new Ays_CPT_Review();
