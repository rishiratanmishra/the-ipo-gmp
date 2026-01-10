<?php
require_once 'wp-load.php';

global $wpdb;
$t_details = $wpdb->prefix . 'ipodetails';
$details_row = $wpdb->get_row("SELECT details_json FROM $t_details WHERE ipo_id = 1105");
$details = json_decode($details_row->details_json, true);

echo "Application Breakup Data:\n";
echo "========================\n\n";
print_r($details['application_breakup']);
