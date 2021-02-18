<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\masterportal\EnsureObjectStructureTrait;
use Drupal\masterportal\Plugin\Field\FieldWidget\MasterportalGeofieldWidget;
use Drupal\masterportal\Service\InstanceServiceInterface;
use Drupal\masterportal\Service\MasterportalInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field formatter implementation to use the Masterportal as a frontend for a geofield field.
 *
 * @FieldFormatter(
 *   id = "masterportal_formatter",
 *   label = @Translation("Masterportal"),
 *   field_types = {
 *     "geofield"
 *   }
 * )
 */
class MasterportalFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  use EnsureObjectStructureTrait;

  /**
   * Custom instance service.
   *
   * @var InstanceServiceInterface
   */
  protected $instanceService;

  /**
   * The Masterportal renderer service.
   *
   * @var MasterportalInterface
   */
  protected $masterportalRenderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration,
      $container->get('masterportal.instanceservice'),
      $container->get('masterportal.renderer')
    );
  }

  /**
   * Masterportal constructor.
   *
   * @param string $plugin_id
   *   The plugnin id.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param array $configuration
   *   The configuration array.
   * @param InstanceServiceInterface $instance_service
   *   Custom instance service.
   * @param MasterportalInterface $masterportal_renderer
   *   Custom renderer service.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if the entity type doesn't exist.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the storage handler couldn't be loaded.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    array $configuration,
    InstanceServiceInterface $instance_service,
    MasterportalInterface $masterportal_renderer
  ) {
    parent::__construct(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings']
    );
    $this->instanceService = $instance_service;
    $this->masterportalRenderer = $masterportal_renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'width' => '100',
      'unit' => '%',
      'aspect_ratio' => 'aspect_ratio_16_9',
      'masterportal_instance' => 'default',
      'initialZoomLevel' => 7,
      'hideOthers' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    static::ensureConfigPath($elements, '*#attached->*library');
    $elements['#attached']['library'][] = 'masterportal/fieldWidgetSettingsForm';

    $elements['masterportal_instance'] = [
      '#type' => 'select',
      '#title' => $this->t('Masterportal instance', [], ['context' => 'Masterportal']),
      '#description' => $this->t('The instance of the Masterportal to use for the field output.', [], ['context' => 'Masterportal']),
      '#options' => $this->instanceService->getInstanceOptions(['config']),
      '#default_value' => $this->getSetting('masterportal_instance'),
    ];

    $elements['initialZoomLevel'] = [
      '#type' => 'select',
      '#title' => $this->t('Initial zoom level', [], ['context' => 'Masterportal']),
      '#options' => array_combine(range(1, 9, 1), range(1, 9, 1)),
      '#default_value' => $this->getSetting('initialZoomLevel'),
    ];

    // TODO - figure out how to outsource DIPAS specific formatter settings.
    $elements['hideOthers'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide other contributions', [], ['context' => 'Masterportal']),
      '#description' => $this->t('If activated, shows only the marker of the current node in the map.', [], ['context' => 'Masterportal']),
      '#default_value' => $this->getSetting('hideOthers'),
    ];

    $elements['width'] = [
      '#type' => 'number',
      '#title' => $this->t('Map width', [], ['context' => 'Masterportal']),
      '#description' => $this->t('The width the map gets integrated in.', [], ['context' => 'Masterportal']),
      '#default_value' => $this->getSetting('width'),
      '#required' => TRUE,
      '#min' => 10,
      '#max' => 2500,
      '#step' => 1,
      '#attributes' => [
        'class' => ['mapWidthValue'],
        'style' => 'width: 70px;',
      ],
    ];

    $elements['unit'] = [
      '#type' => 'radios',
      '#title' => $this->t('Unit', [], ['context' => 'Masterportal']),
      '#description' => $this->t('Is the width value stated in pixel or percent?', [], ['context' => 'Masterportal']),
      '#default_value' => $this->getSetting('unit'),
      '#required' => TRUE,
      '#options' => [
        '%' => $this->t('Percent', [], ['context' => 'Masterportal']),
        'px' => $this->t('Pixel', [], ['context' => 'Masterportal']),
      ],
      '#attributes' => [
        'class' => ['mapWidthUnit'],
      ],
    ];

    $elements['aspect_ratio'] = [
      '#type' => 'select',
      '#title' => $this->t('Aspect ratio', [], ['context' => 'Masterportal']),
      '#description' => $this->t('The aspect ratio the map gets integrated in.', [], ['context' => 'Masterportal']),
      '#options' => MasterportalGeofieldWidget::getAspectRatios(),
      '#default_value' => $this->getSetting('aspect_ratio'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $instances = $this->instanceService->getInstanceOptions();
    $aspectRatios = MasterportalGeofieldWidget::getAspectRatios();
    return [
      $this->t('Masterportal instance: @instance', ['@instance' => $instances[$this->getSetting('masterportal_instance')]], ['context' => 'Masterportal']),
      $this->t('Map width: @width@unit', ['@width' => $this->getSetting('width'), '@unit' => $this->getSetting('unit')], ['context' => 'Masterportal']),
      $this->t('Aspect ratio: @aspect_ratio', ['@aspect_ratio' => $aspectRatios[$this->getSetting('aspect_ratio')]], ['context' => 'Masterportal']),
      $this->t('Initial zoom level: @zoom', ['@zoom' => $this->getSetting('initialZoomLevel')], ['context' => 'Masterportal']),
      // TODO - figure out how to outsource DIPAS specific formatter settings.
      $this->t('Other contributions: @others', ['@others' => !empty($this->getSetting('hideOthers')) ? $this->t('hidden') : $this->t('displayed')], ['context' => 'Masterportal']),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function view(FieldItemListInterface $items, $langcode = NULL) {
    return $this->viewElements($items, $langcode);
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $instance = $this->instanceService->loadInstance($this->getSetting('masterportal_instance'));
    $width = sprintf('%s%s', $this->getSetting('width'), $this->getSetting('unit'));
    $aspectRatio = $this->getSetting('aspect_ratio');
    $zoomLevel = $this->getSetting('initialZoomLevel');
    $query = ['hideOthers' => $this->getSetting('hideOthers')];
    $renderArray = [];
    foreach ($items as $item) {
      $coords = sprintf(
        '%s,%s',
        $item->get('lon')->getString(),
        $item->get('lat')->getString()
      );
      $renderArray[] = $this->masterportalRenderer->iframe($instance, $width, $aspectRatio, $zoomLevel, $coords, NULL, $query);
    }
    return $renderArray;
  }

}
