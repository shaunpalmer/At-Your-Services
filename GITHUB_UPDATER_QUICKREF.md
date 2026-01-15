# GitHub Auto-Updater - Quick Reference

## 🎯 What Was Implemented

A complete GitHub-based auto-updater system using the **Plugin Update Checker** library by Yahnis Elsts.

## 📁 Files Created/Modified

### New Files
- `includes/helpers/class-ays-github-update-manager.php` - Singleton manager class
- `admin/class-ays-update-settings.php` - Admin UI for update management
- `.github/workflows/release.yml` - Automated release workflow
- `GITHUB_UPDATER.md` - Complete documentation
- `GITHUB_UPDATER_QUICKREF.md` - This file

### Modified Files
- `ays.php` - Integrated updater initialization
- `composer.json` - Added plugin-update-checker dependency
- `.gitignore` - Configured to include vendor directory
- `README.md` - Added auto-updater section

### Library Installed
- `vendor/yahnis-elsts/plugin-update-checker/` - v5.6 (committed to repo)

## 🚀 Quick Start for Developers

### Release a New Version (3 Commands)

```bash
# 1. Update version in ays.php header (line 8)
# Change: Version: 0.1.3
# To:     Version: 0.1.4

# 2. Commit and create tag
git add ays.php
git commit -m "Release version 0.1.4"
git tag v0.1.4

# 3. Push everything
git push origin main --tags
```

**What happens automatically:**
1. GitHub Actions detects the tag
2. Installs dependencies (`composer install`)
3. Creates ZIP file (excludes dev files)
4. Publishes GitHub Release with ZIP
5. All WordPress sites see update within 12 hours

## 🎛️ Admin Interface

**Location:** WordPress Admin → Leads → Updates

**Features:**
- View current plugin version
- See last GitHub sync time
- Force immediate update check
- Developer instructions

## 🔧 How It Works

1. **Plugin Update Checker Library** queries GitHub API every 12 hours
2. Compares local version with latest GitHub Release tag
3. If newer version exists, WordPress shows "Update Available"
4. Standard WordPress update mechanism handles installation

## 📦 What Gets Included in Releases

**Included:**
- All PHP files
- Vendor directory (with plugin-update-checker)
- Assets (CSS, JS, images)
- Templates

**Excluded (by workflow):**
- .git directory and files
- .github directory
- tests directory
- Development scripts (test-*.php, manual-*.php, etc.)
- Documentation (.md files)
- Editor configs

## 🔐 Private Repository Support

If the repository becomes private, add authentication:

```php
// In functions.php or custom plugin
add_action( 'plugins_loaded', function() {
    $manager = \AYS\Helpers\AYS_GitHub_Update_Manager::get_instance(
        AYS_GITHUB_REPO,
        AYS_PLUGIN_PATH . 'ays.php',
        AYS_PLUGIN_SLUG
    );
    $manager->set_auth_token( 'ghp_YourPersonalAccessToken' );
}, 2 );
```

## 🐛 Troubleshooting

### Updates Not Appearing
1. Go to **Leads > Updates** in WordPress admin
2. Click "Check for Updates Now"
3. Verify GitHub Release exists with proper tag
4. Check that tag matches version in plugin header

### GitHub Actions Failed
1. Check Actions tab in GitHub repository
2. Common issues:
   - Composer install failed (check composer.json)
   - ZIP creation failed (check file permissions)
   - Release creation failed (check GITHUB_TOKEN permissions)

### Rate Limiting
- Plugin checks every 12 hours (well within limits)
- Unauthenticated: 60 requests/hour
- With token: 5,000 requests/hour

## 📊 Architecture

```
WordPress Plugin (At Your Service)
    ↓
AYS_GitHub_Update_Manager (Singleton)
    ↓
Plugin Update Checker Library (yahnis-elsts)
    ↓
GitHub API
    ↓
GitHub Releases
```

## ✅ Benefits

- ⚡ **Fast Updates**: No WordPress.org review queue
- 🎮 **Full Control**: Release on your schedule
- 🤖 **Automated**: One command deploys everywhere
- 🛡️ **Reliable**: Battle-tested library used by thousands
- 📈 **Professional**: Enterprise-grade solution

## 📚 Additional Documentation

- Full guide: `GITHUB_UPDATER.md`
- Plugin Update Checker: https://github.com/YahnisElsts/plugin-update-checker
- GitHub Actions: https://docs.github.com/en/actions

## 🎓 Key Concepts

**Singleton Pattern**: Ensures only one update check per page load to avoid rate limiting

**GitHub Releases**: Distributes updates via GitHub's release system with ZIP files

**Composer**: Manages the Plugin Update Checker library dependency

**GitHub Actions**: Automates the release process on tag push

---

**Implementation Date:** January 15, 2026  
**Version:** 0.1.3 (ready for 0.1.4 release)  
**Status:** ✅ Complete and ready to use
