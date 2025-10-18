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