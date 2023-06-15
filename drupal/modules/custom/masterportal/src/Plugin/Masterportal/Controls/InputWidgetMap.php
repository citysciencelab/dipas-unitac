<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\Controls;

use Drupal\Core\Form\FormStateInterface;
use Drupal\masterportal\Annotation\ControlPlugin;

/**
 * Defines a control plugin implementation for the isInputMap property.
 *
 * @ControlPlugin(
 *   id = "InputWidgetMap",
 *   title = @Translation("Use the Masterportal as an input interface"),
 *   description = @Translation("Configures the actual instance in a way that it can be used as an input interface."),
 *   category = "utility",
 *   configProperty = "isInputMap"
 * )
 */
class InputWidgetMap extends ControlPluginBase {

  /**
   * The target map projection to be used.
   *
   * @var String
   */
  protected $targetProjection;

  /**
   * Should setMarker be used?
   *
   * @var bool
   */
  protected $setMarker;

  /**
   * Should the map automatically re-center itself around a marker placed?
   *
   * @var bool
   */
  protected $setCenter;

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'targetProjection' => 'EPSG:4326',
      'setMarker' => FALSE,
      'setCenter' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(FormStateInterface $form_state, $dependantSelector = FALSE, $dependantSelectorProperty = NULL, $dependantSelectorValue = NULL) {
    $projectionOptions = array_map(
      function ($projection) {
        return $projection['name'];
      },
      $this->masterportalConfigService->get('projections')
    );

    return [
      'targetProjection' => [
        '#type' => 'select',
        '#title' => $this->t('Target map projection', [], ['context' => 'Masterportal']),
        '#description' => $this->t('The projection that the coordinates should utilize when being transferred from the Masterportal', [], ['context' => 'Masterportal']),
        '#default_value' => $this->targetProjection,
        '#options' => array_combine($projectionOptions, $projectionOptions),
        '#required' => TRUE,
      ],
      'setMarker' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Use setMarker?', [], ['context' => 'Masterportal']),
        '#description' => $this->t('If this Masterportal is used in conjunction with a Geofield, check this option.', [], ['context' => 'Masterportal']),
        '#default_value' => $this->setMarker,
      ],
      'setCenter' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Center the map around a marker set', [], ['context' => 'Masterportal']),
        '#description' => $this->t('If set, the map will automatically re-center itself around a marker placed on the map.', [], ['context' => 'Masterportal']),
        '#default_value' => $this->setCenter,
        '#states' => [
          'invisible' => ['input[name=settings\[PortalSettings\]\[details_InputWidgetMap\]\[pluginsettings\]\[setMarker\]]' => ['checked' => FALSE]],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function hasJsonConfiguration() {
    return FALSE;
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
  public function getConfigurationArray(FormStateInterface $form_state) {
    // We just need to let the config section plugin
    // know that we do our integration ourselves.
    return [
      'setMarker' => (bool) $this->setMarker,
      'setCenter' => (bool) $this->setCenter,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function injectJavascriptConfiguration(\stdClass &$config) {
    $config->inputMap = (object) [
      'targetProjection' => $this->targetProjection,
      'setCenter' => $this->setCenter,
      'setMarker' => $this->setMarker,
    ];
  }

}
