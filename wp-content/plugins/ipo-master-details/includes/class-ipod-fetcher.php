<?php
/**
 * IPOD Fetcher Class
 * 
 * Handles scraping detailed IPO data and storing it in the database.
 * Encapsulates the scraping logic and the scheduling rules.
 * 
 * @package IPO_Master_Details
 */

if (!defined('ABSPATH')) exit;

class IPOD_Fetcher {

    /**
     * Triggered by Cron or Manual Action.
     * Fetches details for eligible IPOs.
     */
    public static function fetch_all() {
        global $wpdb;
        $limit = 15;

        // Calculate precise cutoffs based on WP Local Time
        $now_ts = current_time('timestamp');
        $cutoff_hour = date('Y-m-d H:i:s', $now_ts - 3600);   // 1 Hour ago
        $cutoff_day  = date('Y-m-d H:i:s', $now_ts - 86400);  // 24 Hours ago

        // Query: Select items that are NULL OR (Open & Old) OR (Upcoming & Old)
        // Explicitly excludes Closed items unless they are NULL (never fetched).
        $sql = $wpdb->prepare("
            SELECT 
                m.id, m.slug, m.status, d.fetched_at
            FROM " . IPOD_MASTER . " m
            LEFT JOIN " . IPOD_TABLE . " d ON m.id = d.ipo_id
            WHERE
                d.fetched_at IS NULL
                OR (m.status = 'open' AND d.fetched_at < %s)
                OR (m.status = 'upcoming' AND d.fetched_at < %s)
            ORDER BY 
                (d.fetched_at IS NULL) DESC,
                FIELD(UPPER(m.status),'OPEN','UPCOMING','CLOSED'),
                m.id DESC
            LIMIT %d
        ", $cutoff_hour, $cutoff_day, $limit);

        $ipos = $wpdb->get_results($sql);

        if (!$ipos) return;

        foreach ($ipos as $ipo) {
            if (!self::should_fetch($ipo)) {
                continue;
            }

            $data = self::scrape_data($ipo->id, $ipo->slug);

            // Validation: Ensure valid data before saving
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

    /**
     * Determines if a specific IPO needs to be refetched.
     */
    private static function should_fetch($ipo) {
        if (empty($ipo->fetched_at)) return true;

        $now  = current_time('timestamp'); // Use WP Local Time
        $last = strtotime($ipo->fetched_at);
        $status = strtoupper($ipo->status);

        // Rules matching the query
        if ($status === 'OPEN') return ($now - $last) > 3600; // 1 Hour
        if ($status === 'UPCOMING') return ($now - $last) > 86400; // 24 Hours
        
        return false; 
    }

    /**
     * Scrapes data from external source.
     */
    /**
     * Scrapes data from external source.
     */
    public static function scrape_data($id, $slug) {
        $url = "https://www.ipopremium.in/view/ipo/$id/$slug";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_ENCODING => '', // Handle GZIP
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        ]);

        $html = curl_exec($ch);
        $err  = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if ($err || !$html || $info['http_code'] != 200) {
            return ["error" => "Failed to fetch content"];
        }

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $data = [];

        // Helper: safe clean with unicode support
        $clean = function($s) { 
            return trim(preg_replace('/\s+/u', ' ', $s)); 
        };

        // 1. Basic Info
        $data['ipo_name'] = $clean($xpath->query("//*[contains(@class,'profile-username')]")->item(0)->textContent ?? '');
        $data['dates']    = $clean($xpath->query("//*[contains(@class,'text-muted')]")->item(0)->textContent ?? '');
        $data['image']    = $xpath->query("//div[contains(@class,'box-profile')]//img")->item(0)->getAttribute("src") ?? '';
        
        // 2. Basic Details Table
        $basic = [];
        foreach ($xpath->query("//div[contains(@class,'box-profile')]//table//tr") as $r) {
            $td = $r->getElementsByTagName("td");
            $th = $r->getElementsByTagName("th");
            // Handle both th/td variations
            $k = $th->length ? $clean($th->item(0)->textContent) : ($td->length ? $clean($td->item(0)->textContent) : '');
            $v = $td->length > 1 ? $clean($td->item(1)->textContent) : ($th->length > 1 ? $clean($th->item(1)->textContent) : '');
            
            if($k) $basic[$k] = $v;
        }
        $data['basic_details'] = $basic;

        // 3. Documents
        $docs = [];
        $seen = [];
        foreach ($xpath->query("//div[contains(@class,'box-profile')]//a[contains(@href,'.pdf')]") as $a) {
            $dUrl = $a->getAttribute("href");
            if (isset($seen[$dUrl])) continue;
            $seen[$dUrl] = true;
            $title = $clean($a->textContent) ?: basename(parse_url($dUrl, PHP_URL_PATH));
            $docs[] = ["title" => $title, "url" => $dUrl];
        }
        $data['documents'] = $docs;

        // 4. Scrape Tables (Generic Helper)
        $scrapeTable = function($query, $context = null) use ($xpath, $clean) {
            $rows = [];
            $table = $xpath->query($query, $context)->item(0);
            if (!$table) return [];

            $headers = [];
            foreach ($xpath->query(".//thead//th", $table) as $th) $headers[] = $clean($th->textContent);
            if (empty($headers)) {
                // Try finding headers in first tr if no thead
                foreach ($xpath->query(".//tr[1]/th", $table) as $th) $headers[] = $clean($th->textContent);
            }

            // Body
            $trQuery = empty($xpath->query(".//thead", $table)->length) ? ".//tr[position()>1]" : ".//tbody//tr";
            
            foreach ($xpath->query($trQuery, $table) as $r) {
                $td = $r->getElementsByTagName("td");
                $th = $r->getElementsByTagName("th"); // Sometimes first col is th
                
                $row = [];
                $cells = [];
                
                // Collect all cell texts
                if($th->length) foreach($th as $c) $cells[] = $clean($c->textContent);
                foreach($td as $c) $cells[] = $clean($c->textContent);

                // Map to headers
                foreach ($headers as $i => $h) {
                    if (isset($cells[$i])) $row[$h] = $cells[$i];
                }
                if(!empty($row)) $rows[] = $row;
            }
            return $rows;
        };

        // Subscription, GMP, Lots, etc.
        $data['application_breakup'] = $scrapeTable("//th[contains(.,'Application-Wise')]/ancestor::table");
        $data['subscription']        = $scrapeTable("//h2[contains(.,'Subscription')]/ancestor::div[contains(@class,'card')]//table[1]");
        $data['lot_distribution']    = $scrapeTable("//h2[contains(.,'Lot')]/ancestor::div[contains(@class,'card')]//table");
        $data['reservation']         = $scrapeTable("//h2[contains(.,'Reservation')]/ancestor::div[contains(@class,'card')]//table");
        
        // IPO Details (Key-Value)
        $ipoDetails = [];
        // Use generic selector for robustness
        foreach ($xpath->query("//*[contains(text(),'IPO Details')]/following::table[1]//tr") as $r) {
            $td = $r->getElementsByTagName("td");
            if ($td->length >= 2) {
                $ipoDetails[$clean($td->item(0)->textContent)] = $clean($td->item(1)->textContent);
            }
        }
        $data['ipo_details'] = $ipoDetails;

        // 5. KPI Metrics
        $kpi = [];
        // Robust selector: looks for header containing KPI
        $table = $xpath->query("//*[contains(text(),'KPI')]/following::table[1]")->item(0);
        if ($table) {
            $headers = [];
            foreach ($xpath->query(".//thead//th", $table) as $i => $th) {
                if ($i > 0) $headers[] = $clean($th->textContent);
            }
            foreach ($xpath->query(".//tbody//tr", $table) as $r) {
                $row = [];
                $th = $r->getElementsByTagName("th");
                $td = $r->getElementsByTagName("td");
                if ($th->length && $td->length) {
                    $row['kpi'] = $clean($th->item(0)->textContent);
                    foreach ($td as $i => $cell) {
                        if (isset($headers[$i])) {
                            $row[$headers[$i]] = $clean($cell->textContent);
                        }
                    }
                    $kpi[] = $row;
                }
            }
        }
        $data['kpi'] = $kpi;

        // 6. Peer Comparison (Valuation)
        $peerVal = [];
        $table = $xpath->query("//*[contains(text(),'Peer Comparison (Valuation)')]/following::table[1]")->item(0);
        if ($table) {
            $headers = [];
            foreach ($xpath->query(".//thead//th", $table) as $i => $th) {
                if ($i > 0) $headers[] = $clean($th->textContent);
            }
            foreach ($xpath->query(".//tbody//tr", $table) as $r) {
                $row = [];
                $th = $r->getElementsByTagName("th");
                $td = $r->getElementsByTagName("td");
                if ($th->length) {
                    $row['company'] = $clean($th->item(0)->textContent);
                    foreach ($td as $i => $cell) {
                        if (isset($headers[$i])) $row[$headers[$i]] = $clean($cell->textContent);
                    }
                    $peerVal[] = $row;
                }
            }
        }
        $data['peer_valuation'] = $peerVal;

        // 7. Peer Comparison (Financial)
        $peerFin = [];
        $table = $xpath->query("//*[contains(text(),'Peer Comparison (Financial')]/following::table[1]")->item(0);
        if ($table) {
            $headers = [];
            foreach ($xpath->query(".//thead//th", $table) as $i => $th) {
                if ($i > 0) $headers[] = $clean($th->textContent);
            }
            foreach ($xpath->query(".//tbody//tr", $table) as $r) {
                $row = [];
                $th  = $r->getElementsByTagName("th");
                $td  = $r->getElementsByTagName("td");
                if ($th->length) {
                    $row['company'] = $clean($th->item(0)->textContent);
                    foreach ($td as $i => $cell) {
                        if (isset($headers[$i])) $row[$headers[$i]] = $clean($cell->textContent);
                    }
                    $peerFin[] = $row;
                }
            }
        }
        $data['peer_financials'] = $peerFin;

        // 8. Subscription Demand
        $subscription_demand = [];
        $table = $xpath->query("//th[contains(.,'Subscription Demand')]/ancestor::table")->item(0);
        if ($table) {
             // Logic adapted to use $clean directly
            $headers = [];
            foreach ($xpath->query(".//thead/tr[last()]/th", $table) as $th) {
                $headers[] = $clean($th->textContent);
            }
            foreach ($xpath->query(".//tbody/tr", $table) as $r) {
                $td = $r->getElementsByTagName("td");
                if ($td->length === count($headers)) {
                    $row = [];
                    foreach ($headers as $i => $key) {
                        $row[$key] = $clean($td->item($i)->textContent);
                    }
                    $subscription_demand[] = $row;
                }
            }
        }
        $data['subscription_demand'] = $subscription_demand;

        // 9. QIB Interest
        $qib = [];
        foreach ($xpath->query("//th[contains(.,'QIB Interest')]/ancestor::table//td") as $td) {
            $qib[] = $clean($td->textContent);
        }
        $data['qib_interest'] = $qib;

        // 10. Lead Managers
        $lm = [];
        foreach ($xpath->query("//*[contains(text(),'Lead Manager')]/ancestor::div[contains(@class,'card')]//a[contains(@href,'/lead-manager/')]") as $a) {
            $lm[] = ["name" => $clean($a->textContent)];
        }
        $data['lead_managers'] = $lm;

        // 11. Address & Registrar
        // Use generic selector for robustness
        $data['address']   = $clean($xpath->query("//*[contains(text(),'Address')]/ancestor::div[contains(@class,'card')]//address")->item(0)->textContent ?? '');
        $data['registrar'] = $clean($xpath->query("//*[contains(text(),'Registrar')]/ancestor::div[contains(@class,'card')]")->item(0)->textContent ?? '');

        return $data;
    }
}
