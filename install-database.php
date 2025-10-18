<?php
/**
 * Manual Database Installation Trigger
 * 
 * Run this file once to create all invoicing database tables
 * URL: http://projectstudios.local/wp-content/plugins/At-Your-Services/install-database.php
 * 
 * This creates: clients, items, invoices, invoice_items, payments, email_templates, email_log
 */

// Load WordPress
define('WP_USE_THEMES', false);
$wp_load = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';

if (!file_exists($wp_load)) {
    die('WordPress not found. Check file path.');
}

require($wp_load);

// Security: only admins
if (!current_user_can('manage_options')) {
    wp_die('<h1>Error: Not authorized</h1><p>You must be an administrator to run this.</p>');
}

echo '<h1>AYS Invoicing Database Installation</h1>';
echo '<p>Creating database tables...</p>';
echo '<hr>';

// Load and run the installer
require_once(dirname(__FILE__) . '/includes/invoices/ays-install-invoices.php');

// Call the installer function
ays_invoices_install();

echo '<p style="color: green; font-size: 18px;"><strong>✓ Installation complete!</strong></p>';

// Verify tables were created
global $wpdb;
$tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}ays_%'");

if (empty($tables)) {
    echo '<p style="color: red;"><strong>ERROR: No tables created!</strong></p>';
} else {
    echo '<h2>✓ Tables Created:</h2>';
    echo '<ul style="font-size: 16px;">';
    foreach ($tables as $table) {
        $table_name = array_values((array)$table)[0];
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        echo '<li><code>' . esc_html($table_name) . '</code> (' . intval($count) . ' rows)</li>';
    }
    echo '</ul>';
}

echo '<hr>';
echo '<p><strong>Next steps:</strong></p>';
echo '<ol style="font-size: 16px;">';
echo '<li>Go to <a href="' . admin_url('admin.php?page=ays_invoicing_items') . '">Items tab</a> to create your first item</li>';
echo '<li>Go to <a href="' . admin_url('admin.php?page=ays_invoicing_clients') . '">Clients tab</a> to add a client</li>';
echo '<li>Go to <a href="' . admin_url('admin.php?page=ays_invoicing') . '">Invoices tab</a> to create an invoice</li>';
echo '</ol>';

echo '<hr>';
echo '<p style="color: blue;"><em>You can now delete this file (install-database.php) - it is no longer needed.</em></p>';
?>
