<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\PluginSystem;

/**
 * Provides a plugin manager for InstanceConfigSection plugins.
 *
 * @see plugin_api
 */
class InstanceConfigSectionManager extends PluginManagerBase {

  const PLUGIN_TYPE = 'instance_config_section';

  /**
   * {@inheritdoc}
   */
  protected function getSubdir() {
    return 'Plugin/Masterportal/InstanceConfigSection';
  }

  /**
   * {@inheritdoc}
   */
  protected function getInterface() {
    return 'Drupal\masterportal\PluginSystem\InstanceConfigSectionInterface';
  }

  /**
   * {@inheritdoc}
   */
  protected function getAnnotation() {
    return 'Drupal\masterportal\Annotation\InstanceConfigSection';
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginType() {
    return self::PLUGIN_TYPE;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginTypeOptions() {
    $sections = parent::getPluginTypeOptions();
    $weights = [];
    $names = [];
    array_walk($sections, function (array $section, $pluginId) use ($weights, $names) {
      $weight[$pluginId] = $section['weight'];
      $names[$pluginId] = $section['title']->__toString();
    });
    array_multisort($weights, SORT_NUMERIC, $names, SORT_ASC, $sections);
    return $sections;
  }

}
