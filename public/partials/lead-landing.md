<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Two Column Layout with Form</title>
    <!-- Bootstrap CSS CDN -->
    <!-- Let’s boldly code where no developer has coded before! 🖖 -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <style>
    :root {
  --primary-color: #007bff; /* Default Bootstrap primary color */
  --secondary-color: #dadcdd; /* Default Bootstrap secondary color */
  --Text-color: #000000; /* Default Bootstrap Text-color */
  --background-color: #ffffff; /* Default white background */
  --text-decoration: var(--has-link-decoration, none);
  --list-style-type: none; /* Custom list style type */
  --spacing-xs: 0.3125rem; /* 5px */
  --spacing-sm: 0.625rem;   /* 10px */
  --spacing-md: 1.25rem;    /* 20px */
  --spacing-lg: 1.875rem;   /* 30px */
}
/* Global box-sizing reset */
*, *::before, *::after {
  box-sizing: border-box;
}
body {
        background-color: var(--background-color);
      }

      .container {
       /* padding: 40px;  White space around the content */
        padding: var(--spacing-lg); /* Uses --spacing-lg which is 1.875rem */
        
      }
      
      h2 {
        color: var(--primary-color);
      font-size: 1.75rem;
      margin-bottom: 1.25rem; /* Converted from 20px */
      }

p {
    color: var(--Text-color);
    font-size: 1rem;
    margin-bottom: 1.875rem; /* Converted from 30px */
     /* Clamp paragraph text to 6 lines */
    display: -webkit-box;
    -webkit-line-clamp: 6;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
  }

      /* Restrict image size to 350px max width */
      picture img {
      max-width: 21.875rem; /* Converted from 350px */
        width: 100%;
        height: auto;
      }

ul, ol {
  list-style-type: var(--list-style-type);
padding: 0.3125rem; /* Equivalent to 5px on all sides */
}
      .form-section {
        background-color: var(--secondary-color);
      padding: 1.5625rem; /* Increased padding for extra space */
      /*  border-radius: 8px;  Soft corners for the form section */
      border-radius: 0.5rem; /* Soft corners for the form section 8px */
      color: var(--Text-color);
      }

      /* Responsive text with media queries */
      @media (min-width: 768px) {
        h2 {
          font-size: 2rem;
        }
        p {
          font-size: 1.1rem;
        }
      }

      @media (min-width: 1200px) {
        h2 {
          font-size: 2.5rem;
        }
        p {
          font-size: 1.25rem;
        }
      }
    </style>
  </head>
  <body>
    <div class="container-fluid">
    <div class="container d-flex align-items-center" style="min-height: 100vh">
      <div class="row w-100 card-body">
        <!-- Left Column: Headline and Image -->
        <div class="col-md-6 d-flex flex-column justify-content-center">
          <!-- Cleaning, solved. -->
          <h1>Christchurch top cleaning service
            book online in minutes</h1>
            <!--Placeholders for PHP dynamic headlines, The pitch, The Hook -->
            
<picture>
    <source media="(min-width: 1200px)" srcset="<?php echo plugin_dir_url(__FILE__) . 'public/partials/Images/cleaning-service.png'; ?>" />
    <source media="(min-width: 768px)" srcset="<?php echo plugin_dir_url(__FILE__) . 'public/partials/Images/cleaning-service.png'; ?>" />
    <img src="<?php echo require_once AYS_PLUGIN_PATH . 'public/partials/Images/cleaning-service.png'; ?>" alt="At-Your-Service" class="img-fluid mb-3" />
</picture>
<h2>Effortless Cleaning, Just a Click Away</h2>
<!--Placeholders for PHP dynamic, Replace it with your town or city -->
<p> <!--Restrict to four lines with Css's clamp-->
            <!--Placeholders for PHP dynamic for The pitch, The Hook -->
            For convenience lovers, book your cleaning online today—carpet, windows,
            and more done for you, while you enjoy a spotless home without lifting a finger
            Carpet, windows, and more—cleaning experts at your service.
            Book your clean online today.We’ll handle the mess, you enjoy the rest.
</p>
        </div>

        <!-- Right Column: Form -->
        <div class="col-md-6 form-section ">
          <h3 class="card-text w-auto">Let's talk about your Service needs</h3> <!--Placeholders for PHP dynamic headlines -->
          <form class="card-body">
            <div class="mb-3">
              <label for="name" class="form-label">Name</label>
              <input
                type="text"
                class="form-control"
                id="name"
                placeholder="Enter your name"
              />
            </div>
            <div class="mb-3">
              <label for="email">Email:</label>
              <input type="email"
              class="form-control"
              id="email"
              name="email" 
              placeholder="Enter Your E-mail "/>
              </div>
            <div class="mb-3">
              <label for="phone" class="form-label">Telephone Number</label>
              <input
                type="tel"
                class="form-control"
                id="phone"
                placeholder="Enter your phone number"
              />
            </div><br>
            <h4><label  class="card-text w-auto label label-success" >What services may we assist you with?</label></h4>
            <ul class="form-check"> 
            <li>
              <input type="checkbox" class="form-check-input" id="check1" name="option1" value="something" checked>
              <label class="form-check-label" for="check1">Carpet cleaning</label>
            </li>
            <li>
              <input type="checkbox" class="form-check-input" id="check2" name="option2" value="something">
              <label class="form-check-label" for="check2">Window cleaning</label>
            </li>
            <li>
              <input type="checkbox" class="form-check-input" >
              <label class="form-check-label">Move-out cleaning</label>
            </li>
            <li>
              <input type="checkbox" class="form-check-input" id="check3" name="option3" value="something">
              <label class="form-check-label" for="check3">End Of Tenancy Cleaning</label>
            </li>
            <li>
              <input type="checkbox" class="form-check-input" id="check4" name="option4" value="something">
              <label class="form-check-label" for="check4">Oven Cleaning Services</label>
            </li>
            <li>
              <input type="checkbox" class="form-check-input" id="check5" name="option5" value="something">
              <label class="form-check-label" for="check5">House Cleaning</label>
            </li>
          </ul>
            <button type="submit" class="btn btn-light mw-100">Book In</button></form><br> 
            <input type="checkbox" class="form-check-input" id="Check-box" name="robotOption" value="Not A Robot" unchecked>&nbsp;<label for="Check-box">I'm Not A Robot</label><!-- disabled -->
            <div class="mw-100 mb-3">
            <p class="card-text w-auto">Have a question first? Read our <a href="#" class="text-decoration">FAQ page</a> for answers or call:<a  id="ays_PhoneNumber"   href="tel:+640220601100"  Value"# Placeholder for  PHP code">0220 60 11 00</a><!--Placeholders for PHP dynamic PhoneNumber -->
            </p></div></div>
              </form>
        </div>
      </div>
    </div>

    <!-- Bootstrap JS CDN (Optional for Bootstrap features) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </div>
</body>
</html>
