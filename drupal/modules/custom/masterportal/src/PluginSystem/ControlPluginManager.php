<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\PluginSystem;

/**
 * Provides a plugin manager for ControlPlugin plugins.
 *
 * @see plugin_api
 */
class ControlPluginManager extends PluginManagerBase {

  const PLUGIN_TYPE = 'control_plugin';

  /**
   * {@inheritdoc}
   */
  protected function getSubdir() {
    return 'Plugin/Masterportal/Controls';
  }

  /**
   * {@inheritdoc}
   */
  protected function getInterface() {
    return 'Drupal\masterportal\PluginSystem\ControlPluginInterface';
  }

  /**
   * {@inheritdoc}
   */
  protected function getAnnotation() {
    return 'Drupal\masterportal\Annotation\ControlPlugin';
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
    $options = $this->getPluginDefinitions();
    $categories = [];
    $names = [];
    array_walk($options, function (&$option, $key) use (&$categories, &$names) {
      $categories[$key] = $option['category'];
      $names[$key] = $option['title']->__toString();
      $option = $option['title']->__toString();
    });
    array_multisort($categories, SORT_STRING, $names, SORT_STRING, $options);
    return $options;
  }

}
