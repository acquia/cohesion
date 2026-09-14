(function ($, Drupal, once) {
  "use strict";

  Drupal.behaviors.CohesionLazyLoad = {
    attach: function (context, settings) {

      function isScrollable(el) {
        return (el.scrollWidth > el.clientWidth && (getComputedStyle(el).overflowY === 'auto' || getComputedStyle(el).overflowY === 'scroll')) ||
          (el.scrollHeight > el.clientHeight  && (getComputedStyle(el).overflowX === 'auto' || getComputedStyle(el).overflowX === 'scroll')) ||
          el.tagName === 'HTML';
      }

      once('lazyload-once', '[loading=lazy]', context).forEach(function(item) {
        var $this = $(item);
        $this.parents().each(function() {
          var $parent = $(this);
          if($parent.data('lazyContainerFound') === true) {
            if($parent.data('llContainer')) {
              $parent.data('llContainer').update();
            }
            return false;
          } else if(isScrollable(this)) {
            $parent.data('lazyContainerFound', true);
            var llContainer = new LazyLoad({
              container: this.tagName === 'HTML' ? document : this,
              elements_selector: "[loading=lazy]",
              class_loading: 'coh-lazy-loading',
              class_loaded: 'coh-lazy-loaded',
              class_error: 'coh-lazy-error',
              use_native: true
            });
            $parent.data('llContainer', llContainer);
            return false;
          }
        });

        if ($.fn.matchHeight) {
         $this.on('load', function () {
            if ($(this).length) {
              $.fn.matchHeight._update();
            }
         });
        }
      });
    }
  };

})(jQuery, Drupal, once);
