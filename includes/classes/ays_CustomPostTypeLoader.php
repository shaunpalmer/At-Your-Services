<?php
namespace ays\includes\Classes;
 //Exit if accessed directly: Check for bugs and past
if(!defined('ABSPATH')){
	exit;
}
class ays_CustomPostTypeLoader {
    /**
     * List of custom post types to load
     */
    private $postTypes = [
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
    'ays\includes\ays_blocks\Ays_Block_Team'             => AYS_PLUGIN_PATH . 'includes/ays_blocks/ays-class-block-team.php',
    ];

    /**
     * Load all custom post types
     */
    public function loadPostTypes() {
        foreach ($this->postTypes as $postType) {
            try {
                if (class_exists($postType)) {
                    new $postType();
                    error_log("Successfully instantiated {$postType}");
                } else {
                    error_log("Failed to instantiate {$postType}: Class not found");
                }
            } catch (\Throwable $e) {
                error_log("Failed to instantiate {$postType}: " . $e->getMessage());
            }
        }
    }
}
