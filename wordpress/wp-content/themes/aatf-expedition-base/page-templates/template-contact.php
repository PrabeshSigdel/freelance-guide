<?php
/* Template Name: Contact Us Template */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Fetch meta values
$post_id = get_the_ID();
$phone = get_post_meta($post_id, 'aatf_contact_phone', true);
$email = get_post_meta($post_id, 'aatf_contact_email', true);
$address = get_post_meta($post_id, 'aatf_contact_address', true);
$map_url = get_post_meta($post_id, 'aatf_contact_map_url', true);
?>

<div class="bg-slate-50 py-16 md:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="text-center mb-16">
            <h1 class="text-4xl md:text-5xl font-bold text-slate-900 mb-4"><?php echo esc_html(get_the_title()); ?></h1>
            <div class="text-lg text-slate-600 max-w-2xl mx-auto">
                <?php while(have_posts()) { the_post(); the_content(); } ?>
                <?php if (empty(get_the_content())): ?>
                    <p>We'd love to hear from you. Please fill out this form or get in touch using the information below.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16">
            
            <!-- Left Side: Contact Information -->
            <div class="flex flex-col gap-8">
                
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 flex flex-col gap-6">
                    <h3 class="text-2xl font-semibold text-slate-900">Contact Information</h3>
                    
                    <div class="flex flex-col gap-6">
                        
                        <?php if ($email): ?>
                        <div class="flex items-start gap-4 group">
                            <div class="flex-shrink-0 w-12 h-12 bg-orange-50 text-orange-500 rounded-xl flex items-center justify-center transition-colors group-hover:bg-orange-500 group-hover:text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500 uppercase tracking-wider mb-1">Email Address</p>
                                <a href="mailto:<?php echo esc_attr($email); ?>" class="text-lg text-slate-900 font-medium hover:text-orange-500 transition-colors"><?php echo esc_html($email); ?></a>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($phone): ?>
                        <div class="flex items-start gap-4 group">
                            <div class="flex-shrink-0 w-12 h-12 bg-orange-50 text-orange-500 rounded-xl flex items-center justify-center transition-colors group-hover:bg-orange-500 group-hover:text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500 uppercase tracking-wider mb-1">Phone Number</p>
                                <a href="tel:<?php echo esc_attr(preg_replace('/[^\+0-9]/', '', $phone)); ?>" class="text-lg text-slate-900 font-medium hover:text-orange-500 transition-colors"><?php echo esc_html($phone); ?></a>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($address): ?>
                        <div class="flex items-start gap-4 group">
                            <div class="flex-shrink-0 w-12 h-12 bg-orange-50 text-orange-500 rounded-xl flex items-center justify-center transition-colors group-hover:bg-orange-500 group-hover:text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500 uppercase tracking-wider mb-1">Physical Address</p>
                                <address class="text-lg text-slate-900 font-medium not-italic leading-snug">
                                    <?php echo nl2br(esc_html($address)); ?>
                                </address>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>

                <?php if ($map_url): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden h-64 md:h-80 lg:h-96 relative">
                    <iframe 
                        src="<?php echo esc_url($map_url); ?>" 
                        class="absolute inset-0 w-full h-full border-0"
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
                <?php endif; ?>

            </div>

            <!-- Right Side: Contact Form -->
            <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/40 border border-slate-100 p-8 md:p-10">
                <h3 class="text-2xl font-semibold text-slate-900 mb-8">Send us a Message</h3>
                
                <form action="#" method="POST" class="flex flex-col gap-5" onsubmit="event.preventDefault(); alert('We have received your message. Thank you!');">
                    
                    <div>
                        <label for="contact-name" class="block text-sm font-medium text-slate-700 mb-1.5">Full Name</label>
                        <input type="text" id="contact-name" name="contact-name" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 outline-none transition-all duration-200 bg-slate-50 focus:bg-white text-slate-900"
                            placeholder="John Doe">
                    </div>

                    <div>
                        <label for="contact-email" class="block text-sm font-medium text-slate-700 mb-1.5">Your Email Address</label>
                        <input type="email" id="contact-email" name="contact-email" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 outline-none transition-all duration-200 bg-slate-50 focus:bg-white text-slate-900"
                            placeholder="john@example.com">
                    </div>

                    <div>
                        <label for="contact-subject" class="block text-sm font-medium text-slate-700 mb-1.5">Subject</label>
                        <input type="text" id="contact-subject" name="contact-subject" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 outline-none transition-all duration-200 bg-slate-50 focus:bg-white text-slate-900"
                            placeholder="How can we help you?">
                    </div>

                    <div>
                        <label for="contact-message" class="block text-sm font-medium text-slate-700 mb-1.5">Message</label>
                        <textarea id="contact-message" name="contact-message" rows="5" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 outline-none transition-all duration-200 bg-slate-50 focus:bg-white text-slate-900 resize-y"
                            placeholder="Write your message here..."></textarea>
                    </div>

                    <button type="submit" 
                        class="mt-4 w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3.5 px-8 rounded-xl shadow-lg shadow-orange-500/30 transition-all duration-200 transform hover:-translate-y-0.5">
                        Submit Message
                    </button>
                    
                </form>
            </div>

        </div>
    </div>
</div>

<?php
get_footer();
