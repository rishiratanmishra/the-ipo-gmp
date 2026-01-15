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
            <?php foreach ($ticker_data as $t):
                $gmp_clean = preg_replace('/[^0-9]/', '', $t->gmp);
                $is_pos = $gmp_clean > 0;
                $color = $is_pos ? 'text-emerald-400' : 'text-slate-400';
                ?>
                <span class="text-[11px] font-medium text-slate-400 flex items-center gap-1.5">
                    <span class="text-slate-500"><?php echo esc_html($t->name); ?></span>
                    <span class="<?php echo $color; ?> font-bold"><?php echo $t->gmp ? '₹' . $t->gmp : '₹0'; ?></span>
                </span>
            <?php endforeach; ?>

            <!-- Duplicate for Infinite Loop -->
            <?php foreach ($ticker_data as $t):
                $gmp_clean = preg_replace('/[^0-9]/', '', $t->gmp);
                $is_pos = $gmp_clean > 0;
                $color = $is_pos ? 'text-emerald-400' : 'text-slate-400';
                ?>
                <span class="text-[11px] font-medium text-slate-400 flex items-center gap-1.5">
                    <span class="text-slate-500"><?php echo esc_html($t->name); ?></span>
                    <span class="<?php echo $color; ?> font-bold"><?php echo $t->gmp ? '₹' . $t->gmp : '₹0'; ?></span>
                </span>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Main Header -->
    <header
        class="flex items-center justify-between whitespace-nowrap bg-[#0B111D]/95 backdrop-blur-md px-4 md:px-10 py-3.5">
        <div class="flex items-center gap-10">
            <!-- Logo -->
            <a href="<?php echo home_url('/'); ?>" class="flex items-center gap-3 group">
                <h2 class="text-white text-2xl font-black leading-none tracking-tighter flex items-center font-display">
                    IPO<span class="text-neon-emerald">GMP</span><span
                        class="text-primary text-4xl leading-none">.</span>
                </h2>
            </a>

            <!-- Search Bar (Header) -->
            <form action="<?php echo home_url('/mainboard-ipos/'); ?>" method="GET"
                class="hidden lg:flex items-center w-96 h-10 bg-slate-900 border border-slate-800 rounded-lg px-4 group focus-within:border-primary/40 transition-all relative"
                onsubmit="var q = this.querySelector('input[name=\'q\']'); q.value = q.value.trim(); if(q.value === '') return false;">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input id="tigc-search-input" type="text" name="q" placeholder="Search IPOs GMP..."
                    value="<?php echo isset($_GET['q']) ? esc_attr($_GET['q']) : ''; ?>"
                    class="bg-transparent border-none text-xs text-white focus:ring-0 w-full placeholder:text-slate-600 ml-2"
                    autocomplete="off">

                <!-- Loading Spinner -->
                <div id="tigc-search-loader" class="hidden absolute right-3 top-1/2 -translate-y-1/2">
                    <svg class="animate-spin h-4 w-4 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </div>

                <!-- Live Search Results -->
                <div id="tigc-search-results"
                    class="hidden absolute top-full left-0 w-full mt-2 bg-[#0B1220] border border-border-navy rounded-xl shadow-2xl overflow-hidden z-50">
                    <ul class="divide-y divide-border-navy max-h-64 overflow-y-auto no-scrollbar">
                        <!-- Results injected via JS -->
                    </ul>
                </div>
            </form>
        </div>

        <div class="flex items-center gap-10">
            <!-- Navigation -->
            <nav class="hidden md:flex items-center gap-8">
                <?php
                $nav = [
                    ['name' => 'Homepage', 'link' => home_url('/'), 'active' => is_front_page() || is_page('dashboard')],
                    ['name' => 'Mainboard', 'link' => home_url('/mainboard-ipos/'), 'active' => is_page('mainboard-ipos')],
                    ['name' => 'SME', 'link' => home_url('/sme-ipos/'), 'active' => is_page('sme-ipos')],
                    ['name' => 'Buybacks', 'link' => home_url('/buybacks/'), 'active' => is_page('buybacks')],
                ];

                foreach ($nav as $n):
                    $base_class = "text-[11px] font-bold uppercase tracking-[0.15em] transition-colors";
                    $color_class = $n['active'] ? "text-primary" : "text-slate-400 hover:text-white";
                    ?>
                    <a class="<?php echo "$base_class $color_class"; ?>" href="<?php echo esc_url($n['link']); ?>">
                        <?php echo esc_html($n['name']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <!-- Actions -->
            <div class="flex items-center gap-4">
                <!-- Hamburger Button (Mobile) -->
                <button id="tigc-mobile-toggle" onclick="toggleMobileMenu()" class="md:hidden text-white p-2">
                    <span class="material-symbols-outlined text-3xl">menu</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Menu Drawer -->
    <div id="tigc-mobile-menu"
        class="fixed inset-0 z-50 transform translate-x-full transition-transform duration-300 md:hidden">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="toggleMobileMenu()"></div>

        <!-- Menu Content -->
        <div
            class="absolute right-0 top-0 h-full w-[80%] max-w-[300px] bg-[#0B111D] border-l border-border-navy shadow-2xl p-6 flex flex-col">
            <div class="flex justify-between items-center mb-6">
                <span class="text-white font-bold text-lg">Menu</span>
                <button onclick="toggleMobileMenu()" class="text-slate-400 hover:text-white">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- Mobile Search -->
            <form action="<?php echo home_url('/mainboard-ipos/'); ?>" method="GET" class="mb-6 relative"
                onsubmit="var q = this.querySelector('input[name=\'q\']'); q.value = q.value.trim(); if(q.value === '') return false;">
                <input type="text" name="q" placeholder="Search IPOs..."
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg py-3 px-4 text-sm text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-slate-600">
                <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500">
                    <span class="material-symbols-outlined text-xl">search</span>
                </button>
            </form>

            <nav class="flex flex-col gap-6">
                <?php foreach ($nav as $n):
                    $color_class = $n['active'] ? "text-primary bg-primary/10 border-primary" : "text-slate-400 hover:text-white border-transparent hover:bg-slate-800";
                    ?>
                    <a class="text-sm font-bold uppercase tracking-wider px-4 py-3 rounded-lg border border-dashed transition-all <?php echo $color_class; ?>"
                        href="<?php echo esc_url($n['link']); ?>">
                        <?php echo esc_html($n['name']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="mt-auto pt-6 border-t border-border-navy">
                <p class="text-[10px] text-slate-500 text-center uppercase tracking-widest">© <?php echo date('Y'); ?>
                    IPO GMP</p>
            </div>
        </div>
    </div>
</div>

<style>
    .ticker-animate {
        animation: ticker-slide 20s linear infinite;
    }

    @keyframes ticker-slide {
        0% {
            transform: translateX(0);
        }

        100% {
            transform: translateX(-50%);
        }
    }

    .ticker-animate:hover {
        animation-play-state: paused;
    }

    .ticker-animate:hover {
        animation-play-state: paused;
    }

    /* Hide scrollbar for Chrome, Safari and Opera */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    /* Hide scrollbar for IE, Edge and Firefox */
    .no-scrollbar {
        -ms-overflow-style: none;
        /* IE and Edge */
        scrollbar-width: none;
        /* Firefox */
    }
</style>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('tigc-mobile-menu');
        // Toggle the translate class
        if (menu.classList.contains('translate-x-full')) {
            menu.classList.remove('translate-x-full');
        } else {
            menu.classList.add('translate-x-full');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('tigc-search-input');
        const resultsContainer = document.getElementById('tigc-search-results');
        const loader = document.getElementById('tigc-search-loader');
        const resultsList = resultsContainer.querySelector('ul');
        let debounceTimer;

        searchInput.addEventListener('input', function (e) {
            clearTimeout(debounceTimer);
            const term = e.target.value.trim();

            if (term.length < 2) {
                resultsContainer.classList.add('hidden');
                return;
            }

            debounceTimer = setTimeout(() => {
                const url = '<?php echo admin_url('admin-ajax.php'); ?>?action=tigc_ajax_search&term=' + encodeURIComponent(term);

                // Show Loader
                loader.classList.remove('hidden');

                fetch(url)
                    .then(response => response.json())
                    .then(response => {
                        // Hide Loader
                        loader.classList.add('hidden');

                        if (response.success && response.data.length > 0) {
                            renderResults(response.data);
                        } else {
                            resultsList.innerHTML = '<li class="p-3 text-xs text-slate-500 text-center">No results found.</li>';
                            resultsContainer.classList.remove('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        loader.classList.add('hidden'); // Ensure hide on error
                    });
            }, 300);
        });

        function renderResults(data) {
            resultsList.innerHTML = '';
            data.forEach(item => {
                const li = document.createElement('li');
                li.innerHTML = `
                <a href="<?php echo home_url('/ipo-details/'); ?>?slug=${item.slug}" class="flex items-center justify-between p-3 hover:bg-slate-800/50 transition-colors">
                    <div class="overflow-hidden mr-2">
                        <p class="text-xs font-bold text-white truncate text-ellipsis">${item.name}</p>
                        <p class="text-[10px] text-slate-500 uppercase">${item.status}</p>
                    </div>
                    ${item.gmp ? `<span class="text-xs font-bold text-neon-emerald whitespace-nowrap">+₹${item.gmp}</span>` : ''}
                </a>
            `;
                resultsList.appendChild(li);
            });
            resultsContainer.classList.remove('hidden');
        }

        // Close on click outside
        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                resultsContainer.classList.add('hidden');
            }
        });
    });
</script>