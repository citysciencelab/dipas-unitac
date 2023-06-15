<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\Tools;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\masterportal\Plugin\Masterportal\PluginBase;

/**
 * Defines a tool plugin implementation for QR codes.
 *
 * @ToolPlugin(
 *   id = "QR",
 *   title = @Translation("QR Code"),
 *   description = @Translation("A plugin that allows the placement of qr-codes"),
 *   configProperty = "qr",
 *   isAddon = true
 * )
 */
class QR extends PluginBase {

  public static function getDefaults() {
    return [
      'name' => 'QR-Code',
      'text' => \Drupal::service('string_translation')->translate('Tap on the map to generate a QR code leading to the contribution wizard.',
        [], ['context' => 'Masterportal']),
      'url' => '',
      'projection' => 'EPSG:4326',
    ];
  }
  public function getForm(
    FormStateInterface $form_state,
    $dependantSelector = FALSE,
    $dependantSelectorProperty = NULL,
    $dependantSelectorValue = NULL
  ) {
    $states = [
      'required' => [$dependantSelector => [$dependantSelectorProperty => $dependantSelectorValue]],
    ];
    return [
      'name' => [
        '#type' => 'textfield',
        '#title' => $this->t('Name'),
        '#default_value' => $this->name,
        '#states' => $states,
      ],
      'text' => [
        '#type' => 'textfield',
        '#title' => $this->t('Tool Text'),
        '#default_value' => $this->text,
        '#states' => $states,
      ],
      'url' => [
        '#type' => 'textfield',
        '#title' => $this->t('URL'),
        '#default_value' => $this->url,
        '#description' => $this->t('The URL pointing to the DIPAS contribution wizard without GET parameters',
          [], ['context' => 'Masterportal']),
        '#states' => $states,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurationArray(FormStateInterface $form_state) {
    return [
      'name' => $this->name,
      'text' => $this->text,
      'url' => $this->url,
      'projection' => $this->projection,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function injectConfiguration(\stdClass &$pluginSection) {
    $pluginSection->name = $this->name;
    $pluginSection->text = $this->text;
    $pluginSection->urlSchema = "$this->url?lat={{LAT}}&lon={{LON}}";
    $pluginSection->projection = $this->projection;
  }

}
