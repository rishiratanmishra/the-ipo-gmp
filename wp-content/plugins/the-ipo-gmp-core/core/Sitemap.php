<?php
namespace TIGC\Core;

if (!defined('ABSPATH'))
    exit;

/**
 * Sitemap Class
 * Integrates with WP Native Sitemaps.
 */
class Sitemap
{

    public function __construct()
    {
        // Clean up default sitemaps (Disable Users & Taxonomies)
        add_filter('wp_sitemaps_add_provider', [$this, 'filter_providers'], 10, 2);
        add_filter('wp_sitemaps_post_types', [$this, 'filter_post_types']);

        // Register Custom Provider
        add_action('init', [$this, 'register_provider']);
    }

    public function filter_providers($provider, $name)
    {
        // Only allow 'posts' and our custom 'ipos'
        if ($name !== 'posts' && $name !== 'ipos') {
            return false;
        }
        return $provider;
    }

    public function filter_post_types($post_types)
    {
        return [
            'post' => $post_types['post'],
            'page' => $post_types['page'],
        ];
    }

    public function register_provider()
    {
        if (class_exists('WP_Sitemaps_Provider')) {
            $provider = new IPO_Sitemap_Provider();
            wp_register_sitemap_provider('ipos', $provider);
        }
    }
}

/**
 * Custom Sitemap Provider Class
 */
if (class_exists('WP_Sitemaps_Provider')) {
    class IPO_Sitemap_Provider extends \WP_Sitemaps_Provider
    {
        public function __construct()
        {
            $this->name = 'ipos'; // Provider name
            $this->object_type = 'custom'; // Object type
        }

        public function get_url_list($page_num, $object_subtype = '')
        {
            global $wpdb;
            $limit = 2000;
            $offset = ($page_num - 1) * $limit;
            $table_name = $wpdb->prefix . 'ipomaster';

            // Only Active/Open IPOs? Or all? Usually all for SEO.
            $results = $wpdb->get_results(
                $wpdb->prepare("SELECT slug, updated_at FROM $table_name ORDER BY id DESC LIMIT %d OFFSET %d", $limit, $offset)
            );

            $url_list = [];
            foreach ($results as $row) {
                $url_list[] = [
                    'loc' => home_url('/ipo-details/?slug=' . $row->slug),
                    'lastmod' => !empty($row->updated_at) ? date('c', strtotime($row->updated_at)) : null,
                ];
            }
            return $url_list;
        }

        public function get_max_num_pages($object_subtype = '')
        {
            global $wpdb;
            $table_name = $wpdb->prefix . 'ipomaster';
            $total = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            return ceil($total / 2000);
        }
    }
}
