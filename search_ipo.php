<?php
require_once 'wp-load.php';
global $wpdb;
$t = $wpdb->prefix . 'ipomaster';
$row = $wpdb->get_row("SELECT id, slug FROM $t WHERE name LIKE '%Bharat Coking%' LIMIT 1");
if ($row) {
    echo "ID: " . $row->id . " Slug: " . $row->slug . "\n";
} else {
    echo "Not found\n";
}
