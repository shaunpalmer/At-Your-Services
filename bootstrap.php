<?php
/**
 * PHPUnit Bootstrap
 * Loads WordPress and sets up test environment
 */

// Load WordPress
$wp_load = dirname(__FILE__) . '/../../../../wp-load.php';
if (!file_exists($wp_load)) {
    die("Error: wp-load.php not found at $wp_load\n");
}

require_once $wp_load;

// Define test constants
define('DOING_PHPUNIT', true);

echo "WordPress loaded successfully\n";
echo "Database: " . DB_NAME . "\n";
echo "Prefix: " . $GLOBALS['wpdb']->prefix . "\n";
