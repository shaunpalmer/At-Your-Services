<?php
namespace ays\includes\Classes;


class NamespaceValidator {

    /**
     * Check if a class name has the given namespace prefix.
     * 
     * @param string $className The full class name to check.
     * @param string $namespace The namespace to compare against.
     * @return bool True if the class name matches the namespace, false otherwise.
     */
    public function isNamespacePrefix(string $className, string $namespace): bool {
        // START of: Convert class and namespace to lowercase for case-insensitive comparison
        $className = strtolower($className);
        $namespace = strtolower($namespace);
        // END of: Convert class and namespace to lowercase for case-insensitive comparison

        // START of: Check if the class name starts with the namespace prefix
        return strpos($className, $namespace) === 0;
        // END of: Check if the class name starts with the namespace prefix
    }

    /**
     * Validate if a namespace follows PSR-4 guidelines.
     * 
     * @param string $namespace The namespace to validate.
     * @return bool True if the namespace is valid, false otherwise.
     */
    public function validateNamespaceStructure(string $namespace): bool {
        // START of: Ensure namespace contains only valid characters and is not empty
        $pattern = '/^[a-zA-Z0-9_\\]+$/';
        return preg_match($pattern, $namespace) === 1;
        // END of: Ensure namespace contains only valid characters and is not empty
    }

    /**
     * Validate if the class file exists in the expected directory based on the namespace.
     * 
     * @param string $className The fully-qualified class name.
     * @param string $baseDir The base directory for the namespace.
     * @return bool True if the file exists, false otherwise.
     */
    public function doesClassFileExist(string $className, string $baseDir): bool {
        // START of: Convert namespace to file path
        $filePath = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
        // END of: Convert namespace to file path

        // START of: Check if file exists
        return file_exists($filePath);
        // END of: Check if file exists
    }

    /**
     * Log mismatched namespaces or invalid paths to a log file.
     * 
     * @param string $message The message to log.
     * @return void
     */
    public function logMismatch(string $message): void {
        // START of: Write to log file
        error_log("[NamespaceValidator] $message\n", 3, __DIR__ . '/namespace_validator.log');
        // END of: Write to log file
    }

    /**
     * Integrate with autoloader for dynamic validation during development.
     * 
     * @param string $className The fully-qualified class name.
     * @param string $baseDir The base directory for the namespace.
     * @return bool True if class is successfully loaded, false otherwise.
     */
    public function integrateWithAutoloader(string $className, string $baseDir): bool {
        // START of: Validate namespace and file existence
        if (!$this->validateNamespaceStructure($className)) {
            $this->logMismatch("Invalid namespace structure: $className");
            return false;
        }
        if (!$this->doesClassFileExist($className, $baseDir)) {
            $this->logMismatch("Class file does not exist: $className");
            return false;
        }
        // END of: Validate namespace and file existence

        // START of: Attempt to include the class file
        require_once $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
        return true;
        // END of: Attempt to include the class file
    }
}