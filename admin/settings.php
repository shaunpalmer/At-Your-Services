<?php
/**
 * Email Notification & Validation Settings Page
 * Creates an admin page under Leads > Settings
 */

if (!defined('ABSPATH')) { exit; }

class Ays_Notification_Settings_Page {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page'], 20);
    }
    
    public function add_settings_page() {
        add_submenu_page(
            'edit.php?post_type=ays_lead',
            __('Email Notification Settings', 'ays'),
            __('Settings', 'ays'),
            'manage_options',
            'ays-notification-settings',
            [$this, 'render_settings_page']
        );
    }
    
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('ays_notifications');
                do_settings_sections('ays_notifications');
                submit_button(__('Save Settings', 'ays'));
                ?>
            </form>
            
            <div class="notice notice-info" style="margin-top: 20px;">
                <p><strong>Available Tokens:</strong></p>
                <ul>
                    <li><code>{name}</code> - Lead's name</li>
                    <li><code>{email}</code> - Lead's email</li>
                    <li><code>{phone}</code> - Lead's phone</li>
                    <li><code>{service}</code> - Service requested</li>
                    <li><code>{booking_date}</code> - Preferred date</li>
                    <li><code>{booking_time}</code> - Preferred time</li>
                    <li><code>{notes}</code> - Additional notes</li>
                    <li><code>[all-fields]</code> - All form fields formatted as a list</li>
                    <li><code>{admin_url}</code> - Link to the lead in admin</li>
                </ul>
            </div>
        </div>
        <?php
    }
}

// Initialize the settings page
new Ays_Notification_Settings_Page();