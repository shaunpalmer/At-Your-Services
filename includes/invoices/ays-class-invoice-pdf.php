<?php
/**
 * AYS_Invoice_PDF
 *
 * PDF generation for invoices.
 * Uses DOMPDF for rendering.
 * TODO: Implement PDF rendering.
 *
 * @since 1.0
 */

defined('ABSPATH') || exit;

class AYS_Invoice_PDF {
    protected $repo;

    public function __construct() {
        $this->repo = new AYS_Invoice_Repository();
    }

    /**
     * Generate PDF for invoice.
     * TODO: Implement using DOMPDF.
     *
     * @param int $invoice_id
     * @return string|false PDF content or false on error
     */
    public function generate($invoice_id) {
        // TODO
        return false;
    }

    /**
     * Output PDF to browser.
     * TODO: Implement.
     *
     * @param int $invoice_id
     * @return void
     */
    public function output($invoice_id) {
        // TODO
    }
}
