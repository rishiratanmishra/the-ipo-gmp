<?php
require_once 'wp-load.php';
require_once WP_PLUGIN_DIR . '/ipo-master-details/includes/class-ipod-fetcher.php';

$id = 1105;
$slug = 'bharat-coking-coal-ltd';

echo "Refetching $slug to test Application Breakup fix...\n";
$data = IPOD_Fetcher::scrape_data($id, $slug);

if (isset($data['error'])) {
    die("Error: " . $data['error']);
}

echo "\nApplication Breakup Data:\n";
echo "========================\n";
print_r($data['application_breakup']);

// Save to DB
global $wpdb;
$wpdb->replace($wpdb->prefix . 'ipodetails', [
    "ipo_id"       => $id,
    "slug"         => $slug,
    "details_json" => wp_json_encode($data, JSON_UNESCAPED_UNICODE),
    "fetched_at"   => current_time("mysql"),
    "updated_at"   => current_time("mysql"),
]);

echo "\nDB Updated. Please refresh the page!\n";
