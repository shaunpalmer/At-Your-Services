<?php
// Developer utility: seed sample leads.
// Usage: php seed-leads.php 10
if ( php_sapi_name() !== 'cli' ) { echo "CLI only"; exit; }

$target = isset($argv[1]) ? (int)$argv[1] : 10;
if ( $target < 1 ) { $target = 10; }

$wp_load = dirname(__DIR__,3) . DIRECTORY_SEPARATOR . 'wp-load.php';
if ( ! file_exists( $wp_load ) ) {
    echo "Cannot locate wp-load.php at $wp_load" . PHP_EOL;
    exit(1);
}
require $wp_load;

if ( ! function_exists('wp_insert_post') ) {
    echo "WordPress functions not loaded." . PHP_EOL;
    exit(1);
}

$existing = new WP_Query([
    'post_type' => 'ays_lead',
    'posts_per_page' => -1,
    'fields' => 'ids'
]);
$count_existing = $existing->post_count;

$first_names = ['Alex','Jamie','Taylor','Jordan','Morgan','Riley','Charlie','Sam','Casey','Avery','Elliot','Harper'];
$last_names  = ['Smith','Brown','Johnson','Lee','Wilson','Clark','Taylor','Walker','Young','King','Wright'];

$created = 0;
for ( $i = 0; $i < $target; $i++ ) {
    $fname = $first_names[array_rand($first_names)];
    $lname = $last_names[array_rand($last_names)];
    $full  = "$fname $lname";
    $email = strtolower($fname.'.'.$lname.rand(1,99).'@example.com');
    $digits = str_pad((string)rand(1000,9999),4,'0',STR_PAD_LEFT);
    $phone = '+64-555-' . $digits;
    $dayOffset = $i + 1;
    $booking_date = date('Y-m-d', strtotime("+{$dayOffset} day"));
    $booking_time = sprintf('%02d:%02d', rand(8,16), rand(0,1)?'00':'30');

    $post_id = wp_insert_post([
        'post_type' => 'ays_lead',
        'post_status' => 'publish',
        'post_title' => $full . ' - ' . current_time('mysql'),
        'meta_input' => [
            'ays_email' => $email,
            'ays_phone' => $phone,
            'ays_notes' => 'Sample seeded lead for development demo.',
            'ays_booking_date' => $booking_date,
            'ays_booking_time' => $booking_time,
        ]
    ], true);
    if ( is_wp_error( $post_id ) ) {
        echo 'Error: ' . $post_id->get_error_message() . PHP_EOL;
        continue;
    }
    $created++;
    echo "Created lead #$post_id ($full)" . PHP_EOL;
}

echo "Done. Existing before: $count_existing  Added: $created  Total now: " . ($count_existing + $created) . PHP_EOL;