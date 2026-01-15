<?php
/**
 * Plugin Name: The IPO GMP Core
 * Description: Core functionality, templates, and assets for The IPO GMP platform. Safe from theme updates.
 * Version: 2.0.0
 * Author: Zolaha.com
 * Text Domain: the-ipo-gmp-core
 */

if (!defined('ABSPATH'))
    exit;

define('TIGC_PATH', plugin_dir_path(__FILE__));
define('TIGC_URL', plugin_dir_url(__FILE__));

// Require Core Logic
require_once TIGC_PATH . 'core/Plugin.php';

// Initialize Plugin
function run_the_ipo_gmp_core()
{
    TIGC\Core\Plugin::instance();
}
add_action('plugins_loaded', 'run_the_ipo_gmp_core');

// Enqueue Assets (Shared across all custom templates)
// Moved from old file, kept for compatibility, but ideally should be in Renderer or specific AssetManager
add_action('wp_enqueue_scripts', function () {
    // Basic Check for pages where we need assets
    if (is_page(['dashboard', 'ipo-details', 'buybacks', 'mainboard-ipos', 'sme-ipos', 'upcoming-ipos']) || is_front_page()) {
        wp_enqueue_script('tailwindcss', 'https://cdn.tailwindcss.com?plugins=forms,container-queries', [], null);
        wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap', [], null);
        wp_enqueue_style('material-icons', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap', [], null);

        // Custom Config for Tailwind
        wp_add_inline_script('tailwindcss', '
            tailwind.config = {
                darkMode: "class",
                theme: {
                    extend: {
                        colors: { 
                            "primary": "#0d7ff2", 
                            "background-dark": "#050A18", 
                            "card-dark": "#0B1220", 
                            "border-navy": "#1E293B", 
                            "neon-emerald": "#00FF94", 
                            "purple-accent": "#A855F7" 
                        },
                        fontFamily: { "display": ["Inter", "sans-serif"] }
                    }
                }
            }
        ');
    }
});


