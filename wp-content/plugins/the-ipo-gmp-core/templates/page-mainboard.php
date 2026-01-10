<?php
/**
 * Template Name: Mainboard IPOs Dedicated List
 * Description: Dedicated page for Mainboard IPOs with Search, Filters, and Pagination.
 */

global $wpdb;
$t_master = $wpdb->prefix . 'ipomaster';

// 1. Parameters
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$limit = 20;
$offset = ($paged - 1) * $limit;

$search = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';
$status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

// 2. Query Construction
$where_clauses = ["is_sme = 0"];

// Search Filter
if (!empty($search)) {
    $where_clauses[] = $wpdb->prepare("(name LIKE %s OR symbol LIKE %s)", '%'.$search.'%', '%'.$search.'%');
}

// Status Filter
if (!empty($status) && in_array(strtolower($status), ['open', 'upcoming', 'listed', 'closed'])) {
    if ($status === 'listed') {
        $where_clauses[] = "status = 'listed'";
    } else {
        $where_clauses[] = $wpdb->prepare("status LIKE %s", $status);
    }
}

$where_sql = implode(' AND ', $where_clauses);

// Count Total for Pagination
$total_items = $wpdb->get_var("SELECT COUNT(*) FROM $t_master WHERE $where_sql");
$total_pages = ceil($total_items / $limit);

// Fetch Items
$order_by = "close_date DESC"; // Default
if (strtolower($status) === 'upcoming') $order_by = "open_date ASC";

$mainboard = $wpdb->get_results("
    SELECT * FROM $t_master 
    WHERE $where_sql 
    ORDER BY $order_by 
    LIMIT $limit OFFSET $offset
");

// 3. Helper for URL generation
function tigc_get_filter_url($status_val) {
    $params = $_GET;
    // Set status
    if ($status_val) $params['status'] = $status_val;
    else unset($params['status']);
    
    // Reset page on filter change
    unset($params['paged']);
    
    return '?' . http_build_query($params);
}

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

<main class="max-w-[1280px] mx-auto px-4 md:px-10 py-6">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 mb-6 text-sm font-medium text-slate-500">
        <span class="material-symbols-outlined text-sm">home</span>
        <a href="<?php echo home_url('/'); ?>" class="hover:text-primary transition-colors">Dashboard</a>
        <span class="material-symbols-outlined text-xs">chevron_right</span>
        <span class="text-slate-200">Mainboard IPOs</span>
    </nav>

    <!-- Header & Search -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8">
        <div>
            <h1 class="text-white text-3xl font-black tracking-tight mb-2">Mainboard <span class="text-primary">Market</span></h1>
            <p class="text-slate-400 text-sm font-medium">Tracking <?php echo $total_items; ?> Mainboard IPOs</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto">
             <!-- Status Filters -->
            <div class="flex p-1 bg-slate-900 border border-border-navy rounded-lg">
                <?php 
                $tabs = [
                    '' => 'All', 
                    'open' => 'Open', 
                    'upcoming' => 'Upcoming', 
                    'closed' => 'Closed',
                    'listed' => 'Listed'
                ];
                foreach ($tabs as $k => $v): 
                    $active = (strtolower($status) === $k) ? 'bg-[#0B1220] text-white shadow-sm font-bold border border-white/5' : 'text-slate-500 hover:text-white font-medium';
                ?>
                <a href="<?php echo tigc_get_filter_url($k); ?>" class="px-4 py-2 text-xs rounded-md transition-all <?php echo $active; ?>">
                    <?php echo $v; ?>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Search Bar -->
            <form class="relative group" action="" method="GET">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-primary transition-colors">search</span>
                <input type="text" name="q" value="<?php echo esc_attr($search); ?>" placeholder="Search company..." 
                       class="bg-slate-900 border border-slate-700 text-white text-sm rounded-lg pl-10 pr-4 py-2.5 w-full sm:w-64 focus:ring-1 focus:ring-primary focus:border-primary placeholder-slate-600">
                <?php if($status): ?><input type="hidden" name="status" value="<?php echo esc_attr($status); ?>"><?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Main Table -->
    <div class="overflow-hidden rounded-xl border border-border-navy bg-[#0B1220] shadow-2xl relative min-h-[400px]">
        
        <?php if($mainboard): ?>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#0f172a] border-b border-border-navy">
                    <th class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-widest">Company</th>
                    <th class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-widest">Price Band</th>
                    <th class="px-6 py-4 text-[11px] font-black text-emerald-500 uppercase tracking-widest bg-emerald-500/5">GMP</th>
                    <th class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-widest hidden md:table-cell">Dates</th>
                    <th class="px-6 py-4 text-[11px] font-black text-slate-500 uppercase tracking-widest text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-navy">
                <?php foreach($mainboard as $ipo): 
                    $details_url = home_url('/ipo-details/?slug=' . $ipo->slug);
                    $gmp_val = (float)$ipo->premium;
                    $cap_price = (float)$ipo->max_price;
                    
                    // Handle range in price band if max_price is missing
                    if ($cap_price <= 0 && preg_match('/(\d+)(?!.*\d)/', $ipo->price_band, $m)) {
                         $cap_price = (float)$m[1];
                    }

                    $gmp_perc = ($cap_price > 0) ? round(($gmp_val / $cap_price) * 100, 1) : 0;
                    
                    // Dynamic Row Border for Status
                    $row_status_class = '';
                    if (strtolower($ipo->status) === 'open') $row_status_class = 'border-l-2 border-l-primary';
                ?>
                <tr class="data-table-row transition-all cursor-pointer group <?php echo $row_status_class; ?>" onclick="window.location.href='<?php echo esc_url($details_url); ?>'">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-lg bg-white p-1.5 flex items-center justify-center font-bold text-slate-900 overflow-hidden shadow-sm group-hover:scale-105 transition-transform shrink-0">
                                <?php if(!empty($ipo->icon_url)): ?>
                                    <img src="<?php echo esc_url($ipo->icon_url); ?>" alt="" class="w-full h-full object-contain" />
                                <?php else: ?>
                                    <?php echo substr($ipo->name, 0, 1); ?>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-white group-hover:text-primary transition-colors line-clamp-1"><?php echo esc_html($ipo->name); ?></h3>
                                <p class="text-[10px] text-slate-500 font-bold tracking-wide mt-0.5">Size: ₹<?php echo esc_html($ipo->issue_size_cr ?: '-'); ?> Cr</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-bold text-slate-200 block"><?php echo esc_html($ipo->price_band ?: 'TBA'); ?></span>
                        <span class="text-[10px] text-slate-500 font-medium">Lot: <?php echo esc_html($ipo->lot_size ?: '-'); ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <?php if($gmp_val > 0): ?>
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
                             <span class="text-xs font-medium text-slate-200"><?php echo $ipo->listing_date ? date('M j', strtotime($ipo->listing_date)) : 'TBA'; ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                         <?php 
                            $st = strtolower($ipo->status);
                            $badge_color = 'bg-slate-800 text-slate-400 border-slate-700';
                            if ($st === 'open') $badge_color = 'bg-primary/20 text-primary border-primary/30 animate-pulse';
                            if ($st === 'upcoming') $badge_color = 'bg-purple-500/20 text-purple-400 border-purple-500/30';
                        ?>
                        <span class="inline-block px-2.5 py-0.5 text-[9px] font-black uppercase tracking-widest rounded-full border <?php echo $badge_color; ?>">
                            <?php echo esc_html($ipo->status); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php else: ?>
        <div class="flex flex-col items-center justify-center py-20">
            <span class="material-symbols-outlined text-4xl text-slate-700 mb-2">search_off</span>
            <p class="text-slate-500 font-medium">No IPOs found matching criteria.</p>
            <?php if($status || $search): ?>
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
            $prev_params = $_GET; $prev_params['paged'] = $paged - 1;
            echo '<a href="?' . http_build_query($prev_params) . '" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-700 text-slate-400 hover:bg-slate-800 transition-colors"><span class="material-symbols-outlined text-sm">chevron_left</span></a>';
        }

        // Page Numbers (Simple)
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == $paged) {
                echo '<span class="w-8 h-8 flex items-center justify-center rounded-lg bg-primary text-white font-bold text-xs shadow-lg shadow-blue-500/30">'.$i.'</span>';
            } elseif ($i <= 3 || $i == $total_pages || abs($paged - $i) <= 1) {
                $page_params = $_GET; $page_params['paged'] = $i;
                echo '<a href="?' . http_build_query($page_params) . '" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-700 text-slate-400 hover:bg-slate-800 transition-colors text-xs font-medium">'.$i.'</a>';
            } elseif ($i == 4 && $paged > 5) {
                echo '<span class="text-slate-600 px-1">...</span>';
            }
        }

        // Next Link
        if ($paged < $total_pages) {
            $next_params = $_GET; $next_params['paged'] = $paged + 1;
            echo '<a href="?' . http_build_query($next_params) . '" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-700 text-slate-400 hover:bg-slate-800 transition-colors"><span class="material-symbols-outlined text-sm">chevron_right</span></a>';
        }
        ?>
    </div>
    <?php endif; ?>

</main>
<?php include TIGC_PATH . 'partials/footer-premium.php'; ?>
</body>
</html>
