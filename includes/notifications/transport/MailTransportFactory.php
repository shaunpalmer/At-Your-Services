<?php
/**
 * MailTransportFactory - Factory for creating mail transport instances
 * 
 * PURPOSE:
 * This factory pattern allows switching between different email delivery methods
 * without changing code throughout the application. Just change the default type
 * returned from create() or use the 'ays_mail_transport' filter.
 * 
 * AVAILABLE TRANSPORTS:
 * 
 * 1. 'file_logging' (DEFAULT for development):
 *    - Logs emails to /wp-content/email-log.txt
 *    - No mail server required
 *    - Perfect for local testing
 *    - Emails are NOT actually sent
 * 
 * 2. 'wp_mail':
 *    - Uses WordPress built-in wp_mail() function
 *    - Requires a configured mail server or external SMTP
 *    - When properly configured, actually sends emails
 *    - To use: Ensure your server has mail configured (Postfix, Sendmail, etc.)
 *              OR configure SMTP via mu-plugin
 * 
 * 3. 'null':
 *    - Uses NullTransport which discards all emails
 *    - Useful for testing without any side effects
 *    - Emails are NOT logged or sent
 * 
 * HOW TO SWITCH TRANSPORTS:
 * 
 * In wp-config.php or a mu-plugin:
 * 
 *   // Use file logging (development)
 *   add_filter('ays_mail_transport', function() { return 'file_logging'; });
 *   
 *   // Use wp_mail() (production with mail server)
 *   add_filter('ays_mail_transport', function() { return 'wp_mail'; });
 *   
 *   // Use null transport (testing)
 *   add_filter('ays_mail_transport', function() { return 'null'; });
 * 
 * PRODUCTION SETUP:
 * 
 * When deploying to production:
 * 1. Configure your mail server (Postfix, Exim, etc.) OR external SMTP
 * 2. Update this factory to default to 'wp_mail'
 * 3. For external SMTP (Gmail, SendGrid, etc.):
 *    Add a mu-plugin that hooks 'phpmailer_init' to configure SMTP credentials
 *    (See mu-plugins/mercury-smtp-config.php for an example)
 * 
 * CURRENT CONFIGURATION:
 * - Default transport: 'file_logging'
 * - Email log location: /wp-content/email-log.txt
 * - This is safe for development and doesn't require mail server
 * 
 * @since 1.0.0
 * @package At-Your-Services
 */

defined('ABSPATH') || exit;

final class MailTransportFactory {
    public static function create(): MailTransportInterface {
        // Use filter to allow switching transport via code
        // Default to file_logging for development (safe, no external dependencies)
        // Change to 'wp_mail' when deploying with a configured mail server
        $type = apply_filters('ays_mail_transport', 'file_logging');
        
        if ($type === 'null') {
            return new NullTransport();
        } elseif ($type === 'file_logging') {
            return new FileLoggingTransport();
        }
        
        // wp_mail: Requires configured mail server or external SMTP
        return new WPMailTransport();
    }
}
