<?php
/**
 * Session Configuration for wp-config.php
 * 
 * Copy and paste these lines into your wp-config.php file
 * (typically: c:\xampp\htdocs\projectstudios\wp-config.php)
 * 
 * Add them BEFORE the line: require_once(ABSPATH . 'wp-settings.php');
 */

// ============================================
// PASTE THIS INTO wp-config.php
// ============================================

// Session configuration for local development
// Ensures sessions persist across admin POST requests
if ( ! defined( 'COOKIE_DOMAIN' ) ) {
	define( 'COOKIE_DOMAIN', 'projectstudios.local' );
}

if ( ! defined( 'COOKIEPATH' ) ) {
	define( 'COOKIEPATH', '/' );
}

// Increase session timeout to 48 hours (in seconds)
// Default is 2 days, this makes it explicit
if ( ! defined( 'ABSPATH' ) ) {
	@define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

// Optional: Extend session timeout even longer if needed
define( 'AUTH_COOKIE_EXPIRATION', 172800 ); // 2 days in seconds

// ============================================
// END PASTE
// ============================================
