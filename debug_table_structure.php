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

libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML($html);
libxml_clear_errors();

$xpath = new DOMXPath($dom);

$table = $xpath->query("//*[contains(text(),'Application-Wise')]/ancestor::table")->item(0);

if ($table) {
    echo "HEADERS:\n";
    foreach ($xpath->query(".//thead//th", $table) as $i => $th) {
        echo "  Header $i: " . trim($th->textContent) . "\n";
    }
    
    echo "\nFIRST DATA ROW (all cells in document order):\n";
    $firstRow = $xpath->query(".//tbody//tr", $table)->item(0);
    if ($firstRow) {
        $cellNum = 0;
        foreach ($firstRow->childNodes as $node) {
            if ($node->nodeType === XML_ELEMENT_NODE && ($node->nodeName === 'td' || $node->nodeName === 'th')) {
                echo "  Cell $cellNum ({$node->nodeName}): " . trim($node->textContent) . "\n";
                $cellNum++;
            }
        }
    }
}
