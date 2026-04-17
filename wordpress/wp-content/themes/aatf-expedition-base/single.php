<?php
/**
 * The template for displaying all single posts.
 *
 * @package aatf-expedition-base
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()):
    while (have_posts()):
        the_post();
        $post_id = get_the_ID();
        $thumb = get_the_post_thumbnail_url($post_id, 'full');
        $date = get_the_date();
        $categories = get_the_category();
        ?>

        <main class="site-main bg-white">
            <!-- Content & Sidebar Section -->
            <div class="max-w-7xl mx-auto px-6 py-12 lg:py-20">

                <!-- Breadcrumbs -->
                <nav class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-gray-400 mb-8"
                    aria-label="Breadcrumb">
                    <a href="<?php echo esc_url(home_url('/')); ?>"
                        class="hover:text-[var(--brand-orange)] transition-colors">Home</a>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                    <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>"
                        class="hover:text-[var(--brand-orange)] transition-colors">Blog</a>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-[var(--brand-gray)] truncate max-w-[200px]"><?php the_title(); ?></span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">

                    <!-- Main Content -->
                    <article class="lg:col-span-8">

                        <!-- Title and Date Row -->
                        <header
                            class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-10 pb-8 border-b border-gray-100">
                            <div class="flex-1">
                                <?php if (!empty($categories)): ?>
                                    <span
                                        class="inline-block text-[var(--brand-orange)] text-[10px] font-bold uppercase tracking-[2px] mb-3">
                                        <?php echo esc_html($categories[0]->name); ?>
                                    </span>
                                <?php endif; ?>
                                <h1 class="text-3xl md:text-4xl font-bold text-[var(--brand-dark)] leading-tight">
                                    <?php the_title(); ?>
                                </h1>
                            </div>
                            <div class="flex items-center gap-2 text-gray-400 text-sm font-semibold whitespace-nowrap">
                                <svg class="w-4 h-4 text-[var(--brand-orange)]" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                                </svg>
                                <span><?php echo esc_html($date); ?></span>
                            </div>
                        </header>

                        <!-- Featured Image -->
                        <?php if ($thumb): ?>
                            <div class="mb-12 rounded-2xl overflow-hidden shadow-sm">
                                <img src="<?php echo esc_url($thumb); ?>" alt="<?php the_title_attribute(); ?>"
                                    class="w-full h-auto object-cover">
                            </div>
                        <?php endif; ?>

                        <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed font-sans aatf-post-content">
                            <?php the_content(); ?>
                        </div>

                        <!-- Tags -->
                        <?php $tags = get_the_tags(); ?>
                        <?php if ($tags): ?>
                            <div class="mt-12 pt-8 border-t border-gray-100 flex flex-wrap gap-2">
                                <?php foreach ($tags as $tag): ?>
                                    <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>"
                                        class="bg-gray-50 text-gray-600 text-xs font-semibold px-4 py-2 rounded-lg hover:bg-[var(--brand-orange)] hover:text-white transition-all">
                                        # <?php echo esc_html($tag->name); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Post Navigation -->
                        <nav
                            class="mt-16 pt-12 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-8">
                            <?php
                            $prev_post = get_previous_post();
                            $next_post = get_next_post();
                            ?>

                            <div class="w-full md:w-1/2">
                                <?php if ($prev_post): ?>
                                    <a href="<?php echo esc_url(get_permalink($prev_post->ID)); ?>"
                                        class="group flex flex-col items-start text-left">
                                        <span
                                            class="text-xs font-bold text-[var(--brand-orange)] uppercase tracking-widest mb-2 flex items-center">
                                            <svg class="w-4 h-4 mr-1 transition-transform group-hover:-translate-x-1" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2" />
                                            </svg>
                                            Previous Post
                                        </span>
                                        <span
                                            class="text-xl font-bold text-[var(--brand-dark)] group-hover:text-[var(--brand-orange)] transition-colors line-clamp-2">
                                            <?php echo esc_html(get_the_title($prev_post->ID)); ?>
                                        </span>
                                    </a>
                                <?php endif; ?>
                            </div>

                            <div class="w-full md:w-1/2">
                                <?php if ($next_post): ?>
                                    <a href="<?php echo esc_url(get_permalink($next_post->ID)); ?>"
                                        class="group flex flex-col items-end text-right">
                                        <span
                                            class="text-xs font-bold text-[var(--brand-orange)] uppercase tracking-widest mb-2 flex items-center">
                                            Next Post
                                            <svg class="w-4 h-4 ml-1 transition-transform group-hover:translate-x-1" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2" />
                                            </svg>
                                        </span>
                                        <span
                                            class="text-xl font-bold text-[var(--brand-dark)] group-hover:text-[var(--brand-orange)] transition-colors line-clamp-2">
                                            <?php echo esc_html(get_the_title($next_post->ID)); ?>
                                        </span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </nav>
                    </article>

                    <!-- Sidebar -->
                    <aside class="lg:col-span-4">
                        <div class="sticky top-24 space-y-12">

                            <!-- Related Posts Widget -->
                            <?php
                            $categories = get_the_category($post_id);
                            $cat_ids = wp_list_pluck($categories, 'term_id');

                            $args = array(
                                'post_type'      => 'post',
                                'posts_per_page' => 4,
                                'post__not_in'   => array($post_id),
                                'orderby'        => 'date',
                                'order'          => 'DESC'
                            );

                            if (!empty($cat_ids)) {
                                $args['category__in'] = $cat_ids;
                            }

                            $related_query = new WP_Query($args);

                            if (!$related_query->have_posts()) {
                                unset($args['category__in']);
                                $related_query = new WP_Query($args);
                            }

                            if ($related_query->have_posts()):
                                ?>
                                <section>
                                    <h3
                                        class="text-xl font-extrabold text-[var(--brand-dark)] mb-6 pb-4 border-b-2 border-[var(--brand-orange)] inline-block">
                                        Related Stories
                                    </h3>
                                    <div class="space-y-6">
                                        <?php while ($related_query->have_posts()):
                                            $related_query->the_post(); ?>
                                            <a href="<?php the_permalink(); ?>" class="group flex gap-4 items-center">
                                                <div class="shrink-0 w-20 h-20 rounded-xl overflow-hidden bg-gray-100">
                                                    <?php if (has_post_thumbnail()): ?>
                                                        <?php the_post_thumbnail('thumbnail', array('class' => 'w-full h-full object-cover transition-transform duration-500 group-hover:scale-110')); ?>
                                                    <?php else: ?>
                                                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path
                                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                                                            </svg>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="flex-1">
                                                    <span
                                                        class="text-[10px] font-bold text-[var(--brand-orange)] uppercase tracking-wider mb-1 block">
                                                        <?php echo get_the_date('M d, Y'); ?>
                                                    </span>
                                                    <h4
                                                        class="text-sm font-bold text-[var(--brand-dark)] group-hover:text-[var(--brand-orange)] transition-colors leading-snug line-clamp-2">
                                                        <?php the_title(); ?>
                                                    </h4>
                                                </div>
                                            </a>
                                        <?php endwhile;
                                        wp_reset_postdata(); ?>
                                    </div>
                                </section>
                            <?php endif; ?>

                            <!-- Newsletter/CTA Widget (Optional) -->
                            <section class="bg-[var(--brand-dark)] rounded-3xl p-8 text-white relative overflow-hidden">
                                <div
                                    class="absolute top-0 right-0 w-32 h-32 bg-[var(--brand-orange)] opacity-10 -mr-16 -mt-16 rounded-full">
                                </div>
                                <h3 class="text-xl font-bold mb-4 relative z-10">Plan Your Next Adventure</h3>
                                <p class="text-gray-300 text-sm mb-6 relative z-10">Get expert advice and custom itineraries for
                                    your next trek.</p>
                                <a href="<?php echo esc_url(home_url('/contact')); ?>"
                                    class="inline-block bg-[var(--brand-orange)] text-white px-6 py-2.5 rounded-xl font-bold text-xs tracking-widest uppercase hover:bg-orange-600 transition-colors relative z-10">
                                    Contact Us
                                </a>
                            </section>

                        </div>
                    </aside>

                </div>
            </div>
        </main>

        <style>
            .aatf-post-content h2 {
                font-size: 2rem;
                font-weight: 800;
                color: var(--brand-dark);
                margin-top: 2.5rem;
                margin-bottom: 1.25rem;
            }

            .aatf-post-content h3 {
                font-size: 1.5rem;
                font-weight: 700;
                color: var(--brand-dark);
                margin-top: 2rem;
                margin-bottom: 1rem;
            }

            .aatf-post-content p {
                margin-bottom: 1.5rem;
            }

            .aatf-post-content ul {
                list-style: disc;
                margin-left: 1.5rem;
                margin-bottom: 1.5rem;
            }

            .aatf-post-content ol {
                list-style: decimal;
                margin-left: 1.5rem;
                margin-bottom: 1.5rem;
            }

            .aatf-post-content blockquote {
                border-left: 4px solid var(--brand-orange);
                padding-left: 1.5rem;
                font-style: italic;
                margin: 2rem 0;
                color: var(--brand-gray);
            }

            .aatf-post-content img {
                border-radius: 1rem;
                margin: 2.5rem auto;
            }
        </style>

        <?php
    endwhile;
endif;

get_footer();
