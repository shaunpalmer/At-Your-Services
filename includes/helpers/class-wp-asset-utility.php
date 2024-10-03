<?php

/*
WordPress Path Discovery Utility Class
To use this utility, you would initialize it in your plugin's main file like this:
require_once 'path/to/WP_Asset_Utility.php';
initialize_asset_utility();

class WP_Asset_Utility {
    private $base_path;
    private $base_url;
    private $asset_paths = [
        'css' => ['assets/css', 'css'],
        'js' => ['assets/js', 'js'],
        'images' => ['assets/images', 'images']
    ];
    private $errors = [];

    public function __construct($base_file) {
        $this->base_path = plugin_dir_path($base_file);
        $this->base_url = plugin_dir_url($base_file);
    }

    public function set_asset_paths($paths) {
        foreach ($paths as $type => $path_array) {
            if (!is_array($path_array)) {
                $path_array = [$path_array];
            }
            $this->asset_paths[$type] = array_merge($path_array, $this->asset_paths[$type] ?? []);
        }
    }

    public function get_asset_path($type) {
        if (!isset($this->asset_paths[$type])) {
            $this->add_error("Asset type '$type' not defined.");
            return false;
        }

        foreach ($this->asset_paths[$type] as $path) {
            $full_path = $this->base_path . $path;
            if (is_dir($full_path)) {
                return $full_path;
            }
        }

        $this->add_error("No valid directory found for asset type '$type'.");
        return false;
    }

    public function get_asset_url($type) {
        if (!isset($this->asset_paths[$type])) {
            $this->add_error("Asset type '$type' not defined.");
            return false;
        }

        foreach ($this->asset_paths[$type] as $path) {
            $full_path = $this->base_path . $path;
            if (is_dir($full_path)) {
                return $this->base_url . $path;
            }
        }

        $this->add_error("No valid URL found for asset type '$type'.");
        return false;
    }

    public function enqueue_assets() {
        $scripts = [
            'main-script' => ['file' => 'main.js', 'deps' => ['jquery'], 'version' => '1.0', 'in_footer' => true],
            'main-style' => ['file' => 'style.css', 'deps' => [], 'version' => '1.0'],
        ];

        foreach ($scripts as $handle => $details) {
            $file_extension = pathinfo($details['file'], PATHINFO_EXTENSION);
            $asset_path = $this->get_asset_path($file_extension);
            
            if (!$asset_path) {
                continue;
            }

            $file_path = $asset_path . '/' . $details['file'];
            
            if (!file_exists($file_path)) {
                $this->add_error("File not found: $file_path");
                continue;
            }

            $url = $this->get_asset_url($file_extension) . '/' . $details['file'];

            if ($file_extension === 'css') {
                wp_enqueue_style($handle, $url, $details['deps'], $details['version']);
            } else {
                wp_enqueue_script($handle, $url, $details['deps'], $details['version'], $details['in_footer'] ?? false);
            }
        }

        $this->log_errors();
    }

    private function add_error($message) {
        $this->errors[] = $message;
    }

    private function log_errors() {
        if (!empty($this->errors) && current_user_can('manage_options')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p>WP_Asset_Utility Errors:</p>';
                echo '<ul>';
                foreach ($this->errors as $error) {
                    echo "<li>$error</li>";
                }
                echo '</ul>';
                echo '</div>';
            });
        }
    }
}

// Usage in main plugin file
function initialize_asset_utility() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-wp-asset-utility.php';
    $asset_utility = new WP_Asset_Utility(__FILE__);
    add_action('wp_enqueue_scripts', [$asset_utility, 'enqueue_assets']);
}
add_action('plugins_loaded', 'initialize_asset_utility');

Trying to find way to programmatically go and look for the correct $path  path 
Make up a utility set of functions

/*
Remember to replace 'component' in the example above with the actual component name (e.g., 'cpt', 'blocks', 'templates') when using it in different parts of your plugin.
This approach should make your asset management more robust and easier to debug when issues arise.
function component_enqueue_assets() {
    $asset_utility = new WP_Asset_Utility(plugin_dir_path(__DIR__) . 'atyourservice.php');
    $asset_utility->set_asset_paths([
        'css' => ['component/assets/css', 'assets/css'],
        'js' => ['component/assets/js', 'assets/js']
    ]);
    $asset_utility->enqueue_assets();
}
add_action('wp_enqueue_scripts', 'component_enqueue_assets');


function component_enqueue_assets() {
    $asset_utility = new WP_Asset_Utility(plugin_dir_path(__DIR__) . 'atyourservice.php');
    $asset_utility->set_asset_paths([
        'css' => ['component/assets/css', 'assets/css'],
        'js' => ['component/assets/js', 'assets/js']
    ]);
    $asset_utility->enqueue_assets();
}
add_action('wp_enqueue_scripts', 'component_enqueue_assets');
require_once plugin_dir_path(__FILE__) . 'includes/class-wp-asset-utility.php';

function initialize_asset_utility() {
    $asset_utility = new WP_Asset_Utility(__FILE__);
    add_action('wp_enqueue_scripts', [$asset_utility, 'enqueue_assets']);
}
add_action('plugins_loaded', 'initialize_asset_utility');
This setup provides several advantages:

It will try multiple paths, reducing the chance of failure if the directory structure changes slightly.
If a path is not found, it will log an error but continue trying to enqueue other assets.
Errors are logged and displayed in the admin area, making debugging easier.
The utility is flexible enough to be used in different parts of your plugin with custom paths.

Remember to replace 'component' in the example above with the actual component name (e.g., 'cpt', 'blocks', 'templates') when using it in different parts of your plugin.
This approach should make your asset management more robust and easier to debug when issues arise.
*/
