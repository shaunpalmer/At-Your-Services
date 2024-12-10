<?php
namespace ays\includes\core;  

use ays\includes\helpers\WP_Error_Handler;
use ays\includes\helpers\AYS_ClassAutoloader;

/**
 * Class AYS_Bootstrapper
 * Responsible for initializing the core components of the AYS Plugin.
 */
class AYS_Bootstrapper {
    protected $error_handler;

    /**
     * Constructor to initialize the error handler.
     */
    public function __construct() {
        // Include WP_Error_Handler before instantiating the error handler
        $error_handler_path = plugin_dir_path(__DIR__) . 'helpers/WP_Error_Handler.php';
        if (is_file($error_handler_path)) {
            require_once $error_handler_path;
            $this->error_handler = WP_Error_Handler::get_instance();
        } else {
            error_log('AYS Plugin: WP_Error_Handler file not found at ' . $error_handler_path);
        }
        
        $this->include_required_files();
    } 

    /**
     * Centralized bootstrapping function for AYS Plugin.
     */
    public static function bootstrap() {
        $instance = new self();
        $instance->initialize();
    }

    /**
     * Initializes all required files for the AYS plugin.
     */
    protected function initialize() {
        $this->register_autoloader();
        $this->initialize_settings(); // Register settings here
    }

    /**
     * Initialize settings for AYS Plugin.
     */
    protected function initialize_settings() {
        add_action('admin_init', [$this, 'ays_register_settings']);
        add_action('admin_menu', [$this, 'ays_add_settings_field']);
    }

    /**
     * Register settings for debug log max entries.
     */
    public function ays_register_settings() {
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
    public function ays_add_settings_field() {
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
    public function ays_debug_log_max_entries_callback() {
        $value = (int) get_option('ays_debug_log_max_entries', 20);
        echo '<input type="number" id="ays_debug_log_max_entries" name="ays_debug_log_max_entries" value="' . esc_attr($value) . '" min="1">';
    }

    /**
     * Include all essential files.
     */
    protected function include_required_files() {
        // Including all essential files for the plugin
        $required_files = [
            'constants' => plugin_dir_path(__DIR__) . 'helpers/ays_constants.php',
            'error_handler' => plugin_dir_path(__DIR__) . DIRECTORY_SEPARATOR .'helpers/WP_Error_Handler.php',
            'enqueue' => plugin_dir_path(__DIR__)  . DIRECTORY_SEPARATOR .'admin/enqueue.php',
            'settings' => plugin_dir_path(__DIR__) . 'settings/settings.php',
            'shortcodes' => plugin_dir_path(__DIR__) . 'shortcode/ays_shortcodes.php',
            // Add any other required files here
        ];

        foreach ($required_files as $key => $file_path) {
            if (is_file($file_path)) {
                require_once $file_path;
            } else {
                $error_message = sprintf('AYS Plugin: Required file (%s) not found at %s.', $key, esc_html($file_path));
                error_log($error_message);
                add_action('admin_notices', function() use ($error_message) {
                    echo '<div class="notice notice-error"><p>' . esc_html($error_message) . '</p></div>';
                });
            }
        }
    }

    /**
     * Register the AYS plugin autoloader.
     */
    protected function register_autoloader() {
        $autoloader_path = plugin_dir_path(__DIR__) . 'helpers/autoloader.php';

        if (is_file($autoloader_path)) {
            require_once $autoloader_path;
            AYS_ClassAutoloader::register();
        } else {
            $error_message = 'AYS Plugin: Autoloader file not found.';
            $this->error_handler->log_error($error_message);
            $this->error_handler->display_admin_notice($error_message, 'error');
        }
        }
}