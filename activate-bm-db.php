<?php
define('WP_USE_THEMES', false);
require_once('wp-load.php');
require_once('wp-content/plugins/broker-manager/broker-manager.php');

if(function_exists('bm_install')) {
    bm_install();
    echo "Broker Table Created/Updated Successfully.";
} else {
    echo "Error: bm_install function not found.";
}
