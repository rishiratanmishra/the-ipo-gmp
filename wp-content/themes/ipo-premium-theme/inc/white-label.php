<?php
/**
 * IPO Premium White Label
 *
 * @package IPO_Premium
 */

if (!defined('ABSPATH')) {
    exit;
}

// Only run if option is enabled (we'd have an option for this, or just hardcoded for the "Developer" version)
// For now, we'll add a simple filter based approach.

/**
 * Filter the Theme Name in Appearance > Themes
 * Note: This is tricky in WP as style.css is read directly. 
 * We can only change how it appears in the admin menu or customizer.
 */

/*
// Example: Change "IPO Theme Settings" to "My Brand Settings"
add_filter( 'gettext', 'ipopro_white_label_text', 20, 3 );
function ipopro_white_label_text( $translated_text, $text, $domain ) {
    if ( $domain === 'ipo-premium' ) {
        $brand_name = get_option( 'ipopro_brand_name', 'IPO Premium' ); 
        if( $translated_text === 'IPO Theme Settings' ) {
            return $brand_name . ' Settings';
        }
    }
    return $translated_text;
}
*/
