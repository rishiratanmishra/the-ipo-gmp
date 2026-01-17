<?php
/**
 * Plugin Name: Buyback Manager
 * Description: Manages buyback data, scraping, and admin tabs.
 * Version: 1.0.0
 * Author: zolaha.com
 * Author URI: https://zolaha.com
 */

if (!defined("ABSPATH"))
    exit;
/**
 * Buyback Manager Main Plugin File
 *
 * Bootstrap file to initialize constants, database tables, and include dependencies.
 *
 * @package    zolaha_Buyback_Manager
 * @since      1.0.0
 * @author     zolaha.com
 * @link       https://zolaha.com
 */

define("BBM_PATH", plugin_dir_path(__FILE__));
define("BBM_URL", plugin_dir_url(__FILE__));

// Include Dependencies
require_once BBM_PATH . 'admin/class-bbm-admin.php';
require_once BBM_PATH . 'includes/class-bbm-scraper.php';
require_once BBM_PATH . 'includes/class-bbm-shortcode.php';
require_once BBM_PATH . 'includes/class-bbm-api.php';

// Deprecated: Using custom table instead
// include BBM_PATH . "includes/class-bbm-cpt.php";

// Table Name
global $wpdb;
define('BBM_TABLE', $wpdb->prefix . 'buybacks');

// Install Table Logic
function bbm_install_table()
{
    global $wpdb;
    $table_name = BBM_TABLE;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        company varchar(255) NOT NULL,
        price varchar(100) DEFAULT '',
        status varchar(100) DEFAULT '',
        type varchar(50) DEFAULT '',
        logo varchar(255) DEFAULT '',
        market_price varchar(100) DEFAULT '',
        record_date varchar(100) DEFAULT '',
        period varchar(255) DEFAULT '',
        issue_size varchar(100) DEFAULT '',
        shares varchar(100) DEFAULT '',
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY company_type (company, type)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Add Custom Cron Interval (5 Hours)
add_filter('cron_schedules', function ($schedules) {
    $schedules['every_5_hours'] = [
        'interval' => 5 * 3600,
        'display' => __('Every 5 Hours')
    ];
    return $schedules;
});

// Register Event on Activation
register_activation_hook(__FILE__, function () {
    bbm_install_table(); // Create Table
    BBM_Scraper::fetch_and_store(); // Run once immediately

    // Store Default API Key if not exists
    if (!get_option('bbm_api_key')) {
        update_option('bbm_api_key', 'zolaha_secure_' . wp_generate_password(12, false));
    }

    // Schedule Cron if not exists
    if (!wp_next_scheduled('bbm_auto_fetch_event')) {
        wp_schedule_event(time(), 'every_5_hours', 'bbm_auto_fetch_event');
    }
});

// Clear Event on Deactivation
register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook('bbm_auto_fetch_event');
});

// Hook the event to the scraper function
add_action('bbm_auto_fetch_event', ['BBM_Scraper', 'fetch_and_store']);

// Clear Event on Deactivation
register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook('bbm_daily_scrape_event');
});

// Hook the event to the scraper function
add_action('bbm_daily_scrape_event', ['BBM_Scraper', 'fetch_and_store']);
