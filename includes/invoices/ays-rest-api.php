<?php
/**
 * AYS REST API - Item Search Endpoint
 *
 * Provides REST API endpoints for the invoicing system.
 * Endpoints:
 * - GET /wp-json/ays/v1/items/search (admin-only, paginated item search)
 *
 * @since 1.0
 */

defined( 'ABSPATH' ) || exit;

class AYS_REST_API {

	/**
	 * Initialize REST API routes
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );
	}

	/**
	 * Register REST API routes
	 *
	 * @return void
	 */
	public static function register_routes() {
		// Items search endpoint
		register_rest_route(
			'ays/v1',
			'/items/search',
			[
				'methods'             => 'GET',
				'callback'            => [ __CLASS__, 'items_search' ],
				'permission_callback' => [ __CLASS__, 'check_admin_permission' ],
				'args'                => [
					'search' => [
						'type'              => 'string',
						'description'       => 'Search query for item description or details',
						'sanitize_callback' => 'sanitize_text_field',
						'required'          => false,
					],
					'page'   => [
						'type'              => 'integer',
						'description'       => 'Pagination page number (default: 1)',
						'sanitize_callback' => 'absint',
						'default'           => 1,
					],
					'per_page' => [
						'type'              => 'integer',
						'description'       => 'Results per page (default: 20, max: 100)',
						'sanitize_callback' => 'absint',
						'default'           => 20,
					],
				],
			]
		);
	}

	/**
	 * Check if user has admin permission
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return bool|WP_Error True if user is admin, otherwise error.
	 */
	public static function check_admin_permission( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to access this resource.', 'atyourservice' ),
				[ 'status' => 403 ]
			);
		}
		return true;
	}

	/**
	 * Get items with optional search filter
	 *
	 * Endpoint: GET /wp-json/ays/v1/items/search
	 * Query params: search, page, per_page
	 *
	 * Returns:
	 * {
	 *   "success": true,
	 *   "data": [
	 *     {
	 *       "id": 1,
	 *       "description": "Carpet Shampoo",
	 *       "details": "per room",
	 *       "service_type_id": 2,
	 *       "rate": "49.99",
	 *       "taxable": true,
	 *       "created_at": "2025-10-18 10:30:00"
	 *     },
	 *     ...
	 *   ],
	 *   "total": 42,
	 *   "page": 1,
	 *   "per_page": 20,
	 *   "total_pages": 3
	 * }
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response The response object.
	 */
	public static function items_search( $request ) {
		global $wpdb;

		$search = $request->get_param( 'search' );
		$page = max( 1, $request->get_param( 'page' ) );
		$per_page = min( 100, max( 1, $request->get_param( 'per_page' ) ) ); // Max 100

		// Build query
		$where = '';
		if ( $search ) {
			$search_escaped = '%' . $wpdb->esc_like( $search ) . '%';
			$where = $wpdb->prepare(
				" WHERE (description LIKE %s OR details LIKE %s)",
				$search_escaped,
				$search_escaped
			);
		}

		// Get total count
		$total = intval(
			$wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->prefix}ays_items" . $where
			)
		);

		// Get paginated results
		$offset = ( $page - 1 ) * $per_page;
		$items = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}ays_items" . $where . " ORDER BY created_at DESC LIMIT {$per_page} OFFSET {$offset}"
		);

		// Format response
		$data = [];
		foreach ( $items as $item ) {
			$data[] = [
				'id'              => intval( $item->id ),
				'description'     => $item->description,
				'details'         => $item->details,
				'service_type_id' => intval( $item->service_type_id ),
				'rate'            => floatval( $item->rate ),
				'taxable'         => (bool) $item->taxable,
				'created_at'      => $item->created_at,
			];
		}

		$total_pages = ceil( $total / $per_page );

		return new WP_REST_Response(
			[
				'success'     => true,
				'data'        => $data,
				'total'       => $total,
				'page'        => $page,
				'per_page'    => $per_page,
				'total_pages' => $total_pages,
			],
			200
		);
	}
}

// Initialize REST API on plugins_loaded
add_action( 'plugins_loaded', [ 'AYS_REST_API', 'init' ], 10 );
