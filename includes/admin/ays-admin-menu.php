<?php
/**
 * At Your Service - Admin Menu Registration
 *
 * Handles the creation and management of the main plugin menu in WordPress admin.
 * Creates a top-level "At Your Services" menu with submenus for each module.
 *
 * @package At Your Service
 * @subpackage Admin
 * @since 0.1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class AYS_Admin_Menu
 * 
 * Registers and manages the WordPress admin menu for the At Your Service plugin.
 * Provides a centralized dashboard accessible from the main menu.
 * 
 * @since 0.1.3
 */
class AYS_Admin_Menu {

	/**
	 * The slug used for the main dashboard page
	 * 
	 * @var string
	 */
	private static $main_slug = 'ays-dashboard';

	/**
	 * Initialize the menu system
	 * 
	 * Called on admin_menu hook to register menus and pages
	 * 
	 * @since 0.1.3
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', [ self::class, 'register_menus' ] );
		add_action( 'plugin_action_links_' . AYS_PLUGIN_BASENAME, [ self::class, 'add_settings_link' ] );
	}

	/**
	 * Register the main menu and submenus
	 * 
	 * Creates:
	 * - Top-level menu: "At Your Services"
	 * - Submenu: Dashboard
	 * - Submenu: Invoices
	 * - Submenu: Clients
	 * - Submenu: Items
	 * - Submenu: Payments
	 * - Submenu: Reports
	 * - Submenu: Settings
	 * 
	 * @since 0.1.3
	 * @return void
	 */
	public static function register_menus() {

		// Check capability
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Main menu
		add_menu_page(
			__( 'At Your Services', 'atyourservice' ),           // Page title
			__( 'At Your Services', 'atyourservice' ),           // Menu title
			'manage_options',                                     // Capability
			self::$main_slug,                                     // Menu slug
			[ self::class, 'render_dashboard_page' ],            // Callback
			'dashicons-businessperson',                          // Icon
			25                                                    // Position (just below Services)
		);

		// Dashboard submenu (duplicate of main for clarity)
		add_submenu_page(
			self::$main_slug,
			__( 'Dashboard', 'atyourservice' ),
			__( 'Dashboard', 'atyourservice' ),
			'manage_options',
			self::$main_slug,
			[ self::class, 'render_dashboard_page' ]
		);

		// Invoices submenu - Redirects to the main dashboard's invoices tab.
		$invoices_hook = add_submenu_page(
			self::$main_slug,
			__( 'Invoices', 'atyourservice' ),
			__( 'Invoices', 'atyourservice' ),
			'manage_options',
			'ays-invoices',
			'__return_empty_string'
		);
		add_action( 'load-' . $invoices_hook, function() {
			wp_redirect( admin_url( 'admin.php?page=ays-dashboard&tab=invoices' ) );
			exit;
		});

		// Clients submenu - Redirects to the main dashboard's clients tab.
		$clients_hook = add_submenu_page(
			self::$main_slug,
			__( 'Clients', 'atyourservice' ),
			__( 'Clients', 'atyourservice' ),
			'manage_options',
			'ays-clients',
			'__return_empty_string'
		);
		add_action( 'load-' . $clients_hook, function() {
			wp_redirect( admin_url( 'admin.php?page=ays-dashboard&tab=clients' ) );
			exit;
		});

		// Items submenu - Redirects to the main dashboard's items tab.
		$items_hook = add_submenu_page(
			self::$main_slug,
			__( 'Items', 'atyourservice' ),
			__( 'Items', 'atyourservice' ),
			'manage_options',
			'ays-items',
			'__return_empty_string'
		);
		add_action( 'load-' . $items_hook, function() {
			wp_redirect( admin_url( 'admin.php?page=ays-dashboard&tab=items' ) );
			exit;
		});

		// Payments submenu - Uses a page load hook to redirect to the correct tab.
		$hook = add_submenu_page(
			self::$main_slug,
			__( 'Payments', 'atyourservice' ),
			__( 'Payments', 'atyourservice' ),
			'manage_options',
			'ays-payments', // A simple slug is fine now.
			'__return_empty_string' // Use a WP core function that just returns nothing.
		);
		add_action( 'load-' . $hook, [ self::class, 'redirect_to_payments_tab' ] );

		// Reports submenu
		add_submenu_page(
			self::$main_slug,
			__( 'Reports', 'atyourservice' ),
			__( 'Reports', 'atyourservice' ),
			'manage_options',
			'ays-reports',
			[ self::class, 'render_reports_page' ]
		);

		// Settings submenu - Redirects to the main dashboard's settings tab.
		$settings_hook = add_submenu_page(
			self::$main_slug,
			__( 'Settings', 'atyourservice' ),
			__( 'Settings', 'atyourservice' ),
			'manage_options',
			'ays-settings',
			'__return_empty_string'
		);
		add_action( 'load-' . $settings_hook, function() {
			wp_redirect( admin_url( 'admin.php?page=ays-dashboard&tab=settings' ) );
			exit;
		});
	}

	/**
	 * Render the main dashboard page
	 * 
	 * This is the default landing page when users click "At Your Services"
	 * Shows an overview and loads the Invoicing Dashboard UI
	 * 
	 * @since 0.1.3
	 * @return void
	 */
	public static function render_dashboard_page() {
		?>
		<div class="wrap ays-admin-wrap">
			<h1><?php esc_html_e( 'At Your Services - Dashboard', 'atyourservice' ); ?></h1>
			<p><?php esc_html_e( 'Manage your service business operations from here.', 'atyourservice' ); ?></p>
			
			<?php
			// Load the Invoicing Dashboard if available
			if ( class_exists( 'AYS_Invoice_Admin_UI' ) ) {
				// Create instance and render the invoice dashboard
				$invoice_ui = new AYS_Invoice_Admin_UI();
				$invoice_ui->render_page();
			} else {
				echo '<p style="color: #dc3545;">' . esc_html__( 'Invoicing module not loaded.', 'atyourservice' ) . '</p>';
			}
			?>
		</div>
		<?php
	}

	/**
	 * Render the invoices page
	 * 
	 * @since 0.1.3
	 * @return void
	 */
	public static function render_invoices_page() {
		?>
		<div class="wrap ays-admin-wrap">
			<?php
			if ( class_exists( 'AYS_Invoices_Tab' ) ) {
				AYS_Invoices_Tab::render();
			} else {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'Invoices Tab class not found.', 'atyourservice' ) . '</p></div>';
			}
			?>
		</div>
		<?php
	}

	/**
	 * Render the clients page
	 * 
	 * @since 0.1.3
	 * @return void
	 */
	public static function render_clients_page() {
		?>
		<div class="wrap ays-admin-wrap">
			<?php
			if ( class_exists( 'AYS_Clients_Tab' ) ) {
				AYS_Clients_Tab::render();
			} else {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'Clients Tab class not found.', 'atyourservice' ) . '</p></div>';
			}
			?>
		</div>
		<?php
	}

	/**
	 * Render the items page
	 * 
	 * @since 0.1.3
	 * @return void
	 */
	public static function render_items_page() {
		?>
		<div class="wrap ays-admin-wrap">
			<?php
			if ( class_exists( 'AYS_Items_Tab' ) ) {
				AYS_Items_Tab::render();
			} else {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'Items Tab class not found.', 'atyourservice' ) . '</p></div>';
			}
			?>
		</div>
		<?php
	}

	/**
	 * Redirects the user from the virtual payments page to the correct tab.
	 * 
	 * @since 0.1.3
	 * @return void
	 */
	public static function redirect_to_payments_tab() {
		wp_redirect( admin_url( 'admin.php?page=' . self::$main_slug . '&tab=payments' ) );
		exit;
	}

	/**
	 * Render the reports page
	 * 
	 * @since 0.1.3
	 * @return void
	 */
	public static function render_reports_page() {
		?>
		<div class="wrap ays-admin-wrap">
			<h1><?php esc_html_e( 'Reports', 'atyourservice' ); ?></h1>
			<p><?php esc_html_e( 'View financial reports and analytics for your business.', 'atyourservice' ); ?></p>
			
			<div class="notice notice-info inline">
				<p><?php esc_html_e( 'Reporting and analytics features coming soon.', 'atyourservice' ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the settings page
	 * 
	 * Loads the service types settings interface
	 * 
	 * @since 0.1.3
	 * @return void
	 */
	public static function render_settings_page() {
		?>
		<div class="wrap ays-admin-wrap">
			<h1><?php esc_html_e( 'Settings', 'atyourservice' ); ?></h1>
			<p><?php esc_html_e( 'Configure your At Your Services plugin settings.', 'atyourservice' ); ?></p>

			<?php
			// Load Service Types settings if available
			if ( class_exists( 'AYS_Service_Type_Admin' ) ) {
				$service_type_admin = new AYS_Service_Type_Admin();
				$service_type_admin->render_page();
			} else {
				echo '<p style="color: #dc3545;">' . esc_html__( 'Service types module not loaded.', 'atyourservice' ) . '</p>';
			}
			?>
		</div>
		<?php
	}

	/**
	 * Add settings link to plugin action links
	 * 
	 * Adds an "At Your Services" link to the plugin's action links
	 * on the Plugins page for quick access to the dashboard
	 * 
	 * @since 0.1.3
	 * @param array $links Plugin action links
	 * @return array Modified action links
	 */
	public static function add_settings_link( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=' . self::$main_slug ) ),
			esc_html__( 'Dashboard', 'atyourservice' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Get the main dashboard slug
	 * 
	 * @since 0.1.3
	 * @return string The main dashboard page slug
	 */
	public static function get_main_slug() {
		return self::$main_slug;
	}

	/**
	 * Get the URL to the main dashboard
	 * 
	 * @since 0.1.3
	 * @return string The admin URL to the dashboard
	 */
	public static function get_dashboard_url() {
		return admin_url( 'admin.php?page=' . self::$main_slug );
	}
}

// Initialize the menu system
AYS_Admin_Menu::init();
