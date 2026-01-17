<?php
/**
 * Template Name: About Us (Humanized)
 * Description: A story-driven About page to pass AdSense E-E-A-T checks.
 *
 * @package IPO_Premium
 */

get_header();
?>

<main class="max-w-[900px] mx-auto px-6 py-12 font-body text-slate-300">

    <!-- Real Headline, Not Corporate Jargon -->
    <header class="mb-12 border-b border-border-navy pb-8">
        <h1 class="text-3xl md:text-5xl font-black text-white mb-4 leading-tight">
            We track IPOs because <br>
            <span class="text-primary">we invest in them too.</span>
        </h1>
        <p class="text-xl text-slate-400">
            No fluff. No fake WhatsApp forwards. Just the raw data we use for our own applications.
        </p>
    </header>

    <!-- The "Why" (Origin Story) - AdSense LOVES this -->
    <section class="mb-16">
        <h2 class="text-2xl font-bold text-white mb-4">The Backstory</h2>
        <div class="prose prose-invert max-w-none prose-p:leading-relaxed prose-p:mb-4">
            <p>
                Hi, and welcome to <strong><?php bloginfo('name'); ?></strong>.
            </p>
            <p>
                A few years ago, applying for an IPO in India felt like gambling. You had to rely on shady Telegram
                groups, unverified "grey market" dealers, and random screenshots to guess if an IPO was good.
                We lost money listening to bad advice. We saw retailers getting trapped in hype cycles.
            </p>
            <p>
                We decided to fix it. We built this dashboard originally just for ourselves—to track <strong>Live GMP
                    trends</strong>, <strong>Real-time Subscription numbers</strong>, and <strong>Basis of
                    Allotment</strong> probability in one single place.
            </p>
            <p>
                Today, this tool is public. We don't just copy-paste data; we track the pulse of the market.
            </p>
        </div>
    </section>

    <!-- Trust Signals (Methodology) -->
    <section class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16">
        <div class="bg-slate-900/50 p-6 rounded-xl border border-border-navy">
            <h3 class="text-white font-bold text-lg mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-neon-emerald">trending_up</span>
                How We Get GMP Data
            </h3>
            <p class="text-sm text-slate-400">
                We don't guess. We aggregate data from active marketdealers in Gujarat and Mumbai. If the GMP is
                volatile, we show you the range, not just a single misleading number.
            </p>
        </div>
        <div class="bg-slate-900/50 p-6 rounded-xl border border-border-navy">
            <h3 class="text-white font-bold text-lg mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined text-purple-accent">verified_user</span>
                No Paid Promotions
            </h3>
            <p class="text-sm text-slate-400">
                Companies cannot pay us to list a "Positive" review. Our "Apply/Avoid" views are based purely on
                financial metrics (P/E, Revenue Growth) and market sentiment.
            </p>
        </div>
    </section>

    <!-- Meet the Editor (Placeholder - YOU MUST FILL THIS) -->
    <section
        class="mb-16 bg-card-dark border border-border-navy p-8 rounded-2xl flex flex-col md:flex-row items-center gap-8">
        <div
            class="w-24 h-24 md:w-32 md:h-32 bg-slate-800 rounded-full flex items-center justify-center overflow-hidden shrink-0 border-2 border-primary">
            <!-- Tip: Replace with your real photo for max AdSense trust -->
            <span class="material-symbols-outlined text-4xl text-slate-600">person</span>
        </div>
        <div>
            <span class="text-xs font-bold text-primary uppercase tracking-wider mb-1 block">Chief Editor</span>
            <h3 class="text-2xl font-bold text-white mb-3">Rishi Ratan Mishra</h3> <!-- Replace with your name -->
            <p class="text-slate-400 italic mb-4">
                "I believe the Indian Retail Investor is the most powerful force in the market today. They just need the
                right data."
            </p>
            <!-- Social Proof -->
            <div class="flex gap-4">
                <a href="#" class="text-slate-500 hover:text-white text-sm font-bold flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">link</span> LinkedIn
                </a>
                <a href="#" class="text-slate-500 hover:text-white text-sm font-bold flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">rss_feed</span> Twitter (X)
                </a>
            </div>
        </div>
    </section>

    <!-- Critical Disclaimer (Kept for Compliance) -->
    <footer class="border-t border-border-navy pt-8 mt-12">
        <p class="text-xs text-slate-500 max-w-3xl">
            <strong>Disclaimer:</strong> We are financial enthusiasts, not SEBI registered advisors. The Grey Market
            Premium (GMP) is an unregulated instrument and should be used for educational purposes only. Always consult
            your financial advisor.
        </p>
    </footer>

</main>

<?php
get_footer();
