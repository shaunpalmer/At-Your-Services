<?php
/**
 * Seed sample data for testing
 * Place this in the plugin root and visit it to run
 */

// Load WordPress
define('WP_USE_THEMES', false);
require(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');

// Allow CLI execution for local/dev convenience
$AYS_SEED_IS_CLI = (php_sapi_name() === 'cli');

if (!$AYS_SEED_IS_CLI && !current_user_can('manage_options')) {
    wp_die('Not authorized');
}

global $wpdb;

if ($AYS_SEED_IS_CLI) {
    echo "Seeding Sample Data...\n";
} else {
    echo "<h2>Seeding Sample Data...</h2>";
}

// Helper: check if a column exists
function ays_col_exists($table, $column) {
    global $wpdb;
    $table = esc_sql($table);
    $column = esc_sql($column);
    return (bool) $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM $table LIKE %s", $column));
}

// Helper: make slug
function ays_slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('~[^a-z0-9]+~', '-', $text);
    return trim($text, '-');
}

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
        $to_insert = $st;
        $to_insert['hash'] = md5(uniqid(mt_rand(), true));
        if (ays_col_exists("{$wpdb->prefix}ays_service_types", 'slug')) {
            $to_insert['slug'] = ays_slugify($st['name']);
        }
    $wpdb->insert("{$wpdb->prefix}ays_service_types", $to_insert);
    echo $AYS_SEED_IS_CLI ? "✓ Added service type: {$st['name']}\n" : "<p>✓ Added service type: {$st['name']}</p>";
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
        $to_insert = $item;
        $to_insert['hash'] = md5(uniqid(mt_rand(), true));
        if (!isset($to_insert['status'])) { $to_insert['status'] = 'active'; }
    $wpdb->insert("{$wpdb->prefix}ays_items", $to_insert);
    echo $AYS_SEED_IS_CLI ? "✓ Added item: {$item['description']}\n" : "<p>✓ Added item: {$item['description']}</p>";
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
        echo isset($AYS_SEED_IS_CLI) && $AYS_SEED_IS_CLI ? "✓ Added client: {$client['name']}\n" : "<p>✓ Added client: {$client['name']}</p>";
    }
}

// Ensure each seeded client has a matching WordPress user for the portal
ays_seed_portal_users($clients);

function ays_seed_portal_users($clients) {
    global $wpdb, $AYS_SEED_IS_CLI;

    if (empty($clients)) {
        return;
    }

    $default_password = 'ClientPortal!23';
    foreach ($clients as $client) {
        $client_row = $wpdb->get_row($wpdb->prepare(
            "SELECT id, email, name FROM {$wpdb->prefix}ays_clients WHERE email = %s LIMIT 1",
            $client['email']
        ));
        if (!$client_row) {
            continue;
        }

        $user = get_user_by('email', $client_row->email);
        $created = false;
        if ($user && strpos($user->user_login, '.') === 0) {
            require_once ABSPATH . 'wp-admin/includes/user.php';
            wp_delete_user($user->ID);
            $user = null;
        }
        if (!$user) {
            $login = sanitize_user(preg_replace('~[^a-z0-9]+~', '.', strtolower($client_row->name)));
            $login = trim($login, '.-_' );
            if (!$login) {
                $login = 'client_' . (int) $client_row->id;
            }
            $base_login = $login;
            $suffix = 1;
            while (username_exists($login)) {
                $login = $base_login . $suffix;
                $suffix++;
            }

            $user_id = wp_insert_user([
                'user_login' => $login,
                'user_pass'  => $default_password,
                'user_email' => $client_row->email,
                'display_name' => $client_row->name,
                'role' => 'customer',
            ]);
            if (is_wp_error($user_id)) {
                $message = '⚠️ Failed to create portal user for ' . $client_row->name . ': ' . $user_id->get_error_message();
                echo $AYS_SEED_IS_CLI ? $message . "\n" : '<p>' . esc_html($message) . '</p>';
                continue;
            }
            $created = true;
            $user = get_user_by('id', $user_id);
        }

        if (!$user) {
            continue;
        }

        update_user_meta($user->ID, 'ays_client_id', (int) $client_row->id);

        $wp_user = new WP_User($user->ID);
        $wp_user->add_cap('access_customer_dashboard');
        $wp_user->add_cap('access_client_portal');

        $message = ($created ? '✓ Created portal user ' : '• Refreshed portal user ') . $user->user_login . ' for ' . $client_row->name;
        if ($created) {
            $message .= " (password: {$default_password})";
        }
        echo $AYS_SEED_IS_CLI ? $message . "\n" : '<p>' . esc_html($message) . '</p>';
    }
}

// Invoices
function ays_seed_invoice($client_name, $status, $issue_date_modifier, $due_date_modifier, $items_to_add) {
    global $wpdb, $AYS_SEED_IS_CLI;

    $client_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}ays_clients WHERE name = %s", $client_name));
    if (!$client_id) {
        echo $AYS_SEED_IS_CLI ? "✗ Could not find client: $client_name\n" : "<p>✗ Could not find client: $client_name</p>";
        return;
    }

    $next_num = str_pad($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ays_invoices") + 1, 3, '0', STR_PAD_LEFT);
    $invoice_number = date('Y') . '-' . $next_num;
    // Detect column name for invoice number
    $col_inv_num = ays_col_exists("{$wpdb->prefix}ays_invoices", 'invoice_number') ? 'invoice_number' : (ays_col_exists("{$wpdb->prefix}ays_invoices", 'inv_number') ? 'inv_number' : null);
    $exists = null;
    if ($col_inv_num) {
        $exists = $wpdb->get_row($wpdb->prepare("SELECT id FROM {$wpdb->prefix}ays_invoices WHERE $col_inv_num = %s", $invoice_number));
    }

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
                    'rate' => $item->rate,
                ];
            }
        }
        $total = $subtotal + $tax_amount;

        $insert_invoice = [
            'client_id' => $client_id,
            'issue_date' => date('Y-m-d', strtotime($issue_date_modifier)),
            'due_date' => date('Y-m-d', strtotime($due_date_modifier)),
            'total' => $total,
            'status' => $status,
            'notes' => "Sample invoice for $client_name.",
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
            'hash' => md5(uniqid(mt_rand(), true)),
        ];
        if ($col_inv_num) { $insert_invoice[$col_inv_num] = $invoice_number; }
        // Handle subtotal/tax naming differences
        if (ays_col_exists("{$wpdb->prefix}ays_invoices", 'subtotal')) {
            $insert_invoice['subtotal'] = $subtotal;
        }
        if (ays_col_exists("{$wpdb->prefix}ays_invoices", 'tax_amount')) {
            $insert_invoice['tax_amount'] = $tax_amount;
        } elseif (ays_col_exists("{$wpdb->prefix}ays_invoices", 'tax_total')) {
            $insert_invoice['tax_total'] = $tax_amount;
        }
        $wpdb->insert("{$wpdb->prefix}ays_invoices", $insert_invoice);
        $invoice_id = $wpdb->insert_id;

        // Add items to the invoice
        foreach ($invoice_items_data as $item_data) {
            $insert_item = [
                'invoice_id' => $invoice_id,
                'item_id' => $item_data['item_id'],
                'hash' => md5(uniqid(mt_rand(), true)),
            ];
            // quantity/qty
            if (ays_col_exists("{$wpdb->prefix}ays_invoice_items", 'quantity')) {
                $insert_item['quantity'] = $item_data['quantity'];
            } elseif (ays_col_exists("{$wpdb->prefix}ays_invoice_items", 'qty')) {
                $insert_item['qty'] = $item_data['quantity'];
            }
            // rate/price
            if (ays_col_exists("{$wpdb->prefix}ays_invoice_items", 'rate')) {
                $insert_item['rate'] = $item_data['rate'];
            } elseif (ays_col_exists("{$wpdb->prefix}ays_invoice_items", 'price')) {
                $insert_item['price'] = $item_data['rate'];
            }
            // optional computed columns if present
            $line_total = $item_data['rate'] * $item_data['quantity'];
            $line_tax = $line_total * 0.15; // keep in sync with above assumption
            if (ays_col_exists("{$wpdb->prefix}ays_invoice_items", 'line_total')) {
                $insert_item['line_total'] = $line_total;
            }
            if (ays_col_exists("{$wpdb->prefix}ays_invoice_items", 'line_tax')) {
                $insert_item['line_tax'] = $line_tax;
            } elseif (ays_col_exists("{$wpdb->prefix}ays_invoice_items", 'tax')) {
                $insert_item['tax'] = $line_tax;
            }
            $wpdb->insert("{$wpdb->prefix}ays_invoice_items", $insert_item);
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
    echo $AYS_SEED_IS_CLI ? "✓ Added invoice $invoice_number for $client_name.\n" : "<p>✓ Added invoice $invoice_number for $client_name.</p>";
    }
}

// Seed the invoices
ays_seed_invoice('Alice Williams', 'paid', '-45 days', '-15 days', ['Standard House Clean (3 bed, 2 bath)' => 1, 'Oven Cleaning' => 1]);
ays_seed_invoice('Bob Brown', 'sent', '-10 days', '+20 days', ['End of Tenancy Clean (2 bed flat)' => 1]);
ays_seed_invoice('Charlie Davis', 'draft', 'now', '+30 days', ['Carpet Steam Cleaning (per room)' => 3]);
ays_seed_invoice('Eva\'s Eatery', 'sent', '-5 days', '+25 days', ['Office Clean (per hour)' => 4, 'Window Cleaning (Exterior)' => 10]);
ays_seed_invoice('Alice Williams', 'void', '-60 days', '-30 days', ['Deep Clean / Spring Clean' => 1]);

if ($AYS_SEED_IS_CLI) {
    echo "✓ Done!\n";
} else {
    echo "<h3>✓ Done! Refresh the dashboard to see the sample data.</h3>";
    echo "<p><a href='admin.php?page=ays-invoicing'>Go to dashboard</a></p>";
}
?>
