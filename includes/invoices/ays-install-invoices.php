<?php
/**
 * AYS Invoicing Database Installer
 *
 * Creates custom database tables for the invoicing module on plugin activation.
 * Uses WordPress dbDelta() for safe table creation (idempotent).
 * Runs only once; wrapped in version check.
 *
 * Relationship Model (NZ invoicing):
 * - 1 Business (company_profile) → Many Clients
 * - 1 Client → Many Invoices
 * - 1 Invoice → Many Items (many-to-many via wp_ays_invoice_items bridge)
 * - 1 Invoice → Many Payments
 *
 * Tables created:
 * - wp_ays_company_profile    (business info, single row)
 * - wp_ays_clients            (customers, email-required)
 * - wp_ays_items              (services/products catalog)
 * - wp_ays_invoices           (invoices with status: draft, sent, viewed, overdue, paid, void)
 * - wp_ays_invoice_items      (line items, many-to-many bridge between invoices & items)
 * - wp_ays_payments           (payment log for each invoice)
 * - wp_ays_email_templates    (invoice email templates)
 * - wp_ays_email_log          (sent email audit trail)
 *
 * Conventions:
 * - Primary key: id BIGINT UNSIGNED AUTO_INCREMENT
 * - Hash: CHAR(32) UNIQUE for public/external sharing
 * - Status: ENUM with specific allowed values per table
 * - Timestamps: created_at, updated_at DATETIME; soft delete: deleted_at DATETIME NULL
 * - Money fields: DECIMAL(12,2) for precision (cents/cents, never float)
 * - Foreign keys: CASCADE on invoice delete, SET NULL on entity delete (preserves history)
 *
 * @since 1.0
 */

defined('ABSPATH') || exit;

/**
 * Install invoicing database tables.
 * Called on plugin activation via register_activation_hook().
 * Safe to call multiple times; wrapped in version check.
 */
function ays_invoices_install() {
    global $wpdb;

    // Version check: only run if version mismatch
    $installed_version = get_option('ays_invoices_db_version', '0.0.0');
    if (version_compare($installed_version, '1.1', '>=')) {
        return; // Already installed
    }

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $charset_collate = $wpdb->get_charset_collate();

    // =========================================================================
    // 1. wp_ays_company_profile - Business/Organization Info (1 row)
    // =========================================================================
    $company_table = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ays_company_profile (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        hash CHAR(32) UNIQUE NOT NULL,
        name VARCHAR(190) NOT NULL,
        business_number VARCHAR(50),
        owner_name VARCHAR(190),
        email VARCHAR(190) NOT NULL,
        phone VARCHAR(50),
        mobile VARCHAR(50),
        website VARCHAR(190),
        address_line1 VARCHAR(190),
        address_line2 VARCHAR(190),
        city VARCHAR(120),
        postcode VARCHAR(20),
        logo_id BIGINT UNSIGNED,
        settings_json LONGTEXT,
        status ENUM('active','archived') DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_status (status),
        KEY idx_email (email)
    ) $charset_collate;
    ";
    dbDelta($company_table);

    // =========================================================================
    // 2. wp_ays_clients - Customer/Client List
    // =========================================================================
    $clients_table = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ays_clients (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        hash CHAR(32) UNIQUE NOT NULL,
        name VARCHAR(190) NOT NULL,
        email VARCHAR(190) NOT NULL,
        secondary_email VARCHAR(190),
        phone VARCHAR(50),
        mobile VARCHAR(50),
        address_line1 VARCHAR(190),
        address_line2 VARCHAR(190),
        city VARCHAR(120),
        postcode VARCHAR(20),
        country VARCHAR(120),
        notes TEXT,
        total_billed DECIMAL(12,2) DEFAULT 0.00,
        status ENUM('active','archived','deleted') DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        deleted_at DATETIME NULL,
        KEY idx_email (email),
        KEY idx_name (name),
        KEY idx_status (status)
    ) $charset_collate;
    ";
    dbDelta($clients_table);

    // =========================================================================
    // 3. wp_ays_service_types - Service Type Categories
    // =========================================================================
    $service_types_table = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ays_service_types (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        hash CHAR(32) UNIQUE NOT NULL,
        name VARCHAR(190) NOT NULL,
        slug VARCHAR(100) UNIQUE NOT NULL,
        description TEXT,
        icon_class VARCHAR(100),
        color_hex VARCHAR(7),
        sort_order INT DEFAULT 0,
        status ENUM('active','archived') DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_slug (slug),
        KEY idx_status (status),
        KEY idx_sort_order (sort_order)
    ) $charset_collate;
    ";
    dbDelta($service_types_table);

    // =========================================================================
    // 4. wp_ays_items - Services/Products Catalog
    // =========================================================================
    $items_table = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ays_items (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        hash CHAR(32) UNIQUE NOT NULL,
        service_type_id BIGINT UNSIGNED NULL,
        description VARCHAR(190) NOT NULL,
        details TEXT,
        unit VARCHAR(50),
        rate DECIMAL(12,2),
        taxable TINYINT(1) DEFAULT 1,
        status ENUM('active','archived') DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_service_type_id (service_type_id),
        KEY idx_status (status),
        KEY idx_description (description),
        FOREIGN KEY fk_service_type (service_type_id) REFERENCES {$wpdb->prefix}ays_service_types(id) ON DELETE SET NULL
    ) $charset_collate;
    ";
    dbDelta($items_table);

    // =========================================================================
    // 5. wp_ays_invoices - Main Invoice Records
    // =========================================================================
    $invoices_table = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ays_invoices (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        hash CHAR(32) UNIQUE NOT NULL,
        company_id BIGINT UNSIGNED,
        client_id BIGINT UNSIGNED,
        inv_number VARCHAR(30) UNIQUE,
        prefix VARCHAR(10) DEFAULT 'SC-',
        issue_date DATE,
        due_date DATE,
        from_snapshot_json LONGTEXT NULL,
        bill_to_snapshot_json LONGTEXT NULL,
        subtotal DECIMAL(12,2) DEFAULT 0.00,
        tax_total DECIMAL(12,2) DEFAULT 0.00,
        discount DECIMAL(12,2) DEFAULT 0.00,
        discount_type ENUM('amt','pct','none') DEFAULT 'none',
        shipping DECIMAL(12,2) DEFAULT 0.00,
        total DECIMAL(12,2) DEFAULT 0.00,
        amount_paid DECIMAL(12,2) DEFAULT 0.00,
        balance DECIMAL(12,2) DEFAULT 0.00,
        currency VARCHAR(3) DEFAULT 'NZD',
        payment_url_token VARCHAR(36) UNIQUE NULL,
        notes TEXT,
        terms TEXT,
        status ENUM('draft','sent','viewed','overdue','paid','void') DEFAULT 'draft',
        primary_service_type_id BIGINT UNSIGNED NULL,
        viewed_at DATETIME NULL,
        emailed_at DATETIME NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        deleted_at DATETIME NULL,
        KEY idx_company_id (company_id),
        KEY idx_client_id (client_id),
        KEY idx_inv_number (inv_number),
        KEY idx_status (status),
        KEY idx_issue_date (issue_date),
        KEY idx_payment_url_token (payment_url_token),
        KEY idx_primary_service_type_id (primary_service_type_id),
        FOREIGN KEY fk_company (company_id) REFERENCES {$wpdb->prefix}ays_company_profile(id) ON DELETE SET NULL,
        FOREIGN KEY fk_client (client_id) REFERENCES {$wpdb->prefix}ays_clients(id) ON DELETE SET NULL,
        FOREIGN KEY fk_primary_service_type (primary_service_type_id) REFERENCES {$wpdb->prefix}ays_service_types(id) ON DELETE SET NULL
    ) $charset_collate;
    ";
    dbDelta($invoices_table);

    // =========================================================================
    // 6. wp_ays_invoice_items - Line Items (Many-to-Many Bridge)
    // =========================================================================
    $invoice_items_table = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ays_invoice_items (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        hash CHAR(32) UNIQUE NOT NULL,
        invoice_id BIGINT UNSIGNED NOT NULL,
        item_id BIGINT UNSIGNED NULL,
        description TEXT,
        details TEXT,
        qty DECIMAL(12,2) DEFAULT 1.00,
        rate DECIMAL(12,2) DEFAULT 0.00,
        taxable TINYINT(1) DEFAULT 1,
        line_tax DECIMAL(12,2) DEFAULT 0.00,
        line_total DECIMAL(12,2) DEFAULT 0.00,
        sort_order INT DEFAULT 0,
        status ENUM('active','archived') DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_invoice_id (invoice_id),
        KEY idx_item_id (item_id),
        KEY idx_sort_order (sort_order),
        FOREIGN KEY fk_invoice (invoice_id) REFERENCES {$wpdb->prefix}ays_invoices(id) ON DELETE CASCADE,
        FOREIGN KEY fk_item (item_id) REFERENCES {$wpdb->prefix}ays_items(id) ON DELETE SET NULL
    ) $charset_collate;
    ";
    dbDelta($invoice_items_table);

    // =========================================================================
    // 7. wp_ays_payments - Payment Log/Record
    // =========================================================================
    $payments_table = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ays_payments (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        hash CHAR(32) UNIQUE NOT NULL,
        invoice_id BIGINT UNSIGNED NOT NULL,
        method ENUM('cash','bank','stripe','paypal','other') DEFAULT 'cash',
        amount DECIMAL(12,2) DEFAULT 0.00,
        txn_id VARCHAR(100),
        notes TEXT,
        created_by_user BIGINT UNSIGNED NULL,
        received_at DATETIME,
        status ENUM('pending','confirmed','failed','refunded') DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_invoice_id (invoice_id),
        KEY idx_method (method),
        KEY idx_status (status),
        KEY idx_created_by_user (created_by_user),
        FOREIGN KEY fk_invoice (invoice_id) REFERENCES {$wpdb->prefix}ays_invoices(id) ON DELETE CASCADE
    ) $charset_collate;
    ";
    dbDelta($payments_table);

    // =========================================================================
    // 8. wp_ays_email_templates - Invoice Email Templates
    // =========================================================================
    $email_templates_table = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ays_email_templates (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        hash CHAR(32) UNIQUE NOT NULL,
        slug VARCHAR(50) UNIQUE NOT NULL,
        subject VARCHAR(190),
        body_html LONGTEXT,
        body_text TEXT,
        enabled TINYINT(1) DEFAULT 1,
        status ENUM('active','archived') DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_slug (slug),
        KEY idx_status (status)
    ) $charset_collate;
    ";
    dbDelta($email_templates_table);

    // =========================================================================
    // 9. wp_ays_email_log - Email Send Audit Trail
    // =========================================================================
    $email_log_table = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ays_email_log (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        hash CHAR(32) UNIQUE NOT NULL,
        invoice_id BIGINT UNSIGNED,
        to_email VARCHAR(190),
        subject VARCHAR(190),
        status ENUM('queued','sent','failed') DEFAULT 'queued',
        provider_msg TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        KEY idx_invoice_id (invoice_id),
        KEY idx_to_email (to_email),
        KEY idx_status (status),
        FOREIGN KEY fk_invoice (invoice_id) REFERENCES {$wpdb->prefix}ays_invoices(id) ON DELETE SET NULL
    ) $charset_collate;
    ";
    dbDelta($email_log_table);

    // =========================================================================
    // Note: Company profile seeding is handled separately via admin setup wizard
    // (See: Invoicing Settings page in WordPress admin)
    // This allows multiple service businesses to use the plugin without
    // hardcoded branding.
    // =========================================================================

    // =========================================================================
    // Seed: Default Email Templates (invoice_customer, invoice_admin)
    // Generic templates suitable for any service business
    // =========================================================================
    $customer_template_exists = $wpdb->get_row("SELECT id FROM {$wpdb->prefix}ays_email_templates WHERE slug = 'invoice_customer' LIMIT 1");
    if (!$customer_template_exists) {
        $wpdb->insert(
            "{$wpdb->prefix}ays_email_templates",
            [
                'hash'       => md5(uniqid(mt_rand(), true)),
                'slug'       => 'invoice_customer',
                'subject'    => 'Invoice {invoice_number}',
                'body_html'  => '<p>Dear {client_name},</p>
<p>Thank you for your business. Please find your invoice attached.</p>
<p><strong>Invoice Details:</strong><br/>
Invoice Number: {invoice_number}<br/>
Due Date: {invoice_due_date}<br/>
Amount Due: {invoice_total}</p>
<p>[all-fields]</p>
<p>Please reply to this email if you have any questions.</p>
<p>Best regards</p>',
                'body_text'  => 'Dear {client_name},

Thank you for your business. Please find your invoice details below.

Invoice Number: {invoice_number}
Due Date: {invoice_due_date}
Amount Due: {invoice_total}

Best regards',
                'enabled'    => 1,
                'status'     => 'active',
            ],
            ['%s', '%s', '%s', '%s', '%s', '%d', '%s']
        );
    }

    $admin_template_exists = $wpdb->get_row("SELECT id FROM {$wpdb->prefix}ays_email_templates WHERE slug = 'invoice_admin' LIMIT 1");
    if (!$admin_template_exists) {
        $wpdb->insert(
            "{$wpdb->prefix}ays_email_templates",
            [
                'hash'       => md5(uniqid(mt_rand(), true)),
                'slug'       => 'invoice_admin',
                'subject'    => 'New Invoice Created: {invoice_number} - {client_name}',
                'body_html'  => '<p>A new invoice has been created.</p>
<p><strong>Client:</strong> {client_name} ({client_email})<br/>
<strong>Invoice:</strong> {invoice_number}<br/>
<strong>Amount:</strong> {invoice_total}<br/>
<strong>Due Date:</strong> {invoice_due_date}</p>
<p><a href="{admin_url}">View and manage invoice</a></p>',
                'body_text'  => 'A new invoice has been created.

Client: {client_name} ({client_email})
Invoice: {invoice_number}
Amount: {invoice_total}
Due Date: {invoice_due_date}

View and manage invoice: {admin_url}',
                'enabled'    => 1,
                'status'     => 'active',
            ],
            ['%s', '%s', '%s', '%s', '%s', '%d', '%s']
        );
    }

    // =========================================================================
    // Mark installation complete
    // =========================================================================
    update_option('ays_invoices_db_version', '1.1');
}

/**
 * Hook the installer into plugin activation.
 * Call this in your main plugin file (ays.php):
 *
 * register_activation_hook(__FILE__, 'ays_invoices_install');
 */
// This is called from ays.php, not here.
