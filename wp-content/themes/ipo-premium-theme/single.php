<?php
/**
 * The template for displaying all single posts
 */

get_header();
?>

<div id="scroll-progress" class="fixed top-0 left-0 h-1 bg-[#0d7ff2] z-[9999] transition-all duration-100 w-0"></div>

<main class="max-w-[1100px] mx-auto px-4 md:px-6 py-8">
    <?php
    while (have_posts()):
        the_post();
        $categories = get_the_category();
        $cat = !empty($categories) ? $categories[0] : null;

        // Visibility Options
        $show_breadcrumbs = get_post_meta(get_the_ID(), '_show_breadcrumbs', true) !== 'no';
        $show_share = get_post_meta(get_the_ID(), '_show_share_buttons', true) !== 'no';
        $show_highlights = get_post_meta(get_the_ID(), '_show_quick_highlights', true) !== 'no';
        $show_toc = get_post_meta(get_the_ID(), '_show_toc', true) !== 'no';
        $show_related = get_post_meta(get_the_ID(), '_show_related_posts', true) !== 'no';
        ?>

        <?php if ($show_breadcrumbs): ?>
            <nav class="flex items-center text-[10px] md:text-xs uppercase tracking-[0.15em] text-slate-500 mb-8 overflow-x-auto whitespace-nowrap pb-2"
                aria-label="Breadcrumb">
                <a href="<?php echo home_url(); ?>" class="hover:text-primary transition-colors">Home</a>
                <span class="mx-3 opacity-30">/</span>
                <?php if ($cat): ?>
                    <a href="<?php echo get_category_link($cat->term_id); ?>"
                        class="hover:text-primary transition-colors"><?php echo esc_html($cat->name); ?></a>
                    <span class="mx-3 opacity-30">/</span>
                <?php endif; ?>
                <span class="text-slate-300 font-bold truncate"><?php the_title(); ?></span>
            </nav>
        <?php endif; ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> itemscope itemtype="https://schema.org/BlogPosting">
            <meta itemprop="mainEntityOfPage" content="<?php the_permalink(); ?>">
            <meta itemprop="datePublished" content="<?php echo get_the_date('c'); ?>">
            <meta itemprop="dateModified" content="<?php echo get_the_modified_date('c'); ?>">

            <header class="mb-10">
                <div class="flex items-center gap-3 mb-6">
                    <span
                        class="bg-[#0d7ff2]/10 text-[#0d7ff2] text-[10px] font-black px-3 py-1 rounded-full border border-[#0d7ff2]/20 uppercase">
                        Live Updates
                    </span>
                    <span class="text-slate-500 text-xs font-medium">
                        Last Updated: <?php echo get_the_modified_date('M j, Y'); ?>
                    </span>
                </div>

                <h1 class="text-3xl md:text-6xl font-black text-white leading-[1.15] mb-8 tracking-tight font-display"
                    itemprop="headline">
                    <?php the_title(); ?>
                </h1>

                <?php if ($show_share): ?>
                    <div
                        class="flex flex-wrap items-center justify-between gap-6 p-6 rounded-2xl bg-slate-900/40 border border-white/5">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-10 h-10 rounded-full bg-[#0d7ff2] flex items-center justify-center text-white shadow-lg shadow-[#0d7ff2]/20">
                                <span class="material-symbols-outlined text-xl">account_balance_wallet</span>
                            </div>
                            <div>
                                <p class="text-white font-bold text-sm" itemprop="author">The IPO GMP Team</p>
                                <p class="text-slate-500 text-[11px]">Financial Experts • 5 min read</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <?php
                            $url = urlencode(get_permalink());
                            $title = urlencode(get_the_title());
                            ?>
                            <a href="https://wa.me/?text=<?php echo $title . ' - ' . $url; ?>" target="_blank"
                                class="w-10 h-10 rounded-xl bg-[#25D366]/10 text-[#25D366] flex items-center justify-center hover:bg-[#25D366] hover:text-white transition-all border border-[#25D366]/20">
                                <span class="material-symbols-outlined">share</span>
                            </a>
                            <button onclick="window.print()"
                                class="h-10 px-4 rounded-xl bg-slate-800 text-slate-300 text-xs font-bold border border-white/10 hover:bg-white hover:text-black transition-all">
                                PDF / PRINT
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </header>

            <div class="flex flex-col lg:flex-row gap-12">
                <div class="lg:w-[70%] order-2 lg:order-1">

                    <?php if (has_post_thumbnail()): ?>
                        <div class="mb-10 rounded-3xl overflow-hidden border border-white/5 shadow-2xl">
                            <?php the_post_thumbnail('full', ['class' => 'w-full h-auto object-cover', 'itemprop' => 'image']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($show_highlights): ?>
                        <div
                            class="bg-gradient-to-br from-slate-900 to-[#0d7ff2]/5 border-l-4 border-[#0d7ff2] p-8 rounded-r-2xl mb-12 relative overflow-hidden">
                            <div class="absolute top-[-20px] right-[-20px] opacity-10">
                                <span class="material-symbols-outlined text-9xl">auto_awesome</span>
                            </div>
                            <h3 class="text-white font-black text-lg mb-4 flex items-center gap-2 font-display">
                                <span class="material-symbols-outlined text-[#0d7ff2]">bolt</span> QUICK HIGHLIGHTS
                            </h3>
                            <div class="text-slate-400 text-sm leading-relaxed space-y-2 italic">
                                <?php echo wp_trim_words(get_the_content(), 50, '...'); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="entry-content prose prose-invert prose-lg max-w-none prose-headings:text-white prose-p:text-slate-400 prose-a:text-[#0d7ff2] prose-strong:text-white"
                        itemprop="articleBody">
                        <?php the_content(); ?>
                    </div>

                    <footer class="mt-16 pt-8 border-t border-white/5">
                        <div class="bg-slate-900/50 rounded-2xl p-6 text-center border border-white/5">
                            <p class="text-xs text-slate-500 uppercase tracking-widest font-bold mb-2">Disclaimer</p>
                            <p class="text-[10px] text-slate-600 leading-relaxed">
                                The Grey Market Premium (GMP) data provided here is for information purposes only. GMP is
                                volatile and does not guarantee the listing price. Always consult a certified financial
                                advisor before investing in IPOs.
                            </p>
                        </div>
                    </footer>
                </div>

                <aside class="lg:w-[30%] order-1 lg:order-2">
                    <div class="sticky top-28 space-y-8">
                        <?php if ($show_toc): ?>
                            <div id="table-of-contents"
                                class="hidden p-6 rounded-2xl bg-slate-900/80 backdrop-blur-md border border-white/5 shadow-xl">
                                <h4
                                    class="text-white text-xs font-black uppercase tracking-[0.2em] mb-6 flex items-center justify-between">
                                    Contents
                                    <span class="w-8 h-px bg-white/10"></span>
                                </h4>
                                <nav id="toc-list" class="flex flex-col gap-4 text-[13px] text-slate-500"></nav>
                            </div>
                        <?php endif; ?>

                        <div class="p-6 rounded-2xl bg-[#0d7ff2] text-white">
                            <h4 class="font-black text-lg leading-tight mb-2 text-white">Don't Miss the Next Big IPO!</h4>
                            <p class="text-blue-100 text-xs mb-4">Join our telegram for real-time GMP alerts.</p>
                            <a href="#"
                                class="block text-center py-3 bg-white text-[#0d7ff2] rounded-xl font-bold text-xs uppercase tracking-wider hover:shadow-lg transition-all">Join
                                Telegram</a>
                        </div>
                    </div>
                </aside>
            </div>

            <?php if ($show_related): ?>
                <section class="mt-20 pt-12 border-t border-white/5">
                    <h3 class="text-2xl font-black text-white mb-8 italic font-display">RECOMMENDED UPDATES</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <?php
                        $related = new WP_Query([
                            'post_type' => 'post',
                            'posts_per_page' => 3,
                            'post__not_in' => [get_the_ID()],
                            'orderby' => 'rand'
                        ]);
                        if ($related->have_posts()):
                            while ($related->have_posts()):
                                $related->the_post(); ?>
                                <a href="<?php the_permalink(); ?>" class="group">
                                    <div
                                        class="aspect-video rounded-2xl overflow-hidden bg-slate-900 border border-white/5 mb-4 group-hover:border-[#0d7ff2]/50 transition-all">
                                        <?php if (has_post_thumbnail()):
                                            the_post_thumbnail('medium_large', ['class' => 'w-full h-full object-cover group-hover:scale-110 transition-transform duration-700']); endif; ?>
                                    </div>
                                    <h4 class="text-white font-bold leading-tight group-hover:text-[#0d7ff2] transition-colors">
                                        <?php the_title(); ?></h4>
                                    <p class="text-[10px] text-slate-500 mt-2 font-bold uppercase tracking-widest">
                                        <?php echo get_the_date(); ?></p>
                                </a>
                            <?php endwhile;
                            wp_reset_postdata(); endif; ?>
                    </div>
                </section>
            <?php endif; ?>

        </article>
    <?php endwhile; ?>
</main>

<style>
    /* 1. Scroll Progress */
    #scroll-progress {
        transition: width 0.1s ease-out;
    }

    /* 2. Prose / Content Styling */
    .prose h2 {
        font-size: 2rem !important;
        font-weight: 900 !important;
        margin-top: 3rem !important;
        margin-bottom: 1.5rem !important;
        letter-spacing: -0.02em;
        background: linear-gradient(to right, #fff, #94a3b8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .prose h3 {
        font-size: 1.5rem !important;
        font-weight: 800 !important;
        color: #f1f5f9 !important;
    }

    .prose p {
        line-height: 1.85 !important;
        margin-bottom: 1.8rem !important;
        font-size: 1.05rem !important;
    }

    /* 3. Table Modernization for GMP Data */
    .prose table {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        width: 100% !important;
        border: 1px solid rgba(255, 255, 255, 0.05) !important;
        border-radius: 16px !important;
        overflow: hidden !important;
        margin: 2.5rem 0 !important;
        background: rgba(15, 23, 42, 0.3);
    }

    .prose th {
        background: #0d7ff2 !important;
        color: white !important;
        text-transform: uppercase !important;
        font-size: 0.75rem !important;
        letter-spacing: 0.1em !important;
        padding: 18px !important;
        border: none !important;
    }

    .prose td {
        padding: 16px 18px !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.03) !important;
        color: #cbd5e1 !important;
        font-size: 0.95rem !important;
    }

    .prose tr:last-child td {
        border-bottom: none !important;
    }

    /* 4. TOC Styling */
    .toc-link-active {
        color: #0d7ff2 !important;
        font-weight: 900 !important;
        padding-left: 10px !important;
        border-left: 2px solid #0d7ff2 !important;
    }

    #toc-list a {
        transition: all 0.3s ease;
        text-decoration: none !important;
    }

    #toc-list a:hover {
        color: white;
    }

    /* 5. Blockquotes */
    .prose blockquote {
        border-left-color: #0d7ff2 !important;
        background: rgba(13, 127, 242, 0.05);
        padding: 20px 30px !important;
        border-radius: 0 16px 16px 0;
        font-style: italic;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Progress Bar Logic
        window.addEventListener('scroll', () => {
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            document.getElementById("scroll-progress").style.width = scrolled + "%";
        });

        // 2. Table of Contents Generator
        const content = document.querySelector('.entry-content');
        const headings = content.querySelectorAll('h2, h3');
        const tocContainer = document.getElementById('table-of-contents');
        const tocList = document.getElementById('toc-list');

        if (headings.length > 0) {
            tocContainer.classList.remove('hidden');
            headings.forEach((heading, index) => {
                const id = 'section-' + index;
                heading.id = id;

                const link = document.createElement('a');
                link.href = '#' + id;
                link.textContent = heading.textContent;
                link.className = (heading.tagName === 'H3') ? 'pl-4 opacity-70' : 'font-bold uppercase tracking-wider text-[11px]';

                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    window.scrollTo({
                        top: heading.offsetTop - 100,
                        behavior: 'smooth'
                    });
                });
                tocList.appendChild(link);
            });
        }

        // 3. Highlight active section in TOC
        const observerOptions = { rootMargin: '-100px 0px -70% 0px' };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    document.querySelectorAll('#toc-list a').forEach(link => {
                        link.classList.remove('toc-link-active');
                        if (link.getAttribute('href') === '#' + entry.target.id) {
                            link.classList.add('toc-link-active');
                        }
                    });
                }
            });
        }, observerOptions);

        headings.forEach(h => observer.observe(h));
    });
</script>

<?php get_footer(); ?>