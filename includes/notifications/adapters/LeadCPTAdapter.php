<?php
defined('ABSPATH') || exit;

final class LeadCPTAdapter implements LeadSourceAdapterInterface {
    private int $lead_id;
    public function __construct(int $lead_id) {
        $this->lead_id = $lead_id;
    }
    public function tokens(): array {
        $post = get_post($this->lead_id);
        if (!$post) return [];
        $meta = get_post_meta($this->lead_id);
        return [
            '{name}' => $meta['ays_name'][0] ?? '',
            '{email}' => $meta['ays_email'][0] ?? '',
            '{phone}' => $meta['ays_phone'][0] ?? '',
            '{service}' => $meta['ays_service'][0] ?? '',
            '{message}' => $meta['ays_message'][0] ?? '',
            '{page_url}' => $meta['ays_page_url'][0] ?? '',
            '{timestamp}' => $post->post_date,
        ];
    }
}
