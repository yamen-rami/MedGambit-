(function (window, $) {
    'use strict';

    if (!$ || !$.fn || typeof $.fn.select2 !== 'function') {
        return;
    }

    const normalizeValue = (value, multiple) => {
        if (multiple) {
            return Array.isArray(value) ? value : value ? [value] : [];
        }

        return value || '';
    };

    const getUtils = () => {
        try {
            return $.fn.select2.amd.require('select2/utils');
        } catch (error) {
            return null;
        }
    };

    const getInstance = (select) => {
        const utils = getUtils();
        const instance = utils
            ? utils.GetData(select[0], 'select2')
            : $.data(select[0], 'select2');

        return instance && typeof instance.destroy === 'function' ? instance : null;
    };

    const clearInvalidInstance = (select) => {
        const utils = getUtils();

        if (utils) {
            utils.RemoveData(select[0]);
        }

        select.removeData('select2')
            .removeAttr('data-select2')
            .removeAttr('data-select2-id aria-hidden tabindex')
            .removeClass('select2-hidden-accessible')
            .next('.select2-container')
            .remove();
    };

    const destroy = (select) => {
        const instance = getInstance(select);

        if (instance) {
            instance.destroy();
        } else if (select.hasClass('select2-hidden-accessible') || select.data('select2') != null) {
            clearInvalidInstance(select);
        }
    };

    const init = (selector, options = {}) => {
        const select = $(selector);

        if (!select.length) {
            return false;
        }

        const {
            onChange,
            ajaxUrl,
            resultText = 'name',
            multiple = select.prop('multiple'),
            dropdownParent = select.parent(),
            ...selectOptions
        } = options;

        destroy(select);

        const $dropdownParent = $(dropdownParent);

        if ($dropdownParent.length) {
            $dropdownParent.addClass('medgambit-select2-parent');
        }

        const config = {
            width: '100%',
            allowClear: true,
            dropdownParent: $dropdownParent.length ? $dropdownParent : undefined,
            ...selectOptions,
        };

        if (ajaxUrl) {
            config.ajax = {
                url: ajaxUrl,
                type: 'GET',
                dataType: 'json',
                delay: 250,
                data: (params) => ({
                    search: params.term || '',
                }),
                processResults: (data) => {
                    const results = Array.isArray(data) ? data : (data.data || []);

                    return {
                        results: results.map((item) => ({
                            id: item.id,
                            text: item[resultText] ?? item.name ?? item.text,
                        })),
                    };
                },
            };
        }

        select.select2(config)
            .off('change.medgambitSelect2')
            .on('change.medgambitSelect2', function () {
                if (typeof onChange === 'function') {
                    onChange(normalizeValue($(this).val(), multiple), this);
                }
            });

        return true;
    };

    const destroySelector = (selector) => {
        const select = $(selector);

        destroy(select);
    };

    window.MedGambitSelect2 = {
        init,
        destroy: destroySelector,
        normalizeValue,
    };
})(window, window.jQuery);
