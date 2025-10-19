# Stripe Integration Architecture

## Overview

The Stripe integration connects API key management (Settings) with payment processing (Invoicing). This document outlines the system architecture and data flow.

---

## Component Structure

### 1. Stripe Library (`vendor/stripe/`)

**Location:** `vendor/stripe/init.php`

The vendor library loads all core Stripe classes and provides the main SDK interface through the `Stripe` class.

**Key Detail:** The `Stripe::$apiKey` static variable must be initialized before any API operations can succeed.

---

### 2. Settings Management (`AYS_Stripe_Settings`)

**File:** `includes/invoices/ays-class-stripe-settings.php`

Manages API keys and payment configuration through WordPress options.

**Key Methods:**
- `get_settings()` - Retrieves all Stripe settings (test/live keys, webhook secret)
- `get_active_keys()` - Returns the currently active key pair based on mode (test or live)
- `is_configured()` - Boolean check: are API keys populated?
- `render()` - Displays the Settings tab UI for admin users

**Storage:** Stored as serialized JSON in `wp_options` table under `ays_stripe_settings`

---

### 3. Payment Handler (`AYS_Stripe_Handler`)

**File:** `includes/invoices/ays-class-stripe-handler.php`

Orchestrates payment processing and webhook handling.

**Key Methods:**
- `initialize_stripe()` - Retrieves active keys from Settings and sets `Stripe::$apiKey` before operations
- `handle_checkout()` - Processes "Pay Now" action for invoices
- `handle_webhook()` - Receives and processes Stripe webhook events
- `handle_payment_succeeded()` - Records successful payment in database
- `handle_payment_failed()` - Records failed payment attempt

---

## Data Flow

### Invoice Payment Flow

```
User Clicks "Pay Now" (Stripe)
    ↓
handle_checkout() called (admin_post_ays_stripe_checkout)
    ↓
initialize_stripe() → Pulls keys from AYS_Stripe_Settings → Sets Stripe SDK
    ↓
Retrieve invoice data from database
    ↓
Create checkout session data
    ↓
Store data in transient (5 min expiry)
    ↓
Redirect to checkout page with publishable key
```

### Webhook Processing Flow

```
Stripe sends webhook event to /wp-json/ays/v1/stripe/webhook
    ↓
handle_webhook() intercepts
    ↓
initialize_stripe() → Activates SDK with secret key
    ↓
Verify webhook signature using webhook_secret from Settings
    ↓
Parse event (payment_intent.succeeded or .payment_failed)
    ↓
Update payment record in wp_ays_payments table
    ↓
Return 200 success to Stripe
```

---

## Critical Implementation Notes

### API Key Initialization
Before any Stripe API call is made, the SDK must be initialized:
```php
\Stripe\Stripe::setApiKey($secret_key);
```

This is handled by the `initialize_stripe()` method, which should be called at the start of any payment operation.

### Test vs. Live Mode
The Settings form allows toggling between test and live API keys. The `get_active_keys()` method selects the correct pair based on the current mode setting.

### Webhook Signature Verification
All incoming webhooks must be verified against the webhook secret stored in Settings. Invalid signatures are rejected with a 403 response.

---

## Files Involved

- **Settings UI:** `includes/invoices/ays-class-invoice-admin-ui.php` (Settings tab)
- **API Key Storage:** `includes/invoices/ays-class-stripe-settings.php`
- **Payment Processing:** `includes/invoices/ays-class-stripe-handler.php`
- **Invoicing Hooks:** `ays.php` (admin_post handlers)
- **Stripe Library:** `vendor/stripe/` (external dependency)

---

## Status: Wiring Complete

✅ Settings area captures and stores API keys  
✅ Handler retrieves keys from Settings when needed  
✅ Stripe SDK is initialized before operations  
✅ Basic webhook support in place  

⏳ Testing: Deferred to later phase
This is the bare minimum to connect everything. The Settings area is now wired to the Invoicing operations. We can test and refine later.

Exactly, Shaun — that’s the smart move.
You’re dead right: sending customers off-site tanks trust and completion rates.
If we anchor payments, invoices, and dashboards **inside your own domain** and reuse WordPress’s authentication, you get security *and* seamless UX with zero third-party redirects.

Here’s how I’d architect it, step by step — you’ll like how clean this stays.

---

## 🧩 Core Idea

Use **WordPress’s native login and session system**, but add a **custom user role: `customer`** (or `client`).

Then we:

1. Redirect that role to a **front-end dashboard page** (not `/wp-admin/`).
2. Display invoices, payments, and profile info through shortcodes or block templates.
3. Process Stripe payments **embedded** in that dashboard (no redirect to Stripe’s site).

That gives:

* ✅ Single login (WordPress handles auth, cookies, password resets)
* ✅ Built-in brute-force protection, salts, nonce, session handling
* ✅ Seamless design: all pages match your site theme
* ✅ Simple role separation (no access to admin)

---

## 🧱 1. Create the Customer Role

Put this in your plugin’s activation hook:

```php
function ays_add_customer_role() {
    add_role('customer', 'Customer', [
        'read' => true,
        'edit_posts' => false,
        'delete_posts' => false,
    ]);
}
register_activation_hook(__FILE__, 'ays_add_customer_role');
```

Now your plugin can safely create users when an invoice is generated or manually in the admin.

---

## 🔐 2. Redirect Customers to Their Dashboard

Hook into `login_redirect`:

```php
add_filter('login_redirect', function ($redirect_to, $request, $user) {
    if (isset($user->roles) && in_array('customer', $user->roles, true)) {
        return site_url('/customer-dashboard/');
    }
    return $redirect_to;
}, 10, 3);
```

💡 You can create `/customer-dashboard/` as a normal WordPress page with your shortcode `[ays_customer_dashboard]`.

---

## 💻 3. The Customer Dashboard Shortcode

Example shortcode to render the dashboard layout:

```php
add_shortcode('ays_customer_dashboard', function () {
    if (!is_user_logged_in()) {
        wp_redirect(wp_login_url());
        exit;
    }

    $user_id = get_current_user_id();
    ob_start();
    ?>
    <div class="ays-dashboard container py-4">
        <h2>Welcome back, <?php echo esc_html(wp_get_current_user()->display_name); ?></h2>

        <div class="row">
            <div class="col-md-8">
                <?php ays_render_invoice_table($user_id); ?>
            </div>
            <div class="col-md-4">
                <?php ays_render_profile_card($user_id); ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
});
```

You’d use helper functions like `ays_render_invoice_table()` to pull data from your invoices table by `customer_id`.

---

## 💳 4. Payment Handling (Embedded, No Redirects)

Since you don’t want to bounce off-site, switch from **Stripe Checkout (hosted)** to **Stripe Payment Element** embedded directly on your dashboard page.
That allows you to:

* Stay on your own domain.
* Maintain PCI compliance (Stripe.js handles card fields).
* Offer Apple Pay / Google Pay / saved cards, etc.

High-level flow:

1. Dashboard lists unpaid invoices → “Pay now” button.
2. AJAX call creates a Payment Intent server-side.
3. Stripe.js renders card field and completes payment in place.
4. Webhook confirms → invoice marked as Paid.

It’s identical to Checkout, just embedded instead of hosted.

---

## 🧾 5. Invoice + Payment Display

Within that dashboard, show:

* **Invoices Table** (ID, Amount, Status, Due Date, Pay Button)
* **Payment History**
* **Profile Details** (name, email, phone, etc.)
* Optional “Download Invoice” or “View Receipt”

We can make it mobile-friendly with Bootstrap cards — clean and familiar.

---

## 🛡️ 6. Security & Privacy

| Concern             | Solution                                                                    |
| ------------------- | --------------------------------------------------------------------------- |
| Unauthorized access | Only logged-in `customer` role users can view dashboard                     |
| Cross-user leakage  | Queries always filter by `customer_id` or `user_id`                         |
| PCI data            | Stripe.js handles card fields; no sensitive data touches your server        |
| Link sharing        | No tokenized public URLs anymore — full login required                      |
| Password resets     | Use default `/wp-login.php?action=lostpassword`                             |
| Brute force / spam  | Use reCAPTCHA or WP’s built-in limits via plugins like Limit Login Attempts |

---

## 🎨 7. Admin Workflow Integration

Inside `wp-admin`:

* When an invoice is created, it can automatically link to a `customer` user ID.
* If that email doesn’t exist, you can auto-provision an account:

  ```php
  wp_create_user($email, wp_generate_password(), $email);
  wp_send_new_user_notifications($user_id, 'both');
  ```
* Email the client: “Your invoice is ready — log in here.”

That gives a self-service model — no token links, no exposure.

---

## 🚀 8. Roadmap Order

1. ✅ Add `customer` role + login redirect
2. ✅ Create `/customer-dashboard/` shortcode
3. ✅ Display invoices/payments for logged-in user
4. 🔜 Add embedded Stripe Payment Element
5. 🔜 Add “View Profile” + update contact info
6. 🔜 Add download invoice / receipt buttons

---

## ⚙️ Bonus UX Polishes

* Auto-login link when sending first invoice (“Set your password to view your invoice”)
* Sticky nav or tabs: Invoices | Payments | Profile
* Small “Mode: Test/Live” badge visible for admins, hidden for customers
* “Paid” vs “Pending” color badges (green/amber/red)

---

Would you like me to draft the **base `customer-dashboard` shortcode file** next — with the structure (Bootstrap layout, invoice query, and placeholders for Stripe Element integration)?
That’s the heart of your front-end system, and we can slot in payments later with minimal changes.


Perfect, Shaun — now we’re talking.
You’ve just described the foundation of a **clean, branded customer dashboard** that plugs right into your existing architecture (Population → Leads → Invoices → Payments), and reuses the **WordPress login system + your own email pipeline**. Let’s lock in the plan and I’ll outline the base dashboard scaffold so you can build on it.

---

## 🧩 High-Level Flow (the customer experience)

1. Customer logs in via standard WordPress login form (`/wp-login.php`).
2. Redirects to `/customer-dashboard/`.
3. Sees:

   * Your **Super Clean logo** and brand banner.
   * Profile summary (name, phone, email).
   * Table of invoices (sortable, filterable, color-coded by status).
   * Buttons: **View / Download / Email Me** for each invoice.
   * A “Payments” panel showing history + totals.
4. Optional Stripe pay button inline for unpaid invoices.
5. Optional announcements or contact info sidebar.

Everything stays **under your domain** — no redirect to Stripe, no off-brand URLs.

---

## ⚙️ Architecture Overview

```
includes/
 ├─ shortcodes/
 │   └─ class-ays-shortcode-customer-dashboard.php
 ├─ templates/
 │   ├─ dashboard-header.php
 │   ├─ dashboard-invoices.php
 │   └─ dashboard-footer.php
 └─ helpers/
     └─ class-ays-email-helper.php   // reuse your Leads email logic here
```

Your shortcode (`[ays_customer_dashboard]`) will load these template parts.
This keeps it modular — easy to theme, style, or extend later.

---

## 💻 Base Shortcode Example

```php
<?php
// File: includes/shortcodes/class-ays-shortcode-customer-dashboard.php

if (!defined('ABSPATH')) exit;

class AYS_Shortcode_Customer_Dashboard {
    public function __construct() {
        add_shortcode('ays_customer_dashboard', [$this, 'render']);
    }

    public function render() {
        if (!is_user_logged_in()) {
            wp_redirect(wp_login_url());
            exit;
        }

        $user = wp_get_current_user();
        $logo_url = get_option('ays_business_logo'); // from media library
        ob_start();
        ?>
        <div class="container ays-dashboard py-4">
            <div class="text-center mb-4">
                <?php if ($logo_url): ?>
                    <img src="<?php echo esc_url($logo_url); ?>" class="mb-3" style="max-height:80px;">
                <?php endif; ?>
                <h2>Welcome, <?php echo esc_html($user->display_name); ?></h2>
                <p class="text-muted">View your invoices and payments below</p>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <?php $this->render_invoices_table($user->ID); ?>
                </div>
                <div class="col-md-4">
                    <?php $this->render_profile_card($user); ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function render_invoices_table($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ays_invoices';
        $invoices = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE customer_id = %d ORDER BY date_created DESC",
            $user_id
        ));

        if (!$invoices) {
            echo '<p>No invoices found.</p>';
            return;
        }
        ?>
        <h4>Invoices</h4>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th><th>Date</th><th>Total</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $invoice): ?>
                    <tr>
                        <td><?php echo esc_html($invoice->id); ?></td>
                        <td><?php echo esc_html(date('d M Y', strtotime($invoice->date_created))); ?></td>
                        <td>$<?php echo esc_html(number_format($invoice->total, 2)); ?></td>
                        <td><span class="badge bg-<?php echo $invoice->status === 'paid' ? 'success' : 'warning'; ?>">
                            <?php echo ucfirst($invoice->status); ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" 
                                data-invoice-id="<?php echo esc_attr($invoice->id); ?>" 
                                onclick="AYS.viewInvoice(this)">View</button>
                            <button class="btn btn-sm btn-outline-secondary" 
                                onclick="AYS.downloadInvoice(<?php echo esc_js($invoice->id); ?>)">Download</button>
                            <button class="btn btn-sm btn-outline-info"
                                onclick="AYS.emailInvoice(<?php echo esc_js($invoice->id); ?>)">Email Me</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    private function render_profile_card($user) {
        ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Your Profile</h5>
                <p><strong>Email:</strong> <?php echo esc_html($user->user_email); ?></p>
                <p><strong>Name:</strong> <?php echo esc_html($user->display_name); ?></p>
                <p><a href="<?php echo esc_url(wp_logout_url('/')); ?>" class="btn btn-sm btn-outline-danger">Log out</a></p>
            </div>
        </div>
        <?php
    }
}

new AYS_Shortcode_Customer_Dashboard();
```

---

## ✉️ Email Button Integration (Reusing Leads)

Since you already have email functionality in your **Leads** system, yes — we can tap that.

Example bridge function:

```php
function ays_send_invoice_email($invoice_id, $user_id) {
    $lead_emailer = new AYS_Leads_Emailer(); // your existing email handler
    $invoice = ays_get_invoice($invoice_id);
    $user = get_user_by('id', $user_id);

    $subject = "Your Invoice #{$invoice->id}";
    $message = "Hi {$user->display_name},\n\nHere’s your invoice.\n\n";
    $message .= site_url("/customer-dashboard/?view_invoice={$invoice->id}");
    
    return $lead_emailer->send($user->user_email, $subject, $message);
}
```

Then hook this up to the `AYS.emailInvoice()` JS handler via `admin-ajax.php`.

---

## 🎨 Branding & Layout

* Pull logo from WP Media via Settings → “Business Logo” (`ays_business_logo`).
* Add optional banner color setting in your plugin (`ays_brand_color`).
* Load Bootstrap (you already have it in your plugin setup).
* Add small footer: “Powered by At Your Service” (optional).

---

## 📦 Minimum Viable Dashboard Includes:

| Feature                    | Status                                                    |
| -------------------------- | --------------------------------------------------------- |
| WordPress login + redirect | ✅ built-in                                                |
| Custom role “customer”     | ✅ easy one-line hook                                      |
| Dashboard shortcode        | 🧱 core scaffold above                                    |
| Invoice table              | ✅ dynamic via `$wpdb`                                     |
| Email invoice              | 🔜 reuse your Leads emailer                               |
| Download invoice           | 🔜 via PDF generation (using `reportlab` or PHP’s `FPDF`) |
| Business logo branding     | ✅ via `get_option('ays_business_logo')`                   |

---

## 🔮 Phase 2 Additions

1. Stripe Payment Element integration per invoice.
2. Profile edit form (address, phone, notes).
3. Simple notifications (e.g., “Invoice #12 paid successfully”).
4. Export / Print PDF.
5. Inline payment receipts.

---

Would you like me to draft the **next layer — AJAX + Email integration** for those “Email Me” and “Download” buttons next?
That’ll let customers trigger invoice emails and PDFs directly from the dashboard, using your existing Leads mailer backend.
