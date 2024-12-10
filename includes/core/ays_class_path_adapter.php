<?php

namespace AYS\Includes\Core;

/**
 * Class AYS_PathAdapter
 * 
 * This class manages paths within the AYS plugin. It provides methods for setting, retrieving,
 * and validating paths, supporting various formats such as normalized, real, and URL paths.
 * Additionally, it includes namespace validation and debugging capabilities.
 */
class AYS_PathAdapter
{
    /**
     * Static map of paths for different types.
     * Used to store and retrieve specific paths by their keys.
     */
    private static $pathsMap = [];
    private static $basePath;

    /**
     * Set the base path for all operations.
     *
     * @param string $path Base directory path.
     */
    public static function setBasePath($path)
    {
        self::$basePath = rtrim(realpath($path) ?: $path, DIRECTORY_SEPARATOR);
    }

    /**
     * Add a new path to the map.
     *
     * @param string $key Identifier for the path.
     * @param string $relativePath Relative path to be added.
     */
    public static function addPath($key, $relativePath)
    {
        $fullPath = self::$basePath . DIRECTORY_SEPARATOR . $relativePath;
        self::$pathsMap[$key] = realpath($fullPath) ?: $fullPath;
    }

    /**
     * Retrieve a path based on the specified key and type.
     *
     * @param string $key Identifier for the path.
     * @param string $type Type of path to retrieve (e.g., 'real', 'url', 'normalized').
     * @return string|null The requested path or null if not found.
     */
    public static function getPath($key, $type = 'normalized')
    {
        // Validate namespace path if key is a namespace
        if (strpos($key, '\\') !== false && !self::validateNamespacePath($key, self::$basePath)) {
            return null;
        }

        if (!isset(self::$pathsMap[$key])) {
            return null;
        }

        $path = self::$pathsMap[$key];

        switch ($type) {
            case 'real':
                return realpath($path) ?: $path;
            case 'url':
                return plugins_url(str_replace(self::$basePath, '', $path));
            case 'plugin_dir':
                return wp_normalize_path(plugin_dir_path(__DIR__));
            case 'base_dir':
                return wp_normalize_path(self::$basePath);
            case 'file_exists':
                return self::checkFileExists($path);
            case 'trim_spaces':
                return self::trimWhitespace($path);
            case 'file':
                $relativeClass = substr($key, strlen('ays\\'));
                $relativeClass = str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass);
                $filePath = self::$basePath . DIRECTORY_SEPARATOR . $relativeClass . '.php';
                $realFilePath = realpath($filePath);
                return $realFilePath ?: 'File not found: ' . $filePath;
            case 'normalized':
            default:
                return self::normalizePath($path);
        }
    }

    /**
     * Validate namespace before proceeding with path resolution.
     *
     * @param string $namespace Namespace to validate.
     * @param string $baseDir Base directory for validation.
     * @return bool True if namespace is valid, false otherwise.
     */
    private static function validateNamespacePath($namespace, $baseDir)
    {
        try {
            $validator = new NamespaceValidator(); //Undefined type as a error DOTO fix it

            // Validate namespace structure
            if (!$validator->validateNamespaceStructure($namespace)) {
                throw new \Exception("Invalid namespace structure: $namespace");
            }

            // Validate if the class file exists
            if (!$validator->doesClassFileExist($namespace, $baseDir)) {
                throw new \Exception("Class file does not exist for namespace: $namespace");
            }
        } catch (\Exception $e) {
            error_log("[AYS_PathAdapter] Namespace validation error: " . $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Normalize path to use forward slashes.
     *
     * @param string $path Path to normalize.
     * @return string Normalized path.
     */
    private static function normalizePath($path)
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * Trim whitespace from a given path.
     *
     * @param string $path Path to trim.
     * @return string Trimmed path.
     */
    private static function trimWhitespace($path)
    {
        return trim($path);
    }

    /**
     * Check if the file exists at the given path.
     *
     * @param string $path Path to check.
     * @return string|null Path if file exists, null otherwise.
     */
    private static function checkFileExists($path)
    {
        return is_file($path) ? $path : null;
    }

    /**
     * Check if a class exists in the map.
     *
     * @param string $class Class name to check.
     * @return bool True if class exists, false otherwise.
     */
    public static function classExists($class)
    {
        return isset(self::$pathsMap[$class]) && is_file(self::$pathsMap[$class]);
    }

    /**
     * List all paths in the paths map.
     *
     * @return array Map of all paths.
     */
    public static function listPaths()
    {
        return self::$pathsMap;
    }

    /**
     * Resolve a file path in a case-insensitive manner with a scan limit.
     *
     * @param string $relativePath Relative path to resolve.
     * @param int $maxFiles Maximum number of files to scan.
     * @return string|null Resolved path if found, null otherwise.
     */
    protected static function resolveCaseInsensitiveFile($relativePath, $maxFiles = 25)
    {
        $directory = dirname($relativePath);
        $fileName = basename($relativePath);

        // Ensure the base directory exists
        $baseDir = realpath(self::$basePath . DIRECTORY_SEPARATOR . $directory);
        if (!$baseDir || !is_dir($baseDir)) {
            return null;
        }

        // Scan directory for matching file names
        $files = scandir($baseDir);
        $countFiles = 0;
        foreach ($files as $file) {
            if ($countFiles++ > $maxFiles) {
                break; // Exit scan if file count exceeds the limit
            }
            if (strcasecmp($file, $fileName) === 0) {
                return $baseDir . DIRECTORY_SEPARATOR . $file;
            }
        }

        return null; // File not found
    }
}

/**
 * Class AYS_PathCache
 *
 * Provides caching for frequently accessed paths to optimize performance.
 */
class AYS_PathCache
{
    /**
     * Static cache of paths.
     */
    private static $cache = [];

    /**
     * Retrieve a cached path.
     *
     * @param string $key Identifier for the path.
     * @param string $type Type of path.
     * @return string|null Cached path or null if not found.
     */
    public static function getCachedPath($key, $type)
    {
        return self::$cache[$key][$type] ?? null;
    }

    /**
     * Cache a path.
     *
     * @param string $key Identifier for the path.
     * @param string $type Type of path.
     * @param string $path Path to cache.
     */
    public static function cachePath($key, $type, $path)
    {
        self::$cache[$key][$type] = $path;
    }

    /**
     * Clear all cached paths.
     */
    public static function clearCache()
    {
        self::$cache = [];
    }

    /**
     * List all cached paths.
     *
     * @return array Map of all cached paths.
     */
    public static function listCachedPaths()
    {
        return self::$cache;
    }
}

// Example usage
AYS_PathAdapter::setBasePath(ABSPATH);

// Adding paths
AYS_PathAdapter::addPath('helpers', 'wp-content/plugins/my-plugin/includes/helpers');
// Adding paths
AYS_PathAdapter::addPath('helpers', 'wp-content/plugins/my-plugin/includes/helpers');
AYS_PathAdapter::addPath('templates', 'wp-content/plugins/my-plugin/templates');

// Retrieve paths
$helpersPath = AYS_PathAdapter::getPath('helpers', 'real');
echo "Helpers Path: $helpersPath\n";

$templatesPath = AYS_PathAdapter::getPath('templates', 'normalized');
echo "Templates Path: $templatesPath\n";

// Cache example
AYS_PathCache::cachePath('helpers', 'real', $helpersPath);
AYS_PathCache::cachePath('templates', 'normalized', $templatesPath);

// Debug cached paths
print_r(AYS_PathCache::listCachedPaths());
