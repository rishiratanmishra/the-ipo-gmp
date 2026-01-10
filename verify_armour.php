<?php
require_once 'wp-load.php';
require_once WP_PLUGIN_DIR . '/ipo-master-details/includes/class-ipod-fetcher.php';

$id = 1124;
$slug = 'armour-security-india-ltd';

echo "Refetching $slug...\n";
$data = IPOD_Fetcher::scrape_data($id, $slug);

if (isset($data['error'])) {
    die("Error: " . $data['error']);
}

echo "Success!\n";
echo "About Company Length: " . strlen($data['about_company'] ?? '') . "\n";
echo "Strengths Count: " . count($data['strengths'] ?? []) . "\n";
echo "Strengths Text Length: " . strlen($data['strengths_text'] ?? '') . "\n";
echo "Weaknesses Count: " . count($data['weaknesses'] ?? []) . "\n";
echo "Weaknesses Text Length: " . strlen($data['weaknesses_text'] ?? '') . "\n";
echo "Registrar Name: " . ($data['registrar_name'] ?? 'N/A') . "\n";
echo "Registrar Email: " . ($data['registrar_email'] ?? 'N/A') . "\n";

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
