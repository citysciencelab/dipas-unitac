/**
 * @license GPL-2.0-or-later
 */

(function ($, Drupal, window) {

  'use strict';

  Drupal.MasterportalMessenger = {

    onStateChange: function(event) {
      for (var type in event.data) {
        var stateHandlerMethod = "on" + type[0].toUpperCase() + type.substring(1);
        try {
          Drupal.MasterportalMessenger[stateHandlerMethod](event.data[type]);
        } catch (e) {}
      }
    },

    onCenterPosition: function(coords) {
      $('input[type="text"][name="settings[MapSettings][startCenter]"]').val(
        '[' + Math.round(coords[0]) + ',' + Math.round(coords[1]) + ']'
      );
    },

    onZoomLevel: function(zoomLevel) {
      $('input[type="range"][name="settings[MapSettings][zoomLevel]"]').val(zoomLevel);
    }

  };

  Drupal.behaviors.messenger = {
    attach: function (context) {
      // Add an event listener to listen for Map state changes.
      window.addEventListener(
        "message",
        Drupal.MasterportalMessenger.onStateChange.bind(this)
      );
      // Hide the target input fields since they are filled automatically.
      $('input[type="text"][name="settings[MapSettings][startCenter]"]').parent().hide();
      $('input[type="range"][name="settings[MapSettings][zoomLevel]"]').parent().hide();
    }
  };

}(jQuery, Drupal, window));
