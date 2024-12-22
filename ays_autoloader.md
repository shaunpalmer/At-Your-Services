### Loaders Strategy Documentation

#### Overview

The `AYS_CoreLoader` originally served as the central mechanism to load all classes in the `ays\includes\core` namespace. However, to improve modularity, granularity, and debugging capabilities, additional loaders have been introduced to handle specific namespaces and their corresponding file structures.

---

#### **Loaders Overview**

1. **Core Loader**:

   - **File**: `/includes/core/AYS_CoreLoader.php`
   - **Namespace**: `ays\includes\core`
   - **Purpose**: Load all core functionality classes exclusively.

2. **Helpers Loader**:

   - **File**: `/includes/helpers/HelpersLoader.php`
   - **Namespace**: `ays\includes\helpers`
   - **Purpose**: Load utility and helper classes, providing auxiliary functionality for the plugin.

3. **Post Types Loader**:

   - **File**: `/includes/posttypes/PostTypesLoader.php`
   - **Namespace**: `ays\includes\posttypes`
   - **Purpose**: Handle all custom post type classes, including FAQs, Services, and Locations.

4. **Taxonomies Loader**:
   - **File**: `/includes/taxonomies/TaxonomiesLoader.php`
   - **Namespace**: `ays\includes\taxonomies`
   - **Purpose**: Load taxonomy-related classes, such as `Neighbourhood` and `Price Range`.

---

#### **Cascade Strategy**

1. **Initialization Order**:

   - Each loader is registered independently via `spl_autoload_register`.
   - Priority is based on namespace specificity (e.g., `core` first, followed by `helpers`).

2. **Logs and Debugging**:

   - Each loader logs missing files or invalid namespaces.
   - Granular logging ensures issues are traceable to a specific loader or namespace.

3. **Future Scalability**:
   - Loaders can be extended or modified without impacting others.
   - New namespaces (e.g., `admin`, `blocks`) can be integrated seamlessly.

---

#### **Rationale for Separation**

1. **Granular Debugging**:

   - Isolated loaders make it easier to pinpoint issues in specific namespaces.

2. **Modularity**:

   - Loaders focus solely on their assigned namespaces, adhering to single-responsibility principles.

3. **Preparation for User Control**:
   - Future settings pages can toggle features (e.g., enabling/disabling custom post types) by interacting with their respective loaders.

---

#### **File Structure**

```
/includes/
    /core/
        - AYS_CoreLoader.php
    /helpers/
        - HelpersLoader.php
    /posttypes/
        - PostTypesLoader.php
    /taxonomies/
        - TaxonomiesLoader.php
    /autoloader/
        - AYS_ClassAutoloader.php
```

---

#### **Next Steps**

1. **Testing**:

   - Verify each loader can resolve and include classes within its namespace.
   - Test fallback mechanisms and error handling.

2. **Integration with Settings**:

   - Connect loaders to a user-facing settings page for feature toggling.

3. **Documentation Updates**:
   - Continue maintaining documentation for new loaders and any refinements to the cascade strategy.

# At Your Service Autoloader Strategy

From the information contained in your `ays_autoloader.md` file and related project documentation, it’s evident that you are working on a modularized autoloading strategy for the **At Your Service** plugin. Here's how everything aligns:

## Current File Structure

Your documentation highlights the following loaders:

- **Core Loader**  
  Handles `ays\includes\core`.

- **Helpers Loader**  
  Manages `ays\includes\helpers`.

- **Post Types Loader**  
  Deals with `ays\includes\posttypes`.

- **Taxonomies Loader**  
  Responsible for `ays\includes\taxonomies`.
