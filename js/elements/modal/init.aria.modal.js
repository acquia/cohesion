(function ($, Drupal, once) {
  "use strict";

  Drupal.behaviors.CohesionModal = {
    attach: function (context) {

      $(once('coh-js-modal-init', '.coh-modal')).each(function () {

        var $this = $(this);
        var modalAnimation = $this.data('cohModalAnimation');
        var modalDelayOpen = $this.data('cohModalDelayOpen') || 0;
        var modalDelayClose = $this.data('cohModalDelayClose') || 0;
        var $modalInner = $this.find('> .coh-modal-inner');
        var $modalOverlay = $this.find('> .coh-modal-overlay');
        var $modalCloseWrapper = $this.find('> .coh-modal-close-wrapper');
        var animOptions = {}
        var overlayAnimOptions = {effect: 'fade', duration: 150};
        var delayOpenTimeout, delayCloseTimeout;
        var openClass = 'is-open';

        if (modalAnimation && modalAnimation.effect !== 'none') {
          animOptions = {
            effect: modalAnimation.effect,
            direction: modalAnimation.direction,
            distance: modalAnimation.distance,
            size: modalAnimation.size,
            horizFirst: modalAnimation.horizFirst,
            times: modalAnimation.times,
            easing: modalAnimation.easing,
            duration: modalAnimation.duration
          };
        }

        if (modalAnimation && modalAnimation.effect === 'none') {
          animOptions = {
            duration: 0
          };
        }

        var openAnimOptions = $.extend({}, animOptions);
        var closeAnimOptions = $.extend({
          complete: function () {
            $this.hide();

            $($this).removeClass(openClass);
            $($modalInner).removeClass(openClass);

            Drupal.detachBehaviors($this[0]);
          }
        }, animOptions);

        openAnimOptions.complete = function() {
          // Run Drupal behaviors for anything that is hidden
          Drupal.attachBehaviors($this[0]);

          $($this).addClass(openClass);
          $($modalInner).addClass(openClass);
        };

        if ($this.attr('coh-data-modal-auto-close') !== undefined && modalDelayClose) {
          openAnimOptions.complete = function() {
            // Run Drupal behaviors for anything that is hidden
            Drupal.attachBehaviors($this[0]);
            delayCloseTimeout = setTimeout(function() {
              window.ARIAmodal.closeModal();

              $($this).addClass(openClass);
              $($modalInner).addClass(openClass);

            }, modalDelayClose);
          };
        }

        // Load on init if it's supposed to.
        if ($this.attr('data-modal-auto') !== undefined && $this.attr('hidden') === undefined) {
          delayOpenTimeout = setTimeout(function () {

            $this.show();

            if ($modalCloseWrapper.length) {
              $modalCloseWrapper.show(overlayAnimOptions);
            }

            if ($modalOverlay.length) {
              $modalOverlay.show(overlayAnimOptions);
              $('body').addClass('coh-modal-overlay-open');
            }

            $modalInner.show(openAnimOptions);
          }, modalDelayOpen);
        }

        // Watch the hidden attribute set by modal plugin and run anims if they are set.
        var onMutate = function(mutationsList, observer) {
          mutationsList.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'hidden') {
              var $modalContainer = $(mutation.target);
              var $modal = $modalContainer.find('> .coh-modal-inner');
              if (mutation.target['hidden']) {
                // close the modal
                window.clearTimeout(delayOpenTimeout);
                window.clearTimeout(delayCloseTimeout);

                if ($modalCloseWrapper.length) {
                  $modalCloseWrapper.hide(overlayAnimOptions);
                }

                if ($modalOverlay.length) {
                  $modalOverlay.hide(overlayAnimOptions);
                  $('body').removeClass('coh-modal-overlay-open');
                };

                $modal.hide(closeAnimOptions);
              } else {
                // open the modal
                window.clearTimeout(delayOpenTimeout);
                window.clearTimeout(delayCloseTimeout);

                if ($modalOverlay.length) {
                  $('body').addClass('coh-modal-overlay-open');
                }

                $modalContainer.show(0, function() {
                  $modal.show(openAnimOptions);

                  if ($modalCloseWrapper.length) {
                    $modalCloseWrapper.show(overlayAnimOptions);
                  }

                  if ($modalOverlay.length) {
                    $modalOverlay.show(overlayAnimOptions);
                  };
                });
              }
            }
          });
        };

        var observer = new MutationObserver(onMutate);
        observer.observe($this[0], {attributes: true});
      });
    }
  };

})(jQuery, Drupal, once);
