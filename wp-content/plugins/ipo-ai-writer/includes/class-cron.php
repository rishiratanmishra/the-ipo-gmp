<?php

class IPO_AI_Cron
{

    public function schedule_events()
    {
        if (!wp_next_scheduled('ipo_ai_hourly_event')) {
            wp_schedule_event(time(), 'hourly', 'ipo_ai_hourly_event');
        }

        if (!wp_next_scheduled('ipo_ai_daily_event')) {
            wp_schedule_event(time(), 'daily', 'ipo_ai_daily_event');
        }
    }

    /**
     * Hourly: Check for new IPOs and High-Priority Updates.
     */
    public function run_hourly_checks()
    {
        IPO_AI_Logger::log('Cron: Running Hourly Check');

        global $wpdb;
        $master_table = $wpdb->prefix . 'ipomaster';
        $meta_table = $wpdb->prefix . 'ipo_ai_meta';

        // 1. Find New IPOs (Upcoming/Open) not in meta table
        $new_ipos = $wpdb->get_results("
			SELECT m.id, m.name 
			FROM $master_table m
			LEFT JOIN $meta_table meta ON m.id = meta.ipo_id
			WHERE meta.id IS NULL 
			AND m.status IN ('Upcoming', 'Open')
			LIMIT 5
		");

        foreach ($new_ipos as $ipo) {
            IPO_AI_Logger::log("Cron: Found new IPO - {$ipo->name}");
            IPO_AI_Content_Generator::generate_post($ipo->id, false, 'ipo');
        }

        // 1.1 Find New Buybacks
        $buyback_table = $wpdb->prefix . 'buybacks';
        $new_buybacks = $wpdb->get_results("
			SELECT b.id, b.company as name 
			FROM $buyback_table b
			LEFT JOIN $meta_table meta ON b.id = meta.ipo_id AND meta.type = 'buyback'
			WHERE meta.id IS NULL 
			AND (b.type LIKE '%Open%' OR b.type LIKE '%Upcoming%')
			LIMIT 3
		");

        foreach ($new_buybacks as $bb) {
            IPO_AI_Logger::log("Cron: Found new Buyback - {$bb->name}");
            IPO_AI_Content_Generator::generate_post($bb->id, false, 'buyback');
        }

        // 2. Keyword Rotation (Fast Cycle) for 'Open' IPOs
        $open_posts = $wpdb->get_results("
			SELECT meta.post_id, meta.ipo_id 
			FROM $meta_table meta
			JOIN $master_table m ON meta.ipo_id = m.id
			WHERE m.status = 'Open'
		");

        foreach ($open_posts as $p) {
            if (IPO_AI_Keyword_Research::check_keyword_rotation($p->post_id)) {
                IPO_AI_Logger::log("Cron: Rotating keywords for Post {$p->post_id}");
                IPO_AI_Content_Generator::generate_post($p->ipo_id, true); // Force update
            }
        }
    }

    /**
     * Daily: General updates for Upcoming IPOs.
     */
    public function run_daily_updates()
    {
        IPO_AI_Logger::log('Cron: Running Daily Updates');

        global $wpdb;
        $master_table = $wpdb->prefix . 'ipomaster';
        $meta_table = $wpdb->prefix . 'ipo_ai_meta';

        // Find relevant IPOs
        $ipos = $wpdb->get_results("
			SELECT m.id 
			FROM $master_table m
			JOIN $meta_table meta ON m.id = meta.ipo_id
			WHERE m.status IN ('Upcoming', 'Open')
		");

        foreach ($ipos as $ipo) {
            IPO_AI_Content_Generator::generate_post($ipo->id, true);
        }
    }
}
