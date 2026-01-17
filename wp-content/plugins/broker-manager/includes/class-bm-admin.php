<?php
if (!defined('ABSPATH'))
    exit;

class BM_Admin
{
    public function __construct()
    {
        add_filter('manage_broker_posts_columns', [$this, 'admin_columns']);
        add_action('manage_broker_posts_custom_column', [$this, 'column_content'], 10, 2);
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_post_bm_clear_cache', [$this, 'manual_cache_clear']);
    }

    public function add_settings_page()
    {
        add_submenu_page(
            'edit.php?post_type=broker',
            'Broker Settings',
            'Settings & API',
            'manage_options',
            'bm-settings',
            [$this, 'render_settings_page']
        );
    }

    public function manual_cache_clear()
    {
        if (!current_user_can('manage_options'))
            return;
        delete_transient('bm_api_brokers_list');
        wp_redirect(admin_url("edit.php?post_type=broker&page=bm-settings&updated=1"));
        exit;
    }

    public function render_settings_page()
    {
        // Init Key if missing
        if (!get_option('bm_api_key')) {
            update_option('bm_api_key', 'zolaha_bm_' . wp_generate_password(16, false));
        }
        $api_key = get_option('bm_api_key');

        ?>
        <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet" />
        <script>
            function bm_copy_key() {
                var copyText = document.getElementById("bm_api_key_input");
                navigator.clipboard.writeText(copyText.innerText);
                alert("API Key Copied!");
            }
        </script>

        <div class="wrap" style="font-family:'Inter', sans-serif;">
            <div class="max-w-4xl mx-auto mt-8">
                <div class="bg-white p-8 rounded-xl shadow-sm border border-slate-200">
                    <div class="flex items-center justify-between mb-6">
                        <h1 class="text-2xl font-bold text-slate-900 m-0">Broker Manager API</h1>
                        <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST">
                            <input type="hidden" name="action" value="bm_clear_cache">
                            <button type="submit"
                                class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                                <span class="material-icons-round text-sm">cached</span> Clear Cache
                            </button>
                        </form>
                    </div>

                    <div class="bg-slate-50 p-6 rounded-lg border border-slate-200 mb-8">
                        <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Your API Key</h3>
                        <div class="flex items-center gap-3">
                            <code
                                class="text-lg font-mono text-emerald-600 bg-white px-4 py-2 rounded border border-slate-200 flex-1"
                                id="bm_api_key_input"><?php echo esc_html($api_key); ?></code>
                            <button onclick="bm_copy_key()"
                                class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                                <span class="material-icons-round text-sm">content_copy</span> Copy
                            </button>
                        </div>
                        <p class="text-xs text-slate-400 mt-2">Pass this key in the header <code
                                class="bg-slate-100 text-slate-600 px-1 rounded">X-Api-Key</code> for all requests.</p>
                    </div>

                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Endpoints</h3>
                    <div class="space-y-4">
                        <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-0.5 rounded">GET</span>
                                <code class="text-sm font-mono text-slate-700">/wp-json/zolaha/v1/brokers</code>
                            </div>
                            <p class="text-sm text-slate-600">Fetches list of all active brokers with ratings, pros/cons, and
                                affiliate links.</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <?php
    }

    public function admin_columns($columns)
    {
        $new = [];
        $new['cb'] = $columns['cb'];
        $new['thumbnail'] = 'Logo';
        $new['title'] = 'Broker Name';
        $new['taxonomy-broker_category'] = 'Category';
        $new['bm_rating'] = 'Rating';
        $new['bm_fees'] = 'Fees';
        $new['bm_clicks'] = 'Clicks';
        $new['bm_status'] = 'Status';
        return $new;
    }

    public function column_content($column, $post_id)
    {
        global $wpdb;
        // Simple caching to avoid re-querying for each column of the same post
        static $broker_cache = [];

        if (!isset($broker_cache[$post_id])) {
            $broker_cache[$post_id] = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . BM_TABLE . " WHERE post_id = %d", $post_id));
        }

        $broker = $broker_cache[$post_id];

        // Defaults if row missing (shouldn't happen)
        $logo_url = $broker->logo_url ?? '';
        $rating = $broker->rating ?? 0;
        $fees = $broker->fees ?? '';
        $clicks = $broker->click_count ?? 0;
        $status = $broker->status ?? 'active';
        $featured = $broker->is_featured ?? 0;

        switch ($column) {
            case 'thumbnail':
                if (has_post_thumbnail($post_id)) {
                    echo get_the_post_thumbnail($post_id, 'thumbnail', ["style" => "width:50px;height:50px;object-fit:contain;border-radius:4px"]);
                } else {
                    if ($logo_url) {
                        echo '<img src="' . esc_url($logo_url) . '" style="width:50px;height:50px;object-fit:contain;border-radius:4px">';
                    } else {
                        echo '<span style="color:#ccc">&mdash;</span>';
                    }
                }
                break;

            case 'bm_rating':
                echo $rating ? '<strong>' . esc_html($rating) . '</strong> / 5' : '-';
                break;

            case 'bm_fees':
                echo $fees ? esc_html($fees) : '-';
                break;

            case 'bm_clicks':
                echo intval($clicks);
                break;

            case 'bm_status':
                if ($status == 'active') {
                    echo '<span style="display:inline-block;padding:2px 6px;background:#c8f7c5;color:#2d8a34;border-radius:3px;font-size:11px;font-weight:bold;">ACTIVE</span>';
                } else {
                    echo '<span style="display:inline-block;padding:2px 6px;background:#ffd3d3;color:#b30000;border-radius:3px;font-size:11px;font-weight:bold;">INACTIVE</span>';
                }

                if ($featured) {
                    echo '<div style="margin-top:4px;"><span style="color:#d63384;font-size:10px;">★ Featured</span></div>';
                }
                break;
        }
    }
}

new BM_Admin();
