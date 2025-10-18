<?php
define('WP_USE_THEMES', false);
require('C:/xampp/htdocs/projectstudios/wp-load.php');

echo "=== Debugging Email Sending System ===\n\n";

// Check if classes are loading
echo "1. Checking if classes can be loaded:\n";
$classes = [
    'Mailer',
    'MailTransportFactory',
    'WPMailTransport',
    'AYS_Notifier',
    'AYS_Notification_Settings',
    'LeadArrayAdapter',
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "   ✓ $class loaded\n";
    } else {
        echo "   ✗ $class NOT FOUND\n";
    }
}

echo "\n2. Checking notification log table:\n";
global $wpdb;
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}ays_notification_log'");
if ($table_exists) {
    echo "   ✓ Log table exists\n";
    $log_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ays_notification_log");
    echo "   ✓ Log entries: " . $log_count . "\n";
    echo "\n   Recent log entries:\n";
    $logs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ays_notification_log ORDER BY ts DESC LIMIT 5");
    foreach ($logs as $log) {
        echo "     - [{$log->code}] {$log->ts}: " . substr(var_export(maybe_unserialize($log->details), true), 0, 80) . "\n";
    }
} else {
    echo "   ✗ Log table does NOT exist\n";
}

echo "\n3. Checking settings:\n";
$settings = get_option('ays_notifications_settings');
if ($settings) {
    echo "   ✓ Settings exist\n";
    echo "   - Admin notification enabled: " . ($settings['admin_notification']['enabled'] ? 'YES' : 'NO') . "\n";
    echo "   - Admin email: " . $settings['admin_notification']['to'][0] . "\n";
} else {
    echo "   ✗ Settings NOT FOUND\n";
}

echo "\n4. Testing wp_mail():\n";
$test_result = wp_mail('test@example.com', 'Test', 'Body');
echo "   wp_mail() returned: " . ($test_result ? 'true' : 'false') . "\n";

echo "\n5. Testing Mailer class:\n";
try {
    if (class_exists('Mailer')) {
        $mailer = Mailer::instance();
        $result = $mailer->send('test@example.com', 'Test from Mailer', 'Test body', ['Content-Type: text/html; charset=UTF-8']);
        echo "   Mailer::send() returned: " . ($result ? 'true' : 'false') . "\n";
    } else {
        echo "   Mailer class not found\n";
    }
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

echo "\n6. Checking recent leads:\n";
$recent_leads = get_posts([
    'post_type' => 'ays_lead',
    'posts_per_page' => 2,
    'orderby' => 'date',
    'order' => 'DESC',
]);

foreach ($recent_leads as $lead) {
    echo "   Lead ID {$lead->ID}:\n";
    echo "     - Admin notified: " . (get_post_meta($lead->ID, '_ays_admin_notified', true) ?: 'NO') . "\n";
    echo "     - Customer notified: " . (get_post_meta($lead->ID, '_ays_customer_notified', true) ?: 'NO') . "\n";
}
