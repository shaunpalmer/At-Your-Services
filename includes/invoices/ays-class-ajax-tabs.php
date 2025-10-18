<?php
/**
 * AYS AJAX Tabs Handler
 *
 * Provides REST endpoints for loading tab content via AJAX
 * Eliminates page reloads when switching between invoicing tabs
 *
 * Endpoints:
 * - POST /wp-json/ays/v1/invoicing/tab/{tab_name}
 *
 * @since 1.0
 */

defined( 'ABSPATH' ) || exit;

class AYS_AJAX_Tabs {

	/**
	 * Initialize REST endpoints
	 */
	public static function init() {
		add_action( 'rest_api_init', [ self::class, 'register_tab_endpoint' ] );
	}

	/**
	 * Register the tab content endpoint
	 */
	public static function register_tab_endpoint() {
		register_rest_route(
			'ays/v1',
			'/invoicing/tab/(?P<tab_name>\w+)',
			[
				'methods'             => 'GET',
				'callback'            => [ self::class, 'get_tab_content' ],
				'permission_callback' => function ( WP_REST_Request $request ) {
					// Check user capabilities
					if ( ! current_user_can( 'manage_options' ) ) {
						return false;
					}
					// REST nonce is automatically verified by WordPress if X-WP-Nonce header is present
					// and the user is authenticated. This is handled by the REST API itself.
					return true;
				},
			]
		);
	}

	/**
	 * Get tab content via AJAX
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public static function get_tab_content( WP_REST_Request $request ) {
		$tab_name = sanitize_text_field( $request->get_param( 'tab_name' ) );

		// Validate tab name
		$allowed_tabs = [ 'invoices', 'clients', 'items', 'payments', 'reports', 'settings' ];
		if ( ! in_array( $tab_name, $allowed_tabs, true ) ) {
			return new WP_REST_Response(
				[ 'error' => esc_html__( 'Invalid tab', 'atyourservice' ) ],
				400
			);
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_REST_Response(
				[ 'error' => esc_html__( 'Unauthorized', 'atyourservice' ) ],
				403
			);
		}

		// Start output buffering to capture rendered content
		ob_start();

		// Render the appropriate tab
		switch ( $tab_name ) {
			case 'invoices':
				if ( class_exists( 'AYS_Invoices_Tab' ) ) {
					AYS_Invoices_Tab::render();
				}
				break;

			case 'clients':
				if ( class_exists( 'AYS_Clients_Tab' ) ) {
					AYS_Clients_Tab::render();
				}
				break;

			case 'items':
				if ( class_exists( 'AYS_Items_Tab' ) ) {
					AYS_Items_Tab::render();
				}
				break;

			case 'payments':
				if ( class_exists( 'AYS_Payments_Tab' ) ) {
					AYS_Payments_Tab::render();
				}
				break;

			case 'reports':
				// Reports tab content
				?>
				<details class="ays-details" open>
					<summary>
						📊 <?php esc_html_e( 'Reports & Analytics', 'ays' ); ?>
					</summary>
					<div>
						<div class="left-column">
							<p><?php esc_html_e( 'Financial reports and analytics will appear here.', 'ays' ); ?></p>
						</div>
						<div class="right-column">
							<h4><?php esc_html_e( '📈 Reports Available', 'ays' ); ?></h4>
							<ul>
								<li><?php esc_html_e( 'Revenue by period', 'ays' ); ?></li>
								<li><?php esc_html_e( 'Outstanding invoices', 'ays' ); ?></li>
								<li><?php esc_html_e( 'Top clients', 'ays' ); ?></li>
								<li><?php esc_html_e( 'Service type breakdown', 'ays' ); ?></li>
							</ul>
						</div>
					</div>
				</details>
				<?php
				break;

			case 'settings':
				if ( class_exists( 'AYS_Invoice_Admin_UI' ) ) {
					// Create a temporary instance to call render_settings_tab
					$ui = new AYS_Invoice_Admin_UI();
					$ui->render_settings_tab();
				}
				break;
		}

		// Get buffered content
		$content = ob_get_clean();

		// Return as JSON response
		return new WP_REST_Response(
			[
				'success' => true,
				'tab'     => $tab_name,
				'content' => $content,
			],
			200
		);
	}
}
