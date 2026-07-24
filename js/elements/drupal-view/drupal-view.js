(function($, Drupal, drupalSettings) {
  "use strict";

  Drupal.behaviors.CohesionViews = {
    attach: function (context, settings) {

      function triggerSubmit(element, forceReload) {
        if ($(element).attr("data-reload-on-change") === 'true' || forceReload === true) {
          var submitButton = $($(element).data('submit-button-id'));
          // Auto submit the form if it's not a text field, if it is submit only if enter is pressed or on blur
          if ($(element).attr('type') !== 'text' && !$(element).hasClass('form-text')) {
            submitButton.trigger('click');
          }
          else if ((event.keyCode && event.keyCode === 13) || event.type === 'blur') {
            submitButton.trigger('click');
          }
        }
      }

      $('.coh-apply-filters').on('click', function(event) {

        event.preventDefault();

        var submitButton = false;

        // Iterate all filter elements to copy values to the hidden form.
        $.each($("*[data-bind]").not('a'), function() {
          submitButton = $($(this).data('submit-button-id'));
          if ($(this).attr('data-bind') !== 'sort') {
            $($(this).attr('data-bind')).val($(this).val());
          }
          else {
            $($(this).attr('data-form-bind') + ' [name="sort_by"]').val($(this).attr('name'));
            $($(this).attr('data-form-bind') + ' [name="sort_order"]').val($(this).val());
          }
        });

        if (submitButton !== false) {
          submitButton.trigger('click');
        }
      });

      $('.coh-reset-filters').on('click', function(event) {
        event.preventDefault();
        var submitResetButton  = $($('*[data-bind]').eq(0).data('reset-button-id'));
        if (submitResetButton !== false) {
          submitResetButton.trigger('click');
        }
      });

      // Copy event handlers to standard Drupal form elements.
      $.each($('*[data-bind]').not('a').data('events'), function() {
        // iterate registered handler of original
        $.each(this, function() {
          $($(this).attr('data-bind')).bind(this.type, this.handler);
        });
      });

      // Initially try and unbind everything (Drupal AJAX causes multiple calls of the attach() function).
      $('*[data-bind]').unbind('change input blur keyup click').on('click', function(event) {

        // List style element.
        if ($(this).is('a')) {
          event.preventDefault();

          // Is a multiselect.
          if ($(this).attr('data-multiple') === 'true') {

            $(this).parent().toggleClass('active');

            // Get all values from matching <a>
            var vals = $('li.active a[data-key="'+$(this).attr('data-key')+'"]').map(function(i, v) {
              return $(this).attr('data-value');
            }).toArray();

            $($(this).attr('data-bind')).val(vals);   // Apply the multi-select values.
          }
          // Not a multiselect.
          else {

            if ($(this).attr('data-bind') !== 'sort') {
              $($(this).attr('data-bind')).val($(this).attr('data-value'));
            }
            else {
              $($(this).attr('data-form-bind') + ' [name="sort_by"]').val($(this).attr('data-key'));
              $($(this).attr('data-form-bind') + ' [name="sort_order"]').val($(this).attr('data-value'));
            }
          }

          triggerSubmit(this, true);
        }


      }).on('change', function(event) {

        if ($(this).is('input') || $(this).is('select')) {
          // If input (text, select)

          if ($(this).attr('data-bind') !== 'sort') {
            $($(this).attr('data-bind')).val($(this).val());

            // Specifically for better exposed filters sort combined option.
            if ($(this).attr('data-bind') === 'sort_bef_combine') {
             $($(this).attr('data-form-bind') + ' [name="sort_bef_combine"]').val($(this).attr('name'));
             $($(this).attr('data-form-bind') + ' [name="sort_bef_combine"]').val($(this).val());
            }
          }
          else {
            $($(this).attr('data-form-bind') + ' [name="sort_by"]').val($(this).attr('name'));
            $($(this).attr('data-form-bind') + ' [name="sort_order"]').val($(this).val());
          }

        }
        else {
          // If no input (checkbox, radio)
          $.each($(this).find("input"), function() {
            var parent = $(this).parents('.coh-view');
            parent.find('input[data-drupal-selector="' + $(this).attr('data-drupal-selector') + '"]').prop('checked', $(this).is(':checked'));
          });

        }

        triggerSubmit(this);
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
