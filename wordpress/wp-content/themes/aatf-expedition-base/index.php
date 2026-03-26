<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) {
    while (have_posts()) {
        the_post();
        echo '<article>';
        echo '<h2><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h2>';
        the_excerpt();
        echo '</article>';
    }
} else {
    echo '<p>No content found.</p>';
}

get_footer();
