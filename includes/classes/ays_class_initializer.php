<?php
namespace ays\includes\classes; // Correct casing to match the directory.
 //Exit if accessed directly: Check for bugs and past
if(!defined('ABSPATH')){
	exit;
}

class Ays_Class_Initializer {
    private $essential_files = [
        'constants' => AYS_PLUGIN_PATH . 'includes/helpers/ays_constants.php',
        'autoloader' => AYS_PLUGIN_PATH . 'includes/helpers/autoloader.php',
        'admin_notices' => AYS_PLUGIN_PATH . 'includes/helpers/Admin_Notices.php',
        'functions' => AYS_PLUGIN_PATH . 'includes/helpers/functions.php',
    ];
    
    private $posttype_files = [
        'Ays_CPT_FAQ' => AYS_PLUGIN_PATH . 'includes/posttypes/ays-cpt-faq.php',
        'Ays_CPT_Location' => AYS_PLUGIN_PATH . 'includes/posttypes/ays-cpt-location.php',
        'Ays_CPT_Review' => AYS_PLUGIN_PATH . 'includes/posttypes/ays-cpt-review.php',
        'Ays_CPT_Service' => AYS_PLUGIN_PATH . 'includes/posttypes/ays-cpt-service.php',
        'Ays_CPT_Team' => AYS_PLUGIN_PATH . 'includes/posttypes/ays-cpt-team.php',
    ];

    private $taxonomy_files = [
        'Ays_Taxonomy_Neighbourhood' => AYS_PLUGIN_PATH . 'includes/taxonomies/ays-taxonomy-neighbourhood.php',
        'Ays_Taxonomy_Price_Range' => AYS_PLUGIN_PATH . 'includes/taxonomies/ays-taxonomy-price-range.php',
        'Ays_Taxonomy_Service_Type' => AYS_PLUGIN_PATH . 'includes/taxonomies/ays-taxonomy-service-type.php',
    ];

    public function __construct() {
        $this->include_essential_files(); // Load essential files
        spl_autoload_register([$this, 'ays_autoload']);
        $this->initialize_classes(); // Instantiate CPT and taxonomy classes
    }
    
    private function include_essential_files() {
        foreach ($this->essential_files as $file) {
            if (file_exists($file)) {
                require_once $file;
            } else {
                error_log("$file not found.");
                add_action('admin_notices', function() use ($file) {
                    echo '<div class="error"><p>AYS Plugin: File not found at ' . esc_html($file) . '.</p></div>';
                });
            }
        }
    }

    private function initialize_classes() {
        foreach ($this->posttype_files as $class_name => $file) {
            if (file_exists($file)) {
                require_once $file;
                $this->instantiate_class($class_name);
            }
        }

        foreach ($this->taxonomy_files as $class_name => $file) {
            if (file_exists($file)) {
                require_once $file;
                $this->instantiate_class($class_name);
            }
        }

        // Instantiate Enqueue Scripts
        $this->instantiate_class('Ays_Enqueue_Scripts');
    }

    private function instantiate_class($class_name) {
        if (class_exists($class_name)) {
            try {
                new $class_name();
                error_log("Successfully instantiated $class_name");
            } catch (\Throwable $e) {
                error_log("Failed to instantiate $class_name: " . $e->getMessage());
            }
        } else {
            error_log("Class $class_name not found for instantiation.");
        }
    }

    // Autoloader method to load classes dynamically
    private function ays_autoload($class) {
        $path = AYS_PLUGIN_PATH . 'includes/classes/' . str_replace('_', '-', strtolower($class)) . '.php';
        if (file_exists($path)) {
            require_once $path;
        }
    }
}

// Initialize the Ays_Class_Initializer
$ays_initializer = new Ays_Class_Initializer();

// Hook into WordPress's init action to initialize the plugin
add_action('init', function() use ($ays_initializer) {
    $ays_initializer->initialize();
});