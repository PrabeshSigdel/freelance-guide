<?php
// About Us Page Template Meta Box
add_action('add_meta_boxes_page', function ($post) {
    if (!$post instanceof WP_Post) {
        return;
    }

    $page_template = (string) get_page_template_slug($post->ID);

    if ($page_template !== 'page-templates/template-about.php') {
        return;
    }

    add_meta_box(
        'aatf_about_info_metabox',
        esc_html__('About Us Details', 'aatf-expedition-base'),
        function ($post) {
            wp_nonce_field('aatf_about_info_save', 'aatf_about_info_nonce');

            // Hero Section
            $hero_image_id  = (int) get_post_meta($post->ID, 'aatf_about_hero_image_id', true);
            $hero_image_url = $hero_image_id > 0 ? wp_get_attachment_image_url($hero_image_id, 'large') : '';

            echo '<h3>' . esc_html__('Hero Section', 'aatf-expedition-base') . '</h3>';
            echo '<p><label><strong>' . esc_html__('Hero / Banner Image', 'aatf-expedition-base') . '</strong></label></p>';
            echo '<div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">';
            if ($hero_image_url) {
                echo '<img id="aatf_about_hero_preview" src="' . esc_url($hero_image_url) . '" style="max-width:200px;max-height:100px;border-radius:6px;border:1px solid #ddd;">';
            } else {
                echo '<img id="aatf_about_hero_preview" src="" style="max-width:200px;max-height:100px;display:none;border-radius:6px;border:1px solid #ddd;">';
            }
            echo '</div>';
            echo '<input type="hidden" id="aatf_about_hero_image_id" name="aatf_about_hero_image_id" value="' . esc_attr((string) $hero_image_id) . '">';
            echo '<p>';
            echo '<button type="button" id="aatf_about_hero_select" class="button">' . esc_html__('Select Image', 'aatf-expedition-base') . '</button> ';
            echo '<button type="button" id="aatf_about_hero_remove" class="button button-link-delete" ' . ($hero_image_id < 1 ? 'style="display:none"' : '') . '>' . esc_html__('Remove Image', 'aatf-expedition-base') . '</button>';
            echo '</p>';
            echo '<hr />';

            // Our Story Section
            $story_title = get_post_meta($post->ID, 'aatf_about_story_title', true);
            $story_content = get_post_meta($post->ID, 'aatf_about_story_content', true);
            $story_image_id  = (int) get_post_meta($post->ID, 'aatf_about_story_image_id', true);
            $story_image_url = $story_image_id > 0 ? wp_get_attachment_image_url($story_image_id, 'large') : '';

            echo '<h3>' . esc_html__('Our Story Section', 'aatf-expedition-base') . '</h3>';
            echo '<p><label for="aatf_about_story_title"><strong>' . esc_html__('Story Title', 'aatf-expedition-base') . '</strong></label></p>';
            echo '<p><input type="text" class="widefat" id="aatf_about_story_title" name="aatf_about_story_title" value="' . esc_attr($story_title) . '" placeholder="Our Journey into the Wild"></p>';

            echo '<p><label for="aatf_about_story_content"><strong>' . esc_html__('Story Content (Paragraphs)', 'aatf-expedition-base') . '</strong></label></p>';
            echo '<p><textarea class="widefat" rows="6" id="aatf_about_story_content" name="aatf_about_story_content">' . esc_textarea($story_content) . '</textarea></p>';

            echo '<p><label><strong>' . esc_html__('Story Side Image', 'aatf-expedition-base') . '</strong></label></p>';
            echo '<div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">';
            if ($story_image_url) {
                echo '<img id="aatf_about_story_preview" src="' . esc_url($story_image_url) . '" style="max-width:200px;max-height:100px;border-radius:6px;border:1px solid #ddd;">';
            } else {
                echo '<img id="aatf_about_story_preview" src="" style="max-width:200px;max-height:100px;display:none;border-radius:6px;border:1px solid #ddd;">';
            }
            echo '</div>';
            echo '<input type="hidden" id="aatf_about_story_image_id" name="aatf_about_story_image_id" value="' . esc_attr((string) $story_image_id) . '">';
            echo '<p>';
            echo '<button type="button" id="aatf_about_story_select" class="button">' . esc_html__('Select Image', 'aatf-expedition-base') . '</button> ';
            echo '<button type="button" id="aatf_about_story_remove" class="button button-link-delete" ' . ($story_image_id < 1 ? 'style="display:none"' : '') . '>' . esc_html__('Remove Image', 'aatf-expedition-base') . '</button>';
            echo '</p>';
            echo '<hr />';

            // Values Section
            $values_subtitle = get_post_meta($post->ID, 'aatf_about_values_subtitle', true);
            $values_title = get_post_meta($post->ID, 'aatf_about_values_title', true);
            $values_desc = get_post_meta($post->ID, 'aatf_about_values_desc', true);

            $mission_title = get_post_meta($post->ID, 'aatf_about_mission_title', true);
            $mission_text = get_post_meta($post->ID, 'aatf_about_mission_text', true);

            $vision_title = get_post_meta($post->ID, 'aatf_about_vision_title', true);
            $vision_text = get_post_meta($post->ID, 'aatf_about_vision_text', true);

            $core_title = get_post_meta($post->ID, 'aatf_about_core_title', true);
            $core_text = get_post_meta($post->ID, 'aatf_about_core_text', true);

            echo '<h3>' . esc_html__('Mission & Values Section', 'aatf-expedition-base') . '</h3>';
            echo '<p><label for="aatf_about_values_title"><strong>' . esc_html__('Section Title', 'aatf-expedition-base') . '</strong></label></p>';
            echo '<p><input type="text" class="widefat" id="aatf_about_values_title" name="aatf_about_values_title" value="' . esc_attr($values_title) . '" placeholder="The Values We Live By"></p>';
            echo '<p><label for="aatf_about_values_desc"><strong>' . esc_html__('Section Description', 'aatf-expedition-base') . '</strong></label></p>';
            echo '<p><textarea class="widefat" rows="2" id="aatf_about_values_desc" name="aatf_about_values_desc">' . esc_textarea($values_desc) . '</textarea></p>';

            echo '<h4>' . esc_html__('Mission Card', 'aatf-expedition-base') . '</h4>';
            echo '<p><input type="text" class="widefat" name="aatf_about_mission_title" value="' . esc_attr($mission_title) . '" placeholder="Our Mission"></p>';
            echo '<p><textarea class="widefat" rows="2" name="aatf_about_mission_text" placeholder="Mission text...">' . esc_textarea($mission_text) . '</textarea></p>';

            echo '<h4>' . esc_html__('Vision Card', 'aatf-expedition-base') . '</h4>';
            echo '<p><input type="text" class="widefat" name="aatf_about_vision_title" value="' . esc_attr($vision_title) . '" placeholder="Our Vision"></p>';
            echo '<p><textarea class="widefat" rows="2" name="aatf_about_vision_text" placeholder="Vision text...">' . esc_textarea($vision_text) . '</textarea></p>';

            echo '<h4>' . esc_html__('Values Card', 'aatf-expedition-base') . '</h4>';
            echo '<p><input type="text" class="widefat" name="aatf_about_core_title" value="' . esc_attr($core_title) . '" placeholder="Our Values"></p>';
            echo '<p><textarea class="widefat" rows="2" name="aatf_about_core_text" placeholder="Values text...">' . esc_textarea($core_text) . '</textarea></p>';

            echo '<hr />';

            // Why Us Section
            $whyus_subtitle = get_post_meta($post->ID, 'aatf_about_whyus_subtitle', true);
            $whyus_title = get_post_meta($post->ID, 'aatf_about_whyus_title', true);
            $whyus_desc = get_post_meta($post->ID, 'aatf_about_whyus_desc', true);
            $whyus_points = get_post_meta($post->ID, 'aatf_about_whyus_points', true); // Stored as array or newline string

            echo '<h3>' . esc_html__('Why Choose Us Section', 'aatf-expedition-base') . '</h3>';
            echo '<p><label for="aatf_about_whyus_title"><strong>' . esc_html__('Section Title', 'aatf-expedition-base') . '</strong></label></p>';
            echo '<p><input type="text" class="widefat" id="aatf_about_whyus_title" name="aatf_about_whyus_title" value="' . esc_attr($whyus_title) . '" placeholder="Expertise in Every Step"></p>';
            echo '<p><label for="aatf_about_whyus_desc"><strong>' . esc_html__('Section Description', 'aatf-expedition-base') . '</strong></label></p>';
            echo '<p><textarea class="widefat" rows="2" id="aatf_about_whyus_desc" name="aatf_about_whyus_desc">' . esc_textarea($whyus_desc) . '</textarea></p>';

            echo '<h4>' . esc_html__('Benefits (One per line: Title|Description)', 'aatf-expedition-base') . '</h4>';
            echo '<p><span class="description">' . esc_html__('Format: Title|Description. E.g. Expert Local Guides|Local experts who know every hidden trail.', 'aatf-expedition-base') . '</span></p>';
            echo '<p><textarea class="widefat" rows="6" name="aatf_about_whyus_points">' . esc_textarea($whyus_points) . '</textarea></p>';

            ?>
            <script>
            (function($){
                $(function(){
                    // Hero Image
                    var heroFrame;
                    $('#aatf_about_hero_select').on('click', function(e){
                        e.preventDefault();
                        if (heroFrame) { heroFrame.open(); return; }
                        heroFrame = wp.media({ title: '<?php echo esc_js(__('Select Hero Image', 'aatf-expedition-base')); ?>', button: { text: '<?php echo esc_js(__('Use this image', 'aatf-expedition-base')); ?>' }, multiple: false });
                        heroFrame.on('select', function(){
                            var att = heroFrame.state().get('selection').first().toJSON();
                            $('#aatf_about_hero_image_id').val(att.id);
                            var src = (att.sizes && att.sizes.large) ? att.sizes.large.url : att.url;
                            $('#aatf_about_hero_preview').attr('src', src).show();
                            $('#aatf_about_hero_remove').show();
                        });
                        heroFrame.open();
                    });
                    $('#aatf_about_hero_remove').on('click', function(){
                        $('#aatf_about_hero_image_id').val('0');
                        $('#aatf_about_hero_preview').attr('src','').hide();
                        $(this).hide();
                    });

                    // Story Image
                    var storyFrame;
                    $('#aatf_about_story_select').on('click', function(e){
                        e.preventDefault();
                        if (storyFrame) { storyFrame.open(); return; }
                        storyFrame = wp.media({ title: '<?php echo esc_js(__('Select Story Image', 'aatf-expedition-base')); ?>', button: { text: '<?php echo esc_js(__('Use this image', 'aatf-expedition-base')); ?>' }, multiple: false });
                        storyFrame.on('select', function(){
                            var att = storyFrame.state().get('selection').first().toJSON();
                            $('#aatf_about_story_image_id').val(att.id);
                            var src = (att.sizes && att.sizes.large) ? att.sizes.large.url : att.url;
                            $('#aatf_about_story_preview').attr('src', src).show();
                            $('#aatf_about_story_remove').show();
                        });
                        storyFrame.open();
                    });
                    $('#aatf_about_story_remove').on('click', function(){
                        $('#aatf_about_story_image_id').val('0');
                        $('#aatf_about_story_preview').attr('src','').hide();
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

    if (!isset($_POST['aatf_about_info_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aatf_about_info_nonce'])), 'aatf_about_info_save')) {
        return;
    }

    $fields = array(
        'aatf_about_story_title',
        'aatf_about_values_title',
        'aatf_about_mission_title',
        'aatf_about_vision_title',
        'aatf_about_core_title',
        'aatf_about_whyus_title'
    );
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field(wp_unslash($_POST[$field])));
        }
    }

    $textareas = array(
        'aatf_about_story_content',
        'aatf_about_values_desc',
        'aatf_about_mission_text',
        'aatf_about_vision_text',
        'aatf_about_core_text',
        'aatf_about_whyus_desc',
        'aatf_about_whyus_points'
    );
    foreach ($textareas as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_textarea_field(wp_unslash($_POST[$field])));
        }
    }

    $images = array('aatf_about_hero_image_id', 'aatf_about_story_image_id');
    foreach ($images as $field) {
        if (isset($_POST[$field])) {
            $img_id = absint(wp_unslash($_POST[$field]));
            if ($img_id > 0) {
                update_post_meta($post_id, $field, $img_id);
            } else {
                delete_post_meta($post_id, $field);
            }
        }
    }
});
