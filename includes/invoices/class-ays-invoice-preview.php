<?php
// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * AYS_Invoice_Preview_Page
 *
 * Registers hidden admin pages to render invoice previews for:
 * - Administrators: full admin preview page
 * - Clients/Customers: client invoice view under Client Area
 */
class AYS_Invoice_Preview_Page {

    public static function init() {
        // Register after the Client Area menu (which uses priority 20)
        add_action('admin_menu', [self::class, 'register_pages'], 30);
    }

    public static function register_pages() {
        // Admin preview (manage_options)
        $admin_hook = add_submenu_page(
            'ays-dashboard',
            __('Invoice Preview', 'atyourservice'),
            __('Invoice Preview', 'atyourservice'),
            'manage_options',
            'ays-invoice-preview',
            [self::class, 'render_admin_preview']
        );
        // Hide from menu
        add_action('admin_head', function() {
            remove_submenu_page('ays-dashboard', 'ays-invoice-preview');
        });

        // Client preview (read + custom caps); mounted under Client Area menu
        $client_hook = add_submenu_page(
            'ays-client-dashboard',
            __('View Invoice', 'atyourservice'),
            __('View Invoice', 'atyourservice'),
            'read',
            'ays-client-invoice-view',
            [self::class, 'render_client_preview']
        );
        // Hide from Client Area submenu
        add_action('admin_head', function() {
            remove_submenu_page('ays-client-dashboard', 'ays-client-invoice-view');
        });
    }

    public static function render_admin_preview() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to view this page.', 'atyourservice'));
        }
        $invoice_id = isset($_GET['invoice_id']) ? intval($_GET['invoice_id']) : 0;
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Invoice Preview', 'atyourservice') . '</h1>';
        if (!$invoice_id) {
            echo '<div class="notice notice-warning"><p>' . esc_html__('Missing invoice ID.', 'atyourservice') . '</p></div>';
            echo '</div>';
            return;
        }
        if (class_exists('AYS_Invoice_Renderer')) {
            AYS_Invoice_Renderer::render($invoice_id, 'admin');
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__('Invoice renderer not available.', 'atyourservice') . '</p></div>';
        }
        echo '</div>';
    }

    public static function render_client_preview() {
        // Require our custom access caps for client area
        if (!current_user_can('access_customer_dashboard') && !current_user_can('access_client_portal')) {
            wp_die(__('You do not have access to the client area.', 'atyourservice'));
        }
        $invoice_id = isset($_GET['invoice_id']) ? intval($_GET['invoice_id']) : 0;
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Invoice', 'atyourservice') . '</h1>';
        if (!$invoice_id) {
            echo '<div class="notice notice-warning"><p>' . esc_html__('Missing invoice ID.', 'atyourservice') . '</p></div>';
            echo '</div>';
            return;
        }

        // Verify the invoice belongs to the current client
        global $wpdb;
        $inv_tbl = $wpdb->prefix . 'ays_invoices';
        $inv_row  = $wpdb->get_row($wpdb->prepare("SELECT client_id, status, viewed_at FROM {$inv_tbl} WHERE id = %d", $invoice_id));
        $client_id = $inv_row ? (int) $inv_row->client_id : 0;

        if (!$client_id) {
            echo '<div class="notice notice-error"><p>' . esc_html__('Invoice not found.', 'atyourservice') . '</p></div>';
            echo '</div>';
            return;
        }

        // Find logged-in user's client record
        $user = wp_get_current_user();
        $clients_tbl = $wpdb->prefix . 'ays_clients';
        $client = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$clients_tbl} WHERE id = %d LIMIT 1",
            $client_id
        ));

        $matches_user = false;
        if ($client && $user && !empty($user->user_email)) {
            $client_email = isset($client->email) ? $client->email : (isset($client->client_email) ? $client->client_email : '');
            $matches_user = strcasecmp(trim($client_email), trim($user->user_email)) === 0;
        }

        if (!$matches_user && !current_user_can('manage_options')) {
            wp_die(__('You cannot view this invoice.', 'atyourservice'));
        }

        // Mark as viewed (first-view time) and optionally bump status from draft/sent -> viewed
        // Only update if not already viewed and not paid/void
        if ($inv_row) {
            $current_status = isset($inv_row->status) ? strtolower((string) $inv_row->status) : 'draft';
            $viewed_at_val  = isset($inv_row->viewed_at) ? $inv_row->viewed_at : null;
            $update = [];

            if (empty($viewed_at_val)) {
                $update['viewed_at'] = current_time('mysql');
            }

            if (in_array($current_status, ['draft','sent'], true)) {
                $update['status'] = 'viewed';
            }

            if (!empty($update)) {
                $update['updated_at'] = current_time('mysql');
                $wpdb->update($inv_tbl, $update, ['id' => $invoice_id], null, ['%d']);
            }
        }

        if (class_exists('AYS_Invoice_Renderer')) {
            AYS_Invoice_Renderer::render($invoice_id, 'client');
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__('Invoice renderer not available.', 'atyourservice') . '</p></div>';
        }

        echo '<p style="margin-top:12px;"><a class="button" href="' . esc_url(admin_url('admin.php?page=ays-client-invoices')) . '">' . esc_html__('Back to Invoices', 'atyourservice') . '</a></p>';
        echo '</div>';
    }
}
