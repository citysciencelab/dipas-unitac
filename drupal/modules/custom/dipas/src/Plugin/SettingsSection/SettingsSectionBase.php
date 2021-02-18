<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\Plugin\SettingsSection;

use Drupal\Component\DependencyInjection\Container;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\dipas\PluginSystem\SettingsSectionPluginInterface;
use Drupal\masterportal\DomainAwareTrait;

/**
 * Class SettingsSectionBase.
 *
 * @package Drupal\dipas\Plugin\SettingsSection
 */
abstract class SettingsSectionBase implements SettingsSectionPluginInterface {

  use StringTranslationTrait;
  use DomainAwareTrait;

  /**
   * The plugin definition.
   *
   * @var array
   */
  protected $pluginDefinition;

  /**
   * Drupal's config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var \Drupal\domain\DomainInterface|NULL
   */
  protected $activeDomain;

  /**
   * @var string
   */
  protected $domainSuffix;

  /**
   * SettingsSectionBase constructor.
   *
   * @param array $values
   *   The initial values to set.
   */
  public function __construct(array $values) {
    $this->pluginDefinition = $values['_definition'];
    unset($values['_definition']);
    $this->setValues($values);
    // No nice dependency injection here.
    /* @var \Drupal\Core\DependencyInjection\Container $container */
    $container = \Drupal::getContainer();
    $this->configFactory = $container->get('config.factory');

    $this->domainSuffix = sprintf(':%s', $this->getActiveDomain());

    $this->setAdditionalDependencies($container);
  }

  /**
   * {@inheritdoc}
   */
  public function setValues(array $values) {
    foreach ($values as $key => $value) {
      $this->$key = $value;
    }
  }

  /**
   * {@inheritdoc}
   */
  final public function getProcessedConfigurationValues(array $plugin_values, array $form_values) {
    $values = static::getProcessedValues($plugin_values, $form_values);
    $this->setValues($values);
    $this->onSubmit();
    return $values;
  }

  /**
   * Returns editable instances of the affected configurations.
   *
   * @return \Drupal\Core\Config\Config[]
   */
  protected function getEditableConfiguration() {
    $configs = [];
    foreach ($this->pluginDefinition['affectedConfig'] as $config) {
      $configs[$config] = $this->configFactory->getEditable($config);
    }
    return $configs;
  }

  /**
   * Set additional dependencies for the actual implementation.
   *
   * @param Container $container
   */
  protected function setAdditionalDependencies(Container $container) {}

  /**
   * {@inheritdoc}
   */
  public function hasConfigurationSettings() {
    return TRUE;
  }

  /**
   * Magic getter for yet unset settings. Returns the default value if not set.
   * @param $name
   * @return mixed The default value if not set
   */
  public function __get($name){
      if(isset($this->{$name})){
        return $this->{$name};
      }
      else{
        if (isset(static::getDefaults()[$name])){
          return static::getDefaults()[$name];
        }
      }
  }

}
