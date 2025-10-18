<?php
define('WP_USE_THEMES', false);
require('C:/xampp/htdocs/projectstudios/wp-load.php');

echo "=== Checking Notification Settings ===\n\n";

$opts = get_option('ays_notifications_settings');
echo "Settings stored in database:\n";
var_dump($opts);

echo "\n=== Checking Admin Email ===\n";
$admin_email = get_option('admin_email');
echo "Admin email: " . $admin_email . "\n";

echo "\n=== Checking for Leads ===\n";
$recent_leads = get_posts([
    'post_type' => 'ays_lead',
    'posts_per_page' => 1,
    'orderby' => 'date',
    'order' => 'DESC',
]);

if ($recent_leads) {
    $lead = $recent_leads[0];
    echo "Most recent lead ID: " . $lead->ID . "\n";
    echo "Lead email: " . get_post_meta($lead->ID, 'ays_email', true) . "\n";
    echo "Lead name: " . get_post_meta($lead->ID, 'ays_name', true) . "\n";
} else {
    echo "No leads found.\n";
}
