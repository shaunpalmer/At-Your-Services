# At Your Service WordPress Plugin

A comprehensive WordPress plugin designed specifically for running service-based businesses like **cleaning services, gardeners, plumbers, electricians, HVAC technicians, painters, contractors,** and more. This plugin provides everything you need to manage your service business including lead generation, job tracking, invoicing, custom post types, and more—all built on WordPress.

At Your Service is a flexible, feature-rich WordPress plugin specifically designed for service-based businesses. It offers lead generation forms, job management capabilities, invoicing tools, and custom post types for services, team members, FAQs, reviews, and locations. **Shortcode support** allows users to easily display professional lead generation forms. The plugin is built on modern PHP practices with WordPress coding standards, ensuring scalability and maintainability. The architecture is **extensible**, making it easy to customize. It is **mobile-optimized** and supports **internationalization** with built-in translation readiness.

## Minimum Requirements

**WordPress 5.0 or greater**
**PHP version 7.0 or greater**

At Your Service is the go-to solution for businesses looking to streamline operations and enhance customer management within WordPress.

## Contents

The At Your Service Plugin includes the following files and features:

- `.gitignore`: Files excluded from the repository.
- `README.md`: The document you're currently reading, detailing the plugin setup and usage.
- `CHANGELOG.md`: A log of changes and updates made to the plugin.
- `ays/` directory: The main directory containing the plugin's source code. This includes class definitions for custom post types and taxonomies, JavaScript and CSS assets, template files for forms, and additional components such as hooks and utilities that power the plugin's functionality.
- **Shortcodes for Non-Gutenberg Users**: We provide powerful shortcodes for integrating lead generation forms into any part of your site, optimized for responsive design. The landing page layout is crafted for high conversions and includes:

  - **Two-Column Responsive Layout**: A fully responsive two-column design, perfect for showcasing your business with a combination of visual content and lead capture forms.
  - **Dynamic Headline and Subheadline**: Easily customizable headline (H1) and subheadline (H2) sections that can dynamically display your city or service.
  - **Engaging Visual Content**: The left column includes space for images or videos that help convey your service's value visually, optimized for different screen sizes with a flexible image layout.
  - **Service Pitch Section**: A text section to deliver your pitch, limited to 6 lines with CSS's clamp feature to keep the message concise and impactful.
  - **Lead Capture Form**: The right column features a fully functional form, with inputs for name, email, phone, and checkboxes for selecting services . The form is styled for accessibility and ease of use.
  - **Call to Action**: Includes a prominent "Book In" button for easy user engagement. It also features a checkbox to confirm the user is "Not a Robot" and a fallback option to call your business directly if needed, with a dynamic phone number.

This shortcode is designed to be mobile-optimized, ensuring it looks great across all devices, whether on mobile or desktop. It's the perfect way to capture leads while presenting your service professionally.

## Features

### Business Management Tools

- **Lead Generation**: Capture and manage customer inquiries with professional shortcode-based forms, complete with service selection, contact fields, and responsive design
- **Job Management**: Organize and track jobs through various stages of your business process, from inquiry to completion
- **Invoicing System**: Create, send, and manage invoices with customer status tracking including payment history and account management
- **Custom Post Types**: Organize your business content effectively
  - **Services** - Showcase the services you offer with detailed descriptions and pricing
  - **Team Members** - Display your team, their expertise, and credentials
  - **FAQs** - Answer common customer questions to reduce support burden
  - **Reviews** - Display customer testimonials to build trust
  - **Locations** - Showcase your service areas and coverage zones
- **Taxonomies**: Powerful categorization system
  - **Service Types** - Categorize by service category (cleaning, plumbing, electrical, etc.)
  - **Price Ranges** - Organize services by pricing tiers
  - **Neighbourhoods** - Target specific geographic service areas

### Design & Technical

- **WordPress Native Design**: Styled with WordPress-native colors (#2271b1 admin blue) for a cohesive, professional appearance that matches the WordPress admin
- **Modern CSS Architecture**: Uses CSS variables (--ays-* namespace) for easy customization and theming
- **No Bootstrap Dependency**: Custom lightweight CSS for better performance and faster load times
- **Mobile-Optimized**: Fully responsive design ensures great UX across all devices (phone, tablet, desktop)
- **Internationalization Ready**: Built-in support for translations with proper i18n implementation
- **Developer Friendly**: Clean OOP architecture, proper escaping, sanitization, and WordPress coding standards
- **Extensible**: Easy to customize and extend through WordPress hooks and filters

### Professional Lead Capture

The included shortcode `[ays_service_lead]` creates a conversion-optimized lead form with:

- Responsive two-column layout perfect for landing pages
- Dynamic customizable headline and subheadline
- Visual content area for images or videos
- Service pitch section with concise messaging
- Full contact form (name, email, phone, service selection)
- Professional "Book In" call-to-action button
- Mobile-optimized for all screen sizes

## Premium Version

Looking for even more advanced features? The premium version includes:

- **Advanced CRM**: Comprehensive customer relationship management
- **Automated Email Notifications**: Customer and admin notifications
- **Online Booking System**: Let customers schedule services 24/7
- **Payment Integration**: Accept online payments directly
- **Business Analytics**: Detailed reporting and dashboards
- **Priority Support**: Get dedicated assistance when you need it
- **Advanced Workflow Automation**: Streamline your business processes

[Learn more about At Your Service Premium](https://project-studios.nz/atyourservice)

## Installation

### For Regular Users

1. **Upload via WordPress Dashboard**:
   - Download the plugin zip file.
   - Navigate to the 'Plugins' screen in your WordPress dashboard.
   - Click 'Add New', then 'Upload Plugin'.
   - Select the plugin zip file, upload it, and activate it.

2. **Begin Configuration**:
   - Go to the plugin settings to configure lead generation forms, job tracking, invoicing, and other features.

### For Developers

1. **Using Git and WP-CLI**:
   - Clone the repository to your WordPress plugins directory:
     ```bash
     git clone https://github.com/shaunpalmer/At-Your-Services wp-content/plugins/atyourservice
     ```
   - Navigate to the plugin folder and activate the plugin via WP-CLI:
     ```bash
     wp plugin activate atyourservice
     ```

2. **Using NPM and WP-Scripts**:
   - Install dependencies:
     ```bash
     npm install
     ```
   - Build the plugin using the official WordPress scripts:
     ```bash
     npm run build
     ```
   - For development mode:
     ```bash
     npm run start
     ```

## Task Automation with NPM

We use **npm scripts** for automating common tasks such as building the project, running linters, and managing git hooks. Below is a step-by-step guide for setting up automation for your project.

### 1. Initialize the Project

If you haven't initialized your project yet, start by running:

```bash
npm init -y
```

### 2. Set up Pre-commit Hooks

```bash
npx husky add .husky/pre-commit "npm run lint"
git add .husky/pre-commit
```

## Custom Post Types and Taxonomies

This plugin includes several **Custom Post Types** and **Taxonomies** to enhance the functionality of your WordPress site. Below is a list of the custom post types and taxonomies included, along with their purposes and locations within the project structure.

### Custom Post Types

| Post Type     | Description                                          | Class Name         | File Location                              |
| ------------- | ---------------------------------------------------- | ------------------ | ------------------------------------------ |
| **Services**  | Different kinds of services offered by the business. | `Ays_CPT_Service`  | `includes/post-types/ays-cpt-service.php`  |
| **Teams**     | Teams within the organization.                       | `Ays_CPT_Team`     | `includes/post-types/ays-cpt-team.php`     |
| **FAQs**      | Frequently Asked Questions related to services.      | `Ays_CPT_FAQ`      | `includes/post-types/ays-cpt-faq.php`      |
| **Reviews**   | Customer reviews and testimonials.                   | `Ays_CPT_Review`   | `includes/post-types/ays-cpt-review.php`   |
| **Locations** | Business or service locations.                       | `Ays_CPT_Location` | `includes/post-types/ays-cpt-location.php` |

### Taxonomies

| Taxonomy         | Description                                                          | Class Name                  | File Location                                       |
| ---------------- | -------------------------------------------------------------------- | --------------------------- | --------------------------------------------------- |
| **Service Type** | Categorizes services by type (e.g., Cleaning, Plumbing, etc.).       | `Ays_Taxonomy_Service_Type` | `includes/taxonomies/ays-taxonomy-service-type.php` |
| **Price Range**  | Categorizes services based on pricing tiers (e.g., Budget, Premium). | `Ays_Taxonomy_Price_Range`  | `includes/taxonomies/ays-taxonomy-price-range.php`  |

### Project Structure

- **/includes**: Shared functionality between the admin and public-facing areas.
- **/admin**: Admin-specific features, including settings pages.
- **/public**: Public-facing features, including front-end form logic.
- **/assets**: Stylesheets, JavaScript, and other assets.
- **/templates**: Custom templates for lead generation forms and job management.

```bash
./ays/
├── includes/
│   ├── post-types/
│   │   ├── ays-cpt-service.php      # Custom post type for services
│   │   ├── ays-cpt-team.php         # Custom post type for teams
│   │   ├── ays-cpt-faq.php          # Custom post type for FAQs
│   │   ├── ays-cpt-review.php       # Custom post type for reviews
│   │   └── ays-cpt-location.php     # Custom post type for locations
│   └── taxonomies/
│       ├── ays-taxonomy-service-type.php  # Taxonomy for service types
│       └── ays-taxonomy-price-range.php   # Taxonomy for price ranges
├── assets/
│   ├── css/                          # Stylesheets
│   └── js/                           # JavaScript files
├── templates/
│   └── form.php                      # Template for lead generation form
├── ays.php                           # Main plugin file
```

## Customization

To tailor the plugin to your business needs:

- Adjust the custom post types and taxonomies in the **services** directory.
- Customize pricing models for services like **Service Professionals** or similar service-related classes.
- Modify form layouts and styles by editing the form templates located in **/templates/form.php**.

## WordPress.org Preparation

Project Studios is planning to submit the plugin to WordPress.org. Please ensure the repository adheres to [WordPress plugin directory guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/).

## Recommended Tools

- **Poedit**: For managing translation files.
- **WP CLI**: For plugin activation, setup, and management from the command line.
- **WP scripts**: For task automation such as minifying CSS/JS or linting.
- **Npm init automation**: For additional task automation if needed.

## License

The At Your Service Plugin is licensed under **GPL v2 or later**.

## Future Roadmap

Planned features for upcoming versions:

- **Advanced CRM Integration**: With support for multiple customer types and sales funnel tracking.
- **Subscription Services**: Handling recurring services and automatic invoicing.
- **Franchise Management**: For businesses managing multiple locations or franchises.

## Credits

At Your Service is developed and maintained by **Shaun Palmer** of **Project Studios Web Agency**, evolving with input from the service-based business community.