<?php
if (!defined('ABSPATH')) {
    exit;
}

if (get_theme_mod('aatf_home_show_destinations', '1') !== '1') {
    return;
}

$heading = trim((string) get_theme_mod('aatf_home_destinations_heading', 'Top Destinations'));
$limit = max(1, min(24, (int) get_theme_mod('aatf_home_destinations_limit', 6)));

echo '<section class="aatf-home-section aatf-home-section--destinations">';
if ($heading !== '') {
    echo '<h2 class="aatf-home-section__title">' . esc_html($heading) . '</h2>';
}

if (class_exists('AATF_Frontend_Components')) {
    echo do_shortcode('[aatf_destinations limit="' . esc_attr((string) $limit) . '"]');
} else {
    echo '<p>Destination module is unavailable.</p>';
}

echo '</section>';
