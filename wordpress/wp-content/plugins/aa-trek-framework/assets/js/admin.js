(function ($) {
    'use strict';

    function initItineraryRepeater() {
        var $wrap = $('#aatf-itinerary-rows');

        if (!$wrap.length || !$('#tmpl-aatf-itinerary-row').length) {
            return;
        }

        var template = wp.template('aatf-itinerary-row');

        $('#aatf-add-itinerary-row').on('click', function () {
            var index = $wrap.find('.aatf-itinerary-row').length;
            $wrap.append(template({ index: index }));
        });

        $wrap.on('click', '.aatf-remove-row', function () {
            $(this).closest('.aatf-itinerary-row').remove();
        });
    }

    function renderGalleryPreview(ids) {
        var $preview = $('#aatf-gallery-preview');
        if (!$preview.length) {
            return;
        }

        $preview.empty();

        ids.forEach(function (id) {
            var attachment = wp.media.attachment(id);
            attachment.fetch().then(function () {
                var url = attachment.get('sizes') && attachment.get('sizes').thumbnail
                    ? attachment.get('sizes').thumbnail.url
                    : attachment.get('url');

                if (!url) {
                    return;
                }

                var $item = $('<div />', {
                    class: 'aatf-slider-image-item',
                    css: {
                        position: 'relative',
                        width: '80px',
                        height: '80px'
                    }
                });

                $item.append(
                    $('<img />', {
                        src: url,
                        'data-id': id,
                        css: {
                            width: '80px',
                            height: '80px',
                            objectFit: 'cover',
                            border: '1px solid #ddd'
                        },
                        alt: ''
                    })
                );

                $item.append(
                    $('<button />', {
                        type: 'button',
                        class: 'button-link-delete aatf-remove-slider-image',
                        'data-id': id,
                        text: 'Remove',
                        css: {
                            position: 'absolute',
                            right: '2px',
                            bottom: '2px',
                            background: 'rgba(255,255,255,0.9)',
                            padding: '0 4px',
                            fontSize: '11px'
                        }
                    })
                );

                $preview.append($item);
            });
        });
    }

    function parseGalleryIds(value) {
        if (!value) {
            return [];
        }

        return value
            .split(',')
            .map(function (id) { return parseInt(id, 10); })
            .filter(function (id) { return !Number.isNaN(id) && id > 0; });
    }

    function initGalleryUploader() {
        var $field = $('#trek_gallery_ids');
        var $uploadButton = $('#aatf-gallery-upload');
        var $clearButton = $('#aatf-gallery-clear');
        var $preview = $('#aatf-gallery-preview');

        if (!$field.length || !$uploadButton.length || typeof wp === 'undefined' || !wp.media) {
            return;
        }

        renderGalleryPreview(parseGalleryIds($field.val()));

        var galleryFrame;

        function preloadSelection(frame, ids) {
            var selection = frame.state().get('selection');
            selection.reset();

            ids.forEach(function (id) {
                var attachment = wp.media.attachment(id);
                attachment.fetch();
                selection.add(attachment);
            });
        }

        $uploadButton.on('click', function (event) {
            event.preventDefault();

            if (galleryFrame) {
                preloadSelection(galleryFrame, parseGalleryIds($field.val()));
                galleryFrame.open();
                return;
            }

            galleryFrame = wp.media({
                title: 'Select Trek Slider Images',
                button: { text: 'Use Images' },
                multiple: true,
                library: { type: 'image' }
            });

            galleryFrame.on('select', function () {
                var selection = galleryFrame.state().get('selection');
                var ids = [];

                selection.each(function (attachment) {
                    ids.push(attachment.get('id'));
                });

                $field.val(ids.join(','));
                renderGalleryPreview(ids);
            });

            galleryFrame.open();
        });

        $clearButton.on('click', function (event) {
            event.preventDefault();
            $field.val('');
            renderGalleryPreview([]);
        });

        $preview.on('click', '.aatf-remove-slider-image', function (event) {
            event.preventDefault();
            var removeId = parseInt($(this).attr('data-id'), 10);
            var ids = parseGalleryIds($field.val()).filter(function (id) {
                return id !== removeId;
            });

            $field.val(ids.join(','));
            renderGalleryPreview(ids);
        });
    }

    function renderEquipmentImagePreview($row, imageId, imageUrl) {
        var $preview = $row.find('.aatf-equipment-image-preview');
        var $field = $row.find('.aatf-equipment-image-id');

        $field.val(imageId > 0 ? imageId : '');
        $preview.empty();

        if (imageId > 0 && imageUrl) {
            $preview.append($('<img />', { src: imageUrl, alt: '' }));
        }
    }

    function initEquipmentRepeater() {
        var $wrap = $('#aatf-equipment-sections');
        var $addButton = $('#aatf-add-equipment-row');

        if (!$wrap.length || !$addButton.length || !$('#tmpl-aatf-equipment-row').length) {
            return;
        }

        var template = wp.template('aatf-equipment-row');
        var imageFrame;

        $addButton.on('click', function () {
            var index = $wrap.find('.aatf-equipment-row').length;
            $wrap.append(template({ index: index }));
        });

        $wrap.on('click', '.aatf-remove-equipment-row', function () {
            $(this).closest('.aatf-equipment-row').remove();
        });

        $wrap.on('click', '.aatf-clear-equipment-image', function (event) {
            event.preventDefault();
            renderEquipmentImagePreview($(this).closest('.aatf-equipment-row'), 0, '');
        });

        $wrap.on('click', '.aatf-select-equipment-image', function (event) {
            event.preventDefault();

            var $row = $(this).closest('.aatf-equipment-row');

            if (!imageFrame) {
                imageFrame = wp.media({
                    title: 'Select Equipment Section Image',
                    button: { text: 'Use Image' },
                    multiple: false,
                    library: { type: 'image' }
                });
            }

            imageFrame.off('select').on('select', function () {
                var attachment = imageFrame.state().get('selection').first();
                if (!attachment) {
                    return;
                }

                var imageId = attachment.get('id');
                var sizes = attachment.get('sizes');
                var imageUrl = sizes && sizes.thumbnail ? sizes.thumbnail.url : attachment.get('url');
                renderEquipmentImagePreview($row, imageId, imageUrl);
            });

            imageFrame.open();
        });
    }

    function initAltitudeProfileRepeater() {
        var $wrap = $('#aatf-altitude-profile-rows');
        var $addButton = $('#aatf-add-altitude-profile-row');

        if (!$wrap.length || !$addButton.length || !$('#tmpl-aatf-altitude-profile-row').length) {
            return;
        }

        var template = wp.template('aatf-altitude-profile-row');

        $addButton.on('click', function () {
            var index = $wrap.find('.aatf-altitude-profile-row').length;
            $wrap.append(template({ index: index }));
        });

        $wrap.on('click', '.aatf-remove-altitude-profile-row', function () {
            $(this).closest('.aatf-altitude-profile-row').remove();
        });
    }

    function initGroupPricingRepeater() {
        var $wrap = $('#aatf-group-pricing-rows');
        var $addButton = $('#aatf-add-group-pricing-row');

        if (!$wrap.length || !$addButton.length || !$('#tmpl-aatf-group-pricing-row').length) {
            return;
        }

        var template = wp.template('aatf-group-pricing-row');

        $addButton.on('click', function () {
            var index = $wrap.find('.aatf-group-pricing-row').length;
            $wrap.append(template({ index: index }));
        });

        $wrap.on('click', '.aatf-remove-group-pricing-row', function () {
            $(this).closest('.aatf-group-pricing-row').remove();
        });
    }

    function initFaqTopicBuilder() {
        var $wrap = $('#aatf-faq-topic-groups');
        var $addGroupButton = $('#aatf-add-faq-topic-group');

        if (
            !$wrap.length ||
            !$addGroupButton.length ||
            !$('#tmpl-aatf-faq-topic-group').length ||
            !$('#tmpl-aatf-faq-qa-row').length
        ) {
            return;
        }

        var canUseWpTemplate = typeof wp !== 'undefined' && typeof wp.template === 'function';
        var groupTemplate = canUseWpTemplate ? wp.template('aatf-faq-topic-group') : null;
        var qaTemplate = canUseWpTemplate ? wp.template('aatf-faq-qa-row') : null;

        function getGroupIndexFromName(nameAttr) {
            var match = /aatf_faq_topic_groups\[(\d+)\]/.exec(nameAttr || '');
            return match ? parseInt(match[1], 10) : 0;
        }

        $addGroupButton.on('click', function () {
            var groupIndex = Date.now();
            if (!groupTemplate) {
                return;
            }
            $wrap.append(groupTemplate({ groupIndex: groupIndex }));
        });

        $wrap.on('click', '.aatf-remove-faq-topic-group', function () {
            $(this).closest('.aatf-faq-topic-group').remove();
        });

        $wrap.on('click', '.aatf-add-faq-qa-row', function () {
            var $group = $(this).closest('.aatf-faq-topic-group');
            var nameAttr = $group.find('select[name^="aatf_faq_topic_groups["]').attr('name');
            var groupIndex = getGroupIndexFromName(nameAttr);
            var faqIndex = $group.find('.aatf-faq-qa-row').length;
            if (!qaTemplate) {
                return;
            }
            $group.find('.aatf-faq-qa-rows').append(qaTemplate({ groupIndex: groupIndex, faqIndex: faqIndex }));
        });

        $wrap.on('click', '.aatf-remove-faq-qa-row', function () {
            $(this).closest('.aatf-faq-qa-row').remove();
        });
    }

    $(function () {
        initItineraryRepeater();
        initGalleryUploader();
        initEquipmentRepeater();
        initAltitudeProfileRepeater();
        initGroupPricingRepeater();
        initFaqTopicBuilder();
    });
})(jQuery);
