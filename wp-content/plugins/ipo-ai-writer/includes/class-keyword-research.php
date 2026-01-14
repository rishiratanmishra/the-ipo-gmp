<?php

class IPO_AI_Keyword_Research
{

    /**
     * Generate optimize keywords for an IPO.
     *
     * @param string $ipo_name Name of the IPO.
     * @param string $status Current status (upcoming/open/closed).
     * @param object $ipo_data Full IPO data object.
     * @return array Array of keywords (Primary, Secondary[]).
     */
    public static function generate_keywords($ipo_name, $status, $ipo_data)
    {
        // Prepare data for AI reasoning
        $context = "IPO Name: $ipo_name. Status: $status. GMP: " . ($ipo_data->premium ?? 'N/A');

        $system_prompt = IPO_AI_Prompts::get_keyword_research_system_prompt();
        $user_prompt = IPO_AI_Prompts::get_keyword_research_user_prompt($context);

        $ai_engine = new IPO_AI_Engine();
        $response = $ai_engine->generate_text($system_prompt, $user_prompt);

        if (is_wp_error($response)) {
            IPO_AI_Logger::log('Keyword Generation Failed', $response->get_error_message());
            // Fallback for AI engine error
            return array(
                'primary' => "$ipo_name IPO Status",
                'secondary' => array("$ipo_name IPO GMP", "$ipo_name Allotment Status"),
                'title_suffix' => "$ipo_name IPO Review"
            );
        }

        // Clean JSON markdown if present
        $json_str = str_replace(array('```json', '```'), '', $response);
        $data = json_decode($json_str, true);

        if (!$data) {
            IPO_AI_Logger::log('Keyword Generation Failed', 'Failed to decode JSON response from AI: ' . $json_str);
            // Fallback for JSON decoding error
            return array(
                'primary' => "$ipo_name IPO Status",
                'secondary' => array("$ipo_name IPO GMP", "$ipo_name Allotment Status"),
                'title_suffix' => "$ipo_name IPO Review"
            );
        }

        return $data;
    }

    /**
     * Check if keywords need rotation (Fast-Cycle 6 Hours).
     * 
     * @param int $post_id
     * @return bool True if rotation is needed.
     */
    public static function check_keyword_rotation($post_id)
    {
        // 1. Rank Guard: Check if post is already popular
        // We try to find common view count meta keys
        $views = (int) get_post_meta($post_id, 'post_views_count', true);
        if (!$views) {
            $views = (int) get_post_meta($post_id, 'views', true); // Fallback
        }

        // If post has significant traffic (> 50 views in last 24h approximation or high total)
        // For now, let's say if total views > 500, we consider it "Ranked/Indexed" and stop messing with Title.
        if ($views > 500) {
            IPO_AI_Logger::log("Rank Guard: Skiping rotation for Post $post_id. Too popular ($views views).");
            return false;
        }

        // 2. Time Check
        $last_updated = get_post_meta($post_id, '_ipo_ai_last_keyword_update', true);

        if (!$last_updated) {
            return true;
        }

        $diff = time() - strtotime($last_updated);
        return ($diff > (6 * HOUR_IN_SECONDS));
    }
}
