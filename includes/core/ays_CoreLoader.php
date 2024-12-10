<?php

namespace ays\includes\core;

class AYS_CoreLoader
{
  public static function load($className)
  {
    // Ensure it only loads relevant classes
    if (strpos($className, 'ays\\includes\\helpers') === 0) {
      $baseDir = AYS_PLUGIN_PATH . 'includes/helpers/';
      // Get the relative class name
      $relativeClass = str_replace('ays\\includes\\helpers\\', '', $className);
      // Replace namespace separators with directory separators
      $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

      if (file_exists($file)) {
        require_once $file;
        error_log("Autoloaded class: $className from file: $file");
      } else {
        error_log("Autoloader could not find file: $file for class: $className");
      }
    }
  }
}

// Register the autoloader function properly as a static method
spl_autoload_register(['\ays\includes\core\AYS_CoreLoader', 'load']);
