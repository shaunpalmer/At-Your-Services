<?php
/**
 * Test form submission flow - simulates actual WordPress admin-post.php behavior
 */

// Load WordPress
require_once 'C:/xampp/htdocs/projectstudios/wp-load.php';

echo "=== Invoice Item Form Submission Test ===\n\n";

// Check admin context
echo "TEST 1: Admin Context Check\n";
echo "  - is_admin(): " . (is_admin() ? "YES" : "NO") . "\n";
echo "  - current_user_id(): " . get_current_user_id() . "\n";
echo "  - is_user_logged_in(): " . (is_user_logged_in() ? "YES" : "NO") . "\n";
echo "  - current_user_can('manage_options'): " . (current_user_can('manage_options') ? "YES" : "NO") . "\n\n";

// Simulate the admin-post.php flow
echo "TEST 2: Simulate admin-post.php Flow\n";

// Prepare form data like a real submission
$test_data = [
    'action' => 'ays_add_invoice_item',
    'invoice_id' => 1,
    'description' => 'Test Item from Form',
    'quantity' => '2.50',
    'rate' => '100.00',
    'taxable' => '1'
];

// Get nonce for invoice 1
$nonce = wp_create_nonce('ays_add_invoice_item_1');
$test_data['ays_add_item_nonce'] = $nonce;

echo "  - Action: " . $test_data['action'] . "\n";
echo "  - Nonce: " . $nonce . "\n";
echo "  - Data prepared: " . json_encode($test_data) . "\n\n";

// Simulate $_POST and $_REQUEST like admin-post.php does
echo "TEST 3: Admin-post.php Simulation\n";
echo "  - Checking if action='ays_add_invoice_item' would fire...\n";

// Check if action hook exists
$hook = 'admin_post_' . $test_data['action'];
echo "  - Looking for hook: $hook\n";

// Get list of registered hooks
global $wp_filter;
if (isset($wp_filter[$hook])) {
    echo "  - Hook IS REGISTERED ✓\n";
    echo "  - Hook priority: " . array_keys($wp_filter[$hook]->callbacks)[0] . "\n";
    echo "  - Number of callbacks: " . count($wp_filter[$hook]->callbacks) . "\n";
} else {
    echo "  - Hook NOT FOUND ✗\n";
}

// Check if class exists
echo "\nTEST 4: Class & Method Existence\n";
if (class_exists('AYS_Invoices_Tab')) {
    echo "  - AYS_Invoices_Tab class: EXISTS ✓\n";
    if (method_exists('AYS_Invoices_Tab', 'handle_add_invoice_item')) {
        echo "  - handle_add_invoice_item method: EXISTS ✓\n";
        echo "  - Method is static: " . (is_callable(['AYS_Invoices_Tab', 'handle_add_invoice_item']) ? "YES" : "NO") . "\n";
    } else {
        echo "  - handle_add_invoice_item method: NOT FOUND ✗\n";
    }
} else {
    echo "  - AYS_Invoices_Tab class: NOT FOUND ✗\n";
}

// Test nonce verification in context
echo "\nTEST 5: Nonce Verification Test\n";
$_REQUEST['ays_add_item_nonce'] = $nonce;
$_POST['invoice_id'] = 1;

try {
    // This should work or tell us what's wrong
    check_admin_referer('ays_add_invoice_item_1', 'ays_add_item_nonce');
    echo "  - Nonce verification: PASSED ✓\n";
} catch (Exception $e) {
    echo "  - Nonce verification: FAILED\n";
    echo "  - Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>
