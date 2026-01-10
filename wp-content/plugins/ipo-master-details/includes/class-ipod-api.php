<?php
/**
 * IPOD REST API Handler
 *
 * Exposes detailed IPO data via REST API.
 * Shared Authentication with IPO Master Admin.
 *
 * @package IPO_Master_Details
 */

if (!defined('ABSPATH')) exit;

class IPOD_API {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes() {
        register_rest_route('zolaha/v1', '/details', [
            'methods'  => 'GET',
            'callback' => [$this, 'get_details'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ]
            ]
        ]);
    }

    public function get_details($request) {
        // 1. AUTHENTICATION (Shared Key)
        $api_key = $request->get_header('X-Api-Key');
        $valid_key = get_option('ipom_api_key', 'zolaha_secure_ipo_key_default');

        if ($api_key !== $valid_key) {
            return new WP_Error('forbidden', 'Invalid API Key', ['status' => 403]);
        }

        // 2. INPUT
        $ipo_id = $request->get_param('id');

        // 3. CACHING (Transient)
        $cache_key = 'ipod_api_' . $ipo_id;
        $cached_data = get_transient($cache_key);

        if ($cached_data !== false) {
            return new WP_REST_Response($cached_data, 200, ['X-Cache' => 'HIT']);
        }

        // 4. DB FETCH
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare("SELECT details_json, fetched_at FROM " . IPOD_TABLE . " WHERE ipo_id = %d", $ipo_id));

        if (!$row) {
            return new WP_Error('not_found', 'Details not found for this IPO ID', ['status' => 404]);
        }

        $data = json_decode($row->details_json, true);
        
        // Inject metadata
        $response_data = [
            'ipo_id' => $ipo_id,
            'fetched_at' => $row->fetched_at,
            'data' => $data
        ];

        // 5. SET CACHE (1 Hour)
        set_transient($cache_key, $response_data, 3600);

        // 6. RESPONSE
        return new WP_REST_Response($response_data, 200, ['X-Cache' => 'MISS']);
    }
}
new IPOD_API();
