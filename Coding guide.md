# Custom Instructions for PHP and WordPress Development

_Focus on Logical Thinking_

These instructions emphasize logical thinking and structured problem-solving for PHP and WordPress development, aiming to improve the quality, maintainability, and efficiency of generated code. The goal is to encourage a step-by-step, reasoned approach, even for seemingly simple tasks.

## Core Principles

- **Problem Decomposition:**  
  Before writing any code, break down the problem into smaller, well-defined sub-problems. This involves identifying inputs, expected outputs, and the steps required to transform the inputs into the outputs.

- **Step-by-Step Reasoning:**  
  Explicitly outline the logical steps involved in solving each sub-problem. This can be done through comments, pseudo-code, or a structured chain of thought within the response.

- **Code Clarity and Readability:**  
  Prioritize clean, well-commented code that is easy to understand and maintain. Use meaningful variable and function names. Follow WordPress coding standards where applicable.

- **Efficiency:**  
  Consider the performance implications of the code. Avoid unnecessary computations or database queries. Optimize for speed and resource usage.

- **Error Handling:**  
  Implement robust error handling to prevent unexpected behavior and provide informative error messages.

## Specific Guidance for PHP and WordPress

- **WordPress Best Practices:**  
  Adhere to WordPress coding standards and best practices. Use WordPress functions and APIs whenever possible (e.g., `wp_enqueue_scripts`, `add_action`, `add_filter`).

- **Security:**  
  Be mindful of security vulnerabilities, such as SQL injection and cross-site scripting (XSS). Sanitize user inputs and use prepared statements for database queries.

- **Plugin and Theme Development:**  
  When developing plugins or themes, follow the WordPress plugin and theme development guidelines.

- **Database Interactions:**  
  Use the `WPDB` class for database interactions. Avoid direct SQL queries where possible.

- **Code Comments:**  
  Use clear and concise comments to explain the purpose and functionality of code blocks. Explain the _why_, not just the _what_.

- **Chain of Thought:**  
  Demonstrate the thought process by including comments or a structured explanation that shows how the solution was derived. This might include:
  - Identifying the problem.
  - Breaking the problem into sub-problems.
  - Explaining the logic behind the chosen approach.
  - Justifying the use of specific functions or techniques.
  - Considering potential edge cases or error conditions.

## Example of Chain of Thought within a Response

**Task: Creating a shortcode that displays the current date**

```php
// Problem: Create a shortcode that displays the current date in a specified format.

// Sub-problems:
// 1. Define the shortcode tag.
// 2. Create a function to handle the shortcode output.
// 3. Get the current date.
// 4. Format the date according to the shortcode attributes (or a default format).
// 5. Return the formatted date.

// Logic: We'll use the add_shortcode() function to register the shortcode.
// The callback function will use the date() function to format the date.

function display_current_date_shortcode( $atts ) {
    // Set default date format.
    $atts = shortcode_atts( array(
        'format' => 'F j, Y', // Default format: e.g., January 1, 2024
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

```

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

You’re definitely on a valuable track by considering broader architectural concerns and design patterns from the “Gang of Four” (GoF) and beyond. The “Gang of Four” (Erich Gamma, Richard Helm, Ralph Johnson, and John Vlissides) wrote the seminal book “Design Patterns: Elements of Reusable Object-Oriented Software,” which introduced many classic patterns like Factory Method, Singleton, Adapter, and more. Leveraging these patterns thoughtfully can improve your code’s maintainability, flexibility, and scalability, especially in complex WordPress projects.

🌱: Here’s a more comprehensive guideline to help you think through code design and integration, not just syntax:

1. Contextual Review Before Coding

Assess Existing Code: Identify classes and functions already in place. Are there existing autoloaders, logging utilities, or data access layers you can reuse?
Ask About Dependencies: Confirm what dependencies are already loaded. If you need a logging class, for example, check if the project already has one before writing a new one.
Align with WordPress Lifecycle: Determine where in WordPress’s hook system (init, admin_init, plugins_loaded) your code should run. This ensures your code executes at the right time and has all dependencies ready. 2. Applying Design Patterns Thoughtfully

Singleton: Use when you need a single, global point of access to a resource, like a single logging instance.
Factory Method / Abstract Factory: When you need to create objects without specifying exact classes. For instance, creating different types of "notifiers" (email, Slack, SMS) from one place.
Builder: Useful for constructing complex objects step-by-step, such as generating a complex Gutenberg block configuration or compiling a settings page dynamically.
Adapter: Bridge two incompatible interfaces. For instance, adapting an existing class to match the WordPress HTTP API’s structure.
Decorator: Add responsibilities to objects dynamically without changing their code. For example, wrap a database query class with caching logic.
Composite: Treat individual objects and groups of objects uniformly, like building menus or widget trees.
Strategy / Bridge: Define a family of algorithms or data processing approaches and make them interchangeable. Might be useful for different caching strategies or authentication methods.
Observer (via Hooks): WordPress’s action and filter hooks essentially implement an Observer pattern—leveraging this can keep your code loosely coupled.
State: If your plugin’s behavior changes under different conditions (like admin vs front-end), a State pattern might simplify logic.
Command: Encapsulate requests, such as adding tasks to a queue or deferred action, which can be especially neat for cron jobs or background processing. 3. WordPress-Specific Considerations

Use WordPress APIs: Before reinventing wheels, look at WP’s WP_Logger, WP_CLI::log(), or other standard utilities.
Namespaces and Autoloading: Ensure your classes follow a clear namespace and directory structure, allowing the autoloader to find them easily.
No Unnecessary Reinvention: If your theme or plugin already has a class that does 80% of what you need, consider extending or adapting it rather than building from scratch. 4. Iterative Reasoning and Asking Questions

Self-Check Before Coding: Before writing a new class, ask: “Do I already have something like this?” “Is there a simpler approach?” “Which pattern would best handle this situation?”
Engage in a Dialogue (Even with Yourself): Mentally simulate how the class will be used. Will it need to be instantiated multiple times or just once? Will it integrate with other classes easily?
Prototype and Test: Try a small example or write a quick snippet to confirm your approach before going all-in. 5. Documentation and Notes

Comment Intent, Not Just Implementation: Explain why you chose a certain pattern, what classes it relies on, and how it interacts with the WordPress lifecycle.
Maintain a Project Overview Map: Keep a diagram of your classes, their responsibilities, and their relationships. Update it as your code evolves.
By following these guidelines, you shift from just “making code compile” to thoughtfully engineering solutions that fit neatly into the WordPress environment. Your code won’t only be syntactically correct—it will be maintainable, logical, and future-proofed.

🤔: Does this comprehensive outline help you approach your code with more confidence and clarity, or would you like more detailed examples of how to apply a specific pattern in a WordPress context?
tackled some common culprits (syntax errors, paths) by centralizing paths with define(). Since there are no fatal errors, the issue might be subtler. Consider these additional checks:

🔎 WordPress Debugging & Visibility

Enable WP_DEBUG: Turn on WP_DEBUG and WP_DEBUG_LOG in wp-config.php. Check the debug log for non-fatal notices or warnings that might hint at what’s missing.
Check Hooks & Actions: Ensure your initialization code is hooked into a WordPress action that’s guaranteed to run. If your code runs too early (before plugins_loaded), it might silently fail to initialize properly.
🗃 File Paths & Namespacing

Verify File Paths: Even if you’ve defined constants for paths, double-check that plugin_dir_path(**FILE**) and related constants return what you expect.
Case Sensitivity & Namespaces: Ensure the class namespaces match the directory structure exactly. A minor typo or capitalization mismatch can cause classes not to load, yet not throw a fatal error.
📦 Autoloader & Class Discovery

Autoloader Setup: If you’re using a custom autoloader or Composer, confirm that classes are actually being loaded. Sometimes, an autoloader fails to find a class, and the code that depends on it silently does nothing. You can log a simple message right after requiring your autoloader to confirm it’s running.
Class Existence Checks: Add a temporary if (!class_exists('Your\\Namespace\\YourClass')) { error_log('YourClass not found'); } line. If it logs, you know the class isn’t loading.
🎛 Plugin Activation & Dependencies

Ensure Plugin Activation: Double-check that the plugin is activated. Sometimes the plugin is installed but not activated, leading to code that never runs.
Dependent Plugins or APIs: If your code relies on another plugin or external API, ensure that dependency is loaded first.
🛠 Testing Simplifications

Isolate the Code: Temporarily reduce complexity. Replace complex initialization with a simple error_log('Plugin initialization reached.'); in your bootstrap. If this message doesn’t appear, the problem is in the hooking or loading logic.
Direct File Testing: Try a direct require_once on a known good file and see if logging statements execute, ensuring your logic is at least reachable.

## Debugging and Troubleshooting in WordPress Development

Debugging is an essential part of development. Here are some techniques tailored for WordPress projects:

### 🔎 WordPress Debugging & Visibility

1. **Enable WP_DEBUG:**  
   Turn on `WP_DEBUG` and `WP_DEBUG_LOG` in `wp-config.php`. This outputs notices, warnings, and errors to a debug log for analysis.

2. **Check Hooks & Actions:**  
   Verify your code is hooked into the correct WordPress lifecycle event. If it executes too early (e.g., before `plugins_loaded`), dependencies might not be initialized.

### 🗃 File Paths & Namespacing

1. **Verify File Paths:**  
   Ensure constants like `plugin_dir_path(__FILE__)` return the expected paths. Typos or misconfigured paths can cause silent failures.

2. **Case Sensitivity:**  
   Double-check that namespace definitions match your file directory structure exactly. Mismatches can break class loading without obvious errors.

### 📦 Autoloader & Class Discovery

1. **Test Your Autoloader:**  
   If using Composer or a custom autoloader, confirm it loads all required classes. Add `error_log` statements immediately after the autoloader to verify functionality.

2. **Class Existence Checks:**  
   Use temporary checks like `if (!class_exists('Your\Namespace\ClassName')) { error_log('ClassName not found'); }` to identify missing classes.

### 🎛 Plugin Activation & Dependencies

1. **Confirm Plugin Activation:**  
   Verify the plugin is activated. Sometimes issues arise from attempting to use inactive or deactivated code.

2. **Dependent Plugins or APIs:**  
   Ensure all dependencies, such as third-party plugins or external APIs, are loaded and functional before relying on them.

### 🛠 Testing Simplifications

1. **Isolate the Code:**  
   Temporarily reduce complexity. Replace functionality with a simple test, such as `error_log('Plugin initialization reached.');`.

2. **Direct File Testing:**  
   Test critical logic outside the WordPress context to ensure basic functionality before integration.

---

## Final Thoughts on Structured Problem-Solving

By combining logical thinking, structured problem-solving, and adherence to best practices, you can create maintainable, efficient, and secure WordPress projects. Here's a summary:

- Decompose problems into smaller, manageable tasks.
- Follow WordPress standards and guidelines to ensure compatibility and security.
- Document your reasoning and decisions for future reference.
- Use debugging and design patterns thoughtfully to address issues and optimize solutions.

🔗 **Ready for Implementation?** Feel free to request further examples or dive into specifics for a current project!
