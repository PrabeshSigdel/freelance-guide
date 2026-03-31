<?php
if (!defined('ABSPATH')) {
    exit;
}

// ── Helper: render star SVGs without repeating the long path string ───────────
if (!function_exists('aatf_render_stars')) {
    function aatf_render_stars(float $rating, int $total = 5, string $size = 'w-4 h-4'): void {
        $p = 'M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z';
        static $gc = 0;
        for ($i = 1; $i <= $total; $i++) {
            if ($rating >= $i) {
                echo '<svg class="' . esc_attr($size) . '" style="color:var(--brand-orange);" fill="currentColor" viewBox="0 0 20 20"><path d="' . $p . '"/></svg>';
            } elseif ($rating > $i - 1) {
                $gc++;
                $pct = round(($rating - ($i - 1)) * 100);
                $gid = 'asg' . $gc;
                echo '<svg class="' . esc_attr($size) . '" viewBox="0 0 20 20"><defs><linearGradient id="' . esc_attr($gid) . '"><stop offset="' . $pct . '%" stop-color="var(--brand-orange)"/><stop offset="' . $pct . '%" stop-color="#d1d5db"/></linearGradient></defs><path fill="url(#' . esc_attr($gid) . ')" d="' . $p . '"/></svg>';
            } else {
                echo '<svg class="' . esc_attr($size) . ' text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path d="' . $p . '"/></svg>';
            }
        }
    }
}

get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();

        // ── Meta fields ──────────────────────────────────────────────────────
        $post_id       = get_the_ID();
        $duration      = get_post_meta($post_id, 'trek_duration',      true);
        $price         = (float) get_post_meta($post_id, 'trek_price', true);
        $group         = (int)   get_post_meta($post_id, 'trek_group_size', true);
        $altitude      = get_post_meta($post_id, 'trek_max_altitude',  true);
        $trek_location = (string) get_post_meta($post_id, 'trek_location', true);
        $overview      = get_post_meta($post_id, 'trek_overview',      true);
        $reviews       = (string) get_post_meta($post_id, 'trek_reviews',  true);
        $map_embed     = (string) get_post_meta($post_id, 'trek_map_embed', true);
        $booking_url   = (string) get_post_meta($post_id, 'trek_booking_url', true);
        $rating        = get_post_meta($post_id, 'trek_rating', true);
        $rating        = $rating !== '' ? (float) $rating : 0.0;
        $review_count  = (int) get_post_meta($post_id, 'trek_review_count', true);

        // Highlights
        $highlights_title = (string) get_post_meta($post_id, 'trek_highlights_title', true);
        $highlights_intro = (string) get_post_meta($post_id, 'trek_highlights_intro', true);
        $highlights_text  = (string) get_post_meta($post_id, 'trek_highlights', true);
        $highlights = array_values(array_filter(
            array_map('trim', preg_split("/\r\n|\n|\r/", $highlights_text)),
            static fn($v) => $v !== ''
        ));

        // Slider images
        $slider_ids = get_post_meta($post_id, 'trek_slider_image_ids', true);
        if (!is_array($slider_ids) || empty($slider_ids)) {
            $slider_ids = get_post_meta($post_id, 'trek_gallery_ids', true);
        }
        $slider_ids = is_array($slider_ids)
            ? array_values(array_filter(array_map('absint', $slider_ids)))
            : [];

        // Video
        $video_url   = (string) get_post_meta($post_id, 'trek_video_url', true);
        $video_embed = '';
        $allowed_iframe_kses = ['iframe' => ['src' => true,'width' => true,'height' => true,'frameborder' => true,'allow' => true,'allowfullscreen' => true,'loading' => true,'referrerpolicy' => true,'title' => true]];
        if ($video_url !== '') {
            $video_raw = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', trim($video_url));
            if (stripos($video_raw, '<iframe') !== false) {
                $video_embed = (string) wp_kses($video_raw, $allowed_iframe_kses);
            } else {
                $video_embed = (string) wp_oembed_get($video_raw);
                if ($video_embed === '' && preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $video_raw, $m)) {
                    $video_embed = '<iframe src="' . esc_url('https://www.youtube.com/embed/' . $m[1]) . '" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe>';
                }
                if ($video_embed === '' && preg_match('~^[A-Za-z0-9_-]{11}$~', $video_raw)) {
                    $video_embed = '<iframe src="' . esc_url('https://www.youtube.com/embed/' . $video_raw) . '" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe>';
                }
            }
        }

        // Altitude profile
        $altitude_profile_rows = get_post_meta($post_id, 'trek_altitude_profile', true);
        $altitude_profile = [];
        if (is_array($altitude_profile_rows)) {
            foreach ($altitude_profile_rows as $row) {
                if (!is_array($row)) continue;
                $ap_day   = isset($row['day'])        ? trim((string) $row['day'])   : '';
                $ap_place = isset($row['place'])      ? trim((string) $row['place']) : '';
                $ap_alt   = isset($row['altitude_m']) ? (int) $row['altitude_m']     : 0;
                if ($ap_day === '' && $ap_place === '' && $ap_alt <= 0) continue;
                $altitude_profile[] = ['day' => $ap_day, 'place' => $ap_place, 'altitude_m' => max(0, $ap_alt)];
            }
        }

        // Equipment
        $equipment_sections = get_post_meta($post_id, 'trek_equipment_sections', true);
        $equipment_sections = is_array($equipment_sections) ? $equipment_sections : [];

        // Departures query
        $departures = new WP_Query([
            'post_type'      => 'departure',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => [['key' => 'aatf_trek_id', 'value' => $post_id, 'compare' => '=', 'type' => 'NUMERIC']],
            'meta_key'       => 'departure_start_date',
            'orderby'        => 'meta_value',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        ]);

        // Booking URL
        $booking_page         = get_page_by_path('booking');
        $internal_booking_url = ($booking_page instanceof WP_Post)
            ? get_permalink((int) $booking_page->ID)
            : home_url('/booking/');
        $ext_booking_url  = trim((string) $booking_url);
        $booking_base_url = ($ext_booking_url !== '' && preg_match('~^https?://~i', $ext_booking_url))
            ? $ext_booking_url
            : $internal_booking_url;
        $booking_cta_url  = add_query_arg(['trek_id' => $post_id], $booking_base_url);

        // Allowed kses (defined once)
        $allowed_map_kses = [
            'iframe' => ['src' => true,'width' => true,'height' => true,'frameborder' => true,'allow' => true,'allowfullscreen' => true,'loading' => true,'referrerpolicy' => true,'title' => true],
            'a'      => ['href' => true,'target' => true,'rel' => true],
        ];
?>

<article class="aatf-single-trek">

    <!-- ── Title bar ──────────────────────────────────────────────────────── -->
    <div class="bg-gray-100 border-b border-gray-200 px-6 py-4">
        <div class="max-w-7xl mx-auto flex items-start justify-between flex-wrap gap-4">
            <div>
                <h1 class="font-display text-2xl font-bold" style="color:var(--brand-dark);">
                    <?php echo esc_html(get_the_title()); ?>
                </h1>
                <?php if ($trek_location !== '') : ?>
                <p class="flex items-center gap-1 text-sm mt-1" style="color:var(--brand-gray);">
                    <svg class="w-3.5 h-3.5 shrink-0" style="color:var(--brand-orange);" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                    <?php echo esc_html($trek_location); ?>
                </p>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-6 text-sm">
                <?php if ($price > 0) : ?>
                <div class="flex items-center gap-2">
                    <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-xs" style="color:var(--brand-gray);">From</p>
                        <p class="font-bold" style="color:var(--brand-dark);">$<?php echo esc_html(number_format_i18n($price, 2)); ?></p>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($duration !== '') : ?>
                <div class="flex items-center gap-2">
                    <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/>
                    </svg>
                    <div>
                        <p class="text-xs" style="color:var(--brand-gray);">Duration</p>
                        <p class="font-bold" style="color:var(--brand-dark);"><?php echo esc_html($duration); ?> days</p>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($group > 0) : ?>
                <div class="flex items-center gap-2">
                    <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <div>
                        <p class="text-xs" style="color:var(--brand-gray);">Group Size</p>
                        <p class="font-bold" style="color:var(--brand-dark);"><?php echo esc_html($group); ?></p>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($altitude !== '') : ?>
                <div class="flex items-center gap-2">
                    <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    <div>
                        <p class="text-xs" style="color:var(--brand-gray);">Max Altitude</p>
                        <p class="font-bold" style="color:var(--brand-dark);"><?php echo esc_html($altitude); ?>m</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Hero slider / thumbnail ────────────────────────────────────────── -->
    <?php if (!empty($slider_ids)) : ?>
    <div class="aatf-single-trek__hero aatf-slider" data-aatf-slider>
        <button type="button" class="aatf-slider__btn aatf-slider__btn--prev" aria-label="Previous image">&#10094;</button>
        <div class="aatf-slider__track">
            <?php foreach ($slider_ids as $idx => $sid) :
                $simg = wp_get_attachment_image($sid, 'large', false, [
                    'class'   => 'aatf-single-trek__hero-image',
                    'loading' => $idx === 0 ? 'eager' : 'lazy',
                ]);
                if (!$simg) continue;
            ?>
            <div class="aatf-slider__slide<?php echo $idx === 0 ? ' is-active' : ''; ?>"><?php echo $simg; ?></div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="aatf-slider__btn aatf-slider__btn--next" aria-label="Next image">&#10095;</button>
    </div>
    <?php elseif (has_post_thumbnail($post_id)) : ?>
    <div class="aatf-single-trek__hero">
        <?php echo get_the_post_thumbnail($post_id, 'large', ['class' => 'aatf-single-trek__hero-image w-full object-cover']); ?>
    </div>
    <?php endif; ?>

    <!-- ── Main content ───────────────────────────────────────────────────── -->
    <div class="max-w-7xl mx-auto py-8 px-6 flex gap-8 items-start">

        <!-- ── Left column ──────────────────────────────────────────────────── -->
        <div class="flex-1 min-w-0">

            <!-- Rating + Share row -->
            <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-200">
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-0.5">
                        <?php aatf_render_stars($rating, 5, 'w-4 h-4'); ?>
                    </div>
                    <?php if ($rating > 0) : ?>
                    <span class="text-sm font-semibold" style="color:var(--brand-dark);"><?php echo esc_html(number_format($rating, 1)); ?></span>
                    <?php endif; ?>
                    <?php if ($review_count > 0) : ?>
                    <span class="text-sm" style="color:var(--brand-gray);">by <?php echo esc_html($review_count); ?> reviews</span>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-2">
                    <button class="flex items-center gap-1.5 border border-gray-300 text-xs font-semibold px-3 py-1.5 rounded hover:bg-gray-50 transition-colors" style="color:var(--brand-gray);">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                        Share
                    </button>
                    <button class="flex items-center gap-1.5 border border-gray-300 text-xs font-semibold px-3 py-1.5 rounded hover:bg-gray-50 transition-colors" style="color:var(--brand-gray);">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        Reviews
                    </button>
                </div>
            </div>

            <!-- ── DYNAMIC: Overview panel ──────────────────────────────────── -->
            <?php if (!empty($overview) || !empty($highlights) || $highlights_title !== '' || $highlights_intro !== '') : ?>
            <div class="aatf-panel mb-8">

                <?php if (!empty($overview)) : ?>
                <div class="mb-6">
                    <h2 class="text-xl font-bold mb-3" style="color:var(--brand-dark);">Overview</h2>
                    <p class="text-sm leading-relaxed" style="color:var(--brand-gray);"><?php echo esc_html($overview); ?></p>
                </div>
                <?php endif; ?>

                <?php if ($highlights_title !== '' || $highlights_intro !== '' || !empty($highlights)) : ?>
                <div class="mb-2">
                    <h2 class="text-xl font-bold mb-3" style="color:var(--brand-dark);">
                        <?php echo esc_html($highlights_title !== '' ? $highlights_title : 'Trek Highlights'); ?>
                    </h2>
                    <?php if ($highlights_intro !== '') : ?>
                    <p class="text-sm leading-relaxed mb-3" style="color:var(--brand-gray);"><?php echo esc_html($highlights_intro); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($highlights)) : ?>
                    <div class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm">
                        <?php foreach ($highlights as $hl) : ?>
                        <div class="flex items-center gap-2" style="color:var(--brand-dark);">
                            <svg class="w-4 h-4 shrink-0" style="color:var(--brand-orange);" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <?php echo esc_html($hl); ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php the_content(); ?>
            </div>
            <?php endif; ?>

            <!-- ── DYNAMIC: Trip Cost (shortcode) ───────────────────────────── -->
            <?php if (shortcode_exists('aatf_trek_trip_cost')) : ?>
            <div class="aatf-panel aatf-single-trek__trip-cost mb-8">
                <h2 class="text-xl font-bold mb-4" style="color:var(--brand-dark);">Included / Excluded</h2>
                <?php echo do_shortcode('[aatf_trek_trip_cost trek_id="' . esc_attr((string) $post_id) . '"]'); ?>
            </div>
            <?php endif; ?>

            <!-- ── DYNAMIC: Itinerary (shortcode) ───────────────────────────── -->
            <?php if (shortcode_exists('aatf_trek_itinerary')) : ?>
            <div class="aatf-panel aatf-single-trek__itinerary mb-8">
                <h2 class="text-xl font-bold mb-4" style="color:var(--brand-dark);">Tour Plan</h2>
                <?php echo do_shortcode('[aatf_trek_itinerary trek_id="' . esc_attr((string) $post_id) . '"]'); ?>
            </div>
            <?php endif; ?>

            <!-- ── DYNAMIC: Altitude Profile ────────────────────────────────── -->
            <?php if (!empty($altitude_profile)) :
                $ap_json = wp_json_encode($altitude_profile, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            ?>
            <section class="aatf-panel aatf-altitude-profile mb-8" data-aatf-altitude-profile>
                <h2 class="text-xl font-bold mb-4" style="color:var(--brand-dark);">Altitude Profile</h2>
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-sm" style="color:var(--brand-gray);">Altitude in:</span>
                    <div class="flex gap-2" role="group" aria-label="Altitude unit">
                        <button type="button" class="aatf-altitude-profile__unit is-active text-xs font-semibold px-3 py-1.5 rounded border border-gray-300 hover:bg-gray-50 transition-colors" data-unit="m" aria-pressed="true" style="color:var(--brand-dark);">Meter</button>
                        <button type="button" class="aatf-altitude-profile__unit text-xs font-semibold px-3 py-1.5 rounded border border-gray-300 hover:bg-gray-50 transition-colors" data-unit="ft" aria-pressed="false" style="color:var(--brand-gray);">Feet</button>
                    </div>
                </div>
                <div class="aatf-altitude-profile__chart" data-altitude-chart></div>
                <div class="flex items-center gap-2 mt-3" data-altitude-scroll>
                    <button type="button" class="px-2 py-1 rounded border border-gray-200 text-sm hover:bg-gray-50 transition-colors" data-scroll-dir="left" aria-label="Scroll left">&#8592;</button>
                    <input type="range" class="aatf-altitude-profile__scroll-range flex-1" min="0" max="100" value="0" step="1" aria-label="Scroll altitude profile"/>
                    <button type="button" class="px-2 py-1 rounded border border-gray-200 text-sm hover:bg-gray-50 transition-colors" data-scroll-dir="right" aria-label="Scroll right">&#8594;</button>
                </div>
                <script type="application/json" class="aatf-altitude-profile__data"><?php echo $ap_json !== false ? $ap_json : '[]'; ?></script>
            </section>
            <?php endif; ?>

            <!-- ── DYNAMIC: Map panel ────────────────────────────────────────── -->
            <?php if ($map_embed !== '') : ?>
            <section class="aatf-panel mb-8">
                <h2 class="text-xl font-bold mb-1" style="color:var(--brand-dark);">Location</h2>
                <h3 class="text-base font-semibold mb-4" style="color:var(--brand-gray);">Route map</h3>
                <div class="rounded-xl overflow-hidden border border-gray-200 bg-gray-100">
                    <?php if (stripos($map_embed, '<iframe') !== false) : ?>
                    <div class="w-full [&_iframe]:w-full [&_iframe]:h-56 [&_iframe]:border-0">
                        <?php echo wp_kses($map_embed, $allowed_map_kses); ?>
                    </div>
                    <?php else :
                        $map_url = esc_url($map_embed);
                        if ($map_url !== '') :
                    ?>
                    <div class="h-56 flex items-center justify-center">
                        <a href="<?php echo $map_url; ?>" target="_blank" rel="noopener"
                           class="flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-lg text-white"
                           style="background-color:var(--brand-orange);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                            Open Map
                        </a>
                    </div>
                    <?php endif; endif; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- ── DYNAMIC: Video panel ──────────────────────────────────────── -->
            <?php if ($video_embed !== '') : ?>
            <section class="aatf-panel aatf-single-trek__video mb-8">
                <h2 class="text-xl font-bold mb-4" style="color:var(--brand-dark);">Trek Video</h2>
                <div class="rounded-xl overflow-hidden aspect-video w-full [&_iframe]:w-full [&_iframe]:h-full [&_iframe]:border-0">
                    <?php echo wp_kses($video_embed, $allowed_iframe_kses); ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- ── DYNAMIC: Equipment panel ──────────────────────────────────── -->
            <?php if (!empty($equipment_sections)) : ?>
            <section class="aatf-panel aatf-single-trek__equipment mb-8">
                <h2 class="text-xl font-bold mb-5" style="color:var(--brand-dark);">Equipment</h2>
                <?php foreach ($equipment_sections as $eq_sec) :
                    $eq_heading   = isset($eq_sec['heading'])  ? (string) $eq_sec['heading']  : '';
                    $eq_image_id  = isset($eq_sec['image_id']) ? (int)    $eq_sec['image_id'] : 0;
                    $eq_items     = isset($eq_sec['items']) && is_array($eq_sec['items']) ? $eq_sec['items'] : [];
                    $eq_img_url   = $eq_image_id > 0 ? wp_get_attachment_image_url($eq_image_id, 'thumbnail') : '';
                    if ($eq_heading === '' && empty($eq_items) && !$eq_img_url) continue;
                ?>
                <div class="aatf-equipment-section mb-6">
                    <?php if ($eq_heading !== '' || $eq_img_url) : ?>
                    <div class="flex items-center gap-3 mb-3">
                        <?php if ($eq_img_url) : ?>
                        <div class="w-10 h-10 rounded-lg overflow-hidden shrink-0" style="background:rgba(232,130,12,0.1);">
                            <img src="<?php echo esc_url($eq_img_url); ?>" alt="" class="w-full h-full object-cover"/>
                        </div>
                        <?php endif; ?>
                        <?php if ($eq_heading !== '') : ?>
                        <h4 class="font-bold text-base" style="color:var(--brand-dark);"><?php echo esc_html($eq_heading); ?></h4>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($eq_items)) : ?>
                    <div class="grid grid-cols-3 gap-4">
                        <?php foreach ($eq_items as $eq_item) :
                            $clean = trim((string) $eq_item);
                            if ($clean === '') continue;
                        ?>
                        <div class="flex items-center gap-2 text-sm" style="color:var(--brand-gray);">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:rgba(232,130,12,0.1);">
                                <svg class="w-4 h-4" style="color:var(--brand-orange);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <?php echo esc_html($clean); ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </section>
            <?php endif; ?>

            <!-- ── DYNAMIC: Departures ───────────────────────────────────────── -->
            <?php if ($departures->have_posts()) :
                $dep_status_labels  = ['available' => 'Available', 'limited' => 'Limited', 'guaranteed' => 'Guaranteed', 'full' => 'Full'];
                $dep_status_colors  = ['full' => 'text-red-500', 'limited' => 'text-yellow-600', 'guaranteed' => 'text-blue-600'];
            ?>
            <section class="aatf-panel aatf-single-trek__departures mb-8">
                <h2 class="text-xl font-bold mb-4" style="color:var(--brand-dark);">Departures</h2>
                <div class="space-y-3">
                <?php while ($departures->have_posts()) :
                    $departures->the_post();
                    $dep_id     = get_the_ID();
                    $dep_title  = trim((string) get_the_title($dep_id));
                    $dep_start  = (string) get_post_meta($dep_id, 'departure_start_date', true);
                    $dep_end    = (string) get_post_meta($dep_id, 'departure_end_date',   true);
                    $dep_status = strtolower(trim((string) get_post_meta($dep_id, 'departure_status', true)));
                    $dep_price  = get_post_meta($dep_id, 'departure_price', true);
                    $dep_price  = is_numeric($dep_price) ? (float) $dep_price : 0.0;

                    $s_ts = $dep_start !== '' ? strtotime($dep_start) : false;
                    $e_ts = $dep_end   !== '' ? strtotime($dep_end)   : false;
                    $s_lbl = $s_ts ? date_i18n(get_option('date_format'), $s_ts) : '';
                    $e_lbl = $e_ts ? date_i18n(get_option('date_format'), $e_ts) : '';

                    if ($s_lbl !== '' && $e_lbl !== '' && $s_lbl !== $e_lbl) {
                        $date_lbl = $s_lbl . ' – ' . $e_lbl;
                    } elseif ($s_lbl !== '') {
                        $date_lbl = $s_lbl;
                    } elseif ($e_lbl !== '') {
                        $date_lbl = $e_lbl;
                    } elseif ($dep_title !== '') {
                        $date_lbl = $dep_title;
                    } else {
                        $date_lbl = 'Date to be announced';
                    }

                    $status_lbl   = isset($dep_status_labels[$dep_status]) ? $dep_status_labels[$dep_status] : 'Available';
                    $status_color = isset($dep_status_colors[$dep_status]) ? $dep_status_colors[$dep_status] : 'text-green-600';
                    $eff_price    = $dep_price > 0 ? $dep_price : $price;
                    $price_lbl    = $eff_price > 0 ? '$' . number_format_i18n($eff_price, 0) : 'Price on request';
                    $dep_book_url = add_query_arg(['trek_id' => $post_id, 'departure_id' => $dep_id], $booking_base_url);
                ?>
                <div class="border border-gray-200 rounded-lg px-5 py-4 flex items-center justify-between gap-4 hover:bg-gray-50 transition-colors">
                    <div>
                        <strong class="block text-sm font-semibold mb-1" style="color:var(--brand-dark);"><?php echo esc_html($date_lbl); ?></strong>
                        <div class="flex items-center gap-3 text-xs">
                            <span class="font-medium <?php echo esc_attr($status_color); ?>"><?php echo esc_html($status_lbl); ?></span>
                            <span style="color:var(--brand-gray);"><?php echo esc_html($price_lbl); ?></span>
                        </div>
                    </div>
                    <a class="shrink-0 text-white font-bold text-xs tracking-widest uppercase px-4 py-2 rounded-lg hover:opacity-90 transition-opacity"
                       style="background-color:var(--brand-orange);"
                       href="<?php echo esc_url($dep_book_url); ?>">Book Now</a>
                </div>
                <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- ── DYNAMIC: Reviews ──────────────────────────────────────────── -->
            <?php if ($reviews !== '') : ?>
            <section class="aatf-panel mb-8">
                <h2 class="text-xl font-bold mb-3" style="color:var(--brand-dark);">Reviews Summary</h2>
                <p class="text-sm leading-relaxed" style="color:var(--brand-gray);"><?php echo nl2br(esc_html($reviews)); ?></p>
            </section>
            <?php endif; ?>

            <!-- ── DYNAMIC: FAQ ──────────────────────────────────────────────── -->
            <?php if (class_exists('AATF_Frontend_Components')) : ?>
            <div class="aatf-panel mb-8">
                <?php echo do_shortcode('[aatf_trek_faq]'); ?>
            </div>
            <?php endif; ?>

        </div><!-- /left col -->

        <!-- ── Right column (sticky sidebar — static) ────────────────────── -->
        <div class="w-72 shrink-0 space-y-5 sticky top-4">

            <!-- Booking Tour card -->
            <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-base" style="color:var(--brand-dark);">Book This Trek</h3>
                </div>
                <div class="px-5 py-4 space-y-4 text-sm">
                    <?php if (shortcode_exists('aatf_trek_group_pricing')) :
                        echo do_shortcode('[aatf_trek_group_pricing trek_id="' . esc_attr((string) $post_id) . '" wrap="none"]');
                    endif; ?>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--brand-dark);">From:</label>
                        <div class="flex items-center border border-gray-200 rounded-lg px-3 py-2.5">
                            <input type="date" class="flex-1 text-xs outline-none bg-transparent" style="color:var(--brand-gray);"/>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--brand-dark);">Time:</label>
                        <p class="text-xs italic" style="color:var(--brand-gray);">please select date first</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold mb-1.5" style="color:var(--brand-dark);">Tickets:</label>
                        <p class="text-xs italic" style="color:var(--brand-gray);">please select date first</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs font-semibold mb-1" style="color:var(--brand-dark);">Mandatory fees</p>
                        <p class="text-xs" style="color:var(--brand-gray);">Fee 10%</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold mb-2" style="color:var(--brand-dark);">Add Extra</p>
                        <div class="space-y-2 text-xs" style="color:var(--brand-gray);">
                            <label class="flex items-center justify-between cursor-pointer">
                                <span class="flex items-center gap-2"><input type="checkbox" class="accent-orange-500"/> Service per booking</span>
                                <span class="font-semibold" style="color:var(--brand-dark);">$30.00</span>
                            </label>
                            <label class="flex items-start justify-between cursor-pointer">
                                <span class="flex items-center gap-2"><input type="checkbox" class="accent-orange-500 mt-0.5"/> Service per person</span>
                                <span class="text-right">
                                    <span class="block font-semibold" style="color:var(--brand-dark);">Adult: $17.00</span>
                                    <span class="block" style="color:var(--brand-gray);">Youth: $14.00</span>
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                        <span class="font-bold text-base" style="color:var(--brand-dark);">Total:</span>
                        <?php if ($price > 0) : ?>
                        <span class="font-bold text-base" style="color:var(--brand-orange);">$<?php echo esc_html(number_format_i18n($price, 0)); ?></span>
                        <?php endif; ?>
                    </div>
                    <a href="<?php echo esc_url($booking_cta_url); ?>"
                       class="w-full text-white font-bold text-sm tracking-widest uppercase py-3.5 rounded-lg hover:opacity-90 transition-opacity flex items-center justify-center gap-2"
                       style="background-color:var(--brand-orange);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Book Now
                    </a>
                </div>
            </div>

            <!-- Last Minute Deals (static) -->
            <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-base" style="color:var(--brand-dark);">Last Minute Deals</h3>
                </div>
                <?php
                $lmd = [
                    ['img' => 'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?w=200&q=80', 'alt' => 'Java Bali',    'title' => 'Java &amp; Bali One Life...', 'price' => '$39.00', 'stars' => 4.0],
                    ['img' => 'https://images.unsplash.com/photo-1508009603885-50cf7c579365?w=200&q=80', 'alt' => 'Sri Lanka',    'title' => 'Sri Lanka One Life...',       'price' => '$39.00', 'stars' => 4.0],
                    ['img' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=200&q=80', 'alt' => 'North Island', 'title' => 'North Island Adventure...',    'price' => '$39.00', 'stars' => 4.0],
                    ['img' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=200&q=80', 'alt' => 'Sicily',      'title' => 'Small Group Sicily Food...',   'price' => '$39.00', 'stars' => 4.0],
                ];
                ?>
                <div class="divide-y divide-gray-100">
                    <?php foreach ($lmd as $deal) : ?>
                    <div class="flex gap-3 p-4 hover:bg-gray-50 transition-colors cursor-pointer">
                        <img src="<?php echo esc_url($deal['img']); ?>" alt="<?php echo esc_attr($deal['alt']); ?>" class="w-16 h-14 object-cover rounded-lg shrink-0" loading="lazy"/>
                        <div>
                            <p class="text-xs font-semibold leading-snug mb-1" style="color:var(--brand-dark);"><?php echo $deal['title']; ?></p>
                            <div class="flex gap-0.5 mb-1">
                                <?php aatf_render_stars((float) $deal['stars'], 5, 'w-2.5 h-2.5'); ?>
                            </div>
                            <p class="text-xs">From <span class="font-bold" style="color:var(--brand-orange);"><?php echo esc_html($deal['price']); ?></span></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div><!-- /right col -->

    </div><!-- /main flex -->

</article>

<?php
    endwhile;
endif;

get_footer();