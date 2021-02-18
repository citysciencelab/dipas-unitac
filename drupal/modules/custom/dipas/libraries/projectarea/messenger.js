/**
 * @license GPL-2.0-or-later
 */

(function ($, Drupal, drupalSettings, window) {

  'use strict';

  Drupal.DipasProjectareaSetting = {

    onMessage: function (event) {
      var $textarea = $('textarea[name="settings[ProjectArea][project_area]"]'),
          $button = $('input.button.initProjectArea'),
          $iframe = $('iframe[data-drupal-selector^="edit-settings-projectarea-masterportal-project-area"]');

      for (var type in event.data) {
        var stateHandlerMethod = "on" + type[0].toUpperCase() + type.substring(1);
        try {
          Drupal.DipasProjectareaSetting[stateHandlerMethod]($textarea, $button, $iframe, event.data[type]);
        } catch (e) {}
      }
    },

    onInitDrawTool: function ($textarea, $button, $iframe, data) {
      this.setDefaultValue($iframe.get(0), $textarea.val().length ? JSON.parse($textarea.val()) : {});
      $button.bind('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        this.startDraw($button, $iframe.get(0), $textarea);
      }.bind(this)).show();
    },

    onDrawEnd: function ($textarea, $button, $iframe, data) {
      var GeoJSON = JSON.parse(data),
          GeoData = GeoJSON.features.shift().geometry;

      $textarea.val(JSON.stringify(GeoData));
      $button.removeAttr('disabled');
    },

    startDraw: function ($button, iFrame, $textarea) {
      $button.attr('disabled', 'disabled');

      $textarea.val('');

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
