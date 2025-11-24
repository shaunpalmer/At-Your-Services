<?php
/**
 * At Your Services - Premium License Management
 * 
 * Handles license key validation and premium feature gating
 * Single codebase strategy: One plugin, conditional features
 * 
 * @package At Your Service
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AYS_License {
    
    private static $instance = null;
    const OPTION_KEY = 'ays_license_key';
    const ACTIVE_KEY = 'ays_license_active';
    const FEATURES_KEY = 'ays_premium_features';
    
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }
    
    /**
     * Check if a premium feature is enabled
     * 
     * @param string $feature Feature slug
     * @return bool
     */
    public static function is_premium( $feature = null ) {
        $license_active = get_option( self::ACTIVE_KEY, false );
        
        if ( ! $license_active ) {
            return false;
        }
        
        if ( $feature ) {
            $features = get_option( self::FEATURES_KEY, [] );
            return in_array( $feature, (array) $features, true );
        }
        
        return true;
    }
    
    /**
     * Get license key
     */
    public static function get_license() {
        return get_option( self::OPTION_KEY );
    }
    
    /**
     * Set license key
     */
    public static function set_license( $key ) {
        update_option( self::OPTION_KEY, sanitize_text_field( $key ) );
    }
    
    /**
     * Activate license
     */
    public static function activate_license() {
        update_option( self::ACTIVE_KEY, true );
    }
    
    /**
     * Deactivate license
     */
    public static function deactivate_license() {
        update_option( self::ACTIVE_KEY, false );
    }
    
    /**
     * Register license settings
     */
    public function register_settings() {
        register_setting( 'ays_license_settings', self::OPTION_KEY );
        register_setting( 'ays_license_settings', self::ACTIVE_KEY );
    }
}

// Global helper functions
if ( ! function_exists( 'ays_is_premium' ) ) {
    /**
     * Check if premium feature is enabled
     * 
     * @param string $feature Optional feature to check
     * @return bool
     */
    function ays_is_premium( $feature = null ) {
        return AYS_License::is_premium( $feature );
    }
}

if ( ! function_exists( 'ays_show_premium_cta' ) ) {
    /**
     * Display premium upgrade CTA
     * 
     * @param string $feature_name Human-readable feature name
     */
    function ays_show_premium_cta( $feature_name = 'This feature' ) {
        if ( ays_is_premium() ) {
            return;
        }
        
        echo wp_kses_post( sprintf(
            '<div class="ays-premium-cta" style="padding:16px; background:#f0f4ff; border-left:4px solid #4c51bf; margin:12px 0; border-radius:4px;">
                <strong>🔒 %s</strong> is a premium feature. 
                <a href="%s" class="button button-primary" style="margin-left:12px;">Upgrade Now</a>
            </div>',
            esc_html( $feature_name ),
            esc_url( admin_url( 'admin.php?page=ays-license' ) )
        ) );
    }
}