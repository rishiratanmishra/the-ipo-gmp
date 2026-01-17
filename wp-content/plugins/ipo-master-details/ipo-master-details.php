<?php
/**
 * Plugin Name: IPO Master Details
 * Description: Scrapes and provides detailed analytics for IPOs.
 * Version: 2.0.0
 * Author: zolaha.com
 * Author URI: https://zolaha.com
 * Package: IPO_Master_Details
 */

if (!defined('ABSPATH'))
    exit;

// Constants
global $wpdb;
define("IPOD_PATH", plugin_dir_path(__FILE__));
define("IPOD_URL", plugin_dir_url(__FILE__));
define("IPOD_MASTER", $wpdb->prefix . "ipomaster"); // Dependency from other plugin
define("IPOD_TABLE", $wpdb->prefix . "ipodetails");

// Include Dependencies
require_once IPOD_PATH . 'includes/class-ipod-fetcher.php';
require_once IPOD_PATH . 'admin/class-ipod-admin.php';
require_once IPOD_PATH . 'includes/class-ipod-api.php';

// Initialize Classes
new IPOD_Admin();

// Activation Hook
register_activation_hook(__FILE__, function () {
    global $wpdb;

    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS " . IPOD_TABLE . " (
        ipo_id BIGINT PRIMARY KEY,
        slug VARCHAR(255),
        details_json LONGTEXT,
        fetched_at DATETIME,
        updated_at DATETIME
    ) $charset;";

    require_once ABSPATH . "wp-admin/includes/upgrade.php";
    dbDelta($sql);

    // Schedule Cron
    if (!wp_next_scheduled("ipodetails_hourly_event")) {
        wp_schedule_event(time(), "hourly", "ipodetails_hourly_event");
    }

    // Initial Fetch
    IPOD_Fetcher::fetch_all();
});

// Deactivation Hook
register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook("ipodetails_hourly_event");
});

// Cron Hook
add_action("ipodetails_hourly_event", ['IPOD_Fetcher', 'fetch_all']);

