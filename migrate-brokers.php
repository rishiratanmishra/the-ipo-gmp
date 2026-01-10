<?php
define('WP_USE_THEMES', false);
require_once('wp-load.php');
global $wpdb;

echo "Starting Migration from SQL File...\n";

// Map: meta_key => column_name
$map = [
    'bm_affiliate' => 'affiliate_link',
    'bm_referral' => 'referral_code',
    'bm_status' => 'status',
    'bm_rating' => 'rating',
    'bm_min_deposit' => 'min_deposit',
    'bm_fees' => 'fees',
    'bm_logo_url' => 'logo_url',
    'bm_pros' => 'pros',
    'bm_cons' => 'cons',
    'bm_featured' => 'is_featured',
    'bm_click_count' => 'click_count'
];

$sql_content = file_get_contents('wp_postmeta.sql');

// Extract VALUES (...), (...), ...
preg_match('/INSERT INTO `wp_postmeta`.*?VALUES\s*(.*);/s', $sql_content, $matches);
if(empty($matches[1])) die("No INSERT data found.");

$values_str = $matches[1];
// Split by `), (` to get rows (rough split, but works for simple values)
// Better: regex for (`id`, `pid`, `key`, `val`)
preg_match_all("/\(\d+,\s*(\d+),\s*'([^']*)',\s*'([^']*)'\)/", $values_str, $rows);

$grouped_data = [];

// $rows[1] = post_id, $rows[2] = meta_key, $rows[3] = meta_value
foreach($rows[1] as $k => $post_id) {
    $meta_key = $rows[2][$k];
    $meta_val = $rows[3][$k];

    if(isset($map[$meta_key])) {
        if(!isset($grouped_data[$post_id])) $grouped_data[$post_id] = [];
        $grouped_data[$post_id][$meta_key] = $meta_val;
    }
}

// Filter Junk: Only keep entries that look like Brokers (must have status or rating)
$clean_data = [];
foreach($grouped_data as $pid => $d) {
    if(isset($d['bm_status']) || isset($d['bm_rating']) || isset($d['bm_logo_url']) || isset($d['bbm_price'])) {
        $clean_data[$pid] = $d;
    }
}
$grouped_data = $clean_data;

echo "Found data for " . count($grouped_data) . " brokers (Filtered).\n";

$bm_table = $wpdb->prefix . 'brokers';

// Loop and Create NEW Posts for everything to avoid ID conflicts with existing pages
foreach($grouped_data as $old_pid => $meta) {
    
    // 1. Determine Title
    $logo = $meta['bm_logo_url'] ?? '';
    $aff = $meta['bm_affiliate'] ?? '';
    $title = "Broker Imported";

    $url_to_parse = $logo ?: $aff;
    if($url_to_parse && filter_var($url_to_parse, FILTER_VALIDATE_URL)) {
        $host = parse_url($url_to_parse, PHP_URL_HOST);
        $host = str_ireplace('www.', '', $host);
        $host_parts = explode('.', $host);
        $name = $host_parts[0];
        
        // Specific Overrides
        if(strpos($url_to_parse, 'mstock') !== false) $name = "mStock";
        if(strpos($url_to_parse, 'delta.exchange') !== false) $name = "Delta Exchange";
        if(strpos($url_to_parse, 'dhan.co') !== false) $name = "Dhan";
        if(strpos($url_to_parse, 'angelone') !== false) $name = "Angel One";
        if(strpos($url_to_parse, 'zerodha') !== false) $name = "Zerodha";
        if(strpos($url_to_parse, 'upstox') !== false) $name = "Upstox";
        if(strpos($url_to_parse, 'groww') !== false) $name = "Groww";
        if(strpos($url_to_parse, 'kneo') !== false) $name = "Kotak Neo";
        
        $title = ucfirst($name);
    }

    // 2. Create NEW Post (Ignore $old_pid)
    // Check if this broker already exists by title to avoid duplicates
    $existing_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = %s AND post_type='broker'", $title));
    
    if($existing_id) {
        $new_pid = $existing_id;
        echo "Found Existing Post: $title (ID: $new_pid)\n";
    } else {
        $new_pid = wp_insert_post([
            'post_title' => $title,
            'post_type' => 'broker',
            'post_status' => 'publish',
            'post_author' => 1
        ]);
        echo "Created New Post: $title (ID: $new_pid)\n";
    }

    // 3. Insert into wp_brokers with NEW Post ID
    $data = [
        'post_id' => $new_pid,
        'title' => $title,
        'slug' => get_post_field('post_name', $new_pid),
        'updated_at' => current_time('mysql'),
        'status' => 'active', 
        'is_featured' => 0
    ];

    foreach($map as $key => $col) {
        $val = isset($meta[$key]) ? $meta[$key] : '';
        
        // Formatting
        if($col == 'is_featured') $val = ($val === 'yes') ? 1 : 0;
        if($col == 'click_count') $val = (int)$val;
        if($col == 'rating') $val = (float)$val;

        if($val !== '') $data[$col] = $val;
    }

    // Check if entry exists in brokers table
    $exists_broker = $wpdb->get_var($wpdb->prepare("SELECT id FROM $bm_table WHERE post_id = %d", $new_pid));
    
    if($exists_broker) {
        $wpdb->update($bm_table, $data, ['post_id' => $new_pid]);
    } else {
        $wpdb->insert($bm_table, $data);
    }
}

echo "Migration Complete.\n";
