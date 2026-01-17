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
                <?php
                $footer_logo = get_theme_mod('footer_logo');
                if ($footer_logo): ?>
                    <a href="<?php echo home_url('/'); ?>" class="block mb-6">
                        <img src="<?php echo esc_url($footer_logo); ?>" alt="<?php bloginfo('name'); ?>"
                            class="h-10 w-auto">
                    </a>
                <?php else: ?>
                    <a href="<?php echo home_url('/'); ?>" class="flex items-center gap-3 group">
                        <h2
                            class="text-white text-[28px] font-black leading-none tracking-tighter flex items-center font-display">
                            IPO<span class="text-neon-emerald">GMP</span><span
                                class="text-primary text-4xl leading-none">.</span>
                        </h2>
                    </a>
                <?php endif; ?>
                <p class="text-slate-400 text-[13px] leading-relaxed font-normal font-body">
                    <?php echo esc_html(get_theme_mod('footer_description', 'The leading independent provider of IPO intelligence, subscription data, and exhaustive Grey Market Premium analysis.')); ?>
                </p>
                <!-- Socials -->
                <div class="flex items-center gap-5">
                    <?php if (get_theme_mod('social_twitter')): ?>
                        <a href="<?php echo esc_url(get_theme_mod('social_twitter', '#')); ?>"
                            class="text-slate-500 hover:text-white transition-colors" target="_blank"
                            aria-label="X (Twitter)">
                            <!-- X Logo -->
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                            </svg>
                        </a>
                    <?php endif; ?>

                    <?php if (get_theme_mod('social_linkedin')): ?>
                        <a href="<?php echo esc_url(get_theme_mod('social_linkedin', '#')); ?>"
                            class="text-slate-500 hover:text-[#0077b5] transition-colors" target="_blank"
                            aria-label="LinkedIn">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                    <?php endif; ?>

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
                <?php if (get_theme_mod('footer_col1_menu')):
                    $menu_id = get_theme_mod('footer_col1_menu');
                    ?>
                    <h4 class="text-white font-bold text-xs uppercase tracking-[0.2em] mb-8 font-display">
                        <?php echo esc_html(get_theme_mod('footer_col1_title', 'Intelligence')); ?>
                    </h4>
                    <?php wp_nav_menu([
                        'menu' => $menu_id,
                        'container' => false,
                        'menu_class' => 'space-y-4',
                        'link_before' => '',
                        'link_after' => '',
                    ]); ?>
                    <!-- Style the items via css or filter, but for now standard ul output is okay if we add class to ul -->
                    <!-- Note: default wp_nav_menu layout is li > a. We need to match styles. -->
                    <style>
                        /* Quick fix to style the menu items if not standard */
                        .space-y-4 li {
                            list-style: none;
                            margin: 0;
                        }

                        .space-y-4 li a {
                            color: #64748b;
                            font-size: 13px;
                            font-weight: 500;
                            transition: color 0.2s;
                            text-decoration: none;
                            font-family: var(--font-body);
                        }

                        .space-y-4 li a:hover {
                            color: var(--color-primary);
                        }
                    </style>
                <?php elseif (is_active_sidebar('footer-1')): ?>
                    <?php dynamic_sidebar('footer-1'); ?>
                <?php else: ?>
                    <h4 class="text-white font-bold text-xs uppercase tracking-[0.2em] mb-8 font-display">
                        <?php echo esc_html(get_theme_mod('footer_col1_title', 'Intelligence')); ?>
                    </h4>
                    <ul class="space-y-4">
                        <li><a href="<?php echo home_url('/'); ?>"
                                class="text-slate-500 hover:text-primary text-[13px] font-medium transition-colors font-body">Mainboard
                                IPO Tracker</a></li>
                        <li><a href="<?php echo home_url('/sme-ipos/'); ?>"
                                class="text-slate-500 hover:text-primary text-[13px] font-medium transition-colors font-body">SME
                                IPO
                                Monitoring</a></li>
                        <li><a href="<?php echo home_url('/buybacks/'); ?>"
                                class="text-slate-500 hover:text-primary text-[13px] font-medium transition-colors font-body">Buyback
                                Directory</a></li>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Investor Tools -->
            <div>
                <?php if (get_theme_mod('footer_col2_menu')):
                    $menu_id = get_theme_mod('footer_col2_menu');
                    ?>
                    <h4 class="text-white font-bold text-xs uppercase tracking-[0.2em] mb-8 font-display">
                        <?php echo esc_html(get_theme_mod('footer_col2_title', 'Investor Tools')); ?>
                    </h4>
                    <?php wp_nav_menu([
                        'menu' => $menu_id,
                        'container' => false,
                        'menu_class' => 'space-y-4',
                    ]); ?>
                <?php elseif (is_active_sidebar('footer-2')): ?>
                    <?php dynamic_sidebar('footer-2'); ?>
                <?php else: ?>
                    <h4 class="text-white font-bold text-xs uppercase tracking-[0.2em] mb-8 font-display">
                        <?php echo esc_html(get_theme_mod('footer_col2_title', 'Investor Tools')); ?>
                    </h4>
                    <ul class="space-y-4">
                        <li><a href="#"
                                class="text-slate-500 hover:text-primary text-[13px] font-medium transition-colors font-body">GMP
                                Historical Data</a></li>
                        <li><a href="#"
                                class="text-slate-500 hover:text-primary text-[13px] font-medium transition-colors font-body">Market
                                News Wire</a></li>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Corporate -->
            <div>
                <?php if (get_theme_mod('footer_col3_menu')):
                    $menu_id = get_theme_mod('footer_col3_menu');
                    ?>
                    <h4 class="text-white font-bold text-xs uppercase tracking-[0.2em] mb-8 font-display">
                        <?php echo esc_html(get_theme_mod('footer_col3_title', 'The Platform')); ?>
                    </h4>
                    <?php wp_nav_menu([
                        'menu' => $menu_id,
                        'container' => false,
                        'menu_class' => 'space-y-4',
                    ]); ?>
                <?php elseif (is_active_sidebar('footer-3')): ?>
                    <?php dynamic_sidebar('footer-3'); ?>
                <?php else: ?>
                    <h4 class="text-white font-bold text-xs uppercase tracking-[0.2em] mb-8 font-display">
                        <?php echo esc_html(get_theme_mod('footer_col3_title', 'The Platform')); ?>
                    </h4>
                    <ul class="space-y-4">
                        <li><a href="#"
                                class="text-slate-500 hover:text-white text-[13px] font-medium transition-colors font-body">Privacy
                                &
                                Data Policy</a></li>
                        <li><a href="#"
                                class="text-slate-500 hover:text-white text-[13px] font-medium transition-colors font-body">Terms
                                of
                                Service</a></li>
                        <li><a href="#"
                                class="text-slate-500 hover:text-white text-[13px] font-medium transition-colors font-body">Disclaimer</a>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bottom Footer -->
        <div class="pt-10 border-t border-slate-800/50 flex flex-col md:flex-row justify-between items-center gap-8">
            <div class="flex flex-col gap-1">
                <p class="text-[12px] text-slate-500 font-medium font-body">
                    ©
                    <?php echo date('Y'); ?> <span
                        class="text-slate-300"><?php echo esc_html(get_theme_mod('footer_copyright', 'IPO GMP Premium')); ?></span>.
                    All rights reserved.
                </p>
            </div>

            <?php if (get_theme_mod('footer_badge', true)): ?>
                <div class="flex items-center gap-8">
                    <?php
                    $badge_link = get_theme_mod('footer_badge_link');
                    if ($badge_link):
                        ?>
                        <a href="<?php echo esc_url($badge_link); ?>" target="_blank"
                            class="flex items-center gap-2.5 px-4 py-2 bg-slate-900/50 border border-slate-800 rounded-full hover:bg-slate-800/80 transition-colors group">
                            <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 group-hover:animate-pulse"></div>
                            <span
                                class="text-[10px] text-slate-400 font-bold uppercase tracking-widest group-hover:text-slate-300 transition-colors">
                                <?php echo esc_html(get_theme_mod('footer_badge_label', 'System Operational')); ?>
                            </span>
                        </a>
                    <?php else: ?>
                        <div class="flex items-center gap-2.5 px-4 py-2 bg-slate-900/50 border border-slate-800 rounded-full">
                            <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div>
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">
                                <?php echo esc_html(get_theme_mod('footer_badge_label', 'System Operational')); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>

</html>