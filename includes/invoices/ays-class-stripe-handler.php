<?php
/**
 * AYS Stripe Handler - Payment Processing & Webhooks
 *
 * Handles Stripe Checkout sessions, payment intent confirmations, and webhooks.
 * Provides "Pay Now" functionality for invoices.
 *
 * @since 1.0
 */

defined( 'ABSPATH' ) || exit;

class AYS_Stripe_Handler {

	/**
	 * Initialize Stripe SDK with active API key from settings
	 * Call this before any Stripe API operations
	 *
	 * @return bool True if Stripe initialized successfully, false otherwise
	 */
	public static function initialize_stripe() {
		// Check if settings are configured
		if ( ! AYS_Stripe_Settings::is_configured() ) {
			return false;
		}

		// Get active keys (test or live)
		$keys = AYS_Stripe_Settings::get_active_keys();

		// Set the API key on the Stripe SDK
		if ( ! empty( $keys['secret_key'] ) ) {
			\Stripe\Stripe::setApiKey( $keys['secret_key'] );
			return true;
		}

		return false;
	}

	/**
	 * Initialize Stripe handler
	 * Hook this early in plugin initialization
	 *
	 * @return void
	 */
	public static function init() {
		// REST endpoint for webhook
		add_action( 'rest_api_init', [ self::class, 'register_webhook_route' ] );

		// Admin-post handler for creating checkout session
		add_action( 'admin_post_ays_stripe_checkout', [ self::class, 'handle_checkout' ] );
		add_action( 'admin_post_nopriv_ays_stripe_checkout', [ self::class, 'handle_checkout' ] );
	}

	/**
	 * Register webhook REST route
	 *
	 * @return void
	 */
	public static function register_webhook_route() {
		register_rest_route(
			'ays/v1',
			'/stripe/webhook',
			[
				'methods'             => 'POST',
				'callback'            => [ self::class, 'handle_webhook' ],
				'permission_callback' => '__return_true', // Webhook signature verification in callback
			]
		);
	}

	/**
	 * Handle Stripe webhook event
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response
	 */
	public static function handle_webhook( $request ) {
		// Initialize Stripe SDK first
		if ( ! self::initialize_stripe() ) {
			return new WP_REST_Response( [ 'error' => 'Stripe not configured' ], 400 );
		}

		// Get raw body for signature verification
		$raw_body = $request->get_body();
		$signature = $request->get_header( 'Stripe-Signature' );

		// Get webhook secret from settings
		$settings = AYS_Stripe_Settings::get_settings();
		$webhook_secret = $settings['webhook_secret'];

		if ( empty( $webhook_secret ) ) {
			return new WP_REST_Response( [ 'error' => 'Webhook secret not configured' ], 400 );
		}

		// Verify signature (basic verification without Stripe SDK)
		if ( ! self::verify_webhook_signature( $raw_body, $signature, $webhook_secret ) ) {
			return new WP_REST_Response( [ 'error' => 'Invalid signature' ], 403 );
		}

		// Parse JSON event
		$event = json_decode( $raw_body, true );

		if ( empty( $event['type'] ) ) {
			return new WP_REST_Response( [ 'error' => 'No event type' ], 400 );
		}

		// Handle specific events
		switch ( $event['type'] ) {
			case 'payment_intent.succeeded':
				self::handle_payment_succeeded( $event['data']['object'] );
				break;

			case 'payment_intent.payment_failed':
				self::handle_payment_failed( $event['data']['object'] );
				break;

			default:
				// Ignore other event types
				break;
		}

		return new WP_REST_Response( [ 'success' => true ], 200 );
	}

	/**
	 * Verify webhook signature (HMAC SHA256)
	 *
	 * @param string $raw_body The raw request body.
	 * @param string $signature The Stripe-Signature header.
	 * @param string $secret The webhook secret.
	 * @return bool
	 */
	private static function verify_webhook_signature( $raw_body, $signature, $secret ) {
		// Extract timestamp and signatures from header
		// Format: t=timestamp,v1=signature1,v1=signature2
		$parts = [];
		foreach ( explode( ',', $signature ) as $part ) {
			[ $key, $value ] = explode( '=', $part );
			$parts[ trim( $key ) ] = trim( $value );
		}

		if ( empty( $parts['t'] ) || empty( $parts['v1'] ) ) {
			return false;
		}

		$timestamp = $parts['t'];
		$provided_signature = $parts['v1'];

		// Create signed content (timestamp.body)
		$signed_content = $timestamp . '.' . $raw_body;

		// Compute expected signature
		$expected_signature = hash_hmac( 'sha256', $signed_content, $secret );

		// Constant-time comparison
		return hash_equals( $expected_signature, $provided_signature );
	}

	/**
	 * Handle payment_intent.succeeded event
	 *
	 * @param array $payment_intent The payment intent object from Stripe.
	 * @return void
	 */
	private static function handle_payment_succeeded( $payment_intent ) {
		global $wpdb;

		// Extract metadata
		$metadata = $payment_intent['metadata'] ?? [];
		$invoice_id = intval( $metadata['invoice_id'] ?? 0 );

		if ( empty( $invoice_id ) ) {
			return;
		}

		// Get existing payment record
		$existing_payment = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id FROM {$wpdb->prefix}ays_payments WHERE invoice_id = %d AND method = 'stripe' ORDER BY created_at DESC LIMIT 1",
				$invoice_id
			)
		);

		// Prepare payment data
		$payment_data = [
			'invoice_id'     => $invoice_id,
			'amount'         => floatval( $payment_intent['amount'] ) / 100, // Stripe uses cents
			'method'         => 'stripe',
			'status'         => 'confirmed',
			'transaction_id' => $payment_intent['id'],
			'notes'          => 'Auto-confirmed via Stripe webhook',
			'created_at'     => current_time( 'mysql' ),
			'updated_at'     => current_time( 'mysql' ),
		];

		if ( $existing_payment ) {
			// Update existing payment
			$wpdb->update(
				"{$wpdb->prefix}ays_payments",
				[
					'status'         => 'confirmed',
					'amount'         => $payment_data['amount'],
					'transaction_id' => $payment_data['transaction_id'],
					'notes'          => $payment_data['notes'],
					'updated_at'     => $payment_data['updated_at'],
				],
				[ 'id' => $existing_payment->id ]
			);
		} else {
			// Insert new payment record
			$wpdb->insert(
				"{$wpdb->prefix}ays_payments",
				$payment_data,
				[ '%d', '%f', '%s', '%s', '%s', '%s', '%s', '%s' ]
			);
		}

		// Auto-confirm payment on invoice if enabled
		$settings = AYS_Stripe_Settings::get_settings();
		if ( $settings['auto_confirm_payments'] ) {
			$wpdb->update(
				"{$wpdb->prefix}ays_invoices",
				[ 'status' => 'paid', 'updated_at' => current_time( 'mysql' ) ],
				[ 'id' => $invoice_id ],
				[ '%s', '%s' ],
				[ '%d' ]
			);
		}

		do_action( 'ays_payment_confirmed', $invoice_id, $payment_intent['id'] );
	}

	/**
	 * Handle payment_intent.payment_failed event
	 *
	 * @param array $payment_intent The payment intent object from Stripe.
	 * @return void
	 */
	private static function handle_payment_failed( $payment_intent ) {
		global $wpdb;

		// Extract metadata
		$metadata = $payment_intent['metadata'] ?? [];
		$invoice_id = intval( $metadata['invoice_id'] ?? 0 );

		if ( empty( $invoice_id ) ) {
			return;
		}

		// Insert failed payment record
		$wpdb->insert(
			"{$wpdb->prefix}ays_payments",
			[
				'invoice_id'     => $invoice_id,
				'amount'         => floatval( $payment_intent['amount'] ) / 100,
				'method'         => 'stripe',
				'status'         => 'failed',
				'transaction_id' => $payment_intent['id'],
				'notes'          => 'Payment failed: ' . ( $payment_intent['last_payment_error']['message'] ?? 'Unknown error' ),
				'created_at'     => current_time( 'mysql' ),
				'updated_at'     => current_time( 'mysql' ),
			],
			[ '%d', '%f', '%s', '%s', '%s', '%s', '%s', '%s' ]
		);

		do_action( 'ays_payment_failed', $invoice_id, $payment_intent['id'] );
	}

	/**
	 * Handle checkout session creation
	 * This creates a Stripe Checkout session and redirects to Stripe
	 *
	 * @return void
	 */
	public static function handle_checkout() {
		// Initialize Stripe SDK first
		if ( ! self::initialize_stripe() ) {
			wp_die( esc_html__( 'Stripe is not configured. Please add API keys in Settings.', 'atyourservice' ) );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'atyourservice' ) );
		}

		// Verify nonce
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'ays_stripe_checkout' ) ) {
			wp_die( esc_html__( 'Security check failed', 'atyourservice' ) );
		}

		$invoice_id = intval( $_POST['invoice_id'] ?? 0 );
		if ( empty( $invoice_id ) ) {
			wp_die( esc_html__( 'Invalid invoice', 'atyourservice' ) );
		}

		// Get invoice data
		global $wpdb;
		$invoice = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT i.*, c.client_name, c.client_email 
				 FROM {$wpdb->prefix}ays_invoices i 
				 LEFT JOIN {$wpdb->prefix}ays_clients c ON i.client_id = c.id 
				 WHERE i.id = %d",
				$invoice_id
			)
		);

		if ( ! $invoice ) {
			wp_die( esc_html__( 'Invoice not found', 'atyourservice' ) );
		}

		// Get active keys
		$keys = AYS_Stripe_Settings::get_active_keys();

		// Create checkout data for client-side Stripe.js
		$checkout_data = [
			'invoice_id'     => $invoice_id,
			'amount_cents'   => intval( $invoice->total * 100 ), // Stripe uses cents
			'currency'       => 'usd', // Customize as needed
			'invoice_number' => $invoice->invoice_number,
			'client_email'   => $invoice->client_email,
			'client_name'    => $invoice->client_name,
			'publishable_key' => $keys['public_key'],
		];

		// Store in transient temporarily (5 minutes)
		set_transient( 'ays_checkout_' . $invoice_id, $checkout_data, 5 * MINUTE_IN_SECONDS );

		// Redirect to checkout page (or show modal)
		wp_redirect(
			add_query_arg(
				[
					'page'  => 'ays-checkout',
					'inv'   => $invoice_id,
				],
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Get checkout session data
	 * Used by checkout page/modal
	 *
	 * @param int $invoice_id Invoice ID.
	 * @return array|null
	 */
	public static function get_checkout_data( $invoice_id ) {
		return get_transient( 'ays_checkout_' . $invoice_id );
	}
}

// Initialize on plugin load
if ( function_exists( 'add_action' ) ) {
	add_action( 'wp_loaded', [ 'AYS_Stripe_Handler', 'init' ] );
}
