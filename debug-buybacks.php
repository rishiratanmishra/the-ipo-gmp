<?php
define('WP_USE_THEMES', false);
require_once('wp-load.php');

global $wpdb;
$table = $wpdb->prefix . 'buybacks';

echo "Table: $table\n";

// Get latest 10 rows
$rows = $wpdb->get_results("SELECT id, company, status FROM $table ORDER BY id DESC LIMIT 10");

echo "Latest 10 Rows:\n";
foreach($rows as $r) {
    echo "ID: {$r->id} | Company: {$r->company} | Status: '{$r->status}'\n";
}

echo "\nTesting Query 'LIKE %Open%':\n";
$open = $wpdb->get_results("SELECT * FROM $table WHERE status LIKE '%Open%'");
echo "Count: " . count($open) . "\n";
if($open) {
    foreach($open as $o) {
        echo "Found: {$o->company} ({$o->status})\n";
    }
}
