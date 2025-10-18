<?php
/**
 * AYS_Payment_Service
 *
 * Business logic for payment recording and reconciliation.
 * Wraps AYS_Invoice_Repository with payment-specific methods.
 *
 * @since 1.0
 */

defined('ABSPATH') || exit;

class AYS_Payment_Service {
    protected $repo;

    public function __construct() {
        $this->repo = new AYS_Invoice_Repository();
    }

    /**
     * Record payment (delegated to InvoiceService).
     * This class exists for potential extension (webhooks, reconciliation, etc.).
     *
     * @param int $invoice_id
     * @param array $payment_data
     * @return int|WP_Error Payment ID on success
     */
    public function record_payment($invoice_id, $payment_data) {
        $invoice_service = new AYS_Invoice_Service();
        return $invoice_service->record_payment($invoice_id, $payment_data);
    }

    /**
     * Get payments for invoice.
     *
     * @param int $invoice_id
     * @return array
     */
    public function get_invoice_payments($invoice_id) {
        return $this->repo->get_invoice_payments($invoice_id);
    }

    /**
     * Get total paid for invoice.
     *
     * @param int $invoice_id
     * @return float
     */
    public function get_invoice_total_paid($invoice_id) {
        return $this->repo->get_invoice_total_paid($invoice_id);
    }
}
