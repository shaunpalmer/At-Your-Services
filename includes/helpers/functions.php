<?php
namespace ays\includes\helpers;
//Exit if accessed directly: Check for bugs and past
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Enqueue jQuery with Dismissible Admin Notice for Errors
 */
// function check_and_enqueue_jquery_with_notice() {
//     try {
//         global $wp_scripts;

//         if (!$wp_scripts) {
//             throw new Exception('WP_Scripts object not available.');
//         }

//         if (!wp_script_is('jquery', 'enqueued')) {
//             wp_enqueue_script('jquery');
//         }
//     } catch (Exception $e) {
//         // Store the error message to display as a dismissible admin notice
//         add_action('admin_notices', function () use ($e) {
//             printf(
//                 '<div class="notice notice-error is-dismissible">
//                     <p>%s</p>
//                     <button type="button" class="notice-dismiss">
//                         <span class="screen-reader-text">%s</span>
//                     </button>
//                 </div>',
//                 esc_html('Error: ' . $e->getMessage()),
//                 esc_html__('Dismiss this notice', 'text-domain')
//             );
//         });
//     }
// }
// add_action('wp_enqueue_scripts', 'check_and_enqueue_jquery_with_notice');