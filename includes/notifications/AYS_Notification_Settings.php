<?php
defined('ABSPATH') || exit;

final class AYS_Notification_Settings {
    const OPT_KEY = 'ays_notifications_settings';

    public static function init() {
        add_action('admin_init', [__CLASS__, 'register']);
    }

    public static function defaults() : array {
        return [
            'admin_notification' => [
                'enabled'      => true,
                'to'           => [ get_option('admin_email') ],
                'cc'           => [],
                'bcc'          => [],
                'reply_to'     => '',
                'from_name'    => wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES),
                'from_email'   => get_option('admin_email'),
                'send_html'    => true,
                'delivery_mode'=> 'hybrid',
                'subject_tpl'  => 'New Lead: [{service}] from {name}',
                'body_tpl'     => "You have received a new lead.<br><br><strong>Lead Details:</strong><br>[all-fields]<br><br>You can view the lead here: <a href='{admin_url}'>{admin_url}</a>",
            ],
            'customer_auto_reply' => [
                'enabled'      => true,
                'from_name'    => wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES),
                'from_email'   => get_option('admin_email'),
                'send_html'    => true,
                'subject_tpl'  => 'Thank you for your inquiry, {name}!',
                'body_tpl'     => "Hi {name},<br><br>Thank you for reaching out to us about <strong>{service}</strong>. We have received your message and will get back to you shortly.<br><br>Best regards,<br>The Team",
            ],
            'validation_api_key' => '',
        ];
    }

    private static function parse_emails_list($raw): array {
        if (is_array($raw)) return array_values(array_filter(array_map('sanitize_email', $raw), 'is_email'));
        $raw = (string)$raw;
        $parts = preg_split('/[\s,;]+/u', $raw, -1, PREG_SPLIT_NO_EMPTY);
        $emails = [];
        foreach ($parts as $p) { $e = sanitize_email($p); if ($e && is_email($e)) $emails[] = $e; }
        return array_values(array_unique($emails));
    }
    public static function emails_array(string $key): array {
        $opt = self::get(); $v = $opt[$key] ?? [];
        return is_array($v) ? $v : self::parse_emails_list($v);
    }
    public static function csv_string(array $emails): string {
        return implode("\n", array_map('sanitize_email', $emails));
    }

    public static function get($key = null, $channel = null) {
        $opt = get_option(self::OPT_KEY, self::defaults());
        if ($channel) {
            $opt = $opt[$channel] ?? [];
        }
        return $key ? ($opt[$key] ?? null) : $opt;
    }

    public static function set($key, $value) {
        $opts = get_option(self::OPT_KEY, self::defaults());
        $opts[$key] = $value;
        update_option(self::OPT_KEY, $opts);
    }

    public static function register() {
        register_setting('ays_notifications', self::OPT_KEY, [__CLASS__, 'sanitize']);

        // Sections
        add_settings_section('ays_admin_notif', __('Admin Notification', 'atyourservice'), null, 'ays_notifications');
        add_settings_section('ays_customer_reply', __('Customer Auto-Reply', 'atyourservice'), null, 'ays_notifications');

        // Admin Notification Fields
        add_settings_field('admin_enabled', __('Enable Admin Notification', 'atyourservice'), [__CLASS__, 'render_admin_enabled_field'], 'ays_notifications', 'ays_admin_notif');
        add_settings_field('admin_to', __('To Recipients', 'atyourservice'), [__CLASS__, 'render_admin_to_field'], 'ays_notifications', 'ays_admin_notif');
        add_settings_field('admin_subject', __('Admin Subject', 'atyourservice'), [__CLASS__, 'render_admin_subject_field'], 'ays_notifications', 'ays_admin_notif');
        add_settings_field('admin_body', __('Admin Body', 'atyourservice'), [__CLASS__, 'render_admin_body_field'], 'ays_notifications', 'ays_admin_notif');

        // Customer Auto-Reply Fields
        add_settings_field('customer_enabled', __('Enable Customer Auto-Reply', 'atyourservice'), [__CLASS__, 'render_customer_enabled_field'], 'ays_notifications', 'ays_customer_reply');
        add_settings_field('customer_from_name', __('From Name', 'atyourservice'), [__CLASS__, 'render_customer_from_name_field'], 'ays_notifications', 'ays_customer_reply');
        add_settings_field('customer_from_email', __('From Email', 'atyourservice'), [__CLASS__, 'render_customer_from_email_field'], 'ays_notifications', 'ays_customer_reply');
        add_settings_field('customer_subject', __('Customer Subject', 'atyourservice'), [__CLASS__, 'render_customer_subject_field'], 'ays_notifications', 'ays_customer_reply');
        add_settings_field('customer_body', __('Customer Body', 'atyourservice'), [__CLASS__, 'render_customer_body_field'], 'ays_notifications', 'ays_customer_reply');

        // Email Validation Section
        add_settings_section('ays_email_validation', __('Email Validation', 'atyourservice'), null, 'ays_notifications');
        add_settings_field('validation_api_key', __('Email Validation API Key', 'atyourservice'), [__CLASS__, 'render_validation_api_key_field'], 'ays_notifications', 'ays_email_validation');
    }

    // Render functions for admin fields
    public static function render_admin_enabled_field() {
        $val = self::get('enabled', 'admin_notification');
        echo '<input type="checkbox" name="' . self::OPT_KEY . '[admin_notification][enabled]" value="1" ' . checked(1, $val, false) . ' />';
    }
    public static function render_admin_to_field() {
        $val = self::get('to', 'admin_notification');
        echo '<textarea name="' . self::OPT_KEY . '[admin_notification][to]" rows="3" class="large-text">' . esc_textarea(implode("\n", $val)) . '</textarea>';
        echo '<p class="description">' . __('One email address per line.', 'atyourservice') . '</p>';
    }
    public static function render_admin_subject_field() {
        $val = self::get('subject_tpl', 'admin_notification');
        echo '<input type="text" name="' . self::OPT_KEY . '[admin_notification][subject_tpl]" value="' . esc_attr($val) . '" class="large-text" />';
    }
    public static function render_admin_body_field() {
        $val = self::get('body_tpl', 'admin_notification');
        wp_editor($val, 'admin_body_tpl', ['textarea_name' => self::OPT_KEY . '[admin_notification][body_tpl]', 'media_buttons' => false, 'textarea_rows' => 10]);
        self::render_token_list();
    }

    // Render functions for customer fields
    public static function render_customer_enabled_field() {
        $val = self::get('enabled', 'customer_auto_reply');
        echo '<input type="checkbox" name="' . self::OPT_KEY . '[customer_auto_reply][enabled]" value="1" ' . checked(1, $val, false) . ' />';
    }
    public static function render_customer_from_name_field() {
        $val = self::get('from_name', 'customer_auto_reply');
        echo '<input type="text" name="' . self::OPT_KEY . '[customer_auto_reply][from_name]" value="' . esc_attr($val) . '" class="regular-text" />';
    }
    public static function render_customer_from_email_field() {
        $val = self::get('from_email', 'customer_auto_reply');
        echo '<input type="email" name="' . self::OPT_KEY . '[customer_auto_reply][from_email]" value="' . esc_attr($val) . '" class="regular-text" />';
    }
    public static function render_customer_subject_field() {
        $val = self::get('subject_tpl', 'customer_auto_reply');
        echo '<input type="text" name="' . self::OPT_KEY . '[customer_auto_reply][subject_tpl]" value="' . esc_attr($val) . '" class="large-text" />';
    }
    public static function render_customer_body_field() {
        $val = self::get('body_tpl', 'customer_auto_reply');
        wp_editor($val, 'customer_body_tpl', ['textarea_name' => self::OPT_KEY . '[customer_auto_reply][body_tpl]', 'media_buttons' => false, 'textarea_rows' => 10]);
        self::render_token_list(false);
    }
    public static function render_validation_api_key_field() {
        $val = self::get('validation_api_key');
        echo '<input type="text" name="' . self::OPT_KEY . '[validation_api_key]" value="' . esc_attr($val) . '" class="regular-text" />';
        echo '<p class="description">' . __('Enter your API key from emaillistvalidation.com.', 'atyourservice') . '</p>';
    }

    public static function render_token_list($include_all_fields = true) {
        $tokens = ['{name}', '{email}', '{phone}', '{service}', '{message}', '{page_url}', '{timestamp}', '{lead_id}', '{site_name}', '{admin_url}'];
        if ($include_all_fields) {
            $tokens[] = '[all-fields]';
        }
        echo '<p class="description"><strong>' . __('Available Tokens:', 'atyourservice') . '</strong> ' . implode(', ', $tokens) . '</p>';
    }


    public static function sanitize($input) : array {
        $defaults = self::defaults();
        $output = $defaults;

        // Sanitize Admin Notification
        $admin_input = $input['admin_notification'] ?? [];
        $output['admin_notification']['enabled'] = !empty($admin_input['enabled']);
        $output['admin_notification']['to'] = self::parse_emails_list($admin_input['to'] ?? '');
        $output['admin_notification']['subject_tpl'] = sanitize_text_field($admin_input['subject_tpl'] ?? $defaults['admin_notification']['subject_tpl']);
        $output['admin_notification']['body_tpl'] = wp_kses_post($admin_input['body_tpl'] ?? $defaults['admin_notification']['body_tpl']);

        // Sanitize Customer Auto-Reply
        $customer_input = $input['customer_auto_reply'] ?? [];
        $output['customer_auto_reply']['enabled'] = !empty($customer_input['enabled']);
        $output['customer_auto_reply']['from_name'] = sanitize_text_field($customer_input['from_name'] ?? $defaults['customer_auto_reply']['from_name']);
        $output['customer_auto_reply']['from_email'] = sanitize_email($customer_input['from_email'] ?? $defaults['customer_auto_reply']['from_email']);
        $output['customer_auto_reply']['subject_tpl'] = sanitize_text_field($customer_input['subject_tpl'] ?? $defaults['customer_auto_reply']['subject_tpl']);
        $output['customer_auto_reply']['body_tpl'] = wp_kses_post($customer_input['body_tpl'] ?? $defaults['customer_auto_reply']['body_tpl']);

        // Sanitize Validation API Key
        $output['validation_api_key'] = sanitize_text_field($input['validation_api_key'] ?? '');

        return $output;
    }
}
