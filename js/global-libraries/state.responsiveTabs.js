(function ($, drupalSettings) {
  function storeState(ev) {
    $.each($('.coh-accordion-tabs > .coh-accordion-tabs-inner'), function () {
      drupalSettings.cohesion.elementState.setState(this, {
        selected: $(this).responsiveTabs('getCurrentTab'),
      });
    });
  }

  function loadState(ev) {
    $.each($('.coh-accordion-tabs > .coh-accordion-tabs-inner'), function () {
      const state = drupalSettings.cohesion.elementState.getState(this);
      if (state && state.selected !== undefined) {
        $(this).on('tabs-load', function (event, tabs) {
          tabs.options.active = state.selected;
        });
      }
    });
  }
  document.body.addEventListener('cohPageBuilderDomWillReload', storeState, false);
  document.body.addEventListener('cohPageBuilderDomReloaded', loadState, false);
})(jQuery, drupalSettings);
