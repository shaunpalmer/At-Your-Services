<?php
/**
 * AYS Stripe Settings - Stripe Payment Configuration
 *
 * Manages Stripe API keys, webhook secrets, and payment settings.
 * Stored as WordPress options (JSON serialized).
 *
 * Option: ays_stripe_settings
 * Data: {
 *   test_mode: boolean,
 *   test_public_key: string,
 *   test_secret_key: string,
 *   live_public_key: string,
 *   live_secret_key: string,
 *   webhook_secret: string,
 *   auto_confirm_payments: boolean
 * }
 *
 * @since 1.0
 */

defined( 'ABSPATH' ) || exit;

class AYS_Stripe_Settings {

	const OPTION_KEY = 'ays_stripe_settings';

	/**
	 * Get all Stripe settings
	 *
	 * @return array
	 */
	public static function get_settings() {
		$defaults = [
			'test_mode'              => true,
			'test_public_key'        => '',
			'test_secret_key'        => '',
			'live_public_key'        => '',
			'live_secret_key'        => '',
			'webhook_secret'         => '',
			'auto_confirm_payments'  => true,
		];

		$stored = get_option( self::OPTION_KEY, [] );
		return wp_parse_args( $stored, $defaults );
	}

	/**
	 * Get active API keys (test or live based on mode)
	 *
	 * @return array {public_key, secret_key}
	 */
	public static function get_active_keys() {
		$settings = self::get_settings();
		
		if ( $settings['test_mode'] ) {
			return [
				'public_key' => $settings['test_public_key'],
				'secret_key' => $settings['test_secret_key'],
			];
		}

		return [
			'public_key' => $settings['live_public_key'],
			'secret_key' => $settings['live_secret_key'],
		];
	}

	/**
	 * Check if Stripe is properly configured
	 *
	 * @return bool
	 */
	public static function is_configured() {
		$keys = self::get_active_keys();
		return ! empty( $keys['public_key'] ) && ! empty( $keys['secret_key'] );
	}

	/**
	 * Render the Stripe Settings section
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
		$settings = self::get_settings();
		$is_configured = self::is_configured();

		?>
		<details class="ays-details" open>
			<summary>
				💳 <?php esc_html_e( 'Stripe Payment Settings', 'atyourservice' ); ?>
				<?php if ( $is_configured ) : ?>
					<span class="ays-badge" style="background: #4CAF50;">✓ Configured</span>
				<?php else : ?>
					<span class="ays-badge" style="background: #FF9800;">⚠ Not Configured</span>
				<?php endif; ?>
			</summary>
			<div>
				<form method="post" class="ays-stripe-settings-form">
					<?php wp_nonce_field( 'ays_save_stripe_settings', 'ays_stripe_nonce' ); ?>
					<input type="hidden" name="action" value="ays_update_stripe_settings">

					<div class="left-column">
						<table class="form-table">
							<!-- Test/Live Mode Toggle -->
							<tr>
								<th scope="row">
									<label for="test_mode"><?php esc_html_e( 'Mode', 'atyourservice' ); ?></label>
								</th>
								<td>
									<label style="display: flex; align-items: center; gap: 10px;">
										<input 
											type="checkbox" 
											id="test_mode" 
											name="test_mode"
											value="1"
											<?php checked( $settings['test_mode'], true ); ?>
										>
										<span><?php esc_html_e( 'Test Mode (Enabled)', 'atyourservice' ); ?></span>
									</label>
									<p class="description">
										<?php esc_html_e( 'When enabled, uses test API keys. Uncheck for live payments.', 'atyourservice' ); ?>
									</p>
								</td>
							</tr>

							<!-- Test Keys Section -->
							<tr>
								<th colspan="2" style="background: #f5f5f5; padding: 15px;">
									<strong><?php esc_html_e( '🧪 Test API Keys (from dashboard.stripe.com/test/apikeys)', 'atyourservice' ); ?></strong>
								</th>
							</tr>
							<tr>
								<th scope="row">
									<label for="test_public_key"><?php esc_html_e( 'Test Publishable Key', 'atyourservice' ); ?></label>
								</th>
								<td>
									<input 
										type="password" 
										id="test_public_key" 
										name="test_public_key" 
										value="<?php echo esc_attr( $settings['test_public_key'] ); ?>"
										placeholder="pk_test_..."
										class="large-text code"
									>
									<p class="description"><?php esc_html_e( 'Starts with pk_test_', 'atyourservice' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="test_secret_key"><?php esc_html_e( 'Test Secret Key', 'atyourservice' ); ?></label>
								</th>
								<td>
									<input 
										type="password" 
										id="test_secret_key" 
										name="test_secret_key" 
										value="<?php echo esc_attr( $settings['test_secret_key'] ); ?>"
										placeholder="sk_test_..."
										class="large-text code"
									>
									<p class="description"><?php esc_html_e( 'Starts with sk_test_. Keep this secret!', 'atyourservice' ); ?></p>
								</td>
							</tr>

							<!-- Live Keys Section -->
							<tr>
								<th colspan="2" style="background: #fff3cd; padding: 15px;">
									<strong><?php esc_html_e( '🔴 Live API Keys (from dashboard.stripe.com/apikeys)', 'atyourservice' ); ?></strong>
								</th>
							</tr>
							<tr>
								<th scope="row">
									<label for="live_public_key"><?php esc_html_e( 'Live Publishable Key', 'atyourservice' ); ?></label>
								</th>
								<td>
									<input 
										type="password" 
										id="live_public_key" 
										name="live_public_key" 
										value="<?php echo esc_attr( $settings['live_public_key'] ); ?>"
										placeholder="pk_live_..."
										class="large-text code"
									>
									<p class="description"><?php esc_html_e( 'Starts with pk_live_', 'atyourservice' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="live_secret_key"><?php esc_html_e( 'Live Secret Key', 'atyourservice' ); ?></label>
								</th>
								<td>
									<input 
										type="password" 
										id="live_secret_key" 
										name="live_secret_key" 
										value="<?php echo esc_attr( $settings['live_secret_key'] ); ?>"
										placeholder="sk_live_..."
										class="large-text code"
									>
									<p class="description"><?php esc_html_e( 'Starts with sk_live_. Keep this secret!', 'atyourservice' ); ?></p>
								</td>
							</tr>

							<!-- Webhook Settings -->
							<tr>
								<th colspan="2" style="background: #e3f2fd; padding: 15px;">
									<strong><?php esc_html_e( '🔗 Webhook Configuration', 'atyourservice' ); ?></strong>
								</th>
							</tr>
							<tr>
								<th scope="row">
									<label><?php esc_html_e( 'Webhook URL', 'atyourservice' ); ?></label>
								</th>
								<td>
									<code style="display: block; padding: 10px; background: #f5f5f5; margin: 5px 0; word-break: break-all;">
										<?php echo esc_url( rest_url( 'ays/v1/stripe/webhook' ) ); ?>
									</code>
									<p class="description">
										<?php esc_html_e( 'Add this URL to Stripe Dashboard → Developers → Webhooks → Add Endpoint', 'atyourservice' ); ?><br>
										<?php esc_html_e( 'Events to listen for: payment_intent.succeeded, payment_intent.payment_failed', 'atyourservice' ); ?>
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="webhook_secret"><?php esc_html_e( 'Webhook Secret', 'atyourservice' ); ?></label>
								</th>
								<td>
									<input 
										type="password" 
										id="webhook_secret" 
										name="webhook_secret" 
										value="<?php echo esc_attr( $settings['webhook_secret'] ); ?>"
										placeholder="whsec_..."
										class="large-text code"
									>
									<p class="description"><?php esc_html_e( 'From Webhook endpoint details (Signing secret)', 'atyourservice' ); ?></p>
								</td>
							</tr>

							<!-- Auto Confirm -->
							<tr>
								<th scope="row">
									<label for="auto_confirm_payments"><?php esc_html_e( 'Auto Confirm Payments', 'atyourservice' ); ?></label>
								</th>
								<td>
									<label style="display: flex; align-items: center; gap: 10px;">
										<input 
											type="checkbox" 
											id="auto_confirm_payments" 
											name="auto_confirm_payments"
											value="1"
											<?php checked( $settings['auto_confirm_payments'], true ); ?>
										>
										<span><?php esc_html_e( 'Automatically mark invoices as paid when Stripe confirms payment', 'atyourservice' ); ?></span>
									</label>
								</td>
							</tr>
						</table>

						<p style="margin-top: 20px;">
							<button type="submit" class="button button-primary">
								<?php esc_html_e( 'Save Stripe Settings', 'atyourservice' ); ?>
							</button>
						</p>
					</div>

					<!-- Right Column: Instructions -->
					<div class="right-column">
						<h4><?php esc_html_e( '📖 Quick Setup', 'atyourservice' ); ?></h4>
						<ol>
							<li><?php esc_html_e( 'Go to stripe.com and create account', 'atyourservice' ); ?></li>
							<li><?php esc_html_e( 'Navigate to Dashboard → Developers → API Keys', 'atyourservice' ); ?></li>
							<li><?php esc_html_e( 'Copy test keys first (toggle to Test Mode)', 'atyourservice' ); ?></li>
							<li><?php esc_html_e( 'Paste them above and save', 'atyourservice' ); ?></li>
							<li><?php esc_html_e( 'Go to Webhooks → Add Endpoint', 'atyourservice' ); ?></li>
							<li><?php esc_html_e( 'Paste webhook URL above', 'atyourservice' ); ?></li>
							<li><?php esc_html_e( 'Select events: payment_intent.succeeded', 'atyourservice' ); ?></li>
							<li><?php esc_html_e( 'Copy webhook secret and paste above', 'atyourservice' ); ?></li>
							<li><?php esc_html_e( 'Test with test invoices', 'atyourservice' ); ?></li>
							<li><?php esc_html_e( 'When ready, add live keys and toggle off test mode', 'atyourservice' ); ?></li>
						</ol>

						<h4 style="margin-top: 20px;"><?php esc_html_e( '✨ Features', 'atyourservice' ); ?></h4>
						<ul>
							<li><?php esc_html_e( 'One-click "Pay Now" button on invoices', 'atyourservice' ); ?></li>
							<li><?php esc_html_e( 'Secure Stripe Checkout experience', 'atyourservice' ); ?></li>
							<li><?php esc_html_e( 'Auto-confirm payments via webhook', 'atyourservice' ); ?></li>
							<li><?php esc_html_e( 'Test & Live mode switching', 'atyourservice' ); ?></li>
							<li><?php esc_html_e( 'Payment status tracking', 'atyourservice' ); ?></li>
						</ul>
					</div>
				</form>
			</div>
		</details>

		<style>
			.ays-stripe-settings-form .form-table tr th {
				padding: 12px;
			}
			.ays-stripe-settings-form .form-table tr td {
				padding: 12px;
			}
		</style>
		<?php
	}

	/**
	 * Handle form submission
	 *
	 * @return void
	 */
	private static function handle_form_submission() {
		// Check if this is our form
		if ( ! isset( $_POST['action'] ) || $_POST['action'] !== 'ays_update_stripe_settings' ) {
			return;
		}

		// Verify nonce
		if ( ! isset( $_POST['ays_stripe_nonce'] ) || ! wp_verify_nonce( $_POST['ays_stripe_nonce'], 'ays_save_stripe_settings' ) ) {
			wp_die( esc_html__( 'Security check failed', 'atyourservice' ) );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		// Sanitize and save settings
		$settings = [
			'test_mode'              => isset( $_POST['test_mode'] ) ? (bool) $_POST['test_mode'] : false,
			'test_public_key'        => isset( $_POST['test_public_key'] ) ? sanitize_text_field( wp_unslash( $_POST['test_public_key'] ) ) : '',
			'test_secret_key'        => isset( $_POST['test_secret_key'] ) ? sanitize_text_field( wp_unslash( $_POST['test_secret_key'] ) ) : '',
			'live_public_key'        => isset( $_POST['live_public_key'] ) ? sanitize_text_field( wp_unslash( $_POST['live_public_key'] ) ) : '',
			'live_secret_key'        => isset( $_POST['live_secret_key'] ) ? sanitize_text_field( wp_unslash( $_POST['live_secret_key'] ) ) : '',
			'webhook_secret'         => isset( $_POST['webhook_secret'] ) ? sanitize_text_field( wp_unslash( $_POST['webhook_secret'] ) ) : '',
			'auto_confirm_payments'  => isset( $_POST['auto_confirm_payments'] ) ? (bool) $_POST['auto_confirm_payments'] : false,
		];

		// Save to WordPress options
		update_option( self::OPTION_KEY, $settings );

		// Redirect with success notice
		wp_safe_remote_post(
			add_query_arg(
				[
					'page'       => 'ays-settings',
					'ays_notice' => 'stripe_settings_saved',
				],
				admin_url( 'admin.php' )
			)
		);

		wp_redirect( add_query_arg( 'ays_notice', 'stripe_settings_saved', wp_get_referer() ) );
		exit;
	}
}
