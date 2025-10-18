<?php
defined('ABSPATH') || exit;

final class AYS_Notification_Logger {
    const TABLE = 'ays_notification_log';
    public static function init() {
        // Register table, etc.
    }
    public static function log($code, $details) {
        global $wpdb;
        $wpdb->insert($wpdb->prefix . self::TABLE, [
            'ts' => current_time('mysql'),
            'code' => $code,
            'details' => maybe_serialize($details),
        ]);
    }
    public static function maybe_create_table() {
        // dbDelta table creation logic
    }
}
