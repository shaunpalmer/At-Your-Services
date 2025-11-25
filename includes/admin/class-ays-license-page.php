<?php
/**
 * License Settings Admin Page
 * 
 * Provides UI for license key management and premium feature activation
 * 
 * @package At Your Service
 * @since 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AYS_License_Page {
    
    /**
     * Initialize the license page
     */
    public static function init() {
        add_action( 'admin_menu', [ self::class, 'register_menu' ], 20 );
        add_action( 'admin_init', [ self::class, 'handle_license_actions' ] );
    }
    
    /**
     * Register the license submenu page
     */
    public static function register_menu() {
        add_submenu_page(
            'ays-dashboard',                                    // Parent slug
            __( 'License', 'atyourservice' ),                  // Page title
            __( 'License', 'atyourservice' ),                  // Menu title
            'manage_options',                                   // Capability
            'ays-license',                                      // Menu slug
            [ self::class, 'render_page' ]                     // Callback
        );
    }
    
    /**
     * Handle license activation/deactivation
     */
    public static function handle_license_actions() {
        if ( ! isset( $_POST['ays_license_action'] ) ) {
            return;
        }
        
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        if ( ! wp_verify_nonce( $_POST['ays_license_nonce'] ?? '', 'ays_license_action' ) ) {
            add_settings_error( 'ays_license', 'nonce_failed', __( 'Security check failed.', 'atyourservice' ), 'error' );
            return;
        }
        
        $action = sanitize_text_field( $_POST['ays_license_action'] );
        
        switch ( $action ) {
            case 'activate':
                $license_key = sanitize_text_field( $_POST['ays_license_key'] ?? '' );
                if ( empty( $license_key ) ) {
                    add_settings_error( 'ays_license', 'empty_key', __( 'Please enter a license key.', 'atyourservice' ), 'error' );
                    return;
                }
                
                // Validate license key format (basic validation)
                if ( strlen( $license_key ) < 16 ) {
                    add_settings_error( 'ays_license', 'invalid_key', __( 'Invalid license key format.', 'atyourservice' ), 'error' );
                    return;
                }
                
                // Save and activate
                AYS_License::set_license( $license_key );
                AYS_License::activate_license();
                
                // Set default premium features
                update_option( 'ays_premium_features', [
                    'advanced_invoicing',
                    'email_notifications',
                    'client_portal',
                    'export_functionality',
                    'analytics_dashboard',
                    'custom_fields'
                ]);
                
                add_settings_error( 'ays_license', 'activated', __( 'License activated successfully! Premium features are now enabled.', 'atyourservice' ), 'success' );
                break;
                
            case 'deactivate':
                AYS_License::deactivate_license();
                delete_option( 'ays_premium_features' );
                add_settings_error( 'ays_license', 'deactivated', __( 'License deactivated.', 'atyourservice' ), 'info' );
                break;
        }
    }
    
    /**
     * Render the license settings page
     */
    public static function render_page() {
        $license_key = AYS_License::get_license();
        $is_active = ays_is_premium();
        $features = get_option( 'ays_premium_features', [] );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'At Your Services - License', 'atyourservice' ); ?></h1>
            
            <?php settings_errors( 'ays_license' ); ?>
            
            <div class="ays-license-container" style="max-width: 800px; margin-top: 20px;">
                
                <!-- License Status Card -->
                <div class="card" style="padding: 20px; margin-bottom: 20px;">
                    <h2 style="margin-top: 0;">
                        <?php if ( $is_active ): ?>
                            <span style="color: #46b450;">✓</span> 
                            <?php esc_html_e( 'Premium License Active', 'atyourservice' ); ?>
                        <?php else: ?>
                            <span style="color: #dc3232;">○</span> 
                            <?php esc_html_e( 'Free Version', 'atyourservice' ); ?>
                        <?php endif; ?>
                    </h2>
                    
                    <form method="post" action="">
                        <?php wp_nonce_field( 'ays_license_action', 'ays_license_nonce' ); ?>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="ays_license_key"><?php esc_html_e( 'License Key', 'atyourservice' ); ?></label>
                                </th>
                                <td>
                                    <input 
                                        type="text" 
                                        id="ays_license_key" 
                                        name="ays_license_key" 
                                        value="<?php echo esc_attr( $license_key ); ?>" 
                                        class="regular-text"
                                        placeholder="XXXX-XXXX-XXXX-XXXX"
                                        <?php echo $is_active ? 'readonly' : ''; ?>
                                    />
                                    <?php if ( $is_active ): ?>
                                        <p class="description" style="color: #46b450;">
                                            <?php esc_html_e( 'Your license is active.', 'atyourservice' ); ?>
                                        </p>
                                    <?php else: ?>
                                        <p class="description">
                                            <?php esc_html_e( 'Enter your license key to unlock premium features.', 'atyourservice' ); ?>
                                        </p>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <?php if ( $is_active ): ?>
                                <button type="submit" name="ays_license_action" value="deactivate" class="button button-secondary">
                                    <?php esc_html_e( 'Deactivate License', 'atyourservice' ); ?>
                                </button>
                            <?php else: ?>
                                <button type="submit" name="ays_license_action" value="activate" class="button button-primary">
                                    <?php esc_html_e( 'Activate License', 'atyourservice' ); ?>
                                </button>
                            <?php endif; ?>
                        </p>
                    </form>
                </div>
                
                <!-- Premium Features List -->
                <div class="card" style="padding: 20px;">
                    <h2 style="margin-top: 0;"><?php esc_html_e( 'Premium Features', 'atyourservice' ); ?></h2>
                    
                    <table class="widefat" style="margin-top: 15px;">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Feature', 'atyourservice' ); ?></th>
                                <th style="width: 100px; text-align: center;"><?php esc_html_e( 'Status', 'atyourservice' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $all_features = [
                                'advanced_invoicing' => __( 'Advanced Invoicing', 'atyourservice' ),
                                'email_notifications' => __( 'Email Notifications', 'atyourservice' ),
                                'client_portal' => __( 'Client Portal', 'atyourservice' ),
                                'export_functionality' => __( 'Export Functionality', 'atyourservice' ),
                                'analytics_dashboard' => __( 'Analytics Dashboard', 'atyourservice' ),
                                'custom_fields' => __( 'Custom Fields', 'atyourservice' ),
                            ];
                            
                            foreach ( $all_features as $slug => $name ):
                                $enabled = in_array( $slug, $features, true );
                            ?>
                            <tr>
                                <td><?php echo esc_html( $name ); ?></td>
                                <td style="text-align: center;">
                                    <?php if ( $enabled ): ?>
                                        <span style="color: #46b450; font-weight: bold;">✓ <?php esc_html_e( 'Enabled', 'atyourservice' ); ?></span>
                                    <?php else: ?>
                                        <span style="color: #999;">— <?php esc_html_e( 'Locked', 'atyourservice' ); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if ( ! $is_active ): ?>
                    <div style="margin-top: 20px; padding: 15px; background: #f0f4ff; border-left: 4px solid #4c51bf; border-radius: 4px;">
                        <strong><?php esc_html_e( 'Upgrade to Premium', 'atyourservice' ); ?></strong>
                        <p style="margin: 10px 0 0;">
                            <?php esc_html_e( 'Unlock all premium features with a license key. Visit our website to purchase.', 'atyourservice' ); ?>
                        </p>
                        <a href="https://example.com/purchase" target="_blank" class="button button-primary" style="margin-top: 10px;">
                            <?php esc_html_e( 'Get Premium', 'atyourservice' ); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                
            </div>
        </div>
        <?php
    }
}
