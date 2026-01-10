<?php
/**
 * Template Name: IPO Details Template (Legacy Design)
 * Description: Recreating the original vibrant design with refined font sizes and full content.
 */

global $wpdb;
$slug = isset($_GET['slug']) ? sanitize_text_field($_GET['slug']) : '';
$t_master = $wpdb->prefix . 'ipomaster';
$t_details = $wpdb->prefix . 'ipodetails';

// Fetch Base IPO Data
$ipo = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t_master WHERE slug = %s", $slug));

if (!$ipo) {
    status_header(404);
    nocache_headers();
    include(get_query_template('404'));
    exit;
}

// Fetch Detailed Content
$details_row = $wpdb->get_row($wpdb->prepare("SELECT details_json FROM $t_details WHERE slug = %s OR ipo_id = %d", $slug, $ipo->id));
$details = $details_row ? json_decode($details_row->details_json, true) : null;

$name = $ipo->name;

// Calculations for Top Bar
$gmp = (float)$ipo->premium;
$price_max = (float)$ipo->max_price;
if ($price_max <= 0 && strpos($ipo->price_band, '-') !== false) {
    $parts = explode('-', $ipo->price_band);
    $price_max = (float)preg_replace('/[^0-9.]/', '', end($parts));
}
$gmp_perc = ($price_max > 0) ? round(($gmp / $price_max) * 100, 1) : 0;
$lot_size = (int)$ipo->lot_size;
$est_profit = $gmp * $lot_size;

?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo esc_html($name); ?> - IPO Details</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: { 
                        "primary": "#0d7ff2", 
                        "background-dark": "#050A18", 
                        "card-dark": "#0B1220",
                        "border-navy": "#1E293B", 
                        "neon-emerald": "#00FF94", 
                        "purple-accent": "#A855F7" 
                    },
                    fontFamily: { "display": ["Inter", "sans-serif"] }
                }
            }
        }
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #1E293B; border-radius: 10px; }
        .data-table-row:hover { background-color: rgba(13, 127, 242, 0.05); }
    </style>
</head>
<body class="bg-[#050A18] text-slate-100 min-h-screen font-display antialiased">
<?php include TIGC_PATH . 'partials/header-premium.php'; ?>

<main class="max-w-[1280px] mx-auto px-4 md:px-10 py-6">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 mb-6 text-xs font-bold uppercase tracking-widest text-slate-500">
        <span class="material-symbols-outlined text-sm">home</span>
        <a href="<?php echo home_url('/'); ?>" class="hover:text-primary transition-colors">Homepage</a>
        <span class="text-slate-700">/</span>
        <span class="text-slate-200"><?php echo esc_html($name); ?></span>
    </nav>

    <!-- Top Hero Card (Compact 50-50 Split) -->
    <div class="bg-[#050B14] border border-border-navy rounded-[20px] p-0 overflow-hidden relative mb-6">
        <div class="flex flex-col lg:flex-row items-stretch">
            
            <!-- Left: Branding (50%) -->
            <div class="w-full lg:w-1/2 p-5 lg:p-6 flex flex-col justify-center border-b lg:border-b-0 lg:border-r border-border-navy">
                <div class="flex items-center gap-4">
                    <div class="size-16 rounded-xl bg-white p-2 flex items-center justify-center shadow-xl ring-2 ring-white/5 overflow-hidden shrink-0">
                        <?php if(!empty($ipo->icon_url)): ?>
                            <img src="<?php echo esc_url($ipo->icon_url); ?>" alt="<?php echo esc_attr($name); ?>" class="w-full h-full object-contain" />
                        <?php else: ?>
                            <span class="text-2xl font-black text-slate-900"><?php echo substr($name, 0, 1); ?></span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h1 class="text-xl md:text-2xl lg:text-3xl font-black text-white tracking-tighter mb-2 leading-tight">
                            <?php echo esc_html($name); ?>
                        </h1>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-2 py-0.5 bg-[#1E293B] text-slate-300 text-[10px] font-black uppercase tracking-widest rounded border border-slate-700">
                                <?php echo esc_html($ipo->status); ?>
                            </span>
                            <span class="px-2 py-0.5 bg-[#0F3864] text-blue-300 text-[10px] font-black uppercase tracking-widest rounded border border-blue-500/30">
                                <?php echo $ipo->is_sme ? 'SME' : 'MAINBOARD'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Metrics Container (50%) -->
            <div class="w-full lg:w-1/2 bg-[#080E1A] p-5 lg:p-6 grid grid-cols-2 gap-x-6 gap-y-4 items-center relative">
                
                <!-- Decorator Lines -->
                <div class="absolute inset-x-6 top-1/2 h-px bg-slate-800/50 hidden lg:block"></div>
                <div class="absolute inset-y-6 left-1/2 w-px bg-slate-800/50 hidden lg:block"></div>

                <!-- Expectation -->
                <div class="relative z-10">
                    <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1 flex items-center gap-1.5">
                        <span class="size-1.5 rounded-full bg-emerald-500"></span> Expected GMP
                    </p>
                    <p class="text-2xl font-black text-emerald-400 flex flex-col items-start gap-0.5 leading-none">
                        +₹<?php echo $ipo->premium ?: '0'; ?> 
                    </p>
                </div>

                <!-- Est Profit -->
                <div class="relative z-10 pl-4 lg:pl-6 border-l border-slate-800/50 lg:border-0">
                    <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">Est. Profit</p>
                    <p class="text-xl lg:text-2xl font-black text-white leading-none">
                        ₹<?php echo number_format($est_profit); ?>
                    </p>
                </div>

                <!-- Min Investment -->
                <?php $min_invest = $price_max * $lot_size; ?>
                <div class="relative z-10 pt-4 lg:pt-0 border-t border-slate-800/50 lg:border-0">
                    <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">Min. Invest</p>
                    <p class="text-xl lg:text-2xl font-black text-white leading-none">
                        ₹<?php echo number_format($min_invest); ?>
                    </p>
                </div>

                <!-- Price Band -->
                <div class="relative z-10 pl-4 lg:pl-6 pt-4 lg:pt-0 border-l lg:border-l-0 border-t lg:border-t-0 border-slate-800/50">
                    <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">Price Band</p>
                    <p class="text-lg lg:text-xl font-black text-white leading-none">
                        <?php echo esc_html($ipo->price_band ?: 'TBA'); ?>
                    </p>
                </div>

            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
        
        <!-- Left Column: Data Grid & Main Content -->
        <div class="lg:col-span-3 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                <!-- 6 Data Cards (Tightened) -->
                <div class="p-4 rounded-xl bg-[#0B1220] border border-border-navy flex flex-col gap-1.5 hover:border-primary/40 transition-all">
                    <span class="text-[10px] uppercase tracking-widest font-black text-slate-500">Open Date</span>
                    <span class="text-base font-black text-white"><?php echo $ipo->open_date ? date('M j, Y', strtotime($ipo->open_date)) : 'TBA'; ?></span>
                </div>
                <div class="p-4 rounded-xl bg-[#0B1220] border border-border-navy flex flex-col gap-1.5 hover:border-primary/40 transition-all">
                    <span class="text-[10px] uppercase tracking-widest font-black text-slate-500">Close Date</span>
                    <span class="text-base font-black text-white"><?php echo $ipo->close_date ? date('M j, Y', strtotime($ipo->close_date)) : 'TBA'; ?></span>
                </div>
                <div class="p-4 rounded-xl bg-[#0B1220] border border-border-navy flex flex-col gap-1.5 hover:border-primary/40 transition-all">
                    <span class="text-[10px] uppercase tracking-widest font-black text-slate-500">IPO Size</span>
                    <span class="text-base font-black text-white">₹<?php echo esc_html($ipo->issue_size_cr ?: 'TBA'); ?> cr</span>
                </div>
                <div class="p-4 rounded-xl bg-[#0B1220] border border-border-navy flex flex-col gap-1.5 hover:border-primary/40 transition-all">
                    <span class="text-[10px] uppercase tracking-widest font-black text-slate-500">Lot Size</span>
                    <span class="text-base font-black text-white"><?php echo esc_html($ipo->lot_size ?: 'TBA'); ?></span>
                </div>
                <div class="p-4 rounded-xl bg-[#0B1220] border border-border-navy flex flex-col gap-1.5 hover:border-primary/40 transition-all">
                    <span class="text-[10px] uppercase tracking-widest font-black text-slate-500">Allotment</span>
                    <span class="text-base font-black text-white"><?php echo $ipo->allotment_date ? date('M j, Y', strtotime($ipo->allotment_date)) : 'TBA'; ?></span>
                </div>
                <div class="p-4 rounded-xl bg-[#0B1220] border border-border-navy flex flex-col gap-1.5 hover:border-primary/40 transition-all">
                    <span class="text-[10px] uppercase tracking-widest font-black text-slate-500">Listing</span>
                    <span class="text-base font-black text-white"><?php echo $ipo->listing_date ? date('M j, Y', strtotime($ipo->listing_date)) : 'TBA'; ?></span>
                </div>
            </div>

            <!-- Subscription Status Table -->
            <div class="p-6 rounded-2xl bg-[#0B1220] border border-border-navy shadow-lg">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-base font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-lg">signal_cellular_alt</span>
                        Subscription Status
                    </h2>
                    <span class="bg-neon-emerald text-[#050A18] text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-wider shadow-[0_0_10px_rgba(0,255,148,0.3)]">Live</span>
                </div>
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[9px] font-black text-slate-500 uppercase tracking-widest border-b border-border-navy">
                                <th class="pb-3 px-2">Investor Category</th>
                                <th class="pb-3 px-2 text-center">Offered</th>
                                <th class="pb-3 px-2 text-center">Applied</th>
                                <th class="pb-3 px-2 text-right">Times Subscribed</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-navy">
                            <?php if(isset($details['subscription'])): foreach($details['subscription'] as $s): ?>
                            <tr class="text-xs font-bold data-table-row">
                                <td class="py-3 px-2 text-slate-300"><?php echo esc_html($s['Category'] ?: '-'); ?></td>
                                <td class="py-3 px-2 text-center text-slate-500 font-medium"><?php echo esc_html($s['Offered'] ?: '-'); ?></td>
                                <td class="py-3 px-2 text-center text-slate-500 font-medium"><?php echo esc_html($s['Applied'] ?: '-'); ?></td>
                                <td class="py-3 px-2 text-right text-neon-emerald"><?php echo esc_html($s['Times'] ?: '0.00'); ?>x</td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="4" class="py-8 text-center text-slate-600 text-[10px] font-bold">Subscription data processing...</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Technical IPO Details Grid (Added missing content) -->
            <?php if(isset($details['ipo_details'])): ?>
            <div class="p-6 rounded-2xl bg-[#0B1220] border border-border-navy">
                 <h2 class="text-base font-bold flex items-center gap-2 mb-6">
                    <span class="material-symbols-outlined text-purple-accent text-lg">settings_suggest</span>
                    Technical IPO Specs
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <?php foreach($details['ipo_details'] as $key => $val): ?>
                    <div class="flex justify-between items-center p-3.5 rounded-xl bg-slate-900/30 border border-white/5">
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest"><?php echo esc_html($key); ?></span>
                        <span class="text-xs font-bold text-slate-200"><?php echo esc_html($val); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Lot Distribution (Added missing content) -->
            <?php if(isset($details['lot_distribution'])): ?>
            <div class="p-6 rounded-2xl bg-[#0B1220] border border-border-navy">
                 <h2 class="text-base font-bold flex items-center gap-2 mb-6">
                    <span class="material-symbols-outlined text-blue-400 text-lg">grid_view</span>
                    Bid-wise Lot Distribution
                </h2>
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[9px] font-black text-slate-500 uppercase tracking-widest border-b border-border-navy">
                                <th class="pb-3 px-2">Category</th>
                                <th class="pb-3 px-2 text-center">Lot(s)</th>
                                <th class="pb-3 px-2 text-center">Shares</th>
                                <th class="pb-3 px-2 text-right">Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-navy">
                            <?php foreach($details['lot_distribution'] as $lot): ?>
                            <tr class="text-xs font-bold data-table-row">
                                <td class="py-3 px-2 text-slate-300"><?php echo esc_html($lot['Category'] ?? '-'); ?></td>
                                <td class="py-3 px-2 text-center text-slate-200"><?php echo esc_html($lot['Lot(s)'] ?? '-'); ?></td>
                                <td class="py-3 px-2 text-center text-slate-500"><?php echo esc_html($lot['Qty'] ?? '-'); ?></td>
                                <td class="py-3 px-2 text-right text-primary"><?php echo esc_html($lot['Amount'] ?? '-'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- About Section -->
            <?php if(isset($details['about_company'])): ?>
            <div class="p-6 rounded-2xl bg-[#0B1220] border border-border-navy">
                <h2 class="text-base font-bold text-white mb-4">About the Company</h2>
                <p class="text-slate-400 leading-relaxed text-xs font-medium"><?php echo nl2br(esc_html($details['about_company'])); ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: Sidebar (Tightened) -->
        <div class="space-y-6">
            <!-- Quota Reservation Card (Updated to match screenshot) -->
            <div class="p-6 rounded-2xl bg-[#0B1220] border border-border-navy shadow-lg">
                <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-6">Quota Reservation</h3>
                <div class="space-y-4">
                    <?php if(isset($details['reservation'])): foreach($details['reservation'] as $r): ?>
                    <div class="flex items-center justify-between group">
                        <span class="text-xs font-bold text-slate-400"><?php echo esc_html($r['Category'] ?: '-'); ?></span>
                        <span class="text-[11px] font-black text-white bg-slate-900/80 border border-white/5 px-2.5 py-1.5 rounded-lg min-w-[55px] text-center shadow-sm group-hover:border-primary/30 transition-colors">
                            <?php echo esc_html($r['%'] ?: '0%'); ?>
                        </span>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <!-- Registrar Card (Updated to match screenshot) -->
            <div class="p-6 rounded-2xl bg-[#0B1220] border border-border-navy shadow-lg">
                <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-6">Registrar</h3>
                <div class="bg-slate-900/40 border border-white/5 p-4 rounded-xl flex flex-col gap-4 group hover:border-primary/20 transition-all">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-lg bg-white p-1.5 flex items-center justify-center font-black text-slate-900 shadow-inner shrink-0">
                            <?php 
                                $reg_name = $details['ipo_details']['Registrar'] ?? 'Official';
                                echo substr($reg_name, 0, 1); 
                            ?>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-black text-white leading-tight">
                                <?php echo esc_html($name); ?> Registrar <?php 
                                    // Extract short name (first word) if name is long
                                    $parts = explode(' ', $reg_name);
                                    echo esc_html($parts[0]);
                                ?>
                            </p>
                        </div>
                    </div>
                    <button class="w-full py-2.5 bg-[#1E293B] hover:bg-primary text-white text-[10px] font-black uppercase tracking-[0.1em] rounded-lg transition-all shadow-md">Check Allotment Status</button>
                </div>
            </div>
            
            <!-- Documents (Added missing content) -->
            <?php if(isset($details['documents'])): ?>
            <div class="p-6 rounded-2xl bg-[#0B1220] border border-border-navy">
                <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-6">Official Documents</h3>
                <div class="space-y-2">
                    <?php foreach($details['documents'] as $doc): ?>
                    <a href="<?php echo esc_url($doc['url']); ?>" target="_blank" class="flex items-center justify-between p-3 rounded-xl bg-slate-900/50 hover:bg-slate-800 border border-white/5 transition-colors group">
                        <span class="text-[11px] font-bold text-slate-300 group-hover:text-white"><?php echo esc_html($doc['title']); ?></span>
                        <span class="material-symbols-outlined text-sm text-slate-600 group-hover:text-primary">picture_as_pdf</span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Broker Widget -->
            <?php include TIGC_PATH . 'partials/widget-brokers.php'; ?>
        </div>
    </div>
</main>

<?php include TIGC_PATH . 'partials/footer-premium.php'; ?>
</body>
</html>
