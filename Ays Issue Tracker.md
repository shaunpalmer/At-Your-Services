# AYS Issue Tracker

Daily Backup and Experiment Branch Strategy

1. Establish a Backup Routine
   Daily Git Commit: At the end of each day, commit all working code to the main or dev branch.
   Use clear commit messages summarizing the day's progress (e.g., chore: daily backup - fixed AYS path adapter, documented namespace conflicts).
   Backup to Remote: Push these changes to a remote repository (GitHub, Bitbucket, etc.).
   You could use automation (e.g., Git hooks or a cron job) to push backups daily.
2. Use Experiment Branches
   Create a Branch for Each Issue or Feature:
   Name branches descriptively (e.g., bugfix/activation-hooks, feature/ays-tracker-md-file).
   Work and Test on Branches:
   Keep experimental work isolated. Merge into main or dev only after testing and review.
3. Track Progress in a .md File
   Create a progress-log.md file in the repo:
   Daily Logs: Record:
   Branch name
   Goals or bugs addressed
   Progress notes (e.g., challenges, insights, unresolved issues)
   Completion status
   Use a consistent structure for clarity.

## Summary of Fixed Issues

This document lists the key issues encountered in the last few development cycles and their solutions. It serves as a reference to prevent recurring problems and streamline debugging.

---

| **Issue**                                 | **Details**                                                                    | **Solution**                                                                                 |
| ----------------------------------------- | ------------------------------------------------------------------------------ | -------------------------------------------------------------------------------------------- |
| Autoloader Issues                         | Namespace and path mismatches causing autoloader failures.                     | Ensured correct namespace declaration and adjusted paths in `ays_helpers_autoloader.php`.    |
| Bootstrapper Class Not Initialized        | `AYS_Bootstrapper` was not being properly initialized on the main plugin page. | Verified the initialization order and updated hook to ensure the class was properly invoked. |
| Undefined Variables and Methods           | Missing variables and methods causing runtime errors.                          | Added checks and ensured all required methods were defined and properly scoped.              |
| Hook Not Firing (`ays_plugin_initialize`) | Hook not being recognized due to incorrect scope or name.                      | Fixed hook declaration and ensured it was called in the appropriate context.                 |
| Missing Files in Repository               | Some critical files were missing or misplaced during repository sync.          | Reviewed file structure and restored missing files from backup.                              |
| Merge Conflicts on Branches               | Conflicts between `develop` and `master` branches.                             | Resolved conflicts manually and updated branches with clear commit messages.                 |
| Undefined Constants                       | Missing constants like `AYS_VERSION` causing errors in helper files.           | Defined constants in a central file and ensured consistent usage across the project.         |

---

## Additional Notes

- **Preventive Measures**: Regular backups and isolated development branches.
- **Next Steps**: Document future fixes and solutions in this file for easy tracking.

---

If you encounter more issues, update this table and add detailed resolutions. This will grow into a robust reference for troubleshooting and maintaining the project.

#

#

# Core Files Responsibilities and Roles

This document outlines the roles and responsibilities of each core file in the **At Your Service (AYS)** plugin. The goal is to ensure clarity, avoid duplication, and maintain single responsibility across the codebase. This list will evolve as we debug and refine the plugin.

---

## Core File Responsibilities

### File | Role | Current Status | Notes

- **AYS_Bootstrapper.php**  
  Role: Responsible for initialization and registration of all major components.  
  Status: Functional, requires periodic review.  
  Notes: Handles autoloader registration, error handler initialization, and settings.

- **ays_class_path_adapter.php**  
  Role: Ensures paths are resolved correctly and reliably.  
  Status: Needs review; underperforms currently.  
  Notes: May require a rewrite or removal if redundant.

- **ays_core.php**  
  Role: Placeholder for core features like CPTs and taxonomies.  
  Status: Unclear role; limited implementation.  
  Notes: Should either be developed or removed if unnecessary.

- **ays_CoreLoader.php**  
  Role: Intended to load all core components and manage dependencies.  
  Status: Pending evaluation and development.  
  Notes: Needs clear scope to avoid duplicating bootstrapper tasks.

---

## Responsibilities Breakdown

### 1. **AYS_Bootstrapper.php**

**Purpose**: Centralized initialization of the plugin.  
**Responsibilities**:

- Register autoloader.
- Initialize error handler.
- Register settings and handle admin configurations.

**Checklist**:

- [ ]

---

### 2. **ays_class_path_adapter.php**

**Purpose**: Resolve and manage file paths across the plugin.  
**Responsibilities**:

- Provide a reliable mechanism for file path resolution.
- Prevent hardcoding of paths to enhance portability.

**Checklist**:

- [ ]

---

### 3. **ays_core.php**

**Purpose**: Placeholder for central features like Custom Post Types (CPTs).  
**Responsibilities**:

- Define and register CPTs and taxonomies.
- Integrate core functionalities for plugin expansion.

**Checklist**:

- [ ]

---

### 4. **ays_CoreLoader.php**

**Purpose**: Load core components and manage dependencies.  
**Responsibilities**:

- Serve as a manager for loading essential plugin files.
- Avoid duplicating bootstrapper tasks.

**Checklist**:

- [ ]

---

## Next Steps

### 1. Review and Refactor

- Go through each file and validate its role.
- Refactor for clarity and single responsibility.

### 2. Update Documentation

- Reflect changes and refinements in this document.

### 3. # Cascade Responsibilities and Relationships

---

## Table Overview

| **Component**           | **Primary Responsibility**                                                  | **Integration/Connection**                                      |
| ----------------------- | --------------------------------------------------------------------------- | --------------------------------------------------------------- |
| **AYS_Bootstrapper**    | Initialization and Registration (e.g., autoloader, error handler, settings) | Registers and initializes `AYS_ClassAutoloader`.                |
| **AYS_ClassAutoloader** | General-purpose class autoloading                                           | Handles general class autoloading for the plugin.               |
| **AYS_CoreLoader**      | Core namespace-specific class autoloading                                   | Focuses on resolving and loading classes in the core namespace. |

---

## Cascade Chain Visualization

**Cascade chain With Arrows**

```
AYS_Bootstrapper
      --> AYS_CoreLoader
      --> AYS_ClassAutoloader
      --> General-purpose class autoloading
      --> Handles plugin-wide class registration
      --> Core namespace-specific class autoloading
      --> Loads core plugin components (e.g., CPTs, Taxonomies)

```

## AYS_Bootstrapper:

Registers and initializes core components such as the autoloader and settings.
Explicitly calls and initializes the AYS_CoreLoader via initialize_core_loader.
AYS_CoreLoader:

Focuses on core namespace-specific class autoloading.
Dynamically resolves and includes core plugin classes.
Main Plugin File:

Initializes the plugin via the bootstrapper.
Includes additional files for admin settings, enqueueing assets, and shortcodes.

```

```

# Debugging and Refactoring Report for AYS Plugin

---

## Overview

The goal of this debugging session was to refine the cascade structure of the AYS plugin by ensuring clear responsibilities across the core files: **AYS_Bootstrapper**, **AYS_CoreLoader**, and **AYS_ClassAutoloader**. We also addressed errors and ensured the plugin initialized correctly within WordPress.

---

## Key Changes

### 1. **AYS_Bootstrapper**

**Purpose**: Orchestrates initialization and registration of core components.

**Changes**:

- Updated `initialize_core_loader()` to pass a valid configuration array (`$config`).
- Added validation to check if the core loader is correctly loaded and instantiated.
- Refactored `register_general_autoloader()` to ensure no duplication of responsibilities.

---

### 2. **AYS_CoreLoader**

**Purpose**: Responsible for loading core-specific functionality.

**Changes**:

- Updated `load()` method to validate the `$config` argument and ensure proper path handling.
- Added error logging for invalid configurations and mismatched namespaces.
- Integrated `spl_autoload_register` for static method registration.

---

### 3. **AYS_ClassAutoloader**

**Purpose**: General-purpose class autoloading across the plugin.

**Changes**:

- Validated its responsibility to ensure no overlap with **AYS_CoreLoader**.

---

## Issues and Fixes

### 1. Undefined Method: `AYS_CoreLoader::load()`

**Error**: "Too few arguments passed to AYS_CoreLoader::load()".

**Cause**: The `load()` method expected an argument but none was provided.

**Fix**:

- Passed `$config = ['path' => plugin_dir_path(__DIR__)]` as an argument.
- Updated the `load()` method in **AYS_CoreLoader** to handle the configuration properly.

---

### 2. Array to String Conversion Warning

**Error**: "Array to string conversion in AYS_CoreLoader::load()".

**Cause**: An array was passed to `strpos()` instead of a string.

**Fix**:

- Added validation to ensure `$config['path']` is a string.
- Logged errors for invalid configurations.

---

### 3. Namespace Mismatch

**Error**: "Invalid path or namespace mismatch."

**Cause**: Incorrect namespace or path validation.

**Fix**:

- Verified the namespace matches `ays\includes\core`.
- Ensured paths are correctly resolved using `plugin_dir_path(__DIR__)`.

---

## Testing

### Steps:

1. Activated the plugin in WordPress.
2. Monitored debug logs for warnings or errors.
3. Verified successful initialization by accessing the WordPress admin dashboard.

### Results:

- **Success**: No errors or warnings in debug logs.
- **Outcome**: Plugin initialized correctly, and the dashboard loaded successfully.

---

## Lessons Learned

1. **Validation is Key**: Adding checks for data types and configurations prevents runtime errors.
2. **Separation of Responsibilities**: Clearly defining each class's purpose avoids duplication and confusion.
3. **Error Logging**: Detailed logging helps identify and resolve issues efficiently.
