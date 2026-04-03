<?php

if (!defined('ABSPATH')) {
    exit;
}

class AATF_Frontend_Components
{
    public static function register()
    {
        add_shortcode('aatf_global_header', array(__CLASS__, 'render_global_header'));
        add_shortcode('aatf_global_footer', array(__CLASS__, 'render_global_footer'));
        add_shortcode('aatf_trek_card', array(__CLASS__, 'render_trek_card'));
        add_shortcode('aatf_trek_cards', array(__CLASS__, 'render_trek_cards'));
        add_shortcode('aatf_trek_faq', array(__CLASS__, 'render_trek_faq'));
        add_shortcode('aatf_trek_group_pricing', array(__CLASS__, 'render_trek_group_pricing'));
        add_shortcode('aatf_trek_trip_cost', array(__CLASS__, 'render_trek_trip_cost'));
        add_shortcode('aatf_trek_itinerary', array(__CLASS__, 'render_trek_itinerary'));
        add_shortcode('aatf_booking_form', array(__CLASS__, 'render_booking_form'));
        add_shortcode('aatf_destinations', array(__CLASS__, 'render_destinations'));
        add_shortcode('aatf_testimonials', array(__CLASS__, 'render_testimonials'));
        add_shortcode('aatf_trek_simple_html', array(__CLASS__, 'render_trek_simple_html'));
        add_shortcode('aatf_all_treks_content', array(__CLASS__, 'render_all_treks_content'));
    }

    public static function render_global_header($atts)
    {
        $defaults = self::get_company_defaults();
        $atts = shortcode_atts(array(
            'company_name' => $defaults['company_name'],
            'phone' => $defaults['phone'],
            'email' => $defaults['email'],
            'cta_label' => 'Plan Your Trek',
            'cta_url' => home_url('/booking/'),
        ), $atts, 'aatf_global_header');

        $company_name = esc_html($atts['company_name']);
        $phone = esc_html($atts['phone']);
        $email = sanitize_email($atts['email']);
        $cta_label = esc_html($atts['cta_label']);
        $cta_url = esc_url($atts['cta_url']);

        ob_start();
        ?>
        <header class="aatf-header">
            <div class="aatf-shell aatf-header__inner">
                <div class="aatf-brand"><?php echo $company_name; ?></div>
                <div class="aatf-header__meta">
                    <?php if (!empty($phone)) : ?>
                        <span class="aatf-header__item">Call: <?php echo $phone; ?></span>
                    <?php endif; ?>
                    <?php if (!empty($email)) : ?>
                        <span class="aatf-header__item">Email: <?php echo esc_html($email); ?></span>
                    <?php endif; ?>
                </div>
                <a class="aatf-btn aatf-btn--cta" href="<?php echo $cta_url; ?>"><?php echo $cta_label; ?></a>
            </div>
        </header>
        <?php
        return (string) ob_get_clean();
    }

    public static function render_trek_group_pricing($atts)
    {
        $atts = shortcode_atts(array(
            'trek_id' => 0,
            'title' => 'Group Discount',
            'wrap' => 'panel',
        ), $atts, 'aatf_trek_group_pricing');

        $trek_id = self::resolve_trek_id($atts);
        if ($trek_id <= 0) {
            return '';
        }

        $group_pricing_rows = self::get_group_pricing_rows($trek_id);
        if (empty($group_pricing_rows)) {
            return '';
        }

        ob_start();

        $use_panel_wrap = strtolower((string) $atts['wrap']) !== 'none';

        if ($use_panel_wrap) {
            echo '<section class="aatf-panel aatf-single-trek__group-pricing">';
        } else {
            echo '<div class="aatf-single-trek__group-pricing aatf-single-trek__group-pricing--compact">';
        }
        ?>
        <h3><?php echo esc_html($atts['title']); ?></h3>
        <table class="aatf-group-pricing-table">
            <thead>
                <tr>
                    <th>No. of People</th>
                    <th>Price Per Person</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($group_pricing_rows as $row) : ?>
                    <?php
                    $min_people = isset($row['min_people']) ? (int) $row['min_people'] : 0;
                    $max_people = isset($row['max_people']) ? (int) $row['max_people'] : 0;
                    $price_per_person = isset($row['price_per_person']) ? (float) $row['price_per_person'] : 0.0;

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

                    if ($price_per_person > 0) {
                        $price_decimals = (floor($price_per_person) === $price_per_person) ? 0 : 2;
                        $price_label = 'USD ' . number_format_i18n($price_per_person, $price_decimals);
                    } else {
                        $price_label = 'Price on request';
                    }
                    ?>
                    <tr>
                        <td><?php echo esc_html($range_label); ?></td>
                        <td><?php echo esc_html($price_label); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php

        echo $use_panel_wrap ? '</section>' : '</div>';

        return (string) ob_get_clean();
    }

    public static function render_trek_trip_cost($atts)
    {
        $atts = shortcode_atts(array(
            'trek_id' => 0,
            'title' => 'Trip Cost',
            'includes_title' => 'Cost Includes',
            'excludes_title' => 'Cost Excludes',
        ), $atts, 'aatf_trek_trip_cost');

        $trek_id = self::resolve_trek_id($atts);
        if ($trek_id <= 0) {
            return '';
        }

        $includes = self::split_lines((string) get_post_meta($trek_id, 'trek_cost_includes', true));
        $excludes = self::split_lines((string) get_post_meta($trek_id, 'trek_cost_excludes', true));

        if (empty($includes) && empty($excludes)) {
            return '';
        }

        ob_start();
        ?>
        <section class="aatf-panel aatf-single-trek__trip-cost">
            <h3><?php echo esc_html($atts['title']); ?></h3>

            <?php if (!empty($includes)) : ?>
                <h4><?php echo esc_html($atts['includes_title']); ?></h4>
                <ul>
                    <?php foreach ($includes as $item) : ?>
                        <li><?php echo esc_html($item); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (!empty($excludes)) : ?>
                <h4><?php echo esc_html($atts['excludes_title']); ?></h4>
                <ul>
                    <?php foreach ($excludes as $item) : ?>
                        <li><?php echo esc_html($item); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
        <?php

        return (string) ob_get_clean();
    }

    public static function render_trek_itinerary($atts)
    {
        $atts = shortcode_atts(array(
            'trek_id' => 0,
            'title' => 'Itinerary',
        ), $atts, 'aatf_trek_itinerary');

        $trek_id = self::resolve_trek_id($atts);
        if ($trek_id <= 0) {
            return '';
        }

        $itinerary = get_post_meta($trek_id, 'trek_itinerary', true);
        $itinerary = is_array($itinerary) ? $itinerary : array();
        $clean_rows = array();

        foreach ($itinerary as $row) {
            if (!is_array($row)) {
                continue;
            }

            $day = isset($row['day']) ? (string) $row['day'] : '';
            $title = isset($row['title']) ? (string) $row['title'] : '';
            $description = isset($row['description']) ? (string) $row['description'] : '';

            if ($day === '' && $title === '' && $description === '') {
                continue;
            }

            $clean_rows[] = array(
                'day' => $day,
                'title' => $title,
                'description' => $description,
            );
        }

        if (empty($clean_rows)) {
            return '';
        }

        ob_start();
        ?>
        <section class="aatf-panel aatf-single-trek__itinerary">
            <?php if ($atts['title'] !== '') : ?>
                <div class="aatf-section-header mb-5">
                    <div class="aatf-section-header__icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                    </div>
                    <h2 class="aatf-section-header__title"><?php echo esc_html($atts['title']); ?></h2>
                </div>
            <?php endif; ?>
            <div class="aatf-itinerary-accordion">
                <?php
                $total_rows = count($clean_rows);
                foreach ($clean_rows as $idx => $row) :
                    $day = isset($row['day']) ? (string) $row['day'] : '';
                    $title = isset($row['title']) ? (string) $row['title'] : '';
                    $description = isset($row['description']) ? (string) $row['description'] : '';
                    $heading = trim($day . ($title !== '' ? ' - ' . $title : ''));
                    $item_classes = 'aatf-itinerary-stop';
                    if ($idx === 0) {
                        $item_classes .= ' is-first';
                    }
                    if ($idx === ($total_rows - 1)) {
                        $item_classes .= ' is-last';
                    }
                ?>
                <div class="<?php echo esc_attr($item_classes); ?>">
                    <div class="aatf-itinerary-stop__marker" aria-hidden="true">
                        <span class="aatf-itinerary-stop__dot"></span>
                    </div>
                    <div class="aatf-itinerary-stop__card border border-gray-200 rounded-lg overflow-hidden">
                        <button type="button"
                            class="aatf-itinerary-acc-btn w-full flex items-center justify-between px-5 py-3.5 font-semibold text-sm text-left bg-white"
                            style="color: var(--brand-dark);"
                            aria-expanded="false">
                            <?php echo esc_html($heading); ?>
                            <svg class="chevron w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="aatf-itinerary-acc-body px-5 py-4 text-sm leading-relaxed hidden" style="color: var(--brand-gray);">
                            <?php if ($description !== '') : ?>
                                <?php echo nl2br(esc_html($description)); ?>
                            <?php else : ?>
                                <em>No description provided.</em>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php

        return (string) ob_get_clean();
    }

    public static function render_global_footer($atts)
    {
        $defaults = self::get_company_defaults();
        $atts = shortcode_atts(array(
            'company_name' => $defaults['company_name'],
            'tagline' => 'Trusted trekking adventures in Nepal and beyond.',
            'phone' => $defaults['phone'],
            'email' => $defaults['email'],
            'copyright' => '',
        ), $atts, 'aatf_global_footer');

        $company_name = esc_html($atts['company_name']);
        $tagline = esc_html($atts['tagline']);
        $phone = esc_html($atts['phone']);
        $email = sanitize_email($atts['email']);

        $copyright = trim((string) $atts['copyright']);
        if ($copyright === '') {
            $copyright = sprintf('(c) %s %s. All rights reserved.', gmdate('Y'), $company_name);
        }

        ob_start();
        ?>
        <footer class="aatf-footer">
            <div class="aatf-shell aatf-footer__inner">
                <div>
                    <p class="aatf-footer__brand"><?php echo $company_name; ?></p>
                    <p class="aatf-footer__tagline"><?php echo $tagline; ?></p>
                </div>
                <div class="aatf-footer__contact">
                    <?php if (!empty($phone)) : ?><p><?php echo $phone; ?></p><?php endif; ?>
                    <?php if (!empty($email)) : ?><p><?php echo esc_html($email); ?></p><?php endif; ?>
                </div>
            </div>
            <div class="aatf-shell aatf-footer__legal"><?php echo esc_html($copyright); ?></div>
        </footer>
        <?php
        return (string) ob_get_clean();
    }

    public static function render_booking_form($atts)
    {
        $atts = shortcode_atts(array(
            'trek_id' => 0,
            'departure_id' => 0,
            'title' => 'Book This Trek',
            'subtitle' => 'Fill in your details and we will confirm availability shortly.',
            'submit_label' => 'Send Booking Request',
        ), $atts, 'aatf_booking_form');

        $trek_id = isset($atts['trek_id']) ? (int) $atts['trek_id'] : 0;
        if ($trek_id <= 0 && isset($_GET['trek_id'])) {
            $trek_id = absint(wp_unslash($_GET['trek_id']));
        }
        if ($trek_id <= 0) {
            $post = get_post();
            if ($post && $post->post_type === 'trek') {
                $trek_id = (int) $post->ID;
            }
        }

        $departure_id = isset($atts['departure_id']) ? (int) $atts['departure_id'] : 0;
        if ($departure_id <= 0 && isset($_GET['departure_id'])) {
            $departure_id = absint(wp_unslash($_GET['departure_id']));
        }

        $travelers = isset($_GET['travelers']) ? absint(wp_unslash($_GET['travelers'])) : 1;
        if ($travelers <= 0) {
            $travelers = 1;
        }

        $treks = get_posts(array(
            'post_type' => 'trek',
            'post_status' => 'publish',
            'numberposts' => -1,
            'meta_key' => 'trek_display_order',
            'orderby' => array(
                'meta_value_num' => 'ASC',
                'title' => 'ASC',
            ),
            'order' => 'ASC',
        ));

        if (empty($treks)) {
            return '<p>No treks available for booking right now.</p>';
        }

        $trek_ids = array_map('intval', wp_list_pluck($treks, 'ID'));
        if (!in_array($trek_id, $trek_ids, true)) {
            $trek_id = (int) $trek_ids[0];
        }

        $departures_by_trek = self::get_departure_options_for_treks($trek_ids);
        $selected_trek_departures = isset($departures_by_trek[$trek_id]) && is_array($departures_by_trek[$trek_id]) ? $departures_by_trek[$trek_id] : array();
        $selected_departure_meta = null;
        $default_date = '';

        if ($departure_id > 0) {
            foreach ($selected_trek_departures as $candidate) {
                if (!is_array($candidate)) {
                    continue;
                }

                if (isset($candidate['id']) && (int) $candidate['id'] === $departure_id) {
                    $selected_departure_meta = $candidate;
                    break;
                }
            }
        }

        if (!is_array($selected_departure_meta)) {
            $departure_id = 0;
        } else {
            $default_date = isset($selected_departure_meta['start_date']) ? (string) $selected_departure_meta['start_date'] : '';
        }

        $trek_base_prices = array();
        $group_pricing_map = array();
        foreach ($trek_ids as $current_trek_id) {
            $trek_base_prices[(string) $current_trek_id] = (float) get_post_meta($current_trek_id, 'trek_price', true);
            $group_pricing_map[(string) $current_trek_id] = self::get_group_pricing_rows($current_trek_id);
        }

        $pricing_payload = array(
            'trek_base_prices' => $trek_base_prices,
            'group_pricing' => $group_pricing_map,
            'departures' => $departures_by_trek,
        );
        $pricing_json = wp_json_encode($pricing_payload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

        $selected_trek_title = get_the_title($trek_id);
        $selected_trek_title = is_string($selected_trek_title) && $selected_trek_title !== '' ? $selected_trek_title : 'Selected trek';

        $initial_estimate = self::calculate_booking_estimate($trek_id, $departure_id, $travelers);
        $initial_per_person = isset($initial_estimate['price_per_person']) ? (float) $initial_estimate['price_per_person'] : 0.0;
        $initial_total = isset($initial_estimate['total_price']) ? (float) $initial_estimate['total_price'] : 0.0;
        $initial_source = isset($initial_estimate['source_label']) ? (string) $initial_estimate['source_label'] : 'Price on request';
        $initial_group_rows = isset($group_pricing_map[(string) $trek_id]) && is_array($group_pricing_map[(string) $trek_id])
            ? $group_pricing_map[(string) $trek_id]
            : array();
        $has_initial_group_rows = !empty($initial_group_rows);

        if ($initial_per_person > 0) {
            $initial_per_person_label = 'USD ' . number_format_i18n($initial_per_person, (floor($initial_per_person) === $initial_per_person ? 0 : 2));
        } else {
            $initial_per_person_label = 'Price on request';
        }

        if ($initial_total > 0) {
            $initial_total_label = 'USD ' . number_format_i18n($initial_total, (floor($initial_total) === $initial_total ? 0 : 2));
        } else {
            $initial_total_label = 'Price on request';
        }

        $form_uid = function_exists('wp_unique_id') ? (string) wp_unique_id('aatf-booking-') : ('aatf-booking-' . wp_rand(1000, 9999));
        $trek_input_id = $form_uid . '-trek';
        $departure_input_id = $form_uid . '-departure';
        $date_input_id = $form_uid . '-date';
        $travelers_input_id = $form_uid . '-travelers';
        $message_input_id = $form_uid . '-message';
        $selected_departure_label = 'Any available departure';
        if (is_array($selected_departure_meta) && isset($selected_departure_meta['label']) && (string) $selected_departure_meta['label'] !== '') {
            $selected_departure_label = (string) $selected_departure_meta['label'];
        }

        ob_start();
        ?>
        <section class="aatf-booking">
            <div class="aatf-booking__intro">
                <div class="aatf-booking__eyebrow">Adventure Booking</div>
                <h2 class="aatf-booking__title"><?php echo esc_html($atts['title']); ?></h2>
                <?php if ((string) $atts['subtitle'] !== '') : ?>
                    <p class="aatf-booking__subtitle"><?php echo esc_html((string) $atts['subtitle']); ?></p>
                <?php endif; ?>
            </div>

            <form
                class="aatf-booking-form"
                data-aatf-booking-form
                data-form-uid="<?php echo esc_attr($form_uid); ?>"
                data-initial-trek="<?php echo esc_attr((string) $trek_id); ?>"
                data-initial-departure="<?php echo esc_attr((string) $departure_id); ?>"
                method="post"
                action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
            >
                <input type="hidden" name="action" value="aatf_submit_booking" />
                <input type="hidden" name="security" value="<?php echo esc_attr(wp_create_nonce('aatf_booking_nonce')); ?>" />

                <div class="aatf-booking-form__layout">
                    <div class="aatf-booking-form__main">
                        <div class="aatf-booking-form__section">
                            <div class="aatf-booking-form__section-head">
                                <h3 class="aatf-booking-form__section-title">Trip details</h3>
                                <p class="aatf-booking-form__section-copy">Choose your trek, select a departure date, and set your group size before continuing.</p>
                            </div>
                            <div class="aatf-booking-form__grid">
                                <p class="aatf-booking-form__field">
                                    <label for="<?php echo esc_attr($trek_input_id); ?>" class="aatf-booking-form__label">Trek</label>
                                    <select id="<?php echo esc_attr($trek_input_id); ?>" name="trek_id" required>
                                        <?php foreach ($treks as $trek) : ?>
                                            <option value="<?php echo esc_attr((string) $trek->ID); ?>" <?php selected((int) $trek->ID, $trek_id); ?>>
                                                <?php echo esc_html((string) $trek->post_title); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </p>
                                <p class="aatf-booking-form__field">
                                    <label for="<?php echo esc_attr($departure_input_id); ?>" class="aatf-booking-form__label">Departure</label>
                                    <select id="<?php echo esc_attr($departure_input_id); ?>" name="departure_id" data-aatf-departure-select required>
                                        <option value="">Select a departure date</option>
                                        <?php foreach ($selected_trek_departures as $departure_option) : ?>
                                            <?php
                                            $departure_option_id = isset($departure_option['id']) ? (int) $departure_option['id'] : 0;
                                            $departure_option_label = isset($departure_option['label']) ? (string) $departure_option['label'] : '';
                                            ?>
                                            <option value="<?php echo esc_attr((string) $departure_option_id); ?>" <?php selected($departure_option_id, $departure_id); ?>>
                                                <?php echo esc_html($departure_option_label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </p>
                                <p class="aatf-booking-form__field">
                                    <label for="<?php echo esc_attr($date_input_id); ?>" class="aatf-booking-form__label">Preferred date</label>
                                    <input type="date" id="<?php echo esc_attr($date_input_id); ?>" name="date" value="<?php echo esc_attr($default_date); ?>" required />
                                </p>
                                <p class="aatf-booking-form__field">
                                    <label for="<?php echo esc_attr($travelers_input_id); ?>" class="aatf-booking-form__label">Travelers</label>
                                    <input type="number" id="<?php echo esc_attr($travelers_input_id); ?>" name="travelers" min="1" step="1" value="<?php echo esc_attr((string) $travelers); ?>" required />
                                </p>
                            </div>

                            <div class="aatf-booking-form__step-actions">
                                <button type="button" class="aatf-booking-form__theme-cta aatf-booking-form__continue w-full text-white font-bold text-sm tracking-widest uppercase py-3.5 rounded-lg hover:opacity-90 transition-opacity flex items-center justify-center gap-2 no-underline" style="background-color: var(--brand-orange);" data-aatf-continue-button>Continue</button>
                                <p class="aatf-booking-form__hint">We will open one traveler detail form for each person in your group.</p>
                            </div>
                        </div>

                        <div class="aatf-booking-form__section aatf-booking-form__section--travelers" data-aatf-travelers-section hidden>
                            <div class="aatf-booking-form__section-head">
                                <h3 class="aatf-booking-form__section-title">Traveler details</h3>
                                <p class="aatf-booking-form__section-copy">Add the details for each traveler. The first traveler will be treated as the lead contact.</p>
                            </div>
                            <div class="aatf-booking-form__travelers-list" data-aatf-travelers-list></div>
                            <div class="aatf-booking-form__grid">
                                <p class="aatf-booking-form__field aatf-booking-form__field--span">
                                    <label for="<?php echo esc_attr($message_input_id); ?>" class="aatf-booking-form__label">Message <span class="aatf-booking-form__optional">(optional)</span></label>
                                    <textarea id="<?php echo esc_attr($message_input_id); ?>" name="message" rows="4" placeholder="Tell us anything helpful about your plans, pace, room sharing, or special requests."></textarea>
                                </p>
                            </div>
                            <div class="aatf-booking-form__step-actions aatf-booking-form__step-actions--between">
                                <button type="button" class="aatf-booking-form__back" data-aatf-back-button>Back</button>
                                <p class="aatf-booking-form__hint">Traveler 1 should be the main contact for this booking.</p>
                            </div>
                        </div>

                        <div class="aatf-booking-form__footer">
                            <p class="aatf-booking-form__actions">
                                <button type="submit" class="aatf-booking-form__theme-cta aatf-booking-form__submit w-full text-white font-bold text-sm tracking-widest uppercase py-3.5 rounded-lg hover:opacity-90 transition-opacity flex items-center justify-center gap-2 no-underline" style="background-color: var(--brand-orange);"><?php echo esc_html((string) $atts['submit_label']); ?></button>
                            </p>
                            <p class="aatf-booking-form__status" data-aatf-booking-status aria-live="polite"></p>
                        </div>
                    </div>

                    <aside class="aatf-booking-form__aside">
                        <div class="aatf-booking-form__summary-card">
                            <p class="aatf-booking-form__summary-label">Selected trek</p>
                            <h3 class="aatf-booking-form__summary-title" data-aatf-summary-trek><?php echo esc_html($selected_trek_title); ?></h3>
                            <div class="aatf-booking-form__summary-meta">
                                <span class="aatf-booking-form__summary-pill" data-aatf-summary-departure><?php echo esc_html($selected_departure_label); ?></span>
                                <span class="aatf-booking-form__summary-pill" data-aatf-summary-travelers><?php echo esc_html(sprintf(_n('%s traveler', '%s travelers', $travelers, 'aa-trek-framework'), number_format_i18n($travelers))); ?></span>
                            </div>
                        </div>

                        <div class="aatf-booking-form__estimate" data-aatf-booking-estimate>
                            <p class="aatf-booking-form__card-eyebrow">Live estimate</p>
                            <div class="aatf-booking-form__estimate-row">
                                <span>Price per person</span>
                                <strong data-aatf-estimate-per-person><?php echo esc_html($initial_per_person_label); ?></strong>
                            </div>
                            <div class="aatf-booking-form__estimate-row">
                                <span>Total estimated cost</span>
                                <strong data-aatf-estimate-total><?php echo esc_html($initial_total_label); ?></strong>
                            </div>
                            <div class="aatf-booking-form__estimate-source">
                                <span>Pricing basis</span>
                                <strong data-aatf-estimate-source><?php echo esc_html($initial_source); ?></strong>
                            </div>
                        </div>

                        <div class="aatf-booking-form__group-discount" data-aatf-group-discount-wrap<?php echo $has_initial_group_rows ? '' : ' style="display:none;"'; ?>>
                            <p class="aatf-booking-form__card-eyebrow">Savings for groups</p>
                            <h3>Group discount</h3>
                            <table class="aatf-group-pricing-table aatf-group-pricing-table--booking">
                                <thead>
                                    <tr>
                                        <th>No. of People</th>
                                        <th>Price Per Person</th>
                                    </tr>
                                </thead>
                                <tbody data-aatf-group-discount-body>
                                    <?php foreach ($initial_group_rows as $group_row) : ?>
                                        <?php
                                        $min_people = isset($group_row['min_people']) ? (int) $group_row['min_people'] : 0;
                                        $max_people = isset($group_row['max_people']) ? (int) $group_row['max_people'] : 0;
                                        $price_per_person = isset($group_row['price_per_person']) ? (float) $group_row['price_per_person'] : 0.0;

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

                                        if ($price_per_person > 0) {
                                            $price_decimals = (floor($price_per_person) === $price_per_person) ? 0 : 2;
                                            $price_label = 'USD ' . number_format_i18n($price_per_person, $price_decimals);
                                        } else {
                                            $price_label = 'Price on request';
                                        }
                                        ?>
                                        <tr>
                                            <td><?php echo esc_html($range_label); ?></td>
                                            <td><?php echo esc_html($price_label); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </aside>
                </div>
            </form>
            <script type="application/json" class="aatf-booking-form__pricing-data"><?php echo $pricing_json !== false ? $pricing_json : '{}'; ?></script>
        </section>
        <?php

        return (string) ob_get_clean();
    }

    public static function render_trek_card($atts)
    {
        $atts = shortcode_atts(array(
            'id' => 0,
            'cta_label' => 'View Trek',
        ), $atts, 'aatf_trek_card');

        $trek_id = (int) $atts['id'];
        if ($trek_id <= 0) {
            $post = get_post();
            if ($post && $post->post_type === 'trek') {
                $trek_id = (int) $post->ID;
            }
        }

        if ($trek_id <= 0 || get_post_type($trek_id) !== 'trek') {
            return '';
        }

        $title = get_the_title($trek_id);
        $url = get_permalink($trek_id);
        $duration = get_post_meta($trek_id, 'trek_duration', true);
        $price = (float) get_post_meta($trek_id, 'trek_price', true);
        $difficulty = self::get_first_term_name($trek_id, 'trek_difficulty');
        $region = '';
        $destination_ids = get_post_meta($trek_id, 'trek_destination_ids', true);
        $destination_ids = is_array($destination_ids) ? array_map('absint', $destination_ids) : array();
        if (!empty($destination_ids)) {
            $region = get_the_title((int) $destination_ids[0]);
        }
        if ($region === '') {
            $region = self::get_first_term_name($trek_id, 'trek_region');
        }
        $thumbnail = get_the_post_thumbnail_url($trek_id, 'large');

        ob_start();
        ?>
        <article class="aatf-trek-card">
            <a class="aatf-trek-card__image" href="<?php echo esc_url($url); ?>">
                <?php if ($thumbnail) : ?>
                    <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy" />
                <?php else : ?>
                    <span class="aatf-trek-card__image-placeholder">Trek</span>
                <?php endif; ?>
            </a>
            <div class="aatf-trek-card__body">
                <div class="aatf-badges">
                    <?php echo self::render_price_badge($price); ?>
                    <?php echo self::render_difficulty_badge($difficulty); ?>
                </div>
                <h3 class="aatf-trek-card__title"><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($title); ?></a></h3>
                <p class="aatf-trek-card__meta"><?php echo esc_html($region); ?><?php echo $duration ? ' | ' . esc_html($duration) . ' days' : ''; ?></p>
                <?php echo self::render_cta_button($atts['cta_label'], $url); ?>
            </div>
        </article>
        <?php
        return (string) ob_get_clean();
    }

    public static function render_trek_cards($atts)
    {
        $atts = shortcode_atts(array(
            'limit' => 6,
            'featured' => '',
        ), $atts, 'aatf_trek_cards');

        $args = array(
            'post_type' => 'trek',
            'post_status' => 'publish',
            'posts_per_page' => max(1, (int) $atts['limit']),
            'meta_key' => 'trek_display_order',
            'orderby' => array(
                'meta_value_num' => 'ASC',
                'title' => 'ASC',
            ),
            'order' => 'ASC',
        );

        if ($atts['featured'] === 'yes') {
            $args['meta_query'] = array(
                array(
                    'key' => 'trek_featured',
                    'value' => '1',
                    'compare' => '=',
                ),
            );
        }

        $query = new WP_Query($args);

        if (!$query->have_posts()) {
            return '<p>No treks found.</p>';
        }

        ob_start();
        echo '<div class="aatf-trek-grid">';

        while ($query->have_posts()) {
            $query->the_post();
            echo self::render_trek_card(array('id' => get_the_ID()));
        }

        echo '</div>';
        wp_reset_postdata();

        return (string) ob_get_clean();
    }

    public static function render_trek_faq($atts)
    {
        $atts = shortcode_atts(array(
            'trek_id' => 0,
            'title' => 'Frequently Asked Questions',
        ), $atts, 'aatf_trek_faq');

        $trek_id = (int) $atts['trek_id'];
        if ($trek_id <= 0) {
            $post = get_post();
            if ($post && $post->post_type === 'trek') {
                $trek_id = (int) $post->ID;
            }
        }

        if ($trek_id <= 0) {
            return '';
        }

        $faq_query = new WP_Query(array(
            'post_type' => 'trek_faq',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_key' => 'aatf_trek_id',
            'meta_value' => $trek_id,
            'orderby' => 'menu_order title',
            'order' => 'ASC',
        ));

        if (!$faq_query->have_posts()) {
            return '';
        }

        $grouped = array();

        while ($faq_query->have_posts()) {
            $faq_query->the_post();
            $faq_id = get_the_ID();
            $topic_groups = get_post_meta($faq_id, 'aatf_faq_topic_groups', true);
            $topic_groups = is_array($topic_groups) ? $topic_groups : array();

            foreach ($topic_groups as $topic_group) {
                if (!is_array($topic_group)) {
                    continue;
                }

                $topic_id = isset($topic_group['topic_id']) ? (int) $topic_group['topic_id'] : 0;
                $topic_term = $topic_id > 0 ? get_term($topic_id, 'faq_topic') : null;
                $topic_name = ($topic_term && !is_wp_error($topic_term)) ? (string) $topic_term->name : 'General';

                if (!isset($grouped[$topic_name])) {
                    $grouped[$topic_name] = array();
                }

                $faqs = isset($topic_group['faqs']) && is_array($topic_group['faqs']) ? $topic_group['faqs'] : array();
                foreach ($faqs as $faq_row) {
                    if (!is_array($faq_row)) {
                        continue;
                    }

                    $question = isset($faq_row['question']) ? trim((string) $faq_row['question']) : '';
                    $answer = isset($faq_row['answer']) ? trim((string) $faq_row['answer']) : '';
                    if ($question === '' && $answer === '') {
                        continue;
                    }

                    $grouped[$topic_name][] = array(
                        'question' => $question,
                        'answer' => wpautop($answer),
                    );
                }
            }
        }

        wp_reset_postdata();

        $grouped = array_filter($grouped, static function ($items) {
            return is_array($items) && !empty($items);
        });

        if (empty($grouped)) {
            return '';
        }

        $total_faq_count = 0;
        foreach ($grouped as $items) {
            $total_faq_count += is_array($items) ? count($items) : 0;
        }

        ob_start();
        ?>
        <section class="aatf-faq" aria-label="Trek FAQ">
            <div class="aatf-faq__card">
                <div class="aatf-section-header aatf-faq__header">
                    <div class="aatf-section-header__icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 1.813-2 3.272-2 1.933 0 3.5 1.567 3.5 3.5 0 1.355-.77 2.53-1.895 3.112-.73.378-1.355 1.12-1.355 1.888V16" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 19h.01" /><path stroke-linecap="round" stroke-linejoin="round" d="M4.93 4.93A10 10 0 1019.07 19.07 10 10 0 004.93 4.93z" /></svg>
                    </div>
                    <div class="aatf-faq__header-copy">
                        <h2 class="aatf-section-header__title"><?php echo esc_html($atts['title']); ?></h2>
                        <p class="aatf-faq__intro">Helpful answers for planning this trek, permits, logistics, and what to expect on the trail.</p>
                    </div>
                    <span class="aatf-faq__count"><?php echo esc_html((string) $total_faq_count); ?> FAQs</span>
                </div>
                <?php foreach ($grouped as $topic => $items) : ?>
                    <div class="aatf-faq__group">
                        <div class="aatf-faq__group-head">
                            <h3 class="aatf-faq__group-title"><?php echo esc_html($topic); ?></h3>
                            <span class="aatf-faq__group-count"><?php echo esc_html((string) count($items)); ?> items</span>
                        </div>
                        <?php foreach ($items as $index => $item) : ?>
                            <details class="aatf-faq__item">
                                <summary class="aatf-faq__question">
                                    <span class="aatf-faq__question-text"><?php echo esc_html($item['question']); ?></span>
                                    <span class="aatf-faq__question-icon" aria-hidden="true">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                    </span>
                                </summary>
                                <div class="aatf-faq__answer"><?php echo wp_kses_post($item['answer']); ?></div>
                            </details>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
        return (string) ob_get_clean();
    }

    public static function render_destinations($atts)
    {
        $atts = shortcode_atts(array(
            'limit' => -1,
            'featured' => '',
        ), $atts, 'aatf_destinations');

        $args = array(
            'post_type' => 'destination',
            'post_status' => 'publish',
            'posts_per_page' => (int) $atts['limit'],
        );

        if ($atts['featured'] === 'yes') {
            $args['meta_query'] = array(
                array(
                    'key' => 'destination_featured',
                    'value' => '1',
                    'compare' => '=',
                ),
            );
        }

        $query = new WP_Query($args);

        if (!$query->have_posts()) {
            return '<p>No destinations found.</p>';
        }

        ob_start();
        echo '<div class="aatf-destination-grid">';
        while ($query->have_posts()) {
            $query->the_post();
            $dest_id = get_the_ID();
            $country = get_post_meta($dest_id, 'destination_country', true);
            $thumbnail = get_the_post_thumbnail_url($dest_id, 'large');
            ?>
            <div class="aatf-destination-card">
                <?php if ($thumbnail) : ?>
                    <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php the_title_attribute(); ?>" />
                <?php endif; ?>
                <div class="aatf-destination-card__content">
                    <h3><?php the_title(); ?></h3>
                    <?php if ($country) : ?><p class="aatf-destination-card__country"><?php echo esc_html($country); ?></p><?php endif; ?>
                    <a href="<?php the_permalink(); ?>" class="aatf-btn">Explore</a>
                </div>
            </div>
            <?php
        }
        echo '</div>';
        wp_reset_postdata();
        return (string) ob_get_clean();
    }

    public static function render_testimonials($atts)
    {
        $atts = shortcode_atts(array(
            'limit' => 3,
        ), $atts, 'aatf_testimonials');

        $query = new WP_Query(array(
            'post_type' => 'testimonial',
            'post_status' => 'publish',
            'posts_per_page' => (int) $atts['limit'],
        ));

        if (!$query->have_posts()) {
            return '<p>No testimonials found.</p>';
        }

        ob_start();
        echo '<div class="aatf-testimonial-grid">';
        while ($query->have_posts()) {
            $query->the_post();
            $test_id = get_the_ID();
            $location = (string) get_post_meta($test_id, 'testimonial_location', true);
            if ($location === '') {
                // Backward compatibility for old installs that stored location in author role.
                $location = (string) get_post_meta($test_id, 'testimonial_author_role', true);
            }
            $rating = (int) get_post_meta($test_id, 'testimonial_rating', true);
            if ($rating < 1 || $rating > 5) {
                $rating = 5;
            }
            $avatar_html = get_the_post_thumbnail(
                $test_id,
                'thumbnail',
                array(
                    'class' => 'aatf-testimonial-card__avatar-img',
                    'loading' => 'lazy',
                    'alt' => get_the_title($test_id),
                )
            );
            ?>
            <div class="aatf-testimonial-card">
                <div class="aatf-testimonial-card__avatar">
                    <?php if ($avatar_html) : ?>
                        <?php echo $avatar_html; ?>
                    <?php else : ?>
                        <span class="aatf-testimonial-card__avatar-fallback" aria-hidden="true"><?php echo esc_html(strtoupper(substr((string) get_the_title($test_id), 0, 1))); ?></span>
                    <?php endif; ?>
                </div>
                <div class="aatf-testimonial-card__meta">
                    <strong><?php the_title(); ?></strong>
                    <?php if ($location) : ?><span><?php echo esc_html($location); ?></span><?php endif; ?>
                </div>
                <div class="aatf-testimonial-card__rating">
                    <?php for ($i = 1; $i <= 5; $i++) : ?>
                        <span class="<?php echo ($i <= $rating) ? 'is-filled' : 'is-empty'; ?>" aria-hidden="true"><?php echo ($i <= $rating) ? '&#9733;' : '&#9734;'; ?></span>
                    <?php endfor; ?>
                </div>
                <div class="aatf-testimonial-card__content">
                    <?php the_content(); ?>
                </div>
            </div>
            <?php
        }
        echo '</div>';
        wp_reset_postdata();
        return (string) ob_get_clean();
    }

    public static function render_trek_simple_html($atts)
    {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts, 'aatf_trek_simple_html');

        $trek_id = (int) $atts['id'];
        if ($trek_id <= 0) {
            $post = get_post();
            if ($post && $post->post_type === 'trek') {
                $trek_id = (int) $post->ID;
            }
        }

        if ($trek_id <= 0 || get_post_type($trek_id) !== 'trek') {
            return '';
        }

        $slider_ids = get_post_meta($trek_id, 'trek_slider_image_ids', true);
        if (!is_array($slider_ids) || empty($slider_ids)) {
            $slider_ids = get_post_meta($trek_id, 'trek_gallery_ids', true);
        }
        $slider_ids = is_array($slider_ids) ? array_map('absint', $slider_ids) : array();

        $itinerary = get_post_meta($trek_id, 'trek_itinerary', true);
        $itinerary = is_array($itinerary) ? $itinerary : array();

        $duration = (int) get_post_meta($trek_id, 'trek_duration', true);
        $nights = (int) get_post_meta($trek_id, 'trek_duration_nights', true);
        $altitude = (int) get_post_meta($trek_id, 'trek_max_altitude', true);
        $price = (float) get_post_meta($trek_id, 'trek_price', true);
        $group_size = (int) get_post_meta($trek_id, 'trek_group_size', true);
        $group_pricing_rows = self::get_group_pricing_rows($trek_id);
        $start = (string) get_post_meta($trek_id, 'trek_start_location', true);
        $end = (string) get_post_meta($trek_id, 'trek_end_location', true);
        $transport = (string) get_post_meta($trek_id, 'trek_transport', true);
        $accommodation = (string) get_post_meta($trek_id, 'trek_accommodation', true);
        $meal = (string) get_post_meta($trek_id, 'trek_meal_plan', true);
        $booking = (string) get_post_meta($trek_id, 'trek_booking_url', true);
        $overview = (string) get_post_meta($trek_id, 'trek_overview', true);
        $highlights_title = (string) get_post_meta($trek_id, 'trek_highlights_title', true);
        $highlights_intro = (string) get_post_meta($trek_id, 'trek_highlights_intro', true);
        $highlights_text = (string) get_post_meta($trek_id, 'trek_highlights', true);
        $highlights = preg_split("/\r\n|\n|\r/", $highlights_text);
        $highlights = is_array($highlights) ? array_values(array_filter(array_map('trim', $highlights), static function ($item) {
            return $item !== '';
        })) : array();
        $includes = (string) get_post_meta($trek_id, 'trek_cost_includes', true);
        $excludes = (string) get_post_meta($trek_id, 'trek_cost_excludes', true);
        $reviews = (string) get_post_meta($trek_id, 'trek_reviews', true);
        $map_embed = (string) get_post_meta($trek_id, 'trek_map_embed', true);
        $video_url = (string) get_post_meta($trek_id, 'trek_video_url', true);
        $equipment_sections = get_post_meta($trek_id, 'trek_equipment_sections', true);
        $equipment_sections = is_array($equipment_sections) ? $equipment_sections : array();
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
            }
        }
        $featured_image = get_the_post_thumbnail_url($trek_id, 'large');

        ob_start();
        ?>
        <section>
            <h1><?php echo esc_html(get_the_title($trek_id)); ?></h1>
            <p><a href="<?php echo esc_url(get_permalink($trek_id)); ?>"><?php echo esc_html(get_permalink($trek_id)); ?></a></p>

            <?php if ($featured_image) : ?>
                <h2>Featured Image</h2>
                <p><img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr(get_the_title($trek_id)); ?>" /></p>
            <?php endif; ?>

            <h2>Slider Images</h2>
            <?php if (!empty($slider_ids)) : ?>
                <ul>
                    <?php foreach ($slider_ids as $image_id) : ?>
                        <?php $url = wp_get_attachment_image_url($image_id, 'large'); ?>
                        <?php if ($url) : ?>
                            <li><img src="<?php echo esc_url($url); ?>" alt="" /></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p>No slider images.</p>
            <?php endif; ?>

            <h2>Quick Facts</h2>
            <ul>
                <li>Duration (days): <?php echo esc_html((string) $duration); ?></li>
                <li>Duration (nights): <?php echo esc_html((string) $nights); ?></li>
                <li>Max Altitude (m): <?php echo esc_html((string) $altitude); ?></li>
                <li>Price (USD): <?php echo esc_html((string) $price); ?></li>
                <li>Group Size: <?php echo esc_html((string) $group_size); ?></li>
                <li>Start Location: <?php echo esc_html($start); ?></li>
                <li>End Location: <?php echo esc_html($end); ?></li>
                <li>Transport: <?php echo esc_html($transport); ?></li>
                <li>Accommodation: <?php echo esc_html($accommodation); ?></li>
                <li>Meal Plan: <?php echo esc_html($meal); ?></li>
                <li>Booking URL: <?php echo esc_html($booking); ?></li>
            </ul>

            <?php if (!empty($group_pricing_rows)) : ?>
                <h2>Group Discount</h2>
                <table>
                    <thead>
                        <tr>
                            <th>No. of People</th>
                            <th>Price Per Person</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($group_pricing_rows as $row) : ?>
                            <?php
                            $min_people = isset($row['min_people']) ? (int) $row['min_people'] : 0;
                            $max_people = isset($row['max_people']) ? (int) $row['max_people'] : 0;
                            $price_per_person = isset($row['price_per_person']) ? (float) $row['price_per_person'] : 0.0;

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

                            if ($price_per_person > 0) {
                                $price_decimals = (floor($price_per_person) === $price_per_person) ? 0 : 2;
                                $price_label = 'USD ' . number_format_i18n($price_per_person, $price_decimals);
                            } else {
                                $price_label = 'Price on request';
                            }
                            ?>
                            <tr>
                                <td><?php echo esc_html($range_label); ?></td>
                                <td><?php echo esc_html($price_label); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h2>Overview</h2>
            <p><?php echo nl2br(esc_html($overview)); ?></p>

            <?php if ($highlights_title !== '' || $highlights_intro !== '' || !empty($highlights)) : ?>
                <h2><?php echo esc_html($highlights_title !== '' ? $highlights_title : 'Trek Highlights'); ?></h2>
                <?php if ($highlights_intro !== '') : ?>
                    <p><?php echo nl2br(esc_html($highlights_intro)); ?></p>
                <?php endif; ?>
                <?php if (!empty($highlights)) : ?>
                    <ul>
                        <?php foreach ($highlights as $item) : ?>
                            <li><?php echo esc_html($item); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            <?php endif; ?>

            <h2>Cost Includes</h2>
            <p><?php echo nl2br(esc_html($includes)); ?></p>

            <h2>Cost Excludes</h2>
            <p><?php echo nl2br(esc_html($excludes)); ?></p>

            <h2>Itinerary</h2>
            <?php if (!empty($itinerary)) : ?>
                <ol>
                    <?php foreach ($itinerary as $row) : ?>
                        <li>
                            <p><strong><?php echo esc_html(isset($row['day']) ? (string) $row['day'] : ''); ?> - <?php echo esc_html(isset($row['title']) ? (string) $row['title'] : ''); ?></strong></p>
                            <p><?php echo nl2br(esc_html(isset($row['description']) ? (string) $row['description'] : '')); ?></p>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php else : ?>
                <p>No itinerary rows.</p>
            <?php endif; ?>

            <h2>Reviews Summary</h2>
            <p><?php echo nl2br(esc_html($reviews)); ?></p>

            <h2>Map</h2>
            <div><?php echo wp_kses_post($map_embed); ?></div>

            <?php if ($video_embed) : ?>
                <h2>YouTube Video</h2>
                <div><?php echo wp_kses_post($video_embed); ?></div>
            <?php endif; ?>

            <h2>Equipment</h2>
            <?php if (!empty($equipment_sections)) : ?>
                <?php foreach ($equipment_sections as $section) : ?>
                    <?php
                    $heading = isset($section['heading']) ? (string) $section['heading'] : '';
                    $image_id = isset($section['image_id']) ? (int) $section['image_id'] : 0;
                    $items = isset($section['items']) && is_array($section['items']) ? $section['items'] : array();
                    $image_url = $image_id > 0 ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';
                    ?>
                    <div>
                        <?php if ($image_url) : ?><p><img src="<?php echo esc_url($image_url); ?>" alt="" /></p><?php endif; ?>
                        <?php if ($heading !== '') : ?><h3><?php echo esc_html($heading); ?></h3><?php endif; ?>
                        <?php if (!empty($items)) : ?>
                            <ul>
                                <?php foreach ($items as $item) : ?>
                                    <?php if ((string) $item !== '') : ?><li><?php echo esc_html((string) $item); ?></li><?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>No equipment added.</p>
            <?php endif; ?>
        </section>
        <?php

        return (string) ob_get_clean();
    }

    public static function render_all_treks_content($atts)
    {
        $atts = shortcode_atts(array(
            'status' => 'publish',
            'limit' => -1,
        ), $atts, 'aatf_all_treks_content');

        $query = new WP_Query(array(
            'post_type' => 'trek',
            'post_status' => sanitize_text_field((string) $atts['status']),
            'posts_per_page' => (int) $atts['limit'],
            'meta_key' => 'trek_display_order',
            'orderby' => array(
                'meta_value_num' => 'ASC',
                'title' => 'ASC',
            ),
            'order' => 'ASC',
        ));

        if (!$query->have_posts()) {
            return '<p>No treks found.</p>';
        }

        ob_start();
        echo '<div class="aatf-all-treks-content">';

        while ($query->have_posts()) {
            $query->the_post();
            echo '<section data-trek-id="' . esc_attr((string) get_the_ID()) . '">';
            echo '<h2>' . esc_html(get_the_title()) . '</h2>';
            echo apply_filters('the_content', get_the_content());
            echo '</section>';
        }

        echo '</div>';
        wp_reset_postdata();

        return (string) ob_get_clean();
    }

    private static function render_price_badge($price)
    {
        if ($price <= 0) {
            return '<span class="aatf-badge aatf-badge--price">Price on request</span>';
        }

        return '<span class="aatf-badge aatf-badge--price">From $' . esc_html(number_format_i18n($price, 0)) . '</span>';
    }

    private static function render_difficulty_badge($difficulty)
    {
        if ($difficulty === '') {
            $difficulty = 'Standard';
        }

        return '<span class="aatf-badge aatf-badge--difficulty">' . esc_html($difficulty) . '</span>';
    }

    private static function render_cta_button($label, $url)
    {
        return '<a class="aatf-btn aatf-btn--cta" href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
    }

    private static function resolve_trek_id($atts)
    {
        $trek_id = isset($atts['trek_id']) ? (int) $atts['trek_id'] : 0;

        if ($trek_id <= 0) {
            $post = get_post();
            if ($post && $post->post_type === 'trek') {
                $trek_id = (int) $post->ID;
            }
        }

        if ($trek_id <= 0 || get_post_type($trek_id) !== 'trek') {
            return 0;
        }

        return $trek_id;
    }

    private static function split_lines($text)
    {
        $rows = preg_split("/\r\n|\n|\r/", $text);
        if (!is_array($rows)) {
            return array();
        }

        return array_values(array_filter(array_map('trim', $rows), static function ($item) {
            return $item !== '';
        }));
    }

    private static function get_group_pricing_rows($trek_id)
    {
        $group_pricing_raw = get_post_meta($trek_id, 'trek_group_pricing', true);
        $group_pricing_rows = array();

        if (!is_array($group_pricing_raw)) {
            return $group_pricing_rows;
        }

        foreach ($group_pricing_raw as $row) {
            if (!is_array($row)) {
                continue;
            }

            $min_people = isset($row['min_people']) ? absint($row['min_people']) : 0;
            $max_people = isset($row['max_people']) ? absint($row['max_people']) : 0;
            $price_raw = isset($row['price_per_person']) ? (string) $row['price_per_person'] : '';
            $price_raw = str_replace(',', '', $price_raw);
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

        return $group_pricing_rows;
    }

    private static function get_departure_options_for_treks($trek_ids)
    {
        $trek_ids = is_array($trek_ids) ? array_values(array_filter(array_map('absint', $trek_ids))) : array();
        $options = array();

        foreach ($trek_ids as $trek_id) {
            $options[(string) $trek_id] = array();
        }

        if (empty($trek_ids)) {
            return $options;
        }

        $departure_ids = get_posts(array(
            'post_type' => 'departure',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => 'aatf_trek_id',
                    'value' => $trek_ids,
                    'compare' => 'IN',
                    'type' => 'NUMERIC',
                ),
            ),
            'meta_key' => 'departure_start_date',
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'no_found_rows' => true,
        ));

        foreach ($departure_ids as $departure_id) {
            $departure_id = (int) $departure_id;
            if ($departure_id <= 0) {
                continue;
            }

            $trek_id = (int) get_post_meta($departure_id, 'aatf_trek_id', true);
            if ($trek_id <= 0 || !isset($options[(string) $trek_id])) {
                continue;
            }

            $start_raw = (string) get_post_meta($departure_id, 'departure_start_date', true);
            $end_raw = (string) get_post_meta($departure_id, 'departure_end_date', true);
            $departure_price = (float) get_post_meta($departure_id, 'departure_price', true);

            $label = self::format_departure_label($departure_id, $start_raw, $end_raw);

            $options[(string) $trek_id][] = array(
                'id' => $departure_id,
                'label' => $label,
                'price_per_person' => $departure_price > 0 ? $departure_price : 0.0,
                'start_date' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_raw) ? $start_raw : '',
            );
        }

        return $options;
    }

    private static function calculate_booking_estimate($trek_id, $departure_id, $travelers)
    {
        $trek_id = (int) $trek_id;
        $departure_id = (int) $departure_id;
        $travelers = max(1, (int) $travelers);

        $price_per_person = 0.0;
        $source = 'request';
        $source_label = 'Price on request';

        if ($departure_id > 0 && get_post_type($departure_id) === 'departure') {
            $linked_trek = (int) get_post_meta($departure_id, 'aatf_trek_id', true);
            $departure_price = (float) get_post_meta($departure_id, 'departure_price', true);

            if ($linked_trek === $trek_id && $departure_price > 0) {
                $price_per_person = $departure_price;
                $source = 'departure';
                $source_label = 'Departure price';
            }
        }

        if ($price_per_person <= 0) {
            $group_rows = self::get_group_pricing_rows($trek_id);
            $best_match = null;
            foreach ($group_rows as $row) {
                $min_people = isset($row['min_people']) ? (int) $row['min_people'] : 0;
                $max_people = isset($row['max_people']) ? (int) $row['max_people'] : 0;
                $group_price = isset($row['price_per_person']) ? (float) $row['price_per_person'] : 0.0;

                if ($group_price <= 0 || $min_people <= 0) {
                    continue;
                }

                if ($travelers < $min_people) {
                    continue;
                }

                if ($max_people > 0 && $travelers > $max_people) {
                    continue;
                }

                if (!is_array($best_match) || $min_people > (int) $best_match['min_people']) {
                    $best_match = array(
                        'min_people' => $min_people,
                        'max_people' => $max_people,
                        'price_per_person' => $group_price,
                    );
                }
            }

            if (is_array($best_match)) {
                $price_per_person = (float) $best_match['price_per_person'];
                $source = 'group';
                $source_label = 'Group discount';
            }
        }

        if ($price_per_person <= 0) {
            $base_price = (float) get_post_meta($trek_id, 'trek_price', true);
            if ($base_price > 0) {
                $price_per_person = $base_price;
                $source = 'base';
                $source_label = 'Base trek price';
            }
        }

        $total_price = $price_per_person > 0 ? ($price_per_person * $travelers) : 0.0;

        return array(
            'price_per_person' => $price_per_person,
            'total_price' => $total_price,
            'source' => $source,
            'source_label' => $source_label,
        );
    }

    private static function format_departure_label($departure_id, $start_raw, $end_raw)
    {
        $start_ts = $start_raw !== '' ? strtotime($start_raw) : false;
        $end_ts = $end_raw !== '' ? strtotime($end_raw) : false;
        $start_label = $start_ts ? date_i18n(get_option('date_format'), $start_ts) : '';
        $end_label = $end_ts ? date_i18n(get_option('date_format'), $end_ts) : '';

        if ($start_label !== '' && $end_label !== '' && $start_label !== $end_label) {
            return $start_label . ' - ' . $end_label;
        }

        if ($start_label !== '') {
            return $start_label;
        }

        if ($end_label !== '') {
            return $end_label;
        }

        return trim((string) get_the_title((int) $departure_id));
    }

    private static function get_first_term_name($post_id, $taxonomy)
    {
        $terms = get_the_terms($post_id, $taxonomy);
        if (is_wp_error($terms) || empty($terms)) {
            return '';
        }

        return isset($terms[0]->name) ? (string) $terms[0]->name : '';
    }

    private static function get_company_defaults()
    {
        $defaults = array(
            'company_name' => get_bloginfo('name') ?: 'Trek Company',
            'phone' => '+977-0000000000',
            'email' => get_option('admin_email', 'info@example.com'),
        );

        return apply_filters('aatf_company_defaults', $defaults);
    }
}
