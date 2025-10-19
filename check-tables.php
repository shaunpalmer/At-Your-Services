<?php
/**
 * Check database tables and data
 */
define('WP_USE_THEMES', false);

// Resolve WordPress root robustly
$pluginDir = __DIR__;
$wpLoad = null;

// Common relative path from plugin dir to WP root
$candidates = [
    // Typical: wp-content/plugins/PLUGIN -> wp-load.php is 3 levels up
    $pluginDir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'wp-load.php',
    // XAMPP custom paths may vary; also try document root heuristic
    getenv('DOCUMENT_ROOT') ? rtrim(getenv('DOCUMENT_ROOT'), '\/ ') . DIRECTORY_SEPARATOR . 'wp-load.php' : null,
];

foreach ($candidates as $cand) {
    if ($cand && file_exists($cand)) { $wpLoad = $cand; break; }
}

if (!$wpLoad) {
    fwrite(STDERR, "Unable to locate wp-load.php. Please run via WP context (wp-cli) or adjust path.\n");
    exit(1);
}

require $wpLoad;

global $wpdb;

echo "=== AYS Database Tables ===\n\n";

// Optionally run installer to ensure tables exist and are up to date
if (isset($argv) && in_array('--migrate', $argv, true)) {
    if (file_exists(__DIR__ . '/includes/invoices/ays-install-invoices.php')) {
        require_once __DIR__ . '/includes/invoices/ays-install-invoices.php';
        if (function_exists('ays_invoices_install')) {
            echo "Running invoicing installer (migrations) ...\n";
            ays_invoices_install();
            echo "Done.\n\n";
        }
    }
}

// Check tables
$tables = $wpdb->get_results("SHOW TABLES LIKE '%ays%'");
echo "Tables Found:\n";
foreach ($tables as $table) {
    $table_name = array_values((array)$table)[0];
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    echo "  - $table_name: $count records\n";
}

echo "\n=== Items ===\n";
$items = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ays_items");
echo "Total items: " . count($items) . "\n";
foreach ($items as $item) {
    echo "  - {$item->description} ($" . $item->rate . ")\n";
}

echo "\n=== Clients ===\n";
$clients = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ays_clients WHERE status != 'deleted'");
echo "Total clients: " . count($clients) . "\n";
foreach ($clients as $client) {
    echo "  - {$client->name} ({$client->email})\n";
}

echo "\n=== Invoices ===\n";
$invoices = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ays_invoices");
echo "Total invoices: " . count($invoices) . "\n";
foreach ($invoices as $invoice) {
    echo "  - {$invoice->invoice_number} (\${$invoice->total})\n";
}

echo "\n";
?>
