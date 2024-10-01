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
// C:\xampp\htdocs\WordPressdev\wp-content\plugins
    define('AYS_PLUGIN_VERSION', '1.0.0');
// Define plugin path constant if not already defined.
if ( ! defined( 'AYS_PLUGIN_PATH' ) ) {
    define( 'AYS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

// Include Custom Post Type Classes.
require_once AYS_PLUGIN_PATH . '/includes/post-types/ays-cpt-service.php';
// Initialize CPT classes.  Ays_CPT_Service
require_once AYS_PLUGIN_PATH . 'includes/taxonomies/ays-taxonomy-service-type.php';
require_once AYS_PLUGIN_PATH . 'includes/taxonomies/ays-taxonomy-price-range.php';
require_once AYS_PLUGIN_PATH . 'includes/taxonomies/ays-taxonomy-neighbourhood.php';
require_once AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-team.php';
require_once AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-faq.php';
require_once AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-review.php';
require_once AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-location.php';
/**  
 *  Captain's We've engage Custom post type!” 🖖
 *  Initialize the CPT.
 */
new Ays_CPT_Service(); // ays-cpt-service.php  
new Ays_CPT_Team();
new Ays_CPT_Review();
new Ays_CPT_Location();
new Ays_CPT_FAQ();
/**  
 *  Captain's We've engage taxonomies!” 🖖
 */
// Initialize the class.
new Ays_Taxonomy_Service_Type();
new Ays_Taxonomy_Price_Range();
#new Ays_Taxonomy_Neighbourhood();

/**
 * Captain's Log: All systems are online. The 'At Your Service' plugin is ready for deployment.
 * May our services reach new galaxies of success.
 */


// Include Taxonomy Classes.


#require_once AYS_PLUGIN_PATH . 'includes/taxonomies/ays-taxonomy-neighbourhood.php';

// Initialize other plugin functionalities as needed.
// For example, enqueue scripts, styles, shortcodes, etc.

/**
 * Captain’s Log:
 * All custom post types and taxonomies are registered and ready for action.
 * Engage your plugin’s features and may the debugging gods smile upon you! 🌟
 */