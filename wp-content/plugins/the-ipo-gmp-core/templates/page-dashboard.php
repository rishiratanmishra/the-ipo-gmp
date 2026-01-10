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
    AND status IN ('open', 'upcoming', 'allotment')
    ORDER BY id DESC LIMIT 10
");

// B. Active SME
$sme = $wpdb->get_results("
    SELECT * FROM $t_master 
    WHERE is_sme = 1
    AND status IN ('open', 'upcoming')
    ORDER BY id DESC LIMIT 6
");

// D. Stats
$total_gmp_val = 0; // Placeholder calculation
$active_count = 0; // Initial value

?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>The IPO GMP | Live Grey Market Premium Tracker</title>
    <meta name="description" content="Real-time IPO GMP, Buyback tracker and Market Intelligence."/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: { 
                        "primary": "#0d7ff2", 
                        "background-dark": "#050A18", 
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
        .data-table-row:hover { background-color: rgba(13, 127, 242, 0.05); }
    </style>
</head>
<body class="bg-[#050A18] text-slate-100 min-h-screen font-display antialiased">
<?php include TIGC_PATH . 'partials/header-premium.php'; ?>
<main class="max-w-[1280px] mx-auto px-10 py-8">
    <section class="mb-10 text-center lg:text-left pt-6">
        <h1 class="text-white text-[44px] lg:text-[52px] font-black leading-tight mb-4 tracking-tighter">Empowering Investors with <span class="text-primary">Live IPO GMP</span></h1>
        <p class="text-slate-400 text-lg max-w-2xl font-medium leading-relaxed">Real-time Grey Market Premium (GMP) data, buyback trackers, and market intelligence for the modern investor.</p>
    </section>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-10">
        <div class="flex flex-col gap-2 rounded-xl p-6 border border-border-navy bg-[#0B1220]">
            <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Market sentiment</p>
            <div class="flex items-baseline gap-2 mt-1">
                <p class="text-white text-2xl font-bold">Bullish</p>
                <span class="text-neon-emerald text-sm font-bold">+5.2%</span>
            </div>
            <div class="w-full bg-slate-800 h-1.5 rounded-full mt-3 overflow-hidden">
                <div class="bg-neon-emerald h-full w-[85%] rounded-full shadow-[0_0_10px_#00FF94]"></div>
            </div>
        </div>

        <div class="flex flex-col gap-2 rounded-xl p-6 border border-border-navy bg-[#0B1220]">
            <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Active IPOs</p>
            <p class="text-white text-2xl font-bold"><?php echo $active_count; ?> <span class="text-slate-500 font-normal text-lg">Live</span></p>
            <p class="text-primary text-sm font-medium">Tracking Real-time</p>
        </div>

        <div class="flex flex-col gap-2 rounded-xl p-6 border border-border-navy bg-[#0B1220]">
            <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Avg. Subscription</p>
            <p class="text-white text-2xl font-bold">42.5x</p>
            <p class="text-neon-emerald text-sm font-medium">+8.5% Intensity</p>
        </div>

        <div class="flex flex-col gap-2 rounded-xl p-6 border border-border-navy bg-[#0B1220]">
            <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Total Volume</p>
            <p class="text-white text-2xl font-bold">₹4,250 Cr</p>
            <p class="text-neon-emerald text-sm font-medium">↑ 12.4% vs prev week</p>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-3 space-y-12">
            <section>
                <div class="flex items-center justify-between mb-4 px-1">
                    <h2 class="text-white text-2xl font-bold tracking-tight">Mainboard IPOs</h2>
                    <div class="flex items-center gap-4">
                        <div class="flex gap-2 text-xs font-bold">
                            <button id="btn-active" onclick="filterTable('active')" class="px-3 py-1 bg-primary/20 text-primary rounded-full border border-primary/30 transition-all hover:bg-primary/30">Active</button>
                            <button id="btn-upcoming" onclick="filterTable('upcoming')" class="px-3 py-1 bg-slate-800 text-slate-400 rounded-full border border-slate-700 transition-all hover:bg-slate-700 hover:text-white">Upcoming</button>
                        </div>
                        <a href="<?php echo home_url('/mainboard-ipos/'); ?>" class="text-primary text-xs font-bold hover:underline hidden md:block">View All →</a>
                    </div>
                </div>
                <div class="overflow-hidden rounded-xl border border-border-navy bg-[#0B1220]">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-900/50 border-b border-border-navy">
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-widest">Company Name</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-widest">Price Band</th>
                                <th class="px-6 py-4 text-xs font-semibold text-emerald-500 uppercase tracking-widest bg-emerald-500/5">GMP Premium</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-widest">Listing Dates</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-widest">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-navy">
                            <?php if($mainboard): foreach($mainboard as $ipo): 
                                $details_url = home_url('/ipo-details/?slug=' . $ipo->slug);
                                $gmp_val = $ipo->premium ?: '0';
                                $status_class = strtolower($ipo->status);
                            ?>
                            <tr class="data-table-row transition-colors cursor-pointer group ipo-row row-<?php echo esc_attr($status_class); ?>" onclick="window.location.href='<?php echo esc_url($details_url); ?>'">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded bg-white p-1 flex items-center justify-center font-bold text-slate-900 overflow-hidden group-hover:scale-110 transition-transform">
                                            <?php if(!empty($ipo->icon_url)): ?>
                                                <img src="<?php echo esc_url($ipo->icon_url); ?>" alt="<?php echo esc_attr($ipo->name); ?>" class="w-full h-full object-contain" />
                                            <?php else: ?>
                                                <?php echo substr($ipo->name, 0, 1); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-white group-hover:text-primary transition-colors"><?php echo esc_html($ipo->name); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-slate-300"><?php echo esc_html($ipo->price_band); ?></td>
                                <td class="px-6 py-4 text-sm font-black text-neon-emerald bg-neon-emerald/5 group-hover:bg-neon-emerald/10 transition-colors">+ ₹<?php echo $gmp_val; ?></td>
                                <td class="px-6 py-4 text-sm font-medium text-slate-300">
                                    <?php echo date('M j', strtotime($ipo->open_date)); ?> - <?php echo date('M j', strtotime($ipo->close_date)); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="flex items-center gap-1.5 text-xs font-bold text-primary">
                                        <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span> <?php echo esc_html($ipo->status); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="5" class="px-6 py-4 text-center text-slate-500">No active Mainboard IPOs found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            
            <script>
            function filterTable(filter) {
                // Update Buttons
                const btnActive = document.getElementById('btn-active');
                const btnUpcoming = document.getElementById('btn-upcoming');
                
                const activeClass = "bg-primary/20 text-primary border-primary/30";
                const inactiveClass = "bg-slate-800 text-slate-400 border-slate-700";
                
                if (filter === 'active') {
                    btnActive.className = "px-3 py-1 rounded-full text-xs font-bold border transition-all hover:bg-primary/30 " + activeClass;
                    btnUpcoming.className = "px-3 py-1 rounded-full text-xs font-bold border transition-all hover:bg-slate-700 hover:text-white " + inactiveClass;
                } else {
                    btnUpcoming.className = "px-3 py-1 rounded-full text-xs font-bold border transition-all hover:bg-primary/30 " + activeClass; // Using active style for selected state
                    btnActive.className = "px-3 py-1 rounded-full text-xs font-bold border transition-all hover:bg-slate-700 hover:text-white " + inactiveClass;
                }

                // Filter Rows
                const rows = document.querySelectorAll('.ipo-row');
                rows.forEach(row => {
                    if (filter === 'active') {
                        if (row.classList.contains('row-open') || row.classList.contains('row-allotment')) {
                            row.style.display = 'table-row';
                        } else {
                            row.style.display = 'none';
                        }
                    } else if (filter === 'upcoming') {
                         if (row.classList.contains('row-upcoming')) {
                            row.style.display = 'table-row';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            }
            // Init
            document.addEventListener('DOMContentLoaded', () => filterTable('active'));
            </script>
            <section>
                <div class="flex items-center justify-between mb-4 px-1">
                    <h2 class="text-white text-2xl font-bold tracking-tight">SME IPO Monitor</h2>
                    <a class="text-primary text-xs font-bold hover:underline" href="<?php echo home_url('/sme-ipos/'); ?>">View All SME →</a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php if($sme): foreach($sme as $ipo): 
                        $details_url = home_url('/ipo-details/?slug=' . $ipo->slug);
                    ?>
                    <div onclick="window.location.href='<?php echo esc_url($details_url); ?>'" class="cursor-pointer p-4 rounded-xl border border-border-navy bg-[#0B1220] hover:border-primary/50 transition-all group">
                        <div class="flex justify-between items-start mb-3">
                            <h3 class="text-white font-bold text-base group-hover:text-primary truncate max-w-[200px]"><?php echo esc_html($ipo->name); ?></h3>
                            <?php 
                            $is_open = strtolower($ipo->status) === 'open';
                            $status_classes = $is_open 
                                ? 'text-green-400 bg-green-500/10 border-green-500/20 animate-pulse' 
                                : 'text-slate-400 bg-slate-800/50 border-white/10';
                            ?>
                            <span class="text-[10px] font-bold px-2 py-1 rounded border <?php echo $status_classes; ?> uppercase tracking-wider">
                                <?php echo esc_html($ipo->status); ?>
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">Price</p>
                                <p class="text-sm text-white font-semibold"><?php echo esc_html($ipo->price_band); ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">GMP</p>
                                <p class="text-sm text-neon-emerald font-bold">+ ₹<?php echo $ipo->premium ?: '0'; ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; else: ?>
                    <p class="text-slate-500 text-sm">No active SME IPOs.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
        <aside class="space-y-8">
            <div class="rounded-xl border border-border-navy bg-[#0B1220] overflow-hidden">
                <div class="p-5 border-b border-border-navy flex items-center justify-between">
                    <h3 class="text-white font-bold text-lg">Allotment Corner</h3>
                    <span class="material-symbols-outlined text-primary">link</span>
                </div>
                <div class="p-5 space-y-3">
                    <p class="text-xs text-slate-400 mb-4 leading-relaxed">Check your application status directly on registrar portals:</p>
                    <a class="flex items-center gap-3 p-3 rounded-lg bg-slate-900 border border-border-navy hover:bg-slate-800 transition-colors" href="#">
                        <div class="w-8 h-8 bg-white rounded flex items-center justify-center font-bold text-blue-900 text-[10px]">LI</div>
                        <div>
                            <p class="text-sm font-bold text-white">Link Intime</p>
                            <p class="text-[10px] text-slate-500">Major Registrar</p>
                        </div>
                        <span class="material-symbols-outlined text-slate-600 ml-auto text-sm">open_in_new</span>
                    </a>
                    <!-- More links... -->
                </div>
            </div>
            
            <!-- Buyback Watch Widget -->
            <div class="rounded-xl border border-border-navy bg-[#0B1220] overflow-hidden">
                <div class="p-5 border-b border-border-navy flex items-center justify-between">
                    <h3 class="text-white font-bold text-lg flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-purple-500 animate-pulse"></span>
                        Buyback Watch
                    </h3>
                    <span class="text-[10px] font-bold text-purple-400 bg-purple-500/10 px-2 py-1 rounded border border-purple-500/20">Active</span>
                </div>
                <div class="divide-y divide-border-navy">
                    <?php 
                    // Fetch Active Buybacks
                    $buybacks = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}buybacks WHERE type LIKE '%Open%' OR type LIKE '%Upcoming%' ORDER BY id DESC LIMIT 5");
                    
                    if($buybacks): foreach($buybacks as $bb): 
                        // Calc Premium
                        $offer_price = (float) preg_replace('/[^0-9.]/', '', $bb->price);
                        $mkt_price = (float) preg_replace('/[^0-9.]/', '', $bb->market_price);
                        $premium = 0;
                        if($mkt_price > 0 && $offer_price > 0) {
                            $premium = round((($offer_price - $mkt_price) / $mkt_price) * 100, 1);
                        }
                    ?>
                    <div class="p-4 hover:bg-slate-800/50 transition-colors cursor-pointer group">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="text-white font-bold text-sm group-hover:text-purple-400 transition-colors truncate max-w-[150px]"><?php echo esc_html($bb->company); ?></h4>
                            <span class="text-[10px] font-bold text-slate-400 uppercase"><?php echo esc_html($bb->type ?: 'Tender'); ?></span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex flex-col">
                                <span class="text-slate-500">Price</span>
                                <span class="text-white font-semibold">₹<?php echo esc_html($bb->price); ?></span>
                            </div>
                            <?php if($premium > 0): ?>
                            <div class="flex flex-col items-end">
                                <span class="text-slate-500">Premium</span>
                                <span class="text-neon-emerald font-bold">+<?php echo $premium; ?>%</span>
                            </div>
                            <?php else: ?>
                             <div class="flex flex-col items-end">
                                <span class="text-slate-500">Size</span>
                                <span class="text-slate-300 font-bold"><?php echo esc_html($bb->issue_size ?: '-'); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; else: ?>
                    <div class="p-4 text-center text-slate-500 text-xs">No active buybacks found.</div>
                    <?php endif; ?>
                </div>
                <div class="p-3 bg-slate-900/50 text-center border-t border-border-navy">
                    <a href="<?php echo home_url('/buybacks/'); ?>" class="text-xs font-bold text-purple-400 hover:text-purple-300 transition-colors">View All Buybacks →</a>
                </div>
            </div>
            
            <!-- Live Subscription Widget -->
            <div class="p-5 rounded-xl bg-primary/10 border border-primary/30 relative overflow-hidden">
                <div class="relative z-10">
                    <h4 class="text-primary font-black text-sm uppercase tracking-tighter">Live Subscription</h4>
                    <p class="text-white text-lg font-bold mt-1">Bajaj Housing Finance</p>
                    <div class="flex flex-col gap-3 mt-4">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-400">Retail (2x)</span>
                            <span class="text-white font-bold">7.42x</span>
                        </div>
                        <div class="w-full bg-slate-800 h-1.5 rounded-full">
                            <div class="bg-primary h-full w-[100%] rounded-full shadow-[0_0_8px_rgba(13,127,242,0.6)]"></div>
                        </div>
                    </div>
                </div>
                <div class="absolute -right-4 -bottom-4 opacity-10">
                    <span class="material-symbols-outlined text-[100px]">trending_up</span>
                </div>
            </div>
        </aside>
    </div>
</main>
<?php include TIGC_PATH . 'partials/footer-premium.php'; ?>
</body>
</html>
