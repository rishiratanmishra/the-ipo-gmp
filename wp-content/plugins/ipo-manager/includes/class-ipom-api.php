<?php
/**
 * IPOM REST API Handler
 *
 * Exposes IPO data securely via REST API.
 * Supports API Key Authentication, Caching, and Filtering.
 *
 * @package IPO_Master_Admin
 */

if (!defined('ABSPATH'))
    exit;

class IPOM_API
{

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes()
    {
        register_rest_route('zolaha/v1', '/ipos', [
            'methods' => 'GET',
            'callback' => [$this, 'get_ipos'],
            'permission_callback' => '__return_true', // Validation done inside callback
        ]);
    }

    public function get_ipos($request)
    {
        // 1. SECURITY: API Key Check
        $api_key = $request->get_header('X-Api-Key');
        $valid_key = get_option('ipom_api_key', 'zolaha_secure_ipo_key_default'); // Fetch from DB

        if ($api_key !== $valid_key) {
            return new WP_Error('forbidden', 'Invalid API Key', ['status' => 403]);
        }

        // 2. INPUT: Sanitize & Prepare
        $type = sanitize_text_field($request->get_param('type')); // sme, main
        $search = sanitize_text_field($request->get_param('search'));
        $limit = isset($request['limit']) ? intval($request['limit']) : 20;
        $page = isset($request['page']) ? intval($request['page']) : 1;

        $limit = ($limit > 0 && $limit <= 100) ? $limit : 20;
        $page = ($page > 0) ? $page : 1;
        $offset = ($page - 1) * $limit;

        // 3. CACHING: Check Transients
        $cache_key = 'ipom_api_' . md5($type . $search . $limit . $page);
        $cached_data = get_transient($cache_key);

        if ($cached_data !== false) {
            return new WP_REST_Response($cached_data, 200, ['X-Cache' => 'HIT']);
        }

        // 4. DATABASE QUERY
        global $wpdb;
        $table_name = defined('IPOM_TABLE') ? IPOM_TABLE : $wpdb->prefix . 'ipomaster';

        $where = "WHERE 1=1";
        $args = [];

        if (!empty($type)) {
            if (strtolower($type) === 'sme')
                $where .= " AND is_sme = 1";
            elseif (strtolower($type) === 'main')
                $where .= " AND is_sme = 0";
        }

        if (!empty($search)) {
            $where .= " AND name LIKE %s";
            $args[] = '%' . $wpdb->esc_like($search) . '%';
        }

        // Total Count for Pagination Headers
        if (!empty($args)) {
            $total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name $where", $args));
            $sql = $wpdb->prepare("SELECT * FROM $table_name $where ORDER BY id DESC LIMIT %d OFFSET %d", array_merge($args, [$limit, $offset]));
        } else {
            $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name $where");
            $sql = $wpdb->prepare("SELECT * FROM $table_name $where ORDER BY id DESC LIMIT %d OFFSET %d", $limit, $offset);
        }

        $results = $wpdb->get_results($sql);
        $total_pages = ceil($total_items / $limit);

        // 5. CACHING: Set Transient (1 Hour)
        set_transient($cache_key, $results, 3600);

        // 6. RESPONSE
        $response = new WP_REST_Response($results, 200);
        $response->header('X-WP-Total', $total_items);
        $response->header('X-WP-TotalPages', $total_pages);
        $response->header('X-Cache', 'MISS');

        return $response;
    }
}
new IPOM_API();
