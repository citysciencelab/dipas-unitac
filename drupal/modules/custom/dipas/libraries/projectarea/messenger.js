/**
 * @license GPL-2.0-or-later
 */

(function ($, Drupal, drupalSettings, window) {

  'use strict';

  Drupal.DipasProjectareaSetting = {

    onMessage: function (event) {
      var $textarea_project_area = $('textarea[name="settings[ProjectArea][project_area]"]'),
          $textarea_centerpoint = $('input[name="settings[ProjectArea][project_area_centerpoint]"]'),
          $button = $('input.button.initProjectArea'),
          $iframe = $('iframe[data-drupal-selector^="edit-settings-projectarea-masterportal-project-area"]');

      for (var type in event.data) {
        var stateHandlerMethod = "on" + type[0].toUpperCase() + type.substring(1);
        try {
          Drupal.DipasProjectareaSetting[stateHandlerMethod]($textarea_project_area, $textarea_centerpoint, $button, $iframe, event.data[type]);
        } catch (e) {}
      }
    },

    onInitDrawTool: function ($textarea_project_area, $textarea_centerpoint, $button, $iframe, data) {
      this.setDefaultValue($iframe.get(0), $textarea_project_area.val().length ? JSON.parse($textarea_project_area.val()) : {});
      $button.bind('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        this.startDraw($button, $iframe.get(0), $textarea_project_area);
      }.bind(this)).show();
    },

    onDrawEnd: function ($textarea_project_area, $textarea_centerpoint, $button, $iframe, data) {
      var GeoJSON = JSON.parse(data),
          GeoData = GeoJSON.features.shift();

      $textarea_project_area.val(JSON.stringify(GeoData.geometry));
      $textarea_centerpoint.val(GeoData.properties.centerPoint.coordinates.join(', '));
      $button.removeAttr('disabled');
    },

    startDraw: function ($button, iFrame, $textarea_project_area) {
      $button.attr('disabled', 'disabled');

      $textarea_project_area.val('');

      this.radioMasterportal(
        iFrame,
        'Draw',
        'deleteAllFeatures',
        {}
      );

      this.radioMasterportal(
        iFrame,
        'Draw',
        'initWithoutGUI',
        {
          drawType: 'Polygon',
          opacity: 0.5
        }
      );
    },

    radioMasterportal: function (iFrame, channel, func, data = {}) {
      iFrame.contentWindow.postMessage({
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
          drawType: defaultValue.type,
          opacity: 0.5,
          transformWGS: true,
          initialJSON: {
            type: "FeatureCollection",
            features: [
              {
                type: "Feature",
                geometry: defaultValue,
                properties: {}
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
    }

  };

  Drupal.behaviors.messenger = {
    attach: function (context) {
      window.addEventListener(
        "message",
        Drupal.DipasProjectareaSetting.onMessage
      );
    }
  };

}(jQuery, Drupal, drupalSettings, window));
