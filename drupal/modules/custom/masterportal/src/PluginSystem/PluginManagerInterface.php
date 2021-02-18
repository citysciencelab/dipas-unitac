<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\masterportal\PluginSystem;

/**
 * Interface PluginManagerInterface.
 *
 * Defines the API of our custom plugin managers.
 *
 * @package Drupal\masterportal\PluginSystem
 */
interface PluginManagerInterface {

  /**
   * Returns plugin definitions.
   *
   * @param string|bool $type
   *   If the return value should be filtered on a given type, the type id.
   *
   * @return mixed
   *   The plugin definition(s).
   */
  public function getPluginDefinitions($type = FALSE);

  /**
   * Returns options containing intercepted plugins keyed by their machine name.
   *
   * @return array
   *   The options.
   */
  public function getPluginTypeOptions();

  /**
   * Returns the plugin type id.
   *
   * @return string
   *   The plugin type id.
   */
  public function getPluginType();

}
