<?php

if (!defined('ABSPATH')) {
    exit;
}

class AATF_Trek_Post_Types
{
    private static $destination_parent_invalid_posts = array();

    public static function register_thumbnail_support()
    {
        add_theme_support('post-thumbnails', array('trek', 'destination', 'testimonial'));
        add_post_type_support('trek', 'thumbnail');
        add_post_type_support('destination', 'thumbnail');
        add_post_type_support('testimonial', 'thumbnail');
    }

    public static function register_post_types()
    {
        self::register_treks();
        self::register_destinations();
        self::register_testimonials();
        self::register_departures();
        self::register_faqs();
        self::register_bookings();


        if (is_admin()) {
            self::register_admin_enhancements();
        }
    }

    public static function register_taxonomies()
    {
        register_taxonomy('trek_region', array('trek'), array(
            'label' => 'Regions',
            'public' => true,
            'hierarchical' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'rewrite' => array('slug' => 'trek-region'),
        ));

        register_taxonomy('trek_difficulty', array('trek'), array(
            'label' => 'Difficulty Levels',
            'public' => true,
            'hierarchical' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'rewrite' => array('slug' => 'trek-difficulty'),
        ));

        register_taxonomy('trek_season', array('trek'), array(
            'label' => 'Best Seasons',
            'public' => true,
            'hierarchical' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'rewrite' => array('slug' => 'trek-season'),
        ));

        register_taxonomy('faq_topic', array('trek_faq'), array(
            'label' => 'FAQ Topics',
            'public' => true,
            'hierarchical' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'rewrite' => array('slug' => 'faq-topic'),
        ));
    }

    private static function register_treks()
    {
        register_post_type('trek', array(
            'labels' => array(
                'name' => 'Treks',
                'singular_name' => 'Trek',
                'menu_name' => 'Treks',
                'add_new_item' => 'Add New Trek',
                'edit_item' => 'Edit Trek',
                'view_item' => 'View Trek',
                'all_items' => 'All Treks',
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'treks'),
            'menu_icon' => 'dashicons-palmtree',
            'show_in_menu' => 'aatf-framework',
            'capability_type' => array('trek', 'treks'),
            'map_meta_cap' => true,
            'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'revisions'),
            'show_in_rest' => true,
        ));
    }

    private static function register_destinations()
    {
        register_post_type('destination', array(
            'labels' => array(
                'name' => 'Destinations',
                'singular_name' => 'Destination',
                'menu_name' => 'Destinations',
                'add_new_item' => 'Add New Destination',
                'edit_item' => 'Edit Destination',
                'view_item' => 'View Destination',
                'all_items' => 'All Destinations',
                'parent_item_colon' => 'Parent Destination:',
            ),
            'public' => true,
            'hierarchical' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'destinations'),
            'menu_icon' => 'dashicons-location-alt',
            'show_in_menu' => 'aatf-framework',
            'capability_type' => array('destination', 'destinations'),
            'map_meta_cap' => true,
            'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes'),
            'show_in_rest' => true,
        ));
    }

    private static function register_testimonials()
    {
        register_post_type('testimonial', array(
            'labels' => array(
                'name' => 'Testimonials',
                'singular_name' => 'Testimonial',
            ),
            'public' => true,
            'has_archive' => false,
            'menu_icon' => 'dashicons-format-quote',
            'show_in_menu' => 'aatf-framework',
            'capability_type' => array('testimonial', 'testimonials'),
            'map_meta_cap' => true,
            'supports' => array('title', 'editor', 'thumbnail', 'revisions'),
            'show_in_rest' => true,
        ));
    }

    private static function register_departures()
    {
        register_post_type('departure', array(
            'labels' => array(
                'name' => 'Departures',
                'singular_name' => 'Departure',
            ),
            'public' => true,
            'has_archive' => false,
            'menu_icon' => 'dashicons-calendar-alt',
            'show_in_menu' => 'aatf-framework',
            'capability_type' => array('departure', 'departures'),
            'map_meta_cap' => true,
            'supports' => array('title', 'revisions'),
            'show_in_rest' => true,
        ));
    }

    private static function register_faqs()
    {
        register_post_type('trek_faq', array(
            'labels' => array(
                'name' => 'FAQs',
                'singular_name' => 'FAQ',
            ),
            'public' => true,
            'has_archive' => false,
            'menu_icon' => 'dashicons-editor-help',
            'show_in_menu' => 'aatf-framework',
            'capability_type' => array('trek_faq', 'trek_faqs'),
            'map_meta_cap' => true,
            'supports' => array('title', 'editor', 'revisions'),
            'show_in_rest' => true,
        ));
    }

    private static function register_bookings()
    {
        register_post_type('trek_booking', array(
            'labels' => array(
                'name' => 'Bookings',
                'singular_name' => 'Booking',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Booking',
                'edit_item' => 'Edit Booking',
                'new_item' => 'New Booking',
                'view_item' => 'View Booking',
                'search_items' => 'Search Bookings',
                'not_found' => 'No bookings found',
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'aatf-framework',
            'menu_icon' => 'dashicons-book-alt',
            'supports' => array('title', 'editor'),
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'show_in_rest' => false,
        ));
    }


    private static function register_admin_enhancements()
    {
        add_filter('manage_trek_posts_columns', array(__CLASS__, 'trek_columns'));
        add_action('manage_trek_posts_custom_column', array(__CLASS__, 'render_trek_columns'), 10, 2);
        add_filter('manage_edit-trek_sortable_columns', array(__CLASS__, 'trek_sortable_columns'));
        add_action('restrict_manage_posts', array(__CLASS__, 'trek_admin_filters'));
        add_action('pre_get_posts', array(__CLASS__, 'handle_trek_admin_sorting'));
        add_action('add_meta_boxes_trek', array(__CLASS__, 'add_trek_destination_meta_box'));
        add_action('save_post_trek', array(__CLASS__, 'save_trek_destination_meta_box'));
        add_action('add_meta_boxes_destination', array(__CLASS__, 'add_destination_parent_meta_box'));
        add_action('save_post_destination', array(__CLASS__, 'save_destination_parent_meta_box'));
        add_action('add_meta_boxes_trek', array(__CLASS__, 'add_trek_featured_image_meta_box'), 5);
        add_filter('redirect_post_location', array(__CLASS__, 'maybe_add_destination_parent_notice_flag'), 10, 2);
        add_action('admin_notices', array(__CLASS__, 'render_destination_parent_notice'));
    }

    public static function add_trek_featured_image_meta_box($post)
    {
        if (!$post instanceof WP_Post) {
            return;
        }

        if (!function_exists('post_thumbnail_meta_box')) {
            return;
        }

        add_meta_box(
            'postimagediv',
            esc_html__('Featured image'),
            'post_thumbnail_meta_box',
            'trek',
            'side',
            'low',
            array('post' => $post)
        );
    }

    public static function trek_columns($columns)
    {
        $new_columns = array();
        $new_columns['cb'] = isset($columns['cb']) ? $columns['cb'] : '';
        $new_columns['title'] = 'Trek Name';
        $new_columns['trek_destination'] = 'Destination';
        $new_columns['trek_display_order'] = 'Display Order';
        $new_columns['trek_price'] = 'Price';
        $new_columns['trek_duration'] = 'Duration';
        $new_columns['trek_group_size'] = 'Group';
        $new_columns['taxonomy-trek_difficulty'] = 'Difficulty';
        $new_columns['date'] = isset($columns['date']) ? $columns['date'] : 'Date';

        return $new_columns;
    }

    public static function render_trek_columns($column, $post_id)
    {
        if ($column === 'trek_destination') {
            $destination_ids = get_post_meta($post_id, 'trek_destination_ids', true);
            $destination_ids = is_array($destination_ids) ? array_map('absint', $destination_ids) : array();

            if (empty($destination_ids)) {
                echo '-';
                return;
            }

            $titles = array();
            foreach ($destination_ids as $destination_id) {
                if ($destination_id <= 0) {
                    continue;
                }
                $title = get_the_title($destination_id);
                if ($title) {
                    $titles[] = $title;
                }
            }

            echo !empty($titles) ? esc_html(implode(', ', $titles)) : '-';
            return;
        }

        if ($column === 'trek_price') {
            $price = (float) get_post_meta($post_id, 'trek_price', true);
            echo $price > 0 ? '$' . esc_html(number_format_i18n($price, 0)) : '-';
            return;
        }

        if ($column === 'trek_display_order') {
            $display_order = (int) get_post_meta($post_id, 'trek_display_order', true);
            echo esc_html((string) $display_order);
            return;
        }

        if ($column === 'trek_duration') {
            $duration = (int) get_post_meta($post_id, 'trek_duration', true);
            echo $duration > 0 ? esc_html((string) $duration . ' days') : '-';
            return;
        }

        if ($column === 'trek_group_size') {
            $group_size = (int) get_post_meta($post_id, 'trek_group_size', true);
            echo $group_size > 0 ? esc_html((string) $group_size) : '-';
        }
    }

    public static function trek_sortable_columns($columns)
    {
        $columns['trek_price'] = 'trek_price';
        $columns['trek_display_order'] = 'trek_display_order';
        $columns['trek_duration'] = 'trek_duration';
        $columns['trek_group_size'] = 'trek_group_size';

        return $columns;
    }

    public static function trek_admin_filters($post_type)
    {
        if ($post_type !== 'trek') {
            return;
        }

        $selected_featured = isset($_GET['aatf_featured']) ? sanitize_text_field(wp_unslash($_GET['aatf_featured'])) : '';
        echo '<select name="aatf_featured">';
        echo '<option value="">All Treks</option>';
        echo '<option value="1" ' . selected($selected_featured, '1', false) . '>Featured Only</option>';
        echo '<option value="0" ' . selected($selected_featured, '0', false) . '>Non-featured</option>';
        echo '</select>';

        $selected_destination = isset($_GET['aatf_destination_id']) ? (int) $_GET['aatf_destination_id'] : 0;
        $destinations = get_posts(array(
            'post_type' => 'destination',
            'post_status' => array('publish', 'draft', 'pending', 'private'),
            'numberposts' => -1,
            'orderby' => 'menu_order title',
            'order' => 'ASC',
        ));

        echo '<select name="aatf_destination_id">';
        echo '<option value="0">All Destinations</option>';
        if (method_exists(__CLASS__, 'group_posts_by_parent') && method_exists(__CLASS__, 'render_destination_options')) {
            $tree = self::group_posts_by_parent($destinations);
            self::render_destination_options($tree, 0, $selected_destination, 0);
        } else {
            foreach ($destinations as $destination) {
                echo '<option value="' . esc_attr((string) $destination->ID) . '" ' . selected($selected_destination, (int) $destination->ID, false) . '>' . esc_html($destination->post_title) . '</option>';
            }
        }
        echo '</select>';
    }

    public static function handle_trek_admin_sorting($query)
    {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }

        if ($query->get('post_type') !== 'trek') {
            return;
        }

        $orderby = $query->get('orderby');

        if (in_array($orderby, array('trek_price', 'trek_display_order', 'trek_duration', 'trek_group_size'), true)) {
            $query->set('meta_key', $orderby);
            $query->set('orderby', 'meta_value_num');
        }

        $featured = isset($_GET['aatf_featured']) ? sanitize_text_field(wp_unslash($_GET['aatf_featured'])) : '';
        if ($featured === '1' || $featured === '0') {
            $meta_query = (array) $query->get('meta_query');
            $meta_query[] = array(
                'key' => 'trek_featured',
                'value' => $featured,
                'compare' => '=',
            );
            $query->set('meta_query', $meta_query);
        }

        $destination_id = isset($_GET['aatf_destination_id']) ? (int) $_GET['aatf_destination_id'] : 0;
        if ($destination_id > 0) {
            $meta_query = (array) $query->get('meta_query');
            $meta_query[] = array(
                'key' => 'trek_destination_ids',
                'value' => 'i:' . $destination_id . ';',
                'compare' => 'LIKE',
            );
            $query->set('meta_query', $meta_query);
        }
    }

    public static function add_destination_parent_meta_box()
    {
        add_meta_box(
            'aatf_destination_parent',
            'Destination Hierarchy',
            array(__CLASS__, 'render_destination_parent_meta_box'),
            'destination',
            'normal',
            'high'
        );
    }

    public static function add_trek_destination_meta_box()
    {
        add_meta_box(
            'aatf_trek_destination',
            'Destinations',
            array(__CLASS__, 'render_trek_destination_meta_box'),
            'trek',
            'normal',
            'high'
        );
    }

    public static function render_trek_destination_meta_box($post)
    {
        if (!$post instanceof WP_Post) {
            return;
        }

        wp_nonce_field('aatf_trek_destination_nonce_action', 'aatf_trek_destination_nonce');

        echo '<div id="taxonomy-trek_destination" class="categorydiv">';
        echo '<div class="tabs-panel" style="display:block;max-height:280px;overflow:auto;border:1px solid #dcdcde;padding:8px;">';
        echo '<ul class="categorychecklist form-no-clear">';

        $selected = get_post_meta($post->ID, 'trek_destination_ids', true);
        $selected_ids = is_array($selected) ? array_map('absint', $selected) : array();

        $destinations = get_posts(array(
            'post_type' => 'destination',
            'post_status' => array('publish', 'draft', 'pending', 'private'),
            'numberposts' => -1,
            'orderby' => 'menu_order title',
            'order' => 'ASC',
        ));

        if (empty($destinations)) {
            echo '<li>No destinations found. Create destinations from Destination CPT first.</li>';
        } elseif (method_exists(__CLASS__, 'group_posts_by_parent')) {
            $tree = self::group_posts_by_parent($destinations);
            self::render_destination_checklist_items($tree, 0, $selected_ids, 0);
        } else {
            foreach ($destinations as $destination) {
                $id = (int) $destination->ID;
                echo '<li>';
                echo '<label class="selectit">';
                echo '<input type="checkbox" name="trek_destination_ids[]" value="' . esc_attr((string) $id) . '" ' . checked(in_array($id, $selected_ids, true), true, false) . ' /> ';
                echo esc_html($destination->post_title);
                echo '</label>';
                echo '</li>';
            }
        }

        echo '</ul>';
        echo '</div>';
        echo '</div>';
    }

    public static function render_destination_parent_meta_box($post)
    {
        if (!$post instanceof WP_Post) {
            return;
        }

        wp_nonce_field('aatf_destination_parent_nonce_action', 'aatf_destination_parent_nonce');

        $selected_parent = (int) $post->post_parent;
        $destinations = get_posts(array(
            'post_type' => 'destination',
            'post_status' => array('publish', 'draft', 'pending', 'private'),
            'numberposts' => -1,
            'orderby' => 'menu_order title',
            'order' => 'ASC',
            'exclude' => array((int) $post->ID),
        ));

        echo '<p><label for="aatf_destination_parent_id"><strong>Parent Destination</strong></label></p>';
        echo '<select id="aatf_destination_parent_id" name="aatf_destination_parent_id" class="widefat">';
        echo '<option value="0">None (Top Level)</option>';

        foreach ($destinations as $destination) {
            $depth = self::get_destination_depth($destination->ID);
            $prefix = str_repeat('- ', $depth);
            echo '<option value="' . esc_attr((string) $destination->ID) . '" ' . selected($selected_parent, (int) $destination->ID, false) . '>' . esc_html($prefix . $destination->post_title) . '</option>';
        }

        echo '</select>';
    }

    public static function save_destination_parent_meta_box($post_id)
    {
        if (!isset($_POST['aatf_destination_parent_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aatf_destination_parent_nonce'])), 'aatf_destination_parent_nonce_action')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $parent_id = isset($_POST['aatf_destination_parent_id']) ? absint($_POST['aatf_destination_parent_id']) : 0;

        if ($parent_id === $post_id) {
            $parent_id = 0;
        }

        // Prevent circular destination hierarchy by rejecting descendants as parents.
        if ($parent_id > 0 && self::destination_is_descendant_of($parent_id, (int) $post_id)) {
            self::$destination_parent_invalid_posts[(int) $post_id] = true;
            $parent_id = 0;
        }

        remove_action('save_post_destination', array(__CLASS__, 'save_destination_parent_meta_box'));
        wp_update_post(array(
            'ID' => (int) $post_id,
            'post_parent' => (int) $parent_id,
        ));
        add_action('save_post_destination', array(__CLASS__, 'save_destination_parent_meta_box'));
    }

    public static function maybe_add_destination_parent_notice_flag($location, $post_id)
    {
        $post_id = (int) $post_id;
        if ($post_id <= 0 || !isset(self::$destination_parent_invalid_posts[$post_id])) {
            return $location;
        }

        return add_query_arg('aatf_destination_parent_invalid', '1', $location);
    }

    public static function render_destination_parent_notice()
    {
        if (!is_admin()) {
            return;
        }

        if (!isset($_GET['aatf_destination_parent_invalid']) || $_GET['aatf_destination_parent_invalid'] !== '1') {
            return;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || $screen->id !== 'destination' || $screen->base !== 'post') {
            return;
        }

        echo '<div class="notice notice-warning is-dismissible"><p>';
        echo esc_html__('Invalid parent destination selected. To prevent a loop, parent was reset to None.', 'aatf-expedition-base');
        echo '</p></div>';
    }

    public static function save_trek_destination_meta_box($post_id)
    {
        if (!isset($_POST['aatf_trek_destination_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aatf_trek_destination_nonce'])), 'aatf_trek_destination_nonce_action')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $selected = isset($_POST['trek_destination_ids']) ? (array) wp_unslash($_POST['trek_destination_ids']) : array();
        $destination_ids = array_values(array_filter(array_map('absint', $selected)));
        update_post_meta($post_id, 'trek_destination_ids', $destination_ids);
    }

    private static function get_destination_depth($post_id)
    {
        $depth = 0;
        $parent = (int) wp_get_post_parent_id($post_id);

        while ($parent > 0 && $depth < 10) {
            $depth++;
            $parent = (int) wp_get_post_parent_id($parent);
        }

        return $depth;
    }

    private static function destination_is_descendant_of($candidate_id, $ancestor_id)
    {
        $candidate_id = (int) $candidate_id;
        $ancestor_id = (int) $ancestor_id;

        if ($candidate_id <= 0 || $ancestor_id <= 0) {
            return false;
        }

        $parent = (int) wp_get_post_parent_id($candidate_id);
        $guard = 0;

        while ($parent > 0 && $guard < 50) {
            if ($parent === $ancestor_id) {
                return true;
            }
            $parent = (int) wp_get_post_parent_id($parent);
            $guard++;
        }

        return false;
    }

    private static function group_posts_by_parent($posts)
    {
        $tree = array();
        foreach ($posts as $post) {
            $parent = (int) $post->post_parent;
            if (!isset($tree[$parent])) {
                $tree[$parent] = array();
            }
            $tree[$parent][] = $post;
        }

        return $tree;
    }

    private static function render_destination_options($tree, $parent_id, $selected_id, $depth)
    {
        if (!isset($tree[$parent_id])) {
            return;
        }

        foreach ($tree[$parent_id] as $destination) {
            $prefix = str_repeat('- ', $depth);
            echo '<option value="' . esc_attr((string) $destination->ID) . '" ' . selected($selected_id, (int) $destination->ID, false) . '>' . esc_html($prefix . $destination->post_title) . '</option>';
            self::render_destination_options($tree, (int) $destination->ID, $selected_id, $depth + 1);
        }
    }

    private static function render_destination_checklist_items($tree, $parent_id, $selected_ids, $depth)
    {
        if (!isset($tree[$parent_id])) {
            return;
        }

        foreach ($tree[$parent_id] as $destination) {
            $id = (int) $destination->ID;
            $title = (string) $destination->post_title;
            $prefix = str_repeat('&mdash; ', $depth);

            echo '<li>';
            echo '<label class="selectit">';
            echo '<input type="checkbox" name="trek_destination_ids[]" value="' . esc_attr((string) $id) . '" ' . checked(in_array($id, $selected_ids, true), true, false) . ' /> ';
            echo wp_kses_post($prefix) . esc_html($title);
            echo '</label>';
            echo '</li>';

            self::render_destination_checklist_items($tree, $id, $selected_ids, $depth + 1);
        }
    }
}
