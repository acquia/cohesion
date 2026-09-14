(function ($, Drupal, once) {
    "use strict";

    Drupal.behaviors.CohesionVideoBG = {

        attach: function (context, settings) {

            $(once('coh-js-video-init', '.coh-video-background > .coh-video-background-inner', context)).each( function () {

                var $this = $(this);
                var options = $this.data('cohVideoBackground');

                // Fix for Edge to pause on initial load
                $this[0].pause();

                // Only play the video when it's visible in the viewport, otherwise pause
                if (options.pauseWhenHidden) {
                    $this.bind('inview', function (event, visible) {
                        if (visible === true) {
                            $this[0].play();
                        } else {
                            $this[0].pause();
                        }
                    });
                }

                // On touch, replace video src with poster image
                if (options.disableOnTouch) {
                    if ("ontouchstart" in document.documentElement) {
                        var videoPoster = $this.attr('poster');
                        $this.attr('src', videoPoster);
                    }
                }
            });
        },

        detach: function(context)  {

            $.each($('.coh-video-background > .coh-video-background-inner', context), function() {
                $(this)[0].pause();
            });
        }
    };

})(jQuery, Drupal, once);
