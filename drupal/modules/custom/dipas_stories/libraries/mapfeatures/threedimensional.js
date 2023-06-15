(function ($, Drupal, window) {

  'use strict';

  Drupal.behaviors.MasterportalMapFeatureThreeDimensional = {
    attach: function (context) {
      $('input.mapFeature[type="checkbox"]').bind('click', event => {
        let cb = event.currentTarget,
          fieldname = $(cb).attr('data-fieldname');

        try {
          Drupal.MasterportalMapViewport.updateViewportMap(fieldname);
        } catch (e) {}
      })
    }
  };

}(jQuery, Drupal, window));
