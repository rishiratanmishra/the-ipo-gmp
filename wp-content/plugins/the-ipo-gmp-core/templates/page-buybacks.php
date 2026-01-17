<?php
/**
 * Template Name: Stock Buyback Tracker
 * Description: Dedicated page for Buybacks with Search, Filters, and Pagination.
 */

global $wpdb;
$t_buybacks = $wpdb->prefix . 'buybacks';

// 1. Parameters
$paged = get_query_var('paged') ? get_query_var('paged') : (get_query_var('page') ? get_query_var('page') : (isset($_GET['paged']) ? intval($_GET['paged']) : 1));
$paged = max(1, $paged);
$limit = 20;
$offset = ($paged - 1) * $limit;

$search = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';
$status_param = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : null;

if ($status_param === null) {
    // Default to 'open' if no search, otherwise All
    $status = !empty($search) ? '' : 'open';
} elseif ($status_param === 'all') {
    $status = '';
} else {
    $status = $status_param;
}

// 2. Query Construction
$where_clauses = ["1=1"];

// Search Filter
if (!empty($search)) {
    $where_clauses[] = $wpdb->prepare("company LIKE %s", '%' . $wpdb->esc_like($search) . '%');
}

// Status Filter (DB 'type' column seems to hold status like 'Open' or 'Closed')
if (!empty($status)) {
    // Map status nicely if needed, or direct match
    // DB has 'Open', 'Closed'. Filter 'open', 'closed'
    $where_clauses[] = $wpdb->prepare("type = %s", ucfirst($status));
}

$where_sql = implode(' AND ', $where_clauses);

// Count Total
$total_items = $wpdb->get_var("SELECT COUNT(*) FROM $t_buybacks WHERE $where_sql");
$total_pages = ceil($total_items / $limit);

// Fetch Items
$buybacks = $wpdb->get_results("
    SELECT * FROM $t_buybacks 
    WHERE $where_sql 
    ORDER BY id DESC 
    LIMIT $limit OFFSET $offset
");

// 3. Helper for URL
$base_url = get_permalink();

function tigc_buyback_filter_url($status_val, $base_url)
{
    $params = $_GET;
    // Set status
    if ($status_val)
        $params['status'] = $status_val;
    else
        unset($params['status']);

    // Reset page always
    unset($params['paged']);

    return $base_url . '?' . http_build_query($params);
}
get_header();
?>

<!-- Glow Effects -->
<div
    class="fixed top-0 left-0 w-full h-[500px] bg-gradient-to-b from-purple-900/10 to-transparent pointer-events-none z-0">
</div>

<main class="relative z-10 max-w-[1280px] mx-auto px-4 md:px-10 py-6">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 mb-6 text-sm font-medium text-slate-500">
        <span class="material-symbols-outlined text-sm">home</span>
        <a href="<?php echo home_url('/'); ?>" class="hover:text-primary transition-colors">Homepage</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <span class="text-slate-200">Buyback Intelligence</span>
    </nav>

    <!-- Header & Controls -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8">
        <div>
            <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight mb-2">Buyback <span
                    class="text-purple-500">Scanner</span></h1>
            <p class="text-slate-400 text-sm font-medium">Don't sell cheap. Track <?php echo $total_items; ?> live
                Tender Offers & Open Markets.</p>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto">
            <!-- Status Filters -->
            <div class="flex p-1 bg-slate-900 border border-border-navy rounded-lg w-max">
                <?php
                $tabs = [
                    'all' => 'All',
                    'open' => 'Open',
                    'closed' => 'Closed'
                ];
                $display_status = ($status === '') ? 'all' : $status;
                foreach ($tabs as $k => $v):
                    $active = (strtolower($display_status) === $k) ? 'bg-[#0B1220] text-white shadow-sm font-bold border border-white/5' : 'text-slate-500 hover:text-white font-medium';
                    ?>
                    <a href="<?php echo tigc_buyback_filter_url($k, $base_url); ?>"
                        class="px-4 py-2 text-xs rounded-md transition-all <?php echo $active; ?>">
                        <?php echo $v; ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Search -->
            <form action="<?php echo esc_url($base_url); ?>" method="GET" class="relative group">
                <span
                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-purple-500 transition-colors">search</span>
                <input type="text" name="q" value="<?php echo esc_attr($search); ?>"
                    placeholder="Search all buybacks..."
                    class="bg-slate-900 border border-border-navy text-white text-sm rounded-lg pl-10 pr-4 py-2.5 w-full sm:w-64 focus:ring-1 focus:ring-purple-500 focus:border-purple-500 placeholder-slate-600 transition-all">
                <?php // Status filter removed to allow global search ?>
            </form>
        </div>
    </div>

    <!-- Active Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
        <?php if ($buybacks):
            foreach ($buybacks as $bb):
                $offer_price = (float) preg_replace('/[^0-9.]/', '', $bb->price);
                $mkt_price = (float) preg_replace('/[^0-9.]/', '', $bb->market_price);
                $premium = 0;
                if ($mkt_price > 0 && $offer_price > 0) {
                    $premium = round((($offer_price - $mkt_price) / $mkt_price) * 100, 1);
                }
                ?>
                <div
                    class="bg-[#0B1220] border border-border-navy rounded-2xl p-6 hover:border-purple-500/50 transition-all group relative overflow-hidden flex flex-col h-full">
                    <!-- Progress Bar Background (Subtle) -->
                    <div class="absolute bottom-0 left-0 h-1 bg-purple-500/20 w-full">
                        <div class="h-full bg-purple-500 shadow-[0_0_10px_#A855F7]"
                            style="width: <?php echo min(100, $premium * 2); ?>%"></div>
                    </div>

                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3
                                class="text-white font-bold text-lg group-hover:text-purple-400 transition-colors mb-2 line-clamp-1">
                                <?php echo esc_html($bb->company); ?>
                            </h3>
                            <span
                                class="text-[10px] font-bold text-purple-400 bg-purple-500/10 px-2 py-0.5 rounded border border-purple-500/20 uppercase tracking-wider">
                                <?php echo esc_html($bb->type); // Using type column which is essentially status ?>
                            </span>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">Premium</p>
                            <p class="text-xl font-black <?php echo $premium > 10 ? 'text-neon-emerald' : 'text-white'; ?>">
                                <?php echo $premium > 0 ? '+' . $premium . '%' : '--'; ?>
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-800/50 mt-auto">
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">Buyback Price</p>
                            <p class="text-white font-bold">₹<?php echo esc_html($bb->price); ?></p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">Market Price</p>
                            <p class="text-slate-300 font-semibold text-sm">₹<?php echo esc_html($bb->market_price ?: '-'); ?>
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-between">
                        <div class="text-[11px] text-slate-400">
                            <span class="font-bold text-slate-500">Size:</span> <?php echo esc_html($bb->issue_size); ?>
                        </div>
                        <!-- 
                <button class="text-xs font-bold text-purple-400 flex items-center gap-1 group-hover:gap-2 transition-all">
                    Details <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </button> 
                -->
                    </div>
                </div>
            <?php endforeach; else: ?>
            <div class="col-span-full py-20 text-center bg-[#0B1220] rounded-2xl border border-dashed border-border-navy">
                <span class="material-symbols-outlined text-4xl text-slate-700 mb-4">account_balance</span>
                <h4 class="text-white font-bold mb-1">No Corporate Action</h4>
                <p class="text-slate-500 font-medium text-sm">Markets are quiet on the buyback front.</p>
                <?php if ($status || $search): ?>
                    <a href="<?php echo esc_url($base_url); ?>"
                        class="mt-4 text-xs font-bold text-purple-500 hover:text-purple-400 transition-colors">Clear All
                        Filters</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="mt-8 flex justify-center gap-2">
            <?php
            // Helper to build page URL
            function tigc_page_url($base, $p)
            {
                $params = $_GET;
                $params['paged'] = $p;
                return $base . '?' . http_build_query($params);
            }

            // Prev Link
            if ($paged > 1) {
                echo '<a href="' . tigc_page_url($base_url, $paged - 1) . '" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-700 text-slate-400 hover:bg-slate-800 transition-colors"><span class="material-symbols-outlined text-sm">chevron_left</span></a>';
            }

            // Page Numbers
            for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == $paged) {
                    echo '<span class="w-8 h-8 flex items-center justify-center rounded-lg bg-purple-600 text-white font-bold text-xs shadow-lg shadow-purple-500/30">' . $i . '</span>';
                } elseif ($i <= 3 || $i == $total_pages || abs($paged - $i) <= 1) {
                    echo '<a href="' . tigc_page_url($base_url, $i) . '" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-700 text-slate-400 hover:bg-slate-800 transition-colors text-xs font-medium">' . $i . '</a>';
                } elseif ($i == 4 && $paged > 5) {
                    echo '<span class="text-slate-600 px-1 pt-2">...</span>';
                }
            }

            // Next Link
            if ($paged < $total_pages) {
                echo '<a href="' . tigc_page_url($base_url, $paged + 1) . '" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-700 text-slate-400 hover:bg-slate-800 transition-colors"><span class="material-symbols-outlined text-sm">chevron_right</span></a>';
            }
            ?>
        </div>
    <?php endif; ?>

</main>
<?php include TIGC_PATH . 'partials/footer-premium.php'; ?>
</body>

</html>