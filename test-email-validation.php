<?php
/**
 * Quick test script for Email Validation API
 * Run this from the command line: php test-email-validation.php
 */

// Load WordPress
require_once dirname(__FILE__) . '/../../../wp-load.php';

// Load the validator class
require_once __DIR__ . '/includes/helpers/AYS_Email_Validator.php';

echo "==================================================\n";
echo "Email Validation API Test\n";
echo "==================================================\n\n";

// Get the API key from settings
$settings = get_option('ays_notifications_settings', []);
$api_key = isset($settings['validation_api_key']) ? $settings['validation_api_key'] : '';

// Fallback to hardcoded key for testing if not in settings
if (empty($api_key)) {
    $api_key = 'I22iuKHbS4zwezquV5qKM';
    echo "ℹ️  Using test API key (not found in settings)\n\n";
} else {
    echo "✓ API Key found in settings: " . substr($api_key, 0, 10) . "...\n\n";
}

if (empty($api_key)) {
    echo "❌ ERROR: No API key available!\n";
    exit(1);
}

// Test emails - mix of valid, invalid, and risky
$test_emails = [
    'shaun.palmer0@gmail.com',    // Real email - should be valid
    'ProjectStudioswebdesign@gmail.com',   // Real email - should be valid
    'test@example.com',          // Test domain - might be risky
    'invalid.email',             // Invalid format - no @ sign
    'notreal@fakemailgenerator.com', // Disposable email - likely risky/invalid
    'admin@localhost',           // Invalid domain
];

echo "Testing " . count($test_emails) . " email addresses...\n";
echo "==================================================\n\n";

foreach ($test_emails as $email) {
    echo "Testing: $email\n";
    
    // Use the CORRECT API endpoint: verifEmailv2
    $url = add_query_arg([
        'secret' => $api_key,
        'email'  => $email,
    ], 'https://app.emaillistvalidation.com/api/verifEmailv2');
    
    echo "  API URL: $url\n";
    
    $start = microtime(true);
    // Disable SSL verification for local testing
    $response = wp_remote_get($url, [
        'timeout' => 15,
        'sslverify' => false  // Only for local testing!
    ]);
    $duration = round((microtime(true) - $start) * 1000, 2);
    
    if (is_wp_error($response)) {
        echo "  ❌ API Error: " . $response->get_error_message() . "\n\n";
        continue;
    }
    
    $http_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    
    echo "  HTTP Status: $http_code\n";
    echo "  API Response: '$body'\n";
    
    // Now test with our validator
    $result = AYS_Email_Validator::validate($email);
    
    // Format the result with color-coded output
    $icon = '❓';
    switch ($result) {
        case 'valid':
            $icon = '✅';
            break;
        case 'invalid':
            $icon = '❌';
            break;
        case 'risky':
            $icon = '⚠️';
            break;
        case 'unverified':
            $icon = '⏳';
            break;
    }
    
    echo "  Final Result: $icon $result (took {$duration}ms)\n\n";
}

echo "==================================================\n";
echo "Test Complete!\n";
echo "==================================================\n";
