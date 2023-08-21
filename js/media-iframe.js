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
    const dialog = $('#' + wrapper);
    const dialogHeight = window.innerHeight - 180;
    const dialogWidth = window.innerWidth - 180;
    dialog.dialog('option', 'height', dialogHeight)
    dialog.dialog('option', 'width', dialogWidth)
  }

  /**
   * Try to focus the window of the iframe.
   */
  Drupal.AjaxCommands.prototype.openIframe.focusIframe = function (wrapper) {
    try {
      const dialog = $('#' + wrapper);
      const iframe = $(dialog).find('iframe');
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
      const instance = triggerElement.contentWindow.jQuery;
      const doc = triggerElement.contentWindow.document;

      if (instance && doc) {
        instance(doc).ajaxError(function(event, xhr, settings, thrownError) {
          console.log(thrownError);
          return false;
        });

        instance(doc).ajaxComplete(function (event, xhr, settings) {
          const responseCommands = xhr.responseJSON;

          responseCommands.forEach((cmd)=>{
            if(cmd.command === 'editorDialogSave') {
              const model = {
                entityUUID: cmd.values.attributes['data-entity-uuid'],
                entityId: cmd.values.attributes['data-entity-uuid'],
                entityType: cmd.values.attributes['data-entity-type']
              };

              sendEvent(window, 'ssaMediaModalClose', model);

              // Close modal.
              Drupal.dialog('#drupal-modal').close();
            }

            if(cmd.command === 'settings' && cmd.settings && cmd.settings.media_library) {
              // Check if media source is changed and refresh the iframe src.
              const settingsParams = new URLSearchParams(settings.url.split('?')[1]);
              const source = settingsParams.get('source') || 'core';
              const url = cmd.settings.media_library[source];
              if ($iframe.attr('src') !== url) {
                const [href, paramString] = url.split('?');
                const srcParams = new URLSearchParams(paramString);
                srcParams.delete('_wrapper_format');
                $iframe.attr('src', `${href}?${srcParams.toString()}`);
              }
            }
          });
        });
      }
    });
  }

})(jQuery, Drupal);
