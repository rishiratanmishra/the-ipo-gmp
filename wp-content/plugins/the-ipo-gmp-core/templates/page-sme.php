<?php
/**
 * Template Name: SME IPOs Dedicated List
 * Description: Dedicated page for SME IPOs with Search, Filters, and Pagination.
 */

global $wpdb;
$t_master = $wpdb->prefix . 'ipomaster';

// 1. Parameters
$paged = get_query_var('paged') ? get_query_var('paged') : (get_query_var('page') ? get_query_var('page') : (isset($_GET['paged']) ? intval($_GET['paged']) : 1));
$paged = max(1, $paged);
$limit = 20;
$offset = ($paged - 1) * $limit;

$search = isset($_GET['q']) ? trim(sanitize_text_field($_GET['q'])) : '';
// Status Param Logic: Default to 'open' if not set. explicit 'all' means show all.
$status_param = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : null;

if ($status_param === null) {
    // If searching, default to ALL (empty), otherwise default to OPEN
    $status = !empty($search) ? '' : 'open';
} elseif ($status_param === 'all') {
    $status = '';     // Show All
} else {
    $status = $status_param;
}

// 2. Query Construction
$where_clauses = ["is_sme = 1"];

// Search Filter
if (!empty($search)) {
    $where_clauses[] = $wpdb->prepare("name LIKE %s", '%' . $search . '%');
}

// Status Filter
// Status Filter
if (!empty($status)) {
    $s = strtolower($status);
    $today = current_time('Y-m-d'); // Use WP local time

    if ($s === 'pre-listing') {
        // Match Homepage Logic: Allotment OR (Closed + Future Listing)
        $where_clauses[] = "(
            status IN ('allotment') 
            OR status LIKE '%allotment%'
            OR (status IN ('close', 'closed') AND STR_TO_DATE(listing_date, '%b %d, %Y') >= '$today')
        )";
    } elseif ($s === 'listed') {
        // Explicitly Listed OR (Closed/Allotment AND Past Listing)
        $where_clauses[] = "(
            status = 'listed' 
            OR (
                (status IN ('close', 'closed', 'allotment') OR status LIKE '%allotment%') 
                AND STR_TO_DATE(listing_date, '%b %d, %Y') < '$today'
            )
        )";
    } elseif (in_array($s, ['open', 'upcoming'])) {
        $where_clauses[] = $wpdb->prepare("status LIKE %s", $s);
    }
}

$where_sql = implode(' AND ', $where_clauses);

// Count Total for Pagination
$total_items = $wpdb->get_var("SELECT COUNT(*) FROM $t_master WHERE $where_sql");
$total_pages = ceil($total_items / $limit);

// Fetch Items
// Dynamic Sort based on context
$order_by = "STR_TO_DATE(open_date, '%b %d, %Y') DESC"; // Default (Active/All) - Newest Opened first

if (strtolower($status) === 'upcoming') {
    $order_by = "STR_TO_DATE(open_date, '%b %d, %Y') ASC"; // Soonest opening first
} elseif (strtolower($status) === 'pre-listing') {
    $order_by = "STR_TO_DATE(listing_date, '%b %d, %Y') ASC"; // Soonest listing first
} elseif (in_array(strtolower($status), ['closed', 'listed'])) {
    $order_by = "STR_TO_DATE(listing_date, '%b %d, %Y') DESC"; // Recently listed first
}

$sme = $wpdb->get_results("
    SELECT * FROM $t_master 
    WHERE $where_sql 
    ORDER BY $order_by 
    LIMIT $limit OFFSET $offset
");

// 3. Helper for URL generation
function tigc_get_filter_url_sme($status_val)
{
    $params = $_GET;
    // Set status
    if ($status_val)
        $params['status'] = $status_val;
    else
        unset($params['status']);

    // Reset page on filter change
    unset($params['paged']);

    return '?' . http_build_query($params);
}

get_header();
?>

<main class="max-w-[1280px] mx-auto px-4 md:px-10 py-6">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 mb-6 text-sm font-medium text-slate-500">
        <span class="material-symbols-outlined text-sm">home</span>
        <a href="<?php echo home_url('/'); ?>" class="hover:text-primary transition-colors">Homepage</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <span class="text-slate-200">SME IPOs</span>
    </nav>

    <!-- Header & Search -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8">
        <div>
            <h1 class="text-white text-3xl font-black tracking-tight mb-2">SME <span class="text-primary">Radar</span>
            </h1>
            <p class="text-slate-400 text-sm font-medium">High Risk. High Reward. Tracking <?php echo $total_items; ?>
                SME listings.</p>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto">
            <!-- Status Filters -->
            <div class="flex p-1 bg-slate-900 border border-border-navy rounded-lg">
                <?php
                $tabs = [
                    'open' => 'Open',
                    'pre-listing' => 'Pre-Listing',
                    'upcoming' => 'Upcoming',
                    'listed' => 'Closed'
                ];

                // Determine display status for active tab
                $display_status = ($status === '') ? 'all' : $status;

                foreach ($tabs as $k => $v):
                    $active = (strtolower($display_status) === $k) ? 'bg-primary/20 text-primary border-primary/30 font-bold' : 'text-slate-500 hover:text-white font-medium';
                    ?>
                    <a href="<?php echo tigc_get_filter_url_sme($k); ?>"
                        class="px-4 py-2 text-xs rounded-md transition-all <?php echo $active; ?>">
                        <?php echo $v; ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Search Bar -->
            <form class="relative group" action="" method="GET"
                onsubmit="this.q.value = this.q.value.trim(); if(this.q.value === '') return false;">
                <span
                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-primary transition-colors">search</span>
                <input type="text" id="ipo-search-input" name="q" value="<?php echo esc_attr($search); ?>"
                    placeholder="Search SME..."
                    class="bg-slate-900 border border-slate-700 text-white text-sm rounded-lg pl-10 pr-4 py-2.5 w-full sm:w-64 focus:ring-1 focus:ring-primary focus:border-primary placeholder-slate-600">
                <?php if ($search): ?>
                    <a href="?"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white transition-colors">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Main Table -->
    <div id="ajax-results-wrapper">
        <div
            class="overflow-hidden rounded-xl border border-border-navy bg-[#0B1220] shadow-2xl relative min-h-[400px]">

            <?php if ($sme): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[600px]">
                        <thead>
                            <tr class="bg-[#0f172a] border-b border-border-navy">
                                <th class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-widest">
                                    Company
                                </th>
                                <th class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-widest">Price
                                    Band
                                </th>
                                <th
                                    class="px-6 py-4 text-[11px] font-black text-emerald-500 uppercase tracking-widest bg-emerald-500/5">
                                    GMP</th>
                                <th
                                    class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-widest hidden md:table-cell">
                                    Dates</th>
                                <th
                                    class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-widest text-right">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-navy">
                            <?php foreach ($sme as $ipo):
                                $slug = !empty($ipo->slug) ? $ipo->slug : sanitize_title($ipo->name);
                                $details_url = home_url('/ipo-details/?slug=' . $slug);
                                $gmp_val = (float) $ipo->premium;
                                $cap_price = (float) $ipo->max_price;

                                // Handle range in price band if max_price is missing
                                if ($cap_price <= 0 && preg_match('/(\d+)(?!.*\d)/', $ipo->price_band, $m)) {
                                    $cap_price = (float) $m[1];
                                }

                                $gmp_perc = ($cap_price > 0) ? round(($gmp_val / $cap_price) * 100, 1) : 0;

                                // Dynamic Row Border for Status
                                $row_status_class = '';
                                if (strtolower($ipo->status) === 'open')
                                    $row_status_class = 'border-l-2 border-l-primary';
                                ?>
                                <tr class="data-table-row transition-all cursor-pointer group <?php echo $row_status_class; ?>"
                                    onclick="window.location.href='<?php echo esc_url($details_url); ?>'">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="w-10 h-10 rounded-lg bg-white p-1.5 flex items-center justify-center font-bold text-slate-900 overflow-hidden shadow-sm group-hover:scale-105 transition-transform shrink-0">
                                                <?php if (!empty($ipo->icon_url)): ?>
                                                    <img src="<?php echo esc_url($ipo->icon_url); ?>" alt=""
                                                        class="w-full h-full object-contain" />
                                                <?php else: ?>
                                                    <?php echo substr($ipo->name, 0, 1); ?>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <h3
                                                    class="text-sm font-bold text-white group-hover:text-primary transition-colors line-clamp-1">
                                                    <?php echo esc_html($ipo->name); ?>
                                                </h3>
                                                <p class="text-[10px] text-slate-500 font-bold tracking-wide mt-0.5">Size:
                                                    ₹<?php echo esc_html($ipo->issue_size_cr ?: '-'); ?> Cr</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="text-xs font-bold text-slate-200 block"><?php echo esc_html($ipo->price_band ?: 'TBA'); ?></span>
                                        <span class="text-[10px] text-slate-500 font-medium">Lot:
                                            <?php echo esc_html($ipo->lot_size ?: '-'); ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($gmp_val > 0): ?>
                                            <div class="flex flex-col items-start">
                                                <span class="text-xs font-black text-neon-emerald">+ ₹<?php echo $gmp_val; ?></span>
                                                <span class="text-[10px] font-bold text-slate-400">~<?php echo $gmp_perc; ?>%</span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-[10px] font-bold text-slate-600">--</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 hidden md:table-cell">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] text-slate-400 font-bold uppercase">Listing</span>
                                            <span
                                                class="text-xs font-medium text-slate-200"><?php echo $ipo->listing_date ? date('M j, Y', strtotime($ipo->listing_date)) : 'TBA'; ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <?php
                                        $st = strtolower($ipo->status);
                                        $badge_color = 'bg-slate-800 text-slate-400 border-slate-700';
                                        if ($st === 'open' || $st === 'upcoming')
                                            $badge_color = 'bg-primary/20 text-primary border-primary/30 animate-pulse';
                                        ?>
                                        <span
                                            class="inline-block px-2.5 py-0.5 text-[9px] font-black uppercase tracking-widest rounded-full border <?php echo $badge_color; ?>">
                                            <?php echo esc_html($ipo->status); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php else: ?>
                <div class="flex flex-col items-center justify-center py-20">
                    <span class="material-symbols-outlined text-4xl text-slate-700 mb-2">radar</span>
                    <h4 class="text-white font-bold mb-1">Radar Empty</h4>
                    <p class="text-slate-500 font-medium text-sm">No active SME opportunities found.</p>
                    <?php if ($status || $search): ?>
                        <a href="?" class="mt-4 text-xs font-bold text-primary hover:underline">Clear Filters</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="mt-8 flex justify-center gap-2">
                <?php
                // Prev Link
                if ($paged > 1) {
                    $prev_params = $_GET;
                    $prev_params['paged'] = $paged - 1;
                    echo '<a href="?' . http_build_query($prev_params) . '" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-700 text-slate-400 hover:bg-slate-800 transition-colors"><span class="material-symbols-outlined text-sm">chevron_left</span></a>';
                }

                // Page Numbers (Simple)
                for ($i = 1; $i <= $total_pages; $i++) {
                    if ($i == $paged) {
                        echo '<span class="w-8 h-8 flex items-center justify-center rounded-lg bg-primary text-white font-bold text-xs shadow-lg shadow-blue-500/30">' . $i . '</span>';
                    } elseif ($i <= 3 || $i == $total_pages || abs($paged - $i) <= 1) {
                        $page_params = $_GET;
                        $page_params['paged'] = $i;
                        echo '<a href="?' . http_build_query($page_params) . '" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-700 text-slate-400 hover:bg-slate-800 transition-colors text-xs font-medium">' . $i . '</a>';
                    } elseif ($i == 4 && $paged > 5) {
                        echo '<span class="text-slate-600 px-1">...</span>';
                    }
                }

                // Next Link
                if ($paged < $total_pages) {
                    $next_params = $_GET;
                    $next_params['paged'] = $paged + 1;
                    echo '<a href="?' . http_build_query($next_params) . '" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-700 text-slate-400 hover:bg-slate-800 transition-colors"><span class="material-symbols-outlined text-sm">chevron_right</span></a>';
                }
                ?>
            </div>
        <?php endif; ?>

    </div>
</main>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let searchTimeout;
        // Use ID to ensure we target the correct local search input, not header/global ones
        const searchInput = document.getElementById('ipo-search-input');
        const ajaxContainer = document.getElementById('ajax-results-wrapper');

        if (searchInput && ajaxContainer) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimeout);
                const query = this.value;

                searchTimeout = setTimeout(() => {
                    ajaxContainer.style.opacity = '0.5';
                    ajaxContainer.style.transition = 'opacity 0.2s';

                    const params = new URLSearchParams(window.location.search);
                    if (query.trim()) {
                        params.set('q', query.trim());
                        params.delete('paged');
                    } else {
                        params.delete('q');
                    }

                    const url = window.location.pathname + '?' + params.toString();

                    fetch(url)
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newContent = doc.getElementById('ajax-results-wrapper');

                            if (newContent) {
                                ajaxContainer.innerHTML = newContent.innerHTML;
                            }

                            ajaxContainer.style.opacity = '1';
                            window.history.pushState({}, '', url);
                        })
                        .catch(e => {
                            console.error('AJAX Search Error:', e);
                            ajaxContainer.style.opacity = '1';
                        });
                }, 500);
            });
        }
    });
</script>
<?php include TIGC_PATH . 'partials/footer-premium.php'; ?>
</body>

</html>