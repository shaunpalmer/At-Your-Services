# Changelog

All notable changes to the At Your Service plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.3] - 2024-08-12

### Fixed
- Fixed PHP syntax errors in index.php, templates/template.php, and public/partials/plugin-name-public-display.php
- Resolved Git merge conflicts in README.md
- Cleaned up debug code and test instantiation from main plugin file
- Removed excessive error logging from autoloader
- Fixed version inconsistency between plugin header and constant

### Changed
- Updated plugin version to 1.2.3 for consistency
- Cleaned up main plugin file (ays.php) by removing commented code
- Improved autoloader performance by removing debug logging
- Restructured README.md for better readability

### Added
- Added package.json for npm build process and scripts
- Added phpcs.xml for PHP coding standards compliance
- Added .eslintrc.json for JavaScript linting
- Added .editorconfig for consistent code formatting
- Added proper licensing and coding standards setup

### Removed
- Removed debug/test code from production files
- Removed excessive Captain Kirk/Star Trek comments
- Removed duplicate file includes
- Removed hard-coded development paths