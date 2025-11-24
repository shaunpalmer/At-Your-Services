<?php
/**
 * Direct Test Script - No PHPUnit Framework Required
 * Run: php test-invoice-directly.php
 */

// Load WordPress
$wp_load = dirname(__FILE__) . '/../../wp-load.php';
if (!file_exists($wp_load)) {
    // Try alternate path
    $wp_load = 'C:/xampp/htdocs/projectstudios/wp-load.php';
}
if (!file_exists($wp_load)) {
    die("ERROR: wp-load.php not found at $wp_load\n");
}
require_once $wp_load;

echo "=== Invoice Handler Diagnostic Test ===\n\n";

// Test 1: User Authentication
echo "TEST 1: User Authentication\n";
wp_set_current_user(1);
echo "  - Logged in as: " . wp_get_current_user()->user_login . "\n";
echo "  - User ID: " . get_current_user_id() . "\n";
echo "  - Has manage_options: " . (current_user_can('manage_options') ? 'YES' : 'NO') . "\n\n";

// Test 2: Database Schema
echo "TEST 2: Database Table Schema\n";
global $wpdb;
$items_table = $wpdb->prefix . 'ays_invoice_items';
$columns = $wpdb->get_results("DESCRIBE $items_table");

if ($columns) {
    echo "  Table: $items_table\n";
    echo "  Columns:\n";
    foreach ($columns as $col) {
        echo "    - " . $col->Field . " (" . $col->Type . ")\n";
    }
} else {
    die("  ERROR: Could not describe table: " . $wpdb->last_error . "\n");
}
echo "\n";

// Test 3: Get Sample Invoice
echo "TEST 3: Sample Invoice\n";
$invoices_table = $wpdb->prefix . 'ays_invoices';
$invoice = $wpdb->get_row("SELECT * FROM $invoices_table LIMIT 1");

if ($invoice) {
    echo "  Found invoice ID: " . $invoice->id . "\n";
    echo "  Client ID: " . $invoice->client_id . "\n";
} else {
    die("  ERROR: No invoices found in database\n");
}
echo "\n";

// Test 4: Nonce Generation
echo "TEST 4: Nonce Generation & Verification\n";
$invoice_id = $invoice->id;
$nonce_action = 'ays_add_invoice_item_' . $invoice_id;
$nonce = wp_create_nonce($nonce_action);

echo "  Action: $nonce_action\n";
echo "  Nonce: $nonce\n";

$verified = wp_verify_nonce($nonce, $nonce_action);
echo "  Verification: " . ($verified ? 'PASSED' : 'FAILED') . "\n\n";

// Test 5: Simulate Form Submission
echo "TEST 5: Simulate Form Data\n";
$_POST = [
    'invoice_id' => $invoice_id,
    'ays_add_item_nonce' => $nonce,
    'description' => 'Test Line Item',
    'quantity' => 2,
    'rate' => 100.00,
    'taxable' => 1,
];

echo "  POST['invoice_id']: " . $_POST['invoice_id'] . "\n";
echo "  POST['description']: " . $_POST['description'] . "\n";
echo "  POST['quantity']: " . $_POST['quantity'] . "\n";
echo "  POST['rate']: " . $_POST['rate'] . "\n";
echo "  Nonce field name: ays_add_item_nonce\n\n";

// Test 6: Check Handler Class
echo "TEST 6: Handler Class\n";
if (class_exists('AYS_Invoices_Tab')) {
    echo "  Class exists: YES\n";
    echo "  Methods:\n";
    echo "    - handle_add_invoice_item: " . (method_exists('AYS_Invoices_Tab', 'handle_add_invoice_item') ? 'EXISTS' : 'MISSING') . "\n";
    echo "    - handle_update_invoice_item: " . (method_exists('AYS_Invoices_Tab', 'handle_update_invoice_item') ? 'EXISTS' : 'MISSING') . "\n";
    echo "    - handle_delete_invoice_item: " . (method_exists('AYS_Invoices_Tab', 'handle_delete_invoice_item') ? 'EXISTS' : 'MISSING') . "\n";
} else {
    die("  ERROR: AYS_Invoices_Tab class not found\n");
}
echo "\n";

// Test 7: Try Database Insert
echo "TEST 7: Database Insert\n";
$qty_column = null;

// Check which column exists
$column_check = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$items_table' AND COLUMN_NAME IN ('qty', 'quantity')");
if ($column_check) {
    $qty_column = $column_check[0]->COLUMN_NAME;
    echo "  Using column: $qty_column\n";
} else {
    die("  ERROR: Neither 'qty' nor 'quantity' column found\n");
}

$insert_data = [
    'invoice_id' => $invoice_id,
    'description' => 'PHPUnit Test Item ' . time(),
    'hash' => md5(uniqid('test_', true)),
    $qty_column => 2,
    'rate' => 50.00,
    'taxable' => 1,
    'line_total' => 100.00,
    'line_tax' => 10.00,
    'status' => 'active',
    'created_at' => current_time('mysql'),
    'updated_at' => current_time('mysql'),
];

$inserted = $wpdb->insert($items_table, $insert_data);
if ($inserted) {
    echo "  Insert: SUCCESS\n";
    echo "  New Item ID: " . $wpdb->insert_id . "\n";
} else {
    echo "  Insert: FAILED\n";
    echo "  Error: " . $wpdb->last_error . "\n";
}

echo "\n=== Test Complete ===\n";
