<?php
/**
 * Manually trigger validation for recent leads
 */

// Load WordPress
require_once dirname(__FILE__) . '/../../../wp-load.php';

echo "==================================================\n";
echo "Manual Validation Trigger\n";
echo "==================================================\n\n";

// Get recent leads
$leads = get_posts([
    'post_type' => 'ays_lead',
    'posts_per_page' => 3,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC'
]);

foreach ($leads as $lead) {
    $email = get_post_meta($lead->ID, 'ays_email', true);
    echo "Processing Lead ID: {$lead->ID}\n";
    echo "  Email: $email\n";
    
    if (class_exists('AYS_Validation_Cron')) {
        // Run validation immediately
        AYS_Validation_Cron::handle_event($lead->ID);
        
        // Get the updated status
        $status = get_post_meta($lead->ID, '_ays_email_validation_status', true);
        echo "  Status after validation: $status\n\n";
    } else {
        echo "  ❌ AYS_Validation_Cron class not found!\n\n";
    }
}

echo "==================================================\n";
echo "Validation Complete!\n";
echo "Refresh your Leads page to see updated statuses.\n";
echo "==================================================\n";
