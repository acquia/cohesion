(function ($, Drupal) {
  'use strict';
  Drupal.behaviors.cohesionAccordionElement = {
    attach: function (context, settings) {
      function openAccordion($el) {
        $el.next().slideToggle();
        $el.parent().toggleClass('panel-open');
      }

      $('.cohesion-accordion .panel-heading').off().click(function () {
        openAccordion($(this));
      });

      $('.cohesion-accordion .panel-heading').keypress(function (e) {
        // On enter/return keypress, toggle accordion
        if (e.which == 13) {
          openAccordion($(this));
        }

        // On space keypress, toggle accordion and prevent scrolling down page
        if (e.which == 32) {
          openAccordion($(this));
          e.preventDefault();
        }
      });

      $('.cohesion-accordion .panel-heading a').click(function (e) {
        e.stopPropagation();
      });
    }
  }
})(jQuery, Drupal);
