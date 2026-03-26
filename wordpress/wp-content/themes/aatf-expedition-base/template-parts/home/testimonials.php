<?php
if (!defined('ABSPATH')) {
    exit;
}

if (get_theme_mod('aatf_home_show_testimonials', '1') !== '1') {
    return;
}

$heading = trim((string) get_theme_mod('aatf_home_testimonials_heading', 'What Our Trekkers Say'));
$limit = max(1, min(24, (int) get_theme_mod('aatf_home_testimonials_limit', 3)));

echo '<section class="aatf-home-section aatf-home-section--testimonials">';
if ($heading !== '') {
    echo '<h2 class="aatf-home-section__title">' . esc_html($heading) . '</h2>';
}

if (class_exists('AATF_Frontend_Components')) {
    echo do_shortcode('[aatf_testimonials limit="' . esc_attr((string) $limit) . '"]');
} else {
    echo '<p>Testimonials module is unavailable.</p>';
}

echo '</section>';
