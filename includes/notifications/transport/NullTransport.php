<?php
defined('ABSPATH') || exit;

final class NullTransport implements MailTransportInterface {
    public function send(string $to, string $subject, string $body, array $headers = []) : bool {
        return true; // No-op for testing/disabled
    }
}
