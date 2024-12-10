<?php
namespace ays\includes\helpers;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class WP_Error_Admin_Notices
 *
 * Manages the display of admin notices for errors, warnings, success messages, and informational messages
 * in the WordPress admin interface.
 *
 * @package AtYourService(AYS)
 */
class WP_Error_Admin_Notices {
    /**
     * Display an admin notice.
     *
     * @param string $message The message to display.
     * @param string $type    The type of notice (error, warning, success, info).
     */
    public static function display_notice($message, $type = 'error') {
        if (!in_array($type, ['error', 'warning', 'success', 'info'])) {
            $type = 'info'; // Default to info if an unsupported type is provided.
        }

        printf(
            '<div class="notice notice-%s is-dismissible">
                <p>%s</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">%s</span>
                </button>
            </div>',
            esc_attr($type),
            esc_html($message),
            esc_html__('Dismiss this notice', 'text-domain')
        );
    }

    /**
     * Hook into admin_notices to display a notice.
     *
     * @param string $message The message to display.
     * @param string $type    The type of notice (error, warning, success, info).
     */
    public static function enqueue_notice($message, $type = 'error') {
        add_action('admin_notices', function () use ($message, $type) {
            self::display_notice($message, $type);
        });
    }
}

// Example Usage
if (is_admin()) {
    // Example error notice
    WP_Error_Admin_Notices::enqueue_notice('There has been an error.', 'error');
    // Example warning notice
    WP_Error_Admin_Notices::enqueue_notice('This is a warning message.', 'warning');
    // Example success notice
    WP_Error_Admin_Notices::enqueue_notice('This is a success message.', 'success');
    // Example informational notice
    WP_Error_Admin_Notices::enqueue_notice('This is some information.', 'info');
} 
/**
 * Class WP_Error_Admin_Notices
 *
 * This class manages the display of admin notices for errors and is responsible for reporting them to the WordPress admin interface.
 *
 * @package AtYourService(AYS);
 * 
 * <div class="notice notice-error is-dismissible">
    <p>There has been an error.</p>
</div>

<div class="notice notice-warning is-dismissible">
    <p>This is a warning message.</p>
</div>

<div class="notice notice-success is-dismissible">
    <p>This is a success message.</p>
</div>

<div class="notice notice-info is-dismissible">
    <p>This is some information.</p>
</div>
 */