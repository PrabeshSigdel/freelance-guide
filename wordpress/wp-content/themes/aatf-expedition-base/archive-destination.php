<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<header class="site-hero">
    <h1><?php post_type_archive_title(); ?></h1>
    <?php if (trim((string) get_the_archive_description()) !== '') : ?>
        <p><?php echo esc_html(wp_strip_all_tags((string) get_the_archive_description())); ?></p>
    <?php else : ?>
        <p>Browse destinations and discover related treks.</p>
    <?php endif; ?>
</header>
<?php

$destinations = new WP_Query(array(
    'post_type' => 'destination',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'post_parent' => 0,
    'orderby' => array(
        'menu_order' => 'ASC',
        'title' => 'ASC',
    ),
    'order' => 'ASC',
    'no_found_rows' => true,
));

if ($destinations->have_posts()) {
    echo '<div class="aatf-destination-grid">';

    while ($destinations->have_posts()) {
        $destinations->the_post();
        $destination_id = get_the_ID();
        $country = (string) get_post_meta($destination_id, 'destination_country', true);
        $thumbnail = get_the_post_thumbnail_url($destination_id, 'large');
        $children = get_posts(array(
            'post_type' => 'destination',
            'post_status' => 'publish',
            'posts_per_page' => 8,
            'post_parent' => $destination_id,
            'orderby' => 'menu_order title',
            'order' => 'ASC',
        ));

        echo '<div class="aatf-destination-card">';
        if ($thumbnail) {
            echo '<a href="' . esc_url(get_permalink($destination_id)) . '"><img src="' . esc_url($thumbnail) . '" alt="' . esc_attr(get_the_title($destination_id)) . '" /></a>';
        }
        echo '<div class="aatf-destination-card__content">';
        echo '<h3><a href="' . esc_url(get_permalink($destination_id)) . '">' . esc_html(get_the_title($destination_id)) . '</a></h3>';
        if ($country !== '') {
            echo '<p class="aatf-destination-card__country">' . esc_html($country) . '</p>';
        }
        if (!empty($children)) {
            echo '<ul class="aatf-destination-card__children">';
            foreach ($children as $child) {
                echo '<li><a href="' . esc_url(get_permalink((int) $child->ID)) . '">' . esc_html((string) $child->post_title) . '</a></li>';
            }
            echo '</ul>';
        }
        echo '<a href="' . esc_url(get_permalink($destination_id)) . '" class="aatf-btn">Explore</a>';
        echo '</div>';
        echo '</div>';
    }

    echo '</div>';
    wp_reset_postdata();
} else {
    echo '<p>No destinations found.</p>';
}

get_footer();
