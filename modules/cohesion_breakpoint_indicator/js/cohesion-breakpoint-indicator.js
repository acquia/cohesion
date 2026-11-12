(function ($, Drupal, drupalSettings) {

    'use strict';

    Drupal.behaviors.cohesionBreakpointIndicator = {
        attach: function (context, settings) {

            $('body').addClass('coh-breakpoint-indicator');

            $(document).ready(checkScreensize);

            var resizeTimer;

            $(window).on('resize', function (e) {
                if (resizeTimer) {
                    window.cancelAnimationFrame(resizeTimer);
                }
                resizeTimer = window.requestAnimationFrame(function() {
                    checkScreensize();
                });
            });

            function checkScreensize () {

                if (window.innerWidth >= drupalSettings.cohesion.responsive_grid_settings.breakpoints.xl.width) {
                    removeClasses();
                    $('body').addClass('coh-breakpoint-indicator-xl');
                }

                if (window.innerWidth >= drupalSettings.cohesion.responsive_grid_settings.breakpoints.lg.width && (window.innerWidth < drupalSettings.cohesion.responsive_grid_settings.breakpoints.xl.width)) {
                    removeClasses();
                    $('body').addClass('coh-breakpoint-indicator-lg');
                }

                if ((window.innerWidth >= drupalSettings.cohesion.responsive_grid_settings.breakpoints.md.width) && (window.innerWidth < drupalSettings.cohesion.responsive_grid_settings.breakpoints.lg.width)) {
                    removeClasses();
                    $('body').addClass('coh-breakpoint-indicator-md');
                }

                if ((window.innerWidth >= drupalSettings.cohesion.responsive_grid_settings.breakpoints.sm.width) && (window.innerWidth < drupalSettings.cohesion.responsive_grid_settings.breakpoints.md.width)) {
                    removeClasses();
                    $('body').addClass('coh-breakpoint-indicator-sm');
                }

                if ((window.innerWidth >= drupalSettings.cohesion.responsive_grid_settings.breakpoints.ps.width) && (window.innerWidth < drupalSettings.cohesion.responsive_grid_settings.breakpoints.sm.width)) {
                    removeClasses();
                    $('body').addClass('coh-breakpoint-indicator-ps');
                }

                if ((window.innerWidth < drupalSettings.cohesion.responsive_grid_settings.breakpoints.ps.width)) {
                    removeClasses();
                    $('body').addClass('coh-breakpoint-indicator-xs');
                }
            }

            function removeClasses () {
                $('body').removeClass('coh-breakpoint-indicator-xl coh-breakpoint-indicator-lg coh-breakpoint-indicator-md coh-breakpoint-indicator-sm coh-breakpoint-indicator-ps coh-breakpoint-indicator-xs');
            }
        }
    };

})(jQuery, Drupal, drupalSettings);
