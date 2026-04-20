<?php
/**
 * Template Name: Terms and Conditions
 */

get_header();

$post_id = get_the_ID();

// Main Content
$main_content = get_post_meta($post_id, 'aatf_terms_main_content', true);
?>

<main id="primary" class="site-main aatf-terms-page">

    <div class="aatf-terms-container">
        <h1 class="aatf-terms-headline"><?php esc_html_e('Terms and Conditions', 'aatf-expedition-base'); ?></h1>

        <div class="aatf-terms-content">
            <?php if (empty($main_content)): ?>
                <div class="aatf-terms-empty">
                    <p><?php esc_html_e('No terms and conditions content has been added yet.', 'aatf-expedition-base'); ?></p>
                </div>
            <?php else: ?>
                <div class="aatf-terms-body">
                    <?php echo wpautop(wp_kses_post($main_content)); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php
get_footer();
