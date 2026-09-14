(function ($, Drupal, once) {
  "use strict";

  Drupal.behaviors.CohesionReadMore = {
    attach: function (context) {

      // Libs and global vars
      var cmm = new Drupal.CohesionResponsiveBreakpoints(),
        $this,
        settings;

      $(once('coh-read-more-init', '.js-coh-read-more', context)).each(function () {

        $this = $(this);

        var data = $this.data('cohSettings'),
          key,
          previous;

        // Store the state
        $this.data('open', false);

        settings = {
          'buttonTextCollapsed': data.buttonTextCollapsed,
          'buttonTextExpanded': data.buttonTextExpanded,
          'breakpoints': {}
        };

        // Populate each of the breakpoints regardless
        for (var i = 0; i < cmm.breakpoints.length; i++) {

          key = cmm.breakpoints[i].key;

          // Populate all breakpoints regardless of whether the settings are set or not to simulate inheritance
          settings.breakpoints[key] = {};
          if (typeof data.breakpoints[key] !== 'undefined' && !$.isEmptyObject(data.breakpoints[key])) {
            settings.breakpoints[key] = $.extend({}, previous, data.breakpoints[key]);

            if(typeof settings.breakpoints[key].visibility !== 'undefined'){
              $('> .coh-read-more-inner', $this).removeClass('coh-' + settings.breakpoints[key].visibility + '-' + key );
            }

            previous = settings.breakpoints[key];

          } else if (typeof cmm.breakpoints[i - 1] !== 'undefined' && typeof previous !== 'undefined') {
            settings.breakpoints[key] = previous;
          }
        }

        // Add listeners to toggle the visibility
        // cmm.addListeners(settings, toggleBreakpoint);
        var visibility = settings.breakpoints[cmm.getCurrentBreakpoint().key].visibility;

        if(data.forceInitialVisibility !== undefined) {
            visibility = data.forceInitialVisibility ? 'visible' : 'hidden';
        }

        toggleVisibility(visibility === 'visible', $this, settings);

        // Allow visibility to be toggled externally
        $this[0].toggleVisibility = function() {
            toggleVisibility(!$this.data('open'), $this, settings);
        }

        $('> .coh-read-more-btn', $this).on('click', {element: $this, settings: settings}, runAnimation);
      });

      /**
             * Called
             * @param {type} e
             * @returns {undefined}
             */
      function runAnimation(e) {

        e.preventDefault();

        var element = e.data.element;
        var settings = e.data.settings;

        var origin,
          current = settings.breakpoints[cmm.getCurrentBreakpoint().key];

        if (current.animationOrigin) {
          origin = current.animationOrigin.split(',');
        }

        var currentSettings;

        if (current.animationType && current.animationType !== 'none') {
          currentSettings = {
            effect: current.animationType,
            direction: current.animationDirection,
            distance: current.animationDistance,
            pieces: current.animationPieces,
            percent: current.animationScale,
            origin: origin,
            size: current.animationFoldHeight,
            horizFirst: current.animationHorizontalFirst,
            times: current.animationIterations,
            easing: current.animationEasing,
            duration: current.animationDuration
          };
        }

        toggleVisibility(!element.data('open'), element,  settings, currentSettings);
      }

      /**
             * Toggles the classes and animation of the readmore element
             * @param {boolean} open - toggle,
             * @param {jQuery} element - jquery element,
             * @param {object} settings - the whole settings Object
             * @param {object} toggle - the toggle options true || false || Object || undefined
             * @returns {undefined}
             */
      function toggleVisibility(open, element, settings, toggle) {

        element.data('open', open);

        // Toggle the panel states
        $('> .coh-read-more-inner', element)
          .toggleClass('is-expanded', open)
          .toggleClass('is-collapsed', !open)
          .toggle(toggle || open).promise().then(function () {

            // Toggle the button states
            $('> .coh-read-more-btn', element)
              .toggleClass('is-expanded', open)
              .toggleClass('is-collapsed', !open)
              .attr('aria-expanded', open)
              .text(open ? settings.buttonTextExpanded : settings.buttonTextCollapsed);

            if (open) {
              Drupal.attachBehaviors($(this)[0]);
            }
          });

      }
    }
  };
})(jQuery, Drupal, once);
