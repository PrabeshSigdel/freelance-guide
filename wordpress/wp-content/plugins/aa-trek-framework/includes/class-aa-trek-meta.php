<?php

if (!defined('ABSPATH')) {
    exit;
}

class AATF_Trek_Meta
{
    private const TREK_DISPLAY_ORDER_BACKFILL_OPTION = 'aatf_trek_display_order_backfilled_v1';

    private static $meta_schema = array(
        'trek' => array(
            'trek_display_order' => array('label' => 'Display Order (smaller first)', 'type' => 'number', 'min' => 0, 'step' => 1),
            'trek_duration' => array('label' => 'Duration (days)', 'type' => 'number', 'min' => 1, 'step' => 1),
            'trek_duration_nights' => array('label' => 'Duration (nights)', 'type' => 'number', 'min' => 0, 'step' => 1),
            'trek_max_altitude' => array('label' => 'Max Altitude (meters)', 'type' => 'number', 'min' => 0, 'step' => 1),
            'trek_price' => array('label' => 'Price (USD)', 'type' => 'number', 'min' => 0, 'step' => '0.01'),
            'trek_group_size' => array('label' => 'Group Size', 'type' => 'number', 'min' => 1, 'step' => 1),
            'trek_start_location' => array('label' => 'Start Location', 'type' => 'text'),
            'trek_end_location' => array('label' => 'End Location', 'type' => 'text'),
            'trek_transport' => array('label' => 'Transport', 'type' => 'text'),
            'trek_accommodation' => array(
                'label' => 'Accommodation',
                'type' => 'select',
                'options' => array(
                    '' => 'Select',
                    'teahouse' => 'Teahouse',
                    'camping' => 'Camping',
                    'hotel' => 'Hotel',
                    'mixed' => 'Mixed',
                ),
            ),
            'trek_meal_plan' => array(
                'label' => 'Meal Plan',
                'type' => 'select',
                'options' => array(
                    '' => 'Select',
                    'breakfast' => 'Breakfast',
                    'half-board' => 'Half Board',
                    'full-board' => 'Full Board',
                    'self' => 'Self Managed',
                ),
            ),
            'trek_booking_url' => array('label' => 'External Booking URL', 'type' => 'url'),
            'trek_featured' => array('label' => 'Mark as Featured Trek', 'type' => 'checkbox'),
        ),
        'destination' => array(
            'destination_country' => array('label' => 'Country', 'type' => 'text'),
            'destination_featured' => array('label' => 'Featured Destination', 'type' => 'checkbox'),
        ),
        'testimonial' => array(
            'testimonial_location' => array('label' => 'Client Location (City, Country)', 'type' => 'text'),
            'testimonial_rating' => array(
                'label' => 'Rating (1-5)',
                'type' => 'select',
                'options' => array(
                    '5' => '5 Stars',
                    '4' => '4 Stars',
                    '3' => '3 Stars',
                    '2' => '2 Stars',
                    '1' => '1 Star',
                ),
            ),
        ),
        'trek_booking' => array(
            'booking_trek_id' => array('label' => 'Trek ID', 'type' => 'number'),
            'booking_departure_id' => array('label' => 'Departure ID', 'type' => 'number'),
            'booking_name' => array('label' => 'Full Name', 'type' => 'text'),
            'booking_email' => array('label' => 'Email Address', 'type' => 'text'),
            'booking_phone' => array('label' => 'Phone Number', 'type' => 'text'),
            'booking_date' => array('label' => 'Preferred Date', 'type' => 'text'),
            'booking_travelers' => array('label' => 'Number of Travelers', 'type' => 'number'),
            'booking_price_per_person' => array('label' => 'Price Per Person (USD)', 'type' => 'number'),
            'booking_total_price' => array('label' => 'Total Price (USD)', 'type' => 'number'),
            'booking_price_source' => array('label' => 'Price Source', 'type' => 'text'),
            'booking_status' => array(
                'label' => 'Booking Status',
                'type' => 'select',
                'options' => array(
                    'pending' => 'Pending',
                    'confirmed' => 'Confirmed',
                    'cancelled' => 'Cancelled',
                ),
            ),
        )
        
    );

    public static function register()
    {
        add_action('add_meta_boxes', array(__CLASS__, 'add_meta_boxes'));
        
        // Using specific hooks prevents interference with media uploads (attachments)
        add_action('save_post_trek', array(__CLASS__, 'save_all_meta'), 10, 2);
        add_action('save_post_destination', array(__CLASS__, 'save_all_meta'), 10, 2);
        add_action('save_post_testimonial', array(__CLASS__, 'save_all_meta'), 10, 2);
        add_action('save_post_trek_booking', array(__CLASS__, 'save_all_meta'), 10, 2);
    }

    public static function maybe_backfill_trek_display_order_meta()
    {
        if (get_option(self::TREK_DISPLAY_ORDER_BACKFILL_OPTION, '0') === '1') {
            return;
        }

        $trek_ids = get_posts(array(
            'post_type' => 'trek',
            'post_status' => array('publish', 'draft', 'pending', 'future', 'private'),
            'posts_per_page' => -1,
            'fields' => 'ids',
            'no_found_rows' => true,
        ));

        foreach ($trek_ids as $trek_id) {
            $trek_id = (int) $trek_id;
            if ($trek_id <= 0) {
                continue;
            }

            if (!metadata_exists('post', $trek_id, 'trek_display_order')) {
                // Keep older content visible in ordered queries; editors can later set explicit values.
                update_post_meta($trek_id, 'trek_display_order', 9999);
            }
        }

        update_option(self::TREK_DISPLAY_ORDER_BACKFILL_OPTION, '1', false);
    }

    public static function add_meta_boxes()
    {
        add_meta_box('aatf_trek_details', 'Trek Details', array(__CLASS__, 'render_trek_meta_box'), 'trek', 'normal', 'high');
        add_meta_box('aatf_destination_details', 'Destination Details', array(__CLASS__, 'render_general_meta_box'), 'destination', 'normal', 'high');
        add_meta_box('aatf_testimonial_details', 'Testimonial Details', array(__CLASS__, 'render_testimonial_meta_box'), 'testimonial', 'normal', 'high');
        add_meta_box('aatf_booking_details', 'Booking Details', array(__CLASS__, 'render_general_meta_box'), 'trek_booking', 'normal', 'high');
    }

    public static function render_general_meta_box($post)
    {
        $post_type = $post->post_type;
        if (!isset(self::$meta_schema[$post_type])) {
            return;
        }

        wp_nonce_field('aatf_meta_nonce', 'aatf_meta_nonce');

        echo '<div class="aatf-meta-grid">';
        foreach (self::$meta_schema[$post_type] as $key => $config) {
            self::render_field($key, $post->ID, $post_type);
        }
        echo '</div>';
    }

    public static function render_trek_meta_box($post)
    {
        wp_nonce_field('aatf_meta_nonce', 'aatf_meta_nonce');

        echo '<div class="aatf-meta-grid">';
        echo '<h3>Quick Facts</h3>';

        self::render_field('trek_display_order', $post->ID, 'trek');
        self::render_field('trek_duration', $post->ID, 'trek');
        self::render_field('trek_duration_nights', $post->ID, 'trek');
        self::render_field('trek_max_altitude', $post->ID, 'trek');
        self::render_field('trek_price', $post->ID, 'trek');
        self::render_field('trek_group_size', $post->ID, 'trek');
        self::render_field('trek_featured', $post->ID, 'trek');

        echo '<h3>Logistics</h3>';
        self::render_field('trek_start_location', $post->ID, 'trek');
        self::render_field('trek_end_location', $post->ID, 'trek');
        self::render_field('trek_transport', $post->ID, 'trek');
        self::render_field('trek_accommodation', $post->ID, 'trek');
        self::render_field('trek_meal_plan', $post->ID, 'trek');
        self::render_field('trek_booking_url', $post->ID, 'trek');

        echo '</div>';

        $overview = get_post_meta($post->ID, 'trek_overview', true);
        $highlights_title = get_post_meta($post->ID, 'trek_highlights_title', true);
        $highlights_intro = get_post_meta($post->ID, 'trek_highlights_intro', true);
        $highlights = get_post_meta($post->ID, 'trek_highlights', true);
        $includes = get_post_meta($post->ID, 'trek_cost_includes', true);
        $excludes = get_post_meta($post->ID, 'trek_cost_excludes', true);
        $slider_images = get_post_meta($post->ID, 'trek_slider_image_ids', true);
        if (empty($slider_images)) {
            $slider_images = get_post_meta($post->ID, 'trek_gallery_ids', true);
        }
        $reviews = get_post_meta($post->ID, 'trek_reviews', true);
        $map_embed = get_post_meta($post->ID, 'trek_map_embed', true);
        $itinerary = get_post_meta($post->ID, 'trek_itinerary', true);
        $video_url = get_post_meta($post->ID, 'trek_video_url', true);
        $altitude_profile = get_post_meta($post->ID, 'trek_altitude_profile', true);
        $altitude_profile = is_array($altitude_profile) ? $altitude_profile : array();
        $equipment_sections = get_post_meta($post->ID, 'trek_equipment_sections', true);
        $equipment_sections = is_array($equipment_sections) ? $equipment_sections : array();

        $slider_image_ids = self::normalize_gallery_ids($slider_images);
        $slider_value = implode(',', $slider_image_ids);
        $itinerary_rows = is_array($itinerary) ? $itinerary : array();
        $group_pricing_rows = self::normalize_group_pricing_rows(get_post_meta($post->ID, 'trek_group_pricing', true));

        echo '<p><em>Best Season is managed using the <strong>Best Seasons</strong> taxonomy on this Trek.</em></p>';

        echo '<p><label for="trek_overview"><strong>Overview</strong></label><br/>';
        echo '<textarea class="widefat" rows="4" id="trek_overview" name="trek_overview">' . esc_textarea($overview) . '</textarea></p>';

        echo '<p><label for="trek_highlights_title"><strong>Highlights Title</strong></label><br/>';
        echo '<input type="text" class="widefat" id="trek_highlights_title" name="trek_highlights_title" value="' . esc_attr((string) $highlights_title) . '" placeholder="EBC Trek Highlights" /></p>';

        echo '<p><label for="trek_highlights_intro"><strong>Highlights Intro</strong></label><br/>';
        echo '<textarea class="widefat" rows="3" id="trek_highlights_intro" name="trek_highlights_intro">' . esc_textarea((string) $highlights_intro) . '</textarea></p>';

        echo '<p><label for="trek_highlights"><strong>Highlights List (one per line)</strong></label><br/>';
        echo '<textarea class="widefat" rows="6" id="trek_highlights" name="trek_highlights">' . esc_textarea((string) $highlights) . '</textarea></p>';

        echo '<p><label for="trek_cost_includes"><strong>Cost Includes (one per line)</strong></label><br/>';
        echo '<textarea class="widefat" rows="4" id="trek_cost_includes" name="trek_cost_includes">' . esc_textarea($includes) . '</textarea></p>';

        echo '<p><label for="trek_cost_excludes"><strong>Cost Excludes (one per line)</strong></label><br/>';
        echo '<textarea class="widefat" rows="4" id="trek_cost_excludes" name="trek_cost_excludes">' . esc_textarea($excludes) . '</textarea></p>';

        echo '<hr/><p><strong>Group Pricing (Per Person)</strong></p>';
        echo '<p><em>Add group-size discount ranges for this trek.</em></p>';
        echo '<div id="aatf-group-pricing-rows">';

        if (empty($group_pricing_rows)) {
            $group_pricing_rows[] = array(
                'min_people' => '',
                'max_people' => '',
                'price_per_person' => '',
            );
        }

        foreach ($group_pricing_rows as $index => $row) {
            $min_people_raw = isset($row['min_people']) ? absint($row['min_people']) : 0;
            $max_people_raw = isset($row['max_people']) ? absint($row['max_people']) : 0;
            $price_per_person_raw = isset($row['price_per_person']) ? (float) $row['price_per_person'] : 0.0;

            $min_people = $min_people_raw > 0 ? (string) $min_people_raw : '';
            $max_people = $max_people_raw > 0 ? (string) $max_people_raw : '';
            $price_per_person = $price_per_person_raw > 0 ? (string) $price_per_person_raw : '';

            echo '<div class="aatf-group-pricing-row" style="border:1px solid #ddd;padding:10px;margin-bottom:10px;">';
            echo '<p><label><strong>Min People</strong></label><input type="number" class="widefat" min="1" step="1" name="trek_group_pricing[' . esc_attr((string) $index) . '][min_people]" value="' . esc_attr($min_people) . '" /></p>';
            echo '<p><label><strong>Max People</strong> <small>(optional)</small></label><input type="number" class="widefat" min="1" step="1" name="trek_group_pricing[' . esc_attr((string) $index) . '][max_people]" value="' . esc_attr($max_people) . '" /></p>';
            echo '<p><label><strong>Price Per Person (USD)</strong></label><input type="number" class="widefat" min="0" step="0.01" name="trek_group_pricing[' . esc_attr((string) $index) . '][price_per_person]" value="' . esc_attr($price_per_person) . '" /></p>';
            echo '<button type="button" class="button aatf-remove-group-pricing-row">Remove Group Price</button>';
            echo '</div>';
        }

        echo '</div>';
        echo '<p><button type="button" class="button button-secondary" id="aatf-add-group-pricing-row">Add Group Price</button></p>';

        echo '<p><label><strong>Image Slider Images</strong></label><br/>';
        echo '<input type="hidden" id="trek_gallery_ids" name="trek_gallery_ids" value="' . esc_attr($slider_value) . '" />';
        echo '<button type="button" class="button button-secondary" id="aatf-gallery-upload">Select / Edit Slider Images</button> ';
        echo '<button type="button" class="button" id="aatf-gallery-clear">Clear Slider Images</button><br/>';
        echo '<small>Select multiple images for the trek slider. Use Remove on each preview image to delete one image.</small></p>';
        echo '<div id="aatf-gallery-preview" style="display:flex;flex-wrap:wrap;gap:8px;">';

        foreach ($slider_image_ids as $image_id) {
            $thumb = wp_get_attachment_image_url($image_id, 'thumbnail');
            if (!$thumb) {
                continue;
            }
            echo '<img src="' . esc_url($thumb) . '" data-id="' . esc_attr($image_id) . '" style="width:80px;height:80px;object-fit:cover;border:1px solid #ddd;" alt="" />';
        }

        echo '</div>';

        echo '<p><label for="trek_reviews"><strong>Reviews Summary</strong></label><br/>';
        echo '<textarea class="widefat" rows="3" id="trek_reviews" name="trek_reviews">' . esc_textarea($reviews) . '</textarea></p>';

        echo '<p><label for="trek_map_embed"><strong>Map Embed (iframe or map URL)</strong></label><br/>';
        echo '<textarea class="widefat" rows="3" id="trek_map_embed" name="trek_map_embed">' . esc_textarea($map_embed) . '</textarea></p>';
        echo '<p><label for="trek_video_url"><strong>YouTube Video URL or Embed Iframe</strong></label><br/>';
        echo '<textarea class="widefat" rows="3" id="trek_video_url" name="trek_video_url" placeholder="https://www.youtube.com/watch?v=... or <iframe ...></iframe>">' . esc_textarea((string) $video_url) . '</textarea></p>';

        echo '<hr/><p><strong>Itinerary (Repeater)</strong></p>';
        echo '<div id="aatf-itinerary-rows">';

        if (empty($itinerary_rows)) {
            $itinerary_rows[] = array('day' => '', 'title' => '', 'description' => '');
        }

        foreach ($itinerary_rows as $index => $row) {
            $day = isset($row['day']) ? $row['day'] : '';
            $title = isset($row['title']) ? $row['title'] : '';
            $description = isset($row['description']) ? $row['description'] : '';

            echo '<div class="aatf-itinerary-row" style="border:1px solid #ddd;padding:10px;margin-bottom:10px;">';
            echo '<p><label>Day</label><input type="text" class="widefat" name="trek_itinerary[' . esc_attr($index) . '][day]" value="' . esc_attr($day) . '" /></p>';
            echo '<p><label>Title</label><input type="text" class="widefat" name="trek_itinerary[' . esc_attr($index) . '][title]" value="' . esc_attr($title) . '" /></p>';
            echo '<p><label>Description</label><textarea class="widefat" rows="3" name="trek_itinerary[' . esc_attr($index) . '][description]">' . esc_textarea($description) . '</textarea></p>';
            echo '<button type="button" class="button aatf-remove-row">Remove Day</button>';
            echo '</div>';
        }

        echo '</div>';
        echo '<p><button type="button" class="button button-secondary" id="aatf-add-itinerary-row">Add Day</button></p>';

        echo '<hr/><p><strong>Altitude Profile (Chart)</strong></p>';
        echo '<p><em>Add chart points for day/place/altitude to display altitude profile on the trek page.</em></p>';
        echo '<div id="aatf-altitude-profile-rows">';

        if (empty($altitude_profile)) {
            $altitude_profile[] = array(
                'day' => '',
                'place' => '',
                'altitude_m' => '',
            );
        }

        foreach ($altitude_profile as $index => $row) {
            $day = isset($row['day']) ? (string) $row['day'] : '';
            $place = isset($row['place']) ? (string) $row['place'] : '';
            $altitude_m = isset($row['altitude_m']) ? (string) $row['altitude_m'] : '';

            echo '<div class="aatf-altitude-profile-row" style="border:1px solid #ddd;padding:10px;margin-bottom:10px;">';
            echo '<p><label>Day Label</label><input type="text" class="widefat" name="trek_altitude_profile[' . esc_attr((string) $index) . '][day]" value="' . esc_attr($day) . '" placeholder="Day 1" /></p>';
            echo '<p><label>Place Name</label><input type="text" class="widefat" name="trek_altitude_profile[' . esc_attr((string) $index) . '][place]" value="' . esc_attr($place) . '" placeholder="Namche Bazaar" /></p>';
            echo '<p><label>Altitude (m)</label><input type="number" class="widefat" min="0" step="1" name="trek_altitude_profile[' . esc_attr((string) $index) . '][altitude_m]" value="' . esc_attr($altitude_m) . '" /></p>';
            echo '<button type="button" class="button aatf-remove-altitude-profile-row">Remove Point</button>';
            echo '</div>';
        }

        echo '</div>';
        echo '<p><button type="button" class="button button-secondary" id="aatf-add-altitude-profile-row">Add Point</button></p>';

        echo '<hr/><p><strong>Equipment Sections</strong></p>';
        echo '<p><em>Add section image/icon, heading, and multiple items (one per line).</em></p>';
        echo '<div id="aatf-equipment-sections">';

        if (empty($equipment_sections)) {
            $equipment_sections[] = array(
                'heading' => '',
                'image_id' => 0,
                'items_text' => '',
            );
        }

        foreach ($equipment_sections as $index => $section) {
            $heading = isset($section['heading']) ? (string) $section['heading'] : '';
            $image_id = isset($section['image_id']) ? (int) $section['image_id'] : 0;
            $items = isset($section['items']) && is_array($section['items']) ? $section['items'] : array();
            $items_text = implode("\n", array_map('sanitize_text_field', $items));
            $image_url = $image_id > 0 ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';

            echo '<div class="aatf-equipment-row">';
            echo '<p><label><strong>Heading</strong></label><input type="text" class="widefat" name="trek_equipment_sections[' . esc_attr((string) $index) . '][heading]" value="' . esc_attr($heading) . '" /></p>';
            echo '<p>';
            echo '<input type="hidden" class="aatf-equipment-image-id" name="trek_equipment_sections[' . esc_attr((string) $index) . '][image_id]" value="' . esc_attr((string) $image_id) . '" />';
            echo '<button type="button" class="button button-secondary aatf-select-equipment-image">Select Section Image</button> ';
            echo '<button type="button" class="button aatf-clear-equipment-image">Clear Image</button>';
            echo '</p>';
            echo '<div class="aatf-equipment-image-preview">';
            if ($image_url) {
                echo '<img src="' . esc_url($image_url) . '" alt="" />';
            }
            echo '</div>';
            echo '<p><label><strong>Items (one per line)</strong></label><textarea class="widefat" rows="5" name="trek_equipment_sections[' . esc_attr((string) $index) . '][items_text]">' . esc_textarea($items_text) . '</textarea></p>';
            echo '<p><button type="button" class="button aatf-remove-equipment-row">Remove Section</button></p>';
            echo '</div>';
        }

        echo '</div>';
        echo '<p><button type="button" class="button button-secondary" id="aatf-add-equipment-row">Add Equipment Section</button></p>';

        echo '<script type="text/html" id="tmpl-aatf-itinerary-row">';
        echo '<div class="aatf-itinerary-row" style="border:1px solid #ddd;padding:10px;margin-bottom:10px;">';
        echo '<p><label>Day</label><input type="text" class="widefat" name="trek_itinerary[{{data.index}}][day]" value="" /></p>';
        echo '<p><label>Title</label><input type="text" class="widefat" name="trek_itinerary[{{data.index}}][title]" value="" /></p>';
        echo '<p><label>Description</label><textarea class="widefat" rows="3" name="trek_itinerary[{{data.index}}][description]"></textarea></p>';
        echo '<button type="button" class="button aatf-remove-row">Remove Day</button>';
        echo '</div>';
        echo '</script>';

        echo '<script type="text/html" id="tmpl-aatf-altitude-profile-row">';
        echo '<div class="aatf-altitude-profile-row" style="border:1px solid #ddd;padding:10px;margin-bottom:10px;">';
        echo '<p><label>Day Label</label><input type="text" class="widefat" name="trek_altitude_profile[{{data.index}}][day]" value="" placeholder="Day 1" /></p>';
        echo '<p><label>Place Name</label><input type="text" class="widefat" name="trek_altitude_profile[{{data.index}}][place]" value="" placeholder="Namche Bazaar" /></p>';
        echo '<p><label>Altitude (m)</label><input type="number" class="widefat" min="0" step="1" name="trek_altitude_profile[{{data.index}}][altitude_m]" value="" /></p>';
        echo '<button type="button" class="button aatf-remove-altitude-profile-row">Remove Point</button>';
        echo '</div>';
        echo '</script>';

        echo '<script type="text/html" id="tmpl-aatf-equipment-row">';
        echo '<div class="aatf-equipment-row">';
        echo '<p><label><strong>Heading</strong></label><input type="text" class="widefat" name="trek_equipment_sections[{{data.index}}][heading]" value="" /></p>';
        echo '<p>';
        echo '<input type="hidden" class="aatf-equipment-image-id" name="trek_equipment_sections[{{data.index}}][image_id]" value="" />';
        echo '<button type="button" class="button button-secondary aatf-select-equipment-image">Select Section Image</button> ';
        echo '<button type="button" class="button aatf-clear-equipment-image">Clear Image</button>';
        echo '</p>';
        echo '<div class="aatf-equipment-image-preview"></div>';
        echo '<p><label><strong>Items (one per line)</strong></label><textarea class="widefat" rows="5" name="trek_equipment_sections[{{data.index}}][items_text]"></textarea></p>';
        echo '<p><button type="button" class="button aatf-remove-equipment-row">Remove Section</button></p>';
        echo '</div>';
        echo '</script>';

        echo '<script type="text/html" id="tmpl-aatf-group-pricing-row">';
        echo '<div class="aatf-group-pricing-row" style="border:1px solid #ddd;padding:10px;margin-bottom:10px;">';
        echo '<p><label><strong>Min People</strong></label><input type="number" class="widefat" min="1" step="1" name="trek_group_pricing[{{data.index}}][min_people]" value="" /></p>';
        echo '<p><label><strong>Max People</strong> <small>(optional)</small></label><input type="number" class="widefat" min="1" step="1" name="trek_group_pricing[{{data.index}}][max_people]" value="" /></p>';
        echo '<p><label><strong>Price Per Person (USD)</strong></label><input type="number" class="widefat" min="0" step="0.01" name="trek_group_pricing[{{data.index}}][price_per_person]" value="" /></p>';
        echo '<button type="button" class="button aatf-remove-group-pricing-row">Remove Group Price</button>';
        echo '</div>';
        echo '</script>';
    }

    public static function render_testimonial_meta_box($post)
    {
        wp_nonce_field('aatf_meta_nonce', 'aatf_meta_nonce');

        echo '<p><strong>Use these fields for the testimonial card:</strong></p>';
        echo '<ul style="margin-top:0;">';
        echo '<li><strong>Title</strong>: client name (for example: Sophia Macpherson)</li>';
        echo '<li><strong>Content</strong>: testimonial quote text</li>';
        echo '<li><strong>Featured image</strong>: client photo/avatar</li>';
        echo '</ul>';

        echo '<div class="aatf-meta-grid">';
        self::render_field('testimonial_location', $post->ID, 'testimonial');
        self::render_field('testimonial_rating', $post->ID, 'testimonial');
        echo '</div>';
    }

    public static function save_all_meta($post_id, $post)
    {
        if (!isset($_POST['aatf_meta_nonce']) || !wp_verify_nonce($_POST['aatf_meta_nonce'], 'aatf_meta_nonce')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $post_type = $post->post_type;

        if (isset(self::$meta_schema[$post_type])) {
            foreach (self::$meta_schema[$post_type] as $key => $config) {
                $raw = isset($_POST[$key]) ? wp_unslash($_POST[$key]) : null;
                $value = self::sanitize_field_value($config['type'], $raw);

                if ($config['type'] === 'checkbox') {
                    update_post_meta($post_id, $key, $value ? '1' : '0');
                } else {
                    update_post_meta($post_id, $key, $value);
                }
            }
        }

        if ($post_type === 'trek') {
            self::save_trek_additional_meta($post_id);
        }
    }

    private static function save_trek_additional_meta($post_id)
    {
        delete_post_meta($post_id, 'trek_best_season_text');

        $simple_textareas = array(
            'trek_overview',
            'trek_highlights_intro',
            'trek_highlights',
            'trek_cost_includes',
            'trek_cost_excludes',
            'trek_reviews',
        );

        foreach ($simple_textareas as $key) {
            $value = isset($_POST[$key]) ? sanitize_textarea_field(wp_unslash($_POST[$key])) : '';
            update_post_meta($post_id, $key, $value);
        }

        $map_raw = isset($_POST['trek_map_embed']) ? trim((string) wp_unslash($_POST['trek_map_embed'])) : '';
        $map_raw = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $map_raw);
        $map_value = '';
        if ($map_raw !== '') {
            if (stripos($map_raw, '<iframe') !== false) {
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
                $map_value = trim((string) wp_kses($map_raw, $allowed_iframe));
            } else {
                $map_value = esc_url_raw($map_raw);
            }
        }
        update_post_meta($post_id, 'trek_map_embed', $map_value);

        $highlights_title = isset($_POST['trek_highlights_title']) ? sanitize_text_field(wp_unslash($_POST['trek_highlights_title'])) : '';
        update_post_meta($post_id, 'trek_highlights_title', $highlights_title);

        $video_raw = isset($_POST['trek_video_url']) ? trim((string) wp_unslash($_POST['trek_video_url'])) : '';
        $video_raw = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $video_raw);
        $video_value = '';

        if ($video_raw !== '') {
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
                $video_value = trim((string) wp_kses($video_raw, $allowed_iframe));
            } else {
                $video_value = esc_url_raw($video_raw);
            }
        }

        update_post_meta($post_id, 'trek_video_url', $video_value);

        $gallery_raw = isset($_POST['trek_gallery_ids']) ? wp_unslash($_POST['trek_gallery_ids']) : '';
        $gallery_ids = self::normalize_gallery_ids($gallery_raw);
        update_post_meta($post_id, 'trek_slider_image_ids', $gallery_ids);
        update_post_meta($post_id, 'trek_gallery_ids', $gallery_ids);

        $clean_itinerary = array();
        $itinerary_rows = isset($_POST['trek_itinerary']) ? wp_unslash($_POST['trek_itinerary']) : array();

        if (is_array($itinerary_rows)) {
            foreach ($itinerary_rows as $row) {
                $day = isset($row['day']) ? sanitize_text_field($row['day']) : '';
                $title = isset($row['title']) ? sanitize_text_field($row['title']) : '';
                $description = isset($row['description']) ? sanitize_textarea_field($row['description']) : '';

                if ($day === '' && $title === '' && $description === '') {
                    continue;
                }

                $clean_itinerary[] = array(
                    'day' => $day,
                    'title' => $title,
                    'description' => $description,
                );
            }
        }

        update_post_meta($post_id, 'trek_itinerary', $clean_itinerary);

        $clean_altitude_profile = array();
        $altitude_profile_rows = isset($_POST['trek_altitude_profile']) ? wp_unslash($_POST['trek_altitude_profile']) : array();

        if (is_array($altitude_profile_rows)) {
            foreach ($altitude_profile_rows as $row) {
                $day = isset($row['day']) ? sanitize_text_field((string) $row['day']) : '';
                $place = isset($row['place']) ? sanitize_text_field((string) $row['place']) : '';
                $altitude_m = isset($row['altitude_m']) ? (int) $row['altitude_m'] : 0;

                if ($day === '' && $place === '' && $altitude_m <= 0) {
                    continue;
                }

                $clean_altitude_profile[] = array(
                    'day' => $day,
                    'place' => $place,
                    'altitude_m' => max(0, $altitude_m),
                );
            }
        }

        update_post_meta($post_id, 'trek_altitude_profile', $clean_altitude_profile);

        $group_pricing_rows = isset($_POST['trek_group_pricing']) ? wp_unslash($_POST['trek_group_pricing']) : array();
        $clean_group_pricing = self::normalize_group_pricing_rows($group_pricing_rows);
        update_post_meta($post_id, 'trek_group_pricing', $clean_group_pricing);

        $clean_equipment = array();
        $equipment_rows = isset($_POST['trek_equipment_sections']) ? wp_unslash($_POST['trek_equipment_sections']) : array();

        if (is_array($equipment_rows)) {
            foreach ($equipment_rows as $row) {
                $heading = isset($row['heading']) ? sanitize_text_field($row['heading']) : '';
                $image_id = isset($row['image_id']) ? absint($row['image_id']) : 0;
                $items_text = isset($row['items_text']) ? (string) $row['items_text'] : '';

                $items = preg_split("/\r\n|\n|\r/", $items_text);
                $items = is_array($items) ? $items : array();
                $clean_items = array();

                foreach ($items as $item) {
                    $clean_item = sanitize_text_field($item);
                    if ($clean_item !== '') {
                        $clean_items[] = $clean_item;
                    }
                }

                if ($heading === '' && $image_id <= 0 && empty($clean_items)) {
                    continue;
                }

                $clean_equipment[] = array(
                    'heading' => $heading,
                    'image_id' => $image_id,
                    'items' => $clean_items,
                );
            }
        }

        update_post_meta($post_id, 'trek_equipment_sections', $clean_equipment);
    }

    private static function render_field($key, $post_id, $post_type)
    {
        if (!isset(self::$meta_schema[$post_type][$key])) {
            return;
        }

        $config = self::$meta_schema[$post_type][$key];
        $value = get_post_meta($post_id, $key, true);

        echo '<p><label for="' . esc_attr($key) . '"><strong>' . esc_html($config['label']) . '</strong></label><br/>';

        if ($config['type'] === 'select') {
            echo '<select class="widefat" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '">';
            foreach ($config['options'] as $option_value => $label) {
                echo '<option value="' . esc_attr($option_value) . '" ' . selected((string) $value, (string) $option_value, false) . '>' . esc_html($label) . '</option>';
            }
            echo '</select></p>';
            return;
        }

        if ($config['type'] === 'checkbox') {
            echo '<label><input type="checkbox" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="1" ' . checked((string) $value, '1', false) . ' /> ' . esc_html($config['label']) . '</label></p>';
            return;
        }

        $input_type = in_array($config['type'], array('number', 'url'), true) ? $config['type'] : 'text';
        echo '<input type="' . esc_attr($input_type) . '" class="widefat" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr((string) $value) . '"';

        if ($input_type === 'number') {
            if (isset($config['min'])) {
                echo ' min="' . esc_attr((string) $config['min']) . '"';
            }
            if (isset($config['step'])) {
                echo ' step="' . esc_attr((string) $config['step']) . '"';
            }
        }

        echo ' /></p>';
    }

    private static function sanitize_field_value($type, $raw)
    {
        if ($type === 'number') {
            if ($raw === null || $raw === '') {
                return 0;
            }

            if (strpos((string) $raw, '.') !== false) {
                return (float) $raw;
            }

            return (int) $raw;
        }

        if ($type === 'checkbox') {
            return $raw === '1' || $raw === 1 || $raw === true;
        }

        if ($type === 'url') {
            return $raw ? esc_url_raw((string) $raw) : '';
        }

        if ($type === 'select') {
            return sanitize_text_field((string) $raw);
        }

        return $raw ? sanitize_text_field((string) $raw) : '';
    }

    private static function normalize_group_pricing_rows($rows)
    {
        if (!is_array($rows)) {
            return array();
        }

        $clean_rows = array();

        foreach ($rows as $row) {
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

            $clean_rows[] = array(
                'min_people' => $min_people,
                'max_people' => $max_people,
                'price_per_person' => $price_per_person,
            );
        }

        return $clean_rows;
    }

    private static function normalize_gallery_ids($gallery)
    {
        if (is_array($gallery)) {
            $ids = $gallery;
        } else {
            $ids = explode(',', (string) $gallery);
        }

        $clean_ids = array();

        foreach ($ids as $id) {
            $clean_id = (int) $id;
            if ($clean_id > 0) {
                $clean_ids[] = $clean_id;
            }
        }

        return array_values(array_unique($clean_ids));
    }
}
