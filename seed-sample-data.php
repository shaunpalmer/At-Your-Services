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

// Service Types (ensure status & sort_order columns when present)
$service_types = [
    ['name' => 'Residential Cleaning', 'description' => 'Standard home cleaning services.', 'status' => 'active', 'sort_order' => 1],
    ['name' => 'Commercial Cleaning', 'description' => 'Cleaning for offices and commercial properties.', 'status' => 'active', 'sort_order' => 2],
    ['name' => 'Specialty Cleaning', 'description' => 'Windows, carpets, and other specialized tasks.', 'status' => 'active', 'sort_order' => 3],
    ['name' => 'Move-out Cleaning', 'description' => 'End of tenancy cleaning.', 'status' => 'active', 'sort_order' => 4],
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

// Get Service Type IDs for items
$res_id = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}ays_service_types WHERE name = 'Residential Cleaning'");
$com_id = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}ays_service_types WHERE name = 'Commercial Cleaning'");
$spec_id = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}ays_service_types WHERE name = 'Specialty Cleaning'");
$move_id = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}ays_service_types WHERE name = 'Move-out Cleaning'");

// Items
$items = [
    ['description' => 'Standard House Clean (3 bed, 2 bath)', 'details' => 'Weekly or bi-weekly cleaning service.', 'service_type_id' => $res_id, 'rate' => 180.00, 'taxable' => 1],
    ['description' => 'Deep Clean / Spring Clean', 'details' => 'Intensive top-to-bottom cleaning.', 'service_type_id' => $res_id, 'rate' => 350.00, 'taxable' => 1],
    ['description' => 'Window Cleaning (Exterior)', 'details' => 'Per-pane exterior window washing.', 'service_type_id' => $spec_id, 'rate' => 15.00, 'taxable' => 1],
    ['description' => 'Carpet Steam Cleaning (per room)', 'details' => 'Professional steam cleaning.', 'service_type_id' => $spec_id, 'rate' => 75.00, 'taxable' => 1],
    ['description' => 'Oven Cleaning', 'details' => 'Deep clean of a standard oven.', 'service_type_id' => $spec_id, 'rate' => 95.00, 'taxable' => 1],
    ['description' => 'Office Clean (per hour)', 'details' => 'Hourly rate for commercial spaces.', 'service_type_id' => $com_id, 'rate' => 55.00, 'taxable' => 1],
    ['description' => 'End of Tenancy Clean (2 bed flat)', 'details' => 'Guaranteed to pass inspection.', 'service_type_id' => $move_id, 'rate' => 450.00, 'taxable' => 1],
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
        'hash' => md5(uniqid()), 'name' => 'Alice Williams', 'email' => 'alice.w@example.com', 'phone' => '021 111 2222', 'mobile' => '0271 111 222', 'address_line1' => '12 Ponsonby Road', 'city' => 'Auckland', 'postcode' => '1011', 'status' => 'active',
    ],
    [
        'hash' => md5(uniqid()), 'name' => 'Bob Brown', 'email' => 'b.brown@example.com', 'phone' => '021 333 4444', 'mobile' => '0272 333 444', 'address_line1' => '45 Cuba Street', 'city' => 'Wellington', 'postcode' => '6011', 'status' => 'active',
    ],
    [
        'hash' => md5(uniqid()), 'name' => 'Charlie Davis', 'email' => 'charlie@example.org', 'phone' => '021 555 6666', 'mobile' => '0273 555 666', 'address_line1' => '78 Riccarton Road', 'city' => 'Christchurch', 'postcode' => '8011', 'status' => 'active',
    ],
    [
        'hash' => md5(uniqid()), 'name' => 'Diana Miller', 'email' => 'diana.m@example.com', 'phone' => '021 777 8888', 'mobile' => '0274 777 888', 'address_line1' => '90 George Street', 'city' => 'Dunedin', 'postcode' => '9016', 'status' => 'inactive',
    ],
     [
        'hash' => md5(uniqid()), 'name' => 'Eva\'s Eatery', 'email' => 'accounts@evaseatery.co.nz', 'phone' => '09 456 7890', 'mobile' => '', 'address_line1' => '1 Commercial Bay', 'city' => 'Auckland', 'postcode' => '1010', 'status' => 'active',
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
function ays_seed_invoice($client_name, $status, $issue_date_modifier, $due_date_modifier, $items_to_add) {
    global $wpdb;

    $client_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}ays_clients WHERE name = %s", $client_name));
    if (!$client_id) {
        echo "<p>✗ Could not find client: $client_name</p>";
        return;
    }

    $invoice_number = date('Y') . '-' . str_pad($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ays_invoices") + 1, 3, '0', STR_PAD_LEFT);
    $exists = $wpdb->get_row($wpdb->prepare("SELECT id FROM {$wpdb->prefix}ays_invoices WHERE invoice_number = %s", $invoice_number));

    if (!$exists) {
        $subtotal = 0;
        $tax_amount = 0;
        
        // Temp array to hold item details for insertion
        $invoice_items_data = [];
        foreach ($items_to_add as $item_desc => $quantity) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT id, rate, taxable FROM {$wpdb->prefix}ays_items WHERE description = %s", $item_desc));
            if ($item) {
                $line_total = $item->rate * $quantity;
                $subtotal += $line_total;
                if ($item->taxable) {
                    $tax_amount += $line_total * 0.15; // Assuming 15% tax
                }
                $invoice_items_data[] = [
                    'item_id' => $item->id,
                    'quantity' => $quantity,
                    'price' => $item->rate,
                    'tax' => $item->taxable ? $line_total * 0.15 : 0,
                ];
            }
        }
        $total = $subtotal + $tax_amount;

        $wpdb->insert("{$wpdb->prefix}ays_invoices", [
            'invoice_number' => $invoice_number,
            'client_id' => $client_id,
            'issue_date' => date('Y-m-d', strtotime($issue_date_modifier)),
            'due_date' => date('Y-m-d', strtotime($due_date_modifier)),
            'subtotal' => $subtotal,
            'tax_amount' => $tax_amount,
            'total' => $total,
            'status' => $status,
            'notes' => "Sample invoice for $client_name.",
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ]);
        $invoice_id = $wpdb->insert_id;

        // Add items to the invoice
        foreach ($invoice_items_data as $item_data) {
            $wpdb->insert("{$wpdb->prefix}ays_invoice_items", [
                'invoice_id' => $invoice_id,
                'item_id' => $item_data['item_id'],
                'quantity' => $item_data['quantity'],
                'price' => $item_data['price'],
                'tax' => $item_data['tax'],
            ]);
        }

        // Link invoice to service types via bridge using item->service_type_id
        $item_service_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT it.service_type_id FROM {$wpdb->prefix}ays_invoice_items ii
             JOIN {$wpdb->prefix}ays_items it ON it.id = ii.item_id
             WHERE ii.invoice_id = %d AND it.service_type_id IS NOT NULL",
            $invoice_id
        ));
        foreach ((array)$item_service_ids as $sid) {
            $sid = intval($sid);
            if ($sid > 0) {
                $exists_link = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}ays_invoice_services WHERE invoice_id = %d AND service_type_id = %d",
                    $invoice_id, $sid
                ));
                if (!$exists_link) {
                    $wpdb->insert("{$wpdb->prefix}ays_invoice_services", [
                        'hash' => md5(uniqid(mt_rand(), true)),
                        'invoice_id' => $invoice_id,
                        'service_type_id' => $sid,
                        'sort_order' => 0,
                    ]);
                }
            }
        }
        echo "<p>✓ Added invoice $invoice_number for $client_name.</p>";
    }
}

// Seed the invoices
ays_seed_invoice('Alice Williams', 'paid', '-45 days', '-15 days', ['Standard House Clean (3 bed, 2 bath)' => 1, 'Oven Cleaning' => 1]);
ays_seed_invoice('Bob Brown', 'sent', '-10 days', '+20 days', ['End of Tenancy Clean (2 bed flat)' => 1]);
ays_seed_invoice('Charlie Davis', 'draft', 'now', '+30 days', ['Carpet Steam Cleaning (per room)' => 3]);
ays_seed_invoice('Eva\'s Eatery', 'sent', '-5 days', '+25 days', ['Office Clean (per hour)' => 4, 'Window Cleaning (Exterior)' => 10]);
ays_seed_invoice('Alice Williams', 'void', '-60 days', '-30 days', ['Deep Clean / Spring Clean' => 1]);

echo "<h3>✓ Done! Refresh the dashboard to see the sample data.</h3>";
echo "<p><a href='admin.php?page=ays-invoicing'>Go to dashboard</a></p>";
?>
