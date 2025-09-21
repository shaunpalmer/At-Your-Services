## Changelog

All notable changes to this project will be documented in this file.
The format loosely follows Keep a Changelog and versions aim for Semantic Versioning once stable.

### [Unreleased]
- Settings page stub (planned)
- Email notifications for leads (planned)
- Dynamic pricing calculator (planned)

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

