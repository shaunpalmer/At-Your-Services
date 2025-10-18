<?php
define('WP_USE_THEMES', false);
require('C:/xampp/htdocs/projectstudios/wp-load.php');

global $wpdb;

$table_name = $wpdb->prefix . 'ays_notification_log';

$sql = "CREATE TABLE IF NOT EXISTS $table_name (
    id BIGINT UNSIGNED AUTO_INCREMENT,
    ts DATETIME DEFAULT CURRENT_TIMESTAMP,
    code VARCHAR(20) NOT NULL,
    details LONGTEXT,
    PRIMARY KEY (id),
    INDEX ts (ts),
    INDEX code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$wpdb->query($sql);

if ($wpdb->last_error) {
    echo "Error creating table: " . $wpdb->last_error . "\n";
} else {
    echo "✓ Notification log table created successfully.\n";
}
