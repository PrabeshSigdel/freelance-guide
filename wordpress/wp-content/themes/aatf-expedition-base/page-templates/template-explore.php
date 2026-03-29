<?php
/**
 * Template Name: Explore Template
 *
 * Explore tours landing page template.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$default_sections = array(
    'hero',
    'tour-list',
);

$section_map = array(
    'hero'      => 'hero',
    'tour-list' => 'tour-list',
);

$rendered = array();

foreach ($default_sections as $section_key) {
    $slug = isset($section_map[$section_key]) ? $section_map[$section_key] : $section_key;

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
