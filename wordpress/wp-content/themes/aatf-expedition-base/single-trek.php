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
        $prepared_equipment_sections = array();
        foreach ($equipment_sections as $section) {
            if (!is_array($section)) {
                continue;
            }

            $heading = isset($section['heading']) ? trim((string) $section['heading']) : '';
            $items = isset($section['items']) && is_array($section['items']) ? $section['items'] : array();
            $image_id = isset($section['image_id']) ? absint($section['image_id']) : 0;
            if ($image_id <= 0 && isset($section['image'])) {
                $image_id = absint($section['image']);
            }
            if ($image_id <= 0 && isset($section['equipment_image'])) {
                $image_id = absint($section['equipment_image']);
            }

            $image_url = '';
            if ($image_id > 0) {
                $image_url = (string) wp_get_attachment_image_url($image_id, 'thumbnail');
            }
            if ($image_url === '' && !empty($section['image_url'])) {
                $image_url = esc_url_raw((string) $section['image_url']);
            }
            if ($image_url === '' && !empty($section['image'])) {
                $image_candidate = trim((string) $section['image']);
                if (preg_match('~^https?://~i', $image_candidate)) {
                    $image_url = esc_url_raw($image_candidate);
                }
            }

            $clean_items = array();
            foreach ($items as $item) {
                $clean_item = trim((string) $item);
                if ($clean_item === '') {
                    continue;
                }

                $clean_items[] = $clean_item;
            }

            if ($heading === '' && empty($clean_items) && $image_url === '') {
                continue;
            }

            $prepared_equipment_sections[] = array(
                'heading' => $heading,
                'items' => $clean_items,
                'image_url' => $image_url,
            );
        }
        $cost_includes_text = (string) get_post_meta($post_id, 'trek_cost_includes', true);
        $cost_excludes_text = (string) get_post_meta($post_id, 'trek_cost_excludes', true);
        $cost_includes = array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", $cost_includes_text)), static function ($v) {
            return $v !== '';
        }));
        $cost_excludes = array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", $cost_excludes_text)), static function ($v) {
            return $v !== '';
        }));
        $group_pricing_raw = get_post_meta($post_id, 'trek_group_pricing', true);
        $group_pricing_rows = array();
        if (is_array($group_pricing_raw)) {
            foreach ($group_pricing_raw as $pricing_row) {
                if (!is_array($pricing_row)) {
                    continue;
                }

                $min_people = isset($pricing_row['min_people']) ? absint($pricing_row['min_people']) : 0;
                $max_people = isset($pricing_row['max_people']) ? absint($pricing_row['max_people']) : 0;
                $price_raw = isset($pricing_row['price_per_person']) ? str_replace(',', '', (string) $pricing_row['price_per_person']) : '';
                $price_per_person = is_numeric($price_raw) ? max(0.0, (float) $price_raw) : 0.0;

                if ($min_people <= 0 && $max_people <= 0 && $price_per_person <= 0) {
                    continue;
                }

                if ($min_people > 0 && $max_people > 0 && $max_people < $min_people) {
                    $max_people = $min_people;
                }

                $group_pricing_rows[] = array(
                    'min_people' => $min_people,
                    'max_people' => $max_people,
                    'price_per_person' => $price_per_person,
                );
            }
        }

        $participant_cap = max(1, $group);
        foreach ($group_pricing_rows as $pricing_row) {
            $participant_cap = max(
                $participant_cap,
                isset($pricing_row['min_people']) ? (int) $pricing_row['min_people'] : 0,
                isset($pricing_row['max_people']) ? (int) $pricing_row['max_people'] : 0
            );
        }
        if ($participant_cap <= 1 && !empty($group_pricing_rows)) {
            $participant_cap = 10;
        }

        $default_participants = 1;
        $initial_price_per_person = $price;
        foreach ($group_pricing_rows as $pricing_row) {
            $min_people = isset($pricing_row['min_people']) ? (int) $pricing_row['min_people'] : 0;
            $max_people = isset($pricing_row['max_people']) ? (int) $pricing_row['max_people'] : 0;
            $row_price = isset($pricing_row['price_per_person']) ? (float) $pricing_row['price_per_person'] : 0.0;
            $matches_default = $default_participants >= max(1, $min_people) && ($max_people <= 0 || $default_participants <= $max_people);

            if ($matches_default && $row_price > 0) {
                $initial_price_per_person = $row_price;
                break;
            }
        }
        if ($initial_price_per_person <= 0) {
            $initial_price_per_person = $price;
        }
        $initial_total_price = max(0.0, $default_participants * $initial_price_per_person);
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

        // Booking URL setup
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
        $today_date = wp_date('Y-m-d');

        // Resolve location label the same way tour cards do.
        $location_name = '';
        if (function_exists('aatf_get_first_meta_value')) {
            $location_name = aatf_get_first_meta_value($post_id, array(
                'trek_location',
                '_location',
                'location',
            ));
        } else {
            $location_name = trim((string) get_post_meta($post_id, 'trek_location', true));
        }

        if ($location_name === '') {
            $destination_ids = get_post_meta($post_id, 'trek_destination_ids', true);
            $destination_ids = is_array($destination_ids) ? array_values(array_filter(array_map('absint', $destination_ids))) : array();

            if (!empty($destination_ids)) {
                $destination_id = $destination_ids[0];
                $destination_title = trim((string) get_the_title($destination_id));
                $country = trim((string) get_post_meta($destination_id, 'destination_country', true));

                if ($destination_title !== '' && $country !== '') {
                    $location_name = $destination_title . ', ' . $country;
                } elseif ($destination_title !== '') {
                    $location_name = $destination_title;
                } else {
                    $location_name = $country;
                }
            }
        }

        $destinations = get_the_terms($post_id, 'destination');

        // Get tour type taxonomy if available
        $tour_types = get_the_terms($post_id, 'trek_type');
        $tour_type_name = 'Trekking';
        if ($tour_types && !is_wp_error($tour_types)) {
            $tour_type_name = $tour_types[0]->name;
        }
        $has_overview_section = !empty($overview) || get_the_content() !== '';
        $has_highlights_section = $highlights_title !== '' || $highlights_intro !== '' || !empty($highlights);
        $has_includes_section = !empty($cost_includes) || !empty($cost_excludes);
        $has_equipment_section = !empty($prepared_equipment_sections);
        $itinerary_markup = '';
        if (shortcode_exists('aatf_trek_itinerary')) {
            $itinerary_markup = trim((string) do_shortcode('[aatf_trek_itinerary trek_id="' . esc_attr((string) $post_id) . '"]'));
        }
        $has_itinerary_section = $itinerary_markup !== '';
        $has_altitude_section = !empty($altitude_profile);
        $has_map_section = $map_embed !== '';
        $has_video_section = $video_embed !== '';
        $has_departures_section = $departures->have_posts();
        $faq_markup = '';
        if (class_exists('AATF_Frontend_Components')) {
            $faq_markup = trim((string) do_shortcode('[aatf_trek_faq]'));
        }
        $has_faq_section = $faq_markup !== '';
        $secondary_sections = array();
        if ($has_overview_section) {
            $secondary_sections[] = array('id' => 'trek-overview', 'label' => 'Overview');
        }
        if ($has_highlights_section) {
            $secondary_sections[] = array('id' => 'trek-highlights', 'label' => 'Highlights');
        }
        if ($has_includes_section) {
            $secondary_sections[] = array('id' => 'trek-includes', 'label' => 'Includes & Excludes');
        }
        if ($has_equipment_section) {
            $secondary_sections[] = array('id' => 'trek-equipment', 'label' => 'Equipment');
        }
        if ($has_itinerary_section) {
            $secondary_sections[] = array('id' => 'trek-tour-plan', 'label' => 'Tour Plan');
        }
        if ($has_altitude_section) {
            $secondary_sections[] = array('id' => 'trek-altitude-profile', 'label' => 'Trip Graph');
        }
        if ($has_map_section) {
            $secondary_sections[] = array('id' => 'trek-location', 'label' => 'Trip Map');
        }
        if ($has_video_section) {
            $secondary_sections[] = array('id' => 'trek-video', 'label' => 'Video');
        }
        if ($has_departures_section) {
            $secondary_sections[] = array('id' => 'trek-departures', 'label' => 'Departures');
        }
        if ($has_faq_section) {
            $secondary_sections[] = array('id' => 'trek-faq', 'label' => 'FAQ');
        }
?>

        <?php if (!empty($slider_ids)) : ?>
            <div class="aatf-single-trek__gallery-wrap">
                <div class="aatf-single-trek__gallery">
                    <?php foreach (array_slice($slider_ids, 0, 4) as $index => $image_id) : ?>
                        <?php
                        echo wp_get_attachment_image($image_id, 'large', false, array(
                            'class' => 'aatf-single-trek__gallery-image',
                            'loading' => $index === 0 ? 'eager' : 'lazy',
                        ));
                        ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- ── Title bar ─────────────────────────────────────────────────── -->
        <div class="bg-gray-100 border-b border-gray-200 px-6 py-4">
            <div class="max-w-7xl mx-auto flex items-start justify-between flex-wrap gap-4">
                <div>
                    <h1 class="font-display text-2xl font-bold" style="color: var(--brand-dark);"><?php echo esc_html(get_the_title()); ?></h1>
                    <?php if ($location_name !== '') : ?>
                        <p class="flex items-center gap-1 text-sm mt-1" style="color: var(--brand-gray);">
                            <svg class="w-3.5 h-3.5" style="color: var(--brand-orange);" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                            </svg>
                            <?php echo esc_html($location_name); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <!-- Meta stats -->
                <div class="flex items-center gap-6 text-sm">
                    <?php if ($price > 0) : ?>
                        <div class="flex items-center gap-2">
                            <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="text-xs" style="color: var(--brand-gray);">From</p>
                                <p class="font-bold" style="color: var(--brand-dark);">$<?php echo esc_html(number_format_i18n($price, 2)); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($duration !== '') : ?>
                        <div class="flex items-center gap-2">
                            <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                            </svg>
                            <div>
                                <p class="text-xs" style="color: var(--brand-gray);">Duration</p>
                                <p class="font-bold" style="color: var(--brand-dark);"><?php echo esc_html($duration); ?> days</p>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="flex items-center gap-2">
                        <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        <div>
                            <p class="text-xs" style="color: var(--brand-gray);">Tour Type</p>
                            <p class="font-bold" style="color: var(--brand-dark);"><?php echo esc_html($tour_type_name); ?></p>
                        </div>
                    </div>
                    <?php if ($altitude !== '') : ?>
                        <div class="flex items-center gap-2">
                            <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 8-8" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 7h4v4" />
                            </svg>
                            <div>
                                <p class="text-xs" style="color: var(--brand-gray);">Max Altitude</p>
                                <p class="font-bold" style="color: var(--brand-dark);"><?php echo esc_html($altitude); ?>m</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ── Main content ───────────────────────────────────────────────── -->
        <div class="max-w-7xl mx-auto py-8 flex gap-8 items-start">

            <!-- ── Left column ────────────────────────────────────────────── -->
            <div class="flex-1 min-w-0">

                <!-- Rating + Share/Reviews row -->
                <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-200">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-0.5">
                            <?php for ($i = 0; $i < 5; $i++) : ?>
                                <svg class="w-4 h-4 <?php echo $i < 4 ? 'star-full' : 'star-empty'; ?>" fill="currentColor" viewBox="0 0 20 20" style="color: <?php echo $i < 4 ? 'var(--brand-orange)' : '#d1d5db'; ?>;">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            <?php endfor; ?>
                        </div>
                        <?php if ($reviews !== '') : ?>
                            <span class="text-sm" style="color: var(--brand-gray);"><?php echo esc_html($reviews); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="flex items-center gap-1.5 border border-gray-300 text-xs font-semibold px-3 py-1.5 rounded hover:bg-gray-50 transition-colors" style="color: var(--brand-gray);">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                            Share
                        </button>
                        <button class="flex items-center gap-1.5 border border-gray-300 text-xs font-semibold px-3 py-1.5 rounded hover:bg-gray-50 transition-colors" style="color: var(--brand-gray);">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                            Reviews
                        </button>
                    </div>
                </div>

                <?php if (!empty($secondary_sections)) : ?>
                    <nav class="aatf-single-trek__section-nav mb-8" aria-label="Trek page sections" data-trek-section-nav>
                        <div class="aatf-single-trek__section-nav-track">
                            <?php foreach ($secondary_sections as $index => $section_nav) : ?>
                                <a
                                    href="#<?php echo esc_attr($section_nav['id']); ?>"
                                    class="aatf-single-trek__section-link<?php echo $index === 0 ? ' is-active' : ''; ?>"
                                    data-section-link
                                    data-target="<?php echo esc_attr($section_nav['id']); ?>">
                                    <?php echo esc_html($section_nav['label']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </nav>
                <?php endif; ?>

                <!-- Overview -->
                <?php if ($has_overview_section) : ?>
                    <section class="mb-8 aatf-single-trek__content-section" id="trek-overview" data-section>
                        <h2 class="text-xl font-bold mb-3" style="color: var(--brand-dark);">Overview</h2>
                        <div class="aatf-single-trek__overview">
                        <?php if (get_the_content()) : ?>
                            <div class="aatf-single-trek__overview-content">
                                <?php the_content(); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($overview)) : ?>
                            <p class="aatf-single-trek__overview-summary">
                                <?php echo esc_html($overview); ?>
                            </p>
                        <?php endif; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Highlights -->
                <?php if ($has_highlights_section) : ?>
                    <section class="mb-8 aatf-single-trek__content-section" id="trek-highlights" data-section>
                        <h2 class="text-xl font-bold mb-3" style="color: var(--brand-dark);">
                            <?php echo esc_html($highlights_title !== '' ? $highlights_title : 'Trek Highlights'); ?>
                        </h2>
                        <?php if ($highlights_intro !== '') : ?>
                            <p class="text-sm leading-relaxed mb-3" style="color: var(--brand-gray);"><?php echo esc_html($highlights_intro); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($highlights)) : ?>
                            <ul class="space-y-2">
                                <?php foreach ($highlights as $item) : ?>
                                    <li class="flex items-center gap-2 text-sm" style="color: var(--brand-dark);">
                                        <svg class="w-4 h-4 shrink-0" style="color: var(--brand-orange);" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <?php echo esc_html($item); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

                <!-- Cost Includes/Excludes -->
                <?php if ($has_includes_section) : ?>
                    <section class="mb-8 aatf-single-trek__content-section" id="trek-includes" data-section>
                        <h2 class="text-xl font-bold mb-4" style="color: var(--brand-dark);">Included/Exclude</h2>
                        <div class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm">
                            <!-- Included -->
                            <div class="space-y-2">
                                <?php foreach ($cost_includes as $inc_item) : ?>
                                    <div class="flex items-center gap-2" style="color: var(--brand-dark);">
                                        <svg class="w-4 h-4 shrink-0 text-green-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <?php echo esc_html($inc_item); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <!-- Excluded -->
                            <div class="space-y-2">
                                <?php foreach ($cost_excludes as $exc_item) : ?>
                                    <div class="flex items-center gap-2 text-gray-400">
                                        <svg class="w-4 h-4 shrink-0 text-red-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        <?php echo esc_html($exc_item); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Equipment -->
                <?php if ($has_equipment_section) : ?>
                        <section class="aatf-single-trek__equipment mb-8 aatf-single-trek__content-section" id="trek-equipment" aria-labelledby="trek-equipment-heading" data-section>
                            <div class="aatf-single-trek__equipment-header mb-5">
                                <h2 id="trek-equipment-heading" class="aatf-single-trek__equipment-title text-xl font-bold" style="color: var(--brand-dark);">
                                    <span>Equipment</span>
                                </h2>
                                <p class="aatf-single-trek__equipment-intro text-sm" style="color: var(--brand-gray);">
                                    Pack according to the sections below so you can quickly see what is essential for the trek.
                                </p>
                            </div>
                            <div class="grid grid-cols-1 gap-6">
                                <?php foreach ($prepared_equipment_sections as $section) : ?>
                                    <div class="aatf-single-trek__equipment-card">
                                        <?php if ($section['heading'] !== '' || !empty($section['image_url'])) : ?>
                                            <div class="aatf-single-trek__equipment-card-head">
                                                <?php if (!empty($section['image_url'])) : ?>
                                                    <div class="aatf-single-trek__equipment-card-media">
                                                        <img
                                                            src="<?php echo esc_url($section['image_url']); ?>"
                                                            alt=""
                                                            class="aatf-single-trek__equipment-card-image"
                                                            loading="lazy" />
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($section['heading'] !== '') : ?>
                                                    <h4 class="aatf-single-trek__equipment-card-title text-sm font-bold uppercase tracking-wide" style="color: var(--brand-dark);">
                                                        <?php echo esc_html($section['heading']); ?>
                                                    </h4>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($section['items'])) : ?>
                                            <ul class="aatf-single-trek__equipment-list text-sm">
                                                <?php foreach ($section['items'] as $item) : ?>
                                                    <li class="aatf-single-trek__equipment-item">
                                                        <span class="aatf-single-trek__equipment-bullet" aria-hidden="true">
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 12h8" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7l5 5-5 5" />
                                                            </svg>
                                                        </span>
                                                        <span><?php echo esc_html($item); ?></span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                <?php endif; ?>

                <!-- Itinerary / Tour Plan accordion -->
                <?php if ($has_itinerary_section) : ?>
                    <section class="mb-8 aatf-single-trek__content-section" id="trek-tour-plan" data-section>
                        <?php echo $itinerary_markup; ?>
                    </section>
                <?php endif; ?>

                <!-- Altitude Profile -->
                <?php if ($has_altitude_section) : ?>
                    <section class="mb-8 aatf-altitude-profile aatf-single-trek__content-section" id="trek-altitude-profile" data-aatf-altitude-profile data-section>
                        <div class="aatf-altitude-profile__intro mb-4">
                            <div>
                                <h2 class="text-xl font-bold mb-1" style="color: var(--brand-dark);">Altitude Profile</h2>
                                <p class="aatf-altitude-profile__subtitle text-sm" style="color: var(--brand-gray);">
                                    Follow the elevation changes day by day, including the highest point, lowest point, and key overnight stops.
                                </p>
                            </div>
                            <div class="aatf-altitude-profile__head flex items-center gap-3">
                                <span class="aatf-altitude-profile__label text-sm" style="color: var(--brand-gray);">Altitude in:</span>
                                <div class="aatf-altitude-profile__units flex gap-2" role="group" aria-label="Altitude unit">
                                    <button type="button" class="aatf-altitude-profile__unit is-active text-xs px-3 py-1 rounded font-semibold border border-gray-200" data-unit="m" aria-pressed="true" style="background: rgba(232,130,12,0.1); color: var(--brand-orange);">Meter</button>
                                    <button type="button" class="aatf-altitude-profile__unit text-xs px-3 py-1 rounded font-semibold border border-gray-200 bg-white" data-unit="ft" aria-pressed="false" style="color: var(--brand-gray);">Feet</button>
                                </div>
                            </div>
                        </div>
                        <div class="aatf-altitude-profile__stats" data-altitude-stats></div>
                        <div class="aatf-altitude-profile__chart rounded-lg border border-gray-200 bg-white p-4" data-altitude-chart></div>
                        <div class="aatf-altitude-profile__scroll flex items-center gap-2 mt-3" data-altitude-scroll>
                            <button type="button" class="aatf-altitude-profile__scroll-btn text-gray-400 hover:text-gray-600" data-scroll-dir="left" aria-label="Scroll left">&#8592;</button>
                            <input type="range" class="aatf-altitude-profile__scroll-range flex-1 accent-orange-500" min="0" max="100" value="0" step="1" aria-label="Scroll altitude profile" />
                            <button type="button" class="aatf-altitude-profile__scroll-btn text-gray-400 hover:text-gray-600" data-scroll-dir="right" aria-label="Scroll right">&#8594;</button>
                        </div>
                        <?php
                        $altitude_profile_json = wp_json_encode(
                            $altitude_profile,
                            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
                        );
                        ?>
                        <script type="application/json" class="aatf-altitude-profile__data">
                            <?php echo $altitude_profile_json !== false ? $altitude_profile_json : '[]'; ?>
                        </script>
                    </section>
                <?php endif; ?>

                <!-- Map / Location -->
                <?php if ($has_map_section) : ?>
                    <section class="mb-8 aatf-single-trek__content-section" id="trek-location" data-section>
                        <h2 class="text-xl font-bold mb-1" style="color: var(--brand-dark);">Location</h2>
                        <h3 class="text-base font-semibold mb-4" style="color: var(--brand-gray);">Find closest meeting point</h3>

                        <?php
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

                        if (stripos($map_embed, '<iframe') !== false) : ?>
                            <div class="rounded-xl overflow-hidden border border-gray-200 bg-gray-100 h-[450px] relative [&>iframe]:w-full [&>iframe]:h-full [&>iframe]:border-0">
                                <?php echo wp_kses($map_embed, $allowed_map); ?>
                            </div>
                            <?php else :
                            $map_url = esc_url($map_embed);
                            if ($map_url !== '') : ?>
                                <div class="rounded-xl overflow-hidden border border-gray-200 bg-gray-100 h-[450px] relative flex items-center justify-center">
                                    <div class="text-center">
                                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                        </svg>
                                        <p class="text-sm text-gray-400"><a href="<?php echo $map_url; ?>" target="_blank" rel="noopener" class="hover:text-[var(--brand-orange)] transition-colors">Open Map →</a></p>
                                    </div>
                                </div>
                        <?php endif;
                        endif; ?>
                    </section>
                <?php endif; ?>

                <!-- Video -->
                <?php if ($has_video_section) :
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
                ?>
                    <section class="mb-8 aatf-single-trek__content-section" id="trek-video" data-section>
                        <h2 class="text-xl font-bold mb-4" style="color: var(--brand-dark);">Trek Video</h2>
                        <div class="rounded-xl overflow-hidden border border-gray-200 aspect-video [&>iframe]:w-full [&>iframe]:h-full [&>iframe]:border-0">
                            <?php echo wp_kses($video_embed, $allowed_iframe); ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Departures -->
                <?php if ($has_departures_section) :
                    $status_labels = array(
                        'available' => 'Available',
                        'limited' => 'Limited',
                        'guaranteed' => 'Guaranteed',
                        'full' => 'Full',
                    );
                ?>
                    <section class="mb-8 aatf-single-trek__content-section" id="trek-departures" data-section>
                        <h2 class="text-xl font-bold mb-4" style="color: var(--brand-dark);">Departures</h2>
                        <div class="space-y-3">
                            <?php while ($departures->have_posts()) :
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
                            ?>
                                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center justify-between flex-wrap gap-4">
                                    <div>
                                        <p class="font-bold text-sm" style="color: var(--brand-dark);"><?php echo esc_html($date_label); ?></p>
                                        <div class="flex items-center gap-3 mt-1.5">
                                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold" style="background: rgba(232,130,12,0.1); color: var(--brand-orange);"><?php echo esc_html($status_label); ?></span>
                                            <span class="text-xs font-semibold" style="color: var(--brand-dark);"><?php echo esc_html($price_label); ?></span>
                                        </div>
                                    </div>
                                    <a class="text-white text-xs font-bold tracking-widest uppercase px-5 py-2.5 rounded-lg hover:opacity-90 transition-opacity" style="background-color: var(--brand-orange);" href="<?php echo esc_url($departure_booking_url); ?>">Book Now</a>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </section>
                <?php wp_reset_postdata();
                endif; ?>

                <!-- FAQ -->
                <?php if ($has_faq_section) : ?>
                    <section class="mb-8 aatf-single-trek__content-section" id="trek-faq" data-section>
                        <?php echo $faq_markup; ?>
                    </section>
                <?php endif; ?>

                <!-- Related Tours -->
                <?php
                $related_args = array(
                    'post_type' => 'trek',
                    'post_status' => 'publish',
                    'posts_per_page' => 2,
                    'post__not_in' => array($post_id),
                    'orderby' => 'rand',
                );

                if ($destinations && !is_wp_error($destinations)) {
                    $related_args['tax_query'] = array(
                        array(
                            'taxonomy' => 'destination',
                            'field' => 'term_id',
                            'terms' => $destinations[0]->term_id,
                        ),
                    );
                }

                $related_treks = new WP_Query($related_args);

                if ($related_treks->have_posts()) :
                ?>
                    <div class="">
                        <h2 class="text-xl font-bold mb-5" style="color: var(--brand-dark);">Related Tours</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php while ($related_treks->have_posts()) : $related_treks->the_post();
                                $rel_id = get_the_ID();
                                $rel_title = get_the_title();
                                $rel_url = get_permalink();
                                $rel_price = (float) get_post_meta($rel_id, 'trek_price', true);
                                $rel_duration = get_post_meta($rel_id, 'trek_duration', true);
                                $rel_group = (int) get_post_meta($rel_id, 'trek_group_size', true);
                                $rel_thumb = get_the_post_thumbnail_url($rel_id, 'large');
                                if (!$rel_thumb) $rel_thumb = 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=500&q=80';
                                $rel_dest = '';
                                $rel_dests = get_the_terms($rel_id, 'destination');
                                if ($rel_dests && !is_wp_error($rel_dests)) {
                                    $rel_dest = $rel_dests[0]->name;
                                }
                            ?>
                                <div class="tour-card bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100 flex flex-col">
                                    <div class="relative">
                                        <a href="<?php echo esc_url($rel_url); ?>">
                                            <img src="<?php echo esc_url($rel_thumb); ?>" alt="<?php echo esc_attr($rel_title); ?>" class="w-full h-52 object-cover object-center" />
                                        </a>
                                        <button class="absolute top-3 right-3 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow hover:scale-110 transition-transform" aria-label="Favorite">
                                            <svg class="w-4 h-4 text-[var(--brand-gray)] hover:text-red-500 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="p-4 flex-1 flex flex-col">
                                        <h3 class="font-bold text-[var(--brand-dark)] text-base leading-snug mb-1">
                                            <a href="<?php echo esc_url($rel_url); ?>" class="hover:text-[var(--brand-orange)] transition-colors"><?php echo esc_html($rel_title); ?></a>
                                        </h3>
                                        <div class="flex items-center gap-1 whitespace-nowrap text-xs text-[var(--brand-gray)] mb-3">
                                            <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                            </svg><?php echo esc_html($rel_dest !== '' ? $rel_dest : 'Nepal'); ?>
                                        </div>
                                        <?php if ($rel_price > 0) : ?>
                                            <p class="text-[var(--brand-gray)] text-sm mb-3">From <span class="text-[var(--brand-orange)] font-extrabold text-lg">$<?php echo esc_html(number_format_i18n($rel_price, 2)); ?></span></p>
                                        <?php endif; ?>

                                        <div class="mt-auto border-t border-gray-100 pt-3 flex items-center justify-between text-xs text-[var(--brand-gray)]">
                                            <div class="flex items-center gap-3">
                                                <?php if ($rel_duration) : ?>
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <circle cx="12" cy="12" r="10" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                                                        </svg>
                                                        <?php echo esc_html($rel_duration); ?> days
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($rel_group > 0) : ?>
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                        <?php echo esc_html($rel_group); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <a href="<?php echo esc_url($rel_url); ?>" class="text-[var(--brand-orange)] font-semibold hover:underline flex items-center gap-1">
                                                Explore
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile;
                            wp_reset_postdata(); ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div><!-- /left col -->

            <!-- ── Right column (sticky sidebar) ────────────────────────────── -->
            <div class="w-72 shrink-0 space-y-5 sticky top-4">

                <!-- Booking Tour card -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="font-bold text-base" style="color: var(--brand-dark);">Booking Tour</h3>
                    </div>
                    <div
                        class="px-5 py-4 space-y-4 text-sm"
                        data-booking-pricing
                        data-base-price="<?php echo esc_attr((string) $price); ?>"
                        data-group-pricing="<?php echo esc_attr(wp_json_encode($group_pricing_rows)); ?>">

                        <!-- From date -->
                        <div>
                            <label class="block text-xs font-semibold mb-1.5" style="color: var(--brand-dark);">From:</label>
                            <div class="flex items-center border border-gray-200 rounded-lg px-3 py-2.5 gap-2">
                                <input type="date" min="<?php echo esc_attr($today_date); ?>" class="flex-1 text-xs outline-none bg-transparent" style="color: var(--brand-gray);" />
                            </div>
                        </div>

                        <!-- Participants -->
                        <div>
                            <label class="block text-xs font-semibold mb-1.5" style="color: var(--brand-dark);">Participants:</label>
                            <div class="flex items-center border border-gray-200 rounded-lg px-3 py-2.5 gap-2">
                                <button
                                    type="button"
                                    class="w-7 h-7 rounded-full border border-gray-200 text-base leading-none flex items-center justify-center shrink-0"
                                    data-booking-decrement
                                    aria-label="Decrease participants"
                                    style="color: var(--brand-dark);">-</button>
                                <input
                                    type="number"
                                    min="1"
                                    max="<?php echo esc_attr((string) $participant_cap); ?>"
                                    step="1"
                                    value="<?php echo esc_attr((string) $default_participants); ?>"
                                    class="flex-1 text-center text-xs outline-none bg-transparent"
                                    data-booking-participants
                                    style="color: var(--brand-gray);" />
                                <button
                                    type="button"
                                    class="w-7 h-7 rounded-full border border-gray-200 text-base leading-none flex items-center justify-center shrink-0"
                                    data-booking-increment
                                    aria-label="Increase participants"
                                    style="color: var(--brand-dark);">+</button>
                            </div>
                        </div>

                        <!-- Price per person -->
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs font-semibold mb-1" style="color: var(--brand-gray);">Price per person</p>
                            <p class="font-bold text-lg" data-booking-price-per-person style="color: var(--brand-dark);">
                                $<?php echo esc_html(number_format_i18n($initial_price_per_person, 2)); ?>
                            </p>
                        </div>

                        <?php if (!empty($group_pricing_rows)) : ?>
                            <div class="space-y-3">
                                <button
                                    type="button"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-xs font-semibold text-left flex items-center justify-between hover:bg-gray-50 transition-colors"
                                    data-group-discount-toggle
                                    aria-expanded="false"
                                    style="color: var(--brand-dark);">
                                    <span>View Group Discount</span>
                                    <span class="text-base leading-none" data-group-discount-icon>+</span>
                                </button>
                                <div class="hidden border border-gray-100 rounded-lg overflow-hidden" data-group-discount-panel>
                                    <table class="w-full text-xs">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left font-semibold" style="color: var(--brand-dark);">Group Size</th>
                                                <th class="px-3 py-2 text-left font-semibold" style="color: var(--brand-dark);">Price / Person</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($group_pricing_rows as $pricing_row) : ?>
                                                <?php
                                                $min_people = isset($pricing_row['min_people']) ? (int) $pricing_row['min_people'] : 0;
                                                $max_people = isset($pricing_row['max_people']) ? (int) $pricing_row['max_people'] : 0;
                                                $row_price = isset($pricing_row['price_per_person']) ? (float) $pricing_row['price_per_person'] : 0.0;
                                                if ($min_people > 0 && $max_people > 0 && $min_people !== $max_people) {
                                                    $range_label = $min_people . ' - ' . $max_people;
                                                } elseif ($min_people > 0 && $max_people > 0) {
                                                    $range_label = (string) $min_people;
                                                } elseif ($min_people > 0) {
                                                    $range_label = $min_people . '+';
                                                } elseif ($max_people > 0) {
                                                    $range_label = 'Up to ' . $max_people;
                                                } else {
                                                    $range_label = 'Any size';
                                                }
                                                ?>
                                                <tr class="border-t border-gray-100">
                                                    <td class="px-3 py-2" style="color: var(--brand-gray);"><?php echo esc_html($range_label); ?> Travellers</td>
                                                    <td class="px-3 py-2 font-semibold" style="color: var(--brand-dark);">
                                                        <?php echo $row_price > 0 ? esc_html('$' . number_format_i18n($row_price, 2)) : 'Price on request'; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Total -->
                        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                            <span class="font-bold text-base" style="color: var(--brand-dark);">Total:</span>
                            <span class="font-bold text-lg" data-booking-total style="color: var(--brand-orange);">
                                $<?php echo esc_html(number_format_i18n($initial_total_price, 2)); ?>
                            </span>
                        </div>

                        <!-- Book Now button -->
                        <a href="<?php echo esc_url($booking_cta_url); ?>" class="w-full text-white font-bold text-sm tracking-widest uppercase py-3.5 rounded-lg hover:opacity-90 transition-opacity flex items-center justify-center gap-2 no-underline" style="background-color: var(--brand-orange);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Book Now
                        </a>

                    </div>
                </div>

                <!-- Last Minute Deals -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="font-bold text-base" style="color: var(--brand-dark);">Last Minute Deals</h3>
                    </div>
                    <div class="divide-y divide-gray-100">

                        <div class="flex gap-3 p-4 hover:bg-gray-50 transition-colors cursor-pointer">
                            <img src="https://images.unsplash.com/photo-1514282401047-d79a71a590e8?w=200&q=80" alt="Java Bali" class="w-16 h-14 object-cover rounded-lg shrink-0" />
                            <div>
                                <p class="text-xs font-semibold leading-snug mb-1" style="color: var(--brand-dark);">Java &amp; Bali One Life...</p>
                                <div class="flex gap-0.5 mb-1" style="color: var(--brand-orange);">
                                    <?php for ($s = 0; $s < 5; $s++) : ?>
                                        <svg class="w-2.5 h-2.5 <?php echo $s >= 4 ? 'text-gray-300' : ''; ?>" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-xs">From <span class="font-bold" style="color: var(--brand-orange);">$39.00</span></p>
                            </div>
                        </div>

                        <div class="flex gap-3 p-4 hover:bg-gray-50 transition-colors cursor-pointer">
                            <img src="https://images.unsplash.com/photo-1508009603885-50cf7c579365?w=200&q=80" alt="Sri Lanka" class="w-16 h-14 object-cover rounded-lg shrink-0" />
                            <div>
                                <p class="text-xs font-semibold leading-snug mb-1" style="color: var(--brand-dark);">Sri Lanka One Life...</p>
                                <div class="flex gap-0.5 mb-1" style="color: var(--brand-orange);">
                                    <?php for ($s = 0; $s < 5; $s++) : ?>
                                        <svg class="w-2.5 h-2.5 <?php echo $s >= 4 ? 'text-gray-300' : ''; ?>" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-xs">From <span class="font-bold" style="color: var(--brand-orange);">$39.00</span></p>
                            </div>
                        </div>

                        <div class="flex gap-3 p-4 hover:bg-gray-50 transition-colors cursor-pointer">
                            <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=200&q=80" alt="North Island" class="w-16 h-14 object-cover rounded-lg shrink-0" />
                            <div>
                                <p class="text-xs font-semibold leading-snug mb-1" style="color: var(--brand-dark);">North Island Adventure...</p>
                                <div class="flex gap-0.5 mb-1" style="color: var(--brand-orange);">
                                    <?php for ($s = 0; $s < 5; $s++) : ?>
                                        <svg class="w-2.5 h-2.5 <?php echo $s >= 4 ? 'text-gray-300' : ''; ?>" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-xs">From <span class="font-bold" style="color: var(--brand-orange);">$39.00</span></p>
                            </div>
                        </div>

                        <div class="flex gap-3 p-4 hover:bg-gray-50 transition-colors cursor-pointer">
                            <img src="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=200&q=80" alt="Sicily" class="w-16 h-14 object-cover rounded-lg shrink-0" />
                            <div>
                                <p class="text-xs font-semibold leading-snug mb-1" style="color: var(--brand-dark);">Small Group Sicily Food...</p>
                                <div class="flex gap-0.5 mb-1" style="color: var(--brand-orange);">
                                    <?php for ($s = 0; $s < 5; $s++) : ?>
                                        <svg class="w-2.5 h-2.5 <?php echo $s >= 4 ? 'text-gray-300' : ''; ?>" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-xs">From <span class="font-bold" style="color: var(--brand-orange);">$39.00</span></p>
                            </div>
                        </div>

                    </div>
                </div>

            </div><!-- /right col -->
        </div><!-- /main flex -->

        <!-- Itinerary Accordion JS -->
        <script>
            (function() {
                function formatPrice(amount) {
                    var numeric = Number(amount) || 0;
                    return '$' + numeric.toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }

                function formatAltitudeValue(value, unit) {
                    var numeric = Number(value) || 0;
                    var rounded = Math.round(numeric);
                    return rounded.toLocaleString() + ' ' + unit;
                }

                function createSvgNode(tagName, attributes) {
                    var node = document.createElementNS('http://www.w3.org/2000/svg', tagName);
                    Object.keys(attributes || {}).forEach(function(key) {
                        if (attributes[key] !== undefined && attributes[key] !== null) {
                            node.setAttribute(key, String(attributes[key]));
                        }
                    });
                    return node;
                }

                function setScrollState(scroller, scrollWrap, rangeInput) {
                    if (!scroller || !scrollWrap || !rangeInput) {
                        return;
                    }

                    var maxScroll = Math.max(0, scroller.scrollWidth - scroller.clientWidth);
                    var hasOverflow = maxScroll > 4;
                    scrollWrap.style.display = hasOverflow ? 'flex' : 'none';

                    if (!hasOverflow) {
                        rangeInput.value = '0';
                        return;
                    }

                    var progress = maxScroll > 0 ? (scroller.scrollLeft / maxScroll) * 100 : 0;
                    rangeInput.value = String(Math.max(0, Math.min(100, Math.round(progress))));
                }

                function renderAltitudeProfile(profileRoot) {
                    if (!profileRoot) {
                        return;
                    }

                    var dataNode = profileRoot.querySelector('.aatf-altitude-profile__data');
                    var chartNode = profileRoot.querySelector('[data-altitude-chart]');
                    var statsNode = profileRoot.querySelector('[data-altitude-stats]');
                    var scrollWrap = profileRoot.querySelector('[data-altitude-scroll]');
                    var rangeInput = profileRoot.querySelector('.aatf-altitude-profile__scroll-range');
                    var unitButtons = profileRoot.querySelectorAll('[data-unit]');
                    var currentUnit = 'm';
                    var rows = [];

                    if (!dataNode || !chartNode) {
                        return;
                    }

                    try {
                        rows = JSON.parse(dataNode.textContent || '[]');
                    } catch (error) {
                        rows = [];
                    }

                    rows = Array.isArray(rows) ? rows.filter(function(item) {
                        return item && (item.day || item.place || Number(item.altitude_m) > 0);
                    }) : [];

                    if (!rows.length) {
                        return;
                    }

                    function convertAltitude(value, unit) {
                        var meters = Number(value) || 0;
                        return unit === 'ft' ? meters * 3.28084 : meters;
                    }

                    function summarizeRows(unit) {
                        var altitudes = rows.map(function(row) {
                            return convertAltitude(row.altitude_m, unit);
                        });
                        var maxValue = Math.max.apply(null, altitudes);
                        var minValue = Math.min.apply(null, altitudes);
                        var maxIndex = altitudes.indexOf(maxValue);
                        var minIndex = altitudes.indexOf(minValue);
                        var totalGain = 0;
                        var totalLoss = 0;

                        for (var i = 1; i < altitudes.length; i += 1) {
                            var difference = altitudes[i] - altitudes[i - 1];
                            if (difference > 0) {
                                totalGain += difference;
                            } else {
                                totalLoss += Math.abs(difference);
                            }
                        }

                        return {
                            maxValue: maxValue,
                            minValue: minValue,
                            maxIndex: maxIndex,
                            minIndex: minIndex,
                            totalGain: totalGain,
                            totalLoss: totalLoss,
                            days: rows.length
                        };
                    }

                    function renderStats(unit, summary) {
                        if (!statsNode) {
                            return;
                        }

                        var peakRow = rows[summary.maxIndex] || {};
                        var lowRow = rows[summary.minIndex] || {};
                        var cards = [{
                            label: 'Highest Point',
                            value: formatAltitudeValue(summary.maxValue, unit),
                            note: (peakRow.day ? peakRow.day + ' • ' : '') + (peakRow.place || 'Route high point')
                        }, {
                            label: 'Lowest Point',
                            value: formatAltitudeValue(summary.minValue, unit),
                            note: (lowRow.day ? lowRow.day + ' • ' : '') + (lowRow.place || 'Route low point')
                        }, {
                            label: 'Total Ascent',
                            value: formatAltitudeValue(summary.totalGain, unit),
                            note: 'Cumulative climb across the route'
                        }, {
                            label: 'Total Descent',
                            value: formatAltitudeValue(summary.totalLoss, unit),
                            note: summary.days === 1 ? '1 mapped stage in profile' : summary.days + ' mapped stages in profile'
                        }];

                        statsNode.innerHTML = cards.map(function(card) {
                            return '<div class="aatf-altitude-profile__stat">' +
                                '<span class="aatf-altitude-profile__stat-label">' + card.label + '</span>' +
                                '<span class="aatf-altitude-profile__stat-value">' + card.value + '</span>' +
                                '<span class="aatf-altitude-profile__stat-note">' + card.note + '</span>' +
                                '</div>';
                        }).join('');
                    }

                    function renderChart(unit) {
                        var summary = summarizeRows(unit);
                        var altitudes = rows.map(function(row) {
                            return convertAltitude(row.altitude_m, unit);
                        });
                        var minValue = Math.min(summary.minValue, 0);
                        var maxValue = summary.maxValue;
                        var padding = Math.max(120, (maxValue - minValue) * 0.14);
                        var scaleMin = Math.max(0, minValue - padding * 0.35);
                        var scaleMax = maxValue + padding;
                        var valueRange = Math.max(1, scaleMax - scaleMin);
                        var pointSpacing = rows.length > 8 ? 118 : 104;
                        var chartWidth = Math.max(760, 140 + ((rows.length - 1) * pointSpacing));
                        var chartHeight = 360;
                        var plotLeft = 78;
                        var plotRight = 42;
                        var plotTop = 30;
                        var plotBottom = 84;
                        var plotWidth = chartWidth - plotLeft - plotRight;
                        var plotHeight = chartHeight - plotTop - plotBottom;
                        var yTicks = 5;
                        var xStep = rows.length > 1 ? plotWidth / (rows.length - 1) : 0;
                        var footerText = rows.length + (rows.length === 1 ? ' stage mapped. ' : ' stages mapped. ') + 'Elevation values are approximate and shown to help compare daily climbs, descents, and overnight stops.';

                        renderStats(unit, summary);

                        chartNode.innerHTML = '';

                        var top = document.createElement('div');
                        top.className = 'aatf-altitude-profile__chart-top';
                        top.innerHTML = '<p class="aatf-altitude-profile__chart-title">Day-by-day elevation trend</p>' +
                            '<div class="aatf-altitude-profile__legend">' +
                            '<span class="aatf-altitude-profile__legend-item"><span class="aatf-altitude-profile__legend-swatch aatf-altitude-profile__legend-swatch--line"></span>Elevation line</span>' +
                            '<span class="aatf-altitude-profile__legend-item"><span class="aatf-altitude-profile__legend-swatch aatf-altitude-profile__legend-swatch--bar"></span>Daily altitude band</span>' +
                            '<span class="aatf-altitude-profile__legend-item"><span class="aatf-altitude-profile__legend-swatch aatf-altitude-profile__legend-swatch--peak"></span>Highest point</span>' +
                            '<span class="aatf-altitude-profile__legend-item"><span class="aatf-altitude-profile__legend-swatch aatf-altitude-profile__legend-swatch--low"></span>Lowest point</span>' +
                            '</div>';

                        var frame = document.createElement('div');
                        frame.className = 'aatf-altitude-profile__chart-frame';

                        var scroller = document.createElement('div');
                        scroller.className = 'aatf-altitude-profile__scroller';

                        var svg = createSvgNode('svg', {
                            'class': 'aatf-altitude-profile__svg',
                            viewBox: '0 0 ' + chartWidth + ' ' + chartHeight,
                            role: 'img',
                            'aria-label': 'Altitude profile chart'
                        });

                        var defs = createSvgNode('defs');
                        var areaGradient = createSvgNode('linearGradient', {
                            id: 'aatfAltitudeAreaGradient',
                            x1: '0',
                            y1: '0',
                            x2: '0',
                            y2: '1'
                        });
                        areaGradient.appendChild(createSvgNode('stop', {
                            offset: '0%',
                            'stop-color': '#f3b04f',
                            'stop-opacity': '0.42'
                        }));
                        areaGradient.appendChild(createSvgNode('stop', {
                            offset: '100%',
                            'stop-color': '#f3b04f',
                            'stop-opacity': '0.06'
                        }));
                        defs.appendChild(areaGradient);
                        svg.appendChild(defs);

                        function getX(index) {
                            return plotLeft + (xStep * index);
                        }

                        function getY(value) {
                            return plotTop + ((scaleMax - value) / valueRange) * plotHeight;
                        }

                        for (var tick = 0; tick <= yTicks; tick += 1) {
                            var value = scaleMin + (valueRange / yTicks) * tick;
                            var y = getY(value);
                            svg.appendChild(createSvgNode('line', {
                                x1: plotLeft,
                                y1: y,
                                x2: chartWidth - plotRight,
                                y2: y,
                                'class': 'aatf-altitude-profile__grid-line'
                            }));
                            svg.appendChild(createSvgNode('text', {
                                x: plotLeft - 12,
                                y: y + 4,
                                'text-anchor': 'end',
                                'class': 'aatf-altitude-profile__axis-label'
                            })).textContent = Math.round(value).toLocaleString() + ' ' + unit;
                        }

                        svg.appendChild(createSvgNode('line', {
                            x1: plotLeft,
                            y1: plotTop,
                            x2: plotLeft,
                            y2: chartHeight - plotBottom,
                            'class': 'aatf-altitude-profile__axis-line'
                        }));
                        svg.appendChild(createSvgNode('line', {
                            x1: plotLeft,
                            y1: chartHeight - plotBottom,
                            x2: chartWidth - plotRight,
                            y2: chartHeight - plotBottom,
                            'class': 'aatf-altitude-profile__axis-line'
                        }));

                        svg.appendChild(createSvgNode('text', {
                            x: 24,
                            y: plotTop - 8,
                            'class': 'aatf-altitude-profile__axis-label'
                        })).textContent = 'Altitude';

                        svg.appendChild(createSvgNode('text', {
                            x: chartWidth - plotRight,
                            y: chartHeight - 18,
                            'text-anchor': 'end',
                            'class': 'aatf-altitude-profile__axis-label'
                        })).textContent = 'Trek stages';

                        for (var band = 0; band < yTicks; band += 1) {
                            var bandValue = scaleMin + (valueRange / yTicks) * band;
                            if (bandValue >= 5000) {
                                svg.appendChild(createSvgNode('text', {
                                    x: chartWidth - plotRight - 6,
                                    y: getY(bandValue) - 6,
                                    'text-anchor': 'end',
                                    'class': 'aatf-altitude-profile__band-label'
                                })).textContent = 'High altitude zone';
                                break;
                            }
                        }

                        var linePoints = [];
                        var areaPoints = [];

                        rows.forEach(function(row, index) {
                            var altitudeValue = altitudes[index];
                            var x = getX(index);
                            var y = getY(altitudeValue);
                            var baseY = chartHeight - plotBottom;
                            var columnWidth = Math.min(44, Math.max(28, xStep * 0.48 || 36));
                            var column = createSvgNode('rect', {
                                x: x - (columnWidth / 2),
                                y: y,
                                width: columnWidth,
                                height: Math.max(0, baseY - y),
                                rx: 12,
                                'class': 'aatf-altitude-profile__column' + (index === summary.maxIndex ? ' is-peak' : '') + (index === summary.minIndex ? ' is-low' : '')
                            });

                            svg.appendChild(column);
                            linePoints.push(x + ',' + y);
                            areaPoints.push(x + ',' + y);

                            svg.appendChild(createSvgNode('text', {
                                x: x,
                                y: y - 14,
                                'text-anchor': 'middle',
                                'class': 'aatf-altitude-profile__value-label'
                            })).textContent = Math.round(altitudeValue).toLocaleString() + ' ' + unit;

                            var dot = createSvgNode('circle', {
                                cx: x,
                                cy: y,
                                r: 6,
                                'class': 'aatf-altitude-profile__dot' + (index === summary.maxIndex ? ' is-peak' : '') + (index === summary.minIndex ? ' is-low' : '')
                            });
                            svg.appendChild(dot);

                            svg.appendChild(createSvgNode('text', {
                                x: x,
                                y: chartHeight - 42,
                                'text-anchor': 'middle',
                                'class': 'aatf-altitude-profile__day-label'
                            })).textContent = row.day || 'Stage ' + (index + 1);

                            svg.appendChild(createSvgNode('text', {
                                x: x,
                                y: chartHeight - 24,
                                'text-anchor': 'middle',
                                'class': 'aatf-altitude-profile__place-label'
                            })).textContent = row.place || '';
                        });

                        areaPoints.push(getX(rows.length - 1) + ',' + (chartHeight - plotBottom));
                        areaPoints.push(getX(0) + ',' + (chartHeight - plotBottom));

                        svg.appendChild(createSvgNode('polygon', {
                            points: areaPoints.join(' '),
                            'class': 'aatf-altitude-profile__area'
                        }));
                        svg.appendChild(createSvgNode('polyline', {
                            points: linePoints.join(' '),
                            'class': 'aatf-altitude-profile__line'
                        }));

                        Array.prototype.slice.call(svg.querySelectorAll('.aatf-altitude-profile__dot, .aatf-altitude-profile__value-label, .aatf-altitude-profile__day-label, .aatf-altitude-profile__place-label')).forEach(function(node) {
                            svg.appendChild(node);
                        });

                        scroller.appendChild(svg);
                        frame.appendChild(scroller);

                        var footer = document.createElement('div');
                        footer.className = 'aatf-altitude-profile__footer';
                        footer.innerHTML = '<p class="aatf-altitude-profile__footnote">' + footerText + '</p>';
                        frame.appendChild(footer);

                        chartNode.appendChild(top);
                        chartNode.appendChild(frame);

                        if (rangeInput) {
                            rangeInput.oninput = function() {
                                var maxScroll = Math.max(0, scroller.scrollWidth - scroller.clientWidth);
                                scroller.scrollLeft = (maxScroll * (parseInt(rangeInput.value, 10) || 0)) / 100;
                            };
                        }

                        scroller.onscroll = function() {
                            setScrollState(scroller, scrollWrap, rangeInput);
                        };

                        if (scrollWrap) {
                            scrollWrap.querySelectorAll('[data-scroll-dir]').forEach(function(button) {
                                button.onclick = function() {
                                    var direction = button.getAttribute('data-scroll-dir') === 'left' ? -1 : 1;
                                    scroller.scrollBy({
                                        left: direction * Math.max(220, xStep || 220),
                                        behavior: 'smooth'
                                    });
                                };
                            });
                        }

                        setScrollState(scroller, scrollWrap, rangeInput);
                    }

                    unitButtons.forEach(function(button) {
                        button.addEventListener('click', function() {
                            currentUnit = button.getAttribute('data-unit') === 'ft' ? 'ft' : 'm';
                            unitButtons.forEach(function(unitButton) {
                                var active = unitButton === button;
                                unitButton.classList.toggle('is-active', active);
                                unitButton.setAttribute('aria-pressed', active ? 'true' : 'false');
                                unitButton.style.background = active ? 'rgba(232,130,12,0.1)' : '#ffffff';
                                unitButton.style.color = active ? 'var(--brand-orange)' : 'var(--brand-gray)';
                            });
                            renderChart(currentUnit);
                        });
                    });

                    renderChart(currentUnit);

                    window.addEventListener('resize', function() {
                        var activeButton = profileRoot.querySelector('[data-unit].is-active');
                        currentUnit = activeButton && activeButton.getAttribute('data-unit') === 'ft' ? 'ft' : 'm';
                        var scroller = profileRoot.querySelector('.aatf-altitude-profile__scroller');
                        setScrollState(scroller, scrollWrap, rangeInput);
                    });
                }

                document.querySelectorAll('[data-booking-pricing]').forEach(function(card) {
                    var basePrice = parseFloat(card.getAttribute('data-base-price') || '0') || 0;
                    var participantsField = card.querySelector('[data-booking-participants]');
                    var decrementBtn = card.querySelector('[data-booking-decrement]');
                    var incrementBtn = card.querySelector('[data-booking-increment]');
                    var pricePerPersonEl = card.querySelector('[data-booking-price-per-person]');
                    var totalEl = card.querySelector('[data-booking-total]');
                    var toggleBtn = card.querySelector('[data-group-discount-toggle]');
                    var togglePanel = card.querySelector('[data-group-discount-panel]');
                    var toggleIcon = card.querySelector('[data-group-discount-icon]');
                    var pricingRows = [];

                    try {
                        pricingRows = JSON.parse(card.getAttribute('data-group-pricing') || '[]');
                    } catch (error) {
                        pricingRows = [];
                    }

                    function getPriceForParticipants(count) {
                        var participants = Math.max(1, parseInt(count, 10) || 1);

                        for (var i = 0; i < pricingRows.length; i += 1) {
                            var row = pricingRows[i] || {};
                            var minPeople = parseInt(row.min_people, 10) || 0;
                            var maxPeople = parseInt(row.max_people, 10) || 0;
                            var rowPrice = parseFloat(row.price_per_person) || 0;
                            var minMatch = participants >= Math.max(1, minPeople);
                            var maxMatch = maxPeople <= 0 || participants <= maxPeople;

                            if (minMatch && maxMatch && rowPrice > 0) {
                                return rowPrice;
                            }
                        }

                        return basePrice;
                    }

                    function normalizeParticipantsValue(nextValue) {
                        var min = parseInt(participantsField && participantsField.getAttribute('min'), 10) || 1;
                        var max = parseInt(participantsField && participantsField.getAttribute('max'), 10) || min;
                        var participants = parseInt(nextValue, 10);

                        if (!Number.isFinite(participants)) {
                            participants = min;
                        }

                        return Math.min(max, Math.max(min, participants));
                    }

                    function renderPricing() {
                        if (!participantsField || !pricePerPersonEl || !totalEl) {
                            return;
                        }

                        var participants = normalizeParticipantsValue(participantsField.value);
                        participantsField.value = String(participants);
                        var pricePerPerson = getPriceForParticipants(participants);
                        var totalPrice = participants * pricePerPerson;

                        pricePerPersonEl.textContent = formatPrice(pricePerPerson);
                        totalEl.textContent = formatPrice(totalPrice);
                    }

                    if (participantsField) {
                        participantsField.addEventListener('input', renderPricing);
                        participantsField.addEventListener('change', renderPricing);
                    }

                    if (decrementBtn && participantsField) {
                        decrementBtn.addEventListener('click', function() {
                            participantsField.value = String(normalizeParticipantsValue((parseInt(participantsField.value, 10) || 1) - 1));
                            renderPricing();
                        });
                    }

                    if (incrementBtn && participantsField) {
                        incrementBtn.addEventListener('click', function() {
                            participantsField.value = String(normalizeParticipantsValue((parseInt(participantsField.value, 10) || 1) + 1));
                            renderPricing();
                        });
                    }

                    if (toggleBtn && togglePanel) {
                        toggleBtn.addEventListener('click', function() {
                            var isExpanded = toggleBtn.getAttribute('aria-expanded') === 'true';
                            toggleBtn.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
                            togglePanel.classList.toggle('hidden', isExpanded);
                            if (toggleIcon) {
                                toggleIcon.textContent = isExpanded ? '+' : '-';
                            }
                        });
                    }

                    renderPricing();
                });

                document.querySelectorAll('[data-aatf-altitude-profile]').forEach(function(profileRoot) {
                    renderAltitudeProfile(profileRoot);
                });

                document.querySelectorAll('[data-trek-section-nav]').forEach(function(nav) {
                    var links = Array.prototype.slice.call(nav.querySelectorAll('[data-section-link]'));
                    var sectionIds = links.map(function(link) {
                        return link.getAttribute('data-target') || '';
                    }).filter(Boolean);
                    var sections = sectionIds.map(function(id) {
                        return document.getElementById(id);
                    }).filter(Boolean);
                    var pageBody = document.body;

                    if (!links.length || !sections.length) {
                        return;
                    }

                    function setActiveSection(sectionId) {
                        links.forEach(function(link) {
                            var isActive = link.getAttribute('data-target') === sectionId;
                            link.classList.toggle('is-active', isActive);
                        });

                        var activeLink = nav.querySelector('.aatf-single-trek__section-link.is-active');
                        if (activeLink && typeof activeLink.scrollIntoView === 'function') {
                            activeLink.scrollIntoView({
                                behavior: 'smooth',
                                block: 'nearest',
                                inline: 'center'
                            });
                        }
                    }

                    function syncActiveOnScroll() {
                        var triggerOffset = 180;
                        var activeSectionId = sections[0].id;

                        sections.forEach(function(section) {
                            var rect = section.getBoundingClientRect();
                            if (rect.top - triggerOffset <= 0) {
                                activeSectionId = section.id;
                            }
                        });

                        setActiveSection(activeSectionId);
                    }

                    if ('IntersectionObserver' in window) {
                        var observer = new IntersectionObserver(function() {
                            syncActiveOnScroll();
                        }, {
                            rootMargin: '-22% 0px -58% 0px',
                            threshold: [0, 0.15, 0.35, 0.6]
                        });

                        sections.forEach(function(section) {
                            observer.observe(section);
                        });
                    }

                    document.addEventListener('scroll', syncActiveOnScroll, {
                        passive: true
                    });
                    syncActiveOnScroll();

                    links.forEach(function(link) {
                        link.addEventListener('click', function() {
                            setActiveSection(link.getAttribute('data-target') || '');
                        });
                    });

                    function syncHeaderTakeover() {
                        if (!pageBody) {
                            return;
                        }

                        var navRect = nav.getBoundingClientRect();
                        var shouldTakeOver = navRect.top <= 0;
                        pageBody.classList.toggle('aatf-section-nav-takeover', shouldTakeOver);
                    }

                    if ('IntersectionObserver' in window) {
                        var navObserver = new IntersectionObserver(function() {
                            syncHeaderTakeover();
                        }, {
                            rootMargin: '0px 0px 0px 0px',
                            threshold: [0, 1]
                        });

                        navObserver.observe(nav);
                    }

                    window.addEventListener('scroll', syncHeaderTakeover, {
                        passive: true
                    });
                    window.addEventListener('resize', syncHeaderTakeover);
                    syncHeaderTakeover();
                });

                document.addEventListener('click', function(e) {
                    var btn = e.target.closest('.aatf-itinerary-acc-btn');
                    if (!btn) return;

                    var body = btn.nextElementSibling;
                    var isOpen = btn.classList.contains('accordion-open');
                    var wrapper = btn.closest('.aatf-itinerary-accordion');
                    if (!wrapper) return;

                    // Close all in this accordion
                    wrapper.querySelectorAll('.aatf-itinerary-acc-body').forEach(function(b) {
                        b.classList.add('hidden');
                    });
                    wrapper.querySelectorAll('.aatf-itinerary-acc-btn').forEach(function(b) {
                        b.classList.remove('accordion-open');
                        b.classList.add('bg-white');
                        b.style.background = '';
                        b.setAttribute('aria-expanded', 'false');
                        var chev = b.querySelector('.chevron');
                        if (chev) chev.classList.remove('rotate-180');
                    });

                    // Open clicked if it was closed
                    if (!isOpen && body) {
                        body.classList.remove('hidden');
                        btn.classList.add('accordion-open');
                        btn.classList.remove('bg-white');
                        btn.style.background = 'rgba(232,130,12,0.07)';
                        btn.setAttribute('aria-expanded', 'true');
                        var chevron = btn.querySelector('.chevron');
                        if (chevron) chevron.classList.add('rotate-180');
                    }
                });
            })();
        </script>

<?php
    }
}

get_footer();
