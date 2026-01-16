<?php
/**
 * XML Sitemap Generator for IPO Premium Theme
 * 
 * Generates dynamic XML sitemaps for:
 * - Homepage
 * - IPO detail pages
 * - Archive pages
 * - Blog posts
 * 
 * @package IPO_Premium
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register sitemap rewrite rules
 */
function ipopro_sitemap_rewrite_rules()
{
    add_rewrite_rule('^sitemap\.xml$', 'index.php?ipopro_sitemap=main', 'top');
    add_rewrite_rule('^sitemap-ipos\.xml$', 'index.php?ipopro_sitemap=ipos', 'top');
    add_rewrite_rule('^sitemap-posts\.xml$', 'index.php?ipopro_sitemap=posts', 'top');
}
add_action('init', 'ipopro_sitemap_rewrite_rules');

/**
 * Add custom query vars
 */
function ipopro_sitemap_query_vars($vars)
{
    $vars[] = 'ipopro_sitemap';
    return $vars;
}
add_filter('query_vars', 'ipopro_sitemap_query_vars');

/**
 * Handle sitemap requests
 */
function ipopro_sitemap_template_redirect()
{
    $sitemap_type = get_query_var('ipopro_sitemap');

    if (!$sitemap_type) {
        return;
    }

    header('Content-Type: application/xml; charset=utf-8');
    header('X-Robots-Tag: noindex, follow');

    if ($sitemap_type === 'main') {
        ipopro_generate_sitemap_index();
    } elseif ($sitemap_type === 'ipos') {
        ipopro_generate_ipo_sitemap();
    } elseif ($sitemap_type === 'posts') {
        ipopro_generate_posts_sitemap();
    }

    exit;
}
// add_action('template_redirect', 'ipopro_sitemap_template_redirect');

/**
 * Generate main sitemap index
 */
function ipopro_generate_sitemap_index()
{
    $site_url = home_url('/');

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<?xml-stylesheet type="text/xsl" href="' . esc_url($site_url) . 'wp-content/themes/ipo-premium-theme/assets/sitemap.xsl"?>' . "\n";
    ?>
    <sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
        <sitemap>
            <loc>
                <?php echo esc_url($site_url); ?>sitemap-ipos.xml
            </loc>
            <lastmod>
                <?php echo date('c'); ?>
            </lastmod>
        </sitemap>
        <sitemap>
            <loc>
                <?php echo esc_url($site_url); ?>sitemap-posts.xml
            </loc>
            <lastmod>
                <?php echo date('c'); ?>
            </lastmod>
        </sitemap>
    </sitemapindex>
    <?php
}

/**
 * Generate IPO sitemap
 */
function ipopro_generate_ipo_sitemap()
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
            <loc>
                <?php echo esc_url($site_url); ?>
            </loc>
            <lastmod>
                <?php echo date('c'); ?>
            </lastmod>
            <changefreq>hourly</changefreq>
            <priority>1.0</priority>
        </url>

        <!-- Archive Pages -->
        <url>
            <loc>
                <?php echo esc_url($site_url); ?>mainboard-ipos/
            </loc>
            <lastmod>
                <?php echo date('c'); ?>
            </lastmod>
            <changefreq>daily</changefreq>
            <priority>0.9</priority>
        </url>
        <url>
            <loc>
                <?php echo esc_url($site_url); ?>sme-ipos/
            </loc>
            <lastmod>
                <?php echo date('c'); ?>
            </lastmod>
            <changefreq>daily</changefreq>
            <priority>0.9</priority>
        </url>
        <url>
            <loc>
                <?php echo esc_url($site_url); ?>buybacks/
            </loc>
            <lastmod>
                <?php echo date('c'); ?>
            </lastmod>
            <changefreq>daily</changefreq>
            <priority>0.8</priority>
        </url>

        <!-- IPO Detail Pages -->
        <?php
        $ipos = $wpdb->get_results("SELECT slug, updated_at, icon_url FROM $t_master WHERE slug IS NOT NULL AND slug != '' ORDER BY id DESC LIMIT 1000");

        foreach ($ipos as $ipo) {
            $url = $site_url . 'ipo-details/?slug=' . $ipo->slug;
            $lastmod = $ipo->updated_at ? date('c', strtotime($ipo->updated_at)) : date('c');
            ?>
            <url>
                <loc>
                    <?php echo esc_url($url); ?>
                </loc>
                <lastmod>
                    <?php echo $lastmod; ?>
                </lastmod>
                <changefreq>daily</changefreq>
                <priority>0.8</priority>
                <?php if (!empty($ipo->icon_url)): ?>
                    <image:image>
                        <image:loc>
                            <?php echo esc_url($ipo->icon_url); ?>
                        </image:loc>
                    </image:image>
                <?php endif; ?>
            </url>
            <?php
        }
        ?>
    </urlset>
    <?php
}

/**
 * Generate posts sitemap
 */
function ipopro_generate_posts_sitemap()
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
                <loc>
                    <?php echo esc_url($url); ?>
                </loc>
                <lastmod>
                    <?php echo $lastmod; ?>
                </lastmod>
                <changefreq>weekly</changefreq>
                <priority>0.6</priority>
            </url>
            <?php
        }
        ?>
    </urlset>
    <?php
}

/**
 * Flush rewrite rules on theme activation
 */
function ipopro_sitemap_activation()
{
    ipopro_sitemap_rewrite_rules();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'ipopro_sitemap_activation');
