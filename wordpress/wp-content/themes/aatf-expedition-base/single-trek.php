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

        // Get destination taxonomy if available
        $destinations = get_the_terms($post_id, 'destination');
        $location_name = '';
        if ($destinations && !is_wp_error($destinations)) {
            $location_name = $destinations[0]->name;
        }

        // Get tour type taxonomy if available
        $tour_types = get_the_terms($post_id, 'trek_type');
        $tour_type_name = 'Trekking';
        if ($tour_types && !is_wp_error($tour_types)) {
            $tour_type_name = $tour_types[0]->name;
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

                <!-- Overview -->
                <?php if (!empty($overview) || get_the_content()) : ?>
                <div class="mb-8">
                    <h2 class="text-xl font-bold mb-3" style="color: var(--brand-dark);">Overview</h2>
                    <?php if (get_the_content()) : ?>
                    <div class="text-sm leading-relaxed mb-3" style="color: var(--brand-gray);">
                        <?php the_content(); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($overview)) : ?>
                    <p class="text-sm leading-relaxed" style="color: var(--brand-gray);">
                        <?php echo esc_html($overview); ?>
                    </p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Highlights -->
                <?php if ($highlights_title !== '' || $highlights_intro !== '' || !empty($highlights)) : ?>
                <div class="mb-8">
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
                </div>
                <?php endif; ?>

                <!-- Cost Includes/Excludes -->
                <?php
                $cost_includes_text = (string) get_post_meta($post_id, 'trek_cost_includes', true);
                $cost_excludes_text = (string) get_post_meta($post_id, 'trek_cost_excludes', true);
                $cost_includes = array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", $cost_includes_text)), function($v) { return $v !== ''; }));
                $cost_excludes = array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", $cost_excludes_text)), function($v) { return $v !== ''; }));
                ?>
                <?php if (!empty($cost_includes) || !empty($cost_excludes)) : ?>
                <div class="mb-8">
                    <h2 class="text-xl font-bold mb-4" style="color: var(--brand-dark);">Included/Exclude</h2>
                    <div class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm">
                        <!-- Included -->
                        <div class="space-y-2">
                            <?php foreach ($cost_includes as $inc_item) : ?>
                            <div class="flex items-center gap-2" style="color: var(--brand-dark);">
                                <svg class="w-4 h-4 shrink-0" style="color: var(--brand-orange);" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
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
                </div>
                <?php endif; ?>

                <!-- Equipment -->
                <?php if (!empty($equipment_sections)) : ?>
                <div class="mb-8">
                    <h2 class="text-xl font-bold mb-5" style="color: var(--brand-dark);">Equipment</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach ($equipment_sections as $section) :
                            $heading = isset($section['heading']) ? (string) $section['heading'] : '';
                            $items = isset($section['items']) && is_array($section['items']) ? $section['items'] : array();

                            if ($heading === '' && empty($items)) {
                                continue;
                            }
                            ?>
                        <div class="pl-4" style="border-color: rgba(232,130,12,0.25);">
                            <?php if ($heading !== '') : ?>
                            <h4 class="mb-2 text-sm font-bold uppercase tracking-wide" style="color: var(--brand-dark);"><?php echo esc_html($heading); ?></h4>
                            <?php endif; ?>
                            <?php if (!empty($items)) : ?>
                            <ul class="space-y-1.5 pl-5 text-sm leading-6 list-disc marker:text-[var(--brand-orange)]" style="color: var(--brand-gray);">
                                <?php foreach ($items as $item) :
                                    $clean_item = (string) $item;
                                    if ($clean_item === '') continue;
                                    ?>
                                <li class="pl-1">
                                    <?php echo esc_html($clean_item); ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Itinerary / Tour Plan accordion -->
                <?php if (shortcode_exists('aatf_trek_itinerary')) : ?>
                <div class="mb-8">
                    <?php echo do_shortcode('[aatf_trek_itinerary trek_id="' . esc_attr((string) $post_id) . '"]'); ?>
                </div>
                <?php endif; ?>

                <!-- Altitude Profile -->
                <?php if (!empty($altitude_profile)) : ?>
                <section class="mb-8 aatf-altitude-profile" data-aatf-altitude-profile>
                    <h2 class="text-xl font-bold mb-4" style="color: var(--brand-dark);">Altitude Profile</h2>
                    <div class="aatf-altitude-profile__head flex items-center gap-3 mb-3">
                        <span class="aatf-altitude-profile__label text-sm" style="color: var(--brand-gray);">Altitude in:</span>
                        <div class="aatf-altitude-profile__units flex gap-2" role="group" aria-label="Altitude unit">
                            <button type="button" class="aatf-altitude-profile__unit is-active text-xs px-3 py-1 rounded font-semibold border border-gray-200" data-unit="m" aria-pressed="true" style="background: rgba(232,130,12,0.1); color: var(--brand-orange);">Meter</button>
                            <button type="button" class="aatf-altitude-profile__unit text-xs px-3 py-1 rounded font-semibold border border-gray-200 bg-white" data-unit="ft" aria-pressed="false" style="color: var(--brand-gray);">Feet</button>
                        </div>
                    </div>
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
                    <script type="application/json" class="aatf-altitude-profile__data"><?php echo $altitude_profile_json !== false ? $altitude_profile_json : '[]'; ?></script>
                </section>
                <?php endif; ?>

                <!-- Map / Location -->
                <?php if ($map_embed !== '') : ?>
                <div class="mb-8">
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
                </div>
                <?php endif; ?>

                <!-- Video -->
                <?php if ($video_embed) :
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
                <div class="mb-8">
                    <h2 class="text-xl font-bold mb-4" style="color: var(--brand-dark);">Trek Video</h2>
                    <div class="rounded-xl overflow-hidden border border-gray-200 aspect-video [&>iframe]:w-full [&>iframe]:h-full [&>iframe]:border-0">
                        <?php echo wp_kses($video_embed, $allowed_iframe); ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Departures -->
                <?php if ($departures->have_posts()) :
                    $status_labels = array(
                        'available' => 'Available',
                        'limited' => 'Limited',
                        'guaranteed' => 'Guaranteed',
                        'full' => 'Full',
                    );
                    ?>
                <div class="mb-8">
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
                </div>
                <?php wp_reset_postdata(); endif; ?>

                <!-- FAQ -->
                <?php if (class_exists('AATF_Frontend_Components')) : ?>
                <div class="mb-8">
                    <?php echo do_shortcode('[aatf_trek_faq]'); ?>
                </div>
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
                <div class="mb-8">
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
                        <?php endwhile; wp_reset_postdata(); ?>
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
                    <div class="px-5 py-4 space-y-4 text-sm">

                        <!-- From date -->
                        <div>
                            <label class="block text-xs font-semibold mb-1.5" style="color: var(--brand-dark);">From:</label>
                            <div class="flex items-center border border-gray-200 rounded-lg px-3 py-2.5 gap-2">
                                <input type="date" class="flex-1 text-xs outline-none bg-transparent" style="color: var(--brand-gray);" />
                            </div>
                        </div>

                        <!-- Time -->
                        <div>
                            <label class="block text-xs font-semibold mb-1.5" style="color: var(--brand-dark);">Time:</label>
                            <p class="text-xs italic" style="color: var(--brand-gray);">please select date first</p>
                        </div>

                        <!-- Tickets -->
                        <div>
                            <label class="block text-xs font-semibold mb-1.5" style="color: var(--brand-dark);">Tickets:</label>
                            <p class="text-xs italic" style="color: var(--brand-gray);">please select date first</p>
                        </div>

                        <!-- Meeting point -->
                        <div>
                            <label class="block text-xs font-semibold mb-2" style="color: var(--brand-dark);">Select meeting point (find closest location):</label>
                            <div class="space-y-1.5 text-xs" style="color: var(--brand-gray);">
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input type="radio" name="meeting" class="mt-0.5 accent-orange-500" />
                                    <span>12:00 pm, 1:00 am, 2:00 am, 3:00 am, 5:00 am, 6:00 am. – Sandos Papagayo Beach Resort, Calle las Acacias, Yaiza, España</span>
                                </label>
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input type="radio" name="meeting" class="mt-0.5 accent-orange-500" />
                                    <span>5:00 pm, 10:00 am – Sandos Papagayo Beach Resort, Calle las Acacias, Yaiza, España</span>
                                </label>
                            </div>
                        </div>

                        <!-- Mandatory fees -->
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-xs font-semibold mb-1" style="color: var(--brand-dark);">Mandatory fees</p>
                            <p class="text-xs" style="color: var(--brand-gray);">Fee 10%</p>
                        </div>

                        <!-- Add Extra -->
                        <div>
                            <p class="text-xs font-bold mb-2" style="color: var(--brand-dark);">Add Extra</p>
                            <div class="space-y-2 text-xs" style="color: var(--brand-gray);">
                                <label class="flex items-center justify-between cursor-pointer">
                                    <span class="flex items-center gap-2">
                                        <input type="checkbox" class="accent-orange-500" />
                                        Service per booking
                                    </span>
                                    <span class="font-semibold" style="color: var(--brand-dark);">$30.00</span>
                                </label>
                                <label class="flex items-start justify-between cursor-pointer">
                                    <span class="flex items-center gap-2">
                                        <input type="checkbox" class="accent-orange-500 mt-0.5" />
                                        Service per person
                                    </span>
                                    <span class="text-right">
                                        <span class="block font-semibold" style="color: var(--brand-dark);">Adult: $17.00</span>
                                        <span class="block" style="color: var(--brand-gray);">Youth: $14.00</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                            <span class="font-bold text-base" style="color: var(--brand-dark);">Total:</span>
                        </div>

                        <!-- Book Now button -->
                        <a href="<?php echo esc_url($booking_cta_url); ?>" class="w-full text-white font-bold text-sm tracking-widest uppercase py-3.5 rounded-lg hover:opacity-90 transition-opacity flex items-center justify-center gap-2 no-underline" style="background-color: var(--brand-orange);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Book Now
                        </a>

                        <?php if (shortcode_exists('aatf_trek_group_pricing')) : ?>
                        <div class="mt-2">
                            <?php echo do_shortcode('[aatf_trek_group_pricing trek_id="' . esc_attr((string) $post_id) . '" wrap="none"]'); ?>
                        </div>
                        <?php endif; ?>

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
