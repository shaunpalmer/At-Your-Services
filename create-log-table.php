<?php
/**
 * Create the notification log table
 */

// Load WordPress
require_once dirname(__FILE__) . '/../../../wp-load.php';

echo "Creating notification log table...\n";

if (class_exists('AYS_Notification_Logger')) {
    AYS_Notification_Logger::maybe_create_table();
    echo "✓ Table created successfully!\n";
} else {
    echo "❌ AYS_Notification_Logger class not found!\n";
}
