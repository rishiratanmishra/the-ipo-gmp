<?php
/**
 * Template Name: Stock Buyback Tracker
 */

global $wpdb;
$t_buybacks = $wpdb->prefix . 'buybacks';

// Search and Filter logic
$search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$type_filter = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';

$query = "SELECT * FROM $t_buybacks WHERE 1=1";
if (!empty($search)) {
    $query .= $wpdb->prepare(" AND company LIKE %s", '%' . $wpdb->esc_like($search) . '%');
}
if (!empty($type_filter)) {
    $query .= $wpdb->prepare(" AND type = %s", $type_filter);
}
$query .= " ORDER BY id DESC";

$buybacks = $wpdb->get_results($query);
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Buyback Dashboard - Live Tracker</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: { "primary": "#0d7ff2", "background-dark": "#050A18", "border-navy": "#1E293B", "neon-emerald": "#00FF94", "purple-accent": "#A855F7" },
                    fontFamily: { "display": ["Inter", "sans-serif"] }
                }
            }
        }
    </script>
    <style>
        select option { background-color: #0B1221 !important; color: #cbd5e1 !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #1E293B; border-radius: 10px; }
    </style>
</head>
<body class="bg-[#050A18] text-slate-100 min-h-screen font-display antialiased selection:bg-purple-500/20 selection:text-purple-400">
<?php include TIGC_PATH . 'partials/header-premium.php'; ?>

<!-- Glow Effects -->
<div class="fixed top-0 left-0 w-full h-[500px] bg-gradient-to-b from-purple-900/10 to-transparent pointer-events-none z-0"></div>

<div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Breadcrumb -->
    <nav class="flex mb-6 text-sm font-medium text-slate-500" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-2">
            <li>
                <a href="<?php echo home_url('/'); ?>" class="hover:text-primary transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Dashboard
                </a>
            </li>
            <li class="flex items-center gap-2">
                <span class="text-slate-700">/</span>
                <span class="text-slate-400">Buyback Intelligence</span>
            </li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 mb-10">
        <div>
            <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight mb-2">Buyback <span class="text-purple-500">Watch</span></h1>
            <p class="text-slate-400 text-sm">Real-time tracking of Tender Offers & Open Market Buybacks.</p>
        </div>
        
        <!-- Search & Filter Form -->
        <form action="" method="GET" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <div class="relative flex-grow md:w-64">
                <span class="absolute inset-y-0 left-3 flex items-center text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="search" value="<?php echo esc_attr($search); ?>" placeholder="Search company..." class="w-full bg-slate-900 border-border-navy rounded-lg pl-10 pr-4 py-2.5 text-sm text-white placeholder:text-slate-600 focus:ring-purple-500 focus:border-purple-500 transition-all">
            </div>
            
            <select name="type" class="bg-slate-900 border-border-navy rounded-lg px-4 py-2.5 text-sm text-white focus:ring-purple-500 focus:border-purple-500 outline-none">
                <option value="">All Types</option>
                <option value="Tender" <?php selected($type_filter, 'Tender'); ?>>Tender Offer</option>
                <option value="Open Market" <?php selected($type_filter, 'Open Market'); ?>>Open Market</option>
            </select>
            
            <button type="submit" class="bg-purple-600 hover:bg-purple-500 text-white font-bold py-2.5 px-6 rounded-lg transition-colors text-sm">Apply</button>
        </form>
    </div>

    <!-- Active Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
        <?php if ($buybacks): foreach ($buybacks as $bb): 
            $offer_price = (float) preg_replace('/[^0-9.]/', '', $bb->price);
            $mkt_price = (float) preg_replace('/[^0-9.]/', '', $bb->market_price);
            $premium = 0;
            if($mkt_price > 0 && $offer_price > 0) {
                $premium = round((($offer_price - $mkt_price) / $mkt_price) * 100, 1);
            }
        ?>
        <div class="bg-[#0B1220] border border-border-navy rounded-2xl p-6 hover:border-purple-500/50 transition-all group relative overflow-hidden">
            <!-- Progress Bar Background (Subtle) -->
            <div class="absolute bottom-0 left-0 h-1 bg-purple-500/20 w-full">
                <div class="h-full bg-purple-500 shadow-[0_0_10px_#A855F7]" style="width: <?php echo min(100, $premium * 2); ?>%"></div>
            </div>

            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-white font-bold text-lg group-hover:text-purple-400 transition-colors mb-1"><?php echo esc_html($bb->company); ?></h3>
                    <span class="text-[10px] font-bold text-purple-400 bg-purple-500/10 px-2 py-0.5 rounded border border-purple-500/20 uppercase"><?php echo esc_html($bb->type); ?></span>
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">Premium</p>
                    <p class="text-xl font-black <?php echo $premium > 10 ? 'text-neon-emerald' : 'text-white'; ?>">
                        <?php echo $premium > 0 ? '+' . $premium . '%' : '--'; ?>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 pt-4 border-t border-slate-800/50">
                <div>
                    <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">Buyback Price</p>
                    <p class="text-white font-bold">₹<?php echo esc_html($bb->price); ?></p>
                </div>
                <div>
                    <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">Market Price</p>
                    <p class="text-slate-300 font-semibold text-sm">₹<?php echo esc_html($bb->market_price); ?></p>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <div class="text-[11px] text-slate-400">
                    <span class="font-bold text-slate-500">Size:</span> <?php echo esc_html($bb->issue_size); ?>
                </div>
                <button class="text-xs font-bold text-purple-400 flex items-center gap-1 group-hover:gap-2 transition-all">
                    Details <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </button>
            </div>
        </div>
        <?php endforeach; else: ?>
        <div class="col-span-full py-20 text-center bg-[#0B1220] rounded-2xl border border-dashed border-border-navy">
            <span class="material-symbols-outlined text-4xl text-slate-700 mb-4">search_off</span>
            <p class="text-slate-500">No buyback events found for the current selection.</p>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php include TIGC_PATH . 'partials/footer-premium.php'; ?>
</body>
</html>
