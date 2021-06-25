(function($) {
    'use strict';
    var pluginName = 'dx8ContextualRegion';
    var defaults = {};

    /**
     * Instantiate our plugin
     * @param element passed from jQuery
     * @param options passed in
     * @constructor
     */
    function Plugin(element, options) {
        this.els = {
            $region: $(element)
        };
        this.settings = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        this.init();
    }

    // Avoid Plugin.prototype conflicts
    $.extend(Plugin.prototype, {
        init: function() {
            var self = this;
            var interval;
            var timesToCheck = 8;
            var intervalMs = 200;

            self.uniqueid = new Date().getTime();
            self.els.$parentContainer = $('.dialog-off-canvas-main-canvas');
            self.els.$window = $(window);

            // Select all the siblings with the class that matches the dx-contextual attr stopping if it finds another contextual region.
            self.els.$component = self.els.$region.nextUntil('.dx-contextual-region', '.' + self.els.$region.data('dx-contextual'));

            if(!self.els.$component.length) {
                self.els.$region.remove();
                return;
            }

            self.els.$mask = $('<div class="dx-contextual-region-mask ' + self.els.$region.data('dx-contextual') + '-mask" />');
            self.els.$mask.appendTo(self.els.$parentContainer);
            self.els.$region.detach().appendTo(self.els.$parentContainer);

            self.bindHover();
            self.bindResize();

            self.resetPositions();

            // To ensure edit buttons show in the correct position after all elements have loaded on the page, we reset
            // their position several times (timesToCheck) at intervals after the page has loaded..
            interval = window.setInterval(function() {
                if (timesToCheck < 1) {
                    window.clearInterval(interval);
                }
                self.resetPositions();
                timesToCheck--;
            }, intervalMs);

            $(document).on('drupalToolbarTrayChange', function() {
                self.els.$mask.css({ transition: 'none', opacity: 0 });
                self.els.$region.css({ transition: 'none', opacity: 0 });
                window.setTimeout(function() {
                    self.resetPositions(true);
                }, 300);
            });

            // When sidebar is opened, padding animates out. Hide elements until anim has finished.
            $(window).on('dialog:beforecreate dialog:beforeclose', function() {
                var animationDuration = parseFloat($('.dialog-off-canvas-main-canvas').css('transition-duration')) * 1000 || 700;
                self.els.$mask.css({ opacity: 0 });
                self.els.$region.css({ opacity: 0 });

                window.setTimeout(function() {
                    self.resetPositions(true);
                }, animationDuration);
            });

            // Sadly I don't think calling this here is reliable enough vs having the setTimeout above. E.g. If the anim is cancelled there is no
            // fallback to show the edit pencils again.
            // $(document).on('webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend', '.dialog-off-canvas-main-canvas', function(event) {
            //     if (event.originalEvent.propertyName === 'padding-right') {
            //         self.resetPositions(true);
            //     }
            // });

        },
        bindHover: function() {
            var self = this;
            self.els.$component.add(self.els.$region).on('mouseenter', function() {
                self.setSize();
                self.setTopRight();
                self.els.$mask.addClass('dx-show');
            });
            self.els.$component.add(self.els.$region).on('mouseleave', function() {
                self.els.$mask.removeClass('dx-show');
            });
        },
        resetPositions: function(setVisible) {
            var self = this;
            self.setSize();
            self.setTopRight();
            if (setVisible) {
                self.els.$mask.css({ opacity: 1, transition: '' });
                self.els.$region.css({ opacity: 1, transition: '' });
            }
        },

        bindResize: function() {
            var self = this;
            $(window).off('resize.' + self.uniqueid).on('resize.' + self.uniqueid, function() {

                // debounce costly resetPositions call
                window.clearTimeout(self.resizeTimer);
                self.resizeTimer = window.setTimeout(function() {
                    self.resetPositions(true);
                }, 100);
            });
        },
        setTopRight: function() {
            var minTop,
                maxRight;
            var tops = [];
            var rights = [];

            $.each(this.els.$component, function() {
                var $this = $(this);
                tops.push($this.offset().top);
                rights.push($this.offset().left + $this.outerWidth() + parseInt($this.css('marginRight')));

            });

            minTop = this.getMinOfArray(tops);
            maxRight = this.getMaxOfArray(rights);

            this.els.$region.css({
                top: minTop,
                left: maxRight - 30,
                right: 'auto'
            });
        },
        setSize: function() {
            var minTop,
                minLeft,
                maxRight,
                maxBottom;
            var tops = [];
            var lefts = [];
            var rights = [];
            var bottoms = [];

            $.each(this.els.$component, function() {
                var $this = $(this);
                tops.push($this.offset().top);
                lefts.push($this.offset().left - parseInt($this.css('marginLeft')));
                rights.push($this.offset().left + $this.outerWidth() + parseInt($this.css('marginRight')));
                bottoms.push($this.offset().top + $this.outerHeight() + parseInt($this.css('marginBottom')));

            });

            minTop = this.getMinOfArray(tops);
            minLeft = this.getMinOfArray(lefts);
            maxRight = this.getMaxOfArray(rights);
            maxBottom = this.getMaxOfArray(bottoms);

            if(Math.floor(maxRight) > this.els.$window.width()) {
                this.els.$mask.css({display: 'none'});
                this.els.$region.css({display: 'none'});
            } else {
                this.els.$region.css({display: ''});
                this.els.$mask
                    .css({
                        top: minTop,
                        left: minLeft,
                        width: maxRight - minLeft,
                        height: maxBottom - minTop,
                        display: ''
                    });
            }
        },
        getMaxOfArray: function(numArray) {
            return Math.max.apply(null, numArray);
        },
        getMinOfArray: function(numArray) {
            return Math.min.apply(null, numArray);
        }
    });

    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[pluginName] = function(options) {
        return this.each(function() {
            var plugin = $.data(this, 'plugin_' + pluginName);
            if (!plugin) {
                $.data(this, 'plugin_' + pluginName, new Plugin(this, options));
            } else if (options && typeof plugin[options] === 'function') {
                plugin[options]();
            }
        });
    };

    Drupal.behaviors.dxContextualRegion = {
        attach: function(context) {
            $.each($('.dx-contextual-region', context), function(i) {
                var $this = $(this);
                $this.dx8ContextualRegion();
                // // Guard against initialising multiple times if there are multiple content components on the same page.
                // if(!uidList.includes($(this).data('dx-contextual'))) {
                //     uidList.push($(this).data('dx-contextual'));
                // }
            });
        }
    };

})(jQuery);
