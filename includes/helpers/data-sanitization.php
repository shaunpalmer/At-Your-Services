<?php
/**
 * Input sanitization helper functions.
 *
 * These wrappers centralize common sanitation behavior used across forms.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'ays_sanitize_text' ) ) {
    /**
     * Sanitize scalar text input.
     */
    function ays_sanitize_text( $value ) {
        return sanitize_text_field( wp_unslash( (string) $value ) );
    }
}

if ( ! function_exists( 'ays_sanitize_email' ) ) {
    /**
     * Sanitize and validate email input.
     */
    function ays_sanitize_email( $value ) {
        $email = sanitize_email( wp_unslash( (string) $value ) );
        return is_email( $email ) ? $email : '';
    }
}

if ( ! function_exists( 'ays_sanitize_textarea' ) ) {
    /**
     * Sanitize textarea input.
     */
    function ays_sanitize_textarea( $value ) {
        return sanitize_textarea_field( wp_unslash( (string) $value ) );
    }
}
