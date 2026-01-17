<?php
class BBM_Scraper
{
    /**
     * Fetch Live Buyback Data
     *
     * Scrapes data from external source and updates the database.
     * Includes logic to skip already closed buybacks for performance.
     *
     * @since  1.0.0
     * @return void
     */

    public static function fetch_and_store()
    {
        set_time_limit(300); // Allow 5 minutes for execution (vital for sleep delays)
        global $wpdb;

        // zolaha OPTIMIZATION: Get list of already CLOSED buybacks to skip re-scraping
        $table_name = defined('BBM_TABLE') ? BBM_TABLE : $wpdb->prefix . 'buybacks';
        $closed_companies = $wpdb->get_col("SELECT company FROM $table_name WHERE type = 'Closed' OR status = 'Closed'");
        if (!is_array($closed_companies))
            $closed_companies = [];

        $url = "https://groww.in/buy-back";

        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Referer' => 'https://google.com'
        ];

        $response = wp_remote_get($url, [
            'user-agent' => $headers['User-Agent'], // WP uses this key specifically sometimes, but headers array is better
            'headers' => $headers,
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            error_log("BBM Scraper Error: " . $response->get_error_message());
            return;
        }

        $html = wp_remote_retrieve_body($response);
        if (empty($html)) {
            error_log("BBM Scraper Error: Empty HTML");
            return;
        }

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        // find sections (Open / Upcoming / Closed)
        $headings = $xpath->query("//h2");

        foreach ($headings as $h2) {

            $title_text = strtolower(trim($h2->textContent));
            $type = null;

            if (strpos($title_text, "open") === 0)
                $type = "Open";
            elseif (strpos($title_text, "upcoming") === 0)
                $type = "Upcoming";
            elseif (strpos($title_text, "recently closed") === 0)
                $type = "Closed";

            if (!$type)
                continue;

            // next div containing table
            $table = $h2->nextSibling;
            while ($table && $table->nodeName !== "div") {
                $table = $table->nextSibling;
            }

            if (!$table)
                continue;

            $rows = $xpath->query(".//tbody/tr", $table);

            foreach ($rows as $row) {

                $cols = $xpath->query(".//td", $row);
                if ($cols->length < 4)
                    continue;

                // Basic Values
                $company_name = trim($cols->item(1)->textContent);
                $offer_price = trim($cols->item(2)->textContent);
                $status = trim($cols->item(3)->textContent);

                // zolaha OPTIMIZATION: Skip if already closed
                if (in_array($company_name, $closed_companies)) {
                    continue;
                }

                // Logo
                $logoNode = $xpath->query(".//noscript//img", $cols->item(0));
                $logo = $logoNode->length ? $logoNode->item(0)->getAttribute("src") : "";

                // Get Company Page Link → searchId extract
                $linkNode = $xpath->query(".//a", $cols->item(1));
                $detail_url = $linkNode->length ? $linkNode->item(0)->getAttribute("href") : "";

                $searchId = "";
                if ($detail_url) {
                    $parts = explode('/', trim($detail_url, '/'));
                    $searchId = end($parts);
                }

                // Defaults
                $market_price = "";
                $record_date = "";
                $period = "";
                $issue_size = "";
                $shares = "";

                // ----------------------
                // CALL BUYBACK API
                // ----------------------
                if ($searchId) {

                    // ANTI-BAN: Sleep before API call (2 seconds)
                    // sleep(2); // Commented out by user request (Batching/Throttling disabled)

                    $api_url = "https://groww.in/v1/api/stocks_portfolio/v2/buyback/fetch?searchId=" . $searchId;

                    $api_response = wp_remote_get($api_url, [
                        'headers' => [
                            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                            'Accept' => 'application/json',
                            'Referer' => 'https://groww.in/buy-back'
                        ],
                        'timeout' => 20
                    ]);

                    if (!is_wp_error($api_response)) {

                        $body = wp_remote_retrieve_body($api_response);
                        $json = json_decode($body, true);

                        if (!empty($json['data'])) {

                            $data = $json['data'];

                            // Enriched data
                            $offer_price = $data['offerPrice'] ?? $offer_price;
                            $record_date = $data['recordDate'] ?? "";

                            $start_date = $data['startDate'] ?? "";
                            $end_date = $data['endDate'] ?? "";
                            $period = $start_date . " - " . $end_date;

                            $issue_size = $data['issuedAmount'] ?? "";
                            $shares = $data['issuedShares'] ?? "";
                            $logo = $data['companyLogo'] ?? $logo;

                            // ----------------------
                            // MARKET PRICE (NSE / BSE AUTO)
                            // ----------------------
                            if (!empty($data['exchange'])) {

                                $exchange = strtoupper($data['exchange']);
                                $price_api = "";

                                // BSE
                                if ($exchange === "BSE" && !empty($data['scripCode'])) {
                                    $price_api =
                                        "https://groww.in/v1/api/stocks_data/v1/accord_points/exchange/BSE/segment/CASH/latest_prices_ohlc/" .
                                        $data['scripCode'];
                                }

                                // NSE
                                elseif ($exchange === "NSE" && !empty($data['companySymbol'])) {
                                    $price_api =
                                        "https://groww.in/v1/api/stocks_data/v1/accord_points/exchange/NSE/segment/CASH/latest_prices_ohlc/" .
                                        $data['companySymbol'];
                                }

                                if ($price_api) {

                                    // Sleep slightly for price API too if needed, or rely on the previous sleep
                                    // sleep(1); 

                                    $price_res = wp_remote_get($price_api, [
                                        'headers' => [
                                            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                                            'Accept' => 'application/json'
                                        ],
                                        'timeout' => 15
                                    ]);

                                    if (!is_wp_error($price_res)) {

                                        $price_body = wp_remote_retrieve_body($price_res);
                                        $price_json = json_decode($price_body, true);

                                        if (!empty($price_json['ltp'])) {
                                            $market_price = $price_json['ltp'];
                                        } elseif (!empty($price_json['close'])) {
                                            $market_price = $price_json['close'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // ----------------------
                // SAVE DB
                // ----------------------
                global $wpdb;
                $table_name = BBM_TABLE;

                $wpdb->replace(
                    $table_name,
                    [
                        'company' => $company_name,
                        'price' => $offer_price,
                        'status' => $status,
                        'type' => $type,
                        'logo' => $logo,

                        'market_price' => $market_price,
                        'record_date' => $record_date,
                        'period' => $period,
                        'issue_size' => $issue_size,
                        'shares' => $shares,

                        'updated_at' => current_time('mysql')
                    ]
                );
            }
        }
    }
}
