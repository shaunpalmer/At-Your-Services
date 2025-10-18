<?php
defined('ABSPATH') || exit;

/**
 * Handles email validation using an external API or fallback methods.
 */
class AYS_Email_Validator {

    const API_ENDPOINT = 'https://app.emaillistvalidation.com/api/verifEmailv2';

    /**
     * Validate an email address.
     *
     * @param string $email The email address to validate.
     * @return string The validation status: 'valid', 'risky', 'invalid', or 'unverified'.
     */
    public static function validate($email) {
        $settings = AYS_Notification_Settings::get();
        $api_key = $settings['validation_api_key'] ?? '';

        if (!empty($api_key)) {
            return self::validate_via_api($email, $api_key);
        }

        return self::validate_via_mx($email);
    }

    /**
     * Validate using the emaillistvalidation.com API.
     */
    private static function validate_via_api($email, $api_key) {
        $url = add_query_arg([
            'secret' => $api_key,
            'email'  => $email,
        ], self::API_ENDPOINT);

        $response = wp_remote_get($url, ['timeout' => 15]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            // API call failed, log it and return a neutral status
            $error_msg = is_wp_error($response) ? $response->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code($response);
            if (defined('WP_CLI') || (defined('WP_DEBUG') && WP_DEBUG)) {
                error_log("AYS Email Validator API Error: $error_msg for email: $email");
            }
            AYS_Notification_Logger::log('validator_api_error', [
                'email' => $email,
                'error' => $error_msg,
            ]);
            return 'unverified';
        }

        $body = wp_remote_retrieve_body($response);
        
        // Debug logging
        if (defined('WP_CLI') || (defined('WP_DEBUG') && WP_DEBUG)) {
            error_log("AYS Email Validator API Response for $email: " . $body);
        }
        
        // Try to decode as JSON first (detailed response format)
        $json = json_decode($body, true);
        if (is_array($json)) {
            // Check for error field (spamtrap, etc.)
            if (isset($json['error'])) {
                $error = strtolower($json['error']);
                if (in_array($error, ['spamtrap', 'disposable', 'role'])) {
                    return 'risky';
                }
            }
            
            // Check success and Result fields
            if (isset($json['success']) && $json['success'] === true) {
                return 'valid';
            }
            
            // Check Result field
            if (isset($json['Result'])) {
                $result = strtolower(trim($json['Result']));
                switch ($result) {
                    case 'ok':
                    case 'valid':
                        return 'valid';
                    case 'risky':
                        return 'risky';
                    case 'fail':
                    case 'invalid':
                        return 'invalid';
                    case 'unknown':
                        return 'unverified';
                    default:
                        return 'unverified';
                }
            }
        }
        
        // Fall back to simple string format
        // The API can return various formats:
        // Simple: "ok", "risky", "fail"
        // Colon-separated: "result:reason" like "unknown:unknown"
        $body_trimmed = trim($body);
        
        // Handle colon-separated format
        if (strpos($body_trimmed, ':') !== false) {
            $parts = explode(':', $body_trimmed, 2);
            $result = strtolower(trim($parts[0]));
        } else {
            $result = strtolower($body_trimmed);
        }
        
        switch ($result) {
            case 'ok':
                return 'valid';
            case 'risky':
                return 'risky';
            case 'fail':
            case 'invalid':
                return 'invalid';
            case 'unknown':
                return 'unverified';
            default:
                return 'unverified';
        }
    }

    /**
     * Validate using a simple DNS MX record check as a fallback.
     */
    private static function validate_via_mx($email) {
        $domain = substr(strrchr($email, "@"), 1);
        
        // If there's no @ sign or the domain is empty, it's invalid
        if (empty($domain) || strpos($email, '@') === false) {
            return 'invalid';
        }
        
        if (function_exists('checkdnsrr') && checkdnsrr($domain, 'MX')) {
            return 'unverified'; // It's a valid domain, but we can't confirm the mailbox
        }
        return 'invalid';
    }
}
