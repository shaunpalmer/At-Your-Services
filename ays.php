<?php
/**
 * 
 * Plugin Name: At Your Service
 * Plugin URI: https://project-studios.nz/atyourservice
 * Description: Manage your service business with job tracking, CRM, invoicing, and more—perfect for contractors, cleaners, and service pros.
 *
 * Version: 1.2.3
 * Author: Shaun Palmer
 * Author URI: https://project-studios.nz
 * Text Domain: atyourservice
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package At Your Service
 * @author  Shaun Palmer
 * @since 1.2.3
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

// Exit if accessed directly
if(!defined('ABSPATH')){
	exit;
}

// Define plugin version constant
define('AYS_PLUGIN_VERSION', '1.2.3');

// Define plugin path constant if not already defined.
if ( ! defined( 'AYS_PLUGIN_PATH' ) ) {
    define( 'AYS_PLUGIN_PATH', wp_normalize_path( plugin_dir_path( __FILE__ ) ) );
}

// Load the autoloader
require_once AYS_PLUGIN_PATH . 'includes/helpers/autoloader.php';

// Register the autoloader
Ays_Autoloader::register();

// Include the enqueue.php file
require_once AYS_PLUGIN_PATH . 'admin/enqueue.php';

// Include Custom Post Type Classes.
require_once AYS_PLUGIN_PATH . 'includes/taxonomies/ays-taxonomy-service-type.php';
require_once AYS_PLUGIN_PATH . 'includes/taxonomies/ays-taxonomy-price-range.php';
require_once AYS_PLUGIN_PATH . 'includes/taxonomies/ays-taxonomy-neighbourhood.php';
require_once AYS_PLUGIN_PATH . 'includes/shortcode/ays_shortcodes.php';

// Initialize Custom Post Types
new Ays_CPT_Service();
new Ays_CPT_Team();
new Ays_CPT_Review();
new Ays_CPT_Location();
new Ays_CPT_FAQ();

// Initialize Taxonomies
new Ays_Taxonomy_Service_Type();
new Ays_Taxonomy_Price_Range();
new Ays_Taxonomy_Neighbourhood();

/**
 * Plugin initialization complete.
 * All custom post types and taxonomies are registered and ready.
 */