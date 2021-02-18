<?php

/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */

namespace Drupal\dipas\PluginSystem;

/**
 * Interface SidebarBlockPluginManagerInterface.
 *
 * Defines the API for the plugin manager for sidebar blocks.
 *
 * @package Drupal\dipas\PluginSystem
 */
interface SidebarBlockPluginManagerInterface {

  /**
   * Returns an array containing all defined plugins of this type.
   *
   * @param string|bool $pluginId
   *   When a plugin id is given, only the definition of this
   *   plugin is returned.
   *
   * @return array
   *   The plugin definitions.
   */
  public function getPluginDefinitions($pluginId = FALSE);

}
