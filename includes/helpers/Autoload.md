<?php
namespace ays\includes\helpers;

if (! defined('ABSPATH')) {
  exit; // Exit if accessed directly
}


class AYS_ClassAutoloader {
  private static $classes_map     = array(
    'ays\includes\posttypes\Ays_CPT_FAQ'                 => AYS_PLUGIN_PATH . 'includes/posttypes/ays-cpt-faq.php',
    'ays\includes\posttypes\Ays_CPT_Location'            => AYS_PLUGIN_PATH . 'includes/posttypes/ays-cpt-location.php',
    'ays\includes\posttypes\Ays_CPT_Review'              => AYS_PLUGIN_PATH . 'includes/posttypes/ays-cpt-review.php',
    'ays\includes\posttypes\Ays_CPT_Service'             => AYS_PLUGIN_PATH . 'includes/posttypes/ays-cpt-service.php',
    'ays\includes\posttypes\Ays_CPT_Team'                => AYS_PLUGIN_PATH . 'includes/posttypes/ays-cpt-team.php',
    'ays\includes\taxonomies\Ays_Taxonomy_Neighbourhood' => AYS_PLUGIN_PATH . 'includes/taxonomies/ays-taxonomy-neighbourhood.php',
    'ays\includes\taxonomies\Ays_Taxonomy_Price_Range'   => AYS_PLUGIN_PATH . 'includes/taxonomies/ays-taxonomy-price-range.php',
    'ays\includes\taxonomies\Ays_Taxonomy_Service_Type'  => AYS_PLUGIN_PATH . 'includes/taxonomies/ays-taxonomy-service-type.php',
    'ays\admin\Ays_Enqueue_Scripts'                      => AYS_PLUGIN_PATH . 'admin/enqueue.php',
    'ays\includes\ays_blocks\Ays_Block_Base'             => AYS_PLUGIN_PATH . 'includes/ays_blocks/ays-class-block-base.php',
    'ays\includes\ays_blocks\Ays_Block_FAQ'              => AYS_PLUGIN_PATH . 'includes/ays_blocks/ays-class-block-faq.php',
    'ays\includes\ays_blocks\Ays_Block_Services'         => AYS_PLUGIN_PATH . 'includes/ays_blocks/ays-class-block-services.php',
    'ays\includes\ays_blocks\Ays_Block_Team'             => AYS_PLUGIN_PATH . 'includes/ays_blocks/ays-class-block-team.php'
  );

  private static $is_registered   = false;
  private static $loading_classes = array();

  /**
   * Autoload function to load classes based on namespaces.
   *
   * @param string $class_name The fully-qualified class name.
   */
  public static function autoload($class_name)
  {
    // Step 1: Trim class name to avoid whitespace issues.
    $class_name = trim($class_name);

    // Step 2: Only autoload classes within our namespace.
    if (strpos($class_name, 'ays\\') !== 0) {
      return;
    }

    // Step 3: Prevent recursive loading.
    if (isset(self::$loading_classes[$class_name])) {
      return;
    }
    self::$loading_classes[$class_name] = true;

    // Step 4: Check in pre-defined class map.
    if (isset(self::$classes_map[$class_name])) {
      $file_path = wp_normalize_path(self::$classes_map[$class_name]);
      if (file_exists($file_path)) {
        // Step 5: Include the class file within a try-catch block.
        try {
          require_once $file_path;
        } catch (Exception $e) {
          // Step 6: Handle exceptions and log error.
          // Catch the exception and handle it using your WP_Error_Handler singleton
    // Get the singleton instance of WP_Error_Handler
    $error_handler = WP_Error_Handler::get_instance();
    // Add a debug notice with the error message from the caught exception
    $error_handler->add_debug_notice('Caught an exception: ' . $e->getMessage(), 'error');
        }
        unset(self::$loading_classes[$class_name]);
        return;
      } else {
        error_log("Autoloader: File path '$file_path' for class '$class_name' does not exist");
      }
    }

    // Step 7: If class not found in class map, attempt PSR-4 autoloading.
    $relative_class = substr($class_name, strlen('ays\\'));
    $relative_class = str_replace('\\', DIRECTORY_SEPARATOR, $relative_class);
    $file           = AYS_PLUGIN_PATH . DIRECTORY_SEPARATOR . $relative_class . '.php';
    $real_base      = realpath(AYS_PLUGIN_PATH);
    $real_file      = realpath($file);

    // Step 8: Security check and file inclusion.
    if ($real_file && strpos($real_file, $real_base) === 0 && file_exists($real_file)) {
      try {
        require_once $real_file;
      } catch (Exception $e) {
        error_log('Autoloader exception: ' . $e->getMessage());
      }
    } else {
      error_log("Autoloader: Failed to load class '$class_name' from '$file'");
    }
# Why is this unset here?
    unset(self::$loading_classes[$class_name]);
  }

  /**
   * Registers the autoloader function with spl_autoload_register.
   */
  public static function register()
  {
    if (! self::$is_registered) {
      spl_autoload_register(array(__CLASS__, 'autoload'), true, true);
      self::$is_registered = true;
    }
  }
}

// Ensure the autoloader is registered only once.
if (! class_exists('ays\includes\helpers\AYS_ClassAutoloader')) {
  AYS_ClassAutoloader::register();
}


/*
Logical Steps of the Autoloader:

1. **Class Declaration**: The `AYS_ClassAutoloader` class is declared to encapsulate the autoloading logic.

2. **Class Map Definition**: A private static `$classes_map` array maps fully-qualified class names to their corresponding file paths.

3. **Autoload Function (`autoload` method)**:
   - **Step 1**: Trim the `$class_name` to remove any extra whitespace.
   - **Step 2**: Check if the class belongs to the 'ays' namespace. If not, return early.
   - **Step 3**: Prevent recursive loading by checking if the class is already being loaded.
   - **Step 4**: Check if the class exists in the predefined `$classes_map`.
   - If found, normalize the file path and proceed to include it.
   - **Step 5**: Include the class file within a try-catch block to handle any exceptions.
   - **Step 6**: If the file does not exist, log an error message.
   - **Step 7**: If the class is not in the map, attempt to autoload using PSR-4 conventions.
   - Convert the class name to a file path.
   - **Step 8**: Perform a security check using `realpath` and include the file if it exists.

4. **Register Function (`register` method)**:
   - Checks if the autoloader is already registered.
   - Registers the autoload function using `spl_autoload_register`.

5. **Autoloader Initialization**:
   - Ensures the autoloader is registered only once by checking if the class exists.
   - Calls `AYS_ClassAutoloader::register()` to register the autoloader.

6. **Error Handling**:
   - Exceptions during file inclusion are caught and logged.
   - Security checks prevent directory traversal attacks.
*/



// Change Made: Renamed the class from Ays_Autoloader to AYS_ClassAutoloader.
// Reasoning: To avoid class name conflicts with existing autoloaders and to follow a consistent naming convention. Uppercase acronyms and underscores improve readability.
// Consistent Naming Conventions:

// Change Made: Renamed variables to remove redundant prefixes ($ays_) within the class context.
// Reasoning: Since the class is already namespaced under AYS, prefixes are unnecessary. This improves code clarity and adheres to WordPress coding standards.
// Removed Unnecessary Logging:

// Change Made: Reduced excessive error_log statements.
// Reasoning: Excessive logging can clutter log files and impact performance. Logging should be meaningful and necessary for debugging.
// Added Try-Catch Blocks:

// Change Made: Wrapped require_once statements within try-catch blocks.
// Reasoning: To handle any exceptions during file inclusion, enhancing error handling and security.
// Implemented PSR-4 Autoloading Fallback:

// Change Made: Added logic to autoload classes following PSR-4 standards if they are not found in the class map.
// Reasoning: Increases flexibility and ensures that all classes within the namespace can be autoloaded correctly.
// Used DIRECTORY_SEPARATOR:

// Change Made: Replaced custom directory separators with PHP's DIRECTORY_SEPARATOR.
// Reasoning: Improves portability across different operating systems and eliminates the need for custom constants.
// Security Enhancements:

// Change Made: Ensured that only files within the plugin directory are included by using realpath checks.
// Reasoning: Prevents directory traversal attacks and unauthorized file inclusion.
// Ensured Single Registration of Autoloader:

// Change Made: Modified the registration check to ensure the autoloader is registered only once.
// Reasoning: Prevents multiple registrations which could cause performance issues or unexpected behavior.
// Removed Duplicate Autoloaders:

// Change Made: Removed the global anonymous function autoloader at the end of the code.
// Reasoning: Centralizing the autoloading logic within the class improves maintainability and prevents conflicts.
// Code Formatting and Indentation:

// Change Made: Adjusted indentation to use tabs, added spaces around operators, and aligned array elements.
// Reasoning: Follows WordPress coding standards, improving readability and consistency.
// Explanation of Mistakes:

// Class Name Conflict: The original class name Ays_Autoloader could conflict with existing classes. Renaming it to AYS_ClassAutoloader avoids this issue and follows naming conventions.

// Excessive Logging: Overusing error_log can lead to bloated log files and performance degradation. It's important to log only critical information.

// Inconsistent Naming: Prefixing variables with ays_ inside the AYS_ClassAutoloader class is redundant and can make the code harder to read.

// Lack of Error Handling: Not using try-catch blocks around require_once can lead to unhandled exceptions if there are issues with the included files.

// Duplicate Autoloaders: Having multiple autoloaders can cause conflicts and make debugging difficult. Centralizing the autoloading logic is more efficient.*
// autoloader can't find the correct file based on the class name and namespace, it won't load the class. 
// Key points to check when troubleshooting autoloading issues:
// Namespace and File Structure:
// Ensure your directory structure matches your namespaces exactly. 
// Verify that class names and file names are identical, including case sensitivity. 
// Autoloader Registration:
// Check if you've correctly registered your autoloader using spl_autoload_register. 
// Case Sensitivity:
// Be aware that most Linux systems are case-sensitive, so "MyClass" is different from "myclass". 
// Composer Usage (if applicable):
// Run composer dump-autoload after adding new classes or dependencies to update the autoloader cache. 
// Common scenarios where autoloading might fail:
// Incorrect Class Name: Trying to access a class with a different name than what's defined in the file. 
// Missing Namespace: Not including the correct namespace when referencing a class. 
// Incorrect File Path: The autoloader is searching in the wrong directory for the class file. 
// Typos in Autoloader Configuration: Mistakes in the autoloading configuration within your Composer composer.json file. 
// How to debug autoloading issues:
// Check error logs:
// Look for PHP error messages that might indicate where the autoloader is failing. 
// Print debugging information:
// Add var_dump statements to see if the autoloader is attempting to load the correct file. 
// Inspect file system structure:
// Inspect file system structure:
// Verify that your project's directory structure matches the namespaces you're using   Under the Autoloader covers
// So how does this all work? Enter spl_autoload_register. This function registers your autoloader and it would look something like this:



// define ('PROJECT_ROOT', __DIR__);
// spl_autoload_register(function ($className)
// {
//     $path = PROJECT_ROOT . '\\' . "{$className}.php";
//     $path = str_replace('\\', DIRECTORY_SEPARATOR, $path);
//     if (file_exists($path))
//         include_once $path;
// });
// Here is how it works:

// When PHP comes across an object or function it does not already know about, and you have registered an autoloader with spl_autoload_register, then it passes that unknown name to the function you registered, in this case, an anonymous function.

// That function then constructs a path to a file that should exist according to the project setup. In this case, it is the directory where the autoloader file lives, plus the full namespace and class name. It also adds '.php', as we know it should be a PHP file.

// If the file exists, then we include it and we are done, as PHP has now included the file, so it will know about it on return of this anonymous function.

// If the file does not exist, we don't do anything and just return. PHP will then still not know how to resolve the name and throw an error.

// There are a few things you need to know for a successful autoloader:

// Make sure the class name and file name are in the same case and spelled the same. On case-insensitive file systems (like Windows or OSX), if the case does not match, it will still work, but when you deploy on Linux, things go south quickly (don't ask how I know that).

// Names will include the full namespace with the \ character exactly as written in PHP.

// Make sure your directory structure is set up to match the PHP namespace and case sensitive (see #1.)

// All autoloaded files should begin with <?php and not include any statements not encapsulated in the corresponding class. This prevents unintended code execution.

// Only include one class definition per file. This ensures the autoloader can find it correctly. https://blog.phpfui.com/the-genius-of-the-php-autoloader   Composer autoload.php file
// You can also be lazy and just use the Composer generated autoload.php file. As I will explain in a future post, this is not a good idea for a couple of reasons, but it is a reasonable first and quick autoloader configuration. https://blog.phpfui.com/the-genius-of-the-php-autoloader  I'm not going to cover the technical side of PHP namespaces, there are plenty of tutorials on this topic that are easily found. Instead, I am going to concentrate on best practices for namespaces, because many developers don't seem to understand how to use them.

// First, Some Definitions
// The global namespace
// In PHP, the global namespace starts with a backslash (\) and nothing else. PHP owns the global namespace and you should not be adding to it with a few exceptions. What do I mean by "PHP owns the global namespace". Simply PHP is allowed to put things into the global namespace because PHP defines what PHP means. All library functions are in the global namespace. strlen, count, str_replace are all examples of library functions in the global namespace. PHP is free to add a new function or class in the global namespace and this could break your code when you upgrade to a new version of PHP.

// Use \ for everything in the global namespace
// Best practice in PHP is to prefix all calls to global functions and classes with a leading \. For example, don't do this:


// COPY

// COPY
// $length = strlen('A string I want to know the length of');
// $reflection = new ReflectionClass('MyClass');
// Instead do this:


// COPY

// COPY
// $length = \strlen('A string I want to know the length of');
// $reflection = new \ReflectionClass('MyClass');
// There are two reasons to do this. First, it shows explicitly what function or class you are calling (the global version and not something in the current namespace), and second, it is actually faster for PHP to figure out what you are doing and not have to worry about looking up the name in the current namespace. This may seem like a trivial optimization, but in the end, all these things add up (time is cumulative) and eventually time matters.

// Top Level Namespace and Sections
// The Top Level Namespace is the very first section of a namespace. This is the most import part of a namespace, followed by the next section, and so on. As we will see in a bit, naming conventions are important.

// The Bottom Line
// Don't put things in the global namespace. All your code should be in a namespace!

// The \App namespace is yours, use it!
// When writing a PHP project, you can claim the \App namespace for yourself and your application. This is a widely accepted standard. All the code in your application that you or your team write should be in the \App namespace (and child namespaces, more on that later).

// Reserve your namespaces now
// If you are working for company, and want to make company wide projects available, your top level namespace should be your company name. Search for it on packagist.org (Google's namespace is Google for example) to make sure it is not already in use. If it is, and it is not your company, add something to the namespace to prevent it conflicting in the future (maybe add LLC or Inc to the end of the namespace for example). You don't have to publish anything from your company, but it is nice to know you have a namespace that should not conflict at some point in the future.

// The next thing you might want to do is reserve your own namespace if you plan to do any open source projects. This could be your GitHub user name for example, or just another namespace you made up, or one that relates to an open source project you are developing. Again, search for any existing usages packagist.org before deciding on a name. Then publish a package to reserve the namespace.

// Uppercase The First Letter Please!
// Standard PHP naming convention is to uppercase the first letter of all namespace sections and uppercase the first letter of a class. This goes toward readability of PHP in general. Method names, members and variables are should start with a lowercase letter.

// DRY namespaces and class names
// An important thing to remember is the class name is really part of the namespace in a sense. While you can have a class and a namespace share the same name, you should consider the class name as part of the namespace.

// In programming circles, DRY is a TLA (Three Letter Acronym) for "Don't Repeat Yourself", but as you look around in common packages, this rule is broken all the time.

// Let's look at a commonly used package and how to improve it. The namespace is \Symfony\Component\Process\Pipes. So far so good. Notice each section starts with a Capital letter. But there is a problem. Pipes is plural. Next rule:

// Don't use plurals in namespaces
// Namespaces qualify a class. A class is singular. Namespaces should be singular, since a class is singular, and they are part of the same thing, the namespace. While you may have many classes in a particular namespace, each class is it's own entity. It is just grouped with other classes. The group does not need to be plural, as you refer to just one class as a time.

// So here is the class hierarchy for \Symfony\Component\Process\Pipes:

// \Symfony\Component\Process\Pipes\AbstractPipes

// \Symfony\Component\Process\Pipes\PipesInterface

// \Symfony\Component\Process\Pipes\UnixPipes

// \Symfony\Component\Process\Pipes\WindowsPipes

// You will notice there are only two concrete classes (concrete classes are ones that can actually be turned into objects), UnixPipes and WindowsPipes, which correspond to the two major OSes everyone uses, Unix (Linux) and Windows. The other two classes define what the two concrete classes can do (ie. their interfaces).

// So notice the class hierarchy is not DRY. The word Pipes (should be Pipe) is used in multiple places in the same namespace / class. Here is a better hierarchy:

// \Symfony\Component\Process\Pipe

// \Symfony\Component\Process\PipeInterface

// \Symfony\Component\Process\Pipe\Unix

// \Symfony\Component\Process\Pipe\Windows

// So what have we done? First we made Pipes singular, and also made everything DRY. No multiple Pipe(s) anywhere in a class name. The abstract class Pipe is now in the Process namespace. But that is fine since it is the parent class to both the Unix and Windows concrete classes, and sits just one level up. We also have a PipeInterface in the same namespace to avoid a duplicate Pipe. Interfaces tend to have a descriptive name in them, as they can't just be called Symfony\Component\Process\Pipe\Interface, since Interface is a reserved word in PHP (obviously). We could solve this problem in PHP 8 or higher with a interfaces namespace, such as Symfony\Component\Process\Interface\Pipe, as reserved words can be used in namespaces since PHP 8, but this keeps it compatible with older PHP versions.

// Child Namespaces
// Child namespaces (anything past the top level namespace) allow you to further refine and describe your classes. Use the child namespace to group and describe your classes. But don't abuse it!

// Extra Long Namespace Abuse
// A common problem with namespaces is extraneous children and namespaces that are too specific and too long.

// In our above example, do we really need both Component and Process? We need Pipe for sure, but we could probably live without one or the other, or even both. What is wrong with Symfony\Pipe? Pretty simple.

// Some Exceptions
// In the above example, we had to add a name to Interface, to avoid a PHP reserved word.

// Windows is plural, but is a proper name, so makes sense to use it.

// You can use the global namespace to shorten things, for example, \T() can be a translation function. But keep this to a minimum. And shorter is better in this case.

// To Summarize: https://blog.phpfui.com/php-namespace-best-practice 

// PHP Namespaces
// Namespaces are qualifiers that solve two different problems:

// They allow for better organization by grouping classes that work together to perform a task
// They allow the same name to be used for more than one class
