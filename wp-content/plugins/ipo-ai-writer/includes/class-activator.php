<?php

/**
 * Fired during plugin activation.
 */
class IPO_AI_Activator
{

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate()
    {
        self::create_tables();
        self::create_categories();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create necessary database tables.
     */
    private static function create_tables()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'ipo_ai_meta';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			ipo_id bigint(20) NOT NULL,
			post_id bigint(20) NOT NULL,
			type varchar(20) DEFAULT 'ipo' NOT NULL,
			last_updated datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			current_stage varchar(50) DEFAULT 'upcoming' NOT NULL,
			target_keywords text,
			performance_metrics text,
			generation_log longtext,
			PRIMARY KEY  (id),
			KEY ipo_id (ipo_id)
		) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create default categories if they don't exist.
     */
    private static function create_categories()
    {
        $categories = [
            'Mainboard IPOs' => 'mainboard-ipos',
            'SME IPOs' => 'sme-ipos',
            'Buyback' => 'buyback'
        ];

        foreach ($categories as $name => $slug) {
            if (!term_exists($name, 'category')) {
                wp_insert_term(
                    $name,
                    'category',
                    array(
                        'slug' => $slug,
                    )
                );
            }
        }
    }
}
