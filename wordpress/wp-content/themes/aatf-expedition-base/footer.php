</main>
<?php
$col_1_heading = trim((string) get_theme_mod('aatf_footer_col_1_heading', 'Our Top Treks'));
$col_2_heading = trim((string) get_theme_mod('aatf_footer_col_2_heading', 'Travel Guide'));
$col_3_heading = trim((string) get_theme_mod('aatf_footer_col_3_heading', 'Our Company'));

$company_heading = trim((string) get_theme_mod('aatf_footer_company_heading', ''));
if ($company_heading === '') {
    $company_heading = (string) get_bloginfo('name');
}

$footer_address = trim((string) get_theme_mod('aatf_footer_address', ''));
$footer_phone = trim((string) get_theme_mod('aatf_footer_phone', ''));
$footer_email = trim((string) get_theme_mod('aatf_footer_email', ''));

$copyright = trim((string) get_theme_mod('aatf_footer_copyright', ''));
if ($copyright === '') {
    $copyright = sprintf('Copyright %s by %s. All Rights Reserved.', gmdate('Y'), $company_heading);
}

$footer_logo = get_custom_logo();
?>
<footer class="aatf-site-footer">
    <div class="aatf-shell">
        <div class="aatf-site-footer__top">
            <div class="aatf-site-footer__col">
                <?php if ($col_1_heading !== '') : ?><h3><?php echo esc_html($col_1_heading); ?></h3><?php endif; ?>
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer_col_1',
                    'container' => false,
                    'menu_class' => 'aatf-site-footer__menu',
                    'fallback_cb' => 'wp_page_menu',
                    'depth' => 1,
                ));
                ?>
            </div>
            <div class="aatf-site-footer__col">
                <?php if ($col_2_heading !== '') : ?><h3><?php echo esc_html($col_2_heading); ?></h3><?php endif; ?>
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer_col_2',
                    'container' => false,
                    'menu_class' => 'aatf-site-footer__menu',
                    'fallback_cb' => 'wp_page_menu',
                    'depth' => 1,
                ));
                ?>
            </div>
            <div class="aatf-site-footer__col">
                <?php if ($col_3_heading !== '') : ?><h3><?php echo esc_html($col_3_heading); ?></h3><?php endif; ?>
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer_col_3',
                    'container' => false,
                    'menu_class' => 'aatf-site-footer__menu',
                    'fallback_cb' => 'wp_page_menu',
                    'depth' => 1,
                ));
                ?>
            </div>
        </div>

        <div class="aatf-site-footer__middle">
            <div class="aatf-site-footer__brand">
                <?php if ($footer_logo) : ?>
                    <div class="aatf-site-footer__logo"><?php echo wp_kses_post($footer_logo); ?></div>
                <?php else : ?>
                    <p class="aatf-site-footer__brand-name"><?php echo esc_html($company_heading); ?></p>
                <?php endif; ?>
            </div>
            <div class="aatf-site-footer__contact">
                <h4><?php echo esc_html($company_heading); ?></h4>
                <?php if ($footer_address !== '') : ?><p><?php echo esc_html($footer_address); ?></p><?php endif; ?>
                <?php if ($footer_phone !== '') : ?><p>Phone: <?php echo esc_html($footer_phone); ?></p><?php endif; ?>
                <?php if ($footer_email !== '') : ?><p>Email: <?php echo esc_html($footer_email); ?></p><?php endif; ?>
            </div>
        </div>

        <div class="aatf-site-footer__bottom">
            <p><?php echo esc_html($copyright); ?></p>
        </div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>

</html>