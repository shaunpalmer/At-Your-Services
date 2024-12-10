<?php
namespace ays\includes\classes; // Adjust this if you're placing this in a different namespace.
 //Exit if accessed directly: Check for bugs and past
if(!defined('ABSPATH')){
	exit;
}
class Ays_Debug_Log {
    private $pluginDirPath;
    private $logFilePath;
    private $maxEntries;

    public function __construct() {
        $this->pluginDirPath = __DIR__; // Adjust path if necessary
        $this->logFilePath = realpath($this->pluginDirPath . '/../../debug.log');
        $this->maxEntries = (int) get_option('ays_debug_log_max_entries', 20);
    }

    public function display_debug_log_notice() {
        if ($this->logFilePath && is_file($this->logFilePath)) {
            $logEntries = array_reverse(file($this->logFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
            $message = "<div class='notice notice-info is-dismissible'><p><strong>AYS Debug Log:</strong><br>";

            $entriesCount = 0;

            foreach ($logEntries as $line) {
                if ($entriesCount >= $this->maxEntries) {
                    break;
                }
                $message .= esc_html($line) . "<br>";
                $entriesCount++;
            }

            if ($entriesCount === 0) {
                $message .= "No log entries found for the specified time range.";
            }

            $message .= "</p></div>";
            echo $message;
        } else {
            echo "<div class='notice notice-error is-dismissible'><p><strong>Error:</strong> Debug log file does not exist at: " . esc_html($this->logFilePath) . "</p></div>";
        }
    }
}