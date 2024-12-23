<?php

namespace ays\includes\helpers;

class WP_Error_Handler
{
    private static $instance = null;
    protected static $log_file;

    /**
     * Get the singleton instance of the class.
     *
     * @return WP_Error_Handler
     */
    public static function get_instance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
            static::$instance->initialize();
        }
        return static::$instance;
    }

    /**
     * Initialize the error handler.
     */
    protected function initialize()
    {
        self::$log_file = WP_CONTENT_DIR . '/ays_error_log.log';
        add_action('admin_notices', [__CLASS__, 'display_admin_notices']);
    }

    /**
     * Add a debug notice.
     *
     * Logs the message and conditionally displays an admin notice.
     *
     * @param string $message The message to log and display.
     * @param string $type The type of notice (e.g., 'error', 'warning', 'success').
     */
    public function add_debug_notice($message, $type = 'error')
    {
        $this->log_error($message);

        if (method_exists($this, 'display_admin_notice') && is_callable([$this, 'display_admin_notice'])) {
            $this->display_admin_notice($message, $type);
        } else {
            $this->log_error("Method 'display_admin_notice' is not callable or does not exist.");
        }
    }

    /**
     * Log errors to a file.
     *
     * @param string $message
     * @param int $trace_limit
     */
    public function log_error($message, $trace_limit = 3)
    {
        $timestamp = date('Y-m-d H:i:s');
        $formatted_message = "[$timestamp] $message\n";
        error_log($formatted_message, 3, self::$log_file);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $trace_limit);
            foreach ($trace as $index => $frame) {
                error_log(sprintf(
                    "[TRACE %d] %s:%s in function %s",
                    $index,
                    $frame['file'] ?? '[internal function]',
                    $frame['line'] ?? 'unknown line',
                    $frame['function'] ?? 'unknown function'
                ), 3, self::$log_file);
            }
        }

        $this->trim_logs();
    }

    /**
     * Display admin notices.
     */
    public static function display_admin_notices()
    {
        if (file_exists(self::$log_file)) {
            $log_contents = @file_get_contents(self::$log_file);
            if ($log_contents) {
                echo '<div class="notice notice-error"><p>AYS Plugin Errors:<pre>' . esc_html($log_contents) . '</pre></p></div>';
            }
        }
    }

    /**
     * Handle wp_die calls.
     *
     * @param string $message
     * @param string $title
     * @param array $args
     */
    public function handle_wp_die($message, $title = '', $args = [])
    {
        $this->log_error('WP Die called with message: ' . print_r($message, true));

        if (is_admin()) {
            echo '<div class="error"><p>' . esc_html($message) . '</p></div>';
        } else {
            _default_wp_die_handler($message, $title, $args);
        }
    }

    /**
     * Clear the error log.
     */
    public static function clear_log()
    {
        if (file_exists(self::$log_file)) {
            unlink(self::$log_file);
        }
    }

    /**
     * Trim the log file to the last 100 lines.
     */
    private function trim_logs()
    {
        if (file_exists(self::$log_file)) {
            $lines = file(self::$log_file, FILE_IGNORE_NEW_LINES);
            if (count($lines) > 100) {
                $lines = array_slice($lines, -100);
                file_put_contents(self::$log_file, implode("\n", $lines) . "\n");
            }
        }
    }
}

// Singleton initialization
$handler = WP_Error_Handler::get_instance();

// Replace wp_die with custom handler
add_filter('wp_die_handler', function () {
    return [WP_Error_Handler::get_instance(), 'handle_wp_die'];
});
