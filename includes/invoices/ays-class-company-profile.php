<?php
/**
 * AYS Company Profile - Company Information Management
 *
 * Manages company branding and contact information for invoices.
 * Stored as WordPress options (serialized JSON data).
 *
 * Option: ays_company_profile
 * Data: {
 *   company_name: string,
 *   company_email: email,
 *   company_phone: string,
 *   company_address: string,
 *   company_website: url,
 *   company_logo_id: media ID,
 *   invoice_footer: text,
 *   payment_methods: text,
 *   invoice_terms: text
 *   bank_transfer_enabled: bool,
 *   bank_account_name: string,
 *   bank_bank_name: string,
 *   bank_account_number: string,
 *   bank_branch: string,
 *   bank_swift: string,
 *   bank_iban: string,
 *   bank_reference_hint: string
 * }
 *
 * @since 1.0
 */

defined( 'ABSPATH' ) || exit;

class AYS_Company_Profile {

	const OPTION_KEY = 'ays_company_profile';

	/**
	 * Render the Company Profile section
	 *
	 * @return void
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		// Handle form submission
		self::handle_form_submission();

		// Get current settings
		$profile = self::get_profile();

		?>
		<details class="ays-details" open>
			<summary>
				🏢 <?php esc_html_e( 'Company Profile', 'atyourservice' ); ?>
				<span class="ays-live-badge">LIVE</span>
			</summary>
			<div>
				<form method="post" class="ays-company-form" enctype="multipart/form-data">
					<?php wp_nonce_field( 'ays_save_company_profile', 'ays_company_nonce' ); ?>
					<input type="hidden" name="action" value="ays_update_company_profile">

					<div class="left-column">
						<table class="form-table">
							<tr>
								<th scope="row">
									<label for="company_name"><?php esc_html_e( 'Company Name', 'atyourservice' ); ?> <span style="color: red;">*</span></label>
								</th>
								<td>
									<input 
										type="text" 
										id="company_name" 
										name="company_name" 
										value="<?php echo esc_attr( $profile['company_name'] ); ?>"
										placeholder="<?php esc_attr_e( 'Your Company Name', 'atyourservice' ); ?>"
										class="regular-text"
										required
									>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="company_email"><?php esc_html_e( 'Email', 'atyourservice' ); ?> <span style="color: red;">*</span></label>
								</th>
								<td>
									<input 
										type="email" 
										id="company_email" 
										name="company_email" 
										value="<?php echo esc_attr( $profile['company_email'] ); ?>"
										placeholder="<?php esc_attr_e( 'contact@company.com', 'atyourservice' ); ?>"
										class="regular-text"
										required
									>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="company_phone"><?php esc_html_e( 'Phone', 'atyourservice' ); ?></label>
								</th>
								<td>
									<input 
										type="tel" 
										id="company_phone" 
										name="company_phone" 
										value="<?php echo esc_attr( $profile['company_phone'] ); ?>"
										placeholder="<?php esc_attr_e( '+64 (0)3 123 4567', 'atyourservice' ); ?>"
										class="regular-text"
									>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="company_address"><?php esc_html_e( 'Business Address', 'atyourservice' ); ?></label>
								</th>
								<td>
									<textarea 
										id="company_address" 
										name="company_address" 
										rows="3"
										placeholder="<?php esc_attr_e( '123 Main Street\nChristchurch, 8000\nNew Zealand', 'atyourservice' ); ?>"
										class="large-text"
									><?php echo esc_textarea( $profile['company_address'] ); ?></textarea>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="company_website"><?php esc_html_e( 'Website', 'atyourservice' ); ?></label>
								</th>
								<td>
									<input 
										type="url" 
										id="company_website" 
										name="company_website" 
										value="<?php echo esc_attr( $profile['company_website'] ); ?>"
										placeholder="<?php esc_attr_e( 'https://www.company.com', 'atyourservice' ); ?>"
										class="regular-text"
									>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="company_logo"><?php esc_html_e( 'Company Logo', 'atyourservice' ); ?></label>
								</th>
								<td>
									<div id="company_logo_preview">
										<?php if ( $profile['company_logo_id'] ) : ?>
											<?php echo wp_get_attachment_image( $profile['company_logo_id'], array( 150, 150 ) ); ?>
											<p>
												<button type="button" class="button button-small" id="remove_logo_btn">
													<?php esc_html_e( 'Remove Logo', 'atyourservice' ); ?>
												</button>
											</p>
										<?php else : ?>
											<p style="color: #6b7280;"><?php esc_html_e( 'No logo uploaded yet', 'atyourservice' ); ?></p>
										<?php endif; ?>
									</div>
									<p>
										<button type="button" class="button" id="upload_logo_btn">
											<?php esc_html_e( 'Upload Logo', 'atyourservice' ); ?>
										</button>
									</p>
									<input type="hidden" id="company_logo_id" name="company_logo_id" value="<?php echo esc_attr( $profile['company_logo_id'] ); ?>">
								</td>
							</tr>
						</table>

						<hr style="margin: 30px 0;">
						<h3><?php esc_html_e( 'Invoice Customization', 'atyourservice' ); ?></h3>
						<table class="form-table">
							<tr>
								<th scope="row">
									<label for="bank_transfer_enabled"><?php esc_html_e( 'Enable Bank Transfer Instructions', 'atyourservice' ); ?></label>
								</th>
								<td>
									<label>
										<input type="checkbox" id="bank_transfer_enabled" name="bank_transfer_enabled" value="1" <?php checked( ! empty( $profile['bank_transfer_enabled'] ) ); ?>>
										<?php esc_html_e( 'Show bank transfer details on invoices and customer views', 'atyourservice' ); ?>
									</label>
									<p class="description"><?php esc_html_e( 'Recommended for NZ customers who pay via bank transfer.', 'atyourservice' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="bank_account_name"><?php esc_html_e( 'Account Name', 'atyourservice' ); ?></label>
								</th>
								<td>
									<input type="text" id="bank_account_name" name="bank_account_name" value="<?php echo esc_attr( $profile['bank_account_name'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Your Company Ltd', 'atyourservice' ); ?>">
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="bank_bank_name"><?php esc_html_e( 'Bank Name', 'atyourservice' ); ?></label>
								</th>
								<td>
									<input type="text" id="bank_bank_name" name="bank_bank_name" value="<?php echo esc_attr( $profile['bank_bank_name'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'ANZ / ASB / BNZ / Westpac …', 'atyourservice' ); ?>">
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="bank_account_number"><?php esc_html_e( 'Account Number', 'atyourservice' ); ?></label>
								</th>
								<td>
									<input type="text" id="bank_account_number" name="bank_account_number" value="<?php echo esc_attr( $profile['bank_account_number'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( '12-3456-7890123-00', 'atyourservice' ); ?>">
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="bank_branch"><?php esc_html_e( 'Branch (optional)', 'atyourservice' ); ?></label>
								</th>
								<td>
									<input type="text" id="bank_branch" name="bank_branch" value="<?php echo esc_attr( $profile['bank_branch'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Christchurch', 'atyourservice' ); ?>">
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="bank_swift"><?php esc_html_e( 'SWIFT (optional)', 'atyourservice' ); ?></label>
								</th>
								<td>
									<input type="text" id="bank_swift" name="bank_swift" value="<?php echo esc_attr( $profile['bank_swift'] ); ?>" class="regular-text" placeholder="ABCDEF01">
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="bank_iban"><?php esc_html_e( 'IBAN (optional)', 'atyourservice' ); ?></label>
								</th>
								<td>
									<input type="text" id="bank_iban" name="bank_iban" value="<?php echo esc_attr( $profile['bank_iban'] ); ?>" class="regular-text" placeholder="NZ.. (if applicable)">
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="bank_reference_hint"><?php esc_html_e( 'Reference Hint', 'atyourservice' ); ?></label>
								</th>
								<td>
									<input type="text" id="bank_reference_hint" name="bank_reference_hint" value="<?php echo esc_attr( $profile['bank_reference_hint'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Use your invoice number as the reference', 'atyourservice' ); ?>">
									<p class="description"><?php esc_html_e( 'Shown to customers; e.g., "Use your invoice number as the reference"', 'atyourservice' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="payment_methods"><?php esc_html_e( 'Payment Methods', 'atyourservice' ); ?></label>
								</th>
								<td>
									<textarea 
										id="payment_methods" 
										name="payment_methods" 
										rows="3"
										placeholder="<?php esc_attr_e( 'Bank Transfer: 12-3456-7890123-45\nCash on Site\nOnline Payment via Stripe', 'atyourservice' ); ?>"
										class="large-text"
									><?php echo esc_textarea( $profile['payment_methods'] ); ?></textarea>
									<p class="description"><?php esc_html_e( 'Payment methods displayed on invoices', 'atyourservice' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="invoice_terms"><?php esc_html_e( 'Invoice Terms & Conditions', 'atyourservice' ); ?></label>
								</th>
								<td>
									<textarea 
										id="invoice_terms" 
										name="invoice_terms" 
										rows="4"
										placeholder="<?php esc_attr_e( 'Thank you for your business!\nPayment is due by the date specified.\nLate payments may incur interest charges.\nPlease contact us with any questions.', 'atyourservice' ); ?>"
										class="large-text"
									><?php echo esc_textarea( $profile['invoice_terms'] ); ?></textarea>
									<p class="description"><?php esc_html_e( 'Displayed at bottom of invoices', 'atyourservice' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="invoice_footer"><?php esc_html_e( 'Invoice Footer Text', 'atyourservice' ); ?></label>
								</th>
								<td>
									<textarea 
										id="invoice_footer" 
										name="invoice_footer" 
										rows="2"
										placeholder="<?php esc_attr_e( 'Copyright © 2024 Your Company | ABN: 12 345 678 901', 'atyourservice' ); ?>"
										class="large-text"
									><?php echo esc_textarea( $profile['invoice_footer'] ); ?></textarea>
									<p class="description"><?php esc_html_e( 'Footer text at very bottom of invoice', 'atyourservice' ); ?></p>
								</td>
							</tr>
						</table>

						<p class="submit">
							<?php submit_button( __( 'Save Company Profile', 'atyourservice' ), 'primary', 'submit', false ); ?>
						</p>
					</div>

					<div class="right-column">
						<h4><?php esc_html_e( '📌 Company Profile Help', 'atyourservice' ); ?></h4>
						<ul>
							<li><strong><?php esc_html_e( 'Company Name:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Your business name (appears on all invoices)', 'atyourservice' ); ?></li>
							<li><strong><?php esc_html_e( 'Email:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Contact email for clients', 'atyourservice' ); ?></li>
							<li><strong><?php esc_html_e( 'Phone:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Business phone number', 'atyourservice' ); ?></li>
							<li><strong><?php esc_html_e( 'Address:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Physical or mailing address', 'atyourservice' ); ?></li>
							<li><strong><?php esc_html_e( 'Logo:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Company logo image (max 150x150px)', 'atyourservice' ); ?></li>
							<li><strong><?php esc_html_e( 'Payment Methods:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'How clients can pay you', 'atyourservice' ); ?></li>
							<li><strong><?php esc_html_e( 'Terms:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Payment terms and conditions', 'atyourservice' ); ?></li>
							<li><strong><?php esc_html_e( 'Footer:', 'atyourservice' ); ?></strong> <?php esc_html_e( 'Copyright or legal info', 'atyourservice' ); ?></li>
						</ul>
						<p style="background: #f3f4f6; padding: 10px; border-radius: 4px; margin-top: 20px;">
							<strong><?php esc_html_e( '💡 Tip:', 'atyourservice' ); ?></strong><br/>
							<?php esc_html_e( 'All of this information will be displayed on your invoices. Make sure it\'s accurate and professional!', 'atyourservice' ); ?>
						</p>
					</div>
				</form>
			</div>
		</details>
		<?php

		// Enqueue media uploader script
		self::enqueue_media_uploader();
	}

	/**
	 * Get company profile data
	 *
	 * @return array Profile data with defaults.
	 */
	public static function get_profile() {
		$profile = get_option( self::OPTION_KEY, [] );

		return [
			'company_name' => $profile['company_name'] ?? '',
			'company_email' => $profile['company_email'] ?? get_option( 'admin_email' ),
			'company_phone' => $profile['company_phone'] ?? '',
			'company_address' => $profile['company_address'] ?? '',
			'company_website' => $profile['company_website'] ?? '',
			'company_logo_id' => $profile['company_logo_id'] ?? 0,
			'invoice_footer' => $profile['invoice_footer'] ?? '',
			'payment_methods' => $profile['payment_methods'] ?? '',
			'invoice_terms' => $profile['invoice_terms'] ?? '',
			'bank_transfer_enabled' => ! empty( $profile['bank_transfer_enabled'] ),
			'bank_account_name' => $profile['bank_account_name'] ?? '',
			'bank_bank_name' => $profile['bank_bank_name'] ?? '',
			'bank_account_number' => $profile['bank_account_number'] ?? '',
			'bank_branch' => $profile['bank_branch'] ?? '',
			'bank_swift' => $profile['bank_swift'] ?? '',
			'bank_iban' => $profile['bank_iban'] ?? '',
			'bank_reference_hint' => $profile['bank_reference_hint'] ?? __( 'Use your invoice number as the reference', 'atyourservice' ),
		];
	}

	/**
	 * Handle form submission
	 *
	 * @return void
	 */
	protected static function handle_form_submission() {
		if ( ! isset( $_POST['action'] ) || $_POST['action'] !== 'ays_update_company_profile' ) {
			return;
		}

		if ( ! isset( $_POST['ays_company_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ays_company_nonce'] ) ), 'ays_save_company_profile' ) ) {
			wp_die( esc_html__( 'Security check failed', 'atyourservice' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		$profile = [
			'company_name' => isset( $_POST['company_name'] ) ? sanitize_text_field( wp_unslash( $_POST['company_name'] ) ) : '',
			'company_email' => isset( $_POST['company_email'] ) ? sanitize_email( wp_unslash( $_POST['company_email'] ) ) : '',
			'company_phone' => isset( $_POST['company_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['company_phone'] ) ) : '',
			'company_address' => isset( $_POST['company_address'] ) ? sanitize_textarea_field( wp_unslash( $_POST['company_address'] ) ) : '',
			'company_website' => isset( $_POST['company_website'] ) ? esc_url_raw( wp_unslash( $_POST['company_website'] ) ) : '',
			'company_logo_id' => isset( $_POST['company_logo_id'] ) ? intval( $_POST['company_logo_id'] ) : 0,
			'invoice_footer' => isset( $_POST['invoice_footer'] ) ? sanitize_textarea_field( wp_unslash( $_POST['invoice_footer'] ) ) : '',
			'payment_methods' => isset( $_POST['payment_methods'] ) ? sanitize_textarea_field( wp_unslash( $_POST['payment_methods'] ) ) : '',
			'invoice_terms' => isset( $_POST['invoice_terms'] ) ? sanitize_textarea_field( wp_unslash( $_POST['invoice_terms'] ) ) : '',
			'bank_transfer_enabled' => ! empty( $_POST['bank_transfer_enabled'] ) ? 1 : 0,
			'bank_account_name' => isset( $_POST['bank_account_name'] ) ? sanitize_text_field( wp_unslash( $_POST['bank_account_name'] ) ) : '',
			'bank_bank_name' => isset( $_POST['bank_bank_name'] ) ? sanitize_text_field( wp_unslash( $_POST['bank_bank_name'] ) ) : '',
			'bank_account_number' => isset( $_POST['bank_account_number'] ) ? sanitize_text_field( wp_unslash( $_POST['bank_account_number'] ) ) : '',
			'bank_branch' => isset( $_POST['bank_branch'] ) ? sanitize_text_field( wp_unslash( $_POST['bank_branch'] ) ) : '',
			'bank_swift' => isset( $_POST['bank_swift'] ) ? sanitize_text_field( wp_unslash( $_POST['bank_swift'] ) ) : '',
			'bank_iban' => isset( $_POST['bank_iban'] ) ? sanitize_text_field( wp_unslash( $_POST['bank_iban'] ) ) : '',
			'bank_reference_hint' => isset( $_POST['bank_reference_hint'] ) ? sanitize_text_field( wp_unslash( $_POST['bank_reference_hint'] ) ) : __( 'Use your invoice number as the reference', 'atyourservice' ),
		];

		if ( ! $profile['company_name'] || ! $profile['company_email'] ) {
			wp_die( esc_html__( 'Company name and email are required', 'atyourservice' ) );
		}

		update_option( self::OPTION_KEY, $profile );

		wp_redirect( add_query_arg( 'ays_notice', 'company_profile_updated' ) );
		exit;
	}

	/**
	 * Render bank transfer HTML snippet using profile settings.
	 * Can be embedded in admin/customer views.
	 *
	 * @param string|null $invoice_number Optional invoice number for reference hint interpolation.
	 * @return string HTML
	 */
	public static function get_bank_transfer_html( $invoice_number = null ) {
		$p = self::get_profile();
		if ( empty( $p['bank_transfer_enabled'] ) ) {
			return '';
		}

		$rows = [];
		if ( $p['bank_account_name'] ) {
			$rows[] = '<tr><th style="text-align:left;">' . esc_html__( 'Account Name', 'atyourservice' ) . '</th><td>' . esc_html( $p['bank_account_name'] ) . '</td></tr>';
		}
		if ( $p['bank_bank_name'] ) {
			$rows[] = '<tr><th style="text-align:left;">' . esc_html__( 'Bank', 'atyourservice' ) . '</th><td>' . esc_html( $p['bank_bank_name'] ) . '</td></tr>';
		}
		if ( $p['bank_account_number'] ) {
			$rows[] = '<tr><th style="text-align:left;">' . esc_html__( 'Account Number', 'atyourservice' ) . '</th><td><code>' . esc_html( $p['bank_account_number'] ) . '</code></td></tr>';
		}
		if ( $p['bank_branch'] ) {
			$rows[] = '<tr><th style="text-align:left;">' . esc_html__( 'Branch', 'atyourservice' ) . '</th><td>' . esc_html( $p['bank_branch'] ) . '</td></tr>';
		}
		if ( $p['bank_swift'] ) {
			$rows[] = '<tr><th style="text-align:left;">' . esc_html__( 'SWIFT', 'atyourservice' ) . '</th><td>' . esc_html( $p['bank_swift'] ) . '</td></tr>';
		}
		if ( $p['bank_iban'] ) {
			$rows[] = '<tr><th style="text-align:left;">' . esc_html__( 'IBAN', 'atyourservice' ) . '</th><td>' . esc_html( $p['bank_iban'] ) . '</td></tr>';
		}

		$hint = $p['bank_reference_hint'];
		if ( $invoice_number ) {
			// Simple token replacement if the hint contains {invoice}
			$hint = str_replace( '{invoice}', $invoice_number, $hint );
		}

		$html  = '<div class="ays-bank-transfer" style="border:1px solid #e5e7eb;border-radius:6px;padding:12px;">';
		$html .= '<h4 style="margin-top:0;">' . esc_html__( 'Pay by Bank Transfer', 'atyourservice' ) . '</h4>';
		if ( $rows ) {
			$html .= '<table style="width:100%;border-collapse:collapse;">' . implode( '', $rows ) . '</table>';
		}
		if ( $hint ) {
			$html .= '<p style="margin-top:8px;color:#374151;">' . esc_html( $hint ) . '</p>';
		}
		$html .= '</div>';
		return $html;
	}

	/**
	 * Enqueue media uploader script for logo upload
	 *
	 * @return void
	 */
	protected static function enqueue_media_uploader() {
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			let mediaUploader;

			$('#upload_logo_btn').on('click', function(e) {
				e.preventDefault();

				if (mediaUploader) {
					mediaUploader.open();
					return;
				}

				mediaUploader = wp.media.frames.file_frame = wp.media({
					title: <?php echo wp_json_encode( __( 'Select Company Logo', 'atyourservice' ) ); ?>,
					button: {
						text: <?php echo wp_json_encode( __( 'Use This Image', 'atyourservice' ) ); ?>
					},
					multiple: false,
					library: {
						type: 'image'
					}
				});

				mediaUploader.on('select', function() {
					const attachment = mediaUploader.state().get('selection').first().toJSON();
					$('#company_logo_id').val(attachment.id);
					
					const preview = '<img src="' + attachment.sizes.thumbnail.url + '" style="max-width: 150px; height: auto;" />' +
						'<p><button type="button" class="button button-small" id="remove_logo_btn">Remove Logo</button></p>';
					$('#company_logo_preview').html(preview);
					
				// Rebind remove button
				$('#remove_logo_btn').on('click', function(e) {
					e.preventDefault();
					$('#company_logo_id').val(0);
					$('#company_logo_preview').html('<p style="color: #6b7280;"><?php echo esc_js( __( 'No logo uploaded yet', 'atyourservice' ) ); ?></p>');
				});
				});

				mediaUploader.open();
			});

			// Initial remove button binding
			$('#remove_logo_btn').on('click', function(e) {
				e.preventDefault();
				$('#company_logo_id').val(0);
				$('#company_logo_preview').html('<p style="color: #6b7280;"><?php echo esc_js( __( 'No logo uploaded yet', 'atyourservice' ) ); ?></p>');
			});
		});
		</script>
		<?php
	}
}
