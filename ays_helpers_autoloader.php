<?php
#namespace ays;
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Ensure constants are defined
if (!defined('AYS_PLUGIN_PATH')) {
    define('AYS_PLUGIN_PATH', wp_normalize_path(plugin_dir_path(__FILE__)));
}
if (!defined('AYS_DS')) {
    define('AYS_DS', DIRECTORY_SEPARATOR);
}

// **Autoloader function registered in the global namespace**
spl_autoload_register(function ($className) {
    $namespacePrefix = 'ays\\includes\\helpers\\';
    $baseDir = AYS_PLUGIN_PATH . 'includes' . AYS_DS . 'helpers' . AYS_DS;

    // Check if the class uses the namespace prefix
    if (strpos($className, $namespacePrefix) !== 0) {
        // Not our namespace, return
        return;
    }

    // Get the relative class name
    $relativeClass = substr($className, strlen($namespacePrefix));

    // Handle the specific case where class name doesn't match file name
    if ($relativeClass === 'WP_Error_Admin_Notices') {
        $file = $baseDir . 'Admin_Notices.php';
    } else {
        // For other classes, convert namespace separators to directory separators
        $relativeClassPath = str_replace('\\', AYS_DS, $relativeClass);
        $file = $baseDir . $relativeClassPath . '.php';
    }

    // Include the file if it exists
    if (file_exists($file)) {
        include_once $file;
    } else {
        error_log("Autoloader could not find file: $file");
    }
});

// **Now declare the namespace for your code**
#namespace ays\includes\helpers;

// Example: Ensuring the class is loaded
if (class_exists('ays\\includes\\helpers\\WP_Error_Admin_Notices')) {
    error_log('WP_Error_Admin_Notices class successfully autoloaded.');
} else {
    error_log('Failed to autoload WP_Error_Admin_Notices class.');
}  

add_action('plugins_loaded', function() {
    error_log('Attempting to register AYS_Helpers_Autoloader.');
    if (class_exists('AYS_Helpers_Autoloader')) {
        AYS_Helpers_Autoloader::register();
        error_log('AYS_Helpers_Autoloader registered successfully.');
    } else {
        error_log('AYS_Helpers_Autoloader class not found during registration.');
    }
});