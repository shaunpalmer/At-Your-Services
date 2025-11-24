<?php
// manual-test-permissions.php
// Run this from the terminal: php manual-test-permissions.php

// 1. Load WordPress
// Adjust path to wp-load.php based on your structure
$wp_load_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
if (!file_exists($wp_load_path)) {
    die("Could not find wp-load.php at $wp_load_path\n");
}
require_once $wp_load_path;

echo "WordPress loaded.\n";

// 2. Simulate Admin User
$user_id = 1; // Assuming ID 1 is the main admin
wp_set_current_user($user_id);
$user = wp_get_current_user();
echo "Current User: " . $user->user_login . " (ID: $user_id)\n";

// 3. Check Capabilities
if (current_user_can('manage_options')) {
    echo "Permission Check: PASSED (User has manage_options)\n";
} else {
    echo "Permission Check: FAILED (User missing manage_options)\n";
    print_r($user->caps);
}

// 4. Setup Mock Data for the Handler
// We need a valid invoice ID. Let's grab the first one from the DB.
global $wpdb;
$invoice_table = $wpdb->prefix . 'ays_invoices';
$invoice = $wpdb->get_row("SELECT * FROM $invoice_table LIMIT 1");

if (!$invoice) {
    die("No invoices found to test with. Please create one manually first.\n");
}

$invoice_id = $invoice->id;
echo "Testing with Invoice ID: $invoice_id\n";

// Generate the Nonce exactly as the form does
$nonce_action = 'ays_add_invoice_item_' . $invoice_id;
$nonce_value = wp_create_nonce($nonce_action);
echo "Generated Nonce for action '$nonce_action': $nonce_value\n";

// Mock $_POST
$_POST['invoice_id'] = $invoice_id;
$_POST['ays_add_item_nonce'] = $nonce_value;
$_POST['description'] = 'Automated Test Item ' . time();
$_POST['quantity'] = 1;
$_POST['rate'] = 100.00;
$_POST['taxable'] = 1;

// 5. Call the Handler
// We need to suppress the 'exit' and 'redirect' behavior if possible, 
// but the function has hardcoded exit. 
// Instead, we will replicate the CHECKS here to see if they pass in this CLI environment.

echo "--- Simulating Handler Logic ---\n";

if ( ! is_user_logged_in() ) {
    echo "[FAIL] is_user_logged_in() returned false.\n";
} else {
    echo "[PASS] is_user_logged_in() returned true.\n";
}

if ( ! current_user_can( 'manage_options' ) ) {
    echo "[FAIL] current_user_can('manage_options') returned false.\n";
} else {
    echo "[PASS] current_user_can('manage_options') returned true.\n";
}

$verify_nonce = wp_verify_nonce($_POST['ays_add_item_nonce'], $nonce_action);
if ($verify_nonce) {
    echo "[PASS] Nonce verification passed.\n";
} else {
    echo "[FAIL] Nonce verification failed.\n";
}

// 6. Attempt actual insertion if checks pass
if (is_user_logged_in() && current_user_can('manage_options') && $verify_nonce) {
    echo "--- Attempting DB Insert ---\n";
    $items_table = $wpdb->prefix . 'ays_invoice_items';
    $inserted = $wpdb->insert($items_table, [
        'invoice_id' => $invoice_id,
        'description' => $_POST['description'],
        'quantity' => $_POST['quantity'],
        'rate' => $_POST['rate'],
        'taxable' => 1,
        'hash' => md5(uniqid('test_', true))
    ]);
    
    if ($inserted) {
        echo "[SUCCESS] Item inserted into DB successfully.\n";
    } else {
        echo "[ERROR] DB Insert failed: " . $wpdb->last_error . "\n";
    }
}

echo "Test Complete.\n";
