<?php
/*
 *  ays service lead-landing
 *
 *  @package At Your Service
 * @since 1.3.0
 * @author Shaun Palmer
 * @license GNU General Public License 2.0+
 */





/* Lead form shortcode with submission handling 
We're gonna refactor the following function for outputting the dashboard for customising the left hand panel it needs to it can't be just the way it is the phone looks terrible there's no control from the user bad user experience and so this whole function below called ays leave short form yes it still needs to be two column but the first column needs to be customizable*/
function ays_lead_form_shortcode( $atts = [] ) {
    $saved = get_option( 'ays_lead_form_options', [] );
    // Provide safe defaults if options are blank
    $default_headline = __( 'Christchurch top cleaning service', 'ays' );
    $default_subhead  = __( 'Book online in minutes', 'ays' );
    $headline = isset( $saved['headline'] ) && trim( $saved['headline'] ) !== '' ? $saved['headline'] : $default_headline;
    $subhead  = isset( $saved['subhead'] ) && trim( $saved['subhead'] ) !== '' ? $saved['subhead'] : $default_subhead;
    $extra_html = isset( $saved['extra_html'] ) && trim( $saved['extra_html'] ) !== '' ? $saved['extra_html'] : '';
    $left_bg = isset( $saved['left_bg'] ) && $saved['left_bg'] ? $saved['left_bg'] : '#ffffff';
    $right_bg = isset( $saved['right_bg'] ) && $saved['right_bg'] ? $saved['right_bg'] : '#ffffff';
    $field_bg = isset( $saved['field_bg'] ) && $saved['field_bg'] ? $saved['field_bg'] : '';
    $show_notes = isset( $saved['show_notes'] ) ? (int) $saved['show_notes'] : 1;
    // Allow shortcode attribute overrides
    $atts = shortcode_atts( [
        'headline' => $headline,
        'subhead'  => $subhead,
    ], $atts, 'ays_lead_form' );

    $errors = [];
    $success = false;
    if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['ays_lead_form_submitted'] ) ) {
        // Basic honeypot
        if ( ! empty( $_POST['ays_hp'] ) ) {
            return '<div class="alert alert-warning">' . esc_html__( 'Submission blocked.', 'ays' ) . '</div>';
        }
        if ( ! isset( $_POST['ays_lead_nonce'] ) || ! wp_verify_nonce( $_POST['ays_lead_nonce'], 'ays_lead_submit' ) ) {
            $errors[] = __( 'Security check failed. Please refresh and try again.', 'ays' );
        } else {
            $name  = isset( $_POST['ays_name'] ) ? sanitize_text_field( wp_unslash( $_POST['ays_name'] ) ) : '';
            $email = isset( $_POST['ays_email'] ) ? sanitize_email( wp_unslash( $_POST['ays_email'] ) ) : '';
            $phone = isset( $_POST['ays_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['ays_phone'] ) ) : '';
            $notes = isset( $_POST['ays_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['ays_notes'] ) ) : '';
            $booking_date = isset( $_POST['ays_booking_date'] ) ? sanitize_text_field( wp_unslash( $_POST['ays_booking_date'] ) ) : '';
            $booking_time = isset( $_POST['ays_booking_time'] ) ? sanitize_text_field( wp_unslash( $_POST['ays_booking_time'] ) ) : '';

            if ( empty( $name ) ) { $errors[] = __( 'Name is required.', 'ays' ); }
            if ( empty( $email ) || ! is_email( $email ) ) { $errors[] = __( 'Valid email required.', 'ays' ); }
            if ( empty( $phone ) ) { $errors[] = __( 'Phone is required.', 'ays' ); }

            if ( empty( $errors ) ) {
                $post_id = wp_insert_post( [
                    'post_type'   => 'ays_lead',
                    'post_status' => 'publish',
                    'post_title'  => $name . ' - ' . current_time( 'mysql' ),
                    'meta_input'  => [
                        'ays_email' => $email,
                        'ays_phone' => $phone,
                        'ays_notes' => $notes,
                        'ays_booking_date' => $booking_date,
                        'ays_booking_time' => $booking_time,
                    ],
                ], true );

                if ( is_wp_error( $post_id ) ) {
                    $errors[] = __( 'Could not save lead. Please try again later.', 'ays' );
                } else {
                    $success = true;
                }
            }
        }
    }

    ob_start();
    ?>
    <div class="ays-lead-wrapper" style="display:flex;flex-wrap:wrap;gap:32px;align-items:stretch;">
        <?php if ( $success ) : ?>
            <div class="alert alert-success" role="alert"><?php echo esc_html__( 'Thanks! Your request has been received. We will be in touch shortly.', 'ays' ); ?></div>
        <?php else : ?>
            <?php if ( ! empty( $errors ) ) : ?>
                <div class="alert alert-danger" role="alert">
                    <?php foreach ( $errors as $err ) { echo '<div>' . esc_html( $err ) . '</div>'; } ?>
                </div>
            <?php endif; ?>
            <div class="ays-left-panel" style="flex:1;min-width:280px;background: <?php echo esc_attr( $left_bg ); ?>; padding:24px; border-radius:6px;display:flex;flex-direction:column;justify-content:center;">
                <h2 style="margin:0 0 8px;">&nbsp;<?php echo esc_html( $atts['headline'] ); ?></h2>
                <p style="margin:0 0 12px;font-size:90%;opacity:.85;"><?php echo esc_html( $atts['subhead'] ); ?></p>
                <div class="ays-extra-content" style="margin-top:8px;">
                    <?php if ( $extra_html ) : echo wp_kses_post( $extra_html ); else : ?>
                        <p><?php esc_html_e( 'Book your service today—carpet, windows, and more, done for you while you enjoy a spotless home.', 'ays' ); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="ays-right-panel" style="flex:1;min-width:300px;background: <?php echo esc_attr( $right_bg ); ?>; padding:24px; border-radius:6px;">
                    <form method="post" class="ays-lead-form" novalidate>
                        <?php wp_nonce_field( 'ays_lead_submit', 'ays_lead_nonce' ); ?>
                        <input type="hidden" name="ays_lead_form_submitted" value="1" />
                        <input type="text" name="ays_hp" value="" style="display:none" tabindex="-1" autocomplete="off" />
                        <div class="mb-3">
                            <label class="form-label" for="ays_name"><?php esc_html_e( 'Name', 'ays' ); ?> *</label>
                            <input class="form-control" type="text" id="ays_name" name="ays_name" required value="<?php echo isset( $name ) ? esc_attr( $name ) : ''; ?>" <?php echo $field_bg ? 'style="background:' . esc_attr( $field_bg ) . '"' : ''; ?> />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="ays_email"><?php esc_html_e( 'Email', 'ays' ); ?> *</label>
                            <input class="form-control" type="email" id="ays_email" name="ays_email" required value="<?php echo isset( $email ) ? esc_attr( $email ) : ''; ?>" <?php echo $field_bg ? 'style="background:' . esc_attr( $field_bg ) . '"' : ''; ?> />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="ays_phone"><?php esc_html_e( 'Phone', 'ays' ); ?> *</label>
                            <input class="form-control" type="tel" id="ays_phone" name="ays_phone" required value="<?php echo isset( $phone ) ? esc_attr( $phone ) : ''; ?>" <?php echo $field_bg ? 'style="background:' . esc_attr( $field_bg ) . '"' : ''; ?> />
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="ays_booking_date"><?php esc_html_e( 'Preferred Date', 'ays' ); ?></label>
                                <input class="form-control ays-date" type="text" id="ays_booking_date" name="ays_booking_date" placeholder="YYYY-MM-DD" value="<?php echo isset( $booking_date ) ? esc_attr( $booking_date ) : ''; ?>" <?php echo $field_bg ? 'style="background:' . esc_attr( $field_bg ) . '"' : ''; ?> />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="ays_booking_time"><?php esc_html_e( 'Preferred Time', 'ays' ); ?></label>
                                <input class="form-control ays-time" type="text" id="ays_booking_time" name="ays_booking_time" placeholder="HH:MM" value="<?php echo isset( $booking_time ) ? esc_attr( $booking_time ) : ''; ?>" <?php echo $field_bg ? 'style="background:' . esc_attr( $field_bg ) . '"' : ''; ?> />
                            </div>
                        </div>
                        <?php if ( $show_notes ) : ?>
                            <div class="mb-3">
                                <label class="form-label" for="ays_notes"><?php esc_html_e( 'Notes', 'ays' ); ?></label>
                                <textarea class="form-control" id="ays_notes" name="ays_notes" rows="4" <?php echo $field_bg ? 'style="background:' . esc_attr( $field_bg ) . '"' : ''; ?>><?php echo isset( $notes ) ? esc_textarea( $notes ) : ''; ?></textarea>
                            </div>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary" style="width:100%;max-width:100%;"><?php esc_html_e( 'Send Request', 'ays' ); ?></button>
                    </form>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'ays_lead_form', 'ays_lead_form_shortcode' );

// Backward compatibility old shortcode -> new
function ays_service_form_legacy() { return ays_lead_form_shortcode(); }
add_shortcode( 'ays_service_form', 'ays_service_form_legacy' );
