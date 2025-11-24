<?php
// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * AYS_Invoice_Renderer
 *
 * Shared read-only invoice renderer used by both Admin Preview and Client View.
 */
class AYS_Invoice_Renderer {

    /**
     * Render an invoice by ID in a clean, read-only layout.
     *
     * @param int    $invoice_id Invoice ID.
     * @param string $context    'client' | 'admin' (affects which actions show).
     */
    public static function render($invoice_id, $context = 'client') {
        global $wpdb;

        $invoice_id = intval($invoice_id);
        if (!$invoice_id) {
            echo '<div class="notice notice-error"><p>' . esc_html__('Invalid invoice.', 'atyourservice') . '</p></div>';
            return;
        }

        $inv_table   = $wpdb->prefix . 'ays_invoices';
        $items_table = $wpdb->prefix . 'ays_invoice_items';
        $clients_tbl = $wpdb->prefix . 'ays_clients';

        $invoice = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$inv_table} WHERE id = %d LIMIT 1", $invoice_id));
        if (!$invoice) {
            echo '<div class="notice notice-error"><p>' . esc_html__('Invoice not found.', 'atyourservice') . '</p></div>';
            return;
        }

        $client = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$clients_tbl} WHERE id = %d LIMIT 1", (int) $invoice->client_id));

        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT description, qty AS quantity, rate, taxable FROM {$items_table} WHERE invoice_id = %d ORDER BY id",
            $invoice_id
        ));

        // Company profile
        $company = class_exists('AYS_Company_Profile') ? AYS_Company_Profile::get_profile() : [];
        $company_name    = $company['company_name'] ?? get_bloginfo('name');
        $company_email   = $company['company_email'] ?? get_option('admin_email');
        $company_phone   = $company['company_phone'] ?? '';
        $company_address = $company['company_address'] ?? '';
        $company_logo_id = isset($company['company_logo_id']) ? (int) $company['company_logo_id'] : 0;
        $company_logo    = $company_logo_id ? wp_get_attachment_image_url($company_logo_id, 'medium') : '';
        $invoice_terms   = $company['invoice_terms'] ?? '';
        $invoice_footer  = $company['invoice_footer'] ?? '';

        // Stripe flag (show pay button for non-paid invoices if configured)
        $stripe_enabled = class_exists('AYS_Stripe_Settings') && method_exists('AYS_Stripe_Settings', 'is_configured') && AYS_Stripe_Settings::is_configured();

        // Normalize meta
        $invoice_number = !empty($invoice->invoice_number) ? $invoice->invoice_number : (!empty($invoice->inv_number) ? $invoice->inv_number : ('INV-' . (int) $invoice->id));
        $issue_date     = !empty($invoice->issue_date) ? date_i18n('Y-m-d', strtotime($invoice->issue_date)) : '';
        $due_date       = !empty($invoice->due_date) ? date_i18n('Y-m-d', strtotime($invoice->due_date)) : '';
        $status         = !empty($invoice->status) ? strtolower($invoice->status) : 'draft';

        // Totals
        $subtotal = isset($invoice->subtotal) ? (float) $invoice->subtotal : 0.0;
        $tax_total = isset($invoice->tax_amount) ? (float) $invoice->tax_amount : 0.0;
        $total = isset($invoice->total) ? (float) $invoice->total : ($subtotal + $tax_total);

        // Client info
        $client_name    = $client->name ?? '';
        $client_email   = $client->email ?? ($client->client_email ?? '');
        $client_phone   = $client->phone ?? '';
        $client_address = $client->address ?? '';

        // Enqueue a lightweight stylesheet for the preview
        self::enqueue_styles();

        // Scope variables passed to the view
        $view_context = [
            'context'        => $context,
            'invoice_id'     => $invoice_id,
            'invoice_number' => $invoice_number,
            'issue_date'     => $issue_date,
            'due_date'       => $due_date,
            'status'         => $status,
            'items'          => $items,
            'subtotal'       => $subtotal,
            'tax_total'      => $tax_total,
            'total'          => $total,
            'company_logo'   => $company_logo,
            'company_name'   => $company_name,
            'company_address'=> $company_address,
            'company_email'  => $company_email,
            'company_phone'  => $company_phone,
            'client_name'    => $client_name,
            'client_email'   => $client_email,
            'client_phone'   => $client_phone,
            'client_address' => $client_address,
            'invoice_terms'  => $invoice_terms,
            'invoice_footer' => $invoice_footer,
            'stripe_enabled' => $stripe_enabled,
        ];

        /** @var array $view_context */
        extract($view_context, EXTR_SKIP);

        $view = wp_normalize_path(plugin_dir_path(__FILE__) . 'views/invoice-preview.php');
        if (file_exists($view)) {
            include $view;
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__('Invoice preview template missing.', 'atyourservice') . '</p></div>';
        }
    }

    private static function enqueue_styles() {
        // Best-effort enqueue inside admin; if already enqueued, WP will ignore duplicate handles
        $handle = 'ays-invoice-preview';
        // Anchor to a known file in plugin root to compute correct URL
        $css_url = plugins_url('assets/css/invoice-preview.css', AYS_PLUGIN_PATH . 'index.php');
        wp_enqueue_style($handle, $css_url, [], '1.0');
    }
}
