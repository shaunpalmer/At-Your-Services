<?php
/**
 * 
 * Plugin Name: At Your Service
 * Plugin URI: https://project-studios.nz/atyourservice
 * Description: Manage your service business with job tracking, CRM, invoicing, and more—perfect for contractors, cleaners, and service pros.
 *
 * Version: 0.1.3
 * Author: Shaun Palmer
 * Author URI: https://project-studios.nz
 * Text Domain: atyourservice
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package At Your Service
 * @author  Shaun Palmer
 * @since 0.1.3
 * Requires PHP:7.4
 * Copyright 2024-2030 SHAUN PALMER (email: shaun@projectstudios.nz OR shaun.palmer@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

 //Exit if accessed directly: Check for bugs and past
if(!defined('ABSPATH')){
	exit;
}

// Define plugin path constant if not already defined.
if ( ! defined( 'AYS_PLUGIN_PATH' ) ) {
    define( 'AYS_PLUGIN_PATH', wp_normalize_path( plugin_dir_path( __FILE__ ) ) );
}

// Define plugin basename constant
if ( ! defined( 'AYS_PLUGIN_BASENAME' ) ) {
	define( 'AYS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

// Use custom autoloader (classes not yet namespaced for PSR-4)
require_once AYS_PLUGIN_PATH . 'includes/helpers/autoloader.php';
Ays_Autoloader::register();

// Manually include helpers that are not class-based or need to be loaded early
require_once AYS_PLUGIN_PATH . 'includes/helpers/AYS_Email_Validator.php';



//  Include the enqueue.php file
require_once AYS_PLUGIN_PATH . 'admin/enqueue.php';
require_once AYS_PLUGIN_PATH . 'admin/settings.php';
require_once AYS_PLUGIN_PATH . 'includes/shortcode/ays_shortcodes.php';
require_once AYS_PLUGIN_PATH . 'includes/admin/ays-admin-menu.php';
require_once AYS_PLUGIN_PATH . 'includes/admin/ays-admin-dashboard.php';
require_once AYS_PLUGIN_PATH . 'includes/client/class-ays-client-dashboard.php';

// === Notification System Bootstrap ===
require_once AYS_PLUGIN_PATH . 'includes/notifications/AYS_Notification_Settings.php';
require_once AYS_PLUGIN_PATH . 'includes/notifications/AYS_Notification_Cron.php';
require_once AYS_PLUGIN_PATH . 'includes/notifications/AYS_Validation_Cron.php';
require_once AYS_PLUGIN_PATH . 'includes/notifications/AYS_Notification_Router.php';
require_once AYS_PLUGIN_PATH . 'includes/notifications/AYS_Notification_Logger.php';
if (file_exists(AYS_PLUGIN_PATH . 'includes/notifications/AYS_Notifier.php')) {
	require_once AYS_PLUGIN_PATH . 'includes/notifications/AYS_Notifier.php';
}

// Load the Lead Dashboard admin UI under the Leads CPT menu
require_once AYS_PLUGIN_PATH . 'includes/admin/ays-lead-dashboard-loader.php';

function ays_notifications_bootstrap() {
	AYS_Notification_Settings::init();
	AYS_Notification_Cron::init();
    AYS_Validation_Cron::init();
	AYS_Notification_Logger::init();
	if (class_exists('AYS_Notifier')) {
		AYS_Notifier::init();
	}
    if (class_exists('AYS_Notification_Router')) {
        AYS_Notification_Router::init();
    }

	// === Invoicing Admin UI & AJAX Tabs ===
	if (is_admin()) {
		if (class_exists('AYS_Invoice_Admin_UI')) {
			new AYS_Invoice_Admin_UI();
		}
		if (class_exists('AYS_AJAX_Tabs')) {
			AYS_AJAX_Tabs::init();
		}
		// Register hidden preview pages (admin + client)
		if (class_exists('AYS_Invoice_Preview_Page')) {
			AYS_Invoice_Preview_Page::init();
		}
		// Initialize License Management
		if (class_exists('AYS_License_Page')) {
			AYS_License_Page::init();
		}
		// Ensure AJAX handler is registered regardless of UI instantiation timing
		add_action('wp_ajax_ays_load_tab', ['AYS_Invoice_Admin_UI', 'ajax_load_tab']);
	}
}
add_action('plugins_loaded', 'ays_notifications_bootstrap', 5);

/**
 * Deprecated shortcode: [ays_customer_dashboard]
 * This shortcode is no longer supported. Direct users to the secure Client Area in wp-admin.
 */
add_shortcode('ays_customer_dashboard', function () {
	if (function_exists('trigger_error')) {
		// Inform developers in logs that this shortcode is deprecated
		@trigger_error('[ays_customer_dashboard] shortcode is deprecated. Use the Client Area inside wp-admin.', E_USER_DEPRECATED);
	}
	$link = admin_url('admin.php?page=ays-client-dashboard');
	$message  = '<div style="padding:1rem;border:1px solid #ccc;background:#fff3cd;color:#856404;">';
	$message .= '<strong>[ays_customer_dashboard]</strong> has been deprecated.';
	$message .= '<br>Your Client Portal is now located inside your secure WordPress dashboard.';
	$message .= '<br><a href="' . esc_url($link) . '">Go to Client Area</a>';
	$message .= '</div>';
	return $message;
});

function ays_notifications_activate() {
    // Preflight check: Ensure the site can send emails.
    if (!function_exists('wp_mail') || !get_option('admin_email')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            __('The "At Your Service" plugin requires the ability to send emails to function correctly. Please configure a valid admin email address and ensure your WordPress installation can send mail before activating.', 'atyourservice'),
            __('Activation Failed', 'atyourservice'),
            ['back_link' => true]
        );
    }

	if (class_exists('AYS_Notification_Logger')) {
		AYS_Notification_Logger::maybe_create_table();
	}
	if ( ! wp_next_scheduled('ays_notifications_health_ping') ) {
		wp_schedule_event(time() + 60, 'hourly', 'ays_notifications_health_ping');
	}

	// === Invoicing Module DB Installation ===
	// This is now handled by ays_check_and_install_db() on admin_init
}
register_activation_hook(__FILE__, 'ays_notifications_activate');

// Ensure roles and capabilities for front-end access exist
function ays_add_roles_and_caps() {
	// Create or update a basic Customer role with dashboard access
	$customer_caps = [
		'read' => true,
		// Custom capability required to view the front-end customer dashboard
		'access_customer_dashboard' => true,
	];
	if (!get_role('customer')) {
		add_role('customer', __('Customer', 'atyourservice'), $customer_caps);
	} else {
		// Ensure existing role gets our caps
		$role = get_role('customer');
		foreach ($customer_caps as $cap => $grant) {
			if ($grant && $role && !$role->has_cap($cap)) {
				$role->add_cap($cap);
			}
		}
	}

	// Optional: A separate "Client" role for future portal features
	$client_caps = [
		'read' => true,
		'access_client_portal' => true,
	];
	if (!get_role('client')) {
		add_role('client', __('Client', 'atyourservice'), $client_caps);
	} else {
		$role = get_role('client');
		foreach ($client_caps as $cap => $grant) {
			if ($grant && $role && !$role->has_cap($cap)) {
				$role->add_cap($cap);
			}
		}
	}

	// Grant administrators both capabilities by default
	if ($admin = get_role('administrator')) {
		$admin->add_cap('access_customer_dashboard');
		$admin->add_cap('access_client_portal');
	}
}
register_activation_hook(__FILE__, 'ays_add_roles_and_caps');

// Safety net: ensure capabilities are present even if roles already existed
add_action('init', function () {
	// Ensure admin retains caps (in case roles changed outside this plugin)
	if ($admin = get_role('administrator')) {
		if (!$admin->has_cap('access_customer_dashboard')) {
			$admin->add_cap('access_customer_dashboard');
		}
		if (!$admin->has_cap('access_client_portal')) {
			$admin->add_cap('access_client_portal');
		}
	}
	// Ensure customer role has dashboard cap
	$customer = get_role('customer');
	if (!$customer) {
		add_role('customer', __('Customer', 'atyourservice'), [ 'read' => true, 'access_customer_dashboard' => true ]);
	} else if (!$customer->has_cap('access_customer_dashboard')) {
		$customer->add_cap('access_customer_dashboard');
	}

	// Ensure client role exists and has portal cap
	$client = get_role('client');
	if (!$client) {
		add_role('client', __('Client', 'atyourservice'), [ 'read' => true, 'access_client_portal' => true ]);
	} else if (!$client->has_cap('access_client_portal')) {
		$client->add_cap('access_client_portal');
	}
});

/**
 * Checks if the database tables are installed and installs them if not.
 * This is a more reliable way to ensure DB tables are created, especially during development.
 */
function ays_check_and_install_db() {
    if (get_option('ays_db_version') != '0.1.3') {
        require_once AYS_PLUGIN_PATH . 'includes/invoices/ays-install-invoices.php';
        ays_invoices_install();
        update_option('ays_db_version', '0.1.3');
    }

    // Add a trigger to run the seeder script
    $ays_action = isset($_GET['ays_action']) ? sanitize_key(wp_unslash($_GET['ays_action'])) : '';
    if ($ays_action === 'seed_data') {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Unauthorized', 'atyourservice'));
        }
        check_admin_referer('ays_seed_data');
        require_once AYS_PLUGIN_PATH . 'seed-sample-data.php';
        // Redirect to avoid re-seeding on refresh
        wp_redirect(admin_url('admin.php?page=ays-invoicing&ays_notice=seeded'));
        exit;
    }
}
add_action('admin_init', 'ays_check_and_install_db');

function ays_notifications_deactivate() {
	wp_clear_scheduled_hook('ays_notifications_health_ping');
}
register_deactivation_hook(__FILE__, 'ays_notifications_deactivate');

// Wire up lead trigger to notification system
add_action('ays_lead_created', function ($lead_id, $lead_data = []) {
	if (class_exists('AYS_Notification_Settings') && class_exists('AYS_Notification_Cron')) {
		if (AYS_Notification_Settings::get('enabled')) {
			AYS_Notification_Cron::schedule($lead_id);
		}
	}
}, 10, 2);

/* 
# *  Captain's We've engage Custom post type!” 🖖
# *  Initialize the CPT.
 */
// Instantiate CPTs & Taxonomies (autoloaded)
foreach ([
	'Ays_CPT_Service',
	'Ays_CPT_Team',
	'Ays_CPT_Review',
	'Ays_CPT_Location',
	'Ays_CPT_FAQ',
	'Ays_CPT_Lead',
	'Ays_Taxonomy_Service_Type',
	'Ays_Taxonomy_Price_Range',
	'Ays_Taxonomy_Neighbourhood'
] as $ays_class ) {
	if ( class_exists( $ays_class ) ) {
		new $ays_class();
	}
}

/**
 * Captain's Log: All systems are online. The 'At Your Service' plugin is ready for deployment.
 * May our services reach new galaxies of success.
 */


// Initialize other plugin functionalities as needed.
// For example, enqueue scripts, styles, shortcodes, etc.

/**
 * Captain’s Log:
 * All custom post types and taxonomies are registered and ready for action.
 * Engage your plugin’s features and may the debugging gods smile upon you! 🌟
 */

// === Notification admin-post handlers ===
add_action('admin_post_ays_resend_lead', function() {
	if (!current_user_can('manage_options')) wp_die('Denied');
	$lead_id = isset($_GET['lead_id']) ? intval($_GET['lead_id']) : 0;
	check_admin_referer('ays_resend_lead');
	if ($lead_id && class_exists('AYS_Notifier')) {
		$ok = AYS_Notifier::send_for_lead($lead_id);
		if ($ok) {
			AYS_Notification_Logger::log('manual_resend', ['lead_id'=>$lead_id, 'by'=>get_current_user_id()]);
			wp_redirect(add_query_arg(['ays_notice'=>'resent'], admin_url('edit.php?post_type=ays_lead&page=ays-lead-dashboard')));
			exit;
		} else {
			wp_redirect(add_query_arg(['ays_notice'=>'resend_fail'], admin_url('edit.php?post_type=ays_lead&page=ays-lead-dashboard')));
			exit;
		}
	}
	wp_die('Invalid lead ID');
});

add_action('admin_post_ays_send_test_notice', function() {
	if (!current_user_can('manage_options')) wp_die('Denied');
	check_admin_referer('ays_send_test_notice', 'ays_send_test_notice_nonce');
	// Use current user as test recipient
	$user = wp_get_current_user();
	$to = $user && $user->user_email ? $user->user_email : get_option('admin_email');
	$fake_lead = [
		'name' => $user->display_name ?: 'Test User',
		'email' => $to,
		'message' => 'This is a test notification from At Your Service.',
		'service' => 'Test',
		'page_url' => home_url(),
		'timestamp' => current_time('mysql'),
	];
	if (class_exists('AYS_Notifier')) {
		$ok = AYS_Notifier::send_for_lead(0, $fake_lead);
		if ($ok) {
			AYS_Notification_Logger::log('test_send', ['to'=>$to, 'by'=>get_current_user_id()]);
			wp_redirect(add_query_arg(['ays_notice'=>'test_sent'], admin_url('edit.php?post_type=ays_lead&page=ays-lead-dashboard')));
			exit;
		} else {
			wp_redirect(add_query_arg(['ays_notice'=>'test_fail'], admin_url('edit.php?post_type=ays_lead&page=ays-lead-dashboard')));
			exit;
		}
	}
	wp_die('Test send failed');
});

// === Items Tab admin-post handlers ===
add_action('admin_post_ays_add_item', function() {
	if (class_exists('AYS_Items_Tab')) {
		AYS_Items_Tab::handle_add_item();
	} else {
		wp_die('Items class not found');
	}
});

add_action('admin_post_ays_update_item', function() {
	if (class_exists('AYS_Items_Tab')) {
		AYS_Items_Tab::handle_update_item();
	} else {
		wp_die('Items class not found');
	}
});

add_action('admin_post_ays_delete_item', function() {
	if (class_exists('AYS_Items_Tab')) {
		AYS_Items_Tab::handle_delete_item();
	} else {
		wp_die('Items class not found');
	}
});

// === Service Types Tab admin-post handlers ===
add_action('admin_post_ays_add_service_type', function() {
	if (class_exists('AYS_Service_Types_Tab')) {
		AYS_Service_Types_Tab::handle_add_service_type();
	} else {
		wp_die('Service Types class not found');
	}
});

add_action('admin_post_ays_update_service_type', function() {
	if (class_exists('AYS_Service_Types_Tab')) {
		AYS_Service_Types_Tab::handle_update_service_type();
	} else {
		wp_die('Service Types class not found');
	}
});

add_action('admin_post_ays_delete_service_type', function() {
	if (class_exists('AYS_Service_Types_Tab')) {
		AYS_Service_Types_Tab::handle_delete_service_type();
	} else {
		wp_die('Service Types class not found');
	}
});

// === Clients Tab admin-post handlers ===
add_action('admin_post_ays_add_client', function() {
	if (class_exists('AYS_Clients_Tab')) {
		AYS_Clients_Tab::handle_add_client();
	} else {
		wp_die('Clients class not found');
	}
});

add_action('admin_post_ays_update_client', function() {
	if (class_exists('AYS_Clients_Tab')) {
		AYS_Clients_Tab::handle_update_client();
	} else {
		wp_die('Clients class not found');
	}
});

add_action('admin_post_ays_delete_client', function() {
	if (class_exists('AYS_Clients_Tab')) {
		AYS_Clients_Tab::handle_delete_client();
	} else {
		wp_die('Clients class not found');
	}
});

// === Invoices Tab admin-post handlers ===
add_action('admin_post_ays_create_invoice', function() {
	if (class_exists('AYS_Invoices_Tab')) {
		AYS_Invoices_Tab::handle_create_invoice();
	} else {
		wp_die('Invoices class not found');
	}
});

add_action('admin_post_ays_delete_invoice', function() {
	if (class_exists('AYS_Invoices_Tab')) {
		AYS_Invoices_Tab::handle_delete_invoice();
	} else {
		wp_die('Invoices class not found');
	}
});

add_action('admin_post_ays_update_invoice_services', function() {
	if (class_exists('AYS_Invoices_Tab')) {
		AYS_Invoices_Tab::handle_update_invoice_services();
	} else {
		wp_die('Invoices class not found');
	}
});

// Add/Delete invoice line items
add_action('admin_post_ays_add_invoice_item', function() {
	if (class_exists('AYS_Invoices_Tab')) {
		AYS_Invoices_Tab::handle_add_invoice_item();
	} else {
		wp_die('Invoices class not found');
	}
});

add_action('admin_post_ays_delete_invoice_item', function() {
	if (class_exists('AYS_Invoices_Tab')) {
		AYS_Invoices_Tab::handle_delete_invoice_item();
	} else {
		wp_die('Invoices class not found');
	}
});

// === Payments Tab admin-post handlers ===
add_action('admin_post_ays_add_payment', function() {
	if (class_exists('AYS_Payments_Tab')) {
		AYS_Payments_Tab::handle_add_payment();
	} else {
		wp_die('Payments class not found');
	}
});

add_action('admin_post_ays_update_payment', function() {
	if (class_exists('AYS_Payments_Tab')) {
		AYS_Payments_Tab::handle_update_payment();
	} else {
		wp_die('Payments class not found');
	}
});

add_action('admin_post_ays_delete_payment', function() {
	if (class_exists('AYS_Payments_Tab')) {
		AYS_Payments_Tab::handle_delete_payment();
	} else {
		wp_die('Payments class not found');
	}
});

// === Stripe Payment Handler ===
add_action('admin_post_ays_stripe_checkout', function() {
	if (class_exists('AYS_Stripe_Handler')) {
		AYS_Stripe_Handler::handle_checkout();
	} else {
		wp_die('Stripe handler not found');
	}
});

add_action('admin_post_nopriv_ays_stripe_checkout', function() {
	if (class_exists('AYS_Stripe_Handler')) {
		AYS_Stripe_Handler::handle_checkout();
	} else {
		wp_die('Stripe handler not found');
	}
});

// === Link current WP user to an AYS client record (create if missing) ===
add_action('admin_post_ays_link_client_account', function() {
	if (!is_user_logged_in()) {
		wp_die('Not logged in');
	}
	check_admin_referer('ays_link_client_account');
	$user = wp_get_current_user();
	if (!$user || empty($user->user_email)) {
		wp_die('Missing user email');
	}
	global $wpdb;
	$table = $wpdb->prefix . 'ays_clients';
	// Try find existing by email
	$client = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE email = %s OR secondary_email = %s LIMIT 1", $user->user_email, $user->user_email));
	if (!$client) {
		$insert = [
			'hash' => md5(uniqid('client_', true)),
			'name' => $user->display_name ?: $user->user_nicename ?: $user->user_login,
			'email' => $user->user_email,
			'status' => 'active',
			'created_at' => current_time('mysql'),
			'updated_at' => current_time('mysql'),
		];
		$wpdb->insert($table, $insert);
		$client_id = (int) $wpdb->insert_id;
	} else {
		$client_id = (int) $client->id;
	}
	if ($client_id) {
		update_user_meta($user->ID, 'ays_client_id', $client_id);
		// Redirect back to invoices view
		wp_safe_redirect(admin_url('admin.php?page=ays-client-invoices&ays_notice=linked'));
		exit;
	}
	wp_die('Unable to link client');
});
