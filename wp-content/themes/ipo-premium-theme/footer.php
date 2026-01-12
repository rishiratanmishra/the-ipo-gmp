<?php
/**
 * The template for displaying the footer
 *
 * @package IPO_Premium
 */
?>

<footer class="mt-24 pt-20 pb-10 px-6 bg-card-dark relative border-t border-white/5">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 lg:gap-24 mb-20">

            <!-- Branding & About -->
            <div class="space-y-8">
                <a href="<?php echo home_url('/'); ?>" class="flex items-center gap-3 group">
                    <h2
                        class="text-white text-[28px] font-black leading-none tracking-tighter flex items-center font-display">
                        IPO<span class="text-neon-emerald">GMP</span><span
                            class="text-primary text-4xl leading-none">.</span>
                    </h2>
                </a>
                <p class="text-slate-400 text-[13px] leading-relaxed font-normal">
                    <?php echo esc_html(get_theme_mod('footer_description', 'The leading independent provider of IPO intelligence, subscription data, and exhaustive Grey Market Premium analysis.')); ?>
                </p>
                <!-- Socials -->
                <div class="flex items-center gap-5">
                    <?php if (get_theme_mod('social_twitter')): ?>
                        <a href="<?php echo esc_url(get_theme_mod('social_twitter', '#')); ?>"
                            class="text-slate-500 hover:text-white transition-colors"><span
                                class="material-symbols-outlined">public</span></a>
                    <?php endif; ?>
                    <?php if (get_theme_mod('social_facebook')): ?>
                        <a href="<?php echo esc_url(get_theme_mod('social_facebook', '#')); ?>"
                            class="text-slate-500 hover:text-white transition-colors"><span
                                class="material-symbols-outlined">rss_feed</span></a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Market Intelligence -->
            <div>
                <?php if (is_active_sidebar('footer-1')): ?>
                    <?php dynamic_sidebar('footer-1'); ?>
                <?php else: ?>
                    <h4 class="text-white font-bold text-xs uppercase tracking-[0.2em] mb-8 font-sans">Intelligence</h4>
                    <ul class="space-y-4">
                        <li><a href="<?php echo home_url('/'); ?>"
                                class="text-slate-500 hover:text-primary text-[13px] font-medium transition-colors">Mainboard
                                IPO Tracker</a></li>
                        <li><a href="<?php echo home_url('/sme-ipos/'); ?>"
                                class="text-slate-500 hover:text-primary text-[13px] font-medium transition-colors">SME IPO
                                Monitoring</a></li>
                        <li><a href="<?php echo home_url('/buybacks/'); ?>"
                                class="text-slate-500 hover:text-primary text-[13px] font-medium transition-colors">Buyback
                                Directory</a></li>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Investor Tools -->
            <div>
                <?php if (is_active_sidebar('footer-2')): ?>
                    <?php dynamic_sidebar('footer-2'); ?>
                <?php else: ?>
                    <h4 class="text-white font-bold text-xs uppercase tracking-[0.2em] mb-8 font-sans">Investor Tools</h4>
                    <ul class="space-y-4">
                        <li><a href="#"
                                class="text-slate-500 hover:text-primary text-[13px] font-medium transition-colors">GMP
                                Historical Data</a></li>
                        <li><a href="#"
                                class="text-slate-500 hover:text-primary text-[13px] font-medium transition-colors">Market
                                News Wire</a></li>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Corporate -->
            <div>
                <?php if (is_active_sidebar('footer-3')): ?>
                    <?php dynamic_sidebar('footer-3'); ?>
                <?php else: ?>
                    <h4 class="text-white font-bold text-xs uppercase tracking-[0.2em] mb-8 font-sans">The Platform</h4>
                    <ul class="space-y-4">
                        <li><a href="#"
                                class="text-slate-500 hover:text-white text-[13px] font-medium transition-colors">Privacy &
                                Data Policy</a></li>
                        <li><a href="#"
                                class="text-slate-500 hover:text-white text-[13px] font-medium transition-colors">Terms of
                                Service</a></li>
                        <li><a href="#"
                                class="text-slate-500 hover:text-white text-[13px] font-medium transition-colors">Disclaimer</a>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bottom Footer -->
        <div class="pt-10 border-t border-slate-800/50 flex flex-col md:flex-row justify-between items-center gap-8">
            <div class="flex flex-col gap-1">
                <p class="text-[12px] text-slate-500 font-medium">
                    ©
                    <?php echo date('Y'); ?> <span
                        class="text-slate-300"><?php echo esc_html(get_theme_mod('footer_copyright', 'IPO GMP Premium')); ?></span>.
                    All rights reserved.
                </p>
            </div>

            <?php if (get_theme_mod('footer_badge', true)): ?>
                <div class="flex items-center gap-8">
                    <div class="flex items-center gap-2.5 px-4 py-2 bg-slate-900/50 border border-slate-800 rounded-full">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">
                            <?php echo esc_html(get_theme_mod('footer_badge_label', 'System Operational')); ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>

</html>