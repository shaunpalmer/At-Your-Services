<?php
/**
 * AYS_Item_Service
 *
 * Business logic for items/services/products catalog.
 * Wraps AYS_Invoice_Repository with item-specific methods.
 *
 * @since 1.0
 */

defined('ABSPATH') || exit;

class AYS_Item_Service {
    protected $repo;

    public function __construct() {
        $this->repo = new AYS_Invoice_Repository();
    }

    /**
     * Create new item.
     *
     * @param array $data (description, rate required)
     * @return int|WP_Error Item ID on success
     */
    public function create_item($data) {
        if (empty($data['description'])) {
            return new WP_Error('invalid', 'Description is required.');
        }

        $item_id = $this->repo->insert_item($data);
        if (!$item_id) {
            return new WP_Error('db_error', 'Failed to create item.');
        }

        do_action('ays_item_created', $item_id);
        return $item_id;
    }

    /**
     * Get item by ID.
     *
     * @param int $item_id
     * @return array|null
     */
    public function get_item($item_id) {
        return $this->repo->get_item($item_id);
    }

    /**
     * Update item.
     *
     * @param int $item_id
     * @param array $data
     * @return bool
     */
    public function update_item($item_id, $data) {
        $result = $this->repo->update_item($item_id, $data);
        if ($result) {
            do_action('ays_item_updated', $item_id);
        }
        return $result;
    }

    /**
     * Delete item (soft).
     *
     * @param int $item_id
     * @return bool
     */
    public function delete_item($item_id) {
        $result = $this->repo->delete_item($item_id);
        if ($result) {
            do_action('ays_item_deleted', $item_id);
        }
        return $result;
    }

    /**
     * Get all items (paginated).
     *
     * @param int $page
     * @param int $per_page
     * @return array
     */
    public function get_items($page = 1, $per_page = 50) {
        return $this->repo->get_items($page, $per_page);
    }

    /**
     * Count total items.
     *
     * @return int
     */
    public function count_items() {
        return $this->repo->count_items();
    }
}
