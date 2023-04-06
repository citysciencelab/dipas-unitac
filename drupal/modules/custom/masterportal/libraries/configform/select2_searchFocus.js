/**
 * @license GPL-2.0-or-later
 * @file
 * Contains an automatic focus on select2 search fields when opening on Masterportal layer settings.
 *
 * @package Drupal\masterportal
 */

(function ($, Drupal, document) {

  'use strict';

  Drupal.behaviors.select2_searchFocus = {
    attach: function (context) {
      $(document).on('select2:open', () => {
        document.querySelector('.select2-search__field').focus();
      });
    }
  };

}(jQuery, Drupal, document));
