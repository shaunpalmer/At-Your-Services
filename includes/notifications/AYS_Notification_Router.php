<?php
/**
 * AYS_Notification_Router - Central event router for lead notifications
 * 
 * PURPOSE:
 * Acts as the central event router that listens for new lead creation and
 * triggers the appropriate notification flows. It's the connection point between
 * the lead form submission and the email notification system.
 * 
 * HOW IT WORKS:
 * 
 * 1. INITIALIZATION:
 *    - Called from ays.php during plugin initialization
 *    - Registers the 'ays_lead_created' hook listener in init()
 * 
 * 2. EVENT TRIGGER:
 *    - When a user submits the lead form (ays_shortcodes.php):
 *      a. The shortcode calls wp_insert_post() to create a lead CPT
 *      b. After successful creation, it fires: do_action('ays_lead_created', $post_id, $lead_data)
 *      c. This router's handle_new_lead() method catches it
 * 
 * 3. NOTIFICATION WORKFLOW:
 *    When handle_new_lead() is triggered, it:
 *    - Sends admin notification via AYS_Notifier::send_admin_notification()
 *    - Sends customer auto-reply via AYS_Notifier::send_customer_auto_reply()
 *      (if enabled in settings)
 *    - Schedules async email validation via AYS_Validation_Cron::schedule()
 * 
 * SETTINGS REFERENCE:
 * Admin notification settings stored in wp_options['ays_notifications_settings']
 * - admin_notification.enabled (bool) - Whether to send admin notifications
 * - customer_auto_reply.enabled (bool) - Whether to send auto-replies
 * 
 * DEBUGGING:
 * 
 * If notifications aren't sending:
 * 1. Verify this router is initialized in ays.php:
 *    - AYS_Notification_Router.php is require_once'd
 *    - AYS_Notification_Router::init() is called
 * 
 * 2. Check that 'ays_lead_created' hook is firing:
 *    - Add temporary hook in functions.php: 
 *      add_action('ays_lead_created', function($id, $data) { 
 *        error_log('Lead created: ' . $id); 
 *      }, 1, 2);
 * 
 * 3. Check notification settings:
 *    SELECT * FROM wp_options WHERE option_name = 'ays_notifications_settings';
 * 
 * 4. Check notification logs:
 *    SELECT * FROM wp_ays_notification_log ORDER BY ts DESC LIMIT 20;
 * 
 * 5. Check email log file:
 *    Get-Content C:\xampp\htdocs\projectstudios\wp-content\email-log.txt -Tail 50
 * 
 * CLASS DEPENDENCIES:
 * - AYS_Notifier (sends emails)
 * - AYS_Notification_Settings (retrieves settings)
 * - AYS_Validation_Cron (schedules validation)
 * 
 * EXECUTION FLOW:
 * 
 * Form Submission
 *    ↓
 * ays_shortcodes.php::ays_lead_form_shortcode()
 *    ↓
 * wp_insert_post() creates lead CPT
 *    ↓
 * do_action('ays_lead_created', $post_id, $lead_data)
 *    ↓
 * THIS CLASS::handle_new_lead() [Priority 10]
 *    ↓
 * ├─ AYS_Notifier::send_admin_notification()
 * ├─ AYS_Notifier::send_customer_auto_reply() [if enabled]
 * └─ AYS_Validation_Cron::schedule()
 *    ↓
 * Emails logged/sent
 * Validation scheduled for later
 * 
 * @since 1.0.0
 * @package At-Your-Services
 */

defined('ABSPATH') || exit;

final class AYS_Notification_Router {

    /**
     * Initialize the router by registering the hook listener
     * 
     * Called from ays.php during plugin initialization
     */
    public static function init() {
        add_action('ays_lead_created', [__CLASS__, 'handle_new_lead'], 10, 2);
    }

    /**
     * Handle new lead event and trigger notifications
     *
     * @param int   $lead_id   The ID of the newly created lead post
     * @param array $lead_data The raw form data from the lead
     */
    public static function handle_new_lead($lead_id, $lead_data = []) {
        // 1. Send the notification to the admin
        if (class_exists('AYS_Notifier')) {
            AYS_Notifier::send_admin_notification($lead_id, $lead_data);
        }

        // 2. Send the auto-reply to the customer, if enabled
        $settings = AYS_Notification_Settings::get();
        if (!empty($settings['customer_auto_reply']['enabled'])) {
            if (class_exists('AYS_Notifier')) {
                AYS_Notifier::send_customer_auto_reply($lead_id, $lead_data);
            }
        }

        // 3. Schedule the email validation check
        if (class_exists('AYS_Validation_Cron')) {
            AYS_Validation_Cron::schedule($lead_id);
        }
    }
}
