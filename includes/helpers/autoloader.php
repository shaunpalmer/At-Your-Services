<?php 
if ( ! defined( 'AYS_PLUGIN_PATH' ) ) {
    define( 'AYS_PLUGIN_PATH', wp_normalize_path( plugin_dir_path( __FILE__ ) ) );
}
class Ays_Autoloader {
    protected static $classes_map = [
        'Ays_CPT_FAQ'               => AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-faq.php',
        'Ays_CPT_Location'          => AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-location.php',
        'Ays_CPT_Review'            => AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-review.php',
        'Ays_CPT_Service'           => AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-service.php',
        'Ays_CPT_Team'              => AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-team.php',
        
        'Ays_Taxonomy_Neighbourhood' => AYS_PLUGIN_PATH . 'includes/taxonomies/ays-taxonomy-neighbourhood.php',
        'Ays_Taxonomy_Price_Range'   => AYS_PLUGIN_PATH . 'includes/taxonomies/ays-taxonomy-price-range.php',
        'Ays_Taxonomy_Service_Type'  => AYS_PLUGIN_PATH . 'includes/taxonomies/ays-taxonomy-service-type.php'
    ];

    protected static $cachedClassesMap = [];

    public static function autoload($class_name) {
        // Only autoload Ays_ classes to avoid class pollution
        if (strpos($class_name, 'Ays_') !== 0) {
            return;
        }

        // Attempt to load from the cached classes map
        if (isset(self::$cachedClassesMap[$class_name])) {
            require_once self::$cachedClassesMap[$class_name];
            return;
        }


        // Check pre-defined map
        if (isset(self::$classes_map[$class_name])) {
            // Store in cached map and load
            $file_path = wp_normalize_path(self::$classes_map[$class_name]);
            if (file_exists($file_path)) {
                self::$cachedClassesMap[$class_name] = $file_path;
                require_once $file_path;
                error_log("Autoloader: Successfully loaded class $class_name from cached map at $file_path");
                return;
            }
        }

        // Dynamic file scanning
        $file_path = self::scan_and_find_class($class_name);
        if ($file_path) {
            self::$cachedClassesMap[$class_name] = $file_path;
            require_once $file_path;
            error_log("Autoloader: Successfully loaded class $class_name from scanned path at $file_path");
        } else {
            error_log("Autoloader: Class $class_name not found in any known paths.");
        }
    }



    protected static function scan_and_find_class($class_name) {
        // Define directories to be scanned
        $directories = [
            AYS_PLUGIN_PATH . 'includes/post-types',
            AYS_PLUGIN_PATH . 'includes/helpers',
            AYS_PLUGIN_PATH . 'includes/taxonomies',
            // AYS_PLUGIN_PATH . 'includes/classes', // Uncomment when you start adding classes here
        ];
        foreach ($directories as $dir) {
            // Only scan directories that exist
            if (!is_dir($dir)) {
                continue;
            }
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $path = wp_normalize_path($dir . '/' . $file);
                if (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                    // Attempt to load the file
                    if (self::file_contains_class($path, $class_name)) {
                        return $path;
                    }
                }
            }
        }
        return false;
    }

    protected static function file_contains_class($file_path, $class_name) {
        // Open the file and look for the class definition
        $contents = file_get_contents($file_path);
        return strpos($contents, "class $class_name") !== false;
    }

    public static function register() {
        spl_autoload_register([__CLASS__, 'autoload']);
    }
}

// Register the autoloader for Ays_ classes
Ays_Autoloader::register();


// After the class definition
#var_dump(Ays_Autoloader::$classes_map);

