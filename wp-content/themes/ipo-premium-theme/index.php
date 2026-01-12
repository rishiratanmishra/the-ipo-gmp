<?php
/**
 * The main template file
 */

get_header();
?>

<main class="max-w-[1280px] mx-auto px-4 md:px-10 py-8">
    <?php
    if (have_posts()):
        while (have_posts()):
            the_post();
            ?>
            <div class="glass-card mb-6 p-6 rounded-xl border border-border-navy bg-card-dark">
                <h2 class="text-2xl font-bold text-white mb-2">
                    <a href="<?php the_permalink(); ?>" class="hover:text-primary transition-colors">
                        <?php the_title(); ?>
                    </a>
                </h2>
                <div class="text-slate-400 text-sm mb-4">
                    <?php the_excerpt(); ?>
                </div>
                <a href="<?php the_permalink(); ?>"
                    class="text-primary font-bold text-sm uppercase tracking-wider hover:underline">Read More</a>
            </div>
            <?php
        endwhile;
    else:
        ?>
        <div class="text-center py-20">
            <h1 class="text-2xl font-bold text-white mb-4">Nothing Found</h1>
            <p class="text-slate-400">It seems we can&rsquo;t find what you&rsquo;re looking for.</p>
        </div>
        <?php
    endif;
    ?>
</main>

<?php
get_footer();
