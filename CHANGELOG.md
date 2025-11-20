## Changelog

All notable changes to this project will be documented in this file.
The format loosely follows Keep a Changelog and versions aim for Semantic Versioning once stable.

### [Unreleased]
- Settings page stub (planned)
- Email notifications for leads (planned)
- Dynamic pricing calculator (planned)

Added
- Client Portal dashboard now tolerates legacy invoice schemas (missing `invoice_number`/`created_at`) and still renders invoice history.
- `seed-sample-data.php` provisions portal-ready WordPress accounts for each seeded client and links them automatically.
- Introduced `test-client-dashboard.php` CLI smoke test to quickly confirm Recent Invoices output after seeding.

### [1.3.0] - 2025-10-20
Added
- Client Area inside wp-admin for roles Customer/Client (Invoices, Payments stub) with capability gating.
- Dashboard cleanup for client roles and branded "At Your Service – Client Hub" widget.
- Client invoices table with quick totals and payment actions (Stripe when configured, Bank Transfer details).

Changed
- Greeting prefers AYS Client name matched by email; falls back to WP display name.

Deprecated
- Shortcode [ays_customer_dashboard]. It now displays a friendly notice pointing users to the secure Client Area in wp-admin.

### [0.1.4] - 2025-09-21
Added
- Lead CPT (`ays_lead`) for capturing front-end submissions.
- Secure `[ays_lead_form]` shortcode with nonce, sanitization, honeypot, legacy alias `[ays_service_form]`.
- Autoloader expanded to include taxonomies and new Lead CPT.
- Enqueue logic updated to load stylesheet only when shortcode present.
- Initial structured CHANGELOG.

Changed
- Removed manual `require_once` calls for taxonomy classes in `ays.php`.
- Simplified CPT & taxonomy instantiation loop.

### [0.1.3] - 2025-09-20
Changed
- Cleanup of main plugin file; removal of debug instantiation try/catch blocks.
- README overhaul.

### [0.1.2] - 2025-09-18
Added
- Initial shortcode scaffold and Bootstrap enqueue.

### [0.1.1] - 2025-09-17
Added
- Core CPTs: Service, Team, Review, Location, FAQ.
- Core taxonomies: Service Type, Price Range, Neighbourhood.

### [0.1.0] - 2025-09-16
Initial commit: Project scaffold, autoloader base, plugin bootstrap.

