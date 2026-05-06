<?php
$brand_name = trim((string) get_theme_mod('aatf_footer_company_heading', ''));
if ($brand_name === '') {
    $brand_name = (string) get_bloginfo('name');
}

$company_heading    = trim((string) get_theme_mod('aatf_footer_col_1_heading', 'Company'));
$explore_heading    = trim((string) get_theme_mod('aatf_footer_col_2_heading', 'Explore'));
$newsletter_heading = trim((string) get_theme_mod('aatf_footer_newsletter_heading', 'Newsletter'));

$footer_tagline       = trim((string) get_theme_mod('aatf_footer_tagline', "Welcome to our Trip and Tour Agency.\nLorem simply text amet cing elit."));
$footer_address       = trim((string) get_theme_mod('aatf_footer_address', '66 Broklyn Street New York, USA'));
$footer_phone         = trim((string) get_theme_mod('aatf_footer_phone', '92 666 888 0000'));
$footer_phone_href    = preg_replace('/[^0-9+]/', '', $footer_phone);
$footer_email         = trim((string) get_theme_mod('aatf_footer_email', 'contact@example.com'));
$footer_overlay_image = trim((string) get_theme_mod('aatf_footer_overlay_image', 'https://images.unsplash.com/photo-1519500099198-fd81846b8f03?w=1600&q=60'));

$newsletter_placeholder    = trim((string) get_theme_mod('aatf_footer_newsletter_placeholder', 'Email address'));
$newsletter_button_label   = trim((string) get_theme_mod('aatf_footer_newsletter_button_label', 'Subscribe'));
$terms_label               = trim((string) get_theme_mod('aatf_footer_terms_label', 'I agree to all terms and policies'));

$copyright = trim((string) get_theme_mod('aatf_footer_copyright', ''));
if ($copyright === '') {
    $copyright = sprintf('© %s %s | All rights reserved', gmdate('Y'), $brand_name);
}

$custom_logo_id     = (int) get_theme_mod('custom_logo');
$footer_logo_markup = $custom_logo_id > 0
    ? wp_get_attachment_image($custom_logo_id, 'full', false, array(
        'class' => 'w-full h-full object-contain',
        'alt'   => $brand_name,
    ))
    : '';

$twitter_url   = trim((string) get_theme_mod('aatf_social_twitter', ''));
$facebook_url  = trim((string) get_theme_mod('aatf_social_facebook', ''));
$pinterest_url = trim((string) get_theme_mod('aatf_social_pinterest', ''));
$instagram_url = trim((string) get_theme_mod('aatf_social_instagram', ''));

$social_links = array(
    array(
        'label' => __('Twitter / X', 'aatf-expedition-base'),
        'url'   => $twitter_url,
        'icon'  => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z" /></svg>',
    ),
    array(
        'label' => __('Facebook', 'aatf-expedition-base'),
        'url'   => $facebook_url,
        'icon'  => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" /></svg>',
    ),
    array(
        'label' => __('Pinterest', 'aatf-expedition-base'),
        'url'   => $pinterest_url,
        'icon'  => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738a.36.36 0 01.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.632-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z" /></svg>',
    ),
    array(
        'label' => __('Instagram', 'aatf-expedition-base'),
        'url'   => $instagram_url,
        'icon'  => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" /></svg>',
    ),
);

// Reusable SVG allowlist — covers all social icon markup above plus common SVG elements.
$svg_kses_args = array(
    'svg'    => array(
        'class'        => true,
        'fill'         => true,
        'viewbox'      => true, // wp_kses lowercases attribute names, so viewBox → viewbox
        'aria-hidden'  => true,
        'xmlns'        => true,
        'width'        => true,
        'height'       => true,
        'stroke'       => true,
        'stroke-width' => true,
        'role'         => true,
    ),
    'path'   => array(
        'd'               => true,
        'fill'            => true,
        'fill-rule'       => true,
        'clip-rule'       => true,
        'stroke'          => true,
        'stroke-linecap'  => true,
        'stroke-linejoin' => true,
        'stroke-width'    => true,
    ),
    'circle' => array(
        'cx'           => true,
        'cy'           => true,
        'r'            => true,
        'fill'         => true,
        'stroke'       => true,
        'stroke-width' => true,
    ),
    'rect'   => array(
        'x'      => true,
        'y'      => true,
        'width'  => true,
        'height' => true,
        'rx'     => true,
        'ry'     => true,
        'fill'   => true,
    ),
    'g'      => array(
        'fill'      => true,
        'transform' => true,
    ),
    'title'  => array(),
);

$render_footer_menu = static function (array $locations): array {
    foreach ($locations as $location) {
        $items = array();

        if (has_nav_menu($location)) {
            $menu_locations = get_nav_menu_locations();
            $menu_id        = isset($menu_locations[$location]) ? (int) $menu_locations[$location] : 0;

            if ($menu_id > 0) {
                $raw_items = wp_get_nav_menu_items($menu_id);
                if (is_array($raw_items)) {
                    foreach ($raw_items as $item) {
                        if ((int) $item->menu_item_parent !== 0) {
                            continue;
                        }
                        $items[] = array(
                            'title' => $item->title,
                            'url'   => $item->url,
                        );
                    }
                }
            }
        }

        if (!empty($items)) {
            return $items;
        }
    }

    $fallback_pages = get_pages(array(
        'parent'      => 0,
        'sort_column' => 'menu_order,post_title',
        'sort_order'  => 'ASC',
        'number'      => 5,
    ));

    $items = array();
    foreach ($fallback_pages as $page) {
        $items[] = array(
            'title' => $page->post_title,
            'url'   => get_permalink($page->ID),
        );
    }

    return $items;
};

$company_menu_items = $render_footer_menu(array('footer_company', 'footer_col_1'));
$explore_menu_items = $render_footer_menu(array('footer_explore', 'footer_col_2', 'footer_col_3'));

$subscription_status        = isset($_GET['aatf_footer_subscribe']) ? sanitize_key(wp_unslash($_GET['aatf_footer_subscribe'])) : '';
$subscription_message       = '';
$subscription_message_class = 'text-xs text-gray-400';

if ($subscription_status === 'success') {
    $subscription_message       = __('Thanks for subscribing. We will be in touch soon.', 'aatf-expedition-base');
    $subscription_message_class = 'text-xs text-green-300';
} elseif ($subscription_status === 'invalid') {
    $subscription_message       = __('Please enter a valid email address and accept the terms.', 'aatf-expedition-base');
    $subscription_message_class = 'text-xs text-amber-300';
} elseif ($subscription_status === 'error') {
    $subscription_message       = __('Something went wrong while sending your request. Please try again.', 'aatf-expedition-base');
    $subscription_message_class = 'text-xs text-red-300';
}
?>
<footer class="aatf-modern-footer relative overflow-hidden" style="background-color: var(--brand-dark);">
    <?php if ($footer_overlay_image !== '') : ?>
        <div
            class="aatf-modern-footer__overlay absolute inset-0 opacity-10 bg-cover bg-top bg-no-repeat pointer-events-none"
            style="background-image: url('<?php echo esc_url($footer_overlay_image); ?>');"></div>
    <?php endif; ?>

    <div class="aatf-modern-footer__inner relative z-10 max-w-7xl mx-auto pt-16 pb-10">
        <div class="aatf-modern-footer__grid grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-10">

            <!-- Brand / Contact column -->
            <div class="aatf-modern-footer__brand xl:pr-6">
                <div class="aatf-modern-footer__logo w-16 h-16 flex items-center justify-center shadow-md mb-5 bg-white/5 rounded-lg overflow-hidden">
                    <?php if ($footer_logo_markup !== '') : ?>
                        <?php echo wp_kses_post($footer_logo_markup); ?>
                    <?php else : ?>
                        <span class="text-white text-lg font-bold"><?php echo esc_html(substr($brand_name, 0, 1)); ?></span>
                    <?php endif; ?>
                </div>

                <?php if ($footer_tagline !== '') : ?>
                    <p class="text-gray-400 text-sm leading-relaxed mb-5">
                        <?php echo nl2br(esc_html($footer_tagline)); ?>
                    </p>
                <?php endif; ?>

                <hr class="border-gray-600 mb-5" />

                <ul class="aatf-modern-footer__contact space-y-3 text-sm text-gray-400">
                    <?php if ($footer_phone !== '') : ?>
                        <li class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 shrink-0" style="color: var(--brand-orange);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <a href="<?php echo esc_url('tel:' . $footer_phone_href); ?>" class="hover:text-[var(--brand-orange)] transition-colors">
                                <?php echo esc_html($footer_phone); ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($footer_email !== '') : ?>
                        <li class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 shrink-0" style="color: var(--brand-orange);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <a href="<?php echo esc_url('mailto:' . antispambot($footer_email)); ?>" class="hover:text-[var(--brand-orange)] transition-colors">
                                <?php echo esc_html(antispambot($footer_email)); ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($footer_address !== '') : ?>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 shrink-0 mt-0.5" style="color: var(--brand-orange);" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                            </svg>
                            <span><?php echo esc_html($footer_address); ?></span>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Company menu column -->
            <div class="aatf-modern-footer__menu-col">
                <?php if ($company_heading !== '') : ?>
                    <h4 class="text-white font-semibold text-base mb-5"><?php echo esc_html($company_heading); ?></h4>
                <?php endif; ?>
                <ul class="aatf-modern-footer__menu space-y-3 text-sm text-gray-400">
                    <?php foreach ($company_menu_items as $item) : ?>
                        <li>
                            <a href="<?php echo esc_url($item['url']); ?>" class="hover:text-[var(--brand-orange)] transition-colors">
                                <?php echo esc_html($item['title']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Explore menu column -->
            <div class="aatf-modern-footer__menu-col">
                <?php if ($explore_heading !== '') : ?>
                    <h4 class="text-white font-semibold text-base mb-5"><?php echo esc_html($explore_heading); ?></h4>
                <?php endif; ?>
                <ul class="aatf-modern-footer__menu space-y-3 text-sm text-gray-400">
                    <?php foreach ($explore_menu_items as $item) : ?>
                        <li>
                            <a href="<?php echo esc_url($item['url']); ?>" class="hover:text-[var(--brand-orange)] transition-colors">
                                <?php echo esc_html($item['title']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Newsletter column -->
            <!-- <div class="aatf-modern-footer__newsletter">
                <?php if ($newsletter_heading !== '') : ?>
                    <h4 class="text-white font-semibold text-base mb-5"><?php echo esc_html($newsletter_heading); ?></h4>
                <?php endif; ?>

                <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" class="aatf-modern-footer__form space-y-3">
                    <input type="hidden" name="action" value="aatf_footer_subscribe" />
                    <?php wp_nonce_field('aatf_footer_subscribe', 'aatf_footer_nonce'); ?>

                    <input
                        type="email"
                        name="aatf_footer_email"
                        placeholder="<?php echo esc_attr($newsletter_placeholder); ?>"
                        required
                        class="w-full bg-[var(--brand-navy)] text-gray-300 placeholder-gray-500 text-sm px-4 py-3 rounded-lg border border-gray-700 focus:outline-none focus:border-[var(--brand-orange)] transition-colors" />

                    <button
                        type="submit"
                        class="w-full text-white text-sm font-bold tracking-widest uppercase py-3 rounded-lg transition-opacity hover:opacity-90"
                        style="background-color: var(--brand-orange);">
                        <?php echo esc_html($newsletter_button_label); ?>
                    </button>

                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" name="aatf_footer_terms" value="1" class="sr-only peer" required />
                        <div class="w-5 h-5 rounded-full border-2 border-gray-500 flex items-center justify-center shrink-0 group-hover:border-[var(--brand-orange)] peer-checked:border-[var(--brand-orange)] transition-colors">
                            <svg class="w-3 h-3 text-transparent peer-checked:text-[var(--brand-orange)] group-hover:text-[var(--brand-orange)] transition-colors" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="text-xs text-gray-400"><?php echo esc_html($terms_label); ?></span>
                    </label>

                    <?php if ($subscription_message !== '') : ?>
                        <p class="<?php echo esc_attr($subscription_message_class); ?>">
                            <?php echo esc_html($subscription_message); ?>
                        </p>
                    <?php endif; ?>
                </form>
            </div> -->

        </div>
    </div>

    <!-- Footer bottom bar -->
    <div class="aatf-modern-footer__bottom relative z-10 border-t border-gray-700">
        <div class="aatf-modern-footer__bottom-inner max-w-7xl mx-auto flex flex-col md:flex-row md:items-center px-4 sm:px-6 lg:px-8">

            <!-- Back to top button -->
            <button
                type="button"
                onclick="window.scrollTo({top:0,behavior:'smooth'})"
                class="w-full md:w-[100px] h-[60px] flex items-center justify-center shrink-0 transition-opacity hover:opacity-90"
                style="background-color: var(--brand-orange);"
                aria-label="<?php esc_attr_e('Back to top', 'aatf-expedition-base'); ?>">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                </svg>
            </button>

            <!-- Social icons -->
            <div class="flex items-center gap-3 px-0 md:px-6 py-4">
                <?php foreach ($social_links as $social_link) : ?>
                    <?php if ($social_link['url'] === '') continue; ?>
                    <a
                        href="<?php echo esc_url($social_link['url']); ?>"
                        class="aatf-modern-footer__social-link w-9 h-9 rounded-full bg-white flex items-center justify-center text-gray-600 hover:text-[var(--brand-orange)] hover:scale-110 transition-all shadow-sm"
                        aria-label="<?php echo esc_attr($social_link['label']); ?>">
                        <?php echo wp_kses($social_link['icon'], $svg_kses_args); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Copyright -->
            <p class="md:ml-auto py-4 md:pr-0 text-sm text-gray-400">
                <?php echo esc_html($copyright); ?>
            </p>

        </div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>

</html>