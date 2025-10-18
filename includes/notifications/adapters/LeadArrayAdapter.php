<?php
defined('ABSPATH') || exit;

final class LeadArrayAdapter implements LeadSourceAdapterInterface {
    private array $lead;
    public function __construct(array $lead) {
        $this->lead = $lead;
    }
    public function tokens(): array {
        return [
            '{name}' => $this->lead['name'] ?? '',
            '{email}' => $this->lead['email'] ?? '',
            '{phone}' => $this->lead['phone'] ?? '',
            '{service}' => $this->lead['service'] ?? '',
            '{message}' => $this->lead['message'] ?? '',
            '{page_url}' => $this->lead['page_url'] ?? '',
            '{timestamp}' => $this->lead['timestamp'] ?? '',
        ];
    }
}
