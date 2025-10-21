<?php
// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * AYS Client Portal (Admin-side for Customer/Client roles)
 *
 * Adds a Client Portal menu under wp-admin for users in roles `customer` or `client`.
 * Renders Invoices (and a Payments stub) filtered to the logged-in user's client record.
 */
class AYS_Client_Dashboard {
    public static function init() {
        add_action('admin_menu', [self::class, 'register_menu'], 20);
    }

    /**
     * Register the Client Area admin menu for customer/client roles only.
     */
    public static function register_menu() {
        if (!is_user_logged_in()) {
            return;
        }
        $user = wp_get_current_user();
        $roles = (array) $user->roles;
        $is_clientish = in_array('customer', $roles, true) || in_array('client', $roles, true);
        if (!$is_clientish) {
            return;
        }

        // Additionally require one of our access caps to be future-proof
        if (!current_user_can('access_customer_dashboard') && !current_user_can('access_client_portal')) {
            return;
        }

        // Top-level Client Portal
        add_menu_page(
            __('Client Portal', 'atyourservice'),
            __('Client Portal', 'atyourservice'),
            'read',
            'ays-client-dashboard',
            [self::class, 'render_client_dashboard'],
            'dashicons-media-spreadsheet',
            2
        );

        // Invoices sub-menu
        add_submenu_page(
            'ays-client-dashboard',
            __('Invoices', 'atyourservice'),
            __('Invoices', 'atyourservice'),
            'read',
            'ays-client-invoices',
            [self::class, 'render_client_invoices']
        );

        // Payments sub-menu (stub)
        add_submenu_page(
            'ays-client-dashboard',
            __('Payments', 'atyourservice'),
            __('Payments', 'atyourservice'),
            'read',
            'ays-client-payments',
            [self::class, 'render_client_payments']
        );
    }

    /**
     * Helper: Get the logged-in user's matching AYS client row by email.
     */
    private static function get_current_client() {
        global $wpdb; 
        $user = wp_get_current_user();
        if (!$user || empty($user->user_email)) {
            return null;
        }
        $table = $wpdb->prefix . 'ays_clients';
        // Prefer `email` column, but also check `client_email` if schema varies
        $client = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE email = %s OR client_email = %s LIMIT 1",
            $user->user_email, $user->user_email
        ));
        return $client;
    }

    /**
    * Render the top-level Client Portal intro page.
     */
    public static function render_client_dashboard() {
        if (!current_user_can('access_customer_dashboard') && !current_user_can('access_client_portal')) {
            echo '<div class="notice notice-warning"><p>' . esc_html__('You do not have access to the client portal.', 'atyourservice') . '</p></div>';
            return;
        }
        global $wpdb;
        $client = self::get_current_client();
        $name = $client && !empty($client->name) ? $client->name : wp_get_current_user()->display_name;

        // Quick totals
        $total_paid = 0.0;
        $total_due  = 0.0;
        if ($client) {
            $total_paid = (float) $wpdb->get_var($wpdb->prepare(
                "SELECT COALESCE(SUM(total),0) FROM {$wpdb->prefix}ays_invoices WHERE client_id = %d AND status = 'paid'",
                $client->id
            ));
            $total_due = (float) $wpdb->get_var($wpdb->prepare(
                "SELECT COALESCE(SUM(total),0) FROM {$wpdb->prefix}ays_invoices WHERE client_id = %d AND status IN ('sent','overdue')",
                $client->id
            ));
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Client Portal', 'atyourservice'); ?></h1>
            <p><?php printf(esc_html__('Kia ora, %s 👋', 'atyourservice'), esc_html($name)); ?></p>
            <p><?php esc_html_e('Use the menu on the left to view your invoices and payments, or update your details.', 'atyourservice'); ?></p>

            <div style="display:flex; gap:16px; flex-wrap:wrap; margin:16px 0;">
                <div style="flex:1 1 220px; min-width:220px; background:#fff; border:1px solid #e5e7eb; border-radius:6px; padding:16px;">
                    <div style="font-size:12px;color:#6b7280; text-transform:uppercase; letter-spacing:.04em;">Outstanding</div>
                    <div style="font-size:22px; font-weight:700; margin-top:4px; color:#b91c1c;">$<?php echo esc_html(number_format($total_due, 2)); ?></div>
                    <div style="margin-top:8px;"><a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=ays-client-invoices')); ?>"><?php esc_html_e('View invoices', 'atyourservice'); ?></a></div>
                </div>
                <div style="flex:1 1 220px; min-width:220px; background:#fff; border:1px solid #e5e7eb; border-radius:6px; padding:16px;">
                    <div style="font-size:12px;color:#6b7280; text-transform:uppercase; letter-spacing:.04em;">Total Paid</div>
                    <div style="font-size:22px; font-weight:700; margin-top:4px; color:#065f46;">$<?php echo esc_html(number_format($total_paid, 2)); ?></div>
                    <div style="margin-top:8px;"><a class="button" href="<?php echo esc_url(admin_url('admin.php?page=ays-client-payments')); ?>"><?php esc_html_e('Payment history', 'atyourservice'); ?></a></div>
                </div>
            </div>

            <p><a class="button" href="<?php echo esc_url(admin_url('profile.php')); ?>"><?php esc_html_e('Update my details', 'atyourservice'); ?></a></p>
        </div>
        <?php
    }

    /**
     * Render Invoices list for the current client.
     */
    public static function render_client_invoices() {
        if (!current_user_can('access_customer_dashboard') && !current_user_can('access_client_portal')) {
            echo '<div class="notice notice-warning"><p>' . esc_html__('You do not have access to the client portal.', 'atyourservice') . '</p></div>';
            return;
        }
        global $wpdb;
        $client = self::get_current_client();
        echo '<div class="wrap">';
    echo '<h1>' . esc_html__('My Invoices', 'atyourservice') . '</h1>';
        if (!$client) {
            echo '<p>' . esc_html__('We could not find a client account associated with your login.', 'atyourservice') . '</p></div>';
            return;
        }

        $invoices = $wpdb->get_results($wpdb->prepare(
            "SELECT id, invoice_number, inv_number, issue_date, due_date, total, status
             FROM {$wpdb->prefix}ays_invoices
             WHERE client_id = %d
             ORDER BY issue_date DESC, created_at DESC",
            $client->id
        ));

        if (!$invoices) {
            echo '<p>' . esc_html__('No invoices found.', 'atyourservice') . '</p></div>';
            return;
        }

        // Optional: quick stats
        $total_paid = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(total) FROM {$wpdb->prefix}ays_invoices WHERE client_id = %d AND status = 'paid'",
            $client->id
        ));
        $total_due = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(total) FROM {$wpdb->prefix}ays_invoices WHERE client_id = %d AND status IN ('sent','overdue')",
            $client->id
        ));
        echo '<div style="margin:10px 0;">'
           . '<span><strong>' . esc_html__('Total Paid:', 'atyourservice') . '</strong> $' . esc_html(number_format((float)$total_paid, 2)) . '</span>'
           . ' &nbsp; '
           . '<span><strong>' . esc_html__('Outstanding:', 'atyourservice') . '</strong> $' . esc_html(number_format((float)$total_due, 2)) . '</span>'
           . '</div>';

        $stripe_enabled = class_exists('AYS_Stripe_Settings') && method_exists('AYS_Stripe_Settings','is_configured') && AYS_Stripe_Settings::is_configured();

        echo '<table class="widefat striped"><thead><tr>';
        echo '<th>' . esc_html__('Invoice #', 'atyourservice') . '</th>';
        echo '<th>' . esc_html__('Date', 'atyourservice') . '</th>';
        echo '<th>' . esc_html__('Due', 'atyourservice') . '</th>';
        echo '<th style="text-align:right;">' . esc_html__('Total', 'atyourservice') . '</th>';
        echo '<th>' . esc_html__('Status', 'atyourservice') . '</th>';
        echo '<th>' . esc_html__('Actions', 'atyourservice') . '</th>';
        echo '</tr></thead><tbody>';

        foreach ($invoices as $inv) {
            $num = !empty($inv->invoice_number) ? $inv->invoice_number : (!empty($inv->inv_number) ? $inv->inv_number : ('INV-' . intval($inv->id)));
            $issue = !empty($inv->issue_date) ? date_i18n('M j, Y', strtotime($inv->issue_date)) : '—';
            $due   = !empty($inv->due_date) ? date_i18n('M j, Y', strtotime($inv->due_date)) : '—';
            $total = isset($inv->total) ? number_format((float)$inv->total, 2) : '0.00';
            $status = !empty($inv->status) ? ucfirst($inv->status) : 'Pending';
            echo '<tr>';
            echo '<td>' . esc_html($num) . '</td>';
            echo '<td>' . esc_html($issue) . '</td>';
            echo '<td>' . esc_html($due) . '</td>';
            echo '<td style="text-align:right;">$' . esc_html($total) . '</td>';
            echo '<td>' . esc_html($status) . '</td>';
            echo '<td>';
            // View link to unified preview
            $view_url = admin_url('admin.php?page=ays-client-invoice-view&invoice_id=' . intval($inv->id));
            echo '<a class="button" style="margin-right:6px;" href="' . esc_url($view_url) . '">' . esc_html__('View', 'atyourservice') . '</a>';
            // Stripe Pay button
            if ($stripe_enabled && strtolower($inv->status) !== 'paid') {
                echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="display:inline-block;margin-right:6px;">';
                echo '<input type="hidden" name="action" value="ays_stripe_checkout" />';
                echo '<input type="hidden" name="invoice_id" value="' . intval($inv->id) . '" />';
                // same nonce convention as admin invoice tab
                echo wp_nonce_field('ays_stripe_checkout', '_wpnonce', true, false);
                echo '<button class="button button-primary">' . esc_html__('Pay with Stripe', 'atyourservice') . '</button>';
                echo '</form>';
            }
            // Bank transfer details (inline)
            if (class_exists('AYS_Company_Profile') && strtolower($inv->status) !== 'paid') {
                $bt_html = method_exists('AYS_Company_Profile', 'get_bank_transfer_html')
                    ? AYS_Company_Profile::get_bank_transfer_html($num)
                    : '';
                if ($bt_html) {
                    $details_id = 'ays-bt-' . intval($inv->id);
                    echo '<details id="' . esc_attr($details_id) . '" style="display:inline-block;">';
                    echo '<summary class="button" style="display:inline-block;">' . esc_html__('Bank Transfer', 'atyourservice') . '</summary>';
                    echo '<div style="padding:8px 0; max-width:520px;">' . $bt_html . '</div>';
                    echo '</details>';
                }
            }
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '<p style="margin-top:12px;"><a class="button" href="' . esc_url(admin_url('admin.php?page=ays-client-dashboard')) . '">' . esc_html__('Back to Client Portal', 'atyourservice') . '</a></p>';
        echo '</div>';
    }

    /**
     * Render Payments stub (optional join to payments table if present)
     */
    public static function render_client_payments() {
        if (!current_user_can('access_customer_dashboard') && !current_user_can('access_client_portal')) {
            echo '<div class="notice notice-warning"><p>' . esc_html__('You do not have access to the client portal.', 'atyourservice') . '</p></div>';
            return;
        }
        global $wpdb;
        $client = self::get_current_client();
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('My Payments', 'atyourservice') . '</h1>';
        if (!$client) {
            echo '<p>' . esc_html__('We could not find a client account associated with your login.', 'atyourservice') . '</p></div>';
            return;
        }

        // If a payments table exists, show the most recent payments by joining invoices
        $payments_table = $wpdb->prefix . 'ays_payments';
        $has_payments = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $payments_table)) === $payments_table;
        if ($has_payments) {
            $rows = $wpdb->get_results($wpdb->prepare(
                "SELECT p.id, p.amount, p.method, p.status, p.created_at,
                        i.invoice_number, i.inv_number
                 FROM {$payments_table} p
                 JOIN {$wpdb->prefix}ays_invoices i ON i.id = p.invoice_id
                 WHERE i.client_id = %d
                 ORDER BY p.created_at DESC
                 LIMIT 25",
                $client->id
            ));
        } else {
            $rows = [];
        }

        if (empty($rows)) {
            echo '<p>' . esc_html__('No payments found.', 'atyourservice') . '</p>';
            echo '<p><a class="button" href="' . esc_url(admin_url('admin.php?page=ays-client-dashboard')) . '">' . esc_html__('Back to Client Portal', 'atyourservice') . '</a></p>';
            echo '</div>';
            return;
        }

        echo '<table class="widefat striped"><thead><tr>';
        echo '<th>' . esc_html__('Invoice #', 'atyourservice') . '</th>';
        echo '<th style="text-align:right;">' . esc_html__('Amount', 'atyourservice') . '</th>';
        echo '<th>' . esc_html__('Method', 'atyourservice') . '</th>';
        echo '<th>' . esc_html__('Status', 'atyourservice') . '</th>';
        echo '<th>' . esc_html__('Date', 'atyourservice') . '</th>';
        echo '</tr></thead><tbody>';
        foreach ($rows as $row) {
            $num = !empty($row->invoice_number) ? $row->invoice_number : (!empty($row->inv_number) ? $row->inv_number : '—');
            $amount = number_format((float)$row->amount, 2);
            $method = $row->method ? ucfirst($row->method) : '—';
            $status = $row->status ? ucfirst($row->status) : '—';
            $date = !empty($row->created_at) ? date_i18n('M j, Y', strtotime($row->created_at)) : '—';
            echo '<tr>';
            echo '<td>' . esc_html($num) . '</td>';
            echo '<td style="text-align:right;">$' . esc_html($amount) . '</td>';
            echo '<td>' . esc_html($method) . '</td>';
            echo '<td>' . esc_html($status) . '</td>';
            echo '<td>' . esc_html($date) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '<p style="margin-top:12px;"><a class="button" href="' . esc_url(admin_url('admin.php?page=ays-client-dashboard')) . '">' . esc_html__('Back to Client Portal', 'atyourservice') . '</a></p>';
        echo '</div>';
    }
}

// Initialize
AYS_Client_Dashboard::init();
