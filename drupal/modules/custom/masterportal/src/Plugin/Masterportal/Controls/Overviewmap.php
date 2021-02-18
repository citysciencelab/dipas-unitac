<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\Controls;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a control plugin implementation for the Overviewmap property.
 *
 * @ControlPlugin(
 *   id = "Overviewmap",
 *   title = @Translation("Display overview map"),
 *   description = @Translation("Defines if an overview map should be shown or not."),
 *   category = "display",
 *   configProperty = "overviewMap"
 * )
 */
class Overviewmap extends ControlPluginBase {

  /**
   * Which resolution should be used for the overviewmap?
   *
   * @var string
   */
  protected $resolution;

  /**
   * LayerID to be used for the overviewmap
   *
   * @var string
   */
  protected $layerId;

  /**
   * Should the overviewmap be shown initialy on the map?
   *
   * @var bool
   */
  protected $isInitOpen;

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'resolution' => 400,
      'layerId' => "453",
      'isInitOpen' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(FormStateInterface $form_state, $dependantSelector = FALSE, $dependantSelectorProperty = NULL, $dependantSelectorValue = NULL) {
    $states = [
      'required' => [$dependantSelector => [$dependantSelectorProperty => $dependantSelectorValue]],
    ];
    return [
      'resolution' => [
        '#type' => 'number',
        '#title' => $this->t('Resolution of the overviewmap.', [], ['context' => 'Masterportal']),
        '#default_value' => $this->resolution,
      ],
      'layerId' => [
        '#type' => 'textfield',
        '#title' => $this->t('Layer to be shown on the overviewmap.', [], ['context' => 'Masterportal']),
        '#default_value' => $this->layerId,
        '#states' => $states,
      ],
      'isInitOpen' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Should the overviewmap be shown initially?', [], ['context' => 'Masterportal']),
        '#default_value' => isset($this->isInitOpen) ? $this->isInitOpen : FALSE,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurationArray(FormStateInterface $form_state) {
    return [
      'resolution' => (int) $this->resolution,
      'layerId' => $this->layerId,
      'isInitOpen' => (boolean) $this->isInitOpen,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function injectConfiguration(\stdClass &$pluginSection) {
    $pluginSection->resolution = (int) $this->resolution;
    $pluginSection->layerId = $this->layerId;
    $pluginSection->isInitOpen = (boolean) $this->isInitOpen;
  }
}
