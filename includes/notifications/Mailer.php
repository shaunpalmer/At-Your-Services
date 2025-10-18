<?php
// includes/notifications/Mailer.php
// Singleton facade for mail transport

defined('ABSPATH') || exit;

final class Mailer {
    private static $instance = null;
    private $transport;

    private function __construct() {
        $this->transport = MailTransportFactory::create();
    }

    public static function instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function send($to, $subject, $body, $headers = []) {
        return $this->transport->send($to, $subject, $body, $headers);
    }
}
