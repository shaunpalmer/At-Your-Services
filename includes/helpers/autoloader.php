<?php
/**
 * Autoloader for At Your Service Plugin Classes
 *
 * Automatically loads plugin classes when they are instantiated.
 *
 * @package AtYourService
 * @since 1.0.0
 */

if ( ! defined( 'AYS_PLUGIN_PATH' ) ) {
	define( 'AYS_PLUGIN_PATH', wp_normalize_path( plugin_dir_path( __FILE__ ) ) );
}

/**
 * Class Ays_Autoloader
 *
 * Handles automatic loading of plugin classes.
 */
class Ays_Autoloader {
	
	/**
	 * Map of class names to file paths.
	 *
	 * @var array
	 */
	protected static $classes_map = array(
		// Custom Post Types
		'Ays_CPT_FAQ'      => AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-faq.php',
		'Ays_CPT_Location' => AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-location.php',
		'Ays_CPT_Review'   => AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-review.php',
		'Ays_CPT_Service'  => AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-service.php',
		'Ays_CPT_Team'     => AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-team.php',
		
		// Taxonomies
		'Ays_Taxonomy_Service_Type'  => AYS_PLUGIN_PATH . 'includes/taxonomies/ays-taxonomy-service-type.php',
		'Ays_Taxonomy_Price_Range'   => AYS_PLUGIN_PATH . 'includes/taxonomies/ays-taxonomy-price-range.php',
		'Ays_Taxonomy_Neighbourhood' => AYS_PLUGIN_PATH . 'includes/taxonomies/ays-taxonomy-neighbourhood.php',
	);

	/**
	 * Autoload function to load classes.
	 *
	 * @param string $class_name The name of the class to load.
	 * @return void
	 */
	public static function autoload( $class_name ) {
		// Only autoload Ays_ classes to avoid namespace pollution
		if ( strpos( $class_name, 'Ays_' ) !== 0 ) {
			return;
		}

		if ( array_key_exists( $class_name, self::$classes_map ) ) {
			// Normalize the file path
			$file_path = wp_normalize_path( self::$classes_map[ $class_name ] );
			if ( file_exists( $file_path ) ) {
				require_once $file_path;
			}
		}
	}

	/**
	 * Register the autoloader.
	 *
	 * @return void
	 */
	public static function register() {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}
}

// Register the autoloader for Ays_ classes
Ays_Autoloader::register();

