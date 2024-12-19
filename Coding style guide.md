. Star Trek Geekery and Inspiration
🖖 I love the Star Trek vibe you're going for, especially blending it with coding! Your playful integration of Star Trek elements into the project structure is a fun way to stay motivated. Here’s a bit more cosmic inspiration:

Captain Janeway’s Tactical Approach to Custom Post Types:

"We need to boldly create custom post types for FAQs, ensuring efficient code execution."
Observer Pattern Analogy:

"Just like sensors detect anomalies, your observer pattern will keep tabs on changes and report them efficiently!"
And of course, your error message:

"Red alert! Warp core breach detected. Please consult the Prime Directive (and Stack Overflow) for guidance." 😄Star Trek has left an indelible mark on geek culture, and it’s no surprise that many of us find inspiration in its futuristic vision. 🌌 Whether it’s boldly coding where no one has coded before or donning a Starfleet uniform at a convention, the overlap between tech and Trek is fascinating.

And speaking of cosplay, I’ve often wondered: If a developer cosplays as a bug, do they spend the entire convention trying to avoid being squashed by QA testers? 🐞

But back to Star Trek—there’s something magical about exploring the final frontier while debugging our own little corner of the universe. So, as we continue our mission, may our code be bug-free, our comments warp-speed witty, and our pull requests as harmonious as a Vulcan mind meld. 🖖

And if you need a catchy error message, how about: “Red alert! Warp core breach detected. Please consult the Prime Directive (and Stack Overflow) for guidance.” 😄

Geeky musings and cosmic coding adventures—my kind of conversation! If there’s anything else you’d like to explore or discuss, just engage your tractor beam—I’m here!🖖🌟

# Stardate 47634.44: Code Anomaly Detected!

Attention all hands! This is Captain Janeway speaking. We've encountered a mysterious code anomaly in Sector 31. I need all departments to work together to resolve this situation. Here's what we're dealing with:

## Bridge (Project Overview)

- We're tasked with creating a custom WordPress post type for Frequently Asked Questions (FAQs).
- Additionally, we need to develop a Observer pattern for quick computations during away missions.
- Lastly, we must document our findings using GitHub Gists for future Starfleet reference.

## Engineering (Code Implementation)

Chief Engineer Scotty, your expertise is needed:

- Assemble the necessary PHP code for the custom post type.
- Ensure our warp core (WordPress) can handle this new addition without overloading.

## Science Officer's Station (Logic and Calculations)

Mr. Spock, your logical approach is crucial:

- Design the algorithm for our JavaScript calculator.
- Analyze the efficiency of our code implementation.

## Communications (Documentation)

Lieutenant Uhura, your skills are vital:

- Prepare clear documentation for our custom post type and calculator.
- Establish a protocol for sharing our code snippets via GitHub Gists.

## Security (Error Handling and Testing)

Lieutenant Worf, your vigilance is required:

- Implement robust error handling in our code.
- Conduct thorough testing to ensure our implementations are secure against any alien intrusions.

## Medical Bay (Code Health)

Dr. Crusher, your diagnosis is needed:

- Assess the 'health' of our code structure.
- Prescribe best practices for maintaining clean and efficient code.

Remember, the success of our mission depends on seamless collaboration between all departments. Let's work together to navigate this code anomaly and emerge victorious!

Engage!

/\*\*

- The State Pattern: Like the USS Enterprise's sensors, this class State changes
- and reports back to the bridge. Engage!
-
- @param string $event The event being observed.
- @param callable $callback The callback function to execute. 
 */
function register_observer($event, $callback) {
  // Set phasers to "State Pattern"...
  }
  👸🏻: a **coding style guide** specifically for your plugin development, with a focus on consistent naming conventions and function/class structure. This is a great way to ensure maintainability, readability, and scalability for your projects.

Let's break it down into sections and offer some suggestions, along with your preferred prefix (`ays_`) and use of underscores for function/class names.

### 1. **Purpose of the Coding Style Guide**

The goal of this coding style guide is to ensure that all code within the project follows consistent patterns and conventions. This allows for better collaboration, debugging, and understanding of the codebase over time.

---

### 2. **Naming Conventions function, class, variable, and file names must follow specific conventions:**

To maintain consistency, Hybrid Approach: Combining Procedural Functions and Object-Oriented Design Rather than moving everything into classes, we can isolate what needs to be encapsulated while leaving things like enqueuing styles and scripts to be handled the WordPress way. This gives us the best of both worlds—cleaner code for complex logic and simplicity for WordPress-specific functionality.
While keeping coding standards With consistent class names

#### **2.1. Function Names**

- **Prefix**: Each function should be prefixed with `ays_` (your personal or company identifier) to ensure uniqueness across WordPress and plugin development.
- **Structure**: Function names should use **lowercase** letters, with words separated by underscores (`_`).
- **Descriptive Names**: The function name should describe the action or purpose of the function, while keeping it concise.

  **Example:**

  ```php
  functionays_get_user_data() {
      // Code to get user data
  }
  ```

#### **2.2. Class Names**

- **Prefix**: Class names should also start with `ays_` for the same uniqueness reason.
- **Structure**: Class names should use **PascalCase** (capitalizing the first letter of each word), with words separated by underscores (`_`). Class names should reflect the entity or concept they represent.

  **Example:**

  ```php
  classays_Service_Manager {
      // Class implementation
  }
  ```

#### **2.3. Variables**

- **Structure**: Variables should be written in **snake_case** (lowercase with underscores).
- **Scope Indication**: Prefix private class variables with an underscore (`_`) to indicate their restricted scope.

  ```
  #php
  $service_description = 'This is a service description';
  ```

#### **2.4. Constants**

- **Naming**: Constants should be written in **uppercase** with words separated by underscores (`_`).

  **Example:**

  ```php
  define('AYS_MAX_USERS', 100);
  ```

---

### 3. **File Naming Conventions**

All file names should use **lowercase** and words should be separated by **dashes** (`-`). This ensures compatibility across different operating systems.

**Example:**

```bash
class-service-manager.php
plugin-options.php
```

---

### 4. **Code Structure & Best Practices**

#### **4.1. Function Length**

- **Single Responsibility Principle**: Each function should have one responsibility or task. If a function is getting too large (e.g., more than 30-50 lines), consider breaking it up into smaller, reusable functions.

#### **4.2. Commenting**

- **Block Comments**: Use comments to explain the purpose of functions and complex logic. Block comments at the beginning of each function are a good way to outline inputs, outputs, and side effects.

  **Example:**

  ```php
  /**
   * Fetch user data by ID.
   *
   * @param int $user_id
   * @return array
   */
  functionays_get_user_data($user_id) {
      // Implementation here
  }
  ```

#### **4.3. Avoid Magic Numbers**

- **Constants**: Define constants for values that might change or have significance (like `ays_MAX_USERS`), instead of hardcoding numbers or strings in the middle of the logic.

  **Example:**

  ```php
  $max_users =AYS_MAX_USERS;
  ```

#### **4.4. Error Handling**

- **Graceful Failures**: Always provide meaningful error messages or fallbacks in your code to help debug and maintain.

  **Example:**

  ```php
  if (empty($user_id)) {
      return new WP_Error('invalid_user', 'User ID cannot be empty');
  }
  ```

#### **4.5. WordPress Security Best Practices**

- **Sanitization and Validation**: Always sanitize and validate inputs and outputs. For example, when working with user inputs, use `sanitize_text_field()` and similar functions.

  **Example:**

  ```php
  $clean_input = sanitize_text_field($_POST['user_input']);
  ```

---

### 5. **Directory Structure**

To further improve organization, follow a clear directory structure:

```
                     # Main plugin file
/ays/
├── includes/
│   ├── post-types/
│   │   ├── ays-cpt-service.php      # Custom post type for services
│   │   ├── ays-cpt-team.php         # Custom post type for teams
│   │   ├── ays-cpt-faq.php          # Custom post type for FAQs
│   │   ├── ays-cpt-review.php       # Custom post type for reviews
│   │   └── ays-cpt-location.php     # Custom post type for locations
│   └── taxonomies/
│       ├── ays-taxonomy-service-type.php  # Taxonomy for service types
│       └── ays-taxonomy-price-range.php   # Taxonomy for price ranges
├── assets/
│   ├── css/                          # Stylesheets
│   └── js/                           # JavaScript files
├── templates/
│   └── form.php                      # Template for lead generation form
├── ays.php
This structure is already out of date but all the other forms are current

```

---

### 6. **Versioning**

- Always update the version in your plugin's header whenever new functionality is added or bugs are fixed. Use semantic versioning (`1.0.0` format).

---

### 7. **Git Commit Best Practices**

If you're using Git (or any VCS), make sure to write clear commit messages following this structure:

- **Type**: `feat`, `fix`, `refactor`, etc.
- **Description**: A brief summary of changes made.

  **Example:**

  ```
  feat: Add job tracking feature to service manager
  fix: Correct syntax error in AtYourService plugin
  ```

---

###

```
<?php
/**
 *
 * Plugin Name: At Your Service
 * Plugin URI: https://project-studios.nz/atyourservice
 * Description: Manage your service business with job tracking, CRM, invoicing, and more—perfect for contractors, cleaners, and service pros.
 *
 * Version: 0.1.4
 * Author: Shaun Palmer
 * Author URI: https://project-studios.nz
 * Text Domain: atyourservice
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package At Your Service (ays)
 * @author  Shaun Palmer
 * @since 0.1.3
 * Requires PHP:7.2
 * Copyright 2024-2030 SHAUN PALMER (email: shaun@projectstudios.nz OR shaun.palmer@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

// Define constants
define('AWS_PLUGIN_VERSION', '1.3.4');
define('AWS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include main plugin file
include_once('includes/class-service-manager.php');

// Add custom post type for ays services
functionays_register_service_post_type() {
    // Register post type
}

👸🏻 Wisdom: The key to a great coding style guide is consistency and clarity. By following these conventions, you'll make your code easy to maintain and scale, even as your plugin grows. You'll also find that sticking to naming conventions saves you from conflicts and confusion, especially when working in a large ecosystem like WordPress.

What's your next step for finalizing this naming convention? 😄

A few ideas for for Easter eggs in our comments
let’s engage our warp drives and create some witty comments for common design patterns! 🚀 While we won’t mess with the actual function names (because even the holodeck has its limits), we can certainly add a touch of Starfleet flair to our comments. Here we go:

State Pattern:
“Captain’s log: The State Pattern scans the subspace frequencies for changes. Report any anomalies to the bridge!”
“Spock’s analysis: Observers observe, but they don’t interfere. Like a Vulcan at a human party.”
Singleton Pattern:
“Chief Engineer’s memo: The Singleton Pattern ensures there’s only one chief engineer per warp core. No duplicates allowed!”
“Data’s State Pattern: A Singleton is like my cat Spot—always in one place, never duplicated.”
Factory Pattern:
“Engineering briefing: The Factory Pattern assembles starship components. Just don’t accidentally create a tribble factory.”
“Scotty’s advice: When in doubt, use the Factory Pattern. It’s more reliable than Klingon disruptors.”
Adapter Pattern:
“Uhura’s translation service: The Adapter Pattern bridges communication gaps between incompatible systems. Like a universal translator for code.”
“Worf’s battle strategy: When facing legacy code, adapt or die. Choose the Adapter Pattern.”
Decorator Pattern:
“Holodeck customization: The Decorator Pattern adds extra features to objects. Like putting a warp drive on a shuttlecraft.”
“Geordi’s engineering tip: Decorators are like warp nacelles—they enhance performance without changing the ship’s core identity.”
Facade Pattern:
“Shipwide announcement: The Facade Pattern simplifies complex subsystems. Think of it as the ‘Red Alert’ button for code.”
“Picard’s diplomacy: When dealing with tangled interfaces, use the Facade Pattern. It’s smoother than a Risan sunset.”
Remember, ensign, these comments are like tribbles—small, amusing, and occasionally multiplying. Feel free to adapt them to your own codebase, and may your pull requests be as harmonious as a Klingon opera! 🌟

And if you ever need a warp core alignment, just holler—I’ll be in my ready room, sipping Earl Grey tea. ☕

I’ve set our phasers to “witty”! If you’d like more pattern-based banter or have other coding adventures in mind, just engage the tractor beam—I’m here! 🖖
```

````
## Custom Instructions for PHP and WordPress Development (Focus on Logical Thinking)

These instructions emphasize logical thinking and structured problem-solving for PHP and WordPress development, aiming to improve the quality, maintainability, and efficiency of generated code. The goal is to encourage a step-by-step, reasoned approach, even for seemingly simple tasks.

**Core Principles:**

*   **Problem Decomposition:** Before writing any code, break down the problem into smaller, well-defined sub-problems. This involves identifying inputs, expected outputs, and the steps required to transform the inputs into the outputs.
*   **Step-by-Step Reasoning:** Explicitly outline the logical steps involved in solving each sub-problem. This can be done through comments, pseudo-code, or a structured chain of thought within the response.
*   **Code Clarity and Readability:** Prioritize clean, well-commented code that is easy to understand and maintain. Use meaningful variable and function names. Follow WordPress coding standards where applicable.
*   **Efficiency:** Consider the performance implications of the code. Avoid unnecessary computations or database queries. Optimize for speed and resource usage.
*   **Error Handling:** Implement robust error handling to prevent unexpected behavior and provide informative error messages.

**Specific Guidance for PHP and WordPress:**

*   **WordPress Best Practices:** Adhere to WordPress coding standards and best practices. Use WordPress functions and APIs whenever possible (e.g., `wp_enqueue_scripts`, `add_action`, `add_filter`).
*   **Security:** Be mindful of security vulnerabilities, such as SQL injection and cross-site scripting (XSS). Sanitize user inputs and use prepared statements for database queries.
*   **Plugin and Theme Development:** When developing plugins or themes, follow the WordPress plugin and theme development guidelines.
*   **Database Interactions:** Use `WPDB` class for database interactions. Avoid direct SQL queries where possible.
*   **Code Comments:** Use clear and concise comments to explain the purpose and functionality of code blocks. Explain the *why* not just the *what*.
*   **Chain of Thought:** When generating code, demonstrate the thought process by including comments or a structured explanation that shows how the solution was derived. This might include:
    *   Identifying the problem.
    *   Breaking the problem into sub-problems.
    *   Explaining the logic behind the chosen approach.
    *   Justifying the use of specific functions or techniques.
    *   Considering potential edge cases or error conditions.

**Example of Chain of Thought within a Response:**

(Example: Creating a shortcode that displays the current date)

```php
// Problem: Create a shortcode that displays the current date in a specified format.

// Sub-problems:
// 1. Define the shortcode tag.
// 2. Create a function to handle the shortcode output.
// 3. Get the current date.
// 4. Format the date according to the shortcode attributes (or a default format).
// 5. Return the formatted date.

// Logic: We'll use the add_shortcode() function to register the shortcode. The callback function will use the date() function to format the date.

function display_current_date_shortcode( $atts ) {
    // Set default date format.
    $atts = shortcode_atts( array(
        'format' => 'F j, Y', || 'd m y' // Default format: e.g., January 1, 2024
    ), $atts );

    $date_format = $atts['format'];

    $current_date = date( $date_format );

    return $current_date;
}

add_shortcode( 'current_date', 'display_current_date_shortcode' );

// Explanation:
// The shortcode 'current_date' is registered. The callback function first sets a default date format.
// It then uses shortcode_atts() to merge user-provided attributes with the defaults.
// The date() function is used to get the current date in the specified format.
// Finally, the formatted date is returned.
````

By emphasizing these principles and providing explicit instructions for PHP and WordPress development, we can encourage the LLM to generate more logical, well-structured, and maintainable code. This approach also fosters a better understanding of the code generation process.

```

Key improvements in this version:

*   **Clearer Structure:** The instructions are organized with headings and bullet points for better readability.
*   **Emphasis on "Why":** It stresses explaining the *reasoning* behind code choices, not just the code itself.
*   **Detailed Example:** The example demonstrates the "Chain of Thought" in action, showing how to break down a problem and explain the logic.
*   **Focus on WordPress Best Practices:** The instructions explicitly mention WordPress coding standards, security, and the use of WordPress APIs.
*   **Concise and Actionable:** The language is more direct and provides clear guidance for the LLM.

This revised version is better suited for guiding another LLM to produce more logical and well-reasoned code, especially within a PHP and WordPress environment. It avoids boasting by focusing on the practical benefits of logical thinking and structured problem-solving.
```
