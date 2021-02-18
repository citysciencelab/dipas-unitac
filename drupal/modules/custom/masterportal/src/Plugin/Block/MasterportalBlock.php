<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\masterportal\EnsureObjectStructureTrait;
use Drupal\masterportal\Plugin\Field\FieldWidget\MasterportalWidget;
use Drupal\masterportal\Service\InstanceServiceInterface;
use Drupal\masterportal\Service\MasterportalInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MasterportalBlock.
 *
 * Integrates Masterportal instances as a block.
 *
 * @Block(
 *   id = "masterportal_block",
 *   admin_label = @Translation("Masterportal")
 * )
 *
 * @package Drupal\masterportal\Plugin\Block
 */
class MasterportalBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use EnsureObjectStructureTrait;

  /**
   * Custom service to handle instance configurations.
   *
   * @var InstanceServiceInterface
   */
  protected $instanceService;

  /**
   * Custom renderer service.
   *
   * @var MasterportalInterface
   */
  protected $masterportalRenderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('masterportal.instanceservice'),
      $container->get('masterportal.renderer')
    );
  }

  /**
   * MasterportalBlock constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param InstanceServiceInterface $instance_service
   *   Custom service to handle instance configurations.
   * @param MasterportalInterface $masterportal_renderer
   *   Custom renderer service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    InstanceServiceInterface $instance_service,
    MasterportalInterface $masterportal_renderer
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->instanceService = $instance_service;
    $this->masterportalRenderer = $masterportal_renderer;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'width' => '100',
      'unit' => '%',
      'aspect_ratio' => 'aspect_ratio_16_9',
      'masterportal_instance' => 'default',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    static::ensureConfigPath($form, '*#attached->*library');
    $form['#attached']['library'][] = 'masterportal/fieldWidgetSettingsForm';

    $form['masterportal_instance'] = [
      '#type' => 'select',
      '#title' => $this->t('Masterportal instance', [], ['context' => 'Masterportal']),
      '#description' => $this->t('The instance of the Masterportal to use for the field output.', [], ['context' => 'Masterportal']),
      '#options' => $this->instanceService->getInstanceOptions(['config']),
      '#default_value' => $this->configuration["masterportal_instance"],
    ];

    $form['width'] = [
      '#type' => 'number',
      '#title' => $this->t('Map width', [], ['context' => 'Masterportal']),
      '#description' => $this->t('The width the map gets integrated in.', [], ['context' => 'Masterportal']),
      '#default_value' => $this->configuration["width"],
      '#required' => TRUE,
      '#min' => 10,
      '#max' => 2500,
      '#step' => 1,
      '#attributes' => [
        'class' => ['mapWidthValue'],
        'style' => 'width: 70px;',
      ],
    ];

    $form['unit'] = [
      '#type' => 'radios',
      '#title' => $this->t('Unit', [], ['context' => 'Masterportal']),
      '#description' => $this->t('Is the width value stated in pixel or percent?', [], ['context' => 'Masterportal']),
      '#default_value' => $this->configuration["unit"],
      '#required' => TRUE,
      '#options' => [
        '%' => $this->t('Percent', [], ['context' => 'Masterportal']),
        'px' => $this->t('Pixel', [], ['context' => 'Masterportal']),
      ],
      '#attributes' => [
        'class' => ['mapWidthUnit'],
      ],
    ];

    $form['aspect_ratio'] = [
      '#type' => 'select',
      '#title' => $this->t('Aspect ratio', [], ['context' => 'Masterportal']),
      '#description' => $this->t('The aspect ratio the map gets integrated in.', [], ['context' => 'Masterportal']),
      '#options' => MasterportalWidget::getAspectRatios(),
      '#default_value' => $this->configuration["aspect_ratio"],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['masterportal_instance'] = $form_state->getValue('masterportal_instance');
    $this->configuration['width'] = $form_state->getValue('width');
    $this->configuration['unit'] = $form_state->getValue('unit');
    $this->configuration['aspect_ratio'] = $form_state->getValue('aspect_ratio');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    if (!empty($instance = $this->instanceService->loadInstance($this->configuration['masterportal_instance']))) {
      return $this->masterportalRenderer->iframe(
        $instance,
        sprintf('%d%s', $this->configuration['width'], $this->configuration['unit']),
        $this->configuration['aspect_ratio']
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();
    static::ensureConfigPath($dependencies, '[*config, *module]');
    $dependencies['config'][] = sprintf('masterportal.instance.%s', $this->configuration["masterportal_instance"]);
    $dependencies['module'][] = 'masterportal';
    return $dependencies;
  }

}
