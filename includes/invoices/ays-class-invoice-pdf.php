<?php
/**
 * AYS_Invoice_PDF
 *
 * PDF generation for invoices.
 * Uses DOMPDF for rendering.
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
     *
     * @param int $invoice_id
     * @return string|false PDF content or false on error
     */
    public function generate($invoice_id) {
        $invoice_id = (int) $invoice_id;
        if ($invoice_id <= 0) {
            return false;
        }

        $invoice = $this->repo->get_invoice($invoice_id);
        if (empty($invoice)) {
            return false;
        }

        $html = $this->build_invoice_html($invoice_id);
        if ($html === '') {
            return false;
        }

        if (!class_exists('\Dompdf\Dompdf')) {
            return $html; // Graceful fallback when DOMPDF is unavailable.
        }

        try {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return $dompdf->output();
        } catch (\Throwable $e) {
            if (function_exists('error_log')) {
                error_log('[AYS] Invoice PDF generation failed: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Output PDF to browser.
     *
     * @param int $invoice_id
     * @return void
     */
    public function output($invoice_id) {
        $content = $this->generate($invoice_id);
        if ($content === false) {
            wp_die(esc_html__('Unable to generate invoice output.', 'atyourservice'));
        }

        $invoice_number = 'INV-' . (int) $invoice_id;
        $invoice = $this->repo->get_invoice((int) $invoice_id);
        if (!empty($invoice['inv_number'])) {
            $invoice_number = $invoice['inv_number'];
        }

        if (class_exists('\Dompdf\Dompdf')) {
            nocache_headers();
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . sanitize_file_name($invoice_number . '.pdf') . '"');
            echo $content;
            exit;
        }

        // Fallback: return printable HTML if DOMPDF isn't installed.
        nocache_headers();
        header('Content-Type: text/html; charset=' . get_bloginfo('charset'));
        echo $content;
        exit;
    }

    /**
     * Build invoice HTML using the shared renderer.
     *
     * @param int $invoice_id
     * @return string
     */
    protected function build_invoice_html($invoice_id) {
        if (!class_exists('AYS_Invoice_Renderer')) {
            return '';
        }

        ob_start();
        AYS_Invoice_Renderer::render((int) $invoice_id, 'admin');
        return (string) ob_get_clean();
    }
}
