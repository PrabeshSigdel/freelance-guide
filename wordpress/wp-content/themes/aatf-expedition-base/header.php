<?php if (!defined('ABSPATH')) {
    exit;
} ?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php wp_head(); ?>
</head>

<body <?php body_class('m-0 p-0 overflow-x-hidden'); ?>>
    <?php wp_body_open(); ?>

    <?php
    /* ── Theme-mod values ─────────────────────────────────────────── */
    $cta_label = (string) get_theme_mod('aatf_header_cta_label', 'Contact us at: 9842734229');

    $booking_page        = get_page_by_path('booking');
    $default_booking_url = ($booking_page instanceof WP_Post)
        ? get_permalink((int) $booking_page->ID)
        : home_url('/booking/');

    $cta_url_raw = trim((string) get_theme_mod('aatf_header_cta_url', $default_booking_url));
    $cta_url     = $default_booking_url;
    if ($cta_url_raw !== '') {
        if (preg_match('~^https?://~i', $cta_url_raw)) {
            $cta_url = $cta_url_raw;
        } elseif ($cta_url_raw[0] === '/') {
            $cta_url = home_url($cta_url_raw);
        } else {
            $cta_url = home_url('/' . ltrim($cta_url_raw, '/'));
        }
    }

    $phone      = trim((string) get_theme_mod('aatf_header_phone', '666 888 0000'));
    $phone_href = preg_replace('/[^0-9+]/', '', $phone);

    $email = trim((string) get_theme_mod('aatf_header_email', 'contact@example.com'));

    $twitter_url   = trim((string) get_theme_mod('aatf_social_twitter', ''));
    $facebook_url  = trim((string) get_theme_mod('aatf_social_facebook', ''));
    $instagram_url = trim((string) get_theme_mod('aatf_social_instagram', ''));

    $social_links = array(
        array(
            'label' => __('Twitter / X', 'aatf-expedition-base'),
            'url'   => $twitter_url,
            'icon'  => '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z" /></svg>',
        ),
        array(
            'label' => __('Facebook', 'aatf-expedition-base'),
            'url'   => $facebook_url,
            'icon'  => '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" /></svg>',
        ),
        array(
            'label' => __('Instagram', 'aatf-expedition-base'),
            'url'   => $instagram_url,
            'icon'  => '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" /></svg>',
        ),
    );

    $fallback_pages = get_pages(array(
        'parent'      => 0,
        'sort_column' => 'menu_order,post_title',
        'sort_order'  => 'ASC',
    ));
    ?>

    <!-- ── Top Bar ──────────────────────────────────────────────────── -->
    <div class="bg-[#2d2d2d] text-white text-sm py-2 px-6 flex items-center justify-between">

        <div class="flex items-center gap-6">

            <?php if ($phone !== '') : ?>
                <!-- Phone -->
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[var(--brand-orange)]" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <a href="<?php echo esc_url('tel:' . $phone_href); ?>"
                        class="text-[var(--brand-gray)] hover:text-[var(--brand-orange)] transition-colors">
                        <?php echo esc_html($phone); ?>
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($email !== '') : ?>
                <!-- Email -->
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[var(--brand-orange)]" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <a href="<?php echo esc_url('mailto:' . antispambot($email)); ?>"
                        class="text-[var(--brand-gray)] hover:text-[var(--brand-orange)] transition-colors">
                        <?php echo esc_html(antispambot($email)); ?>
                    </a>
                </div>
            <?php endif; ?>

        </div>

        <div class="flex items-center gap-4">

            <?php foreach ($social_links as $social_link) : ?>
                <?php if ($social_link['url'] === '') {
                    continue;
                } ?>
                <a href="<?php echo esc_url($social_link['url']); ?>"
                    class="inline-flex items-center justify-center text-[var(--brand-gray)] hover:text-[var(--brand-orange)] transition-all hover:-translate-y-0.5"
                    aria-label="<?php echo esc_attr($social_link['label']); ?>">
                    <?php echo wp_kses($social_link['icon'], array(
                        'svg' => array(
                            'class'       => true,
                            'fill'        => true,
                            'viewBox'     => true,
                            'aria-hidden' => true,
                            'xmlns'       => true,
                        ),
                        'path' => array(
                            'd' => true,
                        ),
                    )); ?>
                </a>
            <?php endforeach; ?>

            <?php if ($cta_label !== '') : ?>
                <a href="<?php echo esc_url($cta_url); ?>"
                    class="bg-[var(--brand-orange)] text-white px-4 py-2 text-xs font-semibold tracking-wide ml-2 hover:opacity-90 transition-opacity">
                    <?php echo esc_html($cta_label); ?>
                </a>
            <?php endif; ?>

        </div>
    </div>

    <!-- ── Navigation ───────────────────────────────────────────────── -->
    <nav class="aatf-main-nav bg-white sticky top-0 z-50" aria-label="<?php esc_attr_e('Primary Menu', 'aatf-expedition-base'); ?>">
        <div class="max-w-7xl mx-auto py-3 flex items-center gap-6">

            <!-- Logo / Brand -->
            <div class="w-[68px] h-[68px] flex items-center justify-center shrink-0">
                <?php if (has_custom_logo()) : ?>
                    <?php echo wp_kses_post(get_custom_logo()); ?>
                <?php else : ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>"
                        class="text-[var(--brand-dark)] font-semibold text-sm">
                        <?php bloginfo('name'); ?>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Nav Links -->
            <div class="flex-1 min-w-0 flex items-center justify-center gap-7 text-md font-medium text-[var(--brand-gray)]">
                <?php
                if (has_nav_menu('primary')) {
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'container'      => false,
                        'menu_class'     => 'aatf-site-header__menu flex items-center gap-7',
                        'walker'         => new AATF_Header_Menu_Walker(),
                        'fallback_cb'    => false,
                        'link_before'    => '',
                        'link_after'     => '',
                        'item_spacing'   => 'discard',
                    ));
                } else {
                ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>"
                        class="<?php echo is_front_page() ? 'nav-item text-[var(--brand-dark)] font-semibold border-b-2 border-[var(--brand-orange)] pb-1 cursor-pointer' : 'hover:text-[var(--brand-orange)] transition-colors cursor-pointer'; ?>">
                        <?php esc_html_e('Home', 'aatf-expedition-base'); ?>
                    </a>
                    <?php foreach ($fallback_pages as $fallback_page) : ?>
                        <?php
                        $page_classes = 'hover:text-[var(--brand-orange)] transition-colors cursor-pointer';
                        if (is_page((int) $fallback_page->ID)) {
                            $page_classes = 'nav-item text-[var(--brand-dark)] font-semibold border-b-2 border-[var(--brand-orange)] pb-1 cursor-pointer';
                        }
                        ?>
                        <a href="<?php echo esc_url(get_permalink((int) $fallback_page->ID)); ?>"
                            class="<?php echo esc_attr($page_classes); ?>">
                            <?php echo esc_html(get_the_title((int) $fallback_page->ID)); ?>
                        </a>
                    <?php endforeach; ?>
                <?php
                }
                ?>
            </div>

            <!-- Icons -->
            <div class="flex items-center gap-4 text-[var(--brand-gray)] shrink-0">


                <!-- Account / My Account -->
                <?php if (function_exists('wc_get_page_permalink')) : ?>
                    <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>"
                        class="hover:text-[var(--brand-orange)] transition-colors"
                        aria-label="<?php esc_attr_e('My Account', 'aatf-expedition-base'); ?>">
                    <?php else : ?>
                        <a href="<?php echo esc_url(wp_login_url()); ?>"
                            class="hover:text-[var(--brand-orange)] transition-colors"
                            aria-label="<?php esc_attr_e('Log in', 'aatf-expedition-base'); ?>">
                        <?php endif; ?>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        </a>

            </div>
        </div>

        <!-- ── Mobile hamburger toggle (hidden on desktop) ──────────── -->
        <button class="aatf-nav-toggle hidden" type="button"
            aria-expanded="false" aria-controls="aatf-primary-nav">
            <span class="aatf-nav-toggle__line"></span>
            <span class="aatf-nav-toggle__line"></span>
            <span class="aatf-nav-toggle__line"></span>
            <span class="screen-reader-text"><?php esc_html_e('Toggle menu', 'aatf-expedition-base'); ?></span>
        </button>
    </nav>

    <main class="site-main">
