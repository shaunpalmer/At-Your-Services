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
        'Ays_CPT_Team'     => AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-team.php'
    ];

    public static function autoload($class_name) {
          // Debugging statement
          error_log(print_r(self::$classes_map, true));
        // Only autoload Ays_ classes to avoid class pollution
        if (strpos($class_name, 'Ays_') !== 0) {
            return;
        }

        if (array_key_exists($class_name, self::$classes_map)) {
               
            // Normalize the file path
            $file_path = wp_normalize_path( self::$classes_map[$class_name] );
            if (file_exists($file_path)) {
                require_once $file_path;
                error_log("Autoloader: Successfully loaded class $class_name from $file_path");
            } else {
                error_log("Autoloader: File $file_path does not exist for class $class_name");
            }
        } else {
            error_log("Autoloader: Class $class_name not found in the classes map.");
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

