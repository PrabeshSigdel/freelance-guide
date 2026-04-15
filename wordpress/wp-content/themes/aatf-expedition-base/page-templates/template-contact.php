<?php
/* Template Name: Contact Us Template */

if (!defined('ABSPATH')) {
    exit;
}

// ─── Form Handling Logic ─────────────────────────────────────
$contact_success = false;
$contact_error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aatf_contact_nonce'])) {
    // 1. Verify Nonce
    if (!wp_verify_nonce($_POST['aatf_contact_nonce'], 'aatf_contact_submit')) {
        $contact_error = __('Security check failed. Please refresh and try again.', 'aatf-expedition-base');
    } 
    // 2. Honeypot check (hidden field should be empty)
    elseif (!empty($_POST['aatf_contact_hp'])) {
        $contact_error = __('Spam detected.', 'aatf-expedition-base');
    }
    else {
        // 3. Collect and sanitize input
        $name    = sanitize_text_field($_POST['contact_name'] ?? '');
        $email   = sanitize_email($_POST['contact_email'] ?? '');
        $subject = sanitize_text_field($_POST['contact_subject'] ?? '');
        $message = sanitize_textarea_field($_POST['contact_message'] ?? '');

        // 4. Validation
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            $contact_error = __('Please fill in all required fields.', 'aatf-expedition-base');
        } elseif (!is_email($email)) {
            $contact_error = __('Please enter a valid email address.', 'aatf-expedition-base');
        } else {
            // 5. Send Email
            $admin_email = get_option('admin_email');
            $site_name   = get_bloginfo('name');
            
            $mail_subject = sprintf('[%s Contact Form] %s', $site_name, $subject);
            
            $mail_body  = "You have a new message from your Contact Us form:\n\n";
            $mail_body .= "Name: $name\n";
            $mail_body .= "Email: $email\n";
            $mail_body .= "Subject: $subject\n\n";
            $mail_body .= "Message:\n$message\n\n";
            $mail_body .= "--------------------------------------------------\n";
            $mail_body .= "Sent from: " . esc_url(get_permalink());

            $headers = array('Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $name . ' <' . $email . '>');
            
            if (wp_mail($admin_email, $mail_subject, $mail_body, $headers)) {
                $contact_success = true;
            } else {
                $contact_error = __('Failed to send message. Please try again later or contact us directly.', 'aatf-expedition-base');
            }
        }
    }
}

get_header();

// Fetch meta values
$post_id = get_the_ID();
$phone    = (string) get_post_meta($post_id, 'aatf_contact_phone', true);
$email    = (string) get_post_meta($post_id, 'aatf_contact_email', true);
$address  = (string) get_post_meta($post_id, 'aatf_contact_address', true);
$map_url  = (string) get_post_meta($post_id, 'aatf_contact_map_url', true);

$hero_image_id  = (int) get_post_meta($post_id, 'aatf_contact_hero_image_id', true);
$hero_image_url = $hero_image_id > 0
    ? (string) wp_get_attachment_image_url($hero_image_id, 'full')
    : '';

// Inline page content excerpt for subtitle
$subtitle = '';
if (have_posts()) {
    while (have_posts()) {
        the_post();
        $subtitle = get_the_excerpt();
    }
}
?>

<style>
/* ─── Contact Body ─────────────────────────────────────────── */
.contact-section {
    background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
    padding: clamp(3rem, 7vw, 5rem) 1.25rem;
}
.contact-inner {
    max-width: 1160px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1.05fr;
    gap: 3rem;
    align-items: start;
}
@media (max-width: 960px) {
    .contact-inner { grid-template-columns: 1fr; }
}

/* ─── Left: Info Panel ─────────────────────────────────────── */
.contact-info-panel {
    display: flex;
    flex-direction: column;
    gap: 1.4rem;
}
.contact-info-box {
    background: #ffffff;
    border: 1px solid #e8edf4;
    border-radius: 20px;
    padding: 2rem 2rem 1.75rem;
    box-shadow: 0 4px 24px rgba(15,40,60,0.06);
}
.contact-info-box__title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 0.6rem;
}
.contact-info-box__title-dot {
    display: inline-block;
    width: 10px; height: 10px;
    border-radius: 3px;
    background: #f97316;
}

.contact-detail-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.2s;
}
.contact-detail-item:last-child { border-bottom: none; padding-bottom: 0; }
.contact-detail-item:first-child { padding-top: 0; }

.contact-detail-icon {
    flex-shrink: 0;
    width: 46px; height: 46px;
    border-radius: 13px;
    background: #fff7ed;
    border: 1px solid #fed7aa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #f97316;
    transition: background 0.2s, color 0.2s, transform 0.2s;
}
.contact-detail-item:hover .contact-detail-icon {
    background: #f97316;
    color: #ffffff;
    transform: scale(1.08);
}

.contact-detail-label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.09em;
    color: #94a3b8;
    margin: 0 0 0.25rem;
}
.contact-detail-value {
    font-size: 1rem;
    color: #1e293b;
    font-weight: 500;
    text-decoration: none;
    transition: color 0.2s;
    display: block;
}
.contact-detail-value:hover { color: #f97316; }

/* Map */
.contact-map {
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid #e8edf4;
    box-shadow: 0 4px 24px rgba(15,40,60,0.06);
    height: 280px;
    position: relative;
}
.contact-map iframe {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    border: 0;
    display: block;
}

/* ─── Right: Form Panel ────────────────────────────────────── */
.contact-form-panel {
    background: #ffffff;
    border: 1px solid #e8edf4;
    border-radius: 24px;
    padding: 4.1rem 2.4rem;
    box-shadow: 0 12px 48px rgba(15,40,60,0.09);
    position: relative;
    overflow: hidden;
}
.contact-form-panel::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, #f97316, #ef4444, #f97316);
    background-size: 200% 100%;
    animation: aatf-shimmer 3s linear infinite;
}
@keyframes aatf-shimmer {
    0%   { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.contact-form-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 0.35rem;
}
.contact-form-subtitle {
    color: #64748b;
    font-size: 0.93rem;
    margin: 0 0 2rem;
}

.contact-form { display: flex; flex-direction: column; gap: 1.1rem; }

.contact-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
@media (max-width: 540px) {
    .contact-form-row { grid-template-columns: 1fr; }
    .contact-form-panel { padding: 1.75rem 1.25rem; }
}

.cf-field label {
    display: block;
    font-size: 0.82rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.5rem;
    letter-spacing: 0.02em;
}
.cf-field input,
.cf-field textarea {
    width: 100%;
    padding: 0.8rem 1rem;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    font-size: 0.95rem;
    color: #0f172a;
    background: #f8fafc;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    outline: none;
    resize: vertical;
    box-sizing: border-box;
}
.cf-field input:focus,
.cf-field textarea:focus {
    border-color: #f97316;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.12);
}
.cf-field textarea { min-height: 130px; }

.cf-submit-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.875rem 2rem;
    background-color: var(--brand-orange);
    color: #ffffff;
    font-size: 0.875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    border: none;
    border-radius: 0.5rem;
    cursor: pointer;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    transition: opacity 0.2s ease;
    margin-top: 0.5rem;
}
.cf-submit-btn:hover {
    opacity: 0.9;
    transform: none;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
}
.cf-submit-btn svg { flex-shrink: 0; }

/* ─── Highlight badges ─────────────────────────────────────── */

.contact-badge {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.78rem;
    color: #64748b;
    font-weight: 500;
}
.contact-badge svg { color: #22c55e; flex-shrink: 0; }
</style>

<!-- ── Hero Section ────────────────────────────────────────────── -->
<section class="relative min-h-[450px] flex items-center justify-center text-center py-20 bg-cover bg-center" 
    style="<?php echo $hero_image_url ? "background-image: url('" . esc_url($hero_image_url) . "');" : "background: linear-gradient(135deg, #063329 0%, #0b5c48 60%, #0d6b54 100%);"; ?>">
    
    <div class="absolute inset-0 bg-black/55"></div>
    
    <div class="relative z-10 max-w-4xl mx-auto px-6">
        <h1 class="font-display text-5xl md:text-6xl font-bold text-white mb-6 drop-shadow-lg tracking-tight">
            <?php echo esc_html(get_the_title()); ?>
        </h1>
        
        <?php if ($subtitle) : ?>
            <p class="text-white/80 text-lg md:text-xl font-light mb-10 max-w-2xl mx-auto leading-relaxed">
                <?php echo esc_html($subtitle); ?>
            </p>
        <?php else : ?>
            <p class="text-white/80 text-lg md:text-xl font-light mb-10 max-w-2xl mx-auto leading-relaxed">
                <?php esc_html_e("We'd love to hear from you. Reach out to our team of experts for any questions or support.", "aatf-expedition-base"); ?>
            </p>
        <?php endif; ?>

        <nav class="flex items-center justify-center gap-2 text-sm font-medium text-white/60" aria-label="Breadcrumb">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="hover:text-[var(--brand-orange)] transition-colors text-white/80">Home</a>
            <svg class="w-4 h-4 text-white/40" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-[var(--brand-orange)] font-bold"><?php echo esc_html(get_the_title()); ?></span>
        </nav>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════ -->
<!-- CONTACT BODY                                               -->
<!-- ═══════════════════════════════════════════════════════════ -->
<section class="contact-section">
    <div class="contact-inner">

        <!-- ── Left: Info + Map ── -->
        <div class="contact-info-panel">

            <div class="contact-info-box">
                <h3 class="contact-info-box__title">
                    <span class="contact-info-box__title-dot"></span>
                    Contact Information
                </h3>

                <?php if ($email): ?>
                <div class="contact-detail-item">
                    <div class="contact-detail-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <p class="contact-detail-label">Email Address</p>
                        <a href="mailto:<?php echo esc_attr($email); ?>" class="contact-detail-value"><?php echo esc_html($email); ?></a>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($phone): ?>
                <div class="contact-detail-item">
                    <div class="contact-detail-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </div>
                    <div>
                        <p class="contact-detail-label">Phone Number</p>
                        <a href="tel:<?php echo esc_attr(preg_replace('/[^\+0-9]/', '', $phone)); ?>" class="contact-detail-value"><?php echo esc_html($phone); ?></a>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($address): ?>
                <div class="contact-detail-item">
                    <div class="contact-detail-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <p class="contact-detail-label">Our Office</p>
                        <address class="contact-detail-value" style="font-style:normal;"><?php echo nl2br(esc_html($address)); ?></address>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($map_url): ?>
            <div class="contact-map">
                <iframe
                    src="<?php echo esc_url($map_url); ?>"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="<?php esc_attr_e('Our Location on Google Maps', 'aatf-expedition-base'); ?>"
                ></iframe>
            </div>
            <?php endif; ?>

        </div>


        <!-- ── Right: Form ── -->
        <div class="contact-form-panel">
            <h2 class="contact-form-title">Send Us a Message</h2>
            <p class="contact-form-subtitle">Fill out the form below and we'll get back to you as soon as possible.</p>

            <?php if ($contact_error): ?>
                <div style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; padding:1rem; border-radius:12px; margin-bottom:1.5rem; font-size:0.9rem;">
                    <strong>Error:</strong> <?php echo esc_html($contact_error); ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo esc_url(get_permalink()); ?>" method="POST" class="contact-form" <?php echo $contact_success ? 'style="display:none;"' : ''; ?>>
                <?php wp_nonce_field('aatf_contact_submit', 'aatf_contact_nonce'); ?>
                
                <!-- Honeypot -->
                <div style="display:none !important;">
                    <label>Leave this field empty</label>
                    <input type="text" name="aatf_contact_hp" value="">
                </div>

                <div class="contact-form-row">
                    <div class="cf-field">
                        <label for="cf-name">Full Name</label>
                        <input type="text" id="cf-name" name="contact_name" required placeholder="e.g. John Doe" value="<?php echo isset($_POST['contact_name']) ? esc_attr($_POST['contact_name']) : ''; ?>">
                    </div>
                    <div class="cf-field">
                        <label for="cf-email">Your Email</label>
                        <input type="email" id="cf-email" name="contact_email" required placeholder="john@example.com" value="<?php echo isset($_POST['contact_email']) ? esc_attr($_POST['contact_email']) : ''; ?>">
                    </div>
                </div>

                <div class="cf-field">
                    <label for="cf-subject">Subject</label>
                    <input type="text" id="cf-subject" name="contact_subject" required placeholder="How can we help?" value="<?php echo isset($_POST['contact_subject']) ? esc_attr($_POST['contact_subject']) : ''; ?>">
                </div>

                <div class="cf-field">
                    <label for="cf-message">Message</label>
                    <textarea id="cf-message" name="contact_message" required placeholder="Write your message here..."><?php echo isset($_POST['contact_message']) ? esc_textarea($_POST['contact_message']) : ''; ?></textarea>
                </div>

                <button type="submit" class="cf-submit-btn">
                    Submit 
                </button>
            </form>

            <!-- Success message -->
            <div id="cf-success" style="<?php echo $contact_success ? 'display:flex;' : 'display:none;'; ?>align-items:center;gap:1rem;background:#f0fdf4;border:1.5px solid #86efac;border-radius:14px;padding:1.25rem 1.5rem;margin-top:1.5rem;">
                <svg width="28" height="28" fill="none" stroke="#22c55e" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <p style="margin:0;font-weight:700;color:#166534;font-size:0.95rem;">Message Sent!</p>
                    <p style="margin:0.15rem 0 0;color:#15803d;font-size:0.83rem;">Thank you for reaching out. We'll be in touch soon.</p>
                </div>
            </div>
        </div>

    </div>
</section>

<?php get_footer(); ?>
