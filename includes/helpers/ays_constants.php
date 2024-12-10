<?php
namespace ays\includes\helpers; 

// C:\xampp\htdocs\WordPressdev\wp-content\plugins
define('AYS_PLUGIN_VERSION', '1.2.3');
#Early as possible execution these constants are going to be used throughout the code base
// Define constants
// Prevent duplicate inclusion
if (defined('AYS_PLUGIN_INITIALIZED')) {
    return; // Exit early if already loaded
}
define('AYS_PLUGIN_INITIALIZED', true);
/**
 * Define the plugin URL if not already defined.
 * AYS_PLUGIN_URL provides the URL to the plugin's directory.
 * It is used when referencing assets that need to be accessible in the browser, such as JavaScript, CSS, or image files.
 */
define('AYS_PLUGIN_URL', plugins_url('/', __FILE__));
/**
 * Define the plugin path if not already defined.
 * AYS_PLUGIN_PATH provides the absolute file system path to the plugin's directory.
 * It is used when we need to access files directly on the server, such as including templates or other PHP files.
 */
define('AYS_PLUGIN_PATH', plugin_dir_path(__FILE__)); # Case-insensitive Because of true
define('AYS_DS', DIRECTORY_SEPARATOR);

if (! defined('AYS_PLUGIN_URL')) {
    define('AYS_PLUGIN_URL', wp_normalize_path(plugins_url('/', __FILE__)));
}
// Define a custom constant for the directory separator to enhance code readability
#define('AYS_DS', DIRECTORY_SEPARATOR);
define('AYS_PLUGIN_DIR_PATH', wp_normalize_path(plugin_dir_path(__DIR__)));  // Include the constants file  // Initialize the Error 


#Handlertry {
// // Include the Constants File early in the plugin, only once
// #$constants_path = AYS_PLUGIN_PATH . 'includes/helpers/ays_constants.php';
// if (!defined('AYS_PLUGIN_CONSTANTS_INCLUDED')) {
//     if (file_exists($constants_path)) {
//         require_once $constants_path;
//         define('AYS_PLUGIN_CONSTANTS_INCLUDED', true);
//     } else {
//         $ays_error_handler->log_error("Constants file not found at: " . esc_html($constants_path));
//         return; // Stop further execution if critical files are missing
//     }
// }