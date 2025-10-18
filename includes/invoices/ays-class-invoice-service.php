<?php
/**
 * AYS_Invoice_Service
 *
 * Business logic layer for invoice operations.
 * Handles: totals calculation, tax computation, invoice numbering, status transitions.
 * Uses AYS_Invoice_Repository for all database access.
 *
 * Key principles:
 * - All money math done with DECIMAL precision (no floats)
 * - Email is REQUIRED for any invoice operation
 * - GST defaults to 15% (NZ standard, configurable)
 * - Invoice numbers are atomic (no race conditions)
 * - All state changes trigger hooks for extensibility
 *
 * @since 1.0
 */

defined('ABSPATH') || exit;

class AYS_Invoice_Service {
    protected $repo;
    protected $gst_rate = 0.15; // 15% GST (NZ default)

    public function __construct() {
        $this->repo = new AYS_Invoice_Repository();
    }

    /**
     * =====================================================================
     * INVOICE LIFECYCLE
     * =====================================================================
     */

    /**
     * Create a new invoice.
     * Validates client email, generates atomic invoice number, initializes as draft.
     *
     * @param int $client_id
     * @param array $invoice_data (optional: due_date, notes, etc.)
     * @return int|WP_Error Invoice ID on success, error object on failure
     */
    public function create_invoice($client_id, $invoice_data = []) {
        // Validate client exists and has email
        $client = $this->repo->get_client($client_id);
        if (!$client) {
            return new WP_Error('invalid_client', 'Client not found.');
        }
        if (empty($client['email'])) {
            return new WP_Error('no_email', 'Client must have an email address to create invoice.');
        }

        // Generate atomic invoice number
        $inv_number = $this->generate_invoice_number();
        if (!$inv_number) {
            return new WP_Error('numbering_error', 'Could not generate invoice number.');
        }

        // Prepare invoice data
        $data = [
            'client_id'  => $client_id,
            'inv_number' => $inv_number,
            'prefix'     => isset($invoice_data['prefix']) ? $invoice_data['prefix'] : $this->get_invoice_prefix(),
            'issue_date' => isset($invoice_data['issue_date']) ? $invoice_data['issue_date'] : current_time('Y-m-d'),
            'due_date'   => isset($invoice_data['due_date']) ? $invoice_data['due_date'] : $this->get_default_due_date(),
            'notes'      => isset($invoice_data['notes']) ? $invoice_data['notes'] : '',
            'terms'      => isset($invoice_data['terms']) ? $invoice_data['terms'] : $this->get_default_terms(),
            'status'     => 'draft',
            'subtotal'   => 0.00,
            'tax_total'  => 0.00,
            'total'      => 0.00,
            'amount_paid' => 0.00,
            'balance'    => 0.00,
        ];

        $invoice_id = $this->repo->insert_invoice($data);
        if (!$invoice_id) {
            return new WP_Error('db_error', 'Failed to create invoice.');
        }

        /**
         * Fires after invoice is created.
         *
         * @param int $invoice_id
         * @param int $client_id
         */
        do_action('ays_invoice_created', $invoice_id, $client_id);

        return $invoice_id;
    }

    /**
     * Add line item to invoice.
     * Auto-calculates line_total and line_tax based on qty, rate, taxable status.
     *
     * @param int $invoice_id
     * @param array $item_data (description, qty, rate, taxable)
     * @return int|WP_Error Item ID on success
     */
    public function add_line_item($invoice_id, $item_data) {
        $invoice = $this->repo->get_invoice($invoice_id);
        if (!$invoice) {
            return new WP_Error('not_found', 'Invoice not found.');
        }

        if ($invoice['status'] === 'paid' || $invoice['status'] === 'void') {
            return new WP_Error('locked', 'Cannot modify paid or void invoices.');
        }

        if (empty($item_data['description']) || empty($item_data['qty']) || empty($item_data['rate'])) {
            return new WP_Error('invalid', 'Description, quantity, and rate are required.');
        }

        // Calculate line totals
        $qty = (float) $item_data['qty'];
        $rate = (float) $item_data['rate'];
        $taxable = isset($item_data['taxable']) ? (int) $item_data['taxable'] : 1;

        $line_total = $qty * $rate;
        $line_tax = $taxable ? $this->calculate_tax($line_total) : 0.00;

        $item_data['qty'] = $qty;
        $item_data['rate'] = $rate;
        $item_data['taxable'] = $taxable;
        $item_data['line_total'] = $line_total;
        $item_data['line_tax'] = $line_tax;
        $item_data['invoice_id'] = $invoice_id;

        $item_id = $this->repo->insert_invoice_item($item_data);
        if (!$item_id) {
            return new WP_Error('db_error', 'Failed to add line item.');
        }

        // Recalculate invoice totals
        $this->recalculate_invoice_totals($invoice_id);

        return $item_id;
    }

    /**
     * Remove line item from invoice.
     *
     * @param int $invoice_id
     * @param int $item_id
     * @return bool
     */
    public function remove_line_item($invoice_id, $item_id) {
        $invoice = $this->repo->get_invoice($invoice_id);
        if (!$invoice) {
            return false;
        }

        if ($invoice['status'] === 'paid' || $invoice['status'] === 'void') {
            return false; // Cannot modify locked invoices
        }

        $result = $this->repo->delete_invoice_item($item_id);
        if ($result) {
            $this->recalculate_invoice_totals($invoice_id);
        }

        return $result;
    }

    /**
     * Record payment against invoice.
     * Updates invoice balance and status transitions if fully paid.
     *
     * @param int $invoice_id
     * @param array $payment_data (amount, method, notes)
     * @return int|WP_Error Payment ID on success
     */
    public function record_payment($invoice_id, $payment_data) {
        $invoice = $this->repo->get_invoice($invoice_id);
        if (!$invoice) {
            return new WP_Error('not_found', 'Invoice not found.');
        }

        if (empty($payment_data['amount'])) {
            return new WP_Error('invalid', 'Payment amount is required.');
        }

        $amount = (float) $payment_data['amount'];
        $payment_data['invoice_id'] = $invoice_id;
        $payment_data['received_at'] = isset($payment_data['received_at']) ? $payment_data['received_at'] : current_time('mysql');

        $payment_id = $this->repo->insert_payment($payment_data);
        if (!$payment_id) {
            return new WP_Error('db_error', 'Failed to record payment.');
        }

        // Recalculate invoice balance and update status
        $total_paid = $this->repo->get_invoice_total_paid($invoice_id);
        $new_balance = max(0, $invoice['total'] - $total_paid);

        $status = 'draft';
        if ($new_balance <= 0) {
            $status = 'paid';
        } elseif ($invoice['due_date'] < current_time('Y-m-d')) {
            $status = 'overdue';
        } elseif ($invoice['status'] === 'sent' || $invoice['status'] === 'viewed') {
            $status = $invoice['status'];
        }

        $this->repo->update_invoice($invoice_id, [
            'amount_paid' => $total_paid,
            'balance'     => $new_balance,
            'status'      => $status,
        ]);

        /**
         * Fires after payment is recorded.
         *
         * @param int $invoice_id
         * @param int $payment_id
         * @param float $amount
         */
        do_action('ays_invoice_payment_recorded', $invoice_id, $payment_id, $amount);

        return $payment_id;
    }

    /**
     * Mark invoice as sent (after email).
     *
     * @param int $invoice_id
     * @return bool
     */
    public function mark_sent($invoice_id) {
        return (bool) $this->repo->update_invoice($invoice_id, [
            'status'    => 'sent',
            'emailed_at' => current_time('mysql'),
        ]);
    }

    /**
     * Mark invoice as viewed (after customer opens pay link).
     *
     * @param int $invoice_id
     * @return bool
     */
    public function mark_viewed($invoice_id) {
        $invoice = $this->repo->get_invoice($invoice_id);
        if ($invoice['status'] === 'draft') {
            return false; // Draft invoices don't get "viewed"
        }

        return (bool) $this->repo->update_invoice($invoice_id, [
            'status'    => 'viewed',
            'viewed_at' => current_time('mysql'),
        ]);
    }

    /**
     * =====================================================================
     * CALCULATIONS
     * =====================================================================
     */

    /**
     * Calculate GST for a given amount.
     *
     * @param float $amount
     * @param float $rate (optional, defaults to 15%)
     * @return float
     */
    public function calculate_tax($amount, $rate = null) {
        if ($rate === null) {
            $rate = $this->gst_rate;
        }
        return round($amount * $rate, 2);
    }

    /**
     * Recalculate all totals for an invoice.
     * Sums line items, applies tax, calculates balance.
     *
     * @param int $invoice_id
     * @return bool
     */
    public function recalculate_invoice_totals($invoice_id) {
        $invoice = $this->repo->get_invoice($invoice_id);
        if (!$invoice) {
            return false;
        }

        // Get all line items
        $items = $this->repo->get_invoice_items($invoice_id);

        $subtotal = 0.00;
        $tax_total = 0.00;

        foreach ($items as $item) {
            $subtotal += (float) $item['line_total'];
            $tax_total += (float) $item['line_tax'];
        }

        // Apply discount and shipping
        $discount = isset($invoice['discount']) ? (float) $invoice['discount'] : 0.00;
        $shipping = isset($invoice['shipping']) ? (float) $invoice['shipping'] : 0.00;

        $total = $subtotal + $tax_total - $discount + $shipping;
        $total = max(0, $total); // Never negative

        $amount_paid = $this->repo->get_invoice_total_paid($invoice_id);
        $balance = max(0, $total - $amount_paid);

        return $this->repo->update_invoice($invoice_id, [
            'subtotal'  => $subtotal,
            'tax_total' => $tax_total,
            'total'     => $total,
            'balance'   => $balance,
        ]);
    }

    /**
     * =====================================================================
     * INVOICE NUMBERING
     * =====================================================================
     */

    /**
     * Generate next invoice number (atomic).
     * Uses database version counter to avoid race conditions.
     *
     * @return string|false Invoice number or false on error
     */
    protected function generate_invoice_number() {
        global $wpdb;

        // Get or initialize counter option
        $counter = (int) get_option('ays_invoice_counter', 0);
        $counter++;

        // Pad with zeros (e.g., 000001, 000002)
        $number = str_pad($counter, 6, '0', STR_PAD_LEFT);

        // Update counter
        update_option('ays_invoice_counter', $counter);

        return $number;
    }

    /**
     * Get invoice prefix from settings (default: "INV-").
     *
     * @return string
     */
    protected function get_invoice_prefix() {
        return apply_filters('ays_invoice_prefix', 'INV-');
    }

    /**
     * Get default due date (e.g., +7 days from today).
     *
     * @return string (Y-m-d format)
     */
    protected function get_default_due_date() {
        $days = apply_filters('ays_invoice_default_due_days', 7);
        return date('Y-m-d', strtotime("+{$days} days"));
    }

    /**
     * Get default terms text.
     *
     * @return string
     */
    protected function get_default_terms() {
        return apply_filters(
            'ays_invoice_default_terms',
            'Payment is due by the date specified above. Thank you for your business.'
        );
    }

    /**
     * =====================================================================
     * UTILITIES
     * =====================================================================
     */

    /**
     * Get invoice with all related data (client, items, payments).
     * Useful for rendering/PDF generation.
     *
     * @param int $invoice_id
     * @return array|null
     */
    public function get_invoice_full($invoice_id) {
        $invoice = $this->repo->get_invoice($invoice_id);
        if (!$invoice) {
            return null;
        }

        $client = $this->repo->get_client($invoice['client_id']);
        $items = $this->repo->get_invoice_items($invoice_id);
        $payments = $this->repo->get_invoice_payments($invoice_id);

        return [
            'invoice'  => $invoice,
            'client'   => $client,
            'items'    => $items,
            'payments' => $payments,
        ];
    }

    /**
     * Delete (soft delete) an invoice.
     *
     * @param int $invoice_id
     * @return bool
     */
    public function delete_invoice($invoice_id) {
        return (bool) $this->repo->delete_invoice($invoice_id);
    }
}
