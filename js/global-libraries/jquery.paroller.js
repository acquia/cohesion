/**
 * @sitestudioexcludesonar
 * jQuery plugin paroller.js v1.3.1
 * https://github.com/tgomilar/paroller.js
 * preview: https://tgomilar.github.io/paroller/
 **/
(function (factory) {
  'use strict';

  if (typeof module === 'object' && typeof module.exports === 'object') {
    module.exports = factory(require('jquery'));
  }
  else {
    factory(jQuery);
  }
})(function ($) {
  'use strict';

  var setDirection = {
    bgVertical: function (elem, bgOffset, bgStart) {
      return elem.css({'backgroundPositionY': 'calc(' + -bgOffset + 'px + ' + bgStart + ')'});
    },
    bgHorizontal: function (elem, bgOffset, bgStart) {
      return elem.css({'backgroundPositionX': 'calc(' + -bgOffset + 'px + ' + bgStart + ')'});
    },
    vertical: function (elem, elemOffset, oldTransform) {
      (oldTransform === 'none' ? oldTransform = '' : true);
      return elem.css({
        '-webkit-transform': 'translateY(' + elemOffset + 'px)' + oldTransform,
        '-moz-transform': 'translateY(' + elemOffset + 'px)' + oldTransform,
        'transform': 'translateY(' + elemOffset + 'px)' + oldTransform,
        'transition': 'transform linear',
        'will-change': 'transform'
      });
    },
    horizontal: function (elem, elemOffset, oldTransform) {
      (oldTransform === 'none' ? oldTransform = '' : true);
      return elem.css({
        '-webkit-transform': 'translateX(' + elemOffset + 'px)' + oldTransform,
        '-moz-transform': 'translateX(' + elemOffset + 'px)' + oldTransform,
        'transform': 'translateX(' + elemOffset + 'px)' + oldTransform,
        'transition': 'transform linear',
        'will-change': 'transform'
      });
    }
  };

  $.fn.paroller = function (options) {
    var windowHeight = $(window).height();
    var documentHeight = $(document).height();

    // default options
    options = $.extend({
      factor: 0, // - to +
      type: 'background', // foreground
      direction: 'vertical', // horizontal
      bgstart: 0
    }, options);

    return this.each(function () {
      var working = false;
      var $this = $(this);
      var offset = $this.offset().top;
      var height = $this.outerHeight();
      var dataFactor = $this.data('paroller-factor');
      var dataType = $this.data('paroller-type');
      var dataDirection = $this.data('paroller-direction');
      var dataBgStart = $this.data('paroller-bg-start');
      var factor = (dataFactor) ? dataFactor : options.factor;
      var type = (dataType) ? dataType : options.type;
      var direction = (dataDirection) ? dataDirection : options.direction;
      var bgStart = (dataBgStart) ? dataBgStart : options.bgstart;
      var bgOffset = Math.round(offset * factor);
      var transform = Math.round((offset - (windowHeight / 2) + height) * factor);

      /* Transform directive of element before paroller */
      var oldTransform = $this.css('transform');

      if (type === 'background') {

        if(typeof bgStart === 'number') {
          bgStart = bgStart + 'px';
        }

        if(!bgStart.length) {
          bgStart = '0';
        }
        //if last char is a number, there is no unit specified, add px.
        if(typeof (bgStart.slice(-1) === 'number') ) {
          bgStart = bgStart + 'px';
        }

        if (direction === 'vertical') {
          setDirection.bgVertical($this, bgOffset, bgStart);
        }
        else if (direction === 'horizontal') {
          setDirection.bgHorizontal($this, bgOffset, bgStart);
        }
      }
      else if (type === 'foreground') {
        if (direction === 'vertical') {
          setDirection.vertical($this, transform, oldTransform);
        }
        else if (direction === 'horizontal') {
          setDirection.horizontal($this, transform, oldTransform);
        }
      }

      $(window).on('scroll.paroller', onScroll).trigger('scroll');

      function scrollAction() {
        working = false;
      }

      function onScroll() {
        if (!working) {
          var scrolling = $(this).scrollTop();
          documentHeight = $(document).height();

          bgOffset = Math.round((offset - scrolling) * factor);
          transform = Math.round(((offset - (windowHeight / 2) + height) - scrolling) * factor);

          if (type === 'background') {
            if (direction === 'vertical') {
              setDirection.bgVertical($this, bgOffset, bgStart);
            }
            else if (direction === 'horizontal') {
              setDirection.bgHorizontal($this, bgOffset, bgStart);
            }
          }
          else if ((type === 'foreground') && (scrolling <= documentHeight)) {
            if (direction === 'vertical') {
              setDirection.vertical($this, transform, oldTransform);
            }
            else if (direction === 'horizontal') {
              setDirection.horizontal($this, transform, oldTransform);
            }
          }

          window.requestAnimationFrame(scrollAction);
          working = true;
        }
      }
    });
  };
});
