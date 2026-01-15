<?php
/**
 * Template Name: IPO Archive
 * Description: Generic archive template for Mainboard, SME, and Buybacks.
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$t_master = $wpdb->prefix . 'ipomaster';
$t_buybacks = $wpdb->prefix . 'buybacks';

// 1. Determine Context
$slug = get_post_field('post_name', get_post());
$context = 'mainboard'; // default
$title = 'Mainboard IPOs';
$desc = 'Complete list of Mainboard IPOs including active, upcoming, and closed issues.';

if (strpos($slug, 'sme') !== false) {
    $context = 'sme';
    $title = 'SME IPOs';
    $desc = 'Track all SME IPOs, listing gains, and subscription status.';
} elseif (strpos($slug, 'buyback') !== false) {
    $context = 'buyback';
    $title = 'Buybacks';
    $desc = 'Latest Share Buybacks, Tender Offers, and Open Market buybacks.';
}

// 2. Initial Data (Active by Default)
$items = [];
$total_pages = 1;
$limit = 20;
$default_status = 'active';

if ($context === 'buyback') {
    // Buyback Logic
    $where_sql = "1=1 AND (type LIKE '%Open%' OR type LIKE '%Upcoming%')"; // Active logic
    $items = $wpdb->get_results("SELECT * FROM $t_buybacks WHERE $where_sql ORDER BY id DESC LIMIT $limit");
    $total = $wpdb->get_var("SELECT COUNT(*) FROM $t_buybacks WHERE $where_sql");
    $total_pages = ceil($total / $limit);
} else {
    // IPO Logic
    $is_sme = ($context === 'sme') ? 1 : 0;
    // Active Logic (Match Ajax.php: status='open' OR status LIKE '%live%')
    $where_sql = $wpdb->prepare("is_sme = %d AND (status = 'open' OR status LIKE '%%live%%')", $is_sme);

    $items = $wpdb->get_results("SELECT * FROM $t_master WHERE $where_sql ORDER BY id DESC LIMIT $limit");
    $total = $wpdb->get_var("SELECT COUNT(*) FROM $t_master WHERE $where_sql");
    $total_pages = ceil($total / $limit);
}

get_header();
?>

<main class="max-w-[1280px] mx-auto px-4 md:px-10 py-8">
    <section class="mb-10 text-center lg:text-left pt-6">
        <h1 class="text-white text-3xl md:text-5xl font-black leading-tight mb-4 tracking-tighter">
            <?php echo esc_html($title); ?>
        </h1>
        <p class="text-slate-400 text-base md:text-lg max-w-2xl font-medium leading-relaxed">
            <?php echo esc_html($desc); ?>
        </p>
    </section>

    <!-- Filters & Search -->
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        <!-- Filter Buttons -->
        <div class="flex p-1 bg-slate-900/50 rounded-xl border border-border-navy overflow-x-auto custom-scrollbar">
            <button onclick="setFilter('active')" id="btn-active"
                class="filter-btn active px-4 py-2 rounded-lg text-sm font-bold text-white bg-slate-800 transition-all whitespace-nowrap">Active</button>
            <button onclick="setFilter('pre-listing')" id="btn-pre-listing"
                class="filter-btn px-4 py-2 rounded-lg text-sm font-bold text-slate-400 hover:text-white transition-all whitespace-nowrap">Pre-Listing</button>
            <button onclick="setFilter('upcoming')" id="btn-upcoming"
                class="filter-btn px-4 py-2 rounded-lg text-sm font-bold text-slate-400 hover:text-white transition-all whitespace-nowrap">Upcoming</button>
            <button onclick="setFilter('closed')" id="btn-closed"
                class="filter-btn px-4 py-2 rounded-lg text-sm font-bold text-slate-400 hover:text-white transition-all whitespace-nowrap">Closed</button>
        </div>

        <!-- Search Input -->
        <div class="relative w-full md:w-96">
            <span
                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">search</span>
            <input type="text" id="ipo-search" placeholder="Search companies..."
                class="w-full pl-10 pr-4 py-3 bg-slate-900/50 border border-border-navy rounded-xl text-white placeholder-slate-500 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"
                onkeyup="debounceSearch()">
        </div>
    </div>

    <!-- Results Table -->
    <div class="rounded-xl border border-border-navy bg-card-dark overflow-hidden relative">
        <div id="loading-overlay" class="absolute inset-0 bg-slate-900/80 z-10 hidden flex items-center justify-center">
            <div class="w-8 h-8 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-slate-900/50 border-b border-border-navy">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-widest">
                            Company Name
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-widest">
                            <?php echo ($context === 'buyback') ? 'Type' : 'Price Band'; ?>
                        </th>
                        <th
                            class="px-6 py-4 text-xs font-semibold text-emerald-500 uppercase tracking-widest bg-emerald-500/5">
                            <?php echo ($context === 'buyback') ? 'Offer Price' : 'GMP Premium'; ?>
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-widest">
                            <?php echo ($context === 'buyback') ? 'Size' : 'Dates'; ?>
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-widest">
                            Status
                        </th>
                    </tr>
                </thead>
                <tbody id="ipo-results" class="divide-y divide-border-navy">
                    <?php if ($items):
                        foreach ($items as $item):
                            // Determine Link and Status
                            $link = '#';
                            $status_class = '';

                            if ($context === 'buyback') {
                                // Buyback specific overrides
                                $name = $item->company;
                                $col2 = $item->type;
                                $col3 = $item->price;
                                $col4 = $item->issue_size;
                                $col5 = 'Active';
                            } else {
                                // IPO specific
                                $name = $item->name;
                                $col2 = $item->price_band;

                                $gmp_val = $item->premium ?: '0';
                                $gmp_clean = (float) preg_replace('/[^0-9.-]/', '', $gmp_val);
                                $is_neg = $gmp_clean < 0;
                                $col3 = ($is_neg ? '- ₹' . abs($gmp_clean) : '+ ₹' . $gmp_val);
                                $col3_class = $is_neg ? 'text-red-400' : 'text-neon-emerald bg-neon-emerald/5 group-hover:bg-neon-emerald/10';

                                $col4 = date('M j', strtotime($item->open_date)) . ' - ' . date('M j', strtotime($item->close_date));
                                $col5 = $item->status;
                                $link = home_url('/ipo-details/?slug=' . $item->slug);
                                $status_class = strtolower($item->status);
                            }
                            ?>
                            <tr class="group hover:bg-slate-800/30 transition-colors cursor-pointer"
                                onclick="window.location.href='<?php echo esc_url($link); ?>'">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <?php if ($context !== 'buyback'): ?>
                                            <div
                                                class="w-8 h-8 rounded bg-white p-1 flex items-center justify-center font-bold text-slate-900 overflow-hidden group-hover:scale-110 transition-transform">
                                                <?php if (!empty($item->icon_url)): ?>
                                                    <img src="<?php echo esc_url($item->icon_url); ?>"
                                                        alt="<?php echo esc_attr($name); ?>" class="w-full h-full object-contain" />
                                                <?php else: ?>
                                                    <?php echo substr($name, 0, 1); ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <p class="text-sm font-bold text-white group-hover:text-primary transition-colors">
                                                <?php echo esc_html($name); ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-slate-300">
                                    <?php echo esc_html($col2); ?>
                                </td>
                                <td class="px-6 py-4 text-sm font-black <?php echo $col3_class; ?>">
                                    <?php echo esc_html($col3); ?>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-slate-300">
                                    <?php echo esc_html($col4); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="flex items-center gap-1.5 text-xs font-bold text-primary">
                                        <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                                        <?php echo esc_html($col5); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-500">
                                No records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div
            class="p-4 border-t border-slate-800 flex flex-col md:flex-row justify-between items-center gap-4 bg-slate-900/30">
            <span id="page-info" class="text-xs font-bold text-slate-500 order-2 md:order-1">Page 1</span>

            <div id="pagination-numbers" class="flex items-center gap-1 order-1 md:order-2 flex-wrap justify-center">
                <!-- Numbers injected via JS -->
            </div>
        </div>
    </div>
</main>

<script>
    let currentState = {
        context: '<?php echo $context; ?>',
        status: 'active', // Default aligned with PHP
        search: '',
        paged: 1,
        totalPages: <?php echo max(1, $total_pages); ?> // PHP Calculated
    };

    // Optimization: Only fetch if we somehow didn't load data, but now we assume PHP loaded page 1.
    // So we don't call fetchData() on load unless we want to refresh.
    document.addEventListener('DOMContentLoaded', () => {
        // Initial Pagination UI render
        updatePaginationUI();
    });

    let debounceTimer;

    function setFilter(status) {
        currentState.status = status;
        currentState.paged = 1;

        // Update UI
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active', 'bg-slate-800', 'text-white');
            btn.classList.add('text-slate-400');
        });
        const activeBtn = document.getElementById('btn-' + status);
        activeBtn.classList.add('active', 'bg-slate-800', 'text-white');
        activeBtn.classList.remove('text-slate-400');

        fetchData();
    }

    function debounceSearch() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            currentState.search = document.getElementById('ipo-search').value;
            currentState.paged = 1;

            if (currentState.search.length > 0) {
                // Visual UX: Remove active state from all tabs since search is global
                document.querySelectorAll('.filter-btn').forEach(btn => {
                    btn.classList.remove('active', 'bg-slate-800', 'text-white');
                    btn.classList.add('text-slate-400');
                });
            } else {
                // Restore the highlighted tab for the current status if search is empty
                const activeBtn = document.getElementById('btn-' + currentState.status);
                if (activeBtn) {
                    activeBtn.classList.add('active', 'bg-slate-800', 'text-white');
                    activeBtn.classList.remove('text-slate-400');
                }
            }

            fetchData();
        }, 500);
    }

    function goToPage(page) {
        if (page < 1 || page > currentState.totalPages) return;
        currentState.paged = page;
        fetchData();
    }

    function fetchData() {
        const loading = document.getElementById('loading-overlay');
        loading.classList.remove('hidden');

        const formData = new FormData();
        formData.append('action', 'tigc_filter_ipos');
        formData.append('context', currentState.context);
        formData.append('status', currentState.status);
        formData.append('search', currentState.search);
        formData.append('paged', currentState.paged);

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                loading.classList.add('hidden');
                if (data.success) {
                    document.getElementById('ipo-results').innerHTML = data.data.html;
                    currentState.totalPages = data.data.total_pages; // Assuming backend sends this
                    updatePaginationUI();
                }
            })
            .catch(err => {
                console.error(err);
                loading.classList.add('hidden');
            });
    }

    function updatePaginationUI() {
        document.getElementById('page-info').textContent = 'Page ' + currentState.paged + ' of ' + currentState.totalPages;

        const container = document.getElementById('pagination-numbers');
        let html = '';

        const current = currentState.paged;
        const total = currentState.totalPages;
        const maxVisible = 5; // How many numbers to show

        // Prev Button
        html += `<button onclick="goToPage(${current - 1})" ${current === 1 ? 'disabled' : ''} 
            class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-800 text-slate-400 font-bold text-xs disabled:opacity-30 disabled:cursor-not-allowed hover:bg-slate-700 hover:text-white transition-all">
            <span class="material-symbols-outlined text-sm">chevron_left</span>
        </button>`;

        // Logic for sliding window
        let startPage = Math.max(1, current - Math.floor(maxVisible / 2));
        let endPage = Math.min(total, startPage + maxVisible - 1);

        if (endPage - startPage + 1 < maxVisible) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }

        if (startPage > 1) {
            html += `<button onclick="goToPage(1)" class="w-8 h-8 rounded-lg bg-slate-800 text-slate-400 font-bold text-xs hover:bg-slate-700 hover:text-white transition-all">1</button>`;
            if (startPage > 2) html += `<span class="text-slate-600 px-1">...</span>`;
        }

        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === current ? 'bg-primary text-white shadow-lg shadow-primary/25' : 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white';
            html += `<button onclick="goToPage(${i})" class="w-8 h-8 rounded-lg font-bold text-xs transition-all ${activeClass}">${i}</button>`;
        }

        if (endPage < total) {
            if (endPage < total - 1) html += `<span class="text-slate-600 px-1">...</span>`;
            html += `<button onclick="goToPage(${total})" class="w-8 h-8 rounded-lg bg-slate-800 text-slate-400 font-bold text-xs hover:bg-slate-700 hover:text-white transition-all">${total}</button>`;
        }

        // Next Button
        html += `<button onclick="goToPage(${current + 1})" ${current === total ? 'disabled' : ''} 
            class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-800 text-slate-400 font-bold text-xs disabled:opacity-30 disabled:cursor-not-allowed hover:bg-slate-700 hover:text-white transition-all">
            <span class="material-symbols-outlined text-sm">chevron_right</span>
        </button>`;

        container.innerHTML = html;
    }
</script>

<?php get_footer(); ?>