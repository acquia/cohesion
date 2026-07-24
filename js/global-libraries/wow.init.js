(function($, Drupal, drupalSettings) {
  "use strict";

  Drupal.behaviors.DX8AnimateOnView = {
    attach: function(context, settings) {
      // User agent matches mobile and disabled.
      if (
        /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
          navigator.userAgent
        ) &&
        drupalSettings.cohesion.animate_on_view_mobile !== "ENABLED"
      ) {
        $(".dx8-aov").removeClass("dx8-aov");
      } else {
        var animate = new Animate({
          target: ".dx8-aov",
          animatedClass: "animated",
          offset: [0, 0],
          remove: true,
          scrolled: true,
          reverse: false,
          onLoad: true,
          onScroll: true,
          onResize: true,
          disableFilter: false,
          callbackOnInit: function() {},
          callbackOnInView: function(el) {}
        });
        animate.init();
      }
    }
  };
})(jQuery, Drupal, drupalSettings);
