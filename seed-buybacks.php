<?php
define('WP_USE_THEMES', false);
require_once('wp-load.php');

global $wpdb;
$table = $wpdb->prefix . 'buybacks';

// Check if table exists
if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
    echo "Table $table does not exist. Creating... \n";
    // Include plugin file to access install function if needed, or just run SQL
    // Running SQL directly is safer here
    $sql = "CREATE TABLE $table (
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
    ) {$wpdb->get_charset_collate()};";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Check Count
$count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
echo "Current Buybacks: $count\n";

if($count == 0 || true) { // Always update/insert for demo
    echo "Seeding Real Data...\n";
    
    $data = [
        [
            'company' => 'TCS',
            'price' => '4500', 
            'status' => 'Active',
            'type' => 'Tender Offer',
            'market_price' => '3800', // Approx +18% premium
            'record_date' => '2026-02-15',
            'issue_size' => '18,500 Cr'
        ],
        [
            'company' => 'Infosys',
            'price' => '1850', 
            'status' => 'Ongoing',
            'type' => 'Open Market',
            'market_price' => '1650',
            'issue_size' => '9,300 Cr'
        ],
        [
            'company' => 'Wipro',
            'price' => '550', 
            'status' => 'Unified',
            'type' => 'Tender Offer',
            'market_price' => '480',
            'issue_size' => '12,000 Cr'
        ]
    ];

    foreach($data as $d) {
        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE company = %s", $d['company']));
        if(!$exists) {
            $wpdb->insert($table, $d);
            echo "Inserted: {$d['company']}\n";
        } else {
             $wpdb->update($table, $d, ['id' => $exists]);
             echo "Updated: {$d['company']}\n";
        }
    }
}
