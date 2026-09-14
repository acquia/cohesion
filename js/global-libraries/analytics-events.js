(function ($, Drupal, once) {
  "use strict";

  Drupal.behaviors.AnalyticsEvents = {
    attach: function (context, settings) {

        function fireEvent(type) {
            // GA is enabled, so carry on and create the events got all matching elements.
            $(once('coh-js-analytics-init', '[data-analytics]')).each(function () {

                // Save the element reference.
                var element = $(this);

                // Decode the analytics JSON from the data attribute.
                var events = JSON.parse($(this).attr('data-analytics'));

                // Loop through this attribute object, creating an event for each entry.
                events.forEach(function (event) {

                    event.hitType = 'event';    // Populate the GA data object.
                    element.bind(event.trigger, function (e, inView) {

                        // inview trigger fired, but element left the viewport.
                        if (e.type === 'inview' && !inView) {
                            return;
                        }

                        // Fire the GA event according to: https://developers.google.com/analytics/devguides/collection/analyticsjs/sending-hits
                        var trackerName;

                        try {
                            var trackers = type.getAll();
                            var firstTracker = trackers[0];

                            var activeKey;
                            if (typeof firstTracker.a !== 'undefined') {
                                activeKey = 'a';
                            }
                            if (typeof firstTracker.b !== 'undefined') {
                                activeKey = 'b';
                            }

                            trackerName = firstTracker[activeKey].data.values[":name"] + '.';

                            if (typeof event.trigger !== 'undefined') {
                                delete event.trigger;
                            }
                        }
                        catch (e) {
                            trackerName = '';
                        }

                        // Universal Analytics
                        if(type === 'gtag') {
                            gtag('event', event.eventAction, {
                                'event_category': event.eventCategory,
                                'event_label': event.eventLabel,
                                'event_value': event.eventValue
                            });
                        }

                        // Classic Analytics
                        if(type === 'ga') {
                            ga(trackerName + 'send', 'event', event);
                        }
                    });

                });

            });
        }

        // Check to see if gtag() function is available .
        if (typeof gtag !== 'undefined') {
            fireEvent('gtag');
        }

        // Check to see if ga() function is available.
        if (typeof ga === typeof Function) {
            fireEvent('ga');
        }

        // GA not available, so warn.
        else {
            console.warn('Google Analytics is not available, but GA events have been defined.');
        }
     }
  };

})(jQuery, Drupal, once);
