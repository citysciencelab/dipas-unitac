<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\SearchBar;

use Drupal\Core\Form\FormStateInterface;
use Drupal\masterportal\Plugin\Masterportal\PluginBase;
use Drupal\masterportal\PluginSystem\SearchBarPluginInterface;

/**
 * Defines a SearchBar plugin implementation for Gazetteer.
 *
 * @SearchBarPlugin(
 *   id = "VisibleVector",
 *   title = @Translation("visibleVector"),
 *   description = @Translation("A search bar plugin to utilize a visibleVector search."),
 *   configProperty = "visibleVector"
 * )
 */
class VisibleVector extends PluginBase implements SearchBarPluginInterface {

  /**
   * The layer types to search.
   *
   * @var array
   */
  protected $layerTypes;

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'layerTypes' => ['GeoJSON'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(FormStateInterface $form_state, $dependantSelector = FALSE, $dependantSelectorProperty = NULL, $dependantSelectorValue = NULL) {
    return [
      'layerTypes' => [
        '#type' => 'checkboxes',
        '#title' => $this->t('Layer types', [], ['context' => 'Masterportal']),
        '#options' => [
          'GeoJSON' => $this->t('GeoJSON', [], ['context' => 'Masterportal']),
          'WFS' => $this->t('WFS', [], ['context' => 'Masterportal']),
        ],
        '#default_value' => $this->layerTypes,
        '#states' => [
          'required' => [$dependantSelector => [$dependantSelectorProperty => $dependantSelectorValue]],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurationArray(FormStateInterface $form_state) {
    return [
      'layerTypes' => array_keys(array_filter($this->layerTypes)),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function injectConfiguration(\stdClass &$pluginSection) {
    $pluginSection->layerTypes = $this->layerTypes;
  }

}
