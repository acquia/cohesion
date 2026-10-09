// CSS/JS ONLY
(function ($, Drupal, once) {
    "use strict";

    Drupal.behaviors.jsComponentWithSelect = {
        attach: function attach(context) {
            once('jsComponentWithSelect', '.js_component_with_select', context).forEach(function (component) {
                const content = JSON.parse($(component).attr('data-ssa-custom-component'));

                const template = `
                  <div class="js-component-with-select-container">
                    <h2>Js custom component with select</h2>
                    <label>Heading:</label>
                    <h3 class="heading-text">${content.heading}</h3>
                    <label>Content:</label>
                    <p class="content-text">${content.content}</p>
                    <label>Custom select:</label>
                    <p class="custom-select">${content.custom_select_eu_countries}</p>
                  </div>
                `;

                $(component).append(template);
                $(component).find('.heading-text').css('color', content.font_color);
            });
        }
    };
})(jQuery, Drupal, once);


