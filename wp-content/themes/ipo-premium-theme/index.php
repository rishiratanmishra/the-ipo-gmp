<?php
/**
 * The main template file
 * Refactored for Premium Professional "Insights" Magazine Layout
 */

get_header();
?>

<main class="max-w-[1400px] mx-auto px-4 md:px-10 py-16">

    <!-- Hero Section with Subtle Glow -->
    <header class="relative mb-24 text-left">
        <!-- Background Glow Effect -->
        <div
            class="absolute top-1/2 left-0 -translate-y-1/2 w-[300px] h-[300px] bg-primary/20 blur-[120px] rounded-full pointer-events-none z-0">
        </div>

        <div class="relative z-10">
            <h1 class="text-5xl md:text-7xl font-black text-white tracking-tighter mb-6 leading-tight">
                <span class="text-white">Financial</span>
                <span class="text-neon-emerald">Insights.</span>
            </h1>
            <p class="text-slate-400 text-lg md:text-xl max-w-2xl font-medium leading-relaxed">
                Expert analysis, IPO breakdowns, and market trends curated for the intelligent investor.
            </p>
        </div>
    </header>

    <?php
    // Fetch categories with content
    $categories = get_categories([
        'orderby' => 'count',
        'order' => 'DESC',
        'hide_empty' => true,
        'exclude' => 1
    ]);

    if (!empty($categories)):
        foreach ($categories as $index => $cat):
            // Query 3 recent posts
            $cat_query = new WP_Query([
                'cat' => $cat->term_id,
                'posts_per_page' => 3,
                'post_status' => 'publish'
            ]);

            if ($cat_query->have_posts()):
                ?>
                <section class="mb-24 relative">
                    <!-- Section Header -->
                    <div class="flex items-end justify-between mb-10 border-b border-white/5 pb-4">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 rounded-xl bg-slate-800/50 flex items-center justify-center border border-white/5 shadow-inner">
                                <span class="material-symbols-outlined text-primary text-2xl">
                                    <?php echo ($index % 2 == 0) ? 'trending_up' : 'receipt_long'; ?>
                                </span>
                            </div>
                            <div>
                                <span
                                    class="text-slate-500 text-[10px] font-bold uppercase tracking-widest block mb-1">Explore</span>
                                <h2 class="text-3xl font-black text-white tracking-tight"><?php echo esc_html($cat->name); ?></h2>
                            </div>
                        </div>

                        <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>"
                            class="hidden md:flex group items-center gap-2 text-xs font-bold text-slate-400 hover:text-white transition-colors">
                            View All <?php echo esc_html($cat->name); ?>
                            <div
                                class="w-6 h-6 rounded-full bg-slate-800 flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-all">
                                <span class="material-symbols-outlined text-[10px]">arrow_forward</span>
                            </div>
                        </a>
                    </div>

                    <!-- Cards Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <?php
                        while ($cat_query->have_posts()):
                            $cat_query->the_post();
                            ?>
                            <article id="post-<?php the_ID(); ?>" <?php post_class('group flex flex-col h-full relative hover:-translate-y-2 transition-all duration-500'); ?>>

                                <!-- Card Background with Gradient Border Effect -->
                                <div
                                    class="absolute inset-0 bg-slate-900 rounded-3xl border border-white/5 group-hover:border-primary/20 transition-all duration-500 shadow-xl group-hover:shadow-2xl group-hover:shadow-primary/5 z-0">
                                </div>

                                <!-- Image Container -->
                                <div class="relative h-56 m-3 mb-0 rounded-2xl overflow-hidden z-10">
                                    <a href="<?php the_permalink(); ?>" class="block w-full h-full">
                                        <?php if (has_post_thumbnail()): ?>
                                            <?php the_post_thumbnail('medium_large', ['class' => 'w-full h-full object-cover transition-transform duration-700 group-hover:scale-105']); ?>
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center bg-slate-800 pattern-grid-lg">
                                                <span class="material-symbols-outlined text-4xl text-slate-700">article</span>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Overlay Gradient -->
                                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/40 to-transparent"></div>
                                    </a>

                                    <!-- Date Badge -->
                                    <div
                                        class="absolute top-3 right-3 bg-black/40 backdrop-blur-md border border-white/10 text-white text-[10px] font-bold py-1.5 px-3 rounded-lg flex flex-col items-center leading-none">
                                        <span class="block text-[14px] mb-0.5"><?php echo get_the_date('d'); ?></span>
                                        <span class="block text-slate-400 uppercase"><?php echo get_the_date('M'); ?></span>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 p-6 relative z-10 flex flex-col">
                                    <!-- Meta -->
                                    <div
                                        class="flex items-center gap-3 mb-4 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                        <span class="flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                                            Read
                                        </span>
                                        <span class="text-slate-700">•</span>
                                        <span><?php echo get_the_author(); ?></span>
                                    </div>

                                    <!-- Title -->
                                    <h3
                                        class="text-xl font-bold text-white mb-3 leading-snug group-hover:text-primary transition-colors line-clamp-2">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_title(); ?>
                                        </a>
                                    </h3>

                                    <!-- Excerpt -->
                                    <div
                                        class="text-slate-400 text-sm leading-relaxed line-clamp-2 mb-6 opacity-80 group-hover:opacity-100 transition-opacity">
                                        <?php the_excerpt(); ?>
                                    </div>

                                    <!-- Tags / Footer -->
                                    <div class="mt-auto pt-5 border-t border-dashed border-white/5 flex items-center justify-between">
                                        <?php
                                        $display_cats = get_the_category();
                                        if (!empty($display_cats)):
                                            ?>
                                            <span
                                                class="text-[10px] font-black text-slate-500 bg-slate-800/50 px-2 py-1 rounded border border-white/5 uppercase tracking-wide">
                                                <?php echo esc_html($display_cats[0]->name); ?>
                                            </span>
                                        <?php endif; ?>

                                        <a href="<?php the_permalink(); ?>"
                                            class="text-[11px] font-bold text-primary hover:text-emerald-400 transition-colors uppercase tracking-widest flex items-center gap-1">
                                            Read Now
                                            <span
                                                class="material-symbols-outlined text-[14px] group-hover:translate-x-1 transition-transform">trending_flat</span>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php endwhile;
                        wp_reset_postdata(); ?>
                    </div>

                    <!-- Mobile Button -->
                    <div class="mt-8 md:hidden text-center">
                        <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>"
                            class="inline-block w-full py-4 rounded-xl bg-slate-800 border border-white/10 text-sm font-bold text-white active:scale-95 transition-transform">
                            Browse all <?php echo esc_html($cat->name); ?>
                        </a>
                    </div>
                </section>
                <?php
            endif; // Have posts
        endforeach;
    else:
        ?>
        <div class="py-32 text-center">
            <h2 class="text-2xl font-bold text-white mb-2">No Content Available</h2>
            <p class="text-slate-500">Check back later for market updates.</p>
        </div>
    <?php endif; ?>

</main>

<style>
    /* Optional: Add a subtle grid pattern utility if not in tailwind config */
    .pattern-grid-lg {
        background-image: linear-gradient(to right, #1e293b 1px, transparent 1px),
            linear-gradient(to bottom, #1e293b 1px, transparent 1px);
        background-size: 20px 20px;
    }
</style>

<?php
get_footer();
