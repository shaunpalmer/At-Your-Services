# At Your Service WordPress Plugin

A feature-rich, scalable, and customizable plugin designed for service-based businesses like **cleaning services, gardeners, plumbers,** and more. This plugin offers lead generation, job management, invoicing, and much more, all built on top of WordPress's flexible architecture.

<<<<<<< Updated upstream
<<<<<<< Updated upstream
At Your Service is a flexible WordPress plugin specifically designed for service-based businesses. It offers lead generation, job management, invoicing, and custom post types. **Gutenberg service blocks** and **shortcode support** allow users to easily create and manage forms. The plugin is built on modern PHP practices, ensuring scalability and maintainability. It includes **dynamic pricing calculations**, ideal for providing instant quotes. The architecture is **extensible**, allowing for seamless integration of additional features such as CRM and subscription services. It is **mobile-optimized** and supports **internationalization** with built-in translation files.

# **Minimum Requirements**

At Your Service is a flexible WordPress plugin specifically designed for service-based businesses. It offers lead generation, job management, invoicing, and custom post types. **Gutenberg service blocks** and **shortcode support** allow users to easily create and manage forms. The plugin is built on modern PHP practices, ensuring scalability and maintainability. It includes **dynamic pricing calculations**, ideal for providing instant quotes. The architecture is **extensible**, allowing for seamless integration of additional features such as CRM and subscription services. It is **mobile-optimized** and supports **internationalization** with built-in translation files.

**Minimum Requirements**

> > > > > > > Stashed changes
=======
At Your Service is a flexible WordPress plugin specifically designed for service-based businesses. It offers lead generation, job management, invoicing, and custom post types. **Gutenberg service blocks** and **shortcode support** allow users to easily create and manage forms. The plugin is built on modern PHP practices, ensuring scalability and maintainability. It includes **dynamic pricing calculations**, ideal for providing instant quotes. The architecture is **extensible**, allowing for seamless integration of additional features such as CRM and subscription services. It is **mobile-optimized** and supports **internationalization** with built-in translation files.

**Minimum Requirements**
>>>>>>> Stashed changes

**WordPress 5.0 or greater**
**PHP version 7.0 or greater**

At Your Service is the go-to solution for businesses looking to streamline operations and enhance customer management within WordPress.

## Contents

The At Your Service Plugin includes the following files and features:

- `.gitignore`: Files excluded from the repository.
- `README.md`: The document you're currently reading, detailing the plugin setup and usage.
- `CHANGELOG.md`: A log of changes and updates made to the plugin.
- `ays/` directory: The main directory containing the plugin's source code. This includes class definitions for custom post types and taxonomies, JavaScript and CSS assets, template files for forms, and additional components such as hooks and utilities that power the plugin’s functionality.
- **Shortcodes for Non-Gutenberg Users**: We provide powerful shortcodes for integrating lead generation forms into any part of your site, optimized for responsive design. The landing page layout is crafted for high conversions and includes:

  - **Two-Column Responsive Layout**: A fully responsive two-column design, perfect for showcasing your business with a combination of visual content and lead capture forms.
  - **Dynamic Headline and Subheadline**: Easily customizable headline (H1) and subheadline (H2) sections that can dynamically display your city or service.
  - **Engaging Visual Content**: The left column includes space for images or videos that help convey your service’s value visually, optimized for different screen sizes with a flexible image layout.
  - **Service Pitch Section**: A text section to deliver your pitch, limited to 6 lines with CSS's clamp feature to keep the message concise and impactful.
  - **Lead Capture Form**: The right column features a fully functional form, with inputs for name, email, phone, and checkboxes for selecting services . The form is styled for accessibility and ease of use.
  - **Call to Action**: Includes a prominent "Book In" button for easy user engagement. It also features a checkbox to confirm the user is "Not a Robot" and a fallback option to call your business directly if needed, with a dynamic phone number.
<<<<<<< Updated upstream
    <<<<<<< Updated upstream

This shortcode is designed to be mobile-optimized, ensuring it looks great across all devices, whether on mobile or desktop. It's the perfect way to capture leads while presenting your service professionally.

=======

This shortcode is designed to be mobile-optimized, ensuring it looks great across all devices, whether on mobile or desktop. It's the perfect way to capture leads while presenting your service professionally.

> > > > > > > changes
=======

This shortcode is designed to be mobile-optimized, ensuring it looks great across all devices, whether on mobile or desktop. It's the perfect way to capture leads while presenting your service professionally.
>>>>>>> Stashed changes

## Features

- **Lead Generation**: Collect and manage leads with a built-in cost calculator, quote generator, and Gutenberg service blocks.
- **Shortcodes for Non-Gutenberg Users**: Shortcodes are available for integrating lead generation forms into landing page.
- **Job Management**: Organize and track jobs through various stages of the business process.
- **Invoicing System**: Create, send, and manage invoices, with customer status tracking like credit, bad debt, and payment history.
- **OOP-Based Architecture**: Leverages modern PHP practices like singletons and the State pattern to ensure maintainability and scalability.
- **Custom Post Types**: Use multiple custom post types like 'services' to organize data for service businesses.
- **Live Pricing Calculations**: Dynamic service pricing based on user inputs, designed to provide instant quotes.
- **Extensible Architecture**: Easy to add new features and blocks via a well-organized structure that keeps logic and presentation separate.
- **Internationalization Ready**: Built-in support for translations with a `.pot` file included.
- **Mobile-Optimized**: Focused on performance for mobile users, ensuring the plugin is SEO-friendly.

##Installation Steps for Regular Users:

Upload via WordPress Dashboard:
Download the plugin ZIP file from the source (e.g., WordPress.org or your preferred marketplace).
Unzip the downloaded file. You’ll find a folder named “At Your Service.”
Log in to your WordPress admin dashboard.
Navigate to the “Plugins” section.
Click on “Add New” and then select “Upload Plugin.”
Choose the unzipped “At Your Service” folder and upload it.
Activate the plugin through the ‘Plugins’ menu in WordPress.
Begin Configuration:
After activation, go to the plugin settings. You’ll likely find a new menu item related to “At Your Service.”
Configure the plugin settings according to your requirements. This might include setting up lead generation forms, job tracking, invoicing, and other features.

3. **Begin Configuration**:
   - Go to the plugin settings to configure lead generation forms, job tracking, invoicing, and other features.
=======
### For Developers

1. **Using Git and WP-CLI**:
<<<<<<< Updated upstream
   <<<<<<< Updated upstream - Clone the repository to your WordPress plugins directory:
   `bash
git clone https://github.com/shaunpalmer/At-Your-Services wp-content/plugins/atyourservice
` - Navigate to the plugin folder and activate the plugin via WP-CLI:
   `bash
wp plugin activate atyourservice
`

## Task Automation with NPM

We use **npm scripts** for automating common tasks such as building the project, running linters, and managing git hooks. Below is a step-by-step guide for setting up automation for your project. 2. **Using NPM and WP-Scripts**: - Install dependencies:
`##bash
      npm install
     ` - Build the plugin using the official WordPress scripts:
`bash
      npm run build  
      ` - For development mode:
`bash
      npm run start
      `
`   npx husky add .husky/pre-commit "npm run lint"
      git add .husky/pre-commit
  `
=======

- Clone the repository to your WordPress plugins directory:
  ```bash
  git clone https://github.com/shaunpalmer/At-Your-Services wp-content/plugins/atyourservice
  ```
- Navigate to the plugin folder and activate the plugin via WP-CLI:
  ```bash
  wp plugin activate atyourservice
  ```

## Task Automation with NPM

We use **npm scripts** for automating common tasks such as building the project, running linters, and managing git hooks. Below is a step-by-step guide for setting up automation for your project. 2. **Using NPM and WP-Scripts**: - Install dependencies:
`##bash
      npm install
     ` - Build the plugin using the official WordPress scripts:
`bash
      npm run build  
      ` - For development mode:
`bash
      npm run start
      `
`  npx husky add .husky/pre-commit "npm run lint"
      git add .husky/pre-commit
 `

> > > > > > > Stashed changes
=======
   - Clone the repository to your WordPress plugins directory:
     ```bash
     git clone https://github.com/shaunpalmer/At-Your-Services wp-content/plugins/atyourservice
     ```
   - Navigate to the plugin folder and activate the plugin via WP-CLI:
     ```bash
     wp plugin activate atyourservice
     ```

## Task Automation with NPM

We use **npm scripts** for automating common tasks such as building the project, running linters, and managing git hooks. Below is a step-by-step guide for setting up automation for your project.
>>>>>>> Stashed changes

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

     ```
     npx husky add .husky/pre-commit "npm run lint"
     git add .husky/pre-commit
     ```

     # NPM Automation and Build Setup

To streamline the development process, we’ve incorporated several tools using **NPM** for task automation, linting, and building packages. This section explains how to set up and use these tools in your project.

<<<<<<< Updated upstream
<<<<<<< Updated upstream

### 1. Initialize the Project

=======

### 1. Initialize the Project

> > > > > > > Stashed changes
> > > > > > > If you haven’t initialized your project yet, start by running:
=======
### 1. Initialize the Project

If you haven’t initialized your project yet, start by running:
>>>>>>> Stashed changes

```bash
npm init -y
```

## Custom Post Types and Taxonomies

This plugin includes several **Custom Post Types** and **Taxonomies** to enhance the functionality of your WordPress site. Below is a list of the custom post types and taxonomies included, along with their purposes and locations within the project structure.

### Custom Post Types
<<<<<<< Updated upstream

<<<<<<< Updated upstream
| Post Type | Description | Class Name | File Location |
| ------------ | --------------------------------------------------- | ------------------ | -------------------------------------------- |
| **Services** | Different kinds of services offered by the business. | `Ays_CPT_Service` | `includes/post-types/ays-cpt-service.php` |
| **Teams** | Teams within the organization. | `Ays_CPT_Team` | `includes/post-types/ays-cpt-team.php` |
| **FAQs** | Frequently Asked Questions related to services. | `Ays_CPT_FAQ` | `includes/post-types/ays-cpt-faq.php` |
| **Reviews** | Customer reviews and testimonials. | `Ays_CPT_Review` | `includes/post-types/ays-cpt-review.php` |
| **Locations**| Business or service locations. | `Ays_CPT_Location` | `includes/post-types/ays-cpt-location.php` |

### Taxonomies

| Taxonomy         | Description                                                          | Class Name                  | File Location                                       |
| ---------------- | -------------------------------------------------------------------- | --------------------------- | --------------------------------------------------- |
| **Service Type** | Categorizes services by type (e.g., Cleaning, Plumbing, etc.).       | `Ays_Taxonomy_Service_Type` | `includes/taxonomies/ays-taxonomy-service-type.php` |
| **Price Range**  | Categorizes services based on pricing tiers (e.g., Budget, Premium). | `Ays_Taxonomy_Price_Range`  | `includes/taxonomies/ays-taxonomy-price-range.php`  |

=======
=======
>>>>>>> Stashed changes

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
<<<<<<< Updated upstream

> > > > > > > Stashed changes
=======
>>>>>>> Stashed changes

### Project Structure

- **/includes**: Shared functionality between the admin and public-facing areas.
- **/admin**: Admin-specific features, including settings pages.
- **/public**: Public-facing features, including front-end form logic.
- **/assets**: Stylesheets, JavaScript, and other assets.
- **/templates**: Custom templates for lead generation forms and job management.
<<<<<<< Updated upstream
  <<<<<<< Updated upstream
  =======

> > > > > > > changes
=======
>>>>>>> Stashed changes

```bash
/ays/
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

<<<<<<< Updated upstream
# <<<<<<< Updated upstream

> > > > > > > Stashed changes

=======
>>>>>>> Stashed changes
## Customization

To tailor the plugin to your business needs:

- Adjust the custom post types and taxonomies in the **services** directory.
- Customize pricing models for services like **Service Professionals** or similar service-related classes.
- Modify form layouts and styles by editing the form templates located in **/templates/form.php**.

<<<<<<< Updated upstream
<<<<<<< Updated upstream

=======

> > > > > > >

=======
>>>>>>> Stashed changes
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
<<<<<<< Updated upstream
<<<<<<< Updated upstream

=======

> > > > > > >
=======
>>>>>>> Stashed changes
