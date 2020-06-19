 <?php
/*
Plugin Name: At Your Services
Plugin URI:https://Project-studios.co.nz
Description:Services Custom Post Type & taxonomies that adds custom post types
Version: 0.1.6
Author: shaun palmer
Author URI: shaun@roject-studios.co.nz
Text Domain: http://supercleanchchnz.com
*Tested up to: 5.1
 * License: GPL v3 or later - http://www.gnu.org/licenses/gpl-3.0.html
*/
/*  Copyright 2018  SHAUN PAMLER  (email : shaun.palmer@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
//Exit if accessed directly
if(!defined('ABSPATH')){
  exit; // Exit if accessed directly
	return;
}

function SP_PS_register_AtYourServices() {


	 /* Post Type: Services.
	 that adds custom post types
	 */

	$labels = array(
		"name" => __( "Services", "" ),
		"singular_name" => __( "Service", "" ),
		"menu_name" => __( "Services", "" ),
		"all_items" => __( "All Services", "" ),
		"add_new" => __( "Add New types of Services", "" ),
		"add_new_item" => __( "Add New types of Services", "" ),
		"edit_item" => __( "Edit  Services", "" ),
		"new_item" => __( "New Services", "" ),
		"view_item" => __( "View Services", "" ),
		"view_items" => __( "View Services", "" ),
		"search_items" => __( "Search Services Type", "" ),
		"not_found" => __( "Services Not Found", "" ),
		"featured_image" => __( "Services Image", "" ),
		"set_featured_image" => __( "Set Featured Image of the Services", "" ),
		"remove_featured_image" => __( "Remove Featured Image of the Services", "" ),
		"filter_items_list" => __( "Filter List Services", "" ),
		"items_list_navigation" => __( "service", "" ),
		"items_list" => __( "List Services", "" ),
		"attributes" => __( "Order of Services", "" ),
	);

	$args = array(
		"label" => __( "Services", "At-Your-Services" ),
		"labels" => $labels,
		"description" => "The different kinds of services",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => false,
		"rest_base" => "",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"exclude_from_search" => false,
		"capability_type" => "page",
		"map_meta_cap" => true,
		"hierarchical" => true,
		"rewrite" => array( "slug" => "services",
		'show_in_rest' => true,
		"with_front" => true ),
		"query_var" => true,
		"menu_position" => 5,
    'show_in_rest' => true,
		"menu_icon" => "dashicons-admin-users",
		"supports" => array( "title", "editor", "thumbnail", "excerpt", "custom-fields", "revisions", "page-attributes","post-formats" ),
		"taxonomies" => array( "category", "post_tag", "attachment_category", "attachment_tag", "Type of Services", "location", "price" ),
	);

  // Team Custome type
  register_post_type( 'our-teams',
      array(
          'labels' => array(
              'name' => __( 'Teams' , 'At-Your-Services'),
              'singular_name' => __( 'Team', 'At-Your-Services' )
          ),
          'public' => true,
          'featured_image'=>true,
          'has_archive' => true,
          'menu_icon' => 'dashicons-groups', //  The url to the icon to be used for this menu or the name of the icon from the iconfont
          'supports' => array('title', 'thumbnail', 'author', 'page-attributes'),
      )
  );

  // FAQ Custome type
  register_post_type( 'our-faq',
      array(
          'labels' => array(
              'name' => __( 'FAQs' , 'At-Your-Services'),
              'singular_name' => __( 'FAQ', 'At-Your-Services' )
          ),
          'public' => true,
          'featured_image'=>true,
          'has_archive' => true,
          'menu_icon' => 'dashicons-format-status', //  The url to the icon to be used for this menu or the name of the icon from the iconfont
          'supports' => array('title', 'editor', 'page-attributes'),
      )
  );


  // Reviews Custome type
  register_post_type( 'our-reviews',
      array(
          'labels' => array(
              'name' => __( 'Reviews' , 'At-Your-Services'),
              'singular_name' => __( 'Reviews', 'At-Your-Services' ),
              'menu_name' => __( 'Reviews', '' ),
              'all_items' => __( 'All Reviews', '' ),
              'add_new' => __( 'Add New Review', '' ),
              'add_new_item' => __( 'Add New Review', '' ),
              'edit_item' => __( 'Edit  Reviews', '' ),
              'new_item' => __( 'New Reviews', '' ),
              'view_item' => __( 'View Reviews', '' ),
              'view_items' => __( 'View Reviews', '' ),
              'search_items' => __( 'Search A Reviews ', '' ),
              'not_found' => __( 'Reviews Not Found', '' ),
              'featured_image' => __( 'Reviews Image', '' ),
              'set_featured_image' => __( 'Set Featured Image of the Reviews', '' ),
              'remove_featured_image' => __( 'Remove Featured Image of the Reviews', '' ),
              'filter_items_list' => __( 'Filter List Reviews', '' ),

          ),
          'public' => true,
          'featured_image'=>true,
          'has_archive' => true,
          'publicly_queryable' => true,
      		'show_ui' => true,
      		'show_in_rest' => false,
      		'has_archive' => false,
      		'show_in_menu' => true,
      		'show_in_nav_menus' => true,
      		'exclude_from_search' => false,
      		'capability_type' => 'page','post',
          'show_in_rest' => true,
          'menu_icon' => 'dashicons-format-chat', //  The url to the icon to be used for this menu or the name of the icon from the iconfont
          'supports' => array("title", "editor", "thumbnail", "excerpt", "custom-fields", "revisions", "page-attributes","post-formats"),
      )
  );

	register_post_type( "services", $args );
}

add_action( 'init', 'SP_PS_register_AtYourServices' );



// Custom Taxonomies
function SP_PS_register_AtYourServices_taxonomies() {

    // Type of Service taxonomy
    $labels = array(
		'name'              => 'Type of Services',
        'singular_name'     => 'Type of Service',
        'search_items'      => 'Search Types of Services',
        'all_items'         => 'All Types of Services',
        'parent_item'       => 'Parent Type of Service',
        'parent_item_colon' => 'Parent Type of Service:',
        'edit_item'         => 'Edit Type of Service',
        'update_item'       => 'Update Type of Service',
        'add_new_item'      => 'Add New Type of Service',
        'new_item_name'     => 'New Type of Service Name',
        'menu_name'         => 'Type of Service',
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'Services' ),
        'show_in_rest'		=> true,
    );

    register_taxonomy( 'Service-type', array( 'Services', 'page' ), $args );



}
	add_action( 'init', 'SP_PS_register_AtYourServices_taxonomies' );

	// Price Range taxonomy
	function SP_PS_register_AtYourServices_Price() {

    $labels = array(
        'name'              => 'Price Ranges',
        'singular_name'     => 'Price Range',
        'search_items'      => 'Search Price Ranges',
        'all_items'         => 'All Price Ranges',
        'parent_item'       => 'Parent Price Range',
        'parent_item_colon' => 'Parent Price Range:',
        'edit_item'         => 'Edit Price Range',
        'update_item'       => 'Update Price Range',
        'add_new_item'      => 'Add New Price Range',
        'new_item_name'     => 'New Price Range Name',
        'menu_name'         => 'Price Range',
		"items_list_navigation" =>  'Price', '10' ,
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
		"show_in_menu" 		=> true,
		"show_in_nav_menus" => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'prices' ),
		'show_in_rest'		=> true
    );

    register_taxonomy( 'price', array( 'Services' ), $args );

}
add_action( 'init', 'SP_PS_register_AtYourServices_Price' );



/*For the theme: Flush rewrite rules to add "Services" as a permalink slug
function SP_PS_rewrite_flush() {
 SP_PS_register_AtYourServices();
SP_PS_register_AtYourServices_taxonomies();
SP_PS_register_AtYourServices_Price();
flush_rewrite_rules();
 }
add_action( 'after_switch_theme', 'SP_PS_rewrite_flush' );

//For the Plugin Flush rewrite rules to add "Services" as a permalink slug
      //function SP_PS_rewrite_flush() {
     //SP_PS_register_AtYourServices();
	//SP_PS_register_AtYourServices_taxonomies();
   //SP_PS_register_AtYourServices_Price();
  //flush_rewrite_rules();
 //}
//register_activation_hook( __FILE__, 'SP_PS_rewrite_flush' );
*/













	?>
