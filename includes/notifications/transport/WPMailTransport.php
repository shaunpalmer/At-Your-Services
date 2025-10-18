<?php
defined('ABSPATH') || exit;

final class WPMailTransport implements MailTransportInterface {
    public function send(string $to, string $subject, string $body, array $headers = []) : bool {
        // Log the attempt
        error_log("WPMailTransport::send() attempting to send to: $to");
        
        $result = wp_mail($to, $subject, $body, $headers);
        
        if (!$result) {
            error_log("WPMailTransport::send() failed for: $to");
            // Log global mail errors if they exist
            global $phpmailer;
            if (isset($phpmailer) && is_object($phpmailer) && isset($phpmailer->ErrorInfo)) {
                error_log("PHPMailer ErrorInfo: " . $phpmailer->ErrorInfo);
            }
        } else {
            error_log("WPMailTransport::send() success for: $to");
        }
        
        return $result;
    }
}
