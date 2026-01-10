<?php
$url = "https://www.ipopremium.in/view/ipo/1105/bharat-coking-coal-ltd";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 25,
    CURLOPT_ENCODING => '',
    CURLOPT_USERAGENT => 'Mozilla/5.0'
]);

$html = curl_exec($ch);
curl_close($ch);

// Extract just the Application Breakup table
preg_match('/<table[^>]*>.*?Application-Wise.*?<\/table>/s', $html, $matches);

if ($matches) {
    echo "Application Breakup Table HTML:\n";
    echo "================================\n\n";
    
    // Pretty print the table HTML
    $dom = new DOMDocument();
    @$dom->loadHTML($matches[0]);
    echo $dom->saveHTML();
}
