<?php
namespace ays\includes\Classes;

use Ays\includes\Classes\NamespaceValidator;

class AysAdminLogger {
    private $namespaceValidator;

    /**
     * Constructor to initialize the NamespaceValidator instance
     */
    public function __construct() {
        $this->namespaceValidator = new NamespaceValidator();
    }

    /**
     * Ays Discovery Cache Test Notice
     * - Enhanced version to include namespace validation and file existence checks
     * - Displays success and failure messages separately, with user-friendly summaries
     */
    public function discoveryCacheTestNotice() {
        $messages = [];
        $successMessages = [];
        $errorMessages = [];

        // START of: Enhanced class existence check with namespace validation
        $className = 'Ays\includes\Classes\Ays_Discovery_Cache_Logger';
        $baseDir = __DIR__ . '/';

        if ($this->namespaceValidator->validateNamespaceStructure($className)) {
            if ($this->namespaceValidator->doesClassFileExist($className, $baseDir)) {
                if (class_exists($className)) {
                    $successMessages[] = Ays_Discovery_Cache_Logger::$load_message;
                } else {
                    $errorMessages[] = "Class found in filesystem but failed to load: $className.";
                }
            } else {
                $errorMessages[] = "Class file does not exist for: $className.";
            }
        } else {
            $errorMessages[] = "Namespace structure is invalid for: $className.";
        }
        // END of: Enhanced class existence check with namespace validation

        // START of: Display consolidated notices for success and error messages
        if (!empty($successMessages)) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . esc_html(implode(' ', $successMessages)) . '</p>';
            echo '</div>';
        }

        if (!empty($errorMessages)) {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>' . esc_html(implode(' ', $errorMessages)) . '</p>';
            echo '</div>';
        }
        // END of: Display consolidated notices for success and error messages
    }

    /**
     * Enhanced debug log notice with filtering and detailed insights
     */
    public function debugLogNotice() {
        $pluginDirPath = __DIR__;
        $logFilePath = realpath($pluginDirPath . '/../../debug.log');

        // Set the time zone to UTC (adjust if needed)
        $timezone = new DateTimeZone('UTC');

        // Set the time range (e.g., past 30 minutes)
        $timeRangeMinutes = 30;

        // Define the target date and time range based on the current time
        $currentTime = new DateTime('now', $timezone);
        $startTime = clone $currentTime;
        $startTime->modify("-$timeRangeMinutes minutes");

        // Set the maximum number of log entries to display
        $maxEntries = (int) get_option('ays_debug_log_max_entries', 10);

        if ($logFilePath && file_exists($logFilePath)) {
            $logEntries = file($logFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $message = "<div class='notice notice-info is-dismissible'><p><strong>AYS Debug Log:</strong><br>";

            $entriesFound = false;
            $entriesCount = 0;

            // START of: Enhanced log filtering by namespace or class
            foreach ($logEntries as $line) {
                if (preg_match('/\[(.*?)\]/', $line, $matches)) {
                    $dateString = $matches[1]; // e.g., '17-Nov-2024 13:54:07 UTC'
                    $logTime = DateTime::createFromFormat('d-M-Y H:i:s T', $dateString, $timezone);

                    // Filtering by date and namespace/class
                    if ($logTime && $logTime >= $startTime && $logTime <= $currentTime) {
                        // Add additional filtering for namespace or class if needed
                        if (strpos($line, 'Ays\includes\Classes') !== false) {
                            $message .= esc_html($line) . "<br>";
                            $entriesFound = true;
                            $entriesCount++;
                        }
                    }
                }

                if ($entriesCount >= $maxEntries) {
                    break;
                }
            }
            // END of: Enhanced log filtering by namespace or class

            if (!$entriesFound) {
                $message .= "No log entries found for the specified time range or filter.";
            }

            $message .= "</p></div>";
            echo $message;
        } else {
            echo "<div class='notice notice-error is-dismissible'><p><strong>Error:</strong> Debug log file does not exist at: " . esc_html($logFilePath) . "</p></div>";
        }
    }
}
new AysAdminLogger();
// START of: Instantiate AysAdminLogger and register admin actions
$aysAdminLogger = new AysAdminLogger();
add_action('admin_notices', [$aysAdminLogger, 'discoveryCacheTestNotice']);
add_action('admin_notices', [$aysAdminLogger, 'debugLogNotice']);
// END of: Instantiate AysAdminLogger and register admin actions