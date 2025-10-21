<?php
// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Admin Dashboard cleanup and AYS widget
 *
 * - Removes default WP dashboard widgets for a cleaner client experience
 * - Adds a branded "At Your Service – Client Hub" widget
 * - For customer/client roles, restricts dashboard to only our widget
 */

// Step 1: Remove default WordPress widgets (mostly for non-admins)
add_action('wp_dashboard_setup', function () {
    // Remove WordPress defaults
    remove_meta_box('dashboard_primary', 'dashboard', 'side');       // WordPress News
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');   // Quick Draft
    remove_meta_box('dashboard_activity', 'dashboard', 'normal');    // Recent Activity
    remove_meta_box('dashboard_right_now', 'dashboard', 'normal');   // At a Glance
    remove_meta_box('dashboard_site_health', 'dashboard', 'normal'); // Site Health

    // Optional: only do this for non-admins
    if (!current_user_can('manage_options')) {
        remove_action('welcome_panel', 'wp_welcome_panel');
    }
}, 99);

// Step 2: Add the AYS custom dashboard widget
add_action('wp_dashboard_setup', function () {
    wp_add_dashboard_widget(
        'ays_dashboard_widget',
        __('At Your Service – Client Portal', 'atyourservice'),
        'ays_render_dashboard_widget'
    );
});

/**
 * Render the AYS dashboard widget content
 */
function ays_render_dashboard_widget() {
    global $wpdb;
    $user = wp_get_current_user();
    $is_admin_user = current_user_can('manage_options');

    // Prefer client name from AYS clients table matched by email
    $client_name = $user->display_name ?: $user->user_login;
    $client = $wpdb->get_row($wpdb->prepare(
        "SELECT name FROM {$wpdb->prefix}ays_clients WHERE email = %s OR client_email = %s LIMIT 1",
        $user->user_email, $user->user_email
    ));
    if ($client && !empty($client->name)) {
        $client_name = $client->name;
    }

    // For admins, link to the plugin dashboard tabs; for client roles, link to the client area in wp-admin; otherwise, front-end URL as fallback
    $roles = (array) $user->roles;
    $is_clientish = in_array('customer', $roles, true) || in_array('client', $roles, true);
    $base_admin = admin_url('admin.php?page=ays-dashboard');
    $base_client_admin = admin_url('admin.php?page=ays-client-dashboard');
    $base_front = apply_filters('ays_client_portal_url', home_url('/client-dashboard/'));
    $base_url   = $is_admin_user ? $base_admin : ($is_clientish ? $base_client_admin : $base_front);

    // Derive tabbed URLs (front-end handlers can ignore if not used)
    $invoices_url = add_query_arg('tab', 'invoices', $base_url);
    $payments_url = add_query_arg('tab', 'payments', $base_url);
    $profile_url  = add_query_arg('tab', 'profile',  $base_url);
    ?>
    <div style="padding: .5rem 0;">
    <h3 style="margin: 0 0 .5rem;">Kia ora, <?php echo esc_html($client_name); ?> 👋</h3>
        <p><?php echo esc_html__('Welcome to your client dashboard. From here you can:', 'atyourservice'); ?></p>
        <ul style="margin-left: 1.2rem; list-style-type: disc;">
            <li><a href="<?php echo esc_url($invoices_url); ?>"><?php echo esc_html__('View your invoices', 'atyourservice'); ?></a></li>
            <li><a href="<?php echo esc_url($payments_url); ?>"><?php echo esc_html__('Check payment history', 'atyourservice'); ?></a></li>
            <li><a href="<?php echo esc_url($profile_url); ?>"><?php echo esc_html__('Update your details', 'atyourservice'); ?></a></li>
        </ul>
        <p><?php echo esc_html__('If you need help, contact', 'atyourservice'); ?> <a href="mailto:support@supercleanchchnz.com">support@supercleanchchnz.com</a></p>
        <?php if (!$is_admin_user): ?>
            <p style="margin-top:.75rem;color:#666;font-size:12px;"><?php echo esc_html__('Tip: You can set the client portal URL via the ays_client_portal_url filter.', 'atyourservice'); ?></p>
        <?php endif; ?>
    </div>
    <?php
}

// Step 3: For customer/client roles, restrict dashboard to only our widget
add_action('wp_dashboard_setup', function () {
    if (current_user_can('manage_options')) {
        return; // Admins keep their full dashboard
    }
    $user = wp_get_current_user();
    $roles = (array) $user->roles;
    if (!in_array('customer', $roles, true) && !in_array('client', $roles, true)) {
        return;
    }

    // Clear all core widgets and add only ours back
    global $wp_meta_boxes;
    if (isset($wp_meta_boxes['dashboard'])) {
        $wp_meta_boxes['dashboard']['normal']['core'] = [];
        $wp_meta_boxes['dashboard']['side']['core']   = [];
    }
    wp_add_dashboard_widget(
        'ays_dashboard_widget',
        __('At Your Service – Client Portal', 'atyourservice'),
        'ays_render_dashboard_widget'
    );
}, 100);
