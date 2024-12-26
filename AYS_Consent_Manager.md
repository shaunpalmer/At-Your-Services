## AYS_Consent_Manager

# AYS Consent Manager Documentation

## Purpose

The `AYS_Consent_Manager` class is part of the AYS Plugin and is designed to manage cookie consent dynamically while ensuring compliance with modern privacy expectations. It is structured to be extensible, allowing integration with multiple forms and form builders. The `AYS_Form_Filler` class complements this by handling form pre-filling based on available cookie data.

---

## How It Works

### **AYS_Consent_Manager**

- **Role:** Manages cookie consent through a modal dialog that prompts users to accept the usage of cookies.
- **Initialization:**
  - On `init`, the class checks whether the consent cookie exists.
  - If the cookie is absent, a consent modal is rendered in the footer via `wp_footer`.
- **Features:**
  - **Dynamic Consent Modal:** The modal contains a button (`I Agree`) to set the cookie.
  - **Extensibility:** Developers can register additional forms using `register_form()`.
  - **Dynamic Filtering:** The registered forms can be retrieved via `get_registered_forms()`.

#### Example Use Case

If a user visits the website without an existing consent cookie:

1. A modal is displayed with a consent message.
2. On acceptance, a cookie named `userConsent` is set for 365 days.
3. No further consent modals are shown during this period.

---

### **AYS_Form_Filler**

- **Role:** Fills dynamic form fields with values derived from cookies, including name, email, phone, and IP address.
- **Initialization:**
  - Injects a JavaScript snippet via `wp_footer` if the consent cookie exists.
- **Features:**
  - Supports dynamic form filling for registered forms.
  - Ensures extensibility with a `AYS_Consent_Manager::get_registered_forms()` integration.

#### Example Use Case

1. A user with a valid consent cookie visits a page.
2. Forms registered with `AYS_Consent_Manager::register_form()` are auto-filled with cookie-stored values.
3. The user’s experience is streamlined without additional input.

---

## Key Design Decisions

### Why Extendable?

Hardcoding form handling limits the plugin’s usefulness. By enabling dynamic form registration, the plugin integrates seamlessly with custom and third-party form builders (e.g., PW Forms). This minimizes future refactoring.

### Handling Cookie Rejection

- If a user rejects cookies (closes the modal without consenting):
  - No cookies are set.
  - The modal can reappear on subsequent visits.
- This ensures compliance without affecting user experience significantly.

### Close Button for Consent Modal

To improve user experience, consider adding a close button to the modal:

```php
<button id="closeConsent">Close</button>
```

JavaScript for handling the close action:

```javascript
document.getElementById("closeConsent").addEventListener("click", function () {
  document.getElementById("consentModal").style.display = "none";
});
```

This provides users an option to dismiss the modal without accepting consent.

---

## Extensibility Guidelines

1. **Form Registration:**
   - Use `AYS_Consent_Manager::register_form()` to add forms dynamically.
2. **Dynamic Form Support:**
   - Ensure all forms you want pre-filled have unique IDs.
3. **JavaScript Enhancements:**
   - Extend the JavaScript to include additional fields or validations as needed.

---

## Future Improvements

1. **Granular Consent:**
   - Allow users to consent to specific types of cookies (e.g., analytics, functional).
2. **Admin View:**
   - Provide an admin dashboard to track consent logs or manage settings.
3. **Localization:**
   - Support translations for multilingual websites.

---

## Conclusion

The `AYS_Consent_Manager` and `AYS_Form_Filler` classes provide a robust foundation for managing cookie consent and improving form usability. By focusing on extensibility and compliance, this implementation ensures long-term adaptability and integration potential.
