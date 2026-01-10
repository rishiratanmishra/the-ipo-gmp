<?php
define('WP_USE_THEMES', false);
require_once('wp-load.php');
global $wpdb;
$row = $wpdb->get_row("SELECT slug, name FROM {$wpdb->prefix}ipomaster WHERE status='open' OR status='upcoming' LIMIT 1");
if($row) {
    echo "Slug: " . $row->slug . "\n";
    echo "Name: " . $row->name . "\n";
    echo "Test URL: " . home_url('/ipo-details/?slug=' . $row->slug) . "\n";
} else {
    echo "No open/upcoming IPOs found.\n";
}
