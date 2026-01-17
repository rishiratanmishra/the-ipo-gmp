<?php
/**
 * Template Name: Contact Us (Direct)
 * Description: A simple, non-robotic contact page.
 *
 * @package IPO_Premium
 */

get_header();
?>

<main class="max-w-[800px] mx-auto px-6 py-12">

    <div class="text-center mb-12">
        <h1 class="text-3xl font-black text-white mb-4 font-display">Let's Talk</h1>
        <p class="text-slate-400 text-lg">
            Spotted a wrong GMP rate? Have a suggestion? Or just want to say hi?
        </p>
    </div>

    <!-- Direct Contact Options -->
    <div class="bg-card-dark border border-border-navy rounded-2xl p-8 md:p-12">

        <!-- Email -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-6 border-b border-border-navy pb-8 mb-8">
            <div class="text-center md:text-left">
                <h3 class="text-white font-bold text-xl mb-1 font-display">Email Us</h3>
                <p class="text-slate-400 text-sm">The best way to reach us. We reply within 24 hours.</p>
            </div>
            <a href="mailto:contact@theipogmp.com"
                class="px-6 py-3 bg-primary text-white font-bold rounded-lg hover:bg-blue-600 transition-colors">
                contact@theipogmp.com
            </a>
        </div>

        <!-- Socials -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-6 pb-8 mb-8 border-b border-border-navy">
            <div class="text-center md:text-left">
                <h3 class="text-white font-bold text-xl mb-1 font-display">Social Media</h3>
                <p class="text-slate-400 text-sm">Ping us on Twitter for faster queries.</p>
            </div>
            <div class="flex gap-4">
                <a href="#"
                    class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-white hover:bg-primary transition-colors">
                    <span class="material-symbols-outlined">tag</span> <!-- Twitter/X icon placeholder -->
                </a>
                <a href="#"
                    class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-white hover:bg-blue-500 transition-colors">
                    <span class="material-symbols-outlined">send</span> <!-- Telegram icon placeholder -->
                </a>
            </div>
        </div>

        <!-- Physical Address (Optional but helps AdSense Trust) -->
        <div class="text-center md:text-left">
            <h3 class="text-slate-500 font-bold text-xs uppercase tracking-widest mb-2 font-display">Office Location
            </h3>
            <p class="text-slate-300">
                Sector 62, Noida<br>
                Uttar Pradesh, India - 201301
            </p>
        </div>

    </div>

</main>

<?php
get_footer();
