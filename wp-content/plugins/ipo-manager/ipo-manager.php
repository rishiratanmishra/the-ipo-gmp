<?php
/**
 * Plugin Name: IPO Manager
 * Description: Master administration hub for IPO management and settings.
 * Version: 2.0.0
 * Author: zolaha.com
 * Author URI: https://zolaha.com
 * Package: IPO_Manager
 */

if (!defined('ABSPATH'))
    exit;

// Constants
global $wpdb;
define("IPOM_PATH", plugin_dir_path(__FILE__));
define("IPOM_URL", plugin_dir_url(__FILE__));
define("IPOM_TABLE", $wpdb->prefix . "ipomaster");

// Include Dependencies
require_once IPOM_PATH . 'includes/class-ipom-fetcher.php';
require_once IPOM_PATH . 'includes/class-ipom-shortcode.php';
require_once IPOM_PATH . 'admin/class-ipom-admin.php';
require_once IPOM_PATH . 'includes/class-ipom-api.php'; // API Enabled

// Initialize Classes
new IPOM_Admin();
new IPOM_Shortcode();

// Activation Hook
register_activation_hook(__FILE__, function () {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = IPOM_TABLE;

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT PRIMARY KEY,
        name VARCHAR(255),
        is_sme TINYINT(1) DEFAULT 0,
        open_date VARCHAR(100),
        close_date VARCHAR(100),
        price_band VARCHAR(100),
        min_price VARCHAR(50),
        max_price VARCHAR(50),
        lot_size VARCHAR(50),
        issue_size_cr VARCHAR(50),
        premium VARCHAR(100),
        badge VARCHAR(20),
        allotment_date VARCHAR(100),
        listing_date VARCHAR(100),
        status VARCHAR(50),
        icon_url TEXT,
        slug VARCHAR(255),
        updated_at DATETIME
    ) $charset_collate;";

    require_once(ABSPATH . "wp-admin/includes/upgrade.php");
    dbDelta($sql);

    // Default API Key (Future Proofing)
    if (!get_option('ipom_api_key')) {
        update_option('ipom_api_key', 'zolaha_ipo_' . wp_generate_password(12, false));
    }

    // Schedule Cron
    if (!wp_next_scheduled("ipom_hourly_event")) {
        wp_schedule_event(time(), 'hourly', "ipom_hourly_event");
    }

    // Initial Fetch
    IPOM_Fetcher::fetch_and_store();
});

// Deactivation Hook
register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook("ipom_hourly_event");
});

// Cron Hook
add_action("ipom_hourly_event", ['IPOM_Fetcher', 'fetch_and_store']);
