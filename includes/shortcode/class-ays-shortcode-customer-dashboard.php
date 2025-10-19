<?php
// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Customer Dashboard Shortcode
 *
 * Renders a private, front-end dashboard for logged-in customers to view their
 * invoices and payments. No public invoice exposure; access is via WordPress login.
 *
 * Shortcode: [ays_customer_dashboard]
 */
class AYS_Shortcode_Customer_Dashboard {
    public function __construct() {
        add_shortcode('ays_customer_dashboard', [$this, 'render']);
    }

    /**
     * Render the dashboard for the current logged-in user.
     */
    public function render() {
        if (!is_user_logged_in()) {
            // Send to login and bounce back to the requested page after
            wp_redirect(wp_login_url(add_query_arg([])));
            exit;
        }

        $user = wp_get_current_user();

        ob_start();
        ?>
        <div class="ays-customer-dashboard" style="max-width: 1100px; margin: 20px auto; padding: 16px;">
            <div style="margin-bottom:16px;">
                <h2 style="margin:0 0 8px;">Welcome, <?php echo esc_html($user->display_name ?: $user->user_email); ?></h2>
                <p style="margin:0; color:#555;">View your invoices and payment history below.</p>
            </div>

            <div style="display:grid; grid-template-columns: 2fr 1fr; gap: 16px;">
                <div>
                    <?php $this->render_invoices_table_for_user($user); ?>
                </div>
                <div>
                    <?php $this->render_profile_card($user); ?>
                    <?php $this->render_bank_transfer_card(); ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render invoices for a user by matching email to client records.
     * This avoids leaking data across accounts.
     */
    private function render_invoices_table_for_user(WP_User $user) {
        global $wpdb;
        $invoices_table = $wpdb->prefix . 'ays_invoices';
        $clients_table  = $wpdb->prefix . 'ays_clients';

        // Match the current user's email to client email; adjust column names as available.
        $email = $user->user_email;
        $invoices = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT i.*
                 FROM {$invoices_table} i
                 LEFT JOIN {$clients_table} c ON i.client_id = c.id
                 WHERE (c.client_email = %s OR c.email = %s)
                 ORDER BY i.issue_date DESC, i.created_at DESC",
                $email, $email
            )
        );

        echo '<details class="ays-details" open><summary>📄 Your Invoices</summary><div style="padding:12px;">';
        if (empty($invoices)) {
            echo '<p>No invoices found for your account.</p>';
            echo '</div></details>';
            return;
        }

        echo '<table class="widefat striped"><thead><tr>';
        echo '<th>#</th><th>Date</th><th style="text-align:right;">Total</th><th>Status</th><th>Actions</th>';
        echo '</tr></thead><tbody>';
        foreach ($invoices as $inv) {
            $inv_num   = !empty($inv->invoice_number) ? $inv->invoice_number : ('INV-' . intval($inv->id));
            $issue     = !empty($inv->issue_date) ? date_i18n('M d, Y', strtotime($inv->issue_date)) : '—';
            $total_val = isset($inv->total) ? number_format((float)$inv->total, 2) : '0.00';
            $status    = !empty($inv->status) ? ucfirst($inv->status) : 'Pending';
            echo '<tr>';
            echo '<td>' . esc_html($inv_num) . '</td>';
            echo '<td>' . esc_html($issue) . '</td>';
            echo '<td style="text-align:right;">$' . esc_html($total_val) . '</td>';
            echo '<td>' . esc_html($status) . '</td>';
            echo '<td>';
            echo '<button class="button" disabled title="Coming soon">View</button> ';
            echo '<button class="button" disabled title="Coming soon">Download</button> ';
            if (strtolower($status) !== 'paid') {
                echo '<button class="button button-primary" disabled title="Stripe payment coming soon">Pay now</button>';
            }
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody></table></div></details>';
    }

    private function render_profile_card(WP_User $user) {
        echo '<details class="ays-details" open><summary>👤 Your Profile</summary><div style="padding:12px;">';
        echo '<p><strong>Name:</strong> ' . esc_html($user->display_name ?: '—') . '</p>';
        echo '<p><strong>Email:</strong> ' . esc_html($user->user_email ?: '—') . '</p>';
        echo '<p><a class="button" href="' . esc_url(wp_logout_url(home_url('/'))) . '">Log out</a></p>';
        echo '</div></details>';
    }

    private function render_bank_transfer_card() {
        if (!class_exists('AYS_Company_Profile')) {
            return;
        }
        $html = AYS_Company_Profile::get_bank_transfer_html();
        if (!$html) {
            return;
        }
        echo '<details class="ays-details" open><summary>💳 Bank Transfer Details</summary><div style="padding:12px;">';
        echo $html; // Already escaped within renderer
        echo '</div></details>';
    }
}

// Initialize immediately so the shortcode is available on the front-end
if (class_exists('AYS_Shortcode_Customer_Dashboard')) {
    new AYS_Shortcode_Customer_Dashboard();
}
