<?php
/* Template Name: About Us Template */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$post_id = get_the_ID();

// Hero Section
$hero_image_id  = (int) get_post_meta($post_id, 'aatf_about_hero_image_id', true);
$hero_bg_url = $hero_image_id > 0 ? wp_get_attachment_image_url($hero_image_id, 'full') : '';
$hero_style = $hero_bg_url ? 'background-image: url(' . esc_url($hero_bg_url) . '); background-size: cover; background-position: center;' : '';

// Our Story Section
$story_title = get_post_meta($post_id, 'aatf_about_story_title', true) ?: 'Our Journey into the Wild';
$story_content = get_post_meta($post_id, 'aatf_about_story_content', true);
$story_image_id = (int) get_post_meta($post_id, 'aatf_about_story_image_id', true);
$story_image_url = $story_image_id > 0 ? wp_get_attachment_image_url($story_image_id, 'large') : '';

// Mission & Values Section
$values_subtitle = 'Guided by Core Principles';
$values_title = get_post_meta($post_id, 'aatf_about_values_title', true) ?: 'The Values We Live By';
$values_desc = get_post_meta($post_id, 'aatf_about_values_desc', true) ?: 'In every expedition we lead and every relationship we build, we are guided by a core set of principles that define who we are.';

$mission_title = get_post_meta($post_id, 'aatf_about_mission_title', true) ?: 'Our Mission';
$mission_text = get_post_meta($post_id, 'aatf_about_mission_text', true) ?: 'To deliver exceptional adventure experiences that inspire personal growth and foster a deep appreciation for the world\'s natural environments.';

$vision_title = get_post_meta($post_id, 'aatf_about_vision_title', true) ?: 'Our Vision';
$vision_text = get_post_meta($post_id, 'aatf_about_vision_text', true) ?: 'To be the world\'s most trusted partner for conscious adventure travel, recognized for our commitment to safety, quality, and sustainability.';

$core_title = get_post_meta($post_id, 'aatf_about_core_title', true) ?: 'Our Values';
$core_text = get_post_meta($post_id, 'aatf_about_core_text', true) ?: 'Integrity in action, safety above all, respect for local cultures, and an unwavering passion for the great outdoors.';

// Why Choose Us Section
$whyus_subtitle = 'Why choose Us?';
$whyus_title = get_post_meta($post_id, 'aatf_about_whyus_title', true) ?: 'Expertise in Every Step';
$whyus_desc = get_post_meta($post_id, 'aatf_about_whyus_desc', true) ?: 'We know you have choices when it comes to adventure. Here is why trekking with us is an experience like no other.';
$whyus_points_raw = get_post_meta($post_id, 'aatf_about_whyus_points', true);
$whyus_points = array();

if ($whyus_points_raw) {
    $lines = is_array($whyus_points_raw) ? $whyus_points_raw : preg_split('/\r\n|\n|\r/', $whyus_points_raw);
    foreach ($lines as $line) {
        $parts = explode('|', $line, 2);
        if (count($parts) === 2) {
            $whyus_points[] = array('title' => trim($parts[0]), 'desc' => trim($parts[1]));
        } elseif (count($parts) === 1 && trim($parts[0]) !== '') {
            $whyus_points[] = array('title' => trim($parts[0]), 'desc' => '');
        }
    }
}

if (empty($whyus_points)) {
    $whyus_points = array(
        array('title' => 'Expert Local Guides', 'desc' => 'Local experts who know every hidden trail.'),
        array('title' => 'Safety First', 'desc' => 'Rigorous safety standards and equipment.'),
        array('title' => 'Tailored Experiences', 'desc' => 'Customizable itineraries just for you.'),
        array('title' => 'Sustainable Tourism', 'desc' => 'Leaving only footprints, supporting locals.'),
    );
}

// Convert story_content to paragraphs HTML
if (empty($story_content)) {
    $story_content_html = '<p>Founded by a group of passionate mountaineers and nature enthusiasts, our company began with a simple belief: the world\'s most breathtaking landscapes should be accessible to those who seek adventure with respect and integrity.</p>
<p>Over the past decade, we have grown from a small team of local guides in the Himalayas to a premier adventure travel provider, offering unparalleled experiences that connect people with the raw beauty of our planet.</p>
<p>Our commitment remains unchanged: to provide authentic, safe, and life-changing treks while preserving the natural wonders and cultural heritage of the regions we explore.</p>';
} else {
    $story_content_html = '';
    $paragraphs = preg_split('/\r\n|\n|\r/', $story_content);
    foreach ($paragraphs as $p) {
        $p = trim($p);
        if ($p !== '') {
            $story_content_html .= '<p>' . esc_html($p) . '</p>';
        }
    }
}
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
                Our Story & Mission
            </h1>
            <nav class="flex justify-center items-center gap-2 text-sm font-medium text-gray-300" aria-label="Breadcrumb">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="hover:text-[var(--brand-orange)] transition-colors text-white">Home</a>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span class="text-white">About Us</span>
            </nav>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="py-16 lg:py-24 px-6 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                
                <div class="order-2 lg:order-1">
                    <div class="relative inline-block mb-8">
                        <h2 class="text-3xl lg:text-4xl font-extrabold text-[var(--brand-dark)] pr-10"><?php echo esc_html($story_title); ?></h2>
                        <div class="absolute bottom-0 left-0 w-16 h-1 bg-[var(--brand-orange)] rounded-full"></div>
                    </div>
                    
                    <div class="space-y-6 text-lg leading-relaxed text-[var(--brand-gray)]">
                        <?php echo wp_kses_post($story_content_html); ?>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <div class="relative group">
                        <!-- Decorative background element -->
                        <div class="absolute -inset-4 bg-gray-50 rounded-[32px] -z-10 transition-transform group-hover:scale-105"></div>
                        
                        <?php if ($story_image_url) : ?>
                            <div class="aspect-[4/3] w-full rounded-3xl overflow-hidden shadow-sm transition-shadow group-hover:shadow-md">
                                <img src="<?php echo esc_url($story_image_url); ?>" alt="<?php echo esc_attr($story_title); ?>" class="w-full h-full object-cover">
                            </div>
                        <?php else : ?>
                            <!-- Image Placeholder -->
                            <div class="aspect-[4/3] w-full bg-gray-100 border-2 border-dashed border-gray-200 rounded-3xl flex flex-col items-center justify-center overflow-hidden shadow-sm transition-shadow group-hover:shadow-md">
                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                <span class="text-sm font-semibold text-gray-400 uppercase tracking-widest">Story Image Placeholder</span>
                                <span class="text-xs text-gray-300 mt-1">Suggested: 800 x 600px</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Mission & Values Section -->
    <section class="py-16 lg:py-24 px-6 bg-gray-50">
        <div class="max-w-7xl mx-auto">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <p class="text-[var(--brand-orange)] font-bold text-sm uppercase tracking-wider mb-2"><?php echo esc_html($values_subtitle); ?></p>
                <h2 class="text-3xl lg:text-4xl font-extrabold text-[var(--brand-dark)] mb-6"><?php echo esc_html($values_title); ?></h2>
                <p class="text-[var(--brand-gray)] text-lg"><?php echo esc_html($values_desc); ?></p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Mission Card -->
                <div class="bg-white p-10 rounded-3xl border border-gray-100 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1 text-center group">
                    <div class="w-16 h-16 bg-orange-50 text-[var(--brand-orange)] rounded-2xl flex items-center justify-center mx-auto mb-8 transition-colors group-hover:bg-[var(--brand-orange)] group-hover:text-white">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-[var(--brand-dark)] mb-4"><?php echo esc_html($mission_title); ?></h3>
                    <p class="text-[var(--brand-gray)] leading-relaxed"><?php echo esc_html($mission_text); ?></p>
                </div>

                <!-- Vision Card -->
                <div class="bg-white p-10 rounded-3xl border border-gray-100 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1 text-center group">
                    <div class="w-16 h-16 bg-orange-50 text-[var(--brand-orange)] rounded-2xl flex items-center justify-center mx-auto mb-8 transition-colors group-hover:bg-[var(--brand-orange)] group-hover:text-white">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-[var(--brand-dark)] mb-4"><?php echo esc_html($vision_title); ?></h3>
                    <p class="text-[var(--brand-gray)] leading-relaxed"><?php echo esc_html($vision_text); ?></p>
                </div>

                <!-- Values Card -->
                <div class="bg-white p-10 rounded-3xl border border-gray-100 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1 text-center group">
                    <div class="w-16 h-16 bg-orange-50 text-[var(--brand-orange)] rounded-2xl flex items-center justify-center mx-auto mb-8 transition-colors group-hover:bg-[var(--brand-orange)] group-hover:text-white">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-[var(--brand-dark)] mb-4"><?php echo esc_html($core_title); ?></h3>
                    <p class="text-[var(--brand-gray)] leading-relaxed"><?php echo esc_html($core_text); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Us Section -->
    <section class="py-16 lg:py-24 px-6 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                
                <div>
                    <p class="text-[var(--brand-orange)] font-bold text-sm uppercase tracking-wider mb-2"><?php echo esc_html($whyus_subtitle); ?></p>
                    <h2 class="text-3xl lg:text-4xl font-extrabold text-[var(--brand-dark)] mb-6"><?php echo esc_html($whyus_title); ?></h2>
                    <p class="text-[var(--brand-gray)] text-lg mb-10 leading-relaxed"><?php echo esc_html($whyus_desc); ?></p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-10">
                        <?php foreach ($whyus_points as $point) : ?>
                        <!-- Benefit -->
                        <div class="flex gap-4 items-start">
                            <div class="shrink-0 w-2 h-2 mt-2.5 bg-[var(--brand-orange)] rounded-full"></div>
                            <div>
                                <h4 class="font-bold text-[var(--brand-dark)] mb-1"><?php echo esc_html($point['title']); ?></h4>
                                <?php if ($point['desc']) : ?>
                                    <p class="text-sm text-[var(--brand-gray)]"><?php echo esc_html($point['desc']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="lg:pl-10">
                    <div class="bg-gray-50 rounded-3xl p-8 border border-gray-100 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-[var(--brand-orange)] opacity-5 -mr-16 -mt-16 rounded-full"></div>
                        <h4 class="text-2xl font-bold text-[var(--brand-dark)] mb-4">Ready to Start Your Journey?</h4>
                        <p class="text-[var(--brand-gray)] mb-8">Contact our experts today and start planning the adventure of a lifetime.</p>
                        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="inline-block bg-[var(--brand-dark)] text-white px-8 py-3.5 rounded-xl font-bold text-sm tracking-wide hover:bg-[var(--brand-orange)] transition-colors">
                            Contact Us Today
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

</div>

<?php get_footer(); ?>
