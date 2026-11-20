// CSS/JS ONLY
(function ($, Drupal, once, drupalSettings) {
    "use strict";

    Drupal.behaviors.purejs = {
        attach: function (context) {
            $(once('js-template', '.js_with_template')).each(function() {
                const content = JSON.parse($(this).attr('data-ssa-custom-component'));

                const template = $(this).find(".custom-component")
                $(template).find('.js-title').text(content.input);

                const dropzone = $(this).find('[data-dropzone-id="dropzone_f05eff15_335f_4a16_8cdf_e84c8601e345"]');
                $(template).find('.js-dropzone').html(dropzone.html());
            });
        }
    };
})(jQuery, Drupal, once, drupalSettings);


