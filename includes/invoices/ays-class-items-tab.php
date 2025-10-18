<?php
/**
 * AYS Items Tab - Items Catalog Management
 *
 * Manages the Items/Products catalog for the invoicing system.
 * Provides CRUD interface for service items with collapsible details sections.
 *
 * Database Table: wp_ays_items
 * Columns: id, description, details, service_type_id, rate, taxable, created_at, updated_at
 *
 * @since 1.0
 */

defined( 'ABSPATH' ) || exit;

class AYS_Items_Tab {

	/**
	 * Render the Items tab content
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
		$edit_id = isset( $_GET['edit_item'] ) ? intval( $_GET['edit_item'] ) : 0;
		$edit_item = $edit_id ? self::get_item( $edit_id ) : null;

		?>
		<!-- Items List Section -->
		<details class="ays-details" open>
			<summary>
				📦 <?php esc_html_e( 'Items Catalog', 'atyourservice' ); ?>
				<span class="ays-badge"><?php esc_html_e( self::get_items_count() . ' items', 'atyourservice' ); ?></span>
			</summary>
			<div>
				<div class="left-column">
					<?php self::render_items_table(); ?>
				</div>
				<div class="right-column">
					<h4><?php esc_html_e( '📌 Quick Tips', 'atyourservice' ); ?></h4>
					<ul>
						<li><?php esc_html_e( 'Create items for all services you offer', 'atyourservice' ); ?></li>
						<li><?php esc_html_e( 'Set rate per unit (hourly, per meter, per item)', 'atyourservice' ); ?></li>
						<li><?php esc_html_e( 'Mark taxable items for GST calculation', 'atyourservice' ); ?></li>
						<li><?php esc_html_e( 'Organize by service type for easy filtering', 'atyourservice' ); ?></li>
						<li><?php esc_html_e( 'Edit or delete anytime', 'atyourservice' ); ?></li>
					</ul>
				</div>
			</div>
		</details>

		<!-- Quick Add / Edit Form Section -->
		<details class="ays-details <?php echo $edit_id ? 'open' : ''; ?>">
			<summary>
				<?php if ( $edit_id ) : ?>
					✏️ <?php esc_html_e( 'Edit Item', 'atyourservice' ); ?>
				<?php else : ?>
					➕ <?php esc_html_e( 'Add New Item', 'atyourservice' ); ?>
				<?php endif; ?>
			</summary>
			<div>
				<div class="left-column">
					<?php self::render_item_form( $edit_item ); ?>
				</div>
				<div class="right-column">
					<h4><?php esc_html_e( '📌 Form Help', 'atyourservice' ); ?></h4>
					<ul>
						<li><strong><?php esc_html_e( 'Description:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Name of the service (e.g., "Carpet Shampoo")', 'atyourservice' ); ?></li>
						<li><strong><?php esc_html_e( 'Details:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Additional info (e.g., "per room")', 'atyourservice' ); ?></li>
						<li><strong><?php esc_html_e( 'Service Type:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Category for organization', 'atyourservice' ); ?></li>
						<li><strong><?php esc_html_e( 'Rate:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Price per unit (hourly, meter, item, etc.)', 'atyourservice' ); ?></li>
						<li><strong><?php esc_html_e( 'Taxable:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Check if this item is subject to tax', 'atyourservice' ); ?></li>
					</ul>
				</div>
			</div>
		</details>
		<?php
	}

	/**
	 * Render the items list table
	 *
	 * @return void
	 */
	protected static function render_items_table() {
		global $wpdb;
		$items = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}ays_items ORDER BY created_at DESC" );

		if ( empty( $items ) ) {
			echo '<p>' . esc_html__( 'No items yet. Create your first item below!', 'atyourservice' ) . '</p>';
			return;
		}

		?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Description', 'atyourservice' ); ?></th>
					<th><?php esc_html_e( 'Details', 'atyourservice' ); ?></th>
					<th><?php esc_html_e( 'Service Type', 'atyourservice' ); ?></th>
					<th style="text-align: right;"><?php esc_html_e( 'Rate', 'atyourservice' ); ?></th>
					<th style="text-align: center;"><?php esc_html_e( 'Taxable', 'atyourservice' ); ?></th>
					<th style="text-align: center;"><?php esc_html_e( 'Actions', 'atyourservice' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $items as $item ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $item->description ); ?></strong></td>
						<td><?php echo esc_html( $item->details ); ?></td>
						<td><?php echo esc_html( self::get_service_type_name( $item->service_type_id ) ); ?></td>
						<td style="text-align: right;"><code>$<?php echo esc_html( number_format( $item->rate, 2 ) ); ?></code></td>
						<td style="text-align: center;">
							<?php echo $item->taxable ? '✓ Yes' : '○ No'; ?>
						</td>
						<td style="text-align: center;">
							<a href="<?php echo esc_url( add_query_arg( 'edit_item', $item->id ) ); ?>" class="button button-small">
								<?php esc_html_e( 'Edit', 'atyourservice' ); ?>
							</a>
							<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'action' => 'ays_delete_item', 'item_id' => $item->id ] ), 'ays_delete_item_' . $item->id ) ); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php esc_attr_e( 'Are you sure?', 'atyourservice' ); ?>')">
								<?php esc_html_e( 'Delete', 'atyourservice' ); ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Render the item add/edit form
	 *
	 * @param object|null $item The item to edit, or null for new item.
	 * @return void
	 */
	protected static function render_item_form( $item = null ) {
		global $wpdb;

		// Get service types for dropdown
		$service_types = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}ays_service_types ORDER BY name" );

		$nonce_action = $item ? 'ays_update_item_' . $item->id : 'ays_add_item';
		$action = $item ? 'ays_update_item' : 'ays_add_item';

		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ays-item-form">
			<?php wp_nonce_field( $nonce_action, 'ays_item_nonce' ); ?>
			<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>">
			<?php if ( $item ) : ?>
				<input type="hidden" name="item_id" value="<?php echo esc_attr( $item->id ); ?>">
			<?php endif; ?>

			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="description"><?php esc_html_e( 'Description', 'atyourservice' ); ?> <span style="color: red;">*</span></label>
					</th>
					<td>
						<input 
							type="text" 
							id="description" 
							name="description" 
							value="<?php echo $item ? esc_attr( $item->description ) : ''; ?>"
							placeholder="<?php esc_attr_e( 'e.g., Carpet Shampoo', 'atyourservice' ); ?>"
							required
							class="regular-text"
						>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="details"><?php esc_html_e( 'Details', 'atyourservice' ); ?></label>
					</th>
					<td>
						<input 
							type="text" 
							id="details" 
							name="details" 
							value="<?php echo $item ? esc_attr( $item->details ) : ''; ?>"
							placeholder="<?php esc_attr_e( 'e.g., per room, per hour', 'atyourservice' ); ?>"
							class="regular-text"
						>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="service_type_id"><?php esc_html_e( 'Service Type', 'atyourservice' ); ?></label>
					</th>
					<td>
						<select id="service_type_id" name="service_type_id" class="regular-text">
							<option value="0"><?php esc_html_e( 'None', 'atyourservice' ); ?></option>
							<?php foreach ( $service_types as $st ) : ?>
								<option value="<?php echo esc_attr( $st->id ); ?>" <?php selected( $item && $item->service_type_id === intval( $st->id ) ); ?>>
									<?php echo esc_html( $st->name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="rate"><?php esc_html_e( 'Rate', 'atyourservice' ); ?> <span style="color: red;">*</span></label>
					</th>
					<td>
						<input 
							type="number" 
							id="rate" 
							name="rate" 
							value="<?php echo $item ? esc_attr( number_format( $item->rate, 2, '.', '' ) ) : ''; ?>"
							placeholder="0.00"
							step="0.01"
							min="0"
							required
							class="regular-text"
							style="width: 150px;"
						>
						<span style="margin-left: 10px; color: #666;"><?php esc_html_e( '(per unit)', 'atyourservice' ); ?></span>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="taxable"><?php esc_html_e( 'Taxable', 'atyourservice' ); ?></label>
					</th>
					<td>
						<input 
							type="checkbox" 
							id="taxable" 
							name="taxable" 
							value="1"
							<?php echo $item && $item->taxable ? 'checked' : ''; ?>
						>
						<span><?php esc_html_e( 'Apply tax to this item', 'atyourservice' ); ?></span>
					</td>
				</tr>
			</table>

			<p class="submit">
				<?php submit_button( $item ? __( 'Update Item', 'atyourservice' ) : __( 'Add Item', 'atyourservice' ), 'primary', 'submit', false ); ?>
				<?php if ( $item ) : ?>
					<a href="<?php echo esc_url( remove_query_arg( 'edit_item' ) ); ?>" class="button" style="margin-left: 10px;">
						<?php esc_html_e( 'Cancel', 'atyourservice' ); ?>
					</a>
				<?php endif; ?>
			</p>
		</form>
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
				case 'item_added':
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '✓ Item added successfully!', 'atyourservice' ) . '</p></div>';
					break;
				case 'item_updated':
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '✓ Item updated successfully!', 'atyourservice' ) . '</p></div>';
					break;
				case 'item_deleted':
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '✓ Item deleted successfully!', 'atyourservice' ) . '</p></div>';
					break;
				case 'item_error':
					echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( '✗ An error occurred. Please try again.', 'atyourservice' ) . '</p></div>';
					break;
			}
		}
	}

	/**
	 * Handle form submissions (add, update, delete) - DEPRECATED
	 * Now handled by admin_post actions in main plugin file
	 *
	 * @return void
	 */
	protected static function handle_form_submission() {
		// Deprecated - handled by admin_post hooks
	}

	/**
	 * Handle add item via admin_post
	 *
	 * @return void
	 */
	public static function handle_add_item() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		if ( ! isset( $_POST['ays_item_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ays_item_nonce'] ) ), 'ays_add_item' ) ) {
			wp_die( esc_html__( 'Security check failed', 'atyourservice' ) );
		}

		global $wpdb;

		$description = isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '';
		$details = isset( $_POST['details'] ) ? sanitize_text_field( wp_unslash( $_POST['details'] ) ) : '';
		$service_type_id = isset( $_POST['service_type_id'] ) ? intval( $_POST['service_type_id'] ) : 0;
		$rate = isset( $_POST['rate'] ) ? floatval( $_POST['rate'] ) : 0;
		$taxable = isset( $_POST['taxable'] ) ? 1 : 0;

		if ( ! $description || ! $rate ) {
			wp_redirect( add_query_arg( 'ays_notice', 'item_error', admin_url( 'admin.php?page=ays_invoicing_dashboard&tab=items' ) ) );
			exit;
		}

		$result = $wpdb->insert(
			"{$wpdb->prefix}ays_items",
			[
				'description' => $description,
				'details' => $details,
				'service_type_id' => $service_type_id,
				'rate' => $rate,
				'taxable' => $taxable,
				'created_at' => current_time( 'mysql' ),
				'updated_at' => current_time( 'mysql' ),
			],
			[ '%s', '%s', '%d', '%f', '%d', '%s', '%s' ]
		);

		if ( $result ) {
			wp_redirect( add_query_arg( 'ays_notice', 'item_added', admin_url( 'admin.php?page=ays_invoicing_dashboard&tab=items' ) ) );
		} else {
			wp_redirect( add_query_arg( 'ays_notice', 'item_error', admin_url( 'admin.php?page=ays_invoicing_dashboard&tab=items' ) ) );
		}
		exit;
	}

	/**
	 * Handle update item via admin_post
	 *
	 * @return void
	 */
	public static function handle_update_item() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		$item_id = isset( $_POST['item_id'] ) ? intval( $_POST['item_id'] ) : 0;

		if ( ! isset( $_POST['ays_item_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ays_item_nonce'] ) ), 'ays_update_item_' . $item_id ) ) {
			wp_die( esc_html__( 'Security check failed', 'atyourservice' ) );
		}

		global $wpdb;

		$description = isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '';
		$details = isset( $_POST['details'] ) ? sanitize_text_field( wp_unslash( $_POST['details'] ) ) : '';
		$service_type_id = isset( $_POST['service_type_id'] ) ? intval( $_POST['service_type_id'] ) : 0;
		$rate = isset( $_POST['rate'] ) ? floatval( $_POST['rate'] ) : 0;
		$taxable = isset( $_POST['taxable'] ) ? 1 : 0;

		if ( ! $description || ! $rate ) {
			wp_redirect( add_query_arg( 'ays_notice', 'item_error', admin_url( 'admin.php?page=ays_invoicing_dashboard&tab=items' ) ) );
			exit;
		}

		$result = $wpdb->update(
			"{$wpdb->prefix}ays_items",
			[
				'description' => $description,
				'details' => $details,
				'service_type_id' => $service_type_id,
				'rate' => $rate,
				'taxable' => $taxable,
				'updated_at' => current_time( 'mysql' ),
			],
			[ 'id' => $item_id ],
			[ '%s', '%s', '%d', '%f', '%d', '%s' ],
			[ '%d' ]
		);

		if ( $result !== false ) {
			wp_redirect( add_query_arg( 'ays_notice', 'item_updated', admin_url( 'admin.php?page=ays_invoicing_dashboard&tab=items' ) ) );
		} else {
			wp_redirect( add_query_arg( 'ays_notice', 'item_error', admin_url( 'admin.php?page=ays_invoicing_dashboard&tab=items' ) ) );
		}
		exit;
	}

	/**
	 * Handle delete item via admin_post
	 *
	 * @return void
	 */
	public static function handle_delete_item() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		$item_id = isset( $_GET['item_id'] ) ? intval( $_GET['item_id'] ) : 0;
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'ays_delete_item_' . $item_id ) ) {
			wp_die( esc_html__( 'Security check failed', 'atyourservice' ) );
		}

		global $wpdb;
		$result = $wpdb->delete( "{$wpdb->prefix}ays_items", [ 'id' => $item_id ], [ '%d' ] );

		if ( $result ) {
			wp_redirect( add_query_arg( 'ays_notice', 'item_deleted', admin_url( 'admin.php?page=ays_invoicing_dashboard&tab=items' ) ) );
		} else {
			wp_redirect( add_query_arg( 'ays_notice', 'item_error', admin_url( 'admin.php?page=ays_invoicing_dashboard&tab=items' ) ) );
		}
		exit;
	}

	/**
	 * Get a single item by ID
	 *
	 * @param int $item_id The item ID.
	 * @return object|null The item object or null.
	 */
	protected static function get_item( $item_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ays_items WHERE id = %d", $item_id ) );
	}

	/**
	 * Get total items count
	 *
	 * @return int Total number of items.
	 */
	protected static function get_items_count() {
		global $wpdb;
		return intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}ays_items" ) );
	}

	/**
	 * Get service type name by ID
	 *
	 * @param int $service_type_id The service type ID.
	 * @return string The service type name or empty string.
	 */
	protected static function get_service_type_name( $service_type_id ) {
		if ( ! $service_type_id ) {
			return '';
		}
		global $wpdb;
		$name = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}ays_service_types WHERE id = %d", $service_type_id ) );
		return $name ? $name : '';
	}
}
