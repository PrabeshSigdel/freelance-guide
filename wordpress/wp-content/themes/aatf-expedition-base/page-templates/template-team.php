<?php
/**
 * Template Name: Team Page
 *
 * Full-page template showing the team members via a Tailwind CSS grid.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$post_id = get_the_ID();

// Get the team members
$team_members = get_posts(array(
    'post_type' => 'team_member',
    'post_status' => 'publish',
    'numberposts' => -1,
    'orderby' => 'menu_order title',
    'order' => 'ASC',
));

?>

<div class="site-main aatf-team-page">

    <!-- Hero Section using Tailwind -->
    <section class="team-hero bg-gray-50 py-16 md:py-24 border-b border-gray-200">
        <div class="container mx-auto px-4 max-w-5xl text-center">
            <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-6 drop-shadow-sm tracking-tight">
                <?php echo esc_html(get_the_title($post_id)); ?>
            </h1>

            <div class="team-hero-content text-lg md:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                <?php
                // Fetch post content and apply proper paragraph formatting
                $content = get_post_field('post_content', $post_id);
                echo wp_kses_post(wpautop($content));
                ?>
            </div>
        </div>
    </section>

    <!-- Team Grid Section -->
    <section class="team-grid-section py-16 md:py-24 bg-white">
        <div class="container mx-auto px-4 max-w-7xl">
            <?php if (empty($team_members)): ?>
                <div class="text-center text-gray-500 py-12 text-lg">
                    <p>No team members found. Start adding them in the admin panel!</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-8 md:gap-12">
                    <?php foreach ($team_members as $member):
                        $member_id = $member->ID;

                        $image_id = get_post_meta($member_id, 'team_member_image_id', true);
                        if ($image_id) {
                            $thumbnail_url = wp_get_attachment_image_url($image_id, 'medium_large');
                        } else {
                            $thumbnail_url = get_the_post_thumbnail_url($member_id, 'medium_large');
                        }

                        if (!$thumbnail_url) {
                            $thumbnail_url = 'https://via.placeholder.com/600x800?text=No+Photo'; // fallback
                        }

                        $position = get_post_meta($member_id, 'team_position', true);
                        $experience = get_post_meta($member_id, 'team_experience', true);
                        $languages = get_post_meta($member_id, 'team_languages', true);
                        ?>

                        <div
                            class="team-card group bg-white rounded-3xl overflow-hidden shadow-[0_4px_20px_rgb(0,0,0,0.04)] hover:shadow-2xl transition-all duration-300 border border-gray-100 flex flex-col h-full hover:-translate-y-1">

                            <!-- Image -->
                            <div class="relative w-full aspect-square overflow-hidden bg-gray-100">
                                <img src="<?php echo esc_url($thumbnail_url); ?>"
                                    alt="<?php echo esc_attr($member->post_title); ?>"
                                    class="object-cover w-full h-full transition-transform duration-500 group-hover:scale-105"
                                    loading="lazy" />
                            </div>

                            <!-- Content -->
                            <div class="p-6 flex-grow flex flex-col justify-between relative z-10 bg-white">
                                <div>
                                    <h3 class="text-xl md:text-2xl font-black text-gray-900 mb-1">
                                        <?php echo esc_html($member->post_title); ?></h3>
                                    <?php if (!empty($position)): ?>
                                        <p
                                            class="text-[var(--brand-orange,#e8820c)] font-bold text-xs tracking-wider uppercase mb-5">
                                            <?php echo esc_html($position); ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="space-y-3 pt-4 border-t border-gray-100 text-gray-600">
                                    <?php if (!empty($experience)): ?>
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0 text-blue-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <span class="text-sm font-medium"><strong>Exp:</strong>
                                                <?php echo esc_html($experience); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($languages)): ?>
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center flex-shrink-0 text-emerald-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                                                </svg>
                                            </div>
                                            <span class="text-sm font-medium"><strong>Lang:</strong>
                                                <?php echo esc_html($languages); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

</div>

<?php get_footer(); ?>