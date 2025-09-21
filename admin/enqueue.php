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
    if ( is_singular() ) {
        $content = get_post()->post_content ?? '';
        if ( has_shortcode( $content, 'ays_service_form' ) || has_shortcode( $content, 'ays_lead_form' ) ) {
            // Local plugin-relative CSS (ensure file exists)
            $base_url = plugin_dir_url( dirname( __FILE__ ) );
            $css_path = $base_url . 'assets/css/ays_lead_landing.css';
            wp_enqueue_style( 'ays-lead-landing-css', $css_path, [], '0.2.0' );
            // Flatpickr (lightweight date/time picker)
            wp_enqueue_style( 'flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', [], '4.6.13' );
            wp_enqueue_script( 'flatpickr-js', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js', [], '4.6.13', true );
            wp_add_inline_script( 'flatpickr-js', "document.addEventListener('DOMContentLoaded',function(){if(window.flatpickr){flatpickr('.ays-date',{dateFormat:'Y-m-d',minDate:'today'});flatpickr('.ays-time',{enableTime:true,noCalendar:true,dateFormat:'H:i'});}});" );
        }
    }
}
add_action( 'wp_enqueue_scripts', 'enqueue_ays_styles' );

