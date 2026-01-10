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
        // Application Breakup needs special handling - first header is decorative, skip it
        $appBreakup = [];
        $appTable = $xpath->query("//*[contains(text(),'Application-Wise')]/ancestor::table")->item(0);
        if ($appTable) {
            // Get headers - skip the first one as it's decorative
            $headers = [];
            $allHeaders = [];
            foreach ($xpath->query(".//thead//th", $appTable) as $th) {
                $allHeaders[] = $clean($th->textContent);
            }
            // Skip the first header (Application-Wise Breakup) - it doesn't have corresponding data
            $headers = array_slice($allHeaders, 1);
            
            // Get data rows
            $trQuery = ".//tbody//tr";
            if ($xpath->query(".//thead", $appTable)->length == 0) {
                $trQuery = ".//tr[position()>1]";
            }
            
            foreach ($xpath->query($trQuery, $appTable) as $r) {
                $cells = [];
                
                // Collect cells in DOCUMENT ORDER
                foreach ($r->childNodes as $node) {
                    if ($node->nodeType === XML_ELEMENT_NODE && ($node->nodeName === 'td' || $node->nodeName === 'th')) {
                        $cells[] = $clean($node->textContent);
                    }
                }
                
                // Map to headers
                $row = [];
                foreach ($headers as $i => $h) {
                    if (isset($cells[$i])) {
                        $row[$h] = $cells[$i];
                    }
                }
                
                // Skip rows with "Total Applications" or other footer text
                $skipRow = false;
                foreach ($row as $k => $v) {
                    if (stripos($k, 'Total Applications') !== false || stripos($v, 'Total Applications') !== false || stripos($v, 'IPO Premium') !== false) {
                        $skipRow = true;
                        break;
                    }
                }
                
                if (!$skipRow && count($row) > 1) {
                    $appBreakup[] = $row;
                }
            }
        }
        $data['application_breakup'] = $appBreakup;

        $data['subscription']        = $scrapeTable("//*[contains(text(),'Subscription')]/ancestor::div[contains(@class,'card')]//table[1]");
        $data['lot_distribution']    = $scrapeTable("//*[contains(text(),'Lot') and contains(text(),'Distribution')]/ancestor::div[contains(@class,'card')]//table");
        $data['reservation']         = $scrapeTable("//*[contains(text(),'Reservation')]/ancestor::div[contains(@class,'card')]//table");
        
        // IPO Details (Key-Value)
        $ipoDetails = [];
        foreach ($xpath->query("//*[contains(text(),'IPO Details')]/following::table[1]//tr") as $r) {
            $td = $r->getElementsByTagName("td");
            if ($td->length >= 2) {
                $ipoDetails[$clean($td->item(0)->textContent)] = $clean($td->item(1)->textContent);
            }
        }
        $data['ipo_details'] = $ipoDetails;

        // 5. KPI Metrics
        $data['kpi'] = $scrapeTable("//*[contains(text(),'Key Performance Indicators') or contains(text(),'KPI')]/following::table[1]");

        // 6. Company Financials
        $data['company_financials'] = $scrapeTable("//*[contains(text(),'Company Financial')]/following::table[1]");

        // 7. Peer Comparison (Valuation and Financial)
        // Note: Using flexible contains to handle 'Comparison' vs 'Comparision'
        $data['peer_valuation']  = $scrapeTable("//*[contains(text(),'Peer Compar') and contains(text(),'Valuation')]/following::table[1]");
        $data['peer_financials'] = $scrapeTable("//*[contains(text(),'Peer Compar') and contains(text(),'Financial')]/following::table[1]");

        // 8. Subscription Demand
        $data['subscription_demand'] = $scrapeTable("//*[contains(text(),'Subscription Demand')]/ancestor::table");

        // 9. QIB Interest
        $qib = [];
        foreach ($xpath->query("//*[contains(text(),'QIB Interest')]/ancestor::table//td") as $td) {
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
        $data['address']   = $clean($xpath->query("//*[contains(text(),'Address')]/ancestor::div[contains(@class,'card')]//address")->item(0)->textContent ?? '');
        
        // Refined Registrar
        $regCard = $xpath->query("//*[contains(text(),'Registrar')]/ancestor::div[contains(@class,'card')]")->item(0);
        if ($regCard) {
            $data['registrar_name'] = $clean($xpath->query(".//a[contains(@href,'/registrar/')]", $regCard)->item(0)->textContent ?? '');
            $data['registrar_phone'] = $clean($xpath->query(".//a[contains(@href,'tel:')]", $regCard)->item(0)->textContent ?? '');
            $data['registrar_email'] = $clean($xpath->query(".//a[contains(@href,'mailto:')]", $regCard)->item(0)->textContent ?? '');
            $data['registrar_url']   = $xpath->query(".//a[contains(@target,'_blank')]", $regCard)->item(0)->getAttribute('href') ?? '';
            $data['registrar']       = $clean($regCard->textContent); // Fallback full text
        }

        // 12. About, Strength, Weakness (Using IDs for precision)
        $aboutHeader = $xpath->query("//*[@id='ipo-about']//*[contains(text(),'About Company')]")->item(0);
        if ($aboutHeader) {
            // Find the actual header node if the text was inside a span/strong
            $h = $aboutHeader;
            while ($h && !preg_match('/^h[1-6]$/i', $h->nodeName)) $h = $h->parentNode;
            $h = $h ?: $aboutHeader;
            
            $aboutText = '';
            foreach($xpath->query("following-sibling::*", $h) as $sibling) {
                $aboutText .= $sibling->textContent . "\n";
            }
            $data['about_company'] = $clean($aboutText);
        }
        
        if (empty($data['about_company'])) {
            $data['about_company'] = $clean($xpath->query("//*[@id='ipo-about']")->item(0)->textContent ?? '');
        }
        
        // Clean "Details will be added soon" if it's the only content
        $soon = 'Details will be added soon';
        if (stripos(trim($data['about_company'] ?? ''), $soon) === 0 && strlen($data['about_company']) < 50) $data['about_company'] = '';

        // Strengths
        $strengths = [];
        $strengthNode = $xpath->query("//*[@id='ipo-strength']")->item(0);
        if ($strengthNode) {
            foreach($xpath->query(".//li", $strengthNode) as $li) $strengths[] = $clean($li->textContent);
            if(empty($strengths)) {
                $stext = $clean($strengthNode->textContent);
                if (stripos($stext, $soon) === false || strlen($stext) > 50) $data['strengths_text'] = $stext;
            }
        }
        $data['strengths'] = $strengths;

        // Weaknesses
        $weaknesses = [];
        $weaknessNode = $xpath->query("//*[@id='ipo-weakness']")->item(0);
        if ($weaknessNode) {
            foreach($xpath->query(".//li", $weaknessNode) as $li) $weaknesses[] = $clean($li->textContent);
            if(empty($weaknesses)) {
                $wtext = $clean($weaknessNode->textContent);
                if (stripos($wtext, $soon) === false || strlen($wtext) > 50) $data['weaknesses_text'] = $wtext;
            }
        }
        $data['weaknesses'] = $weaknesses;

        // 13. Reviewers / Recommendations
        $data['reviewers'] = $scrapeTable("//*[contains(text(),'Reviewers')]/following::table[1]");

        return $data;
    }
}
