<?php
/**
 * AYS_Invoice_Admin_UI
 *
 * WordPress admin pages for invoice management.
 * Handles menu registration, page rendering, CRUD forms.
 *
 * @since 1.0
 */

defined('ABSPATH') || exit;

class AYS_Invoice_Admin_UI {
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register_menu'], 20);
    }

    public static function register_menu() {
        // TODO: Implement admin menu
    }
}
