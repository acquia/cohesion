(function ($, Drupal, once) {

  "use strict";

  Drupal.behaviors.CohesionMatchHeights = {

    attach: function (context) {

      var cmm = new Drupal.CohesionResponsiveBreakpoints();

      // List of possible elements that could be loaded into the DOM onload
      var loaders = [
        'img',
        'frame',
        'iframe',
        'input[type="image"]',
        'link',
        'script',
        'style'
      ];

      /**
             * Applies match height to the given and current DOM objects
             * @param {object} settings - the settings
             * @returns {object} the match height object
             */
      function cohInitMatchHeights(settings) {

        var s = settings.cohesion.settings;
        var $this = s.element;
        var target = '';
        var $el;

        if (s && s.breakpoints && settings.cohesion && settings.cohesion.key && s.breakpoints[settings.cohesion.key] && s.breakpoints[settings.cohesion.key].target) {
          target = s.breakpoints[settings.cohesion.key].target;
          target = target.match(/^[.]/) ? target : '.' + target;
        } else {
          return;
        }

        // Should we target the children
        if(typeof s.breakpoints[settings.cohesion.key].children !== 'undefined' && s.breakpoints[settings.cohesion.key].children === true)  {
          $el = $(target, $this);
        } else {
          $el =  s.element.add(target);
        }

        // Save the current matches so we can destroy it later
        if (typeof $this.data('currentMatchHeight') !== 'undefined') {

          // If $el is the same there is not point in matching again
          if ($this.data('currentMatchHeight') === $el) {
            return;
          }

          cohUnsetMatchHeight($this.data('currentMatchHeight'));

          // If none is set then just return as well
          if (s.breakpoints[settings.cohesion.key].target === 'none') {
            return;
          }
        }

        $this.data('currentMatchHeight', $el);

        return $el.matchHeight({
          byRow: false
        });
      }

      /**
             * Unset match heights to just the current active DOM objects
             * @param {type} $this
             * @returns {undefined}
             */
      function cohUnsetMatchHeight($this) {
        return $this.matchHeight({});
      }

      // Trigger match heights to update - this will be called when behaviors are reattached
      $.fn.matchHeight._update();

      $(once('coh-js-matchheights-init', '[data-coh-match-heights]', context)).each(function () {

        var $this = $(this),
          targets = $this.data('cohMatchHeights'),
          key;

        var settings = {};
        settings.element = $this;
        settings.breakpoints = {};

        // Update the settings prior to attaching the listeners
        for (var i = 0; i < cmm.breakpoints.length; i++) {

          key = cmm.breakpoints[i].key;

          // Populate all breakpoints regardless of whether the settings are set or not to simulate inheritance
          settings.breakpoints[key] = {};
          if (typeof targets[key] !== 'undefined') {

            settings.breakpoints[key] = targets[key];

            var previous = targets[key];

          } else {

            if (typeof cmm.breakpoints[i - 1] !== 'undefined' && typeof previous !== 'undefined') {
              settings.breakpoints[key] = previous;
            }
          }
        }

        // Bind the listeners to our callback
        cmm.addListeners(settings, cohInitMatchHeights);

        // Once the ajax has finished loading AND anything else that could effect the layout (onload)
        $(context).ajaxComplete(function (event, xhr, settings) {

          $.fn.matchHeight._update();

          $(loaders.toString(), context).on('load', function () {
            // if the triggering element was a react/styled-component, don't refresh match heights.
            if ($(this).length && this.dataset.styled !== "active") {
              $.fn.matchHeight._update();
            }
          });
        });
      });
    }
  };

})(jQuery, Drupal, once);
