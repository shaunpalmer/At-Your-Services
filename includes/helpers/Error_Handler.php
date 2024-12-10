<?php 
namespace ays\includes\helpers;

/**
 * Class WP_Error_Handler
 *
 * Handles debugging errors and integrates with the WP_Error_Admin_Notices class for admin notices.
 */
class WP_Error_Handler {
    private static $instance = null;

    /**
     * Get the singleton instance of the class.
     *
     * @return WP_Error_Handler
     */
    public static function get_instance() {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Add a debug notice and optionally display it using WP_Error_Admin_Notices.
     *
     * @param string $message The debug message.
     * @param string $type    The type of notice (error, warning, success, info).
     */
    public function add_debug_notice($message, $type = 'error') {
        // Log the error using the new log_error method
        $this->log_error($message);

        // Display the admin notice
        $this->display_admin_notice($message, $type);
    }

    /**
     * Display an admin notice.
     *
     * @param string $message The message to display in the admin notice.
     * @param string $type    The type of notice (error, warning, success, info).
     */
    public function display_admin_notice($message, $type = 'error') {
        add_action('admin_notices', function() use ($message, $type) {
            $classes = 'notice is-dismissible ';
            switch ($type) {
                case 'warning':
                    $classes .= 'notice-warning';
                    break;
                case 'success':
                    $classes .= 'notice-success';
                    break;
                case 'info':
                    $classes .= 'notice-info';
                    break;
                default:
                    $classes .= 'notice-error';
                    break;
            }
            echo '<div class="' . esc_attr($classes) . '"><p>' . esc_html($message) . '</p></div>';
        });
    }

    /**
     * Log error messages, optionally including a limited stack trace.
     *
     * @param string $message The error message to log.
     * @param int    $trace_limit The number of frames from the stack trace to log. Defaults to 3.
     */
    public function log_error($message, $trace_limit = 3) {
        // Log the basic error message
        error_log("[ERROR] $message");

        // Add stack trace only if debugging is enabled
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $trace_limit);
            foreach ($trace as $index => $frame) {
                error_log(sprintf(
                    "[TRACE %d] %s:%s in function %s",
                    $index,
                    isset($frame['file']) ? $frame['file'] : '[internal function]',
                    isset($frame['line']) ? $frame['line'] : 'unknown line',
                    isset($frame['function']) ? $frame['function'] : 'unknown function'
                ));
            }
        }
    }

    /**
     * Custom handler for wp_die().
     *
     * @param string|WP_Error $message The error message.
     * @param string          $title   Optional. The error title.
     * @param array           $args    Optional. Arguments to control behavior.
     */
    public function handle_wp_die($message, $title = '', $args = array()) {
        // Log the error message
        $this->log_error('WP Die called with message: ' . print_r($message, true));

        // Display a custom error message or call the default handler
        if (is_admin()) {
            echo '<div class="error"><p>' . esc_html($message) . '</p></div>';
        } else {
            _default_wp_die_handler($message, $title, $args);
        }
    }
}

// Hook admin notices to display using WP_Error_Admin_Notices
if (class_exists(__NAMESPACE__ . '\WP_Error_Admin_Notices')) {
    \add_action('admin_notices', [__NAMESPACE__ . '\WP_Error_Admin_Notices', 'display_notices']);
} else {
    error_log('WP_Error_Admin_Notices class not found.');
}

// Replace wp_die with custom handler during debugging
\add_filter('wp_die_handler', function () {
    return [\ays\includes\helpers\WP_Error_Handler::get_instance(), 'handle_wp_die'];
});