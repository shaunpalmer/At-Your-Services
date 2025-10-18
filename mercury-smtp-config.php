<?php
/**
 * Configure WordPress to use Mercury/32 SMTP Server for email delivery
 * 
 * Mercury runs on localhost:25 by default
 * This uses PHPMailer's SMTP capabilities through WordPress filters
 */

// Add this filter to wp-config.php or a mu-plugin to enable SMTP
add_action('phpmailer_init', function($phpmailer) {
    // Use SMTP instead of mail()
    $phpmailer->isSMTP();
    
    // Mercury/32 SMTP settings (local)
    $phpmailer->Host = 'localhost';
    $phpmailer->Port = 25;  // Mercury's default SMTP port
    $phpmailer->SMTPAuth = false;  // Mercury doesn't require authentication locally
    $phpmailer->SMTPSecure = '';  // No encryption for local server
    
    // Optional: Set From address
    $phpmailer->From = get_option('admin_email');
    $phpmailer->FromName = get_bloginfo('name');
    
    error_log('PHPMailer configured to use Mercury SMTP: localhost:25');
}, 10, 1);
