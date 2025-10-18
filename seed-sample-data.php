<?php
/**
 * Seed sample data for testing
 * Place this in the plugin root and visit it to run
 */

// Load WordPress
define('WP_USE_THEMES', false);
require(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');

if (!current_user_can('manage_options')) {
    wp_die('Not authorized');
}

global $wpdb;

echo "<h2>Seeding Sample Data...</h2>";

// Service Types
$service_types = [
    ['name' => 'Cleaning', 'description' => 'Cleaning services'],
    ['name' => 'Plumbing', 'description' => 'Plumbing services'],
    ['name' => 'Gardening', 'description' => 'Garden work'],
];

foreach ($service_types as $st) {
    $exists = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}ays_service_types WHERE name = %s",
        $st['name']
    ));
    
    if (!$exists) {
        $wpdb->insert("{$wpdb->prefix}ays_service_types", $st);
        echo "<p>✓ Added service type: {$st['name']}</p>";
    }
}

// Items
$items = [
    ['description' => 'Carpet Cleaning', 'details' => 'Per room', 'service_type_id' => 1, 'rate' => 49.99, 'taxable' => 1],
    ['description' => 'House Cleaning', 'details' => 'Full house', 'service_type_id' => 1, 'rate' => 199.99, 'taxable' => 1],
    ['description' => 'Drain Cleaning', 'details' => 'Per drain', 'service_type_id' => 2, 'rate' => 99.99, 'taxable' => 1],
    ['description' => 'Lawn Mowing', 'details' => 'Per visit', 'service_type_id' => 3, 'rate' => 79.99, 'taxable' => 1],
];

foreach ($items as $item) {
    $exists = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}ays_items WHERE description = %s",
        $item['description']
    ));
    
    if (!$exists) {
        $wpdb->insert("{$wpdb->prefix}ays_items", $item);
        echo "<p>✓ Added item: {$item['description']}</p>";
    }
}

// Clients
$clients = [
    [
        'hash' => md5(uniqid()),
        'name' => 'John Smith',
        'email' => 'john@example.com',
        'phone' => '021 234 5678',
        'mobile' => '0271 234 567',
        'address_line1' => '123 Main St',
        'city' => 'Auckland',
        'postcode' => '1010',
        'status' => 'active',
    ],
    [
        'hash' => md5(uniqid()),
        'name' => 'Sarah Johnson',
        'email' => 'sarah@business.com',
        'phone' => '021 345 6789',
        'mobile' => '0272 345 678',
        'address_line1' => '456 Park Ave',
        'city' => 'Wellington',
        'postcode' => '6011',
        'status' => 'active',
    ],
];

foreach ($clients as $client) {
    $exists = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}ays_clients WHERE email = %s",
        $client['email']
    ));
    
    if (!$exists) {
        $wpdb->insert("{$wpdb->prefix}ays_clients", $client);
        echo "<p>✓ Added client: {$client['name']}</p>";
    }
}

// Invoices
$client_id = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}ays_clients LIMIT 1");
if ($client_id) {
    $invoice_number = date('Y') . '-001';
    $exists = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}ays_invoices WHERE invoice_number = %s",
        $invoice_number
    ));
    
    if (!$exists) {
        $wpdb->insert("{$wpdb->prefix}ays_invoices", [
            'invoice_number' => $invoice_number,
            'client_id' => $client_id,
            'issue_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'subtotal' => 100.00,
            'tax_amount' => 15.00,
            'total' => 115.00,
            'status' => 'draft',
            'notes' => 'Sample invoice',
        ]);
        echo "<p>✓ Added invoice: $invoice_number</p>";
    }
}

echo "<h3>✓ Done! Refresh the dashboard to see the sample data.</h3>";
echo "<p><a href='admin.php?page=ays_invoicing'>Go to dashboard</a></p>";
?>
