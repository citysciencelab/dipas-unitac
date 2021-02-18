/**
 * @license GPL-2.0-or-later
 * @file
 * Contains a sorting plugin to enable sorting of multivalue rows.
 *
 * @package Drupal\masterportal
 */

(function ($, Drupal, window) {

  'use strict';

  Drupal.behaviors.rowsort = {
    attach: function (context) {
      $('div.rowsort', context).each(function (index, layerContainer) {
        $('.multivalueRowWeight', layerContainer).hide();
        $(layerContainer).sortable({
          containment: $(layerContainer),
          update: function (event, ui) {
            var $container = $(ui.item[0]).parent();
            $('.sortableRow', $container).each(function (delta, row) {
              var $weightSelect = $('.multivalueRowWeight select', row);
              $weightSelect.val(delta);
            });
          }
        });
      });
    }
  };

}(jQuery, Drupal, window));
