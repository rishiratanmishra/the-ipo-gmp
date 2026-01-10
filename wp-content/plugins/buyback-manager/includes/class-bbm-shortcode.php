<?php
class BBM_Shortcode{
    /**
     * Shortcode Handler
     *
     * Render buyback tables on the frontend.
     * Shortcode: [bbm_table]
     *
     * @since 1.0.0
     */

function __construct(){
 add_shortcode("buyback_list",[$this,"render"]);
 add_action("wp_enqueue_scripts",[$this,"assets"]);
}

function assets(){
 wp_enqueue_style("bbm-css",BBM_URL."style.css");
 wp_enqueue_script("bbm-js",BBM_URL."tabs.js",['jquery'],false,true);
}

function render(){
 global $wpdb;
 $table_name = BBM_TABLE;
 $tabs=["OPEN"=>"Open","UPCOMING"=>"Upcoming","CLOSED"=>"Closed"];

 ob_start();
 echo "<div class='bbm-tabs'>";

 echo "<ul class='bbm-tab-nav'>";
 foreach($tabs as $k=>$v){
    // Determine active class for first tab could be handled here or via JS defaults
    echo "<li data-tab='$k'>$v</li>";
 }
 echo "</ul>";

 foreach($tabs as $k=>$v){

    // Query Custom Table
    // Use prepared statement for safety even though $k is internal array key
    $posts = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $table_name WHERE type = %s ORDER BY id DESC LIMIT 20", $v) // Matching 'Open', 'Upcoming' from values
    );

    echo "<div class='bbm-tab' id='tab_$k'>";

    if($posts){
        foreach($posts as $p){

            // Map DB columns to variables
            // DB: company, price, status, logo, market_price...
            $price   = esc_html($p->price);
            $status  = esc_html($p->status);
            $logo    = esc_url($p->logo);
            $title   = esc_html($p->company);
            
            // Optional: Format Price or add labels
            
            echo "<div class='bm-broker-card'>
            <div class='bm-card-inner'>
                <div class='bm-col-logo'>
                    <div class='bm-logo-wrapper'>".
                    ($logo ? "<img src='$logo' class='bm-logo'>" : "<span>No Logo</span>")
                    ."</div>
                </div>
                <div class='bm-col-details'>
                    <h3>{$title}</h3>
                    <div class='bm-meta-row'>
                        <span class='bm-price'>Ask: $price</span>
                        <span class='bm-status'>$status</span>
                    </div>
                </div>
            </div>
            </div>";
        }
    } else {
        echo "<p>No entries found for $v.</p>";
    }
    echo "</div>";
 }

 echo "</div>";
 return ob_get_clean();
}
}
new BBM_Shortcode();
