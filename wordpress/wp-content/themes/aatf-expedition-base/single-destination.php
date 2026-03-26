<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) {
    while (have_posts()) {
        the_post();

        $destination_id = get_the_ID();
        $parent_id = (int) wp_get_post_parent_id($destination_id);
        $country = (string) get_post_meta($destination_id, 'destination_country', true);

        $children = get_posts(array(
            'post_type' => 'destination',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'post_parent' => $destination_id,
            'orderby' => 'menu_order title',
            'order' => 'ASC',
        ));

        $related_destinations = $children;
        $related_destinations_title = 'Sub Destinations';
        if (empty($related_destinations) && $parent_id > 0) {
            $related_destinations = get_posts(array(
                'post_type' => 'destination',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'post_parent' => $parent_id,
                'post__not_in' => array($destination_id),
                'orderby' => 'menu_order title',
                'order' => 'ASC',
            ));

            $parent_title = get_the_title($parent_id);
            $related_destinations_title = $parent_title ? 'More in ' . $parent_title : 'Related Destinations';
        }

        $descendants = get_pages(array(
            'post_type' => 'destination',
            'child_of' => $destination_id,
            'sort_column' => 'menu_order,post_title',
            'sort_order' => 'ASC',
        ));

        $related_destination_ids = array($destination_id);
        if (!empty($descendants)) {
            $descendant_ids = array_map('absint', wp_list_pluck($descendants, 'ID'));
            $related_destination_ids = array_values(array_unique(array_merge($related_destination_ids, $descendant_ids)));
        }

        $trek_meta_query = array('relation' => 'OR');
        foreach ($related_destination_ids as $related_destination_id) {
            if ($related_destination_id <= 0) {
                continue;
            }

            $trek_meta_query[] = array(
                'key' => 'trek_destination_ids',
                'value' => 'i:' . $related_destination_id . ';',
                'compare' => 'LIKE',
            );
        }

        $related_treks = null;
        if (count($trek_meta_query) > 1) {
            $related_treks = new WP_Query(array(
                'post_type' => 'trek',
                'post_status' => 'publish',
                'posts_per_page' => 12,
                'meta_query' => $trek_meta_query,
                'meta_key' => 'trek_display_order',
                'orderby' => array(
                    'meta_value_num' => 'ASC',
                    'title' => 'ASC',
                ),
                'order' => 'ASC',
                'no_found_rows' => true,
            ));
        }

        echo '<article class="aatf-single-destination">';
        echo '<h1>' . esc_html(get_the_title()) . '</h1>';

        if ($parent_id > 0) {
            echo '<p class="aatf-single-destination__parent">Part of: <a href="' . esc_url(get_permalink($parent_id)) . '">' . esc_html(get_the_title($parent_id)) . '</a></p>';
        }

        if ($country !== '') {
            echo '<p class="aatf-single-destination__country">' . esc_html($country) . '</p>';
        }

        if (has_post_thumbnail($destination_id)) {
            echo '<div class="aatf-single-destination__hero">';
            echo get_the_post_thumbnail($destination_id, 'large', array('class' => 'aatf-single-destination__hero-image'));
            echo '</div>';
        }

        $content = trim((string) get_the_content());
        if ($content !== '') {
            echo '<section class="aatf-panel">';
            the_content();
            echo '</section>';
        }

        if (!empty($related_destinations)) {
            echo '<section class="aatf-panel">';
            echo '<h3>' . esc_html($related_destinations_title) . '</h3>';
            echo '<div class="aatf-destination-grid">';
            foreach ($related_destinations as $related_destination) {
                $related_id = (int) $related_destination->ID;
                $related_country = (string) get_post_meta($related_id, 'destination_country', true);
                $related_thumb = get_the_post_thumbnail_url($related_id, 'large');

                echo '<div class="aatf-destination-card">';
                if ($related_thumb) {
                    echo '<a href="' . esc_url(get_permalink($related_id)) . '"><img src="' . esc_url($related_thumb) . '" alt="' . esc_attr(get_the_title($related_id)) . '" /></a>';
                }
                echo '<div class="aatf-destination-card__content">';
                echo '<h3><a href="' . esc_url(get_permalink($related_id)) . '">' . esc_html(get_the_title($related_id)) . '</a></h3>';
                if ($related_country !== '') {
                    echo '<p class="aatf-destination-card__country">' . esc_html($related_country) . '</p>';
                }
                echo '<a href="' . esc_url(get_permalink($related_id)) . '" class="aatf-btn">Explore</a>';
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
            echo '</section>';
        }

        if ($related_treks instanceof WP_Query && $related_treks->have_posts()) {
            echo '<section class="aatf-home-section aatf-home-section--treks">';
            echo '<h3>Related Treks</h3>';
            echo '<div class="aatf-trek-grid">';
            while ($related_treks->have_posts()) {
                $related_treks->the_post();
                $trek_id = get_the_ID();
                if (class_exists('AATF_Frontend_Components') && method_exists('AATF_Frontend_Components', 'render_trek_card')) {
                    echo AATF_Frontend_Components::render_trek_card(array('id' => $trek_id));
                } else {
                    echo '<article class="aatf-panel"><h4><a href="' . esc_url(get_permalink($trek_id)) . '">' . esc_html(get_the_title($trek_id)) . '</a></h4></article>';
                }
            }
            echo '</div>';
            echo '</section>';
            wp_reset_postdata();
        }

        echo '</article>';
    }
}

get_footer();
