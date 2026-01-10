<?php
/**
 * Template Name: Mainboard IPOs Dedicated List
 * Description: Dedicated page for all Mainboard IPOs (History & Active).
 */

global $wpdb;
$t_master = $wpdb->prefix . 'ipomaster';

// Fetch All Mainboard IPOs (Paginated in real app, but fetching all for now)
$mainboard = $wpdb->get_results("
    SELECT * FROM $t_master 
    WHERE is_sme = 0
    ORDER BY close_date DESC LIMIT 50
");

?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Mainboard IPOs List | The IPO GMP</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
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
    
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 mb-8 text-sm font-medium text-slate-500">
        <span class="material-symbols-outlined text-sm">home</span>
        <a href="<?php echo home_url('/'); ?>" class="hover:text-primary transition-colors">Dashboard</a>
        <span class="text-slate-700">/</span>
        <span class="text-slate-200">Mainboard IPOs</span>
    </nav>

    <div class="mb-10 pt-4">
        <h1 class="text-white text-4xl font-black mb-2 tracking-tight">Mainboard IPOs <span class="text-primary">Market</span></h1>
        <p class="text-slate-400 text-lg max-w-2xl font-medium">Comprehensive list of all Mainboard IPOs, including active, upcoming, and listed companies.</p>
    </div>

    <!-- Main Table -->
    <div class="overflow-hidden rounded-xl border border-border-navy bg-[#0B1220] shadow-2xl">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-900/50 border-b border-border-navy">
                    <th class="px-6 py-5 text-xs font-black text-slate-500 uppercase tracking-widest">Company</th>
                    <th class="px-6 py-5 text-xs font-black text-slate-500 uppercase tracking-widest">Price Band</th>
                    <th class="px-6 py-5 text-xs font-black text-emerald-500 uppercase tracking-widest bg-emerald-500/5">GMP</th>
                    <th class="px-6 py-5 text-xs font-black text-slate-500 uppercase tracking-widest">Listing Date</th>
                    <th class="px-6 py-5 text-xs font-black text-slate-500 uppercase tracking-widest">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-navy">
                <?php if($mainboard): foreach($mainboard as $ipo): 
                    $details_url = home_url('/ipo-details/?slug=' . $ipo->slug);
                    $gmp_val = $ipo->premium ?: '0';
                    $gmp_perc = ($ipo->max_price > 0) ? round(($gmp_val / $ipo->max_price) * 100, 1) : 0;
                ?>
                <tr class="data-table-row transition-colors cursor-pointer group" onclick="window.location.href='<?php echo esc_url($details_url); ?>'">
                    <td class="px-6 py-5">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-lg bg-white p-1.5 flex items-center justify-center font-bold text-slate-900 overflow-hidden shadow-sm group-hover:scale-105 transition-transform">
                                <?php if(!empty($ipo->icon_url)): ?>
                                    <img src="<?php echo esc_url($ipo->icon_url); ?>" alt="<?php echo esc_attr($ipo->name); ?>" class="w-full h-full object-contain" />
                                <?php else: ?>
                                    <?php echo substr($ipo->name, 0, 1); ?>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="text-base font-bold text-white group-hover:text-primary transition-colors"><?php echo esc_html($ipo->name); ?></p>
                                <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider mt-0.5">Size: ₹<?php echo esc_html($ipo->issue_size_cr); ?> Cr</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-5">
                        <span class="text-sm font-bold text-slate-200"><?php echo esc_html($ipo->price_band); ?></span>
                         <p class="text-[10px] text-slate-500 font-bold mt-0.5">Lot: <?php echo esc_html($ipo->lot_size); ?></p>
                    </td>
                    <td class="px-6 py-5">
                        <div class="flex flex-col items-start">
                            <span class="text-sm font-black text-neon-emerald bg-neon-emerald/10 px-2 py-0.5 rounded border border-neon-emerald/20">+ ₹<?php echo $gmp_val; ?></span>
                            <?php if($gmp_perc > 0): ?>
                                <span class="text-[10px] font-bold text-slate-400 mt-1 pl-1">~<?php echo $gmp_perc; ?>%</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-6 py-5 text-sm font-medium text-slate-300">
                        <?php echo $ipo->listing_date ? date('M j, Y', strtotime($ipo->listing_date)) : 'TBA'; ?>
                    </td>
                    <td class="px-6 py-5">
                         <?php 
                            $status_color = 'bg-slate-800 text-slate-400 border-slate-700';
                            if (strtolower($ipo->status) === 'open') $status_color = 'bg-primary/20 text-primary border-primary/30';
                            if (strtolower($ipo->status) === 'upcoming') $status_color = 'bg-purple-500/20 text-purple-400 border-purple-500/30';
                        ?>
                        <span class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-full border <?php echo $status_color; ?>">
                            <?php echo esc_html($ipo->status); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="5" class="px-6 py-8 text-center text-slate-500">No Mainboard IPOs found in database.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>
<?php include TIGC_PATH . 'partials/footer-premium.php'; ?>
</body>
</html>
