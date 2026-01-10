<?php
require_once('wp-load.php');
global $wpdb;
$tables = $wpdb->get_results('SHOW TABLES', ARRAY_N);
foreach($tables as $t) {
    echo $t[0] . "\n";
}
