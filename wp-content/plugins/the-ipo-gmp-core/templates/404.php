<?php
/**
 * Custom 404 Page for The IPO GMP
 * 
 * Styled with Tailwind CSS to match the platform's dark aesthetic.
 */

if (!defined('ABSPATH'))
    exit;

get_header();
?>

<main class="flex-grow flex items-center justify-center relative z-10 px-4 py-10 min-h-[60vh] overflow-hidden">
    <!-- Background Elements -->
    <div
        class="absolute top-0 left-0 w-full h-[500px] bg-primary/20 blur-[150px] -translate-y-1/2 rounded-full pointer-events-none z-0">
    </div>
    <div
        class="absolute bottom-0 right-0 w-96 h-96 bg-neon-emerald/10 blur-[120px] translate-y-1/4 translate-x-1/4 rounded-full pointer-events-none z-0">
    </div>

    <div class="text-center max-w-2xl mx-auto space-y-6 relative z-10">

        <!-- 404 Visual -->
        <div class="relative inline-block mb-2">
            <h1
                class="text-[120px] md:text-[160px] font-black leading-none text-transparent bg-clip-text bg-gradient-to-b from-white/10 to-transparent select-none">
                404
            </h1>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full">
                <span
                    class="material-symbols-outlined text-[60px] md:text-[80px] text-neon-emerald animate-pulse">rocket_launch</span>
            </div>
        </div>

        <!-- Message -->
        <div class="space-y-4">
            <h2 class="text-3xl md:text-4xl font-bold text-white">Houston, We Have a Problem!</h2>
            <p class="text-slate-400 text-lg md:text-xl font-medium">
                The page you are looking for has drifted into deep space or doesn't exist.
            </p>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mt-8">
            <a href="<?php echo home_url(); ?>"
                class="px-8 py-3.5 bg-primary hover:bg-blue-600 text-white font-bold rounded-xl transition-all shadow-lg shadow-primary/25 flex items-center gap-2 group">
                <span
                    class="material-symbols-outlined transition-transform group-hover:-translate-x-1">arrow_back</span>
                Return Home
            </a>
            <a href="<?php echo home_url('/mainboard-ipos/'); ?>"
                class="px-8 py-3.5 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl border border-white/10 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined">rocket</span>
                View Active IPOs
            </a>
        </div>

    </div>
</main>

<?php get_footer(); ?>