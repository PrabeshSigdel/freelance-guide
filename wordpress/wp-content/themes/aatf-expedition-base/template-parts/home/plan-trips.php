<?php if (!defined('ABSPATH')) exit; ?>

<?php
if (get_theme_mod('aatf_home_show_plan_trips', '1') !== '1') {
    return;
}

$phone    = trim((string) get_theme_mod('aatf_header_phone', '66888000'));
$cta_url  = trim((string) get_theme_mod('aatf_header_cta_url', home_url('/booking/')));
$nav_url  = $cta_url !== '' ? $cta_url : home_url('/booking/');
$heading  = trim((string) get_theme_mod('aatf_home_plan_trips_heading', 'Plan Your Trip with Us'));

$selected_destination_id = absint((int) get_theme_mod('aatf_home_plan_trips_destination_id', 0));
$destination = null;

if ($selected_destination_id > 0) {
    $selected_post = get_post($selected_destination_id);
    if ($selected_post instanceof WP_Post && $selected_post->post_type === 'destination' && $selected_post->post_status === 'publish') {
        $destination = $selected_post;
    }
}

if (!$destination instanceof WP_Post) {
    $destination_posts = get_posts(array(
        'post_type' => 'destination',
        'post_status' => 'publish',
        'numberposts' => 1,
        'orderby' => 'menu_order title',
        'order' => 'ASC',
    ));
    if (!empty($destination_posts)) {
        $destination = $destination_posts[0];
    }
}

if (!$destination instanceof WP_Post) {
    return;
}

$destination_id = (int) $destination->ID;
$destination_title = get_the_title($destination_id);
$destination_url = get_permalink($destination_id);
$country = trim((string) get_post_meta($destination_id, 'destination_country', true));
$thumb = get_the_post_thumbnail_url($destination_id, 'large');
$thumb = $thumb ? $thumb : "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 900 900'%3E%3Cdefs%3E%3ClinearGradient id='g' x1='0' y1='0' x2='1' y2='1'%3E%3Cstop stop-color='%230f172a'/%3E%3Cstop offset='1' stop-color='%23d97706'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='900' height='900' fill='url(%23g)'/%3E%3Cpath d='M0 700 L160 520 L300 630 L470 410 L620 560 L760 350 L900 470 L900 900 L0 900 Z' fill='rgba(255,255,255,0.18)'/%3E%3Cpath d='M0 780 L180 660 L340 730 L520 560 L740 700 L900 580 L900 900 L0 900 Z' fill='rgba(255,255,255,0.3)'/%3E%3C/svg%3E";

$summary = trim((string) get_the_excerpt($destination_id));
if ($summary === '') {
    $summary = wp_trim_words(wp_strip_all_tags((string) get_post_field('post_content', $destination_id)), 28, '...');
}
if ($summary === '') {
    $summary = __('Discover one of our most loved destinations and start shaping your next adventure with our local team.', 'aatf-expedition-base');
}

$child_destinations = get_posts(array(
    'post_type' => 'destination',
    'post_status' => 'publish',
    'posts_per_page' => 3,
    'post_parent' => $destination_id,
    'orderby' => 'menu_order title',
    'order' => 'ASC',
));

$descendants = get_pages(array(
    'post_type' => 'destination',
    'child_of' => $destination_id,
    'sort_column' => 'menu_order,post_title',
    'sort_order' => 'ASC',
));

$related_destination_ids = array($destination_id);
if (!empty($descendants)) {
    $related_destination_ids = array_values(array_unique(array_merge(
        $related_destination_ids,
        array_map('absint', wp_list_pluck($descendants, 'ID'))
    )));
}

$trek_meta_query = array('relation' => 'OR');
foreach ($related_destination_ids as $related_destination_id) {
    if ($related_destination_id <= 0) {
        continue;
    }

    $trek_meta_query[] = array(
        'key' => 'trek_destination_ids',
        'value' => 'i:' . $related_destination_id . ';',
        'compare' => 'LIKE',
    );
}

$related_treks_count = 0;
if (count($trek_meta_query) > 1) {
    $related_treks = new WP_Query(array(
        'post_type' => 'trek',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_query' => $trek_meta_query,
        'no_found_rows' => false,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ));
    $related_treks_count = (int) $related_treks->found_posts;
}

$badge_label = $related_treks_count > 0
    ? sprintf(_n('%s Trek', '%s Treks', $related_treks_count, 'aatf-expedition-base'), number_format_i18n($related_treks_count))
    : __('Featured Destination', 'aatf-expedition-base');

$bullet_items = array();
if ($country !== '') {
    $bullet_items[] = sprintf(__('Based in %s', 'aatf-expedition-base'), $country);
}
if (!empty($child_destinations)) {
    $child_names = array_map(static function ($child_destination) {
        return (string) $child_destination->post_title;
    }, $child_destinations);
    $bullet_items[] = sprintf(__('Includes %s', 'aatf-expedition-base'), implode(', ', $child_names));
}
if ($related_treks_count > 0) {
    $bullet_items[] = sprintf(_n('%s related trek available', '%s related treks available', $related_treks_count, 'aatf-expedition-base'), number_format_i18n($related_treks_count));
}
if (empty($bullet_items)) {
    $bullet_items[] = __('Handpicked adventures planned by experienced local guides.', 'aatf-expedition-base');
}
?>

<!-- ── Plan Your Trip with Us ─────────────────────────────────────── -->
<section class="bg-white py-16 overflow-hidden">
    <div class="max-w-7xl mx-auto flex items-center gap-12">

        <!-- Left: Image -->
        <div class="relative shrink-0 w-[500px] h-[500px]">
            <div class="absolute top-8 right-8 bottom-8 left-0 rounded-[28px] overflow-hidden shadow-xl z-10">
                <img src="<?php echo esc_url($thumb); ?>"
                    alt="<?php echo esc_attr($destination_title); ?>"
                    class="w-full h-full object-cover" />
                <div class="absolute inset-0 bg-gradient-to-t from-black/55 via-black/20 to-transparent"></div>
                <div class="absolute bottom-3 right-3 text-right leading-tight drop-shadow-lg z-10">
                    <p class="text-sm font-bold uppercase tracking-widest" style="color: var(--brand-orange);">
                        <?php esc_html_e('Featured', 'aatf-expedition-base'); ?>
                    </p>
                    <p class="text-xl font-extrabold text-white -mt-1"><?php echo esc_html($badge_label); ?></p>
                </div>
            </div>

            <div class="absolute bottom-0 left-0 z-30 bg-white rounded-xl shadow-lg px-4 py-3 border border-gray-100">
                <p class="text-[10px] font-semibold tracking-widest uppercase mb-0.5" style="color: var(--brand-gray);">
                    <?php esc_html_e('Book Tour Now', 'aatf-expedition-base'); ?>
                </p>
                <p class="text-xl font-extrabold tracking-wide" style="color: var(--brand-dark);">
                    <?php echo esc_html($phone); ?>
                </p>
            </div>
        </div>

        <!-- Right: Content -->
        <div class="flex-1">
            <p class="text-sm font-semibold mb-2" style="color: var(--brand-orange);">
                <?php esc_html_e('Get to know us', 'aatf-expedition-base'); ?>
            </p>
            <h2 class="text-4xl font-extrabold leading-tight mb-5" style="color: var(--brand-dark);">
                <?php echo esc_html($heading); ?>
            </h2>
            <p class="text-md leading-relaxed mb-7" style="color: var(--brand-gray);">
                <?php echo esc_html($summary); ?>
            </p>
            <p class="text-lg font-bold mb-5" style="color: var(--brand-dark);">
                <a href="<?php echo esc_url($destination_url); ?>" class="hover:underline">
                    <?php echo esc_html($destination_title); ?>
                </a>
            </p>

            <ul class="space-y-3 mb-8">
                <?php foreach ($bullet_items as $item) : ?>
                    <li class="flex items-center gap-3 text-sm font-medium" style="color: var(--brand-dark);">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center shrink-0"
                            style="background-color: var(--brand-orange);">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        <?php echo esc_html($item); ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="flex flex-wrap items-center gap-4">
                <a href="<?php echo esc_url($nav_url); ?>"
                class="inline-block text-white text-xs font-bold tracking-widest uppercase px-8 py-4 rounded-lg hover:opacity-90 transition-opacity"
                style="background-color: var(--brand-navy);">
                    <?php esc_html_e('Book with Us Now', 'aatf-expedition-base'); ?>
                </a>
            </div>
        </div>

    </div>
</section>


