<?php
defined('ABSPATH') || exit;

interface LeadSourceAdapterInterface {
    /**
     * @return array Normalized tokens for template substitution
     */
    public function tokens(): array;
}
