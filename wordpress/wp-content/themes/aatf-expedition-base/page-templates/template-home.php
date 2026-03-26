<?php
/**
 * Template Name: Home Template
 *
 * A flexible homepage template that can be enhanced with custom metabox content.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) {
    the_post();
    the_content();
}

get_footer();

