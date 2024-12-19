<?php

/**
 * Plugin Name: At Your Service
 * Plugin URI: https://project-studios.nz/atyourservice
 * Description: Manage your operations with job tracking, CRM, invoicing, and more—perfect for contractors, cleaners, and service pros.
 * Version: 0.1.3
 * Author: Shaun Palmer
 * Author URI: https://project-studios.nz
 * Text Domain: atyourservice
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package At Your Service
 * @since 0.1.3
 * Requires PHP: 7.2
 * Copyright 2024-2030 Shaun Palmer (email: shaun@projectstudios.nz OR shaun.palmer@gmail.com)
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

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin path constants
if (!defined('AYS_PLUGIN_PATH')) {
    define('AYS_PLUGIN_PATH', plugin_dir_path(__FILE__));
}

// Hook into WordPress initialization
add_action('init', function () {
    try {
        // Include the bootstrapper
        require_once AYS_PLUGIN_PATH . 'includes/core/AYS_Bootstrapper.php';

        // Bootstrap the plugin
        \ays\includes\core\AYS_Bootstrapper::bootstrap();
    } catch (Exception $e) {
        // Log the error and display a notice if necessary
        error_log('AYS Plugin Initialization Error: ' . $e->getMessage());
        add_action('admin_notices', function () use ($e) {
            echo '<div class="notice notice-error"><p>AYS Plugin failed to initialize: ' . esc_html($e->getMessage()) . '</p></div>';
        });
    }
});

/**
 * Captain’s Log:
 * All custom post types and taxonomies are registered and ready for action.
 * Engage your plugin’s features and may the debugging gods smile upon you! 🌟
 */
