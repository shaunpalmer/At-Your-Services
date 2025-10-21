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
if (isset($argv) && (in_array('--migrate', $argv, true) || in_array('--force-migrate', $argv, true))) {
    if (file_exists(__DIR__ . '/includes/invoices/ays-install-invoices.php')) {
        require_once __DIR__ . '/includes/invoices/ays-install-invoices.php';
        if (function_exists('ays_invoices_install')) {
            if (in_array('--force-migrate', $argv, true)) {
                // Reset version so installer re-runs fully
                delete_option('ays_invoices_db_version');
            }
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

// Optional quick report
if (isset($argv) && in_array('--report', $argv, true)) {
    echo "\n=== Quick Report ===\n";
    $counts = [
        'clients' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ays_clients WHERE (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')"),
        'invoices' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ays_invoices"),
        'items' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ays_items"),
        'invoice_items' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ays_invoice_items"),
        'service_types' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ays_service_types"),
        'invoice_services' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ays_invoice_services"),
        'payments' => (int) ($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = %s", $wpdb->prefix . 'ays_payments'))
            ? $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ays_payments") : 0),
    ];
    printf(
        "Invoices: %d | Clients: %d | Items: %d | Invoice-Items: %d | Service Types: %d | Invoice-Services: %d | Payments: %d\n",
        $counts['invoices'], $counts['clients'], $counts['items'], $counts['invoice_items'], $counts['service_types'], $counts['invoice_services'], $counts['payments']
    );

    // Orphan checks
    echo "Orphan Checks:\n";
    $orph_invoice_items = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ays_invoice_items ii LEFT JOIN {$wpdb->prefix}ays_invoices i ON ii.invoice_id = i.id WHERE i.id IS NULL");
    $orph_invoice_services = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ays_invoice_services ivs LEFT JOIN {$wpdb->prefix}ays_invoices i ON ivs.invoice_id = i.id WHERE i.id IS NULL");
    $orph_payments = 0;
    $has_payments = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = %s", $wpdb->prefix . 'ays_payments'));
    if ($has_payments) {
        $orph_payments = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ays_payments p LEFT JOIN {$wpdb->prefix}ays_invoices i ON p.invoice_id = i.id WHERE i.id IS NULL");
    }
    printf("  - invoice_items without invoice: %d\n", $orph_invoice_items);
    printf("  - invoice_services without invoice: %d\n", $orph_invoice_services);
    printf("  - payments without invoice: %d\n", $orph_payments);
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
    $num = isset($invoice->invoice_number) && $invoice->invoice_number ? $invoice->invoice_number : (isset($invoice->inv_number) ? $invoice->inv_number : '(no number)');
    echo "  - {$num} (\${$invoice->total})\n";
}

echo "\n";
?>
