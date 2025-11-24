# Premium Feature Gating Plan

**Author:** Development Team  
**Date:** November 24, 2025  
**Status:** PLANNING - Ready for Implementation Phase 10  
**Document:** Strategic reference for feature versioning

---

## Overview

Single codebase strategy: One plugin with conditional feature flags. No code duplication. When a user installs the free version, premium features are gracefully hidden/disabled but code remains in place for easy upgrade activation.

---

## Feature Classification

### TIER 1: FREE FEATURES (Always Included)

#### Invoicing - Core
- Create invoices
- View invoices
- Edit invoices
- Delete invoices
- Email invoices (basic template)
- Download PDF (basic)
- Add/edit/delete line items
- Calculate totals
- Single tax rate
- Mark as paid (status tracking)

#### Leads - Core
- Lead capture forms
- Lead list view
- Basic export (CSV)
- Lead status tracking
- Basic search/filter

#### Client Portal - Core
- View assigned invoices
- View invoice details
- Download invoice PDF
- View contact info
- Client profile view (read-only)

#### Reporting - Core
- Invoice count
- Total revenue (current/YTD)
- Outstanding balance
- Unpaid invoice count
- Simple dashboard charts

---

### TIER 2: PREMIUM FEATURES (License Required)

#### Invoicing - Advanced
- Payment processing (Stripe integration)
- Recurring invoices
- Deposits & partial payments
- Multi-currency support
- Custom invoice templates
- Automated payment reminders
- Invoice sequences/follow-ups
- Deposit tracking
- Payment plans

#### Leads - Advanced
- Lead scoring system
- Automated workflows
- Email sequences
- Lead source tracking
- Smart assignment rules
- Advanced filtering/segmentation
- Lead lifecycle tracking
- Performance analytics

#### Client Portal - Advanced
- Profile editing capability
- Document upload/sharing
- Messaging system
- Payment portal
- Quote acceptance workflow
- Project tracking
- Team collaboration

#### Reporting - Advanced
- Custom date range filtering
- Forecasting & analytics
- Export to Excel/PDF
- Custom dashboard widgets
- Team performance metrics
- Revenue forecasting
- Client profitability analysis

---

## Gating Implementation

### License Class (Already Created)

**File:** `includes/helpers/class-ays-license.php`

```php
// Check if feature is enabled
ays_is_premium() // Returns true if any license active
ays_is_premium( 'feature_slug' ) // Check specific feature

// Show upgrade prompt
ays_show_premium_cta( 'Feature Name' )
```

### Integration Points

#### Invoicing Tab
**File:** `includes/invoices/ays-class-invoices-tab.php`

| Line Range | Feature | Gate |
|-----------|---------|------|
| ~850-900 | Payment buttons/section | `ays_is_premium('advanced_invoicing')` |
| ~920-950 | Recurring invoice setup | `ays_is_premium('advanced_invoicing')` |
| ~970-1000 | Multi-currency selector | `ays_is_premium('advanced_invoicing')` |
| ~1200+ | Custom templates | `ays_is_premium('advanced_invoicing')` |

#### Client Dashboard
**File:** `includes/client/class-ays-client-dashboard.php`

| Feature | Gate |
|---------|------|
| Edit profile button | `ays_is_premium('client_portal_advanced')` |
| Document upload | `ays_is_premium('client_portal_advanced')` |
| Messaging tab | `ays_is_premium('client_portal_advanced')` |
| Payment portal | `ays_is_premium('advanced_invoicing')` |

#### Leads Dashboard
**File:** `admin/class-ays-lead-dashboard.php`

| Feature | Gate |
|---------|------|
| Lead scoring | `ays_is_premium('lead_scoring')` |
| Automation workflows | `ays_is_premium('lead_automation')` |
| Email sequences | `ays_is_premium('email_sequences')` |

#### Reporting
**File:** `includes/pages/` (reporting module)

| Feature | Gate |
|---------|------|
| Custom date ranges | `ays_is_premium('reporting_advanced')` |
| Export to Excel | `ays_is_premium('reporting_advanced')` |
| Forecasting | `ays_is_premium('reporting_advanced')` |
| Analytics | `ays_is_premium('reporting_advanced')` |

---

## Code Patterns

### Pattern 1: Conditional Display
```php
if ( ays_is_premium( 'advanced_invoicing' ) ) {
    echo $this->render_stripe_payment_section();
} else {
    ays_show_premium_cta( 'Payment Processing' );
}
```

### Pattern 2: Remove/Disable Actions
```php
if ( ! ays_is_premium( 'client_portal_advanced' ) ) {
    remove_action( 'wp_footer', 'render_client_edit_button' );
    remove_filter( 'ays_client_tabs', 'add_messaging_tab' );
}
```

### Pattern 3: CSS Hide
```php
if ( ! ays_is_premium( 'reporting_advanced' ) ) {
    echo '<style>
        .ays-export-button { display: none !important; }
        .ays-custom-date-range { display: none !important; }
    </style>';
}
```

### Pattern 4: Return Early
```php
public function create_payment_intent() {
    if ( ! ays_is_premium( 'advanced_invoicing' ) ) {
        wp_send_json_error( 'Upgrade to premium' );
    }
    // Process payment
}
```

---

## Database Storage

```php
// wp_options table
ays_license_key (string) - License key value
ays_license_active (boolean) - License validation status
ays_premium_features (array - serialized) - List of active features
ays_license_expiry (date) - Expiration date
```

---

## Admin License Settings Page

**Location:** Dashboard → Settings → License

**Fields:**
- License key input
- Activate/Deactivate button
- License status display
- Expiry date display
- Feature list display
- Upgrade button (CTA)

---

## Testing Strategy

### Free Version Tests
- [ ] All free features visible
- [ ] All premium features hidden
- [ ] CTAs display correctly
- [ ] No JavaScript errors
- [ ] No PHP errors
- [ ] Performance normal

### Premium Version Tests
- [ ] All premium features visible
- [ ] Free features still work
- [ ] License validation works
- [ ] Feature restrictions removed
- [ ] No performance degradation

### Upgrade Flow Tests
- [ ] Install free version
- [ ] Add license key
- [ ] Features unlock
- [ ] Remove license key
- [ ] Features lock
- [ ] No data loss

---

## Pricing Recommendation

### Free Tier
- Basic invoicing
- Lead capture
- Client dashboard (read-only)
- Basic reporting
- Up to 10 invoices/month

### Professional Tier ($29/month)
- Everything in Free
- Payment processing
- Recurring invoices
- Unlimited invoices
- Advanced reporting

### Enterprise Tier ($99/month)
- Everything in Professional
- Lead automation
- Client portal (editable)
- Email sequences
- Priority support

---

## Success Criteria

✅ Single codebase (no duplication)  
✅ Easy feature toggling  
✅ Professional upgrade experience  
✅ Zero confusion for users  
✅ Scalable to multiple tiers  
✅ Performance unaffected  
✅ Clean code patterns  
✅ Well documented  

---

## Next Steps

1. **Phase 10a:** Create License settings page in admin
2. **Phase 10b:** Integrate license checks into invoicing
3. **Phase 10c:** Integrate license checks into client portal
4. **Phase 10d:** Integrate license checks into leads
5. **Phase 10e:** Integration tests + regression testing
6. **Phase 11:** Release as beta with free/premium tiers

---

**Ready to implement!** Start with Phase 10a creating the admin settings page.
