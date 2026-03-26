<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<header class="site-hero">
    <h1><?php post_type_archive_title(); ?></h1>
    <p>Browse all available trekking packages.</p>
</header>

<?php
if (class_exists('AATF_Frontend_Components')) {
    echo do_shortcode('[aatf_trek_cards limit="12"]');
} else {
    if (have_posts()) {
        while (have_posts()) {
            the_post();
            echo '<article><h2><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h2></article>';
        }
        the_posts_pagination();
    } else {
        echo '<p>No treks found.</p>';
    }
}

get_footer();
