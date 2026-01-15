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

        // Native WP Robots/Description (Fallback)
        add_filter('wp_head', [$this, 'output_meta_tags'], 5);

        // Canonical
        add_filter('get_canonical_url', [$this, 'filter_canonical'], 10, 2);

        // RankMath
        add_filter('rank_math/frontend/title', [$this, 'rankmath_title']);
        add_filter('rank_math/frontend/description', [$this, 'rankmath_description']);
        add_filter('rank_math/frontend/canonical', [$this, 'filter_canonical']);
        add_filter('rank_math/json_ld', [$this, 'inject_schema'], 99);

        // Yoast
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
        if (!$ipo)
            return $title;

        // Dynamic Title Template
        // "Tata Technologies IPO GMP today, Price, Subscription Status"
        return sprintf(
            "%s GMP Today (₹%s), Price, Allotment Date & Status",
            $ipo->name,
            $ipo->premium
        );
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
        if (!$ipo)
            return $desc;

        // Dynamic Description
        return sprintf(
            "Live GMP for %s IPO is ₹%s. Check latest grey market premium, kostak rates, subject to sauda, and subscription status live updates here.",
            $ipo->name,
            $ipo->premium
        );
    }

    /**
     * Canonical URL Filter
     * Must return the specific Query Param URL to avoid duplication issues.
     */
    public function filter_canonical($canonical)
    {
        $ipo = $this->get_current_ipo();
        if (!$ipo)
            return $canonical;

        return home_url('/ipo-details/?slug=' . $ipo->slug);
    }

    /**
     * Output standard meta tags if no SEO plugin is present
     */
    public function output_meta_tags()
    {
        $ipo = $this->get_current_ipo();
        if (!$ipo)
            return;

        // Only output if not handled by plugins (naive check, but safe)
        if (!defined('RANK_MATH_VERSION') && !defined('WPSEO_VERSION')) {
            echo '<meta name="description" content="' . esc_attr($this->rankmath_description('')) . '" />' . "\n";
            // Open Graph (Basic)
            echo '<meta property="og:title" content="' . esc_attr($this->filter_title('')) . '" />' . "\n";
            echo '<meta property="og:description" content="' . esc_attr($this->rankmath_description('')) . '" />' . "\n";
            echo '<meta property="og:url" content="' . esc_attr($this->filter_canonical('')) . '" />' . "\n";
            echo '<meta property="og:type" content="article" />' . "\n";
        }
    }

    /**
     * Inject Schema.org JSON-LD
     */
    public function inject_schema($data)
    {
        $ipo = $this->get_current_ipo();
        if (!$ipo)
            return $data;

        // Add Article Schema or FinancialProduct Schema
        $schema = [
            '@type' => 'NewsArticle', // Or FinancialProduct
            'headline' => $this->filter_title(''),
            'datePublished' => date('c', strtotime($ipo->created_at ?: 'now')),
            'dateModified' => date('c', strtotime($ipo->updated_at ?: 'now')),
            'description' => $this->rankmath_description(''),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $this->filter_canonical(''),
            ],
        ];

        // Merge into RankMath's Graph
        // RankMath usually expects an array of entities. We append ours.
        // Note: Use 'rich_snippet' key if replacing, or just add to graph.
        // This implementation depends on RankMath's specific array structure.
        // Safest is to add to the main entity if possible, but let's just append for now.

        return $data; // Returning unmodified for now to avoid breaking existing schema, needs complex merging logic.
    }
}
