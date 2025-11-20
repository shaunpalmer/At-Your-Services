<?php
/**
 * Quick smoke test for the client dashboard Recent Invoices widget.
 *
 * Usage: php test-client-dashboard.php
 */

define('WP_USE_THEMES', false);
require dirname(__FILE__, 4) . '/wp-load.php';
require_once __DIR__ . '/includes/client/class-ays-client-dashboard.php';

$login = 'clientportal_tester';
$email = 'alice.w@example.com';
$name  = 'Alice Williams Portal Tester';

$user = get_user_by('login', $login);
if (!$user) {
    $user_id = wp_insert_user([
        'user_login' => $login,
        'user_pass'  => wp_generate_password(16, true, true),
        'user_email' => $email,
        'display_name' => $name,
        'role' => 'customer',
    ]);
    if (!is_wp_error($user_id)) {
        $user = get_user_by('id', $user_id);
    } else {
        $user = get_user_by('email', $email);
    }
}

if (!$user) {
    fwrite(STDERR, "Unable to prepare a portal test user.\n");
    exit(1);
}

// Ensure customer role (if present) has portal capabilities for the session.
$customer_role = get_role('customer');
if ($customer_role) {
    if (!$customer_role->has_cap('access_customer_dashboard')) {
        $customer_role->add_cap('access_customer_dashboard');
    }
    if (!$customer_role->has_cap('access_client_portal')) {
        $customer_role->add_cap('access_client_portal');
    }
}

// Guarantee the specific user can load the portal regardless of role config.
$wp_user = new WP_User($user->ID);
$wp_user->add_cap('access_customer_dashboard');
$wp_user->add_cap('access_client_portal');

// Link the WP user to the seeded Alice Williams client via explicit meta.
global $wpdb;
$client_id = $wpdb->get_var($wpdb->prepare(
    "SELECT id FROM {$wpdb->prefix}ays_clients WHERE email = %s LIMIT 1",
    $email
));
if ($client_id) {
    update_user_meta($user->ID, 'ays_client_id', (int) $client_id);
}

wp_set_current_user($user->ID);

ob_start();
AYS_Client_Dashboard::render_client_dashboard();
$html = ob_get_clean();

$has_recent = strpos($html, 'Recent Invoices') !== false;
$invoice_links = substr_count($html, 'View / Print');
preg_match_all('/([0-9]{4}-[0-9]{3}|INV-[0-9]+)/', $html, $matches);
$invoice_numbers = array_unique($matches[1] ?? []);

$summary = [
    'current_user' => $user->user_login,
    'client_id' => $client_id ?: 'unknown',
    'recent_widget' => $has_recent ? 'present' : 'missing',
    'invoice_rows' => $invoice_links,
    'invoice_numbers' => array_slice($invoice_numbers, 0, 5),
];

echo "Client Portal Dashboard Smoke Test\n";
foreach ($summary as $label => $value) {
    if (is_array($value)) {
        $value = $value ? implode(', ', $value) : 'none';
    }
    echo sprintf("- %s: %s\n", $label, $value);
}

$all_good = $has_recent && $invoice_links > 0;

if (!$all_good) {
    // Provide a compact snippet of the HTML for context.
    $snippet = trim(strip_tags($html));
    $snippet = substr($snippet, 0, 400);
    echo "\nRendered HTML snippet:\n" . $snippet . "\n";
}

exit($all_good ? 0 : 1);
