<?php
define('WP_USE_THEMES', false);
require_once('wp-load.php');

$page_title = 'Buybacks';
$page_slug = 'buybacks';
$template = 'template-buybacks.php';

// Check if page exists
$page = get_page_by_path($page_slug);

if ($page) {
    echo "Page '$page_slug' already exists (ID: {$page->ID}).\n";
    // Ensure template is set
    update_post_meta($page->ID, '_wp_page_template', $template);
    echo "Assigned Template: $template\n";
} else {
    // Create Page
    $page_id = wp_insert_post([
        'post_title' => $page_title,
        'post_name' => $page_slug,
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_content' => '', 
    ]);
    
    if($page_id) {
        update_post_meta($page_id, '_wp_page_template', $template);
        echo "Created Page '$page_title' (ID: $page_id) with template '$template'.\n";
    } else {
        echo "Error creating page.\n";
    }
}

// Flush Permalinks
global $wp_rewrite;
$wp_rewrite->set_permalink_structure('/%postname%/');
$wp_rewrite->flush_rules();
echo "Permalinks Flushed.\n";
