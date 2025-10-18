<?php
// includes/admin/ays-lead-dashboard.php
// Add this to your dashboard renderer to include the Lead Notices tab

defined('ABSPATH') || exit;

// Remove any accidental output of handler code above the dashboard
ob_start();
?>
<?php
$buffer = ob_get_clean();
// Only output if buffer is empty (no accidental code)
if (trim($buffer) !== '' && strpos($buffer, 'Lead Form Dashboard') === false) {
    // If there is unexpected output, do not show it
    // Optionally log or handle here
} 
?>
<div class="wrap ays-wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html__('Lead Form Dashboard', 'your-td'); ?></h1>
    <nav class="ays-tabs" data-ays-tabs>
        <a href="#overview" class="ays-tab" data-ays-tab>Overview</a>
        <a href="#content"  class="ays-tab" data-ays-tab>Content</a>
        <a href="#styling"  class="ays-tab" data-ays-tab>Styling</a>
        <a href="#email"    class="ays-tab" data-ays-tab>Email</a>
        <a href="#notices"  class="ays-tab" data-ays-tab>Lead Notices</a>
        <a href="#export"   class="ays-tab" data-ays-tab>Export</a>
    </nav>
    <!-- ...other tab panels... -->
    <section id="notices" class="ays-tabpanel" data-ays-panel>
        <?php
        // Modular: include the Lead Notices tab renderer
        $notices_tab = AYS_PLUGIN_PATH . 'includes/admin/ays-lead-notices.php';
        if (file_exists($notices_tab)) {
            require $notices_tab;
            if (function_exists('ays_render_lead_notices_tab')) {
                ays_render_lead_notices_tab();
            }
        } else {
            echo '<div class="notice notice-error"><p>Lead Notices tab file missing.</p></div>';
        }
        ?>
    </section>
    <section id="export" class="ays-tabpanel" data-ays-panel>
        <?php
        // Modular: render the Export tab using the existing export class
        if (file_exists(AYS_PLUGIN_PATH . 'admin/class-ays-leads-export.php')) {
            require_once AYS_PLUGIN_PATH . 'admin/class-ays-leads-export.php';
            if (class_exists('Ays_Leads_Export_Page')) {
                $export = new Ays_Leads_Export_Page();
                if (method_exists($export, 'render_page')) {
                    $export->render_page();
                }
            }
        } else {
            echo '<div class="notice notice-error"><p>Export tab file missing.</p></div>';
        }
        ?>
    </section>
</div>

