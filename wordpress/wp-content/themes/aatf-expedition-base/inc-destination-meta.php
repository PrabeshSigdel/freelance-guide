<?php
if (!defined('ABSPATH')) {
    exit;
}

/* ── Destination Info Meta Box ───────────────────────────────────── */
add_action('add_meta_boxes', function () {
    add_meta_box(
        'aatf_destination_info_metabox',
        esc_html__('Destination Information', 'aatf-expedition-base'),
        'aatf_destination_info_metabox_cb',
        'destination',
        'normal',
        'high'
    );
});

function aatf_destination_info_metabox_cb($post)
{
    wp_nonce_field('aatf_destination_info_save', 'aatf_destination_info_nonce');

    $sections = array(
        'aatf_dest_about'          => array(
            'label'       => __('About', 'aatf-expedition-base'),
            'placeholder' => __('Write a general overview of this destination...', 'aatf-expedition-base'),
        ),
        'aatf_dest_geography'      => array(
            'label'       => __('Geography & Climate', 'aatf-expedition-base'),
            'placeholder' => __('Describe the geography, terrain, altitude, seasons and climate...', 'aatf-expedition-base'),
        ),
        'aatf_dest_history'        => array(
            'label'       => __('History', 'aatf-expedition-base'),
            'placeholder' => __('Describe the history, ancient kingdoms, key historical events...', 'aatf-expedition-base'),
        ),
        'aatf_dest_language_dress' => array(
            'label'       => __('Language & Dress', 'aatf-expedition-base'),
            'placeholder' => __('Describe the languages spoken, traditional attire and cultural dress...', 'aatf-expedition-base'),
        ),
        'aatf_dest_economy'        => array(
            'label'       => __('Economy', 'aatf-expedition-base'),
            'placeholder' => __('Describe the economic activities, main industries, tourism impact...', 'aatf-expedition-base'),
        ),
    );

    $dest_title = get_the_title($post->ID);

    echo '<style>
        .aatf-dest-meta-section { margin-bottom: 1.5rem; }
        .aatf-dest-meta-section h3 {
            font-size: 0.95rem;
            font-weight: 600;
            color: #0b473a;
            margin: 0 0 0.6rem;
            padding: 0.5rem 0.75rem;
            background: #f0f6f4;
            border-left: 3px solid #0b473a;
            border-radius: 0 6px 6px 0;
        }
    </style>';

    foreach ($sections as $key => $info) {
        $label = ($key === 'aatf_dest_about' && $dest_title !== '')
            ? sprintf(__('About %s', 'aatf-expedition-base'), $dest_title)
            : $info['label'];

        $value = (string) get_post_meta($post->ID, $key, true);

        echo '<div class="aatf-dest-meta-section">';
        echo '<h3>' . esc_html($label) . '</h3>';
        wp_editor($value, $key, array(
            'textarea_name' => $key,
            'textarea_rows' => 8,
            'media_buttons' => false,
            'teeny'         => false,
            'tinymce'       => true,
            'quicktags'     => true,
        ));
        echo '</div>';
    }
}

/* ── Save Destination Info Meta ──────────────────────────────────── */
add_action('save_post_destination', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (wp_is_post_revision($post_id) || !current_user_can('edit_post', $post_id)) {
        return;
    }

    if (
        !isset($_POST['aatf_destination_info_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aatf_destination_info_nonce'])), 'aatf_destination_info_save')
    ) {
        return;
    }

    $fields = array(
        'aatf_dest_about',
        'aatf_dest_geography',
        'aatf_dest_history',
        'aatf_dest_language_dress',
        'aatf_dest_economy',
    );

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, wp_kses_post(wp_unslash($_POST[$field])));
        }
    }
});
