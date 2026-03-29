<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

/* ── Build section order from Customizer ─────────────────────────── */
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

/* ── Map Customizer keys → actual template-part slugs ───────────── */
$section_map = array(
    'hero'                  => 'hero',
    'exotic-places'         => 'exotic-places',
    'plan-trips'            => 'plan-trips',
    'popular-tours'         => 'popular-tours',
    'popular-tours2'        => 'popular-tours2',
    'featured-treks'        => 'popular-tours',       // legacy alias → carousel
    'top-destinations'      => 'exotic-places',       // legacy alias
    'testimonials'          => 'testimonials',
    'departures'            => 'popular-tours2',      // legacy alias → grid
    'exclusive-activities'  => 'plan-trips',          // legacy alias
    'content'               => 'plan-trips',          // legacy alias
    'news-articles'         => 'news-articles',
);

/* ── Ensure blog / news section is always included ──────────────── */
if (!in_array('news-articles', $sections, true)) {
    $sections[] = 'news-articles';
}

/* ── Render each section ─────────────────────────────────────────── */
$rendered = array(); // prevent duplicate renders

foreach ($sections as $section_key) {
    $slug = isset($section_map[$section_key]) ? $section_map[$section_key] : $section_key;

    // Skip if already rendered (e.g. two Customizer keys map to same part)
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
