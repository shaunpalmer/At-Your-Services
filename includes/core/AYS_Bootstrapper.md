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
        // Load CoreLoader first
        $this->initialize_core_loader();

        // Initialize the general autoloader
        $this->register_general_autoloader();

        // Initialize additional components (e.g., error handler, settings)
        $this->initialize_error_handler();
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
                $core_loader = new AYS_CoreLoader();
                $config = ['path' => plugin_dir_path(__DIR__)]; // Example argument
                $core_loader->load($config); // Updated to pass the required argument
                error_log('AYS Plugin: CoreLoader initialized successfully.');
            } else {
                error_log('AYS Plugin: CoreLoader class not found.');
            }
        } else {
            error_log('AYS Plugin: CoreLoader file not found.');
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
            AYS_ClassAutoloader::register();
            error_log('AYS Plugin: General-purpose autoloader registered successfully.');
        } else {
            $error_message = 'AYS Plugin: General-purpose autoloader file not found.';
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
        $error_handler_path = plugin_dir_path(__DIR__) . 'helpers/WP_Error_Handler.php';
        if (is_file($error_handler_path)) {
            require_once $error_handler_path;
            $error_handler = WP_Error_Handler::get_instance();
            error_log('AYS Plugin: WP_Error_Handler initialized successfully.');
        } else {
            error_log('AYS Plugin: WP_Error_Handler file not found at ' . $error_handler_path);
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
