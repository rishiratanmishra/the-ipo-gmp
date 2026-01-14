<?php
require_once('wp-load.php');
$args = array(
    'post_type' => 'post',
    'posts_per_page' => 1,
    'orderby' => 'date',
    'order' => 'DESC'
);
$query = new WP_Query($args);
if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();
        echo "Post ID: " . get_the_ID() . "\n";
        echo "Title: " . get_the_title() . "\n";
        echo "Content Length: " . strlen(get_the_content()) . " characters\n";
        echo "Content Snippet: " . substr(strip_tags(get_the_content()), 0, 200) . "...\n";
    }
} else {
    echo "No posts found.";
}
