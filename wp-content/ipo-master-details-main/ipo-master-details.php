<?php
/**
 * Plugin Name: IPO Details Pro
 * Description: Fetches IPO detail pages via internal scraper and stores JSON into wp_ipodetails (cron-based, production ready)
 * Version: 1.1
 * Author: Rishi Ratan Mishra
 */

if (!defined('ABSPATH')) exit;

global $wpdb;

/* ================= CONFIG ================= */

define("IPOD_MASTER", $wpdb->prefix . "ipomaster");
define("IPOD_TABLE",  $wpdb->prefix . "ipodetails");

/* ================= ACTIVATE ================= */

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

    if (!wp_next_scheduled("ipodetails_cron_event")) {
        wp_schedule_event(time(), "hourly", "ipodetails_cron_event");
    }
});

/* ================= DEACTIVATE ================= */

register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook("ipodetails_cron_event");
});

/* ================= SCRAPER ================= */

require_once plugin_dir_path(__FILE__) . 'includes/scraper.php';

/* ================= CRON ================= */

add_action("ipodetails_cron_event", "ipodetails_fetch_all");
add_action("admin_post_ipod_manual_batch", "ipod_manual_fetch_wrapper");

function ipod_manual_fetch_wrapper() {
    ipodetails_fetch_all();
    wp_redirect(admin_url("admin.php?page=ipo-details"));
    exit;
}

/* ================= CORE LOGIC ================= */

function ipodetails_fetch_all() {
    global $wpdb;

    // Debug logging removed


    $limit = 15;

    /**
     * Fetch IPOs that need updating.
     * Criteria:
     * 1. Not yet fetched (fetched_at is NULL).
     * 2. Or fetched more than 1 hour ago.
     * Prioritizes valid statuses (OPEN, UPCOMING, CLOSED).
     */
    $ipos = $wpdb->get_results(
        $wpdb->prepare(
            "
            SELECT 
                m.id,
                m.slug,
                m.status,
                d.fetched_at
            FROM " . IPOD_MASTER . " m
            LEFT JOIN " . IPOD_TABLE . " d 
                ON m.id = d.ipo_id
            WHERE
                d.fetched_at IS NULL
                OR d.fetched_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ORDER BY 
                FIELD(UPPER(m.status),'OPEN','UPCOMING','CLOSED'),
                m.id DESC
            LIMIT %d
            ",
            $limit
        )
    );



    if (!$ipos) return;

    foreach ($ipos as $ipo) {

        if (!ipodetails_should_fetch($ipo)) {
            continue;
        }



        $data = fetch_ipo_details_data($ipo->id, $ipo->slug);

        if (!$data || isset($data['error']) || empty($data['ipo_name'])) {

            continue;
        }

        $wpdb->replace(IPOD_TABLE, [
            "ipo_id"       => $ipo->id,
            "slug"         => $ipo->slug,
            "details_json" => wp_json_encode($data, JSON_UNESCAPED_UNICODE),
            "fetched_at"   => current_time("mysql"),
            "updated_at"   => current_time("mysql"),
        ]);


    }
}

/* ================= FETCH RULES ================= */

/**
 * Determines if a specific IPO needs to be refetched based on its status and last fetch time.
 *
 * @param object $ipo The IPO object from the database.
 * @return bool True if it should be fetched, false otherwise.
 */
function ipodetails_should_fetch($ipo) {

    if (empty($ipo->fetched_at)) {
        return true;
    }

    $now  = time();
    $last = strtotime($ipo->fetched_at);
    $status = strtoupper($ipo->status);

    // OPEN → every 1 hour
    if ($status === 'OPEN') {
        return ($now - $last) > 3600;
    }

    // UPCOMING → once per day
    if ($status === 'UPCOMING') {
        return ($now - $last) > 86400;
    }

    // CLOSED → fetch only once
    return false;
}

/* ================= ADMIN PAGE ================= */

require_once plugin_dir_path(__FILE__) . 'includes/admin-dashboard.php';

add_action("admin_menu", function () {
    add_menu_page(
        "IPO Details",
        "IPO Details",
        "manage_options",
        "ipo-details",
        "ipodetails_admin_page",
        "dashicons-media-spreadsheet",
        27
    );
});
