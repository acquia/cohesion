
(function ($, Drupal, once, drupalSettings) {
    Drupal.behaviors.machineName = {
        attach: function attach(context, settings) {
            var self = this;
            var $context = $(context);
            var timeout = null;
            var xhr = null;

            function clickEditHandler(e) {
                var data = e.data;
                data.$wrapper.removeClass('visually-hidden');
                data.$target.trigger('focus');
                data.$suffix.hide();
                data.$source.off('.machineName');
            }

            function machineNameHandler(e) {
                var data = e.data;
                var options = data.options;
                var baseValue = $(e.target).val();

                var rx = new RegExp(options.replace_pattern, 'g');

                if (xhr && xhr.readystate !== 4) {
                    xhr.abort();
                    xhr = null;
                }

                if (timeout) {
                    clearTimeout(timeout);
                    timeout = null;
                }

                timeout = setTimeout(function () {
                    xhr = self.transliterate(baseValue, options).done(function (machine) {
                        self.showMachineName(machine, data);
                    });
                }, 300);
            }

            Object.keys(settings.machineName).forEach(function (sourceId) {
                var machine = '';
                var options = settings.machineName[sourceId];

                var $source = $(once('machine-name', sourceId, context)).addClass('machine-name-source');
                var $target = $context.find(options.target).addClass('machine-name-target');
                var $suffix = $context.find(options.suffix);
                var $wrapper = $target.closest('.js-form-item');

                if (!$source.length || !$target.length || !$suffix.length || !$wrapper.length) {
                    return;
                }

                if ($target.hasClass('error')) {
                    return;
                }

                options.maxlength = $target.attr('maxlength');

                $wrapper.addClass('visually-hidden');

                if ($target.is(':disabled') || $target.val() !== '') {
                    machine = $target.val();
                } else if ($source.val() !== '') {
                    machine = self.transliterate($source.val(), options);
                }

                var $preview = $('<span class="machine-name-value">' + options.field_prefix + Drupal.checkPlain(machine) + options.field_suffix + '</span>');
                $suffix.empty();
                if (options.label) {
                    $suffix.append('<span class="machine-name-label">' + options.label + ': </span>');
                }
                $suffix.append($preview);

                if ($target.is(':disabled')) {
                    return;
                }

                var eventData = {
                    $source: $source,
                    $target: $target,
                    $suffix: $suffix,
                    $wrapper: $wrapper,
                    $preview: $preview,
                    options: options
                };

                var $link = $('<span class="admin-link"><button type="button" class="link">' + Drupal.t('Edit') + '</button></span>').on('click', eventData, clickEditHandler);
                $suffix.append($link);

                if ($target.val() === '') {
                    $source.on('formUpdated.machineName', eventData, machineNameHandler).trigger('formUpdated.machineName');
                }

                $target.on('invalid', eventData, clickEditHandler);
            });
        },
        showMachineName: function showMachineName(machine, data) {
            var settings = data.options;

            if (machine !== '') {
                if (machine !== settings.replace) {
                    data.$target.val(machine);
                    data.$preview.html(settings.field_prefix + Drupal.checkPlain(machine) + settings.field_suffix);
                }
                data.$suffix.show();
            } else {
                data.$suffix.hide();
                data.$target.val(machine);
                data.$preview.empty();
            }
        },
        transliterate: function transliterate(source, settings) {
            // AJAX request to get a unique, transliterated machine name.
            return $.get(Drupal.url('ajax_machine_name/transliterate'), {
                text: source,
                langcode: drupalSettings.langcode,
                replace_pattern: settings.replace_pattern,
                replace_token: settings.replace_token,
                replace: settings.replace,
                lowercase: true,
                field_prefix: settings.field_prefix,
                entity_type_id: settings.entity_type_id,
                entity_id: settings.entity_id,
                maxlength: settings.maxlength,
            });
        }
    };
})(jQuery, Drupal, once, drupalSettings);
