<?php

class IPO_AI_Engine
{

    private $api_key;

    public function __construct()
    {
        $this->api_key = get_option('ipo_ai_openrouter_api_key');
    }

    /**
     * Send prompt to OpenRouter API.
     *
     * @param string $system_prompt The system instruction.
     * @param string $user_prompt   The user query/data.
     * @return string|WP_Error Generated text or error.
     */
    public function generate_text($system_prompt, $user_prompt)
    {
        // Default to OpenRouter as per user request
        return $this->generate_via_openrouter($system_prompt, $user_prompt);
    }

    private function generate_via_openrouter($system_prompt, $user_prompt)
    {
        if (empty($this->api_key)) {
            return new WP_Error('missing_key', 'OpenRouter API Key is missing.');
        }

        // Models to try in order (Free Tier Focus)
        // 1. Nvidia Nemotron (High quality free)
        // 2. AllenAI Molmo (Good backup)
        $models = [
            'nvidia/nemotron-nano-9b-v2:free',
            'allenai/molmo-2-8b:free'
        ];

        // Specific overrides if user selected a different premium one in settings, 
        // but for now we prioritize the reliable free ones requested.
        // If we want to respect settings:
        $app_model = get_option('ipo_ai_model');
        if (!empty($app_model) && !in_array($app_model, $models)) {
            array_unshift($models, $app_model); // Try user setting first
        }

        $url = 'https://openrouter.ai/api/v1/chat/completions';

        foreach ($models as $model) {
            IPO_AI_Logger::log("Attempting generation with model: $model");

            $body = array(
                'model' => $model,
                'messages' => array(
                    array(
                        'role' => 'system',
                        'content' => $system_prompt,
                    ),
                    array(
                        'role' => 'user',
                        'content' => $user_prompt,
                    ),
                ),
                'temperature' => 0.7,
                'max_tokens' => 3000,
            );

            $args = array(
                'body' => json_encode($body),
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'HTTP-Referer' => get_site_url(),
                    'X-Title' => get_bloginfo('name'),
                ),
                'timeout' => 90, // Extended timeout for free tiers
                'data_format' => 'body',
            );

            $response = wp_remote_post($url, $args);

            if (is_wp_error($response)) {
                IPO_AI_Logger::log("Network Error with $model: " . $response->get_error_message());
                continue; // Try next model
            }

            $response_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if ($response_code === 200 && isset($data['choices'][0]['message']['content'])) {
                IPO_AI_Logger::log("Success with model: $model");
                // Update option to remember successful model for next time? 
                // update_option('ipo_ai_model', $model); 
                return $data['choices'][0]['message']['content'];
            }

            // Log Error and Continue
            $error_msg = isset($data['error']['message']) ? $data['error']['message'] : json_encode($data);
            IPO_AI_Logger::log("API Failed ($model) [$response_code]: $error_msg");
        }

        return new WP_Error('api_error', 'All OpenRouter models failed. Please check your API Key or Credits.');
    }
}
