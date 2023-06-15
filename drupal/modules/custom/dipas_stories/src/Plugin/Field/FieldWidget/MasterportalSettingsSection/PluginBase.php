<?php

namespace Drupal\dipas_stories\Plugin\Field\FieldWidget\MasterportalSettingsSection;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\dipas_stories\LoadEntityTrait;
use Drupal\dipas_stories\MapSettingsTrait;
use Drupal\dipas_stories\PluginSystem\MasterportalSettingsSectionPluginInterface;
use Drupal\dipas_stories\StoryRelationHandlerTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PluginBase.
 */
abstract class PluginBase implements MasterportalSettingsSectionPluginInterface {

  use StringTranslationTrait, MapSettingsTrait, StoryRelationHandlerTrait, LoadEntityTrait;

  /**
   * @var string
   */
  protected $widgetMode;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * PluginBase constructor.
   *
   * @param array $settings
   */
  public function __construct($widgetMode) {
    $this->widgetMode = $widgetMode;

    $container = \Drupal::getContainer();
    $this->entityTypeManager = $container->get('entity_type.manager');

    $this->setAdditionalDependencies($container);
  }

  /**
   * Helper function to allow plugins to set further dependencies.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return void
   */
  protected function setAdditionalDependencies(ContainerInterface $container) {}

  /**
   * {@inheritdoc}
   */
  protected function getEntityTypeManager() {
    return $this->entityTypeManager;
  }

}
