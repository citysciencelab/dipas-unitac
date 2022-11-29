<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal\InstanceConfigSection;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\masterportal\Form\MultivalueRowTrait;

/**
 * Defines a MapSettings configuration section.
 *
 * @InstanceConfigSection(
 *   id = "MapSettings",
 *   title = @Translation("Map settings"),
 *   description = @Translation("Settings related to the map integration of the Masterportal."),
 *   sectionWeight = 15
 * )
 */
class MapSettings extends InstanceConfigSectionBase {

  use MultivalueRowTrait;

  /**
   * The center coordinate for the map to start with.
   *
   * @var string
   */
  protected $startCenter;

  /**
   * Map panning with two fingers only?
   *
   * @var Boolean
   */
  protected $twoFingerPan;

  /**
   * Should scale options be integrated in config.json?
   *
   * @var Boolean
   */
  protected $useScaleOptions;

  /**
   * A set of different detail levels.
   *
   * @var array
   */
  protected $options;

  /**
   * The initial zoom level.
   *
   * @var int
   */
  protected $zoomLevel;

  /**
   * The map projection (can not be configured!).
   *
   * @var string
   */
  protected $epsg;

  /**
   * {@inheritdoc}
   */
  public static function getDefaults() {
    return [
      'startCenter' => '[565874, 5934140]',
      'twoFingerPan' => FALSE,
      'useScaleOptions' => TRUE,
      'options' => [
        ['resolution' => 66.14579761460263, 'scale' => 250000, 'zoomLevel' => 0],
        ['resolution' => 26.458319045841044, 'scale' => 100000, 'zoomLevel' => 1],
        ['resolution' => 15.874991427504629, 'scale' => 60000, 'zoomLevel' => 2],
        ['resolution' => 10.583327618336419, 'scale' => 40000, 'zoomLevel' => 3],
        ['resolution' => 5.2916638091682096, 'scale' => 20000, 'zoomLevel' => 4],
        ['resolution' => 2.6458319045841048, 'scale' => 10000, 'zoomLevel' => 5],
        ['resolution' => 1.3229159522920524, 'scale' => 5000, 'zoomLevel' => 6],
        ['resolution' => 0.6614579761460262, 'scale' => 2500, 'zoomLevel' => 7],
        ['resolution' => 0.2645831904584105, 'scale' => 1000, 'zoomLevel' => 8],
        ['resolution' => 0.13229159522920521, 'scale' => 500, 'zoomLevel' => 9],
      ],
      'zoomLevel' => 3,
      // Not configurable.
      'epsg' => 'EPSG:25832',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormSectionElements(FormStateInterface $form_state, array $settings, $pluginIdentifier) {
    $center = json_decode($this->startCenter);
    $center = sprintf('%d,%d', $center[0], $center[1]);

    $section = [
      'mapWidget' => [
        'prefix' => [
          '#type' => 'container',
          '#attributes' => ['class' => ['form-item']],
          'label' => [
            '#type' => 'html_tag',
            '#tag' => 'label',
            '#value' => $this->t('Starting center & zoom level', [], ['context' => 'Masterportal']),
          ],
          'description' => [
            '#type' => 'html_tag',
            '#tag' => 'div',
            '#value' => $this->t('Use the map to set the initial zoom level and starting center point of your instance.', [], ['context' => 'Masterportal']),
            '#attributes' => ['class' => ['description']],
          ],
        ],
        'map' => [
          '#type' => 'html_tag',
          '#tag' => 'iframe',
          '#attributes' => [
            'src' => Url::fromRoute(
              'masterportal.fullscreen',
              ['masterportal_instance' => 'default.config'],
              [
                'query' => [
                  'center' => $center,
                  'zoomLevel' => $this->zoomLevel,
                ],
              ]
            )->toString(),
            'width' => 700,
            'height' => 500,
          ],
          '#attached' => [
            'library' => ['masterportal/configmap'],
          ],
        ],
      ],
      'startCenter' => [
        '#type' => 'textfield',
        '#title' => $this->t('Center coordinates', [], ['context' => 'Masterportal']),
        '#description' => $this->t(
          'The @projection (UTM) center coordinates for the map to start with. Must be entered in a valid JSON format as an array of 2 integer values.',
          ['@projection' => $this->epsg],
          ['context' => 'Masterportal']
        ),
        '#default_value' => $this->startCenter,
        '#element_validate' => [
          [$this, 'validateJsonInput'],
          [$this, 'validateCoords'],
        ],
      ],
      'zoomLevel' => [
        '#type' => 'range',
        '#title' => $this->t('Initial zoom level', [], ['context' => 'Masterportal']),
        '#description' => $this->t('The initial zoom level that map gets displayed in.', [], ['context' => 'Masterportal']),
        '#min' => 0,
        '#max' => 9,
        '#step' => 1,
        '#default_value' => $this->zoomLevel,
        '#attributes' => [
          'style' => 'width: 700px;',
        ],
      ],
      'twoFingerPan' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Use two finger pan', [], ['context' => 'Masterportal']),
        '#description' => $this->t('Should panning the map only be possible using two fingers?', [], ['context' => 'Masterportal']),
        '#default_value' => $this->twoFingerPan,
      ],
      'useScaleOptions' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Use map scale options', [], ['context' => 'Masterportal']),
        '#description' => $this->t('Should map scale options get included in the Masterportal configuration?.', [], ['context' => 'Masterportal']),
        '#default_value' => $this->useScaleOptions,
      ],
      'options' => [
        '#type' => 'details',
        '#title' => $this->t('Map scale options', [], ['context' => 'Masterportal']),
        '#description' => $this->t('The scale options the map utilizes.', [], ['context' => 'Masterportal']),
        '#description_display' => 'before',
        '#plugin' => $pluginIdentifier,
        '#states' => [
          'visible' => [':input[type="checkbox"][name="settings[MapSettings][useScaleOptions]"]' => ['checked' => TRUE]],
        ],
      ],
    ];

    $this->createMultivalueFormPortion(
      $section['options'],
      'options',
      $form_state,
      $this->options,
      'No scale options defined. Use the "Add scale option" button to add new options.'
    );

    return $section;
  }

  /**
   * {@inheritdoc}
   */
  protected function getInputRow($property, $delta, $row_defaults, FormStateInterface $form_state) {
    return [
      'resolution' => [
        '#type' => 'range',
        '#title' => $this->t('Map resolution', [], ['context' => 'Masterportal']),
        '#default_value' => !empty($row_defaults['resolution']) ? $row_defaults['resolution'] : 0.1,
        '#min' => 0.1,
        '#max' => 75,
        '#step' => 0.00000000000000001,
        '#inline' => TRUE,
        '#attributes' => [
          'style' => 'width: 700px;',
        ],
        '#states' => [
          'required' => [':input[type="checkbox"][name="settings[MapSettings][useScaleOptions]"]' => ['checked' => TRUE]],
        ],
      ],
      'scale' => [
        '#type' => 'select',
        '#title' => $this->t('Map scale', [], ['context' => 'Masterportal']),
        '#default_value' => !empty($row_defaults['scale']) ? $row_defaults['scale'] : 2000,
        '#options' => array_combine(range(500, 250000, 500), range(500, 250000, 500)),
        '#inline' => TRUE,
        '#states' => [
          'required' => [':input[type="checkbox"][name="settings[MapSettings][useScaleOptions]"]' => ['checked' => TRUE]],
        ],
      ],
      'zoomLevel' => [
        '#type' => 'select',
        '#title' => $this->t('Zoom level', [], ['context' => 'Masterportal']),
        '#default_value' => !empty($row_defaults['zoomLevel']) ? $row_defaults['zoomLevel'] : 0,
        '#options' => array_combine(range(0, 15), range(0, 15)),
        '#inline' => TRUE,
        '#states' => [
          'required' => [':input[type="checkbox"][name="settings[MapSettings][useScaleOptions]"]' => ['checked' => TRUE]],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected static function isSortable($property) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function allowMultipleEmptyAdds($property) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function getRowType($property) {
    return 'fieldset';
  }

  /**
   * {@inheritdoc}
   */
  protected function getAddRowButtonTitle($property) {
    return 'Add scale option';
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
  public function getSectionConfigArray(array $rawFormData, FormStateInterface $form_state) {
    $data = [];
    foreach (self::getDefaults() as $property => $default) {
      if ($property !== 'options') {
        if (!is_bool($default)) {
          $data[$property] = !empty($rawFormData[$property]) ? $rawFormData[$property] : $default;
        }
        else {
          $data[$property] = isset($rawFormData[$property]) ? (bool) $rawFormData[$property] : $default;
        }
      }
      else {
        $data['options'] = self::getData('options', $form_state->getUserInput());
      }
    }
    // Type conversion happens here.
    $data['zoomLevel'] = (int) $data['zoomLevel'];
    foreach ($data["options"] as &$option) {
      array_walk($option, function (&$item, $key) {
        if ($key === 'resolution') {
          $item = (float) $item;
        }
        else {
          $item = (int) $item;
        }
      });
    }
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function injectSectionConfigurationSettings($type, \stdClass &$config) {
    switch ($type) {
      case 'config.js':
        break;

      case 'config.json':
        // Make sure the configuration section exists.
        static::ensureConfigPath($config, 'Portalconfig->mapView');

        // Insert basic settings.
        $config->Portalconfig->mapView->startCenter = json_decode($this->startCenter, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT);
        $config->Portalconfig->mapView->startZoomLevel = $this->zoomLevel;
        $config->Portalconfig->mapView->epsg = $this->epsg;
        $config->Portalconfig->mapView->twoFingerPan = $this->twoFingerPan;

        // Insert map scale options.
        if ($this->useScaleOptions) {
          $config->Portalconfig->mapView->options = array_map(function ($option) {
            return (object) $option;
          }, $this->options);
        }
        break;
    }
  }

}
