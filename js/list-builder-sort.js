(function ($, Drupal, drupalSettings) {

    let accordions = $('details.js-form-wrapper');
    let activeGroup = drupalSettings.cohesion.activeCustomStyleGroup;
    if (typeof accordions === undefined) {
        return false;
    }

    $('a.coh-toggle-accordion').on('click', function (e) {
        e.preventDefault();
        // Close custom style group accordion
        if ($(this).hasClass('close')) {
            $(this).html('Open all');
            $(this).removeClass('close');
            $(this).addClass('open');

            $.each(accordions, function (i, accordion) {
                if ($(accordion).attr('open') === 'open') {
                    $(accordion).removeAttr("open");
                }
            });

        } else if ($(this).hasClass('open')) {
            // Open custom style group accordion
            $(this).html('Close all');
            $(this).removeClass('open');
            $(this).addClass('close');

            $.each(accordions, function (i, accordion) {
                if ($(accordion).attr('open') !== 'open') {
                    $(accordion).find('> summary').trigger('click');
                }
            });
        }

        return false;
    });

    // Open active group
    $.each(accordions, function (i, accordion) {
        let summaryText = $(accordion).find('> summary').text().toLowerCase();
        if (summaryText.indexOf(activeGroup) !== -1) {
            $(accordion).find('> summary').trigger('click');
        }
    });

    if(Drupal.tableDrag) {
      Drupal.tableDrag.prototype.row.prototype._isValidSwap = Drupal.tableDrag.prototype.row.prototype.isValidSwap;
      Drupal.tableDrag.prototype.row.prototype.isValidSwap = function (row) {
        if ($(this.element).hasClass('coh-tabledrag-parent')) {
          var nextRow;
          if (this.direction === 'down') {
            nextRow = $(row).next('tr').get(0);
          } else {
            nextRow = row;
          }

          if ($(nextRow).hasClass('coh-tabledrag-parent-locked')) {
            return false;
          }
        }

        // Return the original result.
        return this._isValidSwap(row);
      };

      Drupal.tableDrag.prototype._dragStart = Drupal.tableDrag.prototype.dragStart;
      Drupal.tableDrag.prototype.dragStart = function (event, self, item) {
        if (self.indentEnabled) {
          var nextRow = $(item).next('tr').get(0);

          if (!$(nextRow).hasClass('coh-tabledrag-parent-locked')) {
            self.indentEnabled = false;
          }
        }

        return self._dragStart(event, self, item);
      };

      /**
       * If the dragged row has a class of 'coh-tabledrag-parent-locked', disable the indentation for the duration of
       * the drag. Store the old indentation setting in _indentEnabled.
       *
       * @override Drupal.tableDrag.dragRow
       */
      Drupal.tableDrag.prototype._dragRow = Drupal.tableDrag.prototype.dragRow;
      Drupal.tableDrag.prototype.dragRow = function (event, self) {

        if (self.rowObject && $(self.rowObject.element).hasClass('coh-tabledrag-parent-locked')) {
          if (self.indentEnabled) {
            self._indentEnabled = true;
            self.indentEnabled = false;
            self.dragObject = null;
          }
        }

        return self._dragRow(event, self);
      };


      /**
       * Restore the original indentation setting, if needed.
       *
       * @override Drupal.tableDrag.dragRow
       */
      Drupal.tableDrag.prototype._dropRow = Drupal.tableDrag.prototype.dropRow;
      Drupal.tableDrag.prototype.dropRow = function (event, self) {
        if (!self.indentEnabled) {
          self.indentEnabled = true;
        }

        if (self._indentEnabled !== null) {
          self.indentEnabled = true;
          self._indentEnabled = null;
          self.dragObject = null;
        }

        return self._dropRow(event, self);
      };
    }
})(jQuery, Drupal, drupalSettings);
