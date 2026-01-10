<?php
/**
 * Shared Premium Header & Ticker Component
 */
global $wpdb;

// Fetch Ticker Data (Active IPOs with GMP)
$t_master = $wpdb->prefix . 'ipomaster';
$ticker_data = $wpdb->get_results("
    SELECT name, premium as gmp, status FROM $t_master 
    WHERE status NOT LIKE '%Closed%' 
    ORDER BY updated_at DESC LIMIT 15
");
?>

<div class="sticky top-0 z-50">
    <!-- Live Ticker -->
    <div class="overflow-hidden h-7 bg-[#0B111D] flex items-center">
        <div class="ticker-animate flex gap-12 items-center whitespace-nowrap px-10">
            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-[0.2em] flex items-center gap-2">
                <span class="w-1 h-1 rounded-full bg-emerald-500"></span>
                Market Pulse:
            </span>
            <?php foreach($ticker_data as $t): 
                $gmp_clean = preg_replace('/[^0-9]/', '', $t->gmp);
                $is_pos = $gmp_clean > 0;
                $color = $is_pos ? 'text-emerald-400' : 'text-slate-400';
            ?>
            <span class="text-[11px] font-medium text-slate-400 flex items-center gap-1.5">
                <span class="text-slate-500"><?php echo esc_html($t->name); ?></span>
                <span class="<?php echo $color; ?> font-bold"><?php echo $t->gmp ? '₹'.$t->gmp : '₹0'; ?></span>
            </span>
            <?php endforeach; ?>
            
            <!-- Duplicate for Infinite Loop -->
            <?php foreach($ticker_data as $t): 
                $gmp_clean = preg_replace('/[^0-9]/', '', $t->gmp);
                $is_pos = $gmp_clean > 0;
                $color = $is_pos ? 'text-emerald-400' : 'text-slate-400';
            ?>
            <span class="text-[11px] font-medium text-slate-400 flex items-center gap-1.5">
                <span class="text-slate-500"><?php echo esc_html($t->name); ?></span>
                <span class="<?php echo $color; ?> font-bold"><?php echo $t->gmp ? '₹'.$t->gmp : '₹0'; ?></span>
            </span>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Main Header -->
    <header class="flex items-center justify-between whitespace-nowrap bg-[#0B111D]/95 backdrop-blur-md px-4 md:px-10 py-3.5">
        <div class="flex items-center gap-10">
            <!-- Logo -->
            <a href="<?php echo home_url('/'); ?>" class="flex items-center gap-2.5 group">
                <div class="size-6 text-primary group-hover:scale-110 transition-transform duration-300">
                    <svg fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <path clip-rule="evenodd" d="M12.0799 24L4 19.2479L9.95537 8.75216L18.04 13.4961L18.0446 4H29.9554L29.96 13.4961L38.0446 8.75216L44 19.2479L35.92 24L44 28.7521L38.0446 39.2479L29.96 34.5039L29.9554 44H18.0446L18.04 34.5039L9.95537 39.2479L4 28.7521L12.0799 24Z" fill="currentColor" fill-rule="evenodd"></path>
                    </svg>
                </div>
                <h2 class="text-white text-[22px] font-black leading-tight tracking-tighter flex items-center gap-1.5 font-display">
                    IPO<span class="text-primary italic">GMP</span>
                </h2>
            </a>

            <!-- Search Bar (Header) -->
            <form action="<?php echo home_url('/ipo-details/'); ?>" method="GET" class="hidden lg:flex items-center w-72 h-10 bg-slate-900 border border-slate-800 rounded-lg px-4 group focus-within:border-primary/40 transition-all">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="q" placeholder="Search IPOs & Buyback news..." value="<?php echo isset($_GET['q']) ? esc_attr($_GET['q']) : ''; ?>" class="bg-transparent border-none text-xs text-white focus:ring-0 w-full placeholder:text-slate-600 ml-2">
            </form>
        </div>

        <div class="flex items-center gap-10">
            <!-- Navigation -->
            <nav class="hidden md:flex items-center gap-8">
                <a class="text-slate-400 hover:text-white text-[11px] font-bold uppercase tracking-[0.15em] transition-colors <?php echo is_front_page() ? 'text-primary' : ''; ?>" href="<?php echo home_url('/'); ?>">Homepage</a>
                <a class="text-slate-400 hover:text-white text-[11px] font-bold uppercase tracking-[0.15em] transition-colors <?php echo is_page('mainboard-ipos') ? 'text-primary' : ''; ?>" href="<?php echo home_url('/mainboard-ipos/'); ?>">Mainboard</a>
                <a class="text-slate-400 hover:text-white text-[11px] font-bold uppercase tracking-[0.15em] transition-colors <?php echo is_page('sme-ipos') ? 'text-primary' : ''; ?>" href="<?php echo home_url('/sme-ipos/'); ?>">SME</a>
                <a class="text-slate-400 hover:text-white text-[11px] font-bold uppercase tracking-[0.15em] transition-colors <?php echo is_page('buybacks') ? 'text-primary' : ''; ?>" href="<?php echo home_url('/buybacks/'); ?>">Buybacks</a>
            </nav>

            <!-- Actions -->
            <div class="flex items-center gap-4">
                <button class="flex h-9 px-5 items-center justify-center rounded-lg bg-primary text-white text-[11px] font-bold uppercase tracking-wider hover:bg-blue-600 transition-all">Portal Login</button>
            </div>
        </div>
    </header>
</div>

<style>
    .ticker-animate {
        animation: ticker-slide 80s linear infinite;
    }
    @keyframes ticker-slide {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    .ticker-animate:hover {
        animation-play-state: paused;
    }
</style>
