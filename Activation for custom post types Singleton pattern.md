Recommended Approach
Instead of trying to limit the instantiation to once, consider the following:

Lazy Loading: If you're concerned about performance, implement lazy loading within your classes so that heavy operations are only executed when necessary.
Singleton Pattern: Ensure that each class is only instantiated once per request by using the Singleton design pattern.
Optimizing Class Code: Review the constructors of your classes to ensure they're not performing expensive operations on every instantiation.
Example of Singleton Pattern
php

class Ays_CPT_FAQ {
private static $instance = null;

    private function __construct() {
        // Your initialization code here
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

}

// Usage
Ays_CPT_FAQ::get_instance();
Conclusion
Wrapping your class instantiations in an activation hook would prevent essential code from running on every request, leading to malfunctioning of your custom post types and potentially breaking your site. It's crucial to instantiate these classes on every page load to maintain the functionality of your plugin.

Recommendation: Keep the class instantiation outside of the activation hook, and focus on optimizing the classes themselves if performance is a concern.
