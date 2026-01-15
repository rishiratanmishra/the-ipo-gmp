<?php
/**
 * Template Name: IPO Details Template (Premium Tabbed UI)
 * Description: Premium tabbed interface with glassmorphism design for comprehensive IPO data.
 */

get_header();

global $wpdb;
$controller = \TIGC\Core\Plugin::instance()->get_controller();
$ipo = $controller->current_ipo;
$details = $controller->current_details;

$slug = isset($_GET['slug']) ? sanitize_text_field($_GET['slug']) : '';

// 1. Controller Validation
if (!$ipo) {
    status_header(404);
    nocache_headers();
    include(get_query_template('404'));
    exit;
}

// 2. On-Demand Fetch (Legacy Support)
// If we have no details, try to fetch them live.
if (empty($details) && class_exists('IPOD_Fetcher')) {
    $scraped_data = \IPOD_Fetcher::scrape_data($ipo->id, $ipo->slug);

    if ($scraped_data && !isset($scraped_data['error']) && !empty($scraped_data['ipo_name'])) {
        $json_data = wp_json_encode($scraped_data, JSON_UNESCAPED_UNICODE);
        $t_details = $wpdb->prefix . 'ipodetails';

        $wpdb->replace($t_details, [
            "ipo_id" => $ipo->id,
            "slug" => $ipo->slug,
            "details_json" => $json_data,
            "fetched_at" => current_time("mysql"),
            "updated_at" => current_time("mysql"),
        ]);

        $details = $scraped_data;
    }
}


$name = $ipo->name;

// Calculations for Top Bar
$gmp = (float) $ipo->premium;
$price_max = (float) $ipo->max_price;
if ($price_max <= 0 && strpos($ipo->price_band, '-') !== false) {
    $parts = explode('-', $ipo->price_band);
    $price_max = (float) preg_replace('/[^0-9.]/', '', end($parts));
}
$gmp_perc = ($price_max > 0) ? round(($gmp / $price_max) * 100, 1) : 0;
$lot_size = (int) $ipo->lot_size;
$est_profit = $gmp * $lot_size;

// ... (Data calculations) ...

// Start Content (Header is already loaded)
?>
<main class="max-w-[1400px] mx-auto px-4 md:px-10 py-6">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 mb-6 text-xs font-bold uppercase tracking-widest text-slate-500">
        <span class="material-symbols-outlined text-sm">home</span>
        <a href="<?php echo home_url('/'); ?>" class="hover:text-primary transition-colors">Homepage</a>
        <span class="text-slate-700">/</span>
        <span class="text-slate-200"><?php echo esc_html($name); ?></span>
    </nav>

    <!-- Hero Card with Sticky Metrics -->
    <div class="glass-card border border-border-navy rounded-[24px] p-6 mb-6 sticky top-0 z-40">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">

            <!-- Left: Branding -->
            <div class="flex items-center gap-4">
                <div
                    class="size-16 rounded-xl bg-white p-2 flex items-center justify-center shadow-xl ring-2 ring-white/10 overflow-hidden shrink-0">
                    <?php if (!empty($ipo->icon_url)): ?>
                        <img src="<?php echo esc_url($ipo->icon_url); ?>" alt="<?php echo esc_attr($name); ?>"
                            class="w-full h-full object-contain" />
                    <?php else: ?>
                        <span class="text-2xl font-black text-slate-900"><?php echo substr($name, 0, 1); ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight mb-1 leading-none">
                        <?php echo esc_html($name); ?>
                    </h1>
                    <div class="flex flex-wrap gap-2 mt-2">
                        <span
                            class="px-2.5 py-1 bg-[#1E293B] text-slate-300 text-[9px] font-black uppercase tracking-widest rounded border border-slate-700">
                            <?php echo esc_html($ipo->status); ?>
                        </span>
                        <span
                            class="px-2.5 py-1 bg-[#0F3864] text-blue-300 text-[9px] font-black uppercase tracking-widest rounded border border-blue-500/30">
                            <?php echo $ipo->is_sme ? 'SME' : 'MAINBOARD'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right: Key Metrics -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 w-full lg:w-auto">
                <div class="text-center lg:text-left">
                    <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">GMP</p>
                    <p
                        class="text-2xl font-black <?php echo ($ipo->premium < 0) ? 'text-red-400' : 'text-emerald-400'; ?> leading-none">
                        <?php echo ($ipo->premium < 0) ? '-₹' . abs($ipo->premium) : '+₹' . ($ipo->premium ?: '0'); ?>
                    </p>
                    <p class="text-[10px] text-slate-400 mt-1">(<?php echo $gmp_perc; ?>%)</p>
                </div>
                <div class="text-center lg:text-left lg:border-l border-slate-700/50 lg:pl-6">
                    <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">Price</p>
                    <p class="text-lg font-black text-white leading-none">
                        <?php echo esc_html($ipo->price_band ?: 'TBA'); ?>
                    </p>
                </div>
                <div class="text-center lg:text-left lg:border-l border-slate-700/50 lg:pl-6">
                    <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">Min. Invest</p>
                    <p class="text-lg font-black text-white leading-none">
                        ₹<?php echo number_format($price_max * $lot_size); ?></p>
                    <p class="text-[10px] text-slate-400 mt-1"><?php echo $lot_size; ?> Shares</p>
                </div>
                <div class="text-center lg:text-left lg:border-l border-slate-700/50 lg:pl-6">
                    <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">Est. Profit</p>
                    <p class="text-2xl font-black text-white leading-none">₹<?php echo number_format($est_profit); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabbed Navigation -->
    <div class="glass-card border border-border-navy rounded-[20px] mb-6 overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <div class="flex gap-2 p-4 min-w-max">
                <button onclick="switchTab('overview')"
                    class="tab-btn active px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-widest transition-all"
                    data-tab="overview">
                    <span class="material-symbols-outlined text-base mr-2 align-middle">dashboard</span>Overview
                </button>
                <button onclick="switchTab('subscriptions')"
                    class="tab-btn px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-widest transition-all bg-slate-900/40 text-slate-400 hover:bg-slate-800"
                    data-tab="subscriptions">
                    <span class="material-symbols-outlined text-base mr-2 align-middle">leaderboard</span>Subscriptions
                </button>
                <button onclick="switchTab('financials')"
                    class="tab-btn px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-widest transition-all bg-slate-900/40 text-slate-400 hover:bg-slate-800"
                    data-tab="financials">
                    <span class="material-symbols-outlined text-base mr-2 align-middle">payments</span>Financials
                </button>
                <button onclick="switchTab('analysis')"
                    class="tab-btn px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-widest transition-all bg-slate-900/40 text-slate-400 hover:bg-slate-800"
                    data-tab="analysis">
                    <span class="material-symbols-outlined text-base mr-2 align-middle">analytics</span>Analysis
                </button>
                <button onclick="switchTab('reviews')"
                    class="tab-btn px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-widest transition-all bg-slate-900/40 text-slate-400 hover:bg-slate-800"
                    data-tab="reviews">
                    <span class="material-symbols-outlined text-base mr-2 align-middle">rate_review</span>Reviews
                </button>
            </div>
        </div>
    </div>

    <!-- Tab Content Container -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

        <!-- Main Content Area -->
        <div class="lg:col-span-3">

            <!-- OVERVIEW TAB -->
            <div id="tab-overview" class="tab-content fade-in space-y-6">

                <!-- Timeline Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <div
                        class="glass-card border border-border-navy rounded-xl p-5 hover:border-primary/40 transition-all">
                        <span class="text-[10px] uppercase tracking-widest font-black text-slate-500 block mb-2">Open
                            Date</span>
                        <span
                            class="text-base font-black text-white"><?php echo $ipo->open_date ? date('M j, Y', strtotime($ipo->open_date)) : 'TBA'; ?></span>
                    </div>
                    <div
                        class="glass-card border border-border-navy rounded-xl p-5 hover:border-primary/40 transition-all">
                        <span class="text-[10px] uppercase tracking-widest font-black text-slate-500 block mb-2">Close
                            Date</span>
                        <span
                            class="text-base font-black text-white"><?php echo $ipo->close_date ? date('M j, Y', strtotime($ipo->close_date)) : 'TBA'; ?></span>
                    </div>
                    <div
                        class="glass-card border border-border-navy rounded-xl p-5 hover:border-primary/40 transition-all">
                        <span class="text-[10px] uppercase tracking-widest font-black text-slate-500 block mb-2">IPO
                            Size</span>
                        <span
                            class="text-base font-black text-white">₹<?php echo esc_html($ipo->issue_size_cr ?: 'TBA'); ?>
                            cr</span>
                    </div>
                    <div
                        class="glass-card border border-border-navy rounded-xl p-5 hover:border-primary/40 transition-all">
                        <span class="text-[10px] uppercase tracking-widest font-black text-slate-500 block mb-2">Lot
                            Size</span>
                        <span
                            class="text-base font-black text-white"><?php echo esc_html($ipo->lot_size ?: 'TBA'); ?></span>
                    </div>
                    <div
                        class="glass-card border border-border-navy rounded-xl p-5 hover:border-primary/40 transition-all">
                        <span
                            class="text-[10px] uppercase tracking-widest font-black text-slate-500 block mb-2">Allotment</span>
                        <span
                            class="text-base font-black text-white"><?php echo $ipo->allotment_date ? date('M j, Y', strtotime($ipo->allotment_date)) : 'TBA'; ?></span>
                    </div>
                    <div
                        class="glass-card border border-border-navy rounded-xl p-5 hover:border-primary/40 transition-all">
                        <span
                            class="text-[10px] uppercase tracking-widest font-black text-slate-500 block mb-2">Listing</span>
                        <span
                            class="text-base font-black text-white"><?php echo $ipo->listing_date ? date('M j, Y', strtotime($ipo->listing_date)) : 'TBA'; ?></span>
                    </div>
                </div>

                <!-- Technical IPO Specs -->
                <?php if (isset($details['ipo_details']) && !empty($details['ipo_details'])): ?>
                    <div class="glass-card border border-border-navy rounded-2xl p-6">
                        <h2 class="text-lg font-bold flex items-center gap-2 mb-6">
                            <span class="material-symbols-outlined text-purple-accent text-xl">settings_suggest</span>
                            Technical IPO Specs
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php foreach ($details['ipo_details'] as $key => $val): ?>
                                <div
                                    class="flex justify-between items-center p-4 rounded-xl bg-slate-900/30 border border-white/5">
                                    <span
                                        class="text-[10px] font-black text-slate-500 uppercase tracking-widest"><?php echo esc_html($key); ?></span>
                                    <span class="text-sm font-bold text-slate-200"><?php echo esc_html($val); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- About Company -->
                <?php if (isset($details['about_company']) && !empty($details['about_company'])): ?>
                    <div class="glass-card border border-border-navy rounded-2xl p-6">
                        <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-xl">business</span>
                            About the Company
                        </h2>
                        <div class="text-slate-400 leading-relaxed text-sm font-medium prose prose-invert max-w-none">
                            <?php echo nl2br(esc_html($details['about_company'])); ?>
                        </div>
                        <?php if (!empty($details['address'])): ?>
                            <div class="mt-6 pt-6 border-t border-border-navy">
                                <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Registered
                                    Address</h3>
                                <p class="text-sm text-slate-300 italic"><?php echo esc_html($details['address']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Lead Managers -->
                <?php if (!empty($details['lead_managers'])): ?>
                    <div class="glass-card border border-border-navy rounded-2xl p-6">
                        <h2 class="text-lg font-bold flex items-center gap-2 mb-6">
                            <span class="material-symbols-outlined text-primary text-xl">groups</span>
                            Lead Managers
                        </h2>
                        <div class="flex flex-wrap gap-3">
                            <?php foreach ($details['lead_managers'] as $lm): ?>
                                <div
                                    class="flex items-center gap-2 bg-slate-900/50 border border-white/5 px-4 py-2.5 rounded-xl">
                                    <span class="size-2 rounded-full bg-primary"></span>
                                    <span class="text-sm font-bold text-slate-200"><?php echo esc_html($lm['name']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- SUBSCRIPTIONS TAB -->
            <div id="tab-subscriptions" class="tab-content hidden space-y-6">

                <!-- Subscription Status (Live) -->
                <div class="glass-card border border-border-navy rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-bold flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-xl">signal_cellular_alt</span>
                            Live Subscription Status
                        </h2>
                        <span
                            class="bg-neon-emerald text-[#050A18] text-[9px] font-black px-3 py-1 rounded uppercase tracking-wider shadow-[0_0_10px_rgba(0,255,148,0.3)]">Live</span>
                    </div>
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left">
                            <thead>
                                <tr
                                    class="text-[9px] font-black text-slate-500 uppercase tracking-widest border-b border-border-navy">
                                    <th class="pb-3 px-2">Investor Category</th>
                                    <th class="pb-3 px-2 text-center">Offered</th>
                                    <th class="pb-3 px-2 text-center">Applied</th>
                                    <th class="pb-3 px-2 text-right">Times Subscribed</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-navy">
                                <?php if (isset($details['subscription']) && !empty($details['subscription'])):
                                    foreach ($details['subscription'] as $s): ?>
                                        <tr class="text-sm font-bold data-table-row">
                                            <td class="py-3 px-2 text-slate-300">
                                                <?php echo esc_html($s['Category'] ?? $s['Investor Category'] ?? '-'); ?>
                                            </td>
                                            <td class="py-3 px-2 text-center text-slate-500 font-medium">
                                                <?php echo esc_html($s['Offered'] ?: '-'); ?>
                                            </td>
                                            <td class="py-3 px-2 text-center text-slate-500 font-medium">
                                                <?php echo esc_html($s['Applied'] ?: '-'); ?>
                                            </td>
                                            <td class="py-3 px-2 text-right text-neon-emerald">
                                                <?php echo esc_html($s['Times'] ?? $s['Times Subscribed'] ?? '0.00'); ?>x
                                            </td>
                                        </tr>
                                    <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-slate-600 text-[10px] font-bold">
                                            Subscription data processing...</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Subscription Demand & QIB Interest -->
                <?php if (!empty($details['subscription_demand']) || !empty($details['qib_interest'])): ?>

                    <!-- Subscription Demand -->
                    <?php if (!empty($details['subscription_demand'])): ?>
                        <div class="glass-card border border-border-navy rounded-2xl p-6">
                            <h2 class="text-lg font-bold flex items-center gap-2 mb-6">
                                <span class="material-symbols-outlined text-blue-400 text-xl">trending_up</span>
                                Subscription Demand
                            </h2>
                            <div class="overflow-x-auto custom-scrollbar">
                                <table class="w-full text-left">
                                    <thead>
                                        <tr class="text-[9px] font-black text-slate-500 uppercase border-b border-border-navy">
                                            <?php
                                            $headers = array_keys($details['subscription_demand'][0]);
                                            foreach ($headers as $h): ?>
                                                <th class="pb-3 px-2"><?php echo esc_html($h); ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border-navy">
                                        <?php foreach ($details['subscription_demand'] as $row): ?>
                                            <tr class="font-bold data-table-row">
                                                <?php foreach ($headers as $h): ?>
                                                    <td class="py-3 px-2 text-slate-300 text-sm">
                                                        <?php echo esc_html($row[$h] ?: '-'); ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- QIB Interest -->
                    <?php if (!empty($details['qib_interest'])): ?>
                        <div class="glass-card border border-border-navy rounded-2xl p-6">
                            <h2 class="text-lg font-bold flex items-center gap-2 mb-6">
                                <span class="material-symbols-outlined text-neon-emerald text-xl">account_balance</span>
                                QIB Interest (Anchor Investors)
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <?php foreach ($details['qib_interest'] as $qib): ?>
                                    <div
                                        class="flex items-center gap-3 p-3 bg-slate-900/50 border border-white/5 rounded-xl hover:border-neon-emerald/30 transition-all">
                                        <div class="flex items-center justify-center size-8 rounded-lg bg-neon-emerald/10 shrink-0">
                                            <span class="material-symbols-outlined text-neon-emerald text-sm">corporate_fare</span>
                                        </div>
                                        <span
                                            class="text-sm font-bold text-slate-300 leading-tight"><?php echo esc_html($qib); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>

                <!-- Application Breakup -->
                <?php if (isset($details['application_breakup']) && !empty($details['application_breakup'])): ?>
                    <div class="glass-card border border-border-navy rounded-2xl p-6">
                        <h2 class="text-lg font-bold flex items-center gap-2 mb-6">
                            <span class="material-symbols-outlined text-purple-accent text-xl">pie_chart</span>
                            Application-Wise Breakup
                        </h2>
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-left">
                                <thead>
                                    <tr
                                        class="text-[9px] font-black text-slate-500 uppercase tracking-widest border-b border-border-navy">
                                        <th class="pb-3 px-2">Category</th>
                                        <th class="pb-3 px-2 text-center">Reserved</th>
                                        <th class="pb-3 px-2 text-center">Applied</th>
                                        <th class="pb-3 px-2 text-right">Times</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border-navy">
                                    <?php foreach ($details['application_breakup'] as $ab): ?>
                                        <tr class="text-sm font-bold data-table-row">
                                            <td class="py-3 px-2 text-slate-300"><?php echo esc_html($ab['Category'] ?? '-'); ?>
                                            </td>
                                            <td class="py-3 px-2 text-center text-slate-200">
                                                <?php echo esc_html($ab['Reserved'] ?? '-'); ?>
                                            </td>
                                            <td class="py-3 px-2 text-center text-slate-500 font-medium">
                                                <?php echo esc_html($ab['Applied'] ?? '-'); ?>
                                            </td>
                                            <td class="py-3 px-2 text-right text-primary">
                                                <?php echo esc_html($ab['Times'] ?? '-'); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Lot Distribution -->
                <?php if (isset($details['lot_distribution']) && !empty($details['lot_distribution'])): ?>
                    <div class="glass-card border border-border-navy rounded-2xl p-6">
                        <h2 class="text-lg font-bold flex items-center gap-2 mb-6">
                            <span class="material-symbols-outlined text-blue-400 text-xl">grid_view</span>
                            Bid-wise Lot Distribution
                        </h2>
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-left">
                                <thead>
                                    <tr
                                        class="text-[9px] font-black text-slate-500 uppercase tracking-widest border-b border-border-navy">
                                        <th class="pb-3 px-2">Category</th>
                                        <th class="pb-3 px-2 text-center">Lot(s)</th>
                                        <th class="pb-3 px-2 text-center">Shares</th>
                                        <th class="pb-3 px-2 text-right">Amount (₹)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border-navy">
                                    <?php foreach ($details['lot_distribution'] as $lot): ?>
                                        <tr class="text-sm font-bold data-table-row">
                                            <td class="py-3 px-2 text-slate-300">
                                                <?php echo esc_html($lot['Category'] ?? '-'); ?>
                                            </td>
                                            <td class="py-3 px-2 text-center text-slate-200">
                                                <?php echo esc_html($lot['Lot(s)'] ?? '-'); ?>
                                            </td>
                                            <td class="py-3 px-2 text-center text-slate-500">
                                                <?php echo esc_html($lot['Qty'] ?? $lot['Shares'] ?? '-'); ?>
                                            </td>
                                            <td class="py-3 px-2 text-right text-primary">
                                                <?php echo esc_html($lot['Amount'] ?? '-'); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- FINANCIALS TAB -->
            <div id="tab-financials" class="tab-content hidden space-y-6">

                <?php
                $has_financials = !empty($details['company_financials']) || !empty($details['kpi']) || !empty($details['peer_valuation']) || !empty($details['peer_financials']);
                if ($has_financials):
                    ?>

                    <!-- Company Financials -->
                    <?php if (!empty($details['company_financials'])): ?>
                        <div class="glass-card border border-border-navy rounded-2xl p-6">
                            <h2 class="text-lg font-bold flex items-center gap-2 mb-6">
                                <span class="material-symbols-outlined text-purple-accent text-xl">account_balance_wallet</span>
                                Company Financials
                            </h2>
                            <div class="overflow-x-auto custom-scrollbar">
                                <table class="w-full text-left">
                                    <thead>
                                        <tr
                                            class="text-[9px] font-black text-slate-500 uppercase tracking-widest border-b border-border-navy">
                                            <?php
                                            $headers = array_keys($details['company_financials'][0]);
                                            foreach ($headers as $h): ?>
                                                <th class="pb-3 px-2"><?php echo esc_html(strtoupper($h)); ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border-navy">
                                        <?php foreach ($details['company_financials'] as $row): ?>
                                            <tr class="text-sm font-bold data-table-row">
                                                <?php foreach ($headers as $h): ?>
                                                    <td class="py-3 px-2 text-slate-300"><?php echo esc_html($row[$h] ?: '-'); ?></td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- KPI Metrics -->
                    <?php if (!empty($details['kpi'])): ?>
                        <div class="glass-card border border-border-navy rounded-2xl p-6">
                            <h2 class="text-lg font-bold flex items-center gap-2 mb-6">
                                <span class="material-symbols-outlined text-neon-emerald text-xl">analytics</span>
                                Key Performance Indicators
                            </h2>
                            <div class="overflow-x-auto custom-scrollbar">
                                <table class="w-full text-left">
                                    <thead>
                                        <tr
                                            class="text-[9px] font-black text-slate-500 uppercase tracking-widest border-b border-border-navy">
                                            <?php
                                            $headers = array_keys($details['kpi'][0]);
                                            foreach ($headers as $h): ?>
                                                <th class="pb-3 px-2"><?php echo esc_html(strtoupper($h)); ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border-navy">
                                        <?php foreach ($details['kpi'] as $row): ?>
                                            <tr class="text-sm font-bold data-table-row">
                                                <?php foreach ($headers as $h): ?>
                                                    <td class="py-3 px-2 text-slate-300"><?php echo esc_html($row[$h] ?: '-'); ?></td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Peer Comparisons -->
                    <?php if (!empty($details['peer_valuation']) || !empty($details['peer_financials'])): ?>
                        <div class="space-y-6">
                            <?php if (!empty($details['peer_valuation'])): ?>
                                <div class="glass-card border border-border-navy rounded-2xl p-6">
                                    <h2 class="text-lg font-bold flex items-center gap-2 mb-6">
                                        <span class="material-symbols-outlined text-primary text-xl">compare_arrows</span>
                                        Peer Comparison (Valuation)
                                    </h2>
                                    <div class="overflow-x-auto custom-scrollbar">
                                        <table class="w-full text-left">
                                            <thead>
                                                <tr
                                                    class="text-[9px] font-black text-slate-500 uppercase tracking-widest border-b border-border-navy">
                                                    <?php
                                                    $headers = array_keys($details['peer_valuation'][0]);
                                                    foreach ($headers as $h): ?>
                                                        <th class="pb-3 px-2"><?php echo esc_html(strtoupper($h)); ?></th>
                                                    <?php endforeach; ?>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-border-navy">
                                                <?php foreach ($details['peer_valuation'] as $row): ?>
                                                    <tr class="text-sm font-bold data-table-row">
                                                        <?php foreach ($headers as $h): ?>
                                                            <td class="py-3 px-2 text-slate-300"><?php echo esc_html($row[$h] ?: '-'); ?>
                                                            </td>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($details['peer_financials'])): ?>
                                <div class="glass-card border border-border-navy rounded-2xl p-6">
                                    <h2 class="text-lg font-bold flex items-center gap-2 mb-6">
                                        <span class="material-symbols-outlined text-purple-accent text-xl">payments</span>
                                        Peer Comparison (Financials)
                                    </h2>
                                    <div class="overflow-x-auto custom-scrollbar">
                                        <table class="w-full text-left">
                                            <thead>
                                                <tr
                                                    class="text-[9px] font-black text-slate-500 uppercase tracking-widest border-b border-border-navy">
                                                    <?php
                                                    $headers = array_keys($details['peer_financials'][0]);
                                                    foreach ($headers as $h): ?>
                                                        <th class="pb-3 px-2"><?php echo esc_html(strtoupper($h)); ?></th>
                                                    <?php endforeach; ?>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-border-navy">
                                                <?php foreach ($details['peer_financials'] as $row): ?>
                                                    <tr class="text-sm font-bold data-table-row">
                                                        <?php foreach ($headers as $h): ?>
                                                            <td class="py-3 px-2 text-slate-300"><?php echo esc_html($row[$h] ?: '-'); ?>
                                                            </td>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="glass-card border border-border-navy rounded-2xl p-12 text-center">
                        <div
                            class="inline-flex items-center justify-center size-16 rounded-full bg-slate-900/50 mb-4 border border-white/5">
                            <span class="material-symbols-outlined text-3xl text-slate-600">account_balance_wallet</span>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Financial Data Not Available</h3>
                        <p class="text-xs text-slate-400 max-w-xs mx-auto">We don't have financial records for this company
                            yet.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ANALYSIS TAB -->
            <div id="tab-analysis" class="tab-content hidden space-y-6">

                <!-- Strengths & Weaknesses -->
                <?php
                $has_analysis = !empty($details['strengths']) || !empty($details['strengths_text']) || !empty($details['weaknesses']) || !empty($details['weaknesses_text']);
                if ($has_analysis):
                    ?>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Strengths -->
                        <div class="glass-card border border-border-navy rounded-2xl p-6">
                            <h2
                                class="text-lg font-black flex items-center gap-2 mb-6 pb-3 border-b border-neon-emerald/20">
                                <div class="flex items-center justify-center size-10 rounded-xl bg-neon-emerald/10">
                                    <span class="material-symbols-outlined text-neon-emerald text-xl">verified</span>
                                </div>
                                <span class="text-white">Strengths</span>
                            </h2>
                            <?php if (!empty($details['strengths'])): ?>
                                <ul class="space-y-4">
                                    <?php foreach ($details['strengths'] as $index => $s): ?>
                                        <li class="flex gap-4 group">
                                            <div
                                                class="flex items-start justify-center size-7 mt-0.5 rounded-lg bg-neon-emerald/10 shrink-0 font-black text-neon-emerald text-xs border border-neon-emerald/20 group-hover:bg-neon-emerald/20 transition-all">
                                                <?php echo ($index + 1); ?>
                                            </div>
                                            <p class="text-sm font-medium text-slate-300 leading-relaxed">
                                                <?php echo esc_html($s); ?>
                                            </p>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php elseif (!empty($details['strengths_text'])): ?>
                                <div class="text-sm font-medium text-slate-300 leading-relaxed space-y-3">
                                    <?php
                                    $paragraphs = explode("\n", $details['strengths_text']);
                                    foreach ($paragraphs as $para):
                                        if (trim($para)):
                                            ?>
                                            <p class="pl-4 border-l-2 border-neon-emerald/30"><?php echo esc_html($para); ?></p>
                                            <?php
                                        endif;
                                    endforeach;
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Weaknesses -->
                        <div class="glass-card border border-border-navy rounded-2xl p-6">
                            <h2 class="text-lg font-black flex items-center gap-2 mb-6 pb-3 border-b border-red-400/20">
                                <div class="flex items-center justify-center size-10 rounded-xl bg-red-400/10">
                                    <span class="material-symbols-outlined text-red-400 text-xl">warning</span>
                                </div>
                                <span class="text-white">Weaknesses</span>
                            </h2>
                            <?php if (!empty($details['weaknesses'])): ?>
                                <ul class="space-y-4">
                                    <?php foreach ($details['weaknesses'] as $index => $w): ?>
                                        <li class="flex gap-4 group">
                                            <div
                                                class="flex items-start justify-center size-7 mt-0.5 rounded-lg bg-red-400/10 shrink-0 font-black text-red-400 text-xs border border-red-400/20 group-hover:bg-red-400/20 transition-all">
                                                <?php echo ($index + 1); ?>
                                            </div>
                                            <p class="text-sm font-medium text-slate-300 leading-relaxed">
                                                <?php echo esc_html($w); ?>
                                            </p>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php elseif (!empty($details['weaknesses_text'])): ?>
                                <div class="text-sm font-medium text-slate-300 leading-relaxed space-y-3">
                                    <?php
                                    $paragraphs = explode("\n", $details['weaknesses_text']);
                                    foreach ($paragraphs as $para):
                                        if (trim($para)):
                                            ?>
                                            <p class="pl-4 border-l-2 border-red-400/30"><?php echo esc_html($para); ?></p>
                                            <?php
                                        endif;
                                    endforeach;
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="glass-card border border-border-navy rounded-2xl p-12 text-center">
                        <div
                            class="inline-flex items-center justify-center size-16 rounded-full bg-slate-900/50 mb-4 border border-white/5">
                            <span class="material-symbols-outlined text-3xl text-slate-600">analytics</span>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Analysis Pending</h3>
                        <p class="text-xs text-slate-400 max-w-xs mx-auto">Our experts are currently analyzing this IPO.
                            Please check back later for Strengths & Weaknesses.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- REVIEWS TAB -->
            <div id="tab-reviews" class="tab-content hidden space-y-6">

                <!-- Broker Recommendations -->
                <?php if (!empty($details['reviewers'])): ?>
                    <div class="glass-card border border-border-navy rounded-2xl p-6">
                        <h2 class="text-lg font-bold flex items-center gap-2 mb-6">
                            <span class="material-symbols-outlined text-primary text-xl">rate_review</span>
                            Broker Recommendations
                        </h2>
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-left">
                                <thead>
                                    <tr
                                        class="text-[9px] font-black text-slate-500 uppercase tracking-widest border-b border-border-navy">
                                        <?php
                                        $headers = array_keys($details['reviewers'][0]);
                                        foreach ($headers as $h): ?>
                                            <th class="pb-3 px-2"><?php echo esc_html(strtoupper($h)); ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border-navy">
                                    <?php foreach ($details['reviewers'] as $row): ?>
                                        <tr class="text-sm font-bold data-table-row">
                                            <?php foreach ($headers as $h): ?>
                                                <td class="py-3 px-2 text-slate-300"><?php echo esc_html($row[$h] ?: '-'); ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="glass-card border border-border-navy rounded-2xl p-12 text-center">
                        <div
                            class="inline-flex items-center justify-center size-16 rounded-full bg-slate-900/50 mb-4 border border-white/5">
                            <span class="material-symbols-outlined text-3xl text-slate-600">rate_review</span>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">No Reviews Found</h3>
                        <p class="text-xs text-slate-400 max-w-xs mx-auto">We couldn't find any broker recommendations or
                            reviews for this IPO at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- RIGHT SIDEBAR -->
        <div class="space-y-6">

            <!-- Quota Reservation -->
            <div class="glass-card border border-border-navy rounded-2xl p-6">
                <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-6">Quota Reservation</h3>
                <div class="space-y-4">
                    <?php if (isset($details['reservation']) && !empty($details['reservation'])):
                        foreach ($details['reservation'] as $r): ?>
                            <div class="flex items-center justify-between group">
                                <span
                                    class="text-xs font-bold text-slate-400"><?php echo esc_html($r['Category'] ?: '-'); ?></span>
                                <span
                                    class="text-[11px] font-black text-white bg-slate-900/80 border border-white/5 px-2.5 py-1.5 rounded-lg min-w-[55px] text-center shadow-sm group-hover:border-primary/30 transition-colors">
                                    <?php echo esc_html($r['%'] ?: '0%'); ?>
                                </span>
                            </div>
                        <?php endforeach; else: ?>
                        <div class="text-center py-4">
                            <span class="text-[10px] text-slate-500 font-bold italic">Quota information not available</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Registrar -->
            <div class="glass-card border border-border-navy rounded-2xl p-6">
                <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-6">Registrar</h3>
                <div
                    class="bg-slate-900/40 border border-white/5 p-4 rounded-xl flex flex-col gap-4 group hover:border-primary/20 transition-all">
                    <div class="flex items-center gap-3">
                        <div
                            class="size-10 rounded-lg bg-white p-1.5 flex items-center justify-center font-black text-slate-900 shadow-inner shrink-0">
                            <?php
                            $reg_name = $details['registrar_name'] ?? $details['ipo_details']['Registrar'] ?? 'Official';
                            echo substr($reg_name, 0, 1);
                            ?>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-black text-white leading-tight">
                                <?php echo esc_html($reg_name); ?>
                            </p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <?php if (!empty($details['registrar_phone'])): ?>
                            <div class="flex items-center gap-2 text-[10px] text-slate-400">
                                <span class="material-symbols-outlined text-xs">call</span>
                                <?php echo esc_html($details['registrar_phone']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($details['registrar_email'])): ?>
                            <div class="flex items-center gap-2 text-[10px] text-slate-400">
                                <span class="material-symbols-outlined text-xs">mail</span>
                                <a href="mailto:<?php echo esc_attr($details['registrar_email']); ?>"
                                    class="hover:text-primary transition-colors"><?php echo esc_html($details['registrar_email']); ?></a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($details['registrar_url'])): ?>
                        <a href="<?php echo esc_url($details['registrar_url']); ?>" target="_blank"
                            class="w-full py-2.5 bg-[#1E293B] hover:bg-primary text-white text-[10px] font-black text-center uppercase tracking-[0.1em] rounded-lg transition-all shadow-md">Check
                            Allotment Status</a>
                    <?php else: ?>
                        <button
                            class="w-full py-2.5 bg-[#1E293B] hover:bg-primary text-white text-[10px] font-black uppercase tracking-[0.1em] rounded-lg transition-all shadow-md">Check
                            Allotment Status</button>
                    <?php endif; ?>
                </div>
            </div>



            <!-- Broker Widget -->
            <?php include TIGC_PATH . 'partials/widget-brokers.php'; ?>
        </div>
    </div>
</main>

<script>
    function switchTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.add('hidden');
            tab.classList.remove('fade-in');
        });

        // Show selected tab
        const selectedTab = document.getElementById('tab-' + tabName);
        if (selectedTab) {
            selectedTab.classList.remove('hidden');
            setTimeout(() => selectedTab.classList.add('fade-in'), 10);
        }

        // Update button states
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
            btn.classList.add('bg-slate-900/40', 'text-slate-400', 'hover:bg-slate-800');
        });

        const activeBtn = document.querySelector(`[data-tab="${tabName}"]`);
        if (activeBtn) {
            activeBtn.classList.add('active');
            activeBtn.classList.remove('bg-slate-900/40', 'text-slate-400', 'hover:bg-slate-800');
        }
    }
</script>

<?php include TIGC_PATH . 'partials/footer-premium.php'; ?>
</body>

</html>