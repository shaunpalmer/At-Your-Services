<?php
if ( ! defined( 'AYS_PLUGIN_PATH' ) ) {
    define( 'AYS_PLUGIN_PATH', wp_normalize_path( plugin_dir_path( __FILE__ ) ) );
}


class Ays_Autoloader {
    protected static $classes_map = [
        'Ays_CPT_FAQ'      => AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-faq.php',
        'Ays_CPT_Location' => AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-location.php',
        'Ays_CPT_Review'   => AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-review.php',
        'Ays_CPT_Service'  => AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-service.php',
        'Ays_CPT_Team'     => AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-team.php',
        'Ays_CPT_Lead'     => AYS_PLUGIN_PATH . 'includes/post-types/ays-cpt-lead.php',
    'Ays_Leads_Export_Page' => AYS_PLUGIN_PATH . 'admin/class-ays-leads-export.php',
    'Ays_Lead_Dashboard_Admin' => AYS_PLUGIN_PATH . 'admin/class-ays-lead-dashboard.php',
    'Ays_Lead_Notices' => AYS_PLUGIN_PATH . 'includes/admin/ays-lead-notices.php',
        // Taxonomies
        'Ays_Taxonomy_Service_Type'   => AYS_PLUGIN_PATH . 'includes/taxonomies/ays-taxonomy-service-type.php',
        'Ays_Taxonomy_Price_Range'    => AYS_PLUGIN_PATH . 'includes/taxonomies/ays-taxonomy-price-range.php',
        'Ays_Taxonomy_Neighbourhood'  => AYS_PLUGIN_PATH . 'includes/taxonomies/ays-taxonomy-neighbourhood.php',
        
        // Notifications
        'AYS_Notification_Router'   => AYS_PLUGIN_PATH . 'includes/notifications/AYS_Notification_Router.php',
        'AYS_Notifier'              => AYS_PLUGIN_PATH . 'includes/notifications/AYS_Notifier.php',
        'AYS_Notification_Settings' => AYS_PLUGIN_PATH . 'includes/notifications/AYS_Notification_Settings.php',
        'AYS_Notification_Logger'   => AYS_PLUGIN_PATH . 'includes/notifications/AYS_Notification_Logger.php',
        'AYS_Validation_Cron'       => AYS_PLUGIN_PATH . 'includes/notifications/AYS_Validation_Cron.php',
        'Mailer'                    => AYS_PLUGIN_PATH . 'includes/notifications/Mailer.php',
        'MailTransportFactory'      => AYS_PLUGIN_PATH . 'includes/notifications/transport/MailTransportFactory.php',
        'MailTransportInterface'    => AYS_PLUGIN_PATH . 'includes/notifications/transport/MailTransportInterface.php',
        'WPMailTransport'           => AYS_PLUGIN_PATH . 'includes/notifications/transport/WPMailTransport.php',
        'NullTransport'             => AYS_PLUGIN_PATH . 'includes/notifications/transport/NullTransport.php',
        'FileLoggingTransport'      => AYS_PLUGIN_PATH . 'includes/notifications/transport/FileLoggingTransport.php',
        
        // Adapter Interfaces (must load before implementing classes)
        'LeadSourceAdapterInterface' => AYS_PLUGIN_PATH . 'includes/notifications/adapters/LeadSourceAdapterInterface.php',
        
        // Adapters
        'LeadArrayAdapter'          => AYS_PLUGIN_PATH . 'includes/notifications/adapters/LeadArrayAdapter.php',
        'LeadCPTAdapter'            => AYS_PLUGIN_PATH . 'includes/notifications/adapters/LeadCPTAdapter.php',
        
        // Invoicing Module
        'AYS_Invoicing_Installer'   => AYS_PLUGIN_PATH . 'includes/invoices/ays-install-invoices.php',
        'AYS_Service_Type_Repository' => AYS_PLUGIN_PATH . 'includes/invoices/ays-class-service-type-repository.php',
        'AYS_Service_Type_Service'  => AYS_PLUGIN_PATH . 'includes/invoices/ays-class-service-type-service.php',
        'AYS_Invoice_Repository'    => AYS_PLUGIN_PATH . 'includes/invoices/ays-class-invoice-repository.php',
        'AYS_Invoice_Service'       => AYS_PLUGIN_PATH . 'includes/invoices/ays-class-invoice-service.php',
        'AYS_Client_Service'        => AYS_PLUGIN_PATH . 'includes/invoices/ays-class-client-service.php',
        'AYS_Item_Service'          => AYS_PLUGIN_PATH . 'includes/invoices/ays-class-item-service.php',
        'AYS_Payment_Service'       => AYS_PLUGIN_PATH . 'includes/invoices/ays-class-payment-service.php',
        'AYS_Invoice_Admin_UI'      => AYS_PLUGIN_PATH . 'includes/invoices/ays-class-invoice-admin-ui.php',
        'AYS_Items_Tab'             => AYS_PLUGIN_PATH . 'includes/invoices/ays-class-items-tab.php',
        'AYS_Invoice_Email'         => AYS_PLUGIN_PATH . 'includes/invoices/ays-class-invoice-email.php',
        'AYS_Invoice_PDF'           => AYS_PLUGIN_PATH . 'includes/invoices/ays-class-invoice-pdf.php'
    ];

    public static function autoload($class_name) {
        // Check if class is in our map first (bypass prefix checks for registered classes)
        if (array_key_exists($class_name, self::$classes_map)) {
            $file_path = wp_normalize_path(self::$classes_map[$class_name]);
            if (file_exists($file_path)) {
                require_once $file_path;
            }
            return;
        }
        
        // For unregistered classes, only allow certain prefixes to avoid class pollution
        // Allowed: Lead*, Mail*, Mailer, Null*, Ays_*, AYS_*, Invoice*
        if (strpos($class_name, 'Lead') === 0 || strpos($class_name, 'Mail') === 0 || 
            strpos($class_name, 'Mailer') === 0 || strpos($class_name, 'Null') === 0 ||
            strpos($class_name, 'Ays_') === 0 || strpos($class_name, 'AYS_') === 0 ||
            strpos($class_name, 'Invoice') === 0) {
            // Fall through
        } else {
            return;
        }
    }

    public static function register() {
        spl_autoload_register([__CLASS__, 'autoload']);
    }
}


// Register the autoloader for Ays_ classes
Ays_Autoloader::register();
// After the class definition
#var_dump(Ays_Autoloader::$classes_map);

