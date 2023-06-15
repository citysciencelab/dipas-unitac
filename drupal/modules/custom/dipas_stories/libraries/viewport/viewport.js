(function ($, Drupal, window) {

  'use strict';

  Drupal.MasterportalMapViewport = {

    data: {},

    initialize (fieldname) {
      if (Drupal.MasterportalMapViewport.data[fieldname] === undefined) {
        Drupal.MasterportalMapViewport.data[fieldname] = {};
        Drupal.MasterportalMapViewport.data[fieldname].$viewportMap = $('iframe.masterportal[data-fieldname="' + fieldname + '"]')
        Drupal.MasterportalMapViewport.data[fieldname].$center = $('.center[data-fieldname=' + fieldname + ']');
        Drupal.MasterportalMapViewport.data[fieldname].$zoomLevel = $('.zoomLevel[data-fieldname=' + fieldname + ']');
        Drupal.MasterportalMapViewport.data[fieldname].$cameraPosition = $('.cameraPosition[data-fieldname=' + fieldname + ']');
        Drupal.MasterportalMapViewport.data[fieldname].$cameraHeading = $('.cameraHeading[data-fieldname=' + fieldname + ']');
        Drupal.MasterportalMapViewport.data[fieldname].$cameraPitch = $('.cameraPitch[data-fieldname=' + fieldname + ']');
        Drupal.MasterportalMapViewport.data[fieldname].$threedimensional = $('input.mapFeature[type="checkbox"][data-fieldname=' + fieldname + ']');
        ['selectedLayers', 'visibleLayers'].forEach(layer => {
          ['Foreground', 'Background'].forEach(property => {
            Drupal.MasterportalMapViewport.data[fieldname]['$' + layer + property] =
              $('.' + layer + '[data-fieldname=' + fieldname + '][data-property="' + property + '"]');
          });
        });
      }
    },

    updateViewportMap (fieldname) {
      Drupal.MasterportalMapViewport.initialize(fieldname);
      if (Drupal.MasterportalMapViewport.data[fieldname].$viewportMap.length) {
        let iFrameSrc = drupalSettings.dipas_stories.previewMasterportalSrc,
          query = {},
          queryString = "";

        Object.entries({
          BackgroundLayer: Drupal.MasterportalMapViewport.data[fieldname].$selectedLayersBackground,
          ForegroundLayer: Drupal.MasterportalMapViewport.data[fieldname].$selectedLayersForeground,
          VisibleLayers: Drupal.MasterportalMapViewport.data[fieldname].$visibleLayersBackground
        }).forEach(([target, source]) => {
          query[target] = $(source).val();

          if (target === 'VisibleLayers') {
            if (Drupal.MasterportalMapViewport.data[fieldname].$visibleLayersForeground.val().length) {
              if (query[target].length) {
                query[target] += '/';
              }
              query[target] += Drupal.MasterportalMapViewport.data[fieldname].$visibleLayersForeground.val();
            }
          }
        });

        if ($(Drupal.MasterportalMapViewport.data[fieldname].$threedimensional).prop('checked')) {
          let cameraPosition = $(Drupal.MasterportalMapViewport.data[fieldname].$cameraPosition).val(),
            cameraHeading = $(Drupal.MasterportalMapViewport.data[fieldname].$cameraHeading).val(),
            cameraPitch = $(Drupal.MasterportalMapViewport.data[fieldname].$cameraPitch).val();

          query['Map/MapMode'] = '3D';

          if (cameraPosition.length > 2) {
            query.cameraPosition = cameraPosition;
          }

          if (cameraHeading.length) {
            query.cameraHeading = cameraHeading;
          }

          if (cameraPitch.length) {
            query.cameraPitch = cameraPitch;
          }
        }
        else {
          query.center = JSON.parse($(Drupal.MasterportalMapViewport.data[fieldname].$center).val()).join(',');
          query.zoomLevel = $(Drupal.MasterportalMapViewport.data[fieldname].$zoomLevel).val();
        }

        queryString = Object.entries(query).map(([key, value]) => key + '=' + value).join('&');

        Drupal.MasterportalMapViewport.data[fieldname].$viewportMap.attr(
          'src',
          iFrameSrc + (iFrameSrc.indexOf('?') > -1 ? '&' : '?') + queryString
        );
      }
    },

    /**
     * Central hub method to catch and process message events from the
     * Masterportal iFrame. Calls subsequently specialized onEventName
     * methods below based on the message type received.
     *
     * @param event
     */
    onStateChange: function(event) {
      const sourceFrame = event.currentTarget[0].frameElement,
        fieldname = $(sourceFrame).attr('data-fieldname');

      for (var type in event.data) {
        var stateHandlerMethod = "on" + type[0].toUpperCase() + type.substring(1);

        try {
          Drupal.MasterportalMapViewport[stateHandlerMethod](fieldname, event.data[type]);
        } catch (e) {}
      }
    },

    /**
     * Specialized message event handler method called by onStateChange.
     *
     * @param fieldname
     * @param coords
     */
    onCenterPosition: function(fieldname, coords) {
      $('input[type="text"][name="' + fieldname + '[0][ViewpointConfiguration][startCenter]"]').val(
        '[' + Math.round(coords[0]) + ',' + Math.round(coords[1]) + ']'
      );
    },

    /**
     * Specialized message event handler method called by onStateChange.
     *
     * @param fieldname
     * @param zoomLevel
     */
    onZoomLevel: function(fieldname, zoomLevel) {
      $('input[type="text"][name*="' + fieldname + '[0][ViewpointConfiguration][zoomLevel]"]').val(zoomLevel);
    },

    /**
     * Specialized message event handler method called by onStateChange.
     *
     * @param fieldname
     * @param position
     */
    onCameraPosition: function (fieldname, position) {
      $('input[type="text"][name="' + fieldname + '[0][ViewpointConfiguration][cameraPosition]"]').val(
        JSON.stringify(position)
      );
    },

    /**
     * Specialized message event handler method called by onStateChange.
     *
     * @param fieldname
     * @param heading
     */
    onCameraHeading: function (fieldname, heading) {
      $('input[type="text"][name="' + fieldname + '[0][ViewpointConfiguration][cameraHeading]"]').val(heading);
    },

    /**
     * Specialized message event handler method called by onStateChange.
     *
     * @param fieldname
     * @param pitch
     */
    onCameraPitch: function (fieldname, pitch) {
      $('input[type="text"][name="' + fieldname + '[0][ViewpointConfiguration][cameraPitch]"]').val(pitch);
    }

  };

  Drupal.behaviors.MasterportalMapViewport = {
    attach: function (context) {
      // Add an event listener to listen for Map state changes.
      window.addEventListener(
        "message",
        Drupal.MasterportalMapViewport.onStateChange.bind(this)
      );
    }
  };

}(jQuery, Drupal, window));
