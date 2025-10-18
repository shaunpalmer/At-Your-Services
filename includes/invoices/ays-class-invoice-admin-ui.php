<?php
/**
 * AYS Invoicing Admin Dashboard
 *
 * Main admin interface for invoicing module.
 * Tabbed dashboard with collapsible <details>/<summary> sections.
 * Two-column layouts: left (content/form), right (help/instructions).
 * Live preview where applicable.
 * Settings API for persistence.
 *
 * Pattern: Inspired by lead dashboard with professional styling.
 * Colors: Blues and purples for modern, professional appearance.
 *
 * @since 1.0
 */

defined('ABSPATH') || exit;

class AYS_Invoice_Admin_UI {

    const OPTION_KEY = 'ays_invoice_settings';

    public function __construct() {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
        add_action('current_screen', [$this, 'add_help_tabs']);
    }

    /**
     * Register admin menu
     */
    public function register_menu() {
        // Parent menu: Invoicing
        add_menu_page(
            __('Invoicing', 'ays'),
            __('Invoicing', 'ays'),
            'manage_ays_invoices',
            'ays_invoicing',
            [$this, 'render_dashboard'],
            'dashicons-money-alt', // Or custom icon
            56 // Position after custom post types
        );

        // Subpages
        add_submenu_page(
            'ays_invoicing',
            __('Invoices', 'ays'),
            __('Invoices', 'ays'),
            'manage_ays_invoices',
            'ays_invoicing',
            [$this, 'render_dashboard']
        );

        add_submenu_page(
            'ays_invoicing',
            __('Clients', 'ays'),
            __('Clients', 'ays'),
            'manage_ays_invoices',
            'ays_invoicing_clients',
            [$this, 'render_dashboard']
        );

        add_submenu_page(
            'ays_invoicing',
            __('Items', 'ays'),
            __('Items', 'ays'),
            'manage_ays_invoices',
            'ays_invoicing_items',
            [$this, 'render_dashboard']
        );

        add_submenu_page(
            'ays_invoicing',
            __('Payments', 'ays'),
            __('Payments', 'ays'),
            'manage_ays_invoices',
            'ays_invoicing_payments',
            [$this, 'render_dashboard']
        );

        add_submenu_page(
            'ays_invoicing',
            __('Reports', 'ays'),
            __('Reports', 'ays'),
            'manage_ays_invoices',
            'ays_invoicing_reports',
            [$this, 'render_dashboard']
        );

        add_submenu_page(
            'ays_invoicing',
            __('Settings', 'ays'),
            __('Settings', 'ays'),
            'manage_ays_invoices',
            'ays_invoicing_settings',
            [$this, 'render_dashboard']
        );
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
     */
    public function admin_assets($hook) {
        // Only on our invoicing pages
        if (strpos($hook, 'ays_invoicing') === false) {
            return;
        }

        // Enqueue color picker if needed
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        // Inline CSS with professional blue/purple theme
        $css = '
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

        .ays-details {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 16px;
            overflow: hidden;
        }

        .ays-details summary {
            padding: 16px 20px;
            font-weight: 600;
            cursor: pointer;
            background: linear-gradient(135deg, #6366f1 0%, #4c51bf 100%);
            color: #fff;
            user-select: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .ays-details summary:hover {
            background: linear-gradient(135deg, #7c7fff 0%, #5c65cf 100%);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
        }

        .ays-details[open] summary {
            border-bottom: 1px solid #e5e7eb;
        }

        .ays-details summary::marker {
            color: #fff;
        }

        .ays-details > div {
            padding: 24px 20px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }

        .ays-details .left-column {
            min-width: 0;
        }

        .ays-details .right-column {
            background: #f8f9fa;
            border-left: 3px solid #4c51bf;
            padding: 16px;
            border-radius: 4px;
        }

        .ays-details .right-column h4 {
            margin-top: 0;
            color: #4c51bf;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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

        .ays-panel {
            background: #f8f9fa;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin-top: 24px;
        }

        .ays-panel h3 {
            margin-top: 0;
            color: #1f2937;
            font-size: 16px;
            font-weight: 600;
        }

        .ays-form-row {
            margin-bottom: 20px;
        }

        .ays-form-row label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
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

        // Inline JavaScript for tab switching
        $js = '
        (function($) {
            $(document).ready(function() {
                // Tab switching
                $(".ays-invoicing-tab").on("click", function(e) {
                    e.preventDefault();
                    var tab = $(this).data("tab");
                    
                    // Hide all content
                    $(".ays-invoicing-content").removeClass("active");
                    $(".ays-invoicing-tab").removeClass("active");
                    
                    // Show selected content
                    $("[data-content=\"" + tab + "\"]").addClass("active");
                    $(this).addClass("active");
                });
                
                // Auto-open first tab on load
                $(".ays-invoicing-tab:first").trigger("click");
            });
        })(jQuery);
        ';

        wp_add_inline_script('jquery-core', $js);
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
     * Render main dashboard
     */
    public function render_dashboard() {
        if (!current_user_can('manage_ays_invoices')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'ays'));
        }

        $current_tab = sanitize_text_field($_GET['page'] ?? 'ays_invoicing');

        ?>
        <div class="wrap ays-invoicing-wrap">
            <div class="ays-invoicing-header">
                <h1>💰 <?php esc_html_e('Invoicing Dashboard', 'ays'); ?></h1>
            </div>

            <nav class="ays-invoicing-tabs">
                <a href="?page=ays_invoicing" class="ays-invoicing-tab <?php echo $current_tab === 'ays_invoicing' ? 'active' : ''; ?>" data-tab="invoices">
                    <?php esc_html_e('Invoices', 'ays'); ?>
                </a>
                <a href="?page=ays_invoicing_clients" class="ays-invoicing-tab <?php echo $current_tab === 'ays_invoicing_clients' ? 'active' : ''; ?>" data-tab="clients">
                    <?php esc_html_e('Clients', 'ays'); ?>
                </a>
                <a href="?page=ays_invoicing_items" class="ays-invoicing-tab <?php echo $current_tab === 'ays_invoicing_items' ? 'active' : ''; ?>" data-tab="items">
                    <?php esc_html_e('Items', 'ays'); ?>
                </a>
                <a href="?page=ays_invoicing_payments" class="ays-invoicing-tab <?php echo $current_tab === 'ays_invoicing_payments' ? 'active' : ''; ?>" data-tab="payments">
                    <?php esc_html_e('Payments', 'ays'); ?>
                </a>
                <a href="?page=ays_invoicing_reports" class="ays-invoicing-tab <?php echo $current_tab === 'ays_invoicing_reports' ? 'active' : ''; ?>" data-tab="reports">
                    <?php esc_html_e('Reports', 'ays'); ?>
                </a>
                <a href="?page=ays_invoicing_settings" class="ays-invoicing-tab <?php echo $current_tab === 'ays_invoicing_settings' ? 'active' : ''; ?>" data-tab="settings">
                    <?php esc_html_e('Settings', 'ays'); ?>
                </a>
            </nav>

            <!-- Invoices Tab -->
            <div class="ays-invoicing-content <?php echo $current_tab === 'ays_invoicing' ? 'active' : ''; ?>" data-content="invoices">
                <?php $this->render_invoices_tab(); ?>
            </div>

            <!-- Clients Tab -->
            <div class="ays-invoicing-content <?php echo $current_tab === 'ays_invoicing_clients' ? 'active' : ''; ?>" data-content="clients">
                <?php $this->render_clients_tab(); ?>
            </div>

            <!-- Items Tab -->
            <div class="ays-invoicing-content <?php echo $current_tab === 'ays_invoicing_items' ? 'active' : ''; ?>" data-content="items">
                <?php $this->render_items_tab(); ?>
            </div>

            <!-- Payments Tab -->
            <div class="ays-invoicing-content <?php echo $current_tab === 'ays_invoicing_payments' ? 'active' : ''; ?>" data-content="payments">
                <?php $this->render_payments_tab(); ?>
            </div>

            <!-- Reports Tab -->
            <div class="ays-invoicing-content <?php echo $current_tab === 'ays_invoicing_reports' ? 'active' : ''; ?>" data-content="reports">
                <?php $this->render_reports_tab(); ?>
            </div>

            <!-- Settings Tab -->
            <div class="ays-invoicing-content <?php echo $current_tab === 'ays_invoicing_settings' ? 'active' : ''; ?>" data-content="settings">
                <?php $this->render_settings_tab(); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render Invoices tab
     */
    protected function render_invoices_tab() {
        ?>
        <details class="ays-details" open>
            <summary>
                📋 <?php esc_html_e('Invoice List', 'ays'); ?>
                <span class="ays-badge"><?php esc_html_e('coming soon', 'ays'); ?></span>
            </summary>
            <div>
                <div class="left-column">
                    <p><?php esc_html_e('Invoice list and management will appear here.', 'ays'); ?></p>
                </div>
                <div class="right-column">
                    <h4><?php esc_html_e('📌 Quick Tips', 'ays'); ?></h4>
                    <ul>
                        <li><?php esc_html_e('Create invoices for any client', 'ays'); ?></li>
                        <li><?php esc_html_e('Search items by service type', 'ays'); ?></li>
                        <li><?php esc_html_e('Track payment status', 'ays'); ?></li>
                    </ul>
                </div>
            </div>
        </details>
        <?php
    }

    /**
     * Render Clients tab
     */
    protected function render_clients_tab() {
        ?>
        <details class="ays-details" open>
            <summary>
                👥 <?php esc_html_e('Manage Clients', 'ays'); ?>
                <span class="ays-badge"><?php esc_html_e('coming soon', 'ays'); ?></span>
            </summary>
            <div>
                <div class="left-column">
                    <p><?php esc_html_e('Client management interface will appear here.', 'ays'); ?></p>
                </div>
                <div class="right-column">
                    <h4><?php esc_html_e('📌 Quick Tips', 'ays'); ?></h4>
                    <ul>
                        <li><?php esc_html_e('Add client contact details', 'ays'); ?></li>
                        <li><?php esc_html_e('Track billing history', 'ays'); ?></li>
                        <li><?php esc_html_e('Email must be unique', 'ays'); ?></li>
                    </ul>
                </div>
            </div>
        </details>
        <?php
    }

    /**
     * Render Items tab
     */
    protected function render_items_tab() {
        ?>
        <details class="ays-details" open>
            <summary>
                📦 <?php esc_html_e('Service Items Catalog', 'ays'); ?>
                <span class="ays-badge"><?php esc_html_e('coming soon', 'ays'); ?></span>
            </summary>
            <div>
                <div class="left-column">
                    <p><?php esc_html_e('Your service items catalog will appear here. Add items like "Carpet Shampoo", "Hourly Labor", "Meter of Pipe", etc.', 'ays'); ?></p>
                </div>
                <div class="right-column">
                    <h4><?php esc_html_e('📌 Item Types', 'ays'); ?></h4>
                    <ul>
                        <li><?php esc_html_e('Flat fee (qty: 1)', 'ays'); ?></li>
                        <li><?php esc_html_e('Per unit (qty: N)', 'ays'); ?></li>
                        <li><?php esc_html_e('Hourly rate (qty: hours)', 'ays'); ?></li>
                        <li><?php esc_html_e('Per meter/distance', 'ays'); ?></li>
                    </ul>
                </div>
            </div>
        </details>

        <details class="ays-details">
            <summary>
                ➕ <?php esc_html_e('Create New Item', 'ays'); ?>
                <span class="ays-badge new"><?php esc_html_e('quick add', 'ays'); ?></span>
            </summary>
            <div>
                <div class="left-column">
                    <form method="post" action="">
                        <div class="ays-form-row">
                            <label for="item_description"><?php esc_html_e('Description', 'ays'); ?> *</label>
                            <input type="text" id="item_description" name="description" placeholder="<?php esc_attr_e('e.g., Carpet Shampoo 2 bed', 'ays'); ?>" />
                            <p class="description"><?php esc_html_e('What service or product is this?', 'ays'); ?></p>
                        </div>

                        <div class="ays-form-row">
                            <label for="item_service_type"><?php esc_html_e('Service Type', 'ays'); ?></label>
                            <select id="item_service_type" name="service_type_id">
                                <option value=""><?php esc_html_e('-- None --', 'ays'); ?></option>
                                <!-- Service types will populate here -->
                            </select>
                            <p class="description"><?php esc_html_e('Organize items by type (e.g., Cleaning, Plumbing)', 'ays'); ?></p>
                        </div>

                        <div class="ays-form-row">
                            <label for="item_rate"><?php esc_html_e('Rate', 'ays'); ?> *</label>
                            <input type="number" id="item_rate" name="rate" step="0.01" min="0" placeholder="0.00" />
                            <p class="description"><?php esc_html_e('Price per unit', 'ays'); ?></p>
                        </div>

                        <div class="ays-form-row">
                            <label for="item_taxable">
                                <input type="checkbox" id="item_taxable" name="taxable" value="1" checked />
                                <?php esc_html_e('Taxable (GST applies)', 'ays'); ?>
                            </label>
                        </div>

                        <?php submit_button(__('Add Item', 'ays'), 'primary', 'submit', false); ?>
                    </form>
                </div>
                <div class="right-column">
                    <h4><?php esc_html_e('💡 Tips', 'ays'); ?></h4>
                    <ul>
                        <li><?php esc_html_e('Use clear, descriptive names', 'ays'); ?></li>
                        <li><?php esc_html_e('Rate = base cost (e.g., $50/unit)', 'ays'); ?></li>
                        <li><?php esc_html_e('On invoice, qty multiplied by rate', 'ays'); ?></li>
                        <li><?php esc_html_e('Taxable depends on your jurisdiction', 'ays'); ?></li>
                    </ul>
                </div>
            </div>
        </details>
        <?php
    }

    /**
     * Render Payments tab
     */
    protected function render_payments_tab() {
        ?>
        <details class="ays-details" open>
            <summary>
                💳 <?php esc_html_e('Payment Log', 'ays'); ?>
                <span class="ays-badge"><?php esc_html_e('coming soon', 'ays'); ?></span>
            </summary>
            <div>
                <div class="left-column">
                    <p><?php esc_html_e('Payment records and audit trail will appear here.', 'ays'); ?></p>
                </div>
                <div class="right-column">
                    <h4><?php esc_html_e('📌 Payment Types', 'ays'); ?></h4>
                    <ul>
                        <li><?php esc_html_e('Cash', 'ays'); ?></li>
                        <li><?php esc_html_e('Bank transfer', 'ays'); ?></li>
                        <li><?php esc_html_e('Stripe/PayPal', 'ays'); ?></li>
                        <li><?php esc_html_e('Other', 'ays'); ?></li>
                    </ul>
                </div>
            </div>
        </details>
        <?php
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
     */
    protected function render_settings_tab() {
        ?>
        <form action="options.php" method="post">
            <?php settings_fields('ays_invoicing_settings'); ?>

            <details class="ays-details" open>
                <summary>
                    ⚙️ <?php esc_html_e('Invoice Defaults', 'ays'); ?>
                </summary>
                <div>
                    <div class="left-column">
                        <div class="ays-form-row">
                            <label for="invoice_prefix"><?php esc_html_e('Invoice Prefix', 'ays'); ?></label>
                            <input type="text" id="invoice_prefix" name="<?php echo self::OPTION_KEY; ?>[prefix]" value="<?php echo esc_attr($this->get_option('prefix', 'INV-')); ?>" maxlength="10" />
                            <p class="description"><?php esc_html_e('e.g., SC-, INV-, or custom prefix', 'ays'); ?></p>
                        </div>

                        <div class="ays-form-row">
                            <label for="gst_rate"><?php esc_html_e('GST/Tax Rate (%)', 'ays'); ?></label>
                            <input type="number" id="gst_rate" name="<?php echo self::OPTION_KEY; ?>[gst_rate]" value="<?php echo esc_attr($this->get_option('gst_rate', 15)); ?>" step="0.01" min="0" max="100" />
                            <p class="description"><?php esc_html_e('Default tax rate for new invoices (e.g., 15 for 15%)', 'ays'); ?></p>
                        </div>

                        <div class="ays-form-row">
                            <label for="due_in_days"><?php esc_html_e('Default Due In (Days)', 'ays'); ?></label>
                            <input type="number" id="due_in_days" name="<?php echo self::OPTION_KEY; ?>[due_in_days]" value="<?php echo esc_attr($this->get_option('due_in_days', 7)); ?>" min="1" />
                            <p class="description"><?php esc_html_e('How many days from issue date before invoice is due', 'ays'); ?></p>
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
                            <p class="description"><?php esc_html_e('Currency symbol for invoices', 'ays'); ?></p>
                        </div>
                    </div>
                    <div class="right-column">
                        <h4><?php esc_html_e('💡 Settings Info', 'ays'); ?></h4>
                        <ul>
                            <li><?php esc_html_e('Prefix: Used in invoice numbering', 'ays'); ?></li>
                            <li><?php esc_html_e('Tax Rate: Default GST applied', 'ays'); ?></li>
                            <li><?php esc_html_e('Due Days: Payment terms', 'ays'); ?></li>
                            <li><?php esc_html_e('Currency: Display symbol', 'ays'); ?></li>
                        </ul>
                    </div>
                </div>
            </details>

            <details class="ays-details">
                <summary>
                    🏷️ <?php esc_html_e('Service Types', 'ays'); ?>
                    <span class="ays-badge"><?php esc_html_e('coming soon', 'ays'); ?></span>
                </summary>
                <div>
                    <div class="left-column">
                        <p><?php esc_html_e('Create service types to organize your items (e.g., Cleaning, Plumbing, Gardening).', 'ays'); ?></p>
                        <p><?php esc_html_e('Service types will be available for filtering when creating invoices.', 'ays'); ?></p>
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

            <?php submit_button(__('Save Settings', 'ays'), 'primary'); ?>
        </form>
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
