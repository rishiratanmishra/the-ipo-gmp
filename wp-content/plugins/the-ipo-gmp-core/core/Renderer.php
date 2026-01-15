<?php
namespace TIGC\Core;

if (!defined('ABSPATH'))
    exit;

/**
 * Renderer Class
 * Handles template loading for the Virtual Pages.
 */
class Renderer
{

    public function __construct()
    {
        add_filter('template_include', [$this, 'load_template']);
    }

    public function load_template($template)
    {
        // 1. Homepage / Dashboard Logic
        if (is_front_page()) {
            $dashboard = TIGC_PATH . 'templates/page-dashboard.php';
            if (file_exists($dashboard)) {
                return $dashboard;
            }
        }

        // 1.5 Archive Pages Logic
        if (is_page(['mainboard-ipos', 'sme-ipos', 'buybacks', 'upcoming-ipos'])) {
            $archive = TIGC_PATH . 'templates/archive-ipo.php';
            if (file_exists($archive)) {
                return $archive;
            }
        }

        // 2. IPO Virtual Page Logic
        $controller = \TIGC\Core\Plugin::instance()->get_controller();

        // If we have a valid IPO in context, load our custom template
        if ($controller && $controller->current_ipo) {
            $custom_template = TIGC_PATH . 'templates/single-ipo.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }

        return $template;
    }
}
