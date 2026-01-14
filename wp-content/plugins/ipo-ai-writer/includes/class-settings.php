<?php

class IPO_AI_Settings
{

    public function add_plugin_admin_menu()
    {
        add_menu_page(
            'IPO AI Writer',
            'IPO AI Writer',
            'manage_options',
            'ipo-ai-writer',
            array($this, 'display_settings_page'),
            'dashicons-welcome-write-blog',
            50
        );

        add_submenu_page(
            'ipo-ai-writer',
            'Settings',
            'Settings',
            'manage_options',
            'ipo-ai-writer'
        );

        add_submenu_page(
            'ipo-ai-writer',
            'Logs',
            'Logs',
            'manage_options',
            'ipo-ai-logs',
            array($this, 'display_logs_page')
        );
    }

    public function register_settings()
    {
        // register_setting('ipo_ai_settings_group', 'ipo_ai_provider'); // Removed - OpenRouter only
        // register_setting('ipo_ai_settings_group', 'ipo_ai_google_api_key'); // Removed
        register_setting('ipo_ai_settings_group', 'ipo_ai_openrouter_api_key');
        register_setting('ipo_ai_settings_group', 'ipo_ai_model');
        register_setting('ipo_ai_settings_group', 'ipo_ai_fallback_provider');
        register_setting('ipo_ai_settings_group', 'ipo_ai_enable_disclaimer');
    }

    public function display_settings_page()
    {
        ?>
        <div class="wrap">
            <h1>IPO AI Writer Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('ipo_ai_settings_group'); ?>
                <?php do_settings_sections('ipo_ai_settings_group'); ?>

                <table class="form-table">
                    <!-- Provider Hidden Field (OpenRouter Force) -->
                    <input type="hidden" name="ipo_ai_provider" value="openrouter">

                    <tr valign="top">
                        <th scope="row">OpenRouter API Key</th>
                        <td>
                            <input type="password" name="ipo_ai_openrouter_api_key"
                                value="<?php echo esc_attr(get_option('ipo_ai_openrouter_api_key')); ?>" class="regular-text" />
                            <p class="description">Enter your <a href="https://openrouter.ai/keys" target="_blank">OpenRouter
                                    API Key</a>.</p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">AI Model</th>
                        <td>
                            <select name="ipo_ai_model">
                                <option value="nvidia/nemotron-nano-9b-v2:free" <?php selected(get_option('ipo_ai_model'), 'nvidia/nemotron-nano-9b-v2:free'); ?>>Nvidia Nemotron 9B (Free - Recommended)</option>
                                <option value="allenai/molmo-2-8b:free" <?php selected(get_option('ipo_ai_model'), 'allenai/molmo-2-8b:free'); ?>>AllenAI Molmo 8B (Free - Backup)</option>
                                <option value="meta-llama/llama-3.1-8b-instruct:free" <?php selected(get_option('ipo_ai_model'), 'meta-llama/llama-3.1-8b-instruct:free'); ?>>Llama 3.1 8B</option>
                            </select>
                            <p class="description">Select a free model supported by OpenRouter.</p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Enable Disclaimer</th>
                        <td>
                            <input type="checkbox" name="ipo_ai_enable_disclaimer" value="1" <?php checked(1, get_option('ipo_ai_enable_disclaimer'), true); ?> />
                            <label>Automatically append financial disclaimer to posts</label>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>

            <hr>

            <h2>Manual Generation Trigger</h2>
            <p>Use this to force generate a post for a specific IPO ID.</p>
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th>Type</th>
                        <td>
                            <select name="force_type" id="force_type_selector" onchange="this.form.submit()">
                                <option value="ipo" <?php selected(isset($_POST['force_type']) ? $_POST['force_type'] : '', 'ipo'); ?>>IPO</option>
                                <option value="buyback" <?php selected(isset($_POST['force_type']) ? $_POST['force_type'] : '', 'buyback'); ?>>Buyback</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>Select Entity</th>
                        <td>
                            <?php
                            global $wpdb;
                            $selected_type = isset($_POST['force_type']) ? $_POST['force_type'] : 'ipo';

                            if ($selected_type === 'buyback') {
                                $table = $wpdb->prefix . 'buybacks';
                                $results = $wpdb->get_results("SELECT id, company as name, type as status FROM $table WHERE type LIKE '%Open%' OR type LIKE '%Upcoming%' ORDER BY id DESC LIMIT 50");
                            } else {
                                $table = $wpdb->prefix . 'ipomaster';
                                $results = $wpdb->get_results("SELECT id, name, status FROM $table WHERE status IN ('Open', 'Upcoming', 'Allotment') ORDER BY id DESC LIMIT 50");
                            }
                            ?>

                            <select name="force_ipo_id" style="min-width: 300px;">
                                <?php if ($results): ?>
                                    <?php foreach ($results as $row): ?>
                                        <option value="<?php echo esc_attr($row->id); ?>">
                                            <?php echo esc_html($row->name . ' (' . $row->status . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="">No active items found</option>
                                <?php endif; ?>
                            </select>
                        </td>
                    </tr>
                </table>
                </td>
                </tr>
                </table>
                <p class="submit">
                    <input type="button" id="start_generation_btn" class="button button-primary" value="Generate Content Now" />
                </p>
            </form>

            <div id="ipo_ai_progress_container"
                style="display:none; margin-top:20px; background:#1d2327; color:#00ff7f; padding:20px; font-family:monospace; border-radius:5px; max-height:300px; overflow-y:auto;">
                <div id="ipo_ai_console_output"></div>
            </div>

            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    $('#start_generation_btn').click(function () {
                        var ipo_id = $('select[name="force_ipo_id"]').val();
                        var type = $('select[name="force_type"]').val();

                        if (!ipo_id) {
                            alert('Please select an IPO/Buyback first.');
                            return;
                        }

                        $('#ipo_ai_progress_container').show();
                        $('#ipo_ai_console_output').html('🚀 Initializing Generator...<br/>');

                        // Disable button
                        $(this).prop('disabled', true);

                        var data = {
                            'action': 'ipo_ai_manual_generate',
                            'ipo_id': ipo_id,
                            'type': type,
                            'security': '<?php echo wp_create_nonce("ipo_ai_gen_nonce"); ?>'
                        };

                        // We use an EventSource-like approach via XHR for streaming if possible, 
                        // but for WP AJAX compatibility, we might just poll or wait. 
                        // Best compat: Standard AJAX but we make the backend output buffer flush.
                        // Actually, simple AJAX waits until end. 
                        // Let's us a customized iframe approach for true streaming or just simulate steps if backend is one-shot.
                        // Given the user wants to SEE steps, we will use a hidden iframe to stream the output.

                        var url = ajaxurl + '?action=ipo_ai_manual_generate&ipo_id=' + ipo_id + '&type=' + type + '&security=' + data.security;
                        $('#ipo_ai_console_output').append('<iframe src="' + url + '" style="width:100%; height:200px; border:none; background:transparent; color:#00ff7f;"></iframe>');
                    });
                });
            </script>
        </div>
        <?php
    }

    // Register the Streaming AJAX Handler in the Constructor or Plugin Init
    // (Note: This function needs to be hooked in class-plugin.php, but we define the method here or simply add the hook in this file constructor for simplicity if possible, but cleaner in plugin.php)
    // For now, I'll add a static helper or just ensure the hook exists.
    // I will add the hook in class-plugin.php in the next step.
    public function display_logs_page()
    {
        $log_file = WP_CONTENT_DIR . '/ipo-ai-debug.log';
        echo '<div class="wrap"><h1>IPO AI Writer Logs</h1>';
        echo '<textarea style="width:100%; height:500px; font-family:monospace;">';
        if (file_exists($log_file)) {
            echo esc_textarea(file_get_contents($log_file));
        } else {
            echo 'No logs found.';
        }
        echo '</textarea></div>';
    }

    /**
     * AJAX Stream Handler
     */
    public static function stream_generation()
    {
        // Security Check
        check_ajax_referer('ipo_ai_gen_nonce', 'security');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $ipo_id = intval($_GET['ipo_id']);
        $raw_type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'ipo';
        $allowed_types = array('ipo', 'buyback');
        if (!in_array($raw_type, $allowed_types)) {
            wp_die('Invalid type parameter');
        }
        $type = $raw_type;

        // Prevent Timeouts & Memory Issues (Critical)
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        // Disable buffering & compression for streaming
        if (function_exists('apache_setenv')) {
            apache_setenv('no-gzip', 1);
        }
        @ini_set('output_buffering', 0);
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);
        for ($i = 0; $i < ob_get_level(); $i++) {
            ob_end_flush();
        }
        ob_implicit_flush(1);

        echo '<body style="background:#1d2327; color:#00ff7f; font-family:monospace; margin:0;">';
        echo '🚀 Starting Process...<br/>';
        flush();

        // Define a custom logger for this session
        $logger = function ($msg) {
            echo $msg . "<br/>";
            echo "<script>window.scrollTo(0,document.body.scrollHeight);</script>";
            flush();
        };

        $logger("🔍 Fetching Data for ID: $ipo_id...");
        sleep(1); // Visual Pause

        // Execute Generation with direct output hooks? 
        // We need to modify Content Generator to accept a 'logger' callback or we just catch logs?
        // Simpler: We just run it and hope for the best, or better, we instantiate it.

        try {
            $result = IPO_AI_Content_Generator::generate_post($ipo_id, true, $type, $logger);

            if (empty($result) || is_wp_error($result)) {
                throw new Exception(is_wp_error($result) ? $result->get_error_message() : "Generation failed (No return data).");
            }

            $logger("✅ <b>SUCCESS! Post Generated.</b>");
        } catch (Exception $e) {
            $logger("❌ ERROR: " . $e->getMessage());
        }

        echo '</body>';
        exit;
    }
}
