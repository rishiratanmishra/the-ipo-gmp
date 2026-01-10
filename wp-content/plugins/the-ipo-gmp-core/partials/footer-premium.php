<?php
/**
 * Shared Premium Footer Component
 */
?>
<footer class="mt-24 border-t border-border-muted pt-20 pb-10 px-6 bg-[#0B111D] relative">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 lg:gap-24 mb-20">
            
            <!-- Branding & About -->
            <div class="space-y-8">
                <a href="<?php echo home_url('/'); ?>" class="flex items-center gap-3 group">
                    <h2 class="text-white text-[28px] font-black leading-none tracking-tighter flex items-center font-display">
                        IPO<span class="text-neon-emerald">GMP</span><span class="text-primary text-4xl leading-none">.</span>
                    </h2>
                </a>
                <p class="text-slate-400 text-[13px] leading-relaxed font-normal">
                    The leading independent provider of IPO intelligence, subscription data, and exhaustive Grey Market Premium analysis for the Indian financial markets. 
                </p>
                <div class="flex items-center gap-5">
                    <a href="#" class="text-slate-500 hover:text-white transition-colors">
                        <svg class="size-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.045 4.126H5.078z"></path></svg>
                    </a>
                    <a href="#" class="text-slate-500 hover:text-white transition-colors">
                        <svg class="size-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.761 0 5-2.239 5-5v-14c0-2.761-2.239-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"></path></svg>
                    </a>
                </div>
            </div>

            <!-- Market Intelligence -->
            <div>
                <h4 class="text-white font-bold text-xs uppercase tracking-[0.2em] mb-8 font-sans">Intelligence</h4>
                <ul class="space-y-4">
                    <li><a href="<?php echo home_url('/'); ?>" class="text-slate-500 hover:text-primary text-[13px] font-medium transition-colors">Mainboard IPO Tracker</a></li>
                    <li><a href="<?php echo home_url('/'); ?>" class="text-slate-500 hover:text-primary text-[13px] font-medium transition-colors">SME IPO Monitoring</a></li>
                    <li><a href="<?php echo home_url('/buybacks/'); ?>" class="text-slate-500 hover:text-primary text-[13px] font-medium transition-colors">Buyback Directory</a></li>
                    <li><a href="#" class="text-slate-500 hover:text-primary text-[13px] font-medium transition-colors">Premium Analysis</a></li>
                </ul>
            </div>

            <!-- Investor Tools -->
            <div>
                <h4 class="text-white font-bold text-xs uppercase tracking-[0.2em] mb-8 font-sans">Investor Tools</h4>
                <ul class="space-y-4">
                    <li><a href="#" class="text-slate-500 hover:text-primary text-[13px] font-medium transition-colors">Best Trading Platforms</a></li>
                    <li><a href="#" class="text-slate-500 hover:text-primary text-[13px] font-medium transition-colors">GMP Historical Data</a></li>
                    <li><a href="#" class="text-slate-500 hover:text-primary text-[13px] font-medium transition-colors">Market News Wire</a></li>
                    <li><a href="#" class="text-slate-500 hover:text-primary text-[13px] font-medium transition-colors">Economic Calendar</a></li>
                </ul>
            </div>

            <!-- Corporate -->
            <div>
                <h4 class="text-white font-bold text-xs uppercase tracking-[0.2em] mb-8 font-sans">The Platform</h4>
                <ul class="space-y-4">
                    <li><a href="#" class="text-slate-500 hover:text-white text-[13px] font-medium transition-colors">About Our Methodology</a></li>
                    <li><a href="#" class="text-slate-500 hover:text-white text-[13px] font-medium transition-colors">Privacy & Data Policy</a></li>
                    <li><a href="#" class="text-slate-500 hover:text-white text-[13px] font-medium transition-colors">Terms of Service</a></li>
                    <li><a href="#" class="text-slate-500 hover:text-white text-[13px] font-medium transition-colors">Investor Disclaimer</a></li>
                </ul>
            </div>
        </div>

        <!-- Bottom Footer -->
        <div class="pt-10 border-t border-slate-800/50 flex flex-col md:flex-row justify-between items-center gap-8">
            <div class="flex flex-col gap-1">
                <p class="text-[12px] text-slate-500 font-medium">
                    © <?php echo date('Y'); ?> <span class="text-slate-300">The IPO GMP Platform</span>. All Data Subject to Market Volatility.
                </p>
                <p class="text-[10px] text-slate-600">Registered with institutional grade data feed providers.</p>
            </div>
            
            <div class="flex items-center gap-8">
                <div class="flex items-center gap-2.5 px-4 py-2 bg-slate-900/50 border border-slate-800 rounded-full">
                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Global Feed Live</span>
                </div>
                <div class="text-[11px] text-slate-600 font-medium italic">
                    Updated <?php echo date('H:i'); ?> IST
                </div>
            </div>
        </div>
    </div>
</footer>
