// CSS/JS ONLY
(function ($, Drupal, once) {
    "use strict";

    Drupal.behaviors.jsComponent = {
        attach: function attach(context) {
            once('jsComponent', '.js_component', context).forEach(function (component) {
                const content = JSON.parse($(component).attr('data-ssa-custom-component'));

                const template = `
                  <div class="js-component-container">
                    <h2>Js custom component</h2>
                    <label>Heading:</label>
                    <h3 class="heading-text">${content.heading}</h3>
                    <label>Content:</label>
                    <p class="content-text">${content.content}</p>
                  </div>
                `;

                $(component).append(template);
                $(component).find('.heading-text').css('color', content.font_color);
            });
        }
    };
})(jQuery, Drupal, once);


