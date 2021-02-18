/**
 * @license GPL-2.0-or-later
 */

(function ($, Drupal, drupalSettings, window) {

  'use strict';

  Drupal.DipasNLPBlock = {

    setFilter: function (event) {
      let block = $(event.currentTarget).parents().filter('div.dipas_nlp_block'),
        blockSettings = $(block).attr('data-blocksettings'),
        dataTarget = $('.body', block),
        url = drupalSettings[blockSettings].callbackUrl,
        query = [];

      $('.header select.onChangeFilter', block).each(function (index, element) {
        if (element.value) {
          query.push($(element).attr('data-field') + '=' + element.value);
        }
      });

      if (query.length) {
        url += '?' + query.join('&');
      }

      $(dataTarget).html($('<p/>').text('Processing...'));

      $.ajax({
        url,
        success: function (data) {
          Drupal.DipasNLPBlock.onResult(block, data);
        }
      });
    },

    onResult: function (block, data) {
      let blockSettings = $(block).attr('data-blocksettings'),
        dataType = drupalSettings[blockSettings].dataType,
        dataTarget = $('.body', block);

      switch (dataType) {

        case 'list':
          if (typeof data === 'object' && data instanceof Array) {
            var html = $('<ul>')
            if (data.length) {
              $.each(data, function (index, element) {
                $('<li/>').appendTo(html).text(element);
              });
            }
            else {
              $('<li/>').appendTo(html).text('No entries match the criteria');
            }
          }
          else {
            var html = $('<p/>').text(data.message);
          }
          dataTarget.html(html);
          break;

        case 'image':
          if (data.url !== undefined) {
            var html = $('<img/>').attr('src', data.url);
          }
          else {
            var html = $('<p/>').text(data.message);
          }

          dataTarget.html(html);
          break;
      }
    }

  };

  Drupal.behaviors.DipasNLPBlock = {
    attach: function (context) {
      $('div.dipas_nlp_block', context).each(function (index, block) {
        var settingsTarget = $(block).attr('data-blocksettings');
        $('.header select.onChangeFilter', block).bind('change', Drupal.DipasNLPBlock.setFilter);
        $.ajax({
          url: drupalSettings[settingsTarget].callbackUrl,
          success: function (data) {
            Drupal.DipasNLPBlock.onResult(block, data);
          }
        });
      });
    }
  };

}(jQuery, Drupal, drupalSettings, window));
