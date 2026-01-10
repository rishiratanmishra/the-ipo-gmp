<?php
/**
 * Sidebar Broker Widget
 * Optimized for narrow columns (Right Sidebar)
 */
global $wpdb;
$table_brokers = $wpdb->prefix . 'brokers';
$domestic_brokers = [];
$crypto_brokers = [];

// Check if table exists and fetch active brokers with categories
if($wpdb->get_var("SHOW TABLES LIKE '$table_brokers'") == $table_brokers) {
    $sql = "SELECT b.*, t.slug as cat_slug 
            FROM $table_brokers b
            LEFT JOIN {$wpdb->prefix}term_relationships tr ON (b.post_id = tr.object_id)
            LEFT JOIN {$wpdb->prefix}term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'broker_category')
            LEFT JOIN {$wpdb->prefix}terms t ON (tt.term_id = t.term_id)
            WHERE b.status='active'
            ORDER BY b.is_featured DESC, b.rating DESC";
            
    $brokers_data = $wpdb->get_results($sql);
    
    if($brokers_data) {
        foreach($brokers_data as $b) {
            $formatted = [
                'name'  => $b->title,
                'rating'=> $b->rating ?: '4.5',
                'color' => '#3b82f6',
                'icon'  => !empty($b->logo_url) ? $b->logo_url : substr($b->title, 0, 1),
                'link'  => $b->affiliate_link,
                'pros'  => !empty($b->pros) ? explode("\n", $b->pros) : ['Zero Equity Delivery', 'Free Account Opening'],
                'cons'  => !empty($b->cons) ? explode("\n", $b->cons) : [],
                'is_img'=> !empty($b->logo_url)
            ];

            if ($b->cat_slug === 'crypto') {
                $crypto_brokers[] = $formatted;
            } else {
                $domestic_brokers[] = $formatted; // Default to domestic
            }
        }
    }
}

// Fallbacks
if (empty($domestic_brokers) && empty($crypto_brokers)) {
    $domestic_brokers = [
        [
            'name' => 'Dhan', 
            'rating' => '4.6', 
            'color' => '#00d09c', 
            'icon' => 'D', 
            'link' => '#', 
            'is_img' => false,
            'pros' => ['Lighting Fast execution', 'Free Trading APIs'],
            'cons' => []
        ],
        [
            'name' => 'Kotak Neo', 
            'rating' => '4.2', 
            'color' => '#ef4444', 
            'icon' => 'K', 
            'link' => '#', 
            'is_img' => false,
            'pros' => ['Zero Brokerage Plan', 'Bank Backed Trust'],
            'cons' => []
        ],
    ];
}
?>
<div class="p-6 rounded-2xl bg-[#0B1220] border border-border-navy shadow-lg relative overflow-hidden">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-base">trending_up</span>
            Trading Apps
        </h3>
        <!-- Category Toggle -->
        <div class="flex bg-slate-900 border border-slate-800 rounded-lg p-0.5">
            <button onclick="switchBrokerTab('domestic')" id="tab-domestic" class="px-3 py-1 text-[10px] font-bold text-white bg-[#1E293B] rounded-md shadow-sm transition-all">Domestic</button>
            <button onclick="switchBrokerTab('crypto')" id="tab-crypto" class="px-3 py-1 text-[10px] font-bold text-slate-500 hover:text-slate-300 transition-all">Crypto</button>
        </div>
    </div>

    <!-- Domestic List -->
    <div id="list-domestic" class="space-y-3">
        <?php foreach($domestic_brokers as $index => $b): ?>
        <div class="group flex items-center justify-between p-3 rounded-xl bg-slate-900/40 border border-white/5 hover:border-primary/30 hover:bg-slate-900/60 transition-all">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-lg bg-white p-1 flex items-center justify-center font-black text-slate-900 shadow-sm shrink-0 overflow-hidden text-lg">
                    <?php if($b['is_img']): ?>
                        <img src="<?php echo esc_url($b['icon']); ?>" alt="<?php echo esc_attr($b['name']); ?>" class="w-full h-full object-contain">
                    <?php else: ?>
                        <?php echo $b['icon']; ?>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <h4 class="text-sm font-bold text-white leading-tight"><?php echo esc_html($b['name']); ?></h4>
                        <button onclick="openBrokerModal(<?php echo $index; ?>, 'domestic')" class="text-slate-500 hover:text-primary transition-colors">
                            <span class="material-symbols-outlined text-[16px] cursor-pointer">info</span>
                        </button>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="text-[10px] font-bold text-amber-400"><?php echo esc_html($b['rating']); ?></span>
                        <span class="material-symbols-outlined text-[10px] text-amber-400">star</span>
                    </div>
                </div>
            </div>
            <a href="<?php echo esc_url($b['link']); ?>" target="_blank" class="px-4 py-1.5 bg-primary hover:bg-blue-600 text-white text-[11px] font-black rounded-lg transition-colors shadow-lg shadow-primary/20">
                Open
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Crypto List -->
    <div id="list-crypto" class="space-y-3 hidden">
        <?php if(!empty($crypto_brokers)): foreach($crypto_brokers as $index => $b): ?>
        <div class="group flex items-center justify-between p-3 rounded-xl bg-slate-900/40 border border-white/5 hover:border-primary/30 hover:bg-slate-900/60 transition-all">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-lg bg-white p-1 flex items-center justify-center font-black text-slate-900 shadow-sm shrink-0 overflow-hidden text-lg">
                    <?php if($b['is_img']): ?>
                        <img src="<?php echo esc_url($b['icon']); ?>" alt="<?php echo esc_attr($b['name']); ?>" class="w-full h-full object-contain">
                    <?php else: ?>
                        <?php echo $b['icon']; ?>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <h4 class="text-sm font-bold text-white leading-tight"><?php echo esc_html($b['name']); ?></h4>
                        <button onclick="openBrokerModal(<?php echo $index; ?>, 'crypto')" class="text-slate-500 hover:text-primary transition-colors">
                            <span class="material-symbols-outlined text-[16px] cursor-pointer">info</span>
                        </button>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="text-[10px] font-bold text-amber-400"><?php echo esc_html($b['rating']); ?></span>
                        <span class="material-symbols-outlined text-[10px] text-amber-400">star</span>
                    </div>
                </div>
            </div>
            <a href="<?php echo esc_url($b['link']); ?>" target="_blank" class="px-4 py-1.5 bg-primary hover:bg-blue-600 text-white text-[11px] font-black rounded-lg transition-colors shadow-lg shadow-primary/20">
                Open
            </a>
        </div>
        <?php endforeach; else: ?>
        <div class="flex flex-col items-center justify-center py-8 text-center text-slate-500">
            <span class="material-symbols-outlined text-3xl mb-2 opacity-50">currency_bitcoin</span>
            <p class="text-xs font-medium">Crypto details coming soon.</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Disclosure -->
    <p class="mt-6 text-[10px] text-slate-600 font-medium text-center leading-relaxed">
        Disclosure: We may earn a commission if you open an account using the links above.
    </p>

    <!-- Modal (Hidden) -->
    <div id="broker-modal-backdrop" class="fixed inset-0 bg-[#0B1220]/90 backdrop-blur-sm z-[100] hidden flex items-end sm:items-center justify-center p-4 opacity-0 transition-opacity duration-300">
        <div id="broker-modal-content" class="bg-[#0F1623] border border-border-navy w-full max-w-md rounded-xl shadow-2xl p-5 transform translate-y-full sm:translate-y-10 transition-transform duration-300">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modal-title" class="text-lg font-black text-white">Broker Details</h3>
                <button onclick="closeBrokerModal()" class="text-slate-500 hover:text-white">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <div class="space-y-4">
                <div>
                    <h4 class="text-[10px] font-black text-emerald-400 uppercase tracking-widest mb-2">Why we like it</h4>
                    <ul id="modal-pros" class="space-y-1.5"></ul>
                </div>
                <div id="modal-cons-container" class="hidden">
                    <h4 class="text-[10px] font-black text-red-400 uppercase tracking-widest mb-2">Things to know</h4>
                    <ul id="modal-cons" class="space-y-1.5"></ul>
                </div>
            </div>

            <a id="modal-link" href="#" target="_blank" class="mt-6 block w-full py-3 bg-primary hover:bg-blue-600 text-white text-xs font-black rounded-lg text-center uppercase tracking-wider transition-colors">
                Open Account
            </a>
        </div>
    </div>

    <script>
        const domesticData = <?php echo json_encode($domestic_brokers); ?>;
        const cryptoData = <?php echo json_encode($crypto_brokers); ?>;
        const modalBackdrop = document.getElementById('broker-modal-backdrop');
        const modalContent = document.getElementById('broker-modal-content');

        // Move modal to body to avoid overflow clipping
        document.body.appendChild(modalBackdrop);
        
        function switchBrokerTab(type) {
            const btnDomestic = document.getElementById('tab-domestic');
            const btnCrypto = document.getElementById('tab-crypto');
            const listDomestic = document.getElementById('list-domestic');
            const listCrypto = document.getElementById('list-crypto');

            if(type === 'domestic') {
                // Active Domestic
                btnDomestic.className = 'px-3 py-1 text-[10px] font-bold text-white bg-[#1E293B] rounded-md shadow-sm transition-all';
                btnCrypto.className = 'px-3 py-1 text-[10px] font-bold text-slate-500 hover:text-slate-300 transition-all';
                listDomestic.classList.remove('hidden');
                listCrypto.classList.add('hidden');
            } else {
                // Active Crypto
                btnCrypto.className = 'px-3 py-1 text-[10px] font-bold text-white bg-[#1E293B] rounded-md shadow-sm transition-all';
                btnDomestic.className = 'px-3 py-1 text-[10px] font-bold text-slate-500 hover:text-slate-300 transition-all';
                listCrypto.classList.remove('hidden');
                listDomestic.classList.add('hidden');
            }
        }
        
        function openBrokerModal(index, type) {
            const data = (type === 'crypto') ? cryptoData[index] : domesticData[index];
            if(!data) return;

            document.getElementById('modal-title').textContent = data.name;
            document.getElementById('modal-link').href = data.link;
            
            // Pros
            const prosList = document.getElementById('modal-pros');
            prosList.innerHTML = '';
            if(data.pros && data.pros.length > 0) {
                data.pros.forEach(pro => {
                    if(pro) prosList.innerHTML += `<li class="text-xs text-slate-300 flex items-start gap-2"><span class="material-symbols-outlined text-sm text-emerald-500 shrink-0">check</span> ${pro}</li>`;
                });
            } else {
                prosList.innerHTML = '<li class="text-xs text-slate-500">No specific highlights.</li>';
            }

            // Cons
            const consContainer = document.getElementById('modal-cons-container');
            const consList = document.getElementById('modal-cons');
            consList.innerHTML = '';
            if(data.cons && data.cons.length > 0) {
                consContainer.classList.remove('hidden');
                data.cons.forEach(con => {
                    if(con) consList.innerHTML += `<li class="text-xs text-slate-300 flex items-start gap-2"><span class="material-symbols-outlined text-sm text-red-500 shrink-0">close</span> ${con}</li>`;
                });
            } else {
                consContainer.classList.add('hidden');
            }

            // Show
            modalBackdrop.classList.remove('hidden');
            // Trigger reflow
            void modalBackdrop.offsetWidth; 
            modalBackdrop.classList.remove('opacity-0');
            modalContent.classList.remove('translate-y-full', 'sm:translate-y-10');
            modalContent.classList.add('translate-y-0');
        }

        function closeBrokerModal() {
            modalBackdrop.classList.add('opacity-0');
            modalContent.classList.remove('translate-y-0');
            modalContent.classList.add('translate-y-full', 'sm:translate-y-10');
            
            setTimeout(() => {
                modalBackdrop.classList.add('hidden');
            }, 300);
        }
    </script>
</div>
