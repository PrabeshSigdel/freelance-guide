<?php
if (!defined('ABSPATH')) {
    exit;
}

if (get_theme_mod('aatf_home_show_destinations', '1') !== '1') {
    return;
}

$destinations_heading = (string) get_theme_mod('aatf_home_destinations_heading', 'Go Exotic Places');
$destinations_limit   = absint((int) get_theme_mod('aatf_home_destinations_limit', 6));
$background_image     = get_theme_mod('aatf_home_destinations_bg_image', '');

$section_style = 'background-color: #ffffff;';
if (!empty($background_image)) {
    $background_url = is_numeric($background_image)
        ? wp_get_attachment_image_url((int) $background_image, 'full')
        : esc_url_raw((string) $background_image);

    if (!empty($background_url)) {
        $section_style = sprintf(
            "background-image: linear-gradient(rgba(255,255,255,0.92), rgba(255,255,255,0.92)), url('%s'); background-size: cover; background-position: center;",
            esc_url($background_url)
        );
    }
}

$destinations = get_posts(array(
    'post_type'   => 'destination',
    'post_status' => 'publish',
    'numberposts' => $destinations_limit,
    'orderby'     => 'menu_order title',
    'order'       => 'ASC',
));
?>

<!-- ── Go Exotic Places ──────────────────────────────────────────── -->
<section class="py-16" style="<?php echo esc_attr($section_style); ?>">
    <div class="max-w-7xl mx-auto">

        <div class="text-center mb-10">
            <p class="text-sm font-semibold tracking-wide text-[var(--brand-orange)]">
                <?php esc_html_e('Destination lists', 'aatf-expedition-base'); ?>
            </p>
            <h2 class="text-4xl font-extrabold text-[var(--brand-dark)] mt-1">
                <?php echo esc_html($destinations_heading); ?>
            </h2>
        </div>

        <div class="grid grid-cols-3 gap-4">

            <?php if (empty($destinations)) : ?>
                <div class="col-span-3 rounded-2xl border border-slate-200 bg-slate-50 px-6 py-12 text-center">
                    <p class="text-sm font-semibold tracking-wide text-[var(--brand-orange)]">
                        <?php esc_html_e('Destination lists', 'aatf-expedition-base'); ?>
                    </p>
                    <h3 class="mt-2 text-2xl font-bold text-[var(--brand-dark)]">
                        <?php esc_html_e('No destinations found yet.', 'aatf-expedition-base'); ?>
                    </h3>
                    <p class="mt-3 text-[var(--brand-gray)]">
                        <?php esc_html_e('Add some destination posts to populate this section automatically.', 'aatf-expedition-base'); ?>
                    </p>
                </div>
                <?php else :
                foreach ($destinations as $i => $destination) :
                    $thumb = get_the_post_thumbnail_url($destination->ID, 'large');
                    if (!$thumb) {
                        $thumb = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 800'%3E%3Cdefs%3E%3ClinearGradient id='g' x1='0' y1='0' x2='1' y2='1'%3E%3Cstop stop-color='%23d97706'/%3E%3Cstop offset='1' stop-color='%230f172a'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='1200' height='800' fill='url(%23g)'/%3E%3Ccircle cx='950' cy='180' r='110' fill='rgba(255,255,255,0.14)'/%3E%3Cpath d='M0 620 L250 430 L420 560 L660 300 L880 520 L1200 260 L1200 800 L0 800 Z' fill='rgba(255,255,255,0.18)'/%3E%3Cpath d='M0 700 L220 560 L420 660 L700 430 L930 650 L1200 470 L1200 800 L0 800 Z' fill='rgba(255,255,255,0.28)'/%3E%3C/svg%3E";
                    }
                    $tour_count = 0;
                    if (post_type_exists('trek')) {
                        $trek_query = new WP_Query(array(
                            'post_type'      => 'trek',
                            'post_status'    => 'publish',
                            'posts_per_page' => -1,
                            'fields'         => 'ids',
                            'meta_query'     => array(array(
                                'key'   => '_destination_id',
                                'value' => $destination->ID,
                            )),
                        ));
                        $tour_count = $trek_query->found_posts;
                    }
                    // Make every 4th card span 2 columns (mimics the Himalayas layout)
                    $span = ($i === 3) ? 'col-span-2' : '';
                ?>
                    <div class="relative rounded-xl overflow-hidden group cursor-pointer h-64 <?php echo esc_attr($span); ?>">
                        <img src="<?php echo esc_url($thumb); ?>"
                            alt="<?php echo esc_attr(get_the_title($destination->ID)); ?>"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>
                        <?php if ($tour_count > 0) : ?>
                            <span class="absolute top-3 right-3 bg-[var(--brand-orange)] text-white text-xs font-bold px-3 py-1 rounded-full">
                                <?php echo esc_html($tour_count); ?> <?php esc_html_e('TOURS', 'aatf-expedition-base'); ?>
                            </span>
                        <?php endif; ?>
                        <a href="<?php echo esc_url(get_permalink($destination->ID)); ?>"
                            class="absolute bottom-4 left-4 group/link">
                            <p class="text-white text-xl font-bold group-hover/link:text-[var(--brand-orange)] transition-colors">
                                <?php echo esc_html(get_the_title($destination->ID)); ?>
                            </p>
                        </a>
                    </div>
            <?php endforeach;
            endif; ?>

        </div>
    </div>
</section>
