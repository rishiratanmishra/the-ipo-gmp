<?php
require_once('wp-load.php');
global $wpdb;

echo "--- DIAGNOSTICS ---\n";

// 1. Check Distinct Statuses
$statuses = $wpdb->get_results("SELECT status, COUNT(*) as c FROM " . IPOD_MASTER . " GROUP BY status");
echo "Statuses in Master:\n";
foreach($statuses as $s) {
    echo " - " . $s->status . ": " . $s->c . "\n";
}

// 2. Check Pending Fetches
$pending = $wpdb->get_var("
    SELECT COUNT(*) 
    FROM " . IPOD_MASTER . " m
    LEFT JOIN " . IPOD_TABLE . " d ON m.id = d.ipo_id
    WHERE d.fetched_at IS NULL
");
echo "\nTotal Pending (NULL fetched_at): $pending\n";

// 3. Test the exact Query from Fetcher
$limit = 15;
$sql = $wpdb->prepare("
    SELECT m.id, m.slug, m.status, d.fetched_at
    FROM " . IPOM_TABLE . " m
    LEFT JOIN " . IPOD_TABLE . " d ON m.id = d.ipo_id
    WHERE d.fetched_at IS NULL
    OR d.fetched_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ORDER BY FIELD(UPPER(m.status),'OPEN','UPCOMING','CLOSED'), m.id DESC
    LIMIT %d
", $limit);

$results = $wpdb->get_results($sql);
echo "\nQuery Result Count: " . count($results) . "\n";

if ($results) {
    echo "First Result: ID=" . $results[0]->id . ", Status=" . $results[0]->status . "\n";
    
    // Test Should Fetch Logic
    require_once(WP_CONTENT_DIR . '/plugins/ipo-master-details/includes/class-ipod-fetcher.php');
    $reflection = new ReflectionMethod('IPOD_Fetcher', 'should_fetch');
    $reflection->setAccessible(true);
    
    foreach($results as $r) {
        if ($r->id == 1116) {
             $now = time();
             $last = strtotime($r->fetched_at);
             $s = strtoupper($r->status);
             echo "DEBUG TIME: Now=$now (" . date('Y-m-d H:i:s') . "), Last=$last ($r->fetched_at), Diff=" . ($now - $last) . ", Status=$s\n";
        }
        $should = $reflection->invoke(null, $r); // Static method
        echo " - ID {$r->id}: Should Fetch? " . ($should ? "YES" : "NO") . "\n";
        
        if ($should) {
            echo "   -> Attempting Scrape for ID {$r->id}...\n";
            $data = IPOD_Fetcher::scrape_data($r->id, $r->slug);
            if (isset($data['error'])) {
                echo "   -> ERROR: " . $data['error'] . "\n";
            } else {
                echo "   -> SUCCESS: Found " . $data['ipo_name'] . "\n";
            }
            break; // Test only one
        }
    }
}
