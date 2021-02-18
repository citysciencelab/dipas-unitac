<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\Controls;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a control plugin implementation for the Orientation property.
 *
 * @ControlPlugin(
 *   id = "Orientation",
 *   title = @Translation("Utilize user location"),
 *   description = @Translation("Defines if the map should focus on the user location or not."),
 *   category = "utility",
 *   configProperty = "orientation"
 * )
 */
class Orientation extends ControlPluginBase {

  /**
   * Which operating mode should be used when accessing the user's location?
   *
   * @var string
   */
  protected $zoomMode;

  /**
   * Flag indicating to use the default POI distances (unconfigurable).
   *
   * @var bool
   */
  protected $poiDistances;

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'zoomMode' => 'none',
      'poiDistances' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(FormStateInterface $form_state, $dependantSelector = FALSE, $dependantSelectorProperty = NULL, $dependantSelectorValue = NULL) {
    return [
      'zoomMode' => [
        '#type' => 'select',
        '#title' => $this->t('Use the user\'s location.', [], ['context' => 'Masterportal']),
        '#description' => $this->t('Should the map focus on the user\'s location?', [], ['context' => 'Masterportal']),
        '#options' => [
          'none' => $this->t('Do not use the location', [], ['context' => 'Masterportal']),
          'once' => $this->t('Focus the location on startup', [], ['context' => 'Masterportal']),
          'always' => $this->t('Always focus on the location', [], ['context' => 'Masterportal']),
        ],
        '#default_value' => $this->zoomMode,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurationArray(FormStateInterface $form_state) {
    return [
      'zoomMode' => $this->zoomMode,
      'poiDistances' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function injectConfiguration(\stdClass &$pluginSection) {
    $pluginSection->zoomMode = $this->zoomMode;
    $pluginSection->poiDistances = $this->poiDistances;
  }

}
