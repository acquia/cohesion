(function ($, drupalSettings) {
  function storeState(ev) {
    $.each($('.js-coh-read-more'), function () {
      drupalSettings.cohesion.elementState.setState(this, {
        isOpen: $(this).data('open'),
      });
    });
  }

  function loadState(ev) {
    $.each($('.js-coh-read-more'), function () {
      const state = drupalSettings.cohesion.elementState.getState(this);
      if (state && state.isOpen !== undefined) {
        $(this).data('cohSettings').forceInitialVisibility = state.isOpen;
      }
    });
  }
  document.body.addEventListener('cohPageBuilderDomWillReload', storeState, false);
  document.body.addEventListener('cohPageBuilderDomReloaded', loadState, false);
})(jQuery, drupalSettings);
