<?php
/**
 * Custom Post Type: FAQ
 *
 * Registers the 'faq' custom post type for the At Your Service plugin.
 *
 * @package AtYourService
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Ays_CPT_FAQ
 *
 * Handles registration of the FAQ custom post type.
 */
class Ays_CPT_FAQ {

    /**
     * Constructor: Hooks into 'init' to register the post type.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
    }

    /**
     * Registers the 'faq' custom post type.
     *
     * @return void
     */
    public function register_post_type() {

        $labels = array(
            'name'                  => __( 'FAQs', 'atyourservice' ),
            'singular_name'         => __( 'FAQ', 'atyourservice' ),
            'menu_name'             => __( 'FAQs', 'atyourservice' ),
            'name_admin_bar'        => __( 'FAQ', 'atyourservice' ),
            'add_new'               => __( 'Add New', 'atyourservice' ),
            'add_new_item'          => __( 'Add New FAQ', 'atyourservice' ),
            'new_item'              => __( 'New FAQ', 'atyourservice' ),
            'edit_item'             => __( 'Edit FAQ', 'atyourservice' ),
            'view_item'             => __( 'View FAQ', 'atyourservice' ),
            'all_items'             => __( 'All FAQs', 'atyourservice' ),
            'search_items'          => __( 'Search FAQs', 'atyourservice' ),
            'parent_item_colon'     => __( 'Parent FAQ:', 'atyourservice' ),
            'not_found'             => __( 'No FAQs found.', 'atyourservice' ),
            'not_found_in_trash'    => __( 'No FAQs found in Trash.', 'atyourservice' ),
            'featured_image'        => __( 'FAQ Image', 'atyourservice' ),
            'set_featured_image'    => __( 'Set featured image', 'atyourservice' ),
            'remove_featured_image' => __( 'Remove featured image', 'atyourservice' ),
            'use_featured_image'    => __( 'Use as featured image', 'atyourservice' ),
            'archives'              => __( 'FAQ Archives', 'atyourservice' ),
            'insert_into_item'      => __( 'Insert into FAQ', 'atyourservice' ),
            'uploaded_to_this_item' => __( 'Uploaded to this FAQ', 'atyourservice' ),
            'filter_items_list'     => __( 'Filter FAQs list', 'atyourservice' ),
            'items_list_navigation' => __( 'FAQs list navigation', 'atyourservice' ),
            'items_list'            => __( 'FAQs list', 'atyourservice' ),
        );

        $args = array(
            'label'                 => __( 'FAQ', 'atyourservice' ),
            'labels'                => $labels,
            'description'           => __( 'Frequently Asked Questions related to services.', 'atyourservice' ),
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_rest'          => true,
            'rest_base'             => 'faq',
            'has_archive'           => false,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-editor-help',
            'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes', 'post-formats' ),
            'taxonomies'            => array( 'category', 'post_tag', 'service_type', 'location' ),
            'rewrite'               => array( 'slug' => 'faq', 'with_front' => true ),
            'hierarchical'          => true,
            'exclude_from_search'   => false,
            'capability_type'       => 'page',
            'map_meta_cap'          => true,
        );

        register_post_type( 'faq', $args );
    }
}

// Initialize the class
new Ays_CPT_FAQ();

