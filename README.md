# At Your Service WordPress Plugin

A feature-rich, scalable, and customizable plugin designed specifically for service-based businesses like Services Pro cleaning, gardeners, plumbers, and similar industries. This plugin offers lead generation, job management, invoicing, and much more, all built on top of WordPress's flexible architecture.

## Contents

The At Your Service Plugin includes the following files and features:

- `.gitignore`: Files excluded from the repository.
- `README.md`: The document you're currently reading, detailing the plugin setup and usage.
- `CHANGELOG.md`: A log of changes and updates made to the plugin.
- `atyourservice` directory: The main directory containing all the plugin's source code, including classes, templates, and assets.

## Features

- **Lead Generation**: Collect and manage leads with a built-in cost calculator, quote generator, and Gutenberg service blocks.
- **Shortcodes for Non-Gutenberg Users**: For users who prefer not to use Gutenberg, shortcodes are available for integrating lead generation forms directly into posts and pages.
- **Job Management**: Organize and track jobs through various stages of the business process.
- **Invoicing System**: Create, send, and manage invoices, complete with customer status tracking like credit, bad debt, and payment history.
- **OOP-Based Architecture**: Leverages modern PHP practices like singletons and the State pattern to ensure maintainability and scalability.
- **Custom Post Types**: Use multiple custom post types like 'services' to organize data for service businesses.
- **Live Pricing Calculations**: Dynamic service pricing based on user inputs, designed to provide instant quotes.
- **Extensible Architecture**: Easy to add new features and blocks via a well-organized structure that keeps logic and presentation separate.
- **Internationalization Ready**: Built-in support for translations with a `.pot` file included.
- **Mobile-Optimized**: Focused on performance for mobile users, ensuring the plugin is SEO-friendly.

## Installation

To install the At Your Service Plugin:

1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Begin configuring lead generation forms, job tracking, and other features via the plugin's settings.

## Customization

To tailor the plugin to your business needs:

- Adjust the custom post types and taxonomies in the `services` directory.
- Customize pricing models for services like CarpetCleaning or similar service-related classes.
- Modify form layouts and styles by editing the form templates located in `/templates/form.php`.

## WordPress.org Preparation

Project Studios is planning to submit the plugin to WordPress.org.

## Recommended Tools

- **Poedit**: For managing translation files.
- **WP CLI**: For plugin activation and setup from the command line.
- **WP scripts**: For task automation such as minifying CSS/JS or linting.

## License

The At Your Service Plugin is licensed under GPL v2 or later.

## Important Notes

### Structure

- `/includes`: Shared functionality between the admin and public-facing areas.
- `/admin`: Admin-specific features, including settings pages.
- `/public`: Public-facing features, including front-end form logic.

### Extending

This plugin is designed to be extensible, allowing for easy integration of additional features like customer relationship management (CRM), quoting tools, and more—similar to WooCommerce for service-based businesses.

## Future Roadmap

Planned features for upcoming versions:

- **Advanced CRM Integration**: With support for multiple customer types and sales funnel tracking.
- **Subscription Services**: Handling recurring services and automatic invoicing.
- **Franchise Management**: For businesses managing multiple locations or franchises.

## Credits

At Your Service is developed and maintained by Shaun Palmer of Project Studios Web Agency, evolving with input from the service-based business community.



## Contents

The At Your Service Plugin includes the following files and features:

- **.gitignore**: Files excluded from the repository.
- **README.md**: The document you're currently reading, detailing the plugin setup and usage.
- **CHANGELOG.md**: A log of changes and updates made to the plugin.
- **atyourservice directory**: The main directory containing all the plugin's source code, including classes, templates, and assets.

## Features

- **Lead Generation**: Collect and manage leads with a built-in cost calculator, quote generator, and Gutenberg service blocks.
- **Shortcodes for Non-Gutenberg Users**: Shortcodes are available for integrating lead generation forms into posts and pages.
- **Job Management**: Organize and track jobs through various stages of the business process.
- **Invoicing System**: Create, send, and manage invoices, with customer status tracking like credit, bad debt, and payment history.
- **OOP-Based Architecture**: Leverages modern PHP practices like singletons and the State pattern to ensure maintainability and scalability.
- **Custom Post Types**: Use multiple custom post types like 'services' to organize data for service businesses.
- **Live Pricing Calculations**: Dynamic service pricing based on user inputs, designed to provide instant quotes.
- **Extensible Architecture**: Easy to add new features and blocks via a well-organized structure that keeps logic and presentation separate.
- **Internationalization Ready**: Built-in support for translations with a .pot file included.
- **Mobile-Optimized**: Focused on performance for mobile users, ensuring the plugin is SEO-friendly.

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
      git clone https://github.com/your-repo/atyourservice.git wp-content/plugins/atyourservice
      ```
    - Navigate to the plugin folder and activate the plugin via WP-CLI:
      ```bash
      wp plugin activate atyourservice
      ```
      developers copy to your command line do the normal things
      ```
       https://github.com/shaunpalmer/At-Your-Services
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

## Customization

To tailor the plugin to your business needs:

- Adjust the custom post types and taxonomies in the **services** directory.
- Customize pricing models for services like **CarpetCleaning** or similar service-related classes.
- Modify form layouts and styles by editing the form templates located in **/templates/form.php**.

## Structure

- **/includes**: Shared functionality between the admin and public-facing areas.
- **/admin**: Admin-specific features, including settings pages.
- **/public**: Public-facing features, including front-end form logic.
- **/assets**: Stylesheets, JavaScript, and other assets.
- **/templates**: Custom templates for lead generation forms and job management.

## WordPress.org Preparation

Project Studios is planning to submit the plugin to WordPress.org. Please ensure the repository adheres to [WordPress plugin directory guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/).

## Recommended Tools

- **Poedit**: For managing translation files.
- **WP CLI**: For plugin activation, setup, and management from the command line.
- **WP scripts**: For task automation such as minifying CSS/JS or linting.
- **Grunt or Gulp**: For additional task automation if needed.

## License

The At Your Service Plugin is licensed under **GPL v2 or later**.

## Future Roadmap

Planned features for upcoming versions:

- **Advanced CRM Integration**: With support for multiple customer types and sales funnel tracking.
- **Subscription Services**: Handling recurring services and automatic invoicing.
- **Franchise Management**: For businesses managing multiple locations or franchises.

## Credits

At Your Service is developed and maintained by **Shaun Palmer** of **Project Studios Web Agency**, evolving with input from the service-based business community.

