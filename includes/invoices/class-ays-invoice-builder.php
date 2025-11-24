<?php
// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * AYS_Invoice_Builder_Page
 *
 * Registers a (hidden) admin submenu page for the unified Invoice Builder
 * and enqueues dedicated assets on that screen only.
 */
class AYS_Invoice_Builder_Page {

    const SLUG = 'ays-invoice-builder';

    public static function init() {
        add_action('admin_menu', [self::class, 'register_page'], 31);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_assets']);
    }

    public static function register_page() {
        $hook = add_submenu_page(
            'ays-dashboard',
            __('Invoice Builder', 'atyourservice'),
            __('Invoice Builder', 'atyourservice'),
            'manage_options',
            self::SLUG,
            [self::class, 'render']
        );
        // Hide from menu; it is navigated programmatically
        add_action('admin_head', function(){
            remove_submenu_page('ays-dashboard', self::SLUG);
        });
        return $hook;
    }

    public static function enqueue_assets($hook_suffix) {
        // Load only when our builder page is shown
        if (strpos($hook_suffix, self::SLUG) === false) {
            return;
        }

        // Styles
        $css_url = plugins_url('assets/css/ays-invoice-builder.css', AYS_PLUGIN_PATH . 'ays.php');
        wp_enqueue_style('ays-invoice-builder', $css_url, [], '1.0.0');

        // Scripts (depend on jquery and underscore for simple escaping)
        $js_url = plugins_url('assets/js/ays-invoice-builder.js', AYS_PLUGIN_PATH . 'ays.php');
        wp_enqueue_script('ays-invoice-builder', $js_url, ['jquery','underscore'], '1.0.0', true);

        wp_localize_script('ays-invoice-builder', 'ays_builder', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('ays_invoice_nonce'),
            'saving'   => __('Saving...', 'atyourservice'),
            'saved'    => __('Saved!', 'atyourservice'),
            'error'    => __('Error saving invoice.', 'atyourservice')
        ]);
    }

    public static function render() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.', 'atyourservice'));
        }
        $path = AYS_PLUGIN_PATH . 'includes/pages/ays-invoice-builder.php';
        if (file_exists($path)) {
            include $path;
        } else {
            echo '<div class="wrap"><div class="notice notice-error"><p>' . esc_html__('Builder template missing.', 'atyourservice') . '</p></div></div>';
        }
    }
}
