<?php
/**
 * Manages per-invoice sidebar preferences (colors, document type, send mode).
 */

defined( 'ABSPATH' ) || exit;

class AYS_Invoice_Preferences {
	protected static function table() {
		global $wpdb;
		return $wpdb->prefix . 'ays_invoice_preferences';
	}

	public static function defaults() {
		return [
			'invoice_id'     => 0,
			'document_type'  => 'invoice',
			'auto_send_mode' => 'manual',
			'send_admin_copy'=> 0,
			'primary_color'  => '#4c51bf',
			'accent_color'   => '#111827',
			'settings'       => [],
		];
	}

	public static function get( $invoice_id ) {
		global $wpdb;
		$invoice_id = intval( $invoice_id );
		if ( ! $invoice_id ) {
			return self::defaults();
		}

		$table = self::table();
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE invoice_id = %d LIMIT 1", $invoice_id ), ARRAY_A );

		if ( ! $row ) {
			$defaults = self::defaults();
			$defaults['invoice_id'] = $invoice_id;
			return $defaults;
		}

		$settings = [];
		if ( ! empty( $row['settings_json'] ) ) {
			$settings = json_decode( $row['settings_json'], true );
			if ( ! is_array( $settings ) ) {
				$settings = [];
			}
		}

		return array_merge( self::defaults(), [
			'invoice_id'     => (int) $row['invoice_id'],
			'document_type'  => $row['document_type'] ?: 'invoice',
			'auto_send_mode' => $row['auto_send_mode'] ?: 'manual',
			'send_admin_copy'=> ! empty( $row['send_admin_copy'] ) ? 1 : 0,
			'primary_color'  => $row['primary_color'] ?: '#4c51bf',
			'accent_color'   => $row['accent_color'] ?: '#111827',
			'settings'       => $settings,
		] );
	}

	public static function save( $invoice_id, $data, $user_id = null ) {
		global $wpdb;
		$invoice_id = intval( $invoice_id );
		if ( ! $invoice_id ) {
			return false;
		}

		$sanitized = self::sanitize_payload( $data );
		$sanitized['invoice_id'] = $invoice_id;
		if ( $user_id ) {
			$sanitized['updated_by'] = (int) $user_id;
		}

		$table    = self::table();
		$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE invoice_id = %d", $invoice_id ) );

		$formats = [ '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%d' ];

		if ( $existing ) {
			$update = $sanitized;
			unset( $update['invoice_id'] );
			return false !== $wpdb->update( $table, $update, [ 'invoice_id' => $invoice_id ], array_slice( $formats, 1 ), [ '%d' ] );
		}

		$sanitized['created_at'] = current_time( 'mysql' );
		return false !== $wpdb->insert( $table, $sanitized, array_merge( $formats, [ '%s' ] ) );
	}

	protected static function sanitize_payload( $data ) {
		$defaults = self::defaults();

		$document_type = isset( $data['document_type'] ) ? sanitize_key( $data['document_type'] ) : $defaults['document_type'];
		$allowed_types = [ 'invoice', 'quote', 'receipt' ];
		if ( ! in_array( $document_type, $allowed_types, true ) ) {
			$document_type = $defaults['document_type'];
		}

		$auto_mode = isset( $data['auto_send_mode'] ) ? sanitize_key( $data['auto_send_mode'] ) : $defaults['auto_send_mode'];
		if ( ! in_array( $auto_mode, [ 'manual', 'auto' ], true ) ) {
			$auto_mode = $defaults['auto_send_mode'];
		}

		$primary = isset( $data['primary_color'] ) ? sanitize_hex_color( $data['primary_color'] ) : null;
		$accent  = isset( $data['accent_color'] ) ? sanitize_hex_color( $data['accent_color'] ) : null;

		$primary = $primary ?: $defaults['primary_color'];
		$accent  = $accent ?: $defaults['accent_color'];

		$send_admin_copy = ! empty( $data['send_admin_copy'] ) ? 1 : 0;

		$settings_payload = [];
		if ( isset( $data['settings_json'] ) ) {
			if ( is_array( $data['settings_json'] ) ) {
				$settings_payload = $data['settings_json'];
			} else {
				$decoded = json_decode( wp_unslash( (string) $data['settings_json'] ), true );
				if ( is_array( $decoded ) ) {
					$settings_payload = $decoded;
				}
			}
		}

		$settings_json = $settings_payload ? wp_json_encode( $settings_payload ) : null;

		return [
			'invoice_id'     => $defaults['invoice_id'],
			'document_type'  => $document_type,
			'auto_send_mode' => $auto_mode,
			'send_admin_copy'=> $send_admin_copy,
			'primary_color'  => $primary,
			'accent_color'   => $accent,
			'settings_json'  => $settings_json,
			'updated_by'     => null,
		];
	}
}
