<?php

if (!defined('ABSPATH')) {
    exit;
}

class AATF_Header_Menu_Walker extends Walker_Nav_Menu
{
    public function start_lvl(&$output, $depth = 0, $args = null)
    {
        $output .= "\n<ul class=\"sub-menu\">\n";
    }

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;

        $has_children = in_array('menu-item-has-children', $classes, true);
        $is_mega = aatf_is_destinations_mega_item($item, $classes);
        if ($is_mega) {
            $classes[] = 'menu-item-mega';
        }

        $class_names = implode(' ', array_map('sanitize_html_class', array_filter($classes)));
        $output .= '<li class="' . esc_attr($class_names) . '">';

        $atts = array();
        $atts['title'] = !empty($item->attr_title) ? $item->attr_title : '';
        $atts['target'] = !empty($item->target) ? $item->target : '';
        $atts['rel'] = !empty($item->xfn) ? $item->xfn : '';
        $atts['href'] = !empty($item->url) ? $item->url : '';
        $atts['class'] = 'menu-link';

        $attributes = '';
        foreach ($atts as $attr => $value) {
            if ($value !== '') {
                $value = ($attr === 'href') ? esc_url($value) : esc_attr($value);
                $attributes .= ' ' . $attr . '="' . $value . '"';
            }
        }

        $title = apply_filters('the_title', $item->title, $item->ID);
        $title = apply_filters('nav_menu_item_title', $title, $item, $args, $depth);

        $output .= '<a' . $attributes . '>' . esc_html($title) . '</a>';

        if ($has_children) {
            $output .= '<button class="aatf-submenu-toggle" type="button" aria-expanded="false"><span class="screen-reader-text">' . esc_html__('Toggle submenu', 'aatf-expedition-base') . '</span><span aria-hidden="true">+</span></button>';
        }

        if ($is_mega) {
            $output .= aatf_render_destinations_mega_menu();
        }
    }
}

function aatf_is_destinations_mega_item($item, $classes)
{
    $mode = (string) get_theme_mod('aatf_header_destination_menu_mode', 'class');
    if (!in_array($mode, array('class', 'auto', 'off'), true)) {
        $mode = 'class';
    }

    if ($mode === 'off') {
        return false;
    }

    if (in_array('aatf-mega-destinations', $classes, true)) {
        return true;
    }

    if ($mode === 'class') {
        return false;
    }

    $title = strtolower(trim(wp_strip_all_tags((string) $item->title)));
    if ($title === 'destinations' || $title === 'destination') {
        return true;
    }

    $url = (string) $item->url;
    if ($url !== '') {
        $path = (string) wp_parse_url($url, PHP_URL_PATH);
        if ($path !== '' && strpos($path, 'destinations') !== false) {
            return true;
        }
    }

    return false;
}

function aatf_render_destinations_mega_menu()
{
    $limit = absint((int) get_theme_mod('aatf_header_destination_limit', 18));
    if ($limit < 1) {
        $limit = 1;
    }
    if ($limit > 50) {
        $limit = 50;
    }

    $show_children = get_theme_mod('aatf_header_destination_show_children', '1') === '1';

    $destinations = get_posts(array(
        'post_type' => 'destination',
        'post_status' => 'publish',
        'numberposts' => $limit,
        'orderby' => 'menu_order title',
        'order' => 'ASC',
    ));

    if (empty($destinations)) {
        return '<div class="aatf-mega-menu"><div class="aatf-mega-menu__empty">' . esc_html__('No destinations found yet.', 'aatf-expedition-base') . '</div></div>';
    }

    $tree = array();
    foreach ($destinations as $destination) {
        $parent_id = (int) $destination->post_parent;
        if (!isset($tree[$parent_id])) {
            $tree[$parent_id] = array();
        }
        $tree[$parent_id][] = $destination;
    }

    $top_level = isset($tree[0]) ? $tree[0] : array();
    if (empty($top_level)) {
        $top_level = $destinations;
    }

    ob_start();
    echo '<div class="aatf-mega-menu" aria-label="' . esc_attr__('Destinations', 'aatf-expedition-base') . '">';
    echo '<div class="aatf-mega-menu__grid">';

    foreach ($top_level as $parent) {
        $parent_id = (int) $parent->ID;
        $children = $show_children && isset($tree[$parent_id]) ? $tree[$parent_id] : array();

        echo '<div class="aatf-mega-menu__col">';
        echo '<h4><a href="' . esc_url(get_permalink($parent_id)) . '">' . esc_html(get_the_title($parent_id)) . '</a></h4>';

        if (!empty($children)) {
            echo '<ul>';
            foreach ($children as $child) {
                $child_id = (int) $child->ID;
                echo '<li><a href="' . esc_url(get_permalink($child_id)) . '">' . esc_html(get_the_title($child_id)) . '</a></li>';
            }
            echo '</ul>';
        }
        echo '</div>';
    }

    $archive_url = get_post_type_archive_link('destination');
    if ($archive_url) {
        echo '<div class="aatf-mega-menu__footer"><a href="' . esc_url($archive_url) . '">' . esc_html__('View All Destinations', 'aatf-expedition-base') . '</a></div>';
    }

    echo '</div>';
    echo '</div>';

    return (string) ob_get_clean();
}

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');

    register_nav_menus(array(
        'primary' => 'Primary Menu',
        'footer_col_1' => 'Footer Column 1',
        'footer_col_2' => 'Footer Column 2',
        'footer_col_3' => 'Footer Column 3',
        'footer_company' => 'Footer Company Menu',
        'footer_explore' => 'Footer Explore Menu',
    ));
});

function aatf_sanitize_checkbox($value)
{
    return ($value === true || $value === '1' || $value === 1) ? '1' : '0';
}

function aatf_sanitize_home_limit($value)
{
    $value = absint($value);
    if ($value < 1) {
        return 1;
    }

    if ($value > 24) {
        return 24;
    }

    return $value;
}

function aatf_sanitize_select($value, $choices, $default)
{
    $value = is_string($value) ? $value : '';
    return in_array($value, $choices, true) ? $value : $default;
}

function aatf_sanitize_home_hero_image($value)
{
    if (is_numeric($value)) {
        return (string) absint((int) $value);
    }

    return esc_url_raw((string) $value);
}

function aatf_home_layout_allowed_sections()
{
    return array(
        'hero',
        'exotic-places',
        'plan-trips',
        'popular-tours',
        'popular-tours2',
        'exclusive-activities',
        'featured-treks',
        'top-destinations',
        'testimonials',
        'departures',
        'content',
        'news-articles',
    );
}

function aatf_parse_home_layout($value)
{
    if (is_array($value)) {
        $raw_items = $value;
    } else {
        $raw_items = preg_split('/\s*,\s*/', (string) $value);
    }

    $allowed = aatf_home_layout_allowed_sections();
    $sections = array();

    foreach ((array) $raw_items as $item) {
        $item = sanitize_key((string) $item);
        if (!in_array($item, $allowed, true)) {
            continue;
        }
        if (!in_array($item, $sections, true)) {
            $sections[] = $item;
        }
    }

    return $sections;
}

function aatf_sanitize_home_layout($value)
{
    $sections = aatf_parse_home_layout($value);
    if (empty($sections)) {
        $sections = aatf_home_layout_allowed_sections();
    }

    return implode(',', $sections);
}

add_action('customize_register', function ($wp_customize) {
    $wp_customize->add_section('aatf_header_section', array(
        'title' => 'Header',
        'priority' => 30,
    ));

    $wp_customize->add_setting('aatf_header_cta_label', array(
        'default' => 'Plan Your Trip',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('aatf_header_cta_label', array(
        'type' => 'text',
        'section' => 'aatf_header_section',
        'label' => 'CTA Button Label',
    ));

    $wp_customize->add_setting('aatf_header_cta_url', array(
        'default' => home_url('/booking/'),
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('aatf_header_cta_url', array(
        'type' => 'url',
        'section' => 'aatf_header_section',
        'label' => 'CTA Button URL',
    ));

    $wp_customize->add_setting('aatf_header_phone', array(
        'default' => '+977-0000000000',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('aatf_header_phone', array(
        'type' => 'text',
        'section' => 'aatf_header_section',
        'label' => 'Phone Number',
    ));

    $wp_customize->add_setting('aatf_header_email', array(
        'default' => 'contact@example.com',
        'sanitize_callback' => 'sanitize_email',
    ));

    $wp_customize->add_control('aatf_header_email', array(
        'type' => 'email',
        'section' => 'aatf_header_section',
        'label' => 'Email Address',
    ));

    $wp_customize->add_setting('aatf_social_twitter', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('aatf_social_twitter', array(
        'type' => 'url',
        'section' => 'aatf_header_section',
        'label' => 'Twitter / X URL',
    ));

    $wp_customize->add_setting('aatf_social_facebook', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('aatf_social_facebook', array(
        'type' => 'url',
        'section' => 'aatf_header_section',
        'label' => 'Facebook URL',
    ));

    $wp_customize->add_setting('aatf_social_instagram', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('aatf_social_instagram', array(
        'type' => 'url',
        'section' => 'aatf_header_section',
        'label' => 'Instagram URL',
    ));

    $wp_customize->add_setting('aatf_social_pinterest', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('aatf_social_pinterest', array(
        'type' => 'url',
        'section' => 'aatf_header_section',
        'label' => 'Pinterest URL',
    ));

    $wp_customize->add_setting('aatf_header_destination_menu_mode', array(
        'default' => 'class',
        'sanitize_callback' => function ($value) {
            return aatf_sanitize_select($value, array('class', 'auto', 'off'), 'class');
        },
    ));

    $wp_customize->add_control('aatf_header_destination_menu_mode', array(
        'type' => 'select',
        'section' => 'aatf_header_section',
        'label' => 'Destination Mega Menu Mode',
        'description' => 'Use "Menu Class Only" and add class "aatf-mega-destinations" to the Destination menu item for best control.',
        'choices' => array(
            'class' => 'Menu Class Only (Recommended)',
            'auto' => 'Auto Detect by title/url',
            'off' => 'Disable Mega Menu',
        ),
    ));

    $wp_customize->add_setting('aatf_header_destination_limit', array(
        'default' => 18,
        'sanitize_callback' => 'aatf_sanitize_home_limit',
    ));

    $wp_customize->add_control('aatf_header_destination_limit', array(
        'type' => 'number',
        'section' => 'aatf_header_section',
        'label' => 'Mega Menu Destination Count',
        'input_attrs' => array(
            'min' => 1,
            'max' => 24,
            'step' => 1,
        ),
    ));

    $wp_customize->add_setting('aatf_header_destination_show_children', array(
        'default' => '1',
        'sanitize_callback' => 'aatf_sanitize_checkbox',
    ));

    $wp_customize->add_control('aatf_header_destination_show_children', array(
        'type' => 'checkbox',
        'section' => 'aatf_header_section',
        'label' => 'Show child destinations in mega menu',
    ));

    $wp_customize->add_section('aatf_homepage_section', array(
        'title' => 'Homepage Sections',
        'priority' => 31,
    ));

    $wp_customize->add_setting('aatf_home_hero_title', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('aatf_home_hero_title', array(
        'type' => 'text',
        'section' => 'aatf_homepage_section',
        'label' => 'Hero Title',
        'description' => 'Leave empty to use Site Title.',
    ));

    $wp_customize->add_setting('aatf_home_hero_text', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('aatf_home_hero_text', array(
        'type' => 'text',
        'section' => 'aatf_homepage_section',
        'label' => 'Hero Subtitle',
        'description' => 'Leave empty to use Site Tagline.',
    ));

    $wp_customize->add_setting('aatf_home_hero_media_type', array(
        'default' => 'none',
        'sanitize_callback' => function ($value) {
            return aatf_sanitize_select((string) $value, array('none', 'image', 'video'), 'none');
        },
    ));
    $wp_customize->add_control('aatf_home_hero_media_type', array(
        'type' => 'select',
        'section' => 'aatf_homepage_section',
        'label' => 'Hero Media Type',
        'choices' => array(
            'none' => 'None',
            'image' => 'Image',
            'video' => 'Video',
        ),
    ));

    $wp_customize->add_setting('aatf_home_hero_image', array(
        'default' => '',
        'sanitize_callback' => 'aatf_sanitize_home_hero_image',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'aatf_home_hero_image', array(
        'section' => 'aatf_homepage_section',
        'label' => 'Hero Image',
    )));

    $wp_customize->add_setting('aatf_home_hero_video_url', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control('aatf_home_hero_video_url', array(
        'type' => 'url',
        'section' => 'aatf_homepage_section',
        'label' => 'Hero Video URL',
        'description' => 'YouTube/Vimeo link or direct .mp4/.webm URL.',
    ));

    $wp_customize->add_setting('aatf_home_layout', array(
        'default' => 'hero,exotic-places,plan-trips,popular-tours,popular-tours2,testimonials,news-articles',
        'sanitize_callback' => 'aatf_sanitize_home_layout',
    ));
    $wp_customize->add_control('aatf_home_layout', array(
        'type' => 'text',
        'section' => 'aatf_homepage_section',
        'label' => 'Homepage Section Order',
        'description' => 'Comma-separated: hero, exotic-places, plan-trips, popular-tours, popular-tours2, testimonials, news-articles',
    ));

    $wp_customize->add_setting('aatf_home_show_treks', array(
        'default' => '1',
        'sanitize_callback' => 'aatf_sanitize_checkbox',
    ));
    $wp_customize->add_control('aatf_home_show_treks', array(
        'type' => 'checkbox',
        'section' => 'aatf_homepage_section',
        'label' => 'Show Trek Section',
    ));

    $wp_customize->add_setting('aatf_home_treks_heading', array(
        'default' => 'Featured Treks',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('aatf_home_treks_heading', array(
        'type' => 'text',
        'section' => 'aatf_homepage_section',
        'label' => 'Treks Heading',
    ));

    $wp_customize->add_setting('aatf_home_treks_limit', array(
        'default' => 6,
        'sanitize_callback' => 'aatf_sanitize_home_limit',
    ));
    $wp_customize->add_control('aatf_home_treks_limit', array(
        'type' => 'number',
        'section' => 'aatf_homepage_section',
        'label' => 'Treks Count',
        'input_attrs' => array(
            'min' => 1,
            'max' => 24,
            'step' => 1,
        ),
    ));

    $wp_customize->add_setting('aatf_home_show_destinations', array(
        'default' => '1',
        'sanitize_callback' => 'aatf_sanitize_checkbox',
    ));
    $wp_customize->add_control('aatf_home_show_destinations', array(
        'type' => 'checkbox',
        'section' => 'aatf_homepage_section',
        'label' => 'Show Destinations Section',
    ));

    $wp_customize->add_setting('aatf_home_destinations_heading', array(
        'default' => 'Top Destinations',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('aatf_home_destinations_heading', array(
        'type' => 'text',
        'section' => 'aatf_homepage_section',
        'label' => 'Destinations Heading',
    ));

    $wp_customize->add_setting('aatf_home_destinations_bg_image', array(
        'default' => '',
        'sanitize_callback' => 'aatf_sanitize_home_hero_image',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'aatf_home_destinations_bg_image', array(
        'section' => 'aatf_homepage_section',
        'label' => 'Exotic Places Background Image',
    )));

    $wp_customize->add_setting('aatf_home_destinations_limit', array(
        'default' => 6,
        'sanitize_callback' => 'aatf_sanitize_home_limit',
    ));
    $wp_customize->add_control('aatf_home_destinations_limit', array(
        'type' => 'number',
        'section' => 'aatf_homepage_section',
        'label' => 'Exotic Places Item Count',
        'input_attrs' => array(
            'min' => 1,
            'max' => 24,
            'step' => 1,
        ),
    ));

    $destination_choices = array(0 => '-- Select Destination --');
    $destination_items = get_posts(array(
        'post_type' => 'destination',
        'post_status' => array('publish', 'private', 'draft'),
        'numberposts' => -1,
        'orderby' => 'menu_order title',
        'order' => 'ASC',
    ));
    foreach ($destination_items as $destination_item) {
        $destination_choices[(int) $destination_item->ID] = (string) $destination_item->post_title;
    }

    $wp_customize->add_setting('aatf_home_show_plan_trips', array(
        'default' => '1',
        'sanitize_callback' => 'aatf_sanitize_checkbox',
    ));
    $wp_customize->add_control('aatf_home_show_plan_trips', array(
        'type' => 'checkbox',
        'section' => 'aatf_homepage_section',
        'label' => 'Show Plan Trips Section',
    ));

    $wp_customize->add_setting('aatf_home_plan_trips_heading', array(
        'default' => 'Plan Your Trip with Us',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('aatf_home_plan_trips_heading', array(
        'type' => 'text',
        'section' => 'aatf_homepage_section',
        'label' => 'Plan Trips Heading',
    ));

    $wp_customize->add_setting('aatf_home_plan_trips_destination_id', array(
        'default' => 0,
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('aatf_home_plan_trips_destination_id', array(
        'type' => 'select',
        'section' => 'aatf_homepage_section',
        'label' => 'Plan Trips Destination',
        'description' => 'Leave unselected to feature the first published destination automatically.',
        'choices' => $destination_choices,
    ));

    $wp_customize->add_setting('aatf_home_show_testimonials', array(
        'default' => '1',
        'sanitize_callback' => 'aatf_sanitize_checkbox',
    ));
    $wp_customize->add_control('aatf_home_show_testimonials', array(
        'type' => 'checkbox',
        'section' => 'aatf_homepage_section',
        'label' => 'Show Testimonials Section',
    ));

    $wp_customize->add_setting('aatf_home_testimonials_heading', array(
        'default' => 'What Our Trekkers Say',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('aatf_home_testimonials_heading', array(
        'type' => 'text',
        'section' => 'aatf_homepage_section',
        'label' => 'Testimonials Heading',
    ));

    $wp_customize->add_setting('aatf_home_testimonials_limit', array(
        'default' => 3,
        'sanitize_callback' => 'aatf_sanitize_home_limit',
    ));
    $wp_customize->add_control('aatf_home_testimonials_limit', array(
        'type' => 'number',
        'section' => 'aatf_homepage_section',
        'label' => 'Testimonials Count',
        'input_attrs' => array(
            'min' => 1,
            'max' => 24,
            'step' => 1,
        ),
    ));

    $wp_customize->add_setting('aatf_home_show_departures', array(
        'default' => '1',
        'sanitize_callback' => 'aatf_sanitize_checkbox',
    ));
    $wp_customize->add_control('aatf_home_show_departures', array(
        'type' => 'checkbox',
        'section' => 'aatf_homepage_section',
        'label' => 'Show Departures Section',
    ));

    $wp_customize->add_setting('aatf_home_departures_heading', array(
        'default' => 'Upcoming Departures',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('aatf_home_departures_heading', array(
        'type' => 'text',
        'section' => 'aatf_homepage_section',
        'label' => 'Departures Heading',
    ));

    $wp_customize->add_setting('aatf_home_departures_limit', array(
        'default' => 6,
        'sanitize_callback' => 'aatf_sanitize_home_limit',
    ));
    $wp_customize->add_control('aatf_home_departures_limit', array(
        'type' => 'number',
        'section' => 'aatf_homepage_section',
        'label' => 'Departures Count',
        'input_attrs' => array(
            'min' => 1,
            'max' => 24,
            'step' => 1,
        ),
    ));

    $wp_customize->add_setting('aatf_home_departures_trek_filter', array(
        'default' => 'all',
        'sanitize_callback' => function ($value) {
            return aatf_sanitize_select((string) $value, array('all', 'featured', 'destination'), 'all');
        },
    ));
    $wp_customize->add_control('aatf_home_departures_trek_filter', array(
        'type' => 'select',
        'section' => 'aatf_homepage_section',
        'label' => 'Departures Trek Filter',
        'choices' => array(
            'all' => 'All Treks',
            'featured' => 'Featured Treks Only',
            'destination' => 'Specific Destination',
        ),
    ));

    $wp_customize->add_setting('aatf_home_departures_destination_id', array(
        'default' => 0,
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('aatf_home_departures_destination_id', array(
        'type' => 'select',
        'section' => 'aatf_homepage_section',
        'label' => 'Departures Destination',
        'description' => 'Used only when "Specific Destination" is selected above.',
        'choices' => $destination_choices,
        'active_callback' => function () {
            return get_theme_mod('aatf_home_departures_trek_filter', 'all') === 'destination';
        },
    ));

    $wp_customize->add_section('aatf_footer_section', array(
        'title' => 'Footer',
        'priority' => 32,
    ));

    $wp_customize->add_setting('aatf_footer_col_1_heading', array(
        'default' => 'Company',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('aatf_footer_col_1_heading', array(
        'type' => 'text',
        'section' => 'aatf_footer_section',
        'label' => 'Footer Column 1 Heading',
    ));

    $wp_customize->add_setting('aatf_footer_col_2_heading', array(
        'default' => 'Explore',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('aatf_footer_col_2_heading', array(
        'type' => 'text',
        'section' => 'aatf_footer_section',
        'label' => 'Footer Column 2 Heading',
    ));

    $wp_customize->add_setting('aatf_footer_col_3_heading', array(
        'default' => 'Quick Links',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('aatf_footer_col_3_heading', array(
        'type' => 'text',
        'section' => 'aatf_footer_section',
        'label' => 'Footer Column 3 Heading',
    ));

    $wp_customize->add_setting('aatf_footer_company_heading', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('aatf_footer_company_heading', array(
        'type' => 'text',
        'section' => 'aatf_footer_section',
        'label' => 'Brand Name (optional)',
        'description' => 'Leave empty to use Site Name.',
    ));

    $wp_customize->add_setting('aatf_footer_tagline', array(
        'default' => "Welcome to our Trip and Tour Agency.\nLorem simply text amet cing elit.",
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('aatf_footer_tagline', array(
        'type' => 'textarea',
        'section' => 'aatf_footer_section',
        'label' => 'Footer Tagline',
    ));

    $wp_customize->add_setting('aatf_footer_overlay_image', array(
        'default' => 'https://images.unsplash.com/photo-1519500099198-fd81846b8f03?w=1600&q=60',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'aatf_footer_overlay_image', array(
        'section' => 'aatf_footer_section',
        'label' => 'Footer Background Overlay Image',
    )));

    $wp_customize->add_setting('aatf_footer_address', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('aatf_footer_address', array(
        'type' => 'text',
        'section' => 'aatf_footer_section',
        'label' => 'Footer Address',
    ));

    $wp_customize->add_setting('aatf_footer_phone', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('aatf_footer_phone', array(
        'type' => 'text',
        'section' => 'aatf_footer_section',
        'label' => 'Footer Phone',
    ));

    $wp_customize->add_setting('aatf_footer_email', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_email',
    ));
    $wp_customize->add_control('aatf_footer_email', array(
        'type' => 'email',
        'section' => 'aatf_footer_section',
        'label' => 'Footer Email',
    ));

    $wp_customize->add_setting('aatf_footer_newsletter_heading', array(
        'default' => 'Newsletter',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('aatf_footer_newsletter_heading', array(
        'type' => 'text',
        'section' => 'aatf_footer_section',
        'label' => 'Newsletter Heading',
    ));

    $wp_customize->add_setting('aatf_footer_newsletter_placeholder', array(
        'default' => 'Email address',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('aatf_footer_newsletter_placeholder', array(
        'type' => 'text',
        'section' => 'aatf_footer_section',
        'label' => 'Newsletter Placeholder',
    ));

    $wp_customize->add_setting('aatf_footer_newsletter_button_label', array(
        'default' => 'Subscribe',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('aatf_footer_newsletter_button_label', array(
        'type' => 'text',
        'section' => 'aatf_footer_section',
        'label' => 'Newsletter Button Label',
    ));

    $wp_customize->add_setting('aatf_footer_terms_label', array(
        'default' => 'I agree to all terms and policies',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('aatf_footer_terms_label', array(
        'type' => 'text',
        'section' => 'aatf_footer_section',
        'label' => 'Terms Checkbox Label',
    ));

    $wp_customize->add_setting('aatf_footer_copyright', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('aatf_footer_copyright', array(
        'type' => 'text',
        'section' => 'aatf_footer_section',
        'label' => 'Footer Copyright Text',
        'description' => 'Leave empty for auto copyright.',
    ));
});

function aatf_handle_footer_newsletter_subscribe()
{
    $redirect = wp_get_referer();
    if (!$redirect) {
        $redirect = home_url('/');
    }

    $redirect = remove_query_arg('aatf_footer_subscribe', $redirect);

    if (
        !isset($_POST['aatf_footer_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aatf_footer_nonce'])), 'aatf_footer_subscribe')
    ) {
        wp_safe_redirect(add_query_arg('aatf_footer_subscribe', 'invalid', $redirect));
        exit;
    }

    $email = isset($_POST['aatf_footer_email']) ? sanitize_email(wp_unslash($_POST['aatf_footer_email'])) : '';
    $consent = isset($_POST['aatf_footer_terms']) ? '1' : '0';

    if ($email === '' || !is_email($email) || $consent !== '1') {
        wp_safe_redirect(add_query_arg('aatf_footer_subscribe', 'invalid', $redirect));
        exit;
    }

    $admin_email = sanitize_email((string) get_option('admin_email'));
    $site_name = wp_specialchars_decode((string) get_bloginfo('name'), ENT_QUOTES);
    $subject = sprintf(__('[%s] Footer newsletter subscription', 'aatf-expedition-base'), $site_name);
    $message = sprintf(
        "New footer newsletter signup.\n\nEmail: %s\nConsent: Yes\nSource: %s\nSubmitted at (UTC): %s",
        $email,
        esc_url_raw($redirect),
        gmdate('c')
    );

    $sent = $admin_email !== '' ? wp_mail($admin_email, $subject, $message) : false;

    if ($sent) {
        do_action('aatf_footer_newsletter_subscription', $email, $redirect);
    }

    wp_safe_redirect(add_query_arg('aatf_footer_subscribe', $sent ? 'success' : 'error', $redirect));
    exit;
}

add_action('admin_post_nopriv_aatf_footer_subscribe', 'aatf_handle_footer_newsletter_subscribe');
add_action('admin_post_aatf_footer_subscribe', 'aatf_handle_footer_newsletter_subscribe');

add_action('wp_enqueue_scripts', function () {
    $stylesheet_path = get_stylesheet_directory() . '/style.css';
    $theme_css_path = get_template_directory() . '/assets/css/theme.css';
    $header_js_path = get_template_directory() . '/assets/js/header.js';

    wp_enqueue_style('aatf-expedition-base', get_stylesheet_uri(), array(), file_exists($stylesheet_path) ? (string) filemtime($stylesheet_path) : '0.1.0');
    wp_enqueue_style('aatf-expedition-base-layout', get_template_directory_uri() . '/assets/css/theme.css', array('aatf-expedition-base'), file_exists($theme_css_path) ? (string) filemtime($theme_css_path) : '0.1.0');
    wp_enqueue_script('aatf-expedition-header', get_template_directory_uri() . '/assets/js/header.js', array(), file_exists($header_js_path) ? (string) filemtime($header_js_path) : '0.1.0', true);

    // Tailwind CSS v4.1.14
    wp_enqueue_script(
        'tailwindcss',
        get_template_directory_uri() . '/assets/js/tailwindcss.js',
        array(),    // no dependencies
        '4.1.14',
        false       // false = load in <head>, required for Tailwind to parse HTML before paint
    );
});

function aatf_theme_has_framework()
{
    return class_exists('AATF_Framework');
}

function aatf_clear_home_featured_treks_cache()
{
    for ($limit = 1; $limit <= 24; $limit++) {
        delete_transient('aatf_home_featured_trek_ids_' . $limit);
    }
}

add_action('save_post_trek', function () {
    aatf_clear_home_featured_treks_cache();
});

add_action('before_delete_post', function ($post_id) {
    if (get_post_type($post_id) !== 'trek') {
        return;
    }
    aatf_clear_home_featured_treks_cache();
});

function aatf_home_template_slug()
{
    return 'page-templates/template-home.php';
}

function aatf_home_sanitize_exclusive_activities($raw)
{
    $raw = is_array($raw) ? $raw : array();

    $title = isset($raw['title']) ? sanitize_text_field((string) $raw['title']) : '';
    $subtitle = isset($raw['subtitle']) ? sanitize_textarea_field((string) $raw['subtitle']) : '';

    $items_raw = isset($raw['items']) && is_array($raw['items']) ? $raw['items'] : array();
    $items = array();

    foreach ($items_raw as $item) {
        if (!is_array($item)) {
            continue;
        }

        $icon_id = isset($item['icon_id']) ? absint((int) $item['icon_id']) : 0;
        $item_title = isset($item['title']) ? sanitize_text_field((string) $item['title']) : '';
        $description = isset($item['description']) ? sanitize_textarea_field((string) $item['description']) : '';

        if ($icon_id < 1 && $item_title === '' && $description === '') {
            continue;
        }

        $items[] = array(
            'icon_id' => $icon_id,
            'title' => $item_title,
            'description' => $description,
        );

        if (count($items) >= 12) {
            break;
        }
    }

    return array(
        'title' => $title,
        'subtitle' => $subtitle,
        'items' => $items,
    );
}

function aatf_sanitize_bullet_textarea($value)
{
    $lines = preg_split('/\r\n|\n|\r/', (string) $value);
    $clean_lines = array();

    foreach ((array) $lines as $line) {
        $line = trim((string) $line);
        $line = preg_replace('/^[\-\*\x{2022}\s]+/u', '', $line);
        $line = sanitize_text_field($line);

        if ($line === '') {
            continue;
        }

        $clean_lines[] = $line;
    }

    return implode("\n", $clean_lines);
}

add_filter('use_block_editor_for_post_type', function ($use_block_editor, $post_type) {
    if ($post_type === 'page') {
        return false;
    }

    return $use_block_editor;
}, 10, 2);

add_action('add_meta_boxes', function () {
    add_meta_box(
        'aatf_home_exclusive_activities',
        esc_html__('Exclusive Activities', 'aatf-expedition-base'),
        function ($post) {
            $value = get_post_meta($post->ID, 'aatf_home_exclusive_activities', true);
            $value = is_array($value) ? $value : array();
            $title = isset($value['title']) ? (string) $value['title'] : '';
            $subtitle = isset($value['subtitle']) ? (string) $value['subtitle'] : '';
            $items = isset($value['items']) && is_array($value['items']) ? $value['items'] : array();

            if (empty($items)) {
                $items = array(
                    array(
                        'icon_id' => 0,
                        'title' => 'Trekking',
                        'description' => 'Exhilarating multi-day adventure through rugged, stunning nature trails',
                    ),
                    array(
                        'icon_id' => 0,
                        'title' => 'Peak Climbing',
                        'description' => 'Thrilling Himalayan ascent blending trekking with basic mountaineering',
                    ),
                    array(
                        'icon_id' => 0,
                        'title' => 'Mountain Biking',
                        'description' => 'Exhilarating off-road adventure riding specially designed bikes over rugged trails',
                    ),
                    array(
                        'icon_id' => 0,
                        'title' => 'Paragliding',
                        'description' => 'Exhilarating adventure sport of soaring through the sky with a lightweight, foot-launched',
                    ),
                    array(
                        'icon_id' => 0,
                        'title' => 'Jungle Safari',
                        'description' => 'Thrilling wildlife adventure exploring dense forests and grasslands, spotting tigers',
                    ),
                    array(
                        'icon_id' => 0,
                        'title' => 'Rafting',
                        'description' => 'Adventure navigating wild rivers in inflatable rafts, conquering rapids with teamwork',
                    ),
                );
            }

            wp_nonce_field('aatf_home_exclusive_activities_save', 'aatf_home_exclusive_nonce');

            echo '<p class="aatf-home-template__help">'
                . esc_html__('Shown when this page uses the "Home Template".', 'aatf-expedition-base')
                . '</p>';

            echo '<p><label for="aatf_home_exclusive_title" class="aatf-home-template__label">'
                . esc_html__('Section Title', 'aatf-expedition-base')
                . '</label></p>';
            echo '<p><input type="text" class="widefat" id="aatf_home_exclusive_title" name="aatf_home_exclusive[title]" value="' . esc_attr($title) . '" placeholder="' . esc_attr__('Exclusive Activities', 'aatf-expedition-base') . '"></p>';

            echo '<p><label for="aatf_home_exclusive_subtitle" class="aatf-home-template__label">'
                . esc_html__('Section Subtitle', 'aatf-expedition-base')
                . '</label></p>';
            echo '<p><textarea class="widefat" rows="3" id="aatf_home_exclusive_subtitle" name="aatf_home_exclusive[subtitle]" placeholder="' . esc_attr__('Discover the world’s most exclusive trekking experiences...', 'aatf-expedition-base') . '">' . esc_textarea($subtitle) . '</textarea></p>';

            echo '<hr />';
            echo '<div class="aatf-home-template__repeater" data-repeater="exclusive-activities">';
            echo '<div class="aatf-home-template__repeater-head">'
                . '<strong>' . esc_html__('Activities', 'aatf-expedition-base') . '</strong>'
                . '<button type="button" class="button aatf-home-template__add" data-action="add-activity">' . esc_html__('Add Activity', 'aatf-expedition-base') . '</button>'
                . '</div>';

            echo '<div class="aatf-home-template__items" data-role="items">';

            foreach (array_values($items) as $index => $item) {
                $icon_id = isset($item['icon_id']) ? absint((int) $item['icon_id']) : 0;
                $item_title = isset($item['title']) ? (string) $item['title'] : '';
                $description = isset($item['description']) ? (string) $item['description'] : '';
                $thumb = $icon_id > 0 ? wp_get_attachment_image($icon_id, 'thumbnail', false, array('class' => 'aatf-home-template__icon-img')) : '';

                echo '<div class="aatf-home-template__item" data-index="' . esc_attr((string) $index) . '">';
                echo '<div class="aatf-home-template__icon">';
                echo '<div class="aatf-home-template__icon-preview" data-role="icon-preview">' . $thumb . '</div>';
                echo '<input type="hidden" data-role="icon-id" name="aatf_home_exclusive[items][' . esc_attr((string) $index) . '][icon_id]" value="' . esc_attr((string) $icon_id) . '">';
                echo '<div class="aatf-home-template__icon-actions">';
                echo '<button type="button" class="button" data-action="select-icon">' . esc_html__('Select Icon', 'aatf-expedition-base') . '</button> ';
                echo '<button type="button" class="button button-link-delete" data-action="remove-icon">' . esc_html__('Remove', 'aatf-expedition-base') . '</button>';
                echo '</div>';
                echo '</div>';

                echo '<div class="aatf-home-template__fields">';
                echo '<p><label class="aatf-home-template__label">' . esc_html__('Title', 'aatf-expedition-base') . '</label>';
                echo '<input type="text" class="widefat" name="aatf_home_exclusive[items][' . esc_attr((string) $index) . '][title]" value="' . esc_attr($item_title) . '" placeholder="' . esc_attr__('Trekking', 'aatf-expedition-base') . '"></p>';
                echo '<p><label class="aatf-home-template__label">' . esc_html__('Description', 'aatf-expedition-base') . '</label>';
                echo '<textarea class="widefat" rows="3" name="aatf_home_exclusive[items][' . esc_attr((string) $index) . '][description]" placeholder="' . esc_attr__('Short description...', 'aatf-expedition-base') . '">' . esc_textarea($description) . '</textarea></p>';

                echo '<p class="aatf-home-template__item-actions"><button type="button" class="button button-link-delete" data-action="remove-activity">' . esc_html__('Remove Activity', 'aatf-expedition-base') . '</button></p>';
                echo '</div>';
                echo '</div>';
            }

            echo '</div>';
            echo '</div>';

            ?>
            <script type="text/html" id="tmpl-aatf-home-activity-item">
                <div class="aatf-home-template__item" data-index="{{ data.index }}">
                    <div class="aatf-home-template__icon">
                        <div class="aatf-home-template__icon-preview" data-role="icon-preview"></div>
                        <input type="hidden" data-role="icon-id" name="aatf_home_exclusive[items][{{ data.index }}][icon_id]" value="0">
                        <div class="aatf-home-template__icon-actions">
                            <button type="button" class="button" data-action="select-icon"><?php echo esc_html__('Select Icon', 'aatf-expedition-base'); ?></button>
                            <button type="button" class="button button-link-delete" data-action="remove-icon"><?php echo esc_html__('Remove', 'aatf-expedition-base'); ?></button>
                        </div>
                    </div>
                    <div class="aatf-home-template__fields">
                        <p>
                            <label class="aatf-home-template__label"><?php echo esc_html__('Title', 'aatf-expedition-base'); ?></label>
                            <input type="text" class="widefat" name="aatf_home_exclusive[items][{{ data.index }}][title]" value="" placeholder="<?php echo esc_attr__('Trekking', 'aatf-expedition-base'); ?>">
                        </p>
                        <p>
                            <label class="aatf-home-template__label"><?php echo esc_html__('Description', 'aatf-expedition-base'); ?></label>
                            <textarea class="widefat" rows="3" name="aatf_home_exclusive[items][{{ data.index }}][description]" placeholder="<?php echo esc_attr__('Short description...', 'aatf-expedition-base'); ?>"></textarea>
                        </p>
                        <p class="aatf-home-template__item-actions">
                            <button type="button" class="button button-link-delete" data-action="remove-activity"><?php echo esc_html__('Remove Activity', 'aatf-expedition-base'); ?></button>
                        </p>
                    </div>
                </div>
            </script>
            <?php
        },
        'page',
        'normal',
        'high'
    );

    add_meta_box(
        'aatf_trek_amenities',
        esc_html__('Tour Amenities', 'aatf-expedition-base'),
        function ($post) {
            $amenities = (string) get_post_meta($post->ID, 'trek_amenities', true);

            wp_nonce_field('aatf_trek_amenities_save', 'aatf_trek_amenities_nonce');

            echo '<p>' . esc_html__('Add one amenity per line. Bullet characters like "-", "*" or "•" are optional.', 'aatf-expedition-base') . '</p>';
            echo '<textarea class="widefat" rows="8" id="aatf_trek_amenities" name="aatf_trek_amenities" placeholder="' . esc_attr__("Free WiFi\nAirport pickup\nGuide\nMeals included", 'aatf-expedition-base') . '">' . esc_textarea($amenities) . '</textarea>';
        },
        'trek',
        'normal',
        'default'
    );
});

add_action('save_post_page', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (wp_is_post_revision($post_id)) {
        return;
    }

    if (!isset($_POST['aatf_home_exclusive_nonce']) || !wp_verify_nonce((string) $_POST['aatf_home_exclusive_nonce'], 'aatf_home_exclusive_activities_save')) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $raw = isset($_POST['aatf_home_exclusive']) ? $_POST['aatf_home_exclusive'] : array();
    $sanitized = aatf_home_sanitize_exclusive_activities($raw);

    if (
        $sanitized['title'] === ''
        && $sanitized['subtitle'] === ''
        && empty($sanitized['items'])
    ) {
        delete_post_meta($post_id, 'aatf_home_exclusive_activities');
        return;
    }

    update_post_meta($post_id, 'aatf_home_exclusive_activities', $sanitized);
});

add_action('save_post_trek', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (wp_is_post_revision($post_id)) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (
        !isset($_POST['aatf_trek_amenities_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aatf_trek_amenities_nonce'])), 'aatf_trek_amenities_save')
    ) {
        return;
    }

    $amenities = isset($_POST['aatf_trek_amenities'])
        ? aatf_sanitize_bullet_textarea(wp_unslash($_POST['aatf_trek_amenities']))
        : '';

    if ($amenities === '') {
        delete_post_meta($post_id, 'trek_amenities');
        return;
    }

    update_post_meta($post_id, 'trek_amenities', $amenities);
});

add_action('admin_enqueue_scripts', function ($hook) {
    if (!in_array($hook, array('post.php', 'post-new.php'), true)) {
        return;
    }

    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || !isset($screen->post_type) || $screen->post_type !== 'page') {
        return;
    }

    wp_enqueue_media();

    $css_path = get_template_directory() . '/assets/css/home-template-admin.css';
    $js_path = get_template_directory() . '/assets/js/home-template-admin.js';
    $css_ver = file_exists($css_path) ? (string) filemtime($css_path) : '0.1.0';
    $js_ver = file_exists($js_path) ? (string) filemtime($js_path) : '0.1.0';

    wp_enqueue_style(
        'aatf-home-template-admin',
        get_template_directory_uri() . '/assets/css/home-template-admin.css',
        array(),
        $css_ver
    );

    wp_enqueue_script(
        'aatf-home-template-admin',
        get_template_directory_uri() . '/assets/js/home-template-admin.js',
        array('jquery', 'wp-util'),
        $js_ver,
        true
    );

    wp_localize_script('aatf-home-template-admin', 'aatfHomeTemplateAdmin', array(
        'templateSlug' => aatf_home_template_slug(),
        'metaboxId' => 'aatf_home_exclusive_activities',
        'mediaTitle' => esc_html__('Select icon', 'aatf-expedition-base'),
        'mediaButton' => esc_html__('Use this icon', 'aatf-expedition-base'),
    ));
});

// Contact Us Page Template Meta Box
add_action('add_meta_boxes_page', function ($post) {
    if (!$post instanceof WP_Post) {
        return;
    }

    $page_template = (string) get_page_template_slug($post->ID);

    if ($page_template !== 'page-templates/template-contact.php') {
        return;
    }

    add_meta_box(
        'aatf_contact_info_metabox',
        esc_html__('Contact Us Details', 'aatf-expedition-base'),
        function ($post) {
            $phone = get_post_meta($post->ID, 'aatf_contact_phone', true);
            $email = get_post_meta($post->ID, 'aatf_contact_email', true);
            $address = get_post_meta($post->ID, 'aatf_contact_address', true);
            $map_url = get_post_meta($post->ID, 'aatf_contact_map_url', true);
            
            wp_nonce_field('aatf_contact_info_save', 'aatf_contact_info_nonce');

            $hero_image_id  = (int) get_post_meta($post->ID, 'aatf_contact_hero_image_id', true);
            $hero_image_url = $hero_image_id > 0 ? wp_get_attachment_image_url($hero_image_id, 'large') : '';

            echo '<p>' . esc_html__('Fill in these details if this page uses the "Contact Us Template".', 'aatf-expedition-base') . '</p>';

            // Hero image picker
            echo '<p><label><strong>' . esc_html__('Hero / Banner Image', 'aatf-expedition-base') . '</strong></label></p>';
            echo '<div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">';
            if ($hero_image_url) {
                echo '<img id="aatf_contact_hero_preview" src="' . esc_url($hero_image_url) . '" style="max-width:200px;max-height:100px;border-radius:6px;border:1px solid #ddd;">';
            } else {
                echo '<img id="aatf_contact_hero_preview" src="" style="max-width:200px;max-height:100px;display:none;border-radius:6px;border:1px solid #ddd;">';
            }
            echo '</div>';
            echo '<input type="hidden" id="aatf_contact_hero_image_id" name="aatf_contact_hero_image_id" value="' . esc_attr((string) $hero_image_id) . '">';
            echo '<p>';
            echo '<button type="button" id="aatf_contact_hero_select" class="button">' . esc_html__('Select Image', 'aatf-expedition-base') . '</button> ';
            echo '<button type="button" id="aatf_contact_hero_remove" class="button button-link-delete" ' . ($hero_image_id < 1 ? 'style="display:none"' : '') . '>' . esc_html__('Remove Image', 'aatf-expedition-base') . '</button>';
            echo '</p>';
            echo '<hr />';

            echo '<p><label for="aatf_contact_phone"><strong>' . esc_html__('Phone Number', 'aatf-expedition-base') . '</strong></label></p>';
            echo '<p><input type="text" class="widefat" id="aatf_contact_phone" name="aatf_contact_phone" value="' . esc_attr($phone) . '" placeholder="' . esc_attr__('+1 234 567 8900', 'aatf-expedition-base') . '"></p>';

            echo '<p><label for="aatf_contact_email"><strong>' . esc_html__('Email Address', 'aatf-expedition-base') . '</strong></label></p>';
            echo '<p><input type="email" class="widefat" id="aatf_contact_email" name="aatf_contact_email" value="' . esc_attr($email) . '" placeholder="' . esc_attr__('info@example.com', 'aatf-expedition-base') . '"></p>';

            echo '<p><label for="aatf_contact_address"><strong>' . esc_html__('Physical Address', 'aatf-expedition-base') . '</strong></label></p>';
            echo '<p><textarea class="widefat" rows="3" id="aatf_contact_address" name="aatf_contact_address" placeholder="' . esc_attr__('123 Adventure Lane...', 'aatf-expedition-base') . '">' . esc_textarea($address) . '</textarea></p>';

            echo '<p><label for="aatf_contact_map_url"><strong>' . esc_html__('Google Map Embed URL (src only)', 'aatf-expedition-base') . '</strong></label></p>';
            echo '<p><input type="url" class="widefat" id="aatf_contact_map_url" name="aatf_contact_map_url" value="' . esc_attr($map_url) . '" placeholder="' . esc_attr__('https://www.google.com/maps/embed?...', 'aatf-expedition-base') . '"></p>';
            echo '<p class="description">' . esc_html__('Go to Google Maps -> Share -> Embed map -> Copy only the URL inside the src="..." attribute.', 'aatf-expedition-base') . '</p>';
            ?>
            <script>
            (function($){
                $(function(){
                    var frame;
                    $('#aatf_contact_hero_select').on('click', function(e){
                        e.preventDefault();
                        if (frame) { frame.open(); return; }
                        frame = wp.media({ title: '<?php echo esc_js(__('Select Hero Image', 'aatf-expedition-base')); ?>', button: { text: '<?php echo esc_js(__('Use this image', 'aatf-expedition-base')); ?>' }, multiple: false });
                        frame.on('select', function(){
                            var att = frame.state().get('selection').first().toJSON();
                            $('#aatf_contact_hero_image_id').val(att.id);
                            var src = (att.sizes && att.sizes.large) ? att.sizes.large.url : att.url;
                            $('#aatf_contact_hero_preview').attr('src', src).show();
                            $('#aatf_contact_hero_remove').show();
                        });
                        frame.open();
                    });
                    $('#aatf_contact_hero_remove').on('click', function(){
                        $('#aatf_contact_hero_image_id').val('0');
                        $('#aatf_contact_hero_preview').attr('src','').hide();
                        $(this).hide();
                    });
                });
            }(jQuery));
            </script>
            <?php
        },
        'page',
        'normal',
        'high'
    );
});

add_action('save_post_page', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (wp_is_post_revision($post_id) || !current_user_can('edit_post', $post_id)) {
        return;
    }

    if (!isset($_POST['aatf_contact_info_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aatf_contact_info_nonce'])), 'aatf_contact_info_save')) {
        return;
    }

    if (isset($_POST['aatf_contact_phone'])) {
        update_post_meta($post_id, 'aatf_contact_phone', sanitize_text_field(wp_unslash($_POST['aatf_contact_phone'])));
    }

    if (isset($_POST['aatf_contact_email'])) {
        update_post_meta($post_id, 'aatf_contact_email', sanitize_email(wp_unslash($_POST['aatf_contact_email'])));
    }

    if (isset($_POST['aatf_contact_address'])) {
        update_post_meta($post_id, 'aatf_contact_address', sanitize_textarea_field(wp_unslash($_POST['aatf_contact_address'])));
    }

    if (isset($_POST['aatf_contact_map_url'])) {
        update_post_meta($post_id, 'aatf_contact_map_url', esc_url_raw(wp_unslash($_POST['aatf_contact_map_url'])));
    }

    if (isset($_POST['aatf_contact_hero_image_id'])) {
        $hero_img_id = absint(wp_unslash($_POST['aatf_contact_hero_image_id']));
        if ($hero_img_id > 0) {
            update_post_meta($post_id, 'aatf_contact_hero_image_id', $hero_img_id);
        } else {
            delete_post_meta($post_id, 'aatf_contact_hero_image_id');
        }
    }
});

require_once get_template_directory() . '/inc-about-meta.php';
