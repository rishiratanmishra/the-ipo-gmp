<?php
/**
 * Plugin Name: The IPO GMP Core
 * Description: Core functionality, templates, and assets for The IPO GMP platform. Safe from theme updates.
 * Version: 1.0.0
 * Author: Zolaha.com
 * Text Domain: the-ipo-gmp-core
 */

if (!defined('ABSPATH')) exit;

define('TIGC_PATH', plugin_dir_path(__FILE__));
define('TIGC_URL', plugin_dir_url(__FILE__));

// Include Loader & Admin
require_once TIGC_PATH . 'includes/class-template-loader.php';
require_once TIGC_PATH . 'includes/class-admin-menu.php';

// Initialize
function run_the_ipo_gmp_core() {
    new TIGC_Template_Loader();
    if (is_admin()) {
        new TIGC_Admin_Menu();
    }
}
add_action('plugins_loaded', 'run_the_ipo_gmp_core');

// Enforce Production Settings
add_action('init', 'tigc_enforce_defaults');
function tigc_enforce_defaults() {
    if (!is_admin()) return;

    // 1. Ensure Search Engine Visibility is ON
    if (get_option('blog_public') == '0') {
        update_option('blog_public', '1');
    }

    // 2. Fix Default Tagline
    $tagline = get_option('blogdescription');
    if ($tagline === 'Just another WordPress site' || empty($tagline)) {
        update_option('blogdescription', 'Live IPO GMP & Market Intelligence');
    }
    
    // 3. Set Date Format to Day Month Year (01 Jan 2026)
    if (get_option('date_format') !== 'j M Y') {
        update_option('date_format', 'j M Y');
    }

    // 4. Set Timezone to IST
    if (get_option('timezone_string') !== 'Asia/Kolkata') {
        update_option('timezone_string', 'Asia/Kolkata');
    }
}

// Activation Hook: Auto-create Pages
register_activation_hook(__FILE__, 'tigc_create_pages');
function tigc_create_pages() {
    $pages = [
        'mainboard-ipos' => 'Mainboard IPOs',
        'sme-ipos'       => 'SME IPOs',
        'buybacks'       => 'Buybacks',
        'ipo-details'    => 'IPO Details'
    ];

    foreach ($pages as $slug => $title) {
        if (!get_page_by_path($slug)) {
            wp_insert_post([
                'post_title'   => $title,
                'post_name'    => $slug,
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => ''
            ]);
        }
    }
}

// Enqueue Assets (Shared across all custom templates)
add_action('wp_enqueue_scripts', function() {
    if (is_page(['dashboard', 'ipo-details', 'buybacks', 'mainboard-ipos', 'sme-ipos']) || is_front_page()) {
        wp_enqueue_script('tailwindcss', 'https://cdn.tailwindcss.com?plugins=forms,container-queries', [], null);
        wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap', [], null);
        wp_enqueue_style('material-icons', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap', [], null);
        
        // Custom Config for Tailwind
        wp_add_inline_script('tailwindcss', '
            tailwind.config = {
                darkMode: "class",
                theme: {
                    extend: {
                        colors: { 
                            "primary": "#0d7ff2", 
                            "background-dark": "#050A18", 
                            "card-dark": "#0B1220", 
                            "border-navy": "#1E293B", 
                            "neon-emerald": "#00FF94", 
                            "purple-accent": "#A855F7" 
                        },
                        fontFamily: { "display": ["Inter", "sans-serif"] }
                    }
                }
            }
        ');
    }
});

// AJAX Search Handler
add_action('wp_ajax_tigc_ajax_search', 'tigc_handle_ajax_search');
add_action('wp_ajax_nopriv_tigc_ajax_search', 'tigc_handle_ajax_search');

function tigc_handle_ajax_search() {
    global $wpdb;
    
    $term = isset($_GET['term']) ? sanitize_text_field($_GET['term']) : '';
    
    if (empty($term)) {
        wp_send_json_success([]);
    }

    $t_master = $wpdb->prefix . 'ipomaster';
    
    // Search by Name ONLY (Removing symbol to avoid column errors)
    $query = $wpdb->prepare("
        SELECT id, name, slug, status, premium 
        FROM $t_master 
        WHERE name LIKE %s 
        ORDER BY CASE WHEN status = 'Open' THEN 1 ELSE 2 END, id DESC 
        LIMIT 5
    ", '%' . $wpdb->esc_like($term) . '%');

    $results = $wpdb->get_results($query);

    if ($wpdb->last_error) {
        wp_send_json_error(['message' => 'DB Error', 'error' => $wpdb->last_error]);
    }

    $data = [];
    foreach ($results as $r) {
        $data[] = [
            'id' => $r->id,
            'name' => $r->name,
            'slug' => $r->slug,
            'status' => $r->status,
            'gmp' => $r->premium
        ];
    }

    wp_send_json_success($data);
}

// --- SITEMAP LOGIC (Moved from sitemap-control) ---

// 1. Clean up default sitemaps (Disable Users & Taxonomies)
add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) {
    // Only allow 'posts' and our custom 'ipos'
    if ( $name !== 'posts' && $name !== 'ipos' ) {
        return false;
    }
    return $provider;
}, 10, 2 );

// 2. Limit Post Types in 'posts' provider to just Post & Page
add_filter( 'wp_sitemaps_post_types', function ( $post_types ) {
    return [
        'post' => $post_types['post'],
        'page' => $post_types['page'],
    ];
});

// 3. Register Custom IPO Sitemap Provider
if ( class_exists( 'WP_Sitemaps_Provider' ) ) {

    class IPO_Sitemap_Provider extends WP_Sitemaps_Provider {
        public function __construct() {
            $this->name        = 'ipos'; // Provider name
            $this->object_type = 'custom'; // Object type
        }

        public function get_url_list( $page_num, $object_subtype = '' ) {
            global $wpdb;
            $limit = 2000;
            $offset = ( $page_num - 1 ) * $limit;
            $table_name = $wpdb->prefix . 'ipomaster';
            
            $results = $wpdb->get_results( 
                $wpdb->prepare( "SELECT slug, updated_at FROM $table_name ORDER BY id DESC LIMIT %d OFFSET %d", $limit, $offset ) 
            );

            $url_list = [];
            foreach ( $results as $row ) {
                $url_list[] = [
                    'loc' => home_url( '/ipo-details/?slug=' . $row->slug ),
                    'lastmod' => !empty($row->updated_at) ? date('c', strtotime($row->updated_at)) : null,
                ];
            }
            return $url_list;
        }

        public function get_max_num_pages( $object_subtype = '' ) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'ipomaster';
            $total = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
            return ceil( $total / 2000 );
        }
    }

    add_action( 'init', function () {
        $provider = new IPO_Sitemap_Provider();
        wp_register_sitemap_provider( 'ipos', $provider );
    } );
}

