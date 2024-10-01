 <?php
/**
 * Service Custom Post Type
 *
 * @package     SP_PS_Project_studios\SP_PS_Project_studiosPlugin\Custom
 * @since       1.0.0
 * @author      shaun palmer
 * @link
 * @license     GNU General Public License 2.0+
 */
namespace SP_PS_Project_studios\SP_PS_Project_studiosPlugin\Custom;
/**
 * Register the custom post type.
 *
 * @since 1.0.0
 *
 * @return void
 */
function register_custom_post_type() {
	$labels = array(
		'name'               => _x( 'Services', 'post type general name', 'SP_PS_Project_studios' ),
		'singular_name'      => _x( 'Service', 'post type singular name', 'SP_PS_Project_studios' ),
		'menu_name'          => _x( 'Services', 'admin menu', 'SP_PS_Project_studios' ),
		'name_admin_bar'     => _x( 'Service', 'add new on admin bar', 'SP_PS_Project_studios' ),
		'add_new_item'       => __( 'Add New Service', 'SP_PS_Project_studios' ),
		'new_item'           => __( 'New Service', 'SP_PS_Project_studios' ),
		'edit_item'          => __( 'Edit Service', 'SP_PS_Project_studios' ),
		'view_item'          => __( 'View Service', 'SP_PS_Project_studios' ),
		'all_items'          => __( 'All Services', 'SP_PS_Project_studios' ),
		'search_items'       => __( 'Search Services', 'SP_PS_Project_studios' ),
		'parent_item_colon'  => __( 'Parent Services:', 'SP_PS_Project_studios' ),
		'not_found'          => __( 'No Services found.', 'SP_PS_Project_studios' ),
		'not_found_in_trash' => __( 'No Services found in Trash.', 'SP_PS_Project_studios' ),
	);
  $exclude_features = array(
		'comments',
		'trackbacks',
		'custom-fields'
	);
	$features = get_all_post_type_features( 'post', $exclude_features );
  
	$args = array(
		'labels' => $labels,
		'supports' => $features,
		'public' => true,
		'hierarchical' => true,
		'has_archive' => true,
		'show_in_rest' => true,
		'rest_base' => 'services',
	);
	register_post_type( 'services', $args );
}
add_action( 'init', __NAMESPACE__ . '\register_custom_post_type' );
/**
 * Get all the post type features for the given post type.
 * @param  string $post_type Given post type.
 * @param  array  $exclude_features Array of features to exclude.
 *
 * @return array
 */
function get_all_post_type_features( $post_type = 'post', $exclude_features = array() ) {
	$features = array_keys(get_all_post_type_supports( $post_type ));
	$features = array_filter( $features, function( $feature ) use($exclude_features) {
		return ! in_array( $feature, $exclude_features );
	});
	return $features;
}
