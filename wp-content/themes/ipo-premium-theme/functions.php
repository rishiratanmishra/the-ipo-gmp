<?php
/**
 * IPO Premium Theme functions and definitions
 *
 * @package IPO_Premium
 */

if (!defined('IPO_THEME_VERSION')) {
    define('IPO_THEME_VERSION', '1.0.0');
}

if (!defined('IPO_THEME_URI')) {
    define('IPO_THEME_URI', get_template_directory_uri());
}

/**
 * Enqueue scripts and styles.
 */
function ipopro_scripts()
{
    // 1. Tailwind (CDN for now, can be swapped for local build)
    wp_enqueue_script('tailwindcss', 'https://cdn.tailwindcss.com?plugins=forms,container-queries', [], null);

    // 2. Google Fonts (Dynamic based on Customizer)
    $heading_font = get_theme_mod('heading_font', 'Inter');
    $body_font = get_theme_mod('body_font', 'Inter');

    $fonts_to_load = array_unique([$heading_font, $body_font]);
    $font_families = [];

    foreach ($fonts_to_load as $font) {
        $font_name = str_replace(' ', '+', $font);
        // Add weights common to all used fonts to ensure consistency
        $font_families[] = "family={$font_name}:wght@300;400;500;600;700;800;900";
    }

    $fonts_url = 'https://fonts.googleapis.com/css2?' . implode('&', $font_families) . '&display=swap';

    wp_enqueue_style('google-fonts', $fonts_url, [], null);

    // 3. Material Icons
    wp_enqueue_style('material-icons', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap', [], null);

    // 4. Main Theme Styles
    wp_enqueue_style('ipo-premium-style', get_stylesheet_uri(), [], IPO_THEME_VERSION);

    // 5. Config Tailwind (Injecting Customizer Variables)
    // We will move this to a separate file (inc/dynamic-css.php) later, keeping it simple for skeleton.
    wp_add_inline_script('tailwindcss', '
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: { 
                        "primary": "var(--color-primary)", 
                        "background-dark": "var(--color-bg)", 
                        "header-dark": "var(--color-header-footer)",
                        "card-dark": "var(--color-card)", 
                        "border-navy": "#1E293B", 
                        "neon-emerald": "var(--color-profit)", 
                        "purple-accent": "#A855F7" 
                    },
                    fontFamily: { 
                        "display": [ "var(--font-heading)", "sans-serif" ],
                        "body": [ "var(--font-body)", "sans-serif" ]
                    }
                }
            }
        }
    ');
}
add_action('wp_enqueue_scripts', 'ipopro_scripts');

/**
 * Theme Setup
 */
function ipopro_setup()
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');

    register_nav_menus([
        'primary' => esc_html__('Primary Menu', 'ipo-premium'),
        'footer' => esc_html__('Footer Menu', 'ipo-premium'),
    ]);
}
add_action('after_setup_theme', 'ipopro_setup');

/**
 * Filter to add Tailwind classes to Nav Menu Links
 */
function ipopro_nav_menu_link_attributes($atts, $item, $args)
{
    if ($args->theme_location == 'primary') {
        $base_class = "text-[11px] font-bold uppercase tracking-[0.15em] transition-colors";
        $active_class = in_array('current-menu-item', $item->classes) ? " text-primary" : " text-slate-400 hover:text-white";

        $atts['class'] = (isset($atts['class']) ? $atts['class'] : '') . $base_class . $active_class;
    }
    return $atts;
}
add_filter('nav_menu_link_attributes', 'ipopro_nav_menu_link_attributes', 10, 3);

/**
 * Filter to remove default LI classes and just use simple structure if needed
 * For now we keep LI standard but rely on 'nav_menu_link_attributes' for the styling.
 */

/**
 * Register Widget Areas
 */
function ipopro_widgets_init()
{
    register_sidebar([
        'name' => esc_html__('Footer Column 1', 'ipo-premium'),
        'id' => 'footer-1',
        'description' => esc_html__('Add widgets here (e.g. Navigation Menu).', 'ipo-premium'),
        'before_widget' => '<div id="%1$s" class="widget %2$s mb-8">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="text-white font-bold text-xs uppercase tracking-[0.2em] mb-8 font-sans">',
        'after_title' => '</h4>',
    ]);
    register_sidebar([
        'name' => esc_html__('Footer Column 2', 'ipo-premium'),
        'id' => 'footer-2',
        'description' => esc_html__('Add widgets here.', 'ipo-premium'),
        'before_widget' => '<div id="%1$s" class="widget %2$s mb-8">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="text-white font-bold text-xs uppercase tracking-[0.2em] mb-8 font-sans">',
        'after_title' => '</h4>',
    ]);
    register_sidebar([
        'name' => esc_html__('Footer Column 3', 'ipo-premium'),
        'id' => 'footer-3',
        'description' => esc_html__('Add widgets here.', 'ipo-premium'),
        'before_widget' => '<div id="%1$s" class="widget %2$s mb-8">',
        'after_widget' => '</div>',
        'before_title' => '<h4 class="text-white font-bold text-xs uppercase tracking-[0.2em] mb-8 font-sans">',
        'after_title' => '</h4>',
    ]);
}
add_action('widgets_init', 'ipopro_widgets_init');

/**
 * Include Required Files
 */
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/license-manager.php';
require get_template_directory() . '/inc/white-label.php';
require get_template_directory() . '/inc/dashboard.php';
require get_template_directory() . '/inc/demo-import.php';
require get_template_directory() . '/inc/tgm-config.php';
require get_template_directory() . '/inc/post-options.php';
require get_template_directory() . '/inc/seo-functions.php';
require get_template_directory() . '/inc/sitemap-generator.php';

/**
 * Hook SEO Meta Tags into wp_head
 */
// add_action('wp_head', 'ipopro_seo_meta_tags', 1);

