<?php if (!defined('ABSPATH')) exit; ?>

<?php
$media_type  = (string) get_theme_mod('aatf_home_hero_media_type', 'none');
$hero_image  = (string) get_theme_mod('aatf_home_hero_image', '');
$hero_title  = (string) get_theme_mod('aatf_home_hero_title', '');
$hero_text   = (string) get_theme_mod('aatf_home_hero_text', '');

if ($hero_title === '') {
    $hero_title = get_bloginfo('name');
}
if ($hero_text === '') {
    $hero_text = get_bloginfo('description');
}
if ($hero_text === '') {
    $hero_text = 'Where Would You Like To Go?';
}

// Resolve background image
$bg_url = 'https://images.unsplash.com/photo-1553856622-d1b352e9a211?w=1600&q=80'; // fallback
if ($media_type === 'image' && $hero_image !== '') {
    if (is_numeric($hero_image)) {
        $src = wp_get_attachment_image_url((int) $hero_image, 'full');
        if ($src) $bg_url = $src;
    } else {
        $bg_url = esc_url($hero_image);
    }
}
?>

<!-- ── Hero ──────────────────────────────────────────────────────── -->
<?php
$explore_page = get_pages(array(
    'meta_key'   => '_wp_page_template',
    'meta_value' => 'page-templates/template-explore.php',
    'number'     => 1,
));
$explore_url = !empty($explore_page) ? get_permalink($explore_page[0]->ID) : home_url('/');
?>
<section class="hero-bg relative min-h-[600px] flex flex-col items-center justify-center text-center"
    style="background-image: url('<?php echo esc_url($bg_url); ?>');">
    <div class="absolute inset-0 bg-black/25"></div>

    <div class="relative z-10 px-6 py-20 w-full">
        <h1 class="font-display text-6xl md:text-7xl font-bold text-white mb-4 drop-shadow-lg tracking-wide">
            <?php echo esc_html($hero_title); ?>
        </h1>
        <p class="text-white/90 text-xl md:text-2xl font-light tracking-widest drop-shadow mb-10">
            <?php echo esc_html($hero_text); ?>
        </p>

        <!-- ── Hero Search Bar ─────────────────────────────────────────── -->
        <div class="flex justify-center px-4">
            <div class="w-full max-w-2xl">
                <form id="heroSearchForm" method="GET" action="<?php echo esc_url($explore_url); ?>" autocomplete="off" class="relative">
                    <input type="hidden" name="post_type" value="trek" />

                    <div class="flex items-center bg-white rounded-full shadow-[0_8px_40px_rgba(0,0,0,0.35)] border border-white/20 overflow-hidden pr-1.5">
                        <!-- Search icon -->
                        <div class="pl-5 pr-3 text-[var(--brand-orange)] shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>

                        <!-- Input -->
                        <input
                            id="heroSearchInput"
                            type="text"
                            name="s"
                            placeholder="<?php esc_attr_e('Search for tours, treks, destinations…', 'aatf-expedition-base'); ?>"
                            class="flex-1 py-4 text-sm font-medium text-[var(--brand-dark)] bg-transparent border-none focus:outline-none focus:ring-0 placeholder:text-gray-400"
                        />

                        <!-- Clear button (hidden by default) -->
                        <button type="button" id="heroSearchClear" class="hidden mr-2 text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <!-- Search button -->
                        <button type="submit" class="bg-[var(--brand-orange)] hover:bg-orange-600 text-white font-bold px-6 py-3 rounded-full transition-colors text-sm tracking-wide whitespace-nowrap shrink-0">
                            <?php esc_html_e('Search', 'aatf-expedition-base'); ?>
                        </button>
                    </div>

                    <!-- Autocomplete Dropdown -->
                    <div id="heroSearchDropdown" class="hidden absolute left-0 right-0 top-full mt-2 bg-white rounded-2xl shadow-[0_16px_50px_rgba(0,0,0,0.15)] border border-gray-100 overflow-hidden z-50">
                        <!-- Loading state -->
                        <div id="heroSearchLoading" class="hidden flex items-center gap-3 px-5 py-4 text-sm text-gray-400">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Searching…
                        </div>
                        <!-- Results list -->
                        <ul id="heroSearchResults" class="divide-y divide-gray-50 max-h-80 overflow-y-auto"></ul>
                        <!-- No results -->
                        <div id="heroSearchEmpty" class="hidden px-5 py-4 text-sm text-gray-400 text-center">
                            No tours found for "<span id="heroSearchEmptyTerm"></span>"
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- ── /Hero Search Bar ────────────────────────────────────────── -->

    </div>
</section>


<script>
(function () {
    var ajaxUrl   = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
    var exploreUrl = '<?php echo esc_js($explore_url); ?>';

    var input      = document.getElementById('heroSearchInput');
    var dropdown   = document.getElementById('heroSearchDropdown');
    var resultsList = document.getElementById('heroSearchResults');
    var loading    = document.getElementById('heroSearchLoading');
    var empty      = document.getElementById('heroSearchEmpty');
    var emptyTerm  = document.getElementById('heroSearchEmptyTerm');
    var clearBtn   = document.getElementById('heroSearchClear');
    var form       = document.getElementById('heroSearchForm');

    var debounceTimer = null;
    var currentXhr    = null;

    function showDropdown() { dropdown.classList.remove('hidden'); }
    function hideDropdown() { dropdown.classList.add('hidden'); }
    function showLoading()  { loading.classList.remove('hidden'); empty.classList.add('hidden'); resultsList.innerHTML = ''; }
    function hideLoading()  { loading.classList.add('hidden'); }

    function renderResults(items, term) {
        resultsList.innerHTML = '';
        if (!items || items.length === 0) {
            emptyTerm.textContent = term;
            empty.classList.remove('hidden');
            return;
        }
        empty.classList.add('hidden');
        items.forEach(function (item) {
            var li = document.createElement('li');
            li.className = 'flex items-center gap-4 px-5 py-3 hover:bg-orange-50 cursor-pointer transition-colors group';
            li.innerHTML =
                (item.thumb
                    ? '<img src="' + item.thumb + '" alt="" class="w-12 h-10 object-cover rounded-lg shrink-0" />'
                    : '<div class="w-12 h-10 bg-gray-100 rounded-lg shrink-0 flex items-center justify-center"><svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></div>') +
                '<div class="flex-1 min-w-0">' +
                    '<p class="text-sm font-semibold text-[#1a1a2e] group-hover:text-[var(--brand-orange)] transition-colors truncate">' + item.title + '</p>' +
                    '<p class="text-xs text-gray-400">Trek</p>' +
                '</div>' +
                '<svg class="w-4 h-4 text-gray-300 group-hover:text-[var(--brand-orange)] transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>';

            li.addEventListener('click', function () {
                window.location.href = item.url;
            });
            resultsList.appendChild(li);
        });
    }

    function doSearch(term) {
        if (currentXhr) { currentXhr.abort(); }
        showLoading();
        showDropdown();

        var xhr = new XMLHttpRequest();
        xhr.open('GET', ajaxUrl + '?action=aatf_trek_search&q=' + encodeURIComponent(term), true);
        xhr.onload = function () {
            hideLoading();
            if (xhr.status === 200) {
                try {
                    var json = JSON.parse(xhr.responseText);
                    if (json.success) {
                        renderResults(json.data, term);
                    }
                } catch (e) {}
            }
        };
        xhr.onerror = function () { hideLoading(); };
        xhr.send();
        currentXhr = xhr;
    }

    input.addEventListener('input', function () {
        var term = input.value.trim();
        clearBtn.classList.toggle('hidden', term === '');

        if (term.length < 2) {
            hideDropdown();
            return;
        }

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () { doSearch(term); }, 280);
    });

    clearBtn.addEventListener('click', function () {
        input.value = '';
        clearBtn.classList.add('hidden');
        hideDropdown();
        input.focus();
    });

    // Hide dropdown when clicking outside
    document.addEventListener('click', function (e) {
        if (!form.contains(e.target)) { hideDropdown(); }
    });

    // Prevent form submission if input is empty
    form.addEventListener('submit', function (e) {
        if (input.value.trim() === '') {
            e.preventDefault();
            window.location.href = exploreUrl;
        }
    });

    // Show dropdown again when re-focusing if there's a value
    input.addEventListener('focus', function () {
        if (input.value.trim().length >= 2 && resultsList.children.length > 0) {
            showDropdown();
        }
    });
})();
</script>
