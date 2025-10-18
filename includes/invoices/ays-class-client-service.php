<?php
/**
 * AYS_Client_Service
 *
 * Business logic for client/customer operations.
 * Wraps AYS_Invoice_Repository with client-specific methods.
 *
 * @since 1.0
 */

defined('ABSPATH') || exit;

class AYS_Client_Service {
    protected $repo;

    public function __construct() {
        $this->repo = new AYS_Invoice_Repository();
    }

    /**
     * Create new client.
     * Email is REQUIRED.
     *
     * @param array $data (name, email required; others optional)
     * @return int|WP_Error Client ID on success
     */
    public function create_client($data) {
        if (empty($data['name']) || empty($data['email'])) {
            return new WP_Error('invalid', 'Name and email are required.');
        }

        $client_id = $this->repo->insert_client($data);
        if (!$client_id) {
            return new WP_Error('db_error', 'Failed to create client.');
        }

        do_action('ays_client_created', $client_id);
        return $client_id;
    }

    /**
     * Get client by ID.
     *
     * @param int $client_id
     * @return array|null
     */
    public function get_client($client_id) {
        return $this->repo->get_client($client_id);
    }

    /**
     * Get client by email.
     *
     * @param string $email
     * @return array|null
     */
    public function get_client_by_email($email) {
        return $this->repo->get_client_by_email($email);
    }

    /**
     * Update client.
     *
     * @param int $client_id
     * @param array $data
     * @return bool
     */
    public function update_client($client_id, $data) {
        $result = $this->repo->update_client($client_id, $data);
        if ($result) {
            do_action('ays_client_updated', $client_id);
        }
        return $result;
    }

    /**
     * Delete client (soft).
     *
     * @param int $client_id
     * @return bool
     */
    public function delete_client($client_id) {
        $result = $this->repo->delete_client($client_id);
        if ($result) {
            do_action('ays_client_deleted', $client_id);
        }
        return $result;
    }

    /**
     * Get all clients (paginated).
     *
     * @param int $page
     * @param int $per_page
     * @return array
     */
    public function get_clients($page = 1, $per_page = 50) {
        return $this->repo->get_clients($page, $per_page);
    }

    /**
     * Count total clients.
     *
     * @return int
     */
    public function count_clients() {
        return $this->repo->count_clients();
    }

    /**
     * Get total billed for a client.
     *
     * @param int $client_id
     * @return float
     */
    public function get_client_total_billed($client_id) {
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT SUM(total) FROM {$wpdb->prefix}ays_invoices WHERE client_id = %d AND status IN ('sent','viewed','overdue','paid')",
            $client_id
        );
        return (float) ($wpdb->get_var($sql) ?? 0);
    }
}
