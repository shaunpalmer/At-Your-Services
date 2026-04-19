<?php
/**
 * AYS_Invoice_Email
 *
 * Email sending for invoices.
 * Reuses the existing AYS notification system (AYS_Notifier).
 *
 * @since 1.0
 */

defined('ABSPATH') || exit;

class AYS_Invoice_Email {
    protected $repo;

    public function __construct() {
        $this->repo = new AYS_Invoice_Repository();
    }

    /**
     * Send invoice to customer.
     *
     * @param int $invoice_id
     * @return bool
     */
    public function send_to_customer($invoice_id) {
        $invoice_id = (int) $invoice_id;
        if ($invoice_id <= 0) {
            return false;
        }

        $invoice = $this->repo->get_invoice($invoice_id);
        if (empty($invoice)) {
            return false;
        }

        $client_id = isset($invoice['client_id']) ? (int) $invoice['client_id'] : 0;
        $client = $client_id > 0 ? $this->repo->get_client($client_id) : null;
        if (empty($client) || empty($client['email']) || !is_email($client['email'])) {
            return false;
        }

        $invoice_number = !empty($invoice['inv_number']) ? $invoice['inv_number'] : ('INV-' . $invoice_id);
        $subject = sprintf(__('Invoice %s from %s', 'atyourservice'), $invoice_number, get_bloginfo('name'));
        $body = $this->build_message_body($invoice, $client, $invoice_number);

        return (bool) wp_mail(
            sanitize_email($client['email']),
            $subject,
            $body,
            ['Content-Type: text/html; charset=UTF-8']
        );
    }

    /**
     * Send invoice to admin.
     *
     * @param int $invoice_id
     * @return bool
     */
    public function send_to_admin($invoice_id) {
        $invoice_id = (int) $invoice_id;
        if ($invoice_id <= 0) {
            return false;
        }

        $invoice = $this->repo->get_invoice($invoice_id);
        if (empty($invoice)) {
            return false;
        }

        $admin_email = get_option('admin_email');
        if (empty($admin_email) || !is_email($admin_email)) {
            return false;
        }

        $client_id = isset($invoice['client_id']) ? (int) $invoice['client_id'] : 0;
        $client = $client_id > 0 ? $this->repo->get_client($client_id) : null;
        $invoice_number = !empty($invoice['inv_number']) ? $invoice['inv_number'] : ('INV-' . $invoice_id);

        $subject = sprintf(__('Invoice %s notification', 'atyourservice'), $invoice_number);
        $body = $this->build_message_body($invoice, (array) $client, $invoice_number);

        return (bool) wp_mail(
            sanitize_email($admin_email),
            $subject,
            $body,
            ['Content-Type: text/html; charset=UTF-8']
        );
    }

    /**
     * Build a safe HTML email body for invoice notifications.
     *
     * @param array $invoice
     * @param array $client
     * @param string $invoice_number
     * @return string
     */
    protected function build_message_body(array $invoice, array $client, $invoice_number) {
        $client_name = !empty($client['name']) ? $client['name'] : __('Customer', 'atyourservice');
        $total = isset($invoice['total']) ? (float) $invoice['total'] : 0.0;
        $currency = !empty($invoice['currency']) ? $invoice['currency'] : 'NZD';
        $due_date = !empty($invoice['due_date']) ? $invoice['due_date'] : __('Not set', 'atyourservice');

        $message  = '<p>' . esc_html(sprintf(__('Hi %s,', 'atyourservice'), $client_name)) . '</p>';
        $message .= '<p>' . esc_html(sprintf(__('Your invoice %s is now available.', 'atyourservice'), $invoice_number)) . '</p>';
        $message .= '<p><strong>' . esc_html__('Total:', 'atyourservice') . '</strong> ' . esc_html(number_format($total, 2)) . ' ' . esc_html($currency) . '<br>';
        $message .= '<strong>' . esc_html__('Due Date:', 'atyourservice') . '</strong> ' . esc_html($due_date) . '</p>';
        $message .= '<p>' . esc_html__('Please sign in to your client area to view and pay this invoice.', 'atyourservice') . '</p>';

        return $message;
    }
}
