/**
 * Implementation of the openIframe Ajax command.
 *
 * This command uses the default openDialog command to initialize a dialog. It
 * then attaches event listeners to the iframe and window to handle closing and
 * resizing the dialog.
 */
(function ($, Drupal) {

  function sendEvent(domEl, eventName, detail) {
    domEl.dispatchEvent(
      new CustomEvent(eventName, {
        bubbles: true,
        detail,
      })
    );
  }

  Drupal.AjaxCommands.prototype.openIframe = function (ajax, response, status) {
    Drupal.AjaxCommands.prototype.openDialog(ajax, response, status)
    this.openIframe.initialResize(ajax.wrapper);
    this.openIframe.focusIframe(ajax.wrapper);
    this.openIframe.initMessageListener();
    Drupal.AjaxCommands.prototype.openIframe._scrollPosition = window.scrollY
  };

  /**
   * Set the size of the dialog to fill most of the viewport.
   */
  Drupal.AjaxCommands.prototype.openIframe.initialResize = function (wrapper) {
    var dialog = $('#' + wrapper)
    var dialogHeight = window.innerHeight - 180
    var dialogWidth = window.innerWidth - 180
    dialog.dialog('option', 'height', dialogHeight)
    dialog.dialog('option', 'width', dialogWidth)
  }

  /**
   * Try to focus the window of the iframe.
   */
  Drupal.AjaxCommands.prototype.openIframe.focusIframe = function (wrapper) {
    try {
      var dialog = $('#' + wrapper)
      var iframe = $(dialog).find('iframe')
      $(iframe).on('load', function() {
        this.contentWindow.focus()
      })
    } catch(e) {}
  }

  Drupal.AjaxCommands.prototype.openIframe.initMessageListener = function () {

    const $iframe = $('#ssaIframe');
    $iframe.on('load', (e) => {
      const triggerElement = e.target;

      // jQuery global ajax listeners have to use the iframes jQuery instance - it can't be use the parent jQuery instance
      var instance = triggerElement.contentWindow.jQuery,
        doc = triggerElement.contentWindow.document;

      if (instance && doc) {

        instance(doc).ajaxError(function(event, xhr, settings, thrownError) {
          console.log(thrownError);
          return false;
        });

        instance(doc).ajaxComplete(function (event, xhr, settings) {

          var response = xhr.responseJSON;
          var urlParams = new URLSearchParams(`?${settings.data}`);

          if (urlParams.has('media_library_select_form_selection') && response[1]) {

            const model = {
              entityUUID: response[1].values.attributes['data-entity-uuid'],
              entityId: response[1].values.attributes['data-entity-uuid'],
              entityType: response[1].values.attributes['data-entity-type']
            };

            sendEvent(window, 'ssaMediaModalClose', model);

            // Close modal.
            Drupal.dialog('#drupal-modal').close();
          }
        });
      }
    });
  }

})(jQuery, Drupal);
