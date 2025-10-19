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

// Attempt to load Stripe SDK if present locally. We do not ship vendor/ in git.
// Try Composer autoload first, then legacy vendor/stripe/init.php. If neither exists, we proceed without Stripe.
$__ays_stripe_loaded = false;
$__ays_vendor_autoload = defined('AYS_PLUGIN_PATH') ? AYS_PLUGIN_PATH . 'vendor/autoload.php' : __DIR__ . '/../../../vendor/autoload.php';
$__ays_stripe_init    = defined('AYS_PLUGIN_PATH') ? AYS_PLUGIN_PATH . 'vendor/stripe/init.php' : __DIR__ . '/../../../vendor/stripe/init.php';
if ( file_exists( $__ays_vendor_autoload ) ) {
	require_once $__ays_vendor_autoload;
	$__ays_stripe_loaded = class_exists( '\\Stripe\\Stripe' );
} elseif ( file_exists( $__ays_stripe_init ) ) {
	require_once $__ays_stripe_init;
	$__ays_stripe_loaded = class_exists( '\\Stripe\\Stripe' );
}

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

		// Show a friendly warning if Stripe SDK isn't available locally
		global $__ays_stripe_loaded;
		if ( ! $__ays_stripe_loaded ) {
			echo '<div class="notice notice-warning" style="margin:12px 0;">'
				. '<p><strong>' . esc_html__( 'Stripe SDK not found locally.', 'atyourservice' ) . '</strong> '
				. esc_html__( 'Payments can still be recorded manually. To enable Stripe processing, install dependencies:', 'atyourservice' )
				. '</p>'
				. '<ol style="margin-left:18px;">'
				. '<li><code>composer install</code> ' . esc_html__( 'in the plugin root', 'atyourservice' ) . '</li>'
				. '<li>' . esc_html__( 'Ensure vendor/ is present on the server (it is .gitignored in source control).', 'atyourservice' ) . '</li>'
				. '</ol>'
				. '</div>';
		}

		// Get edit ID if present
		$edit_id = isset( $_GET['edit_payment'] ) ? intval( $_GET['edit_payment'] ) : 0;
		$edit_payment = $edit_id ? self::get_payment( $edit_id ) : null;

		?>
		<!-- Stripe Configuration Section -->
		<details class="ays-details">
			<summary>
				🔧 <?php esc_html_e( 'Stripe Configuration', 'atyourservice' ); ?>
				<span class="ays-badge <?php echo empty(get_option('ays_stripe_secret_key')) ? 'new' : ''; ?>">
					<?php echo empty(get_option('ays_stripe_secret_key')) ? 'Not Configured' : 'Configured'; ?>
				</span>
			</summary>
			<div>
				<div class="left-column">
					<form method="post" action="options.php">
						<?php settings_fields('ays_payments_settings'); ?>
						<div class="ays-form-row">
							<label for="ays_stripe_publishable_key"><?php esc_html_e( 'Stripe Publishable Key', 'atyourservice' ); ?></label>
							<input type="text" id="ays_stripe_publishable_key" name="ays_stripe_publishable_key"
								   value="<?php echo esc_attr(get_option('ays_stripe_publishable_key', '')); ?>" />
							<p class="description"><?php esc_html_e( 'Your Stripe publishable key (starts with pk_)', 'atyourservice' ); ?></p>
						</div>
						<div class="ays-form-row">
							<label for="ays_stripe_secret_key"><?php esc_html_e( 'Stripe Secret Key', 'atyourservice' ); ?></label>
							<input type="password" id="ays_stripe_secret_key" name="ays_stripe_secret_key"
								   value="<?php echo esc_attr(get_option('ays_stripe_secret_key', '')); ?>" />
							<p class="description"><?php esc_html_e( 'Your Stripe secret key (starts with sk_)', 'atyourservice' ); ?></p>
						</div>
						<?php submit_button(esc_html__('Save Settings', 'atyourservice')); ?>
					</form>
				</div>
				<div class="right-column">
					<h4><?php esc_html_e( '📌 Setup Instructions', 'atyourservice' ); ?></h4>
					<ol>
						<li><?php esc_html_e( 'Log into your', 'atyourservice' ); ?> <a href="https://dashboard.stripe.com/" target="_blank"><?php esc_html_e( 'Stripe Dashboard', 'atyourservice' ); ?></a></li>
						<li><?php esc_html_e( 'Navigate to API Keys in the Developers section', 'atyourservice' ); ?></li>
						<li><?php esc_html_e( 'Copy your Publishable key and Secret key', 'atyourservice' ); ?></li>
						<li><?php esc_html_e( 'Paste them above and save', 'atyourservice' ); ?></li>
					</ol>
					<p><strong><?php esc_html_e( 'Note:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Use test keys for development.', 'atyourservice' ); ?></p>
				</div>
			</div>
		</details>

		<!-- Process Payment Section -->
		<details class="ays-details">
			<summary>
				💳 <?php esc_html_e( 'Process Payment', 'atyourservice' ); ?>
			</summary>
			<div>
				<div class="left-column">
					<p><?php esc_html_e( 'Select an invoice to process payment:', 'atyourservice' ); ?></p>
					<select id="invoice-select" class="ays-form-row">
						<option value=""><?php esc_html_e( 'Choose an invoice...', 'atyourservice' ); ?></option>
						<?php
						global $wpdb;
						$invoices = $wpdb->get_results("SELECT id, invoice_number FROM {$wpdb->prefix}ays_invoices WHERE status != 'paid' ORDER BY created_at DESC");
						foreach ($invoices as $invoice) {
							echo '<option value="' . esc_attr($invoice->id) . '">' . esc_attr($invoice->invoice_number) . '</option>';
						}
						?>
					</select>
					<div id="payment-form" style="display: none;">
						<div id="card-element"><!-- Stripe Card Element --></div>
						<button id="pay-button" class="button button-primary"><?php esc_html_e( 'Pay Invoice', 'atyourservice' ); ?></button>
					</div>
				</div>
				<div class="right-column">
					<h4><?php esc_html_e( '📌 Payment Methods', 'atyourservice' ); ?></h4>
					<ul>
						<li><?php esc_html_e( 'Credit/Debit Cards', 'atyourservice' ); ?></li>
						<li><?php esc_html_e( 'Google Pay', 'atyourservice' ); ?></li>
						<li><?php esc_html_e( 'Bank Transfer (ACH)', 'atyourservice' ); ?></li>
					</ul>
					<p><?php esc_html_e( 'All payments are processed securely through Stripe.', 'atyourservice' ); ?></p>
				</div>
			</div>
		</details>
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

	/**
	 * Handle AJAX request to create Stripe payment intent
	 */
	public static function create_payment_intent() {
		check_ajax_referer('ays_stripe_payment', 'nonce');

		if (!current_user_can('manage_options')) {
			wp_send_json_error('Unauthorized');
		}

		$invoice_id = intval($_POST['invoice_id']);
		if (!$invoice_id) {
			wp_send_json_error('Invalid invoice ID');
		}

		global $wpdb;
		$invoice = $wpdb->get_row($wpdb->prepare(
			"SELECT i.*, c.email FROM {$wpdb->prefix}ays_invoices i 
			LEFT JOIN {$wpdb->prefix}ays_clients c ON i.client_id = c.id 
			WHERE i.id = %d",
			$invoice_id
		));

		if (!$invoice) {
			wp_send_json_error('Invoice not found');
		}

		$stripe_secret_key = get_option('ays_stripe_secret_key');
		if (empty($stripe_secret_key)) {
			wp_send_json_error('Stripe not configured');
		}

		\Stripe\Stripe::setApiKey($stripe_secret_key);

		try {
			$payment_intent = \Stripe\PaymentIntent::create([
				'amount' => intval($invoice->total * 100), // Amount in cents
				'currency' => 'usd', // Assuming USD, can be made configurable
				'metadata' => [
					'invoice_id' => $invoice_id,
					'invoice_number' => $invoice->invoice_number,
				],
				'receipt_email' => $invoice->email,
			]);

			wp_send_json_success([
				'client_secret' => $payment_intent->client_secret,
			]);
		} catch (\Stripe\Exception\ApiErrorException $e) {
			wp_send_json_error($e->getMessage());
		}
	}

	/**
	 * Enqueue Stripe scripts and handle payment processing
	 */
	public static function enqueue_scripts() {
		$stripe_publishable_key = get_option('ays_stripe_publishable_key', '');
		if (!empty($stripe_publishable_key)) {
			wp_enqueue_script('stripe-js', 'https://js.stripe.com/v3/', [], '3', true);
			wp_add_inline_script('stripe-js', "
				var stripe = Stripe('$stripe_publishable_key');
				var elements = stripe.elements();
				var cardElement = elements.create('card');
				var invoiceId = null;

				document.getElementById('invoice-select').addEventListener('change', function() {
					invoiceId = this.value;
					if (invoiceId) {
						document.getElementById('payment-form').style.display = 'block';
						if (!document.getElementById('card-element').hasChildNodes()) {
							cardElement.mount('#card-element');
						}
					} else {
						document.getElementById('payment-form').style.display = 'none';
					}
				});

				document.getElementById('pay-button').addEventListener('click', function(e) {
					e.preventDefault();
					if (!invoiceId) return;

					fetch(ajaxurl, {
						method: 'POST',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded',
						},
						body: 'action=ays_create_payment_intent&invoice_id=' + invoiceId + '&nonce=' + ays_ajax.nonce
					})
					.then(response => response.json())
					.then(data => {
						if (data.success) {
							return stripe.confirmCardPayment(data.data.client_secret, {
								payment_method: {
									card: cardElement,
								}
							});
						} else {
							throw new Error(data.data);
						}
					})
					.then(result => {
						if (result.error) {
							alert('Payment failed: ' + result.error.message);
						} else {
							alert('Payment succeeded!');
							location.reload();
						}
					})
					.catch(error => {
						alert('Error: ' + error.message);
					});
				});
			");

			wp_localize_script('stripe-js', 'ays_ajax', [
				'nonce' => wp_create_nonce('ays_stripe_payment')
			]);
		}
	}
}
