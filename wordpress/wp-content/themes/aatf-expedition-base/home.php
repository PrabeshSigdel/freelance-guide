<?php
/**
 * The template for displaying the blog posts page.
 *
 * @package aatf-expedition-base
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$posts_page_id = get_option('page_for_posts');
$hero_title    = $posts_page_id ? get_the_title($posts_page_id) : __('Our Blog', 'aatf-expedition-base');
$hero_image_id = $posts_page_id ? (int) get_post_meta($posts_page_id, 'aatf_blog_hero_image_id', true) : 0;
$hero_bg_url   = $hero_image_id > 0 ? wp_get_attachment_image_url($hero_image_id, 'full') : '';
$hero_style    = $hero_bg_url ? 'background-image: url(' . esc_url($hero_bg_url) . '); background-size: cover; background-position: center;' : '';
?>

<div class="site-main">

    <!-- Hero Section -->
    <section class="relative py-16 lg:py-24 border-b border-gray-100" style="<?php echo esc_attr($hero_style); ?>">
        <?php if ($hero_bg_url) : ?>
            <div class="absolute inset-0 bg-black/60"></div>
        <?php else : ?>
            <div class="absolute inset-0 bg-[var(--brand-dark)]"></div>
        <?php endif; ?>
        
        <div class="relative max-w-7xl mx-auto px-6 text-center">
            <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-white mb-6 tracking-tight">
                <?php echo esc_html($hero_title); ?>
            </h1>
            <nav class="flex justify-center items-center gap-2 text-sm font-medium text-gray-300" aria-label="Breadcrumb">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="hover:text-[var(--brand-orange)] transition-colors text-white">Home</a>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span class="text-white"><?php echo esc_html($hero_title); ?></span>
            </nav>
        </div>
    </section>

    <!-- News & Articles Grid -->
    <section class="bg-gray-50 font-sans antialiased py-16 lg:py-24">
        <div class="max-w-7xl mx-auto px-6">
            
            <?php if (have_posts()) : ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php
                    while (have_posts()) :
                        the_post();
                        $post_id  = get_the_ID();
                        $thumb    = get_the_post_thumbnail_url($post_id, 'medium_large');
                        if (!$thumb) $thumb = 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=600&q=80';
                        $excerpt  = get_the_excerpt($post_id);
                        if (!$excerpt) {
                            $excerpt = wp_trim_words(wp_strip_all_tags((string) get_post_field('post_content', $post_id)), 20);
                        }
                    ?>
                        <article class="w-full bg-white rounded-[20px] card-shadow overflow-hidden border border-gray-100 h-full flex flex-col" data-purpose="blog-post-card">
                            <header class="relative">
                                <a href="<?php echo esc_url(get_permalink()); ?>" class="block h-64 overflow-hidden group">
                                    <img src="<?php echo esc_url($thumb); ?>"
                                        alt="<?php echo esc_attr(get_the_title()); ?>"
                                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
                                </a>
                            </header>
                            
                            <section class="bg-white px-8 pt-10 pb-8 flex flex-col flex-1">
                                <div class="flex flex-wrap items-center gap-y-2 gap-x-4 mb-4 text-gray-500 text-xs font-medium uppercase tracking-wide">

                                    <div class="flex items-center space-x-1">
                                        <svg class="h-4 w-4 text-[#f99121]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                                        </svg>
                                        <span><?php echo get_the_date(); ?></span>
                                    </div>
                                </div>
                                
                                <h2 class="text-[#2a2d3e] text-2xl font-bold leading-tight mb-4">
                                    <a href="<?php echo esc_url(get_permalink()); ?>" class="hover:text-orange-600 transition-colors duration-200">
                                        <?php the_title(); ?>
                                    </a>
                                </h2>
                                
                                <?php if ($excerpt) : ?>
                                    <p class="text-gray-500 text-sm leading-relaxed mb-6">
                                        <?php echo esc_html($excerpt); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <a href="<?php echo esc_url(get_permalink()); ?>" class="inline-flex items-center text-[#f99121] text-xs font-bold uppercase tracking-[2px] hover:text-orange-600 transition-colors duration-200 mt-auto">
                                    <?php esc_html_e('Read More', 'aatf-expedition-base'); ?>
                                    <svg class="h-4 w-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" />
                                    </svg>
                                </a>
                            </section>
                        </article>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <div class="mt-16 flex justify-center">
                    <?php
                    echo paginate_links(array(
                        'base'         => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                        'format'       => '?paged=%#%',
                        'current'      => max(1, get_query_var('paged')),
                        'total'        => $wp_query->max_num_pages,
                        'prev_text'    => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>',
                        'next_text'    => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>',
                        'type'         => 'list',
                        'class'        => 'aatf-pagination'
                    ));
                    ?>
                </div>

            <?php else : ?>
                <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-200">
                    <p class="text-[var(--brand-gray)] text-lg"><?php esc_html_e('No posts found.', 'aatf-expedition-base'); ?></p>
                </div>
            <?php endif; ?>

        </div>
    </section>

</div>

<style>
/* Custom Pagination Styles */
.aatf-pagination {
    display: flex;
    gap: 0.5rem;
    list-style: none;
    padding: 0;
    margin: 0;
}
.aatf-pagination li a, 
.aatf-pagination li span {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.75rem;
    background: white;
    border: 1px solid #f3f4f6;
    color: var(--brand-dark);
    font-weight: 600;
    font-size: 0.875rem;
    transition: all 0.2s;
}
.aatf-pagination li span.current {
    background: var(--brand-orange);
    border-color: var(--brand-orange);
    color: white;
}
.aatf-pagination li a:hover {
    border-color: var(--brand-orange);
    color: var(--brand-orange);
}
</style>

<?php get_footer(); ?>
