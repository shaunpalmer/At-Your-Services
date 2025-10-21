<?php
// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Minimal Service Types Admin stub to prevent class-not-found during Settings render.
 * You can expand this later with actual settings UI.
 */
class AYS_Service_Type_Admin {
    public function render_page() {
        echo '<div class="notice notice-info"><p>' . esc_html__( 'Service Types settings coming soon.', 'atyourservice' ) . '</p></div>';
        // Optionally reuse Service Type Service here if needed.
    }
}
