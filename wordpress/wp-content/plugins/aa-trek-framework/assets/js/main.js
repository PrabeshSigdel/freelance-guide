(function () {
    'use strict';

    function setBookingStatus(node, message, type) {
        if (!node) {
            return;
        }

        node.textContent = message || '';
        node.classList.remove('is-success', 'is-error', 'is-loading');

        if (type) {
            node.classList.add(type);
        }
    }

    function parsePricingData(form) {
        var section = form.closest('.aatf-booking');
        if (!section) {
            return {};
        }

        var node = section.querySelector('.aatf-booking-form__pricing-data');
        if (!node) {
            return {};
        }

        try {
            var raw = node.textContent || '{}';
            var parsed = JSON.parse(raw);
            return parsed && typeof parsed === 'object' ? parsed : {};
        } catch (error) {
            return {};
        }
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatMoney(amount) {
        var numeric = Number(amount || 0);
        if (!Number.isFinite(numeric) || numeric <= 0) {
            return 'Price on request';
        }

        var decimals = Math.floor(numeric) === numeric ? 0 : 2;
        return 'USD ' + numeric.toLocaleString(undefined, {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    }

    function findDepartureForTrek(pricingData, trekId, departureId) {
        var departures = pricingData && pricingData.departures ? pricingData.departures : {};
        var trekKey = String(trekId || '');
        var list = departures[trekKey];

        if (!Array.isArray(list) || !departureId) {
            return null;
        }

        for (var i = 0; i < list.length; i += 1) {
            var item = list[i];
            if (item && Number(item.id) === Number(departureId)) {
                return item;
            }
        }

        return null;
    }

    function getGroupPrice(pricingData, trekId, travelers) {
        var groups = pricingData && pricingData.group_pricing ? pricingData.group_pricing : {};
        var trekKey = String(trekId || '');
        var rows = groups[trekKey];

        if (!Array.isArray(rows) || !rows.length) {
            return 0;
        }

        var bestPrice = 0;
        var bestMin = 0;

        for (var i = 0; i < rows.length; i += 1) {
            var row = rows[i];
            if (!row || typeof row !== 'object') {
                continue;
            }

            var minPeople = Number(row.min_people || 0);
            var maxPeople = Number(row.max_people || 0);
            var price = Number(row.price_per_person || 0);

            if (price <= 0 || minPeople <= 0) {
                continue;
            }

            if (travelers < minPeople) {
                continue;
            }

            if (maxPeople > 0 && travelers > maxPeople) {
                continue;
            }

            if (minPeople >= bestMin) {
                bestMin = minPeople;
                bestPrice = price;
            }
        }

        return bestPrice;
    }

    function calculateEstimate(pricingData, trekId, departureId, travelers) {
        var perPerson = 0;
        var source = 'Price on request';

        var departure = findDepartureForTrek(pricingData, trekId, departureId);
        if (departure && Number(departure.price_per_person || 0) > 0) {
            perPerson = Number(departure.price_per_person || 0);
            source = 'Departure price';
        }

        if (perPerson <= 0) {
            var groupPrice = getGroupPrice(pricingData, trekId, travelers);
            if (groupPrice > 0) {
                perPerson = groupPrice;
                source = 'Group discount';
            }
        }

        if (perPerson <= 0) {
            var basePrices = pricingData && pricingData.trek_base_prices ? pricingData.trek_base_prices : {};
            var basePrice = Number(basePrices[String(trekId || '')] || 0);
            if (basePrice > 0) {
                perPerson = basePrice;
                source = 'Base trek price';
            }
        }

        var total = perPerson > 0 ? perPerson * travelers : 0;

        return {
            perPerson: perPerson,
            total: total,
            source: source
        };
    }

    function updateEstimateUI(form, estimate) {
        var perPersonNode = form.querySelector('[data-aatf-estimate-per-person]');
        var totalNode = form.querySelector('[data-aatf-estimate-total]');
        var sourceNode = form.querySelector('[data-aatf-estimate-source]');

        if (perPersonNode) {
            perPersonNode.textContent = formatMoney(estimate.perPerson);
        }
        if (totalNode) {
            totalNode.textContent = formatMoney(estimate.total);
        }
        if (sourceNode) {
            sourceNode.textContent = estimate.source;
        }
    }

    function updateSummaryUI(form, pricingData, trekId, departureId, travelers) {
        var trekNode = form.querySelector('[data-aatf-summary-trek]');
        var departureNode = form.querySelector('[data-aatf-summary-departure]');
        var travelersNode = form.querySelector('[data-aatf-summary-travelers]');
        var trekSelect = form.querySelector('select[name="trek_id"]');
        var departure = findDepartureForTrek(pricingData, trekId, departureId);

        if (trekNode && trekSelect && trekSelect.selectedIndex >= 0) {
            trekNode.textContent = trekSelect.options[trekSelect.selectedIndex].textContent || 'Selected trek';
        }

        if (departureNode) {
            departureNode.textContent = departure && departure.label ? departure.label : 'Any available departure';
        }

        if (travelersNode) {
            travelersNode.textContent = String(travelers) + (travelers === 1 ? ' traveler' : ' travelers');
        }
    }

    function buildTravelerCard(index, details, formUid) {
        var travelerNumber = index + 1;
        var traveler = details && typeof details === 'object' ? details : {};
        var fullName = traveler.full_name ? String(traveler.full_name) : '';
        var email = traveler.email ? String(traveler.email) : '';
        var phone = traveler.phone ? String(traveler.phone) : '';
        var passportNumber = traveler.passport_number ? String(traveler.passport_number) : '';
        var nationality = traveler.nationality ? String(traveler.nationality) : '';
        var age = traveler.age ? String(traveler.age) : '';
        var gender = traveler.gender ? String(traveler.gender) : '';
        var leadCopy = travelerNumber === 1 ? '<span class="aatf-booking-form__traveler-badge">Lead contact</span>' : '';
        var emailRequired = travelerNumber === 1 ? ' required' : '';
        var fieldPrefix = String(formUid || 'aatf-booking') + '-traveler-' + String(travelerNumber);

        return '' +
            '<article class="aatf-booking-form__traveler-card" data-aatf-traveler-card>' +
                '<div class="aatf-booking-form__traveler-head">' +
                    '<h4 class="aatf-booking-form__traveler-title">Traveler ' + String(travelerNumber) + '</h4>' +
                    leadCopy +
                '</div>' +
                '<div class="aatf-booking-form__grid">' +
                    '<p class="aatf-booking-form__field">' +
                        '<label class="aatf-booking-form__label" for="' + fieldPrefix + '-name">Full name</label>' +
                        '<input type="text" id="' + fieldPrefix + '-name" name="travelers_details[' + String(index) + '][full_name]" value="' + escapeHtml(fullName) + '" required />' +
                    '</p>' +
                    '<p class="aatf-booking-form__field">' +
                        '<label class="aatf-booking-form__label" for="' + fieldPrefix + '-email">Email' + (travelerNumber === 1 ? '' : ' <span class="aatf-booking-form__optional">(optional)</span>') + '</label>' +
                        '<input type="email" id="' + fieldPrefix + '-email" name="travelers_details[' + String(index) + '][email]" value="' + escapeHtml(email) + '"' + emailRequired + ' />' +
                    '</p>' +
                    '<p class="aatf-booking-form__field aatf-booking-form__field--span">' +
                        '<label class="aatf-booking-form__label" for="' + fieldPrefix + '-phone">Phone <span class="aatf-booking-form__optional">(optional)</span></label>' +
                        '<input type="text" id="' + fieldPrefix + '-phone" name="travelers_details[' + String(index) + '][phone]" value="' + escapeHtml(phone) + '" />' +
                    '</p>' +
                    '<p class="aatf-booking-form__field">' +
                        '<label class="aatf-booking-form__label" for="' + fieldPrefix + '-passport">Passport number <span class="aatf-booking-form__optional">(optional)</span></label>' +
                        '<input type="text" id="' + fieldPrefix + '-passport" name="travelers_details[' + String(index) + '][passport_number]" value="' + escapeHtml(passportNumber) + '" />' +
                    '</p>' +
                    '<p class="aatf-booking-form__field">' +
                        '<label class="aatf-booking-form__label" for="' + fieldPrefix + '-nationality">Nationality <span class="aatf-booking-form__optional">(optional)</span></label>' +
                        '<input type="text" id="' + fieldPrefix + '-nationality" name="travelers_details[' + String(index) + '][nationality]" value="' + escapeHtml(nationality) + '" />' +
                    '</p>' +
                    '<p class="aatf-booking-form__field">' +
                        '<label class="aatf-booking-form__label" for="' + fieldPrefix + '-age">Age <span class="aatf-booking-form__optional">(optional)</span></label>' +
                        '<input type="number" id="' + fieldPrefix + '-age" name="travelers_details[' + String(index) + '][age]" min="0" step="1" value="' + escapeHtml(age) + '" />' +
                    '</p>' +
                    '<p class="aatf-booking-form__field">' +
                        '<label class="aatf-booking-form__label" for="' + fieldPrefix + '-gender">Gender <span class="aatf-booking-form__optional">(optional)</span></label>' +
                        '<select id="' + fieldPrefix + '-gender" name="travelers_details[' + String(index) + '][gender]">' +
                            '<option value="">Select gender</option>' +
                            '<option value="Male"' + (gender === 'Male' ? ' selected' : '') + '>Male</option>' +
                            '<option value="Female"' + (gender === 'Female' ? ' selected' : '') + '>Female</option>' +
                            '<option value="Other"' + (gender === 'Other' ? ' selected' : '') + '>Other</option>' +
                            '<option value="Prefer not to say"' + (gender === 'Prefer not to say' ? ' selected' : '') + '>Prefer not to say</option>' +
                        '</select>' +
                    '</p>' +
                '</div>' +
            '</article>';
    }

    function collectTravelerDetails(form) {
        var nameNodes = form.querySelectorAll('input[name^="travelers_details["][name$="[full_name]"]');
        var details = [];

        Array.prototype.forEach.call(nameNodes, function (nameNode) {
            var match = nameNode.name.match(/^travelers_details\[(\d+)\]\[full_name\]$/);
            if (!match) {
                return;
            }

            var index = Number(match[1]);
            details[index] = {
                full_name: nameNode.value || '',
                email: '',
                phone: '',
                passport_number: '',
                nationality: '',
                age: '',
                gender: ''
            };

            var emailNode = form.querySelector('input[name="travelers_details[' + String(index) + '][email]"]');
            var phoneNode = form.querySelector('input[name="travelers_details[' + String(index) + '][phone]"]');
            var passportNode = form.querySelector('input[name="travelers_details[' + String(index) + '][passport_number]"]');
            var nationalityNode = form.querySelector('input[name="travelers_details[' + String(index) + '][nationality]"]');
            var ageNode = form.querySelector('input[name="travelers_details[' + String(index) + '][age]"]');
            var genderNode = form.querySelector('select[name="travelers_details[' + String(index) + '][gender]"]');

            details[index].email = emailNode ? emailNode.value || '' : '';
            details[index].phone = phoneNode ? phoneNode.value || '' : '';
            details[index].passport_number = passportNode ? passportNode.value || '' : '';
            details[index].nationality = nationalityNode ? nationalityNode.value || '' : '';
            details[index].age = ageNode ? ageNode.value || '' : '';
            details[index].gender = genderNode ? genderNode.value || '' : '';
        });

        return details.filter(function (item) {
            return !!item;
        });
    }

    function renderTravelerForms(form, travelers) {
        var list = form.querySelector('[data-aatf-travelers-list]');
        if (!list) {
            return;
        }

        var count = Math.max(1, Number(travelers || 1));
        var current = collectTravelerDetails(form);
        var html = '';
        var i;
        var formUid = form.getAttribute('data-form-uid') || 'aatf-booking';

        for (i = 0; i < count; i += 1) {
            html += buildTravelerCard(i, current[i] || null, formUid);
        }

        list.innerHTML = html;
    }

    function toggleTravelerStep(form, isVisible) {
        var section = form.querySelector('[data-aatf-travelers-section]');
        var continueButton = form.querySelector('[data-aatf-continue-button]');
        var footer = form.querySelector('.aatf-booking-form__footer');

        if (!section) {
            return;
        }

        section.hidden = !isVisible;

        if (continueButton) {
            continueButton.hidden = isVisible;
        }

        if (footer) {
            footer.hidden = !isVisible;
        }
    }

    function validateSetupStep(form) {
        var departureSelect = form.querySelector('[data-aatf-departure-select]');
        var dateField = form.querySelector('input[name="date"]');
        var travelersInput = form.querySelector('input[name="travelers"]');

        if (travelersInput && Number(travelersInput.value || 0) <= 0) {
            travelersInput.value = '1';
        }

        if (departureSelect && typeof departureSelect.reportValidity === 'function' && !departureSelect.reportValidity()) {
            return false;
        }

        if (dateField && typeof dateField.reportValidity === 'function' && !dateField.reportValidity()) {
            return false;
        }

        if (travelersInput && typeof travelersInput.reportValidity === 'function' && !travelersInput.reportValidity()) {
            return false;
        }

        return true;
    }

    function getGroupRangeLabel(row) {
        var minPeople = Number(row && row.min_people ? row.min_people : 0);
        var maxPeople = Number(row && row.max_people ? row.max_people : 0);

        if (minPeople > 0 && maxPeople > 0 && minPeople !== maxPeople) {
            return String(minPeople) + ' - ' + String(maxPeople);
        }

        if (minPeople > 0 && maxPeople > 0) {
            return String(minPeople);
        }

        if (minPeople > 0) {
            return String(minPeople) + '+';
        }

        if (maxPeople > 0) {
            return 'Up to ' + String(maxPeople);
        }

        return 'Any size';
    }

    function renderGroupDiscount(form, pricingData, trekId) {
        var wrap = form.querySelector('[data-aatf-group-discount-wrap]');
        var body = form.querySelector('[data-aatf-group-discount-body]');

        if (!wrap || !body) {
            return;
        }

        var groups = pricingData && pricingData.group_pricing ? pricingData.group_pricing : {};
        var rows = groups[String(trekId || '')];
        rows = Array.isArray(rows) ? rows : [];

        var html = '';
        for (var i = 0; i < rows.length; i += 1) {
            var row = rows[i];
            if (!row || typeof row !== 'object') {
                continue;
            }

            var rangeLabel = getGroupRangeLabel(row);
            var priceLabel = formatMoney(Number(row.price_per_person || 0));
            html += '<tr><td>' + escapeHtml(rangeLabel) + '</td><td>' + escapeHtml(priceLabel) + '</td></tr>';
        }

        if (html === '') {
            body.innerHTML = '';
            wrap.style.display = 'none';
            return;
        }

        body.innerHTML = html;
        wrap.style.display = 'block';
    }

    function rebuildDepartureOptions(form, pricingData, trekId, preferredDepartureId) {
        var departureSelect = form.querySelector('[data-aatf-departure-select]');
        if (!departureSelect) {
            return 0;
        }

        var departures = pricingData && pricingData.departures ? pricingData.departures : {};
        var list = departures[String(trekId || '')];
        list = Array.isArray(list) ? list : [];

        var optionsHtml = '<option value="">Select a departure date</option>';
        for (var i = 0; i < list.length; i += 1) {
            var item = list[i];
            if (!item || typeof item !== 'object') {
                continue;
            }

            var id = Number(item.id || 0);
            var label = String(item.label || 'Departure');
            optionsHtml += '<option value="' + String(id) + '">' + label.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</option>';
        }

        departureSelect.innerHTML = optionsHtml;

        var selected = 0;
        var target = Number(preferredDepartureId || 0);
        if (target > 0) {
            for (var j = 0; j < list.length; j += 1) {
                if (Number(list[j].id || 0) === target) {
                    selected = target;
                    break;
                }
            }
        }

        departureSelect.value = selected > 0 ? String(selected) : '';
        return selected;
    }

    function applyDepartureDate(form, pricingData, trekId, departureId) {
        var dateField = form.querySelector('input[name="date"]');
        if (!dateField) {
            return;
        }

        var departure = findDepartureForTrek(pricingData, trekId, departureId);
        if (departure && typeof departure.start_date === 'string' && departure.start_date !== '') {
            dateField.value = departure.start_date;
        } else if (!departureId) {
            dateField.value = '';
        }
    }

    function initBookingForms() {
        var forms = document.querySelectorAll('[data-aatf-booking-form]');
        if (!forms.length) {
            return;
        }

        Array.prototype.forEach.call(forms, function (form) {
            var pricingData = parsePricingData(form);
            var statusNode = form.querySelector('[data-aatf-booking-status]');
            var submitButton = form.querySelector('button[type="submit"]');
            var continueButton = form.querySelector('[data-aatf-continue-button]');
            var backButton = form.querySelector('[data-aatf-back-button]');
            var trekSelect = form.querySelector('select[name="trek_id"]');
            var departureSelect = form.querySelector('[data-aatf-departure-select]');
            var travelersInput = form.querySelector('input[name="travelers"]');

            if (!trekSelect || !travelersInput) {
                return;
            }

            var selectedTrek = Number(trekSelect.value || form.getAttribute('data-initial-trek') || 0);
            var selectedDeparture = Number(form.getAttribute('data-initial-departure') || (departureSelect ? departureSelect.value : 0) || 0);
            selectedDeparture = rebuildDepartureOptions(form, pricingData, selectedTrek, selectedDeparture);
            applyDepartureDate(form, pricingData, selectedTrek, selectedDeparture);
            renderGroupDiscount(form, pricingData, selectedTrek);
            renderTravelerForms(form, Number(travelersInput.value || 1));
            toggleTravelerStep(form, false);

            function refreshEstimate() {
                var trekId = Number(trekSelect.value || 0);
                var departureId = departureSelect ? Number(departureSelect.value || 0) : 0;
                var travelers = Math.max(1, Number(travelersInput.value || 1));
                var estimate = calculateEstimate(pricingData, trekId, departureId, travelers);
                updateEstimateUI(form, estimate);
                updateSummaryUI(form, pricingData, trekId, departureId, travelers);
            }

            trekSelect.addEventListener('change', function () {
                var trekId = Number(trekSelect.value || 0);
                selectedDeparture = rebuildDepartureOptions(form, pricingData, trekId, 0);
                renderGroupDiscount(form, pricingData, trekId);
                toggleTravelerStep(form, false);
                refreshEstimate();
            });

            if (departureSelect) {
                departureSelect.addEventListener('change', function () {
                    var trekId = Number(trekSelect.value || 0);
                    var departureId = Number(departureSelect.value || 0);
                    applyDepartureDate(form, pricingData, trekId, departureId);
                    refreshEstimate();
                });
            }

            travelersInput.addEventListener('input', function () {
                if (Number(travelersInput.value || 0) <= 0) {
                    travelersInput.value = '1';
                }
                renderTravelerForms(form, Number(travelersInput.value || 1));
                refreshEstimate();
            });

            if (continueButton) {
                continueButton.addEventListener('click', function () {
                    if (!validateSetupStep(form)) {
                        return;
                    }

                    renderTravelerForms(form, Number(travelersInput.value || 1));
                    toggleTravelerStep(form, true);
                    setBookingStatus(statusNode, '', '');

                    var firstField = form.querySelector('[data-aatf-travelers-list] input[name="travelers_details[0][full_name]"]');
                    if (firstField) {
                        firstField.focus();
                    }
                });
            }

            if (backButton) {
                backButton.addEventListener('click', function () {
                    toggleTravelerStep(form, false);
                    if (continueButton) {
                        continueButton.focus();
                    }
                });
            }

            refreshEstimate();

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                var travelerSection = form.querySelector('[data-aatf-travelers-section]');

                if (!validateSetupStep(form)) {
                    toggleTravelerStep(form, false);
                    return;
                }

                if (travelerSection && travelerSection.hidden) {
                    renderTravelerForms(form, Number(travelersInput.value || 1));
                    toggleTravelerStep(form, true);

                    var leadField = form.querySelector('[data-aatf-travelers-list] input[name="travelers_details[0][full_name]"]');
                    if (leadField) {
                        leadField.focus();
                    }
                    return;
                }

                var formData = new FormData(form);
                if (!formData.get('action')) {
                    formData.append('action', 'aatf_submit_booking');
                }

                var endpoint = form.getAttribute('action') || '/wp-admin/admin-ajax.php';
                var lockedTrek = trekSelect ? trekSelect.value : '';
                var lockedDeparture = departureSelect ? departureSelect.value : '0';
                var lockedTravelers = travelersInput ? travelersInput.value : '1';

                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.classList.add('is-loading');
                }
                setBookingStatus(statusNode, 'Sending your booking request...', 'is-loading');

                fetch(endpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Network response was not OK.');
                        }
                        return response.json();
                    })
                    .then(function (data) {
                        var message = data && data.data && data.data.message ? data.data.message : '';
                        if (data && data.success) {
                            setBookingStatus(statusNode, message || 'Booking request sent successfully.', 'is-success');
                            form.reset();

                            if (trekSelect && lockedTrek !== '') {
                                trekSelect.value = lockedTrek;
                            }

                            selectedDeparture = rebuildDepartureOptions(form, pricingData, Number(lockedTrek || 0), Number(lockedDeparture || 0));
                            renderGroupDiscount(form, pricingData, Number(lockedTrek || 0));

                            if (departureSelect) {
                                departureSelect.value = String(selectedDeparture);
                            }

                            if (travelersInput) {
                                travelersInput.value = String(Math.max(1, Number(lockedTravelers || 1)));
                            }

                            renderTravelerForms(form, Number(lockedTravelers || 1));
                            toggleTravelerStep(form, false);

                            if (data.data) {
                                updateEstimateUI(form, {
                                    perPerson: Number(data.data.price_per_person || 0),
                                    total: Number(data.data.total_price || 0),
                                    source: String(data.data.price_source_label || 'Price on request')
                                });
                                updateSummaryUI(form, pricingData, Number(lockedTrek || 0), Number(lockedDeparture || 0), Math.max(1, Number(lockedTravelers || 1)));
                            } else {
                                refreshEstimate();
                            }
                        } else {
                            setBookingStatus(statusNode, message || 'Failed to send booking request.', 'is-error');
                        }
                    })
                    .catch(function () {
                        setBookingStatus(statusNode, 'Something went wrong. Please try again.', 'is-error');
                    })
                    .finally(function () {
                        if (submitButton) {
                            submitButton.disabled = false;
                            submitButton.classList.remove('is-loading');
                        }
                    });
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initBookingForms();
    });
})();
