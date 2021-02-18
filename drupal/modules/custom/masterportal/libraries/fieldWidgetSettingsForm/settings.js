/**
 * @license GPL-2.0-or-later
 */

(function ($, Drupal) {

  'use strict';

  Drupal.fieldWidgetSettingsForm = {
    widthUnitClickHandler: function (event) {
      var $radio = $(event.currentTarget),
          $settingsForm = $radio.parents().find('.field-plugin-settings-edit-form'),
          $widthValueElement = $('input[type="number"].mapWidthValue', $settingsForm);

      switch ($radio.val()) {
        case '%':
          $widthValueElement.attr('max', 100);
          if ($widthValueElement.val() > 100) {
            $widthValueElement.val(100);
          }
          break;

        case 'px':
          $widthValueElement.attr('max', function () {
            return $(this).attr('data-max');
          });
          break;
      }
    }
  };

  Drupal.behaviors.fieldWidgetSettingsForm = {
    attach: function (context) {
      $('input[type="number"].mapWidthValue', context)
        .once('initNumberField')
        .attr('data-max', function () {
          return $(this).attr('max');
        })
        .bind('change, keyup', function (event) {
          var $elem = $(event.currentTarget),
              min = $elem.attr('min'),
              max = $elem.attr('max');

          if ($elem.val() > max) {
            $elem.val(max);
          }
          else if ($elem.val() < min) {
            $elem.val(min);
          }
        });
      $('input[type="radio"].mapWidthUnit', context)
        .once('clickHandler')
        .bind('click', Drupal.fieldWidgetSettingsForm.widthUnitClickHandler);
      $('input[type="radio"].mapWidthUnit:checked', context).click();
    }
  };

}(jQuery, Drupal));
