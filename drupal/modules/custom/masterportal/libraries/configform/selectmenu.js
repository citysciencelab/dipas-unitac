/**
 * @license GPL-2.0-or-later
 * @file
 * Transforms all .selectmenu items with the jQuery UI plugin.
 *
 * @package Drupal\masterportal
 */

(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.selectmenu = {
    attach: function (context) {
      $.widget(
        'custom.iconselectmenu',
        $.ui.selectmenu,
        {
          _renderItem: function (container, item) {
            var li = $('<li>'),
                wrapper = $('<div>', { text: item.label });

            if (item.disabled) {
              li.addClass('ui-state-disabled');
            }

            $('<span>', {
              'class': 'glyphicon ' + item.element.attr('data-class')
            }).appendTo(wrapper);

            return li.append(wrapper).appendTo(container);
          }
        }
      );

      $('select.selectmenu', context).each(function (index, select) {
        $(select).iconselectmenu().addClass('ui-menu-icons customicons');
      });
    }
  };

}(jQuery, Drupal));
