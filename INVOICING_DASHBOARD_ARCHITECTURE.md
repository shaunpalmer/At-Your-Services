# Invoicing Dashboard - Visual Architecture

## Admin Menu Structure
```
WordPress Admin
└── 💰 Invoicing (Position 56) ← NEW
    ├── 📋 Invoices
    │   └── Renders: ays_invoicing page
    ├── 👥 Clients  
    │   └── Renders: ays_invoicing_clients page
    ├── 📦 Items
    │   └── Renders: ays_invoicing_items page
    ├── 💳 Payments
    │   └── Renders: ays_invoicing_payments page
    ├── 📊 Reports
    │   └── Renders: ays_invoicing_reports page
    └── ⚙️ Settings
        └── Renders: ays_invoicing_settings page
```

---

## Page Rendering Flow

### URL to Rendering
```
wordpress.local/wp-admin/admin.php?page=ays_invoicing
    ↓
admin_menu hook fires
    ↓
add_menu_page() + add_submenu_page()
    ↓
all tabs point to render_dashboard()
    ↓
render_dashboard() reads $_GET['page']
    ↓
Shows correct tab content (invoices, clients, etc.)
    ↓
Tabs stay visible (tab switching via jQuery)
```

### Tab Switching (Client-side)
```
User clicks: [Clients Tab]
    ↓
jQuery captures click
    ↓
All .ays-invoicing-content hidden
    ↓
Show [data-content="clients"]
    ↓
All .ays-invoicing-tab inactive
    ↓
Set [Clients Tab] active
    ↓
NO PAGE RELOAD - instant switch
```

---

## Page Structure (HTML Tree)

```html
<div class="wrap ays-invoicing-wrap">
  ├── <div class="ays-invoicing-header">
  │   └── <h1>💰 Invoicing Dashboard</h1>
  │
  ├── <nav class="ays-invoicing-tabs">
  │   ├── <a href="?page=ays_invoicing" class="ays-invoicing-tab">
  │   ├── <a href="?page=ays_invoicing_clients" class="ays-invoicing-tab">
  │   ├── <a href="?page=ays_invoicing_items" class="ays-invoicing-tab">
  │   ├── <a href="?page=ays_invoicing_payments" class="ays-invoicing-tab">
  │   ├── <a href="?page=ays_invoicing_reports" class="ays-invoicing-tab">
  │   └── <a href="?page=ays_invoicing_settings" class="ays-invoicing-tab">
  │
  ├── <div class="ays-invoicing-content" data-content="invoices">
  │   └── render_invoices_tab() output
  │
  ├── <div class="ays-invoicing-content" data-content="clients">
  │   └── render_clients_tab() output
  │
  ├── <div class="ays-invoicing-content" data-content="items">
  │   └── render_items_tab() output
  │       ├── <details class="ays-details" open>
  │       │   ├── <summary>📦 Service Items Catalog</summary>
  │       │   └── <div>
  │       │       ├── <div class="left-column">...</div>
  │       │       └── <div class="right-column">...</div>
  │       │
  │       └── <details class="ays-details">
  │           ├── <summary>➕ Create New Item</summary>
  │           └── <div>
  │               ├── <div class="left-column">
  │               │   └── <form>
  │               │       ├── <div class="ays-form-row">...</div>
  │               │       ├── <div class="ays-form-row">...</div>
  │               │       └── <button>Add Item</button>
  │               └── <div class="right-column">
  │                   └── <h4>💡 Tips</h4>
  │
  ├── <div class="ays-invoicing-content" data-content="payments">
  │   └── render_payments_tab() output
  │
  ├── <div class="ays-invoicing-content" data-content="reports">
  │   └── render_reports_tab() output
  │
  └── <div class="ays-invoicing-content" data-content="settings">
      └── render_settings_tab() output
          └── <form action="options.php" method="post">
              ├── <?php settings_fields('ays_invoicing_settings'); ?>
              │
              ├── <details class="ays-details" open>
              │   ├── <summary>⚙️ Invoice Defaults</summary>
              │   └── <div>
              │       ├── <div class="left-column">
              │       │   ├── <div class="ays-form-row">
              │       │   │   ├── <label>Invoice Prefix</label>
              │       │   │   ├── <input type="text" name="ays_invoice_settings[prefix]" />
              │       │   │   └── <p class="description">Help text...</p>
              │       │   ├── <div class="ays-form-row">
              │       │   │   ├── <label>GST/Tax Rate (%)</label>
              │       │   │   ├── <input type="number" ... />
              │       │   │   └── <p class="description">...</p>
              │       │   ├── <div class="ays-form-row">
              │       │   │   ├── <label>Due In (Days)</label>
              │       │   │   ├── <input type="number" ... />
              │       │   │   └── <p class="description">...</p>
              │       │   └── <div class="ays-form-row">
              │       │       ├── <label>Currency</label>
              │       │       ├── <select ...>
              │       │       │   ├── <option>NZD</option>
              │       │       │   ├── <option>USD</option>
              │       │       │   ├── <option>AUD</option>
              │       │       │   ├── <option>GBP</option>
              │       │       │   └── <option>EUR</option>
              │       │       └── <p class="description">...</p>
              │       │
              │       └── <div class="right-column">
              │           └── Help tips...
              │
              ├── <details class="ays-details">
              │   ├── <summary>🏷️ Service Types</summary>
              │   └── Help content...
              │
              └── <?php submit_button('Save Settings'); ?>
```

---

## CSS Class Structure

### Layout Classes
```
.ays-invoicing-wrap
├── .ays-invoicing-header
│   └── h1
├── .ays-invoicing-tabs
│   └── .ays-invoicing-tab (multiple)
│       - .active (when selected)
└── .ays-invoicing-content (multiple)
    - .active (when visible)
```

### Details/Summary Classes
```
.ays-details
├── summary
├── > div (content container)
│   ├── .left-column (2fr)
│   └── .right-column (1fr)
│       ├── h4
│       ├── ul
│       └── li
```

### Form Classes
```
.ays-form-row
├── label
├── input | select | textarea
└── .description (help text)
```

### Badge Classes
```
.ays-badge
├── (default) - green (#10b981)
└── .ays-badge.new - amber (#f59e0b)
```

---

## CSS Grid Layouts

### Desktop (>782px) - Two Column
```
┌────────────────────────────────────────────┐
│          Header: Full Width                │
├────────────┬────────────────────────────────┤
│  Left: 2fr │ Right: 1fr (help panel)      │
│            │                                │
│ Main       │ 💡 Tips                       │
│ Content    │ • Tip 1                       │
│ (details)  │ • Tip 2                       │
│            │ • Tip 3                       │
├────────────┴────────────────────────────────┤
│      Form Buttons / Save Button             │
└────────────────────────────────────────────┘
Gap: 24px
```

### Mobile (<782px) - Single Column
```
┌────────────────────────────────────────────┐
│          Header: Full Width                │
├────────────────────────────────────────────┤
│ Main Content (full width)                  │
│                                            │
│ (details - full width)                     │
├────────────────────────────────────────────┤
│ Help Panel (full width)                    │
│ 💡 Tips                                    │
│ • Tip 1                                    │
│ • Tip 2                                    │
├────────────────────────────────────────────┤
│      Form Buttons / Save Button             │
└────────────────────────────────────────────┘
Gap: 24px (both dimensions)
```

---

## Component: Details/Summary Pattern

### Visual States

#### Closed State
```
┌─────────────────────────────────────────┐
│ ▶ 📦 Service Items Catalog    coming..  │ ← Blue gradient
└─────────────────────────────────────────┘
```

CSS:
```css
.ays-details summary {
    background: linear-gradient(135deg, #6366f1 0%, #4c51bf 100%);
    color: white;
    padding: 16px 20px;
    font-weight: 600;
    cursor: pointer;
}
```

#### Hover State (Closed)
```
┌─────────────────────────────────────────┐
│ ▶ 📦 Service Items Catalog    coming..  │ ← Lighter blue + shadow
└─────────────────────────────────────────┘
```

CSS:
```css
.ays-details summary:hover {
    background: linear-gradient(135deg, #7c7fff 0%, #5c65cf 100%);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
}
```

#### Open State
```
┌─────────────────────────────────────────┐
│ ▼ 📦 Service Items Catalog    coming..  │ ← Gradient + border
├─────────────────────────────────────────┤
│ ┌─────────────────┬────────────────────┐│
││ Left Column     │ Right Column        ││
││ Main content    │ 💡 Quick Tips       ││
││                 │ • Tip 1             ││
││                 │ • Tip 2             ││
│└─────────────────┴────────────────────┘│
└─────────────────────────────────────────┘
```

CSS:
```css
.ays-details[open] summary {
    border-bottom: 1px solid #e5e7eb;
}

.ays-details > div {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
    padding: 24px 20px;
}
```

---

## Component: Tabs

### Tab Bar Structure
```
┌─────────────────────────────────────────────────────┐
│ [Invoices] [Clients] [Items] [Payments] [Reports]  │
│                                                     │
│ ▼ Underline on active, light background            │
└─────────────────────────────────────────────────────┘
```

CSS:
```css
.ays-invoicing-tabs {
    display: flex;
    gap: 2px;
    border-bottom: 2px solid #e5e7eb;
}

.ays-invoicing-tab {
    padding: 10px 16px;
    border-bottom: 3px solid transparent;
    color: #6b7280;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.ays-invoicing-tab.active {
    color: #4c51bf;
    border-bottom-color: #4c51bf;
    background: #f0f4ff;
}
```

---

## Component: Form Field

### Structure
```
┌─────────────────────────────────────────┐
│ Invoice Prefix                          │
│ [________INV-__________]  max 10 chars  │
│ e.g., SC-, INV-, or custom prefix       │
└─────────────────────────────────────────┘
```

HTML:
```html
<div class="ays-form-row">
    <label for="invoice_prefix">Invoice Prefix</label>
    <input 
        type="text" 
        id="invoice_prefix" 
        name="ays_invoice_settings[prefix]" 
        value="INV-" 
        maxlength="10" 
    />
    <p class="description">e.g., SC-, INV-, or custom prefix</p>
</div>
```

CSS:
```css
.ays-form-row {
    margin-bottom: 20px;
}

.ays-form-row label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #374151;
}

.ays-form-row input,
.ays-form-row select {
    width: 100%;
    max-width: 400px;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 14px;
}

.ays-form-row input:focus,
.ays-form-row select:focus {
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
```

---

## Asset Delivery

### CSS Delivery
```php
wp_add_inline_style('wp-admin', $css);
    ↓
Inlines 350 lines of CSS into wp-admin's output
    ↓
No extra HTTP request
    ↓
Loads immediately with page
```

### JavaScript Delivery
```php
wp_add_inline_script('jquery-core', $js);
    ↓
Inlines 10 lines of jQuery into jquery-core's output
    ↓
jQuery already loaded in admin
    ↓
Executes immediately when DOM ready
```

---

## Data Flow: Settings Save

### User Action
```
User enters settings:
  - Prefix: "SC-"
  - GST Rate: 20
  - Due In Days: 14
  - Currency: "AUD"
    ↓
Click "Save Settings"
```

### Form Submission
```
<form action="options.php" method="post">
    <?php settings_fields('ays_invoicing_settings'); ?>
    ...
    <input type="hidden" name="option_page" value="ays_invoicing_settings" />
    <input type="hidden" name="action" value="update" />
    <input type="hidden" name="nonce" value="..." />
    ...
    <?php submit_button('Save Settings'); ?>
</form>
    ↓
POST to options.php
```

### Server-side Processing
```
options.php receives POST
    ↓
Verifies nonce (CSRF protection)
    ↓
Calls do_settings_sections('ays_invoicing_settings')
    ↓
Calls sanitize_settings($input):
  - sanitize_text_field('SC-') → 'SC-'
  - floatval(20) → 20.0
  - intval(14) → 14
  - sanitize_text_field('AUD') → 'AUD'
    ↓
Calls update_option('ays_invoice_settings', [...])
    ↓
Redirects back to admin page with success message
```

### Data Retrieval
```
Admin page loads:
    ↓
$opts = get_option('ays_invoice_settings', [])
    ↓
$opts = [
    'prefix' => 'SC-',
    'gst_rate' => 20,
    'due_in_days' => 14,
    'currency' => 'AUD'
]
    ↓
Populate form fields with values:
<input value="<?php echo $opts['prefix']; ?>" />
    ↓
Form displays saved values
```

---

## Security Flow

### Authorization Check
```
URL: /wp-admin/admin.php?page=ays_invoicing
    ↓
render_dashboard() called
    ↓
if (!current_user_can('manage_ays_invoices')) {
    wp_die('You do not have permission');
}
    ↓
User either:
  ✅ Has capability → Render page
  ❌ No capability → Die with message
```

### Input Sanitization
```
User input: "Prefix: <script>alert('xss')</script>"
    ↓
$input['prefix'] = "<script>alert('xss')</script>"
    ↓
sanitize_text_field($input['prefix'])
    ↓
Output: "Prefixscriptalertxssscript"
    ↓
Stored safely in database
```

### Output Escaping
```
Stored value: "SC-"
    ↓
Display in template: <?php echo esc_attr($value); ?>
    ↓
Output: SC-
    ↓
Safe to render in HTML attribute
```

---

## Keyboard Navigation Flow

### Tab Order
```
1. Header (h1) - not focusable
2. Tab 1 [Invoices] ← User presses Tab
3. Tab 2 [Clients]
4. Tab 3 [Items]
5. Tab 4 [Payments]
6. Tab 5 [Reports]
7. Tab 6 [Settings]
8. First <details> disclosure button ← User presses Tab
9. <input> field within details ← User presses Tab
10. <input> field ...
11. <select> field ...
12. [Submit Button] ← User presses Tab
13. Help panel (not focusable, read-only)
14. Back to top ← User presses Tab
```

### Interaction
```
User presses Enter on tab:
    ↓
Click event fires
    ↓
jQuery handler prevents default
    ↓
Switches tab content
    ↓
No page reload
    ↓
Focus remains on tab (visual indicator)

User presses Space on <details>:
    ↓
Native browser behavior
    ↓
Details toggle open/close
    ↓
Content becomes focusable
```

---

## Browser Compatibility

### Features Used
```
Feature                 Support
─────────────────────────────────────
<details>/<summary>     ✅ All modern browsers
CSS Grid                ✅ All modern browsers
CSS Gradient            ✅ All modern browsers
CSS Flexbox             ✅ All modern browsers
jQuery 1.12+            ✅ WordPress standard
ES5 JavaScript          ✅ IE9+
Inline CSS/JS           ✅ All browsers
```

### Tested Browsers
- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+

### Fallbacks
- Details/Summary has native support (no JS needed)
- Grid collapses to single column on no-support browsers
- Forms work without JavaScript (server-side processing)

---

This architecture ensures a **professional, maintainable, and performant** invoicing dashboard that follows WordPress best practices and established UI patterns from the codebase.
