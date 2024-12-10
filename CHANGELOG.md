# Change Log for AYS Plugin

---

## Version 1.1.3 - [10/12/2024]

### Added

- **AYS_Bootstrapper**:
  - Introduced robust validation for core loader and autoloader components.
  - Implemented structured error logging for initialization failures.
- **AYS_CoreLoader**:
  - Added a `load()` method to dynamically resolve core paths based on configuration.
  - Introduced validation for paths and namespaces to avoid mismatches.
  - Integrated `spl_autoload_register` for core-specific autoloading.

### Changed

- Refactored `AYS_Bootstrapper::initialize_core_loader()` to pass a valid configuration array (`$config`).
- Enhanced **AYS_CoreLoader** with improved path validation and error handling.
- Updated **AYS_ClassAutoloader** to clarify its general-purpose autoloading responsibility.

### Fixed

- **Undefined Method Error**:
  - Resolved "Too few arguments passed to AYS_CoreLoader::load()" by ensuring required arguments are provided.
- **Array to String Conversion Warning**:
  - Fixed issue with improper use of `strpos()` by validating `$config['path']` as a string.
- **Namespace Mismatch**:
  - Corrected namespace handling to ensure compatibility with `ays\includes\core`.

### Removed

- Duplication of autoloader responsibilities in **AYS_Bootstrapper** and **AYS_CoreLoader**.

---

## Version 1.0.0 - Initial Release

- Core functionality established with basic autoloading and plugin initialization.
- Includes support for Custom Post Types, Taxonomies, and plugin-wide class registration.
