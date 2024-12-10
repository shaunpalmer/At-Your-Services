<?php

namespace ays\includes\core;

class AYS_CoreLoader
{
  public static function load($config)
  {
    if (is_array($config) && isset($config['path'])) {
      $namespace = 'ays\\includes\\core';
      $path = $config['path'];

      // Ensure $path is a string
      if (is_string($path) && strpos($path, $namespace) !== false) {
        // Continue with the loading process
        error_log("AYS_CoreLoader: Path and namespace are valid. Loading process initiated.");
      } else {
        error_log('AYS_CoreLoader: Invalid path or namespace mismatch.');
      }
    } else {
      error_log('AYS_CoreLoader: Invalid configuration passed to load().');
    }
  }
}

// Register the autoloader function properly as a static method
spl_autoload_register(['\\ays\\includes\\core\\AYS_CoreLoader', 'load']);
