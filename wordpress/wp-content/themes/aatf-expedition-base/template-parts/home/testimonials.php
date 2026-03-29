<?php if (!defined('ABSPATH')) exit; ?>

<?php
$heading = (string) get_theme_mod('aatf_home_testimonials_heading', 'What They\'re Saying');
$limit   = absint((int) get_theme_mod('aatf_home_testimonials_limit', 3));

$testimonials = array();
if (post_type_exists('testimonial')) {
    $testimonials = get_posts(array(
        'post_type'   => 'testimonial',
        'post_status' => 'publish',
        'numberposts' => $limit,
        'orderby'     => 'menu_order date',
        'order'       => 'ASC',
    ));
}

$use_static = empty($testimonials);

$static_testimonials = array(
    array(
        'name'   => 'Kevin Smith',
        'role'   => 'CUSTOMER',
        'review' => 'This is due to their best service, pricing and customer support. It\'s throughly refreshing to such a personal touch. Duis aute irure lupsum reprehenderit.',
        'image'  => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&q=80',
        'rating' => 5,
    ),
    array(
        'name'   => 'Jessica Brown',
        'role'   => 'FOUNDER & CEO',
        'review' => 'This is due to their best service, pricing and customer support. It\'s throughly refreshing to such a personal touch. Duis aute irure lupsum reprehenderit.',
        'image'  => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=200&q=80',
        'rating' => 5,
    ),
    array(
        'name'   => 'David Anderson',
        'role'   => 'CUSTOMER',
        'review' => 'This is due to their best service, pricing and customer support. It\'s throughly refreshing to such a personal touch. Duis aute irure lupsum reprehenderit.',
        'image'  => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=200&q=80',
        'rating' => 5,
    ),
);

if (!function_exists('aatf_get_testimonial_rating')) {
    function aatf_get_testimonial_rating(int $post_id): int
    {
        $candidate_keys = array(
            '_rating',
            'rating',
            'testimonial_rating',
            '_testimonial_rating',
            'stars',
            '_stars',
            'star_rating',
            '_star_rating',
            'review_rating',
            '_review_rating',
        );

        foreach ($candidate_keys as $key) {
            $value = get_post_meta($post_id, $key, true);
            if ($value === '' || $value === null) {
                continue;
            }

            $rating = (int) round((float) $value);
            if ($rating >= 0) {
                return max(0, min(5, $rating));
            }
        }

        return 0;
    }
}
?>

<!-- ── Testimonials ───────────────────────────────────────────────── -->
<section class="bg-white">
    <section class="py-20 max-w-7xl mx-auto">

        <header class="text-center mb-16">
            <span class="text-[var(--brand-orange)] font-semibold text-sm tracking-wide uppercase mb-2 block">
                <?php esc_html_e('Testimonials & reviews', 'aatf-expedition-base'); ?>
            </span>
            <h2 class="text-4xl md:text-5xl font-bold text-[var(--brand-dark)]">
                <?php echo esc_html($heading); ?>
            </h2>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-center">

            <?php if ($use_static) :
                foreach ($static_testimonials as $t) : ?>
                    <article class="flex flex-col items-center">
                        <div class="mb-8">
                            <img src="<?php echo esc_url($t['image']); ?>"
                                alt="<?php echo esc_attr($t['name']); ?>"
                                class="w-32 h-32 rounded-full object-cover shadow-sm mx-auto" />
                        </div>
                        <div class="flex space-x-1 mb-4 text-[var(--brand-orange)] text-xl">
                            <?php echo str_repeat('<span>★</span>', (int) $t['rating']); ?>
                        </div>
                        <p class="text-[var(--brand-gray)] leading-relaxed mb-6 max-w-xs min-h-[80px]">
                            <?php echo esc_html($t['review']); ?>
                        </p>
                        <div>
                            <h4 class="font-bold text-[var(--brand-dark)] text-lg"><?php echo esc_html($t['name']); ?></h4>
                            <span class="text-[var(--brand-orange)] font-bold text-xs tracking-widest uppercase">
                                <?php echo esc_html($t['role']); ?>
                            </span>
                        </div>
                    </article>
                <?php endforeach;
            else :
                foreach ($testimonials as $testimonial) :
                    $thumb  = get_the_post_thumbnail_url($testimonial->ID, 'thumbnail');
                    $review = (string) get_post_meta($testimonial->ID, '_review_text', true);
                    if (!$review) $review = wp_trim_words(get_the_content(null, false, $testimonial->ID), 30);
                    $role   = (string) get_post_meta($testimonial->ID, '_reviewer_role', true);
                    $rating = aatf_get_testimonial_rating($testimonial->ID);
                ?>
                    <article class="flex flex-col items-center">
                        <div class="mb-8">
                            <?php if ($thumb) : ?>
                                <img src="<?php echo esc_url($thumb); ?>"
                                    alt="<?php echo esc_attr(get_the_title($testimonial->ID)); ?>"
                                    class="w-32 h-32 rounded-full object-cover shadow-sm mx-auto" />
                            <?php else : ?>
                                <div class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center mx-auto">
                                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($rating > 0) : ?>
                            <div class="flex space-x-1 mb-4 text-[var(--brand-orange)] text-xl">
                                <?php echo str_repeat('<span>★</span>', $rating); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($review) : ?>
                            <p class="text-[var(--brand-gray)] leading-relaxed mb-6 max-w-xs min-h-[80px]">
                                <?php echo esc_html($review); ?>
                            </p>
                        <?php endif; ?>
                        <div>
                            <h4 class="font-bold text-[var(--brand-dark)] text-lg">
                                <?php echo esc_html(get_the_title($testimonial->ID)); ?>
                            </h4>
                            <?php if ($role) : ?>
                                <span class="text-[var(--brand-orange)] font-bold text-xs tracking-widest uppercase">
                                    <?php echo esc_html($role); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </article>
            <?php endforeach;
            endif; ?>

        </div>
    </section>
</section>
