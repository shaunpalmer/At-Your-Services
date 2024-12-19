<?php

namespace ays\includes\core;

use ays\includes\helpers\AYS_ClassAutoloader;
use ays\includes\helpers\WP_Error_Handler;
use Exception; // Added to resolve undefined type for Exception

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

        // Initialize the error handler, if available
        $this->initialize_error_handler();

        // Initialize plugin settings
        $this->initialize_settings();
    }

    /**
     * Initialize the CoreLoader.
     */
    protected function initialize_core_loader()
    {
        $core_loader_path = plugin_dir_path(__DIR__) . 'core/ays_CoreLoader.php';
        if (is_file($core_loader_path)) {
            require_once $core_loader_path;

            if (class_exists('ays\includes\core\AYS_CoreLoader')) {
                try {
                    $core_loader = new AYS_CoreLoader();
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
     * Initialize the error handler for the plugin.
     */
    protected function initialize_error_handler()
    {
        // Based on your directory structure:
        // C:\xampp\htdocs\wordpress\wp-content\plugins\ays\includes\helpers\Error_Handler.php
        $error_handler_path = plugin_dir_path(__DIR__) . 'helpers/Error_Handler.php';

        if (is_file($error_handler_path)) {
            require_once $error_handler_path;

            // Ensure the class name matches what’s inside Error_Handler.php.
            // Assuming the class name is 'ays\includes\helpers\Error_Handler'
            if (class_exists('ays\includes\helpers\Error_Handler')) {
                try {
                    $error_handler = \ays\includes\helpers\Error_Handler::get_instance();
                    error_log('AYS Plugin: Error_Handler initialized successfully.');
                } catch (Exception $e) {
                    error_log('AYS Plugin: Error_Handler initialization failed - ' . $e->getMessage());
                }
            } else {
                error_log('AYS Plugin: Error_Handler class not found in ' . $error_handler_path);
            }
        } else {
            error_log('AYS Plugin: Error_Handler file not found at ' . $error_handler_path);
        }
    }

    /**
     * Initialize settings for AYS Plugin.
     */
    protected function initialize_settings()
    {
        add_action('admin_init', [$this, 'ays_register_settings']);
        add_action('admin_menu', [$this, 'ays_add_settings_field']);
    }

    /**
     * Register settings for debug log max entries.
     */
    public function ays_register_settings()
    {
        register_setting('ays_settings_group', 'ays_debug_log_max_entries', [
            'type' => 'integer',
            'description' => 'Maximum number of debug log entries to display in the admin notice.',
            'default' => 20,
            'sanitize_callback' => 'absint'
        ]);
    }

    /**
     * Add settings field for debug log max entries.
     */
    public function ays_add_settings_field()
    {
        add_settings_field(
            'ays_debug_log_max_entries',
            'Max Debug Log Entries',
            [$this, 'ays_debug_log_max_entries_callback'],
            'general'
        );
        register_setting('general', 'ays_debug_log_max_entries');
    }

    /**
     * Callback for rendering the input field for max debug log entries.
     */
    public function ays_debug_log_max_entries_callback()
    {
        $value = (int) get_option('ays_debug_log_max_entries', 20);
        echo '<input type="number" id="ays_debug_log_max_entries" name="ays_debug_log_max_entries" value="' . esc_attr($value) . '" min="1">';
    }
}


/**
 * Class AYS_Bootstrapper
 * Responsible for initializing the core components of the AYS Plugin.
 */
// class AYS_Bootstrapper
// {
//     /**
//      * Centralized bootstrapping function for AYS Plugin.
//      */
//     public static function bootstrap()
//     {
//         try {
//             $instance = new self();
//             $instance->initialize();
//         } catch (Exception $e) {
//             error_log('AYS Plugin: Initialization error - ' . $e->getMessage());
//             add_action('admin_notices', function () use ($e) {
//                 echo '<div class="notice notice-error"><p>AYS Plugin failed to initialize: ' . esc_html($e->getMessage()) . '</p></div>';
//             });
//         }
//     }

//     /**
//      * Initializes all required components for the AYS plugin.
//      */
//     protected function initialize()
//     {
//         // Load CoreLoader first
//         $this->initialize_core_loader();

//         // Initialize the general autoloader
//         $this->register_general_autoloader();

//         // Initialize additional components (e.g., error handler, settings)
//         $this->initialize_error_handler();
//         $this->initialize_settings();

//         // Temporary wide debugging
//         add_action('admin_init', function () {
//             $this->temporary_debugging();
//         });
//     }

//     /**
//      * Temporary wide debugging hook for file inclusion.
//      */
//     protected function temporary_debugging()
//     {
//         try {
//             include_once plugin_dir_path(__DIR__) . 'core/ays_CoreLoader.php';
//             error_log('Temporary Debug: Included core/ays_CoreLoader.php successfully.');
//         } catch (Exception $e) {
//             error_log('Temporary Debug: Failed to include core/ays_CoreLoader.php - ' . $e->getMessage());
//         }

//         try {
//             include_once plugin_dir_path(__DIR__) . 'helpers/Error_Handler.php';
//             error_log('Temporary Debug: Included helpers/Error_Handler.php successfully.');
//         } catch (Exception $e) {
//             error_log('Temporary Debug: Failed to include helpers/Error_Handler.php - ' . $e->getMessage());
//         }

//         try {
//             include_once plugin_dir_path(__DIR__) . 'helpers/autoloader.php';
//             error_log('Temporary Debug: Included helpers/autoloader.php successfully.');
//         } catch (Exception $e) {
//             error_log('Temporary Debug: Failed to include helpers/autoloader.php - ' . $e->getMessage());
//         }
//     }

//     /**
//      * Initialize the CoreLoader.
//      */
//     protected function initialize_core_loader()
//     {
//         $core_loader_path = plugin_dir_path(__DIR__) . 'core/ays_CoreLoader.php';
//         if (is_file($core_loader_path)) {
//             require_once $core_loader_path;
//             if (class_exists('ays\includes\core\AYS_CoreLoader')) {
//                 $core_loader = new AYS_CoreLoader();
//                 $config = ['path' => plugin_dir_path(__DIR__)]; // Example argument
//                 $core_loader->load($config); // Updated to pass the required argument
//                 error_log('AYS Plugin: CoreLoader initialized successfully.');
//             } else {
//                 error_log('AYS Plugin: CoreLoader class not found.');
//             }
//         } else {
//             error_log('AYS Plugin: CoreLoader file not found.');
//         }
//     }

//     /**
//      * Register the general-purpose autoloader.
//      */
//     protected function register_general_autoloader()
//     {
//         $autoloader_path = plugin_dir_path(__DIR__) . 'helpers/autoloader.php';

//         if (is_file($autoloader_path)) {
//             require_once $autoloader_path;
//             AYS_ClassAutoloader::register();
//             error_log('AYS Plugin: General-purpose autoloader registered successfully.');
//         } else {
//             $error_message = 'AYS Plugin: General-purpose autoloader file not found.';
//             error_log($error_message);
//             add_action('admin_notices', function () use ($error_message) {
//                 echo '<div class="notice notice-error"><p>' . esc_html($error_message) . '</p></div>';
//             });
//         }
//     }

//     /**
//      * Initialize the error handler for the plugin.
//      */
//     protected function initialize_error_handler()
//     {
//         $error_handler_path = plugin_dir_path(__DIR__) . 'helpers/Error_Handler.php';
//         if (is_file($error_handler_path)) {
//             require_once $error_handler_path;
//             $error_handler = WP_Error_Handler::get_instance();
//             error_log('AYS Plugin: WP_Error_Handler initialized successfully.');
//         } else {
//             error_log('AYS Plugin: WP_Error_Handler file not found at ' . $error_handler_path);
//         }
//     }

//     /**
//      * Initialize settings for AYS Plugin.
//      */
//     protected function initialize_settings()
//     {
//         add_action('admin_init', [$this, 'ays_register_settings']);
//         add_action('admin_menu', [$this, 'ays_add_settings_field']);
//     }

//     /**
//      * Register settings for debug log max entries.
//      */
//     public function ays_register_settings()
//     {
//         register_setting('ays_settings_group', 'ays_debug_log_max_entries', [
//             'type' => 'integer',
//             'description' => 'Maximum number of debug log entries to display in the admin notice.',
//             'default' => 20,
//             'sanitize_callback' => 'absint'
//         ]);
//     }

//     /**
//      * Add settings field for debug log max entries.
//      */
//     public function ays_add_settings_field()
//     {
//         add_settings_field(
//             'ays_debug_log_max_entries',
//             'Max Debug Log Entries',
//             [$this, 'ays_debug_log_max_entries_callback'],
//             'general'
//         );
//         register_setting('general', 'ays_debug_log_max_entries');
//     }

//     /**
//      * Callback for rendering the input field for max debug log entries.
//      */
//     public function ays_debug_log_max_entries_callback()
//     {
//         $value = (int) get_option('ays_debug_log_max_entries', 20);
//         echo '<input type="number" id="ays_debug_log_max_entries" name="ays_debug_log_max_entries" value="' . esc_attr($value) . '" min="1">';
//     }
// }

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
