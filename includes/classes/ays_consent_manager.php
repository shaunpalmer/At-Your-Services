<?php

namespace Ays\Includes\Core;

/**
 * AYS_Consent_Manager Class
 *
 * Handles cookie consent logic for the AYS Plugin.
 * Provides a flexible and extensible structure to allow integration with multiple form types and builders.
 */
class AYS_Consent_Manager
{

  private $cookie_name = 'userConsent';
  private $cookie_duration = 365; // Duration in days

  public function __construct()
  {
    // Check for consent on initialization
    add_action('init', [$this, 'check_consent']);
  }

  /**
   * Checks if the consent cookie exists.
   * If not, enqueue consent form rendering.
   */
  public function check_consent()
  {
    if (!isset($_COOKIE[$this->cookie_name])) {
      add_action('wp_footer', [$this, 'render_consent_form']);
    }
  }

  /**
   * Renders the consent form modal and JavaScript logic.
   */
  public function render_consent_form()
  {
?>
    <div id="consentModal"
      style="position:fixed; bottom:20px; right:20px; background:#fff; padding:15px; border:1px solid #ccc; z-index:1000;">
      <p>We use cookies to enhance your experience. By continuing, you agree to our use of cookies. <a
          href="<?php echo esc_url(home_url('/privacy-policy')); ?>">Learn More</a></p>
      <button id="acceptConsent">I Agree</button>
      <button id="closeConsent">Close</button>
    </div>
    <script>
      document.getElementById('acceptConsent').addEventListener('click', function() {
        document.cookie =
          "<?php echo $this->cookie_name; ?>=true; path=/; max-age=<?php echo $this->cookie_duration * 24 * 60 * 60; ?>";
        document.getElementById('consentModal').style.display = 'none';
      });

      document.getElementById('closeConsent').addEventListener('click', function() {
        document.getElementById('consentModal').style.display = 'none';
      });
    </script>
    <?php
  }

  /**
   * Registers a form to be dynamically managed.
   *
   * @param string $form_id The ID of the form.
   */
  public static function register_form($form_id)
  {
    add_filter('ays_registered_forms', function ($forms) use ($form_id) {
      $forms[] = $form_id;
      return $forms;
    });
  }

  /**
   * Retrieves all registered forms.
   *
   * @return array
   */
  public static function get_registered_forms()
  {
    return apply_filters('ays_registered_forms', []);
  }
}

/**
 * AYS_Form_Filler Class
 *
 * Handles dynamic form pre-filling logic.
 */
class AYS_Form_Filler
{

  private $cookie_name = 'userConsent';

  public function __construct()
  {
    // Hook into footer to inject form-filling script
    add_action('wp_footer', [$this, 'inject_form_filler_script']);
  }

  /**
   * Injects JavaScript for form pre-filling.
   */
  public function inject_form_filler_script()
  {
    if (isset($_COOKIE[$this->cookie_name])) {
      $registered_forms = AYS_Consent_Manager::get_registered_forms();
    ?>
      <script>
        document.addEventListener('DOMContentLoaded', function() {
          const forms = <?php echo json_encode($registered_forms); ?>;
          forms.forEach(function(formId) {
            const form = document.querySelector(`#${formId}`);
            if (form) {
              // Check and fill name if available
              if (document.cookie.includes('name=')) {
                const nameMatch = document.cookie.match(/name=([^;]+)/);
                if (nameMatch) {
                  const nameField = form.querySelector('input[name="name"]');
                  if (nameField) nameField.value = decodeURIComponent(nameMatch[1]);
                }
              }

              // Check and fill email if available
              if (document.cookie.includes('email=')) {
                const emailMatch = document.cookie.match(/email=([^;@]+@[^;@]+\.[^;@]+)/);
                if (emailMatch) {
                  const emailField = form.querySelector('input[name="email"]');
                  if (emailField) emailField.value = decodeURIComponent(emailMatch[1]);
                }
              }

              // Check and fill phone if available
              if (document.cookie.includes('phone=')) {
                const phoneMatch = document.cookie.match(/phone=([^;]+)/);
                if (phoneMatch) {
                  const phoneField = form.querySelector('input[name="phone"]');
                  if (phoneField) phoneField.value = decodeURIComponent(phoneMatch[1]);
                }
              }

              // IP address is optional and hidden by default
              if (document.cookie.includes('ip=')) {
                const ipMatch = document.cookie.match(/ip=([^;]+)/);
                if (ipMatch) {
                  const ipField = form.querySelector('input[name="ip"]');
                  if (ipField) ipField.value = decodeURIComponent(ipMatch[1]);
                }
              }
            }
          });
        });
      </script>
<?php
    }
  }
}

// Initialize the Consent Manager
new AYS_Consent_Manager();

// Initialize the Form Filler
new AYS_Form_Filler();

// Initialize the Form Filler
new AYS_Form_Filler();
