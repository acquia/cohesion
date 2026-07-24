(function ($, drupalSettings) {
  function storeState(ev) {
    $.each($('.coh-slider-container'), function () {
      const $slider = $('> .coh-slider-container-mid > .coh-slider-container-inner', this);
      if($slider[0] && $slider[0].slick) {
          drupalSettings.cohesion.elementState.setState(this, {
            currentSlide: $slider[0].slick.currentSlide || 0,
          });
      }
    });
  }

  function loadState(ev) {
    $.each($('.coh-slider-container'), function () {
      const state = drupalSettings.cohesion.elementState.getState(this);
      const $slider = $('> .coh-slider-container-mid > .coh-slider-container-inner', this);
      $slider.on('init', function (event, slick) {
        if (state && state.currentSlide) {
          slick.currentSlide = state.currentSlide;
        }
      });
    });
  }
  document.body.addEventListener('cohPageBuilderDomWillReload', storeState, false);
  document.body.addEventListener('cohPageBuilderDomReloaded', loadState, false);
})(jQuery, drupalSettings);
