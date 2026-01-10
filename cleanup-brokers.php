<?php
define('WP_USE_THEMES', false);
require_once('wp-load.php');
global $wpdb;

echo "Starting Broker Cleanup...\n";

// 1. Fix Kotak Neo Name (was heuristically named 'Play-lh' from google play url)
$kotak_id = $wpdb->get_var("SELECT post_id FROM " . BM_TABLE . " WHERE title LIKE '%Play-lh%'");
if($kotak_id) {
    $wpdb->update(BM_TABLE, ['title' => 'Kotak Neo', 'slug' => 'kotak-neo'], ['post_id' => $kotak_id]);
    wp_update_post(['ID' => $kotak_id, 'post_title' => 'Kotak Neo', 'post_name' => 'kotak-neo']);
    echo "Fixed Name: Kotak Neo\n";
}

// 2. Define Allowed Brokers
$allowed = ['MStock', 'Delta Exchange', 'Dhan', 'Kotak Neo'];

// 3. Get All Brokers
$brokers = $wpdb->get_results("SELECT id, post_id, title FROM " . BM_TABLE);

foreach($brokers as $b) {
    if(!in_array($b->title, $allowed)) {
        // Delete from Custom Table
        $wpdb->delete(BM_TABLE, ['id' => $b->id]);
        
        // Delete from WP Posts (Trash it)
        wp_delete_post($b->post_id, true);
        
        echo "Deleted: " . $b->title . "\n";
    } else {
        echo "Kept: " . $b->title . "\n";
    }
}

// Clear Cache
delete_transient('bm_api_brokers_list');

echo "Cleanup Complete.\n";
