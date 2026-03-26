<?php

if (!defined('ABSPATH')) {
    exit;
}

class AATF_Trek_Relationships
{
    public static function register()
    {
        add_action('add_meta_boxes_departure', array(__CLASS__, 'add_departure_meta_box'));
        add_action('add_meta_boxes_trek_faq', array(__CLASS__, 'add_faq_meta_box'));
        add_action('add_meta_boxes_trek_faq', array(__CLASS__, 'add_faq_builder_meta_box'));
        add_action('save_post_departure', array(__CLASS__, 'save_departure_relationship'));
        add_action('save_post_trek_faq', array(__CLASS__, 'save_faq_relationship'));

        add_filter('manage_departure_posts_columns', array(__CLASS__, 'add_trek_column'));
        add_filter('manage_trek_faq_posts_columns', array(__CLASS__, 'add_trek_column'));
        add_action('manage_departure_posts_custom_column', array(__CLASS__, 'render_trek_column'), 10, 2);
        add_action('manage_trek_faq_posts_custom_column', array(__CLASS__, 'render_trek_column'), 10, 2);
    }

    public static function add_departure_meta_box()
    {
        add_meta_box(
            'aatf_departure_trek',
            'Departure Details',
            array(__CLASS__, 'render_departure_meta_box'),
            'departure',
            'normal',
            'high'
        );
    }

    public static function add_faq_meta_box()
    {
        add_meta_box(
            'aatf_faq_trek',
            'Linked Trek',
            array(__CLASS__, 'render_faq_linked_trek_field'),
            'trek_faq',
            'side',
            'default'
        );
    }

    public static function add_faq_builder_meta_box()
    {
        add_meta_box(
            'aatf_faq_builder',
            'FAQ Builder (By Topic)',
            array(__CLASS__, 'render_faq_builder_meta_box'),
            'trek_faq',
            'normal',
            'high'
        );
    }

    public static function render_departure_meta_box($post)
    {
        wp_nonce_field('aatf_departure_meta_nonce_action', 'aatf_departure_meta_nonce');

        $trek_id = (int) get_post_meta($post->ID, 'aatf_trek_id', true);
        $start_date = get_post_meta($post->ID, 'departure_start_date', true);
        $end_date = get_post_meta($post->ID, 'departure_end_date', true);
        $price = get_post_meta($post->ID, 'departure_price', true);
        $status = get_post_meta($post->ID, 'departure_status', true);

        $treks = get_posts(array(
            'post_type' => 'trek',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ));

        echo '<div class="aatf-meta-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">';
        
        echo '<div>';
        echo '<label for="aatf_trek_id"><strong>Linked Trek</strong></label><br />';
        echo '<select name="aatf_trek_id" id="aatf_trek_id" class="widefat">';
        echo '<option value="">-- Select Trek --</option>';
        foreach ($treks as $trek) {
            echo '<option value="' . esc_attr($trek->ID) . '" ' . selected($trek_id, $trek->ID, false) . '>' . esc_html($trek->post_title) . '</option>';
        }
        echo '</select>';
        echo '</div>';

        echo '<div>';
        echo '<label for="departure_status"><strong>Availability Status</strong></label><br />';
        echo '<select name="departure_status" id="departure_status" class="widefat">';
        $statuses = array('available' => 'Available', 'limited' => 'Limited', 'guaranteed' => 'Guaranteed', 'full' => 'Full');
        foreach ($statuses as $val => $label) {
            echo '<option value="' . esc_attr($val) . '" ' . selected($status, $val, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '</div>';

        echo '<div>';
        echo '<label for="departure_start_date"><strong>Start Date</strong></label><br />';
        echo '<input type="date" name="departure_start_date" id="departure_start_date" value="' . esc_attr($start_date) . '" class="widefat" />';
        echo '</div>';

        echo '<div>';
        echo '<label for="departure_end_date"><strong>End Date</strong></label><br />';
        echo '<input type="date" name="departure_end_date" id="departure_end_date" value="' . esc_attr($end_date) . '" class="widefat" />';
        echo '</div>';

        echo '<div>';
        echo '<label for="departure_price"><strong>Special Price (optional)</strong></label><br />';
        echo '<input type="number" step="0.01" name="departure_price" id="departure_price" value="' . esc_attr($price) . '" class="widefat" />';
        echo '<p class="description">Override base trek price for this date if set.</p>';
        echo '</div>';

        echo '</div>';
    }

    public static function save_departure_relationship($post_id)
    {
        if (!isset($_POST['aatf_departure_meta_nonce']) || !wp_verify_nonce($_POST['aatf_departure_meta_nonce'], 'aatf_departure_meta_nonce_action')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $fields = array(
            'aatf_trek_id' => 'int',
            'departure_start_date' => 'text',
            'departure_end_date' => 'text',
            'departure_price' => 'float',
            'departure_status' => 'text',
        );

        foreach ($fields as $field => $type) {
            if (isset($_POST[$field])) {
                $value = wp_unslash($_POST[$field]);
                if ($type === 'int') {
                    $value = (int) $value;
                } elseif ($type === 'float') {
                    $value = is_numeric($value) ? (float) $value : '';
                } else {
                    $value = sanitize_text_field($value);
                }
                update_post_meta($post_id, $field, $value);
            }
        }
    }

    public static function render_faq_linked_trek_field($post)
    {
        wp_nonce_field('aatf_faq_trek_nonce_action', 'aatf_faq_trek_nonce');

        $selected = (int) get_post_meta($post->ID, 'aatf_trek_id', true);
        $treks = get_posts(array(
            'post_type' => 'trek',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ));

        echo '<label for="aatf_trek_id"><strong>Select Trek</strong></label><br />';
        echo '<select name="aatf_trek_id" id="aatf_trek_id" class="widefat">';
        echo '<option value="">-- Select Trek --</option>';
        foreach ($treks as $trek) {
            echo '<option value="' . esc_attr($trek->ID) . '" ' . selected($selected, $trek->ID, false) . '>' . esc_html($trek->post_title) . '</option>';
        }
        echo '</select>';
    }

    public static function render_faq_builder_meta_box($post)
    {
        wp_nonce_field('aatf_faq_builder_nonce_action', 'aatf_faq_builder_nonce');

        $groups = get_post_meta($post->ID, 'aatf_faq_topic_groups', true);
        $groups = is_array($groups) ? $groups : array();

        $topics = get_terms(array(
            'taxonomy' => 'faq_topic',
            'hide_empty' => false,
        ));

        if (empty($groups)) {
            $groups[] = array(
                'topic_id' => 0,
                'faqs' => array(
                    array('question' => '', 'answer' => ''),
                ),
            );
        }

        echo '<p><em>Select topic, then add multiple question/answer rows. Add another topic block for another FAQ topic.</em></p>';
        echo '<div id="aatf-faq-topic-groups">';

        foreach ($groups as $group_index => $group) {
            $topic_id = isset($group['topic_id']) ? (int) $group['topic_id'] : 0;
            $faqs = isset($group['faqs']) && is_array($group['faqs']) ? $group['faqs'] : array();
            if (empty($faqs)) {
                $faqs[] = array('question' => '', 'answer' => '');
            }

            echo '<div class="aatf-faq-topic-group">';
            echo '<p><label><strong>FAQ Topic</strong></label><br />';
            echo '<select class="widefat" name="aatf_faq_topic_groups[' . esc_attr((string) $group_index) . '][topic_id]">';
            echo '<option value="0">-- Select Topic --</option>';
            if (!is_wp_error($topics) && !empty($topics)) {
                foreach ($topics as $topic) {
                    echo '<option value="' . esc_attr((string) $topic->term_id) . '" ' . selected($topic_id, (int) $topic->term_id, false) . '>' . esc_html((string) $topic->name) . '</option>';
                }
            }
            echo '</select></p>';

            echo '<div class="aatf-faq-qa-rows">';
            foreach ($faqs as $faq_index => $faq_row) {
                $question = isset($faq_row['question']) ? (string) $faq_row['question'] : '';
                $answer = isset($faq_row['answer']) ? (string) $faq_row['answer'] : '';

                echo '<div class="aatf-faq-qa-row">';
                echo '<p><label><strong>Question</strong></label><input type="text" class="widefat" name="aatf_faq_topic_groups[' . esc_attr((string) $group_index) . '][faqs][' . esc_attr((string) $faq_index) . '][question]" value="' . esc_attr($question) . '" /></p>';
                echo '<p><label><strong>Answer</strong></label><textarea class="widefat" rows="4" name="aatf_faq_topic_groups[' . esc_attr((string) $group_index) . '][faqs][' . esc_attr((string) $faq_index) . '][answer]">' . esc_textarea($answer) . '</textarea></p>';
                echo '<p><button type="button" class="button aatf-remove-faq-qa-row">Remove Q/A</button></p>';
                echo '</div>';
            }
            echo '</div>';

            echo '<p>';
            echo '<button type="button" class="button button-secondary aatf-add-faq-qa-row">Add Q/A</button> ';
            echo '<button type="button" class="button aatf-remove-faq-topic-group">Remove Topic Block</button>';
            echo '</p>';
            echo '</div>';
        }

        echo '</div>';
        echo '<p><button type="button" class="button button-secondary" id="aatf-add-faq-topic-group">Add Another Topic Block</button></p>';

        echo '<script type="text/html" id="tmpl-aatf-faq-topic-group">';
        echo '<div class="aatf-faq-topic-group">';
        echo '<p><label><strong>FAQ Topic</strong></label><br />';
        echo '<select class="widefat" name="aatf_faq_topic_groups[{{data.groupIndex}}][topic_id]">';
        echo '<option value="0">-- Select Topic --</option>';
        if (!is_wp_error($topics) && !empty($topics)) {
            foreach ($topics as $topic) {
                echo '<option value="' . esc_attr((string) $topic->term_id) . '">' . esc_html((string) $topic->name) . '</option>';
            }
        }
        echo '</select></p>';
        echo '<div class="aatf-faq-qa-rows">';
        echo '<div class="aatf-faq-qa-row">';
        echo '<p><label><strong>Question</strong></label><input type="text" class="widefat" name="aatf_faq_topic_groups[{{data.groupIndex}}][faqs][0][question]" value="" /></p>';
        echo '<p><label><strong>Answer</strong></label><textarea class="widefat" rows="4" name="aatf_faq_topic_groups[{{data.groupIndex}}][faqs][0][answer]"></textarea></p>';
        echo '<p><button type="button" class="button aatf-remove-faq-qa-row">Remove Q/A</button></p>';
        echo '</div>';
        echo '</div>';
        echo '<p>';
        echo '<button type="button" class="button button-secondary aatf-add-faq-qa-row">Add Q/A</button> ';
        echo '<button type="button" class="button aatf-remove-faq-topic-group">Remove Topic Block</button>';
        echo '</p>';
        echo '</div>';
        echo '</script>';

        echo '<script type="text/html" id="tmpl-aatf-faq-qa-row">';
        echo '<div class="aatf-faq-qa-row">';
        echo '<p><label><strong>Question</strong></label><input type="text" class="widefat" name="aatf_faq_topic_groups[{{data.groupIndex}}][faqs][{{data.faqIndex}}][question]" value="" /></p>';
        echo '<p><label><strong>Answer</strong></label><textarea class="widefat" rows="4" name="aatf_faq_topic_groups[{{data.groupIndex}}][faqs][{{data.faqIndex}}][answer]"></textarea></p>';
        echo '<p><button type="button" class="button aatf-remove-faq-qa-row">Remove Q/A</button></p>';
        echo '</div>';
        echo '</script>';
    }

    public static function save_faq_relationship($post_id)
    {
        $has_trek_nonce = isset($_POST['aatf_faq_trek_nonce']) && wp_verify_nonce((string) wp_unslash($_POST['aatf_faq_trek_nonce']), 'aatf_faq_trek_nonce_action');
        $has_builder_nonce = isset($_POST['aatf_faq_builder_nonce']) && wp_verify_nonce((string) wp_unslash($_POST['aatf_faq_builder_nonce']), 'aatf_faq_builder_nonce_action');

        if (!$has_trek_nonce && !$has_builder_nonce) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $trek_id = isset($_POST['aatf_trek_id']) ? (int) $_POST['aatf_trek_id'] : 0;
        if ($trek_id > 0) {
            update_post_meta($post_id, 'aatf_trek_id', $trek_id);
        } else {
            delete_post_meta($post_id, 'aatf_trek_id');
        }

        $topic_groups = isset($_POST['aatf_faq_topic_groups']) ? wp_unslash($_POST['aatf_faq_topic_groups']) : array();
        $clean_groups = array();
        $topic_ids = array();

        if (is_array($topic_groups)) {
            foreach ($topic_groups as $group) {
                $topic_id = isset($group['topic_id']) ? absint($group['topic_id']) : 0;
                $faqs = isset($group['faqs']) && is_array($group['faqs']) ? $group['faqs'] : array();

                if ($topic_id <= 0 || !term_exists($topic_id, 'faq_topic')) {
                    continue;
                }

                $clean_faqs = array();
                foreach ($faqs as $faq_row) {
                    $question = isset($faq_row['question']) ? sanitize_text_field((string) $faq_row['question']) : '';
                    $answer = isset($faq_row['answer']) ? sanitize_textarea_field((string) $faq_row['answer']) : '';

                    if ($question === '' && $answer === '') {
                        continue;
                    }

                    $clean_faqs[] = array(
                        'question' => $question,
                        'answer' => $answer,
                    );
                }

                if (empty($clean_faqs)) {
                    continue;
                }

                $clean_groups[] = array(
                    'topic_id' => $topic_id,
                    'faqs' => $clean_faqs,
                );

                $topic_ids[] = $topic_id;
            }
        }

        if (!empty($clean_groups)) {
            update_post_meta($post_id, 'aatf_faq_topic_groups', $clean_groups);
            wp_set_object_terms($post_id, array_values(array_unique($topic_ids)), 'faq_topic', false);
        } else {
            delete_post_meta($post_id, 'aatf_faq_topic_groups');
            wp_set_object_terms($post_id, array(), 'faq_topic', false);
        }
    }

    public static function add_trek_column($columns)
    {
        $columns['aatf_linked_trek'] = 'Linked Trek';
        return $columns;
    }

    public static function render_trek_column($column, $post_id)
    {
        if ($column !== 'aatf_linked_trek') {
            return;
        }

        $trek_id = (int) get_post_meta($post_id, 'aatf_trek_id', true);

        if ($trek_id <= 0) {
            echo 'Not linked';
            return;
        }

        $title = get_the_title($trek_id);
        if (!$title) {
            echo 'Missing Trek';
            return;
        }

        echo esc_html($title);
    }
}
