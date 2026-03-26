<?php
if (!defined('ABSPATH')) {
    exit;
}

$page_id = (int) get_queried_object_id();
if ($page_id < 1) {
    $page_id = (int) get_option('page_on_front');
}

$value = $page_id > 0 ? get_post_meta($page_id, 'aatf_home_exclusive_activities', true) : array();
$value = is_array($value) ? $value : array();

$title = trim((string) ($value['title'] ?? ''));
$subtitle = trim((string) ($value['subtitle'] ?? ''));
$items = isset($value['items']) && is_array($value['items']) ? $value['items'] : array();

if ($title === '') {
    $title = 'Exclusive Activities';
}

if ($subtitle === '') {
    $subtitle = "Discover the world's most exclusive trekking experiences, where private routes lead to\nuntouched Himalayan summits";
}

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

$background_url = '';
if ($page_id > 0) {
    $background_url = (string) get_the_post_thumbnail_url($page_id, 'full');
}

if ($background_url === '') {
    $hero_image_raw = get_theme_mod('aatf_home_hero_image', '');
    if (is_numeric($hero_image_raw) && (int) $hero_image_raw > 0) {
        $background_url = (string) wp_get_attachment_image_url((int) $hero_image_raw, 'full');
    } else {
        $background_url = (string) $hero_image_raw;
    }
}

$style_attr = $background_url !== ''
    ? ' style="--aatf-exclusive-bg: url(\'' . esc_url($background_url) . '\');"'
    : '';

$subtitle_html = wp_kses(nl2br(esc_html($subtitle)), array('br' => array()));
?>
<section class="aatf-exclusive-activities aatf-fullbleed"<?php echo $style_attr; ?>>
    <div class="aatf-exclusive-activities__inner">
        <header class="aatf-exclusive-activities__header">
            <h2 class="aatf-exclusive-activities__title"><?php echo esc_html($title); ?></h2>
            <p class="aatf-exclusive-activities__subtitle"><?php echo $subtitle_html; ?></p>
        </header>

        <div class="aatf-exclusive-activities__grid">
            <?php foreach (array_values($items) as $item) : ?>
                <?php
                $icon_id = isset($item['icon_id']) ? absint((int) $item['icon_id']) : 0;
                $item_title = trim((string) ($item['title'] ?? ''));
                $description = trim((string) ($item['description'] ?? ''));

                if ($item_title === '' && $description === '' && $icon_id < 1) {
                    continue;
                }

                $icon_html = $icon_id > 0
                    ? wp_get_attachment_image($icon_id, 'thumbnail', false, array('class' => 'aatf-exclusive-card__icon', 'loading' => 'lazy'))
                    : '<span class="aatf-exclusive-card__icon-placeholder" aria-hidden="true"></span>';
                ?>
                <article class="aatf-exclusive-card">
                    <div class="aatf-exclusive-card__head">
                        <?php echo wp_kses_post($icon_html); ?>
                        <?php if ($item_title !== '') : ?>
                            <h3 class="aatf-exclusive-card__title"><?php echo esc_html($item_title); ?></h3>
                        <?php endif; ?>
                    </div>
                    <?php if ($description !== '') : ?>
                        <p class="aatf-exclusive-card__desc"><?php echo esc_html($description); ?></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

