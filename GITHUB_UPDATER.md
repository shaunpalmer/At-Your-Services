# GitHub Auto-Updater Implementation

## Overview

The **At Your Service** plugin now includes a professional GitHub-based auto-updater system that bypasses the WordPress.org review queue. This allows for faster deployment of updates directly from GitHub releases to installed WordPress sites.

## How It Works

The system uses the **Plugin Update Checker** library by Yahnis Elsts, a well-known and trusted solution for GitHub-based WordPress plugin updates.

### Architecture

1. **Singleton Pattern**: `AYS_GitHub_Update_Manager` ensures only one update check runs per page load to avoid GitHub API rate limiting.

2. **Automatic Checks**: WordPress checks for updates every 12 hours automatically.

3. **Manual Checks**: Administrators can force an immediate check via the admin interface.

4. **GitHub Releases**: Updates are distributed through GitHub Releases with automatic ZIP creation.

## Components

### 1. GitHub Update Manager Class
**Location**: `includes/helpers/class-ays-github-update-manager.php`

This singleton class manages all update operations:
- Initializes the Plugin Update Checker library
- Provides methods for manual update checks
- Tracks last check time
- Supports private repositories via authentication tokens

### 2. Admin Settings Page
**Location**: `admin/class-ays-update-settings.php`

Provides a user-friendly interface showing:
- Current plugin version
- Last sync time with GitHub
- Manual "Check for Updates Now" button
- Developer documentation

Access via: **Leads > Updates** in WordPress admin

### 3. GitHub Actions Workflow
**Location**: `.github/workflows/release.yml`

Automates the release process:
- Triggers on version tags (e.g., `v0.1.4`)
- Installs Composer dependencies
- Creates a clean ZIP file (excludes dev files)
- Publishes GitHub Release with the ZIP attached

### 4. Main Plugin Integration
**Location**: `ays.php` (lines 48-74)

Defines constants and initializes the updater on `plugins_loaded` hook.

## Usage

### For Administrators

1. Navigate to **Leads > Updates** in WordPress admin
2. View current version and last sync time
3. Click "Check for Updates Now" to force an immediate check
4. When updates are available, use the standard WordPress update interface

### For Developers

#### Releasing a New Version

Follow these steps to release an update:

```bash
# 1. Update the version in ays.php header
# Change: Version: 0.1.3
# To:     Version: 0.1.4

# 2. Commit your changes
git add .
git commit -m "Release version 0.1.4"

# 3. Create and push a version tag
git tag v0.1.4
git push origin main --tags
```

#### What Happens Automatically

1. GitHub Actions detects the new tag
2. Runs `composer install --no-dev --optimize-autoloader`
3. Creates `at-your-services.zip` excluding:
   - Git files and directories
   - Development files (tests, phpunit.xml)
   - Temporary and test scripts
   - Documentation markdown files
   - Editor configurations
4. Creates a GitHub Release with the ZIP attached
5. All WordPress sites with the plugin installed will see the update within 12 hours

#### Private Repository Support

If the repository is private, you can add authentication:

```php
// In a custom plugin or theme
add_action( 'plugins_loaded', function() {
    $manager = \AYS\Helpers\AYS_GitHub_Update_Manager::get_instance(
        AYS_GITHUB_REPO,
        AYS_PLUGIN_PATH . 'ays.php',
        AYS_PLUGIN_SLUG
    );
    
    $manager->set_auth_token( 'your-github-personal-access-token' );
}, 2 );
```

## Technical Details

### Dependencies

- **yahnis-elsts/plugin-update-checker**: ^5.0 (installed via Composer)
- Minimum PHP: 7.4

### Constants Defined

- `AYS_GITHUB_REPO`: GitHub repository URL
- `AYS_PLUGIN_SLUG`: Plugin slug for WordPress identification

### Update Check Process

1. Plugin Update Checker library queries GitHub API
2. Compares local version (from plugin header) with latest GitHub Release tag
3. If newer version found, registers it with WordPress
4. WordPress displays update notification
5. Standard WordPress update mechanism handles download and installation

### File Structure

```
at-your-services/
├── .github/
│   └── workflows/
│       └── release.yml              # Automated release workflow
├── admin/
│   └── class-ays-update-settings.php   # Admin UI
├── includes/
│   └── helpers/
│       └── class-ays-github-update-manager.php  # Core updater class
├── vendor/
│   └── yahnis-elsts/
│       └── plugin-update-checker/   # Library (included in repo)
├── ays.php                          # Main plugin file (integration)
└── composer.json                     # Dependency management
```

## Security Considerations

1. **Nonce Verification**: All update check requests use WordPress nonces
2. **Capability Checks**: Only users with `manage_options` can trigger updates
3. **HTTPS**: All GitHub API calls use HTTPS
4. **No Secrets in Code**: Authentication tokens should never be committed to the repository

## Troubleshooting

### Updates Not Showing

1. Check the "Updates" page to see last sync time
2. Click "Check for Updates Now"
3. Verify the GitHub Release exists and has a ZIP attached
4. Ensure the tag version matches the version in the ZIP's plugin header

### GitHub API Rate Limiting

- Unauthenticated requests: 60/hour
- Authenticated requests: 5,000/hour
- The plugin checks every 12 hours by default to stay well within limits

### Release ZIP Issues

The GitHub Actions workflow automatically:
- Installs production dependencies
- Excludes development files
- Creates a proper plugin ZIP structure

If manual ZIP creation is needed:
```bash
composer install --no-dev
zip -r at-your-services.zip . -x "*.git*" ".github/*" "tests/*" "*.md"
```

## Benefits

✅ **Faster Updates**: No WordPress.org review queue (3-5 days)
✅ **Control**: Full control over release timing
✅ **Automation**: One command releases to all installations
✅ **Professional**: Enterprise-grade update management
✅ **Reliable**: Battle-tested Plugin Update Checker library

## Resources

- Plugin Update Checker: https://github.com/YahnisElsts/plugin-update-checker
- GitHub Actions Documentation: https://docs.github.com/en/actions
- WordPress Plugin API: https://developer.wordpress.org/plugins/

## Maintenance

The update system requires minimal maintenance:

- **Regular**: Create version tags when releasing updates
- **As Needed**: Update GitHub token if repository becomes private
- **Monitor**: Check GitHub Actions for workflow success

## Future Enhancements

Potential additions:
- Update notification emails to administrators
- Rollback functionality
- Beta/alpha channel support
- Changelog display in admin interface
- Update success/failure logging
