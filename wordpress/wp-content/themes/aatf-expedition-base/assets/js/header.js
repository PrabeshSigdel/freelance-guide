(function () {
    var navBreakpoint = 900;

    /* ── Sticky scroll for legacy .aatf-site-header (if present) ─── */
    var legacyHeader = document.querySelector('.aatf-site-header');
    if (legacyHeader) {
        var onLegacyScroll = function () {
            legacyHeader.classList.toggle('is-sticky', window.scrollY > 24);
        };
        onLegacyScroll();
        window.addEventListener('scroll', onLegacyScroll, { passive: true });
    }

    /* ── Sticky scroll for .aatf-main-nav ───────────────────────── */
    var mainNav = document.querySelector('.aatf-main-nav');
    if (mainNav) {
        var onNavScroll = function () {
            mainNav.classList.toggle('is-sticky', window.scrollY > 24);
        };
        onNavScroll();
        window.addEventListener('scroll', onNavScroll, { passive: true });
    }

    /* ── Hamburger / Drawer ──────────────────────────────────────── */
    var navToggle = document.getElementById('aatf-nav-toggle');
    var navClose = document.getElementById('aatf-nav-close');
    var navOverlay = document.getElementById('aatf-nav-overlay');
    var navDrawer = document.getElementById('aatf-primary-nav');

    function openDrawer() {
        if (!navDrawer) { return; }
        navDrawer.classList.add('is-open');
        if (navOverlay) { navOverlay.classList.add('is-active'); }
        if (navToggle) {
            navToggle.classList.add('is-active');
            navToggle.setAttribute('aria-expanded', 'true');
        }
        document.body.classList.add('aatf-nav-open');
    }

    function closeDrawer() {
        if (!navDrawer) { return; }
        navDrawer.classList.remove('is-open');
        if (navOverlay) { navOverlay.classList.remove('is-active'); }
        if (navToggle) {
            navToggle.classList.remove('is-active');
            navToggle.setAttribute('aria-expanded', 'false');
        }
        document.body.classList.remove('aatf-nav-open');
    }

    if (navToggle) {
        navToggle.addEventListener('click', function () {
            var isOpen = navDrawer && navDrawer.classList.contains('is-open');
            isOpen ? closeDrawer() : openDrawer();
        });
    }

    if (navClose) { navClose.addEventListener('click', closeDrawer); }
    if (navOverlay) { navOverlay.addEventListener('click', closeDrawer); }

    /* Close on Escape key */
    document.addEventListener('keydown', function (e) {
        if ((e.key === 'Escape' || e.keyCode === 27) && navDrawer && navDrawer.classList.contains('is-open')) {
            closeDrawer();
            if (navToggle) { navToggle.focus(); }
        }
    });

    /* Close drawer only for in-page anchor navigation. Let full page loads
       replace the view without starting a closing animation first. */
    if (navDrawer) {
        navDrawer.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                var href = link.getAttribute('href') || '';
                if (href.charAt(0) === '#') {
                    closeDrawer();
                }
            });
        });
    }

    /* ── Mobile submenu toggles (tap to expand inside drawer) ────── */
    if (navDrawer) {
        var menuItems = navDrawer.querySelectorAll('.aatf-site-header__menu > li.menu-item-has-children');
        menuItems.forEach(function (item) {
            var btn = item.querySelector('.aatf-submenu-toggle');
            if (!btn) { return; }

            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var isOpen = item.classList.contains('is-open');

                /* Close all siblings */
                menuItems.forEach(function (sibling) {
                    if (sibling !== item) {
                        sibling.classList.remove('is-open');
                        var sibBtn = sibling.querySelector('.aatf-submenu-toggle');
                        if (sibBtn) { sibBtn.setAttribute('aria-expanded', 'false'); }
                    }
                });

                item.classList.toggle('is-open', !isOpen);
                btn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            });

            /* Show/hide the toggle button depending on viewport */
            function syncToggleVisibility() {
                var isMobile = window.innerWidth <= navBreakpoint;
                btn.style.display = isMobile ? 'inline-flex' : 'none';
            }
            syncToggleVisibility();
            window.addEventListener('resize', syncToggleVisibility);
        });
    }

    var sliders = document.querySelectorAll('[data-aatf-slider]');
    sliders.forEach(function (slider) {
        var track = slider.querySelector('.aatf-slider__track');
        var slides = slider.querySelectorAll('.aatf-slider__slide');
        var prevBtn = slider.querySelector('.aatf-slider__btn--prev');
        var nextBtn = slider.querySelector('.aatf-slider__btn--next');
        var current = 0;

        if (!track || !slides.length) {
            return;
        }

        var render = function () {
            var offset = current * slider.clientWidth;

            slides.forEach(function (slide, index) {
                slide.classList.toggle('is-active', index === current);
                slide.setAttribute('aria-hidden', index === current ? 'false' : 'true');
            });

            track.style.transform = 'translateX(-' + offset + 'px)';
        };

        if (slides.length <= 1) {
            if (prevBtn) {
                prevBtn.style.display = 'none';
            }
            if (nextBtn) {
                nextBtn.style.display = 'none';
            }
            render();
            return;
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                current = (current - 1 + slides.length) % slides.length;
                render();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                current = (current + 1) % slides.length;
                render();
            });
        }

        render();

        var resizeTimer = null;
        window.addEventListener('resize', function () {
            window.clearTimeout(resizeTimer);
            resizeTimer = window.setTimeout(render, 100);
        });
    });

    function initTourCarousels() {
        var carousels = document.querySelectorAll('[data-tour-carousel]');
        if (!carousels.length) {
            return;
        }

        carousels.forEach(function (carousel) {
            var track = carousel.querySelector('[data-tour-track]');
            var slider = carousel.querySelector('[data-tour-slider]');
            var dotsWrap = carousel.querySelector('[data-tour-dots]');
            var prevBtn = carousel.querySelector('[data-tour-prev]');
            var nextBtn = carousel.querySelector('[data-tour-next]');
            var cards = slider ? Array.prototype.slice.call(slider.querySelectorAll('.tour-card')) : [];
            var currentPage = 0;
            var totalPages = 1;

            if (!track || !slider || cards.length === 0) {
                return;
            }

            function getGap() {
                var styles = window.getComputedStyle(slider);
                var value = parseFloat(styles.columnGap || styles.gap || '0');
                return Number.isFinite(value) ? value : 0;
            }

            function getCardsPerPage() {
                if (!cards.length) {
                    return 1;
                }

                var trackWidth = track.clientWidth;
                var cardWidth = cards[0].getBoundingClientRect().width;
                var gap = getGap();
                var perPage = Math.floor((trackWidth + gap) / Math.max(1, cardWidth + gap));

                return Math.max(1, perPage);
            }

            function getMaxOffset() {
                return Math.max(0, slider.scrollWidth - track.clientWidth);
            }

            function updateDots() {
                if (!dotsWrap) {
                    return;
                }

                dotsWrap.innerHTML = '';
                dotsWrap.style.display = totalPages <= 1 ? 'none' : 'flex';

                for (var index = 0; index < totalPages; index += 1) {
                    var dot = document.createElement('button');
                    var isActive = index === currentPage;

                    dot.type = 'button';
                    dot.className = 'tour-dot rounded-full transition-all duration-300 ' + (isActive ? 'w-3 h-3 bg-[var(--brand-orange)]' : 'w-2.5 h-2.5 bg-[var(--brand-gray)]');
                    dot.setAttribute('aria-label', 'Go to tour slide ' + (index + 1));
                    dot.setAttribute('aria-pressed', isActive ? 'true' : 'false');

                    (function (pageIndex) {
                        dot.addEventListener('click', function () {
                            currentPage = pageIndex;
                            render();
                        });
                    })(index);

                    dotsWrap.appendChild(dot);
                }
            }

            function render() {
                var cardsPerPage = getCardsPerPage();
                var gap = getGap();
                totalPages = Math.max(1, Math.ceil(cards.length / cardsPerPage));

                if (currentPage > totalPages - 1) {
                    currentPage = totalPages - 1;
                }

                var targetIndex = currentPage * cardsPerPage;
                var firstCard = cards[Math.min(targetIndex, cards.length - 1)];
                var firstCardOffset = firstCard ? firstCard.offsetLeft : 0;
                var offset = Math.min(firstCardOffset, getMaxOffset());

                slider.style.transform = 'translateX(-' + offset + 'px)';
                updateDots();

                if (prevBtn) {
                    prevBtn.style.display = totalPages <= 1 ? 'none' : 'flex';
                    prevBtn.disabled = totalPages <= 1;
                }

                if (nextBtn) {
                    nextBtn.style.display = totalPages <= 1 ? 'none' : 'flex';
                    nextBtn.disabled = totalPages <= 1;
                }
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', function () {
                    currentPage = currentPage <= 0 ? totalPages - 1 : currentPage - 1;
                    render();
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', function () {
                    currentPage = currentPage >= totalPages - 1 ? 0 : currentPage + 1;
                    render();
                });
            }

            render();

            var resizeTimer = null;
            window.addEventListener('resize', function () {
                window.clearTimeout(resizeTimer);
                resizeTimer = window.setTimeout(render, 100);
            });
        });
    }

    initTourCarousels();

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatAltitude(value) {
        if (window.Intl && typeof Intl.NumberFormat === 'function') {
            return new Intl.NumberFormat().format(value);
        }

        return String(value);
    }

    function initAltitudeProfiles() {
        var profiles = document.querySelectorAll('[data-aatf-altitude-profile]');
        if (!profiles.length) {
            return;
        }

        profiles.forEach(function (profileEl) {
            var dataEl = profileEl.querySelector('.aatf-altitude-profile__data');
            var chartEl = profileEl.querySelector('[data-altitude-chart]');
            var unitButtons = profileEl.querySelectorAll('.aatf-altitude-profile__unit');
            var scrollWrap = profileEl.querySelector('[data-altitude-scroll]');
            var scrollRange = scrollWrap ? scrollWrap.querySelector('.aatf-altitude-profile__scroll-range') : null;
            var scrollLeftBtn = scrollWrap ? scrollWrap.querySelector('[data-scroll-dir="left"]') : null;
            var scrollRightBtn = scrollWrap ? scrollWrap.querySelector('[data-scroll-dir="right"]') : null;

            if (!dataEl || !chartEl) {
                return;
            }

            var parsed;
            try {
                parsed = JSON.parse(dataEl.textContent || '[]');
            } catch (error) {
                parsed = [];
            }

            if (!Array.isArray(parsed) || !parsed.length) {
                chartEl.innerHTML = '<p>No altitude profile data.</p>';
                return;
            }

            var points = parsed
                .map(function (row, index) {
                    var day = row && row.day ? String(row.day).trim() : ('Point ' + (index + 1));
                    var place = row && row.place ? String(row.place).trim() : '';
                    var altitudeM = row && row.altitude_m ? parseInt(row.altitude_m, 10) : 0;
                    return {
                        day: day,
                        place: place,
                        altitudeM: Number.isFinite(altitudeM) ? Math.max(0, altitudeM) : 0
                    };
                })
                .filter(function (row) {
                    return row.altitudeM > 0;
                });

            if (!points.length) {
                chartEl.innerHTML = '<p>No altitude profile data.</p>';
                return;
            }

            var activeUnit = 'm';
            var controlsBound = false;

            function getScroller() {
                return chartEl.querySelector('.aatf-altitude-profile__scroller');
            }

            function syncScrollRange() {
                if (!scrollWrap || !scrollRange) {
                    return;
                }

                var scroller = getScroller();
                if (!scroller) {
                    scrollWrap.style.display = 'none';
                    return;
                }

                var maxScroll = Math.max(0, scroller.scrollWidth - scroller.clientWidth);
                if (maxScroll <= 0) {
                    scrollWrap.style.display = 'none';
                    scrollRange.value = '0';
                    return;
                }

                scrollWrap.style.display = 'flex';
                scrollRange.max = String(maxScroll);
                scrollRange.value = String(Math.min(maxScroll, Math.max(0, Math.round(scroller.scrollLeft))));
            }

            function toUnit(valueM) {
                if (activeUnit === 'ft') {
                    return Math.round(valueM * 3.28084);
                }
                return valueM;
            }

            function renderProfile() {
                var width = Math.max(780, ((points.length - 1) * 140) + 120);
                var height = 360;
                var padLeft = 34;
                var padRight = 34;
                var padTop = 62;
                var padBottom = 74;
                var plotWidth = width - padLeft - padRight;
                var plotHeight = height - padTop - padBottom;
                var baselineY = height - padBottom;

                var values = points.map(function (point) {
                    return toUnit(point.altitudeM);
                });
                var minValue = Math.min.apply(null, values);
                var maxValue = Math.max.apply(null, values);
                if (minValue === maxValue) {
                    minValue = Math.max(0, minValue - 1);
                    maxValue = maxValue + 1;
                }

                var denominator = (maxValue - minValue) || 1;
                var chartPoints = points.map(function (point, index) {
                    var x = padLeft + ((points.length === 1 ? 0 : (index / (points.length - 1))) * plotWidth);
                    var value = toUnit(point.altitudeM);
                    var y = padTop + ((maxValue - value) / denominator) * plotHeight;
                    return {
                        x: x,
                        y: y,
                        day: point.day,
                        place: point.place,
                        altitude: value
                    };
                });

                var linePath = chartPoints.map(function (point, index) {
                    return (index === 0 ? 'M ' : 'L ') + point.x.toFixed(2) + ' ' + point.y.toFixed(2);
                }).join(' ');
                var areaPath = linePath + ' L ' + chartPoints[chartPoints.length - 1].x.toFixed(2) + ' ' + baselineY.toFixed(2) + ' L ' + chartPoints[0].x.toFixed(2) + ' ' + baselineY.toFixed(2) + ' Z';
                var unitLabel = activeUnit === 'ft' ? 'ft' : 'm';

                var pointMarkup = chartPoints.map(function (point) {
                    var labelY = Math.max(20, point.y - 42);
                    var lines = [];
                    if (point.place !== '') {
                        lines.push('<tspan x="' + point.x.toFixed(2) + '" dy="0">' + escapeHtml(point.place) + '</tspan>');
                    }
                    lines.push('<tspan x="' + point.x.toFixed(2) + '" dy="' + (point.place !== '' ? '17' : '0') + '">' + escapeHtml(formatAltitude(point.altitude) + ' ' + unitLabel) + '</tspan>');

                    return [
                        '<circle cx="' + point.x.toFixed(2) + '" cy="' + point.y.toFixed(2) + '" r="4" class="aatf-altitude-profile__dot"></circle>',
                        '<text x="' + point.x.toFixed(2) + '" y="' + labelY.toFixed(2) + '" text-anchor="middle" class="aatf-altitude-profile__value-label">' + lines.join('') + '</text>',
                        '<text x="' + point.x.toFixed(2) + '" y="' + (baselineY + 28).toFixed(2) + '" text-anchor="middle" class="aatf-altitude-profile__day-label">' + escapeHtml(point.day) + '</text>'
                    ].join('');
                }).join('');

                var svg = '' +
                    '<svg class="aatf-altitude-profile__svg" viewBox="0 0 ' + width + ' ' + height + '" role="img" aria-label="Altitude profile chart">' +
                    '<path d="' + areaPath + '" class="aatf-altitude-profile__area"></path>' +
                    '<path d="' + linePath + '" class="aatf-altitude-profile__line"></path>' +
                    pointMarkup +
                    '</svg>';

                chartEl.innerHTML = '<div class="aatf-altitude-profile__scroller">' + svg + '</div>';
                syncScrollRange();
            }

            function bindScrollControls() {
                if (controlsBound || !scrollWrap || !scrollRange) {
                    return;
                }

                controlsBound = true;

                scrollRange.addEventListener('input', function () {
                    var scroller = getScroller();
                    if (!scroller) {
                        return;
                    }

                    scroller.scrollLeft = parseInt(scrollRange.value, 10) || 0;
                });

                if (scrollLeftBtn) {
                    scrollLeftBtn.addEventListener('click', function () {
                        var scroller = getScroller();
                        if (!scroller) {
                            return;
                        }

                        scroller.scrollBy({ left: -220, behavior: 'smooth' });
                    });
                }

                if (scrollRightBtn) {
                    scrollRightBtn.addEventListener('click', function () {
                        var scroller = getScroller();
                        if (!scroller) {
                            return;
                        }

                        scroller.scrollBy({ left: 220, behavior: 'smooth' });
                    });
                }

                chartEl.addEventListener('scroll', function () {
                    syncScrollRange();
                }, true);

                window.addEventListener('resize', function () {
                    syncScrollRange();
                });
            }

            unitButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    var nextUnit = button.getAttribute('data-unit') === 'ft' ? 'ft' : 'm';
                    if (nextUnit === activeUnit) {
                        return;
                    }

                    activeUnit = nextUnit;
                    unitButtons.forEach(function (btn) {
                        var isActive = btn === button;
                        btn.classList.toggle('is-active', isActive);
                        btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                    });
                    renderProfile();
                });
            });

            renderProfile();
            bindScrollControls();
        });
    }

    initAltitudeProfiles();
})();
