<?php
namespace TIGC\Core;

if (!defined('ABSPATH'))
    exit;

/**
 * Controller Class
 * Handles data fetching, 404 validation, and mocking the Post Object.
 */
class Controller
{

    public $current_ipo = null;
    public $current_details = null;

    public function __construct()
    {
        // Run BEFORE template_include to setup state
        add_action('template_redirect', [$this, 'setup_ipo_context'], 10);
    }

    /**
     * Main logic to detect IPO context and setup data
     */
    public function setup_ipo_context()
    {
        // 1. Check if we are on the IPO Details Page
        // We check for both path and page ID to be safe
        if (!is_page('ipo-details')) {
            return;
        }

        // 2. Check for Slug
        $slug = isset($_GET['slug']) ? sanitize_text_field($_GET['slug']) : '';
        if (empty($slug)) {
            // Let the standard page render (or it might be an empty state)
            // If strict, we could 404, but maybe the user has a "Please search" content on this page.
            return;
        }

        // 3. Fetch Data
        global $wpdb;
        $t_master = $wpdb->prefix . 'ipomaster';
        $t_details = $wpdb->prefix . 'ipodetails';

        $ipo = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t_master WHERE slug = %s", $slug));

        if (!$ipo) {
            // 404 Trigger
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
            // Don't modify post object, let WP handle 404 template
            return;
        }

        // 4. Determine Post Status
        // If status is not 'Active' or 'Open', maybe we still show it (it's historic data).
        // Standard behavior for this site seems to be show everything.

        $this->current_ipo = $ipo;

        // Fetch Details
        $details_row = $wpdb->get_row($wpdb->prepare("SELECT details_json FROM $t_details WHERE slug = %s OR ipo_id = %d", $slug, $ipo->id));
        $this->current_details = $details_row ? json_decode($details_row->details_json, true) : [];

        // 5. Mock the WP Post Object for SEO & Template compatibility
        $this->mock_post_object($ipo);
    }

    /**
     * Create a fake WP_Post object so plugins think this is a real post
     */
    private function mock_post_object($ipo)
    {
        global $post, $wp_query;

        // Create dummy post object
        $mock_post = new \stdClass();
        $mock_post->ID = 9900000 + $ipo->id; // Fake ID to avoid conflicts
        $mock_post->post_author = 1;
        $mock_post->post_date = $ipo->updated_at ?: current_time('mysql');
        $mock_post->post_date_gmt = $ipo->updated_at ?: current_time('mysql');
        $mock_post->post_content = ''; // Content is rendered via template
        $mock_post->post_title = $ipo->name . ' GMP, Price, Status'; // SEO optimized title
        $mock_post->post_excerpt = "Check GMP for $ipo->name.";
        $mock_post->post_status = 'publish';
        $mock_post->comment_status = 'closed';
        $mock_post->ping_status = 'closed';
        $mock_post->post_password = '';
        $mock_post->post_name = $ipo->slug; // Determine if we want 'ipo-details' or the slug? 
        // Ideally 'ipo-details' is the parent page, but for SEO plugins acting on *this* object, slug is better.
        $mock_post->to_ping = '';
        $mock_post->pinged = '';
        $mock_post->post_modified = $ipo->updated_at ?: current_time('mysql');
        $mock_post->post_modified_gmt = $ipo->updated_at ?: current_time('mysql');
        $mock_post->post_content_filtered = '';
        $mock_post->post_parent = 0; // Or ID of 'ipo-details' page
        $mock_post->guid = home_url('/ipo-details/?slug=' . $ipo->slug);
        $mock_post->menu_order = 0;
        $mock_post->post_type = 'page'; // Treat as page
        $mock_post->post_mime_type = '';
        $mock_post->comment_count = 0;
        $mock_post->filter = 'raw';

        $wp_post = new \WP_Post($mock_post);

        // Override globals
        $post = $wp_post;
        $wp_query->post = $wp_post;
        $wp_query->posts = [$wp_post];
        $wp_query->queried_object = $wp_post;
        $wp_query->queried_object_id = $wp_post->ID;
        $wp_query->found_posts = 1;
        $wp_query->post_count = 1;
        $wp_query->max_num_pages = 1;
        $wp_query->is_page = true;
        $wp_query->is_singular = true;
        $wp_query->is_404 = false;
        $wp_query->is_home = false;
        $wp_query->is_archive = false;

        // Disable caching issues for this mock object if any
        wp_cache_add($wp_post->ID, $wp_post, 'posts');
    }
}
