<?php 
namespace ays\includes\helpers;
/**  includes\helpers\Error_Handler.php namespace ays\includes\helpers;

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
        if (static::$instance === null) { // Use static:: for late static binding
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Add a debug notice and display it using WP_Error_Admin_Notices.
     *
     * @param string $message The debug message.
     * @param string $type    The type of notice (error, warning, success, info).
     */
    public function add_debug_notice($message, $type = 'error') {
        $trace = debug_backtrace();
        $error_context = '';

        if (isset($trace[1]['file'], $trace[1]['line']) && !empty($trace[1]['file']) && !empty($trace[1]['line'])) {
            // Use the caller's file and line information
            $file = $trace[1]['file'];
            $line = $trace[1]['line'];
        } elseif (isset($trace[0]['file'], $trace[0]['line']) && !empty($trace[0]['file']) && !empty($trace[0]['line'])) {
            // Use the current function's file and line information
            $file = $trace[0]['file'];
            $line = $trace[0]['line'];
        } else {
            // File and line information are not available
            $file = 'Unknown file';
            $line = 'Unknown line';

            // Only log the stack trace in development environments
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // Limit the trace depth for readability
                $trace_limit = 5;
                $stack_trace_output = '';

                // Limit the number of frames processed to $trace_limit
                $limited_trace = array_slice($trace, 0, $trace_limit);

                foreach ($limited_trace as $frame) {
                    $stack_trace_output .= sprintf(
                        "%s:%s in function %s\n",
                        isset($frame['file']) ? $frame['file'] : '[internal function]',
                        isset($frame['line']) ? $frame['line'] : '',
                        isset($frame['function']) ? $frame['function'] : ''
                    );
                }

                // Log the stack trace
                error_log("Debug Notice: $message\nStack Trace:\n$stack_trace_output");
            }
        }

        $error_context = sprintf(
            ' in file %s on line %s',
            esc_html($file),
            esc_html($line)
        );

        // Optional: Also log the error with file and line information
        if ($file !== 'Unknown file' && $line !== 'Unknown line') {
            error_log($message . $error_context);
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
        error_log('WP Die called with message: ' . print_r($message, true));

        // Display a custom error message or call the default handler
        if (is_admin()) {
            // Admin-side handling
            echo '<div class="error"><p>' . esc_html($message) . '</p></div>';
        } else {
            // Front-end handling or call the default handler
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
