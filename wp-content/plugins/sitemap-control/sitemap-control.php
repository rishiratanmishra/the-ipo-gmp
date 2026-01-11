<?php
/**
 * Plugin Name: Custom Sitemap Control
 * Description: Keep only post and page in WordPress core sitemap.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) {

    // Disable users and taxonomies sitemap providers
    if ( $name !== 'posts' ) {
        return false;
    }

    return $provider;
}, 10, 2 );

add_filter( 'wp_sitemaps_post_types', function ( $post_types ) {

    // Allow only post and page
    return [
        'post' => $post_types['post'],
        'page' => $post_types['page'],
    ];
});
