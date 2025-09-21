<?php
if ( ! defined( 'AYS_PLUGIN_PATH' ) ) {
    define( 'AYS_PLUGIN_PATH', wp_normalize_path( plugin_dir_path( __FILE__ ) ) );
}


class Ays_Autoloader {
    protected static $classes_map = [
        'Ays_CPT_FAQ'      => AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-faq.php',
        'Ays_CPT_Location' => AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-location.php',
        'Ays_CPT_Review'   => AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-review.php',
        'Ays_CPT_Service'  => AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-service.php',
        'Ays_CPT_Team'     => AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-team.php',
        'Ays_CPT_Lead'     => AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-lead.php',
    'Ays_Leads_Export_Page' => AYS_PLUGIN_PATH . 'admin/class-ays-leads-export.php',
    'Ays_Lead_Dashboard_Admin' => AYS_PLUGIN_PATH . 'admin/class-ays-lead-dashboard.php',
        // Taxonomies
        'Ays_Taxonomy_Service_Type'   => AYS_PLUGIN_PATH . 'includes/taxonomies/ays-taxonomy-service-type.php',
        'Ays_Taxonomy_Price_Range'    => AYS_PLUGIN_PATH . 'includes/taxonomies/ays-taxonomy-price-range.php',
        'Ays_Taxonomy_Neighbourhood'  => AYS_PLUGIN_PATH . 'includes/taxonomies/ays-taxonomy-neighbourhood.php'
    ];

    public static function autoload($class_name) {
        // Only autoload Ays_ classes to avoid class pollution
        if (strpos($class_name, 'Ays_') !== 0) {
            return;
        }

        if (array_key_exists($class_name, self::$classes_map)) {
               
            // Normalize the file path
            $file_path = wp_normalize_path( self::$classes_map[$class_name] );
            if (file_exists($file_path)) {
                require_once $file_path;
            } else {
                // Silent fail to avoid log noise in production – developer can var_dump if needed.
            }
        }
    }

    public static function register() {
        spl_autoload_register([__CLASS__, 'autoload']);
    }
}


// Register the autoloader for Ays_ classes
Ays_Autoloader::register();
// After the class definition
#var_dump(Ays_Autoloader::$classes_map);

