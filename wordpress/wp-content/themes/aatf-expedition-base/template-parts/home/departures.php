<?php
if (!defined('ABSPATH')) {
    exit;
}

if (get_theme_mod('aatf_home_show_departures', '1') !== '1') {
    return;
}

$heading = trim((string) get_theme_mod('aatf_home_departures_heading', 'Upcoming Departures'));
$limit = max(1, min(24, (int) get_theme_mod('aatf_home_departures_limit', 6)));
$today = current_time('Y-m-d');
$trek_filter = (string) get_theme_mod('aatf_home_departures_trek_filter', 'all');
$filter_destination_id = absint((int) get_theme_mod('aatf_home_departures_destination_id', 0));

$departure_meta_query = array(
    array(
        'key' => 'departure_start_date',
        'value' => $today,
        'compare' => '>=',
        'type' => 'DATE',
    ),
);

if ($trek_filter === 'featured' || $trek_filter === 'destination') {
    $trek_query_args = array(
        'post_type' => 'trek',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'no_found_rows' => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    );

    if ($trek_filter === 'featured') {
        $trek_query_args['meta_query'] = array(
            array(
                'key' => 'trek_featured',
                'value' => '1',
                'compare' => '=',
            ),
        );
    } elseif ($trek_filter === 'destination') {
        if ($filter_destination_id <= 0) {
            return;
        }
        $trek_query_args['meta_query'] = array(
            array(
                'key' => 'trek_destination_ids',
                'value' => 'i:' . $filter_destination_id . ';',
                'compare' => 'LIKE',
            ),
        );
    }

    $filtered_trek_query = new WP_Query($trek_query_args);
    $filtered_trek_ids = is_array($filtered_trek_query->posts) ? array_values(array_filter(array_map('absint', $filtered_trek_query->posts))) : array();

    if (empty($filtered_trek_ids)) {
        return;
    }

    $departure_meta_query[] = array(
        'key' => 'aatf_trek_id',
        'value' => $filtered_trek_ids,
        'compare' => 'IN',
        'type' => 'NUMERIC',
    );
}

$departures = new WP_Query(array(
    'post_type' => 'departure',
    'post_status' => 'publish',
    'posts_per_page' => $limit,
    'meta_query' => $departure_meta_query,
    'meta_key' => 'departure_start_date',
    'orderby' => 'meta_value',
    'order' => 'ASC',
    'no_found_rows' => true,
));

if (!$departures->have_posts()) {
    wp_reset_postdata();
    return;
}

$status_labels = array(
    'available' => 'Available',
    'limited' => 'Limited',
    'guaranteed' => 'Guaranteed',
    'full' => 'Full',
);
$booking_page = get_page_by_path('booking');
$internal_booking_url = ($booking_page instanceof WP_Post)
    ? get_permalink((int) $booking_page->ID)
    : home_url('/booking/');

echo '<section class="aatf-home-section aatf-home-section--departures">';
if ($heading !== '') {
    echo '<h2 class="aatf-home-section__title">' . esc_html($heading) . '</h2>';
}

echo '<ul class="aatf-departure-list">';
while ($departures->have_posts()) {
    $departures->the_post();
    $departure_id = get_the_ID();
    $trek_id = (int) get_post_meta($departure_id, 'aatf_trek_id', true);
    $trek_title = $trek_id > 0 ? get_the_title($trek_id) : '';
    $trek_url = $trek_id > 0 ? get_permalink($trek_id) : '';

    $start_raw = (string) get_post_meta($departure_id, 'departure_start_date', true);
    $end_raw = (string) get_post_meta($departure_id, 'departure_end_date', true);
    $status_raw = strtolower(trim((string) get_post_meta($departure_id, 'departure_status', true)));

    $departure_price = get_post_meta($departure_id, 'departure_price', true);
    $departure_price = is_numeric($departure_price) ? (float) $departure_price : 0.0;

    $base_price = $trek_id > 0 ? (float) get_post_meta($trek_id, 'trek_price', true) : 0.0;
    $effective_price = $departure_price > 0 ? $departure_price : $base_price;
    $price_label = $effective_price > 0 ? '$' . number_format_i18n($effective_price, 0) : 'Price on request';

    $start_ts = $start_raw !== '' ? strtotime($start_raw) : false;
    $end_ts = $end_raw !== '' ? strtotime($end_raw) : false;
    $start_label = $start_ts ? date_i18n(get_option('date_format'), $start_ts) : '';
    $end_label = $end_ts ? date_i18n(get_option('date_format'), $end_ts) : '';
    if ($start_label !== '' && $end_label !== '' && $start_label !== $end_label) {
        $date_label = $start_label . ' - ' . $end_label;
    } else {
        $date_label = $start_label !== '' ? $start_label : ($end_label !== '' ? $end_label : 'Date to be announced');
    }

    $status_label = isset($status_labels[$status_raw]) ? $status_labels[$status_raw] : 'Available';
    $external_booking_url = $trek_id > 0 ? trim((string) get_post_meta($trek_id, 'trek_booking_url', true)) : '';
    $booking_base_url = ($external_booking_url !== '' && preg_match('~^https?://~i', $external_booking_url))
        ? $external_booking_url
        : $internal_booking_url;
    $booking_url = add_query_arg(
        array(
            'trek_id' => $trek_id > 0 ? $trek_id : '',
            'departure_id' => $departure_id,
        ),
        $booking_base_url
    );

    echo '<li class="aatf-departure-item">';
    echo '<div class="aatf-departure-item__main">';
    echo '<strong class="aatf-departure-item__date">' . esc_html($date_label) . '</strong>';
    if ($trek_title !== '' && $trek_url !== '') {
        echo '<p class="aatf-departure-item__trek"><a href="' . esc_url($trek_url) . '">' . esc_html($trek_title) . '</a></p>';
    }
    echo '</div>';
    echo '<div class="aatf-departure-item__meta">';
    echo '<span class="aatf-meta-pill">Status: ' . esc_html($status_label) . '</span>';
    echo '<span class="aatf-meta-pill">Price: ' . esc_html($price_label) . '</span>';
    echo '</div>';
    echo '<div class="aatf-departure-item__actions">';
    echo '<a class="aatf-btn aatf-btn--cta aatf-departure-item__book" href="' . esc_url($booking_url) . '">Book Now</a>';
    echo '</div>';
    echo '</li>';
}
echo '</ul>';
echo '</section>';

wp_reset_postdata();
