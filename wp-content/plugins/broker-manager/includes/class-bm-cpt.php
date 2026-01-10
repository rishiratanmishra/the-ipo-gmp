<?php
if (!defined('ABSPATH')) exit;

class BM_CPT {
    public function __construct() {
        add_action('init', [$this, 'register_core']);
    }

    public function register_core() {
        // Register Taxonomy: Categories
        $tax_labels = array(
            'name' => 'Broker Categories',
            'singular_name' => 'Category',
            'menu_name' => 'Broker Categories',
        );
        $tax_args = array(
            'hierarchical'      => true,
            'labels'            => $tax_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'broker-category' ),
        );
        register_taxonomy( 'broker_category', array( 'broker' ), $tax_args );

        // Register Post Type: Broker
        $labels = array(
            'name' => 'Brokers',
            'singular_name' => 'Broker',
            'menu_name' => 'Brokers',
            'add_new' => 'Add Broker',
            'add_new_item' => 'Add New Broker',
            'edit_item' => 'Edit Broker',
            'new_item' => 'New Broker',
            'view_item' => 'View Broker',
            'search_items' => 'Search Brokers',
            'not_found' => 'No Brokers found'
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-chart-line',
            'supports' => array('title', 'thumbnail'),
        );

        register_post_type('broker', $args);
    }
}

new BM_CPT();
