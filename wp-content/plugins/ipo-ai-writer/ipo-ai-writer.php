<?php
/**
 * Plugin Name: IPO AI Writer
 * Description: Automated long-form IPO blog generator with AI, WP-Cron, and real-time dynamic data.
 * Version: 1.0.0
 * Author: Zolaha.com
 * Text Domain: ipo-ai-writer
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define Constants
define('IPO_AI_VERSION', '1.0.0');
define('IPO_AI_PATH', plugin_dir_path(__FILE__));
define('IPO_AI_URL', plugin_dir_url(__FILE__));
define('IPO_AI_TABLE_META', 'run_ipo_ai_meta'); // Will prefix with wp_ in logic

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'IPO_AI_';
    $base_dir = IPO_AI_PATH . 'includes/';

    // Does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Get the relative class name
    $relative_class = substr($class, $len);

    // Replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php

    // Convert Class_Name to class-class-name.php format for WP standards
    $file_name = 'class-' . strtolower(str_replace('_', '-', $relative_class)) . '.php';
    $file = $base_dir . $file_name;

    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

/**
 * The code that runs during plugin activation.
 */
function activate_ipo_ai_writer()
{
    require_once IPO_AI_PATH . 'includes/class-activator.php';
    IPO_AI_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_ipo_ai_writer()
{
    require_once IPO_AI_PATH . 'includes/class-deactivator.php';
    IPO_AI_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_ipo_ai_writer');
register_deactivation_hook(__FILE__, 'deactivate_ipo_ai_writer');

/**
 * Begins execution of the plugin.
 */
function run_ipo_ai_writer()
{
    $plugin = new IPO_AI_Plugin();
    $plugin->run();
}

add_action('plugins_loaded', 'run_ipo_ai_writer');
