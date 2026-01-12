<?php
/**
 * One Click Demo Import Support
 *
 * @package IPO_Premium
 */

if (!defined('ABSPATH')) {
    exit;
}

// Only allow if License is Active
if (class_exists('IPO_License_Manager') && !IPO_License_Manager::is_active()) {
    return;
}

/**
 * Register Demo Import Files
 */
function ipopro_import_files()
{
    return [
        [
            'import_file_name' => 'IPO Premium Demo',
            'categories' => ['Finance', 'Dark Mode'],
            'import_file_url' => 'https://raw.githubusercontent.com/zolaha/ipo-premium-demo/main/demo-content.xml', // Placeholder URL
            'import_widget_file_url' => 'https://raw.githubusercontent.com/zolaha/ipo-premium-demo/main/widgets.wie',     // Placeholder URL
            'import_customizer_file_url' => 'https://raw.githubusercontent.com/zolaha/ipo-premium-demo/main/customizer.dat',  // Placeholder URL
            'import_preview_image_url' => get_template_directory_uri() . '/screenshot.png',
            'import_notice' => __('Please ensure all required plugins are activated before importing the demo.', 'ipo-premium'),
            'preview_url' => 'https://demo.zolaha.com/ipo-premium',
        ],
    ];
}
add_filter('pt-ocdi/import_files', 'ipopro_import_files');

/**
 * After Import Setup
 */
function ipopro_after_import_setup()
{
    // Assign Menus
    $main_menu = get_term_by('name', 'Main Menu', 'nav_menu');

    set_theme_mod(
        'nav_menu_locations',
        [
            'primary' => $main_menu ? $main_menu->term_id : 0,
        ]
    );

    // Assign Front Page
    $front_page_id = get_page_by_title('Home');
    $blog_page_id = get_page_by_title('Blog');

    if ($front_page_id && $blog_page_id) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $front_page_id->ID);
        update_option('page_for_posts', $blog_page_id->ID);
    }

}
add_action('pt-ocdi/after_import', 'ipopro_after_import_setup');

/**
 * Disable Branding
 */
add_filter('pt-ocdi/plugin_page_setup', function ($default_settings) {
    $default_settings['parent_slug'] = 'ipo-premium-dashboard';
    $default_settings['page_title'] = __('One Click Demo Import', 'ipo-premium');
    $default_settings['menu_title'] = __('Import Demo Data', 'ipo-premium');
    $default_settings['capability'] = 'manage_options';
    $default_settings['menu_slug'] = 'ipo-premium-demo-import'; // Explicit slug
    return $default_settings;
});

// Workaround to make it appear as a submenu of our Dashboard
// The filter above changes the parent, but we might need to manually adjust priority or ensure the parent exists first.
// Since 'ipo-premium-dashboard' is created in dashboard.php, this file must be required AFTER it.
