<?php
/**
 * GitHub Update Manager (Singleton)
 *
 * Purpose: Handles self-updates for At Your Service plugin via GitHub.
 * This class ensures only one update check runs per page load to avoid
 * rate limiting on the GitHub API.
 *
 * @package At Your Service
 * @since 0.1.3
 */

namespace AYS\Helpers;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

class AYS_GitHub_Update_Manager {

	/**
	 * @var AYS_GitHub_Update_Manager The single instance of the class
	 */
	private static $instance = null;

	/**
	 * @var object The PUC update checker instance
	 */
	private $updateChecker;

	/**
	 * @var string Plugin slug for options storage
	 */
	private $slug;

	/**
	 * Get the singleton instance
	 *
	 * @param string $repo_url GitHub repository URL
	 * @param string $plugin_file Full path to the main plugin file
	 * @param string $slug Plugin slug
	 * @return AYS_GitHub_Update_Manager
	 */
	public static function get_instance( $repo_url = '', $plugin_file = '', $slug = '' ) {
		if ( self::$instance === null ) {
			self::$instance = new self( $repo_url, $plugin_file, $slug );
		}
		return self::$instance;
	}

	/**
	 * Private constructor to prevent multiple instantiations
	 *
	 * @param string $repo_url GitHub repository URL
	 * @param string $plugin_file Full path to the main plugin file
	 * @param string $slug Plugin slug
	 */
	private function __construct( $repo_url, $plugin_file, $slug ) {
		$this->slug = $slug;

		// Ensure the library exists before trying to load
		$vendor_autoload = dirname( $plugin_file ) . '/vendor/autoload.php';
		
		if ( file_exists( $vendor_autoload ) ) {
			require_once $vendor_autoload;

			// Build the update checker
			$this->updateChecker = PucFactory::buildUpdateChecker(
				$repo_url,
				$plugin_file,
				$slug
			);

			// Tell PUC to check for GitHub "Releases" instead of just tags
			// This is better for production stability.
			if ( method_exists( $this->updateChecker->getVcsApi(), 'enableReleaseAssets' ) ) {
				$this->updateChecker->getVcsApi()->enableReleaseAssets();
			}
		}
	}

	/**
	 * Helper to set a private token if the repo is not public
	 *
	 * @param string $token GitHub Personal Access Token
	 * @return AYS_GitHub_Update_Manager Allows chaining
	 */
	public function set_auth_token( $token ) {
		if ( $this->updateChecker && ! empty( $token ) ) {
			$this->updateChecker->setAuthentication( $token );
		}
		return $this; // Allows chaining
	}

	/**
	 * Manually triggers the update check and records the time.
	 *
	 * @return bool True on success, false on failure
	 */
	public function check_now() {
		if ( $this->updateChecker ) {
			$this->updateChecker->checkForUpdates();
			
			// Save the current time to the WP options table
			update_option( $this->slug . '_last_check', current_time( 'mysql' ) );
			
			return true;
		}
		return false;
	}

	/**
	 * Retrieves the last checked time in a human-readable format.
	 *
	 * @return string Human-readable time difference or "Never"
	 */
	public function get_last_checked_time() {
		$last_check = get_option( $this->slug . '_last_check' );
		
		if ( ! $last_check ) {
			return 'Never';
		}

		// Convert to a "2 hours ago" format
		return human_time_diff( strtotime( $last_check ), current_time( 'timestamp' ) ) . ' ago';
	}

	/**
	 * Get the current plugin version
	 *
	 * @return string Current version number
	 */
	public function get_current_version() {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		
		$plugin_file = str_replace( $this->slug, 'ays', $this->slug );
		$plugin_path = WP_PLUGIN_DIR . '/at-your-services/ays.php';
		
		if ( file_exists( $plugin_path ) ) {
			$plugin_data = get_plugin_data( $plugin_path );
			return $plugin_data['Version'];
		}
		
		return '0.1.3'; // Fallback
	}

	// Prevent cloning and unsafely waking up the singleton
	private function __clone() {}
	
	public function __wakeup() {
		throw new \Exception( "Cannot unserialize a singleton." );
	}
}
