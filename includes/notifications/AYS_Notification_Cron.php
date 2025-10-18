<?php
defined('ABSPATH') || exit;

final class AYS_Notification_Cron {
    const EVENT = 'ays_send_lead_notification';
    public static function init() {
        add_action(self::EVENT, [__CLASS__, 'handle_event'], 10, 1);
    }
    public static function handle_event($lead_id) {
        if (class_exists('AYS_Notifier')) {
            AYS_Notifier::send_for_lead($lead_id);
        }
    }
    public static function schedule($lead_id) {
        if (!wp_next_scheduled(self::EVENT, [$lead_id])) {
            wp_schedule_single_event(time() + 30, self::EVENT, [$lead_id]);
        }
    }
}
