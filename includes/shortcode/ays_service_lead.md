<!-- <?php
/**
 *  ays service lead-landing
 *
 *  @package At Your Service
 * @since 1.3.0 
 * @author Shaun Palmer
 * @license GNU General Public License 2.0+
 */
// Include the enqueue.php file
#include_once plugin_dir_path( __FILE__ ) . 'admin/enqueue.php';

// Hooking into WordPress initialization
// function register_ays_service_form_shortcode() {

//       // Enqueue styles function
//     function ays_service_form_enqueue_styles() {
//         // Register the CSS file
//         wp_register_style(
//             'ays-lead-landing-css',
//             plugin_dir_url(__FILE__) . 'includes/shortcode/css/ays-lead-landing.css',
//             array(), // Dependencies
//             '1.0.0',  // Version
//             'all'     // Media
//         );
//         // Enqueue the CSS file
//         wp_enqueue_style('ays-lead-landing-css');
//     }
//     // Enqueue the stylesheet
//     ays_service_form_enqueue_styles();

// function ays_service_form_shortcode() {
//     ob_start();
//     ?>
//     <!-- <!DOCTYPE html>
//     <html lang="en"> -->
//     <head>
//         <meta charset="UTF-8" />
//         <meta name="viewport" content="width=device-width, initial-scale=1.0" />
//         <title>Service Booking Form</title>
//         <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
//     </head>
//     <body>
//         <div class="container-fluid">
//             <div class="container d-flex align-items-center" style="min-height: 100vh">
//                 <div class="row w-100 card-body">
//                     <!-- Left Column: Headline and Image -->
//                     <div class="col-md-6 d-flex flex-column justify-content-center">
//                         <h1>Christchurch top cleaning service<br>book online in minutes</h1>
//                         <picture>
//                             <source media="(min-width: 1200px)" srcset="<?php echo plugin_dir_url(__FILE__) . 'public/partials/Images/cleaning-service.png'; ?>" />
//                             <source media="(min-width: 768px)" srcset="<?php echo plugin_dir_url(__FILE__) . 'public/partials/Images/cleaning-service.png'; ?>" />
//                             <img src="<?php echo plugin_dir_url(__FILE__) . 'public/partials/Images/cleaning-service.png'; ?>" alt="At-Your-Service" class="img-fluid mb-3" />
//                         </picture>
//                         <h2>Effortless Cleaning, Just a Click Away</h2>
//                         <p>Book your cleaning today—carpet, windows, and more, done for you, while you enjoy a spotless home.</p>
//                     </div>
//                     <!-- Right Column: Form -->
//                     <div class="col-md-6 form-section">
//                         <h3>Let's talk about your service needs</h3>
//                         <form class="card-body">
//                             <div class="mb-3">
//                                 <label for="name" class="form-label">Name</label>
//                                 <input type="text" class="form-control" id="name" placeholder="Enter your name" />
//                             </div>
//                             <div class="mb-3">
//                                 <label for="email" class="form-label">Email</label>
//                                 <input type="email" class="form-control" id="email" placeholder="Enter Your E-mail" />
//                             </div>
//                             <div class="mb-3">
//                                 <label for="phone" class="form-label">Telephone Number</label>
//                                 <input type="tel" class="form-control" id="phone" placeholder="Enter your phone number" />
//                             </div>
//                             <button type="submit" class="btn btn-light mw-100">Book In</button>
//                         </form>
//                     </div>
//                 </div>
//             </div>
//         </div>
//         <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
//     </body>
//     </html>
//     <?php
//     return ob_get_clean();
// }
// add_shortcode('ays_service_form', 'ays_service_form_shortcode'); #<--this is line 66
// }

#add_action('init', 'register_ays_service_form_shortcode'); -->