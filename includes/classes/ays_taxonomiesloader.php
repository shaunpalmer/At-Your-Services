<?php
namespace ays\includes\Classes;

 //Exit if accessed directly: Check for bugs and past
if(!defined('ABSPATH')){
	exit;
}

class ays_taxonomiesloader {
    /**
     * List of custom post types to load
     */
    private $taxonomies = [
        'ays\includes\taxonomies\ays-taxonomy-department-type.php',
        'ays\includes\taxonomies\ays-taxonomy-neighbourhood.php',
        'ays\includes\taxonomies\ays-taxonomy-price-range.php',
        'ays\includes\taxonomies\ays-taxonomy-service-type.php'

    ];

    /**
     * Load all custom post types
     */
    public function loadtaxonomies() {
        foreach ($this->taxonomies as $taxonomies) {
            try {
                if (class_exists($taxonomies)) {
                    new $taxonomies();
                    error_log("Successfully instantiated {$taxonomies}");
                } else {
                    error_log("Failed to instantiate {$taxonomies}: Class not found");
                }
            } catch (\Throwable $e) {
                error_log("Failed to instantiate {$taxonomies}: " . $e->getMessage());
            }
        }
    }
}



