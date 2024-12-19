<?php

namespace AYS\Debug; if (!defined('ABSPATH')) { exit; // Prevent direct access } use AYS\Includes\Core\AYS_PathAdapter;
  use AYS\Includes\Core\AYS_PathCache; use AYS\Includes\Helpers\WP_Error_Handler; // Add error handler for detailed
  debugging /** * Debug Path Resolution Issues. */ class PathDebugger { private static $logFile=WP_CONTENT_DIR
  . '/debug-dynamic-calculated-paths.log' ; public static function debugPaths() { try { // Ensure the base path is set
  AYS_PathAdapter::setBasePath(ABSPATH); // Define critical paths to debug $paths=[ 'helpers'=>
  'wp-content/plugins/ays/includes/helpers',
  'templates' => 'wp-content/plugins/ays/templates',
  ];

  // Add paths to the adapter and retrieve them
  foreach ($paths as $key => $relativePath) {
  AYS_PathAdapter::addPath($key, $relativePath);
  $resolvedPath = AYS_PathAdapter::getPath($key, 'real');

  if (!$resolvedPath) {
  $errorMessage = "Path not found or invalid for key: $key";
  WP_Error_Handler::get_instance()->add_debug_notice($errorMessage, 'error');
  throw new \Exception($errorMessage);
  }

  $logMessage = "Resolved path for $key: $resolvedPath";
  error_log($logMessage);
  self::writeToLogFile($logMessage);
  }

  // Verify namespace resolution
  $namespace = 'AYS\\Includes\\Core\\AYS_PathAdapter';
  if (!class_exists($namespace)) {
  $errorMessage = "Namespace validation failed: $namespace";
  WP_Error_Handler::get_instance()->add_debug_notice($errorMessage, 'error');
  throw new \Exception($errorMessage);
  }

  $namespaceMessage = "Namespace $namespace validated successfully.";
  error_log($namespaceMessage);
  self::writeToLogFile($namespaceMessage);

  // Debug cached paths
  $cachedPaths = AYS_PathCache::listCachedPaths();
  if (empty($cachedPaths)) {
  $warningMessage = "No cached paths found.";
  WP_Error_Handler::get_instance()->add_debug_notice($warningMessage, 'warning');
  self::writeToLogFile($warningMessage);
  } else {
  $cachedPathsMessage = "Cached paths: " . print_r($cachedPaths, true);
  error_log($cachedPathsMessage);
  self::writeToLogFile($cachedPathsMessage);
  }
  } catch (\Exception $e) {
  $errorMessage = "Path Debugging Error: " . $e->getMessage();
  WP_Error_Handler::get_instance()->add_debug_notice($errorMessage, 'error');
  error_log($errorMessage);
  self::writeToLogFile($errorMessage);
  }
  }

  private static function writeToLogFile($message)
  {
  $timestamp = date('Y-m-d H:i:s');
  file_put_contents(self::$logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
  }
  }

  // Hook into WordPress admin to display debugging results in logs
  add_action('admin_init', function () {
  PathDebugger::debugPaths();
  });