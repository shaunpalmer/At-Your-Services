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
require_once AYS_PLUGIN_PATH . 'includes/helpers/autoloader.php';
// Register the autoloader
Ays_Autoloader::register();

//  Include the enqueue.php file
require_once AYS_PLUGIN_PATH . 'admin/enqueue.php';
require_once AYS_PLUGIN_PATH . 'includes/shortcode/ays_shortcodes.php';
// Admin pages (autoloaded classes) instantiate
// Instantiate admin pages once (files no longer self-instantiate)
global $ays_admin_instances;
if ( ! isset( $ays_admin_instances ) ) { $ays_admin_instances = []; }
foreach ( [ 'Ays_Lead_Dashboard_Admin', 'Ays_Leads_Export_Page' ] as $admin_class ) {
	if ( class_exists( $admin_class ) && empty( $ays_admin_instances[ $admin_class ] ) ) {
		$ays_admin_instances[ $admin_class ] = new $admin_class();
	}
}

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

