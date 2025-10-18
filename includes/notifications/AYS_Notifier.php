<?php
/**
 * AYS_Notifier - Dual-Channel Email Notification System
 * 
 * Sends admin notifications and customer auto-replies when a new lead is created.
 * 
 * ARCHITECTURE:
 * - This class is called by AYS_Notification_Router when the 'ays_lead_created' hook fires
 * - It retrieves email templates and settings from AYS_Notification_Settings
 * - It builds email content using token replacement (e.g., {name}, {email}, etc.)
 * - It routes emails through Mailer, which uses MailTransportFactory to choose delivery method
 * - All sending attempts are logged to AYS_Notification_Logger
 * 
 * FLOW:
 * 1. AYS_Notification_Router::handle_new_lead() is triggered by 'ays_lead_created' hook
 * 2. Router calls AYS_Notifier::send_admin_notification() and/or send_customer_auto_reply()
 * 3. Each method:
 *    a. Retrieves relevant settings (enabled, recipients, templates, etc.)
 *    b. Builds token array from lead data using adapters (LeadArrayAdapter or LeadCPTAdapter)
 *    c. Replaces tokens in templates
 *    d. Builds email headers (From, Reply-To, Cc, Bcc, Content-Type, etc.)
 *    e. Calls Mailer::send() which routes through the configured transport
 *    f. Logs result to database via AYS_Notification_Logger
 * 
 * SETTINGS:
 * Settings are stored in wp_options as 'ays_notifications_settings'
 * Managed via WordPress Settings API in includes/notifications/AYS_Notification_Settings.php
 * Configured at: WordPress Admin > Leads > Settings
 * 
 * TRANSPORTS:
 * The actual delivery method is chosen by MailTransportFactory::create()
 * - FileLoggingTransport: Logs to /wp-content/email-log.txt (development)
 * - WPMailTransport: Uses wp_mail() (requires mail server)
 * - NullTransport: Discards emails (for testing)
 * 
 * TOKEN REPLACEMENT:
 * Available tokens in email templates:
 *  {name}, {email}, {phone}, {service}, {booking_date}, {booking_time}, 
 *  {notes}, {lead_id}, {site_name}, {admin_url}, {created_at}
 *  [all-fields] - HTML list of all form fields
 * 
 * DEBUGGING:
 * - Check /wp-content/email-log.txt for file logging
 * - Check wp_ays_notification_log table for detailed logs
 * - Enable PHPMailer debug in WPMailTransport if using wp_mail()
 * 
 * @since 1.0.0
 * @package At-Your-Services
 */

defined('ABSPATH') || exit;

final class AYS_Notifier {
    
    public static function init() {
        // The router will call us, we don't hook directly here
    }

    /**
     * Send admin notification about a new lead
     */
    public static function send_admin_notification($lead_id, $lead_data = []) {
        $settings = AYS_Notification_Settings::get(null, 'admin_notification');
        
        if (empty($settings['enabled'])) {
            return false;
        }

        // Build tokens
        $tokens = self::_build_tokens($lead_id, $lead_data);
        
        // Get recipients
        $to = $settings['to'] ?? [];
        if (empty($to)) {
            $admin = get_option('admin_email');
            if ($admin && is_email($admin)) {
                $to = [$admin];
            }
        }
        
        if (empty($to)) {
            return false;
        }

        // Build email
        $subject = strtr($settings['subject_tpl'] ?? 'New Lead #{lead_id}', $tokens);
        $body = strtr($settings['body_tpl'] ?? '', $tokens);
        
        // Handle [all-fields] token
        $body = self::_render_all_fields_token($body, $tokens);
        
        // Build headers
        $headers = self::_build_headers($settings);
        
        // Send
        return self::_send_email($to, $subject, $body, $headers, $lead_id, 'admin');
    }

    /**
     * Send customer auto-reply
     */
    public static function send_customer_auto_reply($lead_id, $lead_data = []) {
        $settings = AYS_Notification_Settings::get(null, 'customer_auto_reply');
        
        if (empty($settings['enabled'])) {
            return false;
        }

        // Build tokens
        $tokens = self::_build_tokens($lead_id, $lead_data);
        
        // Get customer email
        $customer_email = $lead_data['email'] ?? get_post_meta($lead_id, 'ays_email', true);
        
        if (empty($customer_email) || !is_email($customer_email)) {
            return false;
        }

        // Build email
        $subject = strtr($settings['subject_tpl'] ?? 'Thank you for your inquiry', $tokens);
        $body = strtr($settings['body_tpl'] ?? '', $tokens);
        
        // Handle [all-fields] token
        $body = self::_render_all_fields_token($body, $tokens);
        
        // Build headers
        $headers = self::_build_headers($settings);
        
        // Send
        return self::_send_email([$customer_email], $subject, $body, $headers, $lead_id, 'customer');
    }

    /**
     * Build token array for email templates
     * I think there's a problem with the template here and on line 95 there's also a bug the logic's off or something
     */
    private static function _build_tokens($lead_id, $lead_data = []) {
        // Use adapter to get tokens
        if (!empty($lead_data) && is_array($lead_data)) {
            $adapter = new LeadArrayAdapter($lead_data);
        } else {
            $adapter = new LeadCPTAdapter((int)$lead_id);
        }
        
        $tokens = $adapter->tokens();
        $tokens['{lead_id}'] = $lead_id;
        $tokens['{site_name}'] = get_bloginfo('name');
        $tokens['{admin_url}'] = admin_url('post.php?post=' . $lead_id . '&action=edit');
        $tokens['{created_at}'] = get_post_field('post_date', $lead_id);
        
        return apply_filters('ays_notification_tokens', $tokens, $lead_id, null);
    }

    /**
     * Build email headers
     */
    private static function _build_headers($settings) {
        $headers = [];
        
        $from_name = $settings['from_name'] ?? get_bloginfo('name');
        $from_email = $settings['from_email'] ?? get_option('admin_email');
        $send_html = !empty($settings['send_html']);
        
        if ($from_email) {
            $headers[] = 'From: ' . esc_attr($from_name) . ' <' . sanitize_email($from_email) . '>';
        }
        
        if (!empty($settings['reply_to'])) {
            $headers[] = 'Reply-To: ' . sanitize_email($settings['reply_to']);
        }
        
        if (!empty($settings['cc'])) {
            foreach ($settings['cc'] as $cc) {
                $headers[] = 'Cc: ' . sanitize_email($cc);
            }
        }
        
        if (!empty($settings['bcc'])) {
            foreach ($settings['bcc'] as $bcc) {
                $headers[] = 'Bcc: ' . sanitize_email($bcc);
            }
        }
        
        if ($send_html) {
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
        }
        
        return $headers;
    }

    /**
     * Render the [all-fields] token
     */
    private static function _render_all_fields_token($body, $tokens) {
        if (strpos($body, '[all-fields]') === false) {
            return $body;
        }
        
        $fields_html = '<ul style="list-style: none; padding: 0;">';
        foreach ($tokens as $key => $value) {
            // Skip special tokens
            if (in_array($key, ['{lead_id}', '{site_name}', '{admin_url}', '{created_at}'])) {
                continue;
            }
            
            $label = str_replace(['{', '}', '_'], ['', '', ' '], $key);
            $label = ucwords($label);
            $fields_html .= '<li><strong>' . esc_html($label) . ':</strong> ' . esc_html($value) . '</li>';
        }
        $fields_html .= '</ul>';
        
        return str_replace('[all-fields]', $fields_html, $body);
    }

    /**
     * Send the email
     */
    private static function _send_email($to, $subject, $body, $headers, $lead_id, $type) {
        $success = true;
        
        foreach ((array)$to as $recipient) {
            $should_send = apply_filters('ays_should_send_notification', true, $lead_id, $recipient, $subject, $body, $headers);
            if (!$should_send) {
                continue;
            }
            
            try {
                $sent = Mailer::instance()->send($recipient, $subject, $body, $headers);
                
                if ($sent) {
                    AYS_Notification_Logger::log('sent', [
                        'lead_id' => $lead_id,
                        'to' => $recipient,
                        'type' => $type,
                        'subject' => $subject,
                    ]);
                    
                    if ($lead_id && $type === 'admin') {
                        update_post_meta($lead_id, '_ays_admin_notified', current_time('mysql'));
                    } elseif ($lead_id && $type === 'customer') {
                        update_post_meta($lead_id, '_ays_customer_notified', current_time('mysql'));
                    }
                } else {
                    $success = false;
                    AYS_Notification_Logger::log('fail', [
                        'lead_id' => $lead_id,
                        'to' => $recipient,
                        'type' => $type,
                        'subject' => $subject,
                    ]);
                }
            } catch (\Throwable $e) {
                $success = false;
                AYS_Notification_Logger::log('error', [
                    'lead_id' => $lead_id,
                    'to' => $recipient,
                    'type' => $type,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        return $success;
    }
}
