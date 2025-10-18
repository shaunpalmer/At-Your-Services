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
 * Requires PHP:7.2
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

	// === Invoicing Admin UI ===
	if (is_admin() && class_exists('AYS_Invoice_Admin_UI')) {
		new AYS_Invoice_Admin_UI();
	}
}
add_action('plugins_loaded', 'ays_notifications_bootstrap', 5);

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
	require_once AYS_PLUGIN_PATH . 'includes/invoices/ays-install-invoices.php';
	ays_invoices_install();
}
register_activation_hook(__FILE__, 'ays_notifications_activate');

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