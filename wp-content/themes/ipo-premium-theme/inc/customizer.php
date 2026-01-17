<?php
/**
 * IPO Premium Theme Customizer
 *
 * @package IPO_Premium
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Customizer Controls
 */
function ipopro_customize_register($wp_customize)
{

    // --- PANEL: IPO Theme Settings ---
    $wp_customize->add_panel('ipopro_theme_panel', [
        'title' => __('IPO Theme Settings', 'ipo-premium'),
        'description' => __('Customize the look and feel of your premium theme.', 'ipo-premium'),
        'priority' => 10,
    ]);

    // ==========================================
    // SECTION 1: GLOBAL COLORS
    // ==========================================
    $wp_customize->add_section('ipopro_colors_section', [
        'title' => __('Global Colors', 'ipo-premium'),
        'panel' => 'ipopro_theme_panel',
    ]);

    // Primary Color
    $wp_customize->add_setting('primary_color', ['default' => '#0d7ff2', 'sanitize_callback' => 'sanitize_hex_color']);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'primary_color', [
        'label' => __('Brand Primary Color', 'ipo-premium'),
        'section' => 'ipopro_colors_section',
    ]));

    // Background Color
    $wp_customize->add_setting('ipopro_global_bg', ['default' => '#050A18', 'sanitize_callback' => 'sanitize_hex_color']);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'ipopro_global_bg', [
        'label' => __('Site Background', 'ipo-premium'),
        'section' => 'ipopro_colors_section',
    ]));

    // Header & Footer Background
    $wp_customize->add_setting('header_footer_bg', ['default' => '#0B111D', 'sanitize_callback' => 'sanitize_hex_color']);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'header_footer_bg', [
        'label' => __('Header & Footer Background', 'ipo-premium'),
        'section' => 'ipopro_colors_section',
    ]));

    // Card Background (Glass Base)
    $wp_customize->add_setting('card_bg_color', ['default' => '#0B1220', 'sanitize_callback' => 'sanitize_hex_color']);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'card_bg_color', [
        'label' => __('Card Background', 'ipo-premium'),
        'section' => 'ipopro_colors_section',
    ]));

    // ==========================================
    // SECTION 2: TYPOGRAPHY (PREMIUM)
    // ==========================================
    $wp_customize->add_section('ipopro_typography_section', [
        'title' => __('Typography', 'ipo-premium'),
        'panel' => 'ipopro_theme_panel',
    ]);

    // Heading Font
    $wp_customize->add_setting('heading_font', ['default' => 'Inter', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('heading_font', [
        'label' => __('Heading Font Family', 'ipo-premium'),
        'section' => 'ipopro_typography_section',
        'type' => 'select',
        'choices' => [
            'Inter' => 'Inter',
            'Outfit' => 'Outfit (Modern)',
            'Poppins' => 'Poppins',
            'Plus Jakarta Sans' => 'Plus Jakarta Sans',
        ],
    ]);

    // Body Font
    $wp_customize->add_setting('body_font', ['default' => 'Inter', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('body_font', [
        'label' => __('Body Font Family', 'ipo-premium'),
        'section' => 'ipopro_typography_section',
        'type' => 'select',
        'choices' => [
            'Inter' => 'Inter',
            'Roboto' => 'Roboto',
            'Open Sans' => 'Open Sans',
        ],
    ]);

    // ==========================================
    // SECTION 3: IPO VISUALS (NICHE)
    // ==========================================
    $wp_customize->add_section('ipopro_visuals_section', [
        'title' => __('IPO Visuals', 'ipo-premium'),
        'panel' => 'ipopro_theme_panel',
    ]);

    // Profit Color (GMP+)
    $wp_customize->add_setting('gmp_profit_color', ['default' => '#00FF94', 'sanitize_callback' => 'sanitize_hex_color']);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'gmp_profit_color', [
        'label' => __('GMP Profit Color (Positive)', 'ipo-premium'),
        'section' => 'ipopro_visuals_section',
    ]));

    // Loss Color (GMP-)
    $wp_customize->add_setting('gmp_loss_color', ['default' => '#F87171', 'sanitize_callback' => 'sanitize_hex_color']);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'gmp_loss_color', [
        'label' => __('GMP Loss Color (Negative)', 'ipo-premium'),
        'section' => 'ipopro_visuals_section',
    ]));

    // ==========================================
    // SECTION 4: LAYOUT & UI
    // ==========================================
    $wp_customize->add_section('ipopro_layout_section', [
        'title' => __('Layout & UI', 'ipo-premium'),
        'panel' => 'ipopro_theme_panel',
    ]);

    // Border Radius
    $wp_customize->add_setting('border_radius', ['default' => '12', 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('border_radius', [
        'label' => __('Card Border Radius (px)', 'ipo-premium'),
        'section' => 'ipopro_layout_section',
        'type' => 'range',
        'input_attrs' => ['min' => 0, 'max' => 30, 'step' => 1],
    ]);

    // Glass Blur Strength
    $wp_customize->add_setting('glass_blur', ['default' => '16', 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('glass_blur', [
        'label' => __('Glass Blur Strength (px)', 'ipo-premium'),
        'section' => 'ipopro_layout_section',
        'type' => 'range',
        'input_attrs' => ['min' => 0, 'max' => 50, 'step' => 1],
    ]);

    // Ticker Toggle
    $wp_customize->add_setting('enable_ticker', ['default' => true, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp_customize->add_control('enable_ticker', [
        'label' => __('Enable Top Ticker', 'ipo-premium'),
        'section' => 'ipopro_layout_section',
        'type' => 'checkbox',
    ]);

    // Ticker Speed
    $wp_customize->add_setting('ticker_speed', ['default' => '20', 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('ticker_speed', [
        'label' => __('Ticker Speed (Seconds)', 'ipo-premium'),
        'description' => __('Lower is faster. High values for a slow glide.', 'ipo-premium'),
        'section' => 'ipopro_layout_section',
        'type' => 'number',
        'input_attrs' => ['min' => 5, 'max' => 200, 'step' => 1],
    ]);

    // ==========================================
    // SECTION 4.5: HERO SECTION (NEW)
    // ==========================================
    $wp_customize->add_section('ipopro_hero_section', [
        'title' => __('Hero Section', 'ipo-premium'),
        'panel' => 'ipopro_theme_panel',
        'priority' => 45, // Between Layout and Header
    ]);

    // Badge Text
    $wp_customize->add_setting('hero_badge_text', ['default' => 'LIVE MARKET DATA • MUMBAI / GUJARAT', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('hero_badge_text', [
        'label' => __('Badge Text', 'ipo-premium'),
        'description' => __('Leave empty to hide the badge.', 'ipo-premium'),
        'section' => 'ipopro_hero_section',
        'type' => 'text',
    ]);

    // Headline Part 1 (White)
    $wp_customize->add_setting('hero_headline_1', ['default' => 'Stop Guessing.', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('hero_headline_1', [
        'label' => __('Headline Part 1', 'ipo-premium'),
        'section' => 'ipopro_hero_section',
        'type' => 'text',
    ]);

    // Headline Part 2 (Color)
    $wp_customize->add_setting('hero_headline_2', ['default' => 'Improving.', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('hero_headline_2', [
        'label' => __('Headline Part 2 (Highlighted)', 'ipo-premium'),
        'section' => 'ipopro_hero_section',
        'type' => 'text',
    ]);

    // Headline Prefix (Middle part, e.g. "Start ", White)
    $wp_customize->add_setting('hero_headline_prefix', ['default' => 'Start ', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('hero_headline_prefix', [
        'label' => __('Headline Prefix (Before Highlight)', 'ipo-premium'),
        'section' => 'ipopro_hero_section',
        'type' => 'text',
    ]);

    // Description
    $wp_customize->add_setting('hero_description', [
        'default' => "We track the Grey Market Premium (GMP) so you don't have to rely on rumors. Real-time data for Mainboard & SME IPOs, direct from the street to your screen.",
        'sanitize_callback' => 'sanitize_textarea_field'
    ]);
    $wp_customize->add_control('hero_description', [
        'label' => __('Hero Description', 'ipo-premium'),
        'section' => 'ipopro_hero_section',
        'type' => 'textarea',
    ]);

    // Highlight Color
    $wp_customize->add_setting('hero_highlight_color', ['default' => '#00FF94', 'sanitize_callback' => 'sanitize_hex_color']);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'hero_highlight_color', [
        'label' => __('Headline Highlight Color', 'ipo-premium'),
        'section' => 'ipopro_hero_section',
    ]));

    // ==========================================
    // SECTION 5: HEADER SETTINGS
    // ==========================================
    $wp_customize->add_section('ipopro_header_section', [
        'title' => __('Header Settings', 'ipo-premium'),
        'panel' => 'ipopro_theme_panel',
    ]);

    // Ticker Label
    $wp_customize->add_setting('ticker_label', ['default' => 'Market Pulse:', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('ticker_label', [
        'label' => __('Ticker Label', 'ipo-premium'),
        'section' => 'ipopro_header_section',
        'type' => 'text',
    ]);

    // Search Placeholder
    $wp_customize->add_setting('search_placeholder', ['default' => 'Search IPOs GMP...', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('search_placeholder', [
        'label' => __('Search Placeholder', 'ipo-premium'),
        'section' => 'ipopro_header_section',
        'type' => 'text',
    ]);

    // ==========================================
    // SECTION 6: FOOTER SETTINGS
    // ==========================================
    $wp_customize->add_section('ipopro_footer_section', [
        'title' => __('Footer Settings', 'ipo-premium'),
        'panel' => 'ipopro_theme_panel',
    ]);

    // Footer Description
    $wp_customize->add_setting('footer_description', [
        'default' => 'The leading independent provider of IPO intelligence, subscription data, and exhaustive Grey Market Premium analysis.',
        'sanitize_callback' => 'sanitize_textarea_field'
    ]);
    $wp_customize->add_control('footer_description', [
        'label' => __('Footer Description', 'ipo-premium'),
        'section' => 'ipopro_footer_section',
        'type' => 'textarea',
    ]);

    // Copyright Text
    $wp_customize->add_setting('footer_copyright', ['default' => 'IPO GMP Premium', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('footer_copyright', [
        'label' => __('Copyright Name', 'ipo-premium'),
        'section' => 'ipopro_footer_section',
        'type' => 'text',
    ]);

    // Operational Badge Toggle
    $wp_customize->add_setting('footer_badge', ['default' => true, 'sanitize_callback' => 'wp_validate_boolean']);
    $wp_customize->add_control('footer_badge', [
        'label' => __('Show Operational Badge', 'ipo-premium'),
        'section' => 'ipopro_footer_section',
        'type' => 'checkbox',
    ]);

    // Operational Badge Label
    $wp_customize->add_setting('footer_badge_label', ['default' => 'System Operational', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('footer_badge_label', [
        'label' => __('Badge Label', 'ipo-premium'),
        'section' => 'ipopro_footer_section',
        'type' => 'text',
    ]);

    // Operational Badge Link
    $wp_customize->add_setting('footer_badge_link', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp_customize->add_control('footer_badge_link', [
        'label' => __('Badge Link (Optional)', 'ipo-premium'),
        'section' => 'ipopro_footer_section',
        'type' => 'url',
    ]);

    // Social Links (Instagram & YouTube & Twitter & LinkedIn)
    $wp_customize->add_setting('social_instagram', ['default' => '#', 'sanitize_callback' => 'esc_url_raw']);
    $wp_customize->add_control('social_instagram', [
        'label' => __('Instagram URL', 'ipo-premium'),
        'section' => 'ipopro_footer_section',
        'type' => 'url',
    ]);

    $wp_customize->add_setting('social_youtube', ['default' => '#', 'sanitize_callback' => 'esc_url_raw']);
    $wp_customize->add_control('social_youtube', [
        'label' => __('YouTube URL', 'ipo-premium'),
        'section' => 'ipopro_footer_section',
        'type' => 'url',
    ]);

    $wp_customize->add_setting('social_twitter', ['default' => '#', 'sanitize_callback' => 'esc_url_raw']);
    $wp_customize->add_control('social_twitter', [
        'label' => __('X (Twitter) URL', 'ipo-premium'),
        'section' => 'ipopro_footer_section',
        'type' => 'url',
    ]);

    $wp_customize->add_setting('social_linkedin', ['default' => '#', 'sanitize_callback' => 'esc_url_raw']);
    $wp_customize->add_control('social_linkedin', [
        'label' => __('LinkedIn URL', 'ipo-premium'),
        'section' => 'ipopro_footer_section',
        'type' => 'url',
    ]);

    // Footer Logo
    $wp_customize->add_setting('footer_logo', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'footer_logo', [
        'label' => __('Footer Logo', 'ipo-premium'),
        'section' => 'ipopro_footer_section',
    ]));

    // Fetch Menus for Selection
    $menus = wp_get_nav_menus();
    $menu_choices = ['' => __('-- Select Menu --', 'ipo-premium')];
    if (!empty($menus)) {
        foreach ($menus as $menu) {
            $menu_choices[$menu->term_id] = $menu->name;
        }
    }

    // Footer Column Titles & Menus
    // Col 1
    $wp_customize->add_setting('footer_col1_title', ['default' => 'Intelligence', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('footer_col1_title', [
        'label' => __('Column 1 Title', 'ipo-premium'),
        'section' => 'ipopro_footer_section',
        'type' => 'text',
    ]);

    $wp_customize->add_setting('footer_col1_menu', ['default' => '', 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('footer_col1_menu', [
        'label' => __('Column 1 Menu', 'ipo-premium'),
        'section' => 'ipopro_footer_section',
        'type' => 'select',
        'choices' => $menu_choices,
    ]);

    // Col 2
    $wp_customize->add_setting('footer_col2_title', ['default' => 'Investor Tools', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('footer_col2_title', [
        'label' => __('Column 2 Title', 'ipo-premium'),
        'section' => 'ipopro_footer_section',
        'type' => 'text',
    ]);

    $wp_customize->add_setting('footer_col2_menu', ['default' => '', 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('footer_col2_menu', [
        'label' => __('Column 2 Menu', 'ipo-premium'),
        'section' => 'ipopro_footer_section',
        'type' => 'select',
        'choices' => $menu_choices,
    ]);

    // Col 3
    $wp_customize->add_setting('footer_col3_title', ['default' => 'The Platform', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('footer_col3_title', [
        'label' => __('Column 3 Title', 'ipo-premium'),
        'section' => 'ipopro_footer_section',
        'type' => 'text',
    ]);

    $wp_customize->add_setting('footer_col3_menu', ['default' => '', 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('footer_col3_menu', [
        'label' => __('Column 3 Menu', 'ipo-premium'),
        'section' => 'ipopro_footer_section',
        'type' => 'select',
        'choices' => $menu_choices,
    ]);
}
add_action('customize_register', 'ipopro_customize_register');

/**
 * Generate Dynamic CSS
 */
function ipopro_customizer_css()
{
    ?>
    <style type="text/css">
        :root {
            /* Colors */
            --color-primary:
                <?php echo get_theme_mod('primary_color', '#0d7ff2'); ?>
            ;
            --color-bg:
                <?php echo get_theme_mod('ipopro_global_bg', '#050A18'); ?>
            ;
            --color-header-footer:
                <?php echo get_theme_mod('header_footer_bg', '#0B111D'); ?>
            ;
            --color-card:
                <?php echo get_theme_mod('card_bg_color', '#0B1220'); ?>
            ;
            --color-profit:
                <?php echo get_theme_mod('gmp_profit_color', '#00FF94'); ?>
            ;
            --color-loss:
                <?php echo get_theme_mod('gmp_loss_color', '#F87171'); ?>
            ;
            --color-hero-highlight:
                <?php echo get_theme_mod('hero_highlight_color', '#00FF94'); ?>
            ;

            /* Typography */
            --font-heading: '<?php echo get_theme_mod('heading_font', 'Inter'); ?>', sans-serif;
            --font-body: '<?php echo get_theme_mod('body_font', 'Inter'); ?>', sans-serif;

            /* UI */
            --radius-card:
                <?php echo get_theme_mod('border_radius', '12'); ?>
                px;
            --blur-glass:
                <?php echo get_theme_mod('glass_blur', '16'); ?>
                px;
        }

        /* Apply Variables */
        body {
            background-color: var(--color-bg);
            font-family: var(--font-body);
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: var(--font-heading);
        }

        .text-primary {
            color: var(--color-primary) !important;
        }

        .bg-primary {
            background-color: var(--color-primary) !important;
        }

        .text-neon-emerald {
            color: var(--color-profit) !important;
        }

        .text-red-400 {
            color: var(--color-loss) !important;
        }

        .text-hero-highlight {
            color: var(--color-hero-highlight) !important;
        }

        .glass-card {
            background: linear-gradient(135deg,
                    <?php echo ipopro_adjust_brightness(get_theme_mod('card_bg_color', '#0B1220'), 0); ?>
                    0%,
                    <?php echo ipopro_adjust_brightness(get_theme_mod('card_bg_color', '#0B1220'), -10); ?>
                    100%);
            backdrop-filter: blur(var(--blur-glass));
            -webkit-backdrop-filter: blur(var(--blur-glass));
            border-radius: var(--radius-card);
        }

        /* Footer Widget Styling */
        footer .widget ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            /* space-y-4 equivalent */
        }

        footer .widget ul li {
            margin: 0;
        }

        footer .widget ul li a {
            color: #64748b;
            /* text-slate-500 */
            font-size: 13px;
            font-weight: 500;
            transition: color 0.2s;
            text-decoration: none;
        }

        footer .widget ul li a:hover {
            color: var(--color-primary);
        }
    </style>
    <?php
}
add_action('wp_head', 'ipopro_customizer_css');

/**
 * Helper: Adjust Color Brightness for Gradients
 */
function ipopro_adjust_brightness($hex, $steps)
{
    // Steps should be between -255 and 255. Negative = darker, Positive = lighter
    $steps = max(-255, min(255, $steps));
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));

    return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
}
