<?php
/**
 * Template Name: About Us
 * Description: A professional About Us page for AdSense compliance and user trust.
 *
 * @package IPO_Premium
 */

get_header();
?>

<main class="max-w-[1000px] mx-auto px-6 py-12">
    <!-- Hero Section -->
    <section class="text-center mb-16">
        <span class="text-primary font-bold tracking-widest uppercase text-xs mb-3 block">Transparency & Trust</span>
        <h1 class="text-4xl md:text-5xl font-black text-white mb-6">Empowering Indian <span
                class="text-neon-emerald">Investors</span></h1>
        <p class="text-slate-400 text-lg max-w-2xl mx-auto leading-relaxed">
            We provide real-time, accurate, and unbiased data on Initial Public Offerings (IPOs) to help you make
            informed financial decisions.
        </p>
    </section>

    <!-- Content Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mb-20">
        <!-- Our Mission -->
        <div
            class="bg-card-dark border border-border-navy p-8 rounded-2xl relative overflow-hidden group hover:border-primary/50 transition-colors">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <span class="material-symbols-outlined text-9xl text-primary">analytics</span>
            </div>
            <h2 class="text-2xl font-bold text-white mb-4 flex items-center gap-3">
                <span
                    class="w-8 h-8 rounded bg-primary/20 flex items-center justify-center text-primary text-sm">01</span>
                Our Mission
            </h2>
            <p class="text-slate-400 leading-relaxed">
                The Indian stock market is booming, but reliable information on Grey Market Premiums (GMP) and
                subscription data is often scattered.
                Our mission is to <strong>consolidate real-time IPO intelligence</strong> into a single, easy-to-read
                dashboard. We believe every retail investor deserves access to the same data as high-net-worth
                individuals.
            </p>
        </div>

        <!-- What We Do -->
        <div
            class="bg-card-dark border border-border-navy p-8 rounded-2xl relative overflow-hidden group hover:border-purple-accent/50 transition-colors">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <span class="material-symbols-outlined text-9xl text-purple-accent">currency_rupee</span>
            </div>
            <h2 class="text-2xl font-bold text-white mb-4 flex items-center gap-3">
                <span
                    class="w-8 h-8 rounded bg-purple-accent/20 flex items-center justify-center text-purple-accent text-sm">02</span>
                What We Track
            </h2>
            <ul class="space-y-3 text-slate-400">
                <li class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-neon-emerald text-sm">check_circle</span>
                    <span><strong>Live GMP:</strong> Real-time grey market premium rates.</span>
                </li>
                <li class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-neon-emerald text-sm">check_circle</span>
                    <span><strong>Subscription Status:</strong> QIB, NII, and Retail demand.</span>
                </li>
                <li class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-neon-emerald text-sm">check_circle</span>
                    <span><strong>Allotment Updates:</strong> Instant link availability checks.</span>
                </li>
                <li class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-neon-emerald text-sm">check_circle</span>
                    <span><strong>SME & Mainboard:</strong> Comprehensive coverage of all listings.</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Editorial Policy & Disclaimer (AdSense Critical) -->
    <section class="mb-16">
        <h2 class="text-3xl font-bold text-white mb-8 border-l-4 border-primary pl-4">Editorial Standards</h2>
        <div class="prose prose-invert max-w-none text-slate-400">
            <p>
                <strong>Accuracy:</strong> We source our data from reliable market participants, broker reports, and
                official exchange data (BSE/NSE). While we strive for 100% accuracy, GMP is an informal market indicator
                and can change rapidly.
            </p>
            <p>
                <strong>Unbiased Analysis:</strong> Our reviews and analysis are independent. We do not accept payment
                from companies to promote their IPOs. Any sponsored content (if applicable) will be clearly marked.
            </p>

            <div class="bg-yellow-900/10 border border-yellow-700/30 p-6 rounded-xl mt-8">
                <h3 class="text-yellow-500 text-lg font-bold mb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined">warning</span>
                    Disclaimer
                </h3>
                <p class="text-sm text-yellow-200/80 mb-0">
                    We are NOT financial advisors registered with SEBI. The information provided on this website ("The
                    IPO GMP") is for educational and informational purposes only. Grey Market Premium (GMP) is an
                    unregulated market figure and should not be the sole basis for investment decisions. Stock market
                    investments are subject to market risks. Please consult a settled financial consultant before making
                    any investment decisions.
                </p>
            </div>
        </div>
    </section>

    <!-- Contact CTA -->
    <section
        class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-3xl p-10 text-center border border-border-navy relative overflow-hidden">
        <div class="relative z-10">
            <h2 class="text-2xl font-bold text-white mb-2">Have Question or Feedback?</h2>
            <p class="text-slate-400 mb-6">We'd love to hear from our community of investors.</p>
            <a href="mailto:contact@theipogmp.com"
                class="inline-flex items-center gap-2 bg-primary text-white px-6 py-3 rounded-full font-bold hover:bg-blue-600 transition-colors">
                <span class="material-symbols-outlined text-sm">mail</span>
                Contact Us
            </a>
        </div>

        <!-- Decoration -->
        <div class="absolute top-0 left-0 w-full h-full opacity-30 pointer-events-none">
            <div class="absolute -top-10 -left-10 w-40 h-40 bg-primary rounded-full blur-3xl"></div>
            <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-purple-accent rounded-full blur-3xl"></div>
        </div>
    </section>

</main>

<?php
get_footer();
