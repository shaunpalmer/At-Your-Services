<?php
/**
 * AYS Service Types Tab - Service Types Management
 *
 * Manages the Service Types for organizing items in the invoicing system.
 * Provides CRUD interface for service types.
 *
 * Database Table: wp_ays_service_types
 * Columns: id, name, description, created_at, updated_at
 *
 * @since 1.0
 */

defined( 'ABSPATH' ) || exit;

class AYS_Service_Types_Tab {

	/**
	 * Render the Service Types tab content
	 *
	 * @return void
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		// Display notices
		self::display_notices();

		// Get edit ID if present
		$edit_id = isset( $_GET['edit_service_type'] ) ? intval( $_GET['edit_service_type'] ) : 0;
		$edit_service_type = $edit_id ? self::get_service_type( $edit_id ) : null;

		?>
		<!-- Service Types List Section -->
		<details class="ays-details" open>
			<summary>
				🏷️ <?php esc_html_e( 'Service Types Catalog', 'atyourservice' ); ?>
				<span class="ays-badge"><?php esc_html_e( self::get_service_types_count() . ' types', 'atyourservice' ); ?></span>
			</summary>
			<div>
				<div class="left-column">
					<?php self::render_service_types_table(); ?>
				</div>
				<div class="right-column">
					<h4><?php esc_html_e( '📌 Quick Tips', 'atyourservice' ); ?></h4>
					<ul>
						<li><?php esc_html_e( 'Create service categories to organize your items', 'atyourservice' ); ?></li>
						<li><?php esc_html_e( 'Examples: Residential, Commercial, Specialty', 'atyourservice' ); ?></li>
						<li><?php esc_html_e( 'Service types are optional when creating items', 'atyourservice' ); ?></li>
						<li><?php esc_html_e( 'Use descriptive names for easy filtering', 'atyourservice' ); ?></li>
						<li><?php esc_html_e( 'Edit or delete anytime', 'atyourservice' ); ?></li>
					</ul>
				</div>
			</div>
		</details>

		<!-- Quick Add / Edit Form Section -->
		<details class="ays-details <?php echo $edit_id ? 'open' : ''; ?>">
			<summary>
				<?php if ( $edit_id ) : ?>
					✏️ <?php esc_html_e( 'Edit Service Type', 'atyourservice' ); ?>
				<?php else : ?>
					➕ <?php esc_html_e( 'Add New Service Type', 'atyourservice' ); ?>
				<?php endif; ?>
			</summary>
			<div>
				<div class="left-column">
					<?php self::render_service_type_form( $edit_service_type ); ?>
				</div>
				<div class="right-column">
					<h4><?php esc_html_e( '📌 Form Help', 'atyourservice' ); ?></h4>
					<ul>
						<li><strong><?php esc_html_e( 'Name:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Category name (e.g., "Residential Cleaning")', 'atyourservice' ); ?></li>
						<li><strong><?php esc_html_e( 'Description:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Optional details about this service type', 'atyourservice' ); ?></li>
					</ul>
				</div>
			</div>
		</details>
		<?php
		
		// If editing, add smooth scroll to form
		if ( $edit_id ) {
			?>
			<script>
				document.addEventListener('DOMContentLoaded', function() {
					const formElement = document.getElementById('ays-service-type-form');
					if (formElement) {
						setTimeout(function() {
							formElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
						}, 100);
					}
				});
			</script>
			<?php
		}
	}

	/**
	 * Render the service types list table
	 *
	 * @return void
	 */
	protected static function render_service_types_table() {
		global $wpdb;

		// Get total count for pagination
		$total_service_types = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}ays_service_types" );

		// Create paginator (20 items per page)
		$paginator = new AYS_Pagination( $total_service_types, 20, 'service_types_page' );

		// Enqueue smooth scroll script for pagination
		AYS_Pagination::enqueue_smooth_scroll();

		// Build and execute paginated query
		$query = "SELECT * FROM {$wpdb->prefix}ays_service_types ORDER BY created_at DESC";
		$service_types = $wpdb->get_results( $paginator->get_query_sql( $query ) );

		if ( empty( $service_types ) ) {
			echo '<p>' . esc_html__( 'No service types yet. Create your first one below!', 'atyourservice' ) . '</p>';
			return;
		}

		?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Name', 'atyourservice' ); ?></th>
					<th><?php esc_html_e( 'Description', 'atyourservice' ); ?></th>
					<th style="text-align: center;"><?php esc_html_e( 'Actions', 'atyourservice' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $service_types as $service_type ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $service_type->name ); ?></strong></td>
						<td><?php echo esc_html( $service_type->description ?? '' ); ?></td>
						<td style="text-align: center;">
							<a href="<?php echo esc_url( add_query_arg( [ 'edit_service_type' => $service_type->id, 'tab' => 'service-types' ], admin_url( 'admin.php?page=ays-dashboard' ) ) . '#ays-service-type-form' ); ?>" class="button button-small">
								<?php esc_html_e( 'Edit', 'atyourservice' ); ?>
							</a>
							<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'action' => 'ays_delete_service_type', 'service_type_id' => $service_type->id ], admin_url( 'admin.php?page=ays-dashboard&tab=service-types' ) ), 'ays_delete_service_type_' . $service_type->id ) ); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php esc_attr_e( 'Are you sure?', 'atyourservice' ); ?>')">
								<?php esc_html_e( 'Delete', 'atyourservice' ); ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<!-- Render pagination controls -->
		<?php $paginator->render_simple_pagination(); ?>
		<?php
	}

	/**
	 * Render the service type add/edit form
	 *
	 * @param object|null $service_type The service type to edit, or null for new.
	 * @return void
	 */
	protected static function render_service_type_form( $service_type = null ) {
		$nonce_action = $service_type ? 'ays_update_service_type_' . $service_type->id : 'ays_add_service_type';
		$action = $service_type ? 'ays_update_service_type' : 'ays_add_service_type';

		?>
		<div id="ays-service-type-form">
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ays-service-type-form">
				<?php wp_nonce_field( $nonce_action, 'ays_service_type_nonce' ); ?>
				<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>">
				<?php if ( $service_type ) : ?>
					<input type="hidden" name="service_type_id" value="<?php echo esc_attr( $service_type->id ); ?>">
				<?php endif; ?>

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="name"><?php esc_html_e( 'Name', 'atyourservice' ); ?> <span style="color: red;">*</span></label>
						</th>
						<td>
							<input 
								type="text" 
								id="name" 
								name="name" 
								value="<?php echo $service_type ? esc_attr( $service_type->name ) : ''; ?>"
								placeholder="<?php esc_attr_e( 'e.g., Residential Cleaning', 'atyourservice' ); ?>"
								required
								class="regular-text"
							>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="description"><?php esc_html_e( 'Description', 'atyourservice' ); ?></label>
						</th>
						<td>
							<textarea 
								id="description" 
								name="description" 
								placeholder="<?php esc_attr_e( 'e.g., Services for residential properties', 'atyourservice' ); ?>"
								class="regular-text"
								rows="4"
							><?php echo $service_type ? esc_textarea( $service_type->description ) : ''; ?></textarea>
						</td>
					</tr>
				</table>

				<p class="submit">
					<?php submit_button( $service_type ? __( 'Update Service Type', 'atyourservice' ) : __( 'Add Service Type', 'atyourservice' ), 'primary', 'submit', false ); ?>
					<?php if ( $service_type ) : ?>
						<a href="<?php echo esc_url( remove_query_arg( 'edit_service_type' ) ); ?>" class="button" style="margin-left: 10px;">
							<?php esc_html_e( 'Cancel', 'atyourservice' ); ?>
						</a>
					<?php endif; ?>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Display success/error notices
	 *
	 * @return void
	 */
	protected static function display_notices() {
		if ( isset( $_GET['ays_notice'] ) ) {
			$notice = sanitize_key( $_GET['ays_notice'] );
			
			switch ( $notice ) {
				case 'service_type_added':
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '✓ Service Type added successfully!', 'atyourservice' ) . '</p></div>';
					break;
				case 'service_type_updated':
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '✓ Service Type updated successfully!', 'atyourservice' ) . '</p></div>';
					break;
				case 'service_type_deleted':
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '✓ Service Type deleted successfully!', 'atyourservice' ) . '</p></div>';
					break;
				case 'service_type_error':
					echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( '✗ An error occurred. Please try again.', 'atyourservice' ) . '</p></div>';
					break;
			}
		}
	}

	/**
	 * Get a single service type
	 *
	 * @param int $id Service Type ID.
	 * @return object|null
	 */
	protected static function get_service_type( $id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ays_service_types WHERE id = %d", $id ) );
	}

	/**
	 * Get count of service types
	 *
	 * @return int
	 */
	protected static function get_service_types_count() {
		global $wpdb;
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}ays_service_types" );
	}

	/**
	 * Handle add service type via admin_post
	 *
	 * @return void
	 */
	public static function handle_add_service_type() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		if ( ! isset( $_POST['ays_service_type_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ays_service_type_nonce'] ) ), 'ays_add_service_type' ) ) {
			wp_die( esc_html__( 'Security check failed', 'atyourservice' ) );
		}

		global $wpdb;

		$name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$description = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';

		if ( ! $name ) {
			wp_redirect( add_query_arg( 'ays_notice', 'service_type_error', admin_url( 'admin.php?page=ays-dashboard&tab=service-types' ) ) );
			exit;
		}

		$result = $wpdb->insert(
			"{$wpdb->prefix}ays_service_types",
			[
				'name' => $name,
				'description' => $description,
				'created_at' => current_time( 'mysql' ),
				'updated_at' => current_time( 'mysql' ),
			],
			[ '%s', '%s', '%s', '%s' ]
		);

		if ( $result ) {
			wp_redirect( add_query_arg( 'ays_notice', 'service_type_added', admin_url( 'admin.php?page=ays-dashboard&tab=service-types' ) ) );
		} else {
			wp_redirect( add_query_arg( 'ays_notice', 'service_type_error', admin_url( 'admin.php?page=ays-dashboard&tab=service-types' ) ) );
		}
		exit;
	}

	/**
	 * Handle update service type via admin_post
	 *
	 * @return void
	 */
	public static function handle_update_service_type() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		$service_type_id = isset( $_POST['service_type_id'] ) ? intval( $_POST['service_type_id'] ) : 0;

		if ( ! isset( $_POST['ays_service_type_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ays_service_type_nonce'] ) ), 'ays_update_service_type_' . $service_type_id ) ) {
			wp_die( esc_html__( 'Security check failed', 'atyourservice' ) );
		}

		global $wpdb;

		$name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$description = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';

		if ( ! $name ) {
			wp_redirect( add_query_arg( 'ays_notice', 'service_type_error', admin_url( 'admin.php?page=ays-dashboard&tab=service-types' ) ) );
			exit;
		}

		$result = $wpdb->update(
			"{$wpdb->prefix}ays_service_types",
			[
				'name' => $name,
				'description' => $description,
				'updated_at' => current_time( 'mysql' ),
			],
			[ 'id' => $service_type_id ],
			[ '%s', '%s', '%s' ],
			[ '%d' ]
		);

		if ( $result !== false ) {
			wp_redirect( add_query_arg( 'ays_notice', 'service_type_updated', admin_url( 'admin.php?page=ays-dashboard&tab=service-types' ) ) );
		} else {
			wp_redirect( add_query_arg( 'ays_notice', 'service_type_error', admin_url( 'admin.php?page=ays-dashboard&tab=service-types' ) ) );
		}
		exit;
	}

	/**
	 * Handle delete service type via admin_post
	 *
	 * @return void
	 */
	public static function handle_delete_service_type() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		$service_type_id = isset( $_GET['service_type_id'] ) ? intval( $_GET['service_type_id'] ) : 0;
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'ays_delete_service_type_' . $service_type_id ) ) {
			wp_die( esc_html__( 'Security check failed', 'atyourservice' ) );
		}

		global $wpdb;
		$result = $wpdb->delete( "{$wpdb->prefix}ays_service_types", [ 'id' => $service_type_id ], [ '%d' ] );

		if ( $result ) {
			wp_redirect( add_query_arg( 'ays_notice', 'service_type_deleted', admin_url( 'admin.php?page=ays-dashboard&tab=service-types' ) ) );
		} else {
			wp_redirect( add_query_arg( 'ays_notice', 'service_type_error', admin_url( 'admin.php?page=ays-dashboard&tab=service-types' ) ) );
		}
		exit;
	}
}
