<?php

class IPO_AI_Dynamic_Renderer
{

    /**
     * Filter the content to replace placeholders with real data.
     * 
     * @param string $content
     * @return string
     */
    public static function render_dynamic_content($content)
    {
        if (!is_single() || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        global $post, $wpdb;

        // 1. Get Associated IPO ID
        $table_name = $wpdb->prefix . 'ipo_ai_meta';
        $ipo_id = $wpdb->get_var($wpdb->prepare("SELECT ipo_id FROM $table_name WHERE post_id = %d", $post->ID));

        if (!$ipo_id) {
            return $content;
        }

        // 2. Fetch Live Data (Cached)
        $ipo_data = self::get_live_ipo_data($ipo_id);

        if (!$ipo_data) {
            return $content;
        }

        // 3. Replacements
        $placeholders = [
            '{{IPO_GMP}}' => '₹' . ($ipo_data->premium ?: '0'),
            '{{IPO_PRICE}}' => $ipo_data->price_band,
            '{{IPO_STATUS}}' => $ipo_data->status,
            '{{IPO_SUBSCRIPTION_RETAIL}}' => 'Checking...', // Would need subscription table join
            '{{IPO_DATE_OPEN}}' => $ipo_data->open_date,
            '{{IPO_DATE_CLOSE}}' => $ipo_data->close_date,
        ];

        // Advanced Logic for Subscription if available
        // $sub_data = ... fetch from ipodetails table if exists

        foreach ($placeholders as $key => $value) {
            $content = str_replace($key, '<span class="ipo-dynamic-data">' . esc_html($value) . '</span>', $content);
        }

        // Add Disclaimer if enabled
        if (get_option('ipo_ai_enable_disclaimer')) {
            $disclaimer = '<div class="ipo-ai-disclaimer" style="background:#2c3338; color:#fff; padding:15px; border-left:4px solid #dc3545; margin:20px 0; font-size:0.9em; border-radius: 4px;">
				<strong>⚠️ Disclaimer:</strong> This article is for informational purposes only. The GMP (Grey Market Premium) and subscription data are volatile and market-driven. This is NOT financial advice. Please consult a SEBI-registered investment advisor before bidding.
			</div>';
            $content = $disclaimer . $content;
        }

        return $content;
    }

    /**
     * Fetch IPO data with caching.
     */
    private static function get_live_ipo_data($ipo_id)
    {
        $cache_key = 'ipo_ai_live_data_' . $ipo_id;
        $data = get_transient($cache_key);

        if (false === $data) {
            global $wpdb;
            $master_table = $wpdb->prefix . 'ipomaster';
            $data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $master_table WHERE id = %d", $ipo_id));
            set_transient($cache_key, $data, 5 * MINUTE_IN_SECONDS);
        }

        return $data;
    }

    /**
     * Shortcode for manual injection [ipo_ai_data field="premium"]
     */
    public function shortcode_ipo_data($atts)
    {
        $a = shortcode_atts(array(
            'field' => 'premium',
            'id' => 0 // Optional, defaults to current post's IPO
        ), $atts);

        // logic to fetch specific field...
        return '';
    }
}
