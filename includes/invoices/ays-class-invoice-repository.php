<?php
/**
 * AYS_Invoice_Repository
 *
 * Data access layer for all invoicing operations.
 * Encapsulates $wpdb queries to keep database logic separate from business logic.
 *
 * Responsibilities:
 * - CRUD operations for invoices, clients, items, payments
 * - Query building and filtering
 * - Hash generation for external sharing
 * - Status transitions and soft deletes
 *
 * @since 1.0
 */

defined('ABSPATH') || exit;

class AYS_Invoice_Repository {
    protected $wpdb;
    protected $table_prefix;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_prefix = $wpdb->prefix;
    }

    /**
     * =====================================================================
     * CLIENTS TABLE OPERATIONS
     * =====================================================================
     */

    /**
     * Get single client by ID.
     *
     * @param int $client_id
     * @return array|null
     */
    public function get_client($client_id) {
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}ays_clients WHERE id = %d AND status != 'deleted' LIMIT 1",
            $client_id
        );
        return $this->wpdb->get_row($sql, ARRAY_A);
    }

    /**
     * Get client by email.
     *
     * @param string $email
     * @return array|null
     */
    public function get_client_by_email($email) {
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}ays_clients WHERE email = %s AND status != 'deleted' LIMIT 1",
            $email
        );
        return $this->wpdb->get_row($sql, ARRAY_A);
    }

    /**
     * Get all clients (paginated, sorted).
     *
     * @param int $page (1-indexed)
     * @param int $per_page
     * @param string $orderby (id, name, email, created_at)
     * @param string $order (ASC, DESC)
     * @return array
     */
    public function get_clients($page = 1, $per_page = 50, $orderby = 'created_at', $order = 'DESC') {
        $offset = ($page - 1) * $per_page;
        $allowed_orderby = ['id', 'name', 'email', 'created_at', 'total_billed'];
        $orderby = in_array($orderby, $allowed_orderby) ? $orderby : 'created_at';
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}ays_clients 
             WHERE status != 'deleted' 
             ORDER BY {$orderby} {$order} 
             LIMIT %d OFFSET %d",
            $per_page,
            $offset
        );
        return $this->wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Count total clients (excluding deleted).
     *
     * @return int
     */
    public function count_clients() {
        $sql = "SELECT COUNT(*) FROM {$this->table_prefix}ays_clients WHERE status != 'deleted'";
        return (int) $this->wpdb->get_var($sql);
    }

    /**
     * Insert new client.
     *
     * @param array $data (name, email required; others optional)
     * @return int|false Client ID on success, false on failure
     */
    public function insert_client($data) {
        // Validate required fields
        if (empty($data['name']) || empty($data['email'])) {
            return false;
        }

        $data['email'] = sanitize_email($data['email']);
        if (!is_email($data['email'])) {
            return false;
        }

        // Generate hash for external sharing
        if (empty($data['hash'])) {
            $data['hash'] = md5(uniqid(mt_rand(), true));
        }

        $data['status'] = isset($data['status']) ? $data['status'] : 'active';

        $insert = [
            'hash'     => $data['hash'],
            'name'     => sanitize_text_field($data['name']),
            'email'    => $data['email'],
            'secondary_email' => isset($data['secondary_email']) ? sanitize_email($data['secondary_email']) : null,
            'phone'    => isset($data['phone']) ? sanitize_text_field($data['phone']) : null,
            'mobile'   => isset($data['mobile']) ? sanitize_text_field($data['mobile']) : null,
            'address_line1' => isset($data['address_line1']) ? sanitize_text_field($data['address_line1']) : null,
            'address_line2' => isset($data['address_line2']) ? sanitize_text_field($data['address_line2']) : null,
            'city'     => isset($data['city']) ? sanitize_text_field($data['city']) : null,
            'postcode' => isset($data['postcode']) ? sanitize_text_field($data['postcode']) : null,
            'country'  => isset($data['country']) ? sanitize_text_field($data['country']) : null,
            'notes'    => isset($data['notes']) ? wp_kses_post($data['notes']) : null,
            'status'   => $data['status'],
        ];

        $result = $this->wpdb->insert(
            "{$this->table_prefix}ays_clients",
            $insert,
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );

        return $result ? $this->wpdb->insert_id : false;
    }

    /**
     * Update client.
     *
     * @param int $client_id
     * @param array $data
     * @return bool
     */
    public function update_client($client_id, $data) {
        $update = [];

        if (isset($data['name'])) {
            $update['name'] = sanitize_text_field($data['name']);
        }
        if (isset($data['email'])) {
            $email = sanitize_email($data['email']);
            if (!is_email($email)) {
                return false;
            }
            $update['email'] = $email;
        }
        if (isset($data['phone'])) {
            $update['phone'] = sanitize_text_field($data['phone']);
        }
        if (isset($data['mobile'])) {
            $update['mobile'] = sanitize_text_field($data['mobile']);
        }
        if (isset($data['notes'])) {
            $update['notes'] = wp_kses_post($data['notes']);
        }

        $update['updated_at'] = current_time('mysql');

        return (bool) $this->wpdb->update(
            "{$this->table_prefix}ays_clients",
            $update,
            ['id' => $client_id],
            array_fill(0, count($update), '%s'),
            ['%d']
        );
    }

    /**
     * Soft delete client (mark as deleted, don't remove).
     *
     * @param int $client_id
     * @return bool
     */
    public function delete_client($client_id) {
        return (bool) $this->wpdb->update(
            "{$this->table_prefix}ays_clients",
            ['status' => 'deleted', 'deleted_at' => current_time('mysql')],
            ['id' => $client_id],
            ['%s', '%s'],
            ['%d']
        );
    }

    /**
     * =====================================================================
     * ITEMS TABLE OPERATIONS
     * =====================================================================
     */

    /**
     * Get single item by ID.
     *
     * @param int $item_id
     * @return array|null
     */
    public function get_item($item_id) {
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}ays_items WHERE id = %d AND status = 'active' LIMIT 1",
            $item_id
        );
        return $this->wpdb->get_row($sql, ARRAY_A);
    }

    /**
     * Get all active items (paginated).
     *
     * @param int $page
     * @param int $per_page
     * @return array
     */
    public function get_items($page = 1, $per_page = 50) {
        $offset = ($page - 1) * $per_page;
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}ays_items 
             WHERE status = 'active' 
             ORDER BY description ASC 
             LIMIT %d OFFSET %d",
            $per_page,
            $offset
        );
        return $this->wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Count active items.
     *
     * @return int
     */
    public function count_items() {
        return (int) $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->table_prefix}ays_items WHERE status = 'active'");
    }

    /**
     * Insert new item.
     *
     * @param array $data (description, rate required)
     * @return int|false
     */
    public function insert_item($data) {
        if (empty($data['description'])) {
            return false;
        }

        if (empty($data['hash'])) {
            $data['hash'] = md5(uniqid(mt_rand(), true));
        }

        $insert = [
            'hash'        => $data['hash'],
            'description' => sanitize_text_field($data['description']),
            'details'     => isset($data['details']) ? wp_kses_post($data['details']) : null,
            'unit'        => isset($data['unit']) ? sanitize_text_field($data['unit']) : null,
            'rate'        => isset($data['rate']) ? (float) $data['rate'] : 0.00,
            'taxable'     => isset($data['taxable']) ? (int) $data['taxable'] : 1,
            'status'      => 'active',
        ];

        $result = $this->wpdb->insert(
            "{$this->table_prefix}ays_items",
            $insert,
            ['%s', '%s', '%s', '%s', '%f', '%d', '%s']
        );

        return $result ? $this->wpdb->insert_id : false;
    }

    /**
     * Update item.
     *
     * @param int $item_id
     * @param array $data
     * @return bool
     */
    public function update_item($item_id, $data) {
        $update = [];

        if (isset($data['description'])) {
            $update['description'] = sanitize_text_field($data['description']);
        }
        if (isset($data['rate'])) {
            $update['rate'] = (float) $data['rate'];
        }
        if (isset($data['taxable'])) {
            $update['taxable'] = (int) $data['taxable'];
        }

        $update['updated_at'] = current_time('mysql');

        return (bool) $this->wpdb->update(
            "{$this->table_prefix}ays_items",
            $update,
            ['id' => $item_id],
            array_fill(0, count($update), '%s'),
            ['%d']
        );
    }

    /**
     * Soft delete item.
     *
     * @param int $item_id
     * @return bool
     */
    public function delete_item($item_id) {
        return (bool) $this->wpdb->update(
            "{$this->table_prefix}ays_items",
            ['status' => 'archived'],
            ['id' => $item_id],
            ['%s'],
            ['%d']
        );
    }

    /**
     * =====================================================================
     * INVOICES TABLE OPERATIONS
     * =====================================================================
     */

    /**
     * Get single invoice by ID.
     *
     * @param int $invoice_id
     * @return array|null
     */
    public function get_invoice($invoice_id) {
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}ays_invoices WHERE id = %d AND deleted_at IS NULL LIMIT 1",
            $invoice_id
        );
        return $this->wpdb->get_row($sql, ARRAY_A);
    }

    /**
     * Get invoice by number.
     *
     * @param string $inv_number
     * @return array|null
     */
    public function get_invoice_by_number($inv_number) {
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}ays_invoices WHERE inv_number = %s AND deleted_at IS NULL LIMIT 1",
            $inv_number
        );
        return $this->wpdb->get_row($sql, ARRAY_A);
    }

    /**
     * Get invoices for a client.
     *
     * @param int $client_id
     * @param int $page
     * @param int $per_page
     * @return array
     */
    public function get_invoices_for_client($client_id, $page = 1, $per_page = 50) {
        $offset = ($page - 1) * $per_page;
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}ays_invoices 
             WHERE client_id = %d AND deleted_at IS NULL 
             ORDER BY issue_date DESC 
             LIMIT %d OFFSET %d",
            $client_id,
            $per_page,
            $offset
        );
        return $this->wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Insert new invoice.
     *
     * @param array $data (client_id, inv_number required)
     * @return int|false
     */
    public function insert_invoice($data) {
        if (empty($data['client_id']) || empty($data['inv_number'])) {
            return false;
        }

        if (empty($data['hash'])) {
            $data['hash'] = md5(uniqid(mt_rand(), true));
        }

        $insert = [
            'hash'       => $data['hash'],
            'company_id' => isset($data['company_id']) ? (int) $data['company_id'] : null,
            'client_id'  => (int) $data['client_id'],
            'inv_number' => sanitize_text_field($data['inv_number']),
            'prefix'     => isset($data['prefix']) ? sanitize_text_field($data['prefix']) : 'INV-',
            'issue_date' => isset($data['issue_date']) ? $data['issue_date'] : current_time('Y-m-d'),
            'due_date'   => isset($data['due_date']) ? $data['due_date'] : date('Y-m-d', strtotime('+7 days')),
            'subtotal'   => isset($data['subtotal']) ? (float) $data['subtotal'] : 0.00,
            'tax_total'  => isset($data['tax_total']) ? (float) $data['tax_total'] : 0.00,
            'discount'   => isset($data['discount']) ? (float) $data['discount'] : 0.00,
            'shipping'   => isset($data['shipping']) ? (float) $data['shipping'] : 0.00,
            'total'      => isset($data['total']) ? (float) $data['total'] : 0.00,
            'amount_paid' => isset($data['amount_paid']) ? (float) $data['amount_paid'] : 0.00,
            'balance'    => isset($data['balance']) ? (float) $data['balance'] : 0.00,
            'notes'      => isset($data['notes']) ? wp_kses_post($data['notes']) : null,
            'terms'      => isset($data['terms']) ? wp_kses_post($data['terms']) : null,
            'status'     => isset($data['status']) ? $data['status'] : 'draft',
        ];

        $result = $this->wpdb->insert(
            "{$this->table_prefix}ays_invoices",
            $insert,
            ['%s', '%d', '%d', '%s', '%s', '%s', '%s', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%s', '%s']
        );

        return $result ? $this->wpdb->insert_id : false;
    }

    /**
     * Update invoice.
     *
     * @param int $invoice_id
     * @param array $data
     * @return bool
     */
    public function update_invoice($invoice_id, $data) {
        $update = [];

        if (isset($data['subtotal'])) {
            $update['subtotal'] = (float) $data['subtotal'];
        }
        if (isset($data['tax_total'])) {
            $update['tax_total'] = (float) $data['tax_total'];
        }
        if (isset($data['total'])) {
            $update['total'] = (float) $data['total'];
        }
        if (isset($data['balance'])) {
            $update['balance'] = (float) $data['balance'];
        }
        if (isset($data['amount_paid'])) {
            $update['amount_paid'] = (float) $data['amount_paid'];
        }
        if (isset($data['status'])) {
            $update['status'] = $data['status'];
        }
        if (isset($data['due_date'])) {
            $update['due_date'] = $data['due_date'];
        }

        $update['updated_at'] = current_time('mysql');

        return (bool) $this->wpdb->update(
            "{$this->table_prefix}ays_invoices",
            $update,
            ['id' => $invoice_id],
            array_fill(0, count($update), '%s'),
            ['%d']
        );
    }

    /**
     * Soft delete invoice.
     *
     * @param int $invoice_id
     * @return bool
     */
    public function delete_invoice($invoice_id) {
        return (bool) $this->wpdb->update(
            "{$this->table_prefix}ays_invoices",
            ['deleted_at' => current_time('mysql')],
            ['id' => $invoice_id],
            ['%s'],
            ['%d']
        );
    }

    /**
     * =====================================================================
     * INVOICE ITEMS TABLE OPERATIONS (Line Items)
     * =====================================================================
     */

    /**
     * Get line items for an invoice.
     *
     * @param int $invoice_id
     * @return array
     */
    public function get_invoice_items($invoice_id) {
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}ays_invoice_items 
             WHERE invoice_id = %d 
             ORDER BY sort_order ASC",
            $invoice_id
        );
        return $this->wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Insert line item.
     *
     * @param array $data (invoice_id, description, qty, rate required)
     * @return int|false
     */
    public function insert_invoice_item($data) {
        if (empty($data['invoice_id']) || empty($data['description'])) {
            return false;
        }

        if (empty($data['hash'])) {
            $data['hash'] = md5(uniqid(mt_rand(), true));
        }

        $insert = [
            'hash'       => $data['hash'],
            'invoice_id' => (int) $data['invoice_id'],
            'item_id'    => isset($data['item_id']) ? (int) $data['item_id'] : null,
            'description' => sanitize_text_field($data['description']),
            'details'    => isset($data['details']) ? wp_kses_post($data['details']) : null,
            'qty'        => isset($data['qty']) ? (float) $data['qty'] : 1.00,
            'rate'       => isset($data['rate']) ? (float) $data['rate'] : 0.00,
            'taxable'    => isset($data['taxable']) ? (int) $data['taxable'] : 1,
            'line_tax'   => isset($data['line_tax']) ? (float) $data['line_tax'] : 0.00,
            'line_total' => isset($data['line_total']) ? (float) $data['line_total'] : 0.00,
            'sort_order' => isset($data['sort_order']) ? (int) $data['sort_order'] : 0,
            'status'     => 'active',
        ];

        $result = $this->wpdb->insert(
            "{$this->table_prefix}ays_invoice_items",
            $insert,
            ['%s', '%d', '%d', '%s', '%s', '%f', '%f', '%d', '%f', '%f', '%d', '%s']
        );

        return $result ? $this->wpdb->insert_id : false;
    }

    /**
     * Delete line item.
     *
     * @param int $item_id
     * @return bool
     */
    public function delete_invoice_item($item_id) {
        return (bool) $this->wpdb->delete(
            "{$this->table_prefix}ays_invoice_items",
            ['id' => $item_id],
            ['%d']
        );
    }

    /**
     * =====================================================================
     * PAYMENTS TABLE OPERATIONS
     * =====================================================================
     */

    /**
     * Get payments for an invoice.
     *
     * @param int $invoice_id
     * @return array
     */
    public function get_invoice_payments($invoice_id) {
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->table_prefix}ays_payments 
             WHERE invoice_id = %d 
             ORDER BY created_at DESC",
            $invoice_id
        );
        return $this->wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Insert payment record.
     *
     * @param array $data (invoice_id, amount required)
     * @return int|false
     */
    public function insert_payment($data) {
        if (empty($data['invoice_id']) || empty($data['amount'])) {
            return false;
        }

        if (empty($data['hash'])) {
            $data['hash'] = md5(uniqid(mt_rand(), true));
        }

        $insert = [
            'hash'       => $data['hash'],
            'invoice_id' => (int) $data['invoice_id'],
            'method'     => isset($data['method']) ? $data['method'] : 'cash',
            'amount'     => (float) $data['amount'],
            'txn_id'     => isset($data['txn_id']) ? sanitize_text_field($data['txn_id']) : null,
            'notes'      => isset($data['notes']) ? wp_kses_post($data['notes']) : null,
            'received_at' => isset($data['received_at']) ? $data['received_at'] : current_time('mysql'),
            'status'     => isset($data['status']) ? $data['status'] : 'confirmed',
        ];

        $result = $this->wpdb->insert(
            "{$this->table_prefix}ays_payments",
            $insert,
            ['%s', '%d', '%s', '%f', '%s', '%s', '%s', '%s']
        );

        return $result ? $this->wpdb->insert_id : false;
    }

    /**
     * Get total payments for an invoice.
     *
     * @param int $invoice_id
     * @return float
     */
    public function get_invoice_total_paid($invoice_id) {
        $sql = $this->wpdb->prepare(
            "SELECT SUM(amount) FROM {$this->table_prefix}ays_payments 
             WHERE invoice_id = %d AND status IN ('confirmed', 'pending')",
            $invoice_id
        );
        return (float) ($this->wpdb->get_var($sql) ?? 0);
    }
}
