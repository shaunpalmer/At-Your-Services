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
                echo esc_html( get_post_meta( $post_id, 'ays_email', true ) );
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
}

new Ays_CPT_Lead();
