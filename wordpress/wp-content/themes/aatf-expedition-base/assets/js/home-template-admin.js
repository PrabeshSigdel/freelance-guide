(function ($) {
    function getConfig() {
        return window.aatfHomeTemplateAdmin || {};
    }

    function isHomeTemplateSelected(config) {
        var $tpl = $('#page_template');
        if (!$tpl.length || !config.templateSlug) {
            return false;
        }
        var selected = String($tpl.val() || '').replace(/\\/g, '/');
        var slug = String(config.templateSlug || '').replace(/\\/g, '/');

        if (selected === slug) {
            return true;
        }

        var selectedBase = selected.split('/').pop();
        var slugBase = slug.split('/').pop();
        return selectedBase !== '' && selectedBase === slugBase;
    }

    function toggleMetabox(config) {
        if (!config.metaboxId) {
            return;
        }

        var $box = $('#' + config.metaboxId).closest('.postbox');
        if (!$box.length) {
            return;
        }

        $box.toggle(isHomeTemplateSelected(config));
    }

    function nextIndex($items) {
        var max = -1;
        $items.find('[data-index]').each(function () {
            var idx = parseInt($(this).attr('data-index'), 10);
            if (!Number.isNaN(idx) && idx > max) {
                max = idx;
            }
        });
        return max + 1;
    }

    function openIconPicker(config, $row) {
        var frame = wp.media({
            title: config.mediaTitle || 'Select icon',
            button: { text: config.mediaButton || 'Use this icon' },
            library: { type: 'image' },
            multiple: false
        });

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            var id = attachment && attachment.id ? parseInt(attachment.id, 10) : 0;
            var url = '';

            if (attachment && attachment.sizes && attachment.sizes.thumbnail) {
                url = attachment.sizes.thumbnail.url;
            } else if (attachment && attachment.url) {
                url = attachment.url;
            }

            $row.find('[data-role="icon-id"]').val(id > 0 ? id : 0);
            $row.find('[data-role="icon-preview"]').html(url ? '<img class="aatf-home-template__icon-img" src="' + url + '" alt="">' : '');
        });

        frame.open();
    }

    $(function () {
        var config = getConfig();

        toggleMetabox(config);
        $(document).on('change', '#page_template', function () {
            toggleMetabox(config);
        });

        $(document).on('click', '[data-action="add-activity"]', function (e) {
            e.preventDefault();

            var $metabox = $('#' + config.metaboxId);
            if (!$metabox.length) {
                return;
            }

            var $items = $metabox.find('[data-role="items"]');
            var tpl = wp.template('aatf-home-activity-item');
            if (typeof tpl !== 'function') {
                return;
            }

            var index = nextIndex($items);
            $items.append(tpl({ index: index }));
        });

        $(document).on('click', '[data-action="remove-activity"]', function (e) {
            e.preventDefault();
            $(this).closest('.aatf-home-template__item').remove();
        });

        $(document).on('click', '[data-action="select-icon"]', function (e) {
            e.preventDefault();

            if (typeof wp === 'undefined' || !wp.media) {
                return;
            }

            var $row = $(this).closest('.aatf-home-template__item');
            openIconPicker(config, $row);
        });

        $(document).on('click', '[data-action="remove-icon"]', function (e) {
            e.preventDefault();
            var $row = $(this).closest('.aatf-home-template__item');
            $row.find('[data-role="icon-id"]').val('0');
            $row.find('[data-role="icon-preview"]').empty();
        });
    });
})(jQuery);
