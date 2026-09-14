(function($, once) {
  "use strict";

  Drupal.behaviors.CohesionGoogleMap = {
    attach: function(context, settings) {
        // Only initialize if Google Maps API is available
        if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
          return;
        }

        var elements = $('.js-coh-map-container', context).filter(':visible');

        $.each($(once('coh-js-googlemap-init', elements)), function(i, e) {

        var $this = $(this);

        var settings = $(this).data().settings;
        var styles = $(this).data().styles;

        $('.js-coh-map-marker', $this).each(function() {
          var pin_data = $(this).data();
          var setting = {
            'latlong': pin_data.mapMarkerLatlong,
            'marker_image': pin_data.mapMarkerImage,
            'link': pin_data.mapMarkerLink,
            'label': pin_data.mapMarkerLabel,
            'wysiwyg_info_window': pin_data.mapMarkerInfo,
            'marker_scaled_x': pin_data.mapMarkerScaledX,
            'marker_scaled_y': pin_data.mapMarkerScaledY,
            'info_window_class': pin_data.infoWindowClass
          };

          // Split the lat and lng ready for google
          if (typeof setting.latlong === 'string' && setting.latlong.indexOf(',') !== -1) {
            setting.lat = setting.latlong.split(',')[0];
            setting.lng = setting.latlong.split(',')[1];
          } else {
            setting.lat = setting.lng = ''
          }

          settings.pins.push(setting);
        });

        if ((typeof settings.pins !== 'undefined' && settings.pins.length > 0) || (typeof settings.latlong !== 'undefined' && settings.latlong !== '')) {

          // Set map options
          var map = new google.maps.Map(e, getMapOptions(settings, styles));

          // Set map bounds
          var bounds = new google.maps.LatLngBounds();

          // Set Info markers and info windows
          var infowindow = new google.maps.InfoWindow({
            maxWidth: $('.js-coh-map-container:visible', context).width() - 60
          });

          for (var i = 0; i < settings.pins.length; i++) {
            /**
                             * Set markers
                             */
            var marker = new google.maps.Marker(getMarkerOptions(map, settings.pins[i], settings, settings.marker_animation, settings.pins.length - i));

            bounds.extend(marker.position);
            /**
                             * Set info windows
                             */

            if (settings.pins[i].wysiwyg_info_window && settings.pins[i].info_window_class) {
              settings.pins[i].wysiwyg_info_window  = '<div class="' + settings.pins[i].info_window_class + '">' + settings.pins[i].wysiwyg_info_window + '</div>';
            }

            google.maps.event.addListener(marker, 'click', (function(marker, i) {
              return function() {
                if (typeof settings.pins[i].link !== "undefined" && settings.pins[i].link !== '') {
                  window.open(settings.pins[i].link, unescape(settings.pins[i].open_in));
                } else if (typeof settings.pins[i].wysiwyg_info_window !== "undefined" && settings.pins[i].wysiwyg_info_window !== '') {
                  // Ensure the infowindow fits inside the map bounds. -60 gives room for the padding and close button on the info window.
                  infowindow.maxWidth = $('.js-coh-map-container:visible', context).width() - 60;
                  infowindow.setContent(settings.pins[i].wysiwyg_info_window);
                  infowindow.open(map, marker);
                }
              }
            })(marker, i));
          }

          // For multiple pins zoom to pin bounds, or configured zoom if bound zoom is greater
          if (settings.pins.length > 1) {
            map.fitBounds(bounds);
            var baseZoom = parseInt(settings.zoom);
            var listener = google.maps.event.addListener(map, "idle", function() {
              if (map.getZoom() > baseZoom) map.setZoom(baseZoom);
              google.maps.event.removeListener(listener);
            });
          }

          // On resize, keep map centered on pin/s
          var currentCenter = map.getCenter();
          var resizeTimer;
          window.onresize = function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
              google.maps.event.trigger(map, 'resize');
              if (settings.pins.length === 1) {
                map.setCenter(currentCenter);
              }
            }, 100);
          };
        }
      });
    }
  };

  /**
     * @summary Get map type compared to Chameleon Drupal values
     * @param {type} settings
     * @returns {google.maps.MapTypeId}
     */
  function getMapType(settings) {

    switch(settings.type) {

      case 'default':

        return google.maps.MapTypeId.ROADMAP;
        break;

      case 'satellite':

        return google.maps.MapTypeId.SATELLITE;
        break;

      case 'hybrid':

        return google.maps.MapTypeId.HYBRID;
        break;

      case 'terrain':

        return google.maps.MapTypeId.TERRAIN;
        break;

      default:

        return google.maps.MapTypeId.ROADMAP;
        break;
    }
  }

  /**
     * @summary Get the map options
     * @param {type} settings
     * @returns {$return}
     */
  function getMapOptions(settings, styles) {
    var $return = {};

    $return = {
      zoom: parseInt(settings.zoom),
      mapTypeId: getMapType(settings),
      scrollwheel: getBoolean(settings.scrollwheel),
      mapTypeControl: getBoolean(settings.maptypecontrol),
      draggable: getBoolean(settings.draggable),
      zoomControl: getBoolean(settings.zoomcontrol),
      scaleControl: getBoolean(settings.scalecontrol)
    };

    // Set the center (container as priority)
    if (typeof settings.latlong !== 'undefined' && settings.latlong !== '')  {

      $return.center = new google.maps.LatLng(
        settings.latlong.split(',')[0],
        settings.latlong.split(',')[1]
      );

    } else {

      $return.center = new google.maps.LatLng(
        settings.pins[0].lat,
        settings.pins[0].lng
      )
    }

    if (typeof styles !== "undefined") {
      $return.styles = styles;
    }

    return $return;
  }

  /**
     * @summary Get the marker options
     * @param {type} map
     * @param {type} settings
     * @param {type} defaultPin
     * @returns {$return}
     */
  function getMarkerOptions(map, settings, defaultSettings, marker_animation, zIndex) {

    var $return = {};

    $return = {
      position: new google.maps.LatLng(
        settings.lat,
        settings.lng
      ),
      optimized: false,
      zIndex: zIndex,
      map: map
    };

    if (typeof settings.label !== "undefined") {
      $return.label = settings.label;
    }

    if (typeof settings.marker_image !== "undefined") {
      var image = {
        url: settings.marker_image
      };

      if (typeof settings.marker_scaled_x !== "undefined" && typeof settings.marker_scaled_y !== "undefined") {
        image.scaledSize = new google.maps.Size(settings.marker_scaled_x, settings.marker_scaled_y);
      }
      $return.icon = image;

    } else if (typeof defaultSettings.marker_image !== "undefined") {
      var image = {
        url: defaultSettings.marker_image

      };

      if (typeof defaultSettings.marker_scaled_x !== "undefined" && typeof defaultSettings.marker_scaled_y !== "undefined") {
        image.scaledSize = new google.maps.Size(defaultSettings.marker_scaled_x, defaultSettings.marker_scaled_y);
      }
      $return.icon = image;
    }

    if (typeof marker_animation !== "undefined") {
      if (marker_animation === 'drop') {
        $return.animation = google.maps.Animation.DROP;
      } else if (marker_animation === 'bounce') {
        $return.animation = google.maps.Animation.BOUNCE;
      }
    }

    return $return;
  }

  /**
     * @summary parse string to html
     * @param {type} input
     * @returns html object
     */
  function htmlDecode(input) {
    var e = document.createElement('div');
    e.innerHTML = input;
    return e.childNodes.length === 0 ? "" : e.childNodes[0].nodeValue;
  }

  /**
     * @summary parse string to bool
     * @param {string} string
     * @returns {bool} true || false
     */
  function getBoolean(string) {
    try {
      JSON.parse(string);
    } catch (e) {
      return false;
    }
  }

})(jQuery, once);
