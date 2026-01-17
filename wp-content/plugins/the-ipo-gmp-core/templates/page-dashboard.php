<?php
/**
 * Template Name: IPO Dashboard Home
 * Description: A high-performance, real-time IPO intelligence dashboard.
 */

// 1. DATA FETCHING
global $wpdb;

// Tables
$t_master = $wpdb->prefix . 'ipomaster';

// Queries
// A. Active Mainboard
$mainboard = $wpdb->get_results("
    SELECT * FROM $t_master 
    WHERE is_sme = 0
    AND (
        status IN ('open', 'upcoming', 'allotment') 
        OR (status IN ('close', 'closed') AND STR_TO_DATE(listing_date, '%b %d, %Y') >= CURDATE())
        OR status LIKE '%allotment%' 
    )
    ORDER BY id DESC LIMIT 50
");

// B. Active SME
$sme = $wpdb->get_results("
    SELECT * FROM $t_master 
    WHERE is_sme = 1
    AND (
        status IN ('open', 'upcoming', 'allotment') 
        OR (status IN ('close', 'closed') AND STR_TO_DATE(listing_date, '%b %d, %Y') >= CURDATE())
        OR status LIKE '%allotment%'
    )
    ORDER BY id DESC LIMIT 50
");

// D. Global Stats (Aggregate)
$stats = $wpdb->get_row("
    SELECT 
        COUNT(*) as count, 
        SUM(issue_size_cr) as volume 
    FROM $t_master 
    WHERE status IN ('open', 'upcoming', 'allotment')
");
$active_count = $stats->count ?: 0;
$total_volume_cr = $stats->volume ?: 0;

// E. Sentiment & Trending Logic
$recent_data = $wpdb->get_results("SELECT id, name, premium, status, price_band FROM $t_master WHERE status IN ('open', 'upcoming', 'allotment') ORDER BY id DESC LIMIT 20");

$positive_gmp_count = 0;
$total_gmp_sum = 0;
$gmp_items = 0;
$highest_gmp_ipo = null;
$max_gmp = -1;

foreach ($recent_data as $i) {
    $p = (float) preg_replace('/[^0-9.]/', '', $i->premium);
    if ($p > 0)
        $positive_gmp_count++;
    if ($p > $max_gmp) {
        $max_gmp = $p;
        $highest_gmp_ipo = $i;
    }
    $total_gmp_sum += $p;
    $gmp_items++;
}

// Sentiment Calc
$sentiment_score = $gmp_items > 0 ? ($positive_gmp_count / $gmp_items) * 100 : 0;
if ($sentiment_score >= 80) {
    $sentiment_label = 'Bullish';
    $sentiment_color = 'text-neon-emerald';
    $sentiment_width = '90%';
} elseif ($sentiment_score >= 50) {
    $sentiment_label = 'Neutral';
    $sentiment_color = 'text-yellow-400';
    $sentiment_width = '60%';
} else {
    $sentiment_label = 'Bearish';
    $sentiment_color = 'text-red-400';
    $sentiment_width = '30%';
}

$avg_gmp = $gmp_items > 0 ? round($total_gmp_sum / $gmp_items) : 0;

get_header();
?>
<main class="max-w-[1280px] mx-auto px-10 py-8">
    <!-- AD SLOT: Header Leaderboard (728x90) -->
    <!--
    <div class="hidden md:flex justify-center mb-8">
        <div class="w-[728px] h-[90px] bg-slate-800/50 border border-double border-slate-700 rounded-lg flex items-center justify-center">
            <span class="text-xs font-bold text-slate-600 uppercase tracking-widest">Advertisement</span>
        </div>
    </div>
    -->
    <section class="mb-10 pt-6">
        <?php
        $show_hero_image = get_theme_mod('hero_show_image', false);
        $hero_image_url = get_theme_mod('hero_image');
        ?>
        <div class="<?php echo $show_hero_image ? 'grid grid-cols-1 lg:grid-cols-2 gap-12 items-center' : ''; ?>">
            <!-- Content Column -->
            <div class="<?php echo $show_hero_image ? 'text-left' : 'text-center lg:text-left'; ?>">
                <?php
                $hero_badge = get_theme_mod('hero_badge_text', 'Live Market Data • Mumbai / Gujarat');
                if (!empty($hero_badge)):
                    ?>
                    <div class="inline-block px-3 py-1 rounded-full bg-slate-800 border border-slate-700 mb-4">
                        <p
                            class="text-[10px] mobile:text-xs font-bold text-primary uppercase tracking-widest flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
                            </span>
                            <?php echo esc_html($hero_badge); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <h1 class="text-white text-[40px] lg:text-[56px] font-black leading-none mb-4 tracking-tighter">
                    <?php echo esc_html(get_theme_mod('hero_headline_1', 'Stop Guessing.')); ?> <br>
                    <span
                        class="text-white"><?php echo esc_html(get_theme_mod('hero_headline_prefix', 'Start ')); ?></span>
                    <span
                        class="text-hero-highlight"><?php echo esc_html(get_theme_mod('hero_headline_2', 'Improving.')); ?></span>
                </h1>
                <p
                    class="text-slate-400 text-lg max-w-2xl font-medium leading-relaxed <?php echo $show_hero_image ? '' : 'mx-auto lg:mx-0'; ?>">
                    <?php echo nl2br(esc_html(get_theme_mod('hero_description', "We track the Grey Market Premium (GMP) so you don't have to rely on rumors. Real-time data for Mainboard & SME IPOs, direct from the street to your screen."))); ?>
                </p>
            </div>

            <!-- Image Column -->
            <?php if ($show_hero_image): ?>
                <div class="hidden lg:block relative">
                    <?php if ($hero_image_url): ?>
                        <img src="<?php echo esc_url($hero_image_url); ?>" alt="Hero Image"
                            class="w-full h-auto rounded-2xl shadow-2xl border border-white/5 bg-slate-800/50">
                    <?php else: ?>
                        <!-- Placeholder if enabled but no image selected -->
                        <div
                            class="w-full aspect-video rounded-2xl bg-slate-800/50 border border-dashed border-slate-700 flex items-center justify-center">
                            <span class="text-slate-600 text-sm font-bold uppercase tracking-widest">Hero Image
                                Placeholder</span>
                        </div>
                    <?php endif; ?>

                    <!-- Glow Effect -->
                    <div class="absolute -inset-4 bg-primary/20 blur-3xl -z-10 rounded-[100%] opacity-40"></div>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-10">
        <div class="flex flex-col gap-2 rounded-xl p-6 border border-border-navy bg-card-dark">
            <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Market sentiment</p>
            <div class="flex items-baseline gap-2 mt-1">
                <p class="text-white text-2xl font-bold"><?php echo $sentiment_label; ?></p>
                <span
                    class="<?php echo $sentiment_color; ?> text-sm font-bold"><?php echo round($sentiment_score); ?>%</span>
            </div>
            <div class="w-full bg-slate-800 h-1.5 rounded-full mt-3 overflow-hidden">
                <div class="<?php echo str_replace('text-', 'bg-', $sentiment_color); ?> h-full rounded-full shadow-[0_0_10px_currentColor]"
                    style="width: <?php echo $sentiment_width; ?>"></div>
            </div>
        </div>

        <div class="flex flex-col gap-2 rounded-xl p-6 border border-border-navy bg-card-dark">
            <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Active IPOs</p>
            <p class="text-white text-2xl font-bold"><?php echo $active_count; ?> <span
                    class="text-slate-500 font-normal text-lg">Live</span></p>
            <p class="text-primary text-sm font-medium">Tracking Real-time</p>
        </div>

        <div class="flex flex-col gap-2 rounded-xl p-6 border border-border-navy bg-card-dark">
            <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Avg. GMP</p>
            <p class="text-white text-2xl font-bold">₹<?php echo number_format($avg_gmp); ?></p>
            <p class="text-neon-emerald text-sm font-medium"> across active</p>
        </div>

        <div class="flex flex-col gap-2 rounded-xl p-6 border border-border-navy bg-card-dark">
            <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Total Issue Size</p>
            <p class="text-white text-2xl font-bold">₹<?php echo number_format($total_volume_cr); ?> Cr</p>
            <p class="text-slate-500 text-sm font-medium">Cumulative</p>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-3 space-y-12">
            <section>
                <div class="flex flex-wrap items-center justify-between mb-4 px-1 gap-y-4">
                    <h2 class="text-white text-2xl font-bold tracking-tight">Mainboard IPOs</h2>
                    <div class="flex items-center gap-4">
                        <div class="flex gap-2 text-xs font-bold">
                            <button id="btn-active" onclick="filterTable('active')"
                                class="px-3 py-1 bg-primary/20 text-primary rounded-full border border-primary/30 transition-all hover:bg-primary/30">Active</button>
                            <button id="btn-upcoming" onclick="filterTable('upcoming')"
                                class="px-3 py-1 bg-slate-800 text-slate-400 rounded-full border border-slate-700 transition-all hover:bg-slate-700 hover:text-white">Upcoming</button>
                            <button id="btn-closed" onclick="filterTable('closed')"
                                class="px-3 py-1 bg-slate-800 text-slate-400 rounded-full border border-slate-700 transition-all hover:bg-slate-700 hover:text-white">Pre-Listing</button>
                        </div>
                        <a href="<?php echo home_url('/mainboard-ipos/'); ?>"
                            class="text-primary text-xs font-bold hover:underline hidden md:block">View All →</a>
                    </div>
                </div>
                <div id="mainboard-container" class="rounded-xl border border-border-navy bg-card-dark overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[800px]">
                            <thead>
                                <tr class="bg-slate-900/50 border-b border-border-navy">
                                    <th
                                        class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-widest">
                                        Company Name</th>
                                    <th
                                        class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-widest">
                                        Price Band</th>
                                    <th
                                        class="px-6 py-4 text-xs font-semibold text-emerald-500 uppercase tracking-widest bg-emerald-500/5">
                                        GMP Premium</th>
                                    <th id="header-dates-main"
                                        class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-widest">
                                        Offer Dates</th>
                                    <th
                                        class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-widest">
                                        Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-navy">
                                <?php if ($mainboard):
                                    $ad_counter = 0;
                                    foreach ($mainboard as $ipo):
                                        $ad_counter++;
                                        $details_url = home_url('/ipo-details/?slug=' . $ipo->slug);
                                        $gmp_val = $ipo->premium ?: '0';
                                        $gmp_clean = (float) preg_replace('/[^0-9.-]/', '', $gmp_val);
                                        $is_neg = $gmp_clean < 0;
                                        $gmp_txt = ($is_neg ? '- ₹' . abs($gmp_clean) : '+ ₹' . $gmp_val);
                                        $gmp_color = $is_neg ? 'text-red-400' : 'text-neon-emerald bg-neon-emerald/5 group-hover:bg-neon-emerald/10';
                                        $status_class = strtolower($ipo->status);
                                        ?>
                                        <tr class="data-table-row transition-colors cursor-pointer group ipo-row row-<?php echo esc_attr($status_class); ?>"
                                            data-status="<?php echo esc_attr($status_class); ?>"
                                            onclick="window.location.href='<?php echo esc_url($details_url); ?>'">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div
                                                        class="w-8 h-8 rounded bg-white p-1 flex items-center justify-center font-bold text-slate-900 overflow-hidden group-hover:scale-110 transition-transform">
                                                        <?php if (!empty($ipo->icon_url)): ?>
                                                            <img src="<?php echo esc_url($ipo->icon_url); ?>"
                                                                alt="<?php echo esc_attr($ipo->name); ?>"
                                                                class="w-full h-full object-contain" />
                                                        <?php else: ?>
                                                            <?php echo substr($ipo->name, 0, 1); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <p
                                                            class="text-sm font-bold text-white group-hover:text-primary transition-colors">
                                                            <?php echo esc_html($ipo->name); ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <?php if (false && $ad_counter == 3): // Ads Disabled ?>
                                            </tr>
                                            <!-- AD SLOT: In-Feed Native -->
                                            <tr>
                                                <td colspan="5" class="p-4 bg-card-dark/50">
                                                    <div
                                                        class="w-full h-[100px] bg-slate-800/30 border border-dashed border-slate-700 rounded-xl flex items-center justify-center">
                                                        <span
                                                            class="text-xs font-bold text-slate-600 uppercase tracking-widest">Sponsored
                                                            Content</span>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr class="data-table-row transition-colors cursor-pointer group ipo-row row-<?php echo esc_attr($status_class); ?>"
                                                onclick="window.location.href='<?php echo esc_url($details_url); ?>'"
                                                style="display:none">
                                            <?php endif; ?>
                                            <td class="px-6 py-4 text-sm font-medium text-slate-300">
                                                <?php echo esc_html($ipo->price_band); ?>
                                            </td>
                                            <td
                                                class="px-6 py-4 text-sm font-black <?php echo $gmp_color; ?> transition-colors">
                                                <?php echo $gmp_txt; ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm font-medium text-slate-300">
                                                <span class="date-offer">
                                                    <?php echo date('M j', strtotime($ipo->open_date)); ?> -
                                                    <?php echo date('M j', strtotime($ipo->close_date)); ?>
                                                </span>
                                                <span class="date-listing hidden text-slate-300">
                                                    <?php echo esc_html($ipo->listing_date); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="flex items-center gap-1.5 text-xs font-bold text-primary">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                                                    <?php echo esc_html($ipo->status); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr id="mainboard-no-data" class="hidden">
                                        <td colspan="5" class="py-12 text-center text-slate-500">
                                            <p class="text-xs">No IPOs found for this filter.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="py-12 text-center">
                                            <div class="flex flex-col items-center justify-center gap-3">
                                                <div
                                                    class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center text-slate-600 mb-1">
                                                    <span class="material-symbols-outlined text-2xl">bedtime</span>
                                                </div>
                                                <p class="text-white font-bold text-sm">Market is Sleeping</p>
                                                <p class="text-slate-500 text-xs">No active Mainboard IPOs right now.
                                                    <br>Whales are waiting, you should too.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        </table>
                    </div>
                </div>
            </section>

            <script>
                function filterTable(filter) {
                    // Update Mainboard Buttons
                    const btnActive = document.getElementById('btn-active');
                    const btnUpcoming = document.getElementById('btn-upcoming');
                    const btnClosed = document.getElementById('btn-closed');
                    const headerDates = document.getElementById('header-dates-main');

                    const activeClass = "bg-primary/20 text-primary border-primary/30";
                    const inactiveClass = "bg-slate-800 text-slate-400 border-slate-700";

                    // Reset all
                    btnActive.className = "px-3 py-1 rounded-full text-xs font-bold border transition-all hover:bg-slate-700 hover:text-white " + inactiveClass;
                    btnUpcoming.className = "px-3 py-1 rounded-full text-xs font-bold border transition-all hover:bg-slate-700 hover:text-white " + inactiveClass;
                    if (btnClosed) btnClosed.className = "px-3 py-1 rounded-full text-xs font-bold border transition-all hover:bg-slate-700 hover:text-white " + inactiveClass;

                    // Set Active and update Header
                    if (filter === 'active') {
                        btnActive.className = "px-3 py-1 rounded-full text-xs font-bold border transition-all hover:bg-primary/30 " + activeClass;
                        if (headerDates) headerDates.textContent = "Offer Dates";
                        toggleDateColumns(false, 'mainboard-container');
                    } else if (filter === 'upcoming') {
                        btnUpcoming.className = "px-3 py-1 rounded-full text-xs font-bold border transition-all hover:bg-primary/30 " + activeClass;
                        if (headerDates) headerDates.textContent = "Offer Dates";
                        toggleDateColumns(false, 'mainboard-container');
                    } else if (filter === 'closed') {
                        if (btnClosed) btnClosed.className = "px-3 py-1 rounded-full text-xs font-bold border transition-all hover:bg-primary/30 " + activeClass;
                        if (headerDates) headerDates.textContent = "Listing Date";
                        toggleDateColumns(true, 'mainboard-container');
                    }

                    // Filter Mainboard Rows
                    const rows = document.querySelectorAll('.ipo-row');
                    let visibleCount = 0;
                    rows.forEach(row => {
                        const status = row.getAttribute('data-status') || '';

                        // Define checks
                        const isUpcoming = status.includes('upcoming');
                        const isClosed = status.includes('close') || status.includes('listed') || status.includes('out') || status.includes('basis') || status.includes('refund') || status.includes('credit');
                        const isAllotment = status.includes('allotment'); // Separate check for allotment if needed, usually 'closed' bucket

                        // Refined Logic
                        if (filter === 'active') {
                            // Active = Open, Live, or NOT Upcoming AND NOT Closed
                            // This catch-all ensures 'Closes Today', 'Bidding Started' etc appear in Active.
                            // We explicitly exclude 'Allotment' from active if not already covered by isClosed
                            if (!isUpcoming && !isClosed && !isAllotment) {
                                row.style.display = 'table-row';
                                visibleCount++;
                            } else {
                                row.style.display = 'none';
                            }
                        } else if (filter === 'upcoming') {
                            if (isUpcoming) {
                                row.style.display = 'table-row';
                                visibleCount++;
                            } else {
                                row.style.display = 'none';
                            }
                        } else if (filter === 'closed') {
                            // Pre-Listing / Closed tab shows Closed + Allotment phases
                            if (isClosed || isAllotment) {
                                row.style.display = 'table-row';
                                visibleCount++;
                            } else {
                                row.style.display = 'none';
                            }
                        }
                    });

                    // Toggle No Data Row
                    const noDataRow = document.getElementById('mainboard-no-data');
                    if (noDataRow) {
                        noDataRow.style.display = (visibleCount === 0) ? 'table-row' : 'none';
                    }
                }

                function toggleDateColumns(showListing, containerId) {
                    const container = document.getElementById(containerId);
                    if (!container) return;

                    const offers = container.querySelectorAll('.date-offer');
                    const listings = container.querySelectorAll('.date-listing');

                    if (showListing) {
                        offers.forEach(el => el.classList.add('hidden'));
                        listings.forEach(el => el.classList.remove('hidden'));
                    } else {
                        offers.forEach(el => el.classList.remove('hidden'));
                        listings.forEach(el => el.classList.add('hidden'));
                    }
                }

                function filterSME(filter) {
                    // Update SME Buttons
                    const btnSmeActive = document.getElementById('btn-sme-active');
                    const btnSmeUpcoming = document.getElementById('btn-sme-upcoming');
                    const btnSmeClosed = document.getElementById('btn-sme-closed');
                    const headerDates = document.getElementById('header-dates-sme');

                    const activeClass = "bg-primary/20 text-primary border-primary/30";
                    const inactiveClass = "bg-slate-800 text-slate-400 border-slate-700";

                    // Reset
                    btnSmeActive.className = "px-3 py-1 rounded-full text-xs font-bold border transition-all hover:bg-slate-700 hover:text-white " + inactiveClass;
                    btnSmeUpcoming.className = "px-3 py-1 rounded-full text-xs font-bold border transition-all hover:bg-slate-700 hover:text-white " + inactiveClass;
                    if (btnSmeClosed) btnSmeClosed.className = "px-3 py-1 rounded-full text-xs font-bold border transition-all hover:bg-slate-700 hover:text-white " + inactiveClass;

                    if (filter === 'active') {
                        btnSmeActive.className = "px-3 py-1 rounded-full text-xs font-bold border transition-all hover:bg-primary/30 " + activeClass;
                        if (headerDates) headerDates.textContent = "Offer Dates";
                        toggleDateColumns(false, 'sme-container');
                    } else if (filter === 'upcoming') {
                        btnSmeUpcoming.className = "px-3 py-1 rounded-full text-xs font-bold border transition-all hover:bg-primary/30 " + activeClass;
                        if (headerDates) headerDates.textContent = "Offer Dates";
                        toggleDateColumns(false, 'sme-container');
                    } else if (filter === 'closed') {
                        if (btnSmeClosed) btnSmeClosed.className = "px-3 py-1 rounded-full text-xs font-bold border transition-all hover:bg-primary/30 " + activeClass;
                        if (headerDates) headerDates.textContent = "Listing Date";
                        toggleDateColumns(true, 'sme-container');
                    }

                    // Filter SME Rows
                    const rows = document.querySelectorAll('.sme-row');
                    rows.forEach(row => {
                        const status = row.getAttribute('data-status') || '';

                        const isUpcoming = status.includes('upcoming');
                        const isClosed = status.includes('close') || status.includes('allotment') || status.includes('out');
                        const isOpen = status.includes('open') || status === 'live';

                        if (filter === 'active') {
                            if (isOpen && !isClosed && !isUpcoming) row.style.display = 'table-row';
                            else row.style.display = 'none';
                        } else if (filter === 'upcoming') {
                            if (isUpcoming) row.style.display = 'table-row';
                            else row.style.display = 'none';
                        } else if (filter === 'closed') {
                            if (isClosed) row.style.display = 'table-row';
                            else row.style.display = 'none';
                        }
                    });
                }

                // Init
                document.addEventListener('DOMContentLoaded', () => {
                    filterTable('active');
                    filterSME('active');
                });
            </script>
            <section>
                <div class="flex flex-wrap items-center justify-between mb-4 px-1 gap-y-4">
                    <h2 class="text-white text-2xl font-bold tracking-tight">SME IPO Monitor</h2>
                    <div class="flex items-center gap-4">
                        <div class="flex gap-2 text-xs font-bold">
                            <button id="btn-sme-active" onclick="filterSME('active')"
                                class="px-3 py-1 bg-primary/20 text-primary rounded-full border border-primary/30 transition-all hover:bg-primary/30">Active</button>
                            <button id="btn-sme-upcoming" onclick="filterSME('upcoming')"
                                class="px-3 py-1 bg-slate-800 text-slate-400 rounded-full border border-slate-700 transition-all hover:bg-slate-700 hover:text-white">Upcoming</button>
                            <button id="btn-sme-closed" onclick="filterSME('closed')"
                                class="px-3 py-1 bg-slate-800 text-slate-400 rounded-full border border-slate-700 transition-all hover:bg-slate-700 hover:text-white">Pre-Listing</button>
                        </div>
                        <a class="text-primary text-xs font-bold hover:underline hidden md:block"
                            href="<?php echo home_url('/sme-ipos/'); ?>">View All SME →</a>
                    </div>
                </div>
                <div id="sme-container" class="rounded-xl border border-border-navy bg-card-dark overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[800px]">
                            <thead>
                                <tr class="bg-slate-900/50 border-b border-border-navy">
                                    <th
                                        class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-widest">
                                        Company Name</th>
                                    <th
                                        class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-widest">
                                        Price Band</th>
                                    <th
                                        class="px-6 py-4 text-xs font-semibold text-emerald-500 uppercase tracking-widest bg-emerald-500/5">
                                        GMP Premium</th>
                                    <th id="header-dates-sme"
                                        class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-widest">
                                        Offer Dates</th>
                                    <th
                                        class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-widest">
                                        Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-navy">
                                <?php if ($sme):
                                    foreach ($sme as $ipo):
                                        $details_url = home_url('/ipo-details/?slug=' . $ipo->slug);
                                        $gmp_val = $ipo->premium ?: '0';
                                        $gmp_clean = (float) preg_replace('/[^0-9.-]/', '', $gmp_val);
                                        $is_neg = $gmp_clean < 0;
                                        $gmp_txt = ($is_neg ? '- ₹' . abs($gmp_clean) : '+ ₹' . $gmp_val);
                                        $gmp_color = $is_neg ? 'text-red-400' : 'text-neon-emerald bg-neon-emerald/5 group-hover:bg-neon-emerald/10';
                                        $status_class = strtolower($ipo->status);
                                        ?>
                                        <tr class="data-table-row transition-colors cursor-pointer group sme-row row-<?php echo esc_attr($status_class); ?>"
                                            data-status="<?php echo esc_attr($status_class); ?>"
                                            onclick="window.location.href='<?php echo esc_url($details_url); ?>'">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div
                                                        class="w-8 h-8 rounded bg-white p-1 flex items-center justify-center font-bold text-slate-900 overflow-hidden group-hover:scale-110 transition-transform">
                                                        <?php if (!empty($ipo->icon_url)): ?>
                                                            <img src="<?php echo esc_url($ipo->icon_url); ?>"
                                                                alt="<?php echo esc_attr($ipo->name); ?>"
                                                                class="w-full h-full object-contain" />
                                                        <?php else: ?>
                                                            <?php echo substr($ipo->name, 0, 1); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <p
                                                            class="text-sm font-bold text-white group-hover:text-primary transition-colors">
                                                            <?php echo esc_html($ipo->name); ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-sm font-medium text-slate-300">
                                                <?php echo esc_html($ipo->price_band); ?>
                                            </td>
                                            <td
                                                class="px-6 py-4 text-sm font-black <?php echo $gmp_color; ?> transition-colors">
                                                <?php echo $gmp_txt; ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm font-medium text-slate-300">
                                                <span class="date-offer">
                                                    <?php echo date('M j', strtotime($ipo->open_date)); ?> -
                                                    <?php echo date('M j', strtotime($ipo->close_date)); ?>
                                                </span>
                                                <span class="date-listing hidden text-slate-300">
                                                    <?php echo esc_html($ipo->listing_date); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="flex items-center gap-1.5 text-xs font-bold text-primary">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                                                    <?php echo esc_html($ipo->status); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr id="sme-no-data" class="hidden">
                                        <td colspan="5" class="py-12 text-center">
                                            <div class="flex flex-col items-center justify-center gap-3">
                                                <div
                                                    class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center text-slate-600 mb-1">
                                                    <span class="material-symbols-outlined text-2xl">bubble_chart</span>
                                                </div>
                                                <p class="text-slate-400 font-medium text-sm">No SME IPOs found for this
                                                    filter.</p>
                                                <p class="text-slate-600 text-xs">Try switching tabs.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="py-12 text-center">
                                            <div class="flex flex-col items-center justify-center gap-3">
                                                <div
                                                    class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center text-slate-600 mb-1">
                                                    <span class="material-symbols-outlined text-2xl">bubble_chart</span>
                                                </div>
                                                <p class="text-slate-400 font-medium text-sm">No active SME IPOs currently.
                                                </p>
                                                <p class="text-slate-600 text-xs">Check 'Upcoming' tab for new
                                                    opportunities.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Latest News Section -->
            <section>
                <div class="flex items-center justify-between mb-6 px-1">
                    <h2 class="text-white text-2xl font-bold tracking-tight">Market Pulse</h2>
                    <a class="flex items-center gap-1 text-primary text-xs font-bold hover:text-emerald-400 transition-colors uppercase tracking-wider"
                        href="<?php echo home_url('/blog/'); ?>">
                        Read Reports <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php
                    $recent_posts = get_posts(array('numberposts' => 3, 'post_status' => 'publish'));
                    if ($recent_posts):
                        foreach ($recent_posts as $post):
                            setup_postdata($post);
                            ?>
                            <article class="group relative h-full">
                                <a href="<?php the_permalink(); ?>" class="flex flex-col h-full">
                                    <div
                                        class="relative aspect-video rounded-2xl overflow-hidden mb-4 border border-white/5 group-hover:border-primary/30 group-hover:shadow-lg group-hover:shadow-primary/5 transition-all duration-500">
                                        <?php if (has_post_thumbnail()):
                                            echo get_the_post_thumbnail(null, 'medium', ['class' => 'w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700']);
                                        else: ?>
                                            <div class="w-full h-full flex items-center justify-center bg-slate-800 text-slate-700">
                                                <span class="material-symbols-outlined text-4xl">article</span>
                                            </div>
                                        <?php endif; ?>
                                        <!-- Scrim Gradient -->
                                        <div
                                            class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent opacity-60 group-hover:opacity-40 transition-opacity">
                                        </div>

                                        <!-- Badge -->
                                        <div class="absolute top-3 left-3">
                                            <?php
                                            $cats = get_the_category();
                                            $cat_name = !empty($cats) ? $cats[0]->name : 'Analysis';
                                            ?>
                                            <span
                                                class="px-2 py-1 bg-black/50 backdrop-blur-md border border-white/10 rounded-lg text-[10px] font-bold text-white uppercase tracking-wider">
                                                <?php echo esc_html($cat_name); ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex flex-col flex-1 px-1">
                                        <div
                                            class="flex items-center gap-2 mb-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                                            <span class="text-primary"><?php echo get_the_date('M j'); ?></span>
                                            <span class="w-1 h-1 rounded-full bg-slate-700"></span>
                                            <span><?php echo get_the_date('Y'); ?></span>
                                        </div>

                                        <h3
                                            class="text-white font-bold text-lg leading-snug mb-2 group-hover:text-primary transition-colors line-clamp-2">
                                            <?php the_title(); ?>
                                        </h3>

                                        <p class="text-slate-400 text-xs leading-relaxed line-clamp-2 mb-3">
                                            <?php echo wp_trim_words(get_the_excerpt(), 15, '...'); ?>
                                        </p>

                                        <div
                                            class="mt-auto flex items-center text-xs font-bold text-primary opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300">
                                            Read Article <span
                                                class="material-symbols-outlined text-sm ml-1">trending_flat</span>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        <?php endforeach;
                        wp_reset_postdata();
                    endif; ?>

                    <?php if (!$recent_posts): ?>
                        <div
                            class="col-span-3 py-16 px-4 text-center border border-dashed border-slate-800 rounded-2xl flex flex-col items-center justify-center gap-4 bg-slate-900/30">
                            <div class="relative">
                                <div
                                    class="w-16 h-16 rounded-2xl bg-slate-800 flex items-center justify-center text-slate-600 rotate-3">
                                    <span class="material-symbols-outlined text-3xl">edit_square</span>
                                </div>
                                <div class="w-16 h-16 rounded-2xl bg-slate-800/50 absolute top-0 left-0 -rotate-6 -z-10">
                                </div>
                            </div>

                            <div>
                                <h4 class="text-white font-bold text-base mb-1">Market Insights Loading...</h4>
                                <p class="text-slate-500 text-xs max-w-sm mx-auto leading-relaxed">
                                    Our analysts are currently breaking down the latest GMP trends. <br>Check back shortly
                                    for premium reports.
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
        <aside class="space-y-8">
            <!-- Trending IPO Widget -->
            <?php if ($highest_gmp_ipo): ?>
                <div class="p-5 rounded-xl bg-primary/10 border border-blue-600/40 relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="flex justify-between items-center mb-1">
                            <h4 class="text-primary font-black text-sm uppercase tracking-tighter">🔥 Top Trending</h4>
                            <span
                                class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider <?php echo strtolower($highest_gmp_ipo->status) === 'open' ? 'bg-green-500/20 text-green-400 border border-green-500/30' : 'bg-slate-700 text-slate-300 border border-slate-600'; ?>">
                                <?php echo esc_html($highest_gmp_ipo->status); ?>
                            </span>
                        </div>
                        <p class="text-white text-lg font-bold"><?php echo esc_html($highest_gmp_ipo->name); ?></p>
                        <div class="flex flex-col gap-3 mt-4">
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-400">Current GMP</span>
                                <span class="text-neon-emerald font-bold">+
                                    ₹<?php echo esc_html($highest_gmp_ipo->premium); ?></span>
                            </div>
                            <div class="w-full bg-slate-800 h-1.5 rounded-full">
                                <div class="bg-primary h-full rounded-full shadow-[0_0_8px_rgba(13,127,242,0.6)]"
                                    style="width: 100%"></div>
                            </div>
                            <p class="text-[10px] text-slate-500 text-right">Most demanded active Stock</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Buyback Watch Widget -->
            <div class="rounded-xl border border-border-navy bg-card-dark overflow-hidden">
                <div class="p-5 border-b border-border-navy flex items-center justify-between">
                    <h3 class="text-white font-bold text-lg flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-purple-500 animate-pulse"></span>
                        Buyback Watch
                    </h3>
                    <span
                        class="text-[10px] font-bold text-purple-400 bg-purple-500/10 px-2 py-1 rounded border border-purple-500/20">Active</span>
                </div>
                <div class="divide-y divide-border-navy">
                    <?php
                    // Fetch Active Buybacks
                    $buybacks = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}buybacks WHERE type LIKE '%Open%' OR type LIKE '%Upcoming%' ORDER BY id DESC LIMIT 5");

                    if ($buybacks):
                        foreach ($buybacks as $bb):
                            // Calc Premium
                            $offer_price = (float) preg_replace('/[^0-9.]/', '', $bb->price);
                            $mkt_price = (float) preg_replace('/[^0-9.]/', '', $bb->market_price);
                            $premium = 0;
                            if ($mkt_price > 0 && $offer_price > 0) {
                                $premium = round((($offer_price - $mkt_price) / $mkt_price) * 100, 1);
                            }
                            ?>
                            <div class="p-4 hover:bg-slate-800/50 transition-colors cursor-pointer group">
                                <div class="flex justify-between items-start mb-2">
                                    <h4
                                        class="text-white font-bold text-sm group-hover:text-purple-400 transition-colors truncate max-w-[150px]">
                                        <?php echo esc_html($bb->company); ?>
                                    </h4>
                                    <span
                                        class="text-[10px] font-bold text-slate-400 uppercase"><?php echo esc_html($bb->type ?: 'Tender'); ?></span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex flex-col">
                                        <span class="text-slate-500">Price</span>
                                        <span class="text-white font-semibold">₹<?php echo esc_html($bb->price); ?></span>
                                    </div>
                                    <?php if ($premium > 0): ?>
                                        <div class="flex flex-col items-end">
                                            <span class="text-slate-500">Premium</span>
                                            <span class="text-neon-emerald font-bold">+<?php echo $premium; ?>%</span>
                                        </div>
                                    <?php else: ?>
                                        <div class="flex flex-col items-end">
                                            <span class="text-slate-500">Size</span>
                                            <span
                                                class="text-slate-300 font-bold"><?php echo esc_html($bb->issue_size ?: '-'); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; else: ?>
                        <div class="p-8 text-center flex flex-col items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-slate-600 text-2xl">savings</span>
                            <p class="text-slate-500 text-xs font-medium">No active buybacks.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="p-3 bg-slate-900/50 text-center border-t border-border-navy">
                    <a href="<?php echo home_url('/buybacks/'); ?>"
                        class="text-xs font-bold text-purple-400 hover:text-purple-300 transition-colors">View All
                        Buybacks →</a>
                </div>
            </div>
            <!-- AD SLOT: Sidebar Square (300x250) -->
            <!--
            <div class="w-full h-[250px] bg-slate-800/50 border border-double border-slate-700 rounded-xl flex items-center justify-center">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-widest">Advertisement</span>
            </div>
            -->

            <div class="rounded-xl border border-border-navy bg-card-dark overflow-hidden">
                <div class="p-5 border-b border-border-navy flex items-center justify-between">
                    <h3 class="text-white font-bold text-lg">Allotment Corner</h3>
                    <span class="material-symbols-outlined text-primary">link</span>
                </div>
                <div class="p-5 space-y-3">
                    <p class="text-xs text-slate-400 mb-4 leading-relaxed">Check your application status directly on
                        registrar portals:</p>
                    <a class="flex items-center gap-3 p-3 rounded-lg bg-slate-900 border border-border-navy hover:bg-slate-800 transition-colors"
                        href="https://linkintime.co.in/initial_offer/public-issues.html" target="_blank">
                        <div
                            class="w-8 h-8 bg-white rounded flex items-center justify-center font-bold text-blue-900 text-[10px]">
                            LI</div>
                        <div>
                            <p class="text-sm font-bold text-white">Link Intime</p>
                            <p class="text-[10px] text-slate-500">Major Registrar</p>
                        </div>
                        <span class="material-symbols-outlined text-slate-600 ml-auto text-sm">open_in_new</span>
                    </a>

                    <a class="flex items-center gap-3 p-3 rounded-lg bg-slate-900 border border-border-navy hover:bg-slate-800 transition-colors"
                        href="https://kosmic.kfintech.com/ipostatus/" target="_blank">
                        <div
                            class="w-8 h-8 bg-white rounded flex items-center justify-center font-bold text-blue-600 text-[10px]">
                            KF</div>
                        <div>
                            <p class="text-sm font-bold text-white">KFin Tech</p>
                            <p class="text-[10px] text-slate-500">Major Registrar</p>
                        </div>
                        <span class="material-symbols-outlined text-slate-600 ml-auto text-sm">open_in_new</span>
                    </a>

                    <a class="flex items-center gap-3 p-3 rounded-lg bg-slate-900 border border-border-navy hover:bg-slate-800 transition-colors"
                        href="https://www.bigshareonline.com/ipo_Allotment.html" target="_blank">
                        <div
                            class="w-8 h-8 bg-white rounded flex items-center justify-center font-bold text-orange-600 text-[10px]">
                            BS</div>
                        <div>
                            <p class="text-sm font-bold text-white">Bigshare</p>
                            <p class="text-[10px] text-slate-500">SME Specialist</p>
                        </div>
                        <span class="material-symbols-outlined text-slate-600 ml-auto text-sm">open_in_new</span>
                    </a>

                    <a class="flex items-center gap-3 p-3 rounded-lg bg-slate-900 border border-border-navy hover:bg-slate-800 transition-colors"
                        href="https://www.skylinerta.com/ipo.php" target="_blank">
                        <div
                            class="w-8 h-8 bg-white rounded flex items-center justify-center font-bold text-sky-600 text-[10px]">
                            SK</div>
                        <div>
                            <p class="text-sm font-bold text-white">Skyline</p>
                            <p class="text-[10px] text-slate-500">Registrar</p>
                        </div>
                        <span class="material-symbols-outlined text-slate-600 ml-auto text-sm">open_in_new</span>
                    </a>

                    <a class="flex items-center gap-3 p-3 rounded-lg bg-slate-900 border border-border-navy hover:bg-slate-800 transition-colors"
                        href="https://maashitla.com/allotment-status/" target="_blank">
                        <div
                            class="w-8 h-8 bg-white rounded flex items-center justify-center font-bold text-purple-600 text-[10px]">
                            MS</div>
                        <div>
                            <p class="text-sm font-bold text-white">Maashitla</p>
                            <p class="text-[10px] text-slate-500">SME Registrar</p>
                        </div>
                        <span class="material-symbols-outlined text-slate-600 ml-auto text-sm">open_in_new</span>
                    </a>

                    <a class="flex items-center gap-3 p-3 rounded-lg bg-slate-900 border border-border-navy hover:bg-slate-800 transition-colors"
                        href="https://www.purvashare.com/queries/" target="_blank">
                        <div
                            class="w-8 h-8 bg-white rounded flex items-center justify-center font-bold text-red-600 text-[10px]">
                            PS</div>
                        <div>
                            <p class="text-sm font-bold text-white">Purva Share</p>
                            <p class="text-[10px] text-slate-500">Registrar</p>
                        </div>
                        <span class="material-symbols-outlined text-slate-600 ml-auto text-sm">open_in_new</span>
                    </a>
                </div>
            </div>


            <!-- Trending IPO Widget -->

        </aside>
    </div>
    <!-- SEO & About Section -->
    <section class="mt-16 pt-10 border-t border-border-navy grid grid-cols-1 lg:grid-cols-3 gap-10">
        <div class="lg:col-span-2 prose prose-invert prose-sm max-w-none">
            <h2 class="text-white font-bold text-xl mb-4">India's Most Trusted IPO GMP & Analytics Platform</h2>
            <p class="text-slate-400 leading-relaxed mb-4">
                The IPO GMP is your definitive source for real-time <strong>Grey Market Premium (GMP)</strong>, live
                subscription numbers, and in-depth analysis of Mainboard and SME IPOs in India. We decode complex market
                data into actionable intelligence for retail investors, HNI, and QIBs.
            </p>
        </div>
        <div>
            <div class="p-6 rounded-xl bg-slate-900/50 border border-border-navy">
                <h3 class="text-white font-bold text-sm mb-4">Quick Market Links</h3>
                <ul class="space-y-2 text-xs font-medium text-slate-400">
                    <li><a href="<?php echo home_url('/mainboard-ipos/'); ?>"
                            class="hover:text-primary transition-colors flex items-center gap-2"><span
                                class="w-1 h-1 rounded-full bg-slate-600"></span> Upcoming Mainboard IPOs</a></li>
                    <li><a href="<?php echo home_url('/sme-ipos/'); ?>"
                            class="hover:text-primary transition-colors flex items-center gap-2"><span
                                class="w-1 h-1 rounded-full bg-slate-600"></span> SME IPO Performance</a></li>
                </ul>
            </div>
        </div>
    </section>
</main>


<?php get_footer(); ?>