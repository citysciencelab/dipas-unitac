<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\Plugin\Masterportal;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\masterportal\EnsureObjectStructureTrait;
use Drupal\masterportal\Form\ElementValidateCoordinatesTrait;
use Drupal\masterportal\Form\ElementValidateFileExistsTrait;
use Drupal\masterportal\Form\ElementValidateJsonTrait;
use Drupal\masterportal\GlyphiconTrait;
use Drupal\masterportal\PluginSystem\PluginInterface;

/**
 * Class PluginBase.
 *
 * @package Drupal\masterportal\Plugin\Masterportal
 */
abstract class PluginBase implements PluginInterface {

  use StringTranslationTrait;
  use GlyphiconTrait;
  use ElementValidateJsonTrait;
  use ElementValidateFileExistsTrait;
  use ElementValidateCoordinatesTrait;
  use EnsureObjectStructureTrait;
  use DependencySerializationTrait;

  /**
   * The configuration entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * The plugin definition of the current plugin.
   *
   * @var array
   */
  protected $pluginDefinition;

  /**
   * Custom token service.
   *
   * @var \Drupal\masterportal\Service\MasterportalTokenServiceInterface
   */
  protected $tokenService;

  /**
   * PluginBase constructor.
   *
   * @param array $values
   *   An array of values to set, keyed by property name.
   */
  public function __construct(array $values) {
    // Extract and remove the entity from the values array (if existent).
    foreach (['_entity' => 'entity', '_definition' => 'pluginDefinition'] as $key => $property) {
      if (!empty($values[$key])) {
        $this->{$property} = $values[$key];
        unset($values[$key]);
      }
    }
    // Set the plugin values.
    $this->setValues($values);
    // No nice dependency injection here.
    /* @var \Drupal\Core\DependencyInjection\Container $container */
    $container = \Drupal::getContainer();
    $this->tokenService = $container->get('masterportal.tokens');
    $this->setAdditionalDependencies($container);
    $this->generateGlyphiconStyles();
    $this->preparePlugin();
  }

  /**
   * Sets member variables.
   *
   * @param array $values
   *   The variables to set, keyed by their name.
   */
  public function setValues(array $values) {
    // Make sure the plugin defaults get set.
    foreach (static::getDefaults() as $key => $value) {
      $this->$key = $value;
    }
    // Set all given values afterwards.
    foreach ($values as $key => $value) {
      $this->$key = $value;
    }
  }

  /**
   * Execute additional tasks in the constructor (if needed).
   */
  protected function preparePlugin() {}

  /**
   * Allow actual plugin implementation to get their own dependencies.
   *
   * @param Container $container
   *   Drupal's dependency injection container.
   */
  protected function setAdditionalDependencies(Container $container) {}

  /**
   * {@inheritdoc}
   */
  public function getConfigurationArray(FormStateInterface $form_state) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function injectConfiguration(\stdClass &$pluginSection) {
  }

}
