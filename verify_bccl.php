<?php
require_once 'wp-load.php';
require_once WP_PLUGIN_DIR . '/ipo-master-details/includes/class-ipod-fetcher.php';

$id = 1105;
$slug = 'bharat-coking-coal-ltd';

echo "Refetching $slug...\n";
$data = IPOD_Fetcher::scrape_data($id, $slug);

if (isset($data['error'])) {
    die("Error: " . $data['error']);
}

echo "Success!\n";
echo "Peer Valuation Count: " . count($data['peer_valuation'] ?? []) . "\n";
echo "Peer Financials Count: " . count($data['peer_financials'] ?? []) . "\n";
echo "Company Financials Count: " . count($data['company_financials'] ?? []) . "\n";
echo "Strengths Count: " . count($data['strengths'] ?? []) . "\n";
echo "Weaknesses Count: " . count($data['weaknesses'] ?? []) . "\n";
echo "Reviewers Count: " . count($data['reviewers'] ?? []) . "\n";

// Save to DB
global $wpdb;
$wpdb->replace($wpdb->prefix . 'ipodetails', [
    "ipo_id"       => $id,
    "slug"         => $slug,
    "details_json" => wp_json_encode($data, JSON_UNESCAPED_UNICODE),
    "fetched_at"   => current_time("mysql"),
    "updated_at"   => current_time("mysql"),
]);

echo "DB Updated.\n";
