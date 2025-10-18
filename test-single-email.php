<?php
/**
 * Single email test - tests just ONE email to see raw API response
 */

// Load WordPress
require_once dirname(__FILE__) . '/../../../wp-load.php';

$api_key = 'I22iuKHbS4zwezquV5qKM';

// Test multiple emails
$test_emails = [
    'test@gmail.com',           // Common test email
    'info@google.com',          // Valid corporate email
    'invalid@notarealdomainatall12345.com', // Invalid domain
];

echo "==================================================\n";
echo "Testing Multiple Emails with Correct Endpoint\n";
echo "==================================================\n\n";

foreach ($test_emails as $test_email) {
    echo "\n----- Testing: $test_email -----\n";
    
    // Use the CORRECT endpoint from the official docs!
    $url = 'https://app.emaillistvalidation.com/api/verifEmailv2?secret=' . $api_key . '&email=' . urlencode($test_email);
    
    echo "URL: $url\n";

    $response = wp_remote_get($url, [
        'timeout' => 20,
        'sslverify' => false  // For local testing
    ]);

    if (is_wp_error($response)) {
        echo "❌ Error: " . $response->get_error_message() . "\n\n";
        continue;
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);

    echo "HTTP Status: $code\n";
    echo "Response: $body\n";
    
    // Try to parse as JSON
    $json = json_decode($body, true);
    if ($json && isset($json['Result'])) {
        $result = $json['Result'];
        $success = $json['success'] ?? 'N/A';
        
        $icon = '❓';
        if ($result === 'ok' || $result === 'valid') {
            $icon = '✅';
        } elseif ($result === 'risky') {
            $icon = '⚠️';
        } elseif ($result === 'fail' || $result === 'invalid') {
            $icon = '❌';
        } elseif ($result === 'unknown') {
            $icon = '⏳';
        }
        
        echo "Result: $icon $result (success: $success)\n";
    }
    
    echo "\n";
    sleep(2); // Wait 2 seconds between requests
}

echo "==================================================\n";
echo "Test Complete!\n";
echo "==================================================\n";
