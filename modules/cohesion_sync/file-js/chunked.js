/**
 * Based on: core/modules/file/file.js
 * @preserve
 **/

(function ($, Drupal, once, drupalSettings) {
  Drupal.behaviors.fileValidateAutoAttach = {
    attach: function attach(context, settings) {
      var $context = $(context);
      var elements = void 0;

      function initFileValidation(selector) {
        $(once('fileValidate', $context.find(selector))).on('change.fileValidate', {extensions: elements[selector]}, Drupal.file.validateExtension);
      }

      if (settings.file && settings.file.elements) {
        elements = settings.file.elements;
        Object.keys(elements).forEach(initFileValidation);
      }
    },
    detach: function detach(context, settings, trigger) {
      var $context = $(context);
      var elements = void 0;

      function removeFileValidation(selector) {
        $(once.remove('fileValidate', $context.find(selector))).off('change.fileValidate', Drupal.file.validateExtension)
      }

      if (trigger === 'unload' && settings.file && settings.file.elements) {
        elements = settings.file.elements;
        Object.keys(elements).forEach(removeFileValidation);
      }
    }
  };

  Drupal.behaviors.fileAutoUpload = {
    attach: function attach(context) {
      // Trigger the AJAX form validation.
      $(once('auto-file-upload', '#edit-legacy-import input[type="file"]', context)).on('change.autoFileUpload', Drupal.file.triggerUpload);
    },
    detach: function detach(context, settings, trigger) {
      if (trigger === 'unload') {
        $(once.remove('auto-file-upload', '#edit-legacy-import input[type="file"]', context)).off('.autoFileUpload');
      }
    }
  };

  Drupal.file = Drupal.file || {
    validateExtension: function validateExtension(event) {
      event.preventDefault();

      $('[data-drupal-messages]').remove();

      var extensionPattern = event.data.extensions.replace(/,\s*/g, '|');
      if (extensionPattern.length > 1 && this.value.length > 0) {
        var acceptableMatch = new RegExp('\\.(' + extensionPattern + ')$', 'gi');
        if (!acceptableMatch.test(this.value)) {
          var error = Drupal.t('The selected file %filename cannot be uploaded. Only files with the following extensions are allowed: %extensions.', {
            '%filename': this.value.replace('C:\\fakepath\\', ''),
            '%extensions': extensionPattern.replace(/\|/g, ', ')
          });
          $(this).closest('div.js-form-type-chunked-file').before('<div data-drupal-messages><div role="contentinfo" aria-label="Error message" class="messages messages--error"> <div role="alert"> <h2 class="visually-hidden">Error message</h2>' + error + '</div> </div> </div>');

          this.value = '';

          event.stopImmediatePropagation();
        }
      }
    },
    triggerUpload: function triggerUpload(event) {
      var element = $(this);
      var file;
      var xhr;

      event.preventDefault();

      // Take the file from the input.
      file = $(this)[0].files[0];
      if (file) {
        // AJAX loading animation.
        element.closest('div.js-form-managed-file').append('<div class="ajax-progress ajax-progress-throbber"><div class="throbber">&nbsp;</div></div>');

        // Start the file reader.
        var reader = new FileReader();
        reader.readAsBinaryString(file); // alternatively you can use readAsDataURL
        reader.onloadend = function (evt) {
          // create XHR instance
          xhr = new XMLHttpRequest();

          // Send the file through POST.
          xhr.open('POST', drupalSettings.cohesion.urls.sync_file_chunk, true);

          // Send the filename in the header.
          xhr.setRequestHeader('filename', file.name);

          // make sure we have the sendAsBinary method on all browsers
          XMLHttpRequest.prototype.mySendAsBinary = function (text) {
            var blob;
            var data = new ArrayBuffer(text.length);
            var ui8a = new Uint8Array(data, 0);
            for (var i = 0; i < text.length; i++) ui8a[i] = (text.charCodeAt(i) & 0xff);

            if (typeof window.Blob == 'function') {
              blob = new Blob([data]);
            } else {
              var bb = new (window.MozBlobBuilder || window.WebKitBlobBuilder || window.BlobBuilder)();
              bb.append(data);
              blob = bb.getBlob();
            }

            this.send(blob);
          };

          // state change observer - we need to know when and if the file was successfully uploaded
          xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
              // Stop the loading animation.
              $('.ajax-progress').remove();

              //
              // Process success.
              if (xhr.status === 200) {
                // Change the type to hidden and set the value to the URI.
                var inputElement = element.closest('div.js-form-managed-file input');
                inputElement.get(0).type = 'hidden';
                inputElement.val(xhr.responseText);

                $(inputElement).before('<span>Validating...</span>');

                // Trigger the AJAX validation.
                $(event.target).closest('.js-form-managed-file').find('.js-form-submit[data-drupal-selector$="upload-button"]').trigger('mousedown');
              }
              // Process error.
              else {
                Drupal.file.throwError(element, xhr.responseText);
                event.stopImmediatePropagation();

              }
            }
          };

          // start sending
          xhr.mySendAsBinary(evt.target.result);
        };

      }
    }
  };
})(jQuery, Drupal, once, drupalSettings);
