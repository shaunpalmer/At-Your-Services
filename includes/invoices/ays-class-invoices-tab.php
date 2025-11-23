<?php
/**
 * AYS Invoices Tab - Invoice Management
 *
 * Manages the Invoices for the invoicing system.
 * Provides interface for creating, editing, and viewing invoices.
 *
 * Database Table: wp_ays_invoices
 * Columns: id, invoice_number, client_id, issue_date, due_date, subtotal, tax_amount, total, status, notes, created_at, updated_at
 *
 * @since 1.0
 */

defined( 'ABSPATH' ) || exit;

class AYS_Invoices_Tab {

	/**
	 * Invoice status constants with labels and icons
	 * Statuses: draft, sent, viewed, paid, void
	 */
	const INVOICE_STATUSES = [
		'draft'  => [ 'label' => 'Draft', 'icon' => '📝', 'color' => '#999' ],
		'sent'   => [ 'label' => 'Sent', 'icon' => '📧', 'color' => '#2196F3' ],
		'viewed' => [ 'label' => 'Viewed', 'icon' => '👁️', 'color' => '#4CAF50' ],
		'paid'   => [ 'label' => 'Paid', 'icon' => '✅', 'color' => '#27AE60' ],
		'void'   => [ 'label' => 'Void', 'icon' => '❌', 'color' => '#FF5252' ],
	];

	/**
	 * Render the Invoices tab content
	 *
	 * @return void
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		// Display notices
		self::display_notices();

		// Get edit ID/hash if present
		$edit_id = isset( $_GET['edit_invoice'] ) ? intval( $_GET['edit_invoice'] ) : 0;
		$inv_hash = isset( $_GET['inv_hash'] ) ? sanitize_text_field( wp_unslash( $_GET['inv_hash'] ) ) : '';
		$edit_invoice = $edit_id ? self::get_invoice( $edit_id ) : null;

		// Robust fallback: if ID lookup failed, try by hash (from redirect) or fallback to most recent
		if ( $edit_id && ! $edit_invoice ) {
			if ( $inv_hash ) {
				$edit_invoice = self::get_invoice_by_hash( $inv_hash );
				if ( $edit_invoice ) {
					// Normalize URL to the real ID
					wp_safe_redirect( add_query_arg( [ 'edit_invoice' => intval( $edit_invoice->id ) ], remove_query_arg( 'inv_hash' ) ) );
					exit;
				}
			}
		}

		if ( $edit_id && ! $edit_invoice ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Invoice not found.', 'atyourservice' ) . '</p></div>';
			// Continue to list + quick create so user isn’t blocked
		}

		if ( $edit_invoice ) {
			// Show editor
			self::render_invoice_editor( $edit_invoice );
		} else {
			// Show list
			self::render_invoices_list();
			// Show quick create
			self::render_quick_create_form();
		}
	}

	/**
	 * Render the invoices list
	 *
	 * @return void
	 */
	protected static function render_invoices_list() {
		global $wpdb;
		// Optional filter by service type
		$filter_service = isset( $_GET['svc'] ) ? max( 0, intval( $_GET['svc'] ) ) : 0;

		// Fetch service types for filter dropdown
		$service_types = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}ays_service_types WHERE status = 'active' ORDER BY sort_order, name" );

		if ( $filter_service ) {
			$invoices = $wpdb->get_results( $wpdb->prepare(
				"SELECT i.*, c.name as client_name
				 FROM {$wpdb->prefix}ays_invoices i
				 LEFT JOIN {$wpdb->prefix}ays_clients c ON i.client_id = c.id
				 INNER JOIN {$wpdb->prefix}ays_invoice_services isv ON isv.invoice_id = i.id AND isv.service_type_id = %d
				 ORDER BY i.issue_date DESC LIMIT 50",
				$filter_service
			) );
		} else {
			$invoices = $wpdb->get_results( "SELECT i.*, c.name as client_name FROM {$wpdb->prefix}ays_invoices i LEFT JOIN {$wpdb->prefix}ays_clients c ON i.client_id = c.id ORDER BY i.issue_date DESC LIMIT 50" );
		}

		if ( empty( $invoices ) ) {
			echo '<p>' . esc_html__( 'No invoices yet. Create your first invoice below!', 'atyourservice' ) . '</p>';
			return;
		}

		?>
		<details class="ays-details" open>
			<summary>
				📋 <?php esc_html_e( 'Invoices List', 'atyourservice' ); ?>
				<span class="ays-badge"><?php echo esc_html( count( $invoices ) . ' invoices' ); ?></span>
			</summary>
			<div>
				<div class="left-column">
					<style>
						.ays-chip { display:inline-block; padding:2px 8px; font-size:11px; border-radius:999px; background:#eef2ff; color:#3730a3; border:1px solid #c7d2fe; margin:2px 6px 2px 0; }
						.ays-chip .dot { display:inline-block; width:6px; height:6px; background:#6366f1; border-radius:999px; margin-right:6px; vertical-align:middle; }
					</style>
						<form method="get" action="" style="margin: 0 0 12px; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
							<input type="hidden" name="page" value="ays-dashboard" />
							<input type="hidden" name="tab" value="invoices" />
						<label for="ays-filter-svc" style="font-weight:600;">Filter by Service:</label>
						<select id="ays-filter-svc" name="svc">
							<option value="0">— All services —</option>
							<?php foreach ( (array) $service_types as $svc ) : ?>
								<option value="<?php echo esc_attr( $svc->id ); ?>" <?php selected( (int) $filter_service === (int) $svc->id ); ?>><?php echo esc_html( $svc->name ); ?></option>
							<?php endforeach; ?>
						</select>
						<button class="button">Apply</button>
						<?php if ( $filter_service ) : ?>
								<a class="button button-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=ays-dashboard&tab=invoices' ) ); ?>">Reset</a>
						<?php endif; ?>
					</form>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Invoice #', 'atyourservice' ); ?></th>
								<th><?php esc_html_e( 'Client', 'atyourservice' ); ?></th>
								<th><?php esc_html_e( 'Date', 'atyourservice' ); ?></th>
								<th><?php esc_html_e( 'Due Date', 'atyourservice' ); ?></th>
								<th style="text-align: right;"><?php esc_html_e( 'Total', 'atyourservice' ); ?></th>
								<th style="text-align: center;"><?php esc_html_e( 'Status', 'atyourservice' ); ?></th>
								<th style="text-align: left; width: 25%;"><?php esc_html_e( 'Services', 'atyourservice' ); ?></th>
								<th style="text-align: center;"><?php esc_html_e( 'Actions', 'atyourservice' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $invoices as $invoice ) : 
								$invoice_number = !empty($invoice->invoice_number) ? $invoice->invoice_number : (!empty($invoice->inv_number) ? $invoice->inv_number : ('INV-' . (int) $invoice->id));
							?>
								<tr>
									<td><strong><?php echo esc_html( $invoice_number ); ?></strong></td>
									<td><?php echo esc_html( $invoice->client_name ?: '—' ); ?></td>
									<td><?php echo esc_html( date_i18n( 'M d, Y', strtotime( $invoice->issue_date ) ) ); ?></td>
									<td><?php echo esc_html( date_i18n( 'M d, Y', strtotime( $invoice->due_date ) ) ); ?></td>
									<td style="text-align: right;"><code>$<?php echo esc_html( number_format( $invoice->total, 2 ) ); ?></code></td>
									<td style="text-align: center;">
										<?php self::render_status_badge( $invoice->status ); ?>
									</td>
									<td>
										<?php self::render_service_type_chips( $invoice->id ); ?>
									</td>
									<td style="text-align: center;">
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=ays-invoice-preview&invoice_id=' . intval( $invoice->id ) ) ); ?>" class="button button-small">
											<?php esc_html_e( 'Preview', 'atyourservice' ); ?>
										</a>
										<a href="<?php echo esc_url( add_query_arg( 'edit_invoice', $invoice->id ) ); ?>" class="button button-small">
											<?php esc_html_e( 'Edit', 'atyourservice' ); ?>
										</a>
										<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'action' => 'ays_delete_invoice', 'invoice_id' => $invoice->id ] ), 'ays_delete_invoice_' . $invoice->id ) ); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php esc_attr_e( 'Delete this invoice?', 'atyourservice' ); ?>')">
											<?php esc_html_e( 'Delete', 'atyourservice' ); ?>
										</a>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<div class="right-column">
					<h4><?php esc_html_e( '📌 Quick Tips', 'atyourservice' ); ?></h4>
					<ul>
						<li><?php esc_html_e( 'Create invoices for your clients', 'atyourservice' ); ?></li>
						<li><?php esc_html_e( 'Add line items from your catalog', 'atyourservice' ); ?></li>
						<li><?php esc_html_e( 'Track payment status', 'atyourservice' ); ?></li>
						<li><?php esc_html_e( 'Edit or delete anytime', 'atyourservice' ); ?></li>
					</ul>
					<?php 
					// Show bank transfer block if enabled so admins see what customers will see
					if ( class_exists('AYS_Company_Profile') ) {
						$bt = AYS_Company_Profile::get_bank_transfer_html();
						if ( $bt ) {
							echo '<div style="margin-top:12px">' . $bt . '</div>';
						}
					}
					?>
				</div>
			</div>
		</details>
		<?php
	}

	/**
	 * Render quick create form
	 *
	 * @return void
	 */
	protected static function render_quick_create_form() {
		global $wpdb;
		$clients = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}ays_clients WHERE status = 'active' ORDER BY name" );

		?>
		<details class="ays-details" open>
			<summary>
				➕ <?php esc_html_e( 'Create New Invoice', 'atyourservice' ); ?>
			</summary>
			<div>
				<div class="left-column">
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<?php wp_nonce_field( 'ays_create_invoice', 'ays_invoice_nonce' ); ?>
						<input type="hidden" name="action" value="ays_create_invoice">

						<table class="form-table">
							<tr>
								<th scope="row">
									<label for="client_id"><?php esc_html_e( 'Client', 'atyourservice' ); ?> <span style="color: red;">*</span></label>
								</th>
								<td>
									<select id="client_id" name="client_id" required class="regular-text">
										<option value=""><?php esc_html_e( '— Select a client —', 'atyourservice' ); ?></option>
										<?php foreach ( $clients as $client ) : ?>
											<option value="<?php echo esc_attr( $client->id ); ?>">
												<?php echo esc_html( $client->name ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="issue_date"><?php esc_html_e( 'Issue Date', 'atyourservice' ); ?></label>
								</th>
								<td>
									<input type="date" id="issue_date" name="issue_date" value="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>" class="regular-text" style="width: 200px;" />
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="due_date"><?php esc_html_e( 'Due Date', 'atyourservice' ); ?></label>
								</th>
								<td>
									<input type="date" id="due_date" name="due_date" value="<?php echo esc_attr( date( 'Y-m-d', strtotime( '+30 days' ) ) ); ?>" class="regular-text" style="width: 200px;" />
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="notes"><?php esc_html_e( 'Notes', 'atyourservice' ); ?></label>
								</th>
								<td>
									<textarea 
										id="notes" 
										name="notes" 
										placeholder="<?php esc_attr_e( 'Any special notes for this invoice', 'atyourservice' ); ?>"
										class="regular-text"
										rows="3"
									></textarea>
								</td>
							</tr>
						</table>

						<p class="submit">
							<?php submit_button( __( 'Create Invoice', 'atyourservice' ), 'primary', 'submit', false ); ?>
						</p>
					</form>
				</div>
				<div class="right-column">
					<h4><?php esc_html_e( '📌 Form Help', 'atyourservice' ); ?></h4>
					<ul>
						<li><strong><?php esc_html_e( 'Client:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Select from your active clients list', 'atyourservice' ); ?></li>
						<li><strong><?php esc_html_e( 'Dates:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Issue date (today) and due date (30 days by default)', 'atyourservice' ); ?></li>
						<li><strong><?php esc_html_e( 'Notes:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Optional invoice notes or payment terms', 'atyourservice' ); ?></li>
					</ul>
				</div>
			</div>
		</details>
		<?php
	}

	/**
	 * Render invoice editor
	 *
	 * @param object $invoice The invoice to edit.
	 * @return void
	 */
	protected static function render_invoice_editor( $invoice ) {
		global $wpdb;

		// Ensure invoice has required fields
		if ( ! isset( $invoice->id ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Invalid invoice data.', 'atyourservice' ) . '</p></div>';
			return;
		}

		// Items schema uses `qty` (not `quantity`). Query both and alias.
		$invoice_items = $wpdb->get_results( $wpdb->prepare(
			"SELECT ii.* FROM {$wpdb->prefix}ays_invoice_items ii WHERE ii.invoice_id = %d ORDER BY ii.id ASC",
			$invoice->id
		) );

		// Alias qty → quantity for compatibility
		if ( $invoice_items ) {
			foreach ( $invoice_items as &$item ) {
				if ( ! isset( $item->quantity ) && isset( $item->qty ) ) {
					$item->quantity = $item->qty;
				} elseif ( ! isset( $item->quantity ) ) {
					$item->quantity = 1;
				}
			}
		}

		$clients = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}ays_clients WHERE status = 'active' ORDER BY name" );
		if ( ! $clients ) {
			$clients = [];
		}
		
		$client = null;
		if ( isset( $invoice->client_id ) && $invoice->client_id ) {
			$client = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ays_clients WHERE id = %d", $invoice->client_id ) );
		}
		
		$company_profile = class_exists( 'AYS_Company_Profile' ) ? AYS_Company_Profile::get_profile() : [];
		$company_logo_id = isset( $company_profile['company_logo_id'] ) ? intval( $company_profile['company_logo_id'] ) : 0;
		$company_logo_url = $company_logo_id ? wp_get_attachment_image_url( $company_logo_id, 'medium' ) : '';

		$invoice_repo   = class_exists( 'AYS_Invoice_Repository' ) ? new AYS_Invoice_Repository() : null;
		$payments       = $invoice_repo ? $invoice_repo->get_invoice_payments( $invoice->id ) : [];
		$total_paid     = 0.0;
		foreach ( (array) $payments as $payment_row ) {
			$total_paid += isset( $payment_row['amount'] ) ? (float) $payment_row['amount'] : 0.0;
		}
		$balance        = max( 0, (float) ( isset( $invoice->total ) ? $invoice->total : 0 ) - $total_paid );
		$progress       = (float) ( isset( $invoice->total ) ? $invoice->total : 0 ) > 0 ? min( 100, round( ( $total_paid / (float) $invoice->total ) * 100 ) ) : 0;
		$recent_payments = array_slice( (array) $payments, 0, 3 );
		$preferences    = class_exists( 'AYS_Invoice_Preferences' ) ? AYS_Invoice_Preferences::get( $invoice->id ) : [];
		$primary_color  = isset( $preferences['primary_color'] ) ? sanitize_hex_color( $preferences['primary_color'] ) : '#4c51bf';
		$secondary_color = isset( $preferences['accent_color'] ) ? sanitize_hex_color( $preferences['accent_color'] ) : '#111827';
		if ( empty( $primary_color ) ) {
			$primary_color = '#4c51bf';
		}
		if ( empty( $secondary_color ) ) {
			$secondary_color = '#111827';
		}
		$issue_date_display = ! empty( $invoice->issue_date ) ? date_i18n( get_option( 'date_format' ), strtotime( $invoice->issue_date ) ) : '—';
		$due_date_display   = ! empty( $invoice->due_date ) ? date_i18n( get_option( 'date_format' ), strtotime( $invoice->due_date ) ) : '—';
		$due_delta          = '';
		if ( ! empty( $invoice->due_date ) ) {
			$due_ts = strtotime( $invoice->due_date );
			if ( $due_ts ) {
				$now = current_time( 'timestamp' );
				$due_delta = $due_ts >= $now
					? sprintf( __( 'Due in %s', 'atyourservice' ), human_time_diff( $now, $due_ts ) )
					: sprintf( __( '%s overdue', 'atyourservice' ), human_time_diff( $due_ts, $now ) );
			}
		}
		$preview_warnings = [];
		if ( empty( $invoice_items ) ) {
			$preview_warnings[] = __( 'Add at least one line item before sending.', 'atyourservice' );
		}
		if ( ! $client || empty( $client->email ) ) {
			$preview_warnings[] = __( 'Client email address is missing.', 'atyourservice' );
		}
		if ( empty( $invoice->due_date ) ) {
			$preview_warnings[] = __( 'Set a due date so the client knows when payment is expected.', 'atyourservice' );
		}
		if ( empty( $company_profile['company_name'] ) ) {
			$preview_warnings[] = __( 'Company profile is incomplete. Update your business name in Settings.', 'atyourservice' );
		}
		$invoice_settings = get_option( 'ays_invoice_settings', [] );
		$currency_code    = isset( $invoice_settings['currency'] ) ? strtoupper( $invoice_settings['currency'] ) : 'NZD';
		$currency_symbols = [
			'NZD' => '$',
			'USD' => '$',
			'AUD' => 'A$',
			'GBP' => '£',
			'EUR' => '€',
		];
		$currency_symbol = $currency_symbols[ $currency_code ] ?? '$';
		$format_money    = static function( $amount ) use ( $currency_symbol ) {
			return $currency_symbol . number_format_i18n( (float) $amount, 2 );
		};

		// Simple view switcher (Edit | Preview)
		$view = isset($_GET['view']) ? sanitize_key($_GET['view']) : 'edit';

		?>
		<div class="ays-invoice-editor" style="--ays-invoice-accent: <?php echo esc_attr( $primary_color ); ?>; --ays-invoice-muted: <?php echo esc_attr( $secondary_color ); ?>;">
			<div class="editor-header" style="display:flex;align-items:center;gap:12px;justify-content:space-between;">
				<h2 style="margin:0;">
					<?php echo esc_html__( 'Invoice', 'atyourservice' ) . ' #' . esc_html( $invoice->invoice_number ); ?>
				</h2>
				<div class="editor-actions" style="display:flex;gap:8px;">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=ays-invoice-preview&invoice_id=' . intval( $invoice->id ) ) ); ?>" target="_blank" class="button button-secondary">
						<?php esc_html_e( 'Preview', 'atyourservice' ); ?>
					</a>
					<a href="<?php echo esc_url( remove_query_arg( 'edit_invoice' ) ); ?>" class="button">
						<?php esc_html_e( '← Back to List', 'atyourservice' ); ?>
					</a>
				</div>
			</div>

			<h2 class="nav-tab-wrapper" style="margin-top:10px;">
				<?php
				$base = remove_query_arg( 'view' );
				$edit_url = add_query_arg( [ 'edit_invoice' => intval($invoice->id) ], $base );
				$preview_url = add_query_arg( [ 'edit_invoice' => intval($invoice->id), 'view' => 'preview' ], $base );
				?>
				<a href="<?php echo esc_url( $edit_url ); ?>" class="nav-tab <?php echo $view === 'preview' ? '' : 'nav-tab-active'; ?>"><?php esc_html_e('Edit','atyourservice'); ?></a>
				<a href="<?php echo esc_url( $preview_url ); ?>" class="nav-tab <?php echo $view === 'preview' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Preview','atyourservice'); ?></a>
			</h2>

			<?php if ( $view === 'preview' ) : ?>
				<?php self::render_preview_tab( [
					'invoice'          => $invoice,
					'items'            => $invoice_items,
					'client'           => $client,
					'preferences'      => $preferences,
					'company_profile'  => $company_profile,
					'company_logo_url' => $company_logo_url,
					'payments'         => (array) $payments,
					'recent_payments'  => (array) $recent_payments,
					'currency_symbol'  => $currency_symbol,
					'format_money'     => $format_money,
					'issue_date_label' => $issue_date_display,
					'due_date_label'   => $due_date_display,
					'due_delta'        => $due_delta,
					'total_paid'       => $total_paid,
					'balance'          => $balance,
					'warnings'         => $preview_warnings,
				] ); ?>
				<?php return; endif; ?>

			<div class="editor-content">
				<div class="editor-main">
					<!-- Client Selection Section -->
					<div class="invoice-section" style="border-left-color: #f59e0b; background: #fffbf0;">
						<h3 style="color: #b45309; margin-top: 0;">👤 <?php esc_html_e( 'Bill To', 'atyourservice' ); ?></h3>
						<div style="display: grid; grid-template-columns: 1fr auto; gap: 12px; align-items: center; margin-bottom: 12px;">
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display: grid; grid-template-columns: 1fr auto; gap: 8px;">
								<input type="hidden" name="action" value="ays_update_invoice_client" />
								<input type="hidden" name="invoice_id" value="<?php echo esc_attr( $invoice->id ); ?>" />
								<?php wp_nonce_field( 'ays_update_invoice_client_' . $invoice->id ); ?>
								<select name="client_id" style="width: 100%;">
									<option value="">— <?php esc_html_e( 'Select a client', 'atyourservice' ); ?> —</option>
									<?php foreach ( $clients as $c ) : ?>
										<option value="<?php echo esc_attr( $c->id ); ?>" <?php selected( $invoice->client_id, $c->id ); ?>>
											<?php echo esc_html( $c->name ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<button type="submit" class="button" style="white-space: nowrap;"><?php esc_html_e( 'Select', 'atyourservice' ); ?></button>
							</form>
						</div>
						<?php if ( $client ) : ?>
							<div style="background: white; border-left: 3px solid #10b981; padding: 12px; border-radius: 4px; margin-bottom: 12px;">
								<strong style="color: #059669;">✓ <?php esc_html_e( 'Current Client:', 'atyourservice' ); ?></strong>
								<div style="margin-top: 6px; font-size: 14px;">
									<strong><?php echo esc_html( $client->name ); ?></strong><br />
									<?php if ( ! empty( $client->email ) ) : ?>
										<small style="color: #6b7280;"><?php echo esc_html( $client->email ); ?></small><br />
									<?php endif; ?>
									<?php if ( ! empty( $client->phone ) ) : ?>
										<small style="color: #6b7280;"><?php echo esc_html( $client->phone ); ?></small>
									<?php endif; ?>
								</div>
							</div>
						<?php endif; ?>
						<!-- Inline Create Client Drawer -->
						<details style="margin-top: 8px;">
							<summary style="cursor: pointer; color: #2271b1; font-weight: 500; padding: 8px 0;">+ <?php esc_html_e( 'Create New Client', 'atyourservice' ); ?></summary>
							<div style="padding: 12px; background: white; border: 1px solid #e5e7eb; border-radius: 4px; margin-top: 8px;">
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
									<input type="hidden" name="action" value="ays_add_client_from_invoice" />
									<input type="hidden" name="invoice_id" value="<?php echo esc_attr( $invoice->id ); ?>" />
									<?php wp_nonce_field( 'ays_add_client_from_invoice_' . $invoice->id ); ?>
									<p style="margin: 0 0 8px;">
										<input type="text" name="client_name" placeholder="<?php esc_attr_e( 'Client Name *', 'atyourservice' ); ?>" required style="width: 100%;" class="regular-text" />
									</p>
									<p style="margin: 0 0 8px;">
										<input type="email" name="client_email" placeholder="<?php esc_attr_e( 'Email', 'atyourservice' ); ?>" style="width: 100%;" class="regular-text" />
									</p>
									<p style="margin: 0 0 12px;">
										<input type="tel" name="client_phone" placeholder="<?php esc_attr_e( 'Phone', 'atyourservice' ); ?>" style="width: 100%;" class="regular-text" />
									</p>
									<button type="submit" class="button button-primary"><?php esc_html_e( 'Create & Assign to Invoice', 'atyourservice' ); ?></button>
								</form>
							</div>
						</details>
					</div>

					<!-- Invoice Header -->
					<div class="invoice-section">
						<h3><?php esc_html_e( 'Invoice Details', 'atyourservice' ); ?></h3>
						<table class="form-table">
							<tr>
								<th><label><?php esc_html_e( 'Invoice Number', 'atyourservice' ); ?></label></th>
								<td><code><?php echo esc_html( $invoice->invoice_number ); ?></code></td>
							</tr>
							<tr>
								<th><label><?php esc_html_e( 'Status', 'atyourservice' ); ?></label></th>
								<td><?php self::render_status_badge( $invoice->status ); ?></td>
							</tr>
							<tr>
								<th><label><?php esc_html_e( 'Issue Date', 'atyourservice' ); ?></label></th>
								<td><?php echo esc_html( date_i18n( 'M d, Y', strtotime( $invoice->issue_date ) ) ); ?></td>
							</tr>
							<tr>
								<th><label><?php esc_html_e( 'Due Date', 'atyourservice' ); ?></label></th>
								<td><?php echo esc_html( date_i18n( 'M d, Y', strtotime( $invoice->due_date ) ) ); ?></td>
							</tr>
							<tr>
								<th><label><?php esc_html_e( 'Status', 'atyourservice' ); ?></label></th>
								<td><?php self::render_status_badge( $invoice->status ); ?></td>
							</tr>
							<tr>
								<th><label><?php esc_html_e( 'Services', 'atyourservice' ); ?></label></th>
								<td><?php self::render_service_type_chips( $invoice->id ); ?></td>
							</tr>
						</table>
					</div>

					<!-- Line Items -->
					<div class="invoice-section">
						<h3><?php esc_html_e( 'Line Items', 'atyourservice' ); ?></h3>
						<?php if ( empty( $invoice_items ) ) : ?>
							<p><?php esc_html_e( 'No items on this invoice yet.', 'atyourservice' ); ?></p>
						<?php else : ?>
							<table class="widefat striped">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Description', 'atyourservice' ); ?></th>
										<th style="text-align: right; width: 100px;"><?php esc_html_e( 'Qty', 'atyourservice' ); ?></th>
										<th style="text-align: right; width: 120px;"><?php esc_html_e( 'Rate', 'atyourservice' ); ?></th>
										<th style="text-align: right; width: 120px;"><?php esc_html_e( 'Amount', 'atyourservice' ); ?></th>
										<th style="text-align: center; width: 60px;"><?php esc_html_e( 'Tax', 'atyourservice' ); ?></th>
										<th style="text-align:center; width: 70px;">&nbsp;</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $invoice_items as $item ) : ?>
										<tr style="background:white;border-bottom:1px solid #e5e7eb;">
											<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:contents;">
												<?php wp_nonce_field( 'ays_update_invoice_item_' . $item->id, 'ays_item_nonce' ); ?>
												<input type="hidden" name="action" value="ays_update_invoice_item" />
												<input type="hidden" name="item_id" value="<?php echo esc_attr( $item->id ); ?>" />
												<input type="hidden" name="invoice_id" value="<?php echo esc_attr( $invoice->id ); ?>" />
												<td><input type="text" name="description" value="<?php echo esc_attr( $item->description ); ?>" class="regular-text" style="width:100%;" /></td>
												<td style="text-align: right;"><input type="number" name="quantity" step="0.01" min="0" value="<?php echo esc_attr( $item->quantity ); ?>" style="width:100%;text-align:right;" /></td>
												<td style="text-align: right;"><input type="number" name="rate" step="0.01" min="0" value="<?php echo esc_attr( $item->rate ); ?>" style="width:100%;text-align:right;" /></td>
												<td style="text-align: right;"><code>$<?php echo esc_html( number_format( $item->quantity * $item->rate, 2 ) ); ?></code></td>
												<td style="text-align: center;"><input type="checkbox" name="taxable" value="1" <?php checked( $item->taxable, 1 ); ?> /></td>
												<td style="text-align:center;display:flex;gap:4px;justify-content:center;">
													<button type="submit" class="button button-small" title="<?php esc_attr_e('Update', 'atyourservice'); ?>" style="padding:4px 8px;">💾</button>
													<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ays_delete_invoice_item&item_id=' . intval($item->id) . '&invoice_id=' . intval($invoice->id) ), 'ays_delete_invoice_item_' . intval($item->id) ) ); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php esc_attr_e('Delete this line?', 'atyourservice'); ?>')" style="padding:4px 8px;">🗑️</a>
												</td>
											</form>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>

							<!-- Add Line Item Form -->
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:12px;display:grid;grid-template-columns:1fr 120px 140px 120px 120px;gap:8px;align-items:center;">
								<?php wp_nonce_field( 'ays_add_invoice_item_' . $invoice->id, 'ays_add_item_nonce' ); ?>
								<input type="hidden" name="action" value="ays_add_invoice_item" />
								<input type="hidden" name="invoice_id" value="<?php echo esc_attr( $invoice->id ); ?>" />
								<input type="text" name="description" placeholder="<?php esc_attr_e('Description', 'atyourservice'); ?>" class="regular-text" style="grid-column:1/2;" required />
								<input type="number" name="quantity" step="0.01" min="0" value="1" placeholder="Qty" />
								<input type="number" name="rate" step="0.01" min="0" value="0" placeholder="Rate" />
								<label style="display:flex;align-items:center;gap:6px;justify-content:center;"><input type="checkbox" name="taxable" value="1" checked /> <?php esc_html_e('Taxable', 'atyourservice'); ?></label>
								<button type="submit" class="button button-primary" style="width:100%;"><?php esc_html_e('Add line', 'atyourservice'); ?></button>
							</form>
						<?php endif; ?>
					</div>

					<!-- Totals -->
					<div class="invoice-section invoice-totals">
						<table style="width: 100%; max-width: 400px; margin-left: auto;">
							<tr>
								<th style="text-align: right; padding: 8px;"><?php esc_html_e( 'Subtotal:', 'atyourservice' ); ?></th>
								<td style="text-align: right; padding: 8px;"><code>$<?php echo esc_html( number_format( $invoice->subtotal, 2 ) ); ?></code></td>
							</tr>
							<tr>
								<th style="text-align: right; padding: 8px;"><?php esc_html_e( 'Tax:', 'atyourservice' ); ?></th>
								<td style="text-align: right; padding: 8px;"><code>$<?php echo esc_html( number_format( $invoice->tax_amount, 2 ) ); ?></code></td>
							</tr>
							<tr style="border-top: 2px solid #ccc; font-weight: bold; font-size: 16px;">
								<th style="text-align: right; padding: 8px;"><?php esc_html_e( 'Total:', 'atyourservice' ); ?></th>
								<td style="text-align: right; padding: 8px;"><code>$<?php echo esc_html( number_format( $invoice->total, 2 ) ); ?></code></td>
							</tr>
						</table>
					</div>

					<!-- Notes -->
					<?php if ( $invoice->notes ) : ?>
						<div class="invoice-section">
							<h3><?php esc_html_e( 'Notes', 'atyourservice' ); ?></h3>
							<p><?php echo esc_html( $invoice->notes ); ?></p>
						</div>
					<?php endif; ?>

					<!-- Service Types (Many-to-Many) -->
					<div class="invoice-section">
						<h3><?php esc_html_e( 'Service Types', 'atyourservice' ); ?></h3>
						<?php
							$all_services = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}ays_service_types WHERE status='active' ORDER BY sort_order, name" );
							$selected_services = self::get_invoice_service_type_ids( $invoice->id );
						?>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<?php wp_nonce_field( 'ays_update_invoice_services_' . $invoice->id, 'ays_invoice_services_nonce' ); ?>
							<input type="hidden" name="action" value="ays_update_invoice_services">
							<input type="hidden" name="invoice_id" value="<?php echo esc_attr( $invoice->id ); ?>">
							<select name="service_type_ids[]" multiple size="6" style="min-width:260px;">
								<?php foreach ( $all_services as $svc ) : ?>
									<option value="<?php echo esc_attr( $svc->id ); ?>" <?php selected( in_array( (int) $svc->id, $selected_services, true ) ); ?>>
										<?php echo esc_html( $svc->name ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description" style="margin-top:8px;">
								<?php esc_html_e( 'Hold Ctrl/Cmd to select multiple service types that this invoice covers.', 'atyourservice' ); ?>
							</p>
							<p>
								<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Services', 'atyourservice' ); ?></button>
							</p>
						</form>
					</div>

					<!-- Admin Invoice Preview -->
					<div class="invoice-section">
						<h3 style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
							<span><?php esc_html_e( 'Invoice Preview', 'atyourservice' ); ?></span>
							<button type="button" class="button" id="ays-print-invoice-preview">🖨️ <?php esc_html_e( 'Print Preview', 'atyourservice' ); ?></button>
						</h3>
						<div id="ays-invoice-preview" class="ays-invoice-preview">
							<?php self::render_invoice_preview( $invoice, $invoice_items, $client ); ?>
						</div>
					</div>
				</div>

				<div class="editor-sidebar">
					<!-- Status Card -->
					<div class="sidebar-box" style="background:linear-gradient(135deg, <?php echo esc_attr( $primary_color ); ?> 0%, <?php echo esc_attr( $secondary_color ); ?> 100%);color:white;border:0;">
						<h4 style="margin-top:0;color:white;font-size:13px;text-transform:uppercase;letter-spacing:.5px;"><?php esc_html_e( 'Invoice Status', 'atyourservice' ); ?></h4>
						<div style="font-size:18px;font-weight:700;margin-bottom:4px;">
							<?php 
								$status_map = [ 'draft' => '📝 Draft', 'sent' => '📨 Sent', 'viewed' => '👁️ Viewed', 'paid' => '✓ Paid', 'void' => '🚫 Void' ];
								echo esc_html( $status_map[ $invoice->status ] ?? ucfirst( $invoice->status ) );
							?>
						</div>
						<div style="font-size:12px;opacity:0.9;">
							<?php if ( ! empty( $due_delta ) ) : ?>
								<?php echo esc_html( $due_delta ); ?>
							<?php else : ?>
								<?php esc_html_e( 'Invoice Details', 'atyourservice' ); ?>
							<?php endif; ?>
						</div>
					</div>

					<!-- Totals Summary -->
					<div class="sidebar-box" style="background:#f0fdf4;border:1px solid #86efac;">
						<h4 style="margin-top:0;color:#166534;font-size:13px;text-transform:uppercase;letter-spacing:.5px;"><?php esc_html_e( 'Totals', 'atyourservice' ); ?></h4>
						<div style="font-size:14px;color:#166534;">
							<div style="display:flex;justify-content:space-between;margin-bottom:6px;">
								<span><?php esc_html_e( 'Subtotal:', 'atyourservice' ); ?></span>
								<strong><?php echo call_user_func( $format_money, $invoice->subtotal ?? 0 ); ?></strong>
							</div>
							<?php if ( ! empty( $invoice->tax_amount ) && $invoice->tax_amount > 0 ) : ?>
								<div style="display:flex;justify-content:space-between;margin-bottom:6px;">
									<span><?php esc_html_e( 'Tax:', 'atyourservice' ); ?></span>
									<strong><?php echo call_user_func( $format_money, $invoice->tax_amount ?? 0 ); ?></strong>
								</div>
							<?php endif; ?>
							<div style="padding-top:8px;border-top:1px solid #86efac;display:flex;justify-content:space-between;font-weight:700;font-size:15px;color:#166534;">
								<span><?php esc_html_e( 'Amount Due:', 'atyourservice' ); ?></span>
								<span><?php echo call_user_func( $format_money, $invoice->total ?? 0 ); ?></span>
							</div>
						</div>
					</div>

					<!-- Payment Progress (if payments exist) -->
					<?php if ( ! empty( $payments ) && $total_paid > 0 ) : ?>
						<div class="sidebar-box">
							<h4 style="margin-top:0;color:#059669;font-size:13px;text-transform:uppercase;letter-spacing:.5px;"><?php esc_html_e( '✓ Payments Received', 'atyourservice' ); ?></h4>
							<div style="margin-bottom:8px;">
								<div style="display:flex;justify-content:space-between;margin-bottom:4px;font-size:12px;">
									<span><?php esc_html_e( 'Paid:', 'atyourservice' ); ?></span>
									<strong><?php echo call_user_func( $format_money, $total_paid ); ?></strong>
								</div>
								<?php if ( $balance > 0 ) : ?>
									<div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:12px;">
										<span><?php esc_html_e( 'Outstanding:', 'atyourservice' ); ?></span>
										<strong style="color:#dc2626;"><?php echo call_user_func( $format_money, $balance ); ?></strong>
									</div>
								<?php endif; ?>
							</div>
							<div style="background:#f3f4f6;border-radius:4px;overflow:hidden;height:6px;margin-bottom:8px;">
								<div style="background:#10b981;width:<?php echo esc_attr( $progress ); ?>%;height:100%;"></div>
							</div>
							<div style="font-size:11px;color:#6b7280;text-align:center;">
								<?php printf( esc_html__( '%d%% paid', 'atyourservice' ), intval( $progress ) ); ?>
							</div>
						</div>
					<?php endif; ?>

					<!-- Recent Payments -->
					<?php if ( ! empty( $recent_payments ) ) : ?>
						<div class="sidebar-box">
							<h4 style="margin-top:0;color:#111827;font-size:13px;text-transform:uppercase;letter-spacing:.5px;"><?php esc_html_e( 'Recent Payments', 'atyourservice' ); ?></h4>
							<ul style="margin:0;padding:0;list-style:none;font-size:12px;">
								<?php foreach ( $recent_payments as $p ) : ?>
									<li style="padding:6px 0;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;">
										<span style="color:#6b7280;"><?php echo esc_html( isset( $p['date'] ) ? date_i18n( get_option( 'date_format' ), strtotime( $p['date'] ) ) : '—' ); ?></span>
										<strong style="color:#059669;"><?php echo call_user_func( $format_money, $p['amount'] ?? 0 ); ?></strong>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>

					<!-- Actions Card -->
					<div class="sidebar-box">
						<h4 style="margin-top:0;color:#111827;font-size:13px;text-transform:uppercase;letter-spacing:.5px;"><?php esc_html_e( '⚙️ Actions', 'atyourservice' ); ?></h4>
						<div style="display:flex;flex-direction:column;gap:6px;">
							<?php if ( $invoice->status !== 'paid' ) : ?>
								<?php if ( class_exists( 'AYS_Stripe_Settings' ) && AYS_Stripe_Settings::is_configured() ) : ?>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
										<?php wp_nonce_field( 'ays_stripe_checkout', '_wpnonce' ); ?>
										<input type="hidden" name="action" value="ays_stripe_checkout">
										<input type="hidden" name="invoice_id" value="<?php echo esc_attr( $invoice->id ); ?>">
										<button type="submit" class="button button-primary" style="width:100%;box-sizing:border-box;font-size:12px;">
											💳 <?php esc_html_e( 'Pay Now (Stripe)', 'atyourservice' ); ?>
										</button>
									</form>
								<?php endif; ?>
								<a href="<?php echo esc_url( add_query_arg( [ 'action' => 'ays_mark_paid', 'invoice_id' => $invoice->id ], admin_url( 'admin-post.php' ) ) ); ?>" class="button button-small" style="width:100%;box-sizing:border-box;font-size:12px;" onclick="return confirm('<?php esc_attr_e( 'Mark as paid?', 'atyourservice' ); ?>')">
									<?php esc_html_e( 'Mark as Paid', 'atyourservice' ); ?>
								</a>
							<?php endif; ?>
							<?php if ( $invoice->status !== 'void' ) : ?>
								<a href="<?php echo esc_url( add_query_arg( [ 'action' => 'ays_void_invoice', 'invoice_id' => $invoice->id ], admin_url( 'admin-post.php' ) ) ); ?>" class="button button-small" style="width:100%;box-sizing:border-box;color:#dc2626;font-size:12px;border-color:#fca5a5;" onclick="return confirm('<?php esc_attr_e( 'Mark as void? This cannot be undone.', 'atyourservice' ); ?>')">
									🚫 <?php esc_html_e( 'Void Invoice', 'atyourservice' ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>

					<!-- Bank Transfer Details -->
					<?php if ( class_exists( 'AYS_Company_Profile' ) ) : ?>
						<div class="sidebar-box">
							<h4 style="margin-top:0;color:#111827;font-size:13px;text-transform:uppercase;letter-spacing:.5px;"><?php esc_html_e( '🏦 Bank Transfer', 'atyourservice' ); ?></h4>
							<?php echo AYS_Company_Profile::get_bank_transfer_html( $invoice->invoice_number ); // escaped in renderer ?>
						</div>
					<?php endif; ?>

					<!-- Danger Zone -->
					<?php if ( current_user_can( 'delete_posts' ) ) : ?>
						<div class="sidebar-box" style="background:#fef2f2;border:1px solid #fecaca;border-radius:4px;">
							<h4 style="margin-top:0;color:#991b1b;font-size:13px;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;"><?php esc_html_e( '🚨 Danger Zone', 'atyourservice' ); ?></h4>
							<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'action' => 'ays_delete_invoice', 'invoice_id' => $invoice->id ], admin_url( 'admin-post.php' ) ), 'ays_delete_invoice_' . $invoice->id ) ); ?>" class="button button-small" style="width:100%;box-sizing:border-box;color:#dc2626;font-size:12px;border-color:#fca5a5;" onclick="return confirm('<?php esc_attr_e( 'Permanently delete this invoice and all associated items? This cannot be undone.', 'atyourservice' ); ?>')">
								🗑️ <?php esc_html_e( 'Delete Invoice', 'atyourservice' ); ?>
							</a>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<style>
			.ays-invoice-editor {
				background: white;
				padding: 20px;
				border-radius: 6px;
				margin-top: 20px;
			}
			.editor-header {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-bottom: 24px;
				padding-bottom: 12px;
				border-bottom: 2px solid #e5e7eb;
			}
			.editor-header h2 {
				margin: 0;
			}
			.editor-content {
				display: grid;
				grid-template-columns: 1fr 250px;
				gap: 24px;
			}
			.invoice-section {
				margin-bottom: 24px;
				padding: 16px;
				background: #f9fafb;
				border-radius: 4px;
				border-left: 4px solid #4c51bf;
			}
			.invoice-section h3 {
				margin-top: 0;
				margin-bottom: 12px;
			}
			.invoice-totals {
				background: #f0f4ff;
				border-left-color: #4c51bf;
			}
			.sidebar-box {
				background: white;
				border: 1px solid #e5e7eb;
				border-radius: 4px;
				padding: 12px;
				margin-bottom: 12px;
			}
			.sidebar-box h4 {
				margin-top: 0;
				margin-bottom: 12px;
			}
			.ays-chip { display:inline-block; padding:2px 8px; font-size:11px; border-radius:999px; background:#eef2ff; color:#3730a3; border:1px solid #c7d2fe; margin:2px 6px 2px 0; }
			.ays-chip .dot { display:inline-block; width:6px; height:6px; background:#6366f1; border-radius:999px; margin-right:6px; vertical-align:middle; }
			@media (max-width: 768px) {
				.editor-content {
					grid-template-columns: 1fr;
				}
			}
		</style>

		<script>
		(function(){
			var btn = document.getElementById('ays-print-invoice-preview');
			if(!btn) return;
			btn.addEventListener('click', function(){
				var container = document.getElementById('ays-invoice-preview');
				if(!container) return;
				var printWin = window.open('', '_blank');
				var html = '\n<!doctype html><html><head><meta charset="utf-8"><title>Invoice Preview</title>' +
					'<style>body{font-family:Arial,sans-serif;margin:20px;} .ays-invoice-preview{border:0;padding:0;} .ays-invoice-preview-header{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;padding-bottom:20px;border-bottom:2px solid #e5e7eb;} .ays-invoice-preview-items{width:100%;border-collapse:collapse;margin-bottom:20px;} .ays-invoice-preview-items thead{background:#f3f4f6;border-bottom:2px solid #d1d5db;} .ays-invoice-preview-items th{padding:10px;text-align:left;font-weight:600;font-size:13px;color:#4c51bf;text-transform:uppercase;letter-spacing:.5px;} .ays-invoice-preview-items td{padding:10px;border-bottom:1px solid #e5e7eb;} .ays-invoice-preview-total{display:grid;grid-template-columns:auto 1fr;gap:20px;justify-content:flex-end;margin-top:20px;padding-top:20px;border-top:2px solid #e5e7eb;} .ays-invoice-preview-total-row{display:grid;grid-template-columns:120px 1fr;gap:20px;align-items:center;margin-bottom:8px;} .ays-invoice-preview-total-row.final{font-weight:600;font-size:16px;color:#4c51bf;padding:8px 0;border-top:1px solid #d1d5db;} </style></head><body>' +
					container.innerHTML + '</body></html>';
				printWin.document.open();
				printWin.document.write(html);
				printWin.document.close();
				printWin.focus();
				printWin.print();
			});
		})();
		</script>
		<?php
	}

	/**
	 * Render status badge
	 *
	 * @param string $status The invoice status.
	 * @return void
	 */
	protected static function render_status_badge( $status ) {
		if ( ! isset( self::INVOICE_STATUSES[ $status ] ) ) {
			echo esc_html( $status );
			return;
		}

		$status_info = self::INVOICE_STATUSES[ $status ];
		?>
		<span style="color: <?php echo esc_attr( $status_info['color'] ); ?>; font-weight: 500;">
			<?php echo esc_html( $status_info['icon'] . ' ' . $status_info['label'] ); ?>
		</span>
		<?php
	}

	/**
	 * Render admin invoice preview box content
	 *
	 * @param object $invoice Current invoice
	 * @param array  $items   Invoice items from DB
	 * @param object $client  Client row
	 * @return void
	 */
	protected static function render_invoice_preview( $invoice, $items, $client ) {
		$company = class_exists('AYS_Company_Profile') ? AYS_Company_Profile::get_profile() : [
			'company_name' => get_bloginfo('name'),
			'company_address' => '',
			'company_email' => get_option('admin_email'),
			'company_phone' => '',
		];
		$company_name    = isset($company['company_name']) ? $company['company_name'] : get_bloginfo('name');
		$company_address = isset($company['company_address']) ? nl2br( esc_html( $company['company_address'] ) ) : '';
		$company_email   = isset($company['company_email']) ? $company['company_email'] : '';
		$company_phone   = isset($company['company_phone']) ? $company['company_phone'] : '';

		$client_name  = isset($client->name) ? $client->name : '—';
		$client_email = isset($client->email) ? $client->email : '';
		$client_phone = isset($client->phone) ? $client->phone : '';

		?>
		<div class="ays-invoice-preview-header">
			<div class="ays-invoice-preview-from">
				<h3><?php esc_html_e('From:', 'atyourservice'); ?></h3>
				<p><strong><?php echo esc_html( $company_name ); ?></strong><br/>
					<?php echo $company_address ? $company_address . '<br/>' : ''; ?>
					<?php echo $company_email ? esc_html( $company_email ) . '<br/>' : ''; ?>
					<?php echo $company_phone ? esc_html( $company_phone ) : ''; ?></p>
			</div>
			<div style="text-align:right;">
				<h1 style="margin:0 0 10px;color:#4c51bf;">INVOICE</h1>
				<p style="margin:0;">
					<?php esc_html_e('Invoice #', 'atyourservice'); ?> <strong><?php echo esc_html( $invoice->invoice_number ); ?></strong><br/>
					<small style="color:#6b7280;"><?php esc_html_e('Issued:', 'atyourservice'); ?> <?php echo esc_html( date_i18n( 'Y-m-d', strtotime( $invoice->issue_date ) ) ); ?></small>
				</p>
			</div>
		</div>

		<div class="ays-invoice-preview-header">
			<div class="ays-invoice-preview-to">
				<h3><?php esc_html_e('Bill To:', 'atyourservice'); ?></h3>
				<p><strong><?php echo esc_html( $client_name ); ?></strong><br/>
					<?php echo $client_email ? esc_html( $client_email ) . '<br/>' : ''; ?>
					<?php echo $client_phone ? esc_html( $client_phone ) : ''; ?></p>
			</div>
			<div style="text-align:right;">
				<table style="margin:0 auto;font-size:13px;">
					<tr>
						<td style="padding:4px 20px 4px 0;text-align:right;color:#6b7280;">
							<?php esc_html_e('Due Date:', 'atyourservice'); ?> <strong><?php echo esc_html( date_i18n( 'Y-m-d', strtotime( $invoice->due_date ) ) ); ?></strong>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<table class="ays-invoice-preview-items">
			<thead>
				<tr>
					<th><?php esc_html_e('Description', 'atyourservice'); ?></th>
					<th style="text-align:center;">Qty</th>
					<th style="text-align:right;">Rate</th>
					<th style="text-align:right;">Amount</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $items ) ) : ?>
					<tr><td colspan="4" style="text-align:center;color:#6b7280;">— <?php esc_html_e('No items yet', 'atyourservice'); ?> —</td></tr>
				<?php else : foreach ( $items as $it ) : ?>
					<tr>
						<td><?php echo esc_html( $it->description ); ?></td>
						<td style="text-align:center;">&times;<?php echo esc_html( $it->quantity ); ?></td>
						<td style="text-align:right;">$<?php echo esc_html( number_format( (float)$it->rate, 2 ) ); ?></td>
						<td style="text-align:right;">$<?php echo esc_html( number_format( (float)$it->quantity * (float)$it->rate, 2 ) ); ?></td>
					</tr>
				<?php endforeach; endif; ?>
			</tbody>
		</table>

		<div class="ays-invoice-preview-total">
			<div style="grid-column:1 / -1;display:grid;grid-template-columns:auto 1fr;gap:20px;justify-content:flex-end;">
				<div class="ays-invoice-preview-total-row">
					<strong style="text-align:right;">Subtotal:</strong>
					<span style="text-align:right;">$<?php echo esc_html( number_format( (float)$invoice->subtotal, 2 ) ); ?></span>
				</div>
				<div class="ays-invoice-preview-total-row">
					<strong style="text-align:right;">Tax:</strong>
					<span style="text-align:right;">$<?php echo esc_html( number_format( (float)$invoice->tax_amount, 2 ) ); ?></span>
				</div>
				<div class="ays-invoice-preview-total-row final">
					<strong style="text-align:right;">TOTAL:</strong>
					<span style="text-align:right;">$<?php echo esc_html( number_format( (float)$invoice->total, 2 ) ); ?></span>
				</div>
			</div>
		</div>

		<?php if ( class_exists('AYS_Company_Profile') ) : ?>
			<div style="margin-top:12px;">
				<?php echo AYS_Company_Profile::get_bank_transfer_html( $invoice->invoice_number ); // already escaped ?>
			</div>
		<?php endif; ?>
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
				case 'invoice_created':
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '✓ Invoice created successfully!', 'atyourservice' ) . '</p></div>';
					break;
				case 'invoice_marked_paid':
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '✓ Invoice marked as paid!', 'atyourservice' ) . '</p></div>';
					break;
				case 'invoice_deleted':
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '✓ Invoice deleted successfully!', 'atyourservice' ) . '</p></div>';
					break;
				case 'invoice_error':
					echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( '✗ An error occurred. Please try again.', 'atyourservice' ) . '</p></div>';
					break;
				case 'invoice_services_updated':
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '✓ Service types updated for this invoice.', 'atyourservice' ) . '</p></div>';
					break;
				case 'invoice_client_updated':
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '✓ Invoice client updated successfully!', 'atyourservice' ) . '</p></div>';
					break;
				case 'invoice_client_created':
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '✓ New client created and assigned to invoice!', 'atyourservice' ) . '</p></div>';
					break;
				case 'invoice_sent':
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '✓ Invoice email sent successfully!', 'atyourservice' ) . '</p></div>';
					break;
				case 'invoice_voided':
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '✓ Invoice marked as void.', 'atyourservice' ) . '</p></div>';
					break;
			}
		}
	}

	/**
	 * Get a single invoice by ID
	 *
	 * @param int $invoice_id The invoice ID.
	 * @return object|null The invoice object or null.
	 */
	protected static function get_invoice( $invoice_id ) {
		global $wpdb;
		if ( ! $invoice_id ) {
			return null;
		}
		
		// Try standard query first
		$row = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}ays_invoices WHERE id = %d LIMIT 1",
			$invoice_id
		) );
		
		if ( $row ) {
			// Add aliases for schema compatibility
			if ( ! isset( $row->invoice_number ) && isset( $row->inv_number ) ) {
				$row->invoice_number = $row->inv_number;
			} elseif ( ! isset( $row->invoice_number ) ) {
				$row->invoice_number = 'INV-' . $row->id;
			}
			
			if ( ! isset( $row->tax_amount ) && isset( $row->tax_total ) ) {
				$row->tax_amount = $row->tax_total;
			} elseif ( ! isset( $row->tax_amount ) ) {
				$row->tax_amount = 0;
			}
			
			return $row;
		}
		
		return null;
	}

	/**
	 * Get invoice by hash (fallback safety for redirect)
	 */
	protected static function get_invoice_by_hash( $hash ) {
		global $wpdb;
		if ( empty( $hash ) ) {
			return null;
		}
		
		$row = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}ays_invoices WHERE hash = %s LIMIT 1",
			$hash
		) );
		
		if ( $row ) {
			// Add aliases for schema compatibility
			if ( ! isset( $row->invoice_number ) && isset( $row->inv_number ) ) {
				$row->invoice_number = $row->inv_number;
			} elseif ( ! isset( $row->invoice_number ) ) {
				$row->invoice_number = 'INV-' . $row->id;
			}
			
			if ( ! isset( $row->tax_amount ) && isset( $row->tax_total ) ) {
				$row->tax_amount = $row->tax_total;
			} elseif ( ! isset( $row->tax_amount ) ) {
				$row->tax_amount = 0;
			}
			
			return $row;
		}
		
		return null;
	}

	/**
	 * Add a line item to an invoice
	 */
	public static function handle_add_invoice_item() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
		$invoice_id = isset($_POST['invoice_id']) ? intval($_POST['invoice_id']) : 0;
		check_admin_referer( 'ays_add_invoice_item_' . $invoice_id, 'ays_add_item_nonce' );
		$desc = isset($_POST['description']) ? sanitize_text_field( wp_unslash($_POST['description']) ) : '';
		$qty  = isset($_POST['quantity']) ? floatval( str_replace(',', '.', $_POST['quantity']) ) : 1;
		$rate = isset($_POST['rate']) ? floatval( str_replace(',', '.', $_POST['rate']) ) : 0;
		$taxable = isset($_POST['taxable']) ? 1 : 0;
		if ( ! $invoice_id || $desc === '' ) {
			wp_safe_redirect( add_query_arg( [ 'edit_invoice' => $invoice_id, 'ays_notice' => 'invoice_error' ], admin_url( 'admin.php?page=ays-dashboard&tab=invoices' ) ) );
			exit;
		}
		global $wpdb;
		$table = $wpdb->prefix . 'ays_invoice_items';
		$line_total = round( $qty * $rate, 2 );
		$tax_rate = self::get_tax_rate();
		$line_tax = $taxable ? round( $line_total * $tax_rate, 2 ) : 0.00;
		$wpdb->insert( $table, [
			'hash' => md5( uniqid( 'item_', true ) ),
			'invoice_id' => $invoice_id,
			'description' => $desc,
			'qty' => $qty,
			'rate' => $rate,
			'taxable' => $taxable,
			'line_tax' => $line_tax,
			'line_total' => $line_total,
			'status' => 'active',
			'created_at' => current_time('mysql'),
			'updated_at' => current_time('mysql'),
		], [ '%s','%d','%s','%f','%f','%d','%f','%f','%s','%s','%s' ] );

		self::recalc_invoice_totals( $invoice_id );
		wp_safe_redirect( add_query_arg( [ 'page' => 'ays-dashboard', 'tab' => 'invoices', 'edit_invoice' => $invoice_id ], admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Handle updating a line item
	 */
	public static function handle_update_invoice_item() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
		$item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
		$invoice_id = isset($_POST['invoice_id']) ? intval($_POST['invoice_id']) : 0;
		check_admin_referer( 'ays_update_invoice_item_' . $item_id, 'ays_item_nonce' );
		
		$desc = isset($_POST['description']) ? sanitize_text_field( wp_unslash($_POST['description']) ) : '';
		$qty  = isset($_POST['quantity']) ? floatval( str_replace(',', '.', $_POST['quantity']) ) : 1;
		$rate = isset($_POST['rate']) ? floatval( str_replace(',', '.', $_POST['rate']) ) : 0;
		$taxable = isset($_POST['taxable']) ? 1 : 0;
		
		if ( ! $item_id || ! $invoice_id ) {
			wp_safe_redirect( add_query_arg( [ 'edit_invoice' => $invoice_id, 'ays_notice' => 'invoice_error' ], admin_url( 'admin.php?page=ays-dashboard&tab=invoices' ) ) );
			exit;
		}

		global $wpdb;
		$line_total = round( $qty * $rate, 2 );
		$tax_rate = self::get_tax_rate();
		$line_tax = $taxable ? round( $line_total * $tax_rate, 2 ) : 0.00;

		$wpdb->update(
			$wpdb->prefix . 'ays_invoice_items',
			[
				'description' => $desc,
				'qty' => $qty,
				'rate' => $rate,
				'taxable' => $taxable,
				'line_tax' => $line_tax,
				'line_total' => $line_total,
				'updated_at' => current_time('mysql'),
			],
			[ 'id' => $item_id ],
			[ '%s', '%f', '%f', '%d', '%f', '%f', '%s' ],
			[ '%d' ]
		);

		self::recalc_invoice_totals( $invoice_id );
		wp_safe_redirect( add_query_arg( [ 'page' => 'ays-dashboard', 'tab' => 'invoices', 'edit_invoice' => $invoice_id ], admin_url( 'admin.php' ) ) );
		exit;
	}

	/** Delete a line item */
	public static function handle_delete_invoice_item() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
		$item_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;
		$invoice_id = isset($_GET['invoice_id']) ? intval($_GET['invoice_id']) : 0;
		check_admin_referer( 'ays_delete_invoice_item_' . $item_id );
		global $wpdb;
		$wpdb->delete( $wpdb->prefix.'ays_invoice_items', [ 'id' => $item_id ], [ '%d' ] );
		if ( $invoice_id ) {
			self::recalc_invoice_totals( $invoice_id );
			wp_safe_redirect( add_query_arg( [ 'page' => 'ays-dashboard', 'tab' => 'invoices', 'edit_invoice' => $invoice_id ], admin_url( 'admin.php' ) ) );
			exit;
		}
		wp_safe_redirect( add_query_arg( [ 'page' => 'ays-dashboard', 'tab' => 'invoices' ], admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Recalculate invoice totals from items
	 */
	protected static function recalc_invoice_totals( $invoice_id ) {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT 
			SUM(line_total) AS subtotal,
			SUM(line_tax)   AS tax
			FROM {$wpdb->prefix}ays_invoice_items WHERE invoice_id = %d", $invoice_id ) );
		$subtotal = $row && isset($row->subtotal) ? (float) $row->subtotal : 0.0;
		$tax      = $row && isset($row->tax) ? (float) $row->tax : 0.0;
		$total    = $subtotal + $tax;

		$tax_col = self::get_invoice_tax_column(); // 'tax_total' or 'tax_amount'
		$data = [
			'subtotal' => $subtotal,
			$tax_col   => $tax,
			'total'    => $total,
			'updated_at' => current_time('mysql'),
		];
		$fmt  = [ '%f', '%f', '%f', '%s' ];
		$wpdb->update( $wpdb->prefix.'ays_invoices', $data, [ 'id' => $invoice_id ], $fmt, [ '%d' ] );
	}

	/** Tax rate (default 15%) */
	protected static function get_tax_rate() {
		$rate = get_option('ays_tax_rate');
		if ($rate === false || $rate === '') return 0.15; // NZ GST default
		$rate = floatval($rate);
		if ($rate > 1) { // allow 15 for 15%
			$rate = $rate / 100.0;
		}
		return max(0.0, $rate);
	}

	/** Determine invoice tax column name */
	protected static function get_invoice_tax_column() {
		static $col = null;
		if ($col !== null) return $col;
		global $wpdb;
		$table = $wpdb->prefix . 'ays_invoices';
		$has_tax_amount = (bool) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND COLUMN_NAME = 'tax_amount'",
			$table
		) );
		$col = $has_tax_amount ? 'tax_amount' : 'tax_total';
		return $col;
	}

	/**
	 * Handle create invoice via admin_post
	 *
	 * @return void
	 */
	public static function handle_create_invoice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		if ( ! isset( $_POST['ays_invoice_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ays_invoice_nonce'] ) ), 'ays_create_invoice' ) ) {
			wp_die( esc_html__( 'Security check failed', 'atyourservice' ) );
		}

		global $wpdb;

		$client_id = isset( $_POST['client_id'] ) ? intval( $_POST['client_id'] ) : 0;
		$issue_date = isset( $_POST['issue_date'] ) ? sanitize_text_field( wp_unslash( $_POST['issue_date'] ) ) : date( 'Y-m-d' );
		$due_date = isset( $_POST['due_date'] ) ? sanitize_text_field( wp_unslash( $_POST['due_date'] ) ) : date( 'Y-m-d', strtotime( '+30 days' ) );
		$notes = isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '';

		if ( ! $client_id ) {
			wp_redirect( add_query_arg( 'ays_notice', 'invoice_error', admin_url( 'admin.php?page=ays-dashboard&tab=invoices' ) ) );
			exit;
		}

		// Generate invoice number (e.g., INV-2025-001)
		$last_invoice = $wpdb->get_row( "SELECT MAX(id) as last_id FROM {$wpdb->prefix}ays_invoices" );
		$next_seq = isset($last_invoice->last_id) ? ( (int) $last_invoice->last_id + 1 ) : 1;
		$invoice_number = 'INV-' . date( 'Y' ) . '-' . str_pad( $next_seq, 3, '0', STR_PAD_LEFT );

		// Determine correct column for invoice number (invoice_number vs inv_number)
		$has_invoice_number = (bool) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND COLUMN_NAME = 'invoice_number'",
			$wpdb->prefix . 'ays_invoices'
		) );
		$num_col = $has_invoice_number ? 'invoice_number' : 'inv_number';

		// Choose correct tax column for insert (tax_amount vs tax_total) and include required hash
		$tax_col = self::get_invoice_tax_column();
		$inv_hash = md5( uniqid( 'inv_', true ) );
		$data = [
			'hash'        => $inv_hash,
			$num_col      => $invoice_number,
			'client_id'   => $client_id,
			'issue_date'  => $issue_date,
			'due_date'    => $due_date,
			'subtotal'    => 0,
			$tax_col      => 0,
			'total'       => 0,
			'status'      => 'draft',
			'notes'       => $notes,
			'created_at'  => current_time( 'mysql' ),
			'updated_at'  => current_time( 'mysql' ),
		];
		$formats = [ '%s', '%s', '%d', '%s', '%s', '%f', '%f', '%f', '%s', '%s', '%s', '%s' ];
		$result = $wpdb->insert( "{$wpdb->prefix}ays_invoices", $data, $formats );

		if ( $result ) {
			$invoice_id = $wpdb->insert_id;
			$url = admin_url( 'admin.php?page=ays-dashboard&tab=invoices' );
			$url = add_query_arg( [ 'ays_notice' => 'invoice_created', 'edit_invoice' => $invoice_id, 'inv_hash' => $inv_hash ], $url );
			wp_redirect( $url );
		} else {
			wp_redirect( add_query_arg( 'ays_notice', 'invoice_error', admin_url( 'admin.php?page=ays-dashboard&tab=invoices' ) ) );
		}
		exit;
	}

	/**
	 * Handle delete invoice via admin_post
	 *
	 * @return void
	 */
	public static function handle_delete_invoice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		$invoice_id = isset( $_GET['invoice_id'] ) ? intval( $_GET['invoice_id'] ) : 0;
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'ays_delete_invoice_' . $invoice_id ) ) {
			wp_die( esc_html__( 'Security check failed', 'atyourservice' ) );
		}

		global $wpdb;
		// Delete invoice items first
		$wpdb->delete( "{$wpdb->prefix}ays_invoice_items", [ 'invoice_id' => $invoice_id ], [ '%d' ] );
		// Delete invoice
		$result = $wpdb->delete( "{$wpdb->prefix}ays_invoices", [ 'id' => $invoice_id ], [ '%d' ] );

		if ( $result ) {
			wp_redirect( add_query_arg( 'ays_notice', 'invoice_deleted', admin_url( 'admin.php?page=ays-dashboard&tab=invoices' ) ) );
		} else {
			wp_redirect( add_query_arg( 'ays_notice', 'invoice_error', admin_url( 'admin.php?page=ays-dashboard&tab=invoices' ) ) );
		}
		exit;
	}

	/**
	 * Save invoice service type selections (bridge table)
	 */
	public static function handle_update_invoice_services() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		$invoice_id = isset( $_POST['invoice_id'] ) ? intval( $_POST['invoice_id'] ) : 0;
		if ( ! $invoice_id ) {
			wp_die( esc_html__( 'Invalid invoice.', 'atyourservice' ) );
		}
		// Nonce check (dies with message on failure)
		check_admin_referer( 'ays_update_invoice_services_' . $invoice_id, 'ays_invoice_services_nonce' );

		$service_ids = isset( $_POST['service_type_ids'] ) && is_array( $_POST['service_type_ids'] ) ? array_map( 'intval', (array) $_POST['service_type_ids'] ) : [];

		global $wpdb;
		$table = $wpdb->prefix . 'ays_invoice_services';
		// Clear existing
		$wpdb->delete( $table, [ 'invoice_id' => $invoice_id ], [ '%d' ] );
		// Insert new
		foreach ( $service_ids as $sid ) {
			$wpdb->insert( $table, [
				'hash' => md5( uniqid( mt_rand(), true ) ),
				'invoice_id' => $invoice_id,
				'service_type_id' => $sid,
				'sort_order' => 0,
			], [ '%s', '%d', '%d', '%d' ] );
		}

		wp_redirect( add_query_arg( [ 'edit_invoice' => $invoice_id, 'ays_notice' => 'invoice_services_updated' ], admin_url( 'admin.php?page=ays-dashboard&tab=invoices' ) ) );
		exit;
	}

	/**
	 * Get service type IDs attached to an invoice
	 * @param int $invoice_id
	 * @return int[]
	 */
	protected static function get_invoice_service_type_ids( $invoice_id ) {
		global $wpdb;
		$rows = $wpdb->get_col( $wpdb->prepare( "SELECT service_type_id FROM {$wpdb->prefix}ays_invoice_services WHERE invoice_id = %d", $invoice_id ) );
		return array_map( 'intval', (array) $rows );
	}

	/**
	 * Get service type labels or ids attached to an invoice
	 * @param int  $invoice_id
	 * @param bool $as_names If true return names, else IDs
	 * @return array
	 */
	protected static function get_invoice_service_types( $invoice_id, $as_names = true ) {
		global $wpdb;
		if ( $as_names ) {
			return $wpdb->get_col( $wpdb->prepare(
				"SELECT st.name FROM {$wpdb->prefix}ays_invoice_services ivs
				 JOIN {$wpdb->prefix}ays_service_types st ON st.id = ivs.service_type_id
				 WHERE ivs.invoice_id = %d ORDER BY st.sort_order, st.name",
				$invoice_id
			) );
		}
		return self::get_invoice_service_type_ids( $invoice_id );
	}

	/**
	 * Render chips for invoice service types
	 */
	protected static function render_service_type_chips( $invoice_id ) {
		$names = self::get_invoice_service_types( $invoice_id, true );
		if ( empty( $names ) ) {
			echo '<span style="color:#6b7280">—</span>';
			return;
		}
		foreach ( $names as $name ) {
			echo '<span class="ays-chip"><span class="dot"></span>' . esc_html( $name ) . '</span>';
		}
	}

	/**
	 * Handle updating invoice client (admin-post action)
	 */
	public static function handle_update_invoice_client() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		$invoice_id = isset( $_POST['invoice_id'] ) ? intval( $_POST['invoice_id'] ) : 0;
		check_admin_referer( 'ays_update_invoice_client_' . $invoice_id );

		$client_id = isset( $_POST['client_id'] ) ? intval( $_POST['client_id'] ) : 0;

		global $wpdb;
		$wpdb->update(
			$wpdb->prefix . 'ays_invoices',
			[ 'client_id' => $client_id ],
			[ 'id' => $invoice_id ],
			[ '%d' ],
			[ '%d' ]
		);

		wp_safe_redirect( add_query_arg(
			[
				'page'         => 'ays-dashboard',
				'tab'          => 'invoices',
				'edit_invoice' => $invoice_id,
				'ays_notice'   => 'invoice_client_updated',
			],
			admin_url( 'admin.php' )
		) );
		exit;
	}

	/**
	 * Handle creating a new client from the invoice editor and assigning it
	 */
	public static function handle_add_client_from_invoice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		$invoice_id = isset( $_POST['invoice_id'] ) ? intval( $_POST['invoice_id'] ) : 0;
		check_admin_referer( 'ays_add_client_from_invoice_' . $invoice_id );

		$name  = isset( $_POST['client_name'] ) ? sanitize_text_field( wp_unslash( $_POST['client_name'] ) ) : '';
		$email = isset( $_POST['client_email'] ) ? sanitize_email( wp_unslash( $_POST['client_email'] ) ) : '';
		$phone = isset( $_POST['client_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['client_phone'] ) ) : '';

		if ( empty( $name ) ) {
			wp_safe_redirect( add_query_arg(
				[
					'page'         => 'ays-dashboard',
					'tab'          => 'invoices',
					'edit_invoice' => $invoice_id,
					'ays_notice'   => 'invoice_error',
				],
				admin_url( 'admin.php' )
			) );
			exit;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'ays_clients';

		// Check email uniqueness (if provided)
		if ( ! empty( $email ) ) {
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE email = %s", $email ) );
			if ( $exists ) {
				wp_safe_redirect( add_query_arg(
					[
						'page'         => 'ays-dashboard',
						'tab'          => 'invoices',
						'edit_invoice' => $invoice_id,
						'ays_notice'   => 'invoice_error',
					],
					admin_url( 'admin.php' )
				) );
				exit;
			}
		}

		$data = [
			'hash'       => md5( uniqid( 'client_', true ) ),
			'name'       => $name,
			'email'      => $email,
			'phone'      => $phone,
			'status'     => 'active',
			'created_at' => current_time( 'mysql' ),
			'updated_at' => current_time( 'mysql' ),
		];

		$result = $wpdb->insert( $table, $data );

		if ( $result ) {
			$client_id = $wpdb->insert_id;
			
			// Update the invoice to use this new client
			if ( $invoice_id ) {
				$wpdb->update(
					$wpdb->prefix . 'ays_invoices',
					[ 'client_id' => $client_id ],
					[ 'id' => $invoice_id ],
					[ '%d' ],
					[ '%d' ]
				);
				wp_safe_redirect( add_query_arg(
					[
						'page'         => 'ays-dashboard',
						'tab'          => 'invoices',
						'edit_invoice' => $invoice_id,
						'ays_notice'   => 'invoice_client_created',
					],
					admin_url( 'admin.php' )
				) );
				exit;
			}
		}

		// Fallback
		wp_safe_redirect( add_query_arg(
			[
				'page'         => 'ays-dashboard',
				'tab'          => 'invoices',
				'edit_invoice' => $invoice_id,
				'ays_notice'   => 'invoice_error',
			],
			admin_url( 'admin.php' )
		) );
		exit;
	}

	/**
	 * Handle marking an invoice as paid
	 */
	public static function handle_mark_invoice_paid() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		$invoice_id = isset( $_GET['invoice_id'] ) ? intval( $_GET['invoice_id'] ) : 0;
		if ( ! $invoice_id ) {
			wp_die( esc_html__( 'Invalid invoice', 'atyourservice' ) );
		}

		global $wpdb;
		$wpdb->update(
			$wpdb->prefix . 'ays_invoices',
			[ 'status' => 'paid', 'updated_at' => current_time( 'mysql' ) ],
			[ 'id' => $invoice_id ],
			[ '%s', '%s' ],
			[ '%d' ]
		);

		wp_safe_redirect( add_query_arg(
			[
				'page'         => 'ays-dashboard',
				'tab'          => 'invoices',
				'edit_invoice' => $invoice_id,
				'ays_notice'   => 'invoice_marked_paid',
			],
			admin_url( 'admin.php' )
		) );
		exit;
	}

	/**
	 * Handle voiding an invoice
	 */
	public static function handle_void_invoice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		$invoice_id = isset( $_GET['invoice_id'] ) ? intval( $_GET['invoice_id'] ) : 0;
		if ( ! $invoice_id ) {
			wp_die( esc_html__( 'Invalid invoice', 'atyourservice' ) );
		}

		global $wpdb;
		$wpdb->update(
			$wpdb->prefix . 'ays_invoices',
			[ 'status' => 'void', 'updated_at' => current_time( 'mysql' ) ],
			[ 'id' => $invoice_id ],
			[ '%s', '%s' ],
			[ '%d' ]
		);

		wp_safe_redirect( add_query_arg(
			[
				'page'         => 'ays-dashboard',
				'tab'          => 'invoices',
				'edit_invoice' => $invoice_id,
				'ays_notice'   => 'invoice_voided',
			],
			admin_url( 'admin.php' )
		) );
		exit;
	}

	/**
	 * Handle sending invoice email
	 */
	public static function handle_send_invoice_email() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		$invoice_id = isset( $_GET['invoice_id'] ) ? intval( $_GET['invoice_id'] ) : 0;
		if ( ! $invoice_id ) {
			wp_die( esc_html__( 'Invalid invoice', 'atyourservice' ) );
		}

		$invoice = self::get_invoice( $invoice_id );
		if ( ! $invoice ) {
			wp_die( esc_html__( 'Invoice not found', 'atyourservice' ) );
		}

		global $wpdb;
		$client = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ays_clients WHERE id = %d", $invoice->client_id ) );
		if ( ! $client || empty( $client->email ) ) {
			wp_safe_redirect( add_query_arg(
				[
					'page'         => 'ays-dashboard',
					'tab'          => 'invoices',
					'edit_invoice' => $invoice_id,
					'ays_notice'   => 'invoice_error',
				],
				admin_url( 'admin.php' )
			) );
			exit;
		}

		// Mark as sent if it was draft
		if ( $invoice->status === 'draft' ) {
			$wpdb->update(
				$wpdb->prefix . 'ays_invoices',
				[ 'status' => 'sent', 'updated_at' => current_time( 'mysql' ) ],
				[ 'id' => $invoice_id ],
				[ '%s', '%s' ],
				[ '%d' ]
			);
		}

		// Send email using AYS_Notifier if available
		if ( class_exists( 'AYS_Notifier' ) ) {
			$result = AYS_Notifier::send_invoice_email( $invoice_id, $client->email );
			$notice = $result ? 'invoice_sent' : 'invoice_error';
		} else {
			$notice = 'invoice_error';
		}

		wp_safe_redirect( add_query_arg(
			[
				'page'         => 'ays-dashboard',
				'tab'          => 'invoices',
				'edit_invoice' => $invoice_id,
				'ays_notice'   => $notice,
			],
			admin_url( 'admin.php' )
		) );
		exit;
	}

	/**
	 * Render the preview tab for invoice editor
	 *
	 * @param array $args Associative array of preview data.
	 * @return void
	 */
	protected static function render_preview_tab( $args ) {
		extract( wp_parse_args( $args, [
			'invoice'          => null,
			'items'            => [],
			'client'           => null,
			'preferences'      => [],
			'company_profile'  => [],
			'company_logo_url' => '',
			'payments'         => [],
			'recent_payments'  => [],
			'currency_symbol'  => '$',
			'format_money'     => null,
			'issue_date_label' => '—',
			'due_date_label'   => '—',
			'due_delta'        => '',
			'total_paid'       => 0.0,
			'balance'          => 0.0,
			'warnings'         => [],
		] ) );

		$primary_color  = isset( $preferences['primary_color'] ) ? sanitize_hex_color( $preferences['primary_color'] ) : '#4c51bf';
		$secondary_color = isset( $preferences['accent_color'] ) ? sanitize_hex_color( $preferences['accent_color'] ) : '#111827';

		?>
		<div class="ays-invoice-preview-container" style="background:#f9fafb;padding:20px;border-radius:6px;margin-top:12px;">
			<?php if ( ! empty( $warnings ) ) : ?>
				<div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:4px;padding:12px;margin-bottom:16px;color:#92400e;">
					<strong>⚠️ <?php esc_html_e( 'Before Sending:', 'atyourservice' ); ?></strong>
					<ul style="margin:8px 0 0;padding-left:20px;">
						<?php foreach ( $warnings as $warning ) : ?>
							<li><?php echo esc_html( $warning ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<div class="ays-preview-box" style="background:white;border-radius:6px;box-shadow:0 1px 3px rgba(0,0,0,0.1);padding:20px;">
				<!-- Header: Logo & Title -->
				<div style="display:grid;grid-template-columns:auto 1fr;gap:20px;align-items:start;margin-bottom:24px;padding-bottom:16px;border-bottom:2px solid #e5e7eb;">
					<?php if ( ! empty( $company_logo_url ) ) : ?>
						<img src="<?php echo esc_url( $company_logo_url ); ?>" style="max-width:120px;height:auto;" />
					<?php endif; ?>
					<div>
						<h1 style="margin:0 0 8px;color:<?php echo esc_attr( $primary_color ); ?>;font-size:32px;">INVOICE</h1>
						<p style="margin:0;color:#6b7280;font-size:14px;">
							<?php esc_html_e( 'Invoice #', 'atyourservice' ); ?><strong><?php echo esc_html( $invoice->invoice_number ?? '—' ); ?></strong>
							<?php if ( ! empty( $issue_date_label ) ) : ?>
								<br /><small><?php esc_html_e( 'Issued:', 'atyourservice' ); ?> <?php echo esc_html( $issue_date_label ); ?></small>
							<?php endif; ?>
						</p>
					</div>
				</div>

				<!-- From / To Section -->
				<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;padding-bottom:16px;border-bottom:1px solid #e5e7eb;">
					<div>
						<h4 style="margin:0 0 8px;color:#111827;font-size:13px;text-transform:uppercase;letter-spacing:.5px;"><?php esc_html_e( 'From:', 'atyourservice' ); ?></h4>
						<p style="margin:0;font-size:14px;">
							<?php if ( ! empty( $company_profile['company_name'] ) ) : ?>
								<strong><?php echo esc_html( $company_profile['company_name'] ); ?></strong><br />
							<?php endif; ?>
							<?php if ( ! empty( $company_profile['company_address'] ) ) : ?>
								<?php echo nl2br( esc_html( $company_profile['company_address'] ) ); ?><br />
							<?php endif; ?>
							<?php if ( ! empty( $company_profile['company_email'] ) ) : ?>
								<?php echo esc_html( $company_profile['company_email'] ); ?><br />
							<?php endif; ?>
							<?php if ( ! empty( $company_profile['company_phone'] ) ) : ?>
								<?php echo esc_html( $company_profile['company_phone'] ); ?>
							<?php endif; ?>
						</p>
					</div>
					<div>
						<h4 style="margin:0 0 8px;color:#111827;font-size:13px;text-transform:uppercase;letter-spacing:.5px;"><?php esc_html_e( 'Bill To:', 'atyourservice' ); ?></h4>
						<p style="margin:0;font-size:14px;">
							<?php if ( $client ) : ?>
								<strong><?php echo esc_html( $client->name ?? '—' ); ?></strong><br />
								<?php if ( ! empty( $client->email ) ) : ?>
									<?php echo esc_html( $client->email ); ?><br />
								<?php endif; ?>
								<?php if ( ! empty( $client->phone ) ) : ?>
									<?php echo esc_html( $client->phone ); ?>
								<?php endif; ?>
							<?php else : ?>
								<span style="color:#6b7280;">—</span>
							<?php endif; ?>
						</p>
					</div>
				</div>

				<!-- Key Dates -->
				<div style="display:grid;grid-template-columns:repeat(2, 1fr);gap:16px;margin-bottom:24px;padding:12px;background:#f3f4f6;border-radius:4px;font-size:13px;">
					<div>
						<strong style="color:#111827;"><?php esc_html_e( 'Issue Date', 'atyourservice' ); ?></strong><br />
						<span style="color:#6b7280;"><?php echo esc_html( $issue_date_label ?? '—' ); ?></span>
					</div>
					<div>
						<strong style="color:#111827;"><?php esc_html_e( 'Due Date', 'atyourservice' ); ?></strong><br />
						<span style="color:#6b7280;"><?php echo esc_html( $due_date_label ?? '—' ); ?></span>
						<?php if ( ! empty( $due_delta ) ) : ?>
							<br /><small style="color:<?php echo strpos( $due_delta, 'overdue' ) !== false ? '#dc2626' : '#059669'; ?>;"><?php echo esc_html( $due_delta ); ?></small>
						<?php endif; ?>
					</div>
				</div>

				<!-- Line Items -->
				<table style="width:100%;border-collapse:collapse;margin-bottom:20px;">
					<thead>
						<tr style="background:#f3f4f6;border-bottom:2px solid #d1d5db;">
							<th style="text-align:left;padding:10px;color:<?php echo esc_attr( $primary_color ); ?>;font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:.5px;"><?php esc_html_e( 'Description', 'atyourservice' ); ?></th>
							<th style="text-align:center;padding:10px;color:<?php echo esc_attr( $primary_color ); ?>;font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:.5px;">Qty</th>
							<th style="text-align:right;padding:10px;color:<?php echo esc_attr( $primary_color ); ?>;font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:.5px;">Rate</th>
							<th style="text-align:right;padding:10px;color:<?php echo esc_attr( $primary_color ); ?>;font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:.5px;">Amount</th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $items ) ) : ?>
							<tr><td colspan="4" style="text-align:center;padding:20px;color:#6b7280;">— <?php esc_html_e( 'No line items', 'atyourservice' ); ?> —</td></tr>
						<?php else : ?>
							<?php foreach ( $items as $item ) : ?>
								<tr style="border-bottom:1px solid #e5e7eb;">
									<td style="padding:12px;color:#111827;"><?php echo esc_html( $item->description ?? '—' ); ?></td>
									<td style="text-align:center;padding:12px;color:#111827;">×<?php echo esc_html( $item->quantity ?? 0 ); ?></td>
									<td style="text-align:right;padding:12px;color:#111827;"><?php echo call_user_func( $format_money ?? static function( $a ) { return '$' . number_format( $a, 2 ); }, $item->rate ?? 0 ); ?></td>
									<td style="text-align:right;padding:12px;color:#111827;"><?php echo call_user_func( $format_money ?? static function( $a ) { return '$' . number_format( $a, 2 ); }, ( $item->quantity ?? 0 ) * ( $item->rate ?? 0 ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>

				<!-- Totals -->
				<div style="display:grid;grid-template-columns:auto 1fr;gap:16px;justify-content:flex-end;margin-bottom:24px;padding-top:16px;border-top:2px solid #e5e7eb;">
					<div style="display:grid;grid-template-columns:140px auto;gap:16px;align-items:center;">
						<strong style="text-align:right;color:#111827;"><?php esc_html_e( 'Subtotal:', 'atyourservice' ); ?></strong>
						<span style="text-align:right;color:#111827;"><?php echo call_user_func( $format_money ?? static function( $a ) { return '$' . number_format( $a, 2 ); }, $invoice->subtotal ?? 0 ); ?></span>
					</div>
					<?php if ( ! empty( $invoice->tax_amount ) && $invoice->tax_amount > 0 ) : ?>
						<div style="display:grid;grid-template-columns:140px auto;gap:16px;align-items:center;">
							<strong style="text-align:right;color:#111827;"><?php esc_html_e( 'Tax:', 'atyourservice' ); ?></strong>
							<span style="text-align:right;color:#111827;"><?php echo call_user_func( $format_money ?? static function( $a ) { return '$' . number_format( $a, 2 ); }, $invoice->tax_amount ?? 0 ); ?></span>
						</div>
					<?php endif; ?>
					<div style="display:grid;grid-template-columns:140px auto;gap:16px;align-items:center;padding-top:12px;border-top:1px solid #d1d5db;font-weight:700;font-size:16px;">
						<strong style="text-align:right;color:<?php echo esc_attr( $primary_color ); ?>;"><?php esc_html_e( 'Total:', 'atyourservice' ); ?></strong>
						<span style="text-align:right;color:<?php echo esc_attr( $primary_color ); ?>;"><?php echo call_user_func( $format_money ?? static function( $a ) { return '$' . number_format( $a, 2 ); }, $invoice->total ?? 0 ); ?></span>
					</div>
				</div>

				<!-- Payment Status -->
				<?php if ( ! empty( $payments ) ) : ?>
					<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:4px;padding:12px;margin-bottom:16px;">
						<strong style="color:#166534;"><?php esc_html_e( '✓ Payment Status', 'atyourservice' ); ?></strong>
						<div style="margin-top:8px;font-size:13px;">
							<div><strong><?php esc_html_e( 'Paid:', 'atyourservice' ); ?></strong> <?php echo call_user_func( $format_money ?? static function( $a ) { return '$' . number_format( $a, 2 ); }, $total_paid ); ?></div>
							<?php if ( $balance > 0 ) : ?>
								<div><strong><?php esc_html_e( 'Outstanding:', 'atyourservice' ); ?></strong> <?php echo call_user_func( $format_money ?? static function( $a ) { return '$' . number_format( $a, 2 ); }, $balance ); ?></div>
							<?php endif; ?>
							<?php if ( ! empty( $recent_payments ) ) : ?>
								<div style="margin-top:8px;font-size:12px;">
									<strong><?php esc_html_e( 'Recent payments:', 'atyourservice' ); ?></strong>
									<ul style="margin:4px 0;padding-left:20px;">
										<?php foreach ( $recent_payments as $p ) : ?>
											<li><?php echo call_user_func( $format_money ?? static function( $a ) { return '$' . number_format( $a, 2 ); }, $p['amount'] ?? 0 ); ?> — <?php echo esc_html( isset( $p['date'] ) ? date_i18n( get_option( 'date_format' ), strtotime( $p['date'] ) ) : '—' ); ?></li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>

				<!-- Bank Transfer Details (if available) -->
				<?php if ( class_exists( 'AYS_Company_Profile' ) ) : ?>
					<?php echo AYS_Company_Profile::get_bank_transfer_html( $invoice->invoice_number ?? 'INV-' . $invoice->id ); // Already escaped ?>
				<?php endif; ?>
			</div>
		</div>

		<style>
			@media print {
				.editor-header, .nav-tab-wrapper, .button { display: none; }
			}
		</style>
		<?php
	}
}
