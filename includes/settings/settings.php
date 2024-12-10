<?php
# https://developer.wordpress.org/plugins/settings/custom-settings-page/
/**
 * Create Settings Menu
 */

if ( ! defined( 'AYS_PLUGIN_PATH' ) ) {
    define( 'AYS_PLUGIN_PATH', wp_normalize_path( plugin_dir_path( __FILE__ ) ) );
}
/**
 * Create "At Your Service" Settings Menu
 */
function ays_register_settings_menu() {
    add_menu_page(
        __( 'AYS Overview', 'atyourservice' ),
        __( 'AYS Settings', 'atyourservice' ),
        'manage_options',
        'ays-settings-page',
        'ays_render_settings_page',
        'dashicons-admin-settings',
        null
    );
}
add_action('admin_menu', 'ays_register_settings_menu');

/**
 * Render Settings Page with Tabs
 */
function ays_render_settings_page() {
    // Get the current tab from the URL, default to 'general'
    $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';

    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

        <!-- Display the tabs -->
        <h2 class="nav-tab-wrapper">
            <a href="?page=ays-settings-page&tab=general" class="nav-tab <?php echo $current_tab == 'general' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'General', 'atyourservice' ); ?>
            </a>
            <a href="?page=ays-settings-page&tab=advanced" class="nav-tab <?php echo $current_tab == 'advanced' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'Advanced', 'atyourservice' ); ?>
            </a>
            <a href="?page=ays-settings-page&tab=appearance" class="nav-tab <?php echo $current_tab == 'appearance' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'Appearance', 'atyourservice' ); ?>
            </a>
            <a href="?page=ays-settings-page&tab=tools" class="nav-tab <?php echo $current_tab == 'tools' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'Tools', 'atyourservice' ); ?>
            </a>
        </h2>

        <form action="options.php" method="post">
            <?php
            // Load the appropriate settings sections based on the active tab
            if ( $current_tab === 'general' ) {
                settings_fields( 'ays-settings-general' );
                do_settings_sections( 'ays-settings-general' );
            } elseif ( $current_tab === 'advanced' ) {
                settings_fields( 'ays-settings-advanced' );
                do_settings_sections( 'ays-settings-advanced' );
            } elseif ( $current_tab === 'appearance' ) {
                settings_fields( 'ays-settings-appearance' );
                do_settings_sections( 'ays-settings-appearance' );
            } elseif ( $current_tab === 'tools' ) {
                settings_fields( 'ays-settings-tools' );
                do_settings_sections( 'ays-settings-tools' );
            }

            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/**
 * Initialize Settings for All Tabs
 */
function ays_initialize_settings() {
    // General Settings
    add_settings_section(
        'ays_general_section',
        __( 'General Settings', 'atyourservice' ),
        '',
        'ays-settings-general'
    );
    register_setting(
        'ays-settings-general',
        'ays_general_setting',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );
    add_settings_field(
        'ays_general_setting',
        __( 'General Option', 'atyourservice' ),
        'ays_render_general_setting',
        'ays-settings-general',
        'ays_general_section'
    );

    // Advanced Settings
    add_settings_section(
        'ays_advanced_section',
        __( 'Advanced Settings', 'atyourservice' ),
        '',
        'ays-settings-advanced'
    );
    register_setting(
        'ays-settings-advanced',
        'ays_advanced_setting',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );
    add_settings_field(
        'ays_advanced_setting',
        __( 'Advanced Option', 'atyourservice' ),
        'ays_render_advanced_setting',
        'ays-settings-advanced',
        'ays_advanced_section'
    );

    // Appearance Settings
    add_settings_section(
        'ays_appearance_section',
        __( 'Appearance Settings', 'atyourservice' ),
        '',
        'ays-settings-appearance'
    );
    register_setting(
        'ays-settings-appearance',
        'ays_appearance_setting',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );
    add_settings_field(
        'ays_appearance_setting',
        __( 'Appearance Option', 'atyourservice' ),
        'ays_render_appearance_setting',
        'ays-settings-appearance',
        'ays_appearance_section'
    );

    // Tools Settings
    add_settings_section(
        'ays_tools_section',
        __( 'Tools Settings', 'atyourservice' ),
        '',
        'ays-settings-tools'
    );
    register_setting(
        'ays-settings-tools',
        'ays_tools_setting',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );
    add_settings_field(
        'ays_tools_setting',
        __( 'Tools Option', 'atyourservice' ),
        'ays_render_tools_setting',
        'ays-settings-tools',
        'ays_tools_section'
    );
}
add_action( 'admin_init', 'ays_initialize_settings' );

/**
 * Render General Setting Field
 */
function ays_render_general_setting() {
    $value = get_option( 'ays_general_setting' );
    ?>
    <input type="text" name="ays_general_setting" value="<?php echo esc_attr( $value ); ?>" />
    <?php
}

/**
 * Render Advanced Setting Field
 */
function ays_render_advanced_setting() {
    $value = get_option( 'ays_advanced_setting' );
    ?>
    <input type="text" name="ays_advanced_setting" value="<?php echo esc_attr( $value ); ?>" />
    <?php
}

/**
 * Render Appearance Setting Field
 */
function ays_render_appearance_setting() {
    $value = get_option( 'ays_appearance_setting' );
    ?>
    <input type="text" name="ays_appearance_setting" value="<?php echo esc_attr( $value ); ?>" />
    <?php
}

/**
 * Render Tools Setting Field
 */
function ays_render_tools_setting() {
    $value = get_option( 'ays_tools_setting' );
    ?>
    <input type="text" name="ays_tools_setting" value="<?php echo esc_attr( $value ); ?>" />
    <?php
}
