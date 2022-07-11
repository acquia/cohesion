// CSS/JS ONLY
(function ($, Drupal, drupalSettings) {
    "use strict";

    Drupal.behaviors.jsComponent = {
        attach: function (context) {
            $('.js_component').once().each(function() {
                const content = JSON.parse($(this).attr('data-ssa-custom-component'));

                const template = `
                  <div class="js-component-container">
                    <h2>Js custom component</h2>
                    <label>Heading:</label>
                    <h3 class="heading-text">${content.heading}</h3>
                    <label>Content:</label>
                    <p class="content-text">${content.content}</p>
                  </div>
                `;

                $(this).append(template);
                $(this).find('.heading-text').css('color', content.font_color);
            });
        }
    };
})(jQuery, Drupal, drupalSettings);


