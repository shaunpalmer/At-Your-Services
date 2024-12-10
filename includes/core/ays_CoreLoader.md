<?php

namespace ays\includes\core;

class AYS_CoreLoader
{
  public static function load($className)
  {
    // Debugging: Log the class name to be loaded
    error_log("AYS_CoreLoader: Attempting to autoload class: $className");

    // Ensure it only loads relevant classes
    if (strpos($className, 'ays\\includes\\core') === 0) {
      $baseDir = AYS_PLUGIN_PATH . 'includes/core/';

      // Get the relative class name
      $relativeClass = str_replace('ays\\includes\\core\\', '', $className);

      // Replace namespace separators with directory separators
      $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

      // Debugging: Log the resolved file path
      error_log("AYS_CoreLoader: Resolved path: $file");

      // Check if the file exists and require it
      if (file_exists($file)) {
        require_once $file;
        error_log("AYS_CoreLoader: Successfully autoloaded class: $className from file: $file");
      } else {
        error_log("AYS_CoreLoader: Autoloader could not find file: $file for class: $className");
      }
    } else {
      error_log("AYS_CoreLoader: Skipped class: $className as it does not match the namespace.");
    }
  }
}

// Register the autoloader function properly as a static method
spl_autoload_register(['\\ays\\includes\\core\\AYS_CoreLoader', 'load']);
