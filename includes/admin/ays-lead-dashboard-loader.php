<?php
// Bootstrap the Lead Dashboard admin class for the 'Leads' CPT menu
add_action('plugins_loaded', function() {
    // Always load the export page class so the menu item is present
    if (file_exists(AYS_PLUGIN_PATH . 'admin/class-ays-leads-export.php')) {
        require_once AYS_PLUGIN_PATH . 'admin/class-ays-leads-export.php';
        if (class_exists('Ays_Leads_Export_Page')) {
            new Ays_Leads_Export_Page();
        }
    }
    if (file_exists(AYS_PLUGIN_PATH . 'admin/class-ays-lead-dashboard.php')) {
        require_once AYS_PLUGIN_PATH . 'admin/class-ays-lead-dashboard.php';
        if (class_exists('Ays_Lead_Dashboard_Admin')) {
            new Ays_Lead_Dashboard_Admin();
        }
    }
});
