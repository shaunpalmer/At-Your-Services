<?php

namespace ays\includes\core;

class AYS_CoreLoader
{
  public static function load($class)
  {
    // This condition was added to validate that $class is a string to prevent array-to-string conversion warnings.
    if (!is_string($class)) {
      error_log("AYS_CoreLoader: Invalid class type. Expected string, got " . gettype($class));
      return;
    }

    // Base namespace for the plugin
    $namespace = 'ays\includes\core';
    $base_dir = __DIR__;

    if (strpos($class, $namespace) === 0) {
      $class_file = str_replace([$namespace, '\\'], ['', DIRECTORY_SEPARATOR], $class) . '.php';
      $file = $base_dir . DIRECTORY_SEPARATOR . $class_file;

      // This check ensures that the file for the given class exists before attempting to include it, avoiding runtime errors.
      if (file_exists($file)) {
        require_once $file;
        error_log("AYS_CoreLoader: Successfully loaded class $class from $file. This log entry confirms successful file loading. Review the surrounding logic to ensure similar success for all expected classes.");
      } else {
        error_log("AYS_CoreLoader: Failed to load class $class. Expected file $file does not exist.");
      }
    } elseif ($class === 'ays\\includes\\core\\AYS_Core') {
      // This fallback ensures that core files are explicitly loaded. Confirm that the core file is correctly named and located to avoid this error.
      $core_file = $base_dir . DIRECTORY_SEPARATOR . 'ays_core.php';
      if (file_exists($core_file)) {
        require_once $core_file;
        error_log("AYS_CoreLoader: Fallback to load core file from $core_file.");
      } else {
        error_log("AYS_CoreLoader: Core file not found at $core_file.");
      }
    } else {
      error_log("AYS_CoreLoader: Class $class does not match the namespace $namespace.");
    }
  }
}

spl_autoload_register(['\\ays\\includes\\core\\AYS_CoreLoader', 'load']);
