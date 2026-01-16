<?php
/**
 * SEO Functions for IPO Premium Theme
 * 
 * Handles all SEO-related functionality including:
 * - Meta descriptions and titles
 * - Open Graph tags
 * - Twitter Cards
 * - Schema.org structured data
 * - Canonical URLs
 * 
 * @package IPO_Premium
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main SEO Meta Tags Output
 * Hooked into wp_head with priority 1
 */
function ipopro_seo_meta_tags()
{
    global $wpdb, $post;

    // Get site info
    $site_name = get_bloginfo('name');
    $site_url = home_url('/');

    // Get custom logo URL for social sharing
    $logo_id = get_theme_mod('custom_logo');
    $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';

    // Default fallback image (1200x630 recommended for OG)
    $default_image = $logo_url ?: $site_url . 'wp-content/themes/ipo-premium-theme/assets/images/og-default.jpg';

    // Determine page type and set appropriate meta data
    $page_title = '';
    $meta_description = '';
    $canonical_url = '';
    $og_image = $default_image;
    $og_type = 'website';

    // Homepage / Dashboard
    if (is_front_page() || is_page_template('templates/page-dashboard.php')) {
        $t_master = $wpdb->prefix . 'ipomaster';
        $active_count = $wpdb->get_var("SELECT COUNT(*) FROM $t_master WHERE status IN ('open', 'upcoming', 'allotment')");

        $page_title = "Live IPO GMP, Subscription & Allotment Status - $site_name";
        $meta_description = "Track real-time Grey Market Premium (GMP) for $active_count active IPOs in India. Get live subscription numbers, allotment status, and listing estimates for Mainboard & SME IPOs. Updated " . date('M d, Y') . ".";
        $canonical_url = $site_url;
    }
    // Single IPO Details Page
    elseif (isset($_GET['slug']) && !empty($_GET['slug'])) {
        $slug = sanitize_text_field($_GET['slug']);
        $t_master = $wpdb->prefix . 'ipomaster';
        $ipo = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t_master WHERE slug = %s", $slug));

        if ($ipo) {
            $gmp = $ipo->premium ?: '0';
            $price_band = $ipo->price_band ?: 'TBA';
            $status = $ipo->status ?: 'Active';

            $page_title = "{$ipo->name} IPO GMP, Subscription & Allotment Status - $site_name";
            $meta_description = "{$ipo->name} IPO: Current GMP ₹{$gmp}, Price Band {$price_band}, Status: {$status}. Get live subscription data, allotment status, grey market premium updates, and listing predictions.";
            $canonical_url = home_url('/ipo-details/?slug=' . $slug);
            $og_type = 'article';

            // Use IPO icon if available
            if (!empty($ipo->icon_url)) {
                $og_image = $ipo->icon_url;
            }
        }
    }
    // Mainboard Archive
    elseif (is_page_template('templates/page-mainboard.php') || strpos($_SERVER['REQUEST_URI'], '/mainboard-ipos') !== false) {
        $page_title = "Mainboard IPO GMP List - Live Subscription & Status - $site_name";
        $meta_description = "Complete list of Mainboard IPOs with real-time GMP, subscription status, and allotment updates. Track all active, upcoming, and recently closed Mainboard IPOs in India.";
        $canonical_url = home_url('/mainboard-ipos/');
    }
    // SME Archive
    elseif (is_page_template('templates/page-sme.php') || strpos($_SERVER['REQUEST_URI'], '/sme-ipos') !== false) {
        $page_title = "SME IPO GMP List - Live Subscription & Allotment - $site_name";
        $meta_description = "Track SME IPOs with real-time grey market premium, subscription numbers, and allotment status. Complete list of active and upcoming SME IPOs on NSE Emerge and BSE SME platforms.";
        $canonical_url = home_url('/sme-ipos/');
    }
    // Buybacks Page
    elseif (is_page_template('templates/page-buybacks.php') || strpos($_SERVER['REQUEST_URI'], '/buybacks') !== false) {
        $page_title = "Active Buyback Offers in India - Live Tender Offers - $site_name";
        $meta_description = "Track active share buyback offers and tender offers in India. Get buyback prices, acceptance ratios, and important dates for all open buyback programs.";
        $canonical_url = home_url('/buybacks/');
    }
    // Blog/Posts
    elseif (is_single() && get_post_type() == 'post') {
        $page_title = get_the_title() . " - $site_name";
        $meta_description = wp_trim_words(get_the_excerpt() ?: strip_tags(get_the_content()), 30, '...');
        $canonical_url = get_permalink();
        $og_type = 'article';

        if (has_post_thumbnail()) {
            $og_image = get_the_post_thumbnail_url(get_the_ID(), 'large');
        }
    }
    // Default fallback
    else {
        $page_title = wp_get_document_title();
        $meta_description = get_bloginfo('description') ?: "India's most trusted platform for IPO Grey Market Premium (GMP), live subscription data, and allotment status updates.";
        $canonical_url = get_permalink() ?: $site_url;
    }

    // Output Meta Tags
    ?>
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo esc_attr($meta_description); ?>">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <link rel="canonical" href="<?php echo esc_url($canonical_url); ?>">

    <!-- Open Graph Tags -->
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="<?php echo esc_attr($og_type); ?>">
    <meta property="og:title" content="<?php echo esc_attr($page_title); ?>">
    <meta property="og:description" content="<?php echo esc_attr($meta_description); ?>">
    <meta property="og:url" content="<?php echo esc_url($canonical_url); ?>">
    <meta property="og:site_name" content="<?php echo esc_attr($site_name); ?>">
    <meta property="og:image" content="<?php echo esc_url($og_image); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo esc_attr($page_title); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr($meta_description); ?>">
    <meta name="twitter:image" content="<?php echo esc_url($og_image); ?>">

    <!-- Additional Meta Tags -->
    <meta name="author" content="<?php echo esc_attr($site_name); ?>">
    <meta name="theme-color" content="#0D7FF2">
    <?php

    // Output Schema.org Structured Data
    ipopro_schema_output();
}

/**
 * Schema.org Structured Data Output
 */
function ipopro_schema_output()
{
    global $wpdb;

    $site_name = get_bloginfo('name');
    $site_url = home_url('/');
    $logo_id = get_theme_mod('custom_logo');
    $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';

    // Organization Schema (appears on all pages)
    $organization_schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $site_name,
        'url' => $site_url,
        'logo' => $logo_url,
        'description' => "India's most trusted platform for IPO Grey Market Premium (GMP), live subscription data, and allotment status.",
        'sameAs' => [
            // Add your social media profiles here
            // 'https://www.facebook.com/yourpage',
            // 'https://twitter.com/yourhandle',
            // 'https://www.linkedin.com/company/yourcompany',
        ]
    ];

    // WebSite Schema with Search Action
    $website_schema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $site_name,
        'url' => $site_url,
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => $site_url . 'mainboard-ipos/?q={search_term_string}'
            ],
            'query-input' => 'required name=search_term_string'
        ]
    ];

    $schemas = [$organization_schema, $website_schema];

    // IPO-specific Schema (Financial Product)
    if (isset($_GET['slug']) && !empty($_GET['slug'])) {
        $slug = sanitize_text_field($_GET['slug']);
        $t_master = $wpdb->prefix . 'ipomaster';
        $ipo = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t_master WHERE slug = %s", $slug));

        if ($ipo) {
            $ipo_schema = [
                '@context' => 'https://schema.org',
                '@type' => 'FinancialProduct',
                'name' => $ipo->name . ' IPO',
                'description' => "IPO details for {$ipo->name} including Grey Market Premium, subscription status, and allotment information.",
                'category' => $ipo->is_sme ? 'SME IPO' : 'Mainboard IPO',
                'offers' => [
                    '@type' => 'Offer',
                    'price' => preg_replace('/[^0-9.]/', '', $ipo->max_price ?: '0'),
                    'priceCurrency' => 'INR',
                    'availability' => $ipo->status === 'open' ? 'https://schema.org/InStock' : 'https://schema.org/PreOrder',
                    'validFrom' => $ipo->open_date,
                    'validThrough' => $ipo->close_date
                ]
            ];

            $schemas[] = $ipo_schema;
        }
    }

    // Breadcrumb Schema for detail pages
    if (isset($_GET['slug'])) {
        $breadcrumb_schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => $site_url
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'IPO Details',
                    'item' => home_url('/ipo-details/?slug=' . sanitize_text_field($_GET['slug']))
                ]
            ]
        ];

        $schemas[] = $breadcrumb_schema;
    }

    // Output all schemas
    foreach ($schemas as $schema) {
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
    }
}

/**
 * Add preconnect and DNS prefetch for performance
 */
function ipopro_resource_hints($urls, $relation_type)
{
    if ($relation_type === 'preconnect') {
        $urls[] = [
            'href' => 'https://fonts.googleapis.com',
            'crossorigin' => 'anonymous'
        ];
        $urls[] = [
            'href' => 'https://fonts.gstatic.com',
            'crossorigin' => 'anonymous'
        ];
        $urls[] = 'https://cdn.tailwindcss.com';
    }

    if ($relation_type === 'dns-prefetch') {
        $urls[] = 'https://fonts.googleapis.com';
        $urls[] = 'https://fonts.gstatic.com';
        $urls[] = 'https://cdn.tailwindcss.com';
    }

    return $urls;
}
add_filter('wp_resource_hints', 'ipopro_resource_hints', 10, 2);

/**
 * Optimize title tag output
 */
function ipopro_document_title_parts($title)
{
    // Remove "Archives" from archive titles
    if (isset($title['title']) && strpos($title['title'], 'Archives') !== false) {
        $title['title'] = str_replace(' Archives', '', $title['title']);
    }

    return $title;
}
add_filter('document_title_parts', 'ipopro_document_title_parts');
