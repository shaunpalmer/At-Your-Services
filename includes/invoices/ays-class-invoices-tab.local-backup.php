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
	 * Invoice status constants with labels and icons.
	 */
	protected static function render_invoice_preview( $invoice, $items, $client, $preferences = [], $currency_symbol = '$' ) {
		$company = class_exists( 'AYS_Company_Profile' ) ? AYS_Company_Profile::get_profile() : [
			'company_name'    => get_bloginfo( 'name' ),
			'company_address' => '',
			'company_email'   => get_option( 'admin_email' ),
			'company_phone'   => '',
		];
		if ( empty( $preferences ) && class_exists( 'AYS_Invoice_Preferences' ) ) {
			$preferences = AYS_Invoice_Preferences::get( $invoice->id );
		}

		$doc_map = [
			'invoice' => __( 'Invoice', 'atyourservice' ),
			'quote'   => __( 'Quote', 'atyourservice' ),
			'receipt' => __( 'Receipt', 'atyourservice' ),
		];
		$document_type    = isset( $preferences['document_type'] ) ? sanitize_key( $preferences['document_type'] ) : 'invoice';
		$document_heading = strtoupper( $doc_map[ $document_type ] ?? __( 'Invoice', 'atyourservice' ) );
		$preview_primary  = isset( $preferences['primary_color'] ) ? sanitize_hex_color( $preferences['primary_color'] ) : '#4c51bf';
		class AYS_Invoices_Tab {

			/**
			 * Invoice status constants with labels and icons.
			 */
						>
							<tr>
								<th scope="row">
									<label for="issue_date"><?php esc_html_e( 'Issue Date', 'atyourservice' ); ?></label>
								</th>
								<td>
									<input 
										type="date" 
										id="issue_date" 
										name="issue_date" 
										value="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>"
										class="regular-text"
										style="width: 200px;"
									>
								</td>
							</tr>
	/**
	 * Render admin invoice preview box content
	 *
	 * @param object $invoice Current invoice
	 * @param array  $items   Invoice items from DB
	 * @param object $client  Client row
	 * @param array  $preferences Optional preferences array.
	 * @param string $currency_symbol Currency symbol for display.
	 * @return void
	 */
	protected static function render_invoice_preview( $invoice, $items, $client, $preferences = [], $currency_symbol = '$' ) {
		$company = class_exists( 'AYS_Company_Profile' ) ? AYS_Company_Profile::get_profile() : [
			'company_name'   => get_bloginfo( 'name' ),
			'company_address'=> '',
			'company_email'  => get_option( 'admin_email' ),
			'company_phone'  => '',
		];
		if ( empty( $preferences ) && class_exists( 'AYS_Invoice_Preferences' ) ) {
			$preferences = AYS_Invoice_Preferences::get( $invoice->id );
		}
		$doc_map = [
			'invoice' => __( 'Invoice', 'atyourservice' ),
			'quote'   => __( 'Quote', 'atyourservice' ),
			'receipt' => __( 'Receipt', 'atyourservice' ),
		];
		$document_type   = isset( $preferences['document_type'] ) ? sanitize_key( $preferences['document_type'] ) : 'invoice';
		$document_heading = strtoupper( $doc_map[ $document_type ] ?? __( 'Invoice', 'atyourservice' ) );
		$preview_primary = isset( $preferences['primary_color'] ) ? sanitize_hex_color( $preferences['primary_color'] ) : '#4c51bf';
		$preview_primary = $preview_primary ?: '#4c51bf';
		$company_name    = $company['company_name'] ?? get_bloginfo( 'name' );
		$company_address = ! empty( $company['company_address'] ) ? nl2br( esc_html( $company['company_address'] ) ) : '';
		$company_email   = $company['company_email'] ?? '';
		$company_phone   = $company['company_phone'] ?? '';
		$client_name     = isset( $client->name ) ? $client->name : '—';
		$client_email    = isset( $client->email ) ? $client->email : '';
		$client_phone    = isset( $client->phone ) ? $client->phone : '';
		$issue_date      = ! empty( $invoice->issue_date ) ? date_i18n( 'Y-m-d', strtotime( $invoice->issue_date ) ) : '—';
		$due_date        = ! empty( $invoice->due_date ) ? date_i18n( 'Y-m-d', strtotime( $invoice->due_date ) ) : '—';
		$invoice_number  = ! empty( $invoice->invoice_number ) ? $invoice->invoice_number : ( ! empty( $invoice->inv_number ) ? $invoice->inv_number : 'INV-' . (int) $invoice->id );
		$money_cb        = static function( $amount ) use ( $currency_symbol ) {
			return $currency_symbol . number_format_i18n( (float) $amount, 2 );
		};
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
	 * Render admin invoice preview box content
	 *
	 * @param object $invoice Current invoice
	 * @param array  $items   Invoice items from DB
	 * @param object $client  Client row
	 * @param array  $preferences Optional preferences array.
	 * @param string $currency_symbol Currency symbol for display.
	 * @return void
	 */
		?>
		<div class="ays-invoice-preview-header">
			<div class="ays-invoice-preview-from">
				<h3><?php esc_html_e( 'From:', 'atyourservice' ); ?></h3>
				<p><strong><?php echo esc_html( $company_name ); ?></strong><br/>
					<?php echo $company_address ? $company_address . '<br/>' : ''; ?>
					<?php echo $company_email ? esc_html( $company_email ) . '<br/>' : ''; ?>
					<?php echo $company_phone ? esc_html( $company_phone ) : ''; ?></p>
			</div>
			<div style="text-align:right;">
				<h1 style="margin:0 0 10px;color:<?php echo esc_attr( $preview_primary ); ?>;">&nbsp;<?php echo esc_html( $document_heading ); ?></h1>
				<p style="margin:0;">
					<?php esc_html_e( 'Invoice #', 'atyourservice' ); ?> <strong><?php echo esc_html( $invoice_number ); ?></strong><br/>
					<small style="color:#6b7280;">&nbsp;<?php esc_html_e( 'Issued:', 'atyourservice' ); ?> <?php echo esc_html( $issue_date ); ?></small>
				</p>
			</div>
		</div>

		<div class="ays-invoice-preview-header">
			<div class="ays-invoice-preview-to">
				<h3><?php esc_html_e( 'Bill To:', 'atyourservice' ); ?></h3>
				<p><strong><?php echo esc_html( $client_name ); ?></strong><br/>
					<?php echo $client_email ? esc_html( $client_email ) . '<br/>' : ''; ?>
					<?php echo $client_phone ? esc_html( $client_phone ) : ''; ?></p>
			</div>
			<div style="text-align:right;">
				<table style="margin:0 auto;font-size:13px;">
					<tr>
						<td style="padding:4px 20px 4px 0;text-align:right;color:#6b7280;">
							<?php esc_html_e( 'Due Date:', 'atyourservice' ); ?> <strong><?php echo esc_html( $due_date ); ?></strong>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<table class="ays-invoice-preview-items" style="--ays-preview-accent: <?php echo esc_attr( $preview_primary ); ?>;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Description', 'atyourservice' ); ?></th>
					<th style="text-align:center;">Qty</th>
					<th style="text-align:right;">Rate</th>
					<th style="text-align:right;">Amount</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $items ) ) : ?>
					<tr><td colspan="4" style="text-align:center;color:#6b7280;">— <?php esc_html_e( 'No items yet', 'atyourservice' ); ?> —</td></tr>
				<?php else : foreach ( $items as $it ) :
					$line_total = (float) ( $it->quantity * $it->rate );
				?>
					<tr>
						<td><?php echo esc_html( $it->description ); ?></td>
						<td style="text-align:center;">&times;<?php echo esc_html( $it->quantity ); ?></td>
						<td style="text-align:right;"><?php echo esc_html( $money_cb( $it->rate ) ); ?></td>
						<td style="text-align:right;"><?php echo esc_html( $money_cb( $line_total ) ); ?></td>
					</tr>
				<?php endforeach; endif; ?>
			</tbody>
		</table>

		<div class="ays-invoice-preview-total">
			<div style="grid-column:1 / -1;display:grid;grid-template-columns:auto 1fr;gap:20px;justify-content:flex-end;">
				<div class="ays-invoice-preview-total-row">
					<strong style="text-align:right;">Subtotal:</strong>
					<span style="text-align:right;"><?php echo esc_html( $money_cb( $invoice->subtotal ) ); ?></span>
				</div>
				<div class="ays-invoice-preview-total-row">
					<strong style="text-align:right;">Tax:</strong>
					<span style="text-align:right;"><?php echo esc_html( $money_cb( $invoice->tax_amount ) ); ?></span>
				</div>
				<div class="ays-invoice-preview-total-row final">
					<strong style="text-align:right;">TOTAL:</strong>
					<span style="text-align:right;"><?php echo esc_html( $money_cb( $invoice->total ) ); ?></span>
				</div>
			</div>
		</div>

		<?php if ( class_exists( 'AYS_Company_Profile' ) ) : ?>
			<div style="margin-top:12px;">
				<?php echo AYS_Company_Profile::get_bank_transfer_html( $invoice_number ); // already escaped ?>
			</div>
		<?php endif; ?>
				<table style="margin:0 auto;font-size:13px;">
					<tr>
						<td style="padding:4px 20px 4px 0;text-align:right;color:#6b7280;">
							<?php esc_html_e( 'Due Date:', 'atyourservice' ); ?> <strong><?php echo esc_html( $due_date ); ?></strong>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<table class="ays-invoice-preview-items" style="--ays-preview-accent: <?php echo esc_attr( $preview_primary ); ?>;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Description', 'atyourservice' ); ?></th>
					<th style="text-align:center;">Qty</th>
					<th style="text-align:right;">Rate</th>
					<th style="text-align:right;">Amount</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $items ) ) : ?>
					<tr><td colspan="4" style="text-align:				<summary>
( $items as $it ) :
					$line_total = (float) ( $it->quantity * $it->rate );
				?>
					<tr>
						<td><?php echo esc_html( $it->description ); ?></td>
						<td style="text-align:center;">&times;<?php echo esc_html( $it->quantity ); ?></td>
						<td style="text-align:right;"><?php echo esc_html( $money_cb( $it->rate ) ); ?></td>
						<td style="text-align:right;"><?php echo esc_html( $money_cb( $line_total ) ); ?></td>
					</tr>k_transfer_html( $invoice_number ); // already escaped ?>
			</div>
		<?php endif; ?>
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
																		<?phth><?php esc_html_e( 'Due Date', 'atyourservice' ); ?></thho esc_html( $client->name ); ?>
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
									<input 
										type="date" 
								 $wpdb->get_results( $wpdb->prepare(
			"SELECT ii.*, ii.qty AS quantity FROM {$wpdb->prefix}ays_invoice_items ii WHERE ii.invoice_id = %d ORDER BY ii.created_at",
			$invoice->id
		) );

		$clients = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}ays_clients WHERE status = 'active' ORDER BY name" );
		$client = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ays_clients WHERE id = %d", $invoice->client_id ) );
		$company_profile = class_exists( 'AYS_Company_Profile' ) ? AYS_Company_Profile::get_profile() : [];
		$company_logo_id = isset( $company_profile['company_logo_id'] ) ? intval( $company_profile['company_logo_id'] ) : 0;
		$company_logo_url = $company_logo_id ? wp_get_attachment_image_url( $company_logo_id, 'medium' ) : '';

		$invoice_repo   = class_exists( 'AYS_Invoice_Repository' ) ? new AYS_Invoice_Repository() : null;
		$payments       = $invoice_repo ? $invoice_repo->get_invoice_payments( $invoice->id ) : [];
		$total_paid     = 0.0;
		foreach ( (array) $payments as $payment_row ) {
			$total_paid += isset( $payment_row['amount'] ) ? (float) $payment_row['amount'] : 0.0;
		}
		$balance        = max( 0, (float) $invoice->total - $total_paid );
		$progress       = (float) $invoice->total > 0 ? min( 100, round( ( $total_paid / (float) $invoice->total ) * 100 ) ) : 0;
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
					<!-- Top Section: From / Bill To -->
					<div class="invoice-section" style="display:grid; grid-template-columns: 1fr 1fr; gap:24px; margin-bottom:24px;">
						<!-- From (Company) -->
						<div class="ays-company-profile-box">
							<h3><?php esc_html_e( 'From', 'atyourservice' ); ?></h3>
							<?php 
							// Simple display for now, could be quick edit form later
							$company_name = get_option( 'blogname' );
							$admin_email = get_option( 'admin_email' );
							?>
							<p><strong><?php echo esc_html( $company_name ); ?></strong><br>
							<?php echo esc_html( $admin_email ); ?></p>
							<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=ays-dashboard&tab=settings' ) ); ?>" class="button button-small"><?php esc_html_e( 'Edit Profile', 'atyourservice' ); ?></a></p>
						</div>

						<!-- Bill To (Client) -->
						<div class="ays-client-select-box">
							<h3><?php esc_html_e( 'Bill To', 'atyourservice' ); ?></h3>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
								<input type="hidden" name="action" value="ays_update_invoice_client" />
								<input type="hidden" name="invoice_id" value="<?php echo esc_attr( $invoice->id ); ?>" />
								<?php wp_nonce_field( 'ays_update_invoice_client_' . $invoice->id ); ?>
								
								<div style="display:flex; gap:8px; margin-bottom:8px;">
									<select name="client_id" style="width:100%;">
										<option value=""><?php esc_html_e( '— Select Client —', 'atyourservice' ); ?></option>
										<?php foreach ( $clients as $c ) : ?>
											<option value="<?php echo esc_attr( $c->id ); ?>" <?php selected( $invoice->client_id, $c->id ); ?>>
												<?php echo esc_html( $c->name ); ?>
											</option>
										<?php endforeach; ?>
									</select>
									<button type="submit" class="button"><?php esc_html_e( 'Save', 'atyourservice' ); ?></button>
								</div>
							</form>

							<!-- Inline Create Client Trigger -->
							<details>
								<summary style="cursor:pointer; color:#2271b1;"><?php esc_html_e( '+ Create New Client', 'atyourservice' ); ?></summary>
								<div style="padding:12px; background:#f0f0f1; border-radius:4px; margin-top:8px;">
									<!-- Simple form posting to admin_post_ays_add_client -->
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
										<input type="hidden" name="action" value="ays_add_client" />
										<input type="hidden" name="redirect_to_invoice" value="<?php echo esc_attr( $invoice->id ); ?>" />
										<?php wp_nonce_field( 'ays_add_client_nonce' ); ?>
										
										<p>
											<input type="text" name="name" placeholder="<?php esc_attr_e( 'Client Name *', 'atyourservice' ); ?>" required style="width:100%;" />
										</p>
										<p>
											<input type="email" name="email" placeholder="<?php esc_attr_e( 'Email', 'atyourservice' ); ?>" style="width:100%;" />
										</p>
										<button type="submit" class="button button-primary"><?php esc_html_e( 'Create & Select', 'atyourservice' ); ?></button>
									</form>
								</div>
							</details>
						</div>
					</div>

					<!-- Invoice Meta (Dates) -->
					<div class="invoice-section" style="margin-bottom:24px;">
						<table class="form-table" style="margin:0;">
							<tr>
								<th><label><?php esc_html_e( 'Invoice #', 'atyourservice' ); ?></label></th>
								<td><input type="text" value="<?php echo esc_attr( $invoice->invoice_number ); ?>" readonly class="regular-text" /></td>
								<th><label><?php esc_html_e( 'Status', 'atyourservice' ); ?></label></th>
								<td><?php self::render_status_badge( $invoice->status ); ?></td>
							</tr>
							<tr>
								<th><label><?php esc_html_e( 'Issue Date', 'atyourservice' ); ?></label></th>
								<td><input type="date" value="<?php echo esc_attr( substr( $invoice->issue_date, 0, 10 ) ); ?>" readonly /></td>
								<th><label><?php esc_html_e( 'Due Date', 'atyourservice' ); ?></label></th>
								<td><input type="date" value="<?php echo esc_attr( substr( $invoice->due_date, 0, 10 ) ); ?>" readonly /></td>
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
										<th style="text-align: right; width: 80px;"><?php esc_html_e( 'Qty', 'atyourservice' ); ?></th>
										<th style="text-align: right; width: 100px;"><?php esc_html_e( 'Rate', 'atyourservice' ); ?></th>
										<th style="text-align: center; width: 50px;"><?php esc_html_e( 'Tax', 'atyourservice' ); ?></th>
										<th style="text-align: right; width: 100px;"><?php esc_html_e( 'Total', 'atyourservice' ); ?></th>
										<th style="text-align:center; width: 140px;"><?php esc_html_e( 'Actions', 'atyourservice' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $invoice_items as $item ) : ?>
										<tr>
											<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
												<input type="hidden" name="action" value="ays_update_invoice_item" />
												<input type="hidden" name="item_id" value="<?php echo esc_attr( $item->id ); ?>" />
												<input type="hidden" name="invoice_id" value="<?php echo esc_attr( $invoice->id ); ?>" />
												<?php wp_nonce_field( 'ays_update_invoice_item_' . $item->id, 'ays_update_item_nonce' ); ?>
												
												<td><input type="text" name="description" value="<?php echo esc_attr( $item->description ); ?>" style="width:100%;" /></td>
												<td style="text-align: right;"><input type="number" name="quantity" value="<?php echo esc_attr( $item->quantity ); ?>" step="0.01" style="width:70px;text-align:right;" /></td>
												<td style="text-align: right;"><input type="number" name="rate" value="<?php echo esc_attr( $item->rate ); ?>" step="0.01" style="width:90px;text-align:right;" /></td>
												<td style="text-align: center;"><input type="checkbox" name="taxable" value="1" <?php checked( $item->taxable ); ?> /></td>
												<td style="text-align: right; vertical-align:middle;">$<?php echo esc_html( number_format( $item->quantity * $item->rate, 2 ) ); ?></td>
												<td style="text-align:center; vertical-align:middle;">
													<button type="submit" class="button button-small" title="<?php esc_attr_e('Update', 'atyourservice'); ?>">💾</button>
													<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ays_delete_invoice_item&item_id=' . intval($item->id) . '&invoice_id=' . intval($invoice->id) ), 'ays_delete_invoice_item_' . intval($item->id) ) ); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php esc_attr_e('Delete this line?', 'atyourservice'); ?>')">🗑️</a>
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
					<div class="sidebar-card sidebar-card--status">
						<div class="sidebar-card__header">
							<span>📊 <?php esc_html_e( 'Status Overview', 'atyourservice' ); ?></span>
							<span class="sidebar-status-badge"><?php self::render_status_badge( $invoice->status ); ?></span>
						</div>
						<ul class="sidebar-meta">
							<li><span><?php esc_html_e( 'Client', 'atyourservice' ); ?></span><strong><?php echo esc_html( $client && isset( $client->name ) ? $client->name : '—' ); ?></strong></li>
							<li><span><?php esc_html_e( 'Issued', 'atyourservice' ); ?></span><strong><?php echo esc_html( $issue_date_display ); ?></strong></li>
							<li>
								<span><?php esc_html_e( 'Due', 'atyourservice' ); ?></span>
								<strong><?php echo esc_html( $due_date_display ); ?></strong>
								<?php if ( $due_delta ) : ?>
									<small class="sidebar-hint"><?php echo esc_html( $due_delta ); ?></small>
								<?php endif; ?>
							</li>
						</ul>
						<div class="sidebar-progress" aria-hidden="true">
							<div class="sidebar-progress__track">
								<span style="width: <?php echo esc_attr( $progress ); ?>%"></span>
							</div>
							<p class="sidebar-progress__label">
								<?php printf( esc_html__( '%1$s collected • %2$s outstanding', 'atyourservice' ), esc_html( $format_money( $total_paid ) ), esc_html( $format_money( $balance ) ) ); ?>
							</p>
						</div>
					</div>

					<div class="sidebar-card">
						<div class="sidebar-card__header">
							<span>✉️ <?php esc_html_e( 'Send & Notify', 'atyourservice' ); ?></span>
						</div>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="sidebar-form">
							<?php wp_nonce_field( 'ays_send_invoice_email_' . $invoice->id, 'ays_send_email_nonce' ); ?>
							<input type="hidden" name="action" value="ays_send_invoice_email" />
							<input type="hidden" name="invoice_id" value="<?php echo esc_attr( $invoice->id ); ?>" />
							<button type="submit" class="button button-primary" style="width:100%;" onclick="return confirm('<?php esc_attr_e( 'Send invoice to client via email?', 'atyourservice' ); ?>')">
								<?php esc_html_e( 'Send Invoice Email', 'atyourservice' ); ?>
							</button>
						</form>
						<?php if ( $invoice->status !== 'paid' && $balance > 0 && class_exists( 'AYS_Stripe_Settings' ) && method_exists( 'AYS_Stripe_Settings', 'is_configured' ) && AYS_Stripe_Settings::is_configured() ) : ?>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="sidebar-form">
								<?php wp_nonce_field( 'ays_stripe_checkout', '_wpnonce' ); ?>
								<input type="hidden" name="action" value="ays_stripe_checkout" />
								<input type="hidden" name="invoice_id" value="<?php echo esc_attr( $invoice->id ); ?>" />
								<button type="submit" class="button button-secondary" style="width:100%;">
									💳 <?php esc_html_e( 'Send Stripe Checkout', 'atyourservice' ); ?>
								</button>
							</form>
						<?php endif; ?>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="sidebar-form sidebar-form--stacked">
							<?php wp_nonce_field( 'ays_update_invoice_preferences_' . $invoice->id, 'ays_invoice_preferences_nonce' ); ?>
							<input type="hidden" name="action" value="ays_update_invoice_preferences" />
							<input type="hidden" name="invoice_id" value="<?php echo esc_attr( $invoice->id ); ?>" />
							<label class="sidebar-field">
								<span><?php esc_html_e( 'Document Type', 'atyourservice' ); ?></span>
								<select name="document_type">
									<?php
									$doc_types = [
										'invoice' => __( 'Invoice', 'atyourservice' ),
										'quote'   => __( 'Quote', 'atyourservice' ),
										'receipt' => __( 'Receipt', 'atyourservice' ),
									];
									$current_type = isset( $preferences['document_type'] ) ? $preferences['document_type'] : 'invoice';
									foreach ( $doc_types as $key => $label ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $current_type, $key ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
							</label>
							<fieldset class="sidebar-field">
								<legend><?php esc_html_e( 'Sending Mode', 'atyourservice' ); ?></legend>
								<label class="sidebar-choice">
									<input type="radio" name="auto_send_mode" value="manual" <?php checked( ( $preferences['auto_send_mode'] ?? 'manual' ) === 'manual' ); ?> />
									<span><?php esc_html_e( 'Manual send (default)', 'atyourservice' ); ?></span>
								</label>
								<label class="sidebar-choice">
									<input type="radio" name="auto_send_mode" value="auto" <?php checked( ( $preferences['auto_send_mode'] ?? 'manual' ) === 'auto' ); ?> />
									<span><?php esc_html_e( 'Auto-send when marked Sent', 'atyourservice' ); ?></span>
								</label>
							</fieldset>
							<label class="sidebar-choice">
								<input type="checkbox" name="send_admin_copy" value="1" <?php checked( ! empty( $preferences['send_admin_copy'] ) ); ?> />
								<span><?php esc_html_e( 'Send admin a copy', 'atyourservice' ); ?></span>
							</label>
							<div class="sidebar-color-grid">
								<label class="sidebar-field">
									<span><?php esc_html_e( 'Primary Color', 'atyourservice' ); ?></span>
									<input type="color" name="primary_color" value="<?php echo esc_attr( $primary_color ); ?>" />
								</label>
								<label class="sidebar-field">
									<span><?php esc_html_e( 'Accent Color', 'atyourservice' ); ?></span>
									<input type="color" name="accent_color" value="<?php echo esc_attr( $secondary_color ); ?>" />
								</label>
							</div>
							<button type="submit" class="button button-secondary" style="width:100%;">
								<?php esc_html_e( 'Save Sidebar Settings', 'atyourservice' ); ?>
							</button>
						</form>
					</div>

					<div class="sidebar-card">
						<div class="sidebar-card__header">
							<span>💰 <?php esc_html_e( 'Payments & Activity', 'atyourservice' ); ?></span>
						</div>
						<?php if ( empty( $recent_payments ) ) : ?>
							<p class="sidebar-empty"><?php esc_html_e( 'No payments logged yet.', 'atyourservice' ); ?></p>
						<?php else : ?>
							<ul class="sidebar-list">
								<?php foreach ( $recent_payments as $payment_row ) :
									$method_label   = isset( $payment_row['method'] ) ? strtoupper( $payment_row['method'] ) : __( 'Manual', 'atyourservice' );
									$received_stamp = isset( $payment_row['received_at'] ) ? strtotime( $payment_row['received_at'] ) : false;
									$received_label = $received_stamp ? date_i18n( get_option( 'date_format' ), $received_stamp ) : '—';
									$human_diff     = $received_stamp ? human_time_diff( $received_stamp, current_time( 'timestamp' ) ) : '';
								?>
								<li>
									<strong><?php echo esc_html( $format_money( $payment_row['amount'] ?? 0 ) ); ?></strong>
									<span><?php echo esc_html( $method_label ); ?> · <?php echo esc_html( $received_label ); ?></span>
									<?php if ( $human_diff ) : ?>
										<small class="sidebar-hint"><?php printf( esc_html__( '%s ago', 'atyourservice' ), esc_html( $human_diff ) ); ?></small>
									<?php endif; ?>
								</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="sidebar-form">
							<?php wp_nonce_field( 'ays_mark_invoice_paid_' . $invoice->id, 'ays_mark_invoice_paid_nonce' ); ?>
							<input type="hidden" name="action" value="ays_mark_invoice_paid" />
							<input type="hidden" name="invoice_id" value="<?php echo esc_attr( $invoice->id ); ?>" />
							<button type="submit" class="button button-small button-primary" style="width:100%;" <?php disabled( $balance <= 0 ); ?>>
								<?php esc_html_e( 'Mark Remaining Balance Paid', 'atyourservice' ); ?>
							</button>
						</form>
						<a class="button button-link sidebar-link" href="<?php echo esc_url( admin_url( 'admin.php?page=ays-dashboard&tab=payments&invoice_id=' . intval( $invoice->id ) ) ); ?>">
							<?php esc_html_e( 'Open full payment history', 'atyourservice' ); ?>
						</a>
					</div>

					<?php if ( class_exists( 'AYS_Company_Profile' ) ) : ?>
						<div class="sidebar-card">
							<div class="sidebar-card__header">
								<span>🏦 <?php esc_html_e( 'Bank Transfer Details', 'atyourservice' ); ?></span>
							</div>
							<div class="sidebar-bank">
								<?php echo AYS_Company_Profile::get_bank_transfer_html( $invoice->invoice_number ); // already escaped ?>
							</div>
						</div>
					<?php endif; ?>

					<div class="sidebar-card sidebar-card--danger">
						<div class="sidebar-card__header">
							<span>⚠️ <?php esc_html_e( 'Danger Zone', 'atyourservice' ); ?></span>
						</div>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="sidebar-form">
							<?php wp_nonce_field( 'ays_void_invoice_' . $invoice->id, 'ays_void_invoice_nonce' ); ?>
							<input type="hidden" name="action" value="ays_void_invoice" />
							<input type="hidden" name="invoice_id" value="<?php echo esc_attr( $invoice->id ); ?>" />
							<button type="submit" class="button button-secondary" style="width:100%;" onclick="return confirm('<?php esc_attr_e( 'Void this invoice? Clients will no longer see it.', 'atyourservice' ); ?>')">
								<?php esc_html_e( 'Void Invoice', 'atyourservice' ); ?>
							</button>
						</form>
						<a class="button button-link-delete sidebar-link" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ays_delete_invoice&invoice_id=' . intval( $invoice->id ) ), 'ays_delete_invoice_' . $invoice->id ) ); ?>" onclick="return confirm('<?php esc_attr_e( 'Permanently delete this invoice? This cannot be undone.', 'atyourservice' ); ?>');">
							<?php esc_html_e( 'Delete permanently', 'atyourservice' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>

		<style>
			.ays-invoice-editor {
				background: #fff;
				padding: 24px;
				border-radius: 12px;
				margin-top: 20px;
				box-shadow: 0 20px 45px -25px rgba(15, 23, 42, 0.35);
				--ays-invoice-accent: #4c51bf;
				--ays-invoice-muted: #1f2937;
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
				grid-template-columns: minmax(0, 1fr) 300px;
				gap: 24px;
				align-items: start;
			}
			.invoice-section {
				margin-bottom: 24px;
				padding: 18px;
				background: #f9fafb;
				border-radius: 10px;
				border: 1px solid #e5e7eb;
				border-left: 5px solid var(--ays-invoice-accent, #4c51bf);
				box-shadow: inset 0 1px 0 rgba(255,255,255,0.7);
			}
			.invoice-section h3 {
				margin-top: 0;
				margin-bottom: 12px;
				color: var(--ays-invoice-muted, #1f2937);
			}
			.invoice-totals {
				background: #eef2ff;
				border-left-color: var(--ays-invoice-accent, #4c51bf);
			}
			.editor-sidebar {
				display: flex;
				flex-direction: column;
				gap: 16px;
			}
			.sidebar-card {
				background: #fff;
				border: 1px solid #e5e7eb;
				border-radius: 12px;
				padding: 16px;
				box-shadow: 0 10px 25px -20px rgba(15, 23, 42, 0.45);
			}
			.sidebar-card__header {
				display: flex;
				align-items: center;
				justify-content: space-between;
				font-weight: 600;
				margin-bottom: 12px;
			}
			.sidebar-status-badge span { font-size: 13px; }
			.sidebar-meta {
				list-style: none;
				margin: 0 0 12px;
				padding: 0;
			}
			.sidebar-meta li {
				display: flex;
				justify-content: space-between;
				align-items: baseline;
				font-size: 13px;
				margin-bottom: 6px;
			}
			.sidebar-meta span {
				color: #6b7280;
			}
			.sidebar-meta strong {
				color: var(--ays-invoice-muted, #1f2937);
			}
			.sidebar-hint {
				display: block;
				color: #6b7280;
				font-size: 11px;
				margin-top: 2px;
			}
			.sidebar-progress__track {
				background: #e5e7eb;
				border-radius: 999px;
				height: 8px;
				position: relative;
				overflow: hidden;
				margin-bottom: 8px;
			}
			.sidebar-progress__track span {
				display: block;
				height: 100%;
				background: linear-gradient(90deg, var(--ays-invoice-accent, #4c51bf), #6366f1);
			}
			.sidebar-progress__label {
				margin: 0;
				font-size: 12px;
				color: #374151;
			}
			.sidebar-form {
				margin: 0 0 12px;
			}
			.sidebar-form--stacked {
				border-top: 1px solid #f3f4f6;
				padding-top: 12px;
			}
			.sidebar-field {
				display: block;
				margin-bottom: 10px;
			}
			.sidebar-field span,
			.sidebar-field legend {
				display: block;
				font-size: 12px;
				text-transform: uppercase;
				letter-spacing: 0.04em;
				color: #6b7280;
				margin-bottom: 4px;
			}
			.sidebar-choice {
				display: flex;
				align-items: center;
				gap: 8px;
				margin-bottom: 6px;
				font-size: 13px;
				color: #1f2937;
			}
			.sidebar-choice input[type="radio"],
			.sidebar-choice input[type="checkbox"] {
				margin: 0;
			}
			.sidebar-color-grid {
				display: grid;
				grid-template-columns: repeat(2, minmax(0, 1fr));
				gap: 12px;
			}
			.sidebar-color-grid input[type="color"] {
				width: 100%;
				height: 38px;
				border: 1px solid #d1d5db;
				border-radius: 8px;
				padding: 0;
			}
			.sidebar-list {
				list-style: none;
				margin: 0 0 12px;
				padding: 0;
			}
			.sidebar-list li {
				border: 1px dashed #d1d5db;
				border-radius: 8px;
				padding: 10px;
				margin-bottom: 8px;
				font-size: 13px;
			}
			.sidebar-empty {
				margin: 0 0 12px;
				color: #6b7280;
				font-style: italic;
			}
			.sidebar-link {
				display: block;
				text-align: center;
				font-size: 12px;
				padding-top: 4px;
				color: var(--ays-invoice-accent, #4c51bf);
			}
			.sidebar-link-delete {
				color: #b91c1c !important;
			}
			.sidebar-card--danger {
				border-color: #fee2e2;
				background: #fff5f5;
			}
			.sidebar-bank {
				font-size: 12px;
				color: #374151;
			}
			.ays-chip { display:inline-block; padding:2px 8px; font-size:11px; border-radius:999px; background:#eef2ff; color:#3730a3; border:1px solid #c7d2fe; margin:2px 6px 2px 0; }
			.ays-chip .dot { display:inline-block; width:6px; height:6px; background:#6366f1; border-radius:999px; margin-right:6px; vertical-align:middle; }
			.ays-preview-tab {
				margin-top: 24px;
				padding: 24px;
				background: #f9fafb;
				border: 1px solid #e5e7eb;
				border-radius: 16px;
				box-shadow: inset 0 1px 0 rgba(255,255,255,0.8), 0 30px 60px -45px rgba(15,23,42,0.6);
			}
			.preview-grid {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
				gap: 18px;
				margin-bottom: 24px;
			}
			.preview-grid--two {
				grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
			}
			.preview-card {
				background: #fff;
				border: 1px solid #e5e7eb;
				border-radius: 14px;
				padding: 18px;
				box-shadow: 0 15px 35px -30px rgba(15,23,42,0.55);
			}
			.preview-card header {
				font-size: 11px;
				color: #6b7280;
				text-transform: uppercase;
				letter-spacing: 0.08em;
				margin: 0 0 12px;
				padding-bottom: 8px;
				border-bottom: 1px solid #f3f4f6;
			}
			.preview-card h4 {
				margin: 0 0 6px;
				font-size: 13px;
				color: #111827;
			}
			.preview-card p {
				margin: 0;
				font-size: 13px;
				color: #1f2937;
			}
			.preview-card--logo {
				display: flex;
				align-items: center;
				justify-content: center;
				min-height: 120px;
			}
			.preview-card--logo img {
				max-width: 100%;
				height: auto;
				object-fit: contain;
			}
			.preview-meta {
				list-style: none;
				margin: 0;
				padding: 0;
				display: grid;
				gap: 10px;
				font-size: 13px;
			}
			.preview-meta li {
				display: grid;
				grid-template-columns: auto 1fr;
				gap: 12px;
				align-items: center;
			}
			.preview-meta span {
				display: block;
				font-size: 11px;
				text-transform: uppercase;
				letter-spacing: 0.05em;
				color: #6b7280;
			}
			.preview-meta strong {
				color: #111827;
			}
			.preview-meta small {
				font-size: 11px;
				color: #6b7280;
			}
			.preview-status span {
				font-size: 12px;
			}
			.preview-two-col {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
				gap: 18px;
			}
			.preview-alert {
				margin-bottom: 24px;
				padding: 16px 20px;
				border-radius: 14px;
				border: 1px solid #fed7aa;
				background: #fff7ed;
				color: #7c2d12;
			}
			.preview-alert ul {
				margin: 8px 0 0 20px;
				padding: 0;
			}
			.preview-sheet {
				background: #fff;
				border-radius: 18px;
				border: 1px solid #e5e7eb;
				box-shadow: 0 35px 65px -50px rgba(15,23,42,0.7);
				padding: 24px;
				margin-bottom: 28px;
			}
			.preview-timeline {
				list-style: none;
				margin: 16px 0 0;
				padding: 0;
			}
			.preview-timeline li {
				padding: 10px 0;
				border-bottom: 1px solid #f3f4f6;
			}
			.preview-timeline li:last-child {
				border-bottom: 0;
			}
			.preview-timeline strong {
				display: block;
				color: #111827;
			}
			.preview-timeline span {
				font-size: 12px;
				color: #6b7280;
			}
			.preview-muted {
				color: #9ca3af;
				font-style: italic;
			}
			.preview-color {
				display: inline-flex;
				align-items: center;
				gap: 8px;
			}
			.preview-color::before {
				content: '';
				width: 18px;
				height: 18px;
				border-radius: 999px;
				border: 1px solid #d1d5db;
				background: var(--chip-color, var(--ays-preview-primary, #4c51bf));
			}
			.preview-bank {
				margin-top: 12px;
				padding: 12px;
				border-radius: 10px;
				background: #f3f4f6;
				font-size: 12px;
				color: #111827;
			}
			.preview-card p span,
			.preview-card p strong {
				display: inline-block;
				margin-bottom: 2px;
			}
			@media (max-width: 960px) {
				.editor-content {
					grid-template-columns: 1fr;
				}
				.editor-sidebar {
					flex-direction: row;
					flex-wrap: wrap;
				}
				.sidebar-card {
					flex: 1 1 260px;
				}
			}
			@media (max-width: 640px) {
				.editor-sidebar {
					flex-direction: column;
				}
				.sidebar-card {
					flex: 1 1 auto;
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
	 * Render the dedicated Preview tab layout.
	 *
	 * @param array $context Prepared data from render_invoice_editor.
	 * @return void
	 */
	protected static function render_preview_tab( array $context ) {
		$defaults = [
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
			'issue_date_label' => '',
			'due_date_label'   => '',
			'due_delta'        => '',
			'total_paid'       => 0,
			'balance'          => 0,
			'warnings'         => [],
		];
		$ctx = wp_parse_args( $context, $defaults );
		$invoice = $ctx['invoice'];
		$client  = $ctx['client'];
		$preferences = $ctx['preferences'];
		$money_cb = is_callable( $ctx['format_money'] )
			? $ctx['format_money']
			: static function( $amount ) use ( $ctx ) {
				return $ctx['currency_symbol'] . number_format_i18n( (float) $amount, 2 );
			};
		$doc_map = [
			'invoice' => __( 'Invoice', 'atyourservice' ),
			'quote'   => __( 'Quote', 'atyourservice' ),
			'receipt' => __( 'Receipt', 'atyourservice' ),
		];
		$doc_key    = isset( $preferences['document_type'] ) ? sanitize_key( $preferences['document_type'] ) : 'invoice';
		$doc_label  = strtoupper( $doc_map[ $doc_key ] ?? __( 'Invoice', 'atyourservice' ) );
		$primary    = isset( $preferences['primary_color'] ) ? sanitize_hex_color( $preferences['primary_color'] ) : '#4c51bf';
		$primary    = $primary ?: '#4c51bf';
		$accent     = isset( $preferences['accent_color'] ) ? sanitize_hex_color( $preferences['accent_color'] ) : '#111827';
		$accent     = $accent ?: '#111827';
		$auto_mode  = isset( $preferences['auto_send_mode'] ) && $preferences['auto_send_mode'] === 'auto'
			? __( 'Auto-send when marked Sent', 'atyourservice' )
			: __( 'Manual send only', 'atyourservice' );
		$admin_copy = ! empty( $preferences['send_admin_copy'] )
			? __( 'Yes, admin gets a copy', 'atyourservice' )
			: __( 'No admin copy', 'atyourservice' );
		$company_name  = $ctx['company_profile']['company_name'] ?? get_bloginfo( 'name' );
		$company_email = $ctx['company_profile']['company_email'] ?? get_option( 'admin_email' );
		$company_phone = $ctx['company_profile']['company_phone'] ?? '';
		$company_address = ! empty( $ctx['company_profile']['company_address'] )
			? $ctx['company_profile']['company_address']
			: '';
		$client_email = $client && isset( $client->email ) ? $client->email : '';
		$client_phone = $client && isset( $client->phone ) ? $client->phone : '';
		$client_address_bits = [];
		if ( $client ) {
			foreach ( [ 'address_line1', 'address_line2', 'city', 'postcode', 'country' ] as $field ) {
				if ( ! empty( $client->{$field} ) ) {
					$client_address_bits[] = $client->{$field};
				}
			}
		}
		$client_address = implode( ', ', array_filter( $client_address_bits ) );
		$invoice_number = $invoice ? $invoice->invoice_number : '';
		$bank_html = class_exists( 'AYS_Company_Profile' ) ? AYS_Company_Profile::get_bank_transfer_html( $invoice_number ) : '';
		?>
		<div class="ays-preview-tab" style="--ays-preview-primary: <?php echo esc_attr( $primary ); ?>; --ays-preview-accent: <?php echo esc_attr( $accent ); ?>;">
			<div class="preview-grid">
				<section class="preview-card">
					<header>
						<span><?php esc_html_e( 'Document Snapshot', 'atyourservice' ); ?></span>
					</header>
					<ul class="preview-meta">
						<li><span><?php esc_html_e( 'Document', 'atyourservice' ); ?></span><strong><?php echo esc_html( $doc_label ); ?></strong></li>
						<li><span><?php esc_html_e( 'Invoice #', 'atyourservice' ); ?></span><strong><?php echo esc_html( $invoice_number ); ?></strong></li>
						<li><span><?php esc_html_e( 'Status', 'atyourservice' ); ?></span><span class="preview-status"><?php self::render_status_badge( $invoice->status ); ?></span></li>
						<li><span><?php esc_html_e( 'Issued', 'atyourservice' ); ?></span><strong><?php echo esc_html( $ctx['issue_date_label'] ?: '—' ); ?></strong></li>
						<li><span><?php esc_html_e( 'Due', 'atyourservice' ); ?></span><strong><?php echo esc_html( $ctx['due_date_label'] ?: '—' ); ?></strong><?php if ( $ctx['due_delta'] ) : ?><small><?php echo esc_html( $ctx['due_delta'] ); ?></small><?php endif; ?></li>
						<li><span><?php esc_html_e( 'Outstanding', 'atyourservice' ); ?></span><strong><?php echo esc_html( $money_cb( $ctx['balance'] ) ); ?></strong></li>
						<li><span><?php esc_html_e( 'Collected', 'atyourservice' ); ?></span><strong><?php echo esc_html( $money_cb( $ctx['total_paid'] ) ); ?></strong></li>
					</ul>
				</section>
				<section class="preview-card">
					<header>
						<span><?php esc_html_e( 'People & Addresses', 'atyourservice' ); ?></span>
					</header>
					<div class="preview-two-col">
						<div>
							<h4><?php esc_html_e( 'From', 'atyourservice' ); ?></h4>
							<p><strong><?php echo esc_html( $company_name ); ?></strong><br/>
								<?php if ( $company_address ) : ?>
									<span><?php echo nl2br( esc_html( $company_address ) ); ?></span><br/>
								<?php endif; ?>
								<?php if ( $company_email ) : ?>
									<span><?php echo esc_html( $company_email ); ?></span><br/>
								<?php endif; ?>
								<?php if ( $company_phone ) : ?>
									<span><?php echo esc_html( $company_phone ); ?></span>
								<?php endif; ?>
							</p>
						</div>
						<div>
							<h4><?php esc_html_e( 'Bill To', 'atyourservice' ); ?></h4>
							<p><strong><?php echo esc_html( $client && isset( $client->name ) ? $client->name : '—' ); ?></strong><br/>
								<?php if ( $client_address ) : ?>
									<span><?php echo esc_html( $client_address ); ?></span><br/>
								<?php endif; ?>
								<?php if ( $client_email ) : ?>
									<span><?php echo esc_html( $client_email ); ?></span><br/>
								<?php endif; ?>
								<?php if ( $client_phone ) : ?>
									<span><?php echo esc_html( $client_phone ); ?></span>
								<?php endif; ?>
							</p>
						</div>
					</div>
				</section>
				<?php if ( $ctx['company_logo_url'] ) : ?>
				<section class="preview-card preview-card--logo">
					<img src="<?php echo esc_url( $ctx['company_logo_url'] ); ?>" alt="<?php esc_attr_e( 'Company logo', 'atyourservice' ); ?>" />
				</section>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $ctx['warnings'] ) ) : ?>
				<div class="preview-alert">
					<strong>⚠️ <?php esc_html_e( 'Needs Attention', 'atyourservice' ); ?></strong>
					<ul>
						<?php foreach ( $ctx['warnings'] as $warning ) : ?>
							<li><?php echo esc_html( $warning ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
			<div class="preview-sheet">
				<?php self::render_invoice_preview( $invoice, $ctx['items'], $client, $preferences, $ctx['currency_symbol'] ); ?>
			</div>
			<div class="preview-grid preview-grid--two">
				<section class="preview-card">
					<header>
						<span><?php esc_html_e( 'Payments & Activity', 'atyourservice' ); ?></span>
					</header>
					<p><strong><?php esc_html_e( 'Collected:', 'atyourservice' ); ?></strong> <?php echo esc_html( $money_cb( $ctx['total_paid'] ) ); ?><br/>
					<strong><?php esc_html_e( 'Outstanding:', 'atyourservice' ); ?></strong> <?php echo esc_html( $money_cb( $ctx['balance'] ) ); ?></p>
					<?php if ( empty( $ctx['recent_payments'] ) ) : ?>
						<p class="preview-muted"><?php esc_html_e( 'No payments have been logged for this invoice yet.', 'atyourservice' ); ?></p>
					<?php else : ?>
						<ul class="preview-timeline">
							<?php foreach ( $ctx['recent_payments'] as $row ) :
								$method = isset( $row['method'] ) ? strtoupper( $row['method'] ) : __( 'Manual', 'atyourservice' );
								$received_at = ! empty( $row['received_at'] ) ? date_i18n( get_option( 'date_format' ), strtotime( $row['received_at'] ) ) : '—';
								?>
								<li>
									<strong><?php echo esc_html( $money_cb( $row['amount'] ?? 0 ) ); ?></strong>
									<span><?php echo esc_html( $method ); ?> · <?php echo esc_html( $received_at ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</section>
				<section class="preview-card">
					<header>
						<span><?php esc_html_e( 'Send & Branding Settings', 'atyourservice' ); ?></span>
					</header>
					<ul class="preview-meta">
						<li><span><?php esc_html_e( 'Send Mode', 'atyourservice' ); ?></span><strong><?php echo esc_html( $auto_mode ); ?></strong></li>
						<li><span><?php esc_html_e( 'Admin Copy', 'atyourservice' ); ?></span><strong><?php echo esc_html( $admin_copy ); ?></strong></li>
						<li><span><?php esc_html_e( 'Primary Color', 'atyourservice' ); ?></span>
							<span class="preview-color" style="--chip-color: <?php echo esc_attr( $primary ); ?>;"></span>
						</li>
						<li><span><?php esc_html_e( 'Accent Color', 'atyourservice' ); ?></span>
							<span class="preview-color" style="--chip-color: <?php echo esc_attr( $accent ); ?>;"></span>
						</li>
					</ul>
					<?php if ( $bank_html ) : ?>
						<div class="preview-bank" aria-live="polite">
							<?php echo $bank_html; // already escaped ?>
						</div>
					<?php endif; ?>
				</section>
			</div>
		</div>
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
		$preferences = class_exists( 'AYS_Invoice_Preferences' ) ? AYS_Invoice_Preferences::get( $invoice->id ) : [];
		$doc_map = [
			'invoice' => __( 'Invoice', 'atyourservice' ),
			'quote'   => __( 'Quote', 'atyourservice' ),
			'receipt' => __( 'Receipt', 'atyourservice' ),
		];
		$document_type = isset( $preferences['document_type'] ) ? $preferences['document_type'] : 'invoice';
		$document_heading = strtoupper( $doc_map[ $document_type ] ?? __( 'Invoice', 'atyourservice' ) );
		$preview_primary = isset( $preferences['primary_color'] ) ? sanitize_hex_color( $preferences['primary_color'] ) : '#4c51bf';
		if ( empty( $preview_primary ) ) {
			$preview_primary = '#4c51bf';
		}
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
				<h1 style="margin:0 0 10px;color:<?php echo esc_attr( $preview_primary ); ?>;"><?php echo esc_html( $document_heading ); ?></h1>
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

		<table class="ays-invoice-preview-items" style="--ays-preview-accent: <?php echo esc_attr( $preview_primary ); ?>;">
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
				case 'invoice_mark_paid_error':
					echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( '✗ Could not mark invoice as paid. Please try again.', 'atyourservice' ) . '</p></div>';
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
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '✓ Client selection updated for this invoice.', 'atyourservice' ) . '</p></div>';
					break;
				case 'invoice_email_sent':
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '📧 Invoice email queued for sending.', 'atyourservice' ) . '</p></div>';
					break;
				case 'invoice_client_created':
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '✓ New client created and linked to this invoice.', 'atyourservice' ) . '</p></div>';
					break;
				case 'invoice_preferences_saved':
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '✓ Sidebar preferences saved.', 'atyourservice' ) . '</p></div>';
					break;
				case 'invoice_preferences_error':
					echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( '✗ Could not save preferences. Please retry.', 'atyourservice' ) . '</p></div>';
					break;
				case 'invoice_voided':
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( '✓ Invoice has been voided.', 'atyourservice' ) . '</p></div>';
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
		// Support legacy/new schemas: inv_number (current) vs invoice_number (legacy), tax_total vs tax_amount
		// Provide stable aliases for renderer: invoice_number and tax_amount always available
		$sql = $wpdb->prepare(
			"SELECT i.*, 
					COALESCE(i.invoice_number, i.inv_number, CONCAT('INV-', i.id)) AS invoice_number,
					COALESCE(i.tax_amount, i.tax_total, 0) AS tax_amount
			 FROM {$wpdb->prefix}ays_invoices i WHERE i.id = %d",
			$invoice_id
		);
		return $wpdb->get_row( $sql );
	}

	/**
	 * Get invoice by hash (fallback safety for redirect)
	 */
	protected static function get_invoice_by_hash( $hash ) {
		global $wpdb;
		if ( empty( $hash ) ) return null;
		$sql = $wpdb->prepare(
			"SELECT i.*, 
					COALESCE(i.invoice_number, i.inv_number, CONCAT('INV-', i.id)) AS invoice_number,
					COALESCE(i.tax_amount, i.tax_total, 0) AS tax_amount
			 FROM {$wpdb->prefix}ays_invoices i WHERE i.hash = %s LIMIT 1",
			$hash
		);
		return $wpdb->get_row( $sql );
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
		wp_safe_redirect( add_query_arg( [ 'edit_invoice' => $invoice_id ], admin_url( 'admin.php?page=ays-dashboard&tab=invoices' ) ) );
		exit;
	}

	/**
	 * Update a line item
	 */
	public static function handle_update_invoice_item() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
		
		$item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
		$invoice_id = isset($_POST['invoice_id']) ? intval($_POST['invoice_id']) : 0;
		
		check_admin_referer( 'ays_update_invoice_item_' . $item_id, 'ays_update_item_nonce' );

		$desc = isset($_POST['description']) ? sanitize_text_field( wp_unslash($_POST['description']) ) : '';
		$qty  = isset($_POST['quantity']) ? floatval( str_replace(',', '.', $_POST['quantity']) ) : 0;
		$rate = isset($_POST['rate']) ? floatval( str_replace(',', '.', $_POST['rate']) ) : 0;
		$taxable = isset($_POST['taxable']) ? 1 : 0;

		if ( ! $item_id || ! $invoice_id ) {
			wp_die( 'Invalid request' );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'ays_invoice_items';
		
		$line_total = round( $qty * $rate, 2 );
		$tax_rate = self::get_tax_rate();
		$line_tax = $taxable ? round( $line_total * $tax_rate, 2 ) : 0.00;

		$wpdb->update( $table, [
			'description' => $desc,
			'qty'         => $qty,
			'rate'        => $rate,
			'taxable'     => $taxable,
			'line_tax'    => $line_tax,
			'line_total'  => $line_total,
			'updated_at'  => current_time('mysql'),
		], [ 'id' => $item_id ], [ '%s','%f','%f','%d','%f','%f','%s' ], [ '%d' ] );

		self::recalc_invoice_totals( $invoice_id );
		wp_safe_redirect( add_query_arg( [ 'edit_invoice' => $invoice_id, 'ays_notice' => 'item_updated' ], admin_url( 'admin.php?page=ays-dashboard&tab=invoices' ) ) );
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
			wp_safe_redirect( add_query_arg( [ 'edit_invoice' => $invoice_id ], admin_url( 'admin.php?page=ays-dashboard&tab=invoices' ) ) );
			exit;
		}
		wp_safe_redirect( admin_url( 'admin.php?page=ays-dashboard&tab=invoices' ) );
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
	 * AJAX: Create invoice and return redirect URL.
	 */
	public static function ajax_create_invoice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized', 'atyourservice' ) ] );
		}

		check_ajax_referer( 'ays_invoice_nonce' );

		global $wpdb;

		$client_id  = isset( $_POST['client_id'] ) ? intval( $_POST['client_id'] ) : 0;
		$issue_date = isset( $_POST['issue_date'] ) ? sanitize_text_field( wp_unslash( $_POST['issue_date'] ) ) : date( 'Y-m-d' );
		$due_date   = isset( $_POST['due_date'] ) ? sanitize_text_field( wp_unslash( $_POST['due_date'] ) ) : date( 'Y-m-d', strtotime( '+30 days' ) );
		$notes      = isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '';

		if ( ! $client_id ) {
			wp_send_json_error( [ 'message' => __( 'Please select a client.', 'atyourservice' ) ] );
		}

		// Generate invoice number
		$last_invoice   = $wpdb->get_row( "SELECT MAX(id) as last_id FROM {$wpdb->prefix}ays_invoices" );
		$next_seq       = isset( $last_invoice->last_id ) ? ( (int) $last_invoice->last_id + 1 ) : 1;
		$invoice_number = 'INV-' . date( 'Y' ) . '-' . str_pad( $next_seq, 3, '0', STR_PAD_LEFT );

		$has_invoice_number = (bool) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND COLUMN_NAME = 'invoice_number'",
			$wpdb->prefix . 'ays_invoices'
		) );
		$num_col = $has_invoice_number ? 'invoice_number' : 'inv_number';

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

		if ( ! $result ) {
			wp_send_json_error( [ 'message' => __( 'Error creating invoice.', 'atyourservice' ) ] );
		}

		$invoice_id = $wpdb->insert_id;
		$redirect   = add_query_arg( [ 'ays_notice' => 'invoice_created', 'edit_invoice' => $invoice_id, 'inv_hash' => $inv_hash ], admin_url( 'admin.php?page=ays-dashboard&tab=invoices' ) );
		wp_send_json_success( [ 'redirect' => $redirect ] );
	}

	/**
	 * AJAX: Save Invoice from Builder (Insert or Update)
	 */
	public static function ajax_save_invoice_builder() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized', 'atyourservice' ) ] );
		}

		check_ajax_referer( 'ays_invoice_nonce', 'nonce' );

		global $wpdb;
		$invoices_table = $wpdb->prefix . 'ays_invoices';
		$items_table    = $wpdb->prefix . 'ays_invoice_items';

		// 1. Extract Invoice Data
		$invoice_id = isset( $_POST['invoice_id'] ) ? intval( $_POST['invoice_id'] ) : 0;
		$client_id  = isset( $_POST['client_id'] ) ? intval( $_POST['client_id'] ) : 0;
		$issue_date = isset( $_POST['issue_date'] ) ? sanitize_text_field( $_POST['issue_date'] ) : date( 'Y-m-d' );
		$due_date   = isset( $_POST['due_date'] ) ? sanitize_text_field( $_POST['due_date'] ) : date( 'Y-m-d', strtotime( '+7 days' ) );
		$notes      = isset( $_POST['notes'] ) ? sanitize_textarea_field( $_POST['notes'] ) : '';
		
		// Items (JSON encoded array)
		$items_json = isset( $_POST['items'] ) ? stripslashes( $_POST['items'] ) : '[]';
		$items      = json_decode( $items_json, true );

		if ( ! is_array( $items ) ) {
			$items = [];
		}

		// Calculate Totals
		$subtotal = 0.0;
		$tax_total = 0.0;
		$tax_rate = self::get_tax_rate(); // e.g. 0.15

		foreach ( $items as $item ) {
			$qty  = floatval( $item['qty'] ?? 0 );
			$rate = floatval( $item['rate'] ?? 0 );
			$line_total = round( $qty * $rate, 2 );
			$is_taxable = ! empty( $item['taxable'] );
			
			$subtotal += $line_total;
			if ( $is_taxable ) {
				$tax_total += round( $line_total * $tax_rate, 2 );
			}
		}
		$total = $subtotal + $tax_total;

		// 2. Insert or Update Invoice
		$data = [
			'client_id'  => $client_id,
			'issue_date' => $issue_date,
			'due_date'   => $due_date,
			'subtotal'   => $subtotal,
			'tax_amount' => $tax_total, // Schema uses tax_amount or tax_total, handled by get_invoice_tax_column usually, but let's check
			'total'      => $total,
			'notes'      => $notes,
			'updated_at' => current_time( 'mysql' ),
		];

		// Handle schema variation for tax column
		$tax_col = self::get_invoice_tax_column();
		if ( $tax_col !== 'tax_amount' ) {
			unset( $data['tax_amount'] );
			$data[ $tax_col ] = $tax_total;
		}

		if ( $invoice_id > 0 ) {
			// Update
			$wpdb->update( $invoices_table, $data, [ 'id' => $invoice_id ] );
		} else {
			// Insert
			$data['created_at']     = current_time( 'mysql' );
			$data['status']         = 'draft';
			$data['invoice_number'] = 'INV-' . strtoupper( uniqid() ); // Temp, should be sequential or configurable
			$data['hash']           = md5( uniqid( 'inv_', true ) );
			
			// Handle schema variation for invoice number
			$inv_num_col = self::get_invoice_number_column();
			if ( $inv_num_col !== 'invoice_number' ) {
				unset( $data['invoice_number'] );
				$data[ $inv_num_col ] = 'INV-' . strtoupper( uniqid() );
			}

			$wpdb->insert( $invoices_table, $data );
			$invoice_id = $wpdb->insert_id;

			// Update invoice number with ID if needed (e.g. INV-1001)
			$prefix = get_option( 'ays_invoice_prefix', 'INV-' );
			$new_number = $prefix . $invoice_id;
			$wpdb->update( $invoices_table, [ $inv_num_col => $new_number ], [ 'id' => $invoice_id ] );
		}

		// 3. Save Items (Wipe and Recreate for simplicity in Builder)
		// Note: In a more complex system, we might diff them to preserve IDs, but for now this ensures exact state match.
		$wpdb->delete( $items_table, [ 'invoice_id' => $invoice_id ], [ '%d' ] );

		foreach ( $items as $item ) {
			$qty  = floatval( $item['qty'] ?? 0 );
			$rate = floatval( $item['rate'] ?? 0 );
			$desc = sanitize_text_field( $item['description'] ?? '' );
			$taxable = ! empty( $item['taxable'] ) ? 1 : 0;
			
			$line_total = round( $qty * $rate, 2 );
			$line_tax   = $taxable ? round( $line_total * $tax_rate, 2 ) : 0.0;

			$wpdb->insert( $items_table, [
				'invoice_id'  => $invoice_id,
				'description' => $desc,
				'qty'         => $qty, // Schema uses qty
				'rate'        => $rate,
				'taxable'     => $taxable,
				'line_tax'    => $line_tax,
				'line_total'  => $line_total,
				'hash'        => md5( uniqid( 'item_', true ) ),
				'created_at'  => current_time( 'mysql' ),
				'updated_at'  => current_time( 'mysql' ),
			] );
		}

		// 4. Return Success
		$redirect_url = add_query_arg( [ 
			'page' => 'ays-dashboard', 
			'tab' => 'invoices', 
			'edit_invoice' => $invoice_id,
			'ays_notice' => 'invoice_saved'
		], admin_url( 'admin.php' ) );

		wp_send_json_success( [ 
			'message' => __( 'Invoice saved successfully!', 'atyourservice' ),
			'invoice_id' => $invoice_id,
			'redirect' => $redirect_url
		] );
	}

	/**
	 * Helper to get the correct invoice number column name
	 */
	protected static function get_invoice_number_column() {
		global $wpdb;
		$row = $wpdb->get_results( "SHOW COLUMNS FROM {$wpdb->prefix}ays_invoices LIKE 'invoice_number'" );
		return empty( $row ) ? 'inv_number' : 'invoice_number';
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
	 * Handle updating the client on an invoice via admin-post
	 */
	public static function handle_update_invoice_client() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Unauthorized', 'atyourservice' ) );
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
			wp_die( __( 'Unauthorized', 'atyourservice' ) );
		}

		check_admin_referer( 'ays_add_client_nonce' );

		$name  = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
		$email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
		$redirect_invoice_id = isset( $_POST['redirect_to_invoice'] ) ? intval( $_POST['redirect_to_invoice'] ) : 0;

		if ( empty( $name ) ) {
			wp_die( __( 'Client name is required.', 'atyourservice' ) );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'ays_clients';

		// Check email uniqueness
		if ( ! empty( $email ) ) {
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE email = %s", $email ) );
			if ( $exists ) {
				wp_die( __( 'Client with this email already exists.', 'atyourservice' ) );
			}
		}

		$data = [
			'hash'       => md5( uniqid( 'client_', true ) ),
			'name'       => $name,
			'email'      => $email,
			'status'     => 'active',
			'created_at' => current_time( 'mysql' ),
			'updated_at' => current_time( 'mysql' ),
		];

		$result = $wpdb->insert( $table, $data );

		if ( $result ) {
			$client_id = $wpdb->insert_id;
			
			// If we came from an invoice, update that invoice to use this new client
			if ( $redirect_invoice_id ) {
				$wpdb->update(
					$wpdb->prefix . 'ays_invoices',
					[ 'client_id' => $client_id ],
					[ 'id' => $redirect_invoice_id ],
					[ '%d' ],
					[ '%d' ]
				);
				wp_safe_redirect( add_query_arg(
					[
						'page'         => 'ays-dashboard',
						'tab'          => 'invoices',
						'edit_invoice' => $redirect_invoice_id,
						'ays_notice'   => 'invoice_client_created',
					],
					admin_url( 'admin.php' )
				) );
				exit;
			}
		}

		// Fallback redirect
		wp_safe_redirect( add_query_arg(
			[
				'page'       => 'ays-dashboard',
				'tab'        => 'invoices',
				'ays_notice' => 'invoice_client_created',
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
			wp_die( __( 'Unauthorized', 'atyourservice' ) );
		}

		$invoice_id = isset( $_POST['invoice_id'] ) ? intval( $_POST['invoice_id'] ) : 0;
		check_admin_referer( 'ays_send_invoice_email_' . $invoice_id, 'ays_send_email_nonce' );

		$invoice = self::get_invoice( $invoice_id );
		if ( ! $invoice ) {
			wp_die( __( 'Invoice not found', 'atyourservice' ) );
		}

		// Get client
		global $wpdb;
		$client = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ays_clients WHERE id = %d", $invoice->client_id ) );

		if ( ! $client || empty( $client->email ) ) {
			wp_die( __( 'Client not found or has no email address.', 'atyourservice' ) );
		}

		if ( class_exists( 'AYS_Notifier' ) ) {
			$sent = AYS_Notifier::send_invoice_email( $invoice, $client );
			if ( $sent ) {
				// Update status to 'sent' if it was 'draft'
				if ( $invoice->status === 'draft' ) {
					$wpdb->update(
						$wpdb->prefix . 'ays_invoices',
						[ 'status' => 'sent' ],
						[ 'id' => $invoice->id ],
						[ '%s' ],
						[ '%d' ]
					);
				}
				
				// Add a note or log?
				// For now just redirect with success
				wp_safe_redirect( add_query_arg(
					[
						'page'         => 'ays-dashboard',
						'tab'          => 'invoices',
						'edit_invoice' => $invoice_id,
						'ays_notice'   => 'invoice_email_sent',
					],
					admin_url( 'admin.php' )
				) );
				exit;
			} else {
				wp_die( __( 'Failed to send email.', 'atyourservice' ) );
			}
		} else {
			wp_die( __( 'Notifier class not found.', 'atyourservice' ) );
		}
	}

	/**
	 * Persist sidebar preferences (document type, colors, send mode).
	 */
	public static function handle_update_invoice_preferences() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}
		$invoice_id = isset( $_POST['invoice_id'] ) ? intval( $_POST['invoice_id'] ) : 0;
		check_admin_referer( 'ays_update_invoice_preferences_' . $invoice_id, 'ays_invoice_preferences_nonce' );
		if ( ! $invoice_id || ! class_exists( 'AYS_Invoice_Preferences' ) ) {
			wp_safe_redirect( add_query_arg( [ 'ays_notice' => 'invoice_preferences_error' ], admin_url( 'admin.php?page=ays-dashboard&tab=invoices' ) ) );
			exit;
		}
		$data = [
			'document_type'   => isset( $_POST['document_type'] ) ? sanitize_key( wp_unslash( $_POST['document_type'] ) ) : 'invoice',
			'auto_send_mode'  => isset( $_POST['auto_send_mode'] ) ? sanitize_key( wp_unslash( $_POST['auto_send_mode'] ) ) : 'manual',
			'send_admin_copy' => ! empty( $_POST['send_admin_copy'] ) ? 1 : 0,
			'primary_color'   => isset( $_POST['primary_color'] ) ? wp_unslash( $_POST['primary_color'] ) : '',
			'accent_color'    => isset( $_POST['accent_color'] ) ? wp_unslash( $_POST['accent_color'] ) : '',
		];
		$saved = AYS_Invoice_Preferences::save( $invoice_id, $data, get_current_user_id() );
		$notice = $saved ? 'invoice_preferences_saved' : 'invoice_preferences_error';
		wp_safe_redirect( add_query_arg( [
			'page'         => 'ays-dashboard',
			'tab'          => 'invoices',
			'edit_invoice' => $invoice_id,
			'ays_notice'   => $notice,
		], admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Mark remaining balance as paid by dropping a manual payment entry.
	 */
	public static function handle_mark_invoice_paid() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}
		$invoice_id = isset( $_POST['invoice_id'] ) ? intval( $_POST['invoice_id'] ) : 0;
		check_admin_referer( 'ays_mark_invoice_paid_' . $invoice_id, 'ays_mark_invoice_paid_nonce' );
		$invoice = self::get_invoice( $invoice_id );
		if ( ! $invoice ) {
			wp_die( esc_html__( 'Invoice not found.', 'atyourservice' ) );
		}
		$balance = isset( $invoice->balance ) ? (float) $invoice->balance : max( 0, (float) $invoice->total - (float) $invoice->amount_paid );
		if ( $balance <= 0 ) {
			wp_safe_redirect( add_query_arg( [
				'page'         => 'ays-dashboard',
				'tab'          => 'invoices',
				'edit_invoice' => $invoice_id,
				'ays_notice'   => 'invoice_marked_paid',
			], admin_url( 'admin.php' ) ) );
			exit;
		}
		if ( ! class_exists( 'AYS_Invoice_Service' ) ) {
			wp_die( esc_html__( 'Invoice service unavailable.', 'atyourservice' ) );
		}
		$service = new AYS_Invoice_Service();
		$current_user = wp_get_current_user();
		$note = sprintf(
			esc_html__( 'Marked paid by %s on %s', 'atyourservice' ),
			$current_user ? $current_user->display_name : __( 'System', 'atyourservice' ),
			date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), current_time( 'timestamp' ) )
		);
		$result = $service->record_payment( $invoice_id, [
			'amount'          => $balance,
			'method'          => 'other',
			'notes'           => $note,
			'created_by_user' => get_current_user_id(),
			'received_at'     => current_time( 'mysql' ),
			'status'          => 'confirmed',
		] );
		$notice = ( $result && ! is_wp_error( $result ) ) ? 'invoice_marked_paid' : 'invoice_mark_paid_error';
		wp_safe_redirect( add_query_arg( [
			'page'         => 'ays-dashboard',
			'tab'          => 'invoices',
			'edit_invoice' => $invoice_id,
			'ays_notice'   => $notice,
		], admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Soft-void an invoice so clients can no longer act on it.
	 */
	public static function handle_void_invoice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}
		$invoice_id = isset( $_POST['invoice_id'] ) ? intval( $_POST['invoice_id'] ) : 0;
		check_admin_referer( 'ays_void_invoice_' . $invoice_id, 'ays_void_invoice_nonce' );
		$invoice = self::get_invoice( $invoice_id );
		if ( ! $invoice ) {
			wp_die( esc_html__( 'Invoice not found.', 'atyourservice' ) );
		}
		global $wpdb;
		$updated = $wpdb->update(
			$wpdb->prefix . 'ays_invoices',
			[
				'status'     => 'void',
				'balance'    => 0,
				'updated_at' => current_time( 'mysql' ),
			],
			[ 'id' => $invoice_id ],
			[ '%s', '%f', '%s' ],
			[ '%d' ]
		);
		$notice = false === $updated ? 'invoice_error' : 'invoice_voided';
		wp_safe_redirect( add_query_arg( [
			'page'         => 'ays-dashboard',
			'tab'          => 'invoices',
			'edit_invoice' => $invoice_id,
			'ays_notice'   => $notice,
		], admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Get service type IDs linked to an invoice.
	 *
	 * @param int $invoice_id Invoice ID.
	 * @return array<int>
	 */
	protected static function get_invoice_service_type_ids( $invoice_id ) {
		global $wpdb;
		$rows = $wpdb->get_col( $wpdb->prepare( "SELECT service_type_id FROM {$wpdb->prefix}ays_invoice_services WHERE invoice_id = %d", $invoice_id ) );
		return array_map( 'intval', (array) $rows );
	}

	/**
	 * Fetch service type labels or ids for an invoice.
	 *
	 * @param int  $invoice_id Invoice ID.
	 * @param bool $as_names   True to return names, false for IDs.
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
	 * Render service type chips for the invoice list table.
	 *
	 * @param int $invoice_id Invoice ID.
	 * @return void
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
}
