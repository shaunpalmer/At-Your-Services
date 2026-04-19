<?php
/**
 * Shared helper functions loaded by Composer autoload files.
 * Keep this file side-effect free.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'ays_array_get' ) ) {
    /**
     * Read an array key with default fallback.
     *
     * @param array<string,mixed> $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function ays_array_get( array $array, $key, $default = null ) {
        return array_key_exists( $key, $array ) ? $array[ $key ] : $default;
    }
}

if ( ! function_exists( 'ays_normalize_bool' ) ) {
    /**
     * Normalize common WordPress truthy values to bool.
     */
    function ays_normalize_bool( $value ) {
        if ( is_bool( $value ) ) {
            return $value;
        }

        if ( is_numeric( $value ) ) {
            return (int) $value === 1;
        }

        if ( is_string( $value ) ) {
            $value = strtolower( trim( $value ) );
            return in_array( $value, [ '1', 'true', 'yes', 'on' ], true );
        }

        return ! empty( $value );
    }
}
