<?php
namespace ays\includes\posttypes;
 //Exit if accessed directly: Check for bugs and past
if(!defined('ABSPATH')){
	exit;
}
/**
 * Class Ays_CPT_Team
 *
 * Registers the 'team' custom post type for the 'At Your Service' plugin.
 *
 * @package AtYourService
 * @since 1.0.0
 */


class Ays_CPT_Team {

    /**
     * Constructor: Hooks into 'init' to register the post type.
     */
    public function __construct() {
    add_action( 'init', array( $this, 'ays_register_team_post_type' ) );
    }

    /**
     * Registers the 'team' custom post type.
     *
     * @return void
     */
    public function ays_register_team_post_type() {
        $labels = array(
            'name'                  => __( 'Teams', 'ays' ),
            'singular_name'         => __( 'Team', 'ays' ),
            'menu_name'             => __( 'Teams', 'ays' ),
            'name_admin_bar'        => __( 'Team', 'ays' ),
            'add_new'               => __( 'Add New', 'ays' ),
            'add_new_item'          => __( 'Add New Team', 'ays' ),
            'new_item'              => __( 'New Team', 'ays' ),
            'edit_item'             => __( 'Edit Team', 'ays' ),
            'view_item'             => __( 'View Team', 'ays' ),
            'all_items'             => __( 'All Teams', 'ays' ),
            'search_items'          => __( 'Search Teams', 'ays' ),
            'parent_item_colon'     => __( 'Parent Team:', 'ays' ),
            'not_found'             => __( 'No teams found.', 'ays' ),
            'not_found_in_trash'    => __( 'No teams found in Trash.', 'ays' ),
            'featured_image'        => __( 'Team Image', 'ays' ),
            'set_featured_image'    => __( 'Set featured image', 'ays' ),
            'remove_featured_image' => __( 'Remove featured image', 'ays' ),
            'use_featured_image'    => __( 'Use as featured image', 'ays' ),
            'archives'              => __( 'Team Archives', 'ays' ),
            'insert_into_item'      => __( 'Insert into team', 'ays' ),
            'uploaded_to_this_item' => __( 'Uploaded to this team', 'ays' ),
            'filter_items_list'     => __( 'Filter teams list', 'ays' ),
            'items_list_navigation' => __( 'Teams list navigation', 'ays' ),
            'items_list'            => __( 'Teams list', 'ays' ),
            'attributes'            => __( 'Team Attributes', 'ays' ),
        );

        $args = array(
            'label'                 => __( 'Teams', 'ays' ),
            'labels'                => $labels,
            'description'           => __( 'Different teams in our organization', 'ays' ),
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_rest'          => true,
            'rest_base'             => 'teams',
            'has_archive'           => false,
            'show_in_menu'          => true, // Shows in admin menu
            'show_in_nav_menus'     => true, // Available in Appearance > Menus > menu area
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-groups',
            'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions' ),
            'taxonomies'            => array( 'department', 'team_type', 'post_tag','location','category' ),
            'rewrite'               => array( 'slug' => 'teams', 'with_front' => true ),
            'hierarchical'          => false,
            'exclude_from_search'   => false,
            'capability_type'       => 'post',
            'map_meta_cap'          => true,
            'show_in_admin_bar'     => true,
            'can_export'            => true,
            'delete_with_user'      => false,
            'query_var'             => true,
            '_builtin'              => false,
            'show_in_quick_edit'    => true,
        );

        register_post_type( 'team', $args );

        /**
         * Captain's Log: 'team' post type registered successfully.
         * Our crew is ready for their missions.
         */
    }
}

// Initialize the class
new Ays_CPT_Team();

// Why do programmers prefer dark mode?
// Because light attracts bugs! 🐛💻

# Build a department taxonomy  ays-taxonomy-department-type.php

class Ays_Taxonomy_Department {

    /**
     * Initializes hooks.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_taxonomy' ) );
    }

    /**
     * Registers the 'department' taxonomy.
     *
     * @return void
     */
    public function register_taxonomy() {

        $labels = array(
            'name'              => __( 'Departments', 'ays' ),
            'singular_name'     => __( 'Department', 'ays' ),
            'search_items'      => __( 'Search Departments', 'ays' ),
            'all_items'         => __( 'All Departments', 'ays' ),
            'parent_item'       => __( 'Parent Department', 'ays' ),
            'parent_item_colon' => __( 'Parent Department:', 'ays' ),
            'edit_item'         => __( 'Edit Department', 'ays' ),
            'update_item'       => __( 'Update Department', 'ays' ),
            'add_new_item'      => __( 'Add New Department', 'ays' ),
            'new_item_name'     => __( 'New Department Name', 'ays' ),
            'menu_name'         => __( 'Departments', 'ays' ),
        );

        $args = array(
            'hierarchical'      => true, // Set to 'true' for category-like taxonomy
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'department' ),
        );

        register_taxonomy( 'department', array( 'team' ), $args );
    }
}

// Initialize the class
new Ays_Taxonomy_Department();

// The purpose of the 'department' taxonomy is to categorize your teams based on the departments they belong to within your organization. So if a team works in Marketing, you would assign the 'Marketing' department to that team. If another team works in Engineering, you might assign them to the 'Engineering' or 'Construction' department, depending on how your organization is structured.

// Here's how it works:

// Create Departments:

// In your WordPress admin dashboard, go to Teams > Departments.
// Add departments like Marketing, Engineering, Construction, etc.
// Assign Departments to Teams:

// When you create or edit a Team (custom post type), you'll see a Departments meta box on the editor screen.
// You can select one or multiple departments that the team belongs to.
// Benefits:

// Organization: Categorizing teams by departments helps keep your content organized.
// Filtering and Displaying: You can easily display teams from a specific department on the front end of your site. For example, you might have a page that lists all teams in the Marketing department.
// Hierarchical Structure: Since the taxonomy is hierarchical, you can have parent and child departments. For example, a parent department Engineering with child departments like Software Engineering, Civil Engineering, etc.