(function () {
    var header = document.querySelector('.aatf-site-header');
    if (header) {
        var navToggle = header.querySelector('.aatf-nav-toggle');
        var subToggles = header.querySelectorAll('.aatf-submenu-toggle');

        var onScroll = function () {
            if (window.scrollY > 24) {
                header.classList.add('is-sticky');
            } else {
                header.classList.remove('is-sticky');
            }
        };

        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });

        if (navToggle) {
            navToggle.addEventListener('click', function () {
                var expanded = navToggle.getAttribute('aria-expanded') === 'true';
                navToggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                header.classList.toggle('is-menu-open', !expanded);
            });
        }

        subToggles.forEach(function (toggle) {
            toggle.addEventListener('click', function (event) {
                event.preventDefault();
                var parentItem = toggle.closest('li');
                if (!parentItem) {
                    return;
                }

                var expanded = toggle.getAttribute('aria-expanded') === 'true';
                toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                parentItem.classList.toggle('is-open', !expanded);
            });
        });
    }

    var sliders = document.querySelectorAll('[data-aatf-slider]');
    sliders.forEach(function (slider) {
        var slides = slider.querySelectorAll('.aatf-slider__slide');
        var prevBtn = slider.querySelector('.aatf-slider__btn--prev');
        var nextBtn = slider.querySelector('.aatf-slider__btn--next');
        var current = 0;

        if (!slides.length) {
            return;
        }

        var render = function () {
            slides.forEach(function (slide, index) {
                slide.classList.toggle('is-active', index === current);
            });
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
    });

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
