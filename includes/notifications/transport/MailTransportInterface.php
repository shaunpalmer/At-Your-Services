<?php
defined('ABSPATH') || exit;

interface MailTransportInterface {
    public function send(string $to, string $subject, string $body, array $headers = []) : bool;
}
