<?php
/**
 * Lead Notices Tab for Lead Dashboard
 * Displays notification status, logs, and resend/test actions.
 * Drop this into includes/admin/ays-lead-notices.php
 */

defined('ABSPATH') || exit;

function ays_render_lead_notices_tab() {
    global $wpdb;
    $log_table = $wpdb->prefix . 'ays_notification_log';
    $logs = $wpdb->get_results("SELECT * FROM $log_table ORDER BY ts DESC LIMIT 10");
    $leads = get_posts([
        'post_type'      => 'ays_lead',
        'posts_per_page' => 10,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
    ?>
    <div class="ays-card">
        <h2><?php echo esc_html__('Lead Notices', 'your-td'); ?></h2>
        <p><?php echo esc_html__('Recent notification status and actions for your leads.', 'your-td'); ?></p>

        <h3><?php echo esc_html__('Recent Notification Logs', 'your-td'); ?></h3>
        <table class="widefat fixed">
            <thead><tr>
                <th><?php esc_html_e('Time', 'your-td'); ?></th>
                <th><?php esc_html_e('Code', 'your-td'); ?></th>
                <th><?php esc_html_e('Details', 'your-td'); ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?php echo esc_html($log->ts); ?></td>
                    <td><?php echo esc_html($log->code); ?></td>
                    <td><pre style="white-space:pre-wrap;max-width:400px;"><?php echo esc_html($log->details); ?></pre></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <h3><?php echo esc_html__('Lead Notification Status', 'your-td'); ?></h3>
        <table class="widefat fixed">
            <thead><tr>
                <th><?php esc_html_e('Lead', 'your-td'); ?></th>
                <th><?php esc_html_e('Status', 'your-td'); ?></th>
                <th><?php esc_html_e('Last Notified', 'your-td'); ?></th>
                <th><?php esc_html_e('Actions', 'your-td'); ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ($leads as $lead):
                $notified = get_post_meta($lead->ID, '_ays_notified', true);
                $status = $notified ? __('Sent', 'your-td') : __('Pending/Failed', 'your-td');
                $url = wp_nonce_url(
                    admin_url('admin-post.php?action=ays_resend_lead&lead_id=' . $lead->ID),
                    'ays_resend_lead'
                );
            ?>
                <tr>
                    <td><a href="<?php echo esc_url(get_edit_post_link($lead->ID)); ?>"><?php echo esc_html($lead->post_title ?: 'Lead #' . $lead->ID); ?></a></td>
                    <td><?php echo esc_html($status); ?></td>
                    <td><?php echo esc_html($notified ?: '-'); ?></td>
                    <td>
                        <?php if (!$notified): ?>
                            <a class="button" href="<?php echo esc_url($url); ?>"><?php esc_html_e('Resend', 'your-td'); ?></a>
                        <?php else: ?>
                            <span style="color: #46b450;">&#10003;</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:2em;">
            <?php wp_nonce_field('ays_send_test_notice', 'ays_send_test_notice_nonce'); ?>
            <input type="hidden" name="action" value="ays_send_test_notice">
            <button type="submit" class="button button-secondary"><?php esc_html_e('Send Test Notification', 'your-td'); ?></button>
        </form>
    </div>
    <?php
}
