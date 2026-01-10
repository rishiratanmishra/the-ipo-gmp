<?php
class BBM_API {
    /**
     * REST API Handler
     *
     * Register endpoints for external apps to consume buyback data.
     * Supports Caching, API Keys, Pagination, and Filters.
     *
     * Endpoint: GET /wp-json/zolaha/v1/buybacks
     *
     * @since 1.0.0
     */

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes() {
        register_rest_route('zolaha/v1', '/buybacks', [
            'methods' => 'GET',
            'callback' => [$this, 'get_buybacks'],
            'permission_callback' => '__return_true' // Public API
        ]);
    }

    public function get_buybacks($request) {
        // 1. SECURITY: API Key Check
        $api_key = $request->get_header('X-Api-Key');
        $valid_key = get_option('bbm_api_key', 'zolaha_secure_app_key_123');
        
        if ($api_key !== $valid_key) {
            return new WP_Error('forbidden', 'Invalid API Key', ['status' => 403]);
        }

        global $wpdb;
        $table_name = defined('BBM_TABLE') ? BBM_TABLE : $wpdb->prefix . 'buybacks';

        // 2. INPUT SANITIZATION
        $type = sanitize_text_field($request->get_param('type'));
        $search = sanitize_text_field($request->get_param('search'));
        $limit = $request->get_param('limit') ? intval($request->get_param('limit')) : 10;
        $page = $request->get_param('page') ? intval($request->get_param('page')) : 1;
        $offset = ($page - 1) * $limit;

        // 3. CACHING: Generate Cache Key unique to this request
        $cache_key = 'bbm_api_' . md5($type . $search . $limit . $page);
        $cached_response = get_transient($cache_key);

        if ($cached_response !== false) {
            // Return cached response with header indicating cache hit
            $response = new WP_REST_Response($cached_response['data'], 200);
            $response->header('X-WP-Total', $cached_response['total']);
            $response->header('X-WP-TotalPages', $cached_response['pages']);
            $response->header('X-Cache', 'HIT');
            return $response;
        }

        // --- DB Query Construction ---
        $where = "WHERE 1=1";
        $where_params = [];

        if ($type) {
            $where .= " AND type LIKE %s";
            $where_params[] = '%' . $wpdb->esc_like($type) . '%';
        }

        if ($search) {
            $where .= " AND company LIKE %s";
            $where_params[] = '%' . $wpdb->esc_like($search) . '%';
        }

        // Count Query
        $count_sql = "SELECT COUNT(*) FROM $table_name $where";
        if(!empty($where_params)){
            $total_items = (int) $wpdb->get_var($wpdb->prepare($count_sql, $where_params));
        } else {
            $total_items = (int) $wpdb->get_var($count_sql);
        }
        
        $total_pages = ceil($total_items / $limit);

        // Data Query
        $sql = "SELECT * FROM $table_name $where ORDER BY id DESC LIMIT %d OFFSET %d";
        $data_params = array_merge($where_params, [$limit, $offset]);

        $results = $wpdb->get_results($wpdb->prepare($sql, $data_params));

        if (empty($results)) {
             // 4. ERROR HANDLING: Empty state is not error, but standard 200 with empty array
             $results = [];
        }

        // 5. SET CACHE (Expires in 1 Hour)
        set_transient($cache_key, [
            'data' => $results,
            'total' => $total_items,
            'pages' => $total_pages
        ], 3600);

        // Prepare Response
        $response = new WP_REST_Response($results, 200);
        $response->header('X-WP-Total', $total_items);
        $response->header('X-WP-TotalPages', $total_pages);
        $response->header('X-Cache', 'MISS');

        return $response;
    }
}

new BBM_API();
