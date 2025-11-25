<?php
/**
 * AYS Invoicing Admin Dashboard
 *
 * Main admin interface for invoicing module.
 * Enhanced with: live invoice preview, embedded tables, color pickers, WYSIWYG editors.
 * Pattern: Replicated from lead dashboard (proven, sophisticated pattern).
 * 
 * Architecture:
 * - Multiple <details>/<summary> collapsible sections
 * - Tables embedded for logs and audit trails
 * - Settings API for persistence
 * - Live preview panel updates in real-time
 * - Admin-post handlers for complex actions
 * - Modular structure with separate tab renderers
 *
 * @since 1.0
 */

defined('ABSPATH') || exit;

class AYS_Invoice_Admin_UI {

    const OPTION_KEY = 'ays_invoice_settings';

    public function __construct() {
        // NOTE: Menu registration moved to AYS_Admin_Menu class for centralized management
        // This class now only handles the page content rendering and settings
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
        add_action('current_screen', [$this, 'add_help_tabs']);
        add_action('wp_ajax_ays_create_payment_intent', ['AYS_Payments_Tab', 'create_payment_intent']);
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('ays_invoicing_settings', self::OPTION_KEY, [
            'sanitize_callback' => [$this, 'sanitize_settings'],
        ]);

        add_settings_section(
            'ays_invoicing_main',
            __('Invoicing Configuration', 'ays'),
            '__return_false',
            'ays_invoicing_settings'
        );

        add_settings_field(
            'invoice_prefix',
            __('Invoice Prefix', 'ays'),
            [$this, 'field_invoice_prefix'],
            'ays_invoicing_settings',
            'ays_invoicing_main'
        );

        add_settings_field(
            'gst_rate',
            __('GST Rate (%)', 'ays'),
            [$this, 'field_gst_rate'],
            'ays_invoicing_settings',
            'ays_invoicing_main'
        );

        add_settings_field(
            'due_in_days',
            __('Due In (Days)', 'ays'),
            [$this, 'field_due_in_days'],
            'ays_invoicing_settings',
            'ays_invoicing_main'
        );

        add_settings_field(
            'currency',
            __('Currency', 'ays'),
            [$this, 'field_currency'],
            'ays_invoicing_settings',
            'ays_invoicing_main'
        );
    }

    /**
     * Enqueue admin assets
     * 
     * Includes:
     * - 450+ lines of professional CSS with gradients, shadows, responsive layout
     * - Live preview sync JavaScript (like lead dashboard)
     * - Color picker integration
     * - Inline styles/scripts for zero external HTTP requests
     */
    public function admin_assets($hook) {
        // We only load assets on our specific dashboard page to avoid conflicts.
        // Use strpos for a flexible check, similar to the lead dashboard pattern.
        if (strpos($hook, 'ays-dashboard') === false) {
            return;
        }

        // Enqueue color picker, WYSIWYG editor, and other WP core assets
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_editor(); // For future WYSIWYG fields

        // Inline CSS with professional blue/purple theme (enhanced from lead dashboard pattern)
        $css = '
        /* Core Wrapper */
        .ays-invoicing-wrap {
            background: #fff;
            margin-top: 20px;
        }

        .ays-invoicing-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 2px solid #4c51bf;
        }

        .ays-invoicing-header h1 {
            margin: 0;
            color: #1f2937;
            font-size: 28px;
            font-weight: 600;
        }

        .ays-invoicing-tabs {
            display: flex;
            gap: 2px;
            margin-bottom: 24px;
            flex-wrap: wrap;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: -2px;
        }

        .ays-invoicing-tab {
            padding: 10px 16px;
            background: transparent;
            border: none;
            color: #6b7280;
            font-weight: 500;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            font-size: 14px;
            text-decoration: none;
        }

        .ays-invoicing-tab:hover {
            color: #4c51bf;
            background: #f3f4f6;
            border-bottom-color: #dbeafe;
        }

        .ays-invoicing-tab.active {
            color: #4c51bf;
            border-bottom-color: #4c51bf;
            background: #f0f4ff;
        }

        .ays-invoicing-content {
            display: none;
        }

        .ays-invoicing-content.active {
            display: block;
        }

        /* General layout styles from Lead Dashboard for consistency */
        .ays-panel {
            background: #f9f9f9;
            border: 1px solid #dcdcde;
            border-radius: 6px;
            margin: 0 0 18px;
            padding: 16px 18px;
        }

        .ays-details {
            background: #fff;
            border: 1px solid #c3c4c7; /* Lead dashboard border color */
            border-radius: 6px; /* Lead dashboard border radius */
            margin: 16px 0; /* Add top and bottom margin for breathing room */
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(16, 24, 40, 0.06); /* Subtle depth */
        }

        .ays-details summary {
            padding: 16px 20px;
            font-weight: 600;
            cursor: pointer;
            background: linear-gradient(135deg, #6366f1 0%, #4c51bf 100%); /* Keep the purple/blue gradient */
            color: #fff;
            user-select: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
            border-bottom: 1px solid transparent; /* Add border for open state */
        }

        .ays-details[open] {
            box-shadow: 0 0 0 2px #4c51bf inset, 0 1px 2px rgba(16,24,40,0.06); /* Keep subtle depth */
            border-color: #4c51bf;
        }

        .ays-details[open] summary {
             border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }

        .ays-details summary:hover {
            background: linear-gradient(135deg, #7c7fff 0%, #5c65cf 100%);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
        }

        .ays-details summary::marker {
            color: #fff;
        }

        .ays-details > div {
            padding: 24px 30px; /* Increased horizontal padding */
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px; /* Increased gap */
            border-top: 1px solid #dcdcde; /* A slightly softer grey border */
        }

        /* Loosen form rows inside details on the Invoices tab */
        .ays-details .left-column .form-table th,
        .ays-details .left-column .form-table td {
            padding-top: 10px;
            padding-bottom: 10px;
        }

        .ays-details .left-column {
            min-width: 0;
        }

        .ays-details .right-column {
            background: #f8f9fa;
            border-left: 3px solid #4c51bf;
            padding: 20px; /* Increased padding */
            border-radius: 4px;
        }

        .ays-details .right-column h4 {
            margin-top: 0;
            color: #4c51bf;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-bottom: 8px; /* Added padding */
            border-bottom: 1px solid #e5e7eb; /* Added border */
            margin-bottom: 16px; /* Added margin */
        }

        .ays-details .right-column ul,
        .ays-details .right-column ol {
            margin: 8px 0;
            padding-left: 20px;
            font-size: 13px;
            line-height: 1.6;
            color: #4b5563;
        }

        .ays-details .right-column li {
            margin-bottom: 6px;
        }

        .ays-badge {
            display: inline-block;
            background: #10b981;
            color: #fff;
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 3px;
            margin-left: 8px;
            vertical-align: middle;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .ays-badge.new {
            background: #f59e0b;
        }

        /* Live Preview Badge (from lead dashboard) */
        .ays-live-badge {
            display: inline-block;
            background: #2271b1;
            color: #fff;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 3px;
            margin-left: 6px;
            vertical-align: middle;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .ays-preview-heading {
            margin: 18px 0 6px;
            font-size: 16px;
            font-weight: 600;
        }

        .ays-flex-preview {
            display: flex;
            gap: 24px;
            align-items: flex-start;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        .ays-flex-preview .ays-prev-left {
            flex: 1;
            min-width: 260px;
            border: 1px solid #e2e4e7;
            background: var(--ays-prev-bg, #ffffff);
            padding: 18px;
            border-radius: 6px;
        }

        .ays-flex-preview .ays-prev-right {
            flex: 1;
            min-width: 260px;
        }

        .ays-prev-left h2 {
            margin-top: 0;
            margin-bottom: 8px;
        }

        .ays-invoice-preview {
            background: white;
            border: 1px solid #dcdcde;
            border-radius: 4px;
            padding: 20px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
        }

        .ays-invoice-preview-header {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
        }

        .ays-invoice-preview-from,
        .ays-invoice-preview-to {
            font-size: 13px;
        }

        .ays-invoice-preview-from h3,
        .ays-invoice-preview-to h3 {
            margin: 0 0 8px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #4c51bf;
            letter-spacing: 0.5px;
        }

        .ays-invoice-preview-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .ays-invoice-preview-items thead {
            background: #f3f4f6;
            border-bottom: 2px solid #d1d5db;
        }

        .ays-invoice-preview-items th {
            padding: 10px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            color: #4c51bf;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .ays-invoice-preview-items td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .ays-invoice-preview-total {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 20px;
            justify-content: flex-end;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
        }

        .ays-invoice-preview-total-row {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 20px;
            align-items: center;
            margin-bottom: 8px;
        }

        .ays-invoice-preview-total-row.final {
            font-weight: 600;
            font-size: 16px;
            color: #4c51bf;
            padding: 8px 0;
            border-top: 1px solid #d1d5db;
        }

        .ays-form-row {
            margin-bottom: 20px;
        }

        .ays-form-row label {
            display: block;
            margin-bottom: 8px; /* Increased margin */
            font-weight: 500;
            color: #374151;
            font-size: 14px;
            padding: 0 2px; /* Added slight horizontal padding */
        }

        .ays-form-row input[type="text"],
        .ays-form-row input[type="email"],
        .ays-form-row input[type="number"],
        .ays-form-row select,
        .ays-form-row textarea {
            width: 100%;
            max-width: 400px;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .ays-form-row input:focus,
        .ays-form-row select:focus,
        .ays-form-row textarea:focus {
            outline: none;
            border-color: #4c51bf;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .ays-form-row .description {
            margin-top: 4px;
            font-size: 12px;
            color: #6b7280;
            font-style: italic;
        }

        @media (max-width: 782px) {
            .ays-details > div {
                grid-template-columns: 1fr;
            }

            .ays-invoicing-tabs {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .ays-form-row input,
            .ays-form-row select {
                max-width: 100%;
            }
        }
        ';

        wp_add_inline_style('wp-admin', $css);

        // Localize script data for AJAX tab switching
        // Use wp_rest nonce for REST API calls (WordPress standard)
        // Ensure jQuery is enqueued so our inline JS executes
        wp_enqueue_script('jquery');
        $use_rest = (bool) $this->get_option('use_rest', false);
        $debug    = (bool) $this->get_option('debug', false);
        wp_localize_script('jquery', 'aysInvoicing', [
            'restBase' => esc_url_raw( rest_url( 'ays/v1/invoicing/tab/' ) ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            // Feature flags sourced from settings
            'useRest' => $use_rest,
            'debug'   => $debug,
        ]);

        // Inline JavaScript for AJAX tab switching and live preview sync
        $js = '
        (function($) {
            var tabLoading = false;
            var currentTab = "invoices";

            // Live preview sync - updates invoice preview as user edits settings
            function syncInvoicePreview() {
                var prefix = $("#invoice_prefix").val() || "INV-";
                var gst_rate = parseFloat($("#gst_rate").val()) || 15;
                var dueDays = parseInt($("#due_in_days").val()) || 7;
                var currency = $("#currency").val() || "NZD";
                var currencySymbol = currency === "NZD" ? "$" : currency === "USD" ? "$" : currency === "AUD" ? "A$" : currency === "GBP" ? "£" : "€";
                
                // Update preview elements
                $("[data-preview=prefix]").text(prefix);
                $("[data-preview=gst_rate]").text(gst_rate.toFixed(2));
                $("[data-preview=due_days]").text(dueDays);
                $("[data-preview=currency]").text(currency + " " + currencySymbol);
            }
            
            // AJAX tab switching - load content without page reload
            function loadTabContent(tabName) {
                if (tabLoading) return;
                if (aysInvoicing.debug) {
                    console.log("Loading tab:", tabName);
                    console.log("ajaxUrl:", aysInvoicing.ajaxUrl, "restBase:", aysInvoicing.restBase, "useRest:", aysInvoicing.useRest);
                }
                
                tabLoading = true;
                var $contentArea = $(".ays-invoicing-content");
                
                // Show loading state
                $contentArea.css("opacity", "0.5");
                
                if (aysInvoicing.useRest) {
                    // REST transport (JSON)
                    $.ajax({
                        url: aysInvoicing.restBase + tabName,
                        type: "GET",
                        dataType: "json",
                        headers: { "X-WP-Nonce": aysInvoicing.nonce },
                        success: function(resp) {
                            if (aysInvoicing.debug) console.log("REST success:", resp);
                            if (resp && resp.success) {
                                $contentArea.html(resp.content || "");
                                currentTab = tabName;
                                var newUrl = window.location.pathname + "?page=ays-dashboard&tab=" + tabName;
                                window.history.replaceState({tab: tabName}, "", newUrl);
                                if ($.fn.wpColorPicker) {
                                    $(".ays-color-field").wpColorPicker({
                                        change: function() { syncInvoicePreview(); },
                                        clear: function() { syncInvoicePreview(); }
                                    });
                                }
                            } else {
                                console.error("REST response not success:", resp);
                                $contentArea.html("<p style=\"color:red;\">Error: REST response invalid.</p>");
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("REST load error:", {status: xhr.status, error: error, response: xhr.responseText});
                            var snippet = (xhr.responseText || "").toString().slice(0, 400).replace(/[\n\r]+/g, " ");
                            $contentArea.html(
                                "<div style=\"background:#fff3cd;border:1px solid #ffeeba;padding:10px;border-radius:4px;\">"
                                + "<strong>REST error:</strong> " + (xhr.status || "0") + " " + (xhr.statusText || "")
                                + "<div style=\"margin-top:6px;color:#6b7280;font-size:12px;\">" + snippet + "</div>"
                                + "</div>"
                            );
                        },
                        complete: function() {
                            tabLoading = false;
                            $contentArea.css("opacity", "1");
                        }
                    });
                } else {
                    // admin-ajax transport (HTML)
                    var ajaxUrl = aysInvoicing.ajaxUrl;
                    var data = { action: "ays_load_tab", tab: tabName, nonce: aysInvoicing.nonce, format: "html" };
                    if (aysInvoicing.debug) console.log("AJAX data:", data);
                    $.ajax({
                        url: ajaxUrl,
                        type: "POST",
                        data: data,
                        dataType: "html",
                        success: function(response) {
                            if (aysInvoicing.debug) console.log("AJAX success (html)");
                            $contentArea.html(response);
                            currentTab = tabName;
                            var newUrl = window.location.pathname + "?page=ays-dashboard&tab=" + tabName;
                            window.history.replaceState({tab: tabName}, "", newUrl);
                            if ($.fn.wpColorPicker) {
                                $(".ays-color-field").wpColorPicker({
                                    change: function() { syncInvoicePreview(); },
                                    clear: function() { syncInvoicePreview(); }
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Tab load error:", {status: xhr.status, statusText: xhr.statusText, error: error, response: xhr.responseText});
                            var snippet = (xhr.responseText || "").toString().slice(0, 400).replace(/[\n\r]+/g, " ");
                            $contentArea.html(
                                "<div style=\"background:#fff3cd;border:1px solid #ffeeba;padding:10px;border-radius:4px;\">"
                                + "<strong>AJAX error:</strong> " + (xhr.status || "0") + " " + (xhr.statusText || "")
                                + "<div style=\"margin-top:6px;color:#6b7280;font-size:12px;\">" + snippet + "</div>"
                                + "</div>"
                            );
                        },
                        complete: function() {
                            tabLoading = false;
                            $contentArea.css("opacity", "1");
                        }
                    });
                }
            }
            
            // Tab click handler
            $(document).on("click", ".ays-invoicing-tab", function(e) {
                e.preventDefault();
                
                var $tab = $(this);
                var tabName = $tab.data("tab");
                
                if (aysInvoicing.debug) {
                    console.log("Tab clicked:", tabName);
                    console.log("aysInvoicing:", aysInvoicing);
                }
                
                if (tabName === currentTab) {
                    console.log("Same tab, skipping");
                    return;
                }
                
                // Update active states
                $(".ays-invoicing-tab").removeClass("active");
                $tab.addClass("active");
                
                // Load content via AJAX
                loadTabContent(tabName);
            });
            
            // Sync preview when any setting changes
            $(document).on("input change", "#invoice_prefix, #gst_rate, #due_in_days, #currency", function() {
                syncInvoicePreview();
            });
            
            // Initialize on page load
            $(document).ready(function() {
                if (aysInvoicing.debug) {
                    console.log("AYS Invoicing JS loaded");
                    console.log("aysInvoicing object:", aysInvoicing);
                }
                
                // Set initial active tab from URL parameter
                var urlParams = new URLSearchParams(window.location.search);
                var initialTab = urlParams.get("tab") || "invoices";
                currentTab = initialTab;
                
                if (aysInvoicing.debug) console.log("Initial tab:", initialTab);
                
                // Initialize color pickers if present
                if ($.fn.wpColorPicker) {
                    $(".ays-color-field").wpColorPicker({
                        change: function() { syncInvoicePreview(); },
                        clear: function() { syncInvoicePreview(); }
                    });
                }
                
                // Initial sync
                syncInvoicePreview();
            });
        })(jQuery);
        ';

        // Attach our inline script after jQuery so $() is available
        wp_add_inline_script('jquery', $js);
    }

    /**
     * AJAX handler for loading tab content
     */
    public static function ajax_load_tab() {
        // Verify nonce (will exit with -1 on failure)
        check_ajax_referer('wp_rest', 'nonce');

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $tab_name = sanitize_text_field($_POST['tab'] ?? '');

        // Validate tab name
        $allowed_tabs = ['invoices', 'clients', 'items', 'service-types', 'payments', 'reports', 'settings'];
        if (!in_array($tab_name, $allowed_tabs, true)) {
            wp_send_json_error('Invalid tab');
        }

        // Render with diagnostics
        $content = '';
        try {
            // Start output buffering
            ob_start();

            // Render the appropriate tab
            switch ($tab_name) {
                case 'invoices':
                    (new self())->render_invoices_tab();
                    break;
                case 'clients':
                    (new self())->render_clients_tab();
                    break;
                case 'items':
                    (new self())->render_items_tab();
                    break;
                case 'service-types':
                    (new self())->render_service_types_tab();
                    break;
                case 'payments':
                    (new self())->render_payments_tab();
                    break;
                case 'reports':
                    (new self())->render_reports_tab();
                    break;
                case 'settings':
                    (new self())->render_settings_tab();
                    break;
            }

            $content = ob_get_clean();
        } catch (\Throwable $e) {
            if (function_exists('error_log')) {
                error_log('[AYS] ajax_load_tab error for tab ' . $tab_name . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            }
            $error_html = '<div class="notice notice-error" style="padding:10px;"><strong>Render error:</strong> '
                . esc_html($e->getMessage()) . ' <em>(' . esc_html($e->getFile()) . ':' . intval($e->getLine()) . ')</em></div>';
            if (isset($_POST['format']) && $_POST['format'] === 'html') {
                echo $error_html;
                wp_die();
            }
            wp_send_json_error(['error' => 'Render error', 'message' => $e->getMessage()]);
        }

        // If front-end requested HTML directly, return raw markup
        if (isset($_POST['format']) && $_POST['format'] === 'html') {
            echo $content;
            wp_die();
        }

        // Default: JSON response shape
        wp_send_json_success(['content' => $content, 'tab' => $tab_name]);
    }

    /**
     * Add help tabs
     */
    public function add_help_tabs($screen) {
        if (!isset($screen->id) || strpos($screen->id, 'ays_invoicing') === false) {
            return;
        }

        $screen->add_help_tab([
            'id'      => 'ays_invoicing_intro',
            'title'   => __('Getting Started', 'ays'),
            'content' => '<p>' . esc_html__('The Invoicing module helps you create and manage professional invoices for your service business.', 'ays') . '</p>',
        ]);
    }

    /**
     * Render main dashboard page
     * 
     * Called by AYS_Admin_Menu when user navigates to Dashboard
     * 
     * @return void
     */
    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'ays'));
        }

        $current_tab = sanitize_text_field($_GET['tab'] ?? 'invoices');
        $nonce = wp_create_nonce('ays_invoicing_nonce');

        ?>
        <div class="wrap ays-invoicing-wrap" data-ays-nonce="<?php echo esc_attr($nonce); ?>">
            <div class="ays-invoicing-header">
                <h1>💰 <?php esc_html_e('Invoicing Dashboard', 'ays'); ?></h1>
            </div>

            <nav class="ays-invoicing-tabs">
                <a href="?page=ays-dashboard&tab=invoices" class="ays-invoicing-tab <?php echo $current_tab === 'invoices' ? 'active' : ''; ?>" data-tab="invoices">
                    <?php esc_html_e('Invoices', 'ays'); ?>
                </a>
                <a href="?page=ays-dashboard&tab=clients" class="ays-invoicing-tab <?php echo $current_tab === 'clients' ? 'active' : ''; ?>" data-tab="clients">
                    <?php esc_html_e('Clients', 'ays'); ?>
                </a>
                <a href="?page=ays-dashboard&tab=items" class="ays-invoicing-tab <?php echo $current_tab === 'items' ? 'active' : ''; ?>" data-tab="items">
                    <?php esc_html_e('Items', 'ays'); ?>
                </a>
                <a href="?page=ays-dashboard&tab=service-types" class="ays-invoicing-tab <?php echo $current_tab === 'service-types' ? 'active' : ''; ?>" data-tab="service-types">
                    <?php esc_html_e('Service Types', 'ays'); ?>
                </a>
                <a href="?page=ays-dashboard&tab=payments" class="ays-invoicing-tab <?php echo $current_tab === 'payments' ? 'active' : ''; ?>" data-tab="payments">
                    <?php esc_html_e('Payments', 'ays'); ?>
                </a>
                <a href="?page=ays-dashboard&tab=reports" class="ays-invoicing-tab <?php echo $current_tab === 'reports' ? 'active' : ''; ?>" data-tab="reports">
                    <?php esc_html_e('Reports', 'ays'); ?>
                </a>
                <a href="?page=ays-dashboard&tab=settings" class="ays-invoicing-tab <?php echo $current_tab === 'settings' ? 'active' : ''; ?>" data-tab="settings">
                    <?php esc_html_e('Settings', 'ays'); ?>
                </a>
            </nav>

            <!-- Single content area for AJAX tab switching -->
            <div id="ays-tab-content" class="ays-invoicing-content active">
                <?php 
                // Render initial tab content on page load
                switch ( $current_tab ) {
                    case 'clients':
                        $this->render_clients_tab();
                        break;
                    case 'items':
                        $this->render_items_tab();
                        break;
                    case 'service-types':
                        $this->render_service_types_tab();
                        break;
                    case 'payments':
                        $this->render_payments_tab();
                        break;
                    case 'reports':
                        $this->render_reports_tab();
                        break;
                    case 'settings':
                        $this->render_settings_tab();
                        break;
                    case 'invoices':
                    default:
                        $this->render_invoices_tab();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render Invoices tab
     */
    protected function render_invoices_tab() {
        if ( class_exists( 'AYS_Invoices_Tab' ) ) {
            AYS_Invoices_Tab::render();
        } else {
            echo '<p>' . esc_html__( 'Invoices tab class not found.', 'ays' ) . '</p>';
        }
    }

    /**
     * Render Clients tab
     */
    protected function render_clients_tab() {
        if ( class_exists( 'AYS_Clients_Tab' ) ) {
            AYS_Clients_Tab::render();
        } else {
            echo '<p>' . esc_html__( 'Clients tab class not found.', 'ays' ) . '</p>';
        }
    }

    /**
     * Render Items tab
     */
    protected function render_items_tab() {
        if ( class_exists( 'AYS_Items_Tab' ) ) {
            AYS_Items_Tab::render();
        } else {
            echo '<p style="color: #dc3545;">' . esc_html__( 'Items module not loaded.', 'atyourservice' ) . '</p>';
        }
    }

    /**
     * Render Service Types tab
     */
    protected function render_service_types_tab() {
        if ( class_exists( 'AYS_Service_Types_Tab' ) ) {
            AYS_Service_Types_Tab::render();
        } else {
            echo '<p style="color: #dc3545;">' . esc_html__( 'Service Types module not loaded.', 'atyourservice' ) . '</p>';
        }
    }

    /**
     * Render Payments tab
     */
    protected function render_payments_tab() {
        if ( class_exists( 'AYS_Payments_Tab' ) ) {
            AYS_Payments_Tab::render();
        } else {
            ?>
            <div class="notice notice-error"><p><?php esc_html_e( 'Payments module not loaded', 'atyourservice' ); ?></p></div>
            <?php
        }
    }

    /**
     * Render Reports tab
     */
    protected function render_reports_tab() {
        ?>
        <details class="ays-details" open>
            <summary>
                📊 <?php esc_html_e('Reports & Analytics', 'ays'); ?>
                <span class="ays-badge"><?php esc_html_e('coming soon', 'ays'); ?></span>
            </summary>
            <div>
                <div class="left-column">
                    <p><?php esc_html_e('Financial reports and analytics will appear here.', 'ays'); ?></p>
                </div>
                <div class="right-column">
                    <h4><?php esc_html_e('📈 Reports Available', 'ays'); ?></h4>
                    <ul>
                        <li><?php esc_html_e('Revenue by period', 'ays'); ?></li>
                        <li><?php esc_html_e('Outstanding invoices', 'ays'); ?></li>
                        <li><?php esc_html_e('Top clients', 'ays'); ?></li>
                        <li><?php esc_html_e('Service type breakdown', 'ays'); ?></li>
                    </ul>
                </div>
            </div>
        </details>
        <?php
    }

    /**
     * Render Settings tab with collapsible sections
     * Enhanced with: live preview, multiple details sections, tables
     */
    protected function render_settings_tab() {
        ?>
        <form action="options.php" method="post">
            <?php settings_fields('ays_invoicing_settings'); ?>

            <details class="ays-details" open>
                <summary>
                    ⚙️ <?php esc_html_e('Invoice Defaults', 'ays'); ?>
                    <span class="ays-live-badge">LIVE</span>
                </summary>
                <div>
                    <div class="left-column">
                        <div class="ays-form-row">
                            <label for="invoice_prefix"><?php esc_html_e('Invoice Prefix', 'ays'); ?></label>
                            <input type="text" id="invoice_prefix" name="<?php echo self::OPTION_KEY; ?>[prefix]" value="<?php echo esc_attr($this->get_option('prefix', 'INV-')); ?>" maxlength="10" />
                            <p class="description"><?php esc_html_e('e.g., SC-, INV-, or custom prefix (appears in invoice number)', 'ays'); ?></p>
                        </div>

                        <div class="ays-form-row">
                            <label for="gst_rate"><?php esc_html_e('GST/Tax Rate (%)', 'ays'); ?></label>
                            <input type="number" id="gst_rate" name="<?php echo self::OPTION_KEY; ?>[gst_rate]" value="<?php echo esc_attr($this->get_option('gst_rate', 15)); ?>" step="0.01" min="0" max="100" />
                            <p class="description"><?php esc_html_e('Default tax rate applied to taxable items (e.g., 15 for 15%)', 'ays'); ?></p>
                        </div>

                        <div class="ays-form-row">
                            <label for="due_in_days"><?php esc_html_e('Default Due In (Days)', 'ays'); ?></label>
                            <input type="number" id="due_in_days" name="<?php echo self::OPTION_KEY; ?>[due_in_days]" value="<?php echo esc_attr($this->get_option('due_in_days', 7)); ?>" min="1" />
                            <p class="description"><?php esc_html_e('Payment terms: how many days from invoice date before due', 'ays'); ?></p>
                        </div>

                        <div class="ays-form-row">
                            <label for="currency"><?php esc_html_e('Currency', 'ays'); ?></label>
                            <select id="currency" name="<?php echo self::OPTION_KEY; ?>[currency]">
                                <option value="NZD" <?php selected($this->get_option('currency', 'NZD'), 'NZD'); ?>>NZD (New Zealand Dollar)</option>
                                <option value="USD" <?php selected($this->get_option('currency'), 'USD'); ?>>USD (US Dollar)</option>
                                <option value="AUD" <?php selected($this->get_option('currency'), 'AUD'); ?>>AUD (Australian Dollar)</option>
                                <option value="GBP" <?php selected($this->get_option('currency'), 'GBP'); ?>>GBP (British Pound)</option>
                                <option value="EUR" <?php selected($this->get_option('currency'), 'EUR'); ?>>EUR (Euro)</option>
                            </select>
                            <p class="description"><?php esc_html_e('Currency symbol displayed on invoices', 'ays'); ?></p>
                        </div>
                    </div>
                    <div class="right-column">
                        <h4><?php esc_html_e('💡 Settings Explained', 'ays'); ?></h4>
                        <ul>
                            <li><?php echo wp_kses_post( __('<strong>Prefix:</strong> Part of invoice number (e.g., INV-001)', 'ays') ); ?></li>
                            <li><?php echo wp_kses_post( __('<strong>GST:</strong> Tax percentage added to taxable items', 'ays') ); ?></li>
                            <li><?php echo wp_kses_post( __('<strong>Due Days:</strong> Payment terms (e.g., 7 = due in 7 days)', 'ays') ); ?></li>
                            <li><?php echo wp_kses_post( __('<strong>Currency:</strong> Display symbol ($, £, €, etc.)', 'ays') ); ?></li>
                        </ul>

                        <hr style="margin:16px 0; border:none; border-top:1px solid #e5e7eb;" />
                        <h4><?php esc_html_e('🔧 Developer Options', 'ays'); ?></h4>
                        <p class="description" style="margin-top:0;"><?php esc_html_e('These options control how tabs load and whether debug logs appear in the browser console.', 'ays'); ?></p>
                        <label style="display:block; margin:8px 0;">
                            <input type="checkbox" name="<?php echo self::OPTION_KEY; ?>[use_rest]" value="1" <?php checked( (bool) $this->get_option('use_rest', false), true ); ?> />
                            <?php esc_html_e('Use REST transport for tabs (experimental)', 'ays'); ?>
                        </label>
                        <label style="display:block; margin:8px 0;">
                            <input type="checkbox" name="<?php echo self::OPTION_KEY; ?>[debug]" value="1" <?php checked( (bool) $this->get_option('debug', false), true ); ?> />
                            <?php esc_html_e('Enable debug logs in console', 'ays'); ?>
                        </label>
                    </div>
                </div>
            </details>

            <?php submit_button(__('Save Invoice Defaults', 'ays'), 'primary'); ?>
        </form>

        <!-- Company Profile Section -->
        <?php
        if ( class_exists( 'AYS_Company_Profile' ) ) {
            AYS_Company_Profile::render();
        }
        ?>

        <!-- Stripe Payment Settings Section -->
        <?php
        if ( class_exists( 'AYS_Stripe_Settings' ) ) {
            AYS_Stripe_Settings::render();
        }
        ?>

        <details class="ays-details">
            <summary>
                🏷️ <?php esc_html_e('Service Types', 'ays'); ?>
                <span class="ays-badge"><?php esc_html_e('coming soon', 'ays'); ?></span>
            </summary>
            <div>
                <div class="left-column">
                    <p><?php esc_html_e('Create service types to organize your items (e.g., Cleaning, Plumbing, Gardening).', 'ays'); ?></p>
                    <p><?php esc_html_e('Service types will be available for filtering when creating invoices and managing items.', 'ays'); ?></p>
                    <!-- Service types CRUD table will go here -->
                </div>
                <div class="right-column">
                    <h4><?php esc_html_e('📌 Examples', 'ays'); ?></h4>
                    <ul>
                        <li><?php esc_html_e('Cleaning', 'ays'); ?></li>
                        <li><?php esc_html_e('Plumbing', 'ays'); ?></li>
                        <li><?php esc_html_e('Gardening', 'ays'); ?></li>
                        <li><?php esc_html_e('Consulting', 'ays'); ?></li>
                        <li><?php esc_html_e('Repairs', 'ays'); ?></li>
                    </ul>
                </div>
            </div>
        </details>

        <!-- Live Preview Panel (from lead dashboard pattern) -->
        <div class="ays-panel">
            <div class="ays-preview-heading">
                📋 <?php esc_html_e('Invoice Preview', 'ays'); ?>
                <span class="ays-live-badge">LIVE</span>
            </div>
            <div class="ays-flex-preview">
                <div class="ays-prev-left ays-invoice-preview">
                    <div class="ays-invoice-preview-header">
                        <div class="ays-invoice-preview-from">
                            <h3><?php esc_html_e('From:', 'ays'); ?></h3>
                            <p><strong data-preview="company_name">Your Company Name</strong></p>
                            <p>123 Main Street<br/>Christchurch, 8000<br/>New Zealand</p>
                        </div>
                        <div style="text-align: right;">
                            <h1 style="margin: 0 0 10px; color: #4c51bf;">INVOICE</h1>
                            <p style="margin: 0;">
                                <?php esc_html_e('Invoice #', 'ays'); ?> <strong><span data-preview="prefix">INV-</span>001</strong><br/>
                                <small style="color: #6b7280;"><?php esc_html_e('Issued:', 'ays'); ?> 2024-10-18</small>
                            </p>
                        </div>
                    </div>

                    <div class="ays-invoice-preview-header">
                        <div class="ays-invoice-preview-to">
                            <h3><?php esc_html_e('Bill To:', 'ays'); ?></h3>
                            <p><strong>John Doe</strong><br/>john@example.com<br/>027 123 4567</p>
                        </div>
                        <div style="text-align: right;">
                            <table style="margin: 0 auto; font-size: 13px;">
                                <tr>
                                    <td style="padding: 4px 20px 4px 0; text-align: right; color: #6b7280;">
                                        <?php esc_html_e('Due:', 'ays'); ?> <strong><span data-preview="due_days">7</span> days</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 20px 4px 0; text-align: right; color: #6b7280;">
                                        <?php esc_html_e('Currency:', 'ays'); ?> <strong><span data-preview="currency">NZD</span></strong>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <table class="ays-invoice-preview-items">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Description', 'ays'); ?></th>
                                <th style="text-align: center;"><?php esc_html_e('Qty', 'ays'); ?></th>
                                <th style="text-align: right;"><?php esc_html_e('Rate', 'ays'); ?></th>
                                <th style="text-align: right;"><?php esc_html_e('Amount', 'ays'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php esc_html_e('Carpet Shampoo - 2 Bedrooms', 'ays'); ?></td>
                                <td style="text-align: center;">1</td>
                                <td style="text-align: right;">$150.00</td>
                                <td style="text-align: right;">$150.00</td>
                            </tr>
                            <tr>
                                <td><?php esc_html_e('Window Cleaning - per meter', 'ays'); ?></td>
                                <td style="text-align: center;">24</td>
                                <td style="text-align: right;">$5.00</td>
                                <td style="text-align: right;">$120.00</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="ays-invoice-preview-total">
                        <div style="grid-column: 1 / -1; display: grid; grid-template-columns: auto 1fr; gap: 20px; justify-content: flex-end;">
                            <div class="ays-invoice-preview-total-row">
                                <strong style="text-align: right;">Subtotal:</strong>
                                <span style="text-align: right;">$270.00</span>
                            </div>
                            <div class="ays-invoice-preview-total-row">
                                <strong style="text-align: right;">GST (<span data-preview="gst_rate">15</span>%):</strong>
                                <span style="text-align: right;">$40.50</span>
                            </div>
                            <div class="ays-invoice-preview-total-row final">
                                <strong style="text-align: right;">TOTAL:</strong>
                                <span style="text-align: right;">$310.50</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ays-prev-right">
                    <h4><?php esc_html_e('📌 Preview Notes', 'ays'); ?></h4>
                    <ul>
                        <li><?php esc_html_e('This preview shows how settings affect invoice appearance', 'ays'); ?></li>
                        <li><?php esc_html_e('Invoice prefix appears in the invoice number', 'ays'); ?></li>
                        <li><?php esc_html_e('GST rate is applied to taxable items automatically', 'ays'); ?></li>
                        <li><?php esc_html_e('Due date calculated from issue date + due days', 'ays'); ?></li>
                        <li><?php esc_html_e('Currency symbol displays based on selected currency', 'ays'); ?></li>
                        <li><?php esc_html_e('Save settings to apply changes globally', 'ays'); ?></li>
                    </ul>
                </div>
            </div>
            <p class="description" style="margin-top:14px;">
                <?php esc_html_e('Preview updates instantly as you edit settings above. Save your changes to apply them to all new invoices.', 'ays'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $out = [];
        $out['prefix'] = isset($input['prefix']) ? sanitize_text_field($input['prefix']) : 'INV-';
        $out['gst_rate'] = isset($input['gst_rate']) ? floatval($input['gst_rate']) : 15;
        $out['due_in_days'] = isset($input['due_in_days']) ? intval($input['due_in_days']) : 7;
        $out['currency'] = isset($input['currency']) ? sanitize_text_field($input['currency']) : 'NZD';
        // Developer options
        $out['use_rest'] = !empty($input['use_rest']) ? true : false;
        $out['debug']    = !empty($input['debug']) ? true : false;
        return $out;
    }

    /**
     * Get option helper
     */
    protected function get_option($key, $default = '') {
        $opts = get_option(self::OPTION_KEY, []);
        return isset($opts[$key]) ? $opts[$key] : $default;
    }

    /**
     * Field callbacks
     */
    public function field_invoice_prefix() {
        $val = $this->get_option('prefix', 'INV-');
        printf(
            '<input type="text" name="%1$s[prefix]" value="%2$s" class="regular-text" />',
            self::OPTION_KEY,
            esc_attr($val)
        );
    }

    public function field_gst_rate() {
        $val = $this->get_option('gst_rate', 15);
        printf(
            '<input type="number" name="%1$s[gst_rate]" value="%2$s" step="0.01" min="0" max="100" />',
            self::OPTION_KEY,
            esc_attr($val)
        );
    }

    public function field_due_in_days() {
        $val = $this->get_option('due_in_days', 7);
        printf(
            '<input type="number" name="%1$s[due_in_days]" value="%2$s" min="1" />',
            self::OPTION_KEY,
            esc_attr($val)
        );
    }

    public function field_currency() {
        $val = $this->get_option('currency', 'NZD');
        printf(
            '<select name="%1$s[currency]">
                <option value="NZD" %2$s>NZD</option>
                <option value="USD" %3$s>USD</option>
                <option value="AUD" %4$s>AUD</option>
            </select>',
            self::OPTION_KEY,
            selected($val, 'NZD', false),
            selected($val, 'USD', false),
            selected($val, 'AUD', false)
        );
    }
}
