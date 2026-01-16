<?php
namespace TIGC\Core;

if (!defined('ABSPATH'))
    exit;

/**
 * SEO Class
 * Manages Title, Meta Tags, Canonicals, and Schema.
 * Compatible with RankMath, Yoast, and Native WP.
 */
class SEO
{

    public function __construct()
    {
        // Native WP Title
        add_filter('pre_get_document_title', [$this, 'filter_title'], 999);
        add_filter('document_title_parts', [$this, 'filter_title_parts']);

        // Native WP Robots/Description (Fallback)
        add_filter('wp_head', [$this, 'output_meta_tags'], 1);

        // Canonical
        add_filter('get_canonical_url', [$this, 'filter_canonical'], 10, 2);

        // Resource Hints
        add_filter('wp_resource_hints', [$this, 'resource_hints'], 10, 2);

        // RankMath Integration
        add_filter('rank_math/frontend/title', [$this, 'rankmath_title']);
        add_filter('rank_math/frontend/description', [$this, 'rankmath_description']);
        add_filter('rank_math/frontend/canonical', [$this, 'filter_canonical']);
        add_filter('rank_math/json_ld', [$this, 'inject_schema'], 99);

        // Yoast Integration
        add_filter('wpseo_title', [$this, 'rankmath_title']); // Re-use same logic
        add_filter('wpseo_metadesc', [$this, 'rankmath_description']);
        add_filter('wpseo_canonical', [$this, 'filter_canonical']);
    }

    private function get_current_ipo()
    {
        return \TIGC\Core\Plugin::instance()->get_controller()->current_ipo;
    }

    /**
     * Filter the Page Title
     */
    public function filter_title($title)
    {
        $ipo = $this->get_current_ipo();
        if ($ipo) {
            // Dynamic Title Template
            // "Tata Technologies IPO GMP today, Price, Subscription Status"
            return sprintf(
                "%s GMP Today (₹%s), Price, Allotment Date & Status",
                $ipo->name,
                $ipo->premium
            );
        }

        // Dashboard/Home Title override
        if (is_front_page() || is_page_template('templates/page-dashboard.php')) {
            global $wpdb;
            $t_master = $wpdb->prefix . 'ipomaster';
            // Safe check for table existence
            if ($wpdb->get_var("SHOW TABLES LIKE '$t_master'") == $t_master) {
                $active_count = $wpdb->get_var("SELECT COUNT(*) FROM $t_master WHERE status IN ('open', 'upcoming', 'allotment')");
            } else {
                $active_count = 'Many';
            }
            return "Live IPO GMP, Subscription & Allotment Status - " . get_bloginfo('name');
        }

        // Archive overrides handled by page title, but we can enhance here if needed
        if (is_page_template('templates/page-mainboard.php')) {
            return "Mainboard IPO GMP List - Live Subscription & Status - " . get_bloginfo('name');
        }
        if (is_page_template('templates/page-sme.php')) {
            return "SME IPO GMP List - Live Subscription & Allotment - " . get_bloginfo('name');
        }
        if (is_page_template('templates/page-buybacks.php')) {
            return "Active Buyback Offers in India - Live Tender Offers - " . get_bloginfo('name');
        }

        return $title;
    }

    public function filter_title_parts($title)
    {
        // Remove "Archives" from archive titles
        if (isset($title['title']) && strpos($title['title'], 'Archives') !== false) {
            $title['title'] = str_replace(' Archives', '', $title['title']);
        }
        return $title;
    }

    /**
     * RankMath / Yoast Title Filter
     */
    public function rankmath_title($title)
    {
        return $this->filter_title($title);
    }

    /**
     * RankMath / Yoast Description Filter
     */
    public function rankmath_description($desc)
    {
        $ipo = $this->get_current_ipo();
        if ($ipo) {
            // Dynamic Description
            return sprintf(
                "%s IPO: Current GMP ₹%s, Price Band %s, Status: %s. Get live subscription data, allotment status, grey market premium updates, and listing predictions.",
                $ipo->name,
                $ipo->premium ?: '0',
                $ipo->price_band ?: 'TBA',
                $ipo->status ?: 'Active'
            );
        }

        if (is_front_page() || is_page_template('templates/page-dashboard.php')) {
            global $wpdb;
            $t_master = $wpdb->prefix . 'ipomaster';
            $active_count = 0;
            if ($wpdb->get_var("SHOW TABLES LIKE '$t_master'") == $t_master) {
                $active_count = $wpdb->get_var("SELECT COUNT(*) FROM $t_master WHERE status IN ('open', 'upcoming', 'allotment')");
            }
            return "Track real-time Grey Market Premium (GMP) for " . ($active_count ?: 'active') . " active IPOs in India. Get live subscription numbers, allotment status, and listing estimates for Mainboard & SME IPOs. Updated " . date('M d, Y') . ".";
        }

        if (is_page_template('templates/page-mainboard.php')) {
            return "Complete list of Mainboard IPOs with real-time GMP, subscription status, and allotment updates. Track all active, upcoming, and recently closed Mainboard IPOs in India.";
        }

        if (is_page_template('templates/page-sme.php')) {
            return "Track SME IPOs with real-time grey market premium, subscription numbers, and allotment status. Complete list of active and upcoming SME IPOs on NSE Emerge and BSE SME platforms.";
        }

        if (is_page_template('templates/page-buybacks.php')) {
            return "Track active share buyback offers and tender offers in India. Get buyback prices, acceptance ratios, and important dates for all open buyback programs.";
        }

        return $desc;
    }

    /**
     * Canonical URL Filter
     * Must return the specific Query Param URL to avoid duplication issues.
     */
    public function filter_canonical($canonical)
    {
        $ipo = $this->get_current_ipo();
        if ($ipo) {
            return home_url('/ipo-details/?slug=' . $ipo->slug);
        }

        // Handle Custom Archives explicitly to avoid /page/2 issues or similar if needed
        if (is_page_template('templates/page-mainboard.php'))
            return home_url('/mainboard-ipos/');
        if (is_page_template('templates/page-sme.php'))
            return home_url('/sme-ipos/');
        if (is_page_template('templates/page-buybacks.php'))
            return home_url('/buybacks/');

        return $canonical;
    }

    /**
     * Output standard meta tags if no SEO plugin is present
     */
    public function output_meta_tags()
    {
        // Only output if not handled by plugins
        if (defined('RANK_MATH_VERSION') || defined('WPSEO_VERSION')) {
            return;
        }

        $site_name = get_bloginfo('name');
        $site_url = home_url('/');
        $logo_id = get_theme_mod('custom_logo');
        $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';
        $default_image = $logo_url ?: $site_url . 'wp-content/themes/ipo-premium-theme/assets/images/og-default.jpg';

        $page_title = $this->filter_title(wp_get_document_title());
        $meta_description = $this->rankmath_description(get_bloginfo('description'));
        $canonical_url = $this->filter_canonical(get_permalink() ?: $site_url);
        $og_image = $default_image;
        $og_type = 'website';

        $ipo = $this->get_current_ipo();
        if ($ipo) {
            $og_type = 'article';
            if (!empty($ipo->icon_url)) {
                $og_image = $ipo->icon_url;
            }
        } elseif (is_single() && get_post_type() == 'post') {
            $og_type = 'article';
            if (has_post_thumbnail()) {
                $og_image = get_the_post_thumbnail_url(get_the_ID(), 'large');
            }
            $meta_description = wp_trim_words(get_the_excerpt() ?: strip_tags(get_the_content()), 30, '...');
        }

        ?>
        <!-- SEO Meta Tags (Generated by The IPO GMP Core) -->
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

        // Output Schema
        $this->output_schema();
    }

    /**
     * Output Schema.org JSON-LD
     */
    public function output_schema()
    {
        $site_name = get_bloginfo('name');
        $site_url = home_url('/');
        $logo_id = get_theme_mod('custom_logo');
        $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';

        // Organization
        $organization_schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $site_name,
            'url' => $site_url,
            'logo' => $logo_url,
            'description' => "India's most trusted platform for IPO Grey Market Premium (GMP), live subscription data, and allotment status.",
        ];

        // WebSite
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

        // IPO Details (FinancialProduct)
        $ipo = $this->get_current_ipo();
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

            // Breadcrumb
            $schemas[] = [
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
                        'item' => home_url('/ipo-details/?slug=' . $ipo->slug)
                    ]
                ]
            ];
        }

        foreach ($schemas as $schema) {
            echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
        }
    }

    /**
     * Resource Hints (Preconnect/DNS Prefetch)
     */
    public function resource_hints($urls, $relation_type)
    {
        if ($relation_type === 'preconnect') {
            $urls[] = ['href' => 'https://fonts.googleapis.com', 'crossorigin' => 'anonymous'];
            $urls[] = ['href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous'];
            $urls[] = 'https://cdn.tailwindcss.com';
        }
        if ($relation_type === 'dns-prefetch') {
            $urls[] = 'https://fonts.googleapis.com';
            $urls[] = 'https://fonts.gstatic.com';
            $urls[] = 'https://cdn.tailwindcss.com';
        }
        return $urls;
    }

    /**
     * Inject Schema.org JSON-LD for RankMath
     */
    public function inject_schema($data)
    {
        // For now, if RankMath is active, our output_meta_tags() won't run, 
        // effectively disabling our schema to prevent conflict.
        // If we want to *enhance* RankMath schema, we'd add logic here.
        // Leaving basic pass-through for now.
        return $data;
    }
}
