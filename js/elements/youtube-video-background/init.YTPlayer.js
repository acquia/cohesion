(function($, Drupal, once){
  "use strict";

  Drupal.behaviors.CohesionYoutubeBG = {

    attach: function (context, settings)    {

        var elements = $('.bgndVideo', context).filter(':visible');

        $(once('coh-js-youtube-init', elements)).each(function() {

        var options = $(this).data('property');

        //If there is a / assume it's a URL of some kind - we need just the ID.
        if (options.videoURL && typeof options.videoURL === 'string' && options.videoURL.indexOf('/') !== -1) {
          var p = /^.*(?:(?:youtu\.be\/|v\/|vi\/|u\/\w\/|embed\/)|(?:(?:watch)?\?v(?:i)?=|\&v(?:i)?=))([^#\&\?]*).*/;
          //extract the video id.
          var videoURL = options.videoURL.match(p);
          if (videoURL) {
            options.videoURL = videoURL[1];
          } else {
            options.videoURL = "";
          }
        }

        // Initialise the plugin
        var cohYtplayer = $(this).YTPlayer();

        if (options.stopMovieIfInvisible ) {
          // Only play the video when it becomes visible in the viewport otherwise pause
          $(cohYtplayer).bind('inview', function(event, visible) {
            if (visible === true) {
              $(cohYtplayer).YTPPlay();
            } else {
              $(cohYtplayer).YTPPause();
            }
          });
        }
      });
    },

    detach: function(context)   {

      $.each($('.bgndVideo', context), function() {
        $(this).YTPPause();
      });
    }
  };

})(jQuery, Drupal, once);
