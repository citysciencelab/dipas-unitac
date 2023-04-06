<?php

namespace Drupal\masterportal\PluginSystem;

interface ToolPluginManagerInterface extends PluginManagerInterface {

  /**
   * Helper function to retrieve a plugin definition by a given config property value.
   *
   * @param string $propertyValue
   *   The value of the config property.
   *
   * @return array
   *   The plugin definition or NULL if not found.
   */
  public function getPluginDefinitionByConfigProperty($propertyValue);

}
