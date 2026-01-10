<?php
/**
 * IPOM Admin Class
 *
 * Manages the backend Dashboard UI.
 *
 * @package IPO_Master_Admin
 */

if (!defined('ABSPATH')) exit;

class IPOM_Admin {

    public function __construct() {
        add_action("admin_menu", [$this, "menu"]);
        add_action("admin_enqueue_scripts", [$this, "enqueue_assets"]);
        add_action("admin_post_ipom_manual_fetch", [$this, "manual_fetch"]);
    }

    public function enqueue_assets($hook) {
        if ($hook !== 'toplevel_page_ipo-master' && $hook !== 'ipo-master_page_ipo-master-table') {
            return;
        }
        wp_enqueue_style('ipom-admin-style', IPOM_URL . 'assets/css/admin-style.css', [], '1.0.0');
    }

    public function menu() {
        add_menu_page(
            "IPO Manager",
            "IPO Manager",
            "manage_options",
            "ipo-master",
            [$this, "render_dashboard"],
            "dashicons-chart-line",
            26
        );
    }

    public function manual_fetch() {
        if (!current_user_can('manage_options')) return;
        
        IPOM_Fetcher::fetch_and_store();
        
        wp_redirect(admin_url("admin.php?page=ipo-master&updated=1"));
        exit;
    }

    public function render_dashboard() {
        global $wpdb;
        $table_name = defined('IPOM_TABLE') ? IPOM_TABLE : $wpdb->prefix . 'ipomaster';

        // --- Stats ---
        $total = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $total_sme = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE is_sme=1");
        $total_main = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE is_sme=0");

        // --- Filters ---
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $filter_type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';
        $filter_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $limit = 20;
        $offset = ($paged - 1) * $limit;

        $where = "WHERE 1=1";
        $args = [];

        if ($search) {
            $where .= " AND name LIKE %s";
            $args[] = '%' . $wpdb->esc_like($search) . '%';
        }
        if ($filter_type == 'sme') $where .= " AND is_sme=1";
        if ($filter_type == 'main') $where .= " AND is_sme=0";
        if ($filter_status) {
            $where .= " AND status LIKE %s";
            $args[] = '%' . $wpdb->esc_like($filter_status) . '%';
        }

        // Query
        if (!empty($args)) {
            $total_rows = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name $where", $args));
            $sql = $wpdb->prepare("SELECT * FROM $table_name $where ORDER BY id DESC LIMIT %d OFFSET %d", array_merge($args, [$limit, $offset]));
        } else {
            $total_rows = $wpdb->get_var("SELECT COUNT(*) FROM $table_name $where");
            $sql = $wpdb->prepare("SELECT * FROM $table_name $where ORDER BY id DESC LIMIT %d OFFSET %d", $limit, $offset);
        }
        $table_data = $wpdb->get_results($sql);
        $total_pages = ceil($total_rows / $limit);
        $last_fetch = get_option("ipom_last_fetch", "Never");
        $api_key = get_option('ipom_api_key', 'Not Generated');

        // --- Render: New Tailwind UI ---
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
                        }, 
                        borderRadius: {
                            DEFAULT: "0.25rem", 
                            lg: "0.5rem", 
                            xl: "0.75rem", 
                            full: "9999px"
                        }
                    }
                }
            };

            function ipom_copy_key() {
                var copyText = document.getElementById("ipom_api_key_input");
                navigator.clipboard.writeText(copyText.innerText);
                alert("API Key Copied!");
            }
        </script>
        <style>
            .ipom-dashboard-wrapper { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased;}
            .ipom-dashboard-wrapper .glass-card { background: rgba(12, 20, 39, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.05); }
            .ipom-dashboard-wrapper .glow-blue { box-shadow: 0 0 15px rgba(59, 130, 246, 0.3); }
            .ipom-dashboard-wrapper .glow-emerald { box-shadow: 0 0 10px rgba(16, 185, 129, 0.2); }
            
            /* WP Admin Reset */
            #wpcontent { padding-left: 0; }
            .ipom-dashboard-wrapper a { text-decoration: none; }
            .ipom-display-none { display: none; }
        </style>

        <div class="ipom-dashboard-wrapper bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 min-h-screen">
            <div class="max-w-[1440px] mx-auto p-6 space-y-6">
                
                <!-- Header -->
                <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-border-dark shadow-sm">
                    <div class="space-y-1">
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">IPO Manager</h1>
                        <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                            <span class="flex items-center gap-1.5">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-neon-emerald opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-neon-emerald"></span>
                                </span>
                                Live Scraper Status
                            </span>
                            <span class="opacity-30">|</span>
                            <span class="flex items-center gap-1">
                                <span class="material-icons-round text-[14px]">schedule</span>
                                Last Updated: <?php echo esc_html($last_fetch); ?>
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 w-full md:w-auto">
                        <div class="flex-1 md:flex-none bg-slate-100 dark:bg-slate-900/50 px-4 py-2 rounded-lg border border-slate-200 dark:border-border-dark flex items-center gap-3">
                            <span class="text-[10px] uppercase tracking-wider font-semibold text-slate-400">API KEY</span>
                            <code class="text-xs font-mono text-primary" id="ipom_api_key_input"><?php echo esc_html($api_key); ?></code>
                            <button onclick="ipom_copy_key()" class="material-icons-round text-slate-400 hover:text-white text-sm cursor-pointer">content_copy</button>
                        </div>
                        <a href="<?php echo admin_url("admin-post.php?action=ipom_manual_fetch"); ?>" class="bg-primary hover:bg-green-600 transition-all text-white px-5 py-2.5 rounded-lg flex items-center gap-2 font-medium shadow-lg glow-blue">
                            <span class="material-icons-round text-sm">refresh</span>
                            Fetch Now
                        </a>
                    </div>
                </header>

                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white dark:bg-surface-dark p-6 rounded-xl border-l-4 border-primary shadow-sm border border-slate-200 dark:border-border-dark">
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Total IPOs</p>
                        <h2 class="text-4xl font-bold dark:text-white"><?php echo number_format($total); ?></h2>
                    </div>
                    <div class="bg-white dark:bg-surface-dark p-6 rounded-xl border-l-4 border-emerald-500 shadow-sm border border-slate-200 dark:border-border-dark">
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">SME IPOs</p>
                        <h2 class="text-4xl font-bold dark:text-white"><?php echo number_format($total_sme); ?></h2>
                    </div>
                    <div class="bg-white dark:bg-surface-dark p-6 rounded-xl border-l-4 border-orange-500 shadow-sm border border-slate-200 dark:border-border-dark">
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Mainboard IPOs</p>
                        <h2 class="text-4xl font-bold dark:text-white"><?php echo number_format($total_main); ?></h2>
                    </div>
                </div>

                <!-- Filters -->
                <form method="GET" class="bg-white dark:bg-surface-dark p-4 rounded-xl border border-slate-200 dark:border-border-dark flex flex-wrap items-center gap-4">
                    <input type="hidden" name="page" value="ipo-master">
                    <div class="relative flex-1 min-w-[200px]">
                        <input name="s" value="<?php echo esc_attr($search); ?>" class="w-full bg-slate-50 dark:bg-background-dark border-slate-200 dark:border-border-dark rounded-lg px-4 py-2 text-sm focus:ring-primary focus:border-primary" placeholder="Search IPOs..." type="text"/>
                    </div>
                    <select name="type" class="bg-slate-50 dark:bg-background-dark border-slate-200 dark:border-border-dark rounded-lg px-4 py-2 text-sm text-slate-600 dark:text-slate-300 focus:ring-primary">
                        <option value="">All Types</option>
                        <option value="sme" <?php selected($filter_type, 'sme'); ?>>SME</option>
                        <option value="main" <?php selected($filter_type, 'main'); ?>>Mainboard</option>
                    </select>
                    <select name="status" class="bg-slate-50 dark:bg-background-dark border-slate-200 dark:border-border-dark rounded-lg px-4 py-2 text-sm text-slate-600 dark:text-slate-300 focus:ring-primary">
                        <option value="">All Statuses</option>
                        <option value="Upcoming" <?php selected($filter_status, 'Upcoming'); ?>>Upcoming</option>
                        <option value="Open" <?php selected($filter_status, 'Open'); ?>>Open</option>
                        <option value="Closed" <?php selected($filter_status, 'Closed'); ?>>Closed</option>
                    </select>
                    <button type="submit" class="bg-slate-900 dark:bg-slate-800 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 dark:hover:bg-slate-700 transition-colors">
                        Apply Filters
                    </button>
                    <?php if ($search || $filter_type || $filter_status): ?>
                        <a href="<?php echo admin_url('admin.php?page=ipo-master'); ?>" class="text-sm text-red-500 font-medium ml-2">Reset</a>
                    <?php endif; ?>
                </form>

                <!-- Table -->
                <div class="bg-white dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-border-dark overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-border-dark">
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">ID</th>
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">IPO Name</th>
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Dates</th>
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Price</th>
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status / Premium</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-border-dark">
                                <?php if ($table_data): foreach ($table_data as $r): 
                                    $logo = $r->icon_url ?: 'https://via.placeholder.com/40?text='.substr($r->name,0,1);
                                    $gmp_val = floatval(preg_replace('/[^0-9.]/', '', $r->premium));
                                    $gmp_color = ($gmp_val > 0) ? 'text-neon-emerald' : 'text-slate-400';
                                    
                                    // Status Logic for Colors
                                    $status_bg = 'bg-slate-100 dark:bg-slate-800';
                                    $status_text = 'text-slate-600 dark:text-slate-400';
                                    $st = strtolower($r->status);
                                    if(strpos($st, 'open') !== false) { $status_bg = 'bg-neon-emerald/10'; $status_text = 'text-neon-emerald'; }
                                    if(strpos($st, 'closed') !== false) { $status_bg = 'bg-red-100 dark:bg-red-900/20'; $status_text = 'text-red-600 dark:text-red-400'; }
                                    if(strpos($st, 'upcoming') !== false) { $status_bg = 'bg-blue-100 dark:bg-blue-900/20'; $status_text = 'text-blue-600 dark:text-blue-400'; }
                                ?>
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/30 transition-colors">
                                    <td class="px-6 py-4 text-xs font-mono text-slate-400">#<?php echo $r->id; ?></td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center p-2 border border-slate-200 dark:border-border-dark">
                                                <img alt="Logo" class="w-full h-full object-contain rounded" src="<?php echo esc_url($logo); ?>"/>
                                            </div>
                                            <div>
                                                <div class="font-semibold text-sm dark:text-white"><?php echo esc_html($r->name); ?></div>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-bold tracking-tighter uppercase"><?php echo $r->is_sme ? 'SME' : 'MAIN'; ?></span>
                                                    <span class="text-[10px] text-slate-400"><?php echo esc_html($r->slug); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-xs font-medium dark:text-slate-200"><?php echo esc_html($r->open_date); ?></div>
                                        <div class="text-[10px] text-slate-500">to <?php echo esc_html($r->close_date); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-semibold dark:text-white"><?php echo esc_html($r->price_band); ?></div>
                                        <div class="text-[10px] text-slate-500 uppercase">Lot: <?php echo esc_html($r->lot_size); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1.5">
                                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-full <?php echo $status_bg . ' ' . $status_text; ?> w-fit uppercase"><?php echo esc_html($r->status); ?></span>
                                            <span class="text-xs font-bold <?php echo $gmp_color; ?> flex items-center gap-1">
                                                GMP: <?php echo esc_html($r->premium); ?>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="5" class="px-6 py-4 text-center text-slate-500">No IPOs found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination (Simplified) -->
                    <?php if ($total_pages > 1): ?>
                    <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-border-dark flex items-center justify-between">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Page <?php echo $paged; ?> of <?php echo $total_pages; ?></p>
                        <div class="flex items-center gap-1">
                            <?php 
                            $base_url = add_query_arg(['s' => $search, 'type' => $filter_type, 'status' => $filter_status]);
                            if ($paged > 1) echo '<a href="'.esc_url(add_query_arg('paged', $paged-1, $base_url)).'" class="p-1 rounded hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-400"><span class="material-icons-round text-lg">chevron_left</span></a>';
                            if ($paged < $total_pages) echo '<a href="'.esc_url(add_query_arg('paged', $paged+1, $base_url)).'" class="p-1 rounded hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-400"><span class="material-icons-round text-lg">chevron_right</span></a>';
                            ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Footer Widgets -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-12">
                    <div class="bg-blue-50/50 dark:bg-primary/5 border border-blue-100 dark:border-primary/20 p-4 rounded-xl flex items-start gap-3">
                        <span class="material-icons-round text-primary mt-0.5">info</span>
                        <div>
                            <h4 class="text-sm font-semibold text-primary">System Notification</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mt-1">Automated scrapers are currently operational.</p>
                        </div>
                    </div>
                </div>

            </div>
            
            <!-- Dark Mode Toggle -->

        </div>
        <?php
    }
}
