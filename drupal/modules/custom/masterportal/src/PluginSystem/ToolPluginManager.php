<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\PluginSystem;

/**
 * Provides a plugin manager for ToolPlugin plugins.
 *
 * @see plugin_api
 */
class ToolPluginManager extends PluginManagerBase implements ToolPluginManagerInterface {

  const PLUGIN_TYPE = 'tool_plugin';

  /**
   * {@inheritdoc}
   */
  protected function getSubdir() {
    return 'Plugin/Masterportal/Tools';
  }

  /**
   * {@inheritdoc}
   */
  protected function getInterface() {
    return 'Drupal\masterportal\PluginSystem\ToolPluginInterface';
  }

  /**
   * {@inheritdoc}
   */
  protected function getAnnotation() {
    return 'Drupal\masterportal\Annotation\ToolPlugin';
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
  public function getPluginDefinitionByConfigProperty($propertyValue) {
    $availableToolPlugins = $this->getDefinitions();
    $requestedPluginDefinition = array_filter(
      $availableToolPlugins,
      function ($definition) use ($propertyValue) {
        return $definition['configProperty'] === $propertyValue;
      }
    );

    return count($requestedPluginDefinition) ? array_shift($requestedPluginDefinition) : NULL;
  }

}
