<?php if (!defined('ABSPATH')) exit; ?>

<?php
$media_type  = (string) get_theme_mod('aatf_home_hero_media_type', 'none');
$hero_image  = (string) get_theme_mod('aatf_home_hero_image', '');
$hero_title  = (string) get_theme_mod('aatf_home_hero_title', '');
$hero_text   = (string) get_theme_mod('aatf_home_hero_text', '');

if ($hero_title === '') {
    $hero_title = get_bloginfo('name');
}
if ($hero_text === '') {
    $hero_text = get_bloginfo('description');
}
if ($hero_text === '') {
    $hero_text = 'Where Would You Like To Go?';
}

// Resolve background image
$bg_url = 'https://images.unsplash.com/photo-1553856622-d1b352e9a211?w=1600&q=80'; // fallback
if ($media_type === 'image' && $hero_image !== '') {
    if (is_numeric($hero_image)) {
        $src = wp_get_attachment_image_url((int) $hero_image, 'full');
        if ($src) $bg_url = $src;
    } else {
        $bg_url = esc_url($hero_image);
    }
}
?>

<!-- ── Hero ──────────────────────────────────────────────────────── -->
<section class="hero-bg relative min-h-[600px] flex flex-col items-center justify-center text-center"
    style="background-image: url('<?php echo esc_url($bg_url); ?>');">
    <div class="absolute inset-0 bg-black/30"></div>

    <div class="relative z-10 px-6 py-20">
        <h1 class="font-display text-6xl md:text-7xl font-bold text-[var(--brand-orange)] mb-4 drop-shadow-lg tracking-wide">
            <?php echo esc_html($hero_title); ?>
        </h1>
        <p class="text-white text-xl md:text-2xl font-light tracking-widest drop-shadow">
            <?php echo esc_html($hero_text); ?>
        </p>
    </div>
</section>

<!-- ── Search Bar Toggle (Mobile) ────────────────────────────────── -->
<div class="lg:hidden mx-4 text-center -mt-8 relative z-20 mb-4">
    <button id="mobileSearchToggle" class="bg-[var(--brand-orange)] rounded-xl shadow-lg hover:opacity-90 text-white font-bold px-6 py-3 flex items-center gap-2 mx-auto transition-colors text-sm tracking-wide">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <?php esc_html_e('Find a Tour', 'aatf-expedition-base'); ?>
    </button>
</div>

<!-- ── Search Bar ────────────────────────────────────────────────── -->
<div id="heroSearchBar" class="hidden lg:flex bg-white lg:-mt-7 shadow-[0_8px_40px_rgba(0,0,0,0.13)] mx-4 lg:mx-auto lg:max-w-7xl rounded-lg relative z-20 flex-col lg:flex-row items-stretch lg:items-center divide-y lg:divide-y-0 lg:divide-x divide-gray-200 overflow-hidden border border-gray-100">

    <!-- Destination -->
    <div class="flex items-center gap-3 px-6 py-5 flex-1 cursor-pointer hover:bg-orange-50 transition-colors">
        <div class="text-[var(--brand-orange)] shrink-0">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-xs text-[var(--brand-gray)] mb-0.5 flex items-center gap-1">
                <?php esc_html_e('Where are you going?', 'aatf-expedition-base'); ?>
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="font-bold text-[var(--brand-dark)] text-sm"><?php esc_html_e('Destinations', 'aatf-expedition-base'); ?></div>
        </div>
    </div>

    <!-- Activity -->
    <div class="flex items-center gap-3 px-6 py-5 flex-1 cursor-pointer hover:bg-orange-50 transition-colors">
        <div class="text-[var(--brand-orange)] shrink-0">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.125A7.5 7.5 0 0112 12.75a7.5 7.5 0 017.499 7.375" />
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-xs text-[var(--brand-gray)] mb-0.5 flex items-center gap-1">
                <?php esc_html_e('Activity type', 'aatf-expedition-base'); ?>
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="font-bold text-[var(--brand-dark)] text-sm"><?php esc_html_e('Activity', 'aatf-expedition-base'); ?></div>
        </div>
    </div>

    <!-- When -->
    <div class="flex items-center gap-3 px-6 py-5 flex-1 cursor-pointer hover:bg-orange-50 transition-colors">
        <div class="text-[var(--brand-orange)] shrink-0">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-xs text-[var(--brand-gray)] mb-0.5"><?php esc_html_e('When', 'aatf-expedition-base'); ?></div>
            <div class="font-bold text-[var(--brand-dark)] text-sm"><?php esc_html_e('Date From', 'aatf-expedition-base'); ?></div>
        </div>
    </div>

    <!-- Guests -->
    <div class="flex items-center gap-3 px-6 py-5 flex-1 cursor-pointer hover:bg-orange-50 transition-colors">
        <div class="text-[var(--brand-orange)] shrink-0">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-xs text-[var(--brand-gray)] mb-0.5"><?php esc_html_e('Guests', 'aatf-expedition-base'); ?></div>
            <div class="font-bold text-[var(--brand-dark)] text-sm">0</div>
        </div>
    </div>

    <!-- Filter icon -->
    <div class="px-4 py-5 flex justify-center lg:block cursor-pointer hover:bg-orange-50 transition-colors border-t lg:border-t-0 border-gray-200">
        <svg class="w-6 h-6 text-[var(--brand-orange)]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
        </svg>
    </div>

    <!-- Search Button -->
    <button class="bg-gray-800 rounded-lg lg:rounded-xl hover:bg-gray-900 text-white font-bold px-4 py-4 lg:py-3 mx-4 mb-4 lg:m-0 lg:mr-3 flex items-center justify-center gap-1.5 transition-colors text-sm lg:text-xs tracking-wide uppercase whitespace-nowrap">
        <svg class="w-4 h-4 lg:w-3.5 lg:h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <?php esc_html_e('Search', 'aatf-expedition-base'); ?>
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var toggleBtn = document.getElementById('mobileSearchToggle');
    var searchBar = document.getElementById('heroSearchBar');
    
    if (toggleBtn && searchBar) {
        toggleBtn.addEventListener('click', function() {
            if (searchBar.classList.contains('hidden')) {
                searchBar.classList.remove('hidden');
                searchBar.classList.add('flex');
                // Create a smoother flow: animate opacity or max-height if needed
                // For now, toggle visibility
            } else {
                searchBar.classList.add('hidden');
                searchBar.classList.remove('flex');
            }
        });
    }
});
</script>