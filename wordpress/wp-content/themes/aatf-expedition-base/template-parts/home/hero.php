<?php
if (!defined('ABSPATH')) {
    exit;
}

$hero_title = trim((string) get_theme_mod('aatf_home_hero_title', ''));
if ($hero_title === '') {
    $hero_title = (string) get_bloginfo('name');
}

$hero_text = trim((string) get_theme_mod('aatf_home_hero_text', ''));
if ($hero_text === '') {
    $hero_text = (string) get_bloginfo('description');
}

$hero_media_type = (string) get_theme_mod('aatf_home_hero_media_type', 'none');
if (!in_array($hero_media_type, array('none', 'image', 'video'), true)) {
    $hero_media_type = 'none';
}

$hero_image_raw = get_theme_mod('aatf_home_hero_image', '');
$hero_image = '';
if (is_numeric($hero_image_raw) && (int) $hero_image_raw > 0) {
    $hero_image = (string) wp_get_attachment_image_url((int) $hero_image_raw, 'full');
}
if ($hero_image === '') {
    $hero_image = (string) $hero_image_raw;
}
$hero_video_url = trim((string) get_theme_mod('aatf_home_hero_video_url', ''));
$hero_video_embed = '';
$hero_video_file = '';

if ($hero_media_type === 'video' && $hero_video_url !== '') {
    if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $hero_video_url, $matches)) {
        $video_id = $matches[1];
        $embed_src = add_query_arg(array(
            'autoplay' => '1',
            'mute' => '1',
            'controls' => '0',
            'loop' => '1',
            'playlist' => $video_id,
            'playsinline' => '1',
            'rel' => '0',
            'modestbranding' => '1',
        ), 'https://www.youtube.com/embed/' . $video_id);
        $hero_video_embed = '<iframe src="' . esc_url($embed_src) . '" title="Hero video" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; encrypted-media" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe>';
    } elseif (preg_match('~vimeo\.com/(?:video/)?([0-9]+)~', $hero_video_url, $matches)) {
        $video_id = $matches[1];
        $embed_src = add_query_arg(array(
            'autoplay' => '1',
            'muted' => '1',
            'loop' => '1',
            'background' => '1',
            'autopause' => '0',
        ), 'https://player.vimeo.com/video/' . $video_id);
        $hero_video_embed = '<iframe src="' . esc_url($embed_src) . '" title="Hero video" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; encrypted-media" allowfullscreen loading="lazy"></iframe>';
    } else {
        $oembed = (string) wp_oembed_get($hero_video_url);
        if ($oembed !== '' && preg_match('~<iframe[^>]+src=["\']([^"\']+)["\'][^>]*>~i', $oembed, $match_src)) {
            $embed_src = add_query_arg(array(
                'autoplay' => '1',
                'mute' => '1',
                'controls' => '0',
                'loop' => '1',
                'playsinline' => '1',
            ), $match_src[1]);
            $hero_video_embed = '<iframe src="' . esc_url($embed_src) . '" title="Hero video" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; encrypted-media" allowfullscreen loading="lazy"></iframe>';
        }
        if ($hero_video_embed === '' && preg_match('/\.(mp4|webm|ogg)(\?.*)?$/i', $hero_video_url)) {
            $hero_video_file = $hero_video_url;
        }
    }
}
?>
<section class="site-hero site-hero--home aatf-fullbleed">
    <?php if ($hero_media_type === 'image' && $hero_image !== '') : ?>
        <div class="site-hero__media site-hero__media--image" aria-hidden="true">
            <img src="<?php echo esc_url($hero_image); ?>" alt="" />
        </div>
    <?php elseif ($hero_media_type === 'video' && ($hero_video_embed !== '' || $hero_video_file !== '')) : ?>
        <div class="site-hero__media site-hero__media--video" aria-hidden="true">
            <?php if ($hero_video_embed !== '') : ?>
                <?php
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
                ?>
                <div class="site-hero__video-embed"><?php echo wp_kses($hero_video_embed, $allowed_iframe); ?></div>
            <?php else : ?>
                <video class="site-hero__video-file" autoplay muted loop playsinline preload="metadata">
                    <source src="<?php echo esc_url($hero_video_file); ?>" />
                </video>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="site-hero__inner">
        <h1><?php echo esc_html($hero_title); ?></h1>
        <?php if ($hero_text !== '') : ?>
            <p><?php echo esc_html($hero_text); ?></p>
        <?php endif; ?>
    </div>
</section>
