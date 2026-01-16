<?php
namespace TIGC\Core;

if (!defined('ABSPATH'))
    exit;

/**
 * Sitemap Class
 * Generates custom XML Sitemaps for IPOs and Posts.
 */
class Sitemap
{

    public function __construct()
    {
        // Register Rewrite Rules
        add_action('init', [$this, 'add_rewrite_rules']);

        // Register Query Var
        add_filter('query_vars', [$this, 'add_query_vars']);

        // Handle Template Redirect
        add_action('template_redirect', [$this, 'template_redirect']);

        // Flush on activation (handled by main plugin file typically, but added safely here)
        register_activation_hook(__FILE__, 'flush_rewrite_rules');
    }

    /**
     * Rewrite Rules
     */
    public function add_rewrite_rules()
    {
        add_rewrite_rule('^sitemap\.xml$', 'index.php?ipopro_sitemap=main', 'top');
        add_rewrite_rule('^sitemap-ipos\.xml$', 'index.php?ipopro_sitemap=ipos', 'top');
        add_rewrite_rule('^sitemap-posts\.xml$', 'index.php?ipopro_sitemap=posts', 'top');
    }

    /**
     * Query Vars
     */
    public function add_query_vars($vars)
    {
        $vars[] = 'ipopro_sitemap';
        return $vars;
    }

    /**
     * Output XML
     */
    public function template_redirect()
    {
        $sitemap_type = get_query_var('ipopro_sitemap');

        if (!$sitemap_type) {
            return;
        }

        header('Content-Type: application/xml; charset=utf-8');
        header('X-Robots-Tag: noindex, follow');

        if ($sitemap_type === 'main') {
            $this->generate_index();
        } elseif ($sitemap_type === 'ipos') {
            $this->generate_ipos();
        } elseif ($sitemap_type === 'posts') {
            $this->generate_posts();
        }

        exit;
    }

    /**
     * Generate Index
     */
    private function generate_index()
    {
        $site_url = home_url('/');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        // removed xsl ref to keep it simple self-contained
        ?>
        <sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
            <sitemap>
                <loc><?php echo esc_url($site_url); ?>sitemap-ipos.xml</loc>
                <lastmod><?php echo date('c'); ?></lastmod>
            </sitemap>
            <sitemap>
                <loc><?php echo esc_url($site_url); ?>sitemap-posts.xml</loc>
                <lastmod><?php echo date('c'); ?></lastmod>
            </sitemap>
        </sitemapindex>
        <?php
    }

    /**
     * Generate IPO Sitemap
     */
    private function generate_ipos()
    {
        global $wpdb;
        $site_url = home_url('/');
        $t_master = $wpdb->prefix . 'ipomaster';

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        ?>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
            xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
            <!-- Homepage -->
            <url>
                <loc><?php echo esc_url($site_url); ?></loc>
                <lastmod><?php echo date('c'); ?></lastmod>
                <changefreq>hourly</changefreq>
                <priority>1.0</priority>
            </url>

            <!-- Archive Pages -->
            <url>
                <loc><?php echo esc_url($site_url); ?>mainboard-ipos/</loc>
                <changefreq>daily</changefreq>
                <priority>0.9</priority>
            </url>
            <url>
                <loc><?php echo esc_url($site_url); ?>sme-ipos/</loc>
                <changefreq>daily</changefreq>
                <priority>0.9</priority>
            </url>
            <url>
                <loc><?php echo esc_url($site_url); ?>buybacks/</loc>
                <changefreq>daily</changefreq>
                <priority>0.8</priority>
            </url>

            <!-- IPO Detail Pages -->
            <?php
            // Check table exists first
            if ($wpdb->get_var("SHOW TABLES LIKE '$t_master'") == $t_master) {
                $ipos = $wpdb->get_results("SELECT slug, updated_at, icon_url FROM $t_master WHERE slug IS NOT NULL AND slug != '' ORDER BY id DESC LIMIT 1000");
                foreach ($ipos as $ipo) {
                    $url = $site_url . 'ipo-details/?slug=' . $ipo->slug;
                    $lastmod = $ipo->updated_at ? date('c', strtotime($ipo->updated_at)) : date('c');
                    ?>
                    <url>
                        <loc><?php echo esc_url($url); ?></loc>
                        <lastmod><?php echo $lastmod; ?></lastmod>
                        <changefreq>daily</changefreq>
                        <priority>0.8</priority>
                        <?php
                        // Validate image URL is HTTP
                        if (!empty($ipo->icon_url) && filter_var($ipo->icon_url, FILTER_VALIDATE_URL)): ?>
                            <image:image>
                                <image:loc><?php echo esc_url($ipo->icon_url); ?></image:loc>
                            </image:image>
                        <?php endif; ?>
                    </url>
                    <?php
                }
            }
            ?>
        </urlset>
        <?php
    }

    /**
     * Generate Posts Sitemap
     */
    private function generate_posts()
    {
        $site_url = home_url('/');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        ?>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
            <?php
            $posts = get_posts([
                'post_type' => 'post',
                'post_status' => 'publish',
                'numberposts' => 500,
                'orderby' => 'modified',
                'order' => 'DESC'
            ]);

            foreach ($posts as $post) {
                $url = get_permalink($post->ID);
                $lastmod = date('c', strtotime($post->post_modified));
                ?>
                <url>
                    <loc><?php echo esc_url($url); ?></loc>
                    <lastmod><?php echo $lastmod; ?></lastmod>
                    <changefreq>weekly</changefreq>
                    <priority>0.6</priority>
                </url>
                <?php
            }
            ?>
        </urlset>
        <?php
    }
}
