/**
 * @license GPL-2.0-or-later
 * @file
 * Contains a small script that toggles the display of hidden items on the list builder page.
 *
 * @package Drupal\masterportal
 */

(function ($, Drupal, window) {

  'use strict';

  Drupal.behaviors.showHidden = {
    attach: function (context) {
      $('input[type="checkbox"]#showHidden', context).bind('click', function (event) {
        var $checkbox = $(event.target);
        var url = $checkbox.data('url');
        url += $checkbox.is(':checked') ? '?showHidden' : '';
        window.location = url;
      });
    }
  };

}(jQuery, Drupal, window));
