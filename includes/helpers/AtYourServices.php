<?php
namespace ays\includes\helpers;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// Define a custom constant for the directory separator to enhance code readability
define('AYS_DS', DIRECTORY_SEPARATOR);

// Include necessary files
require_once AYS_DS . 'includes/posttypes/ays-cpt-service.php';
require_once AYS_DS . 'includes/posttypes/ays-cpt-team.php';
require_once AYS_DS . 'includes/posttypes/ays-cpt-faq.php';
require_once AYS_DS . 'includes/posttypes/ays-cpt-review.php';
require_once AYS_DS . 'includes/posttypes/ays-cpt-location.php';
require_once AYS_DS . 'includes/taxonomies/ays-taxonomy-service-type.php';
require_once AYS_DS . 'includes/taxonomies/ays-taxonomy-price-range.php';

// Activation hook
function ays_activate() {
    // Code to run on activation
}
register_activation_hook( __FILE__, 'ays_activate' );

// Deactivation hook
function ays_deactivate() {
    // Code to run on deactivation
}
register_deactivation_hook( __FILE__, 'ays_deactivate' );

// Initialize the plugin
function ays_init() {
    // Custom post types and taxonomies registration
}
add_action( 'init', 'ays_init' );

// Enqueue scripts and styles
function ays_enqueue_scripts() {
    wp_enqueue_style( 'ays-style', plugins_url( 'assets/css/style.css', __FILE__ ) );
    wp_enqueue_script( 'ays-script', plugins_url( 'assets/js/script.js', __FILE__ ), array( 'jquery' ), null, true );
}
add_action( 'wp_enqueue_scripts', 'ays_enqueue_scripts' );

// Shortcodes
function ays_service_shortcode( $atts ) {
    // Shortcode logic here
}
add_shortcode( 'ays_service', 'ays_service_shortcode' );

// Gutenberg block registration
function ays_register_blocks() {
    // Block registration logic here
}
add_action( 'init', 'ays_register_blocks' );

?>
