<?php
define('WP_USE_THEMES', false);
require_once('wp-load.php');

echo "Assigning Categories...\n";

// 1. Create Terms
$dom = wp_insert_term('Domestic', 'broker_category');
$crypto = wp_insert_term('Crypto', 'broker_category');

$term_domestic = term_exists('Domestic', 'broker_category');
$term_crypto = term_exists('Crypto', 'broker_category');

if(!$term_domestic || !$term_crypto) {
    die("Error creating terms.");
}

$dom_id = is_array($term_domestic) ? $term_domestic['term_id'] : $term_domestic;
$crypto_id = is_array($term_crypto) ? $term_crypto['term_id'] : $term_crypto;

echo "Domestic ID: $dom_id, Crypto ID: $crypto_id\n";

// 2. Assign to Brokers
$brokers = [
    'MStock' => $dom_id,
    'Dhan' => $dom_id,
    'Kotak Neo' => $dom_id,
    'Delta Exchange' => $crypto_id
];

global $wpdb;
$posts = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->prefix}posts WHERE post_type='broker'");

foreach($posts as $p) {
    if(isset($brokers[$p->post_title])) {
        wp_set_object_terms($p->ID, (int)$brokers[$p->post_title], 'broker_category');
        echo "Assigned {$p->post_title} -> " . ($brokers[$p->post_title] == $dom_id ? 'Domestic' : 'Crypto') . "\n";
    }
}

echo "Done.\n";
