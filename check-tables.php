<?php
/**
 * Check database tables and data
 */
define('WP_USE_THEMES', false);
require(dirname(__FILE__) . '/../../../../wp-load.php');

global $wpdb;

echo "=== AYS Database Tables ===\n\n";

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
