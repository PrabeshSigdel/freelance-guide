<?php if (!defined('ABSPATH')) exit; ?>

<?php
if (!function_exists('aatf_render_stars')) {
    function aatf_render_stars(float $rating): string
    {
        $html = '<div class="flex">';
        for ($i = 1; $i <= 5; $i++) {
            if ($rating >= $i) {
                $html .= '<span class="text-[var(--brand-orange)]">★</span>';
            } elseif ($rating >= $i - 0.5) {
                $html .= '<span class="text-[var(--brand-orange)]">★</span>';
            } else {
                $html .= '<span class="text-[var(--brand-gray)]">★</span>';
            }
        }
        $html .= '</div>';
        return $html;
    }
}

if (!function_exists('aatf_get_first_meta_value')) {
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
}

if (!function_exists('aatf_get_trek_location_label')) {
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
}

$explore_heading = (string) get_theme_mod('aatf_explore_tours_heading', 'All Tours');
$explore_subheading = (string) get_theme_mod('aatf_explore_tours_subheading', 'Browse every published trek');

$current_page = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
$sort_by = isset($_GET['sort_by']) ? sanitize_key(wp_unslash($_GET['sort_by'])) : 'featured';
$sort_options = array(
    'featured'       => __('Featured', 'aatf-expedition-base'),
    'price_low_high' => __('Price: Low to High', 'aatf-expedition-base'),
    'price_high_low' => __('Price: High to Low', 'aatf-expedition-base'),
    'duration'       => __('Duration', 'aatf-expedition-base'),
);

if (!isset($sort_options[$sort_by])) {
    $sort_by = 'featured';
}

$treks_query = null;
if (post_type_exists('trek')) {
    $query_args = array(
        'post_type'              => 'trek',
        'post_status'            => 'publish',
        'posts_per_page'         => 6,
        'paged'                  => $current_page,
        'orderby'                => array(
            'menu_order' => 'ASC',
            'date'       => 'DESC',
        ),
        'no_found_rows'          => false,
        'ignore_sticky_posts'    => true,
        'update_post_meta_cache' => true,
        'update_post_term_cache' => false,
    );

    if ($sort_by === 'price_low_high') {
        $query_args['meta_key'] = 'trek_price';
        $query_args['orderby'] = 'meta_value_num';
        $query_args['order'] = 'ASC';
    } elseif ($sort_by === 'price_high_low') {
        $query_args['meta_key'] = 'trek_price';
        $query_args['orderby'] = 'meta_value_num';
        $query_args['order'] = 'DESC';
    } elseif ($sort_by === 'duration') {
        $query_args['meta_key'] = 'trek_duration';
        $query_args['orderby'] = 'meta_value_num';
        $query_args['order'] = 'ASC';
    }

    $treks_query = new WP_Query($query_args);
}
?>

<section class="bg-gray-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <header class="text-center mb-12">
            <p class="text-[var(--brand-orange)] font-medium text-sm mb-2">
                <?php echo esc_html($explore_subheading); ?>
            </p>
            <h2 class="text-4xl md:text-5xl font-bold text-[var(--brand-dark)]">
                <?php echo esc_html($explore_heading); ?>
            </h2>
        </header>

        <?php if ($treks_query instanceof WP_Query && $treks_query->have_posts()) : ?>
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-10">
                <div>
                    <p class="text-[var(--brand-gray)] mt-2">
                        <?php
                        echo esc_html(sprintf(
                            _n('%s Tour', '%s Tours', (int) $treks_query->found_posts, 'aatf-expedition-base'),
                            number_format_i18n((int) $treks_query->found_posts)
                        ));
                        ?>
                    </p>
                </div>

                <form method="get" class="flex items-center gap-3 self-start md:self-auto">
                    <?php foreach ($_GET as $key => $value) : ?>
                        <?php
                        if ($key === 'sort_by' || $key === 'paged' || $key === 'page') {
                            continue;
                        }

                        if (is_array($value)) {
                            continue;
                        }
                        ?>
                        <input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr(wp_unslash($value)); ?>">
                    <?php endforeach; ?>

                    <label for="sortBy" class="text-sm font-semibold text-[var(--brand-dark)]">
                        <?php esc_html_e('Sort by', 'aatf-expedition-base'); ?>
                    </label>
                    <select
                        id="sortBy"
                        name="sort_by"
                        onchange="this.form.submit()"
                        class="border border-gray-200 bg-white px-5 py-3 text-sm text-[var(--brand-gray)] focus:outline-none focus:border-[var(--brand-orange)]">
                        <?php foreach ($sort_options as $option_value => $option_label) : ?>
                            <option value="<?php echo esc_attr($option_value); ?>" <?php selected($sort_by, $option_value); ?>>
                                <?php echo esc_html($option_label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
                <?php while ($treks_query->have_posts()) : $treks_query->the_post(); ?>
                    <?php
                    $post_id  = get_the_ID();
                    $thumb    = get_the_post_thumbnail_url($post_id, 'medium_large');
                    if (!$thumb) $thumb = 'https://images.unsplash.com/photo-1553856622-d1b352e9a211?w=600&q=80';
                    $price    = aatf_get_first_meta_value($post_id, array('trek_price', '_price', 'price'));
                    $days     = aatf_get_first_meta_value($post_id, array('trek_duration', '_duration_days', 'duration_days', 'duration'));
                    $guests   = aatf_get_first_meta_value($post_id, array('trek_group_size', '_max_group_size', 'max_group_size', 'group_size'));
                    $rating   = (float) aatf_get_first_meta_value($post_id, array('trek_average_rating', '_average_rating', 'trek_rating', '_rating', 'rating'));
                    $location = aatf_get_trek_location_label($post_id);
                    $featured = aatf_get_first_meta_value($post_id, array('_is_featured', 'trek_featured', 'featured')) === '1';
                    ?>
                    <article class="bg-white rounded-2xl shadow-md overflow-hidden flex flex-col group">
                        <div class="relative h-64 overflow-hidden">
                            <img
                                src="<?php echo esc_url($thumb); ?>"
                                alt="<?php echo esc_attr(get_the_title($post_id)); ?>"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />

                            <?php if ($featured) : ?>
                                <span class="absolute top-4 left-4 bg-[var(--brand-orange)] text-white text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded">
                                    <?php esc_html_e('Featured', 'aatf-expedition-base'); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="p-6 -mt-8 relative z-10 bg-white rounded-t-3xl flex-1 flex flex-col">
                            <div class="flex justify-between items-center mb-3">
                                <div class="flex items-center gap-1 text-[var(--brand-orange)]">
                                    <?php if ($rating > 0) : ?>
                                        <?php echo aatf_render_stars($rating); ?>
                                        <span class="text-xs font-semibold text-[var(--brand-gray)] ml-1"><?php echo esc_html(number_format($rating, 2)); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <h3 class="text-xl font-bold text-[var(--brand-dark)] mb-2">
                                <?php echo esc_html(get_the_title($post_id)); ?>
                            </h3>

                            <?php if ($location) : ?>
                                <p class="text-[var(--brand-gray)] text-sm flex items-center gap-1 mb-4">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                                        <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                                    </svg>
                                    <?php echo esc_html($location); ?>
                                </p>
                            <?php endif; ?>

                            <?php if ($price) : ?>
                                <div class="mb-6">
                                    <p class="text-sm text-[var(--brand-gray)]">
                                        <?php esc_html_e('From', 'aatf-expedition-base'); ?>
                                        <span class="text-2xl font-bold text-[var(--brand-orange)]">
                                            <?php echo esc_html(
                                                is_numeric($price)
                                                    ? '$' . number_format_i18n((float) $price, 0)
                                                    : $price
                                            ); ?>
                                        </span>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <div class="flex items-center justify-between pt-4 border-t border-gray-100 bg-gray-50 -mx-6 px-6 pb-4 mt-auto">
                                <div class="flex items-center gap-4 text-xs font-medium text-[var(--brand-gray)]">
                                    <?php if ($days) : ?>
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                                            </svg>
                                            <?php echo esc_html($days); ?> <?php esc_html_e('days', 'aatf-expedition-base'); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($guests) : ?>
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                                            </svg>
                                            <?php echo esc_html($guests); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="text-[var(--brand-orange)] text-sm font-bold flex items-center gap-1 hover:translate-x-1 transition-transform">
                                    <?php esc_html_e('Explore', 'aatf-expedition-base'); ?>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M17 8l4 4m0 0l-4 4m4-4H3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <?php
            $pagination = paginate_links(array(
                'base'      => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                'format'    => '?paged=%#%',
                'current'   => $current_page,
                'total'     => max(1, (int) $treks_query->max_num_pages),
                'type'      => 'array',
                'prev_text' => __('Previous', 'aatf-expedition-base'),
                'next_text' => __('Next', 'aatf-expedition-base'),
                'add_args'  => array(
                    'sort_by' => $sort_by,
                ),
            ));
            ?>

            <?php if (!empty($pagination) && is_array($pagination)) : ?>
                <div class="flex flex-wrap items-center justify-center gap-3 mt-12">
                    <?php foreach ($pagination as $page_link) : ?>
                        <?php
                        $is_current = strpos($page_link, 'current') !== false;
                        $classes = $is_current
                            ? 'w-11 h-11 rounded-full border border-[var(--brand-orange)] bg-[var(--brand-orange)] text-white flex items-center justify-center font-semibold'
                            : 'min-w-11 px-4 h-11 rounded-full border border-gray-200 bg-white text-[var(--brand-dark)] flex items-center justify-center font-semibold hover:border-[var(--brand-orange)] hover:text-[var(--brand-orange)] transition-colors';

                        $page_link = str_replace('page-numbers', trim('page-numbers ' . $classes), $page_link);
                        echo wp_kses_post($page_link);
                        ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php wp_reset_postdata(); ?>
        <?php else : ?>
            <div class="bg-white border border-gray-200 rounded-2xl p-8 text-center">
                <p class="text-[var(--brand-gray)] text-sm">
                    <?php esc_html_e('No tours have been published yet. Add Trek posts in WordPress admin and they will appear here automatically.', 'aatf-expedition-base'); ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</section>
