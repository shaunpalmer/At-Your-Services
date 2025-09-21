<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Ays_Leads_Export_Page' ) ) :

class Ays_Leads_Export_Page {
    const SLUG = 'ays-leads-export';

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_page' ] );
        add_action( 'admin_post_ays_export_leads', [ $this, 'handle_export' ] );
    }

    public function register_page() {
        add_submenu_page(
            'edit.php?post_type=ays_lead',
            __( 'Export Leads', 'ays' ),
            __( 'Export', 'ays' ),
            'manage_options',
            self::SLUG,
            [ $this, 'render_page' ]
        );
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) { return; }
        $recent = get_posts([
            'post_type' => 'ays_lead',
            'posts_per_page' => 5,
            'post_status' => 'any',
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Export Leads', 'ays' ); ?></h1>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'ays_export_leads', 'ays_export_nonce' ); ?>
                <input type="hidden" name="action" value="ays_export_leads" />
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="ays_from"><?php esc_html_e( 'From Date', 'ays' ); ?></label></th>
                        <td><input type="date" id="ays_from" name="from" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ays_to"><?php esc_html_e( 'To Date', 'ays' ); ?></label></th>
                        <td><input type="date" id="ays_to" name="to" /></td>
                    </tr>
                </table>
                <?php submit_button( __( 'Download CSV', 'ays' ) ); ?>
            </form>

            <details class="ays-collapsible" style="margin-top:24px; border:1px solid #c3c4c7; border-radius:4px; background:#fff;">
                <summary style="padding:10px 14px; cursor:pointer; font-weight:600; background:#f6f7f7;"><?php esc_html_e('Recent Leads Preview','ays'); ?></summary>
                <div style="padding:12px 14px;">
                    <?php if ( empty( $recent ) ) : ?>
                        <p><?php esc_html_e('No leads found yet.','ays'); ?></p>
                    <?php else : ?>
                        <table class="widefat striped">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Name','ays'); ?></th>
                                    <th><?php esc_html_e('Email','ays'); ?></th>
                                    <th><?php esc_html_e('Phone','ays'); ?></th>
                                    <th><?php esc_html_e('Booking','ays'); ?></th>
                                    <th><?php esc_html_e('Actions','ays'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach( $recent as $post ) : ?>
                                <?php $email = get_post_meta($post->ID,'ays_email',true); $phone = get_post_meta($post->ID,'ays_phone',true); $bd = get_post_meta($post->ID,'ays_booking_date',true); $bt = get_post_meta($post->ID,'ays_booking_time',true); ?>
                                <tr>
                                    <td><a href="<?php echo esc_url( get_edit_post_link( $post->ID ) ); ?>"><?php echo esc_html( get_the_title( $post->ID ) ); ?></a></td>
                                    <td><?php echo $email ? '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' : '&mdash;'; ?></td>
                                    <td><?php echo $phone ? '<a href="tel:' . esc_attr( $phone ) . '">' . esc_html( $phone ) . '</a>' : '&mdash;'; ?></td>
                                    <td><?php echo esc_html( trim($bd . ' ' . $bt) ); ?></td>
                                    <td><a class="button button-small" href="<?php echo esc_url( get_edit_post_link( $post->ID ) ); ?>"><?php esc_html_e('View','ays'); ?></a> <a class="button button-small delete-lead" href="<?php echo esc_url( get_delete_post_link( $post->ID ) ); ?>" onclick="return confirm('<?php esc_attr_e('Delete this lead?','ays'); ?>');"><?php esc_html_e('Delete','ays'); ?></a></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </details>

            <details class="ays-collapsible" style="margin-top:18px; border:1px solid #c3c4c7; border-radius:4px; background:#fff;">
                <summary style="padding:10px 14px; cursor:pointer; font-weight:600; background:#f6f7f7;"><?php esc_html_e('Tips & Usage','ays'); ?></summary>
                <div style="padding:12px 14px; line-height:1.5;">
                    <p><?php esc_html_e('Use the date filters above to narrow export results. Leave both blank to export all leads.','ays'); ?></p>
                    <p><?php esc_html_e('Recent leads preview is a quick sanity check; full dataset comes from the CSV.','ays'); ?></p>
                    <p><?php esc_html_e('You can sort and further manipulate the CSV in Excel, Google Sheets, or any BI tool.','ays'); ?></p>
                </div>
            </details>
        </div>
        <?php
    }

    public function handle_export() {
        if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Denied' ); }
        if ( ! isset( $_POST['ays_export_nonce'] ) || ! wp_verify_nonce( $_POST['ays_export_nonce'], 'ays_export_leads' ) ) {
            wp_die( 'Invalid nonce' );
        }
        $from = isset( $_POST['from'] ) ? sanitize_text_field( wp_unslash( $_POST['from'] ) ) : '';
        $to   = isset( $_POST['to'] ) ? sanitize_text_field( wp_unslash( $_POST['to'] ) ) : '';

        $args = [
            'post_type'      => 'ays_lead',
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'orderby'        => 'date',
            'order'          => 'DESC',
            'date_query'     => [],
            'fields'         => 'ids'
        ];
        if ( $from ) { $args['date_query'][] = [ 'after' => $from ]; }
        if ( $to ) { $args['date_query'][] = [ 'before' => $to . ' 23:59:59' ]; }
        if ( empty( $args['date_query'] ) ) { unset( $args['date_query'] ); }

        $ids = get_posts( $args );

        nocache_headers();
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=ays-leads-' . date( 'Ymd-His' ) . '.csv' );

        $out = fopen( 'php://output', 'w' );
        fputcsv( $out, [ 'ID', 'Name', 'Email', 'Phone', 'Booking Date', 'Booking Time', 'Date Created' ] );

        foreach ( $ids as $id ) {
            fputcsv( $out, [
                $id,
                get_the_title( $id ),
                get_post_meta( $id, 'ays_email', true ),
                get_post_meta( $id, 'ays_phone', true ),
                get_post_meta( $id, 'ays_booking_date', true ),
                get_post_meta( $id, 'ays_booking_time', true ),
                get_post_field( 'post_date', $id ),
            ] );
        }
        fclose( $out );
        exit;
    }
}

endif; // class exists