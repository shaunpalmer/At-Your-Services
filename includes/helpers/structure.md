/ays/
├── admin/
│ └── enqueue.php # Handles admin-specific scripts and styles
├── includes/
│ ├── ays_blocks/
│ │ ├── ays-class-block-base.php # Base block class
│ │ ├── ays-class-block-faq.php # FAQ block
│ │ ├── ays-class-block-services.php # Service block
│ │ └── ays-class-block-team.php # Team block
│ ├── classes/
│ │ ├── Ays_Enqueue_Scripts.php # Enqueues plugin assets
│ │ ├── AysAdminLogger.php # Admin logger
│ │ ├── NamespaceValidator.php # Validates namespaces
│ │ ├── data-sanitization.php # Handles data sanitization
│ │ ├── ays_CustomPostTypeLoader.php # Loads custom post types
│ │ └── ays_taxonomiesloader.php # Loads taxonomies
│ ├── helpers/
│ │ ├── Admin_Notices.php # Handles admin notices
│ │ ├── AtYourServices.php # Helper functions for the plugin
│ │ ├── autoloader.php # Autoloader for classes
│ │ ├── functions.php # Generic helper functions
│ │ └── ays_constants.php # Plugin constants
| └── includes\core\ays_core.php
│ ├── posttypes/
│ │ ├── ays-cpt-service.php # Service custom post type
│ │ ├── ays-cpt-team.php # Team custom post type
│ │ ├── ays-cpt-faq.php # FAQ custom post type
│ │ ├── ays-cpt-review.php # Review custom post type
│ │ └── ays-cpt-location.php # Location custom post type
│ ├── settings/
│ │ └── settings.php # Plugin settings management
│ ├── shortcode/
│ │ ├── css/
│ │ │ └── ays-lead-landing.css # Shortcode-specific styles
│ │ ├── ays_shortcodes.php # Shortcode definitions
│ │ └── ays_service_lead.md # Documentation for lead generation shortcode
│ └── taxonomies/
│ ├── ays-taxonomy-service-type.php # Taxonomy for service types
│ └── ays-taxonomy-price-range.php # Taxonomy for price ranges
├── public/
│ ├── css/ # Public stylesheets
│ ├── js/
│ │ └── plugin-name-public.js # Public JavaScript
│ ├── Images/ # Public images
│ └── partials/
│ └── class-plugin-name-public.md # Public template documentation
├── templates/
│ └── form.php # Lead generation form template
├── src/
│ └── blocks/ # JavaScript source for Gutenberg blocks
├── assets/
│ ├── css/ # Plugin stylesheets
│ └── js/ # Plugin JavaScript files
├── node_modules/ # NPM dependencies
├── .gitignore # Git ignored files
├── ays.php # Main plugin file
├── README.md # Plugin documentation
