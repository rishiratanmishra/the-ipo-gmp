<?php
namespace TIGC\Core;

if (!defined('ABSPATH'))
    exit;

/**
 * Main Plugin Class (Singleton)
 * Bootstraps the application.
 */
class Plugin
{
    private static $instance = null;

    private $router;
    private $controller;
    private $renderer;
    private $seo;
    private $sitemap;

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->load_dependencies();
        $this->init_components();
    }

    private function load_dependencies()
    {
        require_once TIGC_PATH . 'core/Router.php';
        require_once TIGC_PATH . 'core/Controller.php';
        require_once TIGC_PATH . 'core/Renderer.php';
        require_once TIGC_PATH . 'core/SEO.php';
        require_once TIGC_PATH . 'core/Sitemap.php';
        require_once TIGC_PATH . 'core/Ajax.php';
        // Admin (Loaded conditionally later or here if always needed)
        // require_once TIGC_PATH . 'admin/Settings.php'; 
    }

    private function init_components()
    {
        $this->router = new Router();
        $this->controller = new Controller();
        $this->renderer = new Renderer();
        $this->seo = new SEO();
        $this->sitemap = new Sitemap();
        new Ajax(); // Initialize Ajax hooks
    }

    /**
     * Getters for components if needed externally
     */
    public function get_controller()
    {
        return $this->controller;
    }
}
