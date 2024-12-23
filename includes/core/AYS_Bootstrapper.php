<?php

namespace ays\includes\core;

use ays\includes\helpers\AYS_ClassAutoloader;
use Exception;

/**
 * Class AYS_Bootstrapper
 * Responsible for initializing the core components of the AYS Plugin.
 */
class AYS_Bootstrapper
{
    /**
     * Centralized bootstrapping function for AYS Plugin.
     */
    public static function bootstrap()
    {
        try {
            $instance = new self();
            $instance->initialize();
            error_log('Bootstrapper initialized successfully');
        } catch (Exception $e) {
            error_log('AYS Plugin: Initialization error - ' . $e->getMessage());
            add_action('admin_notices', function () use ($e) {
                echo '<div class="notice notice-error"><p>AYS Plugin failed to initialize: ' . esc_html($e->getMessage()) . '</p></div>';
            });
        }
    }

    /**
     * Initializes all required components for the AYS plugin.
     */
    protected function initialize()
    {
        // Initialize CoreLoader
        $this->initialize_core_loader();

        // Register the general-purpose autoloader
        $this->register_general_autoloader();

        // Temporary debug for loader issues
        $this->debug_autoloader_issue();

        // Future Initialization (commented for focus)
        // $this->initialize_error_handler();
    }

    /**
     * Initialize the CoreLoader.
     */
    protected function initialize_core_loader()
    {
        $core_loader_path = plugin_dir_path(__DIR__) . 'core/AYS_CoreLoader.php';
        if (is_file($core_loader_path)) {
            require_once $core_loader_path;

            if (class_exists('ays\includes\core\AYS_CoreLoader')) {
                try {
                    $core_loader = new \ays\includes\core\AYS_CoreLoader();
                    $config = ['path' => plugin_dir_path(__DIR__)];
                    $core_loader->load($config);
                    error_log('AYS Plugin: CoreLoader initialized successfully.');
                } catch (Exception $e) {
                    error_log('AYS Plugin: CoreLoader initialization failed - ' . $e->getMessage());
                }
            } else {
                error_log('AYS Plugin: AYS_CoreLoader class not found after including the file.');
            }
        } else {
            error_log('AYS Plugin: CoreLoader file not found at ' . $core_loader_path);
        }
    }

    /**
     * Register the general-purpose autoloader.
     */
    protected function register_general_autoloader()
    {
        $autoloader_path = plugin_dir_path(__DIR__) . 'helpers/autoloader.php';

        if (is_file($autoloader_path)) {
            require_once $autoloader_path;
            if (class_exists('ays\includes\helpers\AYS_ClassAutoloader')) {
                AYS_ClassAutoloader::register();
                error_log('AYS Plugin: General-purpose autoloader registered successfully.');
            } else {
                error_log('AYS Plugin: AYS_ClassAutoloader class not found in autoloader file.');
            }
        } else {
            $error_message = 'AYS Plugin: General-purpose autoloader file not found at ' . $autoloader_path;
            error_log($error_message);
            add_action('admin_notices', function () use ($error_message) {
                echo '<div class="notice notice-error"><p>' . esc_html($error_message) . '</p></div>';
            });
        }
    }

    /**
     * Debugging for autoloader issue.
     */
    protected function debug_autoloader_issue()
    {
        add_action('admin_init', function () {
            if (method_exists('AYS_CoreLoader', 'load')) {
                error_log('AYS_CoreLoader [DEBUG]: load() method exists.');
            } else {
                error_log('AYS_CoreLoader [DEBUG]: load() method does not exist.');
            }
        });
    }
}

/**
 * Temporary Debugging Notes
 * -------------------------
 * Added a temporary debugging hook in `initialize`:
 * - Hooked into `admin_init` for admin-area debugging.
 * - Includes the following files for validation:
 *     1. `core/ays_CoreLoader.php`
 *     2. `helpers/Error_Handler.php`
 *     3. `helpers/autoloader.php`
 * 
 * Purpose:
 * - To verify the existence and correct inclusion of these files.
 * - To confirm that their namespaces and paths are properly aligned.
 *
 * Follow-up:
 * - Remove the temporary debugging logic once issues are resolved.
 * - Restore original implementation or refactor as necessary.
 * - Ensure all files integrate seamlessly into the plugin lifecycle.
 *
 * Refactoring Table:
 * | Section                     | Current Debug Code                                     | Original Implementation         | Reason for Debugging                 |
 * |-----------------------------|-------------------------------------------------------|---------------------------------|---------------------------------------|
 * | CoreLoader Include          | `include_once 'core/ays_CoreLoader.php';`            | Proper instantiation in `initialize_core_loader` | To ensure file exists and loads correctly. |
 * | WP_Error_Handler Include    | `include_once 'helpers/Error_Handler.php';`       | Full integration in `initialize_error_handler` | To verify file path and namespace alignment. |
 * | Autoloader Include          | `include_once 'helpers/autoloader.php';`             | Full integration in `register_general_autoloader` | To validate autoloader setup.              |
 */
