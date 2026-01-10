<?php
define('WP_USE_THEMES', false);
require_once('wp-load.php');
global $wpdb;
$table = $wpdb->prefix . 'ipomaster';
$columns = $wpdb->get_results("DESCRIBE $table");
foreach($columns as $c) {
    echo $c->Field . "\n";
}
