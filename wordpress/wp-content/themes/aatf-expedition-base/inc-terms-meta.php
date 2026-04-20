<?php
// Terms and Conditions Page Template Meta Box
add_action('add_meta_boxes_page', function ($post) {
    if (!$post instanceof WP_Post) {
        return;
    }

    $page_template = (string) get_page_template_slug($post->ID);

    if ($page_template !== 'page-templates/template-terms.php') {
        return;
    }

    add_meta_box(
        'aatf_terms_info_metabox',
        esc_html__('Terms and Conditions Content', 'aatf-expedition-base'),
        function ($post) {
            wp_nonce_field('aatf_terms_info_save', 'aatf_terms_info_nonce');

            // Main Content Section
            $main_content = get_post_meta($post->ID, 'aatf_terms_main_content', true);

            echo '<h3>' . esc_html__('Terms Content', 'aatf-expedition-base') . '</h3>';
            echo '<p><label for="aatf_terms_main_content"><strong>' . esc_html__('Terms & Conditions Text', 'aatf-expedition-base') . '</strong></label></p>';
            wp_editor(
                $main_content,
                'aatf_terms_main_content',
                array(
                    'textarea_name' => 'aatf_terms_main_content',
                    'textarea_rows' => 24,
                    'media_buttons' => false,
                    'teeny'         => false,
                    'tinymce'       => array(
                        'toolbar1' => 'formatselect bold italic underline | bullist numlist | blockquote hr | link unlink | undo redo',
                        'toolbar2' => '',
                        'block_formats' => 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4',
                    ),
                    'quicktags'     => true,
                )
            );
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

    if (!isset($_POST['aatf_terms_info_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aatf_terms_info_nonce'])), 'aatf_terms_info_save')) {
        return;
    }

    // Main Content
    if (isset($_POST['aatf_terms_main_content'])) {
        update_post_meta($post_id, 'aatf_terms_main_content', wp_kses_post(wp_unslash($_POST['aatf_terms_main_content'])));
    }
});
