<?php
/**
 * Template Name: IPO Details Template (Premium Tabbed UI)
 * Description: Premium tabbed interface with glassmorphism design for comprehensive IPO data.
 */

get_header();

global $wpdb;
$t_master = $wpdb->prefix . 'ipomaster';
$t_details = $wpdb->prefix . 'ipodetails';

$slug = isset($_GET['slug']) ? sanitize_text_field($_GET['slug']) : '';

if (empty($slug)) {
    status_header(404);
    nocache_headers();
    include(get_query_template('404'));
    exit;
}

$ipo = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t_master WHERE slug = %s", $slug));

if (!$ipo) {
    status_header(404);
    nocache_headers();
    include(get_query_template('404'));
    exit;
}

$details_row = $wpdb->get_row($wpdb->prepare("SELECT details_json FROM $t_details WHERE slug = %s OR ipo_id = %d", $slug, $ipo->id));
$details = $details_row ? json_decode($details_row->details_json, true) : null;

$name = $ipo->name;
$gmp = (float) $ipo->premium;
$price_max = (float) $ipo->max_price;
if ($price_max <= 0 && strpos($ipo->price_band, '-') !== false) {
    $parts = explode('-', $ipo->price_band);
    $price_max = (float) preg_replace('/[^0-9.]/', '', end($parts));
}
$gmp_perc = ($price_max > 0) ? round(($gmp / $price_max) * 100, 1) : 0;
$lot_size = (int) $ipo->lot_size;
$est_profit = $gmp * $lot_size;
?>

<main class="max-w-[1400px] mx-auto px-4 md:px-10 py-4">

    <nav
        class="flex items-center gap-2 mb-6 text-[10px] md:text-xs font-bold uppercase tracking-widest text-slate-500 overflow-x-auto whitespace-nowrap hide-scrollbar">
        <span class="material-symbols-outlined text-sm">home</span>
        <a href="<?php echo home_url('/'); ?>" class="hover:text-primary transition-colors">Homepage</a>
        <span class="text-slate-700">/</span>
        <span class="text-slate-200"><?php echo esc_html($name); ?></span>
    </nav>

    <div class="glass-card border border-border-navy rounded-[24px] p-5 md:p-6 mb-6  md:top-4 z-40 shadow-2xl">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">

            <div class="flex items-center gap-4 w-full lg:w-auto">
                <div
                    class="size-14 md:size-16 rounded-xl bg-white p-2 flex items-center justify-center shadow-xl ring-2 ring-white/10 overflow-hidden shrink-0">
                    <?php if (!empty($ipo->icon_url)): ?>
                        <img src="<?php echo esc_url($ipo->icon_url); ?>" alt="<?php echo esc_attr($name); ?>"
                            class="w-full h-full object-contain" />
                    <?php else: ?>
                        <span class="text-xl font-black text-slate-900"><?php echo substr($name, 0, 1); ?></span>
                    <?php endif; ?>
                </div>
                <div class="min-w-0">
                    <h1 class="text-xl md:text-2xl font-black text-white tracking-tight mb-1 truncate leading-tight">
                        <?php echo esc_html($name); ?>
                    </h1>
                    <div class="flex flex-wrap gap-2 mt-1">
                        <span
                            class="px-2 py-0.5 bg-slate-800 text-slate-300 text-[10px] font-bold uppercase rounded border border-slate-700">
                            <?php echo esc_html($ipo->status); ?>
                        </span>
                        <span
                            class="px-2 py-0.5 bg-blue-900/40 text-blue-300 text-[10px] font-bold uppercase rounded border border-blue-500/30">
                            <?php echo $ipo->is_sme ? 'SME' : 'MAINBOARD'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <div
                class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-8 w-full lg:w-auto pt-4 md:pt-0 border-t md:border-t-0 border-slate-800">
                <div class="flex flex-col">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">GMP Today</p>
                    <p
                        class="text-xl md:text-2xl font-black <?php echo ($ipo->premium < 0) ? 'text-red-400' : 'text-emerald-400'; ?> leading-none">
                        <?php echo ($ipo->premium < 0) ? '-₹' . abs($ipo->premium) : '+₹' . ($ipo->premium ?: '0'); ?>
                    </p>
                    <p class="text-[11px] font-bold text-slate-400 mt-1">(<?php echo $gmp_perc; ?>%)</p>
                </div>

                <div class="flex flex-col md:border-l border-slate-700/50 md:pl-6">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Price Band</p>
                    <p class="text-lg font-black text-white leading-none">
                        <?php echo esc_html($ipo->price_band ?: 'TBA'); ?>
                    </p>
                </div>

                <div class="flex flex-col md:border-l border-slate-700/50 md:pl-6">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Min. Invest</p>
                    <p class="text-lg font-black text-white leading-none">
                        ₹<?php echo number_format($price_max * $lot_size); ?>
                    </p>
                    <p class="text-[11px] text-slate-400 mt-1"><?php echo $lot_size; ?> Shares</p>
                </div>

                <div class="flex flex-col md:border-l border-slate-700/50 md:pl-6">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Est. Profit</p>
                    <p class="text-xl md:text-2xl font-black text-white leading-none">
                        ₹<?php echo number_format($est_profit); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="glass-card border border-border-navy rounded-[20px] mb-6 overflow-hidden">
        <div class="overflow-x-auto hide-scrollbar">
            <div class="flex gap-2 p-3 min-w-max">
                <?php
                $tabs = [
                    'overview' => ['icon' => 'dashboard', 'label' => 'Overview'],
                    'subscriptions' => ['icon' => 'leaderboard', 'label' => 'Live Sub.'],
                    'financials' => ['icon' => 'payments', 'label' => 'Financials'],
                    'analysis' => ['icon' => 'analytics', 'label' => 'Analysis'],
                    'reviews' => ['icon' => 'rate_review', 'label' => 'Reviews']
                ];
                foreach ($tabs as $id => $tab): ?>
                    <button onclick="switchTab('<?php echo $id; ?>')"
                        class="tab-btn <?php echo $id === 'overview' ? 'active' : 'bg-slate-900/40 text-slate-400 hover:bg-slate-800'; ?> px-5 py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all"
                        data-tab="<?php echo $id; ?>">
                        <span
                            class="material-symbols-outlined text-sm mr-2 align-middle"><?php echo $tab['icon']; ?></span><?php echo $tab['label']; ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-3">
            <div id="tab-overview" class="tab-content fade-in space-y-6">
                <?php if (isset($details['about_company'])): ?>
                    <div class="glass-card border border-border-navy rounded-2xl p-5 md:p-6">
                        <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-lg">business</span>
                            About the Company
                        </h2>
                        <div class="text-slate-300 leading-relaxed text-sm font-medium prose prose-invert max-w-none">
                            <?php echo nl2br(esc_html($details['about_company'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div id="tab-subscriptions" class="tab-content hidden space-y-6"></div>
            <div id="tab-financials" class="tab-content hidden space-y-6"></div>
            <div id="tab-analysis" class="tab-content hidden space-y-6"></div>
            <div id="tab-reviews" class="tab-content hidden space-y-6"></div>
        </div>

        <div class="space-y-6">
            <div class="glass-card border border-border-navy rounded-2xl p-6">
                <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-4">Registrar</h3>
                <div class="bg-slate-900/40 border border-white/5 p-4 rounded-xl flex flex-col gap-4">
                    <p class="text-xs font-black text-white leading-tight">
                        <?php echo esc_html($details['registrar_name'] ?? 'Official Registrar'); ?>
                    </p>
                    <a href="<?php echo esc_url($details['registrar_url'] ?? '#'); ?>" target="_blank"
                        class="w-full py-3 bg-primary hover:bg-primary-dark text-white text-xs font-black text-center uppercase tracking-widest rounded-lg transition-all block">
                        Check Allotment Status
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    /* Utility to hide horizontal scrollbar */
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Active tab style */
    .tab-btn.active {
        background: #3B82F6 !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
</style>

<script>
    function switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.add('hidden');
            tab.classList.remove('fade-in');
        });

        const selectedTab = document.getElementById('tab-' + tabName);
        if (selectedTab) {
            selectedTab.classList.remove('hidden');
            setTimeout(() => selectedTab.classList.add('fade-in'), 10);
        }

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

<?php get_footer(); ?>