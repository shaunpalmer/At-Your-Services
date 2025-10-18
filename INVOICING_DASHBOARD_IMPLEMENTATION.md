# Invoicing Dashboard Implementation

## Overview
Complete professional tabbed dashboard for the invoicing module following the **lead dashboard pattern**. Uses HTML `<details>/<summary>` collapsibles, two-column layouts, and WordPress Settings API.

## Architecture

### Files Created/Modified
- **`includes/invoices/ays-class-invoice-admin-ui.php`** - Main admin UI class (890+ lines)
- **`ays.php`** - Added initialization in plugins_loaded hook

## Key Features

### Dashboard Structure
```
💰 Invoicing Dashboard
├── Invoices Tab
├── Clients Tab
├── Items Tab
├── Payments Tab
├── Reports Tab
└── Settings Tab
```

### UI Pattern (from lead dashboard)

#### Settings API Integration
```php
register_setting('ays_invoicing_settings', self::OPTION_KEY)
add_settings_section()
add_settings_field()
settings_fields() // In forms
```

#### Collapsible Sections
```html
<details class="ays-details" open>
  <summary>📋 Section Title <span class="ays-badge">coming soon</span></summary>
  <div>
    <div class="left-column"><!-- Main content --></div>
    <div class="right-column"><!-- Help text --></div>
  </div>
</details>
```

#### Two-Column CSS Grid
```css
.ays-details > div {
  display: grid;
  grid-template-columns: 2fr 1fr; /* Left: content, Right: help */
  gap: 24px;
}

@media (max-width: 782px) {
  .ays-details > div {
    grid-template-columns: 1fr; /* Stack on mobile */
  }
}
```

## Color Scheme (Professional Blue/Purple)

### Primary Colors
- **Indigo**: `#4c51bf`, `#6366f1` - Headers, links, highlights
- **Gradient**: `linear-gradient(135deg, #6366f1 0%, #4c51bf 100%)`
- **Light Background**: `#f0f4ff`, `#f8f9fa`

### Interactive States
```css
/* Default */
summary {
  background: linear-gradient(135deg, #6366f1 0%, #4c51bf 100%);
}

/* Hover */
summary:hover {
  background: linear-gradient(135deg, #7c7fff 0%, #5c65cf 100%);
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
}
```

### Badges
- ✅ Green (`#10b981`): Success/Complete
- 🟠 Amber (`#f59e0b`): New/Feature
- 🔴 Coming Soon: `<span class="ays-badge">coming soon</span>`

## Tab System

### Tab Navigation
```html
<nav class="ays-invoicing-tabs">
  <a href="?page=ays_invoicing" class="ays-invoicing-tab">Invoices</a>
  <a href="?page=ays_invoicing_clients" class="ays-invoicing-tab">Clients</a>
  <!-- ... -->
</nav>
```

### Tab Switching (jQuery)
```javascript
// Hide all content
$(".ays-invoicing-content").removeClass("active");

// Show selected
$("[data-content=\"" + tab + "\"]").addClass("active");

// Auto-open first tab on load
$(".ays-invoicing-tab:first").trigger("click");
```

## Settings Persistence

### Option Key
```php
const OPTION_KEY = 'ays_invoice_settings';

// Stored as:
$options = get_option('ays_invoice_settings', []);
// {
//   'prefix': 'INV-',
//   'gst_rate': 15,
//   'due_in_days': 7,
//   'currency': 'NZD'
// }
```

### Sanitization
```php
public function sanitize_settings($input) {
    $out = [];
    $out['prefix'] = sanitize_text_field($input['prefix'] ?? 'INV-');
    $out['gst_rate'] = floatval($input['gst_rate'] ?? 15);
    $out['due_in_days'] = intval($input['due_in_days'] ?? 7);
    $out['currency'] = sanitize_text_field($input['currency'] ?? 'NZD');
    return $out;
}
```

## Tabs Implementation

### 1. Invoices Tab
- **Status**: Placeholder with "coming soon" badge
- **Future**: Invoice list, search, filter, create new
- **Quick Tips**: Create invoices, search by type, track status

### 2. Clients Tab
- **Status**: Placeholder with "coming soon" badge
- **Future**: Client directory, contact info, history
- **Quick Tips**: Add contacts, track history, unique emails

### 3. Items Tab
- **Status**: Partially complete
- **Features**:
  - Service items catalog list (coming soon)
  - **Quick add form** (ready to use):
    - Description field
    - Service type dropdown (will populate from DB)
    - Rate input (price per unit)
    - Taxable checkbox (GST applies)
    - Submit button
  - Item type examples: flat fee, per unit, hourly, per meter
- **Help Tips**: Clear names, rate = base cost, qty × rate formula, taxable settings

### 4. Payments Tab
- **Status**: Placeholder with "coming soon" badge
- **Future**: Payment records, audit trail
- **Payment Types**: Cash, bank transfer, Stripe/PayPal, other

### 5. Reports Tab
- **Status**: Placeholder with "coming soon" badge
- **Future**: Revenue reports, analytics
- **Reports Available**: Revenue by period, outstanding invoices, top clients, service type breakdown

### 6. Settings Tab
- **Status**: 90% complete
- **Section 1: Invoice Defaults** ✅
  - Invoice Prefix (text input, max 10 chars)
  - GST/Tax Rate (number, 0-100%, step 0.01)
  - Default Due In (days, integer, min 1)
  - Currency dropdown (NZD, USD, AUD, GBP, EUR)
  - Settings info in right column
  - Form submission: ✅ Save Settings button
- **Section 2: Service Types** (Coming soon)
  - Manage service types UI
  - Examples: Cleaning, Plumbing, Gardening, Consulting, Repairs

## Admin Assets

### CSS Inline
- 350+ lines of professional styling
- Grid layouts, flexbox, gradient backgrounds
- Responsive design with media queries (782px breakpoint)
- Smooth transitions and hover effects
- Focus states with shadow effects

### JavaScript Inline
- Tab switching logic
- Event handlers for dynamic content
- Auto-init first tab on page load

### Enqueued Resources
- WP Color Picker (for future color fields)
- jQuery (for DOM manipulation)

## Menu Registration

### Admin Menu Structure
```php
// Parent menu
add_menu_page(
    'Invoicing',
    'Invoicing',
    'manage_ays_invoices',
    'ays_invoicing',
    [$this, 'render_dashboard'],
    'dashicons-money-alt',
    56 // After CPTs
);

// Submenus
add_submenu_page(
    'ays_invoicing',
    'Invoices',
    'Invoices',
    'manage_ays_invoices',
    'ays_invoicing'
);
// ... (Clients, Items, Payments, Reports, Settings)
```

### Capability
- Uses: `manage_ays_invoices` (will be assigned to admin/editor roles)

## HTML Structure

### Main Wrapper
```html
<div class="wrap ays-invoicing-wrap">
  <div class="ays-invoicing-header">
    <h1>💰 Invoicing Dashboard</h1>
  </div>
  
  <nav class="ays-invoicing-tabs">
    <!-- Tabs -->
  </nav>
  
  <div class="ays-invoicing-content">
    <!-- Content for each tab -->
  </div>
</div>
```

### Form Fields Pattern
```html
<div class="ays-form-row">
  <label for="field_id">Field Label *</label>
  <input type="text" id="field_id" name="field_name" />
  <p class="description">Help text here</p>
</div>
```

## Form Handling

### Settings Tab Form
```html
<form action="options.php" method="post">
  <?php settings_fields('ays_invoicing_settings'); ?>
  
  <details class="ays-details" open>
    <!-- Settings content -->
    <?php submit_button('Save Settings', 'primary'); ?>
  </details>
</form>
```

### Items Tab Form (Quick Add)
```html
<form method="post" action="">
  <!-- Item fields -->
  <?php submit_button('Add Item', 'primary', 'submit', false); ?>
</form>
```

## Integration Points

### Autoloader
- `AYS_Invoice_Admin_UI` already registered in `includes/helpers/autoloader.php`

### Plugin Initialization
```php
// In ays.php plugins_loaded hook:
if (is_admin() && class_exists('AYS_Invoice_Admin_UI')) {
    new AYS_Invoice_Admin_UI();
}
```

## Responsive Design

### Mobile Breakpoint (782px)
- Two-column layout collapses to single column
- Tab navigation becomes scrollable
- Form inputs stretch to full width

### Desktop (>782px)
- Two-column layout: 2fr 1fr (left content, right help)
- 24px gap between columns
- Fixed widths for inputs (max 400px)

## Accessibility

### Semantic HTML
- `<details>/<summary>` native HTML disclosure widget
- `<form>` for all data input
- Labels properly associated with inputs
- Fieldsets for form groups

### Keyboard Navigation
- All interactive elements keyboard accessible
- Tab order logical
- Focus states visible with shadow effects

### ARIA
- Implicit via HTML elements (details, summary, form)

## Performance

### CSS Delivery
- Inline via `wp_add_inline_style()` - no extra HTTP requests
- Minimal CSS (350 lines)
- No external fonts or dependencies

### JavaScript
- Inline via `wp_add_inline_script()` - no extra HTTP requests
- jQuery only (already loaded in WP admin)
- Event delegation for tab switching
- Auto-init on page load

### Database Queries
- Settings stored as single serialized option
- No additional queries beyond Settings API

## Security

### Input Validation
- All inputs sanitized via `sanitize_text_field()`, `floatval()`, `intval()`, etc.
- Output escaped via `esc_html_e()`, `esc_attr()`, `escaped()` functions
- Form nonces via `settings_fields()`

### Authorization
- All pages check `current_user_can('manage_ays_invoices')`
- `wp_die()` on permission denied
- `check_admin_referer()` for POST actions

### SQL Injection
- Settings API handles DB operations
- No direct SQL in this class

## Future Enhancements

### Phase 2: Data Management
- [ ] Implement client CRUD operations
- [ ] Implement item CRUD operations
- [ ] Implement invoice creation/editing
- [ ] REST API endpoints for AJAX

### Phase 3: Advanced Features
- [ ] Invoice editor with line items
- [ ] Client search modal
- [ ] Item typeahead search
- [ ] Payment recording form
- [ ] Report generation

### Phase 4: Advanced UI
- [ ] Live preview (like lead dashboard)
- [ ] Inline editing
- [ ] Bulk actions (export, email)
- [ ] Dashboard widgets

## Files Summary

### ays-class-invoice-admin-ui.php (890 lines)
```
├── Class: AYS_Invoice_Admin_UI
├── Constant: OPTION_KEY = 'ays_invoice_settings'
├── Method: __construct() - Hook registration
├── Method: register_menu() - Admin menu + submenus
├── Method: register_settings() - Settings API
├── Method: admin_assets() - CSS/JS inline
├── Method: add_help_tabs() - Context help
├── Method: render_dashboard() - Main page template
├── Tab Renderers:
│   ├── render_invoices_tab()
│   ├── render_clients_tab()
│   ├── render_items_tab()
│   ├── render_payments_tab()
│   ├── render_reports_tab()
│   └── render_settings_tab()
├── Method: sanitize_settings()
├── Method: get_option()
├── Field Callbacks:
│   ├── field_invoice_prefix()
│   ├── field_gst_rate()
│   ├── field_due_in_days()
│   └── field_currency()
└── CSS/JS: ~350 lines inline styling + 10 lines JavaScript
```

## Testing Checklist

- [ ] Plugin activates without errors
- [ ] Menu appears at position 56 in admin
- [ ] "Invoicing" parent menu visible
- [ ] All 6 submenus visible
- [ ] Tab switching works (click each tab)
- [ ] Settings form saves correctly
- [ ] Settings values persist after page reload
- [ ] Mobile responsive (782px breakpoint)
- [ ] Keyboard navigation works
- [ ] No PHP errors in logs
- [ ] Security check: permission denied for non-admins

## Related Database Tables
- `wp_ays_company_profile` - Business information
- `wp_ays_clients` - Client directory
- `wp_ays_service_types` - Service categorization
- `wp_ays_items` - Service catalog
- `wp_ays_invoices` - Invoice records
- `wp_ays_invoice_items` - Line items
- `wp_ays_payments` - Payment audit log

## Related Classes
- `AYS_Invoice_Admin_UI` - This dashboard class
- `AYS_Service_Type_Repository` - Service types data access
- `AYS_Service_Type_Service` - Service types business logic
- `AYS_Client_Service` - Client CRUD (future)
- `AYS_Item_Service` - Item CRUD (future)
- `AYS_Invoice_Service` - Invoice CRUD (future)

## Git Commit
- **Commit**: Invoicing dashboard with tabbed UI and Settings API
- **Files**: 
  - ✅ `includes/invoices/ays-class-invoice-admin-ui.php` (NEW)
  - ✅ `ays.php` (MODIFIED - added initialization)
