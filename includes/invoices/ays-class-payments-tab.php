<?php
/**
 * AYS Payments Tab - Payment Tracking
 *
 * Tracks payments received for invoices.
 * Supported payment methods: Stripe, Google Pay, Bank Transfer
 *
 * Database Table: wp_ays_payments
 * Columns: id, invoice_id, amount, method, status, transaction_id, notes, created_at, updated_at
 *
 * @since 1.0
 */

defined( 'ABSPATH' ) || exit;

class AYS_Payments_Tab {

	const PAYMENT_METHODS = [
		'stripe'        => 'Stripe',
		'google_pay'    => 'Google Pay',
		'bank_transfer' => 'Bank Transfer',
	];

	/**
	 * Render the Payments tab content
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
		$edit_id = isset( $_GET['edit_payment'] ) ? intval( $_GET['edit_payment'] ) : 0;
		$edit_payment = $edit_id ? self::get_payment( $edit_id ) : null;

		?>
		<!-- Payments Log Section -->
		<details class="ays-details" open>
			<summary>
				💳 <?php esc_html_e( 'Payments Log', 'atyourservice' ); ?>
				<span class="ays-badge"><?php echo esc_html( self::get_payments_count() . ' payments' ); ?></span>
			</summary>
			<div>
				<div class="left-column">
					<?php self::render_payments_table(); ?>
				</div>
				<div class="right-column">
					<h4><?php esc_html_e( '📌 Quick Tips', 'atyourservice' ); ?></h4>
					<ul>
						<li><?php esc_html_e( 'Track all payment methods', 'atyourservice' ); ?></li>
						<li><?php esc_html_e( 'Stripe, Google Pay, Bank Transfer', 'atyourservice' ); ?></li>
						<li><?php esc_html_e( 'Record transaction IDs', 'atyourservice' ); ?></li>
						<li><?php esc_html_e( 'Mark payments as received', 'atyourservice' ); ?></li>
						<li><?php esc_html_e( 'Add notes for reference', 'atyourservice' ); ?></li>
					</ul>
				</div>
			</div>
		</details>

		<!-- Record Payment Form -->
		<details class="ays-details <?php echo $edit_id ? 'open' : ''; ?>">
			<summary>
				<?php if ( $edit_id ) : ?>
					✏️ <?php esc_html_e( 'Edit Payment', 'atyourservice' ); ?>
				<?php else : ?>
					➕ <?php esc_html_e( 'Record Payment', 'atyourservice' ); ?>
				<?php endif; ?>
			</summary>
			<div>
				<div class="left-column">
					<?php self::render_payment_form( $edit_payment ); ?>
				</div>
				<div class="right-column">
					<h4><?php esc_html_e( '📌 Form Help', 'atyourservice' ); ?></h4>
					<ul>
						<li><strong><?php esc_html_e( 'Invoice:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Select the invoice being paid', 'atyourservice' ); ?></li>
						<li><strong><?php esc_html_e( 'Amount:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Payment amount received', 'atyourservice' ); ?></li>
						<li><strong><?php esc_html_e( 'Method:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Stripe, Google Pay, or Bank Transfer', 'atyourservice' ); ?></li>
						<li><strong><?php esc_html_e( 'Transaction ID:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Reference number from payment processor', 'atyourservice' ); ?></li>
						<li><strong><?php esc_html_e( 'Status:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Pending or Confirmed', 'atyourservice' ); ?></li>
					</ul>
				</div>
			</div>
		</details>
		<?php
	}

	/**
	 * Render the payments log table
	 *
	 * @return void
	 */
	protected static function render_payments_table() {
		global $wpdb;
		$payments = $wpdb->get_results(
			"SELECT p.*, i.invoice_number, c.name as client_name 
			FROM {$wpdb->prefix}ays_payments p 
			LEFT JOIN {$wpdb->prefix}ays_invoices i ON p.invoice_id = i.id 
			LEFT JOIN {$wpdb->prefix}ays_clients c ON i.client_id = c.id 
			ORDER BY p.created_at DESC LIMIT 100"
		);

		if ( empty( $payments ) ) {
			echo '<p>' . esc_html__( 'No payments recorded yet.', 'atyourservice' ) . '</p>';
			return;
		}

		?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Date', 'atyourservice' ); ?></th>
					<th><?php esc_html_e( 'Invoice', 'atyourservice' ); ?></th>
					<th><?php esc_html_e( 'Client', 'atyourservice' ); ?></th>
					<th style="text-align: right;"><?php esc_html_e( 'Amount', 'atyourservice' ); ?></th>
					<th><?php esc_html_e( 'Method', 'atyourservice' ); ?></th>
					<th style="text-align: center;"><?php esc_html_e( 'Status', 'atyourservice' ); ?></th>
					<th style="text-align: center;"><?php esc_html_e( 'Actions', 'atyourservice' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $payments as $payment ) : ?>
					<tr>
						<td><?php echo esc_html( date_i18n( 'M d, Y g:i A', strtotime( $payment->created_at ) ) ); ?></td>
						<td><strong><?php echo esc_html( $payment->invoice_number ?: '—' ); ?></strong></td>
						<td><?php echo esc_html( $payment->client_name ?: '—' ); ?></td>
						<td style="text-align: right;"><code>$<?php echo esc_html( number_format( $payment->amount, 2 ) ); ?></code></td>
						<td><?php echo esc_html( self::get_method_label( $payment->method ) ); ?></td>
						<td style="text-align: center;">
							<?php self::render_status_badge( $payment->status ); ?>
						</td>
						<td style="text-align: center;">
							<a href="<?php echo esc_url( add_query_arg( 'edit_payment', $payment->id ) ); ?>" class="button button-small">
								<?php esc_html_e( 'Edit', 'atyourservice' ); ?>
							</a>
							<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'action' => 'ays_delete_payment', 'payment_id' => $payment->id ] ), 'ays_delete_payment_' . $payment->id ) ); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php esc_attr_e( 'Delete this payment?', 'atyourservice' ); ?>')">
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
	 * Render the payment form
	 *
	 * @param object|null $payment The payment to edit, or null for new payment.
	 * @return void
	 */
	protected static function render_payment_form( $payment = null ) {
		global $wpdb;

		$invoices = $wpdb->get_results(
			"SELECT i.id, i.invoice_number, i.total, c.name as client_name 
			FROM {$wpdb->prefix}ays_invoices i 
			LEFT JOIN {$wpdb->prefix}ays_clients c ON i.client_id = c.id 
			WHERE i.status != 'cancelled' 
			ORDER BY i.issue_date DESC"
		);

		$nonce_action = $payment ? 'ays_update_payment_' . $payment->id : 'ays_add_payment';
		$action = $payment ? 'ays_update_payment' : 'ays_add_payment';

		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ays-payment-form">
			<?php wp_nonce_field( $nonce_action, 'ays_payment_nonce' ); ?>
			<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>">
			<?php if ( $payment ) : ?>
				<input type="hidden" name="payment_id" value="<?php echo esc_attr( $payment->id ); ?>">
			<?php endif; ?>

			<table class="form-table">
				<!-- Invoice -->
				<tr>
					<th scope="row">
						<label for="invoice_id"><?php esc_html_e( 'Invoice', 'atyourservice' ); ?> <span style="color: red;">*</span></label>
					</th>
					<td>
						<select id="invoice_id" name="invoice_id" required class="regular-text">
							<option value=""><?php esc_html_e( '— Select an invoice —', 'atyourservice' ); ?></option>
							<?php foreach ( $invoices as $invoice ) : ?>
								<option value="<?php echo esc_attr( $invoice->id ); ?>" <?php selected( $payment && $payment->invoice_id === intval( $invoice->id ) ); ?>>
									<?php echo esc_html( $invoice->invoice_number . ' - ' . $invoice->client_name . ' ($' . number_format( $invoice->total, 2 ) . ')' ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>

				<!-- Amount -->
				<tr>
					<th scope="row">
						<label for="amount"><?php esc_html_e( 'Amount', 'atyourservice' ); ?> <span style="color: red;">*</span></label>
					</th>
					<td>
						<input 
							type="number" 
							id="amount" 
							name="amount" 
							value="<?php echo $payment ? esc_attr( number_format( $payment->amount, 2, '.', '' ) ) : ''; ?>"
							placeholder="0.00"
							step="0.01"
							min="0"
							required
							class="regular-text"
							style="width: 150px;"
						>
					</td>
				</tr>

				<!-- Payment Method -->
				<tr>
					<th scope="row">
						<label for="method"><?php esc_html_e( 'Payment Method', 'atyourservice' ); ?> <span style="color: red;">*</span></label>
					</th>
					<td>
						<select id="method" name="method" required class="regular-text">
							<option value=""><?php esc_html_e( '— Select method —', 'atyourservice' ); ?></option>
							<?php foreach ( self::PAYMENT_METHODS as $key => $label ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $payment && $payment->method === $key ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>

				<!-- Transaction ID -->
				<tr>
					<th scope="row">
						<label for="transaction_id"><?php esc_html_e( 'Transaction ID', 'atyourservice' ); ?></label>
					</th>
					<td>
						<input 
							type="text" 
							id="transaction_id" 
							name="transaction_id" 
							value="<?php echo $payment ? esc_attr( $payment->transaction_id ) : ''; ?>"
							placeholder="<?php esc_attr_e( 'e.g., ch_1234567890 or bank ref code', 'atyourservice' ); ?>"
							class="regular-text"
						>
						<p class="description"><?php esc_html_e( 'Reference from payment processor', 'atyourservice' ); ?></p>
					</td>
				</tr>

				<!-- Status -->
				<tr>
					<th scope="row">
						<label for="status"><?php esc_html_e( 'Status', 'atyourservice' ); ?></label>
					</th>
					<td>
						<select id="status" name="status" class="regular-text">
							<option value="pending" <?php selected( ! $payment || $payment->status === 'pending' ); ?>>
								<?php esc_html_e( '⏳ Pending', 'atyourservice' ); ?>
							</option>
							<option value="confirmed" <?php selected( $payment && $payment->status === 'confirmed' ); ?>>
								<?php esc_html_e( '✅ Confirmed', 'atyourservice' ); ?>
							</option>
						</select>
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
							placeholder="<?php esc_attr_e( 'Any notes about this payment', 'atyourservice' ); ?>"
							class="regular-text"
							rows="3"
						><?php echo $payment ? esc_textarea( $payment->notes ) : ''; ?></textarea>
					</td>
				</tr>
			</table>

			<p class="submit">
				<?php submit_button( $payment ? __( 'Update Payment', 'atyourservice' ) : __( 'Record Payment', 'atyourservice' ), 'primary', 'submit', false ); ?>
				<?php if ( $payment ) : ?>
					<a href="<?php echo esc_url( remove_query_arg( 'edit_payment' ) ); ?>" class="button" style="margin-left: 10px;">
						<?php esc_html_e( 'Cancel', 'atyourservice' ); ?>
					</a>
				<?php endif; ?>
			</p>
		</form>
		<?php
	}

	/**
	 * Get payment method label
	 *
	 * @param string $method The payment method key.
	 * @return string The method label.
	 */
	protected static function get_method_label( $method ) {
		return self::PAYMENT_METHODS[ $method ] ?? $method;
	}

	/**
	 * Render status badge
	 *
	 * @param string $status The payment status.
	 * @return void
	 */
	protected static function render_status_badge( $status ) {
		$badges = [
			'pending'   => '⏳ Pending',
			'confirmed' => '✅ Confirmed',
		];

		echo esc_html( $badges[ $status ] ?? $status );
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
				case 'payment_added':
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '✓ Payment recorded successfully!', 'atyourservice' ) . '</p></div>';
					break;
				case 'payment_updated':
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '✓ Payment updated successfully!', 'atyourservice' ) . '</p></div>';
					break;
				case 'payment_deleted':
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '✓ Payment deleted successfully!', 'atyourservice' ) . '</p></div>';
					break;
				case 'payment_error':
					echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( '✗ An error occurred. Please try again.', 'atyourservice' ) . '</p></div>';
					break;
			}
		}
	}

	/**
	 * Get a single payment by ID
	 *
	 * @param int $payment_id The payment ID.
	 * @return object|null The payment object or null.
	 */
	protected static function get_payment( $payment_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ays_payments WHERE id = %d", $payment_id ) );
	}

	/**
	 * Get total payments count
	 *
	 * @return int Total number of payments.
	 */
	protected static function get_payments_count() {
		global $wpdb;
		return intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}ays_payments" ) );
	}

	/**
	 * Handle add payment via admin_post
	 *
	 * @return void
	 */
	public static function handle_add_payment() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		if ( ! isset( $_POST['ays_payment_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ays_payment_nonce'] ) ), 'ays_add_payment' ) ) {
			wp_die( esc_html__( 'Security check failed', 'atyourservice' ) );
		}

		global $wpdb;

		$invoice_id = isset( $_POST['invoice_id'] ) ? intval( $_POST['invoice_id'] ) : 0;
		$amount = isset( $_POST['amount'] ) ? floatval( $_POST['amount'] ) : 0;
		$method = isset( $_POST['method'] ) ? sanitize_text_field( wp_unslash( $_POST['method'] ) ) : '';
		$transaction_id = isset( $_POST['transaction_id'] ) ? sanitize_text_field( wp_unslash( $_POST['transaction_id'] ) ) : '';
		$status = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'pending';
		$notes = isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '';

		if ( ! $invoice_id || ! $amount || ! $method ) {
			wp_redirect( add_query_arg( 'ays_notice', 'payment_error', admin_url( 'admin.php?page=ays-dashboard&tab=payments' ) ) );
			exit;
		}

		$result = $wpdb->insert(
			"{$wpdb->prefix}ays_payments",
			[
				'invoice_id' => $invoice_id,
				'amount' => $amount,
				'method' => $method,
				'transaction_id' => $transaction_id,
				'status' => $status,
				'notes' => $notes,
				'created_at' => current_time( 'mysql' ),
				'updated_at' => current_time( 'mysql' ),
			],
			[ '%d', '%f', '%s', '%s', '%s', '%s', '%s', '%s' ]
		);

		if ( $result ) {
			wp_redirect( add_query_arg( 'ays_notice', 'payment_added', admin_url( 'admin.php?page=ays-dashboard&tab=payments' ) ) );
		} else {
			wp_redirect( add_query_arg( 'ays_notice', 'payment_error', admin_url( 'admin.php?page=ays-dashboard&tab=payments' ) ) );
		}
		exit;
	}

	/**
	 * Handle update payment via admin_post
	 *
	 * @return void
	 */
	public static function handle_update_payment() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		$payment_id = isset( $_POST['payment_id'] ) ? intval( $_POST['payment_id'] ) : 0;

		if ( ! isset( $_POST['ays_payment_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ays_payment_nonce'] ) ), 'ays_update_payment_' . $payment_id ) ) {
			wp_die( esc_html__( 'Security check failed', 'atyourservice' ) );
		}

		global $wpdb;

		$invoice_id = isset( $_POST['invoice_id'] ) ? intval( $_POST['invoice_id'] ) : 0;
		$amount = isset( $_POST['amount'] ) ? floatval( $_POST['amount'] ) : 0;
		$method = isset( $_POST['method'] ) ? sanitize_text_field( wp_unslash( $_POST['method'] ) ) : '';
		$transaction_id = isset( $_POST['transaction_id'] ) ? sanitize_text_field( wp_unslash( $_POST['transaction_id'] ) ) : '';
		$status = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'pending';
		$notes = isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '';

		if ( ! $invoice_id || ! $amount || ! $method ) {
			wp_redirect( add_query_arg( 'ays_notice', 'payment_error', admin_url( 'admin.php?page=ays-dashboard&tab=payments' ) ) );
			exit;
		}

		$result = $wpdb->update(
			"{$wpdb->prefix}ays_payments",
			[
				'invoice_id' => $invoice_id,
				'amount' => $amount,
				'method' => $method,
				'transaction_id' => $transaction_id,
				'status' => $status,
				'notes' => $notes,
				'updated_at' => current_time( 'mysql' ),
			],
			[ 'id' => $payment_id ],
			[ '%d', '%f', '%s', '%s', '%s', '%s', '%s' ],
			[ '%d' ]
		);

		if ( $result !== false ) {
			wp_redirect( add_query_arg( 'ays_notice', 'payment_updated', admin_url( 'admin.php?page=ays-dashboard&tab=payments' ) ) );
		} else {
			wp_redirect( add_query_arg( 'ays_notice', 'payment_error', admin_url( 'admin.php?page=ays-dashboard&tab=payments' ) ) );
		}
		exit;
	}

	/**
	 * Handle delete payment via admin_post
	 *
	 * @return void
	 */
	public static function handle_delete_payment() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		$payment_id = isset( $_GET['payment_id'] ) ? intval( $_GET['payment_id'] ) : 0;
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'ays_delete_payment_' . $payment_id ) ) {
			wp_die( esc_html__( 'Security check failed', 'atyourservice' ) );
		}

		global $wpdb;
		$result = $wpdb->delete( "{$wpdb->prefix}ays_payments", [ 'id' => $payment_id ], [ '%d' ] );

		if ( $result ) {
			wp_redirect( add_query_arg( 'ays_notice', 'payment_deleted', admin_url( 'admin.php?page=ays-dashboard&tab=payments' ) ) );
		} else {
			wp_redirect( add_query_arg( 'ays_notice', 'payment_error', admin_url( 'admin.php?page=ays-dashboard&tab=payments' ) ) );
		}
		exit;
	}
}
