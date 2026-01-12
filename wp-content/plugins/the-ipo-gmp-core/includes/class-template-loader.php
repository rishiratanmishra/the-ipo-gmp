<?php

class TIGC_Template_Loader {

    public function __construct() {
        add_filter('page_template', [$this, 'load_custom_template']);
        add_filter('template_include', [$this, 'force_front_page_template']);
        add_filter('404_template', [$this, 'load_404_template']);
    }

    /**
     * Load custom templates for specific pages.
     */
    public function load_custom_template($template) {
        global $post;

        if (!$post) return $template;

        $slug = $post->post_name;

        // Map Slugs to Template Files
        $map = [
            'ipo-details'    => 'page-ipo-details.php',
            'buybacks'       => 'page-buybacks.php',
            'mainboard-ipos' => 'page-mainboard.php', // New Dedicated Page
            'sme-ipos'       => 'page-sme.php'         // New Dedicated Page
        ];

        if (array_key_exists($slug, $map)) {
            $file = TIGC_PATH . 'templates/' . $map[$slug];
            if (file_exists($file)) {
                return $file;
            }
        }

        return $template;
    }

    /**
     * Force the dashboard template for the Front Page.
     */
    public function force_front_page_template($template) {
        if (is_front_page()) {
            $file = TIGC_PATH . 'templates/page-dashboard.php';
            if (file_exists($file)) {
                return $template = $file;
            }
        }
        return $template;
    }

    /**
     * Load Custom 404 Template
     */
    public function load_404_template($template) {
        $file = TIGC_PATH . 'templates/404.php';
        if (file_exists($file)) {
            return $file;
        }
        return $template;
    }
}
