(function ($, Drupal, once) {

  Drupal.behaviors.CohesionScroll = {

    attach: function (context) {

      if(drupalSettings.cohesion.add_animation_classes === 'ENABLED') {

        function getWindowOffset() {
          return window.pageYOffset || document.documentElement.scrollTop;
        }

        var previous = getWindowOffset();

        $(once('cohWindowScroll', 'body', context)).each(function() {

          var $body = $(this);
          var timeout, flag = false;

          // Add the class onload if there is an offset
          if(getWindowOffset() > 0) {
            $body.addClass('is-scrolled');
          }

          // Bind the listener
          //window.onscroll = function() {
          $(window).scroll(function() {
            var current = getWindowOffset();

            // Determine if the user is scrolling up or down
            var state = current > previous;

            // These classes should only be applied when the event is taking place
            if(!flag) {
              flag = true;
              $body.addClass('is-scrolling');
            }

            // These class should persist even once scrolling has ceased
            if(current !== previous)    {
              $body.addClass('is-scrolled').toggleClass('is-scrolled-down', state).toggleClass('is-scrolled-up', !state);
            }

            // Store the previous position (handles mobile or negative scrolling)
            previous = current <= 0 ? 0 : current;

            // Remove is-scrolling on scroll stop (no event for scrollStop)
            clearTimeout(timeout);
            timeout = setTimeout(function() {
              $body.removeClass('is-scrolling');
              flag = false;
            }, 200);

            // Remove 'is-scrolled' class if there is no offset
            if(previous === 0) {
              $body.removeClass('is-scrolled');
            }
          });
        });
      }
    }
  };
})(jQuery, Drupal, once, drupalSettings);
