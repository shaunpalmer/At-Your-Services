<?php
// Expected scoped variables (extracted by renderer):
// $context, $invoice_id, $invoice_number, $issue_date, $due_date, $status,
// $items, $subtotal, $tax_total, $total,
// $company_logo, $company_name, $company_address, $company_email, $company_phone,
// $client_name, $client_email, $client_phone, $client_address,
// $invoice_terms, $invoice_footer, $stripe_enabled

defined('ABSPATH') || exit;

$bank_html = class_exists('AYS_Company_Profile') ? AYS_Company_Profile::get_bank_transfer_html($invoice_number) : '';
?>
<div class="ays-invoice-preview-card ays-invoice-preview-<?php echo esc_attr($context); ?>">
  <div class="ays-invoice-topbar">
    <?php if ($context === 'client') : ?>
      <?php if ($stripe_enabled && $status !== 'paid') : ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-block;margin-right:8px;">
          <input type="hidden" name="action" value="ays_stripe_checkout" />
          <input type="hidden" name="invoice_id" value="<?php echo intval($invoice_id); ?>" />
          <?php wp_nonce_field('ays_stripe_checkout', '_wpnonce'); ?>
          <button class="button button-primary">💳 <?php esc_html_e('Pay Now', 'atyourservice'); ?></button>
        </form>
      <?php endif; ?>
      <button class="button" onclick="window.print()">🖨️ <?php esc_html_e('Print / Save PDF', 'atyourservice'); ?></button>
    <?php else: ?>
      <button class="button" onclick="window.print()">🖨️ <?php esc_html_e('Print', 'atyourservice'); ?></button>
    <?php endif; ?>
  </div>

  <div class="ays-invoice-header">
    <div class="company">
      <?php if (!empty($company_logo)) : ?>
        <img src="<?php echo esc_url($company_logo); ?>" class="company-logo" alt="<?php echo esc_attr($company_name); ?>" />
      <?php endif; ?>
      <div class="company-meta">
        <h2><?php echo esc_html($company_name); ?></h2>
        <?php if ($company_address) : ?>
          <p><?php echo nl2br(esc_html($company_address)); ?></p>
        <?php endif; ?>
        <p class="muted">
          <?php echo esc_html($company_email); ?><?php echo $company_phone ? ' • ' . esc_html($company_phone) : ''; ?>
        </p>
      </div>
    </div>
    <div class="invoice-meta">
      <h3><?php esc_html_e('Invoice #', 'atyourservice'); ?><?php echo ' ' . esc_html($invoice_number); ?></h3>
      <p><strong><?php esc_html_e('Date:', 'atyourservice'); ?></strong> <?php echo esc_html($issue_date ?: '—'); ?></p>
      <p><strong><?php esc_html_e('Due:', 'atyourservice'); ?></strong> <?php echo esc_html($due_date ?: '—'); ?></p>
      <p><strong><?php esc_html_e('Status:', 'atyourservice'); ?></strong> <?php echo esc_html(ucfirst($status)); ?></p>
    </div>
  </div>

  <div class="ays-invoice-billto">
    <h4><?php esc_html_e('Bill To:', 'atyourservice'); ?></h4>
    <p>
      <?php echo esc_html($client_name ?: ''); ?>
      <?php if ($client_address) echo '<br>' . nl2br(esc_html($client_address)); ?>
      <?php if ($client_email) echo '<br>' . esc_html($client_email); ?>
      <?php if ($client_phone) echo '<br>' . esc_html($client_phone); ?>
    </p>
  </div>

  <table class="widefat striped ays-invoice-items">
    <thead>
      <tr>
        <th><?php esc_html_e('Description', 'atyourservice'); ?></th>
        <th style="width:80px;text-align:center;"><?php esc_html_e('Qty', 'atyourservice'); ?></th>
        <th style="width:120px;text-align:right;"><?php esc_html_e('Rate', 'atyourservice'); ?></th>
        <th style="width:140px;text-align:right;"><?php esc_html_e('Total', 'atyourservice'); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($items)) : ?>
        <tr><td colspan="4" style="text-align:center;color:#6b7280;">— <?php esc_html_e('No items', 'atyourservice'); ?> —</td></tr>
      <?php else: foreach ($items as $it):
        $qty = isset($it->quantity) ? (float)$it->quantity : (isset($it->qty) ? (float)$it->qty : 1);
        $rate = isset($it->rate) ? (float)$it->rate : 0;
        $line_total = isset($it->line_total) ? (float)$it->line_total : ($qty * $rate);
      ?>
        <tr>
          <td><?php echo esc_html($it->description); ?></td>
          <td style="text-align:center;"><?php echo esc_html($qty); ?></td>
          <td style="text-align:right;">$<?php echo esc_html(number_format($rate, 2)); ?></td>
          <td style="text-align:right;">$<?php echo esc_html(number_format($line_total, 2)); ?></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
    <tfoot>
      <tr>
        <th colspan="3" style="text-align:right;"><?php esc_html_e('Subtotal:', 'atyourservice'); ?></th>
        <th style="text-align:right;">$<?php echo esc_html(number_format((float)$subtotal, 2)); ?></th>
      </tr>
      <tr>
        <th colspan="3" style="text-align:right;"><?php esc_html_e('Tax:', 'atyourservice'); ?></th>
        <th style="text-align:right;">$<?php echo esc_html(number_format((float)$tax_total, 2)); ?></th>
      </tr>
      <tr>
        <th colspan="3" style="text-align:right;"><strong><?php esc_html_e('Total:', 'atyourservice'); ?></strong></th>
        <th style="text-align:right;"><strong>$<?php echo esc_html(number_format((float)$total, 2)); ?></strong></th>
      </tr>
    </tfoot>
  </table>

  <?php if ($context === 'client'): ?>
    <div class="ays-payment-actions">
      <?php if (!empty($bank_html) && $status !== 'paid'): ?>
        <div class="ays-bank-transfer-wrap"><?php echo $bank_html; // already escaped in helper ?></div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($invoice_terms)) : ?>
    <div class="ays-invoice-footer">
      <?php echo wpautop(esc_html($invoice_terms)); ?>
    </div>
  <?php endif; ?>
  <?php if (!empty($invoice_footer)) : ?>
    <div class="ays-invoice-legal muted">
      <?php echo wpautop(esc_html($invoice_footer)); ?>
    </div>
  <?php endif; ?>
</div>
