(function($, Drupal, once, drupalSettings){
  "use strict";

  Drupal.behaviors.DX8ParallaxScrolling = {

    attach: function (context, settings){

      var cmm = new Drupal.CohesionResponsiveBreakpoints();

      var resetCSS = {
        'background': '',
        'transform': '',
        'transition': '',
        'will-change': '',
        'background-position-y': '',
        'background-position-x': ''
      };

      var count = 0;
      var total = 0;

      function initParoller(settings) {

        var key = settings.cohesion.key,
          settings = settings.cohesion.settings,
          $el = settings.$el;

        if(!count) {
          total = $('[data-coh-paroller]', context).length;
          count = total;
        }

        if(count === total) {
          //Ensure that unbind only happens the first time we loop round the elements.
          $(window).off('.paroller');
        }

        count--;

        // Always wipe the CSS when changing between breakpoints
        $el.css(resetCSS);

        if(settings.breakpoints[key].enabled){

          // Init the paroller elements
          $el.paroller({
            factor: settings.breakpoints[key].factor,
            direction: settings.breakpoints[key].direction,
            type: settings.breakpoints[key].type,
            bgstart: settings.breakpoints[key].bgstart
          });
        }
      }

      $(once('dx8-js-parallax-init', '[data-coh-paroller]', context)).each(function(){
        var $el = $(this);
        var responsiveSettings = $el.data('coh-paroller');
        var key, previous;
        var settings = {
          $el: $el,
          breakpoints: {}
        };

        for (var i = 0; i < cmm.breakpoints.length; i++) {

          key = cmm.breakpoints[i].key;

          // Populate all breakpoints regardless of whether the settings are set or not to simulate inheritance
          settings.breakpoints[key] = {};
          if (typeof responsiveSettings[key] !== 'undefined') {

            settings.breakpoints[key] = responsiveSettings[key];

            previous = responsiveSettings[key];

          } else {

            if (typeof cmm.breakpoints[i - 1] !== 'undefined' && typeof previous !== 'undefined') {
              settings.breakpoints[key] = previous;
            }
          }
        }

        cmm.addListeners(settings, initParoller);
      });
    }
  };

})(jQuery, Drupal, once, drupalSettings);
