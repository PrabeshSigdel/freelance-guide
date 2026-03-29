<?php
/**
 * Template Name: Home Template
 *
 * Flexible homepage sections template.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$default_sections = array(
    'hero',
    'exotic-places',
    'plan-trips',
    'popular-tours',
    'popular-tours2',
    'testimonials',
    'news-articles',
);

$layout_raw = (string) get_theme_mod(
    'aatf_home_layout',
    implode(',', $default_sections)
);

$sections = aatf_parse_home_layout($layout_raw);
if (empty($sections)) {
    $sections = $default_sections;
}

$section_map = array(
    'hero'                  => 'hero',
    'exotic-places'         => 'exotic-places',
    'plan-trips'            => 'plan-trips',
    'popular-tours'         => 'popular-tours',
    'popular-tours2'        => 'popular-tours2',
    'featured-treks'        => 'popular-tours',
    'top-destinations'      => 'exotic-places',
    'testimonials'          => 'testimonials',
    'departures'            => 'popular-tours2',
    'exclusive-activities'  => 'plan-trips',
    'content'               => 'plan-trips',
    'news-articles'         => 'news-articles',
);

if (!in_array('news-articles', $sections, true)) {
    $sections[] = 'news-articles';
}

$rendered = array();

foreach ($sections as $section_key) {
    $slug = isset($section_map[$section_key]) ? $section_map[$section_key] : $section_key;

    if (in_array($slug, $rendered, true)) {
        continue;
    }

    $file = get_template_directory() . '/template-parts/home/' . $slug . '.php';
    if (file_exists($file)) {
        get_template_part('template-parts/home/' . $slug);
        $rendered[] = $slug;
    }
}

get_footer();
