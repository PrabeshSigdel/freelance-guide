<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

/* ── Define sections for Explore Tours page ──────────────────────── */
$default_sections = array(
    'hero',
    'tour-list',
);

/* ── Map keys → actual template-part slugs ───────────────────────── */
$section_map = array(
    'hero'      => 'hero',
    'tour-list' => 'tour-list',
);

/* ── Render each section ─────────────────────────────────────────── */
$rendered = array(); // prevent duplicate renders

foreach ($default_sections as $section_key) {
    $slug = isset($section_map[$section_key]) ? $section_map[$section_key] : $section_key;

    // Skip if already rendered
    if (in_array($slug, $rendered, true)) {
        continue;
    }

    $file = get_template_directory() . '/template-parts/explore-tour-page/' . $slug . '.php';

    if (file_exists($file)) {
        get_template_part('template-parts/explore-tour-page/' . $slug);
        $rendered[] = $slug;
    }
}

get_footer();
