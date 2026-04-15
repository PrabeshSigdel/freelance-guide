<?php
/* Template Name: About Us Template */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="site-main">
    
    <!-- Hero Section -->
    <section class="bg-white py-16 lg:py-24 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-[var(--brand-dark)] mb-6 tracking-tight">
                Our Story & Mission
            </h1>
            <nav class="flex justify-center items-center gap-2 text-sm font-medium text-[var(--brand-gray)]" aria-label="Breadcrumb">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="hover:text-[var(--brand-orange)] transition-colors">Home</a>
                <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span class="text-[var(--brand-dark)]">About Us</span>
            </nav>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="py-16 lg:py-24 px-6 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                
                <div class="order-2 lg:order-1">
                    <div class="relative inline-block mb-8">
                        <h2 class="text-3xl lg:text-4xl font-extrabold text-[var(--brand-dark)] pr-10">Our Journey into the Wild</h2>
                        <div class="absolute bottom-0 left-0 w-16 h-1 bg-[var(--brand-orange)] rounded-full"></div>
                    </div>
                    
                    <div class="space-y-6 text-lg leading-relaxed text-[var(--brand-gray)]">
                        <p>Founded by a group of passionate mountaineers and nature enthusiasts, our company began with a simple belief: the world's most breathtaking landscapes should be accessible to those who seek adventure with respect and integrity.</p>
                        <p>Over the past decade, we have grown from a small team of local guides in the Himalayas to a premier adventure travel provider, offering unparalleled experiences that connect people with the raw beauty of our planet.</p>
                        <p>Our commitment remains unchanged: to provide authentic, safe, and life-changing treks while preserving the natural wonders and cultural heritage of the regions we explore.</p>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <div class="relative group">
                        <!-- Decorative background element -->
                        <div class="absolute -inset-4 bg-gray-50 rounded-[32px] -z-10 transition-transform group-hover:scale-105"></div>
                        
                        <!-- Image Placeholder -->
                        <div class="aspect-[4/3] w-full bg-gray-100 border-2 border-dashed border-gray-200 rounded-3xl flex flex-col items-center justify-center overflow-hidden shadow-sm transition-shadow group-hover:shadow-md">
                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                            <span class="text-sm font-semibold text-gray-400 uppercase tracking-widest">Story Image Placeholder</span>
                            <span class="text-xs text-gray-300 mt-1">Suggested: 800 x 600px</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Mission & Values Section -->
    <section class="py-16 lg:py-24 px-6 bg-gray-50">
        <div class="max-w-7xl mx-auto">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <p class="text-[var(--brand-orange)] font-bold text-sm uppercase tracking-wider mb-2">Guided by Core Principles</p>
                <h2 class="text-3xl lg:text-4xl font-extrabold text-[var(--brand-dark)] mb-6">The Values We Live By</h2>
                <p class="text-[var(--brand-gray)] text-lg">In every expedition we lead and every relationship we build, we are guided by a core set of principles that define who we are.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Mission Card -->
                <div class="bg-white p-10 rounded-3xl border border-gray-100 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1 text-center group">
                    <div class="w-16 h-16 bg-orange-50 text-[var(--brand-orange)] rounded-2xl flex items-center justify-center mx-auto mb-8 transition-colors group-hover:bg-[var(--brand-orange)] group-hover:text-white">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-[var(--brand-dark)] mb-4">Our Mission</h3>
                    <p class="text-[var(--brand-gray)] leading-relaxed">To deliver exceptional adventure experiences that inspire personal growth and foster a deep appreciation for the world's natural environments.</p>
                </div>

                <!-- Vision Card -->
                <div class="bg-white p-10 rounded-3xl border border-gray-100 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1 text-center group">
                    <div class="w-16 h-16 bg-orange-50 text-[var(--brand-orange)] rounded-2xl flex items-center justify-center mx-auto mb-8 transition-colors group-hover:bg-[var(--brand-orange)] group-hover:text-white">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-[var(--brand-dark)] mb-4">Our Vision</h3>
                    <p class="text-[var(--brand-gray)] leading-relaxed">To be the world's most trusted partner for conscious adventure travel, recognized for our commitment to safety, quality, and sustainability.</p>
                </div>

                <!-- Values Card -->
                <div class="bg-white p-10 rounded-3xl border border-gray-100 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1 text-center group">
                    <div class="w-16 h-16 bg-orange-50 text-[var(--brand-orange)] rounded-2xl flex items-center justify-center mx-auto mb-8 transition-colors group-hover:bg-[var(--brand-orange)] group-hover:text-white">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-[var(--brand-dark)] mb-4">Our Values</h3>
                    <p class="text-[var(--brand-gray)] leading-relaxed">Integrity in action, safety above all, respect for local cultures, and an unwavering passion for the great outdoors.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Us Section -->
    <section class="py-16 lg:py-24 px-6 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                
                <div>
                    <p class="text-[var(--brand-orange)] font-bold text-sm uppercase tracking-wider mb-2">Why choose Us?</p>
                    <h2 class="text-3xl lg:text-4xl font-extrabold text-[var(--brand-dark)] mb-6">Expertise in Every Step</h2>
                    <p class="text-[var(--brand-gray)] text-lg mb-10 leading-relaxed">We know you have choices when it comes to adventure. Here is why trekking with us is an experience like no other.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-10">
                        <!-- Benefit 1 -->
                        <div class="flex gap-4">
                            <div class="shrink-0 w-6 h-6 bg-[var(--brand-orange)] text-white rounded-full flex items-center justify-center text-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-[var(--brand-dark)] mb-1">Expert Local Guides</h4>
                                <p class="text-sm text-[var(--brand-gray)]">Local experts who know every hidden trail.</p>
                            </div>
                        </div>

                        <!-- Benefit 2 -->
                        <div class="flex gap-4">
                            <div class="shrink-0 w-6 h-6 bg-[var(--brand-orange)] text-white rounded-full flex items-center justify-center text-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-[var(--brand-dark)] mb-1">Safety First</h4>
                                <p class="text-sm text-[var(--brand-gray)]">Rigorous safety standards and equipment.</p>
                            </div>
                        </div>

                        <!-- Benefit 3 -->
                        <div class="flex gap-4">
                            <div class="shrink-0 w-6 h-6 bg-[var(--brand-orange)] text-white rounded-full flex items-center justify-center text-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-[var(--brand-dark)] mb-1">Tailored Experiences</h4>
                                <p class="text-sm text-[var(--brand-gray)]">Customizable itineraries just for you.</p>
                            </div>
                        </div>

                        <!-- Benefit 4 -->
                        <div class="flex gap-4">
                            <div class="shrink-0 w-6 h-6 bg-[var(--brand-orange)] text-white rounded-full flex items-center justify-center text-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-[var(--brand-dark)] mb-1">Sustainable Tourism</h4>
                                <p class="text-sm text-[var(--brand-gray)]">Leaving only footprints, supporting locals.</p>
                            </div>
                        </div>
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
