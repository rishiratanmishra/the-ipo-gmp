<?php

/**
 * The core plugin class.
 */
class IPO_AI_Plugin
{

    /**
     * Define the core functionality of the plugin.
     */
    public function __construct()
    {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies()
    {
        // Core Classes
        require_once IPO_AI_PATH . 'includes/class-settings.php';
        require_once IPO_AI_PATH . 'includes/class-ai-engine.php';
        require_once IPO_AI_PATH . 'includes/class-content-generator.php';
        require_once IPO_AI_PATH . 'includes/class-keyword-research.php';
        require_once IPO_AI_PATH . 'includes/class-dynamic-renderer.php';
        require_once IPO_AI_PATH . 'includes/class-image-generator.php';
        require_once IPO_AI_PATH . 'includes/class-cron.php';
        require_once IPO_AI_PATH . 'includes/class-logger.php';
        require_once IPO_AI_PATH . 'includes/class-prompts.php';
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     */
    private function define_admin_hooks()
    {
        $settings = new IPO_AI_Settings();
        add_action('admin_menu', array($settings, 'add_plugin_admin_menu'));
        add_action('admin_init', array($settings, 'register_settings'));
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     */
    private function define_public_hooks()
    {
        // Frontend Hooks
        add_filter('the_content', array('IPO_AI_Dynamic_Renderer', 'render_dynamic_content'));
        $renderer = new IPO_AI_Dynamic_Renderer(); // Re-instantiate for shortcode if needed, or make shortcode method static too
        add_shortcode('ipo_ai_data', array($renderer, 'shortcode_ipo_data'));

        // AJAX Stream
        add_action('wp_ajax_ipo_ai_manual_generate', array('IPO_AI_Settings', 'stream_generation'));

        // Cron
        $cron = new IPO_AI_Cron();
        add_action('init', array($cron, 'schedule_events'));
        add_action('ipo_ai_hourly_event', array($cron, 'run_hourly_checks'));
        add_action('ipo_ai_daily_event', array($cron, 'run_daily_updates'));
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run()
    {
        // Trigger actions
    }
}
