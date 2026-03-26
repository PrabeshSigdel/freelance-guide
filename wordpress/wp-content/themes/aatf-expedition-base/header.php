<?php if (!defined('ABSPATH')) { exit; } ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php
$cta_label = (string) get_theme_mod('aatf_header_cta_label', 'Plan Your Trip');
$booking_page = get_page_by_path('booking');
$default_booking_url = ($booking_page instanceof WP_Post)
    ? get_permalink((int) $booking_page->ID)
    : home_url('/booking/');
$cta_url_raw = trim((string) get_theme_mod('aatf_header_cta_url', $default_booking_url));
$cta_url = $default_booking_url;
if ($cta_url_raw !== '') {
    if (preg_match('~^https?://~i', $cta_url_raw)) {
        $cta_url = $cta_url_raw;
    } elseif ($cta_url_raw[0] === '/') {
        $cta_url = home_url($cta_url_raw);
    } else {
        $cta_url = home_url('/' . ltrim($cta_url_raw, '/'));
    }
}
$phone = trim((string) get_theme_mod('aatf_header_phone', '+977-9848532201'));
$phone_href = preg_replace('/[^0-9+]/', '', $phone);
?>
<header class="aatf-site-header">
    <div class="aatf-shell aatf-site-header__inner">
        <div class="aatf-site-header__brand">
            <?php if (has_custom_logo()) : ?>
                <?php echo wp_kses_post(get_custom_logo()); ?>
            <?php else : ?>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="aatf-site-header__brand-link"><?php bloginfo('name'); ?></a>
            <?php endif; ?>
        </div>

        <button class="aatf-nav-toggle" type="button" aria-expanded="false" aria-controls="aatf-primary-nav">
            <span class="aatf-nav-toggle__line"></span>
            <span class="aatf-nav-toggle__line"></span>
            <span class="aatf-nav-toggle__line"></span>
            <span class="screen-reader-text"><?php echo esc_html__('Toggle menu', 'aatf-expedition-base'); ?></span>
        </button>

        <nav id="aatf-primary-nav" class="aatf-site-header__nav" aria-label="<?php echo esc_attr__('Primary Menu', 'aatf-expedition-base'); ?>">
            <?php
            if (has_nav_menu('primary')) {
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'container' => false,
                    'menu_class' => 'aatf-site-header__menu',
                    'walker' => new AATF_Header_Menu_Walker(),
                    'fallback_cb' => false,
                ));
            } else {
                wp_page_menu(array(
                    'menu_class' => 'aatf-site-header__menu',
                    'show_home' => true,
                ));
            }
            ?>
        </nav>

        <div class="aatf-site-header__actions">
            <?php if ($phone !== '') : ?>
                <a class="aatf-header-phone" href="<?php echo esc_url('tel:' . $phone_href); ?>"><?php echo esc_html($phone); ?></a>
            <?php endif; ?>
            <a class="aatf-header-search" href="<?php echo esc_url(home_url('/?s=')); ?>" aria-label="<?php echo esc_attr__('Search', 'aatf-expedition-base'); ?>">
                <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false">
                    <path fill="currentColor" d="M10 4a6 6 0 104.472 10.03l4.249 4.25a1 1 0 001.415-1.415l-4.25-4.249A6 6 0 0010 4zm0 2a4 4 0 110 8 4 4 0 010-8z" />
                </svg>
            </a>
            <?php if ($cta_label !== '') : ?>
                <a class="aatf-btn aatf-btn--cta" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_label); ?></a>
            <?php endif; ?>
        </div>
    </div>
</header>
<main class="site-main">
