<?php
/**
 * The template for displaying all single posts
 */

get_header();
?>

<main class="max-w-[1000px] mx-auto px-4 md:px-6 py-10">
    <?php
    while (have_posts()):
        the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

            <?php if (has_post_thumbnail()): ?>
                <div class="mb-8 rounded-xl overflow-hidden shadow-2xl border border-border-navy">
                    <?php the_post_thumbnail('full', ['class' => 'w-full h-auto object-cover']); ?>
                </div>
            <?php endif; ?>

            <div class="glass-card p-8 md:p-10 rounded-2xl border border-border-navy bg-card-dark">
                <!-- Header -->
                <header class="mb-8 border-b border-border-navy pb-8">
                    <div class="flex items-center gap-2 text-primary font-bold text-sm uppercase tracking-wider mb-3">
                        <?php echo get_the_category_list(', '); ?>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-white leading-tight mb-4">
                        <?php the_title(); ?>
                    </h1>
                    <div class="flex items-center text-slate-400 text-sm">
                        <span>
                            <?php echo get_the_date(); ?>
                        </span>
                        <span class="mx-2">•</span>
                        <span>By IPO GMP AI</span>
                    </div>
                </header>

                <!-- Content -->
                <div class="entry-content prose prose-invert prose-lg max-w-none text-slate-300">
                    <?php the_content(); ?>
                </div>

                <!-- Footer -->
                <footer class="mt-8 pt-8 border-t border-border-navy">
                     <p class="text-xs text-slate-500 text-center">
                        &copy; <?php echo date('Y'); ?> The IPO GMP. All Market Data is indicative and for educational purposes only.
                     </p>
                </footer>
            </div>
        </article>
        <?php
    endwhile;
    ?>
</main>

<style>
    /* Premium Typography for Prose */
    .prose h2 {
        color: #fff;
        font-size: 1.8rem;
        margin-top: 2em;
        margin-bottom: 1em;
    }

    .prose h3 {
        color: #e2e8f0;
        font-size: 1.4rem;
        margin-top: 1.5em;
        margin-bottom: 0.8em;
    }

    .prose p {
        margin-bottom: 1.5em;
        line-height: 1.8;
    }

    .prose ul {
        list-style: disc;
        padding-left: 1.5em;
        margin-bottom: 1.5em;
    }

    .prose table {
        width: 100%;
        border-collapse: collapse;
        margin: 2em 0;
    }

    .prose th,
    .prose td {
        border: 1px solid #1e293b;
        padding: 12px;
        font-size: 0.95em;
    }

    .prose th {
        background: #0f172a;
        color: #fff;
    }

    .prose a {
        color: #00ff7f;
        text-decoration: none;
    }

    .prose a:hover {
        text-decoration: underline;
    }

    /* Dynamic Data Tokens */
    .ipo-dynamic-data {
        color: #00ff7f;
        font-weight: bold;
        background: rgba(0, 255, 127, 0.1);
        padding: 2px 6px;
        rounded: 4px;
    }
</style>

<?php
get_footer();
