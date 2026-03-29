<?php if (!defined('ABSPATH')) exit; ?>

<?php
$treks_heading = (string) get_theme_mod('aatf_home_treks_heading', 'Most Popular Tours');
$treks_limit   = absint((int) get_theme_mod('aatf_home_treks_limit', 8));

$treks = array();
if (post_type_exists('trek')) {
    $treks = get_posts(array(
        'post_type'   => 'trek',
        'post_status' => 'publish',
        'numberposts' => $treks_limit,
        'orderby'     => 'menu_order date',
        'order'       => 'ASC',
    ));
}

$use_static = empty($treks);

$static_tours = array(
    array(
        'title'    => 'Sri Lanka One Life Adventures',
        'location' => 'Central Park West NY, USA',
        'price'    => '$39.00',
        'days'     => '10 days',
        'guests'   => 50,
        'rating'   => 0,
        'image'    => 'https://images.unsplash.com/photo-1508009603885-50cf7c579365?w=600&q=80',
        'url'      => '#',
    ),
    array(
        'title'    => 'Greece, Italy, Switzerland and Paris',
        'location' => 'Central Park West NY, USA',
        'price'    => '$39.00',
        'days'     => '10 days',
        'guests'   => 50,
        'rating'   => 3.6,
        'image'    => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=600&q=80',
        'url'      => '#',
    ),
    array(
        'title'    => 'North Island Adventure Tour',
        'location' => 'Central Park West NY, USA',
        'price'    => '$39.00',
        'days'     => '10 days',
        'guests'   => 50,
        'rating'   => 3.8,
        'image'    => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=600&q=80',
        'url'      => '#',
    ),
    array(
        'title'    => 'Java & Bali One Life Adventures',
        'location' => 'Central Park West NY, USA',
        'price'    => '$39.00',
        'days'     => '10 days',
        'guests'   => 50,
        'rating'   => 4.2,
        'image'    => 'https://images.unsplash.com/photo-1486325212027-8081e485255e?w=600&q=80',
        'url'      => '#',
    ),
    array(
        'title'    => 'Maldives Paradise Escape',
        'location' => 'Central Park West NY, USA',
        'price'    => '$49.00',
        'days'     => '7 days',
        'guests'   => 30,
        'rating'   => 5.0,
        'image'    => 'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?w=600&q=80',
        'url'      => '#',
    ),
    array(
        'title'    => 'Kyoto Cherry Blossom Journey',
        'location' => 'Central Park West NY, USA',
        'price'    => '$45.00',
        'days'     => '12 days',
        'guests'   => 40,
        'rating'   => 3.5,
        'image'    => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?w=600&q=80',
        'url'      => '#',
    ),
    array(
        'title'    => 'Patagonia End of the World Trek',
        'location' => 'Central Park West NY, USA',
        'price'    => '$55.00',
        'days'     => '14 days',
        'guests'   => 20,
        'rating'   => 3.0,
        'image'    => 'https://images.unsplash.com/photo-1501854140801-50d01698950b?w=600&q=80',
        'url'      => '#',
    ),
    array(
        'title'    => 'Iceland Northern Lights Tour',
        'location' => 'Central Park West NY, USA',
        'price'    => '$59.00',
        'days'     => '8 days',
        'guests'   => 25,
        'rating'   => 4.0,
        'image'    => 'https://images.unsplash.com/photo-1520769669658-f07657f5a307?w=600&q=80',
        'url'      => '#',
    ),
);

/**
 * Render star icons for a given rating (0–5, supports halves).
 */
function aatf_render_stars(float $rating): string
{
    $html  = '<div class="flex text-[var(--brand-orange)]">';
    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i) {
            // full star
            $html .= '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
        } elseif ($rating >= $i - 0.75) {
            // half star
            $pct  = round(($rating - ($i - 1)) * 100);
            $html .= '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">';
            $html .= '<defs><linearGradient id="hstar' . $i . '"><stop offset="' . $pct . '%" stop-color="var(--brand-orange)"/><stop offset="' . $pct . '%" stop-color="#d1d5db"/></linearGradient></defs>';
            $html .= '<path fill="url(#hstar' . $i . ')" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
        } else {
            // empty star
            $html .= '<svg class="w-4 h-4 text-[var(--brand-gray)]" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
        }
    }
    $html .= '</div>';
    return $html;
}

/**
 * Return the first non-empty meta value from a list of candidate keys.
 */
function aatf_get_first_meta_value(int $post_id, array $keys): string
{
    foreach ($keys as $key) {
        $value = get_post_meta($post_id, $key, true);
        if (is_array($value)) {
            $value = reset($value);
        }

        $value = trim((string) $value);
        if ($value !== '') {
            return $value;
        }
    }

    return '';
}

/**
 * Build a trek location label from direct meta or the first related destination.
 */
function aatf_get_trek_location_label(int $post_id): string
{
    $location = aatf_get_first_meta_value($post_id, array(
        'trek_location',
        '_location',
        'location',
    ));

    if ($location !== '') {
        return $location;
    }

    $destination_ids = get_post_meta($post_id, 'trek_destination_ids', true);
    $destination_ids = is_array($destination_ids) ? array_values(array_filter(array_map('absint', $destination_ids))) : array();

    if (empty($destination_ids)) {
        return '';
    }

    $destination_id = $destination_ids[0];
    $destination_title = trim((string) get_the_title($destination_id));
    $country = trim((string) get_post_meta($destination_id, 'destination_country', true));

    if ($destination_title !== '' && $country !== '') {
        return $destination_title . ', ' . $country;
    }

    if ($destination_title !== '') {
        return $destination_title;
    }

    return $country;
}
?>

<!-- ── Most Popular Tours (Carousel) ─────────────────────────────── -->
<section class="bg-white py-16 px-4">
    <div class="mx-auto">

        <div class="text-center mb-12">
            <p class="text-sm font-semibold tracking-wide text-[var(--brand-orange)] mb-1">
                <?php esc_html_e('Featured tours', 'aatf-expedition-base'); ?>
            </p>
            <h2 class="text-4xl font-extrabold text-[var(--brand-dark)]">
                <?php echo esc_html($treks_heading); ?>
            </h2>
        </div>

        <div class="relative" data-tour-carousel>
            <button
                type="button"
                class="absolute left-0 top-1/2 z-20 hidden -translate-y-1/2 -translate-x-1/2 rounded-full border border-gray-200 bg-white p-3 text-[var(--brand-dark)] shadow-md transition hover:scale-105 hover:text-[var(--brand-orange)] lg:flex"
                data-tour-prev
                aria-label="<?php esc_attr_e('Previous tours', 'aatf-expedition-base'); ?>">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <button
                type="button"
                class="absolute right-0 top-1/2 z-20 hidden -translate-y-1/2 translate-x-1/2 rounded-full border border-gray-200 bg-white p-3 text-[var(--brand-dark)] shadow-md transition hover:scale-105 hover:text-[var(--brand-orange)] lg:flex"
                data-tour-next
                aria-label="<?php esc_attr_e('Next tours', 'aatf-expedition-base'); ?>">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            <div class="overflow-hidden" id="tourTrack" data-tour-track>
                <div class="flex transition-transform duration-500 ease-in-out gap-6 mb-2 will-change-transform" id="tourSlider" data-tour-slider>

                    <?php if ($use_static) :
                        foreach ($static_tours as $tour) : ?>
                            <div class="tour-card bg-white rounded-2xl shadow-md overflow-hidden shrink-0 w-full sm:w-[calc(50%-12px)] xl:w-[calc(25%-18px)] border border-gray-100">
                                <div class="relative">
                                    <img src="<?php echo esc_url($tour['image']); ?>"
                                        alt="<?php echo esc_attr($tour['title']); ?>"
                                        class="w-full h-52 object-cover" />
                                    <button class="absolute top-3 right-3 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow hover:scale-110 transition-transform"
                                        aria-label="<?php esc_attr_e('Add to wishlist', 'aatf-expedition-base'); ?>">
                                        <svg class="w-4 h-4 text-[var(--brand-gray)] hover:text-red-500 transition-colors"
                                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="p-4">
                                    <?php if ($tour['rating'] > 0) : ?>
                                        <div class="flex items-center gap-1 mb-1">
                                            <?php echo aatf_render_stars((float) $tour['rating']); ?>
                                            <span class="text-sm font-semibold text-[var(--brand-gray)]"><?php echo esc_html($tour['rating']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <h3 class="font-bold text-[var(--brand-dark)] text-base leading-snug mb-1">
                                        <?php echo esc_html($tour['title']); ?>
                                    </h3>
                                    <div class="flex items-center gap-1 whitespace-nowrap text-xs text-[var(--brand-gray)] mb-3">
                                        <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                        </svg>
                                        <?php echo esc_html($tour['location']); ?>
                                    </div>
                                    <p class="text-[var(--brand-gray)] text-sm mb-3">
                                        <?php esc_html_e('From', 'aatf-expedition-base'); ?>
                                        <span class="text-[var(--brand-orange)] font-extrabold text-lg"><?php echo esc_html($tour['price']); ?></span>
                                    </p>
                                    <div class="border-t border-gray-100 pt-3 flex items-center justify-between text-xs text-[var(--brand-gray)]">
                                        <div class="flex items-center gap-3">
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                                                </svg>
                                                <?php echo esc_html($tour['days']); ?>
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <?php echo esc_html($tour['guests']); ?>
                                            </span>
                                        </div>
                                        <a href="<?php echo esc_url($tour['url']); ?>"
                                            class="text-[var(--brand-orange)] font-semibold hover:underline flex items-center gap-1">
                                            <?php esc_html_e('Explore', 'aatf-expedition-base'); ?>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach;
                    else :
                        foreach ($treks as $trek) :
                            $thumb    = get_the_post_thumbnail_url($trek->ID, 'medium_large');
                            if (!$thumb) $thumb = 'https://images.unsplash.com/photo-1553856622-d1b352e9a211?w=600&q=80';
                            $price    = aatf_get_first_meta_value($trek->ID, array('trek_price', '_price', 'price'));
                            $days     = aatf_get_first_meta_value($trek->ID, array('trek_duration', '_duration_days', 'duration_days', 'duration'));
                            $guests   = aatf_get_first_meta_value($trek->ID, array('trek_group_size', '_max_group_size', 'max_group_size', 'group_size'));
                            $rating   = (float) aatf_get_first_meta_value($trek->ID, array('trek_average_rating', '_average_rating', 'trek_rating', '_rating', 'rating'));
                            $location = aatf_get_trek_location_label($trek->ID);
                        ?>
                            <div class="tour-card bg-white rounded-2xl shadow-md overflow-hidden shrink-0 w-full sm:w-[calc(50%-12px)] xl:w-[calc(25%-18px)] border border-gray-100">
                                <div class="relative">
                                    <img src="<?php echo esc_url($thumb); ?>"
                                        alt="<?php echo esc_attr(get_the_title($trek->ID)); ?>"
                                        class="w-full h-52 object-cover" />
                                    <button class="absolute top-3 right-3 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow hover:scale-110 transition-transform"
                                        aria-label="<?php esc_attr_e('Add to wishlist', 'aatf-expedition-base'); ?>">
                                        <svg class="w-4 h-4 text-[var(--brand-gray)] hover:text-red-500 transition-colors"
                                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="p-4">
                                    <?php if ($rating > 0) : ?>
                                        <div class="flex items-center gap-1 mb-1">
                                            <?php echo aatf_render_stars($rating); ?>
                                            <span class="text-sm font-semibold text-[var(--brand-gray)]"><?php echo esc_html(number_format($rating, 1)); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <h3 class="font-bold text-[var(--brand-dark)] text-base leading-snug mb-1">
                                        <?php echo esc_html(get_the_title($trek->ID)); ?>
                                    </h3>
                                    <?php if ($location) : ?>
                                        <div class="flex items-center gap-1 whitespace-nowrap text-xs text-[var(--brand-gray)] mb-3">
                                            <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                            </svg>
                                            <?php echo esc_html($location); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($price) : ?>
                                        <p class="text-[var(--brand-gray)] text-sm mb-3">
                                            <?php esc_html_e('From', 'aatf-expedition-base'); ?>
                                            <span class="text-[var(--brand-orange)] font-extrabold text-lg">
                                                <?php echo esc_html(
                                                    is_numeric($price)
                                                        ? '$' . number_format_i18n((float) $price, 0)
                                                        : $price
                                                ); ?>
                                            </span>
                                        </p>
                                    <?php endif; ?>
                                    <div class="border-t border-gray-100 pt-3 flex items-center justify-between text-xs text-[var(--brand-gray)]">
                                        <div class="flex items-center gap-3">
                                            <?php if ($days) : ?>
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <circle cx="12" cy="12" r="10" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                                                    </svg>
                                                    <?php echo esc_html($days); ?> <?php esc_html_e('days', 'aatf-expedition-base'); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($guests) : ?>
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                    <?php echo esc_html($guests); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <a href="<?php echo esc_url(get_permalink($trek->ID)); ?>"
                                            class="text-[var(--brand-orange)] font-semibold hover:underline flex items-center gap-1">
                                            <?php esc_html_e('Explore', 'aatf-expedition-base'); ?>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                    <?php endforeach;
                    endif; ?>

                </div><!-- /tourSlider -->
            </div><!-- /overflow-hidden -->

            <!-- Dot indicators -->
            <div class="flex justify-center items-center gap-2 mt-10" id="tourDots" data-tour-dots></div>
            </div>

        </div><!-- /relative wrapper -->
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var carousel = document.querySelector('[data-tour-carousel]');
    if (!carousel) {
        return;
    }

    var track = carousel.querySelector('#tourTrack');
    var slider = carousel.querySelector('#tourSlider');
    var dotsWrap = carousel.querySelector('#tourDots');
    var prevBtn = carousel.querySelector('[data-tour-prev]');
    var nextBtn = carousel.querySelector('[data-tour-next]');

    if (!track || !slider || !dotsWrap) {
        return;
    }

    var cards = Array.prototype.slice.call(slider.querySelectorAll('.tour-card'));
    if (!cards.length) {
        return;
    }

    var currentPage = 0;
    var totalPages = 1;

    function getGap() {
        var styles = window.getComputedStyle(slider);
        var gap = parseFloat(styles.columnGap || styles.gap || '0');
        return Number.isFinite(gap) ? gap : 0;
    }

    function getCardsPerPage() {
        if (!cards.length) {
            return 1;
        }

        var trackWidth = track.clientWidth;
        var cardWidth = cards[0].getBoundingClientRect().width;
        var gap = getGap();
        var perPage = Math.floor((trackWidth + gap) / Math.max(cardWidth + gap, 1));

        return Math.max(1, perPage);
    }

    function renderDots() {
        dotsWrap.innerHTML = '';
        dotsWrap.style.display = totalPages > 1 ? 'flex' : 'none';

        for (var index = 0; index < totalPages; index += 1) {
            var dot = document.createElement('button');
            var isActive = index === currentPage;

            dot.type = 'button';
            dot.className = 'tour-dot rounded-full transition-all duration-300';
            dot.style.width = isActive ? '12px' : '10px';
            dot.style.height = isActive ? '12px' : '10px';
            dot.style.backgroundColor = isActive ? 'var(--brand-orange)' : 'var(--brand-gray)';
            dot.setAttribute('data-slide', String(index));
            dot.setAttribute('aria-label', 'Go to slide ' + (index + 1));
            dot.setAttribute('aria-pressed', isActive ? 'true' : 'false');

            dot.addEventListener('click', function (event) {
                currentPage = parseInt(event.currentTarget.getAttribute('data-slide') || '0', 10);
                update();
            });

            dotsWrap.appendChild(dot);
        }
    }

    function updateButtons() {
        var shouldShow = totalPages > 1;

        if (prevBtn) {
            prevBtn.style.display = shouldShow ? 'flex' : 'none';
            prevBtn.disabled = !shouldShow;
        }

        if (nextBtn) {
            nextBtn.style.display = shouldShow ? 'flex' : 'none';
            nextBtn.disabled = !shouldShow;
        }
    }

    function update() {
        var cardsPerPage = getCardsPerPage();
        var targetIndex = currentPage * cardsPerPage;
        var maxPage = Math.max(0, Math.ceil(cards.length / cardsPerPage) - 1);

        totalPages = maxPage + 1;
        if (currentPage > maxPage) {
            currentPage = maxPage;
            targetIndex = currentPage * cardsPerPage;
        }

        var targetCard = cards[Math.min(targetIndex, cards.length - 1)];
        var maxOffset = Math.max(0, slider.scrollWidth - track.clientWidth);
        var offset = targetCard ? Math.min(targetCard.offsetLeft, maxOffset) : 0;

        slider.style.transform = 'translateX(-' + offset + 'px)';
        renderDots();
        updateButtons();
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            currentPage = currentPage <= 0 ? totalPages - 1 : currentPage - 1;
            update();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            currentPage = currentPage >= totalPages - 1 ? 0 : currentPage + 1;
            update();
        });
    }

    var resizeTimer = null;
    window.addEventListener('resize', function () {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(update, 100);
    });

    update();
});
</script>
