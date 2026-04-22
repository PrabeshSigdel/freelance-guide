<?php
if (!defined('ABSPATH')) {
    exit;
}

/* ── Helper functions (same as popular-tours.php) ─────────────────── */
if (!function_exists('aatf_dest_get_first_meta_value')) {
    function aatf_dest_get_first_meta_value(int $post_id, array $keys): string
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

if (!function_exists('aatf_dest_get_trek_location_label')) {
    function aatf_dest_get_trek_location_label(int $post_id): string
    {
        $location = aatf_dest_get_first_meta_value($post_id, array('trek_location', '_location', 'location'));
        if ($location !== '') {
            return $location;
        }
        $destination_ids = get_post_meta($post_id, 'trek_destination_ids', true);
        $destination_ids = is_array($destination_ids)
            ? array_values(array_filter(array_map('absint', $destination_ids)))
            : array();
        if (empty($destination_ids)) {
            return '';
        }
        $dest_id    = $destination_ids[0];
        $dest_title = trim((string) get_the_title($dest_id));
        $country    = trim((string) get_post_meta($dest_id, 'destination_country', true));
        if ($dest_title !== '' && $country !== '') {
            return $dest_title . ', ' . $country;
        }
        return $dest_title !== '' ? $dest_title : $country;
    }
}

if (!function_exists('aatf_dest_render_stars')) {
    function aatf_dest_render_stars(float $rating): string
    {
        $html = '<div class="dest-stars">';
        for ($i = 1; $i <= 5; $i++) {
            if ($rating >= $i) {
                $html .= '<svg class="dest-star dest-star--full" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
            } else {
                $html .= '<svg class="dest-star dest-star--empty" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
            }
        }
        $html .= '</div>';
        return $html;
    }
}

get_header();

if (have_posts()) {
    while (have_posts()) {
        the_post();

        $destination_id = get_the_ID();
        $parent_id      = (int) wp_get_post_parent_id($destination_id);
        $country        = (string) get_post_meta($destination_id, 'destination_country', true);
        $thumb_url      = (string) get_the_post_thumbnail_url($destination_id, 'full');
        $main_content   = trim((string) get_the_content());

        // Info section meta
        $dest_about    = (string) get_post_meta($destination_id, 'aatf_dest_about', true);
        $dest_geo      = (string) get_post_meta($destination_id, 'aatf_dest_geography', true);
        $dest_history  = (string) get_post_meta($destination_id, 'aatf_dest_history', true);
        $dest_lang     = (string) get_post_meta($destination_id, 'aatf_dest_language_dress', true);
        $dest_economy  = (string) get_post_meta($destination_id, 'aatf_dest_economy', true);

        // Build info tabs array (only non-empty sections)
        $dest_title    = get_the_title();
        $info_sections = array();
        if ($dest_about !== '')
            $info_sections['about']     = array('label' => 'About ' . $dest_title, 'icon' => '🌏', 'content' => $dest_about);
        if ($dest_geo !== '')
            $info_sections['geography'] = array('label' => 'Geography & Climate', 'icon' => '🏔️', 'content' => $dest_geo);
        if ($dest_history !== '')
            $info_sections['history']   = array('label' => 'History', 'icon' => '📜', 'content' => $dest_history);
        if ($dest_lang !== '')
            $info_sections['language']  = array('label' => 'Language & Dress', 'icon' => '🎎', 'content' => $dest_lang);
        if ($dest_economy !== '')
            $info_sections['economy']   = array('label' => 'Economy', 'icon' => '💼', 'content' => $dest_economy);

        // Children / sibling destinations
        $children = get_posts(array(
            'post_type'      => 'destination',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'post_parent'    => $destination_id,
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
        ));

        $related_destinations       = $children;
        $related_destinations_title = 'Sub Destinations';
        if (empty($related_destinations) && $parent_id > 0) {
            $related_destinations = get_posts(array(
                'post_type'      => 'destination',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'post_parent'    => $parent_id,
                'post__not_in'   => array($destination_id),
                'orderby'        => 'menu_order title',
                'order'          => 'ASC',
            ));
            $pt                         = get_the_title($parent_id);
            $related_destinations_title = $pt ? 'More in ' . $pt : 'Related Destinations';
        }

        // Related treks query
        $descendants = get_pages(array(
            'post_type'   => 'destination',
            'child_of'    => $destination_id,
            'sort_column' => 'menu_order,post_title',
            'sort_order'  => 'ASC',
        ));

        $related_destination_ids = array($destination_id);
        if (!empty($descendants)) {
            $descendant_ids          = array_map('absint', wp_list_pluck($descendants, 'ID'));
            $related_destination_ids = array_values(array_unique(array_merge($related_destination_ids, $descendant_ids)));
        }

        $trek_meta_query = array('relation' => 'OR');
        foreach ($related_destination_ids as $rid) {
            if ($rid <= 0) continue;
            $trek_meta_query[] = array(
                'key'     => 'trek_destination_ids',
                'value'   => 'i:' . $rid . ';',
                'compare' => 'LIKE',
            );
        }

        $related_treks = null;
        if (count($trek_meta_query) > 1) {
            $related_treks = new WP_Query(array(
                'post_type'      => 'trek',
                'post_status'    => 'publish',
                'posts_per_page' => 12,
                'meta_query'     => $trek_meta_query,
                'meta_key'       => 'trek_display_order',
                'orderby'        => array('meta_value_num' => 'ASC', 'title' => 'ASC'),
                'order'          => 'ASC',
                'no_found_rows'  => true,
            ));
        }

        ?>
        <div class="dest-page">

            <!-- ── Hero ─────────────────────────────────────────── -->
            <section class="dest-hero<?php echo $thumb_url ? ' dest-hero--has-image' : ''; ?>"
                     <?php if ($thumb_url) : ?>style="--dest-hero-bg: url('<?php echo esc_url($thumb_url); ?>')"<?php endif; ?>>
                <div class="dest-hero__overlay">
                    <div class="dest-hero__inner">

                        <nav class="dest-breadcrumb" aria-label="Breadcrumb">
                            <a href="<?php echo esc_url(get_post_type_archive_link('destination') ?: home_url('/')); ?>">Destinations</a>
                            <?php if ($parent_id > 0) : ?>
                                <span class="dest-breadcrumb__sep" aria-hidden="true">›</span>
                                <a href="<?php echo esc_url(get_permalink($parent_id)); ?>"><?php echo esc_html(get_the_title($parent_id)); ?></a>
                            <?php endif; ?>
                            <span class="dest-breadcrumb__sep" aria-hidden="true">›</span>
                            <span><?php the_title(); ?></span>
                        </nav>

                        <h1 class="dest-hero__title"><?php the_title(); ?></h1>

                        <?php if ($country !== '') : ?>
                            <span class="dest-hero__country">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                                <?php echo esc_html($country); ?>
                            </span>
                        <?php endif; ?>

                    </div>
                </div>
            </section>

            <!-- ── Info Tabs ─────────────────────────────────────── -->
            <?php if (!empty($info_sections) || $main_content !== '') : ?>
            <div class="dest-body">
                <div class="dest-body__inner">

                    <?php if (!empty($info_sections)) : ?>
                    <div class="dest-info-sections">
                        <?php foreach ($info_sections as $key => $sec) : ?>
                        <div class="dest-info-section" id="dest-section-<?php echo esc_attr($key); ?>">
                            <div class="dest-info-section__header">
                                <span class="dest-info-section__icon" aria-hidden="true"><?php echo $sec['icon']; ?></span>
                                <h2 class="dest-info-section__title"><?php echo esc_html($sec['label']); ?></h2>
                            </div>
                            <div class="dest-info-section__content">
                                <?php echo wp_kses_post($sec['content']); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($main_content !== '') : ?>
                    <div class="dest-wp-content">
                        <?php the_content(); ?>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
            <?php endif; ?>

            <!-- ── Sub / Related Destinations ───────────────────── -->
            <?php if (!empty($related_destinations)) : ?>
            <section class="dest-related-destinations">
                <div class="dest-section-inner">
                    <h2 class="dest-section-title"><?php echo esc_html($related_destinations_title); ?></h2>
                    <div class="aatf-destination-grid">
                        <?php foreach ($related_destinations as $rd) :
                            $rid   = (int) $rd->ID;
                            $rthumb = get_the_post_thumbnail_url($rid, 'large');
                            $rcountry = (string) get_post_meta($rid, 'destination_country', true);
                        ?>
                        <div class="aatf-destination-card">
                            <?php if ($rthumb) : ?>
                            <a href="<?php echo esc_url(get_permalink($rid)); ?>" tabindex="-1" aria-hidden="true">
                                <img src="<?php echo esc_url($rthumb); ?>" alt="<?php echo esc_attr(get_the_title($rid)); ?>" loading="lazy">
                            </a>
                            <?php endif; ?>
                            <div class="aatf-destination-card__content">
                                <h3><a href="<?php echo esc_url(get_permalink($rid)); ?>"><?php echo esc_html(get_the_title($rid)); ?></a></h3>
                                <?php if ($rcountry !== '') : ?>
                                <p class="aatf-destination-card__country"><?php echo esc_html($rcountry); ?></p>
                                <?php endif; ?>
                                <a href="<?php echo esc_url(get_permalink($rid)); ?>" class="aatf-btn">Explore</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <?php endif; ?>

            <!-- ── Related Tours ─────────────────────────────────── -->
            <?php if ($related_treks instanceof WP_Query && $related_treks->have_posts()) : ?>
            <section class="dest-tours">
                <div class="dest-section-inner">
                    <div class="dest-tours__header">
                        <p class="dest-tours__eyebrow">Handpicked Adventures</p>
                        <h2 class="dest-section-title">Tours in <?php the_title(); ?></h2>
                    </div>
                    <div class="dest-tour-grid">
                        <?php
                        while ($related_treks->have_posts()) :
                            $related_treks->the_post();
                            $t_id       = get_the_ID();
                            $t_thumb    = get_the_post_thumbnail_url($t_id, 'medium_large');
                            if (!$t_thumb) $t_thumb = 'https://images.unsplash.com/photo-1553856622-d1b352e9a211?w=600&q=80';
                            $t_price    = aatf_dest_get_first_meta_value($t_id, array('trek_price', '_price', 'price'));
                            $t_days     = aatf_dest_get_first_meta_value($t_id, array('trek_duration', '_duration_days', 'duration_days', 'duration'));
                            $t_guests   = aatf_dest_get_first_meta_value($t_id, array('trek_group_size', '_max_group_size', 'max_group_size', 'group_size'));
                            $t_rating   = (float) aatf_dest_get_first_meta_value($t_id, array('trek_average_rating', '_average_rating', 'trek_rating', '_rating', 'rating'));
                            $t_location = aatf_dest_get_trek_location_label($t_id);
                        ?>
                        <div class="dest-tour-card">
                            <div class="dest-tour-card__img-wrap">
                                <a href="<?php echo esc_url(get_permalink($t_id)); ?>" tabindex="-1" aria-hidden="true">
                                    <img src="<?php echo esc_url($t_thumb); ?>"
                                         alt="<?php echo esc_attr(get_the_title($t_id)); ?>"
                                         class="dest-tour-card__img" loading="lazy">
                                </a>
                            </div>
                            <div class="dest-tour-card__body">
                                <div class="dest-tour-card__top">
                                    <?php if ($t_rating > 0) : ?>
                                    <div class="dest-tour-card__stars">
                                        <?php echo aatf_dest_render_stars($t_rating); ?>
                                        <span class="dest-tour-card__rating-val"><?php echo esc_html(number_format($t_rating, 1)); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <h3 class="dest-tour-card__title">
                                        <a href="<?php echo esc_url(get_permalink($t_id)); ?>"><?php echo esc_html(get_the_title($t_id)); ?></a>
                                    </h3>
                                    <?php if ($t_location) : ?>
                                    <div class="dest-tour-card__location">
                                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                        <?php echo esc_html($t_location); ?>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($t_price) : ?>
                                    <p class="dest-tour-card__price-line">
                                        From <span class="dest-tour-card__price"><?php echo esc_html(is_numeric($t_price) ? '$' . number_format_i18n((float) $t_price, 0) : $t_price); ?></span>
                                    </p>
                                    <?php endif; ?>
                                </div>
                                <div class="dest-tour-card__footer">
                                    <div class="dest-tour-card__meta">
                                        <?php if ($t_days) : ?>
                                        <span class="dest-tour-card__meta-item">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/></svg>
                                            <?php echo esc_html($t_days); ?> days
                                        </span>
                                        <?php endif; ?>
                                        <?php if ($t_guests) : ?>
                                        <span class="dest-tour-card__meta-item">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <?php echo esc_html($t_guests); ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="<?php echo esc_url(get_permalink($t_id)); ?>" class="dest-tour-card__explore">
                                        Explore
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                </div>
            </section>
            <?php endif; ?>


        </div><!-- .dest-page -->


        <?php
    }
}

get_footer();
