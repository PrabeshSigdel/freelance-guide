<?php
/**
 * Template Name: Gallery
 *
 * Full-page gallery template showing album-based, categorized photo gallery.
 * Albums are managed via AATF > Gallery Albums in the admin.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$post_id = get_the_ID();

// ---- Hero content ----
$hero_title    = (string) get_post_meta($post_id, 'aatf_gallery_hero_title', true);
$hero_subtitle = (string) get_post_meta($post_id, 'aatf_gallery_hero_subtitle', true);
$hero_image_id = (int)    get_post_meta($post_id, 'aatf_gallery_hero_image_id', true);
$hero_bg_url   = $hero_image_id > 0 ? (string) wp_get_attachment_image_url($hero_image_id, 'full') : '';

// Fallback copy
if ($hero_title === '') {
    $hero_title = (string) get_the_title($post_id) ?: 'Our Gallery';
}
if ($hero_subtitle === '') {
    $hero_subtitle = 'Explore our adventures through the lens — mountains, trails & unforgettable moments.';
}

// ---- Gallery categories ----
$terms = get_terms(array(
    'taxonomy'   => 'gallery_category',
    'hide_empty' => true,
    'orderby'    => 'name',
    'order'      => 'ASC',
));

if (is_wp_error($terms) || !is_array($terms)) {
    $terms = array();
}

// ---- All albums ----
$all_albums = get_posts(array(
    'post_type'      => 'gallery_album',
    'post_status'    => 'publish',
    'numberposts'    => -1,
    'orderby'        => array('meta_value_num' => 'ASC', 'date' => 'DESC'),
    'meta_key'       => 'aatf_gallery_order',
));

?>

<div class="site-main">
    <?php
    include get_template_directory() . '/template-parts/gallery/album-grid.php';
    ?>
</div>

<?php get_footer(); ?>
