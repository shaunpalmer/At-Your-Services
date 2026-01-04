<?php
/**
 * At Your Service - Enqueue Scripts and Styles
 *
 * Handles loading of CSS and JavaScript files for the plugin.
 *
 * @package AtYourService
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Enqueue Bootstrap CSS and JS for service forms.
 *
 * @return void
 */
function ays_service_form_enqueue_styles() {
    // Bootstrap CSS
    wp_enqueue_style(
        'ays-bootstrap-css',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css',
        array(),
        '5.3.0'
    );

    // Bootstrap JS
    wp_enqueue_script(
        'ays-bootstrap-js',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js',
        array(),
        '5.3.0',
        true
    );
}
add_action( 'wp_enqueue_scripts', 'ays_service_form_enqueue_styles' );

/**
 * Enqueue lead landing page styles when shortcode is present.
 *
 * @return void
 */
function ays_enqueue_lead_landing_styles() {
    if ( is_singular() && has_shortcode( get_post()->post_content, 'ays_service_form' ) ) {
        wp_enqueue_style(
            'ays-lead-landing-css',
            plugin_dir_url( dirname( __FILE__ ) ) . 'includes/shortcode/css/ays-lead-landing.css',
            array(),
            AYS_PLUGIN_VERSION
        );
    }
}
add_action( 'wp_enqueue_scripts', 'ays_enqueue_lead_landing_styles' );

/**
 * Enqueue main plugin styles.
 *
 * @return void
 */
function ays_enqueue_main_styles() {
    wp_enqueue_style(
        'ays-main-css',
        plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/style.css',
        array(),
        AYS_PLUGIN_VERSION
    );
}
add_action( 'wp_enqueue_scripts', 'ays_enqueue_main_styles' );

