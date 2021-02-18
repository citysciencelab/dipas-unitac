<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\SearchBar;

use Drupal\Core\Form\FormStateInterface;
use Drupal\masterportal\Plugin\Masterportal\PluginBase;
use Drupal\masterportal\PluginSystem\SearchBarPluginInterface;

/**
 * Defines a SearchBar plugin implementation for Gdi.
 *
 * @SearchBarPlugin(
 *   id = "Gdi",
 *   title = @Translation("Gdi"),
 *   description = @Translation("A search bar plugin to utilize an elastic search."),
 *   configProperty = "gdi"
 * )
 */
class Gdi extends PluginBase implements SearchBarPluginInterface {

  /**
   * Minimum number of characters at which the search is initiated
   *
   * @var string
   */
  protected $minChar;

  /**
   * The layer id to use for the search.
   *
   * @var number
   */
  protected $serviceId;

    /**
   * QueryObject parameters
   *
   * @var string
   */
  protected $queryObject;

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'minChars' => 3,
      'serviceId' => 'elastic',
      'queryObject' => '{"id":"query","params":{"query_string":"%%searchString%%"}}',
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
      'minChars' => [
        '#type' => 'number',
        '#title' => $this->t('Number of characters after which the search is initiated.', [], ['context' => 'Masterportal']),
        '#default_value' => $this->minChars,
      ],
      'serviceId' => [
        '#type' => 'textfield',
        '#title' => $this->t('Layer to be used for the elastic search.', [], ['context' => 'Masterportal']),
        '#default_value' => $this->serviceId,
        '#states' => $states,
      ],
      'queryObject' => [
        '#type' => 'textarea',
        '#title' => $this->t('Query parameters for the elastic search', [], ['context' => 'Masterportal']),
        '#default_value' => $this->queryObject,
        '#element_validate' => [[$this, 'validateJsonInput']],
        '#states' => $states,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurationArray(FormStateInterface $form_state) {
    return [
      'minChars' => $this->minChars,
      'serviceId' => $this->serviceId,
      'queryObject' => $this->queryObject,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function injectConfiguration(\stdClass &$pluginSection) {
    $pluginSection->minChars = $this->minChars;
    $pluginSection->serviceId = $this->serviceId;
    $pluginSection->queryObject = json_decode($this->queryObject);
  }

}
