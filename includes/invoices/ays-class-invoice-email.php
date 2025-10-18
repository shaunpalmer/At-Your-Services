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
     * TODO: Implement using existing email system.
     *
     * @param int $invoice_id
     * @return bool
     */
    public function send_to_customer($invoice_id) {
        // TODO
        return false;
    }

    /**
     * Send invoice to admin.
     * TODO: Implement using existing email system.
     *
     * @param int $invoice_id
     * @return bool
     */
    public function send_to_admin($invoice_id) {
        // TODO
        return false;
    }
}
