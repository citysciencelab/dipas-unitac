<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\InstanceConfigSection;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\masterportal\Form\MultivalueRowTrait;

/**
 * Class LayerSectionBase.
 *
 * An abstract implementation for layer configuration sections.
 *
 * @package Drupal\masterportal\Plugin\Masterportal\InstanceConfigSection
 */
abstract class LayerSectionBase extends InstanceConfigSectionBase implements LayerSectionPluginInterface {

  use MultivalueRowTrait;

  /**
   * Custom service to handle layer definitions.
   *
   * @var \Drupal\masterportal\Service\LayerServiceInterface
   */
  protected $layerService;

  /**
   * A session-related temp store object.
   *
   * @var PrivateTempStore
   */
  protected $tempStore;

  /**
   * Array of installed gfiThemes.
   *
   * @var array
   */
  protected $gfiThemes;

  /**
   * {@inheritdoc}
   */
  protected function setAdditionalDependencies(Container $container) {
    $this->layerService = $container->get('masterportal.layerservice');
    $this->tempStore = $container->get('tempstore.private')->get('masterportal');
  }

  /**
   * {@inheritdoc}
   */
  protected function preparePlugin() {
    if (empty($this->gfiThemes = $this->tempStore->get('gfiThemes'))) {
      $this->gfiThemes = [];
      $masterportal = file_get_contents(sprintf(
        '%s/libraries/masterportal/js/masterportal.js',
        drupal_get_path('module', 'masterportal')
      ));
      preg_match_all('~("|\')([^"\']+?)\1===[a-z]\.gfiTheme~ism', $masterportal, $matches);
      $this->gfiThemes = array_merge(['default'], $matches[2]);
      $this->tempStore->set('gfiThemes', $this->gfiThemes);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'layer' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  final public function getFormSectionElements(FormStateInterface $form_state) {

    // Prepare the container.
    $section = [];

    // Create the multivalue form portion for background layers.
    $this->createMultivalueFormPortion(
      $section,
      $this->getSectionProperty(),
      $form_state,
      !empty($this->layer) ? $this->layer : [],
      'No layers defined. Click the "Add layer" button to add new layers.'
    );

    return $section;
  }

  /**
   * {@inheritdoc}
   */
  final protected function getInputRow($property, $delta, array $row_defaults, FormStateInterface $form_state) {
    // Determine the placeholder label for configured layers.
    $label_placeholder = ($ids = json_decode($row_defaults['id'])) !== NULL && is_array($ids)
      ? $this->layerService->getLayerNameForCompositeIds($ids)
      : $this->layerService->getLayerDefinition($row_defaults['id'])->name;

    // Return the input elements for the current row.
    $row = [
      'layerid' => [
        '#type' => 'textfield',
        '#title' => $this->t('Layer ID', [], ['context' => 'Masterportal']),
        '#size' => 8,
        '#maxlength' => 500,
        '#default_value' => $row_defaults['id'],
        '#attributes' => [
          'style' => 'background-color: #cccccc;',
          'readonly' => 'readonly',
        ],
        '#inline' => TRUE,
      ],
      'name' => [
        '#type' => 'textfield',
        '#title' => $this->t('Layer name', [], ['context' => 'Masterportal']),
        '#size' => 90,
        '#default_value' => !empty($row_defaults['custom']['name']) ? $row_defaults['custom']['name'] : '',
        '#attributes' => [
          'placeholder' => $label_placeholder,
        ],
        '#inline' => TRUE,
      ],
      'visibility' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Initially visible', [], ['context' => 'Masterportal']),
        '#default_value' => !empty($row_defaults['custom']['visibility']) ? 1 : NULL,
        '#inline' => TRUE,
      ],
      'details' => [
        '#type' => 'details',
        'layerattribution' => [
          '#type' => 'textfield',
          '#title' => $this->t('Layer attribution', [], ['context' => 'Masterportal']),
          '#default_value' => !empty($row_defaults['custom']['layerattribution']) ? $row_defaults['custom']['layerattribution'] : '',
          '#size' => 90,
        ],
        'gfiTheme' => [
          '#type' => 'select',
          '#title' => $this->t('GFI theme', [], ['context' => 'Masterportal']),
          '#description' => $this->t('Select the GFI theme to use for this layer.', [], ['context' => 'Masterportal']),
          '#options' => array_combine($this->gfiThemes, $this->gfiThemes),
          '#default_value' => !empty($row_defaults['custom']['gfiTheme']) ? $row_defaults['custom']['gfiTheme'] : 'default',
        ],
        'mixin' => [
          '#type' => 'textarea',
          '#title' => $this->t('Custom JSON properties', [], ['context' => 'Masterportal']),
          '#description' => $this->t('Enter valid JSON data to override specific layer properties. Use with caution!', [], ['context' => 'Masterportal']),
          '#default_value' => !empty($row_defaults['custom']['mixin']) ? $row_defaults['custom']['mixin'] : '',
          '#size' => 90,
          '#element_validate' => [[$this, 'validateJsonInput']],
        ],
      ],
    ];

    return $row;
  }

  /**
   * {@inheritdoc}
   */
  final protected function getAddSelector($property) {
    return [
      '#type' => 'select2',
      '#title' => $this->t('Choose layer to add', [], ['context' => 'Masterportal']),
      '#options' => $this->layerService->getLayerOptions(),
      '#select2' => ['allowClear' => TRUE],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getAddRowButtonTitle($property) {
    return 'Add layer';
  }

  /**
   * {@inheritdoc}
   */
  final protected function getDataToAdd($property, array $current_state, array $user_input, $addSelectorValue, FormStateInterface $form_state) {
    $currentlyUsedLayerIds = array_map(function ($row) {
      return $row['id'];
    }, $current_state);
    if (
      !empty($addSelectorValue) &&
      !in_array($addSelectorValue, $currentlyUsedLayerIds) &&
      (
        (json_decode($addSelectorValue) === NULL && !empty($definition = $this->layerService->getLayerDefinition($addSelectorValue))) ||
        json_decode($addSelectorValue) !== NULL
      )
    ) {
      return ['id' => $addSelectorValue, 'custom' => []];
    }
    elseif (in_array($addSelectorValue, $currentlyUsedLayerIds)) {
      // Set a message if the layer is already in the stack.
      \Drupal::messenger()->addWarning(
        $this->t('The layer requested is already in the layer stack!', [], ['context' => 'Masterportal'])
      );
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  final protected static function isSortable($property) {
    return TRUE;
  }

  /**
   * Helper function to extract layer setting informations.
   *
   * @param string $layersection
   *   The layer section to extract.
   * @param FormStateInterface $form_state
   *   The FormStateInterface object.
   *
   * @return array
   *   The layerdefinition for that section.
   */
  final protected function collectLayerSettings($layersection, FormStateInterface $form_state) {

    // Get the data of the multivalue section.
    $data = self::getData($layersection, $form_state->getUserInput());

    // Preprocess it.
    array_walk($data, function (&$item) {

      // Prepare the return object.
      $processed = [
        'id' => $item['layerid'],
        'custom' => [],
      ];

      // Is the layer name overridden?
      if (!empty($item["name"])) {
        $processed['custom']['name'] = $item["name"];
      }
      // If it's not overridden, but a group of layers, we need to set
      // the group label as the custom layer name.
      elseif (($decoded = json_decode($item["layerid"])) !== NULL && is_array($decoded)) {
        $processed['custom']['name'] = $this->layerService->getLayerNameForCompositeIds($decoded);
      }

      // Is this layer initially visible?
      $processed['custom']['visibility'] = !empty($item["visibility"]);

      // Iterate over the details and store each key separately.
      foreach ($item["details"] as $key => $input) {
        if (!empty($input)) {
          $processed['custom'][$key] = $input;
        }
      }

      // Overwrite the raw input data with the processed form.
      $item = $processed;
    });

    return $data;

  }

  /**
   * {@inheritdoc}
   */
  final public function getSectionConfigArray(array $rawFormData, FormStateInterface $form_state) {
    return ['layer' => $this->collectLayerSettings(static::getSectionProperty(), $form_state)];
  }

  /**
   * {@inheritdoc}
   */
  final public function injectSectionConfigurationSettings($type, \stdClass &$config) {
    switch ($type) {
      case 'config.js':
        break;

      case 'config.json':
        // Make sure the container exists.
        static::ensureConfigPath($config, sprintf('Themenconfig->%s->*Layer', $this->getSectionConfigName()));

        // Inject each layer configured.
        foreach ($this->layer as $layer) {

          // Insert layer packages as arrays.
          if (($decoded = json_decode($layer['id'])) !== NULL && is_array($decoded)) {
            $layerConfig = (object) ['id' => $decoded];
          }
          else {
            $layerConfig = (object) ['id' => $layer['id']];
          }

          // Mix in the custom overrides.
          foreach ($layer['custom'] as $key => $value) {
            switch ($key) {

              case 'mixin':
                $mixin = (array) json_decode($value);
                $this->setLayerProperties($mixin, $layerConfig);
                break;

              case 'gfiTheme':
                if ($value !== 'default') {
                  $this->setLayerProperties([$key => $value], $layerConfig);
                }
                break;

              default:
                $this->setLayerProperties([$key => $value], $layerConfig);

            }
          }

          // Place the layer configuration in the configuration section.
          $config->Themenconfig->{$this->getSectionConfigName()}->Layer[] = $layerConfig;
        }
        break;
    }
  }

  /**
   * Sets single properties on a layer definition.
   *
   * @param array $properties
   *   The properties to set, keyed by their property name.
   * @param \stdClass $layerConfig
   *   The layer configuration object.
   */
  final protected function setLayerProperties(array $properties, \stdClass &$layerConfig) {
    foreach ($properties as $key => $value) {
      $layerConfig->{$key} = $value;
    }
  }

  /**
   * {@inheritdoc}
   */
  final public function getLayerIdsInUse() {
    $layerIdsInUse = [];
    foreach ($this->layer as $layer) {
      if (($decoded = json_decode($layer['id'])) !== NULL && is_array($decoded)) {
        $layerIdsInUse = array_merge($layerIdsInUse, $decoded);
      }
      else {
        $layerIdsInUse[] = $layer['id'];
      }
    }
    return $layerIdsInUse;
  }

  /**
   * Returns the property name of the section plugin.
   *
   * @return string
   *   The name of the current plugin.
   */
  abstract public static function getSectionProperty();

  /**
   * Returns the configuration property name of the section plugin.
   *
   * @return string
   *   The name of the configuration property.
   */
  abstract protected function getSectionConfigName();

}
