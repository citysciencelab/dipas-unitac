<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\geofield\GeoPHP\GeoPHPInterface;
use Drupal\geofield\WktGeneratorInterface;
use Drupal\masterportal\Service\InstanceServiceInterface;
use Drupal\masterportal\Service\MasterportalInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

trait MasterportalWidgetTrait {

    /**
   * Custom instance service.
   *
   * @var InstanceServiceInterface
   */
  protected $instanceService;

  /**
   * The currently processed request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

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
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('geofield.geophp'),
      $container->get('geofield.wkt_generator'),
      $container->get('request_stack'),
      $container->get('masterportal.instanceservice'),
      $container->get('masterportal.renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    array $third_party_settings,
    GeoPHPInterface $geophp_wrapper,
    WktGeneratorInterface $wkt_generator,
    RequestStack $request_stack,
    InstanceServiceInterface $instance_service,
    MasterportalInterface $masterportal_renderer
  ) {
    parent::__construct(
      $plugin_id,
      $plugin_definition,
      $field_definition,
      $settings,
      $third_party_settings,
      $geophp_wrapper,
      $wkt_generator
    );
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->instanceService = $instance_service;
    $this->masterportalRenderer = $masterportal_renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'masterportal_instance' => NULL,
      'width' => '100',
      'unit' => '%',
      'aspect_ratio' => 'aspect_ratio_16_9',
      'editingZoomLevel' => 6,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = static::INTEGRATE_PARENT_SETTINGS ?  parent::settingsForm($form, $form_state) : [];

    $elements['html5_geolocation']['#type'] = 'hidden';
    $elements['html5_geolocation']['#value'] = $this->getSetting('html5_geolocation');

    static::ensureConfigPath($elements, '*#attached->*library');
    $elements['#attached']['library'][] = 'masterportal/fieldWidgetSettingsForm';

    $elements['masterportal_instance'] = [
      '#type' => 'select',
      '#title' => $this->t('Masterportal instance'),
      '#description' => $this->t('Select the Masterportal instance that will be used for this field.'),
      '#required' => TRUE,
      '#default_value' => $this->getSetting('masterportal_instance'),
      '#options' => $this->instanceService->getInstanceOptions(['config']),
    ];

    $elements['width'] = [
      '#type' => 'number',
      '#title' => $this->t('Map width', [], ['context' => 'Masterportal']),
      '#description' => $this->t('The width the map gets integrated in.', [], ['context' => 'Masterportal']),
      '#default_value' => $this->getSetting('width'),
      '#required' => TRUE,
      '#min' => 10,
      '#max' => 1000,
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
      '#options' => static::getAspectRatios(),
      '#default_value' => $this->getSetting('aspect_ratio'),
    ];

    $elements['editingZoomLevel'] = [
      '#type' => 'select',
      '#title' => $this->t('Editing zoom level', [], ['context' => 'Masterportal']),
      '#description' => $this->t('The zoom level to use when editing existing content.', [], ['context' => 'Masterportal']),
      '#options' => array_combine(range(0, 9, 1), range(0, 9, 1)),
      '#default_value' => $this->getSetting('editingZoomLevel'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $aspectRatios = static::getAspectRatios();
    $chosenInstance = $this->instanceService->getInstanceOptions(['config'])[$this->getSetting('masterportal_instance')];
    return [
      $this->t('Map instance: @instance', ['@instance' => $chosenInstance], ['context' => 'Masterportal']),
      $this->t('Map width: @width@unit', ['@width' => $this->getSetting('width'), '@unit' => $this->getSetting('unit')], ['context' => 'Masterportal']),
      $this->t('Aspect ratio: @aspect_ratio', ['@aspect_ratio' => $aspectRatios[$this->getSetting('aspect_ratio')]], ['context' => 'Masterportal']),
      $this->t('Editing zoom level is @editingZoomLevel', ['@editingZoomLevel' => $this->getSetting('editingZoomLevel')], ['context' => 'Masterportal']),
    ];
  }

  /**
   * Returns a set of available aspect ratios, keyed by CSS class.
   *
   * @return array
   *   The aspect ratio options.
   */
  public static function getAspectRatios() {
    return [
      'aspect_ratio_1_1' => '1:1',
      'aspect_ratio_4_3' => '4:3',
      'aspect_ratio_16_9' => '16:9',
      'aspect_ratio_16_10' => '16:10',
      'aspect_ratio_21_10' => '21:10',
    ];
  }

  abstract protected static function ensureConfigPath(&$config, $path);

  abstract public function getSetting($key);

}
