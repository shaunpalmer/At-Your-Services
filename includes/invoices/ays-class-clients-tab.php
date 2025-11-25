<?php
/**
 * AYS Clients Tab - Client/Customer Management
 *
 * Manages the Clients/Customer directory for the invoicing system.
 * Provides CRUD interface for client records with email uniqueness validation.
 *
 * Database Table: wp_ays_clients
 * Columns: id, hash, name, email, secondary_email, phone, mobile, address*, notes, total_billed, status, created_at, updated_at, deleted_at
 *
 * @since 1.0
 */

defined( 'ABSPATH' ) || exit;

class AYS_Clients_Tab {

	/**
	 * Render the Clients tab content
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
		$edit_id = isset( $_GET['edit_client'] ) ? intval( $_GET['edit_client'] ) : 0;
		$edit_client = $edit_id ? self::get_client( $edit_id ) : null;

		?>
		<!-- Clients List Section -->
		<details class="ays-details" open>
			<summary>
				👥 <?php esc_html_e( 'Clients Directory', 'atyourservice' ); ?>
				<span class="ays-badge"><?php echo esc_html( self::get_clients_count() . ' clients' ); ?></span>
			</summary>
			<div>
				<div class="left-column">
					<?php self::render_clients_table(); ?>
				</div>
				<div class="right-column">
					<h4><?php esc_html_e( '📌 Quick Tips', 'atyourservice' ); ?></h4>
					<ul>
						<li><?php esc_html_e( 'Each client record stores contact info', 'atyourservice' ); ?></li>
						<li><?php esc_html_e( 'Email addresses must be unique', 'atyourservice' ); ?></li>
						<li><?php esc_html_e( 'Add phone/mobile for easy contact', 'atyourservice' ); ?></li>
						<li><?php esc_html_e( 'Track total billed per client', 'atyourservice' ); ?></li>
						<li><?php esc_html_e( 'Archive inactive clients', 'atyourservice' ); ?></li>
					</ul>
				</div>
			</div>
		</details>

		<!-- Quick Add / Edit Form Section -->
		<details class="ays-details <?php echo $edit_id ? 'open' : ''; ?>">
			<summary>
				<?php if ( $edit_id ) : ?>
					✏️ <?php esc_html_e( 'Edit Client', 'atyourservice' ); ?>
				<?php else : ?>
					➕ <?php esc_html_e( 'Add New Client', 'atyourservice' ); ?>
				<?php endif; ?>
			</summary>
			<div>
				<div class="left-column">
					<?php self::render_client_form( $edit_client ); ?>
				</div>
				<div class="right-column">
					<h4><?php esc_html_e( '📌 Form Help', 'atyourservice' ); ?></h4>
					<ul>
						<li><strong><?php esc_html_e( 'Name:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Full name of the client/business', 'atyourservice' ); ?></li>
						<li><strong><?php esc_html_e( 'Email:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Primary contact email (must be unique)', 'atyourservice' ); ?></li>
						<li><strong><?php esc_html_e( 'Phone/Mobile:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Contact numbers', 'atyourservice' ); ?></li>
						<li><strong><?php esc_html_e( 'Address:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Full physical address (optional)', 'atyourservice' ); ?></li>
						<li><strong><?php esc_html_e( 'Status:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Active/Archived status', 'atyourservice' ); ?></li>
					</ul>
				</div>
			</div>
		</details>
		<?php
	}

	/**
	 * Render the clients list table
	 *
	 * @return void
	 */
	protected static function render_clients_table() {
		global $wpdb;

		// Get total count for pagination
		$total_clients = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}ays_clients WHERE status != 'deleted'" );

		// Create paginator (20 items per page)
		$paginator = new AYS_Pagination( $total_clients, 20, 'clients_page' );

		// Enqueue smooth scroll script for pagination
		AYS_Pagination::enqueue_smooth_scroll();

		// Build and execute paginated query
		$query = "SELECT * FROM {$wpdb->prefix}ays_clients WHERE status != 'deleted' ORDER BY created_at DESC";
		$clients = $wpdb->get_results( $paginator->get_query_sql( $query ) );

		if ( empty( $clients ) ) {
			echo '<p>' . esc_html__( 'No clients yet. Create your first client below!', 'atyourservice' ) . '</p>';
			return;
		}

		?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Name', 'atyourservice' ); ?></th>
					<th><?php esc_html_e( 'Email', 'atyourservice' ); ?></th>
					<th><?php esc_html_e( 'Phone', 'atyourservice' ); ?></th>
					<th><?php esc_html_e( 'City', 'atyourservice' ); ?></th>
					<th style="text-align: right;"><?php esc_html_e( 'Total Billed', 'atyourservice' ); ?></th>
					<th style="text-align: center;"><?php esc_html_e( 'Status', 'atyourservice' ); ?></th>
					<th style="text-align: center;"><?php esc_html_e( 'Actions', 'atyourservice' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $clients as $client ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $client->name ); ?></strong></td>
						<td><a href="mailto:<?php echo esc_attr( $client->email ); ?>"><?php echo esc_html( $client->email ); ?></a></td>
						<td><?php echo esc_html( $client->phone ?: $client->mobile ?: '—' ); ?></td>
						<td><?php echo esc_html( $client->city ?: '—' ); ?></td>
						<td style="text-align: right;"><code>$<?php echo esc_html( number_format( $client->total_billed, 2 ) ); ?></code></td>
						<td style="text-align: center;">
							<?php 
							$status_badge = $client->status === 'active' ? '🟢 Active' : '🔶 Archived';
							echo esc_html( $status_badge );
							?>
						</td>
						<td style="text-align: center;">
							<a href="<?php echo esc_url( add_query_arg( [ 'edit_client' => $client->id, 'tab' => 'clients' ], admin_url( 'admin.php?page=ays-dashboard' ) ) ); ?>" class="button button-small">
								<?php esc_html_e( 'Edit', 'atyourservice' ); ?>
							</a>
							<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'action' => 'ays_delete_client', 'client_id' => $client->id ], admin_url( 'admin.php?page=ays-dashboard&tab=clients' ) ), 'ays_delete_client_' . $client->id ) ); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php esc_attr_e( 'Are you sure?', 'atyourservice' ); ?>')">
								<?php esc_html_e( 'Delete', 'atyourservice' ); ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<!-- Render pagination controls -->
		<?php $paginator->render_simple_pagination(); ?>
		<script>
			(function() {
				// Smooth scroll to table when pagination link is clicked
				document.addEventListener('click', function(e) {
					if (e.target.closest('.pagination a, .pagination-nav a')) {
						setTimeout(function() {
							const table = document.querySelector('table.widefat');
							if (table) {
								table.scrollIntoView({ behavior: 'smooth', block: 'start' });
							}
						}, 100);
					}
				});
			})();
		</script>
		<?php
	}

	/**
	 * Render the client add/edit form
	 *
	 * @param object|null $client The client to edit, or null for new client.
	 * @return void
	 */
	protected static function render_client_form( $client = null ) {
		$nonce_action = $client ? 'ays_update_client_' . $client->id : 'ays_add_client';
		$action = $client ? 'ays_update_client' : 'ays_add_client';

		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ays-client-form">
			<?php wp_nonce_field( $nonce_action, 'ays_client_nonce' ); ?>
			<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>">
			<?php if ( $client ) : ?>
				<input type="hidden" name="client_id" value="<?php echo esc_attr( $client->id ); ?>">
			<?php endif; ?>

			<table class="form-table">
				<!-- Name -->
				<tr>
					<th scope="row">
						<label for="name"><?php esc_html_e( 'Name', 'atyourservice' ); ?> <span style="color: red;">*</span></label>
					</th>
					<td>
						<input 
							type="text" 
							id="name" 
							name="name" 
							value="<?php echo $client ? esc_attr( $client->name ) : ''; ?>"
							placeholder="<?php esc_attr_e( 'e.g., John Doe or ABC Corp', 'atyourservice' ); ?>"
							required
							class="regular-text"
						>
					</td>
				</tr>

				<!-- Email -->
				<tr>
					<th scope="row">
						<label for="email"><?php esc_html_e( 'Email', 'atyourservice' ); ?> <span style="color: red;">*</span></label>
					</th>
					<td>
						<input 
							type="email" 
							id="email" 
							name="email" 
							value="<?php echo $client ? esc_attr( $client->email ) : ''; ?>"
							placeholder="<?php esc_attr_e( 'john@example.com', 'atyourservice' ); ?>"
							required
							class="regular-text"
						>
						<p class="description"><?php esc_html_e( 'Must be unique', 'atyourservice' ); ?></p>
					</td>
				</tr>

				<!-- Secondary Email -->
				<tr>
					<th scope="row">
						<label for="secondary_email"><?php esc_html_e( 'Secondary Email', 'atyourservice' ); ?></label>
					</th>
					<td>
						<input 
							type="email" 
							id="secondary_email" 
							name="secondary_email" 
							value="<?php echo $client ? esc_attr( $client->secondary_email ) : ''; ?>"
							placeholder="<?php esc_attr_e( 'alternate@example.com', 'atyourservice' ); ?>"
							class="regular-text"
						>
					</td>
				</tr>

				<!-- Phone -->
				<tr>
					<th scope="row">
						<label for="phone"><?php esc_html_e( 'Phone', 'atyourservice' ); ?></label>
					</th>
					<td>
						<input 
							type="tel" 
							id="phone" 
							name="phone" 
							value="<?php echo $client ? esc_attr( $client->phone ) : ''; ?>"
							placeholder="<?php esc_attr_e( '+1 (555) 123-4567', 'atyourservice' ); ?>"
							class="regular-text"
						>
					</td>
				</tr>

				<!-- Mobile -->
				<tr>
					<th scope="row">
						<label for="mobile"><?php esc_html_e( 'Mobile', 'atyourservice' ); ?></label>
					</th>
					<td>
						<input 
							type="tel" 
							id="mobile" 
							name="mobile" 
							value="<?php echo $client ? esc_attr( $client->mobile ) : ''; ?>"
							placeholder="<?php esc_attr_e( '+1 (555) 987-6543', 'atyourservice' ); ?>"
							class="regular-text"
						>
					</td>
				</tr>

				<!-- Address Line 1 -->
				<tr>
					<th scope="row">
						<label for="address_line1"><?php esc_html_e( 'Address Line 1', 'atyourservice' ); ?></label>
					</th>
					<td>
						<input 
							type="text" 
							id="address_line1" 
							name="address_line1" 
							value="<?php echo $client ? esc_attr( $client->address_line1 ) : ''; ?>"
							placeholder="<?php esc_attr_e( 'Street address', 'atyourservice' ); ?>"
							class="regular-text"
						>
					</td>
				</tr>

				<!-- Address Line 2 -->
				<tr>
					<th scope="row">
						<label for="address_line2"><?php esc_html_e( 'Address Line 2', 'atyourservice' ); ?></label>
					</th>
					<td>
						<input 
							type="text" 
							id="address_line2" 
							name="address_line2" 
							value="<?php echo $client ? esc_attr( $client->address_line2 ) : ''; ?>"
							placeholder="<?php esc_attr_e( 'Apartment, suite, etc.', 'atyourservice' ); ?>"
							class="regular-text"
						>
					</td>
				</tr>

				<!-- City -->
				<tr>
					<th scope="row">
						<label for="city"><?php esc_html_e( 'City', 'atyourservice' ); ?></label>
					</th>
					<td>
						<input 
							type="text" 
							id="city" 
							name="city" 
							value="<?php echo $client ? esc_attr( $client->city ) : ''; ?>"
							placeholder="<?php esc_attr_e( 'City', 'atyourservice' ); ?>"
							class="regular-text"
						>
					</td>
				</tr>

				<!-- Postcode -->
				<tr>
					<th scope="row">
						<label for="postcode"><?php esc_html_e( 'Postcode', 'atyourservice' ); ?></label>
					</th>
					<td>
						<input 
							type="text" 
							id="postcode" 
							name="postcode" 
							value="<?php echo $client ? esc_attr( $client->postcode ) : ''; ?>"
							placeholder="<?php esc_attr_e( 'Postal code', 'atyourservice' ); ?>"
							class="regular-text"
							style="width: 150px;"
						>
					</td>
				</tr>

				<!-- Country -->
				<tr>
					<th scope="row">
						<label for="country"><?php esc_html_e( 'Country', 'atyourservice' ); ?></label>
					</th>
					<td>
						<input 
							type="text" 
							id="country" 
							name="country" 
							value="<?php echo $client ? esc_attr( $client->country ) : ''; ?>"
							placeholder="<?php esc_attr_e( 'Country', 'atyourservice' ); ?>"
							class="regular-text"
						>
					</td>
				</tr>

				<!-- Notes -->
				<tr>
					<th scope="row">
						<label for="notes"><?php esc_html_e( 'Notes', 'atyourservice' ); ?></label>
					</th>
					<td>
						<textarea 
							id="notes" 
							name="notes" 
							placeholder="<?php esc_attr_e( 'Any special notes or instructions', 'atyourservice' ); ?>"
							class="regular-text"
							rows="3"
						><?php echo $client ? esc_textarea( $client->notes ) : ''; ?></textarea>
					</td>
				</tr>

				<!-- Status -->
				<tr>
					<th scope="row">
						<label for="status"><?php esc_html_e( 'Status', 'atyourservice' ); ?></label>
					</th>
					<td>
						<select id="status" name="status" class="regular-text">
							<option value="active" <?php selected( ! $client || $client->status === 'active' ); ?>>
								<?php esc_html_e( '🟢 Active', 'atyourservice' ); ?>
							</option>
							<option value="archived" <?php selected( $client && $client->status === 'archived' ); ?>>
								<?php esc_html_e( '🔶 Archived', 'atyourservice' ); ?>
							</option>
						</select>
					</td>
				</tr>
			</table>

			<p class="submit">
				<?php submit_button( $client ? __( 'Update Client', 'atyourservice' ) : __( 'Add Client', 'atyourservice' ), 'primary', 'submit', false ); ?>
				<?php if ( $client ) : ?>
					<a href="<?php echo esc_url( remove_query_arg( 'edit_client' ) ); ?>" class="button" style="margin-left: 10px;">
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
				case 'client_added':
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '✓ Client added successfully!', 'atyourservice' ) . '</p></div>';
					break;
				case 'client_updated':
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '✓ Client updated successfully!', 'atyourservice' ) . '</p></div>';
					break;
				case 'client_deleted':
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '✓ Client deleted successfully!', 'atyourservice' ) . '</p></div>';
					break;
				case 'email_exists':
					echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( '✗ Email already exists. Please use a different email address.', 'atyourservice' ) . '</p></div>';
					break;
				case 'client_error':
					echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( '✗ An error occurred. Please try again.', 'atyourservice' ) . '</p></div>';
					break;
			}
		}
	}

	/**
	 * Handle add client via admin_post
	 *
	 * @return void
	 */
	public static function handle_add_client() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		if ( ! isset( $_POST['ays_client_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ays_client_nonce'] ) ), 'ays_add_client' ) ) {
			wp_die( esc_html__( 'Security check failed', 'atyourservice' ) );
		}

		global $wpdb;

		$name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$secondary_email = isset( $_POST['secondary_email'] ) ? sanitize_email( wp_unslash( $_POST['secondary_email'] ) ) : '';
		$phone = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
		$mobile = isset( $_POST['mobile'] ) ? sanitize_text_field( wp_unslash( $_POST['mobile'] ) ) : '';
		$address_line1 = isset( $_POST['address_line1'] ) ? sanitize_text_field( wp_unslash( $_POST['address_line1'] ) ) : '';
		$address_line2 = isset( $_POST['address_line2'] ) ? sanitize_text_field( wp_unslash( $_POST['address_line2'] ) ) : '';
		$city = isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '';
		$postcode = isset( $_POST['postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['postcode'] ) ) : '';
		$country = isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '';
		$notes = isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '';
		$status = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'active';

		if ( ! $name || ! $email ) {
			wp_redirect( add_query_arg( 'ays_notice', 'client_error', admin_url( 'admin.php?page=ays-dashboard&tab=clients' ) ) );
			exit;
		}

		// Check if email already exists
		$email_exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}ays_clients WHERE email = %s AND status != 'deleted'", $email ) );
		if ( $email_exists ) {
			wp_redirect( add_query_arg( 'ays_notice', 'email_exists', admin_url( 'admin.php?page=ays-dashboard&tab=clients' ) ) );
			exit;
		}

		$hash = md5( $email . time() );

		$result = $wpdb->insert(
			"{$wpdb->prefix}ays_clients",
			[
				'hash' => $hash,
				'name' => $name,
				'email' => $email,
				'secondary_email' => $secondary_email,
				'phone' => $phone,
				'mobile' => $mobile,
				'address_line1' => $address_line1,
				'address_line2' => $address_line2,
				'city' => $city,
				'postcode' => $postcode,
				'country' => $country,
				'notes' => $notes,
				'status' => $status,
				'created_at' => current_time( 'mysql' ),
				'updated_at' => current_time( 'mysql' ),
			],
			[ '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
		);

		if ( $result ) {
			wp_redirect( add_query_arg( 'ays_notice', 'client_added', admin_url( 'admin.php?page=ays-dashboard&tab=clients' ) ) );
		} else {
			wp_redirect( add_query_arg( 'ays_notice', 'client_error', admin_url( 'admin.php?page=ays-dashboard&tab=clients' ) ) );
		}
		exit;
	}

	/**
	 * Handle update client via admin_post
	 *
	 * @return void
	 */
	public static function handle_update_client() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		$client_id = isset( $_POST['client_id'] ) ? intval( $_POST['client_id'] ) : 0;

		if ( ! isset( $_POST['ays_client_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ays_client_nonce'] ) ), 'ays_update_client_' . $client_id ) ) {
			wp_die( esc_html__( 'Security check failed', 'atyourservice' ) );
		}

		global $wpdb;

		$name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$secondary_email = isset( $_POST['secondary_email'] ) ? sanitize_email( wp_unslash( $_POST['secondary_email'] ) ) : '';
		$phone = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
		$mobile = isset( $_POST['mobile'] ) ? sanitize_text_field( wp_unslash( $_POST['mobile'] ) ) : '';
		$address_line1 = isset( $_POST['address_line1'] ) ? sanitize_text_field( wp_unslash( $_POST['address_line1'] ) ) : '';
		$address_line2 = isset( $_POST['address_line2'] ) ? sanitize_text_field( wp_unslash( $_POST['address_line2'] ) ) : '';
		$city = isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '';
		$postcode = isset( $_POST['postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['postcode'] ) ) : '';
		$country = isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '';
		$notes = isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '';
		$status = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'active';

		if ( ! $name || ! $email ) {
			wp_redirect( add_query_arg( 'ays_notice', 'client_error', admin_url( 'admin.php?page=ays-dashboard&tab=clients' ) ) );
			exit;
		}

		// Check if new email already exists (different client)
		$email_exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}ays_clients WHERE email = %s AND status != 'deleted' AND id != %d", $email, $client_id ) );
		if ( $email_exists ) {
			wp_redirect( add_query_arg( 'ays_notice', 'email_exists', admin_url( 'admin.php?page=ays-dashboard&tab=clients' ) ) );
			exit;
		}

		$result = $wpdb->update(
			"{$wpdb->prefix}ays_clients",
			[
				'name' => $name,
				'email' => $email,
				'secondary_email' => $secondary_email,
				'phone' => $phone,
				'mobile' => $mobile,
				'address_line1' => $address_line1,
				'address_line2' => $address_line2,
				'city' => $city,
				'postcode' => $postcode,
				'country' => $country,
				'notes' => $notes,
				'status' => $status,
				'updated_at' => current_time( 'mysql' ),
			],
			[ 'id' => $client_id ],
			[ '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ],
			[ '%d' ]
		);

		if ( $result !== false ) {
			wp_redirect( add_query_arg( 'ays_notice', 'client_updated', admin_url( 'admin.php?page=ays-dashboard&tab=clients' ) ) );
		} else {
			wp_redirect( add_query_arg( 'ays_notice', 'client_error', admin_url( 'admin.php?page=ays-dashboard&tab=clients' ) ) );
		}
		exit;
	}

	/**
	 * Handle delete client via admin_post
	 *
	 * @return void
	 */
	public static function handle_delete_client() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		$client_id = isset( $_GET['client_id'] ) ? intval( $_GET['client_id'] ) : 0;
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'ays_delete_client_' . $client_id ) ) {
			wp_die( esc_html__( 'Security check failed', 'atyourservice' ) );
		}

		global $wpdb;
		// Soft delete: mark as deleted instead of permanent removal
		$result = $wpdb->update(
			"{$wpdb->prefix}ays_clients",
			[
				'status' => 'deleted',
				'deleted_at' => current_time( 'mysql' ),
			],
			[ 'id' => $client_id ],
			[ '%s', '%s' ],
			[ '%d' ]
		);

		if ( $result !== false ) {
			wp_redirect( add_query_arg( 'ays_notice', 'client_deleted', admin_url( 'admin.php?page=ays-dashboard&tab=clients' ) ) );
		} else {
			wp_redirect( add_query_arg( 'ays_notice', 'client_error', admin_url( 'admin.php?page=ays-dashboard&tab=clients' ) ) );
		}
		exit;
	}

	/**
	 * Get a single client by ID
	 *
	 * @param int $client_id The client ID.
	 * @return object|null The client object or null.
	 */
	protected static function get_client( $client_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ays_clients WHERE id = %d AND status != 'deleted'", $client_id ) );
	}

	/**
	 * Get total clients count
	 *
	 * @return int Total number of active clients.
	 */
	protected static function get_clients_count() {
		global $wpdb;
		return intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}ays_clients WHERE status != 'deleted'" ) );
	}
}
