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

// D. Stats Calculation (Real Data)
$active_query = "SELECT * FROM $t_master WHERE status IN ('open', 'upcoming')";
$all_active = $wpdb->get_results($active_query);

$active_count = count($all_active);
$positive_gmp = 0;
$total_gmp_sum = 0;
$total_volume_cr = 0;
$highest_gmp_ipo = null;
$highest_gmp_val = -1;

foreach($all_active as $ipo) {
    // GMP Stats
    $gmp = (float)$ipo->premium;
    $price = (float)str_replace(',', '', $ipo->price_band); // simplified
    
    if($gmp > 0) $positive_gmp++;
    $total_gmp_sum += $gmp;

    // Highest GMP Tracker
    if($gmp > $highest_gmp_val) {
        $highest_gmp_val = $gmp;
        $highest_gmp_ipo = $ipo;
    }

    // Volume (Issue Size) - Attempt to parse "1200 Cr" or similar
    // Assuming issue_size is text like "1200 Cr"
    $size_clean = (float)preg_replace('/[^0-9.]/', '', $ipo->issue_size);
    if($size_clean > 0) $total_volume_cr += $size_clean;
}

// Sentiment Logic
$sentiment_score = $active_count > 0 ? ($positive_gmp / $active_count) * 100 : 0;
if($active_count == 0) {
    $sentiment_label = 'Neutral'; $sentiment_color = 'text-slate-400'; $sentiment_width = '50%';
} elseif($sentiment_score >= 60) {
    $sentiment_label = 'Bullish'; $sentiment_color = 'text-neon-emerald'; $sentiment_width = '85%';
} elseif ($sentiment_score >= 30) {
    $sentiment_label = 'Neutral'; $sentiment_color = 'text-yellow-400'; $sentiment_width = '50%';
} else {
    $sentiment_label = 'Bearish'; $sentiment_color = 'text-red-500'; $sentiment_width = '25%';
}

// Avg GMP
$avg_gmp = $active_count > 0 ? round($total_gmp_sum / $active_count) : 0;

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
<?php include 'header-premium.php'; ?>
<main class="max-w-[1280px] mx-auto px-10 py-8">
    <section class="mb-10 text-center lg:text-left pt-6">
        <h1 class="text-white text-[44px] lg:text-[52px] font-black leading-tight mb-4 tracking-tighter">Empowering Investors with <span class="text-primary">Live IPO GMP</span></h1>
        <p class="text-slate-400 text-lg max-w-2xl font-medium leading-relaxed">Real-time Grey Market Premium (GMP) data, buyback trackers, and market intelligence for the modern investor.</p>
    </section>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-10">
        <!-- 1. Market Sentiment -->
        <div class="flex flex-col gap-2 rounded-xl p-6 border border-border-navy bg-[#0B1220]">
            <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Market sentiment</p>
            <div class="flex items-baseline gap-2 mt-1">
                <p class="text-white text-2xl font-bold"><?php echo $sentiment_label; ?></p>
                <!-- <span class="text-neon-emerald text-sm font-bold">+5.2%</span> -->
            </div>
            <div class="w-full bg-slate-800 h-1.5 rounded-full mt-3 overflow-hidden">
                <div class="<?php echo str_replace('text-', 'bg-', $sentiment_color); ?> h-full rounded-full shadow-[0_0_10px_currentColor]" style="width: <?php echo $sentiment_width; ?>"></div>
            </div>
        </div>

        <!-- 2. Active IPOs -->
        <div class="flex flex-col gap-2 rounded-xl p-6 border border-border-navy bg-[#0B1220]">
            <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Active IPOs</p>
            <p class="text-white text-2xl font-bold"><?php echo $active_count; ?> <span class="text-slate-500 font-normal text-lg">Live</span></p>
            <p class="text-primary text-sm font-medium">Tracking Real-time</p>
        </div>

        <!-- 3. Avg GMP (Replaced Fake Subscription) -->
        <div class="flex flex-col gap-2 rounded-xl p-6 border border-border-navy bg-[#0B1220]">
            <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Avg. GMP</p>
            <p class="text-white text-2xl font-bold">₹<?php echo $avg_gmp; ?></p>
            <p class="text-neon-emerald text-sm font-medium">Across Active IPOs</p>
        </div>

        <!-- 4. Total Volume -->
        <div class="flex flex-col gap-2 rounded-xl p-6 border border-border-navy bg-[#0B1220]">
            <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider">Total Issue Size</p>
            <p class="text-white text-2xl font-bold">₹<?php echo number_format($total_volume_cr); ?> Cr</p>
            <p class="text-slate-500 text-sm font-medium">Cumulative (Active)</p>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-3 space-y-12">
            <section>
                <div class="flex items-center justify-between mb-4 px-1">
                    <h2 class="text-white text-2xl font-bold tracking-tight">Mainboard IPOs</h2>
                    <div class="flex gap-2 text-xs font-bold">
                        <button id="btn-active" onclick="filterTable('active')" class="px-3 py-1 bg-primary/20 text-primary rounded-full border border-primary/30 transition-all hover:bg-primary/30">Active</button>
                        <button id="btn-upcoming" onclick="filterTable('upcoming')" class="px-3 py-1 bg-slate-800 text-slate-400 rounded-full border border-slate-700 transition-all hover:bg-slate-700 hover:text-white">Upcoming</button>
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
                            <?php if($mainboard): 
                                $ad_counter = 0;
                                foreach($mainboard as $ipo): 
                                $ad_counter++;
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
                    <a class="text-primary text-xs font-bold hover:underline" href="#">View All SME →</a>
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
            
            <!-- Trending IPO Widget (Replaced Live Subscription) -->
            <?php if($highest_gmp_ipo): ?>
            <div class="p-5 rounded-xl bg-primary/10 border border-primary/30 relative overflow-hidden">
                <div class="relative z-10">
                    <h4 class="text-primary font-black text-sm uppercase tracking-tighter">🔥 Top Trending</h4>
                    <p class="text-white text-lg font-bold mt-1"><?php echo esc_html($highest_gmp_ipo->name); ?></p>
                    <div class="flex flex-col gap-3 mt-4">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-400">Current GMP</span>
                            <span class="text-neon-emerald font-bold">+ ₹<?php echo esc_html($highest_gmp_ipo->premium); ?></span>
                        </div>
                        <div class="w-full bg-slate-800 h-1.5 rounded-full">
                            <div class="bg-primary h-full rounded-full shadow-[0_0_8px_rgba(13,127,242,0.6)]" style="width: 100%"></div>
                        </div>
                        <p class="text-[10px] text-slate-500 text-right">Most demanded active Stock</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </aside>
    </div>
    <!-- SEO & About Section -->
    <section class="mt-16 pt-10 border-t border-border-navy grid grid-cols-1 lg:grid-cols-3 gap-10">
        <div class="lg:col-span-2 prose prose-invert prose-sm max-w-none">
            <h2 class="text-white font-bold text-xl mb-4">India's Most Trusted IPO GMP & Analytics Platform</h2>
            <p class="text-slate-400 leading-relaxed mb-4">
                The IPO GMP is your definitive source for real-time <strong>Grey Market Premium (GMP)</strong>, live subscription numbers, and in-depth analysis of Mainboard and SME IPOs in India. We decode complex market data into actionable intelligence for retail investors, HNI, and QIBs.
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <h3 class="text-white font-bold text-sm mb-2">Why Track GMP?</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">Grey Market Premium indicates the estimated listing price of an IPO. A high GMP often suggests strong listing gains, while negative GMP signals caution.</p>
                </div>
                <div>
                    <h3 class="text-white font-bold text-sm mb-2">Live Subscription Data</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">Track real-time bidding status across Retail, NII, and QIB categories to gauge market demand before placing your bid.</p>
                </div>
            </div>
        </div>
        <div>
            <div class="p-6 rounded-xl bg-slate-900/50 border border-border-navy">
                <h3 class="text-white font-bold text-sm mb-4">Quick Market Links</h3>
                <ul class="space-y-2 text-xs font-medium text-slate-400">
                    <li><a href="#" class="hover:text-primary transition-colors flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-slate-600"></span> Upcoming Mainboard IPOs</a></li>
                    <li><a href="#" class="hover:text-primary transition-colors flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-slate-600"></span> SME IPO Performance</a></li>
                    <li><a href="#" class="hover:text-primary transition-colors flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-slate-600"></span> Buyback Opportunities</a></li>
                    <li><a href="#" class="hover:text-primary transition-colors flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-slate-600"></span> IPO Allotment Status</a></li>
                </ul>
            </div>
        </div>
    </section>
</main>

<!-- Notification FAB -->
<button onclick="requestNotification()" class="fixed bottom-6 right-6 z-50 bg-primary hover:bg-blue-600 text-white p-4 rounded-full shadow-lg shadow-blue-500/30 transition-all hover:scale-110 group" title="Get GMP Alerts">
    <span class="material-symbols-outlined text-2xl group-hover:animate-bell">notifications_active</span>
</button>

<script>
function requestNotification() {
    if ("Notification" in window) {
        Notification.requestPermission().then(function (permission) {
            if (permission === "granted") {
                new Notification("The IPO GMP", { 
                    body: "You are now subscribed to real-time GMP Alerts! 🚀", 
                    icon: "https://cdn-icons-png.flaticon.com/512/1040/1040230.png"
                });
            } else {
                alert("Please allow notifications to get real-time GMP updates.");
            }
        });
    } else {
        alert("Your browser does not support notifications.");
    }
}
</script>

<style>
    @keyframes bell-ring {
        0% { transform: rotate(0); }
        10% { transform: rotate(30deg); }
        30% { transform: rotate(-28deg); }
        50% { transform: rotate(34deg); }
        70% { transform: rotate(-32deg); }
        90% { transform: rotate(30deg); }
        100% { transform: rotate(0); }
    }
    .group:hover .group-hover\:animate-bell {
        animation: bell-ring 1s ease-in-out infinite;
    }
</style>

<?php include 'footer-premium.php'; ?>
</body>
</html>
