<?php
define('WP_USE_THEMES', false);
require_once('wp-load.php');
global $wpdb;
$table = $wpdb->prefix . 'ipomaster';

echo "Table: $table\n";

// 1. Check Count
$count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
echo "Total Rows: $count\n";

// 2. Dump all Categories and Statuses
$results = $wpdb->get_results("SELECT id, name, category, status FROM $table LIMIT 20");
foreach($results as $r) {
    echo "ID: {$r->id} | Name: {$r->name} | Cat: '{$r->category}' | Status: '{$r->status}'\n";
}
