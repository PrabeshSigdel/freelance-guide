<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) {
    while (have_posts()) {
        the_post();
        $page_content = (string) get_post_field('post_content', get_the_ID());
        $has_booking_shortcode = function_exists('has_shortcode') && has_shortcode($page_content, 'aatf_booking_form');
        $should_render_default_booking_form = is_page('booking') && !$has_booking_shortcode && trim($page_content) === '';
        echo '<article>';
        if (!is_page('booking')) {
            echo '<h1>' . esc_html(get_the_title()) . '</h1>';
        }
        the_content();
        if ($should_render_default_booking_form && shortcode_exists('aatf_booking_form')) {
            echo do_shortcode('[aatf_booking_form]');
        }
        echo '</article>';
    }
}

get_footer();
