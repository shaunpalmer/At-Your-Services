<?php
/**
 * AYS Service Type Service
 *
 * Business logic layer for service types.
 * Handles CRUD operations and validation.
 * Service types organize items (e.g., "Cleaning", "Plumbing", "Gardening").
 * Flexible per business: users define their own types.
 *
 * @since 1.0
 */

defined('ABSPATH') || exit;

class AYS_Service_Type_Service {

    /**
     * @var AYS_Service_Type_Repository
     */
    private $repository;

    /**
     * Constructor
     *
     * @param AYS_Service_Type_Repository $repository (optional)
     */
    public function __construct($repository = null) {
        $this->repository = $repository ?: new AYS_Service_Type_Repository();
    }

    /**
     * Get all active service types
     *
     * @return array Array of service type objects
     */
    public function get_all() {
        return $this->repository->get_all();
    }

    /**
     * Get service type by ID
     *
     * @param int $id Service type ID
     * @return object|null Service type object or null
     */
    public function get_by_id($id) {
        return $this->repository->get_by_id($id);
    }

    /**
     * Get service type by slug
     *
     * @param string $slug Service type slug
     * @return object|null Service type object or null
     */
    public function get_by_slug($slug) {
        return $this->repository->get_by_slug($slug);
    }

    /**
     * Create a new service type
     *
     * @param array $data Service type data
     * @return int|false Service type ID or false on failure
     *
     * @throws Exception If validation fails
     */
    public function create($data = []) {
        // Validate required fields
        if (empty($data['name'])) {
            throw new Exception('Service type name is required.');
        }

        $name = sanitize_text_field($data['name']);

        // Generate slug
        $slug = isset($data['slug']) ? sanitize_title($data['slug']) : sanitize_title($name);

        // Check for duplicate slug
        if ($this->repository->slug_exists($slug)) {
            throw new Exception('A service type with that slug already exists.');
        }

        // Prepare data
        $create_data = [
            'name'        => $name,
            'slug'        => $slug,
            'description' => isset($data['description']) ? sanitize_textarea_field($data['description']) : '',
            'icon_class'  => isset($data['icon_class']) ? sanitize_text_field($data['icon_class']) : '',
            'color_hex'   => isset($data['color_hex']) ? sanitize_hex_color($data['color_hex']) : '#0073aa',
            'sort_order'  => isset($data['sort_order']) ? intval($data['sort_order']) : 0,
        ];

        $type_id = $this->repository->create($create_data);

        if (!$type_id) {
            throw new Exception('Failed to create service type.');
        }

        do_action('ays_service_type_created', $type_id, $create_data);

        return $type_id;
    }

    /**
     * Update a service type
     *
     * @param int   $id   Service type ID
     * @param array $data Fields to update
     * @return bool True on success, false on failure
     *
     * @throws Exception If validation fails
     */
    public function update($id, $data = []) {
        $id = intval($id);

        // Verify service type exists
        $existing = $this->repository->get_by_id($id);
        if (!$existing) {
            throw new Exception('Service type not found.');
        }

        // Validate slug if provided
        if (isset($data['slug'])) {
            $new_slug = sanitize_title($data['slug']);
            if ($this->repository->slug_exists($new_slug, $id)) {
                throw new Exception('That slug is already in use.');
            }
        }

        // Prepare update data
        $update_data = [];

        if (isset($data['name'])) {
            $update_data['name'] = sanitize_text_field($data['name']);
        }

        if (isset($data['slug'])) {
            $update_data['slug'] = sanitize_title($data['slug']);
        }

        if (isset($data['description'])) {
            $update_data['description'] = sanitize_textarea_field($data['description']);
        }

        if (isset($data['icon_class'])) {
            $update_data['icon_class'] = sanitize_text_field($data['icon_class']);
        }

        if (isset($data['color_hex'])) {
            $update_data['color_hex'] = sanitize_hex_color($data['color_hex']);
        }

        if (isset($data['sort_order'])) {
            $update_data['sort_order'] = intval($data['sort_order']);
        }

        if (isset($data['status'])) {
            $status = sanitize_text_field($data['status']);
            if (in_array($status, ['active', 'archived'], true)) {
                $update_data['status'] = $status;
            }
        }

        $result = $this->repository->update($id, $update_data);

        if ($result) {
            do_action('ays_service_type_updated', $id, $update_data);
        }

        return $result;
    }

    /**
     * Delete (soft delete) a service type
     *
     * @param int $id Service type ID
     * @return bool True on success, false on failure
     */
    public function delete($id) {
        $id = intval($id);

        $result = $this->repository->delete($id);

        if ($result) {
            do_action('ays_service_type_deleted', $id);
        }

        return $result;
    }

    /**
     * Permanently hard-delete a service type
     * Unlinks all associated items first
     *
     * @param int $id Service type ID
     * @return bool True on success, false on failure
     */
    public function force_delete($id) {
        $id = intval($id);

        $result = $this->repository->force_delete($id);

        if ($result) {
            do_action('ays_service_type_force_deleted', $id);
        }

        return $result;
    }

    /**
     * Search service types
     *
     * @param string $query Search query
     * @param int    $limit Results limit
     * @return array Array of matching service types
     */
    public function search($query = '', $limit = 20) {
        return $this->repository->search($query, $limit);
    }

    /**
     * Get service type count
     *
     * @param string $status Filter by status (optional)
     * @return int Count of service types
     */
    public function count($status = null) {
        return $this->repository->count($status);
    }

    /**
     * Get all service types as dropdown options
     *
     * @param bool $include_none Include "-- None --" option
     * @return array Array of [id => name] pairs
     */
    public function get_options($include_none = true) {
        $types = $this->get_all();
        $options = [];

        if ($include_none) {
            $options[0] = __('-- None --', 'ays');
        }

        foreach ($types as $type) {
            $options[$type->id] = $type->name;
        }

        return $options;
    }

    /**
     * Validate service type data
     *
     * @param array $data Service type data
     * @return array Array of error messages (empty if valid)
     */
    public function validate($data) {
        $errors = [];

        // Validate name
        if (empty($data['name'])) {
            $errors['name'] = __('Service type name is required.', 'ays');
        }

        // Validate color hex if provided
        if (isset($data['color_hex']) && !empty($data['color_hex'])) {
            if (!preg_match('/^#[0-9A-F]{6}$/i', $data['color_hex'])) {
                $errors['color_hex'] = __('Invalid hex color format.', 'ays');
            }
        }

        // Validate sort order if provided
        if (isset($data['sort_order']) && !is_numeric($data['sort_order'])) {
            $errors['sort_order'] = __('Sort order must be a number.', 'ays');
        }

        return $errors;
    }
}
