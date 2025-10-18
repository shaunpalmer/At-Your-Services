<?php
/**
 * Check if WP-Cron jobs are scheduled for email validation
 */

// Load WordPress
require_once dirname(__FILE__) . '/../../../wp-load.php';

echo "==================================================\n";
echo "WP-Cron Status Check\n";
echo "==================================================\n\n";

// Get all scheduled cron jobs
$crons = _get_cron_array();

echo "Looking for 'ays_validate_lead_email' cron jobs...\n\n";

$found = 0;
foreach ($crons as $timestamp => $cron) {
    if (isset($cron['ays_validate_lead_email'])) {
        $found++;
        $time_until = $timestamp - time();
        $date = date('Y-m-d H:i:s', $timestamp);
        
        echo "✓ Found scheduled validation job:\n";
        echo "  Scheduled for: $date\n";
        echo "  Time until run: $time_until seconds\n";
        
        foreach ($cron['ays_validate_lead_email'] as $job) {
            if (isset($job['args'][0])) {
                $lead_id = $job['args'][0];
                echo "  Lead ID: $lead_id\n";
                
                // Get the email for this lead
                $email = get_post_meta($lead_id, 'ays_email', true);
                echo "  Email: $email\n";
            }
        }
        echo "\n";
    }
}

if ($found === 0) {
    echo "❌ No validation cron jobs found!\n";
    echo "This means the validation isn't being scheduled when leads are created.\n\n";
} else {
    echo "Found $found scheduled validation job(s).\n\n";
}

// Check if WP-Cron is disabled
if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
    echo "⚠️  WARNING: WP-Cron is DISABLED!\n";
    echo "You need to set up a real cron job or enable WP-Cron.\n";
} else {
    echo "✓ WP-Cron is enabled.\n";
}

echo "\n==================================================\n";
echo "Recent Leads:\n";
echo "==================================================\n\n";

// Get the 5 most recent leads
$leads = get_posts([
    'post_type' => 'ays_lead',
    'posts_per_page' => 5,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC'
]);

foreach ($leads as $lead) {
    $email = get_post_meta($lead->ID, 'ays_email', true);
    $status = get_post_meta($lead->ID, '_ays_email_validation_status', true);
    echo "Lead ID: {$lead->ID}\n";
    echo "  Email: $email\n";
    echo "  Status: " . ($status ? $status : 'not set') . "\n";
    echo "  Created: {$lead->post_date}\n\n";
}
