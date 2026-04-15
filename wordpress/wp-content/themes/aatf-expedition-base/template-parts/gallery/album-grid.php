<?php
/**
 * Gallery Album Grid + Lightbox
 *
 * Expected variables passed from template-gallery.php:
 *   $terms         (array) — gallery_category terms  (may be empty = "All" only)
 *   $all_albums    (array) — all published gallery_album WP_Post objects
 *   $hero_title    (string)
 *   $hero_subtitle (string)
 *   $hero_bg_url   (string)
 */

if (!defined('ABSPATH')) {
    exit;
}

$hero_bg_url   = isset($hero_bg_url)   ? $hero_bg_url   : '';
$hero_title    = isset($hero_title)    ? $hero_title    : 'Our Gallery';
$hero_subtitle = isset($hero_subtitle) ? $hero_subtitle : 'Memories from the Mountains';
$terms         = isset($terms)         ? $terms         : array();
$all_albums    = isset($all_albums)    ? $all_albums    : array();

$hero_style = $hero_bg_url
    ? 'background-image: url(' . esc_url($hero_bg_url) . '); background-size: cover; background-position: center;'
    : '';
?>

<div class="aatf-gallery-page">

    <!-- ======================================================
         Hero
    ======================================================= -->
    <section class="relative py-16 lg:py-24 border-b border-gray-100" style="<?php echo esc_attr($hero_style); ?>">
        <?php if ($hero_bg_url) : ?>
            <div class="absolute inset-0 bg-black/60"></div>
        <?php else : ?>
            <div class="absolute inset-0 bg-[var(--brand-dark)]"></div>
        <?php endif; ?>

        <div class="relative max-w-7xl mx-auto px-6 text-center">
            <p class="text-[var(--brand-orange)] font-bold text-sm uppercase tracking-widest mb-3">Photo Gallery</p>
            <h1 class="text-3xl md:text-5xl font-extrabold text-white mb-4 tracking-tight">
                <?php echo esc_html($hero_title); ?>
            </h1>
            <p class="text-gray-300 text-lg max-w-xl mx-auto mb-6"><?php echo esc_html($hero_subtitle); ?></p>
            <nav class="flex justify-center items-center gap-2 text-sm font-medium text-gray-300" aria-label="Breadcrumb">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="hover:text-[var(--brand-orange)] transition-colors text-white">Home</a>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span class="text-white">Gallery</span>
            </nav>
        </div>
    </section>

    <!-- ======================================================
         Gallery body
    ======================================================= -->
    <section class="py-16 lg:py-20 px-6 bg-white">
        <div class="max-w-7xl mx-auto">

            <?php if (empty($all_albums)) : ?>
                <!-- No albums at all -->
                <div class="aatf-gallery-empty">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                    </svg>
                    <h3 class="text-xl font-bold text-gray-400 mb-2"><?php esc_html_e('No albums yet', 'aatf-expedition-base'); ?></h3>
                    <p class="text-gray-400"><?php esc_html_e('Add Gallery Albums from the WordPress admin to display them here.', 'aatf-expedition-base'); ?></p>
                </div>

            <?php else : ?>

                <!-- ---- Category Filter Tabs ---- -->
                <?php if (!empty($terms)) : ?>
                <div class="aatf-gallery-filters" role="tablist" aria-label="<?php esc_attr_e('Filter by Category', 'aatf-expedition-base'); ?>">
                    <button class="aatf-gallery-filter-btn is-active" data-filter="all" role="tab" aria-selected="true" id="aatf-filter-all">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <?php esc_html_e('All Albums', 'aatf-expedition-base'); ?>
                    </button>
                    <?php foreach ($terms as $term) : ?>
                        <button
                            class="aatf-gallery-filter-btn"
                            data-filter="<?php echo esc_attr((string)$term->slug); ?>"
                            role="tab"
                            aria-selected="false"
                            id="aatf-filter-<?php echo esc_attr((string)$term->slug); ?>"
                        >
                            <?php echo esc_html($term->name); ?>
                            <span style="background:rgba(0,0,0,0.08);border-radius:9999px;padding:1px 7px;font-size:0.72rem;">
                                <?php echo esc_html((string)$term->count); ?>
                            </span>
                        </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- ---- Album Cards ---- -->
                <div class="aatf-album-grid" id="aatf-album-grid">
                <?php foreach ($all_albums as $album) :
                    $album_id    = (int) $album->ID;
                    $title       = get_the_title($album_id);
                    $description = (string) get_post_meta($album_id, 'aatf_gallery_description', true);
                    $image_ids   = get_post_meta($album_id, 'aatf_gallery_image_ids', true);
                    $image_ids   = is_array($image_ids) ? array_values(array_filter(array_map('absint', $image_ids))) : array();
                    $count       = count($image_ids);

                    // Cover = featured image → fallback first gallery image
                    $cover_url = '';
                    if (has_post_thumbnail($album_id)) {
                        $cover_url = get_the_post_thumbnail_url($album_id, 'large');
                    } elseif (!empty($image_ids)) {
                        $cover_url = wp_get_attachment_image_url($image_ids[0], 'large');
                    }
                    $cover_url = $cover_url ? $cover_url : '';

                    // Category slugs for JS filter
                    $cat_terms  = get_the_terms($album_id, 'gallery_category');
                    $cat_slugs  = array();
                    if ($cat_terms && !is_wp_error($cat_terms)) {
                        foreach ($cat_terms as $ct) {
                            $cat_slugs[] = $ct->slug;
                        }
                    }
                    $data_cats = esc_attr(implode(' ', $cat_slugs));

                    // Build JSON payload for lightbox
                    $lightbox_images = array();
                    foreach ($image_ids as $img_id) {
                        $full   = wp_get_attachment_image_url($img_id, 'full');
                        $large  = wp_get_attachment_image_url($img_id, 'large');
                        $thumb  = wp_get_attachment_image_url($img_id, 'medium');
                        $alt    = get_post_meta($img_id, '_wp_attachment_image_alt', true);
                        if ($large) {
                            $lightbox_images[] = array(
                                'full'  => $full  ?: $large,
                                'large' => $large,
                                'thumb' => $thumb ?: $large,
                                'alt'   => $alt   ?: $title,
                            );
                        }
                    }
                    $lightbox_json = wp_json_encode($lightbox_images);
                ?>
                    <div
                        class="aatf-album-card"
                        data-cats="<?php echo $data_cats; ?>"
                        data-album-id="<?php echo esc_attr((string)$album_id); ?>"
                        data-title="<?php echo esc_attr($title); ?>"
                        data-desc="<?php echo esc_attr($description); ?>"
                        data-images="<?php echo esc_attr((string)$lightbox_json); ?>"
                        role="button"
                        tabindex="0"
                        aria-label="<?php printf(esc_attr__('Open album: %s', 'aatf-expedition-base'), esc_attr($title)); ?>"
                    >
                        <?php if ($cover_url) : ?>
                            <img class="aatf-album-card__cover" src="<?php echo esc_url($cover_url); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
                        <?php else : ?>
                            <div class="aatf-album-card__placeholder">
                                <svg width="48" height="48" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                                </svg>
                            </div>
                        <?php endif; ?>

                        <!-- Hover open hint -->
                        <div class="aatf-album-card__open-hint" aria-hidden="true">
                            <svg width="18" height="18" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803z"/></svg>
                        </div>

                        <!-- Info overlay -->
                        <div class="aatf-album-card__info">
                            <?php if (!empty($cat_slugs) && !empty($terms)) :
                                $term_names = array();
                                foreach ($terms as $t) {
                                    if (in_array($t->slug, $cat_slugs, true)) {
                                        $term_names[] = $t->name;
                                    }
                                }
                            ?>
                            <div class="aatf-album-card__meta" style="margin-bottom:6px;">
                                <span style="color:var(--brand-orange,#f5740a);font-weight:600;font-size:0.75rem;letter-spacing:0.06em;text-transform:uppercase;">
                                    <?php echo esc_html(implode(', ', $term_names)); ?>
                                </span>
                            </div>
                            <?php endif; ?>

                            <h3 class="aatf-album-card__title"><?php echo esc_html($title); ?></h3>
                            <div class="aatf-album-card__meta">
                                <?php if ($count > 0) : ?>
                                <span class="aatf-album-card__count-badge">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                                    <?php printf(_n('%d Photo', '%d Photos', $count, 'aatf-expedition-base'), $count); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div><!-- .aatf-album-grid -->

            <?php endif; // end if albums exist ?>

        </div><!-- .max-w-7xl -->
    </section>

</div><!-- .aatf-gallery-page -->


<!-- =====================================================================
     LIGHTBOX OVERLAY (masonry grid)
====================================================================== -->
<div id="aatf-lightbox" class="aatf-lightbox-overlay" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Gallery Lightbox', 'aatf-expedition-base'); ?>">
    <div class="aatf-lightbox-header">
        <div>
            <div class="aatf-lightbox-title" id="aatf-lightbox-title"></div>
            <div class="aatf-lightbox-subtitle" id="aatf-lightbox-subtitle"></div>
        </div>
        <button class="aatf-lightbox-close" id="aatf-lightbox-close" aria-label="<?php esc_attr_e('Close', 'aatf-expedition-base'); ?>">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <div class="aatf-lightbox-body">
        <div class="aatf-lightbox-masonry" id="aatf-lightbox-masonry"></div>
    </div>
</div>

<!-- Single photo viewer -->
<div id="aatf-photo-viewer" class="aatf-photo-viewer" role="dialog" aria-modal="true">
    <img class="aatf-photo-viewer__img" id="aatf-photo-viewer-img" src="" alt="">
    <button class="aatf-photo-viewer__close" id="aatf-photo-viewer-close" aria-label="<?php esc_attr_e('Close photo', 'aatf-expedition-base'); ?>">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
    <button class="aatf-photo-viewer__nav aatf-photo-viewer__prev" id="aatf-photo-viewer-prev" aria-label="<?php esc_attr_e('Previous photo', 'aatf-expedition-base'); ?>">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </button>
    <button class="aatf-photo-viewer__nav aatf-photo-viewer__next" id="aatf-photo-viewer-next" aria-label="<?php esc_attr_e('Next photo', 'aatf-expedition-base'); ?>">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </button>
    <div class="aatf-photo-viewer__counter" id="aatf-photo-viewer-counter"></div>
</div>


<!-- =====================================================================
     GALLERY JAVASCRIPT
====================================================================== -->
<script>
(function(){
    'use strict';

    /* ---- state ---- */
    var currentImages   = [];   // [{full,large,thumb,alt}]
    var currentIndex    = 0;

    /* ---- element refs ---- */
    var lightbox        = document.getElementById('aatf-lightbox');
    var lightboxTitle   = document.getElementById('aatf-lightbox-title');
    var lightboxSub     = document.getElementById('aatf-lightbox-subtitle');
    var lightboxClose   = document.getElementById('aatf-lightbox-close');
    var lightboxMasonry = document.getElementById('aatf-lightbox-masonry');

    var viewer          = document.getElementById('aatf-photo-viewer');
    var viewerImg       = document.getElementById('aatf-photo-viewer-img');
    var viewerClose     = document.getElementById('aatf-photo-viewer-close');
    var viewerPrev      = document.getElementById('aatf-photo-viewer-prev');
    var viewerNext      = document.getElementById('aatf-photo-viewer-next');
    var viewerCounter   = document.getElementById('aatf-photo-viewer-counter');

    /* ======================================================
       FILTER PILLS
    ====================================================== */
    var filterBtns = document.querySelectorAll('.aatf-gallery-filter-btn');
    var albumCards = document.querySelectorAll('.aatf-album-card');

    filterBtns.forEach(function(btn){
        btn.addEventListener('click', function(){
            var filter = btn.getAttribute('data-filter');

            filterBtns.forEach(function(b){
                b.classList.remove('is-active');
                b.setAttribute('aria-selected', 'false');
            });
            btn.classList.add('is-active');
            btn.setAttribute('aria-selected', 'true');

            var hasVisible = false;
            albumCards.forEach(function(card){
                if (filter === 'all') {
                    card.style.display = '';
                    hasVisible = true;
                } else {
                    var cats = (card.getAttribute('data-cats') || '').split(' ');
                    if (cats.indexOf(filter) !== -1) {
                        card.style.display = '';
                        hasVisible = true;
                    } else {
                        card.style.display = 'none';
                    }
                }
            });

            // Show empry state if needed
            var emptyMsg = document.getElementById('aatf-filter-empty');
            if (emptyMsg) { emptyMsg.remove(); }
            if (!hasVisible) {
                var grid = document.getElementById('aatf-album-grid');
                if (grid) {
                    var m = document.createElement('div');
                    m.id = 'aatf-filter-empty';
                    m.className = 'aatf-gallery-empty';
                    m.style.gridColumn = '1 / -1';
                    m.textContent = '<?php echo esc_js(__('No albums in this category yet.', 'aatf-expedition-base')); ?>';
                    grid.appendChild(m);
                }
            }
        });
    });

    /* ======================================================
       OPEN LIGHTBOX
    ====================================================== */
    function openLightbox(card) {
        var title  = card.getAttribute('data-title')  || '';
        var desc   = card.getAttribute('data-desc')   || '';
        var raw    = card.getAttribute('data-images')  || '[]';
        var images = [];

        try { images = JSON.parse(raw); } catch(e) {}

        currentImages = images;
        document.body.style.overflow = 'hidden';

        lightboxTitle.textContent = title;
        lightboxSub.textContent   = desc || (images.length + ' <?php echo esc_js(_n('photo', 'photos', 99, 'aatf-expedition-base')); ?>');

        // Build masonry
        lightboxMasonry.innerHTML = '';
        images.forEach(function(img, idx){
            var item = document.createElement('div');
            item.className = 'aatf-lightbox-item';
            item.setAttribute('data-index', idx);

            var el = document.createElement('img');
            el.src     = img.large;
            el.alt     = img.alt || title;
            el.loading = 'lazy';

            item.appendChild(el);
            lightboxMasonry.appendChild(item);

            item.addEventListener('click', function(){ openPhotoViewer(idx); });
        });

        if (images.length === 0) {
            lightboxMasonry.innerHTML = '<p style="color:rgba(255,255,255,0.4);text-align:center;padding:40px"><?php echo esc_js(__('No photos in this album yet.', 'aatf-expedition-base')); ?></p>';
        }

        lightbox.classList.add('is-open');
        lightboxClose.focus();
    }

    function closeLightbox() {
        lightbox.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    albumCards.forEach(function(card){
        card.addEventListener('click', function(){ openLightbox(card); });
        card.addEventListener('keydown', function(e){
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openLightbox(card);
            }
        });
    });

    lightboxClose.addEventListener('click', closeLightbox);

    lightbox.addEventListener('click', function(e){
        if (e.target === lightbox) { closeLightbox(); }
    });

    /* ======================================================
       SINGLE PHOTO VIEWER
    ====================================================== */
    function openPhotoViewer(index) {
        if (!currentImages.length) { return; }
        currentIndex = ((index % currentImages.length) + currentImages.length) % currentImages.length;

        var img = currentImages[currentIndex];
        viewerImg.src = img.full || img.large;
        viewerImg.alt = img.alt;
        viewerCounter.textContent = (currentIndex + 1) + ' / ' + currentImages.length;

        viewer.classList.add('is-open');
        viewerClose.focus();
    }

    function closePhotoViewer() {
        viewer.classList.remove('is-open');
        viewerImg.src = '';
    }

    function prevPhoto() { openPhotoViewer(currentIndex - 1); }
    function nextPhoto() { openPhotoViewer(currentIndex + 1); }

    viewerClose.addEventListener('click', closePhotoViewer);
    viewerPrev.addEventListener('click',  prevPhoto);
    viewerNext.addEventListener('click',  nextPhoto);

    viewer.addEventListener('click', function(e){
        if (e.target === viewer) { closePhotoViewer(); }
    });

    /* ======================================================
       KEYBOARD NAVIGATION
    ====================================================== */
    document.addEventListener('keydown', function(e){
        if (viewer.classList.contains('is-open')) {
            if (e.key === 'ArrowLeft')  { e.preventDefault(); prevPhoto(); }
            if (e.key === 'ArrowRight') { e.preventDefault(); nextPhoto(); }
            if (e.key === 'Escape')     { closePhotoViewer(); }
            return;
        }
        if (lightbox.classList.contains('is-open')) {
            if (e.key === 'Escape') { closeLightbox(); }
        }
    });

}());
</script>
