<?php if (!defined('ABSPATH')) exit; ?>

<?php
$view_all_url = get_permalink(get_option('page_for_posts')) ?: home_url('/blog/');
$news_heading = (string) get_theme_mod('aatf_home_news_heading', 'News & Articles');
$news_subheading = (string) get_theme_mod('aatf_home_news_subheading', 'From the blog post');
$news_limit = 3;

$posts = new WP_Query(array(
    'post_type'              => 'post',
    'post_status'            => 'publish',
    'posts_per_page'         => $news_limit,
    'orderby'                => 'date',
    'order'                  => 'DESC',
    'ignore_sticky_posts'    => true,
    'no_found_rows'          => true,
    'update_post_meta_cache' => false,
    'update_post_term_cache' => false,
));
?>

<!-- ── News & Articles ────────────────────────────────────────────── -->
<section class="bg-gray-50 font-sans antialiased py-16">
    <!-- BEGIN: NewsAndArticlesSection -->
    <section class="max-w-7xl mx-auto" data-purpose="blog-section">

        <!-- BEGIN: SectionHeader -->
        <header class="flex flex-col md:flex-row md:items-end justify-between mb-12" data-purpose="section-header">
            <div class="space-y-2">
                <p class="text-accent-orange font-medium text-sm tracking-wide">
                    <?php echo esc_html($news_subheading); ?>
                </p>
                <h2 class="text-4xl md:text-5xl font-extrabold text-deep-blue">
                    <?php echo esc_html($news_heading); ?>
                </h2>
            </div>
            <div class="mt-6 md:mt-0">
                <a href="<?php echo esc_url($view_all_url); ?>"
                    class="inline-block bg-[var(--brand-orange)] text-white font-bold text-xs tracking-widest px-8 py-4 rounded shadow-md hover:bg-orange-600 transition-colors uppercase"
                    data-purpose="view-all-button">
                    <?php esc_html_e('View All Posts', 'aatf-expedition-base'); ?>
                </a>
            </div>
        </header>
        <!-- END: SectionHeader -->

        <!-- BEGIN: ArticlesGrid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" data-purpose="article-grid">

            <?php if ($posts->have_posts()) :
                while ($posts->have_posts()) :
                    $posts->the_post();
                    $post_id  = get_the_ID();
                    $thumb    = get_the_post_thumbnail_url($post_id, 'medium_large');
                    if (!$thumb) $thumb = 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=600&q=80';
                    $day      = get_the_date('d', $post_id);
                    $month    = get_the_date('M', $post_id);
                    $excerpt  = get_the_excerpt($post_id);
                    if (!$excerpt) {
                        $excerpt = wp_trim_words(wp_strip_all_tags((string) get_post_field('post_content', $post_id)), 20);
                    }
                    $comments = (int) get_comments_number($post_id);
                    $author   = get_the_author_meta('display_name', (int) get_post_field('post_author', $post_id));
                ?>
                    <article class="w-full bg-white rounded-[20px] card-shadow overflow-hidden border border-gray-100 h-full flex flex-col"
                        data-purpose="blog-post-card">
                        <header class="relative">
                            <div class="h-64 overflow-hidden">
                                <img src="<?php echo esc_url($thumb); ?>"
                                    alt="<?php echo esc_attr(get_the_title($post_id)); ?>"
                                    class="w-full h-full object-cover" />
                            </div>
                            <div class="date-badge absolute right-0 bottom-0 bg-[#f99121] text-white pt-2 pb-[25px] px-4 flex flex-col items-center justify-center"
                                data-purpose="date-badge">
                                <span class="text-xl font-bold leading-none"><?php echo esc_html($day); ?></span>
                                <span class="text-[10px] font-semibold uppercase tracking-wider"><?php echo esc_html($month); ?></span>
                            </div>
                        </header>
                        <section class="curved-overlap bg-white px-8 pt-10 pb-8 flex flex-col flex-1 min-h-[18rem]">
                            <div class="flex items-center space-x-4 mb-4 text-gray-500 text-xs font-medium uppercase tracking-wide">
                                <div class="flex items-center space-x-1">
                                    <svg class="h-4 w-4 text-[#f99121]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                                    </svg>
                                    <span><?php echo esc_html($author); ?></span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <svg class="h-4 w-4 text-[#f99121]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                                    </svg>
                                    <span><?php echo esc_html($comments); ?> <?php esc_html_e('Comments', 'aatf-expedition-base'); ?></span>
                                </div>
                            </div>
                            <h2 class="text-[#2a2d3e] text-2xl font-bold leading-tight mb-4">
                                <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="hover:text-orange-600 transition-colors duration-200">
                                    <?php echo esc_html(get_the_title($post_id)); ?>
                                </a>
                            </h2>
                            <?php if ($excerpt) : ?>
                                <p class="news-article-excerpt text-gray-500 text-sm leading-relaxed">
                                    <?php echo esc_html($excerpt); ?>
                                </p>
                            <?php else : ?>
                                <div class="news-article-excerpt mb-6" aria-hidden="true"></div>
                            <?php endif; ?>
                            <a href="<?php echo esc_url(get_permalink($post_id)); ?>"
                                class="news-article-readmore inline-flex items-center text-[#f99121] text-xs font-bold uppercase tracking-[2px] hover:text-orange-600 transition-colors duration-200 mt-auto"
                                data-purpose="read-more-link">
                                <?php esc_html_e('Read More', 'aatf-expedition-base'); ?>
                                <svg class="h-4 w-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" />
                                </svg>
                            </a>
                        </section>
                    </article>
            <?php endwhile;
                wp_reset_postdata();
            else : ?>
                <p class="text-sm text-[var(--brand-gray)]"><?php esc_html_e('No posts published yet.', 'aatf-expedition-base'); ?></p>
            <?php endif; ?>

        </div>
        <!-- END: ArticlesGrid -->
    </section>
    <!-- END: NewsAndArticlesSection -->
</section>
