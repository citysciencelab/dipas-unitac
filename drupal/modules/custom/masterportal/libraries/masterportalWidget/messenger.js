/**
 * @license GPL-2.0-or-later
 */

(function ($, Drupal, drupalSettings, window) {

  'use strict';

  Drupal.MasterportalMessenger = {

    onMessage: function (event) {
      var $widget = $(event.source.frameElement).parents().filter('[class*="field--widget-masterportal-"]'),
          $widgetMap = $('fieldset.masterportalWidgetMap', $widget),
          fieldname = $widgetMap.attr('data-fieldname');

      for (var type in event.data) {
        var stateHandlerMethod = "on" + type[0].toUpperCase() + type.substring(1);
        try {
          Drupal.MasterportalMessenger[stateHandlerMethod](fieldname, $widget, event.data[type]);
        } catch (e) {}
      }
    },

    // "setMarker" is only used by MasterportalGeofieldWidget
    onSetMarker: function (fieldname, $widget, coords) {
      $('input[name="' + fieldname + '[0][value][lon]"]', $widget).val(coords[0]);
      $('input[name="' + fieldname + '[0][value][lat]"]', $widget).val(coords[1]);
    },

    onInitDrawTool: function (fieldname, $widget, data) {
      var $mapWrapper = $('fieldset.masterportalWidgetMap', $widget),
          iframe = $('iframe.masterportal', $mapWrapper).get(0).contentWindow,
          fieldtype = $mapWrapper.attr('data-fieldtype');

      if (fieldtype === "Textfield") {
        let defaultValue = typeof drupalSettings.masterportal[fieldname] !== "undefined"
          ? drupalSettings.masterportal[fieldname]
          : false;

        if (defaultValue !== false) {
          this.setDefaultValue(iframe, defaultValue);
        }

        $('input.geometryType', $widget).on(
          'click',
          (event) => this.geometryTypeClickHandler($widget, iframe, event.currentTarget.value)
        );
      }
    },

    onDrawEnd: function (fieldname, $widget, data) {
      var GeoJSON = JSON.parse(data),
          Feature = GeoJSON.features.shift(),
          GeoData = {
            geometry: Feature.geometry,
            centerPoint: Feature.properties.centerPoint
          };
      $('input.geometryType', $widget).prop('checked', false);
      $('textarea.masterportalWidget', $widget).val(JSON.stringify(GeoData));
    },

    radioMasterportal: function (iFrame, channel, func, data = {}) {
      iFrame.postMessage({
        radio_channel: channel,
        radio_function: func,
        radio_para_object: data
      });
    },

    setDefaultValue: function (iFrame, defaultValue) {
      this.radioMasterportal(
        iFrame,
        'Draw',
        'initWithoutGUI',
        {
          drawType: defaultValue.geometry.type,
          opacity: 0.5,
          transformWGS: true,
          initialJSON: {
            type: "FeatureCollection",
            features: [
              {
                type: "Feature",
                geometry: {
                  type: defaultValue.geometry.type,
                  coordinates: defaultValue.geometry.coordinates
                }
              }
            ]
          }
        }
      );

      this.radioMasterportal(
        iFrame,
        'Draw',
        'cancelDrawWithoutGUI',
        {
          cursor: true
        }
      );
    },

    geometryTypeClickHandler: function ($widget, iFrame, drawType) {
      $('textarea.masterportalWidget', $widget).val('');

      this.radioMasterportal(
        iFrame,
        'Draw',
        'deleteAllFeatures'
      );

      this.radioMasterportal(
        iFrame,
        'Draw',
        'initWithoutGUI',
        {
          drawType: drawType,
          opacity: 0.5
        }
      );
    }

  };

  Drupal.behaviors.messenger = {
    attach: function (context) {
      window.addEventListener(
        "message",
        Drupal.MasterportalMessenger.onMessage
      );
    }
  };

}(jQuery, Drupal, drupalSettings, window));
