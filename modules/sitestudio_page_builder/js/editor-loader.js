(function ($, Drupal, drupalSettings) {
  var destroyEvent, reAttachEvent;
  if ( typeof window.CustomEvent !== "function" ) {
    // Polyfill for IE11 creating events.
    function CustomEvent ( event, params ) {
      params = params || { bubbles: false, cancelable: false, detail: undefined };
      var evt = document.createEvent( 'CustomEvent' );
      evt.initCustomEvent( event, params.bubbles, params.cancelable, params.detail );
      return evt;
    }
    CustomEvent.prototype = window.Event.prototype;
    window.CustomEvent = CustomEvent;
    //End polyfill.
    destroyEvent = new CustomEvent('siteStudioDestroy');
    reAttachEvent = new CustomEvent('siteStudioReAttach');
  } else {
    destroyEvent = new Event('siteStudioDestroy');
    reAttachEvent = new Event('siteStudioReAttach');
  }
  drupalSettings.cohesion.formGroup = 'frontendEditor';
  drupalSettings.cohesion.formId = 'frontendEditor';
  let localStorageLocation = 'Drupal.siteStudio.pageBuilderEnabled';
  let pageBuilderEnabled = isPageBuilderEnabled();
  const $loader = $('<div class="coh-editor-loading-container"><div class="coh-editor-loading"><p class="visually-hidden">Loading</p></div></div>')

  function initLoader() {
    $('body').append($loader)
  }

  function removeLoader() {
    $loader.remove();
  }

  function closeToolbarTrays() {
    if(Drupal.toolbar.models && Drupal.toolbar.models.toolbarModel) {
      $(Drupal.toolbar.models.toolbarModel.get('activeTab')).trigger('click');
    }
  }
  function removeDrupalToolbar() {
    const toolbarBar = document.getElementById('toolbar-bar');
    closeToolbarTrays();
    toolbarBar.style.opacity = 0;
    toolbarBar.style.visibility = 'hidden';
    toolbarBar.setAttribute('aria-hidden', 'true');
  }

  function removeContextualEdit() {
    $('[data-contextual-id], .contextual, .dx-contextual-region-mask, .dx-contextual-region').remove();
  }

  function enablePageBuilder(ev) {
    if(ev) {
      ev.preventDefault();
    }

    if(isDrupalInEditMode()) {
      // Come out of Drupal's edit mode by triggering a click of the edit button.
      $('#toolbar-bar .toolbar-icon-edit').click();
    }

    removeContextualEdit();
    initLoader();

    $('#coh-builder-btn').attr('aria-pressed', 'true');

    // Init the Site Studio page builder
    const appEl = document.getElementById('cohApp');
    if (!appEl) {
      const domEl = document.createElement('div');
      domEl.id = 'cohApp';
      domEl.classList.add('coh-app');
      document.body.appendChild(domEl);
      $.getScript(drupalSettings.cohesion.urls['frontend-builder-js'].url, function () {
        removeDrupalToolbar();
        removeLoader();
      });
    } else {
      appEl.dispatchEvent(reAttachEvent);
    }
    storePageBuilderEnabled(true);

  }

  function isDrupalInEditMode() {
    return localStorage.getItem('Drupal.contextualToolbar.isViewing') === 'false'; // false means we are in edit mode.
  }

  function isPageBuilderEnabled() {
    return localStorage.getItem(localStorageLocation) === 'true'
  }

  /**
   *
   * @param value boolean
   */
  function storePageBuilderEnabled(value) {
    return localStorage.setItem(localStorageLocation, value ? 'true' : 'false');
  }

  // Attach the cohesion quick edit functionality to the page, binding the onclick function to toggle edit mode.
  Drupal.behaviors.siteStudioEditor = {
    attach: function attach(context) {
      $('body', context)
          .once('initSiteStudio')
          .each(function() {
            if($('[data-coh-canvas]').length > 0) {

              if(isPageBuilderEnabled()) {
                enablePageBuilder()
              }

              $('#coh-builder-btn').on('click', ()=> {
                if(!isPageBuilderEnabled()) {
                  enablePageBuilder()
                }
              });

              $('#coh-builder-toggle').removeClass('hidden');
            } else {
              $('#coh-builder-toggle').addClass('hidden');
            }
          });
    }
  };
})(jQuery, Drupal, drupalSettings);
