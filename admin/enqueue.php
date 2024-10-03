<?php
# Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
exit;
}
// Enqueue Bootstrap CSS and JS
function ays_service_form_enqueue_styles() {
// Bootstrap CSS
wp_enqueue_style(
'bootstrap-css',
'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css'
);

    // Bootstrap JS
    wp_enqueue_script(
        'bootstrap-js',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js',
        array(),
        null,
        true
    );

}
add_action('wp_enqueue_scripts', 'ays_service_form_enqueue_styles');

function enqueue_ays_styles() {
    if ( is_singular() && has_shortcode( get_post()->post_content, 'ays_service_form' ) ) {
        wp_enqueue_style( 'ays-lead-landing-css', plugin_dir_url(__FILE__) . 'public/css/ays-lead-landing.css' );
    }
}
add_action( 'wp_enqueue_scripts', 'enqueue_ays_styles' );

