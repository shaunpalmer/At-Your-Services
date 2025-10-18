<?php
/**
 * AYS Service Type Repository
 *
 * Data access layer for service types.
 * Handles all database queries for wp_ays_service_types table.
 * All queries sanitized and escaped via $wpdb.
 *
 * Service Types:
 * - Categorize items (e.g., "Cleaning", "Plumbing", "Gardening")
 * - Flexible per business (user-defined, not hardcoded)
 * - Used for filtering invoices and organizing items
 * - Each business type (cleaner, plumber, electrician) defines their own
 *
 * @since 1.0
 */

defined('ABSPATH') || exit;

class AYS_Service_Type_Repository {

    /**
     * Get all active service types, ordered by sort_order
     *
     * @return array Array of service type objects
     */
    public function get_all() {
        global $wpdb;

        $sql = $wpdb->prepare("
            SELECT id, hash, name, slug, description, icon_class, color_hex, sort_order, status
            FROM {$wpdb->prefix}ays_service_types
            WHERE status = %s
            ORDER BY sort_order ASC, name ASC
        ", 'active');

        return $wpdb->get_results($sql);
    }

    /**
     * Get service type by ID
     *
     * @param int $id Service type ID
     * @return object|null Service type object or null
     */
    public function get_by_id($id) {
        global $wpdb;

        $id = intval($id);
        if ($id <= 0) {
            return null;
        }

        $sql = $wpdb->prepare("
            SELECT id, hash, name, slug, description, icon_class, color_hex, sort_order, status, created_at, updated_at
            FROM {$wpdb->prefix}ays_service_types
            WHERE id = %d
        ", $id);

        return $wpdb->get_row($sql);
    }

    /**
     * Get service type by slug
     *
     * @param string $slug Service type slug
     * @return object|null Service type object or null
     */
    public function get_by_slug($slug) {
        global $wpdb;

        $slug = sanitize_title($slug);
        if (empty($slug)) {
            return null;
        }

        $sql = $wpdb->prepare("
            SELECT id, hash, name, slug, description, icon_class, color_hex, sort_order, status, created_at, updated_at
            FROM {$wpdb->prefix}ays_service_types
            WHERE slug = %s
        ", $slug);

        return $wpdb->get_row($sql);
    }

    /**
     * Create a new service type
     *
     * @param array $data {
     *     @type string $name         Service type name (required)
     *     @type string $slug         URL slug (auto-generated if not provided)
     *     @type string $description  Service type description
     *     @type string $icon_class   Dashicon or custom icon class
     *     @type string $color_hex    Hex color code (7 chars: #RRGGBB)
     *     @type int    $sort_order   Sort order (default: 0)
     * }
     * @return int|false Service type ID or false on failure
     */
    public function create($data = []) {
        global $wpdb;

        // Validate required fields
        $name = isset($data['name']) ? sanitize_text_field($data['name']) : '';
        if (empty($name)) {
            return false;
        }

        // Generate slug if not provided
        $slug = isset($data['slug']) ? sanitize_title($data['slug']) : sanitize_title($name);

        // Check for duplicate slug
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}ays_service_types WHERE slug = %s LIMIT 1",
            $slug
        ));
        if ($existing) {
            return false; // Slug already exists
        }

        // Prepare data
        $insert_data = [
            'hash'        => md5(uniqid(mt_rand(), true)),
            'name'        => $name,
            'slug'        => $slug,
            'description' => isset($data['description']) ? wp_kses_post($data['description']) : '',
            'icon_class'  => isset($data['icon_class']) ? sanitize_text_field($data['icon_class']) : '',
            'color_hex'   => isset($data['color_hex']) ? sanitize_hex_color($data['color_hex']) : '#0073aa',
            'sort_order'  => isset($data['sort_order']) ? intval($data['sort_order']) : 0,
            'status'      => 'active',
        ];

        $formats = ['%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s'];

        $result = $wpdb->insert("{$wpdb->prefix}ays_service_types", $insert_data, $formats);

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Update a service type
     *
     * @param int   $id   Service type ID
     * @param array $data Fields to update
     * @return bool True on success, false on failure
     */
    public function update($id, $data = []) {
        global $wpdb;

        $id = intval($id);
        if ($id <= 0) {
            return false;
        }

        $update_data = [];
        $formats = [];

        if (isset($data['name'])) {
            $update_data['name'] = sanitize_text_field($data['name']);
            $formats[] = '%s';
        }

        if (isset($data['slug'])) {
            $new_slug = sanitize_title($data['slug']);

            // Check if new slug conflicts with existing (other than this ID)
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}ays_service_types WHERE slug = %s AND id != %d LIMIT 1",
                $new_slug,
                $id
            ));
            if ($existing) {
                return false; // Slug conflict
            }

            $update_data['slug'] = $new_slug;
            $formats[] = '%s';
        }

        if (isset($data['description'])) {
            $update_data['description'] = wp_kses_post($data['description']);
            $formats[] = '%s';
        }

        if (isset($data['icon_class'])) {
            $update_data['icon_class'] = sanitize_text_field($data['icon_class']);
            $formats[] = '%s';
        }

        if (isset($data['color_hex'])) {
            $update_data['color_hex'] = sanitize_hex_color($data['color_hex']);
            $formats[] = '%s';
        }

        if (isset($data['sort_order'])) {
            $update_data['sort_order'] = intval($data['sort_order']);
            $formats[] = '%d';
        }

        if (isset($data['status'])) {
            $status = sanitize_text_field($data['status']);
            if (in_array($status, ['active', 'archived'], true)) {
                $update_data['status'] = $status;
                $formats[] = '%s';
            }
        }

        if (empty($update_data)) {
            return false; // Nothing to update
        }

        $formats[] = '%d'; // For the WHERE clause

        return (bool) $wpdb->update(
            "{$wpdb->prefix}ays_service_types",
            $update_data,
            ['id' => $id],
            $formats,
            ['%d']
        );
    }

    /**
     * Delete (soft delete) a service type
     *
     * @param int $id Service type ID
     * @return bool True on success, false on failure
     */
    public function delete($id) {
        global $wpdb;

        $id = intval($id);
        if ($id <= 0) {
            return false;
        }

        // Soft delete: mark as archived
        return (bool) $wpdb->update(
            "{$wpdb->prefix}ays_service_types",
            ['status' => 'archived'],
            ['id' => $id],
            ['%s'],
            ['%d']
        );
    }

    /**
     * Permanently delete a service type (hard delete)
     *
     * @param int $id Service type ID
     * @return bool True on success, false on failure
     */
    public function force_delete($id) {
        global $wpdb;

        $id = intval($id);
        if ($id <= 0) {
            return false;
        }

        // First, unlink any items that reference this service type
        $wpdb->update(
            "{$wpdb->prefix}ays_items",
            ['service_type_id' => null],
            ['service_type_id' => $id],
            ['%s'],
            ['%d']
        );

        // Then hard delete the service type
        return (bool) $wpdb->delete(
            "{$wpdb->prefix}ays_service_types",
            ['id' => $id],
            ['%d']
        );
    }

    /**
     * Search service types by keyword
     *
     * @param string $query Search query
     * @param int    $limit Results limit
     * @return array Array of service type objects
     */
    public function search($query = '', $limit = 20) {
        global $wpdb;

        $query = sanitize_text_field($query);
        $limit = intval($limit);

        $sql = "
            SELECT id, hash, name, slug, description, icon_class, color_hex, sort_order, status
            FROM {$wpdb->prefix}ays_service_types
            WHERE status = 'active'
        ";

        if (!empty($query)) {
            $search_term = '%' . $wpdb->esc_like($query) . '%';
            $sql .= $wpdb->prepare(
                " AND (name LIKE %s OR description LIKE %s)",
                $search_term,
                $search_term
            );
        }

        $sql .= " ORDER BY sort_order ASC, name ASC";

        if ($limit > 0) {
            $sql .= $wpdb->prepare(" LIMIT %d", $limit);
        }

        return $wpdb->get_results($sql);
    }

    /**
     * Check if service type slug exists
     *
     * @param string $slug Service type slug
     * @param int    $exclude_id Exclude this ID from check (for updates)
     * @return bool True if exists, false otherwise
     */
    public function slug_exists($slug, $exclude_id = 0) {
        global $wpdb;

        $slug = sanitize_title($slug);
        if (empty($slug)) {
            return false;
        }

        $exclude_id = intval($exclude_id);

        $sql = $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}ays_service_types WHERE slug = %s",
            $slug
        );

        if ($exclude_id > 0) {
            $sql .= $wpdb->prepare(" AND id != %d", $exclude_id);
        }

        return (bool) $wpdb->get_row($sql . " LIMIT 1");
    }

    /**
     * Get count of service types
     *
     * @param string $status Filter by status (optional)
     * @return int Count of service types
     */
    public function count($status = null) {
        global $wpdb;

        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}ays_service_types";

        if ($status !== null) {
            $status = sanitize_text_field($status);
            $sql = $wpdb->prepare($sql . " WHERE status = %s", $status);
        }

        return intval($wpdb->get_var($sql));
    }
}
