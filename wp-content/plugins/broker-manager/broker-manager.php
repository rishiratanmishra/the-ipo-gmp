<?php
/**
 * Plugin Name: Broker Manager
 * Description: Defines and manages broker information and reviews.
 * Version: 1.0.0
 * Author: Zolaha.com
 * Author URI: https://zolaha.com
 */

if (!defined('ABSPATH')) exit;

define('BM_PATH', plugin_dir_path(__FILE__));
define('BM_URL', plugin_dir_url(__FILE__));
global $wpdb;
define('BM_TABLE', $wpdb->prefix . 'brokers');

// Include Classes
require_once BM_PATH . 'includes/class-bm-cpt.php';
require_once BM_PATH . 'includes/class-bm-meta.php';
require_once BM_PATH . 'includes/class-bm-admin.php';
require_once BM_PATH . 'includes/class-bm-shortcode.php';
require_once BM_PATH . 'includes/class-bm-api.php';

// Activation Hook: Create Table
register_activation_hook(__FILE__, 'bm_install');

function bm_install() {
    global $wpdb;
    $table_name = BM_TABLE;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        post_id bigint(20) UNSIGNED NOT NULL,
        title varchar(255) NOT NULL,
        slug varchar(255) NOT NULL,
        affiliate_link text,
        referral_code varchar(100),
        status varchar(50) DEFAULT 'active',
        rating decimal(3,1) DEFAULT 0.0,
        min_deposit varchar(100),
        fees varchar(255),
        logo_url text,
        pros text,
        cons text,
        is_featured tinyint(1) DEFAULT 0,
        click_count int(11) DEFAULT 0,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY post_id (post_id),
        KEY status (status)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Auto-generate API Key if missing
    if(!get_option('bm_api_key')) {
        update_option('bm_api_key', wp_generate_password(32, false));
    }
}

// Enqueue Assets (Frontend)
function bm_enqueue_assets() {
    wp_enqueue_style('bm-style', BM_URL . 'assets/css/style.css', [], '2.1');
    wp_enqueue_script('bm-script', BM_URL . 'assets/js/script.js', ['jquery'], '2.1', true);
    
    // Pass AJAX URL to script
    wp_localize_script('bm-script', 'bm_ajax', [
        'url' => admin_url('admin-ajax.php')
    ]);
}
add_action('wp_enqueue_scripts', 'bm_enqueue_assets');
