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
                    <?php if (get_theme_mod('social_instagram')): ?>
                        <a href="<?php echo esc_url(get_theme_mod('social_instagram', '#')); ?>"
                            class="text-slate-500 hover:text-[#E1306C] transition-colors" target="_blank"
                            aria-label="Instagram">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                            </svg>
                        </a>
                    <?php endif; ?>
                    <?php if (get_theme_mod('social_youtube')): ?>
                        <a href="<?php echo esc_url(get_theme_mod('social_youtube', '#')); ?>"
                            class="text-slate-500 hover:text-[#FF0000] transition-colors" target="_blank"
                            aria-label="YouTube">
                            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                            </svg>
                        </a>
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