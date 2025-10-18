<?php
/**
 * Lead Custom Post Type
 * Stores lead submissions from front-end forms.
 *
 * @package AtYourService
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Ays_CPT_Lead {
    const POST_TYPE = 'ays_lead';

    public function __construct() {
        add_action( 'init', [ $this, 'register_cpt' ] );
        add_filter( 'manage_edit-' . self::POST_TYPE . '_columns', [ $this, 'columns' ] );
        add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', [ $this, 'column_content' ], 10, 2 );

        // Bulk actions for email verification
        add_filter( 'bulk_actions-edit-' . self::POST_TYPE, [ $this, 'add_bulk_action' ] );
        add_filter( 'handle_bulk_actions-edit-' . self::POST_TYPE, [ $this, 'handle_bulk_action' ], 10, 3 );
        add_action( 'admin_notices', [ $this, 'show_bulk_action_notice' ] );
    }

    public function register_cpt() {
        $labels = [
            'name'               => __( 'Leads', 'ays' ),
            'singular_name'      => __( 'Lead', 'ays' ),
            'add_new'            => __( 'Add New', 'ays' ),
            'add_new_item'       => __( 'Add New Lead', 'ays' ),
            'edit_item'          => __( 'Edit Lead', 'ays' ),
            'new_item'           => __( 'New Lead', 'ays' ),
            'view_item'          => __( 'View Lead', 'ays' ),
            'search_items'       => __( 'Search Leads', 'ays' ),
            'not_found'          => __( 'No leads found', 'ays' ),
            'not_found_in_trash' => __( 'No leads found in Trash', 'ays' ),
            'menu_name'          => __( 'Leads', 'ays' ),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'menu_position'      => 25,
            'menu_icon'          => 'dashicons-groups',
            'supports'           => [ 'title', 'custom-fields' ],
            'capability_type'    => 'post',
            'has_archive'        => false,
        ];
        register_post_type( self::POST_TYPE, $args );
    }

    public function columns( $cols ) {
        $new = [];
        $new['cb']      = isset( $cols['cb'] ) ? $cols['cb'] : '';
        $new['title']    = __( 'Lead Name', 'ays' );
        $new['email']    = __( 'Email', 'ays' );
        $new['phone']    = __( 'Phone', 'ays' );
        $new['booking']  = __( 'Booking', 'ays' );
        $new['date']     = isset( $cols['date'] ) ? $cols['date'] : __( 'Date', 'ays' );
        return $new;
    }

    public function column_content( $column, $post_id ) {
        switch ( $column ) {
            case 'email':
                $email = get_post_meta( $post_id, 'ays_email', true );
                $status = get_post_meta( $post_id, '_ays_email_validation_status', true );
                $status_icon = '';
                switch ($status) {
                    case 'valid':
                        $status_icon = '<span class="dashicons dashicons-yes-alt" style="color: #46b450;" title="Verified"></span>';
                        break;
                    case 'risky':
                        $status_icon = '<span class="dashicons dashicons-warning" style="color: #ffb900;" title="Risky"></span>';
                        break;
                    case 'invalid':
                        $status_icon = '<span class="dashicons dashicons-dismiss" style="color: #dc3232;" title="Invalid"></span>';
                        break;
                    default:
                        $status_icon = '<span class="dashicons dashicons-hourglass" style="color: #999;" title="Unverified"></span>';
                        break;
                }
                echo esc_html( $email ) . ' ' . $status_icon;
                break;
            case 'phone':
                echo esc_html( get_post_meta( $post_id, 'ays_phone', true ) );
                break;
            case 'booking':
                $d = get_post_meta( $post_id, 'ays_booking_date', true );
                $t = get_post_meta( $post_id, 'ays_booking_time', true );
                echo esc_html( trim( $d . ' ' . $t ) );
                break;
        }
    }

    /**
     * Add "Verify Emails" to the bulk actions dropdown.
     */
    public function add_bulk_action( $bulk_actions ) {
        $bulk_actions['ays_verify_emails'] = __( 'Verify Emails', 'ays' );
        return $bulk_actions;
    }

    /**
     * Handle the "Verify Emails" bulk action.
     */
    public function handle_bulk_action( $redirect_to, $action_name, $post_ids ) {
        if ( 'ays_verify_emails' !== $action_name ) {
            return $redirect_to;
        }

        $processed_count = 0;
        foreach ( $post_ids as $post_id ) {
            if ( class_exists( 'AYS_Validation_Cron' ) ) {
                AYS_Validation_Cron::schedule( $post_id );
                $processed_count++;
            }
        }

        $redirect_to = add_query_arg( 'ays_bulk_verified', $processed_count, $redirect_to );
        return $redirect_to;
    }

    /**
     * Show a notice after the bulk action has been processed.
     */
    public function show_bulk_action_notice() {
        if ( ! empty( $_REQUEST['ays_bulk_verified'] ) ) {
            $count = intval( $_REQUEST['ays_bulk_verified'] );
            printf( '<div class="notice notice-success is-dismissible"><p>' .
                esc_html( _n(
                    '%s email has been queued for verification.',
                    '%s emails have been queued for verification.',
                    $count,
                    'ays'
                ) ) .
                '</p></div>', $count );
        }
    }
}
