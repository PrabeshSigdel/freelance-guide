<?php
/**
 * Gallery Album Meta Box
 * Renders a meta box on gallery_album posts for managing cover image,
 * gallery images (repeatable), description, and display order.
 */

if (!defined('ABSPATH')) {
    exit;
}

// ---------------------------------------------------------------------------
// Register meta box
// ---------------------------------------------------------------------------
add_action('add_meta_boxes_gallery_album', function ($post) {
    if (!$post instanceof WP_Post) {
        return;
    }

    add_meta_box(
        'aatf_gallery_album_metabox',
        esc_html__('Gallery Album Details', 'aatf-expedition-base'),
        'aatf_render_gallery_album_metabox',
        'gallery_album',
        'normal',
        'high'
    );
});

// ---------------------------------------------------------------------------
// Render callback
// ---------------------------------------------------------------------------
function aatf_render_gallery_album_metabox(WP_Post $post)
{
    wp_nonce_field('aatf_gallery_album_save', 'aatf_gallery_album_nonce');

    // Retrieve saved values
    $description = (string) get_post_meta($post->ID, 'aatf_gallery_description', true);
    $order       = (int)    get_post_meta($post->ID, 'aatf_gallery_order', true);
    $image_ids   = get_post_meta($post->ID, 'aatf_gallery_image_ids', true);
    $image_ids   = is_array($image_ids) ? array_values(array_filter(array_map('absint', $image_ids))) : array();

    // ---- Section: Description & Order -----
    echo '<h3 style="margin-top:10px;">' . esc_html__('Album Info', 'aatf-expedition-base') . '</h3>';

    echo '<p><label for="aatf_gallery_description"><strong>' . esc_html__('Album Description', 'aatf-expedition-base') . '</strong></label></p>';
    echo '<p><textarea class="widefat" rows="3" id="aatf_gallery_description" name="aatf_gallery_description" placeholder="' . esc_attr__('Short description of this album...', 'aatf-expedition-base') . '">' . esc_textarea($description) . '</textarea></p>';

    echo '<p><label for="aatf_gallery_order"><strong>' . esc_html__('Display Order', 'aatf-expedition-base') . '</strong></label></p>';
    echo '<p><input type="number" id="aatf_gallery_order" name="aatf_gallery_order" value="' . esc_attr((string) $order) . '" min="0" step="1" style="width:100px;"> <span class="description">' . esc_html__('Lower numbers appear first.', 'aatf-expedition-base') . '</span></p>';

    echo '<hr />';

    // ---- Section: Gallery Images ----
    echo '<h3>' . esc_html__('Gallery Images', 'aatf-expedition-base') . '</h3>';
    echo '<p class="description">' . esc_html__('Add all photos for this album. Drag to reorder. The first image is used as a fallback cover if no Featured Image is set.', 'aatf-expedition-base') . '</p>';
    echo '<br>';

    // Hidden serialised input – JS writes to this
    echo '<input type="hidden" id="aatf_gallery_image_ids" name="aatf_gallery_image_ids" value="' . esc_attr(implode(',', $image_ids)) . '">';

    // Thumbnail strip
    echo '<div id="aatf-gallery-image-strip" style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:14px;">';
    foreach ($image_ids as $img_id) {
        $src = wp_get_attachment_image_url($img_id, 'thumbnail');
        if (!$src) {
            continue;
        }
        printf(
            '<div class="aatf-gallery-thumb" data-id="%d" style="position:relative;width:90px;height:90px;border-radius:6px;overflow:hidden;border:2px solid #ddd;cursor:grab;">'
            . '<img src="%s" style="width:100%%;height:100%%;object-fit:cover;">'
            . '<button type="button" class="aatf-gallery-remove" data-id="%d" title="%s" style="position:absolute;top:3px;right:3px;background:#cc3333;color:#fff;border:none;border-radius:50%%;width:22px;height:22px;cursor:pointer;font-size:14px;line-height:1;padding:0;">&times;</button>'
            . '</div>',
            esc_attr((string) $img_id),
            esc_url($src),
            esc_attr((string) $img_id),
            esc_attr__('Remove', 'aatf-expedition-base')
        );
    }
    echo '</div>';

    echo '<button type="button" id="aatf_gallery_add_images" class="button button-primary">'
        . '<span class="dashicons dashicons-plus" style="line-height:1.6;margin-right:4px;"></span> '
        . esc_html__('Add Images', 'aatf-expedition-base')
        . '</button>';

    echo '<hr />';

    // ---- JS ----
    ?>
    <style>
        #aatf-gallery-image-strip .aatf-gallery-thumb { box-sizing: border-box; }
        #aatf-gallery-image-strip .aatf-gallery-thumb:hover { border-color: #f5740a; }
        .aatf-gallery-remove:hover { background: #aa0000 !important; }
    </style>
    <script>
    (function($){
        $(function(){

            var frame;

            // Collect IDs from the strip and write to the hidden input
            function syncIds() {
                var ids = [];
                $('#aatf-gallery-image-strip .aatf-gallery-thumb').each(function(){
                    ids.push($(this).data('id'));
                });
                $('#aatf_gallery_image_ids').val(ids.join(','));
            }

            // Build a thumb element for a single attachment
            function buildThumb(att) {
                var src = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
                return $('<div>')
                    .addClass('aatf-gallery-thumb')
                    .attr('data-id', att.id)
                    .css({ position:'relative', width:'90px', height:'90px', borderRadius:'6px', overflow:'hidden', border:'2px solid #ddd', cursor:'grab' })
                    .append(
                        $('<img>').attr('src', src).css({ width:'100%', height:'100%', objectFit:'cover' })
                    )
                    .append(
                        $('<button>').attr({
                            type: 'button',
                            title: '<?php echo esc_js(__('Remove', 'aatf-expedition-base')); ?>'
                        })
                        .addClass('aatf-gallery-remove')
                        .attr('data-id', att.id)
                        .html('&times;')
                        .css({ position:'absolute', top:'3px', right:'3px', background:'#cc3333', color:'#fff', border:'none', borderRadius:'50%', width:'22px', height:'22px', cursor:'pointer', fontSize:'14px', lineHeight:'1', padding:'0' })
                    );
            }

            // Open media frame
            $('#aatf_gallery_add_images').on('click', function(e){
                e.preventDefault();

                if (frame) {
                    frame.open();
                    return;
                }

                frame = wp.media({
                    title:    '<?php echo esc_js(__('Select Gallery Images', 'aatf-expedition-base')); ?>',
                    button:   { text: '<?php echo esc_js(__('Add to Gallery', 'aatf-expedition-base')); ?>' },
                    multiple: true,
                    library:  { type: 'image' }
                });

                frame.on('select', function(){
                    var selection = frame.state().get('selection');
                    selection.each(function(attachment){
                        var att = attachment.toJSON();
                        // Skip duplicates
                        if ($('#aatf-gallery-image-strip .aatf-gallery-thumb[data-id="' + att.id + '"]').length) {
                            return;
                        }
                        $('#aatf-gallery-image-strip').append( buildThumb(att) );
                    });
                    syncIds();
                });

                frame.open();
            });

            // Remove single image
            $(document).on('click', '.aatf-gallery-remove', function(){
                $(this).closest('.aatf-gallery-thumb').remove();
                syncIds();
            });

            // Sortable drag-to-reorder
            if ($.fn.sortable) {
                $('#aatf-gallery-image-strip').sortable({
                    items: '.aatf-gallery-thumb',
                    tolerance: 'pointer',
                    update: function(){ syncIds(); }
                });
            }

        });
    }(jQuery));
    </script>
    <?php
}

// ---------------------------------------------------------------------------
// Save meta
// ---------------------------------------------------------------------------
add_action('save_post_gallery_album', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (wp_is_post_revision($post_id) || !current_user_can('edit_post', $post_id)) {
        return;
    }

    if (
        !isset($_POST['aatf_gallery_album_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aatf_gallery_album_nonce'])), 'aatf_gallery_album_save')
    ) {
        return;
    }

    // Description
    if (isset($_POST['aatf_gallery_description'])) {
        update_post_meta($post_id, 'aatf_gallery_description', sanitize_textarea_field(wp_unslash($_POST['aatf_gallery_description'])));
    }

    // Display order
    if (isset($_POST['aatf_gallery_order'])) {
        update_post_meta($post_id, 'aatf_gallery_order', absint(wp_unslash($_POST['aatf_gallery_order'])));
    }

    // Gallery image IDs (comma-separated from hidden input)
    if (isset($_POST['aatf_gallery_image_ids'])) {
        $raw  = sanitize_text_field(wp_unslash($_POST['aatf_gallery_image_ids']));
        $parts = $raw !== '' ? explode(',', $raw) : array();
        $ids   = array_values(array_filter(array_map('absint', $parts)));
        if (!empty($ids)) {
            update_post_meta($post_id, 'aatf_gallery_image_ids', $ids);
        } else {
            delete_post_meta($post_id, 'aatf_gallery_image_ids');
        }
    }
});
