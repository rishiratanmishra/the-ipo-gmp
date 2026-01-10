<?php
/**
 * IPOD Admin Class
 *
 * Manages the backend Dashboard for IPO Details.
 *
 * @package IPO_Master_Details
 */

if (!defined('ABSPATH')) exit;

class IPOD_Admin {

    public function __construct() {
        add_action("admin_menu", [$this, "menu"]);
        add_action("admin_enqueue_scripts", [$this, "enqueue_assets"]);
        add_action("admin_post_ipod_manual_fetch", [$this, "manual_fetch"]);
    }

    public function enqueue_assets($hook) {
        if ($hook !== 'toplevel_page_ipo-details') {
            return;
        }
        // Reusing the same premium style as IPO Master Admin for consistency
        wp_enqueue_style('ipod-admin-style', IPOD_URL . 'assets/css/admin-style.css', [], '1.0.0');
    }

    public function menu() {
        add_menu_page(
            "IPO Details",
            "IPO Details",
            "manage_options",
            "ipo-details",
            [$this, "render_dashboard"],
            "dashicons-media-spreadsheet",
            27
        );
    }

    public function manual_fetch() {
        if (!current_user_can('manage_options')) return;
        
        IPOD_Fetcher::fetch_all();
        
        wp_redirect(admin_url("admin.php?page=ipo-details&updated=1"));
        exit;
    }

    public function render_dashboard() {
        global $wpdb;

        // Stats
        $total_master = $wpdb->get_var("SELECT COUNT(*) FROM " . IPOM_TABLE);
        $total_details = $wpdb->get_var("SELECT COUNT(*) FROM " . IPOD_TABLE);
        $coverage = $total_master > 0 ? round(($total_details / $total_master) * 100, 1) : 0;

        // Recent limit
        $limit = 20;
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($paged - 1) * $limit;

        // Query joined data
        $sql = "SELECT d.*, m.name, m.slug, m.status as master_status FROM " . IPOD_TABLE . " d 
                JOIN " . IPOM_TABLE . " m ON d.ipo_id = m.id 
                ORDER BY d.updated_at DESC LIMIT $limit OFFSET $offset";
        
        $rows = $wpdb->get_results($sql);
        $total_rows = $wpdb->get_var("SELECT COUNT(*) FROM " . IPOD_TABLE);
        $total_pages = ceil($total_rows / $limit);

        // API Key (Shared with Master)
        $api_key = get_option('ipom_api_key', 'Not Generated');

        ?>
        <!-- Tailwind & Fonts -->
        <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet"/>
        <script>
            tailwind.config = {
                darkMode: "class", 
                theme: {
                    extend: {
                        colors: {
                            primary: "#39E079", 
                            "background-light": "#f6f8f7", 
                            "background-dark": "#122017", 
                            "surface-dark": "#0C1427", 
                            "border-dark": "#1E293B", 
                            "neon-emerald": "#10B981"
                        }, 
                        fontFamily: {
                            display: "Inter", 
                            sans: ["Inter", "sans-serif"]
                        }
                    }
                }
            };

            function ipod_copy_key() {
                var copyText = document.getElementById("ipod_api_key_input");
                navigator.clipboard.writeText(copyText.innerText);
                alert("API Key Copied!");
            }
        </script>
        <style>
            .ipod-dashboard-wrapper { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
            .ipod-dashboard-wrapper .glass-card { background: rgba(12, 20, 39, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.05); }
            .ipod-dashboard-wrapper .glow-blue { box-shadow: 0 0 15px rgba(59, 130, 246, 0.3); }
            #wpcontent { padding-left: 0; }
            .ipod-dashboard-wrapper a { text-decoration: none; }
        </style>

        <div class="ipod-dashboard-wrapper bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 min-h-screen">
            <div class="max-w-[1440px] mx-auto p-6 space-y-6">
                
                <!-- Header -->
                <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-border-dark shadow-sm">
                    <div class="space-y-1">
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">IPO Details Manager</h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Deep analytics and JSON data storage for IPOs</p>
                    </div>
                    <div class="flex items-center gap-3 w-full md:w-auto">
                        <!-- API Key Box -->
                        <div class="flex-1 md:flex-none bg-slate-100 dark:bg-slate-900/50 px-4 py-2 rounded-lg border border-slate-200 dark:border-border-dark flex items-center gap-3">
                            <span class="text-[10px] uppercase tracking-wider font-semibold text-slate-400">SHARED API KEY</span>
                            <code class="text-xs font-mono text-primary" id="ipod_api_key_input"><?php echo esc_html($api_key); ?></code>
                            <button onclick="ipod_copy_key()" class="material-icons-round text-slate-400 hover:text-white text-sm cursor-pointer">content_copy</button>
                        </div>
                        
                        <a href="<?php echo admin_url("admin-post.php?action=ipod_manual_fetch"); ?>" onclick="return confirm('Run batch fetch for pending IPOs?');" class="bg-primary hover:bg-green-600 transition-all text-white px-5 py-2.5 rounded-lg flex items-center gap-2 font-medium shadow-lg glow-blue">
                            <span class="material-icons-round text-sm">update</span>
                            Batch Fetch
                        </a>
                    </div>
                </header>

                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white dark:bg-surface-dark p-6 rounded-xl border-l-4 border-primary shadow-sm border border-slate-200 dark:border-border-dark">
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Details Stored</p>
                        <h2 class="text-4xl font-bold dark:text-white"><?php echo number_format($total_details); ?></h2>
                    </div>
                    <div class="bg-white dark:bg-surface-dark p-6 rounded-xl border-l-4 border-blue-500 shadow-sm border border-slate-200 dark:border-border-dark">
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Coverage</p>
                        <h2 class="text-4xl font-bold dark:text-white"><?php echo $coverage; ?>%</h2>
                    </div>
                    <div class="bg-white dark:bg-surface-dark p-6 rounded-xl border-l-4 border-purple-500 shadow-sm border border-slate-200 dark:border-border-dark">
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Master Records</p>
                        <h2 class="text-4xl font-bold dark:text-white"><?php echo number_format($total_master); ?></h2>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-border-dark">
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">ID</th>
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">IPO Name</th>
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</th>
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Last Fetched</th>
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Data Size</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-border-dark">
                                <?php if ($rows): foreach ($rows as $r): 
                                    $jsonSize = strlen($r->details_json) / 1024; // KB
                                    
                                    // Status Badge logic (from Master)
                                    $status_bg = 'bg-slate-100 dark:bg-slate-800';
                                    $status_text = 'text-slate-600 dark:text-slate-400';
                                    $st = strtolower($r->master_status);
                                    if(strpos($st, 'open') !== false) { $status_bg = 'bg-neon-emerald/10'; $status_text = 'text-neon-emerald'; }
                                    if(strpos($st, 'closed') !== false) { $status_bg = 'bg-red-100 dark:bg-red-900/20'; $status_text = 'text-red-600 dark:text-red-400'; }
                                    if(strpos($st, 'upcoming') !== false) { $status_bg = 'bg-blue-100 dark:bg-blue-900/20'; $status_text = 'text-blue-600 dark:text-blue-400'; }
                                ?>
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/30 transition-colors">
                                    <td class="px-6 py-4 text-xs font-mono text-slate-400">#<?php echo $r->ipo_id; ?></td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-sm dark:text-white"><?php echo esc_html($r->name); ?></div>
                                        <div class="text-[10px] text-slate-400 mt-0.5"><?php echo esc_html($r->slug); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full <?php echo $status_bg . ' ' . $status_text; ?> w-fit uppercase"><?php echo esc_html($r->master_status); ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium dark:text-slate-200"><?php echo human_time_diff(strtotime($r->fetched_at), current_time('timestamp')); ?> ago</div>
                                        <div class="text-[10px] text-slate-500"><?php echo $r->fetched_at; ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-mono text-xs text-slate-500 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded border border-slate-200 dark:border-border-dark"><?php echo round($jsonSize, 2); ?> KB</span>
                                    </td>
                                </tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="5" class="px-6 py-4 text-center text-slate-500">No details fetched yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-border-dark flex items-center justify-between">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Page <?php echo $paged; ?> of <?php echo $total_pages; ?></p>
                        <div class="flex items-center gap-1">
                            <?php 
                            $base_url = add_query_arg([]);
                            if ($paged > 1) echo '<a href="'.esc_url(add_query_arg('paged', $paged-1, $base_url)).'" class="p-1 rounded hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-400"><span class="material-icons-round text-lg">chevron_left</span></a>';
                            if ($paged < $total_pages) echo '<a href="'.esc_url(add_query_arg('paged', $paged+1, $base_url)).'" class="p-1 rounded hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-400"><span class="material-icons-round text-lg">chevron_right</span></a>';
                            ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
}
