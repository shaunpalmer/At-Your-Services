<?php

namespace ays\includes\core;

class AYS_CoreLoader
{
  public static function load($class)
  {
    // Validate that $class is a string to prevent array-to-string conversion warnings.
    if (!is_string($class)) {
      error_log("AYS_CoreLoader: Invalid class type. Expected string, got " . gettype($class));
      return;
    }

    // Base namespace for the plugin
    $base_namespace = 'ays\\includes\\core'; // Adjusted to target core-specific classes only.
    $base_dir = __DIR__ . '/core';

    // Normalize the class name to handle case insensitivity
    $class = strtolower($class);
    $base_namespace = strtolower($base_namespace);

    // Check if the class belongs to the plugin namespace
    if (strpos($class, $base_namespace) === 0) {
      // Resolve the class to a file path
      $class_path = str_replace(['\\', $base_namespace], [DIRECTORY_SEPARATOR, ''], $class);
      $file = $base_dir . $class_path . '.php';

      // Ensure the file exists before requiring it
      if (file_exists($file)) {
        require_once $file;
        error_log("AYS_CoreLoader: Successfully loaded class $class from $file.");
      } else {
        error_log("AYS_CoreLoader: Failed to load class $class. Expected file $file does not exist.");
      }
    } else {
      // Log for unmatched classes
      error_log("AYS_CoreLoader: Class $class does not belong to namespace $base_namespace.");
    }
  }
}

// Register the autoloader
spl_autoload_register(['\\ays\\includes\\core\\AYS_CoreLoader', 'load']);


use ays\includes\helpers\WP_Error_Handler;

namespace ays\includes\helpers;

class HelpersLoader
{
  public static function load($class)
  {
    if (!is_string($class)) {
      error_log("HelpersLoader: Invalid class type. Expected string, got " . gettype($class));
      return;
    }

    $namespace = 'ays\\includes\\helpers';
    $base_dir = __DIR__;

    if (strpos($class, $namespace) === 0) {
      $class_path = str_replace([$namespace, '\\'], ['', DIRECTORY_SEPARATOR], $class);
      $file = $base_dir . DIRECTORY_SEPARATOR . $class_path . '.php';

      if (file_exists($file)) {
        require_once $file;
        error_log("HelpersLoader: Successfully loaded $class from $file.");
      } else {
        error_log("HelpersLoader: File $file for class $class not found.");
      }
    }
  }
}

spl_autoload_register(['\\ays\\includes\\helpers\\HelpersLoader', 'load']);

namespace ays\includes\posttypes;

class PostTypesLoader
{
  public static function load($class)
  {
    if (!is_string($class)) {
      error_log("PostTypesLoader: Invalid class type. Expected string, got " . gettype($class));
      return;
    }

    $namespace = 'ays\\includes\\posttypes';
    $base_dir = __DIR__;

    if (strpos($class, $namespace) === 0) {
      $class_path = str_replace([$namespace, '\\'], ['', DIRECTORY_SEPARATOR], $class);
      $file = $base_dir . DIRECTORY_SEPARATOR . $class_path . '.php';

      if (file_exists($file)) {
        require_once $file;
        error_log("PostTypesLoader: Successfully loaded $class from $file.");
      } else {
        error_log("PostTypesLoader: File $file for class $class not found.");
      }
    }
  }
}

spl_autoload_register(['\\ays\\includes\\posttypes\\PostTypesLoader', 'load']);

namespace ays\includes\taxonomies;

class TaxonomiesLoader
{
  public static function load($class)
  {
    if (!is_string($class)) {
      error_log("TaxonomiesLoader: Invalid class type. Expected string, got " . gettype($class));
      return;
    }

    $namespace = 'ays\\includes\\taxonomies';
    $base_dir = __DIR__;

    if (strpos($class, $namespace) === 0) {
      $class_path = str_replace([$namespace, '\\'], ['', DIRECTORY_SEPARATOR], $class);
      $file = $base_dir . DIRECTORY_SEPARATOR . $class_path . '.php';

      if (file_exists($file)) {
        require_once $file;
        error_log("TaxonomiesLoader: Successfully loaded $class from $file.");
      } else {
        error_log("TaxonomiesLoader: File $file for class $class not found.");
      }
    }
  }
}

spl_autoload_register(['\\ays\\includes\\taxonomies\\TaxonomiesLoader', 'load']);
