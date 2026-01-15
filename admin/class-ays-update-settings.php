<?php
/**
 * GitHub Update Settings Page
 * Provides UI for manual update checks and displays update status
 *
 * @package At Your Service
 * @since 0.1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AYS_Update_Settings_Page {
	
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_settings_page' ], 21 );
		add_action( 'admin_post_ays_check_updates', [ $this, 'handle_check_updates' ] );
	}
	
	public function add_settings_page() {
		add_submenu_page(
			'edit.php?post_type=ays_lead',
			__( 'Software Updates', 'atyourservice' ),
			__( 'Updates', 'atyourservice' ),
			'manage_options',
			'ays-update-settings',
			[ $this, 'render_settings_page' ]
		);
	}
	
	public function handle_check_updates() {
		// Verify nonce
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'check_updates_action' ) ) {
			wp_die( __( 'Security check failed', 'atyourservice' ) );
		}
		
		// Check if user has permission
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have permission to perform this action', 'atyourservice' ) );
		}
		
		// Get the update manager instance and trigger a check
		$manager = \AYS\Helpers\AYS_GitHub_Update_Manager::get_instance(
			AYS_GITHUB_REPO,
			AYS_PLUGIN_PATH . 'ays.php',
			AYS_PLUGIN_SLUG
		);
		
		$manager->check_now();
		
		// Redirect back with success message
		wp_redirect( add_query_arg(
			[ 'page' => 'ays-update-settings', 'updated' => '1' ],
			admin_url( 'edit.php?post_type=ays_lead' )
		) );
		exit;
	}
	
	public function render_settings_page() {
		// Get the update manager instance
		$manager = \AYS\Helpers\AYS_GitHub_Update_Manager::get_instance(
			AYS_GITHUB_REPO,
			AYS_PLUGIN_PATH . 'ays.php',
			AYS_PLUGIN_SLUG
		);
		
		$last_sync = $manager->get_last_checked_time();
		$current_version = $manager->get_current_version();
		
		// Get plugin data for more info
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugin_data = get_plugin_data( AYS_PLUGIN_PATH . 'ays.php' );
		
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			
			<?php if ( isset( $_GET['updated'] ) && $_GET['updated'] === '1' ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php _e( 'Update check completed successfully!', 'atyourservice' ); ?></p>
				</div>
			<?php endif; ?>
			
			<div class="card" style="max-width: 800px;">
				<h2><?php _e( 'Software Updates', 'atyourservice' ); ?></h2>
				
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e( 'Plugin Name', 'atyourservice' ); ?></th>
						<td><strong><?php echo esc_html( $plugin_data['Name'] ); ?></strong></td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Current Version', 'atyourservice' ); ?></th>
						<td><strong><?php echo esc_html( $current_version ); ?></strong></td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Last Sync with GitHub', 'atyourservice' ); ?></th>
						<td><strong><?php echo esc_html( $last_sync ); ?></strong></td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Repository', 'atyourservice' ); ?></th>
						<td>
							<a href="<?php echo esc_url( AYS_GITHUB_REPO ); ?>" target="_blank">
								<?php echo esc_html( AYS_GITHUB_REPO ); ?>
							</a>
						</td>
					</tr>
				</table>
				
				<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top: 20px;">
					<input type="hidden" name="action" value="ays_check_updates">
					<?php wp_nonce_field( 'check_updates_action' ); ?>
					
					<p class="description" style="margin-bottom: 10px;">
						<?php _e( 'Forcing a check ignores the 12-hour cache and queries GitHub immediately.', 'atyourservice' ); ?>
					</p>
					
					<?php submit_button( __( 'Check for Updates Now', 'atyourservice' ), 'primary', 'submit', false ); ?>
				</form>
			</div>
			
			<div class="card" style="max-width: 800px; margin-top: 20px;">
				<h2><?php _e( '🚀 How Updates Work', 'atyourservice' ); ?></h2>
				<p><?php _e( 'This plugin uses a GitHub Self-Updater to bypass the WordPress.org review queue.', 'atyourservice' ); ?></p>
				
				<h3><?php _e( 'Update Process:', 'atyourservice' ); ?></h3>
				<ol>
					<li><?php _e( 'The plugin compares the Version header in the main file with the latest Release Tag on GitHub.', 'atyourservice' ); ?></li>
					<li><?php _e( 'When a new version is found, WordPress displays an "Update Available" notification.', 'atyourservice' ); ?></li>
					<li><?php _e( 'Updates are checked automatically every 12 hours, or you can force a check using the button above.', 'atyourservice' ); ?></li>
				</ol>
				
				<h3><?php _e( 'For Developers:', 'atyourservice' ); ?></h3>
				<ol>
					<li><?php _e( 'Update the version number in the plugin header.', 'atyourservice' ); ?></li>
					<li><?php _e( 'Commit and push your changes to GitHub.', 'atyourservice' ); ?></li>
					<li><?php _e( 'Create a new Git tag matching the version (e.g., v0.1.4).', 'atyourservice' ); ?></li>
					<li><?php _e( 'Push the tag to GitHub - this automatically triggers a release build.', 'atyourservice' ); ?></li>
				</ol>
				
				<p class="description">
					<?php _e( 'The GitHub Actions workflow automatically packages the plugin and creates a release whenever you push a version tag.', 'atyourservice' ); ?>
				</p>
			</div>
		</div>
		<?php
	}
}

// Initialize the settings page
new AYS_Update_Settings_Page();
