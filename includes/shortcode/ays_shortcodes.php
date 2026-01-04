<?php
/*
 *  ays service lead-landing
 *
 *  @package At Your Service
 * @since 1.3.0
 * @author Shaun Palmer
 * @license GNU General Public License 2.0+
 */

/**
 * Shortcode function for the service lead generation form.
 * Displays a two-column layout with service information and a contact form.
 *
 * @return string HTML output for the shortcode
 */
function ays_service_form_shortcode() {
    ob_start();
    ?>
    <div class="ays-container-fluid">
        <div class="ays-container ays-lead-wrapper">
            <div class="ays-row ays-lead-content">
                <!-- Left Column: Headline and Image -->
                <div class="ays-col-half ays-lead-column">
                    <h1>Christchurch top cleaning service<br>book online in minutes</h1>
                    <picture>
                        <source media="(min-width: 1200px)" srcset="<?php echo esc_url( plugin_dir_url(__FILE__) . 'public/partials/Images/cleaning-service.png' ); ?>" />
                        <source media="(min-width: 768px)" srcset="<?php echo esc_url( plugin_dir_url(__FILE__) . 'public/partials/Images/cleaning-service.png' ); ?>" />
                        <img src="<?php echo esc_url( plugin_dir_url(__FILE__) . 'public/partials/Images/cleaning-service.png' ); ?>" alt="At-Your-Service" class="ays-lead-image" />
                    </picture>
                    <h2>Effortless Cleaning, Just a Click Away</h2>
                    <p>Book your cleaning today—carpet, windows, and more, done for you, while you enjoy a spotless home.</p>
                </div>
                <!-- Right Column: Form -->
                <div class="ays-col-half ays-lead-form-section">
                    <h3>Let's talk about your service needs</h3>
                    <form class="ays-card-body">
                        <div class="ays-form-group">
                            <label for="name" class="ays-form-label">Name</label>
                            <input type="text" class="ays-form-control" id="name" placeholder="Enter your name" />
                        </div>
                        <div class="ays-form-group">
                            <label for="email" class="ays-form-label">Email</label>
                            <input type="email" class="ays-form-control" id="email" placeholder="Enter Your E-mail" />
                        </div>
                        <div class="ays-form-group">
                            <label for="phone" class="ays-form-label">Telephone Number</label>
                            <input type="tel" class="ays-form-control" id="phone" placeholder="Enter your phone number" />
                        </div>
                        <button type="submit" class="ays-btn ays-btn-primary">Book In</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('ays_service_form', 'ays_service_form_shortcode');
