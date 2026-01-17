<?php
/**
 * Broker Manager REST API Handler
 *
 * Exposes detailed Broker data via REST API.
 * Secure Access via API Key.
 *
 * @package Broker_Manager
 */

if (!defined('ABSPATH'))
    exit;

class BM_API
{

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes()
    {
        register_rest_route('zolaha/v1', '/brokers', [
            'methods' => 'GET',
            'callback' => [$this, 'get_brokers'],
            'permission_callback' => '__return_true', // Validation inside callback
        ]);
    }

    public function get_brokers($request)
    {
        // 1. AUTHENTICATION
        $api_key = $request->get_header('X-Api-Key');
        $valid_key = get_option('bm_api_key', '');

        if (empty($valid_key) || $api_key !== $valid_key) {
            return new WP_Error('forbidden', 'Invalid or Missing API Key', ['status' => 403]);
        }

        // 2. CACHING (Transient - 1 Hour)
        $cache_key = 'bm_api_brokers_list';
        $cached_data = get_transient($cache_key);

        if ($cached_data !== false) {
            // Check for force refresh parameter
            if (!$request->get_param('refresh')) {
                return new WP_REST_Response($cached_data, 200, ['X-Cache' => 'HIT']);
            }
        }

        global $wpdb;

        // Query Custom Table for maximum performance
        $results = $wpdb->get_results("
            SELECT * FROM " . BM_TABLE . " 
            WHERE status = 'active' 
            ORDER BY is_featured DESC, rating DESC, title ASC
        ");

        $brokers = [];
        foreach ($results as $row) {
            // Parse Pros/Cons
            $pros = array_filter(array_map('trim', explode("\n", $row->pros)));
            $cons = array_filter(array_map('trim', explode("\n", $row->cons)));

            // Fallback for logo if missing in table (check featured image)
            $logo = $row->logo_url;
            if (!$logo && $row->post_id) {
                $thumb_id = get_post_thumbnail_id($row->post_id);
                if ($thumb_id) {
                    $logo = wp_get_attachment_url($thumb_id);
                }
            }

            $brokers[] = [
                'id' => (int) $row->post_id, // Keep WP ID as reference
                'title' => $row->title,
                'slug' => $row->slug,
                'affiliate_link' => $row->affiliate_link,
                'referral_code' => $row->referral_code,
                'rating' => (float) $row->rating,
                'min_deposit' => $row->min_deposit,
                'fees' => $row->fees,
                'logo_url' => $logo,
                'is_featured' => (bool) $row->is_featured,
                'pros' => array_values($pros),
                'cons' => array_values($cons)
            ];
        }

        // 4. SET CACHE
        set_transient($cache_key, $brokers, 12 * HOUR_IN_SECONDS); // 12 Hours

        // 5. RESPONSE
        return new WP_REST_Response([
            'status' => 'success',
            'count' => count($brokers),
            'data' => $brokers
        ], 200, ['X-Cache' => 'MISS']);
    }
}

new BM_API();
