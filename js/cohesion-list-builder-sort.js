(function ($, Drupal, drupalSettings) {

    'use strict';

    Drupal.behaviors.cohesionListbuilderSort = {
        attach: function (context, settings) {

            var MutationObserver = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver;
            var cookieName = null;
            var elements = null;
            if (document.querySelectorAll('#edit-styles details').length > 0) {
                elements = document.querySelectorAll('#edit-styles details');
                cookieName = 'custom_styles_open_accordions';
            }
            else if (document.querySelectorAll('#edit-cohesion-component details').length > 0) {
                elements = document.querySelectorAll('#edit-cohesion-component details');
                cookieName = 'components_open_accordions';
            }
            else if (document.querySelectorAll('#edit-cohesion-helper details').length > 0) {
                elements = document.querySelectorAll('#edit-cohesion-helper details');
                cookieName = 'helpers_open_accordions';
            }


            var open_accordions = [];
            var accordions = getCookie(cookieName);

            if (elements) {

                elements.forEach(function (element) {

                    var observer = new MutationObserver(function (mutations) {
                        mutations.forEach(function (mutation) {
                            // check if the accordion has been opened
                            if (mutation.target.open === true) {
                                // add id to array.
                                open_accordions.push(mutation.target.id);
                                // add array to cookie.
                                document.cookie = cookieName + " =" + open_accordions;

                            }
                            else {
                                // else it's been closed remove from array
                                var i = open_accordions.indexOf(mutation.target.id);
                                if (i !== -1) {
                                    open_accordions.splice(i, 1);
                                }
                                // set cookie.
                                document.cookie = cookieName + " =" + open_accordions;
                            }
                        });
                    });

                    observer.observe(element, {
                        childList: true,
                        attributes: true,
                    });

                });
            }

            // // if cookie has value get the ids of the elements out and apply
            // open attribute.
            if (accordions) {
                $.each(accordions.split(","), function (i, val) {
                    $('#' + val).attr('open', '');
                });

                // check if all accordions are open and update toggle
                // open/close button.
                if (accordions.split(",").length === elements.length) {
                    $('a.coh-toggle-accordion').html('Close all');
                    $('a.coh-toggle-accordion').removeClass('open');
                    $('a.coh-toggle-accordion').addClass('close');
                }
            }

            function getCookie(name) {
                var re = new RegExp(name + "=([^;]+)");
                var value = re.exec(document.cookie);
                return (value != null) ? unescape(value[1]) : null;
            }
        }
    };

})(jQuery, Drupal, drupalSettings);

// Minimal guard to ensure an unsaved-changes warning is shown for admin tables
(function ($, Drupal) {
    'use strict';
    Drupal.behaviors.cohesionTabledragHardening = {
        attach: function () {
            if (Drupal.tableDrag && Drupal.tableDrag.prototype && Drupal.tableDrag.prototype.row && Drupal.tableDrag.prototype.row.prototype) {
                var original = Drupal.tableDrag.prototype.row.prototype.addChangedWarning;
                Drupal.tableDrag.prototype.row.prototype.addChangedWarning = function () {
                    try {
                        if (!$(this.table.parentNode).find('.tabledrag-changed-warning').length) {
                            var el = (typeof Drupal.theme === 'function' && Drupal.theme('tableDragChangedWarning')) || (function () {
                                var d = document.createElement('div');
                                d.className = 'tabledrag-changed-warning messages messages--warning';
                                d.setAttribute('role', 'alert');
                                d.innerHTML = '<abbr class="warning tabledrag-changed" title="Changed">*</abbr> You have unsaved changes.';
                                return d;
                            })();
                            $(el).insertBefore(this.table);
                            $(this.table).addClass('tabledrag-has-changes');
                        }
                    } catch (e) {
                        if (original) {
                            original.call(this);
                        }
                    }
                };
            }
        }
    };
})(jQuery, Drupal);