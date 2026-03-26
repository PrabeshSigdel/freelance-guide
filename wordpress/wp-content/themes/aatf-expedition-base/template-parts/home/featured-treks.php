<?php
if (!defined('ABSPATH')) {
    exit;
}

if (get_theme_mod('aatf_home_show_treks', '1') !== '1') {
    return;
}

$heading = trim((string) get_theme_mod('aatf_home_treks_heading', 'Featured Treks'));
$limit = max(1, min(24, (int) get_theme_mod('aatf_home_treks_limit', 6)));
$cache_key = 'aatf_home_featured_trek_ids_' . $limit;
$featured_ids = get_transient($cache_key);

if (!is_array($featured_ids)) {
    $featured_query = new WP_Query(array(
        'post_type' => 'trek',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'fields' => 'ids',
        'meta_query' => array(
            array(
                'key' => 'trek_featured',
                'value' => '1',
                'compare' => '=',
            ),
        ),
        'meta_key' => 'trek_display_order',
        'orderby' => array(
            'meta_value_num' => 'ASC',
            'title' => 'ASC',
        ),
        'order' => 'ASC',
        'no_found_rows' => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ));

    $featured_ids = is_array($featured_query->posts) ? array_map('absint', $featured_query->posts) : array();
    set_transient($cache_key, $featured_ids, 6 * HOUR_IN_SECONDS);
}

echo '<section class="aatf-home-section aatf-home-section--treks">';
if ($heading !== '') {
    echo '<h2 class="aatf-home-section__title">' . esc_html($heading) . '</h2>';
}

if (empty($featured_ids)) {
    echo '<p>No featured treks found.</p>';
    echo '</section>';
    return;
}

echo '<div class="aatf-trek-grid">';
foreach ($featured_ids as $trek_id) {
    if ($trek_id <= 0 || get_post_status($trek_id) !== 'publish') {
        continue;
    }

    if (class_exists('AATF_Frontend_Components') && method_exists('AATF_Frontend_Components', 'render_trek_card')) {
        echo AATF_Frontend_Components::render_trek_card(array('id' => $trek_id));
        continue;
    }

    echo '<article class="aatf-panel"><h3><a href="' . esc_url(get_permalink($trek_id)) . '">' . esc_html(get_the_title($trek_id)) . '</a></h3></article>';
}
echo '</div>';
echo '</section>';
