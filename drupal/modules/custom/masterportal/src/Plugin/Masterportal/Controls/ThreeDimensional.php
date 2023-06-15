<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\Controls;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a control plugin implementation for the 3D toggle button.
 *
 * @ControlPlugin(
 *   id = "3D",
 *   title = @Translation("3D"),
 *   description = @Translation("3D Control element."),
 *   category = "button",
 *   configProperty = "button3d"
 * )
 */
class ThreeDimensional extends ControlPluginBase {

  /**
   * Should the map be opened in 3D mode?
   *
   * @var bool
   */
  protected $startingMap3D;

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'startingMap3D' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(FormStateInterface $form_state, $dependantSelector = FALSE, $dependantSelectorProperty = NULL, $dependantSelectorValue = NULL) {
    return [
      'startingMap3D' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Should the map be started in 3D initially?', [], ['context' => 'Masterportal']),
        '#default_value' => isset($this->startingMap3D) ? $this->startingMap3D : FALSE,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurationArray(FormStateInterface $form_state) {
    return [
      'startingMap3D' => (boolean) $this->startingMap3D,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function hasJavascriptConfiguration() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function injectConfiguration(\stdClass &$pluginSection) {
    $pluginSection = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function injectJavascriptConfiguration(\stdClass &$config) {
    $config->startingMap3D = (boolean) $this->startingMap3D;
  }
}
