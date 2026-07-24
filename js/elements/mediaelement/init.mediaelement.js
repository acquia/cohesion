(function ($, Drupal, once) {
    "use strict";

    Drupal.behaviors.DX8MediaElement = {
        attach: function (context) {
            // Ensure hidden elements do not get automatically run when in a hidden 'something' (only applicable to native HTML5 videos)
            $.each(
                $(
                    ".coh-video > .coh-video-inner:not(.mejs__container):hidden",
                    context
                ),
                function () {
                    // Handle Embeds (YouTube, Vimeo etc)
                    if ($(this)[0].player && $(this)[0].player.media) {
                        $(this)[0].player.media.pause();
                    }

                    // HTML5 players
                    this.pause();
                }
            );

            var elements = $('.coh-video > .coh-video-inner:not(.mejs__container)', context).filter(':visible');

            $(once("dx8-js-mediaelement-init", elements)).each(function () {
                    var $this = $(this);
                    var $videoContainer = $this.closest(".coh-video");
                    var options = $this.data("cohVideo");
                    var videoSrc = $this.attr("src");
                    var videoType = mejs.Utils.getTypeFromFile(videoSrc);

                    options.success = function (mediaElement) {
                        // add video renderer class to video container
                        $videoContainer.addClass(
                            "coh-video-" + mediaElement.rendererName
                        );

                        // hide center play button if toggled off in sidebar editor
                        if (options.showPlayCenter === false) {
                            $videoContainer.addClass(
                                "coh-video-hide-center-play"
                            );
                        }

                        // ensure center play button is still clickable if `clickToPlayPause` is disabled
                        if (options.clickToPlayPause === false) {
                            $videoContainer
                                .find(
                                    ".mejs__overlay-play .mejs__overlay-button"
                                )
                                .on("click", function () {
                                    mediaElement.play();
                                });
                        }

                        // add control hide class to video container if all controls toggled off in sidebar editor
                        if (!options.features.length) {
                            $videoContainer.addClass("coh-video-hide-controls");
                        }

                        function playVideoHover() {
                            $videoContainer.addClass("coh-video-hover");
                            mediaElement.play();
                        }

                        function pauseVideoHover() {
                            $videoContainer.removeClass("coh-video-hover");
                            mediaElement.pause();
                        }

                        // Play video on hover if setting enabled
                        if (options.playOnHover === true) {
                            $videoContainer.on("mouseenter", playVideoHover);
                            $videoContainer.on("mouseleave", pauseVideoHover);
                        }

                        $("body").on("tabs-activate", function (e) {
                            //If the tab contains this media element, fire a window resize event to ensure the video fills the tab.
                            if (e.target.contains(e.target)) {
                                window.dispatchEvent(new Event("resize"));
                            }
                        });

                        // Check the type to see if it'll be a Vimeo or YouTube embed.
                        const embedTypes = ["video/x-vimeo", "video/vimeo", "video/x-youtube", "video/youtube"];

                        if (embedTypes.includes(videoType)) {
                            // We only have access to the "video" element itself so the title is added as an attribute to there.
                            // And then we grab it and add it to the iframe that mediaelement creates for the embed.
                            const wrapper =
                                // Prefer the container provided by mediaelement if available.
                                (mediaElement && mediaElement.container)
                                    ? mediaElement.container
                                    // Fallback: find the mediaelement wrapper within this video's container.
                                    : $videoContainer.find('.mejs__mediaelement')[0];

                            if (wrapper) {
                                const iframe = wrapper.querySelector('iframe');
                                const videoElement = wrapper.querySelector('video');
                                const titleValue = videoElement && videoElement.getAttribute('title');

                                if (iframe && titleValue) {
                                    iframe.setAttribute('title', titleValue);
                                }
                            }
                        }
                    };

                    // If Vimeo embed, hide mediaelement controls
                    if (
                        videoType === "video/x-vimeo" ||
                        videoType === "video/vimeo"
                    ) {
                        options.features = [];
                        options.showPlayCenter = false;
                    }

                    // If YouTube embed, hide mediaelement controls
                    if (
                        videoType === "video/x-youtube" ||
                        videoType === "video/youtube"
                    ) {
                        options.features = [];
                        options.showPlayCenter = false;

                        // If URL doesn't contain `controls=0`, show native controls
                        if (videoSrc.indexOf("controls=0") === -1) {
                            options.youtube = {
                                controls: 1,
                            };
                        }

                        // If URL is a nocookie URL tell mediaelement to use nocookie domain.
                        if (videoSrc.includes("nocookie")) {
                            options.youtube = {
                                nocookie: 1,
                            };
                        }
                    }

                    $this.mediaelementplayer(options);
                });
        },

        detach: function (context, settings, cause) {
            // Ensure when the video is unloaded or hidden from it is paused
            $.each($("video", context), function () {
                // Handle Embeds (YouTube, Vimeo etc)
                if ($(this)[0].player && $(this)[0].player.media) {
                    $(this)[0].player.media.pause();
                }

                // Handle native HTML5 videos
                this.pause();
            });
        },
    };
})(jQuery, Drupal, once);
