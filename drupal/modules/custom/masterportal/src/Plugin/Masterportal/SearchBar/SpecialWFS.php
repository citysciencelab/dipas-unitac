<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\SearchBar;

use Drupal\Core\Form\FormStateInterface;
use Drupal\masterportal\Form\MultivalueRowTrait;
use Drupal\masterportal\Plugin\Masterportal\PluginBase;
use Drupal\masterportal\PluginSystem\SearchBarPluginInterface;

/**
 * Defines a SearchBar plugin implementation for specialWFS.
 *
 * @SearchBarPlugin(
 *   id = "specialWFS",
 *   title = @Translation("specialWFS"),
 *   description = @Translation("A search bar plugin to utilize a specialWFS search."),
 *   configProperty = "specialWFS"
 * )
 */
class SpecialWFS extends PluginBase implements SearchBarPluginInterface {

  use MultivalueRowTrait;

  /**
   * The default number of search results to display.
   *
   * @var int
   */
  protected $maxFeatures;

  /**
   * Timeout in ms after services invoked are considered stale.
   *
   * @var int
   */
  protected $timeout;

  /**
   * The search definitions.
   *
   * @var array
   */
  protected $WFSSearchDefinitions;

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'maxFeatures' => 20,
      'timeout' => 6000,
      'WFSSearchDefinitions' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(FormStateInterface $form_state, $dependantSelector = FALSE, $dependantSelectorProperty = NULL, $dependantSelectorValue = NULL) {
    $form = [
      'maxFeatures' => [
        '#type' => 'number',
        '#title' => $this->t('Maximum features', [], ['context' => 'Masterportal']),
        '#description' => $this->t('Default count of maximum features to find. Can be overridden in distinct implementations.', [], ['context' => 'Masterportal']),
        '#min' => 1,
        '#max' => 100,
        '#step' => 1,
        '#default_value' => $this->maxFeatures,
        '#states' => ['required' => [$dependantSelector => [$dependantSelectorProperty => $dependantSelectorValue]]],
      ],
      'timeout' => [
        '#type' => 'number',
        '#title' => $this->t('Timeout', [], ['context' => 'Masterportal']),
        '#description' => $this->t('Time in seconds after which requests to search services are considered stale.', [], ['context' => 'Masterportal']),
        '#min' => 1,
        '#max' => 30,
        '#step' => 1,
        '#default_value' => ($this->timeout / 1000),
        '#states' => ['required' => [$dependantSelector => [$dependantSelectorProperty => $dependantSelectorValue]]],
      ],
      'WFSSearchDefinitions' => [
        '#type' => 'details',
        '#open' => TRUE,
        '#title' => $this->t('Service definitions', [], ['context' => 'Masterportal']),
        '#description' => $this->t('Definitions on which this search service performs a search on.', [], ['context' => 'Masterportal']),
        '#plugin' => sprintf('%s/%s', 'searchbar_plugin', 'specialWFS'),
      ],
    ];

    $this->createMultivalueFormPortion(
      $form['WFSSearchDefinitions'],
      'WFSSearchDefinitions',
      $form_state,
      $this->WFSSearchDefinitions,
      'No definitions defined. Use "Add definition" to add new definitions.'
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function getInputRow($property, $delta, array $row_defaults, FormStateInterface $form_state) {
    return [
      'name' => [
        '#type' => 'textfield',
        '#title' => $this->t('Category name', [], ['context' => 'Masterportal']),
        '#description' => $this->t('Name, which is displayed in the recommendation list.', [], ['context' => 'Masterportal']),
        '#default_value' => !empty($row_defaults['name']) ? $row_defaults['name'] : '',
      ],
      'url' => [
        '#type' => 'url',
        '#title' => $this->t('URL', [], ['context' => 'Masterportal']),
        '#description' => $this->t('Webaddress of the WFS layer.', [], ['context' => 'Masterportal']),
        '#default_value' => !empty($row_defaults['url']) ? $row_defaults['url'] : '',
      ],
      'typeName' => [
        '#type' => 'textfield',
        '#title' => $this->t('Type name', [], ['context' => 'Masterportal']),
        '#description' => $this->t('TypeName of the WFS layer.', [], ['context' => 'Masterportal']),
        '#default_value' => !empty($row_defaults['typeName']) ? $row_defaults['typeName'] : '',
      ],
      'propertyNames' => [
        '#type' => 'textfield',
        '#title' => $this->t('Property names', [], ['context' => 'Masterportal']),
        '#description' => $this->t('The name(s) of the properties that are to be searched. Must contain an array in valid JSON format.', [], ['context' => 'Masterportal']),
        '#default_value' => !empty($row_defaults['propertyNames']) ? $row_defaults['propertyNames'] : '',
        '#element_validate' => [[$this, 'validateJsonInput']],
      ],
      'geometryName' => [
        '#type' => 'textfield',
        '#title' => $this->t('Geometry name', [], ['context' => 'Masterportal']),
        '#description' => $this->t('Attribute name of the geometry.', [], ['context' => 'Masterportal']),
        '#default_value' => !empty($row_defaults['geometryName']) ? $row_defaults['geometryName'] : 'app:geom',
      ],
      'maxFeatures' => [
        '#type' => 'number',
        '#title' => $this->t('Maximum features', [], ['context' => 'Masterportal']),
        '#description' => $this->t('Maximum number of features to find.', [], ['context' => 'Masterportal']),
        '#min' => 1,
        '#max' => 50,
        '#step' => 1,
        '#default_value' => !empty($row_defaults['maxFeatures']) ? $row_defaults['maxFeatures'] : 20,
      ],
      'data' => [
        '#type' => 'textfield',
        '#title' => $this->t('Data', [], ['context' => 'Masterportal']),
        '#description' => $this->t('Filter parameters for the WFS request.', [], ['context' => 'Masterportal']),
        '#default_value' => !empty($row_defaults['data']) ? $row_defaults['data'] : '',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getRowType($property) {
    return 'details';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRowTitle($property) {
    return 'Definition';
  }

  /**
   * {@inheritdoc}
   */
  protected function getRowProperties($property) {
    return [
      '#open' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getAddRowButtonTitle($property) {
    return 'Add definition';
  }

  /**
   * {@inheritdoc}
   */
  protected function getDataToAdd($property, array $current_state, array $user_input, $addSelectorValue, FormStateInterface $form_state) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurationArray(FormStateInterface $form_state) {
    return [
      'maxFeatures' => (int) $this->maxFeatures,
      'timeout' => (int) $this->timeout * 1000,
      'WFSSearchDefinitions' => self::getData('WFSSearchDefinitions', $form_state->getUserInput()),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function injectConfiguration(\stdClass &$pluginSection) {
    $pluginSection->maxFeatures = $this->maxFeatures;
    $pluginSection->timeout = $this->timeout;
    $pluginSection->definitions = [];
    foreach ($this->WFSSearchDefinitions as $definition) {
      $definitionObject = new \stdClass();
      foreach ($definition as $key => $value) {
        if (!empty($value)) {
          if ($key === 'propertyNames') {
            $value = json_decode($value);
            if (!is_array($value)) {
              continue;
            }
          }
          $definitionObject->{$key} = $value;
        }
      }
      $pluginSection->definitions[] = $definitionObject;
    }
  }

}
