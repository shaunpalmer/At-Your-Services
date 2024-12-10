<?php

}
/**
 * 
 * Plugin Name: At Your Service
 * Plugin URI: https://project-studios.nz/atyourservice
 * Description: Manage your  with job tracking, CRM, invoicing, and more—perfect for contractors, cleaners, and service pros.
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
if (!defined('ABSPATH')) {
    exit;
// this is plugin's main file
// Include the constants file using forward slashes

// Include the autoloader
// Define the autoloader path using DIRECTORY_SEPARATOR for cross-platform compatibility
#$autoloader_path = AYS_PLUGIN_PATH . 'includes' . AYS_DS . 'helpers' . AYS_DS . 'autoloader.php';
/**
 * Initializes the AYS Plugin.
 */
function ays_plugin_initialize() {
    $bootstrapper_path = AYS_PLUGIN_PATH . 'includes/core/ays_core.php';

    try {
        if (is_file($bootstrapper_path)) {
            require_once $bootstrapper_path;
            ays\includes\core\AYS_Bootstrapper::bootstrap();
        } else {
            throw new Exception('AYS Plugin Bootstrapper not found. Plugin cannot be initialized.');
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}
add_action('init', 'ays_plugin_initialize');

// Ensure the autoloader is registered only once
if (! class_exists('AYS_ClassAutoloader')) {
    // Include the autoloader file
    #require_once AYS_PLUGIN_PATH . 'includes' . AYS_DS . 'classes' . AYS_DS . 'ays_CustomPostTypeLoader.php';
    require_once AYS_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'autoloader.php';

    // Ensure the autoloader is registered only once.
    if (! class_exists('ays\includes\helpers\AYS_ClassAutoloader')) {
        AYS_ClassAutoloader::register();
        
    }
}
//  Include the enqueue.php file
require_once AYS_PLUGIN_PATH . 'admin/enqueue.php';
// Include the autoloader file early
#require_once AYS_PLUGIN_PATH . AYS_DS .'ays_class_initializer.php';
/**
 * Include Settings Page
 */

require_once AYS_PLUGIN_PATH  . 'includes/settings/settings.php';
require_once AYS_PLUGIN_PATH . 'includes/shortcode/ays_shortcodes.php';
# *  Captain's We've engage taxonomies!” 🖖

/**
 * Captain’s Log:
 * All custom post types and taxonomies are registered and ready for action.
 * Engage your plugin’s features and may the debugging gods smile upon you! 🌟
 */
code we just
code we just wrote 
// Ensure this file is being run within WordPress
<!-- if (!defined('ABSPATH')) {
    exit;
}

// Define plugin path constants if not already defined
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
This is a bootstrapper class call that we just wrote preserve all of this up to code we just wrote
Code we just wrote -->
