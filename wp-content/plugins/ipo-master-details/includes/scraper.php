<?php
/**
 * Fetches detailed IPO data from the source URL.
 * Wraps logic to ensure function existence checks for safety.
 *
 * @param int|string $id The IPO ID.
 * @param string $slug The IPO Slug.
 * @param string $manual_url Optional manual URL override.
 * @return array The scraped data or error array.
 */
if (!function_exists('fetch_ipo_details_data')) {
    function fetch_ipo_details_data($id, $slug, $manual_url = '') {
        // Debug logging removed

        header("Content-Type: application/json; charset=UTF-8");

        $url = ($id && $slug)
            ? "https://www.ipopremium.in/view/ipo/$id/$slug"
            : $manual_url;
            


        if (!$url) {

            return ["error" => "Missing url or id+slug"];
        }

        // 1. Fetch HTML Content
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        ]);

        $html = curl_exec($ch);
        $err  = curl_error($ch);
        $info = curl_getinfo($ch); // detailed info
        curl_close($ch);

        if ($err) {

            return ["error" => "cURL Error: $err"];
        }
        


        if (!$html || $info['http_code'] != 200) {

            return ["error" => "Failed to fetch HTML or 404"];
        }

        // 2. Parse HTML using DOMDocument
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();
        

        
        // ... (rest of code)
        
        $xpath = new DOMXPath($dom);

        // Helper function defined conditionally for safety
        if (!function_exists('clean_scraper_text')) {
            function clean_scraper_text($s) {
                return trim(preg_replace('/\s+/', ' ', $s));
            }
        }

        $data = [];

        // --- Basic Info ---
        $data['ipo_name'] = clean_scraper_text($xpath->query("//h2[contains(@class,'profile-username')]")->item(0)->textContent ?? '');
        $data['dates']    = clean_scraper_text($xpath->query("//p[contains(@class,'text-muted')]")->item(0)->textContent ?? '');
        $data['image']    = $xpath->query("//div[contains(@class,'box-profile')]//img")->item(0)->getAttribute("src") ?? '';

        // --- Basic Details Table ---
        $basic = [];
        foreach ($xpath->query("//div[contains(@class,'box-profile')]//table//tr") as $r) {
            $td = $r->getElementsByTagName("td");
            $th = $r->getElementsByTagName("th");
            if ($td->length >= 2) $basic[clean_scraper_text($td->item(0)->textContent)] = clean_scraper_text($td->item(1)->textContent);
            if ($th->length >= 2) $basic[clean_scraper_text($th->item(0)->textContent)] = clean_scraper_text($th->item(1)->textContent);
        }
        $data['basic_details'] = $basic;

        // --- 1. Documents (RHP / Anchor PDFs) ---
        $docs = [];
        $seen = [];
        foreach ($xpath->query("//div[contains(@class,'box-profile')]//a[contains(@href,'.pdf')]") as $a) {
            $url = $a->getAttribute("href");
            if (isset($seen[$url])) continue; 
            $seen[$url] = true;
            $title = clean_scraper_text($a->textContent);
            if ($title === '') {
                $title = basename(parse_url($url, PHP_URL_PATH));
            }
            $docs[] = ["title" => $title, "url"   => $url];
        }
        $data['documents'] = $docs;

        // --- 2. Application-Wise Breakup ---
        $application_breakup = [];
        $table = $xpath->query("//th[contains(.,'Application-Wise')]/ancestor::table")->item(0);
        if ($table) {
            $headers = [];
            foreach ($xpath->query(".//thead/tr[last()]/th", $table) as $th) {
                $headers[] = clean_scraper_text($th->textContent);
            }
            foreach ($xpath->query(".//tbody/tr", $table) as $r) {
                $td = $r->getElementsByTagName("td");
                if ($td->length === count($headers)) {
                    $row = [];
                    foreach ($headers as $i => $key) {
                        $row[$key] = clean_scraper_text($td->item($i)->textContent);
                    }
                    $application_breakup[] = $row;
                }
            }
        }
        $data['application_breakup'] = $application_breakup;

        // --- 3. Subscription Demand ---
        $subscription_demand = [];
        $table = $xpath->query("//th[contains(.,'Subscription Demand')]/ancestor::table")->item(0);
        if ($table) {
            $headers = [];
            foreach ($xpath->query(".//thead/tr[last()]/th", $table) as $th) {
                $headers[] = clean_scraper_text($th->textContent);
            }
            foreach ($xpath->query(".//tbody/tr", $table) as $r) {
                $td = $r->getElementsByTagName("td");
                if ($td->length === count($headers)) {
                    $row = [];
                    foreach ($headers as $i => $key) {
                        $row[$key] = clean_scraper_text($td->item($i)->textContent);
                    }
                    $subscription_demand[] = $row;
                }
            }
        }
        $data['subscription_demand'] = $subscription_demand;

        // --- Subscription Card ---
        $subscription = [];
        $table = $xpath->query("//h2[contains(.,'Subscription')]/ancestor::div[contains(@class,'card')]//table[1]")->item(0);
        if ($table) {
            $headers = [];
            foreach ($xpath->query(".//thead/tr/th", $table) as $th) {
                $headers[] = clean_scraper_text($th->textContent);
            }
            foreach ($xpath->query(".//tbody/tr", $table) as $r) {
                $td = $r->getElementsByTagName("td");
                if ($td->length === count($headers)) {
                    $row = [];
                    foreach ($headers as $i => $key) {
                        $row[$key] = clean_scraper_text($td->item($i)->textContent);
                    }
                    $subscription[] = $row;
                }
            }
        }
        $data['subscription'] = $subscription;


        // --- 4. QIB Interest ---
        $qib = [];
        foreach ($xpath->query("//th[contains(.,'QIB Interest')]/ancestor::table//td") as $td) {
            $qib[] = clean_scraper_text($td->textContent);
        }
        $data['qib_interest'] = $qib;

        // --- 5. Lot Distribution ---
        $lot_distribution = [];
        $table = $xpath->query("//h2[contains(.,'Lot')]/ancestor::div[contains(@class,'card')]//table")->item(0);
        if ($table) {
            $headers = [];
            foreach ($xpath->query(".//tr[1]/th", $table) as $th) {
                $headers[] = clean_scraper_text($th->textContent);
            }
            foreach ($xpath->query(".//tr[position()>1]", $table) as $r) {
                $td = $r->getElementsByTagName("td");
                if ($td->length === count($headers)) {
                    $row = [];
                    foreach ($headers as $i => $key) {
                        $row[$key] = clean_scraper_text($td->item($i)->textContent);
                    }
                    $lot_distribution[] = $row;
                }
            }
        }
        $data['lot_distribution'] = $lot_distribution;

        // --- 6. Reservation ---
        $reservation = [];
        $table = $xpath->query("//h2[contains(.,'Reservation')]/ancestor::div[contains(@class,'card')]//table")->item(0);
        if ($table) {
            $headers = [];
            foreach ($xpath->query(".//tr[1]/th", $table) as $th) {
                $headers[] = clean_scraper_text($th->textContent);
            }
            foreach ($xpath->query(".//tr[position()>1]", $table) as $r) {
                $td = $r->getElementsByTagName("td");
                if ($td->length === count($headers)) {
                    $row = [];
                    foreach ($headers as $i => $key) {
                        $row[$key] = clean_scraper_text($td->item($i)->textContent);
                    }
                    $reservation[] = $row;
                }
            }
        }
        $data['reservation'] = $reservation;

        // --- 7. IPO Details ---
        $ipoDetails = [];
        foreach ($xpath->query("//h5[contains(.,'IPO Details')]/following::table[1]//tr") as $r) {
            $td = $r->getElementsByTagName("td");
            if ($td->length >= 2) {
                $ipoDetails[clean_scraper_text($td->item(0)->textContent)] = clean_scraper_text($td->item(1)->textContent);
            }
        }
        $data['ipo_details'] = $ipoDetails;

        // --- KPI Metrics ---
        $kpi = [];
        $table = $xpath->query("//h5[contains(.,'KPI')]/following::table[1]")->item(0);
        if ($table) {
            $headers = [];
            foreach ($xpath->query(".//thead//th", $table) as $i => $th) {
                if ($i > 0) $headers[] = clean_scraper_text($th->textContent);
            }
            foreach ($xpath->query(".//tbody//tr", $table) as $r) {
                $row = [];
                $th = $r->getElementsByTagName("th");
                $td = $r->getElementsByTagName("td");
                if ($th->length && $td->length) {
                    $row['kpi'] = clean_scraper_text($th->item(0)->textContent);
                    foreach ($td as $i => $cell) {
                        if (isset($headers[$i])) {
                            $row[$headers[$i]] = clean_scraper_text($cell->textContent);
                        }
                    }
                    $kpi[] = $row;
                }
            }
        }
        $data['kpi'] = $kpi;

        // --- Peer Comparison (Valuation) ---
        $peerVal = [];
        $table = $xpath->query("//span[contains(.,'Peer Comparison (Valuation)')]/following::table[1]")->item(0);
        if ($table) {
            $headers = [];
            foreach ($xpath->query(".//thead//th", $table) as $i => $th) {
                if ($i > 0) $headers[] = clean_scraper_text($th->textContent);
            }
            foreach ($xpath->query(".//tbody//tr", $table) as $r) {
                $row = [];
                $th = $r->getElementsByTagName("th");
                $td = $r->getElementsByTagName("td");
                if ($th->length) {
                    $row['company'] = clean_scraper_text($th->item(0)->textContent);
                    foreach ($td as $i => $cell) {
                        if (isset($headers[$i])) $row[$headers[$i]] = clean_scraper_text($cell->textContent);
                    }
                    $peerVal[] = $row;
                }
            }
        }
        $data['peer_valuation'] = $peerVal;

        // --- Peer Comparison (Financial) ---
        $peerFin = [];
        $table = $xpath->query("//span[contains(.,'Peer Comparison (Financial')]/following::table[1]")->item(0);
        if ($table) {
            $headers = [];
            foreach ($xpath->query(".//thead//th", $table) as $i => $th) {
                if ($i > 0) $headers[] = clean_scraper_text($th->textContent);
            }
            foreach ($xpath->query(".//tbody//tr", $table) as $r) {
                $row = [];
                $th  = $r->getElementsByTagName("th");
                $td  = $r->getElementsByTagName("td");
                if ($th->length) {
                    $row['company'] = clean_scraper_text($th->item(0)->textContent);
                    foreach ($td as $i => $cell) {
                        if (isset($headers[$i])) $row[$headers[$i]] = clean_scraper_text($cell->textContent);
                    }
                    $peerFin[] = $row;
                }
            }
        }
        $data['peer_financials'] = $peerFin;

        // --- 11. About Company ---
        $data['about_company'] = clean_scraper_text($xpath->query("//h5[contains(.,'About Company')]/following-sibling::p")->item(0)->textContent ?? '');

        // --- 12. Lead Managers ---
        $lm = [];
        foreach ($xpath->query("//h2[contains(.,'Lead Manager')]/ancestor::div[contains(@class,'card')]//a[contains(@href,'/lead-manager/')]") as $a) {
            $lm[] = ["name" => clean_scraper_text($a->textContent)];
        }
        $data['lead_managers'] = $lm;

        // --- Address & Registrar ---
        $data['address'] = clean_scraper_text($xpath->query("//h2[contains(.,'Address')]/ancestor::div[contains(@class,'card')]//address")->item(0)->textContent ?? '');
        $data['registrar'] = clean_scraper_text($xpath->query("//h2[contains(.,'Registrar')]/ancestor::div[contains(@class,'card')]")->item(0)->textContent ?? '');

        return $data;
    }
}
?>
