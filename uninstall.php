<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://project-studios.nz
 * @since      1.0.0
 * @package    At_Your_Service
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Uninstall At Your Service Plugin
 *
 * Removes all plugin data including:
 * - Custom post types (Services, Team, FAQs, Reviews, Locations)
 * - Taxonomies (Service Types, Price Ranges, Neighbourhoods)
 * - Plugin options
 */

// Delete all Services posts
$services = get_posts(
	array(
		'post_type'      => 'ays_service',
		'posts_per_page' => -1,
		'post_status'    => 'any',
	)
);

foreach ( $services as $service ) {
	wp_delete_post( $service->ID, true );
}

// Delete all Team posts
$team_members = get_posts(
	array(
		'post_type'      => 'ays_team',
		'posts_per_page' => -1,
		'post_status'    => 'any',
	)
);

foreach ( $team_members as $member ) {
	wp_delete_post( $member->ID, true );
}

// Delete all FAQ posts
$faqs = get_posts(
	array(
		'post_type'      => 'ays_faq',
		'posts_per_page' => -1,
		'post_status'    => 'any',
	)
);

foreach ( $faqs as $faq ) {
	wp_delete_post( $faq->ID, true );
}

// Delete all Review posts
$reviews = get_posts(
	array(
		'post_type'      => 'ays_review',
		'posts_per_page' => -1,
		'post_status'    => 'any',
	)
);

foreach ( $reviews as $review ) {
	wp_delete_post( $review->ID, true );
}

// Delete all Location posts
$locations = get_posts(
	array(
		'post_type'      => 'ays_location',
		'posts_per_page' => -1,
		'post_status'    => 'any',
	)
);

foreach ( $locations as $location ) {
	wp_delete_post( $location->ID, true );
}

// Delete all Service Type taxonomy terms
$service_types = get_terms(
	array(
		'taxonomy'   => 'ays_service_type',
		'hide_empty' => false,
	)
);

if ( ! is_wp_error( $service_types ) ) {
	foreach ( $service_types as $term ) {
		wp_delete_term( $term->term_id, 'ays_service_type' );
	}
}

// Delete all Price Range taxonomy terms
$price_ranges = get_terms(
	array(
		'taxonomy'   => 'ays_price_range',
		'hide_empty' => false,
	)
);

if ( ! is_wp_error( $price_ranges ) ) {
	foreach ( $price_ranges as $term ) {
		wp_delete_term( $term->term_id, 'ays_price_range' );
	}
}

// Delete all Neighbourhood taxonomy terms
$neighbourhoods = get_terms(
	array(
		'taxonomy'   => 'ays_neighbourhood',
		'hide_empty' => false,
	)
);

if ( ! is_wp_error( $neighbourhoods ) ) {
	foreach ( $neighbourhoods as $term ) {
		wp_delete_term( $term->term_id, 'ays_neighbourhood' );
	}
}

// Delete plugin options
delete_option( 'ays_version' );
delete_option( 'ays_settings' );

// For multisite installations
if ( is_multisite() ) {
	delete_site_option( 'ays_version' );
	delete_site_option( 'ays_settings' );
}

// Clear any cached data
wp_cache_flush();
