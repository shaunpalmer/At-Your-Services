# At Your Service (Free Version)

A WordPress plugin for service businesses to manage leads, clients, invoicing, and notifications.

## Version & Requirements

- **Plugin Version:** `0.1.3`
- **WordPress:** `5.0+`
- **PHP:** `7.4+`
- **MySQL:** `5.6+` (or MariaDB equivalent)

## Core Features

- Lead capture and management
- Client management dashboard
- Invoice creation and tracking
- Payment recording and Stripe checkout integration
- Notification workflows and logging
- Configurable invoice company profile and footer settings

## Installation

### Option 1: WordPress Admin

1. Download the plugin ZIP from Releases.
2. In WordPress admin, go to **Plugins → Add New → Upload Plugin**.
3. Upload ZIP and click **Install Now**.
4. Activate the plugin.

### Option 2: Manual Upload

1. Extract plugin files.
2. Upload folder to `/wp-content/plugins/At-Your-Services/`.
3. Activate from **Plugins** in wp-admin.

### Option 3: Developer Setup

```bash
cd wp-content/plugins
git clone https://github.com/shaunpalmer/At-Your-Services.git atyourservice
wp plugin activate atyourservice
```

## Usage

1. Activate plugin.
2. Configure settings in the plugin dashboard.
3. Create/manage clients and invoices.
4. Use lead capture features on your site.

## Testing Notes

PHPUnit bootstrap expects a WordPress installation (`wp-load.php`) outside this repository, so tests must run inside a local WordPress environment.

## Contributing

1. Fork the repository.
2. Create a branch.
3. Commit your changes.
4. Open a Pull Request.

## License

GPL-2.0-or-later
