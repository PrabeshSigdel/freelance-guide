<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

$default_sections = aatf_home_layout_allowed_sections();
$layout_raw = (string) get_theme_mod('aatf_home_layout', implode(',', $default_sections));
$sections = aatf_parse_home_layout($layout_raw);
if (empty($sections)) {
    $sections = $default_sections;
}

$exclusive_section = 'exclusive-activities';
if (!in_array($exclusive_section, $sections, true)) {
    $hero_pos = array_search('hero', $sections, true);
    if ($hero_pos === false) {
        array_unshift($sections, $exclusive_section);
    } else {
        array_splice($sections, $hero_pos + 1, 0, $exclusive_section);
    }
}

$data_sections = array('featured-treks', 'top-destinations', 'testimonials', 'departures');
$has_framework = class_exists('AATF_Frontend_Components');

foreach ($sections as $section) {
    if (!$has_framework && in_array($section, $data_sections, true)) {
        continue;
    }

    get_template_part('template-parts/home/' . $section);
}

if (!$has_framework && array_intersect($sections, $data_sections)) {
    echo '<p class="aatf-home-empty">Activate AA Trek Framework plugin to load homepage sections.</p>';
}

get_footer();
