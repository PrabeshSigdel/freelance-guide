<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) {
    while (have_posts()) {
        the_post();

        $post_id = get_the_ID();
        $duration = get_post_meta($post_id, 'trek_duration', true);
        $price = (float) get_post_meta($post_id, 'trek_price', true);
        $group = (int) get_post_meta($post_id, 'trek_group_size', true);
        $altitude = get_post_meta($post_id, 'trek_max_altitude', true);
        $slider_ids = get_post_meta($post_id, 'trek_slider_image_ids', true);
        if (!is_array($slider_ids) || empty($slider_ids)) {
            $slider_ids = get_post_meta($post_id, 'trek_gallery_ids', true);
        }
        $slider_ids = is_array($slider_ids) ? array_values(array_filter(array_map('absint', $slider_ids))) : array();
        $overview = get_post_meta($post_id, 'trek_overview', true);
        $reviews = (string) get_post_meta($post_id, 'trek_reviews', true);
        $map_embed = (string) get_post_meta($post_id, 'trek_map_embed', true);
        $booking_url = (string) get_post_meta($post_id, 'trek_booking_url', true);
        $highlights_title = (string) get_post_meta($post_id, 'trek_highlights_title', true);
        $highlights_intro = (string) get_post_meta($post_id, 'trek_highlights_intro', true);
        $highlights_text = (string) get_post_meta($post_id, 'trek_highlights', true);
        $highlights = preg_split("/\r\n|\n|\r/", $highlights_text);
        $highlights = is_array($highlights) ? array_values(array_filter(array_map('trim', $highlights), static function ($item) {
            return $item !== '';
        })) : array();
        $video_url = (string) get_post_meta($post_id, 'trek_video_url', true);
        $altitude_profile_rows = get_post_meta($post_id, 'trek_altitude_profile', true);
        $altitude_profile_rows = is_array($altitude_profile_rows) ? $altitude_profile_rows : array();
        $altitude_profile = array();
        foreach ($altitude_profile_rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $day = isset($row['day']) ? trim((string) $row['day']) : '';
            $place = isset($row['place']) ? trim((string) $row['place']) : '';
            $point_altitude = isset($row['altitude_m']) ? (int) $row['altitude_m'] : 0;

            if ($day === '' && $place === '' && $point_altitude <= 0) {
                continue;
            }

            $altitude_profile[] = array(
                'day' => $day,
                'place' => $place,
                'altitude_m' => max(0, $point_altitude),
            );
        }
        $equipment_sections = get_post_meta($post_id, 'trek_equipment_sections', true);
        $equipment_sections = is_array($equipment_sections) ? $equipment_sections : array();
        $departures = new WP_Query(array(
            'post_type' => 'departure',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'aatf_trek_id',
                    'value' => $post_id,
                    'compare' => '=',
                    'type' => 'NUMERIC',
                ),
            ),
            'meta_key' => 'departure_start_date',
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'no_found_rows' => true,
        ));
        $video_embed = '';

        if ($video_url !== '') {
            $video_raw = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', trim($video_url));

            if (stripos($video_raw, '<iframe') !== false) {
                $allowed_iframe = array(
                    'iframe' => array(
                        'src' => true,
                        'width' => true,
                        'height' => true,
                        'frameborder' => true,
                        'allow' => true,
                        'allowfullscreen' => true,
                        'loading' => true,
                        'referrerpolicy' => true,
                        'title' => true,
                    ),
                );
                $video_embed = (string) wp_kses($video_raw, $allowed_iframe);
            } else {
                $video_embed = (string) wp_oembed_get($video_raw);
                if ($video_embed === '' && preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $video_raw, $matches)) {
                    $embed_src = 'https://www.youtube.com/embed/' . $matches[1];
                    $video_embed = '<iframe src="' . esc_url($embed_src) . '" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe>';
                }

                if ($video_embed === '' && preg_match('~^[A-Za-z0-9_-]{11}$~', $video_raw)) {
                    $embed_src = 'https://www.youtube.com/embed/' . $video_raw;
                    $video_embed = '<iframe src="' . esc_url($embed_src) . '" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe>';
                }
            }
        }

        echo '<article class="aatf-single-trek">';
        echo '<h1>' . esc_html(get_the_title()) . '</h1>';

        if (!empty($slider_ids)) {
            echo '<div class="aatf-single-trek__hero aatf-slider" data-aatf-slider>';
            echo '<button type="button" class="aatf-slider__btn aatf-slider__btn--prev" aria-label="Previous image">&#10094;</button>';
            echo '<div class="aatf-slider__track">';
            foreach ($slider_ids as $index => $image_id) {
                $image = wp_get_attachment_image($image_id, 'large', false, array(
                    'class' => 'aatf-single-trek__hero-image',
                    'loading' => $index === 0 ? 'eager' : 'lazy',
                ));
                if ($image) {
                    echo '<div class="aatf-slider__slide' . ($index === 0 ? ' is-active' : '') . '">' . $image . '</div>';
                }
            }
            echo '</div>';
            echo '<button type="button" class="aatf-slider__btn aatf-slider__btn--next" aria-label="Next image">&#10095;</button>';
            echo '</div>';
        } elseif (has_post_thumbnail($post_id)) {
            echo '<div class="aatf-single-trek__hero">';
            echo get_the_post_thumbnail($post_id, 'large', array('class' => 'aatf-single-trek__hero-image'));
            echo '</div>';
        }

        echo '<div class="aatf-single-trek__meta">';
        if ($duration !== '') {
            echo '<span class="aatf-meta-pill">Duration: ' . esc_html($duration) . ' days</span>';
        }
        if ($price > 0) {
            echo '<span class="aatf-meta-pill">Price: $' . esc_html(number_format_i18n($price, 0)) . '</span>';
        }
        if ($group > 0) {
            echo '<span class="aatf-meta-pill">Group: ' . esc_html($group) . '</span>';
        }
        if ($altitude !== '') {
            echo '<span class="aatf-meta-pill">Max Altitude: ' . esc_html($altitude) . 'm</span>';
        }
        echo '</div>';

        echo '<div class="aatf-two-col">';
        echo '<div class="aatf-panel">';
        the_content();
        if (!empty($overview)) {
            echo '<h3>Overview</h3><p>' . esc_html($overview) . '</p>';
        }

        if ($highlights_title !== '' || $highlights_intro !== '' || !empty($highlights)) {
            echo '<h3>' . esc_html($highlights_title !== '' ? $highlights_title : 'Trek Highlights') . '</h3>';
            if ($highlights_intro !== '') {
                echo '<p>' . esc_html($highlights_intro) . '</p>';
            }
            if (!empty($highlights)) {
                echo '<ul>';
                foreach ($highlights as $item) {
                    echo '<li>' . esc_html($item) . '</li>';
                }
                echo '</ul>';
            }
        }
        echo '</div>';

        $booking_page = get_page_by_path('booking');
        $internal_booking_url = ($booking_page instanceof WP_Post)
            ? get_permalink((int) $booking_page->ID)
            : home_url('/booking/');
        $external_booking_url = trim((string) $booking_url);
        $booking_base_url = $internal_booking_url;
        if ($external_booking_url !== '' && preg_match('~^https?://~i', $external_booking_url)) {
            $booking_base_url = $external_booking_url;
        }
        $booking_cta_url = add_query_arg(
            array(
                'trek_id' => $post_id,
            ),
            $booking_base_url
        );

        echo '<aside class="aatf-panel">';
        echo '<h3>Book This Trek</h3>';
        echo '<p>Ready to plan this route? Start your booking request.</p>';
        echo '<p><a class="aatf-btn aatf-btn--cta" href="' . esc_url($booking_cta_url) . '">Book Now</a></p>';
        if (shortcode_exists('aatf_trek_group_pricing')) {
            echo do_shortcode('[aatf_trek_group_pricing trek_id="' . esc_attr((string) $post_id) . '" wrap="none"]');
        }
        echo '</aside>';
        echo '</div>';

        if (shortcode_exists('aatf_trek_trip_cost')) {
            echo do_shortcode('[aatf_trek_trip_cost trek_id="' . esc_attr((string) $post_id) . '"]');
        }

        if (shortcode_exists('aatf_trek_itinerary')) {
            echo do_shortcode('[aatf_trek_itinerary trek_id="' . esc_attr((string) $post_id) . '"]');
        }

        if (!empty($altitude_profile)) {
            echo '<section class="aatf-panel aatf-altitude-profile" data-aatf-altitude-profile>';
            echo '<h3>Altitude Profile</h3>';
            echo '<div class="aatf-altitude-profile__head">';
            echo '<span class="aatf-altitude-profile__label">Altitude in:</span>';
            echo '<div class="aatf-altitude-profile__units" role="group" aria-label="Altitude unit">';
            echo '<button type="button" class="aatf-altitude-profile__unit is-active" data-unit="m" aria-pressed="true">Meter</button>';
            echo '<button type="button" class="aatf-altitude-profile__unit" data-unit="ft" aria-pressed="false">Feet</button>';
            echo '</div>';
            echo '</div>';
            echo '<div class="aatf-altitude-profile__chart" data-altitude-chart></div>';
            echo '<div class="aatf-altitude-profile__scroll" data-altitude-scroll>';
            echo '<button type="button" class="aatf-altitude-profile__scroll-btn" data-scroll-dir="left" aria-label="Scroll left">&#8592;</button>';
            echo '<input type="range" class="aatf-altitude-profile__scroll-range" min="0" max="100" value="0" step="1" aria-label="Scroll altitude profile" />';
            echo '<button type="button" class="aatf-altitude-profile__scroll-btn" data-scroll-dir="right" aria-label="Scroll right">&#8594;</button>';
            echo '</div>';
            $altitude_profile_json = wp_json_encode(
                $altitude_profile,
                JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
            );
            echo '<script type="application/json" class="aatf-altitude-profile__data">' . ($altitude_profile_json !== false ? $altitude_profile_json : '[]') . '</script>';
            echo '</section>';
        }

        if ($map_embed !== '') {
            $allowed_map = array(
                'iframe' => array(
                    'src' => true,
                    'width' => true,
                    'height' => true,
                    'frameborder' => true,
                    'allow' => true,
                    'allowfullscreen' => true,
                    'loading' => true,
                    'referrerpolicy' => true,
                    'title' => true,
                ),
                'a' => array(
                    'href' => true,
                    'target' => true,
                    'rel' => true,
                ),
            );
            echo '<section class="aatf-panel">';
            echo '<h3>Route Map</h3>';
            if (stripos($map_embed, '<iframe') !== false) {
                echo wp_kses($map_embed, $allowed_map);
            } else {
                $map_url = esc_url($map_embed);
                if ($map_url !== '') {
                    echo '<p><a href="' . $map_url . '" target="_blank" rel="noopener">Open Map</a></p>';
                }
            }
            echo '</section>';
        }

        if ($video_embed) {
            $allowed_iframe = array(
                'iframe' => array(
                    'src' => true,
                    'width' => true,
                    'height' => true,
                    'frameborder' => true,
                    'allow' => true,
                    'allowfullscreen' => true,
                    'loading' => true,
                    'referrerpolicy' => true,
                    'title' => true,
                ),
            );
            echo '<section class="aatf-panel aatf-single-trek__video">';
            echo '<h3>Trek Video</h3>';
            echo wp_kses($video_embed, $allowed_iframe);
            echo '</section>';
        }

        if (!empty($equipment_sections)) {
            echo '<section class="aatf-panel aatf-single-trek__equipment">';
            echo '<h3>Equipment</h3>';

            foreach ($equipment_sections as $section) {
                $heading = isset($section['heading']) ? (string) $section['heading'] : '';
                $image_id = isset($section['image_id']) ? (int) $section['image_id'] : 0;
                $items = isset($section['items']) && is_array($section['items']) ? $section['items'] : array();
                $image_url = $image_id > 0 ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';

                if ($heading === '' && empty($items) && !$image_url) {
                    continue;
                }

                echo '<div class="aatf-equipment-section">';
                echo '<div class="aatf-equipment-section__heading">';
                if ($image_url) {
                    echo '<img class="aatf-equipment-section__icon" src="' . esc_url($image_url) . '" alt="" />';
                }
                if ($heading !== '') {
                    echo '<h4>' . esc_html($heading) . '</h4>';
                }
                echo '</div>';

                if (!empty($items)) {
                    echo '<ul class="aatf-equipment-section__items">';
                    foreach ($items as $item) {
                        $clean_item = (string) $item;
                        if ($clean_item === '') {
                            continue;
                        }
                        echo '<li>' . esc_html($clean_item) . '</li>';
                    }
                    echo '</ul>';
                }

                echo '</div>';
            }

            echo '</section>';
        }

        if ($departures->have_posts()) {
            $status_labels = array(
                'available' => 'Available',
                'limited' => 'Limited',
                'guaranteed' => 'Guaranteed',
                'full' => 'Full',
            );

            echo '<section class="aatf-panel aatf-single-trek__departures">';
            echo '<h3>Departures</h3>';
            echo '<ul class="aatf-departure-list">';

            while ($departures->have_posts()) {
                $departures->the_post();
                $departure_id = get_the_ID();
                $departure_title = trim((string) get_the_title($departure_id));
                $start_raw = (string) get_post_meta($departure_id, 'departure_start_date', true);
                $end_raw = (string) get_post_meta($departure_id, 'departure_end_date', true);
                $status_raw = strtolower(trim((string) get_post_meta($departure_id, 'departure_status', true)));
                $departure_price = get_post_meta($departure_id, 'departure_price', true);
                $departure_price = is_numeric($departure_price) ? (float) $departure_price : 0.0;

                $start_ts = $start_raw !== '' ? strtotime($start_raw) : false;
                $end_ts = $end_raw !== '' ? strtotime($end_raw) : false;
                $start_label = $start_ts ? date_i18n(get_option('date_format'), $start_ts) : '';
                $end_label = $end_ts ? date_i18n(get_option('date_format'), $end_ts) : '';

                $date_label = '';
                if ($start_label !== '' && $end_label !== '' && $start_label !== $end_label) {
                    $date_label = $start_label . ' - ' . $end_label;
                } elseif ($start_label !== '') {
                    $date_label = $start_label;
                } elseif ($end_label !== '') {
                    $date_label = $end_label;
                } elseif ($departure_title !== '') {
                    $date_label = $departure_title;
                } else {
                    $date_label = 'Date to be announced';
                }

                $status_label = isset($status_labels[$status_raw]) ? $status_labels[$status_raw] : 'Available';
                $effective_price = $departure_price > 0 ? $departure_price : $price;
                $price_label = $effective_price > 0 ? '$' . number_format_i18n($effective_price, 0) : 'Price on request';
                $departure_booking_url = add_query_arg(
                    array(
                        'trek_id' => $post_id,
                        'departure_id' => $departure_id,
                    ),
                    $booking_base_url
                );

                echo '<li class="aatf-departure-item">';
                echo '<strong class="aatf-departure-item__date">' . esc_html($date_label) . '</strong>';
                echo '<div class="aatf-departure-item__meta">';
                echo '<span class="aatf-meta-pill">Status: ' . esc_html($status_label) . '</span>';
                echo '<span class="aatf-meta-pill">Price: ' . esc_html($price_label) . '</span>';
                echo '</div>';
                echo '<a class="aatf-btn aatf-btn--cta aatf-departure-item__book" href="' . esc_url($departure_booking_url) . '">Book Now</a>';
                echo '</li>';
            }

            echo '</ul>';
            echo '</section>';
            wp_reset_postdata();
        }

        if ($reviews !== '') {
            echo '<section class="aatf-panel">';
            echo '<h3>Reviews Summary</h3>';
            echo '<p>' . nl2br(esc_html($reviews)) . '</p>';
            echo '</section>';
        }

        if (class_exists('AATF_Frontend_Components')) {
            echo do_shortcode('[aatf_trek_faq]');
        }

        echo '</article>';
    }
}

get_footer();
