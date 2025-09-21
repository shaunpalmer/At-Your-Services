<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Ays_Lead_Dashboard_Admin' ) ) :

class Ays_Lead_Dashboard_Admin {
    const OPTION_KEY = 'ays_lead_form_options';

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_submenu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_assets' ] );
        add_action( 'current_screen', [ $this, 'add_help_tabs' ] );
    }

    public function add_submenu() {
        add_submenu_page(
            'edit.php?post_type=ays_lead',
            __( 'Lead Form Dashboard', 'ays' ),
            __( 'Dashboard', 'ays' ),
            'manage_options',
            'ays-lead-dashboard',
            [ $this, 'render_page' ]
        );
    }

    public function register_settings() {
        register_setting( 'ays_lead_dashboard', self::OPTION_KEY, [ $this, 'sanitize' ] );

        add_settings_section(
            'ays_lead_dashboard_section',
            __( 'Lead Form Customization', 'ays' ),
            '__return_false',
            'ays_lead_dashboard'
        );

        add_settings_field( 'headline', __( 'Headline', 'ays' ), [ $this, 'field_headline' ], 'ays_lead_dashboard', 'ays_lead_dashboard_section' );
        add_settings_field( 'subhead', __( 'Subhead', 'ays' ), [ $this, 'field_subhead' ], 'ays_lead_dashboard', 'ays_lead_dashboard_section' );
        add_settings_field( 'extra_html', __( 'Extra Content', 'ays' ), [ $this, 'field_extra' ], 'ays_lead_dashboard', 'ays_lead_dashboard_section' );
        add_settings_field( 'left_bg', __( 'Left Panel Background', 'ays' ), [ $this, 'field_left_bg' ], 'ays_lead_dashboard', 'ays_lead_dashboard_section' );
        add_settings_field( 'show_notes', __( 'Show Notes Field', 'ays' ), [ $this, 'field_show_notes' ], 'ays_lead_dashboard', 'ays_lead_dashboard_section' );
    add_settings_field( 'right_bg', __( 'Right Panel Background', 'ays' ), [ $this, 'field_right_bg' ], 'ays_lead_dashboard', 'ays_lead_dashboard_section' );
        add_settings_field( 'field_bg', __( 'Form Field Background', 'ays' ), [ $this, 'field_field_bg' ], 'ays_lead_dashboard', 'ays_lead_dashboard_section' );
    }

    /**
     * Enqueue lightweight admin styles + JS only on our dashboard page.
     */
    public function admin_assets( $hook ) {
        // $hook example: ays_lead_page_ays-lead-dashboard
        if ( strpos( $hook, 'ays-lead-dashboard' ) === false ) {
            return;
        }
        // Core color picker assets
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        $css = '/* AYS Dashboard polish */
        .ays-panel{background:#f9f9f9;border:1px solid #dcdcde;border-radius:6px;margin:0 0 18px;padding:16px 18px;}
        .ays-flex-preview{display:flex;gap:24px;align-items:flex-start;flex-wrap:wrap;margin-top:8px}
        .ays-flex-preview .ays-prev-left{flex:1;min-width:260px;border:1px solid #e2e4e7;background:var(--ays-prev-bg,#ffffff);padding:18px;border-radius:6px}
        .ays-flex-preview .ays-prev-right{flex:1;min-width:260px}
        .ays-prev-left h2{margin-top:0;margin-bottom:8px;}
        .ays-details summary{padding:12px 16px;font-weight:600;cursor:pointer;}
        .ays-details{background:#fff;border:1px solid #c3c4c7;border-radius:6px;margin-bottom:14px}
        .ays-details[open]{box-shadow:0 0 0 2px #2271b1 inset;border-color:#2271b1}
        .ays-details > div{padding:16px 20px;border-top:1px solid #c3c4c7}
        .ays-inline-mono{font-family:monospace;font-size:12px;background:#fff;padding:2px 4px;border-radius:3px;border:1px solid #dcdcde}
        .ays-preview-heading{margin:18px 0 6px;font-size:16px;font-weight:600}
        .ays-live-badge{display:inline-block;background:#2271b1;color:#fff;font-size:11px;padding:2px 6px;border-radius:3px;margin-left:6px;vertical-align:middle;letter-spacing:.5px}
        ';        
        wp_add_inline_style( 'wp-admin', $css );
    $js = "(function($){function sync(){var h=$('#ays_headline').val()||'Christchurch top cleaning service';var s=$('#ays_subhead').val()||'Book online in minutes';var extra=(window.tinymce && tinymce.get('ays_lead_form_options_extra_html'))?tinymce.get('ays_lead_form_options_extra_html').getContent():$('#ays_lead_form_options_extra_html').val();var leftBg=$('#ays_left_bg').val()||'#ffffff';var rightBg=$('#ays_right_bg').val()||'#ffffff';var fieldBg=$('#ays_field_bg').val()||'#ffffff';var showNotes=$('#ays_show_notes').is(':checked');var fallbackExtra='<p>Book your service today—carpet, windows, and more.</p>';
        $('.ays-prev-left').css('--ays-prev-bg',leftBg).find('[data-prev=headline]').text(h);$('.ays-prev-left [data-prev=subhead]').text(s);var extraWrap=$('.ays-prev-left [data-prev=extra]');extraWrap.html(extra?extra:fallbackExtra);
        $('.ays-preview-left').css('background-color', leftBg);$('.ays-preview-right').css('background-color', rightBg);$('.ays-preview-right input, .ays-preview-right textarea').css('background-color', fieldBg);$('.ays-preview-headline').text(h);$('.ays-preview-subhead').text(s);$('.ays-preview-extra').html(extra?extra:fallbackExtra);$('.ays-preview-notes').toggle(showNotes);}function copyShortcode(){var sc='[ays_lead_form]'; if(navigator.clipboard){navigator.clipboard.writeText(sc).then(showCopied,showCopied);} else {var ta=$('<textarea>').val(sc).appendTo('body').select();try{document.execCommand('copy');}catch(e){}ta.remove();showCopied();}}function showCopied(){var fb=$('#ays-copy-feedback');fb.stop(true,true).fadeIn(120);setTimeout(function(){fb.fadeOut(300);},1800);}$(document).on('click','#ays-copy-shortcode',function(e){e.preventDefault();copyShortcode();});$(document).on('input change','#ays_headline,#ays_subhead,#ays_left_bg,#ays_right_bg,#ays_field_bg,#ays_show_notes',sync);document.addEventListener('tinymce-editor-init',function(e){if(e.editor.id==='ays_lead_form_options_extra_html'){e.editor.on('keyup change',sync);}});$(document).ready(function(){ if($('#ays_left_bg').length){ $('#ays_left_bg').wpColorPicker({ change:function(){ sync(); }, clear:function(){ sync(); } }); } if($('#ays_right_bg').length){ $('#ays_right_bg').wpColorPicker({ change:function(){ sync(); }, clear:function(){ sync(); } }); } if($('#ays_field_bg').length){ $('#ays_field_bg').wpColorPicker({ change:function(){ sync(); }, clear:function(){ sync(); } }); } sync(); });})(jQuery);";
        wp_add_inline_script( 'jquery-core', $js );
    }

    public function add_help_tabs( $screen ) {
        if ( ! isset( $screen->id ) || strpos( $screen->id, 'ays-lead-dashboard' ) === false ) {
            return;
        }
        $screen->add_help_tab( [
            'id'      => 'ays_headline_help',
            'title'   => __( 'Headline Tips', 'ays' ),
            'content' => '<p>' . esc_html__( 'Keep it clear and outcome-focused, e.g., “Sparkling Home Cleaning in 60 Seconds.”', 'ays' ) . '</p>' . '<p><code class="ays-inline-mono">[ays_lead_form]</code> ' . esc_html__( 'will reflect changes instantly in the preview.', 'ays' ) . '</p>',
        ] );
        $screen->add_help_tab( [
            'id'      => 'ays_extra_help',
            'title'   => __( 'Extra Content Ideas', 'ays' ),
            'content' => '<p>' . esc_html__( 'Use bullet lists, trust badges, or a short guarantee. Avoid overly long paragraphs.', 'ays' ) . '</p>',
        ] );
    }

    public function sanitize( $input ) {
        $out = [];
        $out['headline'] = isset( $input['headline'] ) ? sanitize_text_field( $input['headline'] ) : '';
        $out['subhead']  = isset( $input['subhead'] ) ? sanitize_text_field( $input['subhead'] ) : '';
        $out['extra_html'] = isset( $input['extra_html'] ) ? wp_kses_post( $input['extra_html'] ) : '';
        $out['left_bg'] = isset( $input['left_bg'] ) ? sanitize_hex_color( $input['left_bg'] ) : '';
        $out['right_bg'] = isset( $input['right_bg'] ) ? sanitize_hex_color( $input['right_bg'] ) : '';
        $out['field_bg'] = isset( $input['field_bg'] ) ? sanitize_hex_color( $input['field_bg'] ) : '';
        $out['show_notes'] = ! empty( $input['show_notes'] ) ? 1 : 0;
        return $out;
    }

    protected function get_option( $key, $default = '' ) {
        $opts = get_option( self::OPTION_KEY, [] );
        return isset( $opts[ $key ] ) ? $opts[ $key ] : $default;
    }

    public function field_headline() {
        $val = $this->get_option('headline');
        printf( '<input type="text" id="ays_headline" class="regular-text" name="%1$s[headline]" value="%2$s" placeholder="%3$s" />', self::OPTION_KEY, esc_attr( $val ), esc_attr__( 'e.g. Premium Home Cleaning', 'ays' ) );
    }

    public function field_subhead() {
        $val = $this->get_option('subhead');
        printf( '<input type="text" id="ays_subhead" class="regular-text" name="%1$s[subhead]" value="%2$s" placeholder="%3$s" />', self::OPTION_KEY, esc_attr( $val ), esc_attr__( 'Fast, insured & reliable', 'ays' ) );
    }

    public function field_extra() {
        $content = $this->get_option('extra_html');
        wp_editor( $content, 'ays_lead_form_options_extra_html', [
            'textarea_name' => self::OPTION_KEY . '[extra_html]',
            'media_buttons' => true,
            'textarea_rows' => 6,
        ] );
    }

    public function field_left_bg() {
        $val = $this->get_option('left_bg', '#ffffff');
        printf( '<input type="text" id="ays_left_bg" class="ays-color-field" name="%1$s[left_bg]" value="%2$s" data-default-color="#ffffff" />', self::OPTION_KEY, esc_attr( $val ) );
        echo '<p class="description">' . esc_html__( 'Background color for the left descriptive panel.', 'ays' ) . '</p>';
    }

    public function field_right_bg() {
        $val = $this->get_option('right_bg', '#ffffff');
        printf( '<input type="text" id="ays_right_bg" class="ays-color-field" name="%1$s[right_bg]" value="%2$s" data-default-color="#ffffff" />', self::OPTION_KEY, esc_attr( $val ) );
        echo '<p class="description">' . esc_html__( 'Background color for the right form panel.', 'ays' ) . '</p>';
    }

    public function field_field_bg() {
        $val = $this->get_option('field_bg', '#ffffff');
        printf( '<input type="text" id="ays_field_bg" class="ays-color-field" name="%1$s[field_bg]" value="%2$s" data-default-color="#ffffff" />', self::OPTION_KEY, esc_attr( $val ) );
        echo '<p class="description">' . esc_html__( 'Background color used for the individual form input & textarea fields.', 'ays' ) . '</p>';
    }

    public function field_show_notes() {
        $checked = $this->get_option('show_notes', 1 ) ? 'checked' : '';
        printf( '<label><input type="checkbox" id="ays_show_notes" name="%1$s[show_notes]" value="1" %2$s /> %3$s</label>', self::OPTION_KEY, $checked, esc_html__( 'Display the notes textarea field', 'ays' ) );
    }

    public function render_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Lead Form Dashboard', 'ays' ); ?></h1>
            <p class="description"><?php esc_html_e( 'Tweak the left panel content & style for the front-end lead form. Live preview updates instantly.', 'ays' ); ?></p>
            <form action="options.php" method="post" id="ays-lead-dashboard-form">
                <?php settings_fields( 'ays_lead_dashboard' ); ?>
                <details class="ays-details" open>
                    <summary><?php esc_html_e('Headline & Subhead','ays'); ?> <span class="ays-live-badge">LIVE</span></summary>
                    <div>
                        <?php $this->field_headline(); ?>
                        <p class="description" style="margin-top:4px;"><strong><?php esc_html_e('Headline','ays'); ?></strong>: <?php esc_html_e('Primary attention grabber e.g. “Christchurch Top Cleaning Service”.', 'ays'); ?></p>
                        <hr />
                        <?php $this->field_subhead(); ?>
                        <p class="description" style="margin-top:4px;"><strong><?php esc_html_e('Subhead','ays'); ?></strong>: <?php esc_html_e('Short supporting promise e.g. “Book online in minutes”.', 'ays'); ?></p>
                    </div>
                </details>
                <details class="ays-details">
                    <summary><?php esc_html_e('Extra Content (WYSIWYG)','ays'); ?></summary>
                    <div>
                        <?php $this->field_extra(); ?>
                        <p class="description" style="margin-top:8px;"><?php esc_html_e('Add trust badges, bullet benefits, or a short guarantee.', 'ays'); ?></p>
                    </div>
                </details>
                <details class="ays-details">
                    <summary><?php esc_html_e('Styling & Field Toggles','ays'); ?></summary>
                    <div>
                        <table class="form-table" role="presentation"><tbody>
                            <tr><th scope="row"><?php esc_html_e('Left Panel Background','ays'); ?></th><td><?php $this->field_left_bg(); ?></td></tr>
                            <tr><th scope="row"><?php esc_html_e('Show Notes Field','ays'); ?></th><td><?php $this->field_show_notes(); ?></td></tr>
                            <tr><th scope="row"><?php esc_html_e('Right Panel Background','ays'); ?></th><td><?php $this->field_right_bg(); ?></td></tr>
                            <tr><th scope="row"><?php esc_html_e('Form Field Background','ays'); ?></th><td><?php $this->field_field_bg(); ?></td></tr>
                        </tbody></table>
                    </div>
                </details>
                <?php submit_button(); ?>
            </form>
            <div class="ays-panel">
                <div class="ays-preview-heading"><?php esc_html_e('Live Preview','ays'); ?> <span class="ays-live-badge">LIVE</span></div>
                <div class="ays-flex-preview">
                    <div class="ays-prev-left ays-preview-left">
                        <h2 data-prev="headline" class="ays-preview-headline">Christchurch top cleaning service</h2>
                        <p data-prev="subhead" class="ays-preview-subhead">Book online in minutes</p>
                        <div data-prev="extra" class="ays-preview-extra"><p><?php esc_html_e('Book your service today—carpet, windows, and more, done for you while you enjoy a spotless home.', 'ays'); ?></p></div>
                    </div>
                    <div class="ays-prev-right ays-preview-right">
                        <p style="margin-top:0;font-weight:600;"><?php esc_html_e('Form (simplified preview)','ays'); ?></p>
                        <p><label><?php esc_html_e('Name','ays'); ?> *</label><br /><input type="text" style="width:100%;max-width:280px;" disabled /></p>
                        <p><label><?php esc_html_e('Email','ays'); ?> *</label><br /><input type="email" style="width:100%;max-width:280px;" disabled /></p>
                        <p><label><?php esc_html_e('Phone','ays'); ?> *</label><br /><input type="tel" style="width:100%;max-width:280px;" disabled /></p>
                        <p class="ays-preview-notes"><label><?php esc_html_e('Notes','ays'); ?></label><br /><textarea style="width:100%;max-width:280px;" rows="3" disabled></textarea></p>
                    </div>
                </div>
                <p class="description" style="margin-top:14px;display:flex;flex-wrap:wrap;align-items:center;gap:8px;">
                    <span><?php esc_html_e('Preview mirrors front-end two-column layout. Save changes then view any page using the shortcode:', 'ays'); ?></span>
                    <code class="ays-inline-mono" id="ays-shortcode-text">[ays_lead_form]</code>
                    <button type="button" class="button" id="ays-copy-shortcode" aria-label="<?php esc_attr_e('Copy shortcode to clipboard','ays'); ?>"><?php esc_html_e('Copy','ays'); ?></button>
                    <span id="ays-copy-feedback" style="display:none;color:#2271b1;font-weight:600;"><?php esc_html_e('Copied!','ays'); ?></span>
                </p>
            </div>
        </div>
        <?php
    }
}

endif; // class exists
