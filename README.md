# At Your Service (WordPress Plugin)

At Your Service is a WordPress plugin for service businesses to manage leads, clients, invoices, and payments from the WordPress admin.

## Core Features

- Lead capture shortcode with nonce protection and anti-spam honeypot
- Notification system for admin alerts and customer auto-replies
- Invoicing dashboard (clients, items, invoices, payments, reports)
- Client-area capability and role setup
- Stripe-ready payments flow (with graceful fallback when SDK is unavailable)
- Premium feature gating and license admin page

## Requirements

- WordPress 5.0+
- PHP 7.4+
- MySQL 5.6+ (or MariaDB equivalent)

## Installation

1. Copy the plugin folder to `wp-content/plugins/At-Your-Services`.
2. Activate **At Your Service** in WordPress Admin → Plugins.
3. Open **At Your Services → Dashboard** to configure and use features.

## Shortcodes

- `[ays_lead_form]` — Public lead capture form
- `[ays_service_form]` — Legacy alias to `ays_lead_form`
- `[ays_customer_dashboard]` — Deprecated (users are redirected to the secure client area in wp-admin)

## Development Notes

- Main entrypoint: `ays.php`
- Invoicing module: `includes/invoices/`
- Notifications: `includes/notifications/`
- Admin menu/dashboard: `includes/admin/`
- Shortcodes: `includes/shortcode/`

## Testing & Validation

- PHPUnit config exists (`phpunit.xml`) but requires a local WordPress environment with `wp-load.php` available.
- Basic syntax validation can be run with `php -l` across plugin PHP files.

## License

GPL-2.0-or-later
