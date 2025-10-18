<?php
/**
 * FileLoggingTransport - Development mail transport
 * 
 * WHAT IT DOES:
 * Instead of actually sending emails, this transport logs all email details to a file.
 * This is perfect for development and testing when you don't have a mail server configured.
 * 
 * WHY USE THIS:
 * - No mail server required
 * - No external dependencies or API keys
 * - Emails are not actually sent (no risk of contacting real users)
 * - Easy to review what would have been sent
 * - Completely safe for local development
 * 
 * LOG FILE LOCATION:
 * /wp-content/email-log.txt
 * 
 * LOG FILE FORMAT:
 * Each email entry contains:
 * - Timestamp
 * - Recipient email address
 * - Email subject
 * - Headers (as JSON)
 * - Full email body (HTML or plain text)
 * - Separator line
 * 
 * Example:
 * [2025-10-18 14:30:22] TO: admin@example.com
 * SUBJECT: New Lead from John Doe
 * HEADERS: ["From: ...", "Content-Type: text/html; charset=UTF-8"]
 * BODY:
 * <html>...email content...</html>
 * ================================================================================
 * 
 * HOW TO VIEW LOGS:
 * 
 * PowerShell:
 *   Get-Content C:\xampp\htdocs\projectstudios\wp-content\email-log.txt -Tail 50
 * 
 * Linux/Mac:
 *   tail -50 /var/www/html/wp-content/email-log.txt
 * 
 * Alternatively, open the file directly in an editor.
 * 
 * HOW TO CLEAR LOGS:
 * 
 * PowerShell:
 *   Clear-Content C:\xampp\htdocs\projectstudios\wp-content\email-log.txt
 * 
 * Linux/Mac:
 *   > /var/www/html/wp-content/email-log.txt
 * 
 * WHEN TO USE:
 * ✓ Local development
 * ✓ Testing lead form
 * ✓ Verifying email templates work correctly
 * ✓ Before deploying to production
 * 
 * WHEN NOT TO USE:
 * ✗ Production (use wp_mail or external SMTP instead)
 * ✗ When you need to actually send emails to users
 * 
 * SWITCHING TO REAL EMAIL:
 * 
 * When you're ready to actually send emails:
 * 1. Configure your mail server or external SMTP
 * 2. Add this filter to wp-config.php or mu-plugin:
 *    add_filter('ays_mail_transport', function() { return 'wp_mail'; });
 * 3. The system will start using the configured mail server
 * 4. You can delete the email-log.txt file or leave it for reference
 * 
 * @since 1.0.0
 * @package At-Your-Services
 */

defined('ABSPATH') || exit;

final class FileLoggingTransport implements MailTransportInterface {
    public function send(string $to, string $subject, string $body, array $headers = []) : bool {
        $log_file = WP_CONTENT_DIR . '/email-log.txt';
        
        // Format the log entry with timestamp, recipient, subject, headers, and body
        $entry = sprintf(
            "[%s] TO: %s\nSUBJECT: %s\nHEADERS: %s\nBODY:\n%s\n%s\n\n",
            current_time('Y-m-d H:i:s'),
            $to,
            $subject,
            json_encode($headers),
            $body,
            str_repeat('=', 80)
        );
        
        // Append to log file (create if doesn't exist)
        $success = file_put_contents($log_file, $entry, FILE_APPEND | LOCK_EX);
        
        // Also log to PHP error log for debugging
        if ($success) {
            error_log("Email logged to: $log_file (TO: $to, SUBJECT: $subject)");
            return true;
        } else {
            error_log("Failed to write to email log: $log_file");
            return false;
        }
    }
}
