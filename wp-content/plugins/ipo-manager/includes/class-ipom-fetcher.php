<?php
/**
 * IPOM Fetcher Class
 *
 * Handles data scraping and database updates.
 *
 * @package IPO_Master_Admin
 */

if (!defined('ABSPATH')) exit;

class IPOM_Fetcher {

    /**
     * Fetch Data from Source
     *
     * @return void
     */
    public static function fetch_and_store() {
        global $wpdb;
        $table_name = defined('IPOM_TABLE') ? IPOM_TABLE : $wpdb->prefix . 'ipomaster';
        
        // Increase timelimit for scraping
        set_time_limit(300);

        $url = "https://www.ipopremium.in/ipo";
        $response = wp_remote_get($url, ["headers" => ["user-agent" => "Mozilla/5.0"]]);

        if (is_wp_error($response)) {
            error_log("IPOM Error: " . $response->get_error_message());
            return;
        }

        $body = wp_remote_retrieve_body($response);
        $json = json_decode($body, true);

        if (!$json || !isset($json["data"])) {
            error_log("IPOM Error: Invalid JSON response");
            return;
        }

        foreach ($json["data"] as $item) {
            $name = strip_tags($item["name"] ?? "");
            $is_sme = 0;

            if (preg_match('/\((.*?)\)/', $name, $m)) {
                if (stripos($m[1], "SME") !== false) $is_sme = 1;
                $name = trim(str_replace($m[0], "", $name));
            }

            $premium_raw = $item["premium"] ?? "";
            $badge = "";
            if (stripos($premium_raw, "SELLER") !== false) $badge = "SELLER";
            elseif (stripos($premium_raw, "BUYER") !== false) $badge = "BUYER";

            $premium = trim(strip_tags($premium_raw));

            $wpdb->replace($table_name, [
                "id" => $item["id"] ?? 0,
                "name" => $name,
                "is_sme" => $is_sme,
                "open_date" => $item["open"] ?? "",
                "close_date" => $item["close"] ?? "",
                "price_band" => $item["price"] ?? "",
                "min_price" => $item["min_price"] ?? "",
                "max_price" => $item["max_price"] ?? "",
                "lot_size" => $item["lot_size"] ?? "",
                "issue_size_cr" => $item["issue_size"] ?? "",
                "premium" => $premium,
                "badge" => $badge,
                "allotment_date" => $item["allotment_date"] ?? "",
                "listing_date" => $item["listing_date"] ?? "",
                "status" => $item["current_status"] ?? "",
                "icon_url" => $item["icon_url"] ?? "",
                "slug" => $item["slug"] ?? "",
                "updated_at" => current_time("mysql")
            ]);
        }

        update_option("ipom_last_fetch", current_time("mysql"));
    }
}
