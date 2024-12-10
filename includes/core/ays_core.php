<?php

namespace ays\includes\core;

use ays\includes\helpers\WP_Error_Handler;
// Initialize the Error Handler
#use ays\includes\helpers\AYS_ClassAutoloader;

// Add settings for the maximum number of log entries to display
function ays_register_settings()
{
  register_setting('ays_settings_group', 'ays_debug_log_max_entries', [
    'type' => 'integer',
    'description' => 'Maximum number of debug log entries to display in the admin notice.',
    'default' => 20,
    'sanitize_callback' => 'absint'
  ]);
}
#add_action('admin_init', 'ays_register_settings');

// Add a settings field to update the maximum number of log entries to display
function ays_add_settings_field()
{
  add_settings_field(
    'ays_debug_log_max_entries',
    'Max Debug Log Entries',
    'ays_debug_log_max_entries_callback',
    'general'
  );
  register_setting('general', 'ays_debug_log_max_entries');
}
#add_action('admin_menu', 'ays_add_settings_field');

function ays_debug_log_max_entries_callback()
{
  $value = (int) get_option('ays_debug_log_max_entries', 20);
  echo '<input type="number" id="ays_debug_log_max_entries" name="ays_debug_log_max_entries"
  value="' . esc_attr($value) . '" min="1">';
}
