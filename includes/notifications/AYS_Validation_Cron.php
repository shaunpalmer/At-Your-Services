<?php
defined('ABSPATH') || exit;

/**
 * Handles WP-Cron scheduling for asynchronous email validation.
 */
final class AYS_Validation_Cron {
    const EVENT = 'ays_validate_lead_email';

    /**
     * Hooks into the cron event.
     */
    public static function init() {
        add_action(self::EVENT, [__CLASS__, 'handle_event'], 10, 1);
    }

    /**
     * Handles the cron event to validate a lead's email.
     *
     * @param int $lead_id The ID of the lead to validate.
     */
    public static function handle_event($lead_id) {
        $email = get_post_meta($lead_id, 'ays_email', true);
        if (empty($email) || !is_email($email)) {
            update_post_meta($lead_id, '_ays_email_validation_status', 'invalid');
            return;
        }

        if (class_exists('AYS_Email_Validator')) {
            $status = AYS_Email_Validator::validate($email);
            update_post_meta($lead_id, '_ays_email_validation_status', $status);
        }
    }

    /**
     * Schedules a single event to validate the lead's email.
     *
     * @param int $lead_id The ID of the lead.
     */
    public static function schedule($lead_id) {
        // Schedule to run in 1 minute to avoid race conditions.
        if (!wp_next_scheduled(self::EVENT, [$lead_id])) {
            wp_schedule_single_event(time() + 60, self::EVENT, [$lead_id]);
        }
    }
}
