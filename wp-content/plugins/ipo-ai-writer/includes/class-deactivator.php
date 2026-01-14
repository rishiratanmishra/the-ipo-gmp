<?php

/**
 * Fired during plugin deactivation.
 */
class IPO_AI_Deactivator
{

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function deactivate()
    {
        // Clear scheduled crons
        $timestamp = wp_next_scheduled('ipo_ai_hourly_event');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'ipo_ai_hourly_event');
        }

        $daily = wp_next_scheduled('ipo_ai_daily_event');
        if ($daily) {
            wp_unschedule_event($daily, 'ipo_ai_daily_event');
        }

        flush_rewrite_rules();
    }
}
