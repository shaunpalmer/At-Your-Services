<?php
// Exit if accessed directly
defined('ABSPATH') || exit;

global $wpdb;
$table      = $wpdb->prefix . 'ays_invoices';
$invoice_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$invoice    = $invoice_id ? $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $invoice_id)) : null;

// Fetch clients for dropdown
$clients = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}ays_clients WHERE status = 'active' ORDER BY name");
?>

<div class="wrap ays-invoice-builder-wrap" id="ays-invoice-builder-wrap">
  <div class="ays-invoice-header">
    <h1><?php echo $invoice ? esc_html(sprintf(__('Edit Invoice #%d', 'atyourservice'), (int) $invoice->id)) : esc_html__('Create New Invoice', 'atyourservice'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=ays-dashboard&tab=invoices')); ?>" class="button">⬅ <?php esc_html_e('Back to Invoices', 'atyourservice'); ?></a>
    <button id="ays-save-invoice" class="button button-primary">💾 <?php esc_html_e('Save Invoice', 'atyourservice'); ?></button>
  </div>

  <input type="hidden" id="ays-invoice-id" value="<?php echo $invoice ? intval($invoice->id) : 0; ?>">

  <div class="ays-invoice-grid">
    <!-- Left: Inputs -->
    <div class="ays-invoice-form">
      <h2><?php esc_html_e('Invoice Details', 'atyourservice'); ?></h2>

      <label for="ays-client"><?php esc_html_e('Client', 'atyourservice'); ?></label>
      <div style="display:flex; gap:8px; margin-bottom:8px;">
        <select id="ays-client" style="flex-grow:1;">
          <option value=""><?php esc_html_e('— Select a client —', 'atyourservice'); ?></option>
          <?php foreach ($clients as $c): ?>
            <option value="<?php echo esc_attr($c->id); ?>" <?php selected($invoice && (int)$invoice->client_id === (int)$c->id); ?>>
              <?php echo esc_html($c->name); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button type="button" class="button" id="ays-toggle-new-client">➕ <?php esc_html_e('New', 'atyourservice'); ?></button>
      </div>

      <!-- Inline New Client Form (Hidden by default) -->
      <div id="ays-new-client-form" style="display:none; background:#f9fafb; padding:12px; border:1px solid #e5e7eb; border-radius:4px; margin-bottom:16px;">
        <h4 style="margin-top:0;"><?php esc_html_e('Create New Client', 'atyourservice'); ?></h4>
        <div style="display:grid; gap:8px;">
            <input type="text" id="new-client-name" placeholder="<?php esc_attr_e('Client Name *', 'atyourservice'); ?>" required>
            <input type="email" id="new-client-email" placeholder="<?php esc_attr_e('Email', 'atyourservice'); ?>">
            <input type="text" id="new-client-phone" placeholder="<?php esc_attr_e('Phone', 'atyourservice'); ?>">
            <textarea id="new-client-address" placeholder="<?php esc_attr_e('Address', 'atyourservice'); ?>" rows="2"></textarea>
            <div style="display:flex; gap:8px;">
                <button type="button" class="button button-primary" id="ays-create-client-btn"><?php esc_html_e('Create Client', 'atyourservice'); ?></button>
                <button type="button" class="button" id="ays-cancel-client-btn"><?php esc_html_e('Cancel', 'atyourservice'); ?></button>
            </div>
        </div>
      </div>

      <label for="ays-issue-date"><?php esc_html_e('Issue Date', 'atyourservice'); ?></label>
      <input type="date" id="ays-issue-date" value="<?php echo esc_attr($invoice ? substr((string)$invoice->issue_date,0,10) : date('Y-m-d')); ?>">

      <label for="ays-due-date"><?php esc_html_e('Due Date', 'atyourservice'); ?></label>
      <input type="date" id="ays-due-date" value="<?php echo esc_attr($invoice ? substr((string)$invoice->due_date,0,10) : date('Y-m-d', strtotime('+30 days'))); ?>">

      <label for="ays-notes"><?php esc_html_e('Notes', 'atyourservice'); ?></label>
      <textarea id="ays-notes" placeholder="<?php esc_attr_e('Optional notes or payment terms', 'atyourservice'); ?>"><?php echo $invoice ? esc_textarea((string)$invoice->notes) : ''; ?></textarea>

      <h3><?php esc_html_e('Line Items', 'atyourservice'); ?></h3>
      <div id="ays-items">
        <div class="item-row">
          <input type="text" class="item-desc" placeholder="<?php esc_attr_e('Description', 'atyourservice'); ?>">
          <input type="number" class="item-qty" value="1" min="1">
          <input type="number" class="item-rate" value="0" step="0.01">
          <button class="remove-item" type="button">✖</button>
        </div>
      </div>

      <button id="add-item" class="button" type="button">➕ <?php esc_html_e('Add Item', 'atyourservice'); ?></button>
    </div>

    <!-- Right: Preview -->
    <div class="ays-invoice-preview">
      <div class="invoice-card">
        <header>
          <h1>Super Clean</h1>
          <p>P.O BOX 262 Christchurch Canterbury NZ<br>
          projectstudioswebdesign@gmail.com<br>
          +64 22 101 4024</p>
        </header>

        <section class="invoice-meta">
          <p><strong><?php esc_html_e('Invoice Date:', 'atyourservice'); ?></strong> <span id="prev-date">—</span></p>
          <p><strong><?php esc_html_e('Due Date:', 'atyourservice'); ?></strong> <span id="prev-due">—</span></p>
          <p><strong><?php esc_html_e('Client:', 'atyourservice'); ?></strong> <span id="prev-client">—</span></p>
        </section>

        <table class="invoice-table">
          <thead>
            <tr><th><?php esc_html_e('Description', 'atyourservice'); ?></th><th><?php esc_html_e('Qty', 'atyourservice'); ?></th><th><?php esc_html_e('Rate', 'atyourservice'); ?></th><th><?php esc_html_e('Total', 'atyourservice'); ?></th></tr>
          </thead>
          <tbody id="preview-items"></tbody>
          <tfoot>
            <tr><td colspan="3"><?php esc_html_e('Subtotal', 'atyourservice'); ?></td><td id="subtotal">$0.00</td></tr>
            <tr><td colspan="3"><?php esc_html_e('Tax (15%)', 'atyourservice'); ?></td><td id="tax">$0.00</td></tr>
            <tr><td colspan="3"><strong><?php esc_html_e('Total', 'atyourservice'); ?></strong></td><td id="grand-total">$0.00</td></tr>
          </tfoot>
        </table>

        <section class="invoice-notes">
          <p id="prev-notes" class="italic"></p>
        </section>
      </div>
    </div>
  </div>
</div>
