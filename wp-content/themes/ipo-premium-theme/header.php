<?php
/**
 * The header for our theme
 *
 * @package IPO_Premium
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="dark">

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class('bg-background-dark text-slate-100 font-display antialiased'); ?>>
    <?php wp_body_open(); ?>

    <?php
    // Ticker Logic
    $enable_ticker = get_theme_mod('enable_ticker', true);
    if ($enable_ticker) {
        global $wpdb;
        $t_master = $wpdb->prefix . 'ipomaster';
        // Safe check if table exists to avoid errors on fresh install
        if ($wpdb->get_var("SHOW TABLES LIKE '$t_master'") == $t_master) {
            $ticker_data = $wpdb->get_results("
            SELECT name, premium as gmp, status FROM $t_master 
            WHERE status NOT LIKE '%Closed%' 
            ORDER BY updated_at DESC LIMIT 15
        ");
        } else {
            $ticker_data = [];
        }
    }
    ?>

    <div class="sticky top-0 z-50">
        <!-- Live Ticker -->
        <?php if ($enable_ticker && !empty($ticker_data)): ?>
            <div class="overflow-hidden h-7 bg-header-dark flex items-center relative">
                <!-- Ticker Wrapper -->
                <div class="ticker-animate flex items-center whitespace-nowrap min-w-full">
                    <?php
                    // Prepare Ticker Content Function
                    $ticker_label = get_theme_mod('ticker_label', 'Market Pulse:');

                    // Closure to render one set of data
                    $render_set = function () use ($ticker_data, $ticker_label) {
                        ?>
                        <div class="flex items-center gap-12 px-10">
                            <!-- Label -->
                            <span
                                class="text-[9px] font-bold text-slate-500 uppercase tracking-[0.2em] flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <?php echo esc_html($ticker_label); ?>
                            </span>

                            <!-- Items -->
                            <?php foreach ($ticker_data as $t):
                                $gmp_clean = preg_replace('/[^0-9]/', '', $t->gmp);
                                $is_pos = $gmp_clean > 0;
                                $color = $is_pos ? 'text-neon-emerald' : 'text-slate-400';
                                ?>
                                <span class="text-[11px] font-medium text-slate-400 flex items-center gap-2">
                                    <span class="text-slate-500"><?php echo esc_html($t->name); ?></span>
                                    <span
                                        class="<?php echo $color; ?> font-bold"><?php echo $t->gmp ? '₹' . $t->gmp : '₹0'; ?></span>
                                </span>
                            <?php endforeach; ?>
                        </div>
                        <?php
                    };

                    // Render Twice for Seamless Loop
                    $render_set();
                    $render_set();
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Main Header -->
        <header
            class="flex items-center justify-between whitespace-nowrap bg-header-dark/95 backdrop-blur-md px-4 md:px-10 py-3.5 border-b border-white/5">
            <div class="flex items-center gap-10">
                <!-- Logo -->
                <a href="<?php echo home_url('/'); ?>" class="flex items-center gap-3 group">
                    <?php if (has_custom_logo()): ?>
                        <?php the_custom_logo(); ?>
                    <?php else: ?>
                        <h2
                            class="text-white text-2xl font-black leading-none tracking-tighter flex items-center font-display">
                            IPO<span class="text-neon-emerald">GMP</span><span
                                class="text-primary text-4xl leading-none">.</span>
                        </h2>
                    <?php endif; ?>
                </a>

                <!-- Search Bar (Header) -->
                <form action="<?php echo home_url('/ipo-details/'); ?>" method="GET"
                    class="hidden lg:flex items-center w-96 h-10 bg-slate-900 border border-slate-800 rounded-lg px-4 group focus-within:border-primary/40 transition-all relative">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input id="tigc-search-input" type="text" name="q"
                        placeholder="<?php echo esc_attr(get_theme_mod('search_placeholder', 'Search IPOs GMP...')); ?>"
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
                        class="hidden absolute top-full left-0 w-full mt-2 bg-card-dark border border-border-navy rounded-xl shadow-2xl overflow-hidden z-50">
                        <ul class="divide-y divide-border-navy max-h-64 overflow-y-auto no-scrollbar"></ul>
                    </div>
                </form>
            </div>

            <div class="flex items-center gap-10">
                <!-- Navigation -->
                <!-- Navigation -->
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'container' => 'nav',
                    'container_class' => 'hidden md:flex items-center gap-8',
                    'menu_class' => 'flex items-center gap-8', // Ensuring UL inherits layout structure
                    'fallback_cb' => false,
                    'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                ]);
                ?>

                <!-- Actions -->
                <div class="flex items-center gap-4">
                    <button id="tigc-mobile-toggle" onclick="toggleMobileMenu()" class="md:hidden text-white p-2">
                        <span class="material-symbols-outlined text-3xl">menu</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Mobile Menu Drawer (Copied Logic) -->
        <div id="tigc-mobile-menu"
            class="fixed inset-0 z-50 transform translate-x-full transition-transform duration-300 md:hidden">
            <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="toggleMobileMenu()"></div>
            <div
                class="absolute right-0 top-0 h-full w-[80%] max-w-[300px] bg-header-dark border-l border-border-navy shadow-2xl p-6 flex flex-col">
                <div class="flex justify-between items-center mb-6">
                    <span class="text-white font-bold text-lg">Menu</span>
                    <button onclick="toggleMobileMenu()" class="text-slate-400 hover:text-white">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form action="<?php echo home_url('/ipo-details/'); ?>" method="GET" class="mb-6 relative"
                    onsubmit="var q = this.querySelector('input[name=\'q\']'); q.value = q.value.trim(); if(q.value === '') return false;">
                    <input id="tigc-mobile-search-input" type="text" name="q" placeholder="Search IPOs..."
                        class="w-full bg-slate-900 border border-slate-700 rounded-lg py-3 px-4 text-sm text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-slate-600"
                        autocomplete="off">
                    <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500">
                        <span class="material-symbols-outlined text-xl">search</span>
                    </button>
                    <!-- Mobile Search Results -->
                    <div id="tigc-mobile-search-results"
                        class="hidden absolute top-full left-0 w-full mt-2 bg-card-dark border border-border-navy rounded-xl shadow-2xl overflow-hidden z-50">
                        <ul class="divide-y divide-border-navy max-h-48 overflow-y-auto no-scrollbar"></ul>
                    </div>
                </form>
                <nav class="flex flex-col gap-6">
                    <nav class="flex flex-col gap-6">
                        <?php
                        $locations = get_nav_menu_locations();
                        $menu_items = [];
                        if (isset($locations['primary'])) {
                            $menu = get_term($locations['primary'], 'nav_menu');
                            if ($menu) {
                                $menu_items = wp_get_nav_menu_items($menu->term_id);
                            }
                        }

                        if ($menu_items) {
                            foreach ($menu_items as $item) {
                                $is_active = (is_home() && $item->url == home_url('/')) || ($post && get_permalink($post->ID) == $item->url);
                                $color_class = $is_active ? "text-primary bg-primary/10 border-primary" : "text-slate-400 hover:text-white border-transparent hover:bg-slate-800";
                                ?>
                                <a class="text-sm font-bold uppercase tracking-wider px-4 py-3 rounded-lg border border-dashed transition-all <?php echo $color_class; ?>"
                                    href="<?php echo esc_url($item->url); ?>">
                                    <?php echo esc_html($item->title); ?>
                                </a>
                                <?php
                            }
                        } else {
                            echo '<p class="text-slate-500 text-xs px-4">No menu assigned.</p>';
                        }
                        ?>
                    </nav>
                </nav>
            </div>
        </div>
    </div>

    <style>
        @keyframes ticker-slide {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-50%);
            }
        }

        @-webkit-keyframes ticker-slide {
            0% {
                -webkit-transform: translateX(0);
            }

            100% {
                -webkit-transform: translateX(-50%);
            }
        }

        .ticker-animate {
            display: flex;
            width: max-content;
            animation: ticker-slide 20s linear infinite;
            will-change: transform;
        }

        .ticker-animate:hover {
            animation-play-state: paused;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('tigc-mobile-menu');
            menu.classList.toggle('translate-x-full');
        }
        // Search JS logic should ideally be key-bound or checked, ensuring it runs only once.
        // For now, simpler inline to match previous implementation.
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('tigc-search-input');
            if (!searchInput) return;
            const resultsContainer = document.getElementById('tigc-search-results');
            const loader = document.getElementById('tigc-search-loader');
            const resultsList = resultsContainer.querySelector('ul');
            let debounceTimer;

            searchInput.addEventListener('input', function (e) {
                clearTimeout(debounceTimer);
                const term = e.target.value.trim();
                if (term.length < 2) { resultsContainer.classList.add('hidden'); return; }

                debounceTimer = setTimeout(() => {
                    const url = '<?php echo admin_url('admin-ajax.php'); ?>?action=tigc_ajax_search&term=' + encodeURIComponent(term);
                    loader.classList.remove('hidden');
                    fetch(url).then(r => r.json()).then(res => {
                        loader.classList.add('hidden');
                        if (res.success && res.data.length > 0) {
                            resultsList.innerHTML = '';
                            res.data.forEach(item => {
                                const li = document.createElement('li');
                                li.innerHTML = `<a href="<?php echo home_url('/ipo-details/'); ?>?slug=${item.slug}" class="flex items-center justify-between p-3 hover:bg-slate-800/50 transition-colors"><div class="overflow-hidden mr-2"><p class="text-xs font-bold text-white truncate">${item.name}</p><p class="text-[10px] text-slate-500 uppercase">${item.status}</p></div>${item.gmp ? `<span class="text-xs font-bold text-neon-emerald whitespace-nowrap">+₹${item.gmp}</span>` : ''}</a>`;
                                resultsList.appendChild(li);
                            });
                            resultsContainer.classList.remove('hidden');
                        } else {
                            resultsList.innerHTML = '<li class="p-3 text-xs text-slate-500 text-center">No results found.</li>';
                            resultsContainer.classList.remove('hidden');
                        }
                    });
                }, 300);
            });
        });

        // Mobile Search Logic
        const mobileInput = document.getElementById('tigc-mobile-search-input');
        const mobileResultsContainer = document.getElementById('tigc-mobile-search-results');
        let mobileDebounceTimer; // Separate timer for mobile

        if (mobileInput && mobileResultsContainer) {
            const mobileList = mobileResultsContainer.querySelector('ul');

            mobileInput.addEventListener('input', function (e) {
                clearTimeout(mobileDebounceTimer);
                const term = e.target.value.trim();

                if (term.length < 2) {
                    mobileResultsContainer.classList.add('hidden');
                    return;
                }

                mobileDebounceTimer = setTimeout(() => {
                    const url = '<?php echo admin_url('admin-ajax.php'); ?>?action=tigc_ajax_search&term=' + encodeURIComponent(term);

                    fetch(url)
                        .then(r => r.json())
                        .then(res => {
                            if (res.success && res.data.length > 0) {
                                mobileList.innerHTML = '';
                                res.data.forEach(item => {
                                    const li = document.createElement('li');
                                    const gmpHtml = item.gmp ? `<span class="text-xs font-bold text-neon-emerald whitespace-nowrap">+₹${item.gmp}</span>` : '';

                                    li.innerHTML = `
                                            <a href="<?php echo home_url('/ipo-details/'); ?>?slug=${item.slug}" class="flex items-center justify-between p-3 hover:bg-slate-800/50 transition-colors border-b border-border-navy last:border-0">
                                                <div class="overflow-hidden mr-2">
                                                    <p class="text-xs font-bold text-white truncate">${item.name}</p>
                                                    <p class="text-[10px] text-slate-500 uppercase">${item.status}</p>
                                                </div>
                                                ${gmpHtml}
                                            </a>`;
                                    mobileList.appendChild(li);
                                });
                                mobileResultsContainer.classList.remove('hidden');
                            } else {
                                mobileList.innerHTML = '<li class="p-3 text-xs text-slate-500 text-center">No results found.</li>';
                                mobileResultsContainer.classList.remove('hidden');
                            }
                        })
                        .catch(err => {
                            console.error('Mobile search error:', err);
                        });
                }, 300);
            });
        }

        document.addEventListener('click', function (e) {
            if (searchInput && !searchInput.contains(e.target) && resultsContainer && !resultsContainer.contains(e.target)) {
                resultsContainer.classList.add('hidden');
            }
            if (mobileInput && !mobileInput.contains(e.target) && mobileResultsContainer && !mobileResultsContainer.contains(e.target)) {
                mobileResultsContainer.classList.add('hidden');
            }
        });
    </script>