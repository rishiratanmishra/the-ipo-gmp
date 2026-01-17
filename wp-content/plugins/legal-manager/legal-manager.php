<?php
/**
 * Plugin Name: Legal Manager
 * Description: Manages legal pages, disclaimers, and compliance documents.
 * Version: 1.0.0
 * Author: zolaha.com
 * Author URI: https://zolaha.com
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class YourIPO_Legal_Manager
{

    public function __construct()
    {
        add_action('init', array($this, 'register_legal_post_type'));
        add_action('rest_api_init', array($this, 'register_custom_rest_routes'));
    }

    public function register_custom_rest_routes()
    {
        register_rest_route('your-ipo/v1', '/legal-pages', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_legal_pages_custom'),
            'permission_callback' => '__return_true',
        ));
    }

    public function get_legal_pages_custom()
    {
        $args = array(
            'post_type' => 'legal_page',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        );
        $posts = get_posts($args);
        $data = array();

        foreach ($posts as $post) {
            $data[] = array(
                'id' => $post->ID,
                'title' => array('rendered' => $post->post_title),
                'content' => array('rendered' => wpautop($post->post_content)),
            );
        }
        return $data;
    }

    public function register_legal_post_type()
    {
        $labels = array(
            'name' => _x('Legal Pages', 'Post Type General Name', 'text_domain'),
            'singular_name' => _x('Legal Page', 'Post Type Singular Name', 'text_domain'),
            'menu_name' => __('App Legal', 'text_domain'),
            'name_admin_bar' => __('Legal Page', 'text_domain'),
            'archives' => __('Item Archives', 'text_domain'),
            'attributes' => __('Item Attributes', 'text_domain'),
            'parent_item_colon' => __('Parent Item:', 'text_domain'),
            'all_items' => __('All Legal Pages', 'text_domain'),
            'add_new_item' => __('Add New Legal Page', 'text_domain'),
            'add_new' => __('Add New', 'text_domain'),
            'new_item' => __('New Item', 'text_domain'),
            'edit_item' => __('Edit Item', 'text_domain'),
            'update_item' => __('Update Item', 'text_domain'),
            'view_item' => __('View Item', 'text_domain'),
            'view_items' => __('View Items', 'text_domain'),
            'search_items' => __('Search Item', 'text_domain'),
            'not_found' => __('Not found', 'text_domain'),
            'not_found_in_trash' => __('Not found in Trash', 'text_domain'),
            'featured_image' => __('Featured Image', 'text_domain'),
            'set_featured_image' => __('Set featured image', 'text_domain'),
            'remove_featured_image' => __('Remove featured image', 'text_domain'),
            'use_featured_image' => __('Use as featured image', 'text_domain'),
            'insert_into_item' => __('Insert into item', 'text_domain'),
            'uploaded_to_this_item' => __('Uploaded to this item', 'text_domain'),
            'items_list' => __('Items list', 'text_domain'),
            'items_list_navigation' => __('Items list navigation', 'text_domain'),
            'filter_items_list' => __('Filter items list', 'text_domain'),
        );
        $args = array(
            'label' => __('Legal Page', 'text_domain'),
            'description' => __('Post Type Description', 'text_domain'),
            'labels' => $labels,
            'supports' => array('title', 'editor', 'revisions'),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 5,
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'has_archive' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => true,
            'capability_type' => 'page',
            'show_in_rest' => true, // Important for REST API access
            'rest_base' => 'legal-pages',
        );
        register_post_type('legal_page', $args);
    }

    public function register_rest_fields()
    {
        // Add plain content field if needed, but 'content.rendered' usually suffices.
    }
}

new YourIPO_Legal_Manager();
