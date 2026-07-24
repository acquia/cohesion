// @TODO withClassName || with-class-name

(function ($, Drupal, once, drupalSettings) {
    'use strict';

    Drupal.behaviors.CohesionSlick = {
        attach: function (context) {
            var gridSettings = drupalSettings.cohesion.responsive_grid_settings;
            var cmm = new Drupal.CohesionResponsiveBreakpoints(
                drupalSettings.cohesion.responsive_grid_settings
            );
            var onceInit = 'coh-slider-container-init';
            const isMatchHeightEnabled = drupalSettings.cohesion.front_end_settings.global_js.matchHeight;

            $.each($('.coh-slider-container', context), function () {
                var $this = $(this);
                var $slider = $('> .coh-slider-container-mid > .coh-slider-container-inner', this);
                var $slides = $('> .coh-slider-item', $slider);

                // Refresh slick if this is re-called (e.g. when opening a tab containing a slick slider and attaching drupal behaviors)
                if ($this.data('jquery-once-' + onceInit)) {
                    $slider.slick('refresh');
                    return;
                }

                // handle case where in VPB you might add a slider but not have added the slide yet. Trick slick into thinking is initialised.
                if (!$slides.length) {
                    $slider.addClass('slick-initialized');
                    return;
                }

                function updateCount(slick) {
                    var i = (slick.currentSlide ? slick.currentSlide : 0) + 1;

                    slick.$slideCounter.text(i + '/' + slick.slideCount);
                }

                function updateCounter(slick) {
                    if (slick.options.counter) {
                        // Only append if it doesnt exist - moves the DOM element around so we don't have to worry about any clean up
                        if (!$(slick.options.appendCounter).find(slick.$slideCounter).length) {
                            $(slick.options.appendCounter).append(slick.$slideCounter);
                        }
                    } else {
                        // Detach from the DOM, but keep in memory ready for use later if we need it
                        slick.$slideCounter.detach();
                    }
                    // Always keep the counter up to date regardless
                    updateCount(slick);
                }

                function updateNavigationLabels(slick) {
                    // Accessibility of labels
                    if (slick.options.infinite && slick.options.arrows) {
                        if (i === 1) {
                            $(slick.$prevArrow).attr('aria-label', 'Last slide');
                        } else {
                            $(slick.$prevArrow).attr('aria-label', 'Previous slide');
                        }

                        if (i === slick.slideCount) {
                            $(slick.$nextArrow).attr('aria-label', 'First slide');
                        } else {
                            $(slick.$nextArrow).attr('aria-label', 'Next slide');
                        }
                    }
                }

                // Recall behaviors for any cloned slides
                $slider.on('init', function (event, slick) {
                    var clones = $($('.slick-cloned', slick.$slideTrack));

                    // Cache the slide counter into memory
                    slick.$slideCounter = $('<div />', {
                        class: slick.options.counterClass,
                    });

                    // We only want to init this once we know that the slider has actually initd
                    once(onceInit, $this);

                    $.each(clones, function () {
                        Drupal.behaviors.CohesionSlick.attach($(this));
                        // Fix click events on cloned slides
                        $(this).on('click', function (e) {
                            // Find the clicked child element
                            const $clonedChild = $(e.target);
                        
                            // Capture the element's tag name and class list safely
                            const clonedChildTag = $clonedChild.prop('tagName').toLowerCase();
                            const clonedChildClassList = $clonedChild.attr('class') 
                                ? `.${$clonedChild.attr('class').trim().split(/\s+/).join('.')}` 
                                : ''; // Handles elements without class
                        
                            // Find the original slide
                            const originalIndex = $(this).attr('data-slick-index') % slick.slideCount;
                            const $originalSlide = $(slick.$slides.get(originalIndex));
                        
                            // Find and trigger the corresponding child element in the original slide
                            $originalSlide.find(`${clonedChildTag}${clonedChildClassList}`).trigger('click');
                        });
                    });

                    updateCounter(slick);

                    updateNavigationLabels(slick);
                });

                $slider.on('afterChange', function (event, slick) {
                    updateCount(slick);

                    updateNavigationLabels(slick);
                });

                $slider.on('breakpoint', function (event, slick, breakpoint) {
                    // Update the counter encase the position has changed per breakpoint
                    updateCounter(slick);
                });

                var settings = $slider.data().cohSlider;

                settings.mobileFirst = cmm.getGridType() === cmm.constants.mobile;

                // Handle the dots if the user asked for the dots to be numbers otherwise just fallback to a style
                // We can't use === as twig sends back 0 || 1
                settings.customPaging = function (slider, i) {
                    return settings.dotsNumbers == true
                        ? $('<button type="button" />').text(i + 1)
                        : $('<button type="button" />');
                };

                if (typeof settings.asNavFor !== 'undefined' && settings.asNavFor !== null) {
                    settings.asNavFor = $(settings.asNavFor + ' .coh-slider-container-inner');
                }

                var responsive = [];
                var matchHeights = {};
                var matchHeightsInit = false;
                var previousResponsiveSettings = false;

                // Loop through the slick slider breakpoints.
                for (var i = 0; i < cmm.breakpoints.length; i++) {
                    var breakpointKey = cmm.breakpoints[i].key;
                    var breakpoint = settings.responsive[breakpointKey];

                    // Grab the match heights settings
                    if (
                        typeof breakpoint !== 'undefined' &&
                        typeof breakpoint.matchHeights !== 'undefined' &&
                        !Array.isArray(breakpoint.matchHeights)
                    ) {
                        matchHeights[breakpointKey] = settings.responsive[breakpointKey].matchHeights;

                        // Handle custom classes
                        if (typeof matchHeights[breakpointKey].class !== 'undefined') {
                            matchHeights[breakpointKey].target = matchHeights[breakpointKey].class;
                        }

                        matchHeightsInit = true;
                    }

                    // If the grid is set to desktop first then grab the desktop first settings and
                    // set them outside the responsive settings so they are default
                    if (cmm.getGridType() !== cmm.constants.mobile && breakpointKey === 'xl') {
                        // Move the current settings into global
                        if (typeof breakpoint.appendArrows !== 'undefined') {
                            breakpoint.appendArrows = $(breakpoint.appendArrows.trim() + ':first', $this);
                        }

                        // Move the current settings into global
                        if (typeof breakpoint.appendDots !== 'undefined') {
                            breakpoint.appendDots = $(breakpoint.appendDots.trim() + ':first', $this);
                        }

                        // Move the current settings into global
                        if (typeof breakpoint.appendCounter !== 'undefined') {
                            breakpoint.appendCounter = $(breakpoint.appendCounter.trim() + ':first', $this);
                        }

                        // Move the current settings into global
                        if (typeof breakpoint.appendPlaypause !== 'undefined') {
                            breakpoint.appendPlaypause = $(breakpoint.appendPlaypause.trim() + ':first', $this);
                        }

                        jQuery.extend(settings, breakpoint);
                    } else {
                        // Remove anything without any settings
                        if (!jQuery.isEmptyObject(breakpoint)) {
                            var responsive_obj = { settings: breakpoint };

                            // Update the slick slider object with the pixel value width.
                            if (typeof gridSettings.breakpoints[breakpointKey].width === 'undefined') {
                                // This must be the lowest breakpoint because it doesn't have a width defined
                                responsive_obj.breakpoint = 0;
                            } else {
                                // Otherwise grab the width from the responsive grid settings
                                if (cmm.getGridType() === cmm.constants.desktop) {
                                    // If the grid is desktop first we have to readd the +1 max-width fix because slick uses < rather than <=
                                    responsive_obj.breakpoint = cmm.getBreakpointMediaWidth(breakpointKey) + 1;
                                } else {
                                    responsive_obj.breakpoint = cmm.getBreakpointMediaWidth(breakpointKey);
                                }
                            }

                            // Handle `appendArrows` to include `this` otherwise if you have multiple sliders on the page it bubbles down
                            if (typeof breakpoint.appendArrows !== 'undefined') {
                                responsive_obj.settings.appendArrows = $(
                                    breakpoint.appendArrows.trim() + '',
                                    $this
                                );
                            }

                            if (typeof breakpoint.appendDots !== 'undefined') {
                                responsive_obj.settings.appendDots = $(breakpoint.appendDots.trim() + '', $this);
                            }

                            if (typeof breakpoint.appendCounter !== 'undefined') {
                                responsive_obj.settings.appendCounter = $(
                                    breakpoint.appendCounter.trim() + '',
                                    $this
                                );
                            }

                            if (typeof breakpoint.appendPlaypause !== 'undefined') {
                                responsive_obj.settings.appendPlaypause = $(
                                    breakpoint.appendPlaypause.trim() + ':first',
                                    $this
                                );
                            }

                            if (previousResponsiveSettings !== false) {
                                responsive_obj.settings = $.extend(
                                    {},
                                    previousResponsiveSettings,
                                    responsive_obj.settings
                                );
                            }
                            previousResponsiveSettings = responsive_obj.settings;
                            responsive.push(responsive_obj);
                        }
                    }
                }

                // Set the rtl
                settings.rtl = document.dir === 'ltr' ? false : true;

                settings.responsive = responsive;

                // Init the slick slider.
                $slider.slick(settings);

                // Init match heights
                if (matchHeightsInit !== false && isMatchHeightEnabled) {
                    $slider.cohesionContainerMatchHeights({
                        excludeElements: ['slide'],
                        targets: matchHeights,
                        context: context,
                        expressionPrefixes: [
                            '.slick-list > .slick-track',
                            '.slick-list > .slick-track > .coh-slider-item',
                        ],
                        loaders: [
                            '.coh-slider-container > .coh-slider-container-mid > .coh-slider-container-inner img',
                            '.coh-slider-container > .coh-slider-container-mid > .coh-slider-container-inner frame',
                            '.coh-slider-container > .coh-slider-container-mid > .coh-slider-container-inner iframe',
                            '.coh-slider-container > .coh-slider-container-mid > .coh-slider-container-inner img',
                            '.coh-slider-container > .coh-slider-container-mid > .coh-slider-container-inner input[type="image"]',
                            '.coh-slider-container > .coh-slider-container-mid > .coh-slider-container-inner link',
                            '.coh-slider-container > .coh-slider-container-mid > .coh-slider-container-inner script',
                            '.coh-slider-container > .coh-slider-container-mid > .coh-slider-container-inner style',
                        ],
                    });
                }

                // Update match heights on change
                var breakpointOriginal = false;
                if (isMatchHeightEnabled) {
                    if ($.fn.matchHeight._groups.length > 0) {
                        $slider.on('breakpoint', function (event, slick, breakpoint) {
                            if (breakpointOriginal !== breakpoint) {
                                $.fn.matchHeight._update();

                                breakpointOriginal = breakpoint;
                            }
                        });
                    }
                }
            });
        },
    };
})(jQuery, Drupal, once, drupalSettings);
